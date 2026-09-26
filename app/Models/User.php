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
        'karyawan_id',
        'role_cd',
        'gudang_id',
        'active_st',
        'deleted_st',
    ];

    protected $hidden = [
        'password',
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
     * Cek apakah pengguna adalah Staf / Kepala Bagian Produksi
     */
    public function isProduksi(): bool
    {
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

    /**
     * Hak akses Barang Keluar (Pemakaian Bahan Baku / Outbound)
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
     * Hak akses Lacak Stok & Kartu Stok (Persediaan Gudang)
     */
    public function canAccessStok(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('stok_view');
    }

    /**
     * Hak akses Master Data
     */
    public function canAccessMasterData(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_barang_view');
    }

    public function canManageMasterData(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_barang_manage');
    }

    public function canAccessSupplier(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_supplier_view') || $this->canDo('master_supplier_manage');
    }

    public function canManageSupplier(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_supplier_manage');
    }

    public function canManageGudang(): bool
    {
        return $this->isSuperAdmin() || $this->canDo('master_gudang_manage');
    }

    /**
     * Dapatkan nama rute halaman muka (dashboard) default berdasarkan role
     */
    public function getDashboardRoute(): string
    {
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
