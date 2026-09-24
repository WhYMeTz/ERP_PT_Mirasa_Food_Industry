<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatStokBatch extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_stok_batch';
    protected $primaryKey = 'stok_id';

    protected $fillable = [
        'gudang_id',
        'barang_id',
        'batch_no',
        'expired_tgl',
        'qty_awal',
        'harga_satuan',
        'sisa_qty',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'expired_tgl'  => 'date',
        'qty_awal'     => 'decimal:4',
        'harga_satuan' => 'decimal:4',
        'sisa_qty'     => 'decimal:4',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
    ];

    /**
     * Hitung Qty Keluar = Qty Awal - Sisa Qty
     */
    public function getQtyKeluarAttribute(): float
    {
        $keluar = (float) $this->qty_awal - (float) $this->sisa_qty;
        return max(0, $keluar);
    }

    /**
     * Hitung Sisa Nilai = Sisa Qty * Harga Satuan
     */
    public function getSisaNilaiAttribute(): float
    {
        return (float) $this->sisa_qty * (float) $this->harga_satuan;
    }

    /**
     * Status ketersediaan stok batch: 'Tersedia' atau 'Habis'
     */
    public function getStatusStokAttribute(): string
    {
        return (float) $this->sisa_qty > 0 ? 'Tersedia' : 'Habis';
    }

    /**
     * Relasi ke Gudang
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
