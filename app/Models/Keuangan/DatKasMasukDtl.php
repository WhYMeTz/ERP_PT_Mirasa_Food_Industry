<?php

namespace App\Models\Keuangan;

use App\Models\Penjualan\DatSoHdr;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatKasMasukDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_kas_masuk_dtl';
    protected $primaryKey = 'bkmdt_id';

    protected $fillable = [
        'bkm_id',
        'so_id',
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
        return $this->belongsTo(DatKasMasukHdr::class, 'bkm_id', 'bkm_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(DatSoHdr::class, 'so_id', 'so_id');
    }
}
