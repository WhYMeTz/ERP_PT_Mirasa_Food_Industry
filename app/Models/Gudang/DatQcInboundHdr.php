<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DatQcInboundHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_qc_inbound_hdr';
    protected $primaryKey = 'qc_id';

    protected $fillable = [
        'qc_no',
        'po_id',
        'supplier_id',
        'gudang_id',
        'negara_produsen',
        'lokasi_panen',
        'umur_singkong_bln',
        'tgl_panen',
        'jumlah_sample_kg',
        'surat_jalan_supplier',
        'plat_nomor_truk',
        'sopir_nama',
        'bebas_cemaran_st',
        'angkut_barang_haram_st',
        'komentar_transportasi',
        'tgl_periksa',
        'petugas_qc_nama',
        'qc_supervisor_nama',
        'status_qc',
        'catatan_umum',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'tgl_periksa'            => 'datetime',
        'tgl_panen'              => 'date',
        'umur_singkong_bln'      => 'decimal:1',
        'jumlah_sample_kg'       => 'decimal:2',
        'bebas_cemaran_st'       => 'boolean',
        'angkut_barang_haram_st' => 'boolean',
        'deleted_st'             => 'boolean',
        'active_st'              => 'boolean',
    ];

    public function po(): BelongsTo
    {
        return $this->belongsTo(DatPoHdr::class, 'po_id', 'po_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MstSupplier::class, 'supplier_id', 'supplier_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatQcInboundDtl::class, 'qc_id', 'qc_id');
    }

    public function terima(): HasOne
    {
        return $this->hasOne(DatTerimaHdr::class, 'qc_id', 'qc_id');
    }
}
