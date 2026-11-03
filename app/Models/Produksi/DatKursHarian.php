<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatKursHarian extends Model
{
    use HasFactory;

    protected $table = 'dat_kurs_harian';
    protected $primaryKey = 'kurs_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'tanggal',
        'mata_uang',
        'kurs_tengah',
        'sumber',
        'catatan',
        'created_by',
        'created_dt',
        'updated_by',
        'updated_dt',
        'active_st',
        'deleted_st',
        'version_no',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'kurs_tengah' => 'float',
        'active_st'   => 'boolean',
        'deleted_st'  => 'boolean',
        'version_no'  => 'integer',
    ];

    /**
     * Scope untuk data aktif dan tidak terhapus
     */
    public function scopeActive($query)
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }
}

