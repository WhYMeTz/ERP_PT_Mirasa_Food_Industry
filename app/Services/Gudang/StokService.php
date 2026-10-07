<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatStokBatch;
use App\Models\Gudang\DatStokLedger;
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
        float $hargaSatuan = 0
    ): DatStokBatch {
        if ($qty <= 0) {
            throw new Exception("Kuantitas penambahan stok harus lebih besar dari 0.");
        }

        // Lock baris stok batch untuk mencegah race condition
        $stok = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('batch_no', $batchNo)
            ->lockForUpdate()
            ->first();

        if (!$stok) {
            $stok = DatStokBatch::create([
                'gudang_id'    => $gudangId,
                'barang_id'    => $barangId,
                'batch_no'     => $batchNo,
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
        ?string $keterangan = null
    ): DatStokBatch {
        if ($qty <= 0) {
            throw new Exception("Kuantitas pengurangan stok harus lebih besar dari 0.");
        }

        $stok = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('batch_no', $batchNo)
            ->lockForUpdate()
            ->first();

        $currentQty = $stok ? (float) $stok->sisa_qty : 0;
        if (!$stok || $currentQty < $qty) {
            throw new Exception("Stok tidak mencukupi untuk Batch '{$batchNo}'. Tersedia: {$currentQty}, Dibutuhkan: {$qty}");
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
        ?string $keterangan = null
    ): array {
        if ($totalQty <= 0) {
            throw new Exception("Kuantitas pemotongan stok harus lebih besar dari 0.");
        }

        $availableBatches = $this->getAvailableBatches($gudangId, $barangId);
        $totalAvailable = $availableBatches->sum('sisa_qty');

        if ($totalAvailable < $totalQty) {
            throw new Exception("Total stok tidak mencukupi. Tersedia: {$totalAvailable}, Dibutuhkan: {$totalQty}");
        }

        $remainingToDeduct = $totalQty;
        $deductions = [];

        foreach ($availableBatches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $currentBatchQty = (float) $batch->sisa_qty;
            $qtyToTake = min($remainingToDeduct, $currentBatchQty);

            $this->deductStock($gudangId, $barangId, $batch->batch_no, $qtyToTake, $dokumenNo, $keterangan);

            $remainingToDeduct -= $qtyToTake;
            $deductions[] = [
                'batch_no'     => $batch->batch_no,
                'qty_deducted' => $qtyToTake,
                'sisa_batch'   => $currentBatchQty - $qtyToTake,
            ];
        }

        return $deductions;
    }

    /**
     * Mengambil daftar batch barang yang masih memiliki sisa stok (untuk alur FIFO).
     */
    public function getAvailableBatches(int $gudangId, int $barangId): Collection
    {
        return DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('sisa_qty', '>', 0)
            ->orderByRaw('expired_tgl ASC NULLS LAST')
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
     public function getStokKpiMetrics(int|array|null $gudangId = null): array
     {
         $batchQuery = DatStokBatch::where('deleted_st', false);
         $this->applyGudangFilter($batchQuery, $gudangId);

         $totalNilaiPersediaan = (float) (clone $batchQuery)
             ->where('sisa_qty', '>', 0)
             ->selectRaw('COALESCE(SUM(sisa_qty * harga_satuan), 0) as total')
             ->value('total');

         $totalBatchAktif = (int) (clone $batchQuery)
             ->where('sisa_qty', '>', 0)
             ->count();

         // Hitung status SKU barang
         $sub = (clone $batchQuery)->selectRaw('barang_id, SUM(sisa_qty) as total_sisa')->groupBy('barang_id');

         $barangStats = MstBarang::active()
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
        ?int $jenisBarangId = null
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
        ?int $jenisBarangId = null
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
}
