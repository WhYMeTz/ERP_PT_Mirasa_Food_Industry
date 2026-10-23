<?php

namespace App\Models\Penjualan;

use App\Models\MasterData\MstBarang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatSoReturDtl extends Model
{
    use HasFactory;

    protected $table = 'dat_so_retur_dtl';
    protected $primaryKey = 'retur_dtl_id';

    protected $fillable = [
        'retur_id',
        'sodtl_id',
        'barang_id',
        'batch_no',
        'qty_retur',
        'harga_satuan',
        'subtotal_nilai',
        'kondisi_cd',
        'catatan_txt',
    ];

    protected $casts = [
        'qty_retur'      => 'decimal:4',
        'harga_satuan'   => 'decimal:4',
        'subtotal_nilai' => 'decimal:4',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatSoReturHdr::class, 'retur_id', 'retur_id');
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(DatSoDtl::class, 'sodtl_id', 'sodtl_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }

    public function getKondisiLabelAttribute(): string
    {
        return match ($this->kondisi_cd) {
            'RUSAK_KEMASAN'  => 'Kemasan Rusak / Sobek',
            'BOCOR_KEMPES'   => 'Bocor / Kempes Udara',
            'EXPIRED'        => 'Mendekati Kadaluarsa',
            'CACAT_PRODUKSI' => 'Cacat Mutu / Gosong',
            default          => 'Lainnya / Sortir',
        };
    }
}
