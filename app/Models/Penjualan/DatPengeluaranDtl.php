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

    /**
     * Dapatkan tanggal expired dari batch terkait
     */
    public function getExpiredTglAttribute(): ?string
    {
        if (empty($this->batch_no)) {
            return null;
        }

        return \App\Models\Gudang\DatStokBatch::where('batch_no', $this->batch_no)
            ->where('barang_id', $this->barang_id)
            ->value('expired_tgl')
            ?? \App\Models\Produksi\DatPackagingHdr::where('batch_no', $this->batch_no)
                ->where('barang_jadi_id', $this->barang_id)
                ->value('expired_tgl');
    }

    /**
     * Dapatkan tanggal produksi/kemas dari batch terkait
     */
    public function getPackTglAttribute(): ?string
    {
        if (empty($this->batch_no)) {
            return null;
        }

        return \App\Models\Produksi\DatPackagingHdr::where('batch_no', $this->batch_no)
            ->where('barang_jadi_id', $this->barang_id)
            ->value('pack_tgl');
    }
}
