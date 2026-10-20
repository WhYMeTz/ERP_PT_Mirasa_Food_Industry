<?php

namespace App\Models\Keuangan;

use App\Models\MasterData\MstCustomer;
use App\Services\Keuangan\KasBankService;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatKasMasukHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_kas_masuk_hdr';
    protected $primaryKey = 'bkm_id';

    protected $fillable = [
        'bkm_no',
        'tgl_terima',
        'kas_bank_id',
        'kategori_penerimaan',
        'customer_id',
        'penyetor_nama',
        'metode_terima',
        'no_referensi_bank',
        'total_terima',
        'catatan',
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
        'tgl_terima'   => 'date',
        'total_terima' => 'float',
        'void_dt'      => 'datetime',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
    ];

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(MstKasBank::class, 'kas_bank_id', 'kas_bank_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MstCustomer::class, 'customer_id', 'customer_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatKasMasukDtl::class, 'bkm_id', 'bkm_id');
    }

    public function getTerbilangAttribute(): string
    {
        return KasBankService::terbilang((float) $this->total_terima);
    }

    public function getKategoriBadgeAttribute(): array
    {
        return match ($this->kategori_penerimaan) {
            'PENJUALAN_LIMBAH'  => ['label' => 'Minyak/Limbah', 'class' => 'limbah', 'color' => '#047857', 'bg' => '#ecfdf5', 'border' => '#a7f3d0'],
            'SETORAN_MODAL'     => ['label' => 'Modal Owner', 'class' => 'modal', 'color' => '#7e22ce', 'bg' => '#faf5ff', 'border' => '#e9d5ff'],
            'PENJUALAN_TUNAI'   => ['label' => 'Jual Tunai', 'class' => 'tunai', 'color' => '#1d4ed8', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
            'PELUNASAN_PIUTANG' => ['label' => 'Pelunasan SO', 'class' => 'piutang', 'color' => '#c2410c', 'bg' => '#fff7ed', 'border' => '#fed7aa'],
            default             => ['label' => 'Lain-lain', 'class' => 'lain', 'color' => '#475569', 'bg' => '#f8fafc', 'border' => '#cbd5e1'],
        };
    }
}
