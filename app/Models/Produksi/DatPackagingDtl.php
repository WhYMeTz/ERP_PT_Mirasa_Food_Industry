<?php

namespace App\Models\Produksi;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatPackagingDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_packaging_dtl';
    protected $primaryKey = 'packdtl_id';

    protected $fillable = [
        'pack_id',
        'kategori_bahan',
        'barang_id',
        'batch_no',
        'qty_standar',
        'qty_aktual',
        'satuan_cd',
        'harga_satuan',
        'subtotal_nilai',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'qty_standar'    => 'decimal:4',
        'qty_aktual'     => 'decimal:4',
        'harga_satuan'   => 'decimal:4',
        'subtotal_nilai' => 'decimal:4',
        'deleted_st'     => 'boolean',
        'active_st'      => 'boolean',
        'version_no'     => 'integer',
    ];

    /**
     * Relasi ke Header Dokumen Pengemasan
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatPackagingHdr::class, 'pack_id', 'pack_id');
    }

    /**
     * Relasi ke Master Barang Komoditas Bahan yang Dipotong
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }

    /**
     * Scope data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('deleted_st', false);
    }
}
