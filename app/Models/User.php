<?php

namespace App\Models;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstKaryawan;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_plain',
        'karyawan_id',
        'role_cd',
        'gudang_id',
        'active_st',
        'deleted_st',
    ];

    protected $hidden = [
        'password',
        'password_plain',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'active_st'         => 'boolean',
            'deleted_st'        => 'boolean',
        ];
    }

    /**
     * Scope untuk pengguna aktif dan belum dihapus
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }

    /**
     * Relasi ke data profil fisik Karyawan
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(MstKaryawan::class, 'karyawan_id', 'karyawan_id');
    }

    /**
     * Relasi ke Gudang tugas default pengguna (contoh: Gudang Magelang)
     */
     public function gudang(): BelongsTo
     {
         return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
     }

    /**
     * Relasi ke daftar Gudang yang ditugaskan ke pengguna (Multi-Gudang)
     */
    public function assignedGudangs(): BelongsToMany
    {
        return $this->belongsToMany(
            MstGudang::class,
            'sys_user_gudang',
            'user_id',
            'gudang_id'
        )->withPivot(['is_primary', 'active_st', 'deleted_st'])->withTimestamps();
    }

    /**
     * Relasi pivot tabel penugasan gudang
     */
    public function userGudangPivots(): HasMany
    {
        return $this->hasMany(\App\Models\Auth\SysUserGudang::class, 'user_id', 'id');
    }

    /**
     * Dapatkan daftar ID Gudang yang diizinkan untuk pengguna ini.
     * Jika Superadmin -> seluruh gudang aktif diizinkan.
     * Jika Admin Gudang -> HANYA gudang yang di-assign padanya.
     */
    public function getAllowedGudangIds(): array
    {
        if ($this->isSuperAdmin()) {
            return MstGudang::active()->pluck('gudang_id')->toArray();
        }

        $ids = $this->assignedGudangs()
            ->where('mst_gudang.active_st', true)
            ->where('mst_gudang.deleted_st', false)
            ->pluck('mst_gudang.gudang_id')
            ->toArray();

        if (empty($ids) && !empty($this->gudang_id)) {
            $ids = [(int) $this->gudang_id];
        }

        return array_map('intval', $ids);
    }

    /**
     * Dapatkan koleksi Gudang yang diizinkan untuk pengguna ini.
     */
    public function getAllowedGudangList(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->isSuperAdmin()) {
            return MstGudang::active()->orderBy('gudang_nm')->get();
        }

        $gudangs = $this->assignedGudangs()
            ->where('mst_gudang.active_st', true)
            ->where('mst_gudang.deleted_st', false)
            ->orderBy('gudang_nm')
            ->get();

        if ($gudangs->isEmpty() && !empty($this->gudang_id)) {
            $gudangs = MstGudang::where('gudang_id', $this->gudang_id)->get();
        }

        return $gudangs;
    }

    /**
     * Cek apakah pengguna memiliki wewenang mengelola gudang tertentu
     */
    public function canAccessGudang(?int $gudangId): bool
    {
        if (empty($gudangId)) {
            return $this->isSuperAdmin();
        }
        return in_array((int) $gudangId, $this->getAllowedGudangIds(), true);
    }

    /**
     * Cek apakah pengguna adalah Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return strtoupper((string) $this->role_cd) === 'SUPERADMIN';
    }


    /**
     * Cek apakah pengguna adalah Operator Pengemasan Lapangan (Mobile Shopfloor)
     */
    public function isOperatorPackaging(): bool
    {
        return str_contains(strtoupper((string) $this->role_cd), 'OPERATOR_PACKAGING')
            || str_contains(strtoupper((string) $this->role_cd), 'OPERATOR_KEMAS');
    }

    /**
     * Cek apakah pengguna adalah Staf / Kepala Bagian Produksi
     */
    public function isProduksi(): bool
    {
        if ($this->isOperatorPackaging()) {
            return false;
        }
        return str_contains(strtoupper((string) $this->role_cd), 'PRODUKSI');
    }

    /**
     * Cek apakah pengguna adalah Petugas / Admin Gudang
     */
    public function isGudang(): bool
    {
        return str_contains(strtoupper((string) $this->role_cd), 'GUDANG');
    }

    /**
     * Cek apakah pengguna adalah Bagian Pembelian (Purchasing)
     */
    public function isPurchasing(): bool
    {
        return str_contains(strtoupper((string) $this->role_cd), 'PURCHASING');
    }

    /**
     * Cek apakah pengguna adalah Bagian Finance & Akuntansi
     */
    public function isFinance(): bool
    {
        return str_contains(strtoupper((string) $this->role_cd), 'FINANCE');
    }

    /**
     * Cek apakah pengguna adalah Bagian Quality Control (QC)
     */
    public function isQc(): bool
    {
        return str_contains(strtoupper((string) $this->role_cd), 'QC');
    }

    /**
     * Cek apakah pengguna memiliki salah satu dari daftar role
     *
     * @param string|array $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $roles = is_array($roles) ? $roles : [$roles];
        $currentRole = strtoupper((string) $this->role_cd);

        foreach ($roles as $role) {
            $expectedRole = strtoupper(trim($role));
            if ($currentRole === $expectedRole || str_contains($currentRole, $expectedRole)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek hak akses secara dinamis sesuai konfigurasi database yang diatur Superadmin
     */
    public function canDo(string $permissionCd): bool
    {
        return app(\App\Services\Auth\PermissionService::class)->isAllowed($this, $permissionCd);
    }

    /**
     * Hak akses pengelolaan Akun & Pengguna Sistem (Khusus Superadmin)
     */
    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('user_manage');
    }

    /**
     * Hak akses Purchase Order (PO)
     */
    public function canAccessPo(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('po_view') || $this->canDo('po_create');
    }

    public function canCreatePo(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('po_create');
    }

    public function canEditPo(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('po_edit') || $this->canDo('po_create');
    }

    public function canDeletePo(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('po_delete');
    }

    /**
     * Hak akses Sales Order (SO) / Penjualan
     */
    public function canAccessSo(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('so_view') || $this->canDo('so_create');
    }

    public function canCreateSo(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('so_create');
    }

    public function canEditSo(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('so_edit') || $this->canDo('so_create');
    }

    public function canDeleteSo(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('so_delete');
    }

    /**
     * Cek apakah pengguna memiliki permission tertentu
     */
    public function hasPermission(string $permissionCd): bool
    {
        return $this->isSuperAdmin() || $this->canDo($permissionCd);
    }

    /**
     * Hak akses Barang Masuk (GRN / Inbound)
     */
    public function canAccessTerima(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('terima_view') || $this->canDo('terima_create');
    }

    public function canCreateTerima(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('terima_create');
    }

    public function canEditTerima(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('terima_edit') || $this->canDo('terima_create');
    }

    public function canDeleteTerima(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('terima_delete');
    }

    /**
     * Hak akses Retur Pembelian ke Supplier
     */
    public function canAccessRetur(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('retur_view') || $this->canDo('retur_create');
    }

    public function canCreateRetur(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('retur_create');
    }

    public function canDeleteRetur(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('retur_delete');
    }

    /**
     * Hak akses Barang Keluar (Pemakaian Bahan Baku / Outbound SPK)
     */
    public function canAccessPemakaian(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('pemakaian_view') || $this->canDo('pemakaian_create');
    }

    public function canCreatePemakaian(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('pemakaian_create');
    }

    /**
     * Hak akses Produksi & Laporan HPP Harian
     */
    public function canAccessProduksi(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('produksi_view') || $this->canDo('produksi_create');
    }

    public function canCreateProduksi(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('produksi_create');
    }

    /**
     * Hak akses Pengemasan Barang Jadi & Kalkulasi HPP (Packaging)
     */
    public function canAccessPackaging(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('packaging_view') || $this->canAccessProduksi();
    }

    public function canCreatePackaging(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('packaging_create') || $this->canCreateProduksi();
    }

    /**
     * Hak akses Lacak Stok Hasil Produksi (WIP & Barang Jadi)
     */
    public function canAccessStokProduksi(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('stok_fg_view');
    }

    /**
     * Hak akses Lacak Stok & Kartu Stok (Persediaan Gudang Bahan)
     */
    public function canAccessStok(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('stok_view');
    }

    /**
     * Hak akses Adjustment Stok (Koreksi Opname Bahan)
     */
    public function canAccessAdjustment(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('adjustment_view') || $this->canDo('adjustment_create');
    }

    public function canCreateAdjustment(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('adjustment_create');
    }

    public function canVoidAdjustment(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('adjustment_void');
    }

    /**
     * Hak akses Modul Keuangan (Hutang, Pembayaran & Kas/Bank)
     */
    public function canAccessKeuangan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canAccessHutang() 
            || $this->canAccessBayarHutang() 
            || $this->canAccessKasMasuk()
            || $this->canAccessKasBank()
            || $this->canAccessCoa()
            || $this->canAccessJurnal();
    }

    public function canAccessJurnal(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('jurnal_view') || $this->canAccessCoa() || $this->canAccessHutang();
    }

    public function canAccessCoa(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('coa_view') || $this->canDo('kas_bank_view') || $this->canDo('hutang_view');
    }

    public function canManageCoa(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('coa_manage') || $this->canDo('kas_bank_manage');
    }

    public function canAccessKasMasuk(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('kas_masuk_view') || $this->canDo('kas_masuk_create');
    }

    public function canCreateKasMasuk(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('kas_masuk_create');
    }

    public function canVoidKasMasuk(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('kas_masuk_void');
    }

    public function canAccessHutang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('hutang_view') || $this->canDo('hutang_create');
    }

    public function canCreateHutang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('hutang_create');
    }

    public function canEditHutang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('hutang_edit');
    }

    public function canDeleteHutang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('hutang_delete');
    }

    public function canAccessBayarHutang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('bayar_hutang_view') || $this->canDo('bayar_hutang_create');
    }

    public function canCreateBayarHutang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('bayar_hutang_create');
    }

    public function canVoidBayarHutang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('bayar_hutang_void');
    }

    public function canAccessKasBank(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('kas_bank_view') || $this->canDo('kas_bank_manage');
    }

    public function canManageKasBank(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('kas_bank_manage');
    }

    /**
     * Hak akses Master Data
     */
    public function canAccessMasterData(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_barang_view')
            || $this->canAccessKaryawan()
            || $this->canAccessResep()
            || $this->canAccessLiniProduksi()
            || $this->canAccessTarifProduksi()
            || $this->canAccessTarifKemasan()
            || $this->canAccessSupplier()
            || $this->canAccessJenisSupplier()
            || $this->canAccessCustomer()
            || $this->canAccessPerusahaan()
            || $this->canAccessSatuan()
            || $this->canAccessJenis()
            || $this->canAccessMesin();
    }

    public function canManageMasterData(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_barang_manage')
            || $this->canDo('master_barang_create')
            || $this->canDo('master_barang_edit');
    }

    public function canCreateMasterBarang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_barang_create') || $this->canDo('master_barang_manage');
    }

    public function canEditMasterBarang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_barang_edit') || $this->canDo('master_barang_manage');
    }

    public function canDeleteMasterBarang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_barang_delete') || $this->canDo('master_barang_manage');
    }

    public function canAccessSatuan(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_satuan_view') || $this->canDo('master_satuan_create') || $this->canDo('master_gudang_manage');
    }

    public function canCreateMasterSatuan(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_satuan_create') || $this->canDo('master_gudang_manage');
    }

    public function canEditMasterSatuan(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_satuan_edit') || $this->canDo('master_gudang_manage');
    }

    public function canDeleteMasterSatuan(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_satuan_delete') || $this->canDo('master_gudang_manage');
    }

    public function canAccessJenis(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_jenis_view') || $this->canDo('master_jenis_create') || $this->canDo('master_barang_manage');
    }

    public function canCreateMasterJenis(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_jenis_create') || $this->canDo('master_barang_manage');
    }

    public function canEditMasterJenis(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_jenis_edit') || $this->canDo('master_barang_manage');
    }

    public function canDeleteMasterJenis(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_jenis_delete') || $this->canDo('master_barang_manage');
    }

    public function canAccessSupplier(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_supplier_view') || $this->canDo('master_supplier_manage');
    }

    public function canManageSupplier(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_supplier_manage')
            || $this->canDo('master_supplier_create')
            || $this->canDo('master_supplier_edit');
    }

    public function canCreateMasterSupplier(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_supplier_create') || $this->canDo('master_supplier_manage');
    }

    public function canEditMasterSupplier(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_supplier_edit') || $this->canDo('master_supplier_manage');
    }

    public function canDeleteMasterSupplier(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_supplier_delete') || $this->canDo('master_supplier_manage');
    }

    public function canAccessJenisSupplier(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_jenis_supplier_view') 
            || $this->canDo('master_supplier_manage');
    }

    public function canCreateMasterJenisSupplier(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_jenis_supplier_create') 
            || $this->canDo('master_supplier_manage');
    }

    public function canEditMasterJenisSupplier(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_jenis_supplier_edit') 
            || $this->canDo('master_supplier_manage');
    }

    public function canDeleteMasterJenisSupplier(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_jenis_supplier_delete') 
            || $this->canDo('master_supplier_manage');
    }

    public function canAccessCustomer(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_customer_view') || $this->canDo('master_customer_manage');
    }

    public function canManageCustomer(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_customer_manage')
            || $this->canDo('master_customer_create')
            || $this->canDo('master_customer_edit');
    }

    public function canCreateMasterCustomer(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_customer_create') || $this->canDo('master_customer_manage');
    }

    public function canEditMasterCustomer(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_customer_edit') || $this->canDo('master_customer_manage');
    }

    public function canDeleteMasterCustomer(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_customer_delete') || $this->canDo('master_customer_manage');
    }

    public function canManageGudang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_gudang_manage') || $this->canDo('master_perusahaan_manage');
    }

    public function canAccessPerusahaan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_perusahaan_view') 
            || $this->canDo('master_perusahaan_manage')
            || $this->canDo('master_gudang_manage');
    }

    public function canCreateMasterPerusahaan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_perusahaan_create') 
            || $this->canDo('master_perusahaan_manage')
            || $this->canDo('master_gudang_manage');
    }

    public function canEditMasterPerusahaan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_perusahaan_edit') 
            || $this->canDo('master_perusahaan_manage')
            || $this->canDo('master_gudang_manage');
    }

    public function canDeleteMasterPerusahaan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_perusahaan_delete') 
            || $this->canDo('master_perusahaan_manage')
            || $this->canDo('master_gudang_manage');
    }

    /**
     * Hak akses Master Data Karyawan
     */
    public function canAccessKaryawan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_karyawan_view') 
            || $this->canDo('master_karyawan_manage');
    }

    public function canCreateMasterKaryawan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_karyawan_create') 
            || $this->canDo('master_karyawan_manage');
    }

    public function canEditMasterKaryawan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_karyawan_edit') 
            || $this->canDo('master_karyawan_manage');
    }

    public function canDeleteMasterKaryawan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_karyawan_delete') 
            || $this->canDo('master_karyawan_manage');
    }

    /**
     * Hak akses Master Formula Resep (BOM)
     */
    public function canAccessResep(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_resep_view') 
            || $this->canDo('master_resep_manage');
    }

    public function canCreateMasterResep(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_resep_create') 
            || $this->canDo('master_resep_manage');
    }

    public function canEditMasterResep(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_resep_edit') 
            || $this->canDo('master_resep_manage');
    }

    public function canDeleteMasterResep(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_resep_delete') 
            || $this->canDo('master_resep_manage');
    }

    /**
     * Hak akses Master Lini Produksi / Tujuan
     */
    public function canAccessLiniProduksi(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_lini_view') 
            || $this->canDo('master_lini_manage');
    }

    public function canCreateMasterLiniProduksi(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_lini_create') 
            || $this->canDo('master_lini_manage');
    }

    public function canEditMasterLiniProduksi(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_lini_edit') 
            || $this->canDo('master_lini_manage');
    }

    public function canDeleteMasterLiniProduksi(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('master_lini_delete') 
            || $this->canDo('master_lini_manage');
    }

    /**
     * Hak akses Master Mesin Operasional Pabrik (Produksi & Packaging)
     */
    public function canAccessMesin(): bool
    {
        return $this->isSuperAdmin() 
            || $this->isProduksi()
            || $this->canDo('master_mesin_view') 
            || $this->canDo('master_mesin_manage');
    }

    public function canCreateMasterMesin(): bool
    {
        return $this->isSuperAdmin() 
            || $this->isProduksi()
            || $this->canDo('master_mesin_create') 
            || $this->canDo('master_mesin_manage');
    }

    public function canEditMasterMesin(): bool
    {
        return $this->isSuperAdmin() 
            || $this->isProduksi()
            || $this->canDo('master_mesin_edit') 
            || $this->canDo('master_mesin_manage');
    }

    public function canDeleteMasterMesin(): bool
    {
        return $this->isSuperAdmin() 
            || $this->isProduksi()
            || $this->canDo('master_mesin_delete') 
            || $this->canDo('master_mesin_manage');
    }

    /**
     * Hak akses Master Standar Tarif Produksi & FOH
     */
    public function canAccessTarifProduksi(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('tarif_produksi_view')
            || $this->canDo('tarif_produksi_manage');
    }

    public function canManageTarifProduksi(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('tarif_produksi_manage');
    }

    /**
     * Hak akses Master Standar Harga Kemasan & Packaging
     */
    public function canAccessTarifKemasan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('tarif_kemasan_view')
            || $this->canDo('tarif_kemasan_manage')
            || $this->canAccessTarifProduksi()
            || $this->canAccessPackaging();
    }

    public function canManageTarifKemasan(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('tarif_kemasan_manage')
            || $this->canManageTarifProduksi()
            || $this->canCreatePackaging();
    }

    /**
     * Hak akses QC Inbound Bahan Baku
     */
    /**
     * Hak akses QC Mobile (Smartphone / Lapangan)
     */
    public function canAccessQcMobile(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('qc_mobile_view') 
            || $this->canDo('qc_mobile_create')
            || $this->canDo('qc_view');
    }

    public function canCreateQcMobile(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('qc_mobile_create') 
            || $this->canDo('qc_create');
    }

    public function canEditQcMobile(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('qc_mobile_edit') 
            || $this->canDo('qc_edit');
    }

    public function canDeleteQcMobile(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('qc_mobile_delete') 
            || $this->canDo('qc_delete');
    }

    /**
     * Hak akses QC Web (Desktop Admin & Dokumen HACCP)
     */
    public function canAccessQcWeb(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('qc_web_view') 
            || $this->canDo('qc_view');
    }

    public function canEditQcWeb(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('qc_web_edit') 
            || $this->canDo('qc_edit');
    }

    public function canDeleteQcWeb(): bool
    {
        return $this->isSuperAdmin() 
            || $this->canDo('qc_web_delete') 
            || $this->canDo('qc_delete');
    }

    /**
     * Hak akses QC Umum (Fallback & backward compatibility)
     */
    public function canAccessQc(): bool
    {
        return $this->canAccessQcMobile() || $this->canAccessQcWeb();
    }

    public function canCreateQc(): bool
    {
        return $this->canCreateQcMobile();
    }

    public function canEditQc(): bool
    {
        return $this->canEditQcMobile() || $this->canEditQcWeb();
    }

    public function canDeleteQc(): bool
    {
        return $this->canDeleteQcMobile() || $this->canDeleteQcWeb();
    }

    /**
     * Dapatkan nama rute halaman muka (dashboard) default berdasarkan role
     */
    public function getDashboardRoute(): string
    {
        if ($this->isOperatorPackaging()) {
            return 'produksi.packaging.mobile';
        }

        if ($this->isQc()) {
            return 'qc.inbound.create';
        }

        if ($this->isProduksi()) {
            return 'gudang.pemakaian.index';
        }

        if ($this->isPurchasing()) {
            return 'gudang.po.index';
        }

        if ($this->isGudang()) {
            return 'gudang.stok.index';
        }

        return 'gudang.stok.index';
    }
}
