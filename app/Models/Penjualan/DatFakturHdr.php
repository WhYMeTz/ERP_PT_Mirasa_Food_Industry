<?php

namespace App\Models\Penjualan;

use App\Models\MasterData\MstCustomer;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatFakturHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_faktur_hdr';
    protected $primaryKey = 'faktur_id';

    protected $fillable = [
        'faktur_no',
        'faktur_tgl',
        'tgl_jatuh_tempo',
        'pengeluaran_id',
        'so_id',
        'customer_id',
        'customer_po_no',
        'top_txt',
        'ppn_tipe',
        'ppn_persen',
        'subtotal_bruto',
        'total_diskon',
        'total_potongan',
        'dpp_nominal',
        'ppn_nominal',
        'total_tagihan',
        'total_dibayar',
        'sisa_piutang',
        'status_pembayaran',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'faktur_tgl'        => 'date',
        'tgl_jatuh_tempo'   => 'date',
        'ppn_persen'        => 'decimal:2',
        'subtotal_bruto'    => 'decimal:4',
        'total_diskon'      => 'decimal:4',
        'total_potongan'    => 'decimal:4',
        'dpp_nominal'       => 'decimal:4',
        'ppn_nominal'       => 'decimal:4',
        'total_tagihan'     => 'decimal:4',
        'total_dibayar'     => 'decimal:4',
        'sisa_piutang'      => 'decimal:4',
        'deleted_st'        => 'boolean',
        'active_st'         => 'boolean',
        'version_no'        => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MstCustomer::class, 'customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(DatSoHdr::class, 'so_id');
    }

    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(DatPengeluaranHdr::class, 'pengeluaran_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatFakturDtl::class, 'faktur_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }
}
