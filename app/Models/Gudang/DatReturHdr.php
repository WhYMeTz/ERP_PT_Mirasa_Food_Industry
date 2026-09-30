<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatReturHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_retur_hdr';
    protected $primaryKey = 'retur_id';

    protected $fillable = [
        'retur_no',
        'retur_tgl',
        'supplier_id',
        'gudang_id',
        'po_id',
        'terima_id',
        'tindakan_cd',
        'suratjalan_supplier_no',
        'total_nominal',
        'status_cd',
        'alasan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'retur_tgl'     => 'date',
        'total_nominal' => 'decimal:4',
        'deleted_st'    => 'boolean',
        'active_st'     => 'boolean',
    ];

    /**
     * Relasi ke Header PO (jika ada referensi PO)
     */
    public function po(): BelongsTo
    {
        return $this->belongsTo(DatPoHdr::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke Dokumen Penerimaan Barang (GRN) jika ada
     */
    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(DatTerimaHdr::class, 'terima_id', 'terima_id');
    }

    public function terima(): BelongsTo
    {
        return $this->penerimaan();
    }

    /**
     * Relasi ke Mitra Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MstSupplier::class, 'supplier_id', 'supplier_id');
    }

    /**
     * Relasi ke Gudang asal barang dikeluarkan
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke Detail Item yang Diretur
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatReturDtl::class, 'retur_id', 'retur_id');
    }

    /**
     * Helper status label
     */
    public function getTindakanLabelAttribute(): string
    {
        return match ($this->tindakan_cd) {
            'REPLACE'     => 'Minta Kirim Ulang (Pengganti)',
            'CREDIT_NOTE' => 'Potong Tagihan (Credit Note)',
            default       => $this->tindakan_cd,
        };
    }
}
