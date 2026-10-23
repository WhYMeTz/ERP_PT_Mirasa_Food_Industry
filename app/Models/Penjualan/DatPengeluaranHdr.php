<?php

namespace App\Models\Penjualan;

use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstGudang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatPengeluaranHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_pengeluaran_hdr';
    protected $primaryKey = 'pengeluaran_id';

    protected $fillable = [
        'pengeluaran_no',
        'pengeluaran_tgl',
        'so_id',
        'customer_id',
        'gudang_id',
        'supir_nm',
        'nopol_kendaraan',
        'ekspedisi_nm',
        'total_qty',
        'status_cd',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'pengeluaran_tgl' => 'date',
        'total_qty'       => 'decimal:4',
        'deleted_st'      => 'boolean',
        'active_st'       => 'boolean',
        'version_no'      => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(DatSoHdr::class, 'so_id', 'so_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MstCustomer::class, 'customer_id', 'customer_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatPengeluaranDtl::class, 'pengeluaran_id', 'pengeluaran_id');
    }

    public function faktur(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DatFakturHdr::class, 'pengeluaran_id', 'pengeluaran_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }
}
