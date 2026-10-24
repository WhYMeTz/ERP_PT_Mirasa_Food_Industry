<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatJurnalHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_jurnal_hdr';
    protected $primaryKey = 'jurnal_id';

    protected $fillable = [
        'jurnal_no',
        'tgl_jurnal',
        'tipe_transaksi',
        'ref_no',
        'ref_id',
        'keterangan',
        'total_debet',
        'total_kredit',
        'is_balanced',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'tgl_jurnal'   => 'date',
        'total_debet'  => 'float',
        'total_kredit' => 'float',
        'is_balanced'  => 'boolean',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(DatJurnalDtl::class, 'jurnal_id', 'jurnal_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }
}
