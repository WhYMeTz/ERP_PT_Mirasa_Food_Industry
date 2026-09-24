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
        $query = User::with(['karyawan', 'gudang'])->active();

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
        return User::with(['karyawan', 'gudang'])->where('id', $id)->firstOrFail();
    }

    /**
     * Menyimpan akun pengguna baru
     */
    public function store(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            $data['active_st'] = true;
            $data['deleted_st'] = false;

            return User::create($data);
        });
    }

    /**
     * Memperbarui akun pengguna
     */
    public function update(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);

            // Update password hanya jika diisi
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);
            return $user->fresh(['karyawan', 'gudang']);
        });
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
