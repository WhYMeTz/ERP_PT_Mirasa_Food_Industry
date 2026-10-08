<?php

namespace App\Services\Auth;

use App\Models\Auth\SysRolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    /**
     * Matriks Hak Akses Berbasis Modul Alur Kerja Pabrik (Domain / Module-Based Matrix)
     */
    public const MODULE_GROUPS = [
        'INBOUND' => [
            'group_key'    => 'INBOUND',
            'group_title'  => '🚚 Pengadaan & Inbound (Bahan Masuk)',
            'group_desc'   => 'Pesanan PO pembelian, pemeriksaan mutu QC HACCP, penerimaan GRN, dan retur vendor.',
            'badge'        => 'INBOUND',
            'badge_color'  => '#d97706',
            'bg_color'     => '#fffbeb',
            'border_color' => '#fde68a',
            'modules'      => [
                'PO' => [
                    'label'   => 'Purchase Order (PO Beli)',
                    'desc'    => 'Pemesanan bahan baku singkong dan bahan penolong ke pemasok.',
                    'actions' => [
                        'view'   => ['key' => 'po_view',   'label' => 'Lihat Data'],
                        'create' => ['key' => 'po_create', 'label' => 'Buat PO Baru'],
                        'edit'   => ['key' => 'po_edit',   'label' => 'Edit / Koreksi'],
                        'delete' => ['key' => 'po_delete', 'label' => 'Hapus / Batal'],
                    ],
                ],
                'QC' => [
                    'label'   => 'Pengujian Mutu (QC & HACCP)',
                    'desc'    => 'Inspeksi kedatangan sampling, uji laboratorium goreng, dan verifikasi checklist.',
                    'actions' => [
                        'view'   => ['key' => 'qc_view',   'label' => 'Lihat Tiket QC'],
                        'create' => ['key' => 'qc_create', 'label' => 'Input Uji QC'],
                        'edit'   => ['key' => 'qc_edit',   'label' => 'Edit / Koreksi'],
                        'delete' => ['key' => 'qc_delete', 'label' => 'Hapus / Batal'],
                    ],
                ],
                'TERIMA' => [
                    'label'   => 'Penerimaan Barang (GRN)',
                    'desc'    => 'Bongkar muat fisik barang, nomor batch kedatangan, dan penimbangan akhir.',
                    'actions' => [
                        'view'   => ['key' => 'terima_view',   'label' => 'Lihat Penerimaan'],
                        'create' => ['key' => 'terima_create', 'label' => 'Catat Penerimaan'],
                        'edit'   => ['key' => 'terima_edit',   'label' => 'Edit / Koreksi'],
                        'delete' => ['key' => 'terima_delete', 'label' => 'Hapus / Batal'],
                    ],
                ],
                'RETUR' => [
                    'label'   => 'Retur Pembelian ke Vendor',
                    'desc'    => 'Pengembalian barang reject atau tidak sesuai spesifikasi kembali ke supplier.',
                    'actions' => [
                        'view'   => ['key' => 'retur_view',   'label' => 'Lihat Retur'],
                        'create' => ['key' => 'retur_create', 'label' => 'Catat Retur Baru'],
                        'delete' => ['key' => 'retur_delete', 'label' => 'Hapus / Batal'],
                    ],
                ],
            ],
        ],
        'GUDANG' => [
            'group_key'    => 'GUDANG',
            'group_title'  => '📦 Stok Gudang Bahan Baku & Penolong',
            'group_desc'   => 'Saldo fisik singkong, minyak, bumbu & kemasan, mutasi kartu stok, dan koreksi opname.',
            'badge'        => 'GUDANG BAHAN',
            'badge_color'  => '#059669',
            'bg_color'     => '#f0fdf4',
            'border_color' => '#a7f3d0',
            'modules'      => [
                'STOK_BAHAN' => [
                    'label'   => 'Lacak Stok & Kartu Stok Bahan',
                    'desc'    => 'Monitoring saldo fisik real-time, buku mutasi kartu stok ledger, dan rekap aset.',
                    'actions' => [
                        'view' => ['key' => 'stok_view', 'label' => 'Buka / Lihat Stok'],
                    ],
                ],
                'ADJUSTMENT' => [
                    'label'   => 'Adjustment Stok (Koreksi Opname)',
                    'desc'    => 'Penyesuaian selisih fisik stok, susut timbangan, dan opname persediaan bahan.',
                    'actions' => [
                        'view'   => ['key' => 'adjustment_view',   'label' => 'Lihat Koreksi'],
                        'create' => ['key' => 'adjustment_create', 'label' => 'Input Penyesuaian'],
                        'delete' => ['key' => 'adjustment_void',   'label' => 'Batalkan / Void'],
                    ],
                ],
            ],
        ],
        'PRODUKSI' => [
            'group_key'    => 'PRODUKSI',
            'group_title'  => '🏭 Operasional Produksi Pabrik',
            'group_desc'   => 'Pengeluaran bahan ke dapur penggorengan, input hasil masak olahan, dan buku HPP.',
            'badge'        => 'PRODUKSI',
            'badge_color'  => '#ea580c',
            'bg_color'     => '#fff7ed',
            'border_color' => '#fed7aa',
            'modules'      => [
                'SPK' => [
                    'label'   => 'Pemakaian Bahan (SPK Masak)',
                    'desc'    => 'Permintaan dan pengeluaran bahan baku / penolong ke dapur produksi.',
                    'actions' => [
                        'view'   => ['key' => 'pemakaian_view',   'label' => 'Lihat SPK'],
                        'create' => ['key' => 'pemakaian_create', 'label' => 'Catat Pengeluaran'],
                    ],
                ],
                'HASIL_PRODUKSI' => [
                    'label'   => 'Hasil Olahan & Rekapitulasi HPP',
                    'desc'    => 'Pencatatan batch masak/kemas, borongan pekerja, gas LPG, dan evaluasi biaya HPP.',
                    'actions' => [
                        'view'   => ['key' => 'produksi_view',   'label' => 'Lihat Hasil & HPP'],
                        'create' => ['key' => 'produksi_create', 'label' => 'Input Hasil Masak'],
                    ],
                ],
            ],
        ],
        'STOK_PRODUKSI' => [
            'group_key'    => 'STOK_PRODUKSI',
            'group_title'  => '🗃️ Stok Hasil Produksi (WIP & FG)',
            'group_desc'   => 'Persediaan keripik olahan setengah jadi (WIP) dan produk jadi siap jual (Finished Goods).',
            'badge'        => 'HASIL PRODUKSI',
            'badge_color'  => '#0891b2',
            'bg_color'     => '#ecfeff',
            'border_color' => '#a5f3fc',
            'modules'      => [
                'STOK_FG' => [
                    'label'   => 'Lacak Stok WIP & Barang Jadi (FG)',
                    'desc'    => 'Saldo fisik olahan keripik matang dan mutasi kartu stok keluar-masuk barang jadi.',
                    'actions' => [
                        'view' => ['key' => 'stok_fg_view', 'label' => 'Buka / Lihat Stok WIP/FG'],
                    ],
                ],
            ],
        ],
        'PENJUALAN' => [
            'group_key'    => 'PENJUALAN',
            'group_title'  => '🛒 Penjualan (Sales Order & Outbound)',
            'group_desc'   => 'Pesanan pembelian produk keripik jadi dari mitra customer, agen toko, dan distributor.',
            'badge'        => 'PENJUALAN',
            'badge_color'  => '#2563eb',
            'bg_color'     => '#eff6ff',
            'border_color' => '#bfdbfe',
            'modules'      => [
                'SO' => [
                    'label'   => 'PO Penjualan (SO Customer)',
                    'desc'    => 'Pencatatan pesanan produk jadi dari customer dan pengiriman barang.',
                    'actions' => [
                        'view'   => ['key' => 'so_view',   'label' => 'Lihat Pesanan'],
                        'create' => ['key' => 'so_create', 'label' => 'Buat SO Baru'],
                        'edit'   => ['key' => 'so_edit',   'label' => 'Edit Pesanan'],
                        'delete' => ['key' => 'so_delete', 'label' => 'Hapus / Batal'],
                    ],
                ],
            ],
        ],
        'MASTER' => [
            'group_key'    => 'MASTER',
            'group_title'  => '🗂️ Master Data & Konfigurasi Pabrik',
            'group_desc'   => 'Katalog material barang, formula resep BOM, tarif upah/FOH, relasi mitra, dan fasilitas.',
            'badge'        => 'MASTER DATA',
            'badge_color'  => '#7c3aed',
            'bg_color'     => '#faf5ff',
            'border_color' => '#e9d5ff',
            'modules'      => [
                'BARANG' => [
                    'label'   => 'Katalog Barang & Bahan',
                    'desc'    => 'Master singkong, minyak, bumbu, kemasan, WIP & barang jadi.',
                    'actions' => [
                        'view'   => ['key' => 'master_barang_view',   'label' => 'Lihat Data'],
                        'create' => ['key' => 'master_barang_create', 'label' => 'Tambah'],
                        'edit'   => ['key' => 'master_barang_edit',   'label' => 'Edit'],
                        'delete' => ['key' => 'master_barang_delete', 'label' => 'Nonaktifkan'],
                    ],
                ],
                'RESEP' => [
                    'label'   => 'Formula Resep (BOM Pabrik)',
                    'desc'    => 'Standar komposisi bahan baku & penolong per batch keripik.',
                    'actions' => [
                        'view'   => ['key' => 'master_resep_view',   'label' => 'Lihat Resep'],
                        'create' => ['key' => 'master_resep_create', 'label' => 'Tambah'],
                        'edit'   => ['key' => 'master_resep_edit',   'label' => 'Edit'],
                        'delete' => ['key' => 'master_resep_delete', 'label' => 'Hapus'],
                    ],
                ],
                'TARIF' => [
                    'label'   => 'Standar Tarif Produksi & FOH',
                    'desc'    => 'Tarif upah borongan masak, bumbu racik, gas LPG, dan FOH pabrik.',
                    'actions' => [
                        'view'   => ['key' => 'tarif_produksi_view',   'label' => 'Lihat Tarif'],
                        'create' => ['key' => 'tarif_produksi_manage', 'label' => 'Kelola Tarif'],
                    ],
                ],
                'LINI' => [
                    'label'   => 'Lini Produksi / Lokasi Masak',
                    'desc'    => 'Dapur penggorengan, stasiun perajangan, dan lini pengemasan.',
                    'actions' => [
                        'view'   => ['key' => 'master_lini_view',   'label' => 'Lihat Data'],
                        'create' => ['key' => 'master_lini_create', 'label' => 'Tambah'],
                        'edit'   => ['key' => 'master_lini_edit',   'label' => 'Edit'],
                        'delete' => ['key' => 'master_lini_delete', 'label' => 'Hapus'],
                    ],
                ],
                'SUPPLIER' => [
                    'label'   => 'Mitra Supplier & Petani',
                    'desc'    => 'Data kontak, alamat, dan klasifikasi supplier pemasok.',
                    'actions' => [
                        'view'   => ['key' => 'master_supplier_view',   'label' => 'Lihat Data'],
                        'create' => ['key' => 'master_supplier_create', 'label' => 'Tambah'],
                        'edit'   => ['key' => 'master_supplier_edit',   'label' => 'Edit'],
                        'delete' => ['key' => 'master_supplier_delete', 'label' => 'Nonaktifkan'],
                    ],
                ],
                'CUSTOMER' => [
                    'label'   => 'Mitra Customer (Toko / Agen)',
                    'desc'    => 'Data klien pembeli produk keripik dan kontak toko.',
                    'actions' => [
                        'view'   => ['key' => 'master_customer_view',   'label' => 'Lihat Data'],
                        'create' => ['key' => 'master_customer_create', 'label' => 'Tambah'],
                        'edit'   => ['key' => 'master_customer_edit',   'label' => 'Edit'],
                        'delete' => ['key' => 'master_customer_delete', 'label' => 'Nonaktifkan'],
                    ],
                ],
                'KARYAWAN' => [
                    'label'   => 'Data Karyawan & Operator',
                    'desc'    => 'Profil staf, jabatan, departemen, dan data absensi.',
                    'actions' => [
                        'view'   => ['key' => 'master_karyawan_view',   'label' => 'Lihat Data'],
                        'create' => ['key' => 'master_karyawan_create', 'label' => 'Tambah'],
                        'edit'   => ['key' => 'master_karyawan_edit',   'label' => 'Edit'],
                        'delete' => ['key' => 'master_karyawan_delete', 'label' => 'Nonaktifkan'],
                    ],
                ],
                'PERUSAHAAN' => [
                    'label'   => 'Master Perusahaan / Cabang',
                    'desc'    => 'Entitas badan usaha PT / CV dalam grup Mirasa.',
                    'actions' => [
                        'view' => ['key' => 'master_perusahaan_view', 'label' => 'Lihat Data'],
                        'edit' => ['key' => 'master_perusahaan_edit', 'label' => 'Edit Data'],
                    ],
                ],
                'SATUAN' => [
                    'label'   => 'Satuan Ukur Barang',
                    'desc'    => 'Unit ukuran standar (kg, roll, bal, pack, liter, sak).',
                    'actions' => [
                        'view'   => ['key' => 'master_satuan_view',   'label' => 'Lihat Data'],
                        'create' => ['key' => 'master_satuan_create', 'label' => 'Tambah'],
                        'edit'   => ['key' => 'master_satuan_edit',   'label' => 'Edit'],
                        'delete' => ['key' => 'master_satuan_delete', 'label' => 'Hapus'],
                    ],
                ],
                'JENIS' => [
                    'label'   => 'Kategori / Jenis Barang',
                    'desc'    => 'Klasifikasi kelompok barang (RAW, WIP, FG, PACK).',
                    'actions' => [
                        'view'   => ['key' => 'master_jenis_view',   'label' => 'Lihat Data'],
                        'create' => ['key' => 'master_jenis_create', 'label' => 'Tambah'],
                        'edit'   => ['key' => 'master_jenis_edit',   'label' => 'Edit'],
                        'delete' => ['key' => 'master_jenis_delete', 'label' => 'Hapus'],
                    ],
                ],
                'USERS' => [
                    'label'   => 'Hak Akses & Pengguna Sistem',
                    'desc'    => 'Wewenang akun login, role pengguna, dan matriks hak akses.',
                    'actions' => [
                        'view' => ['key' => 'user_manage', 'label' => 'Kelola Hak Akses'],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Alias backwards compatibility
     */
    public const MODULES = self::MODULE_GROUPS;

    /**
     * Konfigurasi hak akses bawaan (default) saat database pertama kali diinisialisasi
     */
    public const DEFAULT_PERMISSIONS = [
        'ADMIN_GUDANG' => [
            'qc_view',
            'terima_view',
            'terima_create',
            'terima_edit',
            'terima_delete',
            'retur_view',
            'retur_create',
            'retur_delete',
            'stok_view',
            'adjustment_view',
            'adjustment_create',
            'pemakaian_view',
            'pemakaian_create',
            'master_barang_view',
            'master_satuan_view',
            'master_jenis_view',
        ],
        'STAFF_PRODUKSI' => [
            'pemakaian_view',
            'pemakaian_create',
            'produksi_view',
            'produksi_create',
            'stok_fg_view',
            'master_barang_view',
            'master_resep_view',
            'master_lini_view',
        ],
        'PURCHASING' => [
            'po_view',
            'po_create',
            'po_edit',
            'po_delete',
            'qc_view',
            'terima_view',
            'retur_view',
            'retur_create',
            'stok_view',
            'master_barang_view',
            'master_supplier_view',
            'master_supplier_create',
            'master_supplier_edit',
        ],
        'FINANCE' => [
            'po_view',
            'qc_view',
            'terima_view',
            'retur_view',
            'stok_view',
            'adjustment_view',
            'pemakaian_view',
            'produksi_view',
            'stok_fg_view',
            'so_view',
            'so_create',
            'so_edit',
            'tarif_produksi_view',
            'tarif_produksi_manage',
            'master_barang_view',
            'master_customer_view',
        ],
        'QC' => [
            'qc_view',
            'qc_create',
            'qc_edit',
            'qc_delete',
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
        $roles = array_keys(app(RoleService::class)->getNonSuperAdminRoles());
        if (empty($roles)) {
            $roles = ['ADMIN_GUDANG', 'PURCHASING', 'STAFF_PRODUKSI', 'FINANCE', 'QC'];
        }
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
        foreach (self::MODULE_GROUPS as $group) {
            foreach ($group['modules'] as $module) {
                foreach ($module['actions'] as $act) {
                    $keys[] = $act['key'];
                }
            }
        }
        return array_values(array_unique($keys));
    }

    /**
     * Ambil matriks hak akses untuk seluruh role (untuk antarmuka Superadmin)
     */
    public function getPermissionMatrix(): array
    {
        $this->ensureInitialized();

        $roles = app(RoleService::class)->getNonSuperAdminRoles();
        if (empty($roles)) {
            $roles = [
                'ADMIN_GUDANG'   => 'Admin / Petugas Gudang',
                'PURCHASING'     => 'Purchasing / Pengadaan Bahan',
                'STAFF_PRODUKSI' => 'Staff / Operator Produksi',
                'FINANCE'        => 'Finance & Akuntansi',
                'QC'             => 'Quality Control (QC)',
            ];
        }

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

        $this->clearCache();
    }

    /**
     * Hapus cache hak akses peran
     */
    public function clearCache(): void
    {
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
