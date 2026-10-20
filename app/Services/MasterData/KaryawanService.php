<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstKaryawan;
use App\Services\Common\CodeGeneratorService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KaryawanService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil data mentah query builder untuk Export & PDF
     */
    public function getAllFilteredQuery($search = null, $departemen = null, $kelompok = null, $shift = null): Builder
    {
        $query = MstKaryawan::with('user.gudang')->active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'LIKE', "%{$search}%")
                  ->orWhere('karyawan_nm', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($departemen)) {
            $query->where('departemen_cd', $departemen);
        }

        if (!empty($kelompok)) {
            $query->where('kelompok', $kelompok);
        }

        if (!empty($shift)) {
            $query->where('shift', $shift);
        }

        return $query;
    }

    /**
     * Mengambil data berhalaman untuk Tabel Utama
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, ?string $departemen = null, ?string $kelompok = null, ?string $shift = null): LengthAwarePaginator
    {
        $query = MstKaryawan::with('user.gudang')->active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'LIKE', "%{$search}%")
                  ->orWhere('karyawan_nm', 'LIKE', "%{$search}%")
                  ->orWhere('jabatan_nm', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($departemen)) {
            $query->where('departemen_cd', $departemen);
        }

        if (!empty($kelompok)) {
            $query->where('kelompok', $kelompok);
        }

        if (!empty($shift)) {
            $query->where('shift', $shift);
        }

        return $query->orderBy('karyawan_id', 'desc')->paginate($perPage);
    }

    public function store(array $data): MstKaryawan
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['nik'])) {
                $data['nik'] = $this->codeGenerator->generate('mst_karyawan', 'nik', 'KRY-');
            }
            return MstKaryawan::create($data);
        });
    }

    public function update(int $karyawan_id, array $data): MstKaryawan
    {
        return DB::transaction(function () use ($karyawan_id, $data) {
            $karyawan = MstKaryawan::findOrFail($karyawan_id);
            $karyawan->update($data);
            return $karyawan->fresh();
        });
    }

    public function delete(int $karyawan_id): bool
    {
        return DB::transaction(function () use ($karyawan_id) {
            $karyawan = MstKaryawan::findOrFail($karyawan_id);
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
