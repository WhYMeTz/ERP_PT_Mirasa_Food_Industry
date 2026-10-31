<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatKemasanSirkularDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_kemasan_sirkular_dtl';
    protected $primaryKey = 'kemasan_sirkular_dtl_id';

    protected $fillable = [
        'kemasan_sirkular_id',
        'barang_asal_id',
        'barang_mulus_id',
        'barang_bmg_id',
        'barang_rusak_id',
        'kirim_qty',
        'kembali_qty',
        'mulus_qty',
        'bmg_qty',
        'rusak_qty',
        'selisih_qty',
        'batch_no_mulus',
        'batch_no_bmg',
        'batch_no_rusak',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'kirim_qty'   => 'decimal:4',
        'kembali_qty' => 'decimal:4',
        'mulus_qty'   => 'decimal:4',
        'bmg_qty'     => 'decimal:4',
        'rusak_qty'   => 'decimal:4',
        'selisih_qty' => 'decimal:4',
        'deleted_st'  => 'boolean',
        'active_st'   => 'boolean',
        'version_no'  => 'integer',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatKemasanSirkularHdr::class, 'kemasan_sirkular_id');
    }

    public function barangAsal(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_asal_id');
    }

    public function barangMulus(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_mulus_id');
    }

    public function barangBmg(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_bmg_id');
    }

    public function barangRusak(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_rusak_id');
    }
}

