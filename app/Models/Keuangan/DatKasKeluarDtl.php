<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatKasKeluarDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_kas_keluar_dtl';
    protected $primaryKey = 'bkkdt_id';

    protected $fillable = [
        'bkk_id',
        'coa_id',
        'uraian',
        'qty',
        'satuan',
        'harga_satuan',
        'nominal',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'qty'          => 'float',
        'harga_satuan' => 'float',
        'nominal'      => 'float',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatKasKeluarHdr::class, 'bkk_id', 'bkk_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'coa_id', 'coa_id');
    }
}

