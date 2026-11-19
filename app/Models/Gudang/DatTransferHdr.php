<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Models\User;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatTransferHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_transfer_hdr';
    protected $primaryKey = 'transfer_id';

    protected $fillable = [
        'transfer_no',
        'transfer_tgl',
        'gudang_asal_id',
        'gudang_tujuan_id',
        'no_surat_jalan',
        'sopir_nama',
        'nopol_kendaraan',
        'catatan_txt',
        'status_st',
        'total_qty',
        'total_nilai',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'transfer_tgl' => 'date',
        'total_qty'    => 'decimal:4',
        'total_nilai'  => 'decimal:4',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
        'version_no'   => 'integer',
    ];

    public function isPosted(): bool
    {
        return ($this->status_st ?? 'POSTED') === 'POSTED';
    }

    public function isVoid(): bool
    {
        return ($this->status_st ?? '') === 'VOID';
    }

    /**
     * Relasi ke Gudang Asal
     */
    public function gudangAsal(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_asal_id', 'gudang_id');
    }

    /**
     * Relasi ke Gudang Tujuan
     */
    public function gudangTujuan(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_tujuan_id', 'gudang_id');
    }

    /**
     * Relasi ke Detail Item yang Ditransfer
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatTransferDtl::class, 'transfer_id', 'transfer_id')
            ->where('deleted_st', false);
    }

    /**
     * Relasi ke Pembuat Dokumen
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
