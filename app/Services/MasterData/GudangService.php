<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstGudang;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GudangService
{
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstGudang::active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('gudang_cd', 'ILIKE', "%{$search}%")
                  ->orWhere('gudang_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('tipe_gudang_cd', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('gudang_id', 'asc')->paginate($perPage);
    }

    public function getById(int $id): MstGudang
    {
        return MstGudang::where('gudang_id', $id)->firstOrFail();
    }

    public function store(array $data): MstGudang
    {
        return DB::transaction(function () use ($data) {
            return MstGudang::create($data);
        });
    }

    public function update(int $id, array $data): MstGudang
    {
        return DB::transaction(function () use ($id, $data) {
            $gudang = MstGudang::findOrFail($id);
            $gudang->update($data);
            return $gudang->fresh();
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $gudang = MstGudang::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $gudang->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
