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
        'user_id',
        'adj_tgl',
        'kategori_komponen',
        'role_cd',
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

    /**
     * Relasi ke User Operator Pembuat Penyesuaian
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    /**
     * Scope untuk memfilter riwayat berita acara berdasarkan wewenang role user.
     * Super Administrator & Direksi dapat melihat seluruh riwayat berita acara.
     * Role lain hanya dapat melihat riwayat yang dibuat oleh role yang bersangkutan.
     */
    public function scopeForUser($query, ?\App\Models\User $user = null)
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return $query;
        }

        if ($user->isSuperAdmin() || in_array(strtoupper((string) $user->role_cd), ['SUPERADMIN', 'DIREKSI'])) {
            return $query;
        }

        $roleCd = strtoupper((string) $user->role_cd);

        return $query->where(function ($q) use ($user, $roleCd) {
            $q->where('role_cd', $roleCd)
              ->orWhere('user_id', $user->id)
              ->orWhere('created_by', 'ILIKE', "%{$roleCd}%");
        });
    }
}
