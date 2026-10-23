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

class DatSoReturHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_so_retur_hdr';
    protected $primaryKey = 'retur_id';

    protected $fillable = [
        'retur_no',
        'retur_tgl',
        'so_id',
        'customer_id',
        'gudang_id',
        'tindakan_cd',
        'total_qty',
        'total_nilai',
        'alasan_txt',
        'status_cd',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'retur_tgl'   => 'date',
        'total_qty'   => 'decimal:4',
        'total_nilai' => 'decimal:4',
        'deleted_st'  => 'boolean',
        'active_st'   => 'boolean',
        'version_no'  => 'integer',
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
        return $this->hasMany(DatSoReturDtl::class, 'retur_id', 'retur_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }

    public function getTindakanLabelAttribute(): string
    {
        return match ($this->tindakan_cd) {
            'POTONG_TAGIHAN' => 'Potong Piutang / Tagihan (Credit Note)',
            'TUKAR_GULING'   => 'Tukar Guling / Ganti Barang Fisik',
            default          => $this->tindakan_cd,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_cd) {
            'CONFIRMED' => '<span class="badge" style="background:#dcfce7; color:#166534; font-weight:700;">Terkonfirmasi</span>',
            'VOID'      => '<span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:700;">Dibatalkan (Void)</span>',
            default     => '<span class="badge" style="background:#f1f5f9; color:#475569; font-weight:700;">' . $this->status_cd . '</span>',
        };
    }
}
