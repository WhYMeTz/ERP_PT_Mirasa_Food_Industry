<?php

namespace App\Services\Produksi;

use App\Models\Produksi\MstBomDtl;
use App\Models\Produksi\MstBomHdr;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BomService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar resep BOM terpaginasi
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstBomHdr::with(['barangJadi.satuanDasar', 'details.barangMentah.satuanDasar'])->active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('bom_no', 'ILIKE', "%{$search}%")
                  ->orWhere('bom_nm', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barangJadi', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('bom_id', 'desc')->paginate($perPage);
    }

    /**
     * Mengambil seluruh resep aktif
     */
    public function getAllActive(): Collection
    {
        return MstBomHdr::with('barangJadi')->active()->orderBy('bom_nm')->get();
    }

    /**
     * Mengambil data resep berdasarkan ID
     */
    public function getById(int $id): MstBomHdr
    {
        return MstBomHdr::with(['barangJadi.satuanDasar', 'details.barangMentah.satuanDasar'])->where('bom_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan Formula Resep (BOM) baru beserta rincian bahannya
     */
    public function store(array $data): MstBomHdr
    {
        return DB::transaction(function () use ($data) {
            $bomNo = !empty($data['bom_no']) ? trim($data['bom_no']) : $this->codeGenerator->generate('mst_bom_hdr', 'bom_no', 'BOM-');

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 bahan baku / penolong dalam resep BOM.");
            }

            $header = MstBomHdr::create([
                'bom_no'           => $bomNo,
                'bom_nm'           => $data['bom_nm'],
                'barang_jadi_id'   => $data['barang_jadi_id'],
                'batch_ukuran_qty' => (float) ($data['batch_ukuran_qty'] ?? 1),
                'catatan_txt'      => $data['catatan_txt'] ?? null,
            ]);

            foreach ($items as $item) {
                MstBomDtl::create([
                    'bom_id'           => $header->bom_id,
                    'barang_mentah_id' => $item['barang_mentah_id'],
                    'kebutuhan_qty'    => (float) $item['kebutuhan_qty'],
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ]);
            }

            return $header->fresh(['barangJadi', 'details.barangMentah']);
        });
    }

    /**
     * Memperbarui formula resep BOM
     */
    public function update(int $id, array $data): MstBomHdr
    {
        return DB::transaction(function () use ($id, $data) {
            $header = MstBomHdr::findOrFail($id);

            $header->update([
                'bom_nm'           => $data['bom_nm'],
                'barang_jadi_id'   => $data['barang_jadi_id'],
                'batch_ukuran_qty' => (float) ($data['batch_ukuran_qty'] ?? 1),
                'catatan_txt'      => $data['catatan_txt'] ?? null,
            ]);

            if (isset($data['items'])) {
                $header->details()->delete();
                foreach ($data['items'] as $item) {
                    MstBomDtl::create([
                        'bom_id'           => $header->bom_id,
                        'barang_mentah_id' => $item['barang_mentah_id'],
                        'kebutuhan_qty'    => (float) $item['kebutuhan_qty'],
                        'catatan_txt'      => $item['catatan_txt'] ?? null,
                    ]);
                }
            }

            return $header->fresh(['barangJadi', 'details.barangMentah']);
        });
    }

    /**
     * Menghapus formula resep (Soft Delete)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $bom = MstBomHdr::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $bom->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }

    /**
     * Kalkulasi kebutuhan bahan baku secara proporsional berdasarkan target kuantitas produksi.
     * Formula: (Target Qty / Batch Ukuran Qty) * Kebutuhan Qty per Batch
     */
    public function kalkulasiKebutuhanBahan(int $barangJadiId, float $targetQty, ?int $bomId = null): array
    {
        $query = MstBomHdr::with(['barangJadi.satuanDasar', 'details.barangMentah.satuanDasar'])->active();

        if ($bomId) {
            $bom = $query->where('bom_id', $bomId)->first();
        } else {
            $bom = $query->where('barang_jadi_id', $barangJadiId)->first();
        }

        if (!$bom) {
            throw new Exception("Formula Resep (BOM) untuk produk target tidak ditemukan. Harap buat resep terlebih dahulu.");
        }

        if ((float) $bom->batch_ukuran_qty <= 0) {
            throw new Exception("Ukuran batch standar pada resep {$bom->bom_no} tidak valid (harus > 0).");
        }

        $rasio = $targetQty / (float) $bom->batch_ukuran_qty;
        $bahanList = [];

        foreach ($bom->details as $dtl) {
            $kebutuhanTotal = (float) $dtl->kebutuhan_qty * $rasio;

            $bahanList[] = [
                'barang_id'            => $dtl->barang_mentah_id,
                'barang_cd'            => $dtl->barangMentah?->barang_cd,
                'barang_nm'            => $dtl->barangMentah?->barang_nm,
                'satuan_nm'            => $dtl->barangMentah?->satuanDasar?->satuan_nm,
                'kebutuhan_per_batch'  => (float) $dtl->kebutuhan_qty,
                'kebutuhan_total'      => $kebutuhanTotal,
            ];
        }

        return [
            'bom_id'           => $bom->bom_id,
            'bom_no'           => $bom->bom_no,
            'bom_nm'           => $bom->bom_nm,
            'batch_ukuran_qty' => (float) $bom->batch_ukuran_qty,
            'target_qty'       => $targetQty,
            'rasio_faktor'     => $rasio,
            'materials'        => $bahanList,
        ];
    }
}
