<?php

namespace App\Models\Penjualan;

use App\Models\MasterData\MstBarang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatPengeluaranDtl extends Model
{
    use HasFactory;

    protected $table = 'dat_pengeluaran_dtl';
    protected $primaryKey = 'pengeluaran_dtl_id';

    protected $fillable = [
        'pengeluaran_id',
        'sodtl_id',
        'barang_id',
        'batch_no',
        'kirim_qty',
        'catatan_txt',
    ];

    protected $casts = [
        'kirim_qty' => 'decimal:4',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatPengeluaranHdr::class, 'pengeluaran_id', 'pengeluaran_id');
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(DatSoDtl::class, 'sodtl_id', 'sodtl_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
