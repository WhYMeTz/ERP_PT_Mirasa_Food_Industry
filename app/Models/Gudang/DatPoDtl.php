<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatPoDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_po_dtl';
    protected $primaryKey = 'podtl_id';

    protected $fillable = [
        'po_id',
        'barang_id',
        'pesan_qty',
        'harga_nominal',
        'subtotal_nominal',
        'terima_qty',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'pesan_qty'        => 'decimal:4',
        'harga_nominal'    => 'decimal:4',
        'subtotal_nominal' => 'decimal:4',
        'terima_qty'       => 'decimal:4',
        'deleted_st'       => 'boolean',
        'active_st'        => 'boolean',
    ];

    /**
     * Relasi ke Header PO
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatPoHdr::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }

    /**
     * Relasi ke detail penerimaan fisik barang
     */
    public function terimaDetails(): HasMany
    {
        return $this->hasMany(DatTerimaDtl::class, 'podtl_id', 'podtl_id');
    }

    /**
     * Sisa kuantitas yang belum diterima
     */
    public function getSisaQtyAttribute(): float
    {
        return max(0, (float) $this->pesan_qty - (float) $this->terima_qty);
    }
}
