<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstMesin extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_mesin';
    protected $primaryKey = 'mesin_id';

    protected $fillable = [
        'mesin_cd',
        'mesin_nm',
        'kategori_mesin',
        'jenis_proses',
        'kapasitas_per_jam',
        'satuan_kapasitas',
        'tipe_energi',
        'status_kondisi',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'kapasitas_per_jam' => 'float',
        'deleted_st'        => 'boolean',
        'active_st'         => 'boolean',
    ];

    /**
     * Scope query untuk filter pencarian berdasarkan kode, nama, jenis proses, atau keterangan.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('mesin_cd', 'ILIKE', "%{$term}%")
              ->orWhere('mesin_nm', 'ILIKE', "%{$term}%")
              ->orWhere('jenis_proses', 'ILIKE', "%{$term}%")
              ->orWhere('tipe_energi', 'ILIKE', "%{$term}%")
              ->orWhere('keterangan', 'ILIKE', "%{$term}%");
        });
    }

    /**
     * Scope query untuk filter kategori mesin (PRODUKSI / PACKAGING).
     */
    public function scopeKategori($query, ?string $kategori)
    {
        if (empty($kategori) || strtoupper($kategori) === 'SEMUA') {
            return $query;
        }

        return $query->where('kategori_mesin', strtoupper($kategori));
    }

    /**
     * Scope mesin kategori produksi.
     */
    public function scopeProduksi($query)
    {
        return $query->where('kategori_mesin', 'PRODUKSI');
    }

    /**
     * Scope mesin kategori packaging.
     */
    public function scopePackaging($query)
    {
        return $query->where('kategori_mesin', 'PACKAGING');
    }

    /**
     * Scope mesin dengan kondisi siap operasi.
     */
    public function scopeSiap($query)
    {
        return $query->where('status_kondisi', 'SIAP');
    }

    /**
     * Label badge HTML untuk Kategori Mesin.
     */
    public function getKategoriBadgeAttribute(): string
    {
        if ($this->kategori_mesin === 'PRODUKSI') {
            return '<span class="badge-cat-produksi">PRODUKSI / OLAH</span>';
        }
        return '<span class="badge-cat-packaging">PACKAGING / KEMAS</span>';
    }

    /**
     * Label badge HTML untuk Status Kondisi Mesin.
     */
    public function getStatusKondisiBadgeAttribute(): string
    {
        return match ($this->status_kondisi) {
            'SIAP'        => '<span class="badge-kondisi-siap">SIAP OPERASI</span>',
            'MAINTENANCE' => '<span class="badge-kondisi-maintenance">MAINTENANCE</span>',
            'RUSAK'       => '<span class="badge-kondisi-rusak">RUSAK / PERBAIKAN</span>',
            default       => '<span class="badge-kondisi-default">' . e($this->status_kondisi) . '</span>',
        };
    }
}
