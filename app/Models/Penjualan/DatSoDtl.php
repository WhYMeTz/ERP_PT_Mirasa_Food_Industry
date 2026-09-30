<?php

namespace App\Models\Penjualan;

use App\Models\MasterData\MstBarang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatSoDtl extends Model
{
    use HasFactory;

    protected $table = 'dat_so_dtl';
    protected $primaryKey = 'sodtl_id';

    protected $fillable = [
        'so_id',
        'barang_id',
        'pesan_qty',
        'kirim_qty',
        'harga_satuan',
        'diskon_persen',
        'diskon_nominal',
        'potongan_nominal',
        'harga_netto',
        'subtotal_netto',
        'ppn_tipe',
        'ppn_persen',
        'ppn_nominal',
        'subtotal_tagihan',
        'catatan_txt',
    ];

    protected $casts = [
        'pesan_qty'        => 'decimal:4',
        'kirim_qty'        => 'decimal:4',
        'harga_satuan'     => 'decimal:4',
        'diskon_persen'    => 'decimal:2',
        'diskon_nominal'   => 'decimal:4',
        'potongan_nominal' => 'decimal:4',
        'harga_netto'      => 'decimal:4',
        'subtotal_netto'   => 'decimal:4',
        'ppn_persen'       => 'decimal:2',
        'ppn_nominal'      => 'decimal:4',
        'subtotal_tagihan' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(DatSoHdr::class, 'so_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id');
    }

    public function getSisaQtyAttribute(): float
    {
        return max(0, (float) $this->pesan_qty - (float) $this->kirim_qty);
    }
}
