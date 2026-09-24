<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatPakaiHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_pakai_hdr';
    protected $primaryKey = 'pakai_id';

    protected $fillable = [
        'pakai_no',
        'pakai_tgl',
        'gudang_id',
        'tujuan_pemakaian',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'pakai_tgl'  => 'date',
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    /**
     * Relasi ke Gudang Asal
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke Rincian Barang Keluar
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatPakaiDtl::class, 'pakai_id', 'pakai_id');
    }
}
