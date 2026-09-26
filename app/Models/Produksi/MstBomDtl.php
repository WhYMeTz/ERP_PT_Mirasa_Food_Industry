<?php

namespace App\Models\Produksi;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MstBomDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_bom_dtl';
    protected $primaryKey = 'bomdtl_id';

    protected $fillable = [
        'bom_id',
        'barang_mentah_id',
        'kebutuhan_qty',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'kebutuhan_qty' => 'decimal:4',
        'deleted_st'    => 'boolean',
        'active_st'     => 'boolean',
    ];

    /**
     * Relasi ke Header BOM
     */
    public function bom(): BelongsTo
    {
        return $this->belongsTo(MstBomHdr::class, 'bom_id', 'bom_id');
    }

    /**
     * Relasi ke Master Barang Mentah / Bahan Baku
     */
    public function barangMentah(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_mentah_id', 'barang_id');
    }
}
