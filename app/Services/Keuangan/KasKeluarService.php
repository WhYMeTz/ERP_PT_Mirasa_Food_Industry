<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatBayarHutangHdr;
use App\Models\Keuangan\DatKasKeluarDtl;
use App\Models\Keuangan\DatKasKeluarHdr;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Models\Keuangan\MstKategoriPengeluaran;
use App\Models\MasterData\MstKaryawan;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KasKeluarService
{
    public function __construct(
        protected JurnalService $jurnalService
    ) {}

    /**
     * Generate Nomor Bukti Kas/Bank Keluar (BKK-YYYYMM-XXXX)
     * Sinkron antara BKK Hutang dan BKK Operasional agar urut dan tidak duplikat.
     */
    public function generateBkkNo(?string $date = null): string
    {
        $dt = $date ? Carbon::parse($date) : now();
        $prefix = 'BKK-' . $dt->format('Ym') . '-';

        // Cari nomor terakhir dari kedua tabel (bayar_hutang & kas_keluar)
        $lastHutang = DatBayarHutangHdr::where('bayar_no', 'LIKE', "{$prefix}%")
            ->orderBy('bayar_no', 'desc')
            ->value('bayar_no');

        $lastOperasional = DatKasKeluarHdr::where('bkk_no', 'LIKE', "{$prefix}%")
            ->orderBy('bkk_no', 'desc')
            ->value('bkk_no');

        $numHutang = $lastHutang ? (int) substr($lastHutang, -4) : 0;
        $numOps = $lastOperasional ? (int) substr($lastOperasional, -4) : 0;

        $maxNum = max($numHutang, $numOps);
        $nextNumber = str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT);

        return $prefix . $nextNumber;
    }

    /**
     * Dapatkan daftar transaksi Kas Keluar Operasional dengan paginasi dan filter
     */
    public function getKasKeluarListPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatKasKeluarHdr::with(['kasBank', 'coa', 'kategoriPengeluaran.coaDefault', 'details.coa', 'jurnal', 'pemohon'])
            ->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('bkk_no', 'ILIKE', "%{$search}%")
                  ->orWhere('penerima_nama', 'ILIKE', "%{$search}%")
                  ->orWhere('departemen_pemohon', 'ILIKE', "%{$search}%")
                  ->orWhere('no_referensi_bank', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan', 'ILIKE', "%{$search}%")
                  ->orWhereHas('pemohon', function ($qp) use ($search) {
                      $qp->where('karyawan_nm', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['kas_bank_id'])) {
            $query->where('kas_bank_id', $filters['kas_bank_id']);
        }

        if (!empty($filters['status_cd'])) {
            $query->where('status_cd', $filters['status_cd']);
        }

        if (!empty($filters['kategori'])) {
            $query->where('kategori_pengeluaran', $filters['kategori']);
        }

        if (!empty($filters['coa_id'])) {
            $query->where('coa_id', $filters['coa_id']);
        }

        if (!empty($filters['metode_bayar'])) {
            $query->where('metode_bayar', $filters['metode_bayar']);
        }

        if (!empty($filters['tgl_mulai'])) {
            $query->whereDate('tgl_keluar', '>=', $filters['tgl_mulai']);
        }

        if (!empty($filters['tgl_selesai'])) {
            $query->whereDate('tgl_keluar', '<=', $filters['tgl_selesai']);
        }

        return $query->orderBy('tgl_keluar', 'desc')
                     ->orderBy('bkk_id', 'desc')
                     ->paginate($perPage)
                     ->withQueryString();
    }

    /**
     * Dapatkan detail BKK berdasarkan ID
     */
    public function getById(int $id): DatKasKeluarHdr
    {
        return DatKasKeluarHdr::with(['kasBank.coa', 'coa', 'kategoriPengeluaran.coaDefault', 'details.coa', 'jurnal.details.coa', 'pemohon'])
            ->where('deleted_st', false)
            ->findOrFail($id);
    }

    /**
     * Simpan Transaksi Bukti Kas Keluar Operasional (BKK) Tahap 1 (Kasir)
     * Otomatis:
     * 1. Kurangi Saldo Kas & Bank fisik
     * 2. Catat Mutasi Kas Keluar
     * 3. Simpan BKK dengan status DRAFT & jurnal_id = null (menunggu verifikasi COA & posting oleh Akuntansi)
     */
    public function storeKasKeluar(array $payload, User $user): DatKasKeluarHdr
    {
        return DB::transaction(function () use ($payload, $user) {
            $kasBankId = (int) $payload['kas_bank_id'];
            $tglKeluar = $payload['tgl_keluar'] ?? now()->toDateString();
            $kategori  = $payload['kategori_pengeluaran'] ?? 'BIAYA_OPERASIONAL';
            $penerima  = trim($payload['penerima_nama'] ?? '');
            $items     = $payload['items'] ?? [];

            // Tangkap identitas pemohon dan tipe penerima
            $pemohonKaryawanId = !empty($payload['pemohon_karyawan_id']) ? (int) $payload['pemohon_karyawan_id'] : null;
            $departemenPemohon = !empty($payload['departemen_pemohon']) ? trim($payload['departemen_pemohon']) : null;
            $tipePenerima      = !empty($payload['tipe_penerima']) ? trim($payload['tipe_penerima']) : 'PIHAK_KETIGA';

            // Resolusi Kategori Pengeluaran dari Master Data
            $kategoriId = !empty($payload['kategori_id']) ? (int) $payload['kategori_id'] : null;
            $defaultCoaId = !empty($payload['coa_id']) ? (int) $payload['coa_id'] : null;

            if ($kategoriId) {
                $katObj = MstKategoriPengeluaran::where('deleted_st', false)->find($kategoriId);
                if ($katObj) {
                    $kategori = $katObj->kode_kategori;
                    if (empty($defaultCoaId) && $katObj->coa_id_default) {
                        $defaultCoaId = $katObj->coa_id_default;
                    }
                }
            } elseif (!empty($payload['kategori_pengeluaran'])) {
                $katObj = MstKategoriPengeluaran::where('deleted_st', false)
                    ->where('kode_kategori', $payload['kategori_pengeluaran'])
                    ->first();
                if ($katObj) {
                    $kategoriId = $katObj->kategori_id;
                    if (empty($defaultCoaId) && $katObj->coa_id_default) {
                        $defaultCoaId = $katObj->coa_id_default;
                    }
                }
            }

            if ($pemohonKaryawanId) {
                $karyawan = MstKaryawan::where('deleted_st', false)->find($pemohonKaryawanId);
                if ($karyawan) {
                    if (empty($departemenPemohon)) {
                        $departemenPemohon = $karyawan->departemen_cd ?? $karyawan->jabatan_nm ?? null;
                    }
                    if ($tipePenerima === 'KARYAWAN') {
                        if (empty($penerima) || !empty($payload['pilih_karyawan_sebagai_penerima'])) {
                            $penerima = $karyawan->karyawan_nm;
                        }
                    }
                }
            }

            if (empty($penerima)) {
                throw new Exception("Nama penerima / pihak yang dibayar wajib diisi.");
            }

            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 baris rincian pengeluaran kas.");
            }

            // Lock rekening bank sumber dana lebih awal untuk mengetahui context
            $kasBank = MstKasBank::where('deleted_st', false)
                ->where('active_st', true)
                ->lockForUpdate()
                ->findOrFail($kasBankId);

            $defaultCoaId = !empty($payload['coa_id']) ? (int) $payload['coa_id'] : null;

            // Direct Nominal First processing
            $processedItems = [];
            $totalKeluar = 0;
            foreach ($items as $item) {
                $uraian = trim($item['uraian'] ?? '');
                if (empty($uraian)) {
                    continue;
                }

                $qty = (float) ($item['qty'] ?? 1);
                if ($qty <= 0) {
                    $qty = 1;
                }
                $satuan = !empty($item['satuan']) ? trim($item['satuan']) : 'LS';

                $rawNominal = isset($item['nominal']) ? (float) $item['nominal'] : 0;
                $rawHarga   = isset($item['harga_satuan']) ? (float) $item['harga_satuan'] : 0;

                if ($rawNominal > 0) {
                    $nominal = $rawNominal;
                    $hargaSatuan = ($rawHarga > 0) ? $rawHarga : ($qty > 0 ? ($nominal / $qty) : $nominal);
                } elseif ($rawHarga > 0) {
                    $nominal = $qty * $rawHarga;
                    $hargaSatuan = $rawHarga;
                } else {
                    throw new Exception("Nominal pada setiap rincian pengeluaran harus lebih besar dari Rp 0.");
                }

                if ($nominal <= 0) {
                    throw new Exception("Nominal pada setiap rincian pengeluaran harus lebih besar dari Rp 0.");
                }

                $totalKeluar += $nominal;
                $processedItems[] = [
                    'coa_id'       => !empty($item['coa_id']) ? (int) $item['coa_id'] : $defaultCoaId,
                    'uraian'       => $uraian,
                    'qty'          => $qty,
                    'satuan'       => $satuan,
                    'harga_satuan' => $hargaSatuan,
                    'nominal'      => $nominal,
                    'catatan'      => !empty($item['catatan']) ? trim($item['catatan']) : null,
                ];
            }

            if (empty($processedItems)) {
                throw new Exception("Minimal harus ada 1 baris rincian pengeluaran kas dengan nominal valid.");
            }

            if ($totalKeluar <= 0) {
                throw new Exception("Total pengeluaran harus lebih besar dari Rp 0.");
            }

            // Validasi saldo cukup jika akun kas
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            if ($kasBank->tipe_akun === 'KAS' && $saldoSebelum < $totalKeluar) {
                throw new Exception("Saldo pada akun '{$kasBank->nama_akun}' tidak mencukupi (Tersedia: Rp " . number_format($saldoSebelum, 0, ',', '.') . ", Dibutuhkan: Rp " . number_format($totalKeluar, 0, ',', '.') . ").");
            }

            $bkkNo = !empty($payload['bkk_no']) ? trim($payload['bkk_no']) : $this->generateBkkNo($tglKeluar);

            // 1. Buat Header BKK Operasional dengan status DRAFT (Menunggu Verifikasi Jurnal Akuntansi)
            $bkk = DatKasKeluarHdr::create([
                'bkk_no'               => $bkkNo,
                'tgl_keluar'           => $tglKeluar,
                'kas_bank_id'          => $kasBank->kas_bank_id,
                'coa_id'               => $defaultCoaId,
                'pemohon_karyawan_id'  => $pemohonKaryawanId,
                'departemen_pemohon'   => $departemenPemohon,
                'tipe_penerima'        => $tipePenerima,
                'kategori_id'          => $kategoriId,
                'kategori_pengeluaran' => $kategori,
                'penerima_nama'        => $penerima,
                'metode_bayar'         => $payload['metode_bayar'] ?? 'CASH',
                'no_referensi_bank'    => !empty($payload['no_referensi_bank']) ? trim($payload['no_referensi_bank']) : null,
                'total_keluar'         => $totalKeluar,
                'catatan'              => !empty($payload['catatan']) ? trim($payload['catatan']) : null,
                'status_cd'            => 'DRAFT',
                'jurnal_id'            => null,
                'created_by'           => $user->name ?? 'SYSTEM',
                'updated_by'           => $user->name ?? 'SYSTEM',
            ]);

            // 2. Buat Detail Rincian Item BKK
            foreach ($processedItems as $item) {
                DatKasKeluarDtl::create([
                    'bkk_id'       => $bkk->bkk_id,
                    'coa_id'       => $item['coa_id'],
                    'uraian'       => $item['uraian'],
                    'qty'          => $item['qty'],
                    'satuan'       => $item['satuan'],
                    'harga_satuan' => $item['harga_satuan'],
                    'nominal'      => $item['nominal'],
                    'catatan'      => $item['catatan'],
                    'created_by'   => $user->name ?? 'SYSTEM',
                    'updated_by'   => $user->name ?? 'SYSTEM',
                ]);
            }

            // 3. Kurangi Saldo Berjalan Rekening Kas & Bank
            $saldoSesudah = $saldoSebelum - $totalKeluar;
            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            // 4. Catat Buku Mutasi Kas & Bank Keluar
            $namaAkun = $kasBank->nama_akun ?? 'Kas/Bank';
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => $tglKeluar,
                'tipe_mutasi'     => 'KELUAR',
                'nominal'         => $totalKeluar,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'PENGELUARAN_OPERASIONAL',
                'ref_no'          => $bkk->bkk_no,
                'ref_id'          => $bkk->bkk_id,
                'keterangan'      => "Pengeluaran Kas/Bank ({$bkk->bkk_no}) - Dibayarkan kepada {$penerima} via {$namaAkun}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            return $bkk;
        });
    }

    /**
     * Verifikasi Pengeluaran Kas (BKK Operasional) oleh Akuntansi & Terbitkan Jurnal Umum (Tahap 2)
     */
    public function verifikasiDanTerbitkanJurnal(int $bkkId, int $coaBebanId, ?string $tglJurnal, User $user): DatKasKeluarHdr
    {
        return DB::transaction(function () use ($bkkId, $coaBebanId, $tglJurnal, $user) {
            $bkk = DatKasKeluarHdr::with(['kasBank', 'details'])
                ->where('deleted_st', false)
                ->lockForUpdate()
                ->findOrFail($bkkId);

            if ($bkk->status_cd === 'VOID') {
                throw new Exception("BKK {$bkk->bkk_no} sudah berstatus VOID dan tidak dapat dijurnal.");
            }

            if ($bkk->status_cd === 'POSTED' && !empty($bkk->jurnal_id)) {
                throw new Exception("BKK {$bkk->bkk_no} sudah diverifikasi dan sudah memiliki Jurnal Umum.");
            }

            $coaBeban = MstCoa::where('deleted_st', false)->findOrFail($coaBebanId);
            $kasBank = $bkk->kasBank;

            // Cari COA Kredit untuk Kas/Bank sumber
            $kasBankCoaId = $kasBank?->coa_id;
            if (!$kasBankCoaId && $kasBank) {
                $kasCoa = MstCoa::where('kode_akun', $kasBank->kode_akun)->first()
                    ?? MstCoa::where('kode_akun', '1100')->first();
                $kasBankCoaId = $kasCoa?->coa_id;
            }

            if (!$kasBankCoaId) {
                throw new Exception("Akun COA untuk sumber kas/bank '{$kasBank?->nama_akun}' belum terpetakan.");
            }

            // Update coa_id pada detail BKK
            $bkk->details()->update([
                'coa_id'     => $coaBeban->coa_id,
                'updated_by' => $user->name ?? 'SYSTEM',
            ]);

            // Susun baris Jurnal Umum (Debet: Akun Beban pilihan Akuntansi, Kredit: Kas/Bank)
            $entries = [];
            foreach ($bkk->details as $dtl) {
                $entries[] = [
                    'coa_id'     => $coaBeban->coa_id,
                    'debet'      => (float) $dtl->nominal,
                    'kredit'     => 0,
                    'keterangan' => "BKK {$bkk->bkk_no}: {$dtl->uraian} - {$bkk->penerima_nama}",
                ];
            }

            if (empty($entries)) {
                $entries[] = [
                    'coa_id'     => $coaBeban->coa_id,
                    'debet'      => (float) $bkk->total_keluar,
                    'kredit'     => 0,
                    'keterangan' => "BKK {$bkk->bkk_no} - {$bkk->penerima_nama}",
                ];
            }

            $entries[] = [
                'coa_id'     => $kasBankCoaId,
                'debet'      => 0,
                'kredit'     => (float) $bkk->total_keluar,
                'keterangan' => "Pengeluaran Kas/Bank {$bkk->bkk_no} kepada {$bkk->penerima_nama}",
            ];

            $tanggalPosting = $tglJurnal ?: $bkk->tgl_keluar->toDateString();

            $jurnal = $this->jurnalService->catatJurnal(
                'BKK_OPERASIONAL',
                $tanggalPosting,
                $bkk->bkk_no,
                $bkk->bkk_id,
                "Bukti Kas Keluar Operasional - {$bkk->bkk_no} ({$bkk->penerima_nama})",
                $entries,
                $user
            );

            $bkk->update([
                'coa_id'     => $coaBeban->coa_id,
                'jurnal_id'  => $jurnal->jurnal_id,
                'status_cd'  => 'POSTED',
                'updated_by' => $user->name ?? 'SYSTEM',
            ]);

            return $bkk;
        });
    }

    /**
     * Batalkan / Void Transaksi Bukti Kas Keluar (BKK)
     * Mengembalikan saldo kas/bank dan membalik jurnal (jika sudah diterbitkan).
     */
    public function voidKasKeluar(int $bkkId, string $alasan, User $user): DatKasKeluarHdr
    {
        return DB::transaction(function () use ($bkkId, $alasan, $user) {
            $bkk = DatKasKeluarHdr::with(['kasBank', 'jurnal'])->lockForUpdate()->findOrFail($bkkId);

            if ($bkk->status_cd === 'VOID') {
                throw new Exception("Dokumen Kas Keluar {$bkk->bkk_no} sudah berstatus VOID.");
            }

            $kasBank = MstKasBank::lockForUpdate()->findOrFail($bkk->kas_bank_id);

            // 1. Kembalikan saldo kas/bank
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum + (float) $bkk->total_keluar;

            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            // 2. Catat Mutasi Pemulihan
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => now()->toDateString(),
                'tipe_mutasi'     => 'MASUK',
                'nominal'         => (float) $bkk->total_keluar,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'VOID_KAS_KELUAR',
                'ref_no'          => $bkk->bkk_no,
                'ref_id'          => $bkk->bkk_id,
                'keterangan'      => "Pembatalan (VOID) Kas Keluar {$bkk->bkk_no}: {$alasan}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            // 3. Batalkan Jurnal terkait jika sudah diterbitkan oleh Akuntansi
            if (!empty($bkk->jurnal_id)) {
                $this->jurnalService->batalkanJurnal($bkk->bkk_no, $user);
            }

            // 4. Update Status BKK
            $bkk->update([
                'status_cd'   => 'VOID',
                'alasan_void' => $alasan,
                'void_by'     => $user->name ?? 'SYSTEM',
                'void_dt'     => now(),
                'updated_by'  => $user->name ?? 'SYSTEM',
            ]);

            return $bkk;
        });
    }
}
