<?php

namespace App\Models\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SysRole extends Model
{
    protected $table = 'sys_roles';
    protected $primaryKey = 'role_id';

    public $timestamps = false;

    const CREATED_AT = 'created_dt';
    const UPDATED_AT = 'updated_dt';

    protected $fillable = [
        'role_cd',
        'role_nm',
        'desc_txt',
        'is_system',
        'created_by',
        'created_dt',
        'updated_by',
        'updated_dt',
        'active_st',
        'deleted_st',
        'version_no',
    ];

    protected $casts = [
        'is_system'  => 'boolean',
        'active_st'  => 'boolean',
        'deleted_st' => 'boolean',
        'version_no' => 'integer',
        'created_dt' => 'datetime',
        'updated_dt' => 'datetime',
    ];

    /**
     * Scope untuk mengambil peran yang aktif dan belum dihapus
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active_st', true)->where('deleted_st', false);
    }

    /**
     * Relasi ke perincian izin peran
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(SysRolePermission::class, 'role_cd', 'role_cd');
    }

    /**
     * Relasi ke akun pengguna yang menggunakan peran ini
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_cd', 'role_cd')
            ->where('active_st', true)
            ->where('deleted_st', false);
    }
}
