<?php

namespace App\Services\Auth;

use App\Models\Auth\SysRole;
use App\Models\Auth\SysRolePermission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoleService
{
    /**
     * Ambil seluruh peran yang aktif dari basis data
     */
    public function getAllRoles(): Collection
    {
        return SysRole::active()
            ->withCount('users')
            ->orderBy('is_system', 'desc')
            ->orderBy('role_nm', 'asc')
            ->get();
    }

    /**
     * Ambil daftar peran dalam format key => label untuk dropdown select
     */
    public function getRolesForSelect(): array
    {
        return Cache::remember('mirasa_roles_select', 3600, function () {
            return SysRole::active()
                ->orderBy('is_system', 'desc')
                ->orderBy('role_nm', 'asc')
                ->pluck('role_nm', 'role_cd')
                ->toArray();
        });
    }

    /**
     * Ambil daftar peran selain SUPERADMIN untuk matriks hak akses
     */
    public function getNonSuperAdminRoles(): array
    {
        return SysRole::active()
            ->where('role_cd', '!=', 'SUPERADMIN')
            ->orderBy('is_system', 'desc')
            ->orderBy('role_nm', 'asc')
            ->pluck('role_nm', 'role_cd')
            ->toArray();
    }

    /**
     * Simpan peran baru dan inisialisasi hak akses awalnya
     */
    public function storeRole(array $data, ?string $userName = null): SysRole
    {
        $rawCode = trim($data['role_cd'] ?? '');
        $roleCd = strtoupper(preg_replace('/[^A-Za-z0-9_]/', '_', $rawCode));

        if (empty($roleCd)) {
            throw new InvalidArgumentException('Kode peran (Role Code) wajib diisi.');
        }

        $existing = SysRole::where('role_cd', $roleCd)->first();
        if ($existing && !$existing->deleted_st) {
            throw new InvalidArgumentException("Kode peran '{$roleCd}' sudah terdaftar dalam sistem.");
        }

        return DB::transaction(function () use ($existing, $roleCd, $data, $userName) {
            $now = now();
            $actor = $userName ?? auth()->user()?->name ?? 'SUPERADMIN';

            if ($existing && $existing->deleted_st) {
                // Restore role yang pernah dihapus
                $existing->update([
                    'role_nm'    => $data['role_nm'],
                    'desc_txt'   => $data['desc_txt'] ?? null,
                    'active_st'  => true,
                    'deleted_st' => false,
                    'updated_by' => $actor,
                    'updated_dt' => $now,
                    'version_no' => $existing->version_no + 1,
                ]);
                $role = $existing;
            } else {
                $role = SysRole::create([
                    'role_cd'    => $roleCd,
                    'role_nm'    => $data['role_nm'],
                    'desc_txt'   => $data['desc_txt'] ?? null,
                    'is_system'  => false,
                    'created_by' => $actor,
                    'created_dt' => $now,
                    'updated_by' => $actor,
                    'updated_dt' => $now,
                    'active_st'  => true,
                    'deleted_st' => false,
                    'version_no' => 1,
                ]);
            }

            // Inisialisasi hak akses ke tabel sys_role_permissions
            $allKeys = PermissionService::getAllPermissionKeys();
            $copyFrom = !empty($data['copy_from_role']) ? $data['copy_from_role'] : null;
            $templatePerms = [];

            if ($copyFrom) {
                $templatePerms = SysRolePermission::where('role_cd', $copyFrom)
                    ->where('allowed_st', true)
                    ->pluck('permission_cd')
                    ->toArray();
            }

            foreach ($allKeys as $key) {
                $isAllowed = in_array($key, $templatePerms, true);
                SysRolePermission::updateOrCreate(
                    ['role_cd' => $roleCd, 'permission_cd' => $key],
                    [
                        'allowed_st' => $isAllowed,
                        'created_by' => $actor,
                        'updated_by' => $actor,
                    ]
                );
            }

            $this->clearCache();

            return $role;
        });
    }

    /**
     * Perbarui data peran
     */
    public function updateRole(int $roleId, array $data, ?string $userName = null): SysRole
    {
        return DB::transaction(function () use ($roleId, $data, $userName) {
            $role = SysRole::findOrFail($roleId);
            $actor = $userName ?? auth()->user()?->name ?? 'SUPERADMIN';

            $role->update([
                'role_nm'    => $data['role_nm'],
                'desc_txt'   => $data['desc_txt'] ?? null,
                'updated_by' => $actor,
                'updated_dt' => now(),
                'version_no' => $role->version_no + 1,
            ]);

            $this->clearCache();

            return $role;
        });
    }

    /**
     * Hapus peran (Soft delete) dengan validasi proteksi
     */
    public function deleteRole(int $roleId, ?string $userName = null): bool
    {
        return DB::transaction(function () use ($roleId, $userName) {
            $role = SysRole::findOrFail($roleId);

            if ($role->is_system) {
                throw new InvalidArgumentException("Peran bawaan sistem '{$role->role_cd}' dilindungi dan tidak dapat dihapus.");
            }

            $userCount = User::where('role_cd', $role->role_cd)->active()->count();
            if ($userCount > 0) {
                throw new InvalidArgumentException("Peran '{$role->role_nm}' tidak dapat dihapus karena masih digunakan oleh {$userCount} akun pengguna aktif.");
            }

            $actor = $userName ?? auth()->user()?->name ?? 'SUPERADMIN';

            $role->update([
                'active_st'  => false,
                'deleted_st' => true,
                'updated_by' => $actor,
                'updated_dt' => now(),
                'version_no' => $role->version_no + 1,
            ]);

            $this->clearCache();

            return true;
        });
    }

    /**
     * Bersihkan cache peran dan hak akses
     */
    public function clearCache(): void
    {
        Cache::forget('mirasa_roles_select');
        Cache::forget('mirasa_role_permissions');
    }
}
