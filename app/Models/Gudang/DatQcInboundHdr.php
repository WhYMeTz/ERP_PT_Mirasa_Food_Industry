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
        if ($this->pengujian2List && $this->pengujian2List->isNotEmpty()) {
            return true;
        }

        // Jika seluruh muatan surat jalan sudah diturunkan di pabrik pada Uji 1, tidak ada sisa muatan untuk Uji 2
        $sj = (float) ($this->jumlah_surat_jalan ?? 0);
        $pabrik = (float) ($this->jumlah_di_pabrik ?? 0);
        $gross = (float) ($this->details?->first()?->qty_timbang_gross ?? 0);
        $muatanTurun = max($pabrik, $gross);

        if ($sj > 0 && $muatanTurun >= $sj) {
            return true;
        }

        return false;
    }

    public function terima(): HasOne
    {
        return $this->hasOne(DatTerimaHdr::class, 'qc_id', 'qc_id');
    }

    /**
     * Resolusi dokumen GRN lengkap:
     * 1. Cek langsung dari dat_terima_hdr.qc_id
     * 2. Cek via dat_terima_dtl.qcdtl_id (jika ditarik bersama tiket QC lain ke 1 GRN)
     * 3. Cek via Parent QC (jika ini P2)
     * 4. Cek via Child QC (jika ini P1)
     */
    public function getTerimaAttribute(): ?DatTerimaHdr
    {
        if (array_key_exists('terima_resolved', $this->relations)) {
            return $this->relations['terima_resolved'];
        }

        // 1. Cek relasi langsung via header qc_id
        $terima = null;
        if (array_key_exists('terima', $this->relations)) {
            $terima = $this->relations['terima'];
        } else {
            $terima = $this->hasOne(DatTerimaHdr::class, 'qc_id', 'qc_id')->first();
        }

        if ($terima && !$terima->deleted_st) {
            $this->relations['terima_resolved'] = $terima;
            return $terima;
        }

        // 2. Cek via dat_terima_dtl.qcdtl_id
        $qcDtlIds = $this->relationLoaded('details')
            ? $this->details->pluck('qcdtl_id')->filter()->all()
            : $this->details()->pluck('qcdtl_id')->filter()->all();

        if (!empty($qcDtlIds)) {
            $terimaDtl = DatTerimaDtl::with('header')
                ->whereIn('qcdtl_id', $qcDtlIds)
                ->where('deleted_st', false)
                ->first();

            if ($terimaDtl?->header && !$terimaDtl->header->deleted_st) {
                $this->relations['terima_resolved'] = $terimaDtl->header;
                return $terimaDtl->header;
            }
        }

        // 3. Cek via parent QC jika ini P2 (Pengujian 2)
        if ($this->parent_qc_id) {
            $parent = $this->relationLoaded('parentQc') ? $this->parentQc : $this->parentQc()->first();
            if ($parent && $parent->terima) {
                $this->relations['terima_resolved'] = $parent->terima;
                return $parent->terima;
            }
        }

        // 4. Cek via child QC jika ini P1 (Pengujian 1)
        $children = $this->relationLoaded('pengujian2List') ? $this->pengujian2List : $this->pengujian2List()->get();
        if ($children && $children->isNotEmpty()) {
            foreach ($children as $p2) {
                if ($p2->terima) {
                    $this->relations['terima_resolved'] = $p2->terima;
                    return $p2->terima;
                }
            }
        }

        $this->relations['terima_resolved'] = null;
        return null;
    }
}
