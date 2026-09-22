<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstJenisBarang extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_jenis_barang';
    protected $primaryKey = 'jenis_barang_id';

    protected $fillable = [
        'jenis_barang_cd',
        'jenis_barang_nm',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'deleted_st' => 'boolean',
        'active_st' => 'boolean',
    ];

    /**
     * Relasi ke master barang
     */
    public function barang(): HasMany
    {
        return $this->hasMany(MstBarang::class, 'jenis_barang_id', 'jenis_barang_id');
    }
}
