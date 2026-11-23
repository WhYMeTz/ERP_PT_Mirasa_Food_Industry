<?php

namespace App\Models\Keuangan;

use App\Models\Penjualan\DatFakturHdr;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatBayarPiutangDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_bayar_piutang_dtl';
    protected $primaryKey = 'bayardt_id';

    protected $fillable = [
        'bayar_id',
        'faktur_id',
        'nominal_bayar',
        'sisa_piutang_sebelum',
        'sisa_piutang_sesudah',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'nominal_bayar'       => 'decimal:4',
        'sisa_piutang_sebelum' => 'decimal:4',
        'sisa_piutang_sesudah' => 'decimal:4',
        'deleted_st'          => 'boolean',
        'active_st'           => 'boolean',
        'version_no'          => 'integer',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatBayarPiutangHdr::class, 'bayar_id', 'bayar_id');
    }

    public function faktur(): BelongsTo
    {
        return $this->belongsTo(DatFakturHdr::class, 'faktur_id', 'faktur_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }
}
