<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatKasMasukDtl;
use App\Models\Keuangan\DatKasMasukHdr;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Models\User;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KasMasukService
{
    public function __construct(
        protected JurnalService $jurnalService
    ) {}

    /**
     * Generate Nomor Bukti Kas Masuk (BKM): BKM-YYYYMM-XXXX
     */
    public function generateBkmNo(?string $date = null): string
    {
        $dt = $date ? \Carbon\Carbon::parse($date) : now();
        $prefix = 'BKM-' . $dt->format('Ym') . '-';

        $last = DatKasMasukHdr::where('bkm_no', 'LIKE', "{$prefix}%")
            ->orderBy('bkm_no', 'desc')
            ->value('bkm_no');

        if ($last) {
            $lastNumber = (int) substr($last, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    /**
     * Dapatkan daftar transaksi BKM dengan paginasi dan filter
     */
    public function getKasMasukListPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatKasMasukHdr::with(['kasBank.coa', 'customer', 'details', 'jurnal', 'coa'])
            ->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('bkm_no', 'ILIKE', "%{$search}%")
                  ->orWhere('penyetor_nama', 'ILIKE', "%{$search}%")
                  ->orWhere('no_referensi_bank', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan', 'ILIKE', "%{$search}%")
                  ->orWhereHas('details', function ($qd) use ($search) {
                      $qd->where('uraian', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
            $query->where('status_cd', $filters['status']);
        }

        if (!empty($filters['kas_bank_id'])) {
            $query->where('kas_bank_id', $filters['kas_bank_id']);
        }

        if (!empty($filters['kategori'])) {
            $query->where('kategori_penerimaan', $filters['kategori']);
        }

        if (!empty($filters['metode_terima'])) {
            $query->where('metode_terima', $filters['metode_terima']);
        }

        if (!empty($filters['tgl_mulai'])) {
            $query->whereDate('tgl_terima', '>=', $filters['tgl_mulai']);
        }

        if (!empty($filters['tgl_selesai'])) {
            $query->whereDate('tgl_terima', '<=', $filters['tgl_selesai']);
        }

        return $query->orderBy('tgl_terima', 'desc')
                     ->orderBy('bkm_id', 'desc')
                     ->paginate($perPage)
                     ->withQueryString();
    }

    /**
     * Hitung jumlah status dokumen BKM untuk Segmented Tab Filter
     */
    public function getStatusCounts(): array
    {
        $base = DatKasMasukHdr::where('deleted_st', false);

        return [
            'all'    => (clone $base)->count(),
            'draft'  => (clone $base)->where('status_cd', 'DRAFT')->count(),
            'posted' => (clone $base)->where('status_cd', 'POSTED')->count(),
            'void'   => (clone $base)->where('status_cd', 'VOID')->count(),
        ];
    }

    /**
     * Metrik KPI Penerimaan Kas & Bank (Fokus Operasional Kasir Harian)
     */
    public function getKpiMetrics(): array
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $base = DatKasMasukHdr::where('deleted_st', false);

        // 1. Kas Masuk Hari Ini (non-VOID)
        $hariIniBase = (clone $base)->where('status_cd', '!=', 'VOID')->whereDate('tgl_terima', $today);
        $totalHariIni = (float) (clone $hariIniBase)->sum('total_terima');
        $countHariIni = (int) (clone $hariIniBase)->count();

        // 2. Kas Masuk Bulan Ini (non-VOID)
        $bulanIniBase = (clone $base)->where('status_cd', '!=', 'VOID')->whereBetween('tgl_terima', [$startOfMonth, $endOfMonth]);
        $totalBulanIni = (float) (clone $bulanIniBase)->sum('total_terima');
        $countBulanIni = (int) (clone $bulanIniBase)->count();

        // 3. Menunggu Verifikasi Akuntan (DRAFT)
        $draftBase = (clone $base)->where('status_cd', 'DRAFT');
        $countDraft = (int) (clone $draftBase)->count();
        $totalDraft = (float) (clone $draftBase)->sum('total_terima');

        // 4. Total Transaksi Terverifikasi (POSTED) & Rekening Kas/Bank Terlibat
        $countPosted = (int) (clone $base)->where('status_cd', 'POSTED')->count();
        $totalPosted = (float) (clone $base)->where('status_cd', 'POSTED')->sum('total_terima');
        $totalRekeningAktif = (int) MstKasBank::where('deleted_st', false)->where('active_st', true)->count();

        return [
            'total_hari_ini'       => $totalHariIni,
            'count_hari_ini'       => $countHariIni,
            'total_bulan_ini'      => $totalBulanIni,
            'count_bulan_ini'      => $countBulanIni,
            'count_draft'          => $countDraft,
            'total_draft'          => $totalDraft,
            'count_posted'         => $countPosted,
            'total_posted'         => $totalPosted,
            'total_rekening_aktif' => $totalRekeningAktif,
        ];
    }

    /**
     * Dapatkan detail BKM berdasarkan ID
     */
    public function getById(int $id): DatKasMasukHdr
    {
        return DatKasMasukHdr::with(['kasBank.coa', 'customer', 'details', 'jurnal.details.coa', 'coa'])
            ->where('deleted_st', false)
            ->findOrFail($id);
    }

    /**
     * Simpan Transaksi Bukti Kas Masuk (BKM) - Tahap 1: Kasir
     * - Status dokumen = 'DRAFT', jurnal_id = null
     * - Saldo kas/bank fisik bertambah dan mutasi kas (DatKasMutasi) tercatat
     */
    public function storeKasMasuk(array $payload, User $user): DatKasMasukHdr
    {
        return DB::transaction(function () use ($payload, $user) {
            $kasBankId = (int) $payload['kas_bank_id'];
            $tglTerima = $payload['tgl_terima'] ?? now()->toDateString();
            $kategori  = $payload['kategori_penerimaan'] ?? 'PENDAPATAN_LAIN';
            $items     = $payload['items'] ?? [];

            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 baris rincian penerimaan kas.");
            }

            // Hitung total terima dari rincian
            $totalTerima = 0;
            foreach ($items as $item) {
                $nom = (float) ($item['nominal'] ?? 0);
                if ($nom <= 0) {
                    throw new Exception("Nominal pada setiap rincian harus lebih besar dari Rp 0.");
                }
                $totalTerima += $nom;
            }

            if ($totalTerima <= 0) {
                throw new Exception("Total nominal penerimaan harus lebih besar dari Rp 0.");
            }

            // Lock rekening bank tujuan
            $kasBank = MstKasBank::where('deleted_st', false)
                ->where('active_st', true)
                ->lockForUpdate()
                ->findOrFail($kasBankId);

            $bkmNo = !empty($payload['bkm_no']) ? trim($payload['bkm_no']) : $this->generateBkmNo($tglTerima);

            // 1. Buat Header BKM dengan status DRAFT (Menunggu Verifikasi Jurnal oleh Akuntansi)
            $bkm = DatKasMasukHdr::create([
                'bkm_no'              => $bkmNo,
                'tgl_terima'          => $tglTerima,
                'kas_bank_id'         => $kasBank->kas_bank_id,
                'coa_id'              => null,
                'kategori_penerimaan' => $kategori,
                'customer_id'         => !empty($payload['customer_id']) ? $payload['customer_id'] : null,
                'penyetor_nama'       => !empty($payload['penyetor_nama']) ? trim($payload['penyetor_nama']) : null,
                'metode_terima'       => $payload['metode_terima'] ?? 'TRANSFER',
                'no_referensi_bank'   => !empty($payload['no_referensi_bank']) ? trim($payload['no_referensi_bank']) : null,
                'total_terima'        => $totalTerima,
                'catatan'             => !empty($payload['catatan']) ? trim($payload['catatan']) : null,
                'status_cd'           => 'DRAFT',
                'jurnal_id'           => null,
                'created_by'          => $user->name ?? 'SYSTEM',
                'updated_by'          => $user->name ?? 'SYSTEM',
            ]);

            // 2. Buat Rincian Item BKM
            foreach ($items as $item) {
                DatKasMasukDtl::create([
                    'bkm_id'       => $bkm->bkm_id,
                    'so_id'        => !empty($item['so_id']) ? $item['so_id'] : null,
                    'uraian'       => trim($item['uraian']),
                    'qty'          => (float) ($item['qty'] ?? 1),
                    'satuan'       => !empty($item['satuan']) ? trim($item['satuan']) : null,
                    'harga_satuan' => (float) ($item['harga_satuan'] ?? 0),
                    'nominal'      => (float) $item['nominal'],
                    'catatan'      => !empty($item['catatan']) ? trim($item['catatan']) : null,
                    'created_by'   => $user->name ?? 'SYSTEM',
                    'updated_by'   => $user->name ?? 'SYSTEM',
                ]);
            }

            // 3. Tambah Saldo Berjalan Rekening Kas & Bank (Penerimaan Fisik Langsung Masuk)
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum + $totalTerima;

            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            // 4. Catat Buku Mutasi Kas & Bank Masuk
            $namaAkun = $kasBank->nama_akun ?? 'Kas/Bank';
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => $tglTerima,
                'tipe_mutasi'     => 'MASUK',
                'nominal'         => $totalTerima,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'PENERIMAAN_KAS',
                'ref_no'          => $bkm->bkm_no,
                'ref_id'          => $bkm->bkm_id,
                'keterangan'      => "Penerimaan Kas/Bank ({$bkm->bkm_no}) - " . ($payload['penyetor_nama'] ?? $kategori) . " via {$namaAkun}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            return $bkm;
        });
    }

    /**
     * Verifikasi Penerimaan Kas (BKM) oleh Akuntansi & Terbitkan Jurnal Umum (Tahap 2)
     * - Double-Entry GL:
     *   Debet : Akun Kas/Bank Tujuan
     *   Kredit: Akun COA Lawan terpilih (Pendapatan Limbah 4200/6114, Penjualan Tunai 4100, Modal 3100, dll)
     * - Sinkronisasi Keterangan 2 Akun Jurnal (Debet & Kredit menggunakan keterangan identik)
     * - Total Debet == Total Kredit = total_terima (Keseimbangan / Selisih Rp 0)
     * - Update status_cd = 'POSTED', simpan jurnal_id dan coa_id
     */
    public function verifikasiDanTerbitkanJurnal(
        int $bkmId,
        int $coaId,
        ?string $tglJurnal = null,
        ?User $user = null,
        ?string $keteranganJurnal = null
    ): DatKasMasukHdr {
        return DB::transaction(function () use ($bkmId, $coaId, $tglJurnal, $user, $keteranganJurnal) {
            $bkm = DatKasMasukHdr::with(['kasBank', 'details'])
                ->where('deleted_st', false)
                ->lockForUpdate()
                ->findOrFail($bkmId);

            if ($bkm->status_cd === 'VOID') {
                throw new Exception("BKM {$bkm->bkm_no} sudah berstatus VOID dan tidak dapat diverifikasi.");
            }

            if ($bkm->status_cd === 'POSTED' && !empty($bkm->jurnal_id)) {
                throw new Exception("BKM {$bkm->bkm_no} sudah diverifikasi dan Jurnal Umum telah terbit.");
            }

            $coaLawan = MstCoa::where('deleted_st', false)->findOrFail($coaId);
            $kasBank = $bkm->kasBank;

            // Cari COA Debet untuk Kas/Bank tujuan
            $kasBankCoaId = $kasBank?->coa_id;
            if (!$kasBankCoaId && $kasBank) {
                $kasCoa = MstCoa::where('kode_akun', $kasBank->kode_akun)->first()
                    ?? MstCoa::where('kode_akun', '1100')->first();
                $kasBankCoaId = $kasCoa?->coa_id;
            }

            if (!$kasBankCoaId) {
                throw new Exception("Akun COA untuk rekening kas/bank '{$kasBank?->nama_akun}' belum terpetakan di Master Data.");
            }

            $tanggalPosting = $tglJurnal ?: $bkm->tgl_terima->toDateString();
            $penyetor = $bkm->penyetor_nama ?: 'Penyetor';

            // Sinkronisasi Keterangan Jurnal (Debet & Kredit wajib identik)
            $keteranganFinal = trim($keteranganJurnal ?? '');
            if (empty($keteranganFinal)) {
                $uraianUtama = $bkm->details->first()?->uraian ?: 'Penerimaan Kas/Bank';
                $keteranganFinal = "BKM {$bkm->bkm_no} - {$uraianUtama} ({$penyetor})";
            }

            $nominal = (float) $bkm->total_terima;

            // Susun baris Jurnal Umum (Debet: Kas/Bank, Kredit: Akun Lawan)
            // Keseimbangan terjamin: total Debet == total Kredit = $nominal (Selisih 0)
            $entries = [
                [
                    'coa_id'     => $kasBankCoaId,
                    'debet'      => $nominal,
                    'kredit'     => 0,
                    'keterangan' => $keteranganFinal,
                ],
                [
                    'coa_id'     => $coaLawan->coa_id,
                    'debet'      => 0,
                    'kredit'     => $nominal,
                    'keterangan' => $keteranganFinal,
                ],
            ];

            $jurnal = $this->jurnalService->catatJurnal(
                'KAS_MASUK',
                $tanggalPosting,
                $bkm->bkm_no,
                $bkm->bkm_id,
                $keteranganFinal,
                $entries,
                $user
            );

            // Update header BKM dengan status POSTED dan link jurnal
            $bkm->update([
                'coa_id'     => $coaLawan->coa_id,
                'jurnal_id'  => $jurnal->jurnal_id,
                'status_cd'  => 'POSTED',
                'updated_by' => $user->name ?? 'SYSTEM',
            ]);

            return $bkm;
        });
    }

    /**
     * Batalkan / Void Transaksi Bukti Kas Masuk (BKM)
     * - Jika DRAFT: Balikkan saldo kas/bank dan mutasi kas
     * - Jika POSTED: Balikkan saldo kas, mutasi kas, dan batalkan Jurnal GL terkait
     */
    public function voidKasMasuk(int $bkmId, string $alasan, User $user): DatKasMasukHdr
    {
        return DB::transaction(function () use ($bkmId, $alasan, $user) {
            $bkm = DatKasMasukHdr::lockForUpdate()->findOrFail($bkmId);

            if ($bkm->status_cd === 'VOID') {
                throw new Exception("Dokumen Kas Masuk {$bkm->bkm_no} sudah berstatus VOID.");
            }

            $kasBank = MstKasBank::lockForUpdate()->findOrFail($bkm->kas_bank_id);

            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum - (float) $bkm->total_terima;

            // 1. Kurangi kembali saldo kas/bank
            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            // 2. Catat pembalik mutasi kas (KELUAR)
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => now()->toDateString(),
                'tipe_mutasi'     => 'KELUAR',
                'nominal'         => (float) $bkm->total_terima,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'VOID_KAS_MASUK',
                'ref_no'          => 'VOID-' . $bkm->bkm_no,
                'ref_id'          => $bkm->bkm_id,
                'keterangan'      => "Pembatalan (VOID) Bukti Kas Masuk {$bkm->bkm_no}: {$alasan}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            // 3. Batalkan Jurnal GL jika status sebelumnya POSTED atau ada jurnal_id
            if (!empty($bkm->jurnal_id) || $bkm->status_cd === 'POSTED') {
                $this->jurnalService->batalkanJurnal($bkm->bkm_no, $user);
            }

            // 4. Ubah status BKM jadi VOID beserta audit trail void
            $bkm->update([
                'status_cd'   => 'VOID',
                'alasan_void' => trim($alasan),
                'void_by'     => $user->name ?? 'SYSTEM',
                'void_dt'     => now(),
                'updated_by'  => $user->name ?? 'SYSTEM',
            ]);

            return $bkm;
        });
    }
}
