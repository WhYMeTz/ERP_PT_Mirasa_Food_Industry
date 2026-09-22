<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstSatuan extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_satuan';
    protected $primaryKey = 'satuan_id';

    protected $fillable = [
        'satuan_cd',
        'satuan_nm',
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
     * Relasi ke barang yang menggunakan satuan ini sebagai satuan dasar
     */
    public function barangDasar(): HasMany
    {
        return $this->hasMany(MstBarang::class, 'satuan_dasar_id', 'satuan_id');
    }

    /**
     * Relasi ke barang yang menggunakan satuan ini sebagai satuan besar
     */
    public function barangBesar(): HasMany
    {
        return $this->hasMany(MstBarang::class, 'satuan_besar_id', 'satuan_id');
    }
}
