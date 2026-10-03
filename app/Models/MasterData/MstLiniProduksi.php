<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstLiniProduksi extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_lini_produksi';
    protected $primaryKey = 'lini_id';

    protected $fillable = [
        'lini_cd',
        'lini_nm',
        'tipe_batch',
        'keterangan',
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
     * Scope query untuk filter pencarian.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('lini_cd', 'ILIKE', "%{$term}%")
              ->orWhere('lini_nm', 'ILIKE', "%{$term}%")
              ->orWhere('keterangan', 'ILIKE', "%{$term}%");
        });
    }
}
