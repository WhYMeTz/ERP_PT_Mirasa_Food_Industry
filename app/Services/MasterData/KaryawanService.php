<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstKaryawan;
use App\Services\Common\CodeGeneratorService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KaryawanService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar karyawan terpaginasi dengan pencarian
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, ?string $departemen = null): LengthAwarePaginator
    {
        $query = MstKaryawan::with('user.gudang')->active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'ILIKE', "%{$search}%")
                  ->orWhere('karyawan_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('jabatan_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($departemen)) {
            $query->where('departemen_cd', $departemen);
        }

        return $query->orderBy('karyawan_id', 'desc')->paginate($perPage);
    }

    /**
     * Mengambil semua karyawan aktif untuk dropdown form
     */
    public function getAllActive(): Collection
    {
        return MstKaryawan::active()->orderBy('karyawan_nm', 'asc')->get();
    }

    /**
     * Mengambil satu karyawan berdasarkan ID
     */
    public function getById(int $id): MstKaryawan
    {
        return MstKaryawan::with('user.gudang')->where('karyawan_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan data karyawan baru
     */
    public function store(array $data): MstKaryawan
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['nik'])) {
                $data['nik'] = $this->codeGenerator->generate('mst_karyawan', 'nik', 'KRY-');
            }

            return MstKaryawan::create($data);
        });
    }

    /**
     * Memperbarui data karyawan
     */
    public function update(int $id, array $data): MstKaryawan
    {
        return DB::transaction(function () use ($id, $data) {
            $karyawan = MstKaryawan::findOrFail($id);
            $karyawan->update($data);
            return $karyawan->fresh();
        });
    }

    /**
     * Menghapus karyawan (Soft Delete)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $karyawan = MstKaryawan::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $karyawan->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
