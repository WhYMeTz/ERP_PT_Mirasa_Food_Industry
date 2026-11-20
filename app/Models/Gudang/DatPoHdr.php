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
        'is_ada_revisi',
        'total_revisi_count',
        'terakhir_direvisi_at',
        'terakhir_direvisi_by',
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
        'is_ada_revisi'           => 'boolean',
        'total_revisi_count'      => 'integer',
        'terakhir_direvisi_at'    => 'datetime',
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

    protected $appends = [
        'qc_status_info',
        'creator_name',
    ];

    /**
     * Nama pengguna pemohon / staf yang membuat dokumen PO
     */
    public function getCreatorNameAttribute(): string
    {
        if (empty($this->created_by)) {
            return 'Tim Gudang';
        }

        if (is_numeric($this->created_by)) {
            static $userCache = null;
            if ($userCache === null) {
                $userCache = \App\Models\User::pluck('name', 'id')->toArray();
            }
            return $userCache[(int) $this->created_by] ?? ('User #' . $this->created_by);
        }

        return (string) $this->created_by;
    }

    /**
     * Ringkasan & status hasil uji inspeksi mutu (QC Inbound) untuk dokumen PO ini
     */
    public function getQcStatusInfoAttribute(): array
    {
        $qcList = $this->relationLoaded('qcInbound') ? $this->qcInbound : $this->qcInbound()->get();

        if ($qcList->isEmpty()) {
            return [
                'tested'         => false,
                'status'         => 'BELUM_DIUJI',
                'label'          => 'Belum Diuji QC',
                'short_label'    => 'Belum Diuji',
                'badge_class'    => 'badge-secondary',
                'color'          => '#64748b',
                'bg'             => '#f8fafc',
                'border'         => '#cbd5e1',
                'icon'           => '🔬',
                'count'          => 0,
                'summary'        => 'Belum ada sampling / inspeksi kedatangan oleh tim QC Lapangan',
                'total_gross'    => 0,
                'total_netto'    => 0,
                'total_refraksi' => 0,
                'total_reject'   => 0,
            ];
        }

        $count = $qcList->count();
        $totalGross = 0;
        $totalNetto = 0;
        $totalRefraksi = 0;
        $totalReject = 0;
        $hasDraft = false;
        $hasRejectPartial = false;
        $allRejectTotal = true;

        foreach ($qcList as $qc) {
            $st = strtoupper((string) $qc->status_qc);
            if ($st === 'DRAFT') {
                $hasDraft = true;
            }

            if ($st === 'DITOLAK_TOTAL' || $st === 'DITOLAK') {
                // ticket rejected
            } else {
                $allRejectTotal = false;
            }

            if ($st === 'DITERIMA_PARSIAL') {
                $hasRejectPartial = true;
            }

            $details = $qc->relationLoaded('details') ? $qc->details : $qc->details()->get();

            foreach ($details as $dtl) {
                $gross = (float) $dtl->qty_timbang_gross;
                $netto = (float) $dtl->qty_netto_lolos;
                $ref = (float) $dtl->qty_refraksi;
                $rej = (float) $dtl->qty_reject;

                $totalGross += $gross;
                $totalNetto += $netto;
                $totalRefraksi += $ref;
                $totalReject += $rej;

                if ($rej > 0 || $ref > 0 || in_array($dtl->keputusan_qc, ['REJECT_PARTIAL', 'REJECT_TOTAL'])) {
                    $hasRejectPartial = true;
                }
            }
        }

        if ($allRejectTotal && $count > 0) {
            return [
                'tested'         => true,
                'status'         => 'DITOLAK',
                'label'          => 'Ditolak QC (Reject Total)',
                'short_label'    => 'Ditolak QC',
                'badge_class'    => 'badge-danger',
                'color'          => '#b91c1c',
                'bg'             => '#fef2f2',
                'border'         => '#fecaca',
                'icon'           => '🔴',
                'count'          => $count,
                'summary'        => 'Muatan tidak memenuhi standar mutu & ditolak oleh tim QC',
                'total_gross'    => $totalGross,
                'total_netto'    => $totalNetto,
                'total_refraksi' => $totalRefraksi,
                'total_reject'   => $totalReject,
            ];
        }

        if ($hasRejectPartial || $totalReject > 0 || $totalRefraksi > 0) {
            return [
                'tested'         => true,
                'status'         => 'PARSIAL',
                'label'          => 'Lolos Bersyarat (Ada Refraksi/Reject)',
                'short_label'    => 'Teruji (Bersyarat)',
                'badge_class'    => 'badge-warning',
                'color'          => '#b45309',
                'bg'             => '#fffbeb',
                'border'         => '#fde68a',
                'icon'           => '🟡',
                'count'          => $count,
                'summary'        => 'Lolos dengan catatan pemotongan refraksi / afkir QC',
                'total_gross'    => $totalGross,
                'total_netto'    => $totalNetto,
                'total_refraksi' => $totalRefraksi,
                'total_reject'   => $totalReject,
            ];
        }

        if ($hasDraft) {
            return [
                'tested'         => true,
                'status'         => 'SEDANG_UJI',
                'label'          => 'Sedang Diinspeksi QC',
                'short_label'    => 'Sedang Diuji',
                'badge_class'    => 'badge-info',
                'color'          => '#0369a1',
                'bg'             => '#f0f9ff',
                'border'         => '#bae6fd',
                'icon'           => '⏳',
                'count'          => $count,
                'summary'        => 'Proses sampling mutu lapangan masih berlangsung',
                'total_gross'    => $totalGross,
                'total_netto'    => $totalNetto,
                'total_refraksi' => $totalRefraksi,
                'total_reject'   => $totalReject,
            ];
        }

        return [
            'tested'         => true,
            'status'         => 'LOLOS',
            'label'          => 'Lulus Uji Mutu QC',
            'short_label'    => 'Teruji (Lolos QC)',
            'badge_class'    => 'badge-success',
            'color'          => '#047857',
            'bg'             => '#ecfdf5',
            'border'         => '#a7f3d0',
            'icon'           => '🟢',
            'count'          => $count,
            'summary'        => 'Seluruh parameter pengujian mutu memenuhi standar pabrik',
            'total_gross'    => $totalGross,
            'total_netto'    => $totalNetto,
            'total_refraksi' => $totalRefraksi,
            'total_reject'   => $totalReject,
        ];
    }

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
        return $this->hasMany(DatPoDtl::class, 'po_id', 'po_id')->where('deleted_st', false);
    }

    /**
     * Relasi ke riwayat penerimaan barang fisik yang menggunakan PO ini
     */
    public function penerimaan(): HasMany
    {
        return $this->hasMany(DatTerimaHdr::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke riwayat inspeksi mutu & sampling kedatangan oleh tim QC Lapangan
     */
    public function qcInbound(): HasMany
    {
        return $this->hasMany(DatQcInboundHdr::class, 'po_id', 'po_id')->where('deleted_st', false);
    }

    /**
     * Relasi ke riwayat audit trail / edit dokumen PO
     */
    public function revisiLog(): HasMany
    {
        return $this->hasMany(DatPoRevisiLog::class, 'po_id', 'po_id')
            ->where('deleted_st', false)
            ->orderBy('revisi_tgl', 'desc')
            ->orderBy('revisi_id', 'desc');
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
