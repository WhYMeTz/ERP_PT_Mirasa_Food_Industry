<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatReturDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_retur_dtl';
    protected $primaryKey = 'returdtl_id';

    protected $fillable = [
        'retur_id',
        'barang_id',
        'podtl_id',
        'batch_no',
        'retur_qty',
        'harga_satuan',
        'subtotal_nominal',
        'alasan_reject',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'retur_qty'        => 'decimal:4',
        'harga_satuan'     => 'decimal:4',
        'subtotal_nominal' => 'decimal:4',
        'deleted_st'       => 'boolean',
        'active_st'        => 'boolean',
    ];

    /**
     * Relasi ke Header Retur
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatReturHdr::class, 'retur_id', 'retur_id');
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }

    /**
     * Relasi ke Detail PO asal (jika ada)
     */
    public function poDetail(): BelongsTo
    {
        return $this->belongsTo(DatPoDtl::class, 'podtl_id', 'podtl_id');
    }
}
