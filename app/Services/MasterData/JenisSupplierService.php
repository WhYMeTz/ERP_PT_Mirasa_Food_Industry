<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstJenisSupplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JenisSupplierService
{
    /**
     * Mengambil daftar master jenis supplier dengan pagination.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstJenisSupplier::active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('jenis_supplier_cd', 'ILIKE', "%{$search}%")
                  ->orWhere('jenis_supplier_nm', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('jenis_supplier_id', 'asc')->paginate($perPage);
    }

    /**
     * Mengambil semua jenis supplier aktif untuk kebutuhan dropdown / pilihan.
     */
    public function getAllActive(): Collection
    {
        return MstJenisSupplier::active()
            ->orderBy('jenis_supplier_nm', 'asc')
            ->get();
    }

    /**
     * Mengambil satu data jenis supplier berdasarkan ID.
     */
    public function getById(int $id): MstJenisSupplier
    {
        return MstJenisSupplier::where('jenis_supplier_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan data jenis supplier baru via DB Transaction.
     */
    public function store(array $data): MstJenisSupplier
    {
        return DB::transaction(function () use ($data) {
            return MstJenisSupplier::create($data);
        });
    }

    /**
     * Memperbarui data jenis supplier via DB Transaction.
     */
    public function update(int $id, array $data): MstJenisSupplier
    {
        return DB::transaction(function () use ($id, $data) {
            $record = MstJenisSupplier::findOrFail($id);
            $record->update($data);
            return $record->fresh();
        });
    }

    /**
     * Melakukan Soft Delete dengan menandai deleted_st = true.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $record = MstJenisSupplier::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $record->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
