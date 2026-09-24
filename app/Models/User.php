<?php

namespace App\Models;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstKaryawan;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Cek apakah pengguna adalah Petugas Gudang
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
}
