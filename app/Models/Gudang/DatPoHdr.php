<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatPoHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_po_hdr';
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'po_no',
        'po_tgl',
        'tgl_estimasi_datang',
        'supplier_id',
        'gudang_id',
        'approver_id',
        'status_cd',
        'approval_token',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'sent_to_supplier_at',
        'sent_to_supplier_by',
        'supplier_email_sent',
        'supplier_email_notes',
        'is_harga_termasuk_pajak',
        'subtotal_bruto',
        'diskon_total',
        'potongan_nominal',
        'dpp_nominal',
        'ppn_nominal',
        'total_nominal',
        'total_tagihan',
        'catatan_txt',
        'remark_txt',
        'term_of_payment',
        'closed_at',
        'closed_by',
        'closed_reason',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'po_tgl'                  => 'date',
        'tgl_estimasi_datang'     => 'date',
        'closed_at'               => 'datetime',
        'approved_at'             => 'datetime',
        'rejected_at'             => 'datetime',
        'sent_to_supplier_at'     => 'datetime',
        'is_harga_termasuk_pajak' => 'boolean',
        'subtotal_bruto'          => 'decimal:4',
        'diskon_total'        => 'decimal:4',
        'potongan_nominal'    => 'decimal:4',
        'dpp_nominal'         => 'decimal:4',
        'ppn_nominal'         => 'decimal:4',
        'total_nominal'       => 'decimal:4',
        'total_tagihan'       => 'decimal:4',
        'deleted_st'          => 'boolean',
        'active_st'           => 'boolean',
    ];

    /**
     * Menghitung total persentase pemenuhan barang PO (%)
     */
    public function getPersentaseTerimaAttribute(): float
    {
        $totalPesan = (float) $this->details->sum('pesan_qty');
        if ($totalPesan <= 0) return 0;
        $totalTerima = (float) $this->details->sum('terima_qty');
        return round(($totalTerima / $totalPesan) * 100, 1);
    }

    /**
     * Menghitung realisasi nominal barang yang sudah benar-benar diterima (Rp)
     */
    public function getRealisasiNominalAttribute(): float
    {
        return (float) $this->details->sum(function ($dtl) {
            return (float) $dtl->terima_qty * (float) $dtl->harga_nominal;
        });
    }

    /**
     * Total kuantitas barang yang masih tersisa belum diterima
     */
    public function getTotalSisaQtyAttribute(): float
    {
        return (float) $this->details->sum(function ($dtl) {
            return (float) $dtl->sisa_qty;
        });
    }

    /**
     * Apakah PO memenuhi syarat untuk ditutup paksa (Force Close)
     */
    public function canBeForceClosed(): bool
    {
        return $this->status_cd === 'PARTIAL' && $this->total_sisa_qty > 0;
    }

    /**
     * Relasi ke detail item pesanan (PO Detail)
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatPoDtl::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke riwayat penerimaan barang fisik yang menggunakan PO ini
     */
    public function penerimaan(): HasMany
    {
        return $this->hasMany(DatTerimaHdr::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MstSupplier::class, 'supplier_id', 'supplier_id');
    }

    /**
     * Relasi ke Gudang tujuan pengiriman
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke User Atasan yang ditugaskan menyetujui PO
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id', 'id');
    }

    /**
     * Cek apakah dokumen PO sudah disetujui resmi
     */
    public function isApproved(): bool
    {
        return $this->status_cd === 'APPROVED';
    }

    /**
     * Cek apakah dokumen PO sedang menunggu persetujuan atasan
     */
    public function isPendingApproval(): bool
    {
        return $this->status_cd === 'PENDING_APPROVAL';
    }

    /**
     * Cek apakah dokumen PO ditolak oleh atasan
     */
    public function isRejected(): bool
    {
        return $this->status_cd === 'REJECTED';
    }

    /**
     * Cek apakah PO sudah pernah dikirimkan ke email supplier
     */
    public function isSentToSupplier(): bool
    {
        return !empty($this->sent_to_supplier_at);
    }
}
