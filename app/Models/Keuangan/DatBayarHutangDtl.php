<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatBayarHutangDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_bayar_hutang_dtl';
    protected $primaryKey = 'bayardt_id';

    protected $fillable = [
        'bayar_id',
        'hutang_id',
        'nominal_bayar',
        'sisa_hutang_sebelum',
        'sisa_hutang_sesudah',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'nominal_bayar'       => 'float',
        'sisa_hutang_sebelum' => 'float',
        'sisa_hutang_sesudah' => 'float',
        'deleted_st'          => 'boolean',
        'active_st'           => 'boolean',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatBayarHutangHdr::class, 'bayar_id', 'bayar_id');
    }

    public function hutang(): BelongsTo
    {
        return $this->belongsTo(DatHutangHdr::class, 'hutang_id', 'hutang_id');
    }
}
