<?php

namespace App\Models\Keuangan;

use App\Models\MasterData\MstCustomer;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DatBayarPiutangHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_bayar_piutang_hdr';
    protected $primaryKey = 'bayar_id';

    protected $fillable = [
        'bkm_no',
        'tgl_bayar',
        'customer_id',
        'kas_bank_id',
        'metode_bayar',
        'no_referensi_bank',
        'total_bayar',
        'diterima_dari',
        'catatan',
        'status_cd',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'tgl_bayar'   => 'date',
        'total_bayar' => 'decimal:4',
        'deleted_st'  => 'boolean',
        'active_st'   => 'boolean',
        'version_no'  => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MstCustomer::class, 'customer_id', 'customer_id');
    }

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(MstKasBank::class, 'kas_bank_id', 'kas_bank_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatBayarPiutangDtl::class, 'bayar_id', 'bayar_id');
    }

    public function jurnal(): HasOne
    {
        return $this->hasOne(DatJurnalHdr::class, 'ref_no', 'bkm_no')
            ->where('tipe_transaksi', 'BAYAR_PIUTANG')
            ->where('deleted_st', false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }
}
