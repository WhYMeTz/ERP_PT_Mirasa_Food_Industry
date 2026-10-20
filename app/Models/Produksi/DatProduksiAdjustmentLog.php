<?php

namespace App\Models\Produksi;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Riwayat Penyesuaian HPP & Biaya Produksi (Audit Trail).
 * Menyimpan catatan berita acara, selisih biaya, dan audit trail penyesuaian HPP.
 */
class DatProduksiAdjustmentLog extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_produksi_adjustment_log';
    protected $primaryKey = 'adj_id';

    protected $fillable = [
        'adj_no',
        'produksi_id',
        'adj_tgl',
        'kategori_komponen',
        'total_biaya_sebelum',
        'total_biaya_sesudah',
        'selisih_biaya',
        'hpp_sebelum',
        'hpp_sesudah',
        'selisih_hpp',
        'alasan_txt',
        'perubahan_field_json',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'adj_tgl'              => 'date',
        'total_biaya_sebelum'  => 'decimal:2',
        'total_biaya_sesudah'  => 'decimal:2',
        'selisih_biaya'        => 'decimal:2',
        'hpp_sebelum'          => 'decimal:2',
        'hpp_sesudah'          => 'decimal:2',
        'selisih_hpp'          => 'decimal:2',
        'perubahan_field_json' => 'array',
        'deleted_st'           => 'boolean',
        'active_st'            => 'boolean',
        'version_no'           => 'integer',
    ];

    /**
     * Relasi ke Header Produksi yang Disesuaikan
     */
    public function produksi(): BelongsTo
    {
        return $this->belongsTo(DatProduksiHdr::class, 'produksi_id', 'produksi_id');
    }
}
