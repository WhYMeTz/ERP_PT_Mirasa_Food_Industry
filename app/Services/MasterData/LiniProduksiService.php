<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstLiniProduksi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LiniProduksiService
{
    /**
     * Mengambil daftar master lini produksi dengan pagination dan filter pencarian.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstLiniProduksi::where('deleted_st', false);

        if (!empty($search)) {
            $query->search($search);
        }

        return $query->orderBy('lini_id', 'asc')->paginate($perPage);
    }

    /**
     * Mengambil seluruh lini produksi yang aktif untuk kebutuhan form dropdown.
     */
    public function getAllActive(): Collection
    {
        return MstLiniProduksi::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('lini_id', 'asc')
            ->get();
    }

    /**
     * Mengambil satu data lini produksi berdasarkan ID.
     */
    public function getById(int $id): MstLiniProduksi
    {
        return MstLiniProduksi::where('deleted_st', false)
            ->where('lini_id', $id)
            ->firstOrFail();
    }

    /**
     * Menyimpan data lini produksi baru via DB Transaction.
     */
    public function store(array $data): MstLiniProduksi
    {
        return DB::transaction(function () use ($data) {
            $data['lini_cd'] = strtoupper(trim($data['lini_cd']));
            $data['lini_nm'] = strtoupper(trim($data['lini_nm']));
            $data['kategori_lini'] = !empty($data['kategori_lini']) ? strtoupper(trim($data['kategori_lini'])) : 'FINISH GOOD (FG)';
            $data['tipe_batch'] = strtoupper(trim($data['tipe_batch'] ?? 'REGULER'));

            return MstLiniProduksi::create($data);
        });
    }

    /**
     * Memperbarui data lini produksi via DB Transaction.
     */
    public function update(int $id, array $data): MstLiniProduksi
    {
        return DB::transaction(function () use ($id, $data) {
            $lini = MstLiniProduksi::findOrFail($id);

            if (isset($data['lini_cd'])) {
                $data['lini_cd'] = strtoupper(trim($data['lini_cd']));
            }
            if (isset($data['lini_nm'])) {
                $data['lini_nm'] = strtoupper(trim($data['lini_nm']));
            }
            if (isset($data['kategori_lini'])) {
                $data['kategori_lini'] = strtoupper(trim($data['kategori_lini']));
            }
            if (isset($data['tipe_batch'])) {
                $data['tipe_batch'] = strtoupper(trim($data['tipe_batch']));
            }

            $lini->update($data);
            return $lini->fresh();
        });
    }

    /**
     * Melakukan Soft Delete dengan menandai deleted_st = true.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $lini = MstLiniProduksi::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $lini->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
