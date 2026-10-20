<?php

namespace App\Models\Keuangan;

use App\Models\MasterData\MstSupplier;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatBayarHutangHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_bayar_hutang_hdr';
    protected $primaryKey = 'bayar_id';

    protected $fillable = [
        'bayar_no',
        'tgl_bayar',
        'supplier_id',
        'kas_bank_id',
        'metode_bayar',
        'no_referensi_bank',
        'total_bayar',
        'penerima_nama',
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
        'total_bayar' => 'float',
        'deleted_st'  => 'boolean',
        'active_st'   => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MstSupplier::class, 'supplier_id', 'supplier_id');
    }

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(MstKasBank::class, 'kas_bank_id', 'kas_bank_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatBayarHutangDtl::class, 'bayar_id', 'bayar_id');
    }
}
