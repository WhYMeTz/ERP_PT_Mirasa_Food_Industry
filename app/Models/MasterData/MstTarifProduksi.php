<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstTarifProduksi extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_tarif_produksi';
    protected $primaryKey = 'tarif_id';

    protected $fillable = [
        'kategori',
        'kode_tarif',
        'nama_tarif',
        'satuan_basis',
        'satuan_label',
        'nilai_tarif',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'nilai_tarif' => 'float',
        'deleted_st'  => 'boolean',
        'active_st'   => 'boolean',
        'version_no'  => 'integer',
    ];

    /**
     * Scope filter berdasarkan kategori
     */
    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', strtoupper($kategori));
    }

    /**
     * Scope pencarian cepat
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('kode_tarif', 'ILIKE', "%{$term}%")
              ->orWhere('nama_tarif', 'ILIKE', "%{$term}%")
              ->orWhere('kategori', 'ILIKE', "%{$term}%");
        });
    }
}
