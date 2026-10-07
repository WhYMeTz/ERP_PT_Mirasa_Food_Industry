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
        'kategori_barang',
        'tahap_uji',
        'posisi_bak',
        'parent_qc_id',
        'batch_no',
        'nama_jenis',
        'negara_produsen',
        'nama_produsen',
        'lokasi_panen',
        'umur_singkong_bln',
        'tgl_panen',
        'jumlah_sample_kg',
        'jumlah_sample_pcs',
        'jumlah_sample_gr',
        'surat_jalan_supplier',
        'nomor_do',
        'jumlah_surat_jalan',
        'jumlah_di_pabrik',
        'plat_nomor_truk',
        'sopir_nama',
        'bebas_cemaran_st',
        'angkut_barang_haram_st',
        'komentar_transportasi',
        'terdaftar_lppom_st',
        'komentar_lppom',
        'ada_sertifikat_halal_st',
        'komentar_sertifikat',
        'sertifikat_halal_berlaku_st',
        'komentar_berlaku',
        'tgl_periksa',
        'petugas_qc_nama',
        'qc_supervisor_nama',
        'status_qc',
        'status_uji_goreng',
        'tgl_uji_goreng',
        'petugas_uji_goreng',
        'catatan_umum',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'tgl_periksa'                 => 'datetime',
        'tgl_uji_goreng'              => 'datetime',
        'tgl_panen'                   => 'date',
        'umur_singkong_bln'           => 'decimal:1',
        'jumlah_sample_kg'            => 'decimal:2',
        'jumlah_sample_gr'            => 'decimal:2',
        'jumlah_surat_jalan'          => 'decimal:2',
        'jumlah_di_pabrik'            => 'decimal:2',
        'bebas_cemaran_st'            => 'boolean',
        'angkut_barang_haram_st'      => 'boolean',
        'terdaftar_lppom_st'          => 'boolean',
        'ada_sertifikat_halal_st'     => 'boolean',
        'sertifikat_halal_berlaku_st' => 'boolean',
        'deleted_st'                  => 'boolean',
        'active_st'                   => 'boolean',
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

    public function parentQc(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_qc_id', 'qc_id');
    }

    public function pengujian2List(): HasMany
    {
        return $this->hasMany(self::class, 'parent_qc_id', 'qc_id')->where('deleted_st', false);
    }

    public function isPengujian2Done(): bool
    {
        return $this->pengujian2List && $this->pengujian2List->isNotEmpty();
    }

    public function terima(): HasOne
    {
        return $this->hasOne(DatTerimaHdr::class, 'qc_id', 'qc_id');
    }
}
