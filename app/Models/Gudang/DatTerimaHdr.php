<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatTerimaHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_terima_hdr';
    protected $primaryKey = 'terima_id';

    protected $fillable = [
        'terima_no',
        'terima_tgl',
        'po_id',
        'qc_id',
        'supplier_id',
        'gudang_id',
        'suratjalan_no',
        'tgl_jatuh_tempo',
        'status_cd',
        'catatan_txt',
        'subtotal_nominal',
        'potongan_nominal',
        'dpp_nominal',
        'ppn_tipe',
        'ppn_persen',
        'ppn_nominal',
        'total_tagihan',
        'is_ada_revisi',
        'total_revisi_count',
        'terakhir_direvisi_at',
        'terakhir_direvisi_by',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'terima_tgl'       => 'date',
        'tgl_jatuh_tempo'  => 'date',
        'subtotal_nominal' => 'decimal:4',
        'potongan_nominal' => 'decimal:4',
        'dpp_nominal'      => 'decimal:4',
        'ppn_persen'       => 'decimal:2',
        'ppn_nominal'      => 'decimal:4',
        'total_tagihan'    => 'decimal:4',
        'is_ada_revisi'    => 'boolean',
        'total_revisi_count' => 'integer',
        'terakhir_direvisi_at' => 'datetime',
        'deleted_st'       => 'boolean',
        'active_st'        => 'boolean',
    ];

    /**
     * Relasi ke Header PO (opsional jika non-PO)
     */
    public function po(): BelongsTo
    {
        return $this->belongsTo(DatPoHdr::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke Tiket Pemeriksaan Kualitas (Incoming QC)
     */
    public function qcInbound(): BelongsTo
    {
        return $this->belongsTo(DatQcInboundHdr::class, 'qc_id', 'qc_id');
    }

    public function qc(): BelongsTo
    {
        return $this->qcInbound();
    }


    /**
     * Relasi ke Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MstSupplier::class, 'supplier_id', 'supplier_id');
    }

    /**
     * Relasi ke Gudang tempat barang disimpan
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke rincian item barang yang diterima
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatTerimaDtl::class, 'terima_id', 'terima_id');
    }

    /**
     * Relasi ke rincian tagihan hutang pembelian Keuangan (AP)
     */
    public function hutangDetails(): HasMany
    {
        return $this->hasMany(\App\Models\Keuangan\DatHutangDtl::class, 'terima_id', 'terima_id')
            ->where('deleted_st', false);
    }

    /**
     * Relasi ke riwayat log revisi / audit trail penerimaan barang
     */
    public function revisiLog(): HasMany
    {
        return $this->hasMany(DatTerimaRevisiLog::class, 'terima_id', 'terima_id')
            ->orderBy('revisi_id', 'desc');
    }

    /**
     * Memeriksa status pemakaian stok fisik via TerimaBarangService
     */
    public function checkStockUsage(): array
    {
        return app(\App\Services\Gudang\TerimaBarangService::class)->checkStockUsage($this);
    }

    /**
     * Cek cepat apakah dokumen terkunci dari edit/hapus karena pemakaian stok pada batch
     */
    public function isLockedByStockUsage(): bool
    {
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }

        foreach ($this->details as $dtl) {
            $netto = max(0, (float) $dtl->terima_qty - (float) ($dtl->reject_qty ?? 0));
            if ($netto <= 0) {
                continue;
            }

            $gradeCd = !empty($dtl->grade_cd) ? strtoupper(trim($dtl->grade_cd)) : null;

            // Cek apakah ada mutasi OUT nyata pada batch ini (produksi, packaging, retur, pengeluaran)
            $hasOut = \App\Models\Gudang\DatStokLedger::where('gudang_id', $this->gudang_id)
                ->where('barang_id', $dtl->barang_id)
                ->where('batch_no', $dtl->batch_no)
                ->when(!empty($gradeCd), fn($q) => $q->where('grade_cd', $gradeCd))
                ->where('tipe_transaksi_cd', 'OUT')
                ->where('keterangan_txt', 'NOT ILIKE', '%Rollback Edit%')
                ->where('keterangan_txt', 'NOT ILIKE', '%Pembatalan%')
                ->where('dokumen_no', 'NOT ILIKE', '%/GRN/%')
                ->exists();

            if ($hasOut) {
                return true;
            }

            $batch = \App\Models\Gudang\DatStokBatch::where('gudang_id', $this->gudang_id)
                ->where('barang_id', $dtl->barang_id)
                ->where('batch_no', $dtl->batch_no)
                ->when(!empty($gradeCd), fn($q) => $q->where('grade_cd', $gradeCd))
                ->first();

            if (!$batch || (float) $batch->sisa_qty < $netto) {
                return true;
            }
        }

        return false;
    }
}
