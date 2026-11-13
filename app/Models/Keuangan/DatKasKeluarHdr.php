<?php

namespace App\Models\Keuangan;

use App\Services\Keuangan\KasBankService;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatKasKeluarHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_kas_keluar_hdr';
    protected $primaryKey = 'bkk_id';

    protected $fillable = [
        'bkk_no',
        'tgl_keluar',
        'kas_bank_id',
        'coa_id',
        'kategori_pengeluaran',
        'penerima_nama',
        'metode_bayar',
        'no_referensi_bank',
        'total_keluar',
        'catatan',
        'jurnal_id',
        'status_cd',
        'alasan_void',
        'void_by',
        'void_dt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'tgl_keluar'   => 'date',
        'total_keluar' => 'float',
        'void_dt'      => 'datetime',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
    ];

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(MstKasBank::class, 'kas_bank_id', 'kas_bank_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'coa_id', 'coa_id');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(DatJurnalHdr::class, 'jurnal_id', 'jurnal_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatKasKeluarDtl::class, 'bkk_id', 'bkk_id');
    }

    public function getTerbilangAttribute(): string
    {
        return KasBankService::terbilang((float) $this->total_keluar);
    }
}

