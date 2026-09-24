<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatPoHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_po_hdr';
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'po_no',
        'po_tgl',
        'supplier_id',
        'gudang_id',
        'status_cd',
        'total_nominal',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'po_tgl'        => 'date',
        'total_nominal' => 'decimal:4',
        'deleted_st'    => 'boolean',
        'active_st'     => 'boolean',
    ];

    /**
     * Relasi ke detail item pesanan (PO Detail)
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatPoDtl::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke riwayat penerimaan barang fisik yang menggunakan PO ini
     */
    public function penerimaan(): HasMany
    {
        return $this->hasMany(DatTerimaHdr::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MstSupplier::class, 'supplier_id', 'supplier_id');
    }

    /**
     * Relasi ke Gudang tujuan pengiriman
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }
}
