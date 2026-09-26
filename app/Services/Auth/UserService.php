<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Daftar peran (roles) hak akses resmi ERP PT Mirasa
     */
    public const ROLES = [
        'SUPERADMIN'     => 'Super Administrator (Akses Penuh)',
        'STAFF_PRODUKSI' => 'Staff / Operator Produksi (Terkunci ke Unit Pabrik)',
        'ADMIN_GUDANG'   => 'Admin / Petugas Gudang (Terkunci ke Gudang Tugas)',
        'PURCHASING'     => 'Purchasing / Pengadaan Bahan Baku',
        'FINANCE'        => 'Finance & Akuntansi (Hutang / Piutang)',
        'QC'             => 'Quality Control & Grading Mutu',
    ];

    /**
     * Mengambil daftar pengguna terpaginasi
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, ?string $role = null): LengthAwarePaginator
    {
        $query = User::with(['karyawan', 'gudang', 'assignedGudangs'])->active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('role_cd', 'ILIKE', "%{$search}%")
                  ->orWhereHas('karyawan', function ($kq) use ($search) {
                      $kq->where('karyawan_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('nik', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($role)) {
            $query->where('role_cd', $role);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Mengambil data pengguna berdasarkan ID
     */
    public function getById(int $id): User
    {
        return User::with(['karyawan', 'gudang', 'assignedGudangs'])->where('id', $id)->firstOrFail();
    }

    /**
     * Menyimpan akun pengguna baru beserta penugasan multi-gudang
     */
    public function store(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $gudangIds = $data['gudang_ids'] ?? null;
            unset($data['gudang_ids']);

            $data['password'] = Hash::make($data['password']);
            $data['active_st'] = true;
            $data['deleted_st'] = false;

            $user = User::create($data);

            if ($gudangIds !== null) {
                $this->syncUserGudangs($user, ['gudang_ids' => $gudangIds]);
            } elseif (!empty($user->gudang_id)) {
                $this->syncUserGudangs($user, ['gudang_id' => $user->gudang_id]);
            }

            return $user->fresh(['karyawan', 'gudang', 'assignedGudangs']);
        });
    }

    /**
     * Memperbarui akun pengguna dan sinkronisasi hak gudang
     */
    public function update(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);

            $gudangIds = $data['gudang_ids'] ?? null;
            unset($data['gudang_ids']);

            // Update password hanya jika diisi
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            if ($gudangIds !== null) {
                $this->syncUserGudangs($user, ['gudang_ids' => $gudangIds]);
            } elseif (array_key_exists('gudang_id', $data)) {
                $this->syncUserGudangs($user, ['gudang_id' => $data['gudang_id']]);
            }

            return $user->fresh(['karyawan', 'gudang', 'assignedGudangs']);
        });
    }

    /**
     * Sinkronisasi relasi multi-gudang (sys_user_gudang)
     */
    protected function syncUserGudangs(User $user, array $data): void
    {
        if (isset($data['gudang_ids'])) {
            $gudangIds = array_values(array_filter(array_map('intval', (array) $data['gudang_ids'])));
            $syncData = [];
            $first = true;
            foreach ($gudangIds as $gid) {
                $syncData[$gid] = [
                    'is_primary' => $first,
                    'active_st'  => true,
                    'deleted_st' => false,
                ];
                $first = false;
            }
            $user->assignedGudangs()->sync($syncData);

            // Perbarui users.gudang_id untuk kompatibilitas legacy
            if (!empty($gudangIds)) {
                if (!in_array($user->gudang_id, $gudangIds)) {
                    $user->gudang_id = $gudangIds[0];
                    $user->save();
                }
            } else {
                $user->gudang_id = null;
                $user->save();
            }
        } elseif (isset($data['gudang_id'])) {
            $gudangId = !empty($data['gudang_id']) ? (int) $data['gudang_id'] : null;
            if ($gudangId) {
                $user->assignedGudangs()->sync([
                    $gudangId => [
                        'is_primary' => true,
                        'active_st'  => true,
                        'deleted_st' => false,
                    ]
                ]);
            } else {
                $user->assignedGudangs()->detach();
            }
        }
    }

    /**
     * Menghapus akun pengguna (Soft Delete)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);

            return $user->update([
                'deleted_st' => true,
                'active_st'  => false,
            ]);
        });
    }

    /**
     * Mengambil daftar pilihan role
     */
    public function getAvailableRoles(): array
    {
        return self::ROLES;
    }
}
