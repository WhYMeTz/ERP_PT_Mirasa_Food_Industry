<?php

namespace App\Services\Auth;

use App\Models\Auth\SysRolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    /**
     * Seluruh daftar hak akses dan menu yang dapat diatur oleh Superadmin
     */
    public const MODULES = [
        'TRANSAKSI_GUDANG' => [
            'title' => '📦 Transaksi Gudang & Operasional Produksi',
            'items' => [
                'po_view'          => ['label' => 'Lihat Purchase Order (PO)', 'desc' => 'Membuka menu dan melihat daftar dokumen pemesanan bahan.'],
                'po_create'        => ['label' => 'Buat & Kelola PO', 'desc' => 'Membuat PO baru, mengubah, membatalkan, atau menutup PO.'],
                'terima_view'      => ['label' => 'Lihat Barang Masuk (GRN)', 'desc' => 'Membuka menu penerimaan fisik bahan masuk dari supplier.'],
                'terima_create'    => ['label' => 'Catat Penerimaan Barang & Batch', 'desc' => 'Mencatat fisik bongkar muat, no batch baru, dan harga masuk.'],
                'pemakaian_view'   => ['label' => 'Lihat Barang Keluar (OUT)', 'desc' => 'Membuka menu pemakaian bahan keluar untuk lini produksi.'],
                'pemakaian_create' => ['label' => 'Catat Pengeluaran Bahan Produksi', 'desc' => 'Mengeluarkan bahan baku/bumbu per batch untuk SPK pabrik.'],
                'stok_view'        => ['label' => 'Lacak Stok & Kartu Stok', 'desc' => 'Memantau sisa kuantitas batch (Tersedia/Habis) dan buku mutasi.'],
            ],
        ],
        'MASTER_DATA' => [
            'title' => '📁 Master Data & Katalog Referensi',
            'items' => [
                'master_barang_view'     => ['label' => 'Lihat Katalog Barang', 'desc' => 'Melihat daftar master singkong, minyak, bumbu, dan kemasan.'],
                'master_barang_manage'   => ['label' => 'Kelola Master Barang', 'desc' => 'Menambah barang baru, mengatur batas minimum stok & harga beli.'],
                'master_supplier_view'   => ['label' => 'Lihat Master Supplier', 'desc' => 'Melihat daftar mitra supplier petani singkong & vendor.'],
                'master_supplier_manage' => ['label' => 'Kelola Master Supplier', 'desc' => 'Menambah dan mengedit mitra rekanan supplier.'],
                'master_gudang_manage'   => ['label' => 'Kelola Gudang & Satuan', 'desc' => 'Menambah dan mengedit daftar gudang unit dan satuan barang.'],
            ],
        ],
        'SISTEM' => [
            'title' => '👥 Pengaturan Akun & Keamanan Sistem',
            'items' => [
                'user_manage' => ['label' => 'Manajemen Pengguna & Hak Akses', 'desc' => 'Mengelola user login, password, dan mengubah hak akses peran.'],
            ],
        ],
    ];

    /**
     * Konfigurasi hak akses bawaan (default) saat database pertama kali diinisialisasi
     */
    public const DEFAULT_PERMISSIONS = [
        'ADMIN_GUDANG' => [
            'terima_view',
            'terima_create',
            'pemakaian_view',
            'pemakaian_create',
            'stok_view',
            'master_barang_view',
            'master_gudang_manage',
        ],
        'STAFF_PRODUKSI' => [
            'pemakaian_view',
            'pemakaian_create',
            'stok_view',
            'master_barang_view',
        ],
        'PURCHASING' => [
            'po_view',
            'po_create',
            'terima_view',
            'stok_view',
            'master_barang_view',
            'master_supplier_view',
            'master_supplier_manage',
        ],
        'FINANCE' => [
            'po_view',
            'terima_view',
            'pemakaian_view',
            'stok_view',
            'master_barang_view',
        ],
        'QC' => [
            'terima_view',
            'terima_create',
            'stok_view',
            'master_barang_view',
        ],
    ];

    /**
     * Pastikan tabel hak akses memiliki data awal jika masih kosong
     */
    public function ensureInitialized(): void
    {
        if (SysRolePermission::count() === 0) {
            $this->seedDefaults();
        }
    }

    /**
     * Isi nilai bawaan ke tabel sys_role_permissions
     */
    public function seedDefaults(): void
    {
        DB::transaction(function () {
            foreach (self::DEFAULT_PERMISSIONS as $roleCd => $permissions) {
                foreach (self::getAllPermissionKeys() as $key) {
                    $isAllowed = in_array($key, $permissions, true);
                    SysRolePermission::updateOrCreate(
                        ['role_cd' => $roleCd, 'permission_cd' => $key],
                        ['allowed_st' => $isAllowed, 'updated_by' => 'SYSTEM_INIT']
                    );
                }
            }
        });

        Cache::forget('mirasa_role_permissions');
    }

    /**
     * Ambil seluruh permission key yang tersedia
     */
    public static function getAllPermissionKeys(): array
    {
        $keys = [];
        foreach (self::MODULES as $module) {
            foreach (array_keys($module['items']) as $key) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    /**
     * Ambil matriks hak akses untuk seluruh role (untuk antarmuka Superadmin)
     */
    public function getPermissionMatrix(): array
    {
        $this->ensureInitialized();

        $roles = [
            'ADMIN_GUDANG'   => 'Admin / Petugas Gudang',
            'PURCHASING'     => 'Purchasing / Pengadaan Bahan',
            'STAFF_PRODUKSI' => 'Staff / Operator Produksi',
            'FINANCE'        => 'Finance & Akuntansi',
            'QC'             => 'Quality Control (QC)',
        ];

        $records = SysRolePermission::all()->groupBy('role_cd');

        $matrix = [];
        foreach ($roles as $roleCd => $roleName) {
            $rolePerms = $records->get($roleCd, collect());
            $matrix[$roleCd] = [
                'name'        => $roleName,
                'permissions' => $rolePerms->pluck('allowed_st', 'permission_cd')->toArray(),
            ];
        }

        return $matrix;
    }

    /**
     * Perbarui izin untuk satu role tertentu
     *
     * @param string $roleCd
     * @param array $allowedKeys Array of permission_cd yang dicentang
     * @param string|null $updatedBy Nama user yang mengubah
     */
    public function updateRolePermissions(string $roleCd, array $allowedKeys, ?string $updatedBy = null): void
    {
        DB::transaction(function () use ($roleCd, $allowedKeys, $updatedBy) {
            $allKeys = self::getAllPermissionKeys();

            foreach ($allKeys as $key) {
                $allowed = in_array($key, $allowedKeys, true);
                SysRolePermission::updateOrCreate(
                    ['role_cd' => $roleCd, 'permission_cd' => $key],
                    [
                        'allowed_st' => $allowed,
                        'updated_by' => $updatedBy ?? 'SUPERADMIN',
                    ]
                );
            }
        });

        Cache::forget('mirasa_role_permissions');
    }

    /**
     * Cek apakah pengguna saat ini diizinkan melakukan tindakan / membuka menu tertentu
     */
    public function isAllowed(User $user, string $permissionCd): bool
    {
        // Superadmin selalu memiliki otoritas penuh ke semua fungsi
        if ($user->isSuperAdmin()) {
            return true;
        }

        $roleCd = strtoupper((string) $user->role_cd);

        // Ambil daftar izin dari cache untuk performa tinggi
        $permissions = Cache::remember('mirasa_role_permissions', 3600, function () {
            return SysRolePermission::where('allowed_st', true)
                ->get()
                ->groupBy('role_cd')
                ->map(fn ($items) => $items->pluck('permission_cd')->toArray())
                ->toArray();
        });

        if (isset($permissions[$roleCd])) {
            return in_array($permissionCd, $permissions[$roleCd], true);
        }

        // Fallback ke default jika belum tersimpan di DB
        $default = self::DEFAULT_PERMISSIONS[$roleCd] ?? [];
        return in_array($permissionCd, $default, true);
    }
}
