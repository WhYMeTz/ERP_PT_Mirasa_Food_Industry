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
        'qty_masuk_persediaan',
        'qty_dimusnahkan',
        'perlakuan_cd',
        'harga_satuan',
        'subtotal_nilai',
        'kondisi_cd',
        'catatan_txt',
    ];

    protected $casts = [
        'qty_retur'            => 'decimal:4',
        'qty_masuk_persediaan' => 'decimal:4',
        'qty_dimusnahkan'      => 'decimal:4',
        'harga_satuan'         => 'decimal:4',
        'subtotal_nilai'       => 'decimal:4',
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

    public function getProductionBatchNoAttribute(): ?string
    {
        if (!empty($this->batch_no) && !str_starts_with($this->batch_no, 'RET-')) {
            return $this->batch_no;
        }

        $so = $this->header?->order;
        if ($so) {
            $shippedBatch = \App\Models\Penjualan\DatPengeluaranDtl::whereHas('header', function ($q) use ($so) {
                $q->where('so_id', $so->so_id)->where('status_cd', '!=', 'BATAL');
            })
            ->where('barang_id', $this->barang_id)
            ->whereNotNull('batch_no')
            ->where('batch_no', '!=', '')
            ->value('batch_no');

            if ($shippedBatch) {
                return $shippedBatch;
            }
        }

        return !empty($this->batch_no) && !str_starts_with($this->batch_no, 'RET-') ? $this->batch_no : '-';
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

    public function getPerlakuanLabelAttribute(): string
    {
        return match ($this->perlakuan_cd) {
            'DIMUSNAHKAN'      => 'Dimusnahkan (Scrap)',
            'CAMPUR'           => 'Pecah (Sebagian Masuk & Sebagian Musnah)',
            default            => 'Masuk Persediaan (Sortir KW 2)',
        };
    }
}
