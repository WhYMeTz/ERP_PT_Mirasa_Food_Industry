<?php

namespace App\Models\Penjualan;

use App\Models\MasterData\MstCustomer;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatSoHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_so_hdr';
    protected $primaryKey = 'so_id';

    protected $fillable = [
        'so_no',
        'faktur_no',
        'surat_jalan_no',
        'so_tgl',
        'customer_id',
        'customer_po_no',
        'tgl_kirim_estimasi',
        'status_cd',
        'catatan_txt',
        'subtotal_bruto',
        'diskon_total',
        'potongan_nominal',
        'dpp_nominal',
        'ppn_tipe',
        'ppn_persen',
        'ppn_nominal',
        'total_tagihan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'so_tgl'             => 'date',
        'tgl_kirim_estimasi' => 'date',
        'subtotal_bruto'     => 'decimal:4',
        'diskon_total'       => 'decimal:4',
        'potongan_nominal'   => 'decimal:4',
        'dpp_nominal'        => 'decimal:4',
        'ppn_persen'         => 'decimal:2',
        'ppn_nominal'        => 'decimal:4',
        'total_tagihan'      => 'decimal:4',
        'deleted_st'         => 'boolean',
        'active_st'          => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MstCustomer::class, 'customer_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatSoDtl::class, 'so_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }

    public function getTotalPesanQtyAttribute(): float
    {
        return (float) $this->details->sum('pesan_qty');
    }

    public function getTotalKirimQtyAttribute(): float
    {
        return (float) $this->details->sum('kirim_qty');
    }

    public function getTotalSisaQtyAttribute(): float
    {
        return (float) $this->details->sum(function ($dtl) {
            return max(0, (float) $dtl->pesan_qty - (float) $dtl->kirim_qty);
        });
    }

    public function getPersentaseKirimAttribute(): float
    {
        $pesan = $this->total_pesan_qty;
        if ($pesan <= 0) return 0;
        return round(($this->total_kirim_qty / $pesan) * 100, 1);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_cd) {
            'APPROVED'   => '<span class="badge" style="background:#dcfce7; color:#166534; font-weight:700;">Pesanan Disetujui</span>',
            'PROCESSING' => '<span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700;">Dalam Proses</span>',
            'PARTIAL'    => '<span class="badge" style="background:#fef3c7; color:#92400e; font-weight:700;">Terkirim Sebagian</span>',
            'COMPLETED'  => '<span class="badge" style="background:#f1f5f9; color:#475569; font-weight:700;">Selesai Terkirim</span>',
            'CANCELLED'  => '<span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:700;">Dibatalkan</span>',
            default      => '<span class="badge" style="background:#f1f5f9; color:#475569; font-weight:700;">' . $this->status_cd . '</span>',
        };
    }
}
