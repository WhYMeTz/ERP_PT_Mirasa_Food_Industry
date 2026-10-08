<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatAdjustmentDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_adjustment_dtl';
    protected $primaryKey = 'adjdtl_id';

    protected $fillable = [
        'adj_id',
        'barang_id',
        'batch_no',
        'stok_sistem_qty',
        'harga_satuan',
        'total_sistem_nilai',
        'stok_fisik_qty',
        'total_fisik_nilai',
        'selisih_qty',
        'total_selisih_nilai',
        'alasan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'stok_sistem_qty'     => 'decimal:4',
        'harga_satuan'        => 'decimal:4',
        'total_sistem_nilai'  => 'decimal:4',
        'stok_fisik_qty'      => 'decimal:4',
        'total_fisik_nilai'   => 'decimal:4',
        'selisih_qty'         => 'decimal:4',
        'total_selisih_nilai' => 'decimal:4',
        'deleted_st'          => 'boolean',
        'active_st'           => 'boolean',
        'version_no'          => 'integer',
    ];

    /**
     * Relasi ke Header Penyesuaian
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatAdjustmentHdr::class, 'adj_id', 'adj_id');
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
