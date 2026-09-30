<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatQcInboundDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_qc_inbound_dtl';
    protected $primaryKey = 'qcdtl_id';

    protected $fillable = [
        'qc_id',
        'podtl_id',
        'barang_id',
        'status_raw_material',
        'diameter_kurang_4cm_persen',
        'diameter_lebih_4cm_persen',
        'kondisi_segar',
        'kondisi_layu',
        'kondisi_basah',
        'kondisi_terkelupas',
        'kondisi_busuk',
        'kondisi_berjamur',
        'kondisi_lembek',
        'fryer_rasa',
        'fryer_tekstur',
        'fryer_penampakan',
        'defect_breakage_persen',
        'defect_cluster_persen',
        'defect_foldover_persen',
        'defect_oilsoaked_persen',
        'defect_gambos_persen',
        'qty_timbang_gross',
        'kadar_air_persen',
        'refraksi_persen',
        'qty_refraksi',
        'qty_reject',
        'qty_netto_lolos',
        'grade_cd',
        'kondisi_fisik',
        'keputusan_qc',
        'catatan_dtl',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'diameter_kurang_4cm_persen' => 'decimal:2',
        'diameter_lebih_4cm_persen'  => 'decimal:2',
        'kondisi_segar'              => 'boolean',
        'kondisi_layu'               => 'boolean',
        'kondisi_basah'              => 'boolean',
        'kondisi_terkelupas'         => 'boolean',
        'kondisi_busuk'              => 'boolean',
        'kondisi_berjamur'           => 'boolean',
        'kondisi_lembek'             => 'boolean',
        'defect_breakage_persen'     => 'decimal:2',
        'defect_cluster_persen'      => 'decimal:2',
        'defect_foldover_persen'     => 'decimal:2',
        'defect_oilsoaked_persen'    => 'decimal:2',
        'defect_gambos_persen'       => 'decimal:2',
        'qty_timbang_gross'          => 'decimal:4',
        'kadar_air_persen'           => 'decimal:2',
        'refraksi_persen'            => 'decimal:2',
        'qty_refraksi'               => 'decimal:4',
        'qty_reject'                 => 'decimal:4',
        'qty_netto_lolos'            => 'decimal:4',
        'deleted_st'                 => 'boolean',
        'active_st'                  => 'boolean',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatQcInboundHdr::class, 'qc_id', 'qc_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }

    public function poDetail(): BelongsTo
    {
        return $this->belongsTo(DatPoDtl::class, 'podtl_id', 'podtl_id');
    }
}
