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
        'supplier_id',
        'gudang_id',
        'suratjalan_no',
        'status_cd',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'terima_tgl' => 'date',
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    /**
     * Relasi ke Header PO (opsional jika non-PO)
     */
    public function po(): BelongsTo
    {
        return $this->belongsTo(DatPoHdr::class, 'po_id', 'po_id');
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
}
