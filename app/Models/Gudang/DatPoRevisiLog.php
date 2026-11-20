<?php

namespace App\Models\Gudang;

use App\Models\User;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Riwayat Audit Trail Perubahan & Koreksi Dokumen Purchase Order.
 * Mencatat siapa yang mengedit, waktu edit, alasan, dan rincian komparasi sebelum vs sesudah.
 */
class DatPoRevisiLog extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_po_revisi_log';
    protected $primaryKey = 'revisi_id';

    protected $fillable = [
        'po_id',
        'revisi_no',
        'revisi_tgl',
        'user_id',
        'user_nama',
        'user_role',
        'kategori_perubahan',
        'alasan_edit',
        'ringkasan_perubahan',
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
     * Relasi ke Header Purchase Order
     */
    public function po(): BelongsTo
    {
        return $this->belongsTo(DatPoHdr::class, 'po_id', 'po_id');
    }

    /**
     * Relasi ke Pengguna/User yang melakukan edit
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

