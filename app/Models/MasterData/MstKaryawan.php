<?php

namespace App\Models\MasterData;

use App\Models\User;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MstKaryawan extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_karyawan';
    protected $primaryKey = 'karyawan_id';

    protected $fillable = [
        'nik',
        'karyawan_nm',
        'departemen_cd',
        'jabatan_nm',
        'telepon_no',
        'email',
        'alamat_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    /**
     * Relasi ke akun user jika karyawan memiliki hak akses sistem
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'karyawan_id', 'karyawan_id');
    }
}
