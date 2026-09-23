<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstSupplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstSupplier::active()->with('jenisSupplier');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier_cd', 'ILIKE', "%{$search}%")
                  ->orWhere('supplier_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('kontak_no', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('supplier_id', 'asc')->paginate($perPage);
    }

    public function getById(int $id): MstSupplier
    {
        return MstSupplier::where('supplier_id', $id)->firstOrFail();
    }

    public function store(array $data): MstSupplier
    {
        return DB::transaction(function () use ($data) {
            return MstSupplier::create($data);
        });
    }

    public function update(int $id, array $data): MstSupplier
    {
        return DB::transaction(function () use ($id, $data) {
            $supplier = MstSupplier::findOrFail($id);
            $supplier->update($data);
            return $supplier->fresh();
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $supplier = MstSupplier::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $supplier->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
