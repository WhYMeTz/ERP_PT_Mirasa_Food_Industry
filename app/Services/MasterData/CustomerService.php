<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstCustomer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstCustomer::active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_cd', 'ILIKE', "%{$search}%")
                  ->orWhere('customer_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('kontak_no', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('customer_id', 'asc')->paginate($perPage);
    }

    public function getById(int $id): MstCustomer
    {
        return MstCustomer::where('customer_id', $id)->firstOrFail();
    }

    public function store(array $data): MstCustomer
    {
        return DB::transaction(function () use ($data) {
            return MstCustomer::create($data);
        });
    }

    public function update(int $id, array $data): MstCustomer
    {
        return DB::transaction(function () use ($id, $data) {
            $customer = MstCustomer::findOrFail($id);
            $customer->update($data);
            return $customer->fresh();
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $customer = MstCustomer::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $customer->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
