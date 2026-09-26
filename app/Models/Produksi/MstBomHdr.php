<?php

namespace App\Models\Produksi;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstBomHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_bom_hdr';
    protected $primaryKey = 'bom_id';

    protected $fillable = [
        'bom_no',
        'bom_nm',
        'barang_jadi_id',
        'batch_ukuran_qty',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'batch_ukuran_qty' => 'decimal:4',
        'deleted_st'       => 'boolean',
        'active_st'        => 'boolean',
    ];

    /**
     * Relasi ke Master Barang Jadi / Output Produk
     */
    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_jadi_id', 'barang_id');
    }

    /**
     * Relasi ke Rincian Bahan Baku (BOM Detail)
     */
    public function details(): HasMany
    {
        return $this->hasMany(MstBomDtl::class, 'bom_id', 'bom_id')
            ->where('deleted_st', false);
    }
}
