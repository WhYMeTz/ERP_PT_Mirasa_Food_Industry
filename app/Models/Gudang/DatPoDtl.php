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
        'diskon_persen',
        'diskon_nominal',
        'potongan_nominal',
        'harga_netto',
        'subtotal_netto',
        'pajak_id',
        'is_harga_termasuk_pajak',
        'ppn_tipe',
        'ppn_persen',
        'ppn_nominal',
        'subtotal_nominal',
        'subtotal_tagihan',
        'terima_qty',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'pajak_id'                => 'integer',
        'is_harga_termasuk_pajak' => 'boolean',
        'pesan_qty'               => 'decimal:4',
        'harga_nominal'           => 'decimal:4',
        'diskon_persen'           => 'decimal:2',
        'diskon_nominal'          => 'decimal:4',
        'potongan_nominal'        => 'decimal:4',
        'harga_netto'             => 'decimal:4',
        'subtotal_netto'          => 'decimal:4',
        'ppn_persen'              => 'decimal:2',
        'ppn_nominal'             => 'decimal:4',
        'subtotal_nominal'        => 'decimal:4',
        'subtotal_tagihan'        => 'decimal:4',
        'terima_qty'              => 'decimal:4',
        'deleted_st'              => 'boolean',
        'active_st'               => 'boolean',
    ];

    /**
     * Relasi ke Master Pajak
     */
    public function pajak(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterData\MstPajak::class, 'pajak_id', 'pajak_id');
    }

    /**
     * Relasi ke Header PO
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatPoHdr::class, 'po_id', 'po_id');
    }

    /**
     * Alias relasi ke Header PO
     */
    public function po(): BelongsTo
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
     * Relasi ke detail pengujian mutu QC
     */
    public function qcDetails(): HasMany
    {
        return $this->hasMany(DatQcInboundDtl::class, 'podtl_id', 'podtl_id')
            ->where('deleted_st', false);
    }

    /**
     * Total kuantitas yang telah diuji oleh QC (baik lolos, siap gudang, maupun diterima)
     */
    public function getQcTestedQtyAttribute(): float
    {
        if ($this->relationLoaded('qcDetails')) {
            return (float) $this->qcDetails
                ->filter(function ($qd) {
                    return $qd->header && !$qd->header->deleted_st && $qd->header->status_qc !== 'DITOLAK_TOTAL';
                })
                ->sum('qty_timbang_gross');
        }

        return (float) DatQcInboundDtl::where('podtl_id', $this->podtl_id)
            ->where('deleted_st', false)
            ->whereHas('header', function ($q) {
                $q->where('deleted_st', false)->where('status_qc', '!=', 'DITOLAK_TOTAL');
            })
            ->sum('qty_timbang_gross');
    }

    /**
     * Sisa kuantitas PO yang belum diuji oleh QC
     */
    public function getSisaBelumQcQtyAttribute(): float
    {
        $tested = max($this->qc_tested_qty, (float) $this->terima_qty);
        return max(0, (float) $this->pesan_qty - $tested);
    }

    /**
     * Status pengujian QC: SELESAI, SEBAGIAN, atau BELUM
     */
    public function getQcStatusLabelAttribute(): string
    {
        if ($this->sisa_belum_qc_qty <= 0 || (float) $this->terima_qty >= (float) $this->pesan_qty) {
            return 'SELESAI';
        }
        if ($this->qc_tested_qty > 0) {
            return 'SEBAGIAN';
        }
        return 'BELUM';
    }

    /**
     * Sisa kuantitas yang belum diterima
     */
    public function getSisaQtyAttribute(): float
    {
        return max(0, (float) $this->pesan_qty - (float) $this->terima_qty);
    }
}
