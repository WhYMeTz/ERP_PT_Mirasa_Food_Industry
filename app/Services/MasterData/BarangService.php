<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstBarang;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangService
{
    /**
     * Mengambil daftar master barang dengan pagination dan Eager Loading
     * untuk mencegah masalah N+1 Query.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstBarang::with(['jenisBarang', 'satuanDasar', 'satuanBesar'])
            ->active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('barang_cd', 'ILIKE', "%{$search}%")
                  ->orWhere('barang_nm', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('barang_id', 'desc')->paginate($perPage);
    }

    /**
     * Mengambil satu data barang berdasarkan ID.
     */
    public function getById(int $id): MstBarang
    {
        return MstBarang::with(['jenisBarang', 'satuanDasar', 'satuanBesar'])
            ->where('barang_id', $id)
            ->firstOrFail();
    }

    /**
     * Menyimpan data barang baru menggunakan DB Transaction.
     */
    public function store(array $data): MstBarang
    {
        return DB::transaction(function () use ($data) {
            return MstBarang::create($data);
        });
    }

    /**
     * Memperbarui data barang menggunakan DB Transaction.
     */
    public function update(int $id, array $data): MstBarang
    {
        return DB::transaction(function () use ($id, $data) {
            $barang = MstBarang::findOrFail($id);
            $barang->update($data);
            return $barang->fresh(['jenisBarang', 'satuanDasar', 'satuanBesar']);
        });
    }

    /**
     * Melakukan Soft Delete dengan mengubah flag deleted_st menjadi '1'
     * dan mengisi deleted_at serta deleted_by tanpa menghapus data secara permanen.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $barang = MstBarang::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $barang->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
