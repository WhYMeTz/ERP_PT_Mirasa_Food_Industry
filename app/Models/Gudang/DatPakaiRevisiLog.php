<?php

namespace App\Models\Gudang;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Riwayat Audit & Koreksi Pemakaian Bahan (Audit Trail Kesalahan Input).
 * Mencatat perbandingan sebelum vs sesudah, verifikator pengoreksi, dan staf gudang pembuat input.
 */
class DatPakaiRevisiLog extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_pakai_revisi_log';
    protected $primaryKey = 'revisi_id';

    protected $fillable = [
        'pakai_id',
        'revisi_no',
        'revisi_tgl',
        'pembuat_nama',
        'verifikator_nama',
        'kategori_kesalahan',
        'catatan_koreksi',
        'data_sebelum_json',
        'data_sesudah_json',
        'perubahan_json',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'revisi_tgl'        => 'datetime',
        'data_sebelum_json' => 'array',
        'data_sesudah_json' => 'array',
        'perubahan_json'    => 'array',
        'deleted_st'        => 'boolean',
        'active_st'         => 'boolean',
    ];

    /**
     * Relasi ke Header Pemakaian
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatPakaiHdr::class, 'pakai_id', 'pakai_id');
    }
}

