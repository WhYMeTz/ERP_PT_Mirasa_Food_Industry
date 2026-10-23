<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatJurnalDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_jurnal_dtl';
    protected $primaryKey = 'jurnaldtl_id';

    protected $fillable = [
        'jurnal_id',
        'coa_id',
        'debet',
        'kredit',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'debet'      => 'float',
        'kredit'     => 'float',
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatJurnalHdr::class, 'jurnal_id', 'jurnal_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'coa_id', 'coa_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }
}
