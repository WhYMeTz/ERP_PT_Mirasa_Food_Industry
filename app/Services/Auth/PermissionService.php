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
        'AKSI_EDIT' => [
            'title'        => '✏️ Hak Akses Edit & Koreksi Data (Revisi Transaksi)',
            'desc'         => 'Wewenang mengoreksi transaksi yang salah ketik atau penyesuaian timbangan/lab.',
            'category_key' => 'edit',
            'theme_color'  => '#d97706',
            'bg_color'     => '#fffbeb',
            'border_color' => '#fde68a',
            'badge'        => '✏️ EDIT',
            'badge_bg'     => '#fef3c7',
            'badge_text'   => '#b45309',
            'items'        => [
                'qc_edit' => [
                    'label'  => 'Edit & Koreksi Tiket QC',
                    'desc'   => 'Mengubah data tiket QC yang salah ketik atau revisi timbangan/lab.',
                    'action' => 'EDIT',
                ],
                'po_edit' => [
                    'label'  => 'Edit & Koreksi Purchase Order (PO)',
                    'desc'   => 'Mengubah tanggal, supplier, gudang, catatan, atau rincian item pesanan PO yang belum diterima.',
                    'action' => 'EDIT',
                ],
                'terima_edit' => [
                    'label'  => 'Edit & Koreksi Penerimaan Barang',
                    'desc'   => 'Mengubah data penerimaan barang, nomor surat jalan, tanggal, nomor batch, dan rincian fisik komoditas.',
                    'action' => 'EDIT',
                ],
                'master_barang_edit' => [
                    'label'  => 'Edit & Koreksi Master Barang',
                    'desc'   => 'Mengubah kode, nama, jenis, satuan, konversi, batas minimum stok, dan harga standar barang.',
                    'action' => 'EDIT',
                ],
                'master_satuan_edit' => [
                    'label'  => 'Edit Data Master Satuan',
                    'desc'   => 'Mengubah kode dan nama unit satuan barang.',
                    'action' => 'EDIT',
                ],
                'master_jenis_edit' => [
                    'label'  => 'Edit Master Jenis Barang',
                    'desc'   => 'Mengubah kode dan nama jenis/kategori barang (RAW, WIP, FG, PACK).',
                    'action' => 'EDIT',
                ],
            ],
        ],
        'AKSI_DELETE' => [
            'title'        => '🗑️ Hak Akses Hapus & Batalkan Data (Void / Pembatalan)',
            'desc'         => 'Wewenang menghapus atau membatalkan dokumen operasional.',
            'category_key' => 'delete',
            'theme_color'  => '#dc2626',
            'bg_color'     => '#fef2f2',
            'border_color' => '#fecaca',
            'badge'        => '🗑️ HAPUS',
            'badge_bg'     => '#fee2e2',
            'badge_text'   => '#b91c1c',
            'items'        => [
                'qc_delete' => [
                    'label'  => 'Hapus / Batalkan Tiket QC',
                    'desc'   => 'Menghapus tiket QC yang batal atau salah input.',
                    'action' => 'DELETE',
                ],
                'po_delete' => [
                    'label'  => 'Hapus Purchase Order (PO)',
                    'desc'   => 'Menghapus dokumen Purchase Order (khusus Super Admin / wewenang khusus).',
                    'action' => 'DELETE',
                ],
                'terima_delete' => [
                    'label'  => 'Hapus / Batalkan Penerimaan Barang',
                    'desc'   => 'Menghapus transaksi penerimaan barang dan membatalkan mutasi stok masuk terkait.',
                    'action' => 'DELETE',
                ],
                'master_barang_delete' => [
                    'label'  => 'Hapus / Nonaktifkan Master Barang',
                    'desc'   => 'Menonaktifkan data master barang agar tidak dapat digunakan dalam transaksi baru.',
                    'action' => 'DELETE',
                ],
                'master_satuan_delete' => [
                    'label'  => 'Hapus / Nonaktifkan Master Satuan',
                    'desc'   => 'Menonaktifkan data unit satuan ukuran.',
                    'action' => 'DELETE',
                ],
                'master_jenis_delete' => [
                    'label'  => 'Hapus / Nonaktifkan Jenis Barang',
                    'desc'   => 'Menonaktifkan jenis/kategori klasifikasi barang.',
                    'action' => 'DELETE',
                ],
            ],
        ],
        'AKSI_CREATE' => [
            'title'        => '➕ Hak Akses Input & Transaksi Baru (Entry Operasional)',
            'desc'         => 'Wewenang mencatat dokumen baru untuk transaksi harian.',
            'category_key' => 'create',
            'theme_color'  => '#15803d',
            'bg_color'     => '#f0fdf4',
            'border_color' => '#bbf7d0',
            'badge'        => '➕ INPUT',
            'badge_bg'     => '#dcfce7',
            'badge_text'   => '#15803d',
            'items'        => [
                'po_create' => [
                    'label'  => 'Buat & Kelola PO',
                    'desc'   => 'Membuat PO baru, mengubah, membatalkan, atau menutup PO.',
                    'action' => 'CREATE',
                ],
                'qc_create' => [
                    'label'  => 'Input Inspeksi QC (Mobile)',
                    'desc'   => 'Mencatat hasil uji kadar air, refraksi kotoran, dan timbangan sampling.',
                    'action' => 'CREATE',
                ],
                'terima_create' => [
                    'label'  => 'Catat Penerimaan Barang & Batch',
                    'desc'   => 'Mencatat fisik bongkar muat, no batch baru, dan harga masuk.',
                    'action' => 'CREATE',
                ],
                'pemakaian_create' => [
                    'label'  => 'Catat Pengeluaran Bahan Produksi',
                    'desc'   => 'Mengeluarkan bahan baku/bumbu per batch untuk SPK pabrik.',
                    'action' => 'CREATE',
                ],
                'produksi_create' => [
                    'label'  => 'Input Hasil Produksi & HPP',
                    'desc'   => 'Mencatat hasil timbangan WIP harian, absensi tenaga kerja, dan gas.',
                    'action' => 'CREATE',
                ],
                'retur_create' => [
                    'label'  => 'Catat Retur Pembelian',
                    'desc'   => 'Membuat retur pengembalian barang fisik dan update kuota PO/tagihan.',
                    'action' => 'CREATE',
                ],
                'master_barang_create' => [
                    'label'  => 'Tambah Master Barang Baru',
                    'desc'   => 'Mendaftarkan barang baru ke dalam katalog sistem.',
                    'action' => 'CREATE',
                ],
                'master_satuan_create' => [
                    'label'  => 'Tambah Master Satuan Baru',
                    'desc'   => 'Mendaftarkan unit satuan ukuran baru ke sistem.',
                    'action' => 'CREATE',
                ],
                'master_jenis_create' => [
                    'label'  => 'Tambah Jenis Barang Baru',
                    'desc'   => 'Mendaftarkan kategori / jenis klasifikasi barang baru.',
                    'action' => 'CREATE',
                ],
            ],
        ],
        'AKSI_VIEW' => [
            'title'        => '👁️ Hak Akses Lihat Menu & Monitoring (Read-Only)',
            'desc'         => 'Wewenang membuka menu untuk memantau data tanpa izin mengubah.',
            'category_key' => 'view',
            'theme_color'  => '#0284c7',
            'bg_color'     => '#f0f9ff',
            'border_color' => '#bae6fd',
            'badge'        => '👁️ LIHAT',
            'badge_bg'     => '#e0f2fe',
            'badge_text'   => '#0369a1',
            'items'        => [
                'po_view' => [
                    'label'  => 'Lihat Purchase Order (PO)',
                    'desc'   => 'Membuka menu dan melihat daftar dokumen pemesanan bahan.',
                    'action' => 'VIEW',
                ],
                'qc_view' => [
                    'label'  => 'Lihat Tiket QC Masuk',
                    'desc'   => 'Melihat daftar dan riwayat hasil inspeksi mutu bahan baku dari QC.',
                    'action' => 'VIEW',
                ],
                'terima_view' => [
                    'label'  => 'Lihat Penerimaan Barang & Bahan Baku (GRN)',
                    'desc'   => 'Membuka menu penerimaan fisik bahan masuk dari supplier.',
                    'action' => 'VIEW',
                ],
                'pemakaian_view' => [
                    'label'  => 'Lihat Barang Keluar (OUT)',
                    'desc'   => 'Membuka menu pemakaian bahan keluar untuk lini produksi.',
                    'action' => 'VIEW',
                ],
                'produksi_view' => [
                    'label'  => 'Lihat Laporan HPP & Produksi',
                    'desc'   => 'Melihat buku rekap HPP harian, rendemen, dan hasil WIP.',
                    'action' => 'VIEW',
                ],
                'retur_view' => [
                    'label'  => 'Lihat Retur Pembelian',
                    'desc'   => 'Melihat daftar dokumen pengembalian barang cacat/rusak ke supplier.',
                    'action' => 'VIEW',
                ],
                'stok_view' => [
                    'label'  => 'Lacak Stok & Kartu Stok',
                    'desc'   => 'Memantau sisa kuantitas batch (Tersedia/Habis) dan buku mutasi.',
                    'action' => 'VIEW',
                ],
            ],
        ],
        'MASTER_DATA' => [
            'title'        => '📁 Master Data & Keamanan Sistem',
            'desc'         => 'Wewenang mengelola katalog referensi dan akun sistem.',
            'category_key' => 'master',
            'theme_color'  => '#7c3aed',
            'bg_color'     => '#faf5ff',
            'border_color' => '#e9d5ff',
            'badge'        => '⚙️ KELOLA',
            'badge_bg'     => '#f3e8ff',
            'badge_text'   => '#6b21a8',
            'items'        => [
                'master_barang_view'     => ['label' => 'Lihat Katalog Barang', 'desc' => 'Melihat daftar master singkong, minyak, bumbu, dan kemasan.', 'action' => 'VIEW'],
                'master_barang_manage'   => ['label' => 'Kelola Master Barang', 'desc' => 'Menambah barang baru, mengatur batas minimum stok & harga beli.', 'action' => 'MANAGE'],
                'master_supplier_view'   => ['label' => 'Lihat Master Supplier', 'desc' => 'Melihat daftar mitra supplier petani singkong & vendor.', 'action' => 'VIEW'],
                'master_supplier_manage' => ['label' => 'Kelola Master Supplier', 'desc' => 'Menambah dan mengedit mitra rekanan supplier.', 'action' => 'MANAGE'],
                'master_gudang_manage'   => ['label' => 'Kelola Gudang & Satuan', 'desc' => 'Menambah dan mengedit daftar gudang unit dan satuan barang.', 'action' => 'MANAGE'],
                'user_manage'            => ['label' => 'Manajemen Pengguna & Hak Akses', 'desc' => 'Mengelola user login, password, dan mengubah hak akses peran.', 'action' => 'MANAGE'],
            ],
        ],
    ];

    /**
     * Konfigurasi hak akses bawaan (default) saat database pertama kali diinisialisasi
     */
    public const DEFAULT_PERMISSIONS = [
        'ADMIN_GUDANG' => [
            'qc_view',
            'qc_create',
            'qc_edit',
            'terima_view',
            'terima_create',
            'terima_edit',
            'terima_delete',
            'retur_view',
            'retur_create',
            'pemakaian_view',
            'pemakaian_create',
            'produksi_view',
            'produksi_create',
            'stok_view',
            'master_barang_view',
            'master_barang_create',
            'master_barang_edit',
            'master_satuan_create',
            'master_satuan_edit',
            'master_jenis_create',
            'master_jenis_edit',
            'master_gudang_manage',
        ],
        'STAFF_PRODUKSI' => [
            'pemakaian_view',
            'pemakaian_create',
            'produksi_view',
            'produksi_create',
            'stok_view',
            'master_barang_view',
        ],
        'PURCHASING' => [
            'po_view',
            'po_create',
            'po_edit',
            'qc_view',
            'terima_view',
            'retur_view',
            'retur_create',
            'stok_view',
            'master_barang_view',
            'master_supplier_view',
            'master_supplier_manage',
        ],
        'FINANCE' => [
            'po_view',
            'qc_view',
            'terima_view',
            'retur_view',
            'pemakaian_view',
            'produksi_view',
            'stok_view',
            'master_barang_view',
        ],
        'QC' => [
            'qc_view',
            'qc_create',
            'qc_edit',
            'po_view',
            'terima_view',
            'retur_view',
            'stok_view',
            'master_barang_view',
        ],
    ];

    /**
     * Pastikan tabel hak akses memiliki data awal jika masih kosong,
     * serta menambahkan key baru jika ada fitur/modul baru yang ditambahkan ke sistem.
     */
    public function ensureInitialized(): void
    {
        if (SysRolePermission::count() === 0) {
            $this->seedDefaults();
            return;
        }

        $allKeys = self::getAllPermissionKeys();
        $roles = ['ADMIN_GUDANG', 'PURCHASING', 'STAFF_PRODUKSI', 'FINANCE', 'QC'];
        $existing = SysRolePermission::select('role_cd', 'permission_cd')->get();
        $keyed = [];
        foreach ($existing as $e) {
            $keyed[$e->role_cd . '_' . $e->permission_cd] = true;
        }

        $hasNew = false;
        foreach ($roles as $roleCd) {
            $defaultPerms = self::DEFAULT_PERMISSIONS[$roleCd] ?? [];
            foreach ($allKeys as $key) {
                if (!isset($keyed[$roleCd . '_' . $key])) {
                    $isAllowed = in_array($key, $defaultPerms, true);
                    SysRolePermission::create([
                        'role_cd'       => $roleCd,
                        'permission_cd' => $key,
                        'allowed_st'    => $isAllowed,
                        'updated_by'    => 'SYSTEM_INIT',
                    ]);
                    $hasNew = true;
                }
            }
        }

        if ($hasNew) {
            Cache::forget('mirasa_role_permissions');
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
