<?php

namespace App\Models\Penjualan;

use App\Models\MasterData\MstBarang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatFakturDtl extends Model
{
    use HasFactory;

    protected $table = 'dat_faktur_dtl';
    protected $primaryKey = 'faktur_dtl_id';

    protected $fillable = [
        'faktur_id',
        'pengeluaran_dtl_id',
        'sodtl_id',
        'barang_id',
        'barang_nm',
        'qty',
        'satuan',
        'harga_satuan',
        'diskon_nominal',
        'subtotal_netto',
        'ppn_nominal',
        'total_nominal',
        'catatan_txt',
    ];

    protected $casts = [
        'qty'            => 'decimal:4',
        'harga_satuan'   => 'decimal:4',
        'diskon_nominal' => 'decimal:4',
        'subtotal_netto' => 'decimal:4',
        'ppn_nominal'    => 'decimal:4',
        'total_nominal'  => 'decimal:4',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatFakturHdr::class, 'faktur_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id');
    }

    public function soDetail(): BelongsTo
    {
        return $this->belongsTo(DatSoDtl::class, 'sodtl_id');
    }
}
