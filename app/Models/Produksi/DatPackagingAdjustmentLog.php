<?php

namespace App\Models\Produksi;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Riwayat Penyesuaian HPP & Biaya Pengemasan Barang Jadi (Audit Trail).
 * Menyimpan catatan berita acara, rincian koreksi per komponen, selisih biaya, dan dampak HPP.
 */
class DatPackagingAdjustmentLog extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_packaging_adjustment_log';
    protected $primaryKey = 'adj_id';

    protected $fillable = [
        'adj_no',
        'pack_id',
        'adj_tgl',
        'kategori_komponen',
        'total_biaya_sebelum',
        'total_biaya_sesudah',
        'selisih_biaya',
        'hpp_karton_sebelum',
        'hpp_karton_sesudah',
        'selisih_hpp_karton',
        'hpp_pcs_sebelum',
        'hpp_pcs_sesudah',
        'selisih_hpp_pcs',
        'alasan_txt',
        'no_referensi_txt',
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
        'hpp_karton_sebelum'   => 'decimal:4',
        'hpp_karton_sesudah'   => 'decimal:4',
        'selisih_hpp_karton'   => 'decimal:4',
        'hpp_pcs_sebelum'      => 'decimal:4',
        'hpp_pcs_sesudah'      => 'decimal:4',
        'selisih_hpp_pcs'      => 'decimal:4',
        'perubahan_field_json' => 'array',
        'deleted_st'           => 'boolean',
        'active_st'            => 'boolean',
        'version_no'           => 'integer',
    ];

    /**
     * Relasi ke Header Dokumen Pengemasan yang Disesuaikan
     */
    public function packaging(): BelongsTo
    {
        return $this->belongsTo(DatPackagingHdr::class, 'pack_id', 'pack_id');
    }
}
