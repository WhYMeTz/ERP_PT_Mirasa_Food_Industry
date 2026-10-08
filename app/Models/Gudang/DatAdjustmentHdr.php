<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatAdjustmentHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_adjustment_hdr';
    protected $primaryKey = 'adj_id';

    protected $fillable = [
        'adj_no',
        'adj_tgl',
        'gudang_id',
        'kategori_adj',
        'catatan_txt',
        'total_item',
        'total_selisih_qty',
        'total_selisih_nilai',
        'status_cd',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'adj_tgl'             => 'date',
        'total_item'          => 'integer',
        'total_selisih_qty'   => 'decimal:4',
        'total_selisih_nilai' => 'decimal:4',
        'deleted_st'          => 'boolean',
        'active_st'           => 'boolean',
        'version_no'          => 'integer',
    ];

    /**
     * Relasi ke Gudang Asal
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke Rincian Barang Penyesuaian
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatAdjustmentDtl::class, 'adj_id', 'adj_id');
    }

    /**
     * Scope untuk penyesuaian yang aktif dan belum dihapus
     */
    public function scopeActive($query)
    {
        return $query->where('deleted_st', false);
    }
}
