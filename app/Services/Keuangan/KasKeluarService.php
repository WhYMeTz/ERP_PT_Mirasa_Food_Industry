<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatBayarHutangHdr;
use App\Models\Keuangan\DatKasKeluarDtl;
use App\Models\Keuangan\DatKasKeluarHdr;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
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
        $query = DatKasKeluarHdr::with(['kasBank', 'coa', 'details.coa', 'jurnal'])
            ->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('bkk_no', 'ILIKE', "%{$search}%")
                  ->orWhere('penerima_nama', 'ILIKE', "%{$search}%")
                  ->orWhere('no_referensi_bank', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['kas_bank_id'])) {
            $query->where('kas_bank_id', $filters['kas_bank_id']);
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
        return DatKasKeluarHdr::with(['kasBank.coa', 'coa', 'details.coa', 'jurnal.details.coa'])
            ->where('deleted_st', false)
            ->findOrFail($id);
    }

    /**
     * Simpan Transaksi Bukti Kas Keluar Operasional (BKK)
     * Otomatis:
     * 1. Kurangi Saldo Kas & Bank
     * 2. Catat Mutasi Kas Keluar
     * 3. Catat Jurnal Umum Double Entry (Debet: Beban, Kredit: Kas/Bank)
     */
    public function storeKasKeluar(array $payload, User $user): DatKasKeluarHdr
    {
        return DB::transaction(function () use ($payload, $user) {
            $kasBankId = (int) $payload['kas_bank_id'];
            $tglKeluar = $payload['tgl_keluar'] ?? now()->toDateString();
            $kategori  = $payload['kategori_pengeluaran'] ?? 'BIAYA_OPERASIONAL';
            $penerima  = trim($payload['penerima_nama'] ?? '');
            $items     = $payload['items'] ?? [];

            if (empty($penerima)) {
                throw new Exception("Nama penerima / pihak yang dibayar wajib diisi.");
            }

            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 baris rincian pengeluaran kas.");
            }

            // Hitung total keluar dari rincian
            $totalKeluar = 0;
            foreach ($items as $item) {
                $nom = (float) ($item['nominal'] ?? 0);
                if ($nom <= 0) {
                    throw new Exception("Nominal pada setiap rincian harus lebih besar dari Rp 0.");
                }
                $totalKeluar += $nom;
            }

            if ($totalKeluar <= 0) {
                throw new Exception("Total pengeluaran harus lebih besar dari Rp 0.");
            }

            // Lock rekening bank sumber dana
            $kasBank = MstKasBank::where('deleted_st', false)
                ->where('active_st', true)
                ->lockForUpdate()
                ->findOrFail($kasBankId);

            // Validasi saldo cukup jika akun kas
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            if ($kasBank->tipe_akun === 'KAS' && $saldoSebelum < $totalKeluar) {
                throw new Exception("Saldo pada akun '{$kasBank->nama_akun}' tidak mencukupi (Tersedia: Rp " . number_format($saldoSebelum, 0, ',', '.') . ", Dibutuhkan: Rp " . number_format($totalKeluar, 0, ',', '.') . ").");
            }

            $bkkNo = !empty($payload['bkk_no']) ? trim($payload['bkk_no']) : $this->generateBkkNo($tglKeluar);

            // Tentukan akun COA Header utama (default dari baris pertama jika tidak dispesifikasi)
            $defaultCoaId = !empty($payload['coa_id']) ? (int) $payload['coa_id'] : (!empty($items[0]['coa_id']) ? (int) $items[0]['coa_id'] : null);
            if (!$defaultCoaId) {
                // Fallback ke 6110 Beban Lain-lain jika tidak ada
                $fallbackCoa = MstCoa::where('kode_akun', '6110')->first();
                $defaultCoaId = $fallbackCoa?->coa_id;
            }

            // 1. Buat Header BKK Operasional
            $bkk = DatKasKeluarHdr::create([
                'bkk_no'               => $bkkNo,
                'tgl_keluar'           => $tglKeluar,
                'kas_bank_id'          => $kasBank->kas_bank_id,
                'coa_id'               => $defaultCoaId,
                'kategori_pengeluaran' => $kategori,
                'penerima_nama'        => $penerima,
                'metode_bayar'         => $payload['metode_bayar'] ?? 'CASH',
                'no_referensi_bank'    => !empty($payload['no_referensi_bank']) ? trim($payload['no_referensi_bank']) : null,
                'total_keluar'         => $totalKeluar,
                'catatan'              => !empty($payload['catatan']) ? trim($payload['catatan']) : null,
                'status_cd'            => 'POSTED',
                'created_by'           => $user->name ?? 'SYSTEM',
                'updated_by'           => $user->name ?? 'SYSTEM',
            ]);

            // 2. Buat Detail Rincian Item BKK & Kumpulkan entri jurnal beban
            $journalDebits = [];
            foreach ($items as $item) {
                $itemCoaId = !empty($item['coa_id']) ? (int) $item['coa_id'] : $defaultCoaId;
                $uraian = trim($item['uraian']);
                $nom = (float) $item['nominal'];

                DatKasKeluarDtl::create([
                    'bkk_id'       => $bkk->bkk_id,
                    'coa_id'       => $itemCoaId,
                    'uraian'       => $uraian,
                    'qty'          => (float) ($item['qty'] ?? 1),
                    'satuan'       => !empty($item['satuan']) ? trim($item['satuan']) : null,
                    'harga_satuan' => (float) ($item['harga_satuan'] ?? $nom),
                    'nominal'      => $nom,
                    'catatan'      => !empty($item['catatan']) ? trim($item['catatan']) : null,
                    'created_by'   => $user->name ?? 'SYSTEM',
                    'updated_by'   => $user->name ?? 'SYSTEM',
                ]);

                // Agregasi debet jurnal per COA
                if (!isset($journalDebits[$itemCoaId])) {
                    $journalDebits[$itemCoaId] = [
                        'coa_id'     => $itemCoaId,
                        'nominal'    => 0,
                        'keterangan' => $uraian,
                    ];
                }
                $journalDebits[$itemCoaId]['nominal'] += $nom;
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

            // 5. Catat Jurnal Umum Double Entry Otomatis
            // Temukan COA Kredit untuk Kas/Bank sumber
            $kasBankCoaId = $kasBank->coa_id;
            if (!$kasBankCoaId) {
                $kasCoa = MstCoa::where('kode_akun', $kasBank->kode_akun)->first()
                    ?? MstCoa::where('kode_akun', '1100')->first();
                $kasBankCoaId = $kasCoa?->coa_id;
            }

            $entries = [];
            // A. Debet Beban
            foreach ($journalDebits as $jd) {
                $entries[] = [
                    'coa_id'     => $jd['coa_id'],
                    'debet'      => $jd['nominal'],
                    'kredit'     => 0,
                    'keterangan' => "BKK {$bkk->bkk_no}: {$jd['keterangan']} - {$penerima}",
                ];
            }
            // B. Kredit Kas/Bank
            $entries[] = [
                'coa_id'     => $kasBankCoaId,
                'debet'      => 0,
                'kredit'     => $totalKeluar,
                'keterangan' => "Pengeluaran Kas/Bank {$bkk->bkk_no} kepada {$penerima}",
            ];

            $jurnal = $this->jurnalService->catatJurnal(
                'BKK_OPERASIONAL',
                $tglKeluar,
                $bkk->bkk_no,
                $bkk->bkk_id,
                "Bukti Kas Keluar Operasional - {$bkk->bkk_no} ({$penerima})",
                $entries,
                $user
            );

            // Hubungkan jurnal ke BKK
            $bkk->update([
                'jurnal_id' => $jurnal->jurnal_id,
            ]);

            return $bkk;
        });
    }

    /**
     * Batalkan / Void Transaksi Bukti Kas Keluar (BKK)
     * Mengembalikan saldo kas/bank dan membalik jurnal.
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

            // 3. Batalkan Jurnal terkait
            if ($bkk->jurnal) {
                $bkk->jurnal->update([
                    'deleted_st' => true,
                    'deleted_by' => $user->name ?? 'SYSTEM',
                    'deleted_at' => now(),
                    'keterangan' => "[VOID]: " . $bkk->jurnal->keterangan . " (Alasan: {$alasan})",
                ]);
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

