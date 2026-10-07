<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatStokBatch;
use App\Models\Gudang\DatStokLedger;
use App\Models\Gudang\DatTerimaDtl;
use App\Models\Gudang\DatQcInboundHdr;
use App\Models\Gudang\DatPakaiDtl;
use App\Models\Produksi\DatProduksiDtl;
use App\Models\MasterData\MstBarang;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StokService
{
    /**
     * Menambahkan stok ke gudang per nomor batch & mencatat audit ke kartu stok (Ledger).
     * Harus dipanggil di dalam DB::transaction().
     */
    public function addStock(
        int $gudangId,
        int $barangId,
        string $batchNo,
        float $qty,
        ?string $expiredTgl,
        string $dokumenNo,
        ?string $keterangan = null,
        float $hargaSatuan = 0,
        ?string $gradeCd = 'A'
    ): DatStokBatch {
        if ($qty <= 0) {
            throw new Exception("Kuantitas penambahan stok harus lebih besar dari 0.");
        }

        $gradeCd = !empty($gradeCd) ? strtoupper(trim($gradeCd)) : 'A';

        // Lock baris stok batch untuk mencegah race condition (per gudang + barang + nomor batch + grade)
        $stok = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('batch_no', $batchNo)
            ->where('grade_cd', $gradeCd)
            ->lockForUpdate()
            ->first();

        if (!$stok) {
            $stok = DatStokBatch::create([
                'gudang_id'    => $gudangId,
                'barang_id'    => $barangId,
                'batch_no'     => $batchNo,
                'grade_cd'     => $gradeCd,
                'expired_tgl'  => $expiredTgl,
                'qty_awal'     => $qty,
                'harga_satuan' => $hargaSatuan,
                'sisa_qty'     => $qty,
            ]);
        } else {
            $stok->qty_awal = (float) $stok->qty_awal + $qty;
            $stok->sisa_qty = (float) $stok->sisa_qty + $qty;
            if ($hargaSatuan > 0) {
                $stok->harga_satuan = $hargaSatuan;
            }
            if (!empty($expiredTgl)) {
                $stok->expired_tgl = $expiredTgl;
            }
            $stok->save();
        }

        // Hitung total saldo berjalan barang ini di gudang tersebut
        $totalSaldoAkhir = (float) DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->sum('sisa_qty');

        // Catat ke kartu stok mutasi (Ledger)
        DatStokLedger::create([
            'gudang_id'         => $gudangId,
            'barang_id'         => $barangId,
            'batch_no'          => $batchNo,
            'grade_cd'          => $gradeCd,
            'transaksi_tgl'     => now(),
            'dokumen_no'        => $dokumenNo,
            'tipe_transaksi_cd' => 'IN',
            'qty'               => $qty,
            'saldoakhir_qty'    => $totalSaldoAkhir,
            'keterangan_txt'    => $keterangan ?? "Penerimaan Dokumen {$dokumenNo}",
        ]);

        return $stok;
    }

    /**
     * Mengurangi ketersediaan stok dari nomor batch tertentu & mencatat audit ke kartu stok (Ledger).
     * Harus dipanggil di dalam DB::transaction().
     */
    public function deductStock(
        int $gudangId,
        int $barangId,
        string $batchNo,
        float $qty,
        string $dokumenNo,
        ?string $keterangan = null,
        ?string $gradeCd = null
    ): DatStokBatch {
        if ($qty <= 0) {
            throw new Exception("Kuantitas pengurangan stok harus lebih besar dari 0.");
        }

        $query = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('batch_no', $batchNo);

        if (!empty($gradeCd)) {
            $query->where('grade_cd', strtoupper(trim($gradeCd)));
        }

        $stok = $query->lockForUpdate()->first();

        $currentQty = $stok ? (float) $stok->sisa_qty : 0;
        if (!$stok || $currentQty < $qty) {
            $gradeInfo = !empty($gradeCd) ? " (Grade {$gradeCd})" : "";
            throw new Exception("Stok tidak mencukupi untuk Batch '{$batchNo}'{$gradeInfo}. Tersedia: {$currentQty}, Dibutuhkan: {$qty}");
        }

        $stok->sisa_qty = $currentQty - $qty;
        $stok->save();

        // Hitung saldo akhir berjalan
        $totalSaldoAkhir = (float) DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->sum('sisa_qty');

        // Catat ke kartu stok mutasi (Ledger)
        DatStokLedger::create([
            'gudang_id'         => $gudangId,
            'barang_id'         => $barangId,
            'batch_no'          => $batchNo,
            'grade_cd'          => $stok->grade_cd ?? 'A',
            'transaksi_tgl'     => now(),
            'dokumen_no'        => $dokumenNo,
            'tipe_transaksi_cd' => 'OUT',
            'qty'               => $qty,
            'saldoakhir_qty'    => $totalSaldoAkhir,
            'keterangan_txt'    => $keterangan ?? "Pengeluaran Dokumen {$dokumenNo}",
        ]);

        return $stok;
    }

    /**
     * Mengurangi stok secara otomatis menggunakan urutan FIFO / FEFO (First Expired / First In).
     * Mengembalikan rincian alokasi pemotongan batch.
     */
    public function deductStockFifo(
        int $gudangId,
        int $barangId,
        float $totalQty,
        string $dokumenNo,
        ?string $keterangan = null,
        ?string $gradeCd = null
    ): array {
        if ($totalQty <= 0) {
            throw new Exception("Kuantitas pemotongan stok harus lebih besar dari 0.");
        }

        $availableBatches = $this->getAvailableBatches($gudangId, $barangId, $gradeCd);
        $totalAvailable = $availableBatches->sum('sisa_qty');

        if ($totalAvailable < $totalQty) {
            $gradeText = !empty($gradeCd) ? " (Grade {$gradeCd})" : "";
            throw new Exception("Total stok{$gradeText} tidak mencukupi. Tersedia: {$totalAvailable}, Dibutuhkan: {$totalQty}");
        }

        $remainingToDeduct = $totalQty;
        $deductions = [];

        foreach ($availableBatches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $currentBatchQty = (float) $batch->sisa_qty;
            $qtyToTake = min($remainingToDeduct, $currentBatchQty);

            $this->deductStock($gudangId, $barangId, $batch->batch_no, $qtyToTake, $dokumenNo, $keterangan, $batch->grade_cd);

            $remainingToDeduct -= $qtyToTake;
            $deductions[] = [
                'batch_no'     => $batch->batch_no,
                'grade_cd'     => $batch->grade_cd,
                'qty_deducted' => $qtyToTake,
                'sisa_batch'   => $currentBatchQty - $qtyToTake,
            ];
        }

        return $deductions;
    }

    /**
     * Mengambil daftar batch barang yang masih memiliki sisa stok (untuk alur FIFO).
     */
    public function getAvailableBatches(int $gudangId, int $barangId, ?string $gradeCd = null): Collection
    {
        $query = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('sisa_qty', '>', 0);

        if (!empty($gradeCd)) {
            $query->where('grade_cd', strtoupper(trim($gradeCd)));
        }

        return $query->orderByRaw('expired_tgl ASC NULLS LAST')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Helper untuk memfilter query berdasarkan satu gudang atau kumpulan gudang (multi-warehouse)
     */
    protected function applyGudangFilter($query, int|array|null $gudangId, string $column = 'gudang_id'): void
    {
        if (is_array($gudangId)) {
            $query->whereIn($column, $gudangId);
        } elseif ($gudangId !== null) {
            $query->where($column, $gudangId);
        }
    }

    /**
     * Helper untuk memfilter barang berdasarkan scope:
     * - 'bahan': Khusus Bahan Baku & Penolong (RAW, BB, BP, PACK, SUPP)
     * - 'produksi': Khusus Hasil Produksi (WIP, FG)
     */
    public function applyScopeFilter($query, ?string $scope): void
    {
        if ($scope === 'bahan') {
            $query->whereHas('jenisBarang', function ($jq) {
                $jq->whereNotIn('jenis_barang_cd', ['WIP', 'FG']);
            });
        } elseif ($scope === 'produksi') {
            $query->whereHas('jenisBarang', function ($jq) {
                $jq->whereIn('jenis_barang_cd', ['WIP', 'FG']);
            });
        }
    }

    /**
     * Helper untuk memfilter batch / ledger berdasarkan scope barang (bahan vs produksi)
     */
    public function applyBatchScopeFilter($query, ?string $scope): void
    {
        if ($scope === 'bahan') {
            $query->whereHas('barang.jenisBarang', function ($jq) {
                $jq->whereNotIn('jenis_barang_cd', ['WIP', 'FG']);
            });
        } elseif ($scope === 'produksi') {
            $query->whereHas('barang.jenisBarang', function ($jq) {
                $jq->whereIn('jenis_barang_cd', ['WIP', 'FG']);
            });
        }
    }

    /**
     * Mengambil total stok suatu barang di seluruh gudang atau satu/kumpulan gudang tertentu.
     */
    public function getTotalStock(int $barangId, int|array|null $gudangId = null): float
    {
        $query = DatStokBatch::where('barang_id', $barangId)->where('deleted_st', false);
        $this->applyGudangFilter($query, $gudangId);

        return (float) $query->sum('sisa_qty');
    }

    /**
     * Mengambil ringkasan metrik KPI inventaris gudang (Aset Nilai, Total Batch, Status SKU).
     */
    public function getStokKpiMetrics(int|array|null $gudangId = null, ?string $scope = null): array
    {
        $batchQuery = DatStokBatch::where('deleted_st', false);
        $this->applyGudangFilter($batchQuery, $gudangId);
        $this->applyBatchScopeFilter($batchQuery, $scope);

        $totalNilaiPersediaan = (float) (clone $batchQuery)
            ->where('sisa_qty', '>', 0)
            ->selectRaw('COALESCE(SUM(sisa_qty * harga_satuan), 0) as total')
            ->value('total');

        $totalBatchAktif = (int) (clone $batchQuery)
            ->where('sisa_qty', '>', 0)
            ->count();

        // Hitung status SKU barang
        $sub = (clone $batchQuery)->selectRaw('barang_id, SUM(sisa_qty) as total_sisa')->groupBy('barang_id');

        $barangQuery = MstBarang::active();
        $this->applyScopeFilter($barangQuery, $scope);

        $barangStats = $barangQuery
            ->leftJoinSub($sub, 's', 'mst_barang.barang_id', '=', 's.barang_id')
            ->selectRaw('
                COUNT(*) as total_sku,
                COUNT(CASE WHEN COALESCE(s.total_sisa, 0) > 0 THEN 1 END) as sku_tersedia,
                COUNT(CASE WHEN COALESCE(s.total_sisa, 0) > 0 AND COALESCE(s.total_sisa, 0) <= mst_barang.batas_minimum_qty THEN 1 END) as sku_menipis,
                COUNT(CASE WHEN COALESCE(s.total_sisa, 0) <= 0 THEN 1 END) as sku_habis
            ')
            ->first();

        return [
            'total_nilai'   => $totalNilaiPersediaan,
            'batch_aktif'   => $totalBatchAktif,
            'total_sku'     => (int) ($barangStats->total_sku ?? 0),
            'sku_tersedia'  => (int) ($barangStats->sku_tersedia ?? 0),
            'sku_menipis'   => (int) ($barangStats->sku_menipis ?? 0),
            'sku_habis'     => (int) ($barangStats->sku_habis ?? 0),
        ];
    }

    /**
     * Mengambil ringkasan stok teragregasi per-barang (Level 1) beserta relasi sub-batch FIFO (Level 2).
     */
    public function getStokSummaryByBarang(
        int $perPage = 15,
        int|array|null $gudangId = null,
        ?string $search = null,
        ?string $status = null,
        ?int $jenisBarangId = null,
        ?string $scope = null
    ): LengthAwarePaginator {
        $subquery = DatStokBatch::selectRaw('
            barang_id,
            COALESCE(SUM(qty_awal), 0) as total_qty_awal,
            COALESCE(SUM(sisa_qty), 0) as total_sisa_qty,
            COALESCE(SUM(sisa_qty * harga_satuan), 0) as total_sisa_nilai,
            COUNT(CASE WHEN sisa_qty > 0 THEN 1 END) as active_batch_count,
            COUNT(*) as total_batch_count
        ')
        ->where('deleted_st', false);

        $this->applyGudangFilter($subquery, $gudangId);
        $subquery->groupBy('barang_id');

        $query = MstBarang::active()
            ->leftJoinSub($subquery, 'stok_agg', 'mst_barang.barang_id', '=', 'stok_agg.barang_id')
            ->select(
                'mst_barang.*',
                DB::raw('COALESCE(stok_agg.total_qty_awal, 0) as total_qty_awal'),
                DB::raw('COALESCE(stok_agg.total_sisa_qty, 0) as total_sisa_qty'),
                DB::raw('COALESCE(stok_agg.total_sisa_nilai, 0) as total_sisa_nilai'),
                DB::raw('COALESCE(stok_agg.active_batch_count, 0) as active_batch_count'),
                DB::raw('COALESCE(stok_agg.total_batch_count, 0) as total_batch_count')
            )
            ->with([
                'jenisBarang',
                'satuanDasar',
                'stokBatches' => function ($q) use ($gudangId) {
                    $this->applyGudangFilter($q, $gudangId);
                    $q->with('gudang')
                      ->where('deleted_st', false)
                      ->orderByRaw('CASE WHEN sisa_qty > 0 THEN 1 ELSE 0 END DESC')
                      ->orderByRaw('expired_tgl ASC NULLS LAST')
                      ->orderBy('created_at', 'asc');
                }
            ]);

        $this->applyScopeFilter($query, $scope);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('barang_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('barang_cd', 'ILIKE', "%{$search}%")
                  ->orWhereHas('jenisBarang', function ($jq) use ($search) {
                      $jq->where('jenis_barang_nm', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('stokBatches', function ($sq) use ($search) {
                      $sq->where('batch_no', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($jenisBarangId)) {
            $query->where('mst_barang.jenis_barang_id', $jenisBarangId);
        }

        if ($status === 'tersedia') {
            $query->whereRaw('COALESCE(stok_agg.total_sisa_qty, 0) > 0');
        } elseif ($status === 'menipis') {
            $query->whereRaw('COALESCE(stok_agg.total_sisa_qty, 0) > 0 AND COALESCE(stok_agg.total_sisa_qty, 0) <= mst_barang.batas_minimum_qty');
        } elseif ($status === 'habis') {
            $query->whereRaw('COALESCE(stok_agg.total_sisa_qty, 0) <= 0');
        } elseif ($status === 'aman') {
            $query->whereRaw('COALESCE(stok_agg.total_sisa_qty, 0) > mst_barang.batas_minimum_qty');
        }

        return $query->orderByRaw('CASE WHEN COALESCE(stok_agg.total_sisa_qty, 0) > 0 THEN 1 ELSE 0 END DESC')
            ->orderBy('mst_barang.barang_nm', 'asc')
            ->paginate($perPage);
    }

    /**
     * Mengambil data monitoring stok per batch & gudang untuk tampilan dashboard gudang / Lacak Stok.
     */
    public function getMonitoringStok(
        int $perPage = 15,
        int|array|null $gudangId = null,
        ?string $search = null,
        ?string $status = null,
        ?int $jenisBarangId = null,
        ?string $scope = null
    ): LengthAwarePaginator {
        $query = DatStokBatch::with(['barang.satuanDasar', 'barang.jenisBarang', 'gudang'])
            ->where('deleted_st', false);

        // Filter status stok (Tersedia / Habis / Semua)
        if ($status === 'tersedia') {
            $query->where('sisa_qty', '>', 0);
        } elseif ($status === 'habis') {
            $query->where('sisa_qty', '<=', 0);
        }

        $this->applyGudangFilter($query, $gudangId);
        $this->applyBatchScopeFilter($query, $scope);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($jenisBarangId)) {
            $query->whereHas('barang', function ($bq) use ($jenisBarangId) {
                $bq->where('jenis_barang_id', $jenisBarangId);
            });
        }

        return $query->orderBy('barang_id', 'asc')
            ->orderBy('gudang_id', 'asc')
            ->orderByRaw('expired_tgl ASC NULLS LAST')
            ->paginate($perPage);
    }

    /**
     * Mengambil riwayat kartu stok (Ledger) per barang untuk audit mutasi.
     */
    public function getKartuStok(int $barangId, int|array|null $gudangId = null, ?string $startDate = null, ?string $endDate = null, int $perPage = 25): LengthAwarePaginator
    {
        $query = DatStokLedger::with(['gudang', 'barang.satuanDasar'])
            ->where('barang_id', $barangId);

        $this->applyGudangFilter($query, $gudangId);

        if ($startDate) {
            $query->whereDate('transaksi_tgl', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaksi_tgl', '<=', $endDate);
        }

        return $query->orderBy('transaksi_tgl', 'desc')
            ->orderBy('ledger_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Halaman Rekapitulasi Stok Komoditas & Valuasi Persediaan (Blueprint 10 - Rekap Stok).
     * Mendukung Snapshot Harian (Daily Balance Sheet seperti di Excel PT Mirasa) & Split Tab:
     * - Tab 'hasil_produksi': Stok Gudang Jadi (FG & WIP)
     * - Tab 'bahan': Stok Bahan Baku & Penolong (RAW, BP, PACK, SUPP, BB)
     * Menampilkan: Stok Awal, Masuk (IN), Keluar (OUT), Stok Akhir, Satuan, Harga, Total Nilai, Keterangan, Status.
     */
    public function getRekapStok(
        array $filters = [],
        int $perPage = 50,
        int|array|null $allowedGudangIds = null
    ): LengthAwarePaginator {
        $gudangId = !empty($filters['gudang_id']) ? (int) $filters['gudang_id'] : $allowedGudangIds;
        $tab = $filters['tab'] ?? 'hasil_produksi';
        $tgl = !empty($filters['tanggal']) ? Carbon::parse($filters['tanggal'])->toDateString() : date('Y-m-d');

        // 1. Subquery mutasi hari ini (Tanggal T)
        $subIn = DatStokLedger::selectRaw('barang_id, COALESCE(SUM(qty), 0) as masuk_hari_ini')
            ->where('tipe_transaksi_cd', 'IN')
            ->whereDate('transaksi_tgl', $tgl)
            ->where('deleted_st', false);
        $this->applyGudangFilter($subIn, $gudangId);
        $subIn->groupBy('barang_id');

        $subOut = DatStokLedger::selectRaw('barang_id, COALESCE(SUM(qty), 0) as keluar_hari_ini')
            ->where('tipe_transaksi_cd', 'OUT')
            ->whereDate('transaksi_tgl', $tgl)
            ->where('deleted_st', false);
        $this->applyGudangFilter($subOut, $gudangId);
        $subOut->groupBy('barang_id');

        // Keterangan hari ini (gabungan keterangan transaksi ledger pada tanggal T)
        $subKet = DatStokLedger::selectRaw('barang_id, STRING_AGG(DISTINCT keterangan_txt, \'; \') as keterangan_hari_ini')
            ->whereDate('transaksi_tgl', $tgl)
            ->where('deleted_st', false)
            ->whereNotNull('keterangan_txt')
            ->where('keterangan_txt', '!=', '');
        $this->applyGudangFilter($subKet, $gudangId);
        $subKet->groupBy('barang_id');

        // 2. Subquery Batch saat ini
        $subBatches = DatStokBatch::selectRaw('
                barang_id,
                COALESCE(SUM(qty_awal), 0) as total_batch_awal,
                COALESCE(SUM(sisa_qty), 0) as sisa_sekarang,
                CASE WHEN SUM(sisa_qty) > 0 THEN SUM(sisa_qty * harga_satuan) / SUM(sisa_qty) ELSE AVG(harga_satuan) END as harga_satuan
            ')
            ->where('deleted_st', false);
        $this->applyGudangFilter($subBatches, $gudangId);
        $subBatches->groupBy('barang_id');

        // 3. Subquery mutasi masa depan (setelah tanggal T, untuk rollback jika melihat tanggal lampau)
        $subFutureIn = DatStokLedger::selectRaw('barang_id, COALESCE(SUM(qty), 0) as in_future')
            ->where('tipe_transaksi_cd', 'IN')
            ->whereDate('transaksi_tgl', '>', $tgl)
            ->where('deleted_st', false);
        $this->applyGudangFilter($subFutureIn, $gudangId);
        $subFutureIn->groupBy('barang_id');

        $subFutureOut = DatStokLedger::selectRaw('barang_id, COALESCE(SUM(qty), 0) as out_future')
            ->where('tipe_transaksi_cd', 'OUT')
            ->whereDate('transaksi_tgl', '>', $tgl)
            ->where('deleted_st', false);
        $this->applyGudangFilter($subFutureOut, $gudangId);
        $subFutureOut->groupBy('barang_id');

        $query = MstBarang::active();
        if ($tab === 'hasil_produksi') {
            $query->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['FG', 'WIP']));
        } else {
            $query->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['RAW', 'BP', 'PACK', 'SUPP', 'BB']));
        }

        $query->leftJoinSub($subBatches, 'b', 'mst_barang.barang_id', '=', 'b.barang_id')
            ->leftJoinSub($subIn, 'lin', 'mst_barang.barang_id', '=', 'lin.barang_id')
            ->leftJoinSub($subOut, 'lout', 'mst_barang.barang_id', '=', 'lout.barang_id')
            ->leftJoinSub($subKet, 'lket', 'mst_barang.barang_id', '=', 'lket.barang_id')
            ->leftJoinSub($subFutureIn, 'fin', 'mst_barang.barang_id', '=', 'fin.barang_id')
            ->leftJoinSub($subFutureOut, 'fout', 'mst_barang.barang_id', '=', 'fout.barang_id')
            ->select(
                'mst_barang.*',
                DB::raw('COALESCE(lin.masuk_hari_ini, 0) as total_masuk'),
                DB::raw('COALESCE(lout.keluar_hari_ini, 0) as total_keluar'),
                DB::raw('lket.keterangan_hari_ini as keterangan_txt'),
                DB::raw('COALESCE(b.harga_satuan, mst_barang.harga_beli_standar, 0) as harga_satuan'),
                DB::raw('GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) as stok_akhir'),
                DB::raw('GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0) - COALESCE(lin.masuk_hari_ini, 0) + COALESCE(lout.keluar_hari_ini, 0))) as stok_awal'),
                DB::raw('(GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) * COALESCE(b.harga_satuan, mst_barang.harga_beli_standar, 0)) as nilai_persediaan')
            )
            ->with(['jenisBarang', 'satuanDasar']);

        if (!empty($filters['jenis_cd']) && $filters['jenis_cd'] !== 'all') {
            $jcd = strtoupper($filters['jenis_cd']);
            if ($jcd === 'RAW' || $jcd === 'BB') {
                $query->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['RAW', 'BB']));
            } elseif ($jcd === 'PACK') {
                $query->where(function($q) {
                    $q->whereHas('jenisBarang', fn($jb) => $jb->where('jenis_barang_cd', 'PACK'))
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%KARTON%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%PLASTIK%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%ROLL%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%LAKBAN%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%RAFIA%');
                });
            } elseif ($jcd === 'SUPP') {
                $query->where(function($q) {
                    $q->whereHas('jenisBarang', fn($jb) => $jb->where('jenis_barang_cd', 'SUPP'))
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%BUMBU%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%GARAM%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%MSG%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%PERENYAH%');
                });
            } else {
                $query->whereHas('jenisBarang', fn($q) => $q->where('jenis_barang_cd', $jcd));
            }
        } elseif (!empty($filters['jenis_barang_id'])) {
            $query->where('mst_barang.jenis_barang_id', $filters['jenis_barang_id']);
        }

        // Filter pencarian: Nama Barang / Kode Barang
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('mst_barang.barang_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('mst_barang.barang_cd', 'ILIKE', "%{$search}%");
            });
        }

        // Filter Status
        if (!empty($filters['status'])) {
            $st = strtolower($filters['status']);
            if ($st === 'bergerak') {
                $query->whereRaw('(COALESCE(lin.masuk_hari_ini, 0) > 0 OR COALESCE(lout.keluar_hari_ini, 0) > 0)');
            } elseif ($st === 'aman') {
                $query->whereRaw('GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) > mst_barang.batas_minimum_qty');
            } elseif ($st === 'rendah') {
                $query->whereRaw('GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) > 0 AND GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) <= mst_barang.batas_minimum_qty');
            } elseif ($st === 'habis') {
                $query->whereRaw('GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) <= 0');
            }
        }

        // Order: barang yang bergerak hari ini diprioritaskan di atas, lalu yang punya stok, lalu nama barang
        return $query->orderByRaw('CASE WHEN (COALESCE(lin.masuk_hari_ini, 0) > 0 OR COALESCE(lout.keluar_hari_ini, 0) > 0) THEN 1 ELSE 0 END DESC')
            ->orderByRaw('CASE WHEN GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) > 0 THEN 1 ELSE 0 END DESC')
            ->orderBy('mst_barang.barang_nm', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil ringkasan total KPI Rekap Stok untuk kartu indikator atas berdasarkan tab & tanggal.
     */
    public function getRekapStokTotals(array $filters = [], int|array|null $allowedGudangIds = null): array
    {
        $gudangId = !empty($filters['gudang_id']) ? (int) $filters['gudang_id'] : $allowedGudangIds;
        $tab = $filters['tab'] ?? 'hasil_produksi';
        $tgl = !empty($filters['tanggal']) ? Carbon::parse($filters['tanggal'])->toDateString() : date('Y-m-d');

        // Subquery mutasi hari ini (Tanggal T)
        $subIn = DatStokLedger::selectRaw('barang_id, COALESCE(SUM(qty), 0) as masuk_hari_ini')
            ->where('tipe_transaksi_cd', 'IN')
            ->whereDate('transaksi_tgl', $tgl)
            ->where('deleted_st', false);
        $this->applyGudangFilter($subIn, $gudangId);
        $subIn->groupBy('barang_id');

        $subOut = DatStokLedger::selectRaw('barang_id, COALESCE(SUM(qty), 0) as keluar_hari_ini')
            ->where('tipe_transaksi_cd', 'OUT')
            ->whereDate('transaksi_tgl', $tgl)
            ->where('deleted_st', false);
        $this->applyGudangFilter($subOut, $gudangId);
        $subOut->groupBy('barang_id');

        $subBatches = DatStokBatch::selectRaw('
                barang_id,
                COALESCE(SUM(sisa_qty), 0) as sisa_sekarang,
                CASE WHEN SUM(sisa_qty) > 0 THEN SUM(sisa_qty * harga_satuan) / SUM(sisa_qty) ELSE AVG(harga_satuan) END as harga_satuan
            ')
            ->where('deleted_st', false);
        $this->applyGudangFilter($subBatches, $gudangId);
        $subBatches->groupBy('barang_id');

        $subFutureIn = DatStokLedger::selectRaw('barang_id, COALESCE(SUM(qty), 0) as in_future')
            ->where('tipe_transaksi_cd', 'IN')
            ->whereDate('transaksi_tgl', '>', $tgl)
            ->where('deleted_st', false);
        $this->applyGudangFilter($subFutureIn, $gudangId);
        $subFutureIn->groupBy('barang_id');

        $subFutureOut = DatStokLedger::selectRaw('barang_id, COALESCE(SUM(qty), 0) as out_future')
            ->where('tipe_transaksi_cd', 'OUT')
            ->whereDate('transaksi_tgl', '>', $tgl)
            ->where('deleted_st', false);
        $this->applyGudangFilter($subFutureOut, $gudangId);
        $subFutureOut->groupBy('barang_id');

        $query = MstBarang::active();
        if ($tab === 'hasil_produksi') {
            $query->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['FG', 'WIP']));
        } else {
            $query->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['RAW', 'BP', 'PACK', 'SUPP', 'BB']));
        }

        $query->leftJoinSub($subBatches, 'b', 'mst_barang.barang_id', '=', 'b.barang_id')
            ->leftJoinSub($subIn, 'lin', 'mst_barang.barang_id', '=', 'lin.barang_id')
            ->leftJoinSub($subOut, 'lout', 'mst_barang.barang_id', '=', 'lout.barang_id')
            ->leftJoinSub($subFutureIn, 'fin', 'mst_barang.barang_id', '=', 'fin.barang_id')
            ->leftJoinSub($subFutureOut, 'fout', 'mst_barang.barang_id', '=', 'fout.barang_id');

        if (!empty($filters['jenis_cd']) && $filters['jenis_cd'] !== 'all') {
            $jcd = strtoupper($filters['jenis_cd']);
            if ($jcd === 'RAW' || $jcd === 'BB') {
                $query->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['RAW', 'BB']));
            } elseif ($jcd === 'PACK') {
                $query->where(function($q) {
                    $q->whereHas('jenisBarang', fn($jb) => $jb->where('jenis_barang_cd', 'PACK'))
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%KARTON%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%PLASTIK%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%ROLL%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%LAKBAN%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%RAFIA%');
                });
            } elseif ($jcd === 'SUPP') {
                $query->where(function($q) {
                    $q->whereHas('jenisBarang', fn($jb) => $jb->where('jenis_barang_cd', 'SUPP'))
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%BUMBU%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%GARAM%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%MSG%')
                      ->orWhere('mst_barang.barang_nm', 'ILIKE', '%PERENYAH%');
                });
            } else {
                $query->whereHas('jenisBarang', fn($q) => $q->where('jenis_barang_cd', $jcd));
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('mst_barang.barang_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('mst_barang.barang_cd', 'ILIKE', "%{$search}%");
            });
        }

        $items = $query->select(
            'mst_barang.*',
            DB::raw('COALESCE(lin.masuk_hari_ini, 0) as masuk_hari_ini'),
            DB::raw('COALESCE(lout.keluar_hari_ini, 0) as keluar_hari_ini'),
            DB::raw('COALESCE(b.harga_satuan, mst_barang.harga_beli_standar, 0) as harga_satuan'),
            DB::raw('GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) as stok_akhir'),
            DB::raw('GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0) - COALESCE(lin.masuk_hari_ini, 0) + COALESCE(lout.keluar_hari_ini, 0))) as stok_awal'),
            DB::raw('(GREATEST(0, (COALESCE(b.sisa_sekarang, 0) - COALESCE(fin.in_future, 0) + COALESCE(fout.out_future, 0))) * COALESCE(b.harga_satuan, mst_barang.harga_beli_standar, 0)) as nilai_persediaan')
        )->with(['satuanDasar', 'jenisBarang'])->get();

        $totalSku = $items->count();
        $grandAwal = $items->sum('stok_awal');
        $grandMasuk = $items->sum('masuk_hari_ini');
        $grandKeluar = $items->sum('keluar_hari_ini');
        $grandAkhir = $items->sum('stok_akhir');
        $grandNilai = $items->sum('nilai_persediaan');

        $skuAman = 0;
        $skuRendah = 0;
        $skuHabis = 0;
        $skuBergerak = 0;

        $totalKartonFg = 0;
        $totalBerkoKg = 0;
        $totalSingkongKg = 0;
        $totalMinyakKg = 0;

        foreach ($items as $it) {
            $akhir = (float) $it->stok_akhir;
            $minQty = (float) $it->batas_minimum_qty;
            if ($it->masuk_hari_ini > 0 || $it->keluar_hari_ini > 0) {
                $skuBergerak++;
            }
            if ($akhir <= 0) {
                $skuHabis++;
            } elseif ($akhir <= $minQty) {
                $skuRendah++;
            } else {
                $skuAman++;
            }

            $jcd = $it->jenisBarang?->jenis_barang_cd ?? '';
            $nm = strtoupper($it->barang_nm);
            $satuan = strtoupper($it->satuanDasar?->satuan_cd ?? '');

            if ($jcd === 'FG' || str_contains($satuan, 'KARTON') || str_contains($satuan, 'DUS')) {
                $totalKartonFg += $akhir;
            }
            if (str_contains($nm, 'BERKO') || str_contains($it->barang_cd, 'BRK')) {
                $totalBerkoKg += $akhir;
            }
            if (str_contains($nm, 'SINGKONG') || $jcd === 'RAW') {
                $totalSingkongKg += $akhir;
            }
            if (str_contains($nm, 'MINYAK')) {
                $totalMinyakKg += $akhir;
            }
        }

        return [
            'total_sku'              => $totalSku,
            'grand_stok_awal'        => (float) $grandAwal,
            'grand_masuk'            => (float) $grandMasuk,
            'grand_keluar'           => (float) $grandKeluar,
            'grand_stok_akhir'       => (float) $grandAkhir,
            'grand_nilai_persediaan' => (float) $grandNilai,
            'sku_aman'               => $skuAman,
            'sku_rendah'             => $skuRendah,
            'sku_habis'              => $skuHabis,
            'sku_bergerak_count'     => $skuBergerak,
            'total_karton_fg'        => (float) $totalKartonFg,
            'total_berko_kg'         => (float) $totalBerkoKg,
            'total_singkong_kg'      => (float) $totalSingkongKg,
            'total_minyak_kg'        => (float) $totalMinyakKg,
        ];
    }

    /**
     * Mengambil dossier lengkap penelusuran (genealogy / traceability) untuk sebuah nomor batch:
     * - Data saldo stok saat ini (per gudang & per grade)
     * - Riwayat asal-usul inbound (Penerimaan GRN, PO, Supplier)
     * - Hasil inspeksi kualitas (QC Inbound: kadar air, refraksi, rendemen pati, kondisi truk, afkir)
     * - Asal proses produksi jika merupakan batch WIP / Finished Goods
     * - Riwayat pemakaian / barang keluar (SPK Pemakaian, Masak, Packing)
     * - Log mutasi kartu stok kronologis (Ledger timeline)
     */
    public function getBatchTraceability(string $batchNo, ?int $barangId = null): array
    {
        $batchNo = trim($batchNo);

        // 1. Ambil seluruh data stok batch fisik
        $stokQuery = DatStokBatch::with(['barang.satuanDasar', 'barang.jenisBarang', 'gudang'])
            ->where('batch_no', $batchNo)
            ->where('deleted_st', false);

        if ($barangId) {
            $stokQuery->where('barang_id', $barangId);
        }

        $stokBatches = $stokQuery->get();

        // 2. Identifikasi Master Barang
        $barang = null;
        if ($stokBatches->isNotEmpty()) {
            $barang = $stokBatches->first()->barang;
        } elseif ($barangId) {
            $barang = MstBarang::with(['satuanDasar', 'jenisBarang'])->find($barangId);
        }

        // 3. Kalkulasi Ringkasan Stok Batch
        $totalAwal = (float) $stokBatches->sum('qty_awal');
        $totalSisa = (float) $stokBatches->sum('sisa_qty');
        $totalKeluar = max(0, $totalAwal - $totalSisa);
        $pctTerpakai = $totalAwal > 0 ? round(($totalKeluar / $totalAwal) * 100, 1) : 0;

        $hasExpired = false;
        foreach ($stokBatches as $sb) {
            if ($sb->expired_tgl && Carbon::parse($sb->expired_tgl)->isPast() && (float)$sb->sisa_qty > 0) {
                $hasExpired = true;
                break;
            }
        }

        $statusBatch = 'TERSEDIA';
        if ($hasExpired) {
            $statusBatch = 'EXPIRED';
        } elseif ($totalSisa <= 0) {
            $statusBatch = 'HABIS';
        } elseif ($totalAwal > 0 && ($totalSisa / $totalAwal) <= 0.2) {
            $statusBatch = 'MENIPIS';
        }

        $stokItems = $stokBatches->map(function ($sb) {
            $isExp = $sb->expired_tgl ? Carbon::parse($sb->expired_tgl)->isPast() : false;
            return [
                'stok_id'      => $sb->stok_id,
                'gudang_id'    => $sb->gudang_id,
                'gudang_nm'    => $sb->gudang?->gudang_nm ?? '-',
                'grade_cd'     => $sb->grade_cd ?? 'A',
                'qty_awal'     => (float) $sb->qty_awal,
                'sisa_qty'     => (float) $sb->sisa_qty,
                'qty_keluar'   => max(0, (float) $sb->qty_awal - (float) $sb->sisa_qty),
                'harga_satuan' => (float) $sb->harga_satuan,
                'sisa_nilai'   => (float) $sb->sisa_qty * (float) $sb->harga_satuan,
                'expired_tgl'  => $sb->expired_tgl ? Carbon::parse($sb->expired_tgl)->format('d/m/Y') : null,
                'is_expired'   => $isExp,
                'is_habis'     => (float) $sb->sisa_qty <= 0,
            ];
        })->toArray();

        // 4. Inbound - Penerimaan Barang (GRN) dari Supplier
        $terimaQuery = DatTerimaDtl::with([
            'header.supplier',
            'header.po',
            'header.gudang',
            'header.qcInbound.details',
            'barang.satuanDasar'
        ])
        ->where('batch_no', $batchNo)
        ->where('deleted_st', false);

        if ($barangId) {
            $terimaQuery->where('barang_id', $barangId);
        }

        $terimaRaw = $terimaQuery->get();

        if (!$barang && $terimaRaw->isNotEmpty()) {
            $barang = $terimaRaw->first()->barang;
        }

        $terimaList = $terimaRaw->map(function ($td) {
            $hdr = $td->header;
            return [
                'terimadtl_id'  => $td->terimadtl_id,
                'terima_no'     => $hdr?->terima_no ?? '-',
                'terima_tgl'    => $hdr?->terima_tgl ? Carbon::parse($hdr->terima_tgl)->format('d/m/Y') : '-',
                'supplier_nm'   => $hdr?->supplier?->supplier_nm ?? '-',
                'suratjalan_no' => $hdr?->suratjalan_no ?? '-',
                'po_no'         => $hdr?->po?->po_no ?? null,
                'gudang_nm'     => $hdr?->gudang?->gudang_nm ?? '-',
                'terima_qty'    => (float) $td->terima_qty,
                'grade_cd'      => $td->grade_cd ?? 'A',
                'harga_nominal' => (float) $td->harga_nominal,
                'subtotal'      => (float) $td->subtotal_netto ?: ((float) $td->terima_qty * (float) $td->harga_nominal),
                'catatan_txt'   => $td->catatan_txt ?? $hdr?->catatan_txt,
                'qc_id'         => $hdr?->qc_id,
            ];
        })->toArray();

        // 5. Inbound - QC Inspeksi Bahan Masuk
        $qcList = [];
        $qcHdrIds = [];

        // Kumpulkan dari tiket terima yang punya qc_id
        foreach ($terimaRaw as $td) {
            if ($td->header?->qcInbound) {
                $qcHdrIds[] = $td->header->qcInbound->qc_id;
            }
        }

        // Query QC langsung via batch_no
        $directQc = DatQcInboundHdr::with(['supplier', 'details.barang'])
            ->where(function ($q) use ($batchNo) {
                $q->where('batch_no', $batchNo)
                  ->orWhereHas('details', fn($qd) => $qd->where('batch_no', $batchNo));
            })
            ->where('deleted_st', false)
            ->get();

        foreach ($directQc as $dq) {
            $qcHdrIds[] = $dq->qc_id;
        }

        $qcHdrIds = array_unique(array_filter($qcHdrIds));
        if (!empty($qcHdrIds)) {
            $qcRecords = DatQcInboundHdr::with(['supplier', 'details.barang', 'gudang'])
                ->whereIn('qc_id', $qcHdrIds)
                ->where('deleted_st', false)
                ->orderBy('tgl_periksa', 'desc')
                ->get();

            $qcList = $qcRecords->map(function ($qc) {
                $dtlList = $qc->details->map(function ($qd) {
                    return [
                        'barang_nm'         => $qd->barang?->barang_nm ?? '-',
                        'qty_gross'         => (float) $qd->qty_timbang_gross,
                        'kadar_air_persen'  => (float) $qd->kadar_air_persen,
                        'refraksi_persen'   => (float) $qd->refraksi_persen,
                        'qty_refraksi'      => (float) $qd->qty_refraksi,
                        'qty_reject'        => (float) $qd->qty_reject,
                        'qty_netto_lolos'   => (float) $qd->qty_netto_lolos,
                        'grade_cd'          => $qd->grade_cd ?? 'A',
                        'kondisi_fisik'     => $qd->kondisi_fisik ?? '-',
                        'keputusan_qc'      => $qd->keputusan_qc ?? 'PASSED',
                        'catatan_dtl'       => $qd->catatan_dtl,
                    ];
                })->toArray();

                return [
                    'qc_id'                => $qc->qc_id,
                    'qc_no'                => $qc->qc_no,
                    'tgl_periksa'          => $qc->tgl_periksa ? Carbon::parse($qc->tgl_periksa)->format('d/m/Y H:i') : '-',
                    'supplier_nm'          => $qc->supplier?->supplier_nm ?? '-',
                    'surat_jalan_supplier' => $qc->surat_jalan_supplier ?? '-',
                    'plat_nomor_truk'      => $qc->plat_nomor_truk ?? '-',
                    'sopir_nama'           => $qc->sopir_nama ?? '-',
                    'petugas_qc_nama'      => $qc->petugas_qc_nama ?? '-',
                    'posisi_bak'           => $qc->posisi_bak ?? '-',
                    'status_qc'            => $qc->status_qc,
                    'catatan_umum'         => $qc->catatan_umum,
                    'details'              => $dtlList,
                ];
            })->toArray();
        }

        // 6. Asal Produksi (Jika Batch Output WIP / FG)
        $produksiOrigin = null;
        $produksiRaw = DatProduksiDtl::with([
            'header.gudang',
            'header.pemakaianBahan.details.barang',
            'barang.satuanDasar'
        ])
        ->where('batch_no', $batchNo)
        ->where('deleted_st', false)
        ->first();

        if ($produksiRaw) {
            $hdrProd = $produksiRaw->header;
            if (!$barang) {
                $barang = $produksiRaw->barang;
            }

            $rawMaterialsUsed = [];
            if ($hdrProd?->pemakaianBahan && $hdrProd->pemakaianBahan->details) {
                foreach ($hdrProd->pemakaianBahan->details as $pbd) {
                    $rawMaterialsUsed[] = [
                        'barang_nm' => $pbd->barang?->barang_nm ?? '-',
                        'batch_no'  => $pbd->batch_no,
                        'grade_cd'  => $pbd->grade_cd ?? 'A',
                        'qty'       => (float) $pbd->qty_keluar,
                    ];
                }
            }

            $produksiOrigin = [
                'produksi_id'         => $hdrProd?->produksi_id,
                'produksi_no'         => $hdrProd?->produksi_no ?? '-',
                'produksi_tgl'        => $hdrProd?->produksi_tgl ? Carbon::parse($hdrProd->produksi_tgl)->format('d/m/Y') : '-',
                'lini_produksi'       => $hdrProd?->lini_produksi ?? 'PRODUKSI IFM',
                'varietas_singkong'   => $hdrProd?->varietas_singkong ?? '-',
                'rendemen_persen'     => (float) ($hdrProd?->rendemen_persen ?? 0),
                'hpp_satuan'          => (float) $produksiRaw->hpp_satuan,
                'qty_hasil'           => (float) $produksiRaw->qty_hasil,
                'satuan_cd'           => $produksiRaw->satuan_cd ?? 'KG',
                'raw_materials_used'  => $rawMaterialsUsed,
            ];
        }

        // 7. Outbound - Riwayat Pemakaian / Pengeluaran Bahan
        $pakaiQuery = DatPakaiDtl::with([
            'header.gudang',
            'header.produksi'
        ])
        ->where('batch_no', $batchNo)
        ->where('deleted_st', false);

        if ($barangId) {
            $pakaiQuery->where('barang_id', $barangId);
        }

        $pakaiRaw = $pakaiQuery->orderBy('created_at', 'desc')->get();

        $pemakaianList = $pakaiRaw->map(function ($pd) {
            $hdr = $pd->header;
            return [
                'pakaidtl_id'      => $pd->pakaidtl_id,
                'pakai_no'         => $hdr?->pakai_no ?? '-',
                'pakai_tgl'        => $hdr?->pakai_tgl ? Carbon::parse($hdr->pakai_tgl)->format('d/m/Y') : '-',
                'gudang_nm'        => $hdr?->gudang?->gudang_nm ?? '-',
                'tujuan_pemakaian' => $hdr?->tujuan_pemakaian ?? 'PRODUKSI',
                'qty_keluar'       => (float) $pd->qty_keluar,
                'grade_cd'         => $pd->grade_cd ?? 'A',
                'harga_satuan'     => (float) $pd->harga_satuan,
                'total_harga'      => (float) $pd->total_harga,
                'keterangan_txt'   => $pd->keterangan_txt ?? $hdr?->catatan_txt,
                'produksi_no'      => $hdr?->produksi?->produksi_no,
                'lini_produksi'    => $hdr?->produksi?->lini_produksi,
            ];
        })->toArray();

        // 8. Log Kartu Stok Kronologis (Ledger Timeline)
        $ledgerQuery = DatStokLedger::with(['gudang'])
            ->where('batch_no', $batchNo)
            ->where('deleted_st', false);

        if ($barangId) {
            $ledgerQuery->where('barang_id', $barangId);
        }

        $ledgerRaw = $ledgerQuery->orderBy('transaksi_tgl', 'asc')
            ->orderBy('ledger_id', 'asc')
            ->get();

        $ledgerTimeline = $ledgerRaw->map(function ($ld) {
            return [
                'ledger_id'         => $ld->ledger_id,
                'transaksi_tgl'     => $ld->transaksi_tgl ? Carbon::parse($ld->transaksi_tgl)->format('d/m/Y H:i') : '-',
                'dokumen_no'        => $ld->dokumen_no,
                'tipe_transaksi_cd' => $ld->tipe_transaksi_cd,
                'grade_cd'          => $ld->grade_cd ?? 'A',
                'qty'               => (float) $ld->qty,
                'saldoakhir_qty'    => (float) $ld->saldoakhir_qty,
                'gudang_nm'         => $ld->gudang?->gudang_nm ?? '-',
                'keterangan_txt'    => $ld->keterangan_txt,
            ];
        })->toArray();

        // Tipe origin: Apakah dari Supplier atau Hasil Pabrik?
        $originType = $produksiOrigin ? 'MANUFACTURE' : 'PURCHASE';

        return [
            'success'     => true,
            'batch_no'    => $batchNo,
            'origin_type' => $originType,
            'barang'      => [
                'barang_id'  => $barang?->barang_id,
                'barang_cd'  => $barang?->barang_cd ?? '-',
                'barang_nm'  => $barang?->barang_nm ?? 'Komoditas Batch #' . $batchNo,
                'jenis_nm'   => $barang?->jenisBarang?->jenis_barang_nm ?? '-',
                'jenis_cd'   => $barang?->jenisBarang?->jenis_barang_cd ?? '-',
                'satuan_nm'  => $barang?->satuanDasar?->satuan_nm ?? 'Unit',
            ],
            'stok' => [
                'total_qty_awal'   => $totalAwal,
                'total_sisa_qty'   => $totalSisa,
                'total_qty_keluar' => $totalKeluar,
                'pct_terpakai'     => $pctTerpakai,
                'status_batch'     => $statusBatch,
                'items'            => $stokItems,
            ],
            'inbound' => [
                'terima_list' => $terimaList,
                'qc_list'     => $qcList,
                'produksi'    => $produksiOrigin,
            ],
            'outbound' => [
                'pemakaian_list' => $pemakaianList,
            ],
            'ledger' => $ledgerTimeline,
        ];
    }
}

