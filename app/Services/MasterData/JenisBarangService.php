<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstJenisBarang;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JenisBarangService
{
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstJenisBarang::active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('jenis_barang_cd', 'ILIKE', "%{$search}%")
                  ->orWhere('jenis_barang_nm', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('jenis_barang_id', 'asc')->paginate($perPage);
    }

    public function getById(int $id): MstJenisBarang
    {
        return MstJenisBarang::where('jenis_barang_id', $id)->firstOrFail();
    }

    public function store(array $data): MstJenisBarang
    {
        return DB::transaction(function () use ($data) {
            return MstJenisBarang::create($data);
        });
    }

    public function update(int $id, array $data): MstJenisBarang
    {
        return DB::transaction(function () use ($id, $data) {
            $jenis = MstJenisBarang::findOrFail($id);
            $jenis->update($data);
            return $jenis->fresh();
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $jenis = MstJenisBarang::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $jenis->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
