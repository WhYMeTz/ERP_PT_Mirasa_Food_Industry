<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatStokBatch;
use App\Models\Gudang\DatStokLedger;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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
     * Mengambil total stok suatu barang di seluruh gudang atau satu gudang tertentu.
     */
    public function getTotalStock(int $barangId, ?int $gudangId = null): float
    {
        $query = DatStokBatch::where('barang_id', $barangId);
        if ($gudangId) {
            $query->where('gudang_id', $gudangId);
        }

        return (float) $query->sum('sisa_qty');
    }

    /**
     * Mengambil data monitoring stok per batch & gudang untuk tampilan dashboard gudang / Lacak Stok.
     */
    public function getMonitoringStok(int $perPage = 15, ?int $gudangId = null, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        $query = DatStokBatch::with(['barang.satuanDasar', 'barang.jenisBarang', 'gudang']);

        // Filter status stok (Tersedia / Habis / Semua)
        if ($status === 'tersedia') {
            $query->where('sisa_qty', '>', 0);
        } elseif ($status === 'habis') {
            $query->where('sisa_qty', '<=', 0);
        }

        if ($gudangId) {
            $query->where('gudang_id', $gudangId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  });
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
    public function getKartuStok(int $barangId, ?int $gudangId = null, ?string $startDate = null, ?string $endDate = null, int $perPage = 25): LengthAwarePaginator
    {
        $query = DatStokLedger::with(['gudang', 'barang.satuanDasar'])
            ->where('barang_id', $barangId);

        if ($gudangId) {
            $query->where('gudang_id', $gudangId);
        }

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
}
