<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatPoDtl;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PoService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar bahan baku & bahan penolong yang stok fisiknya di bawah batas minimum (Safety Stock).
     * Digunakan untuk memicu alert atau rekomendasi pembuatan PO otomatis.
     */
    public function getBarangBelowMinimum(?int $gudangId = null): Collection
    {
        $barangList = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->bahanBaku()
            ->active()
            ->where('batas_minimum_qty', '>', 0)
            ->get();

        $stokQuery = DatStokBatch::query()->where('sisa_qty', '>', 0);
        if ($gudangId) {
            $stokQuery->where('gudang_id', $gudangId);
        }

        $stockPerBarang = $stokQuery->groupBy('barang_id')
            ->selectRaw('barang_id, SUM(sisa_qty) as total_sisa')
            ->pluck('total_sisa', 'barang_id');

        return $barangList->filter(function ($item) use ($stockPerBarang) {
            $currentStock = (float) ($stockPerBarang[$item->barang_id] ?? 0);
            $item->current_stock = $currentStock;
            $item->selisih_kurang = max(0, (float) $item->batas_minimum_qty - $currentStock);
            return $currentStock < (float) $item->batas_minimum_qty;
        })->values();
    }

    /**
     * Mengambil daftar PO dengan pagination dan filter pencarian.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        $query = DatPoHdr::with(['supplier', 'gudang', 'details.barang'])
            ->where('deleted_st', false);

        if (!empty($status)) {
            $query->where('status_cd', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('po_no', 'ILIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('supplier_nm', 'ILIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('po_tgl', 'desc')
            ->orderBy('po_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mengambil detail satu dokumen PO berdasarkan ID.
     */
    public function getById(int $id): DatPoHdr
    {
        return DatPoHdr::with([
            'supplier',
            'gudang',
            'details.barang.satuanDasar',
            'details.barang.satuanBesar',
            'penerimaan.details'
        ])->where('po_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan transaksi pembuatan PO baru beserta rincian itemnya via DB Transaction.
     */
    public function store(array $data): DatPoHdr
    {
        return DB::transaction(function () use ($data) {
            $poNo = !empty($data['po_no']) ? trim($data['po_no']) : $this->codeGenerator->generatePoNo();

            // Hitung total nominal dari item-item PO
            $totalNominal = 0;
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang dalam Purchase Order.");
            }

            foreach ($items as $item) {
                $qty = (float) ($item['pesan_qty'] ?? 0);
                $harga = (float) ($item['harga_nominal'] ?? 0);
                $totalNominal += ($qty * $harga);
            }

            $header = DatPoHdr::create([
                'po_no'         => $poNo,
                'po_tgl'        => $data['po_tgl'] ?? date('Y-m-d'),
                'supplier_id'   => $data['supplier_id'],
                'gudang_id'     => $data['gudang_id'],
                'status_cd'     => $data['status_cd'] ?? 'APPROVED', // Langsung siap diterima
                'total_nominal' => $totalNominal,
                'catatan_txt'   => $data['catatan_txt'] ?? null,
            ]);

            foreach ($items as $item) {
                $qty = (float) ($item['pesan_qty'] ?? 0);
                $harga = (float) ($item['harga_nominal'] ?? 0);
                $subtotal = $qty * $harga;

                DatPoDtl::create([
                    'po_id'            => $header->po_id,
                    'barang_id'        => $item['barang_id'],
                    'pesan_qty'        => $qty,
                    'harga_nominal'    => $harga,
                    'subtotal_nominal' => $subtotal,
                    'terima_qty'       => 0,
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ]);

                // Update harga beli acuan di Master Barang dengan harga terbaru
                if ($harga > 0) {
                    $barang = MstBarang::find($item['barang_id']);
                    if ($barang && (float) $barang->harga_beli_standar != $harga) {
                        $barang->harga_beli_standar = $harga;
                        $barang->save();
                    }
                }
            }

            return $header->fresh(['details.barang']);
        });
    }

    /**
     * Memperbarui dokumen PO jika status masih DRAFT atau APPROVED (belum ada penerimaan fisik).
     */
    public function update(int $id, array $data): DatPoHdr
    {
        return DB::transaction(function () use ($id, $data) {
            $po = DatPoHdr::with('details')->findOrFail($id);

            // Cek apakah sudah ada barang yang diterima
            $sudahAdaPenerimaan = $po->details->contains(fn($dtl) => (float) $dtl->terima_qty > 0);
            if ($sudahAdaPenerimaan) {
                throw new Exception("PO tidak dapat diedit karena sebagian atau seluruh barang sudah diterima di gudang.");
            }

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang dalam Purchase Order.");
            }

            $totalNominal = 0;
            foreach ($items as $item) {
                $qty = (float) ($item['pesan_qty'] ?? 0);
                $harga = (float) ($item['harga_nominal'] ?? 0);
                $totalNominal += ($qty * $harga);
            }

            $po->update([
                'po_tgl'        => $data['po_tgl'] ?? $po->po_tgl,
                'supplier_id'   => $data['supplier_id'] ?? $po->supplier_id,
                'gudang_id'     => $data['gudang_id'] ?? $po->gudang_id,
                'total_nominal' => $totalNominal,
                'catatan_txt'   => $data['catatan_txt'] ?? $po->catatan_txt,
            ]);

            // Hapus detail lama dan ganti dengan yang baru
            DatPoDtl::where('po_id', $po->po_id)->delete();

            foreach ($items as $item) {
                $qty = (float) ($item['pesan_qty'] ?? 0);
                $harga = (float) ($item['harga_nominal'] ?? 0);
                $subtotal = $qty * $harga;

                DatPoDtl::create([
                    'po_id'            => $po->po_id,
                    'barang_id'        => $item['barang_id'],
                    'pesan_qty'        => $qty,
                    'harga_nominal'    => $harga,
                    'subtotal_nominal' => $subtotal,
                    'terima_qty'       => 0,
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ]);
            }

            return $po->fresh(['details.barang']);
        });
    }

    /**
     * Membatalkan PO jika belum ada penerimaan barang.
     */
    public function cancel(int $id, ?string $reason = null): DatPoHdr
    {
        return DB::transaction(function () use ($id, $reason) {
            $po = DatPoHdr::with('details')->findOrFail($id);

            $sudahAdaPenerimaan = $po->details->contains(fn($dtl) => (float) $dtl->terima_qty > 0);
            if ($sudahAdaPenerimaan) {
                throw new Exception("PO tidak dapat dibatalkan karena sudah ada barang yang diterima.");
            }

            $po->update([
                'status_cd'   => 'CANCELLED',
                'catatan_txt' => trim(($po->catatan_txt ? $po->catatan_txt . "\n" : "") . "Dibatalkan: " . $reason),
            ]);

            return $po;
        });
    }

    /**
     * Mengambil daftar PO yang statusnya aktif (APPROVED atau PARTIAL) untuk dropdown Penerimaan Barang.
     */
    public function getOpenPoList(?int $supplierId = null): Collection
    {
        $query = DatPoHdr::with(['supplier', 'gudang', 'details.barang.satuanDasar'])
            ->whereIn('status_cd', ['APPROVED', 'PARTIAL'])
            ->where('deleted_st', false);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->orderBy('po_tgl', 'desc')->get();
    }
}
