<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatBayarPiutangDtl;
use App\Models\Keuangan\DatBayarPiutangHdr;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Models\MasterData\MstCustomer;
use App\Models\Penjualan\DatFakturHdr;
use App\Models\User;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BayarPiutangService
{
    protected JurnalService $jurnalService;
    protected PengaturanJurnalService $pengaturanJurnalService;

    public function __construct(
        JurnalService $jurnalService,
        PengaturanJurnalService $pengaturanJurnalService
    ) {
        $this->jurnalService = $jurnalService;
        $this->pengaturanJurnalService = $pengaturanJurnalService;
    }

    /**
     * Generate Nomor Bukti Kas Masuk (BKM AR) dengan format BKM-YYYYMM-XXXX
     */
    public function generateBkmNo(?string $tglBayar = null): string
    {
        $dateObj = $tglBayar ? \Carbon\Carbon::parse($tglBayar) : now();
        $prefix = 'BKM-' . $dateObj->format('Ym') . '-';

        $lastRecord = DatBayarPiutangHdr::where('bkm_no', 'like', $prefix . '%')
            ->orderBy('bkm_no', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNum = (int) substr($lastRecord->bkm_no, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    /**
     * Ambil daftar riwayat pelunasan piutang (paginated)
     */
    public function getBayarPiutangListPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatBayarPiutangHdr::with(['customer', 'kasBank', 'details.faktur'])
            ->where('deleted_st', false);

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['kas_bank_id'])) {
            $query->where('kas_bank_id', $filters['kas_bank_id']);
        }

        if (!empty($filters['metode_bayar'])) {
            $query->where('metode_bayar', $filters['metode_bayar']);
        }

        if (!empty($filters['status_cd'])) {
            $statusCd = strtoupper(trim($filters['status_cd']));
            if ($statusCd === 'VOID') {
                $query->where('status_cd', 'VOID');
            } elseif ($statusCd === 'SEBAGIAN') {
                $query->where('status_cd', 'POSTED')
                    ->whereHas('details', function ($q) {
                        $q->where('sisa_piutang_sesudah', '>', 0);
                    });
            } elseif ($statusCd === 'LUNAS') {
                $query->where('status_cd', 'POSTED')
                    ->whereDoesntHave('details', function ($q) {
                        $q->where('sisa_piutang_sesudah', '>', 0);
                    });
            } elseif ($statusCd === 'POSTED') {
                $query->where('status_cd', 'POSTED');
            } else {
                $query->where('status_cd', $statusCd);
            }
        }

        if (!empty($filters['tgl_mulai'])) {
            $query->whereDate('tgl_bayar', '>=', $filters['tgl_mulai']);
        }

        if (!empty($filters['tgl_selesai'])) {
            $query->whereDate('tgl_bayar', '<=', $filters['tgl_selesai']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('bkm_no', 'like', "%{$search}%")
                  ->orWhere('no_referensi_bank', 'like', "%{$search}%")
                  ->orWhere('diterima_dari', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($sq) use ($search) {
                      $sq->where('customer_nm', 'like', "%{$search}%")
                         ->orWhere('customer_cd', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('tgl_bayar', 'desc')
            ->orderBy('bayar_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Dapatkan daftar invoice/faktur penjualan milik customer yang belum lunas
     */
    public function getUnpaidFakturCustomer(int $customerId): Collection
    {
        return DatFakturHdr::with(['customer', 'details'])
            ->where('customer_id', $customerId)
            ->where('deleted_st', false)
            ->where('active_st', true)
            ->where('status_pembayaran', '!=', 'LUNAS')
            ->where('sisa_piutang', '>', 0)
            ->orderBy('tgl_jatuh_tempo', 'asc')
            ->orderBy('faktur_tgl', 'asc')
            ->get();
    }

    /**
     * Simpan Pelunasan Piutang (Eksekusi Transaksi BKM AR)
     */
    public function simpanPelunasanPiutang(array $payload, ?User $user = null): DatBayarPiutangHdr
    {
        return DB::transaction(function () use ($payload, $user) {
            $userName = $user?->name ?? 'SYSTEM';
            $tglBayar = $payload['tgl_bayar'] ?? now()->toDateString();
            $customerId = (int) $payload['customer_id'];
            $kasBankId = (int) $payload['kas_bank_id'];
            $metodeBayar = strtoupper(trim($payload['metode_bayar'] ?? 'TRANSFER'));
            $noRefBank = !empty($payload['no_referensi_bank']) ? trim($payload['no_referensi_bank']) : null;
            $catatan = !empty($payload['catatan']) ? trim($payload['catatan']) : null;

            // 1. Kunci dan validasi Rekening Kas/Bank
            $kasBank = MstKasBank::lockForUpdate()->findOrFail($kasBankId);

            // 2. Validasi Customer
            $customer = MstCustomer::findOrFail($customerId);
            $diterimaDari = !empty($payload['diterima_dari']) ? trim($payload['diterima_dari']) : $customer->customer_nm;

            // 3. Validasi & Filter Alokasi Faktur
            $rawAllocations = $payload['allocations'] ?? [];
            $validAllocations = [];
            $totalBayar = 0;

            foreach ($rawAllocations as $alloc) {
                $fakturId = (int) ($alloc['faktur_id'] ?? 0);
                $nominal = (float) ($alloc['nominal_bayar'] ?? 0);

                if ($fakturId > 0 && $nominal > 0) {
                    $validAllocations[] = [
                        'faktur_id'     => $fakturId,
                        'nominal_bayar' => $nominal,
                        'catatan'       => !empty($alloc['catatan']) ? trim($alloc['catatan']) : null,
                    ];
                    $totalBayar += $nominal;
                }
            }

            if (empty($validAllocations) || $totalBayar <= 0) {
                throw new Exception('Pilih minimal satu faktur penjualan dan tentukan nominal pembayaran.');
            }

            // 4. Generate Nomor BKM
            $bkmNo = $this->generateBkmNo($tglBayar);

            // 5. Buat Header BKM
            $bkm = DatBayarPiutangHdr::create([
                'bkm_no'            => $bkmNo,
                'tgl_bayar'         => $tglBayar,
                'customer_id'       => $customerId,
                'kas_bank_id'       => $kasBankId,
                'metode_bayar'      => $metodeBayar,
                'no_referensi_bank' => $noRefBank,
                'total_bayar'       => $totalBayar,
                'diterima_dari'     => $diterimaDari,
                'catatan'           => $catatan,
                'status_cd'         => 'POSTED',
                'created_by'        => $userName,
                'updated_by'        => $userName,
            ]);

            // 6. Proses Pemotongan Piutang Tiap Faktur & Buat Detail BKM
            foreach ($validAllocations as $alloc) {
                $faktur = DatFakturHdr::lockForUpdate()->findOrFail($alloc['faktur_id']);

                if ($faktur->customer_id != $customerId) {
                    throw new Exception("Faktur {$faktur->faktur_no} bukan milik customer {$customer->customer_nm}.");
                }

                $nominal = $alloc['nominal_bayar'];
                $sisaSebelum = (float) $faktur->sisa_piutang;

                if ($nominal > ($sisaSebelum + 0.01)) {
                    throw new Exception("Nominal bayar (Rp " . number_format($nominal, 0, ',', '.') . ") melebihi sisa piutang faktur {$faktur->faktur_no} (Rp " . number_format($sisaSebelum, 0, ',', '.') . ").");
                }

                $sisaSesudah = max(0, $sisaSebelum - $nominal);
                $totalDibayarBaru = (float) $faktur->total_dibayar + $nominal;
                $newStatus = ($sisaSesudah <= 0.0001) ? 'LUNAS' : 'SEBAGIAN';

                DatBayarPiutangDtl::create([
                    'bayar_id'             => $bkm->bayar_id,
                    'faktur_id'            => $faktur->faktur_id,
                    'nominal_bayar'        => $nominal,
                    'sisa_piutang_sebelum' => $sisaSebelum,
                    'sisa_piutang_sesudah' => $sisaSesudah,
                    'catatan'              => $alloc['catatan'] ?? "Pelunasan via {$bkmNo}",
                    'created_by'           => $userName,
                    'updated_by'           => $userName,
                ]);

                $faktur->update([
                    'total_dibayar'     => $totalDibayarBaru,
                    'sisa_piutang'      => $sisaSesudah,
                    'status_pembayaran' => $newStatus,
                    'updated_by'        => $userName,
                ]);
            }

            // 7. Tambah Saldo Berjalan Rekening Kas & Bank (+ totalBayar)
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum + $totalBayar;

            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $userName,
            ]);

            // 8. Catat Mutasi Arus Kas Masuk
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => $tglBayar,
                'tipe_mutasi'     => 'MASUK',
                'nominal'         => $totalBayar,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'BAYAR_PIUTANG',
                'ref_no'          => $bkm->bkm_no,
                'ref_id'          => $bkm->bayar_id,
                'keterangan'      => "Penerimaan pelunasan piutang customer {$customer->customer_nm} via {$kasBank->nama_akun} ({$bkm->bkm_no})",
                'created_by'      => $userName,
                'updated_by'      => $userName,
            ]);

            // 9. Auto-jurnal Double-Entry
            $kodeKas = $kasBank->getCoaKodeAkun();
            $kodePiutang = $this->getCoaPiutangKode();

            $keteranganJurnal = "Penerimaan Piutang - {$customer->customer_nm} - {$bkm->bkm_no}";

            $jurnalEntries = [
                [
                    'kode_akun'  => $kodeKas,      // (Debet) Kas/Bank bertambah
                    'debet'      => $totalBayar,
                    'kredit'     => 0,
                    'keterangan' => $keteranganJurnal,
                ],
                [
                    'kode_akun'  => $kodePiutang,  // (Kredit) Piutang Usaha berkurang
                    'debet'      => 0,
                    'kredit'     => $totalBayar,
                    'keterangan' => $keteranganJurnal,
                ],
            ];

            $this->jurnalService->catatJurnal(
                tipeTransaksi: 'BAYAR_PIUTANG',
                tglJurnal: $tglBayar,
                refNo: $bkm->bkm_no,
                refId: $bkm->bayar_id,
                keterangan: $keteranganJurnal,
                entries: $jurnalEntries,
                user: $user
            );

            return $bkm;
        });
    }

    /**
     * Batalkan / Void Transaksi Pembayaran BKM AR
     */
    public function batalkanPelunasanPiutang(int $bayarId, string $alasan = '', ?User $user = null): DatBayarPiutangHdr
    {
        return DB::transaction(function () use ($bayarId, $alasan, $user) {
            $userName = $user?->name ?? 'SYSTEM';

            $bkm = DatBayarPiutangHdr::lockForUpdate()
                ->with(['details', 'kasBank', 'customer'])
                ->where('deleted_st', false)
                ->findOrFail($bayarId);

            if ($bkm->status_cd === 'VOID') {
                throw new Exception("Bukti Kas Masuk {$bkm->bkm_no} sudah dalam status VOID.");
            }

            $kasBank = MstKasBank::lockForUpdate()->findOrFail($bkm->kas_bank_id);

            // 1. Kembalikan sisa piutang pada masing-masing faktur penjualan
            foreach ($bkm->details as $dtl) {
                $faktur = DatFakturHdr::lockForUpdate()->find($dtl->faktur_id);
                if ($faktur) {
                    $sisaBaru = (float) $faktur->sisa_piutang + (float) $dtl->nominal_bayar;
                    $dibayarBaru = max(0, (float) $faktur->total_dibayar - (float) $dtl->nominal_bayar);
                    $newStatus = ($dibayarBaru <= 0.0001) ? 'BELUM_LUNAS' : 'SEBAGIAN';

                    $faktur->update([
                        'total_dibayar'     => $dibayarBaru,
                        'sisa_piutang'      => $sisaBaru,
                        'status_pembayaran' => $newStatus,
                        'updated_by'        => $userName,
                    ]);
                }
            }

            // 2. Balikkan Saldo Rekening Kas & Bank (- totalBayar)
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum - (float) $bkm->total_bayar;

            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $userName,
            ]);

            // 3. Catat Mutasi Pengembalian Dana / Kas Keluar
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => now()->toDateString(),
                'tipe_mutasi'     => 'KELUAR',
                'nominal'         => (float) $bkm->total_bayar,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'MUTASI_KAS',
                'ref_no'          => $bkm->bkm_no . '-VOID',
                'ref_id'          => $bkm->bayar_id,
                'keterangan'      => "Pembatalan / VOID penerimaan piutang BKM #{$bkm->bkm_no}" . (!empty($alasan) ? ". Alasan: {$alasan}" : ''),
                'created_by'      => $userName,
                'updated_by'      => $userName,
            ]);

            // 4. Update Status BKM ke VOID
            $catatanUpdate = $bkm->catatan;
            if (!empty($alasan)) {
                $catatanUpdate = ($catatanUpdate ? $catatanUpdate . "\n" : '') . "[VOID: {$alasan}]";
            }

            $bkm->update([
                'status_cd'  => 'VOID',
                'catatan'    => $catatanUpdate,
                'updated_by' => $userName,
            ]);

            // 5. Batalkan Jurnal dan Balikkan Saldo COA
            $this->jurnalService->batalkanJurnal($bkm->bkm_no, $user);

            return $bkm;
        });
    }

    /**
     * Dapatkan Kode Akun COA Piutang Usaha
     */
    protected function getCoaPiutangKode(): string
    {
        $configured = $this->pengaturanJurnalService->getCoaKode('PIUTANG_USAHA', '');
        if (!empty($configured)) {
            return $configured;
        }

        $coa = MstCoa::where('deleted_st', false)
            ->where(function ($q) {
                $q->where('kode_akun', '1120')
                  ->orWhere('kode_akun', '1103')
                  ->orWhere('nama_akun', 'like', '%Piutang Dagang%')
                  ->orWhere('nama_akun', 'like', '%Piutang Usaha%');
            })
            ->orderByRaw("CASE WHEN kode_akun = '1120' THEN 1 WHEN kode_akun = '1103' THEN 2 ELSE 3 END")
            ->first();

        return $coa ? $coa->kode_akun : '1120';
    }
}
