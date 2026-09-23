<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstSatuan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SatuanService
{
    /**
     * Mengambil daftar master satuan dengan pagination.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstSatuan::active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('satuan_cd', 'ILIKE', "%{$search}%")
                  ->orWhere('satuan_nm', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('satuan_id', 'asc')->paginate($perPage);
    }

    /**
     * Mengambil satu data satuan berdasarkan ID.
     */
    public function getById(int $id): MstSatuan
    {
        return MstSatuan::where('satuan_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan data satuan baru via DB Transaction.
     */
    public function store(array $data): MstSatuan
    {
        return DB::transaction(function () use ($data) {
            return MstSatuan::create($data);
        });
    }

    /**
     * Memperbarui data satuan via DB Transaction.
     */
    public function update(int $id, array $data): MstSatuan
    {
        return DB::transaction(function () use ($id, $data) {
            $satuan = MstSatuan::findOrFail($id);
            $satuan->update($data);
            return $satuan->fresh();
        });
    }

    /**
     * Melakukan Soft Delete dengan menandai deleted_st = true.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $satuan = MstSatuan::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $satuan->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
