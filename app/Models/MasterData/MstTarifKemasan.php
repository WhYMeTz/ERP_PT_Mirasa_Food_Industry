<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstTarifKemasan extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_tarif_kemasan';
    protected $primaryKey = 'tarif_kemasan_id';

    protected $fillable = [
        'kategori_bahan',
        'kode_tarif',
        'nama_tarif',
        'satuan_basis',
        'satuan_label',
        'nilai_tarif',
        'nilai_tarif_sekunder',
        'satuan_sekunder_label',
        'berlaku_sejak',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'nilai_tarif'          => 'float',
        'nilai_tarif_sekunder' => 'float',
        'berlaku_sejak'        => 'date',
        'deleted_st'           => 'boolean',
        'active_st'            => 'boolean',
        'version_no'           => 'integer',
    ];

    /**
     * Scope data aktif (belum dihapus)
     */
    public function scopeActive($query)
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }

    /**
     * Scope filter berdasarkan kategori bahan
     */
    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori_bahan', strtoupper($kategori));
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
              ->orWhere('kategori_bahan', 'ILIKE', "%{$term}%");
        });
    }
}
