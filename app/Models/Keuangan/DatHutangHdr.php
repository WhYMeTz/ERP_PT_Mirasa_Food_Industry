<?php

namespace App\Models\Keuangan;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatHutangHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_hutang_hdr';
    protected $primaryKey = 'hutang_id';

    protected $fillable = [
        'hutang_no',
        'no_faktur_supplier',
        'supplier_id',
        'gudang_id',
        'tgl_tagihan',
        'tgl_jatuh_tempo',
        'tipe_hutang',
        'periode_bulan',
        'total_bruto',
        'total_diskon',
        'total_potongan_item',
        'total_potongan_retur',
        'total_ppn',
        'biaya_ongkir',
        'total_tagihan',
        'total_dibayar',
        'sisa_hutang',
        'status_pembayaran',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'tgl_tagihan'          => 'date',
        'tgl_jatuh_tempo'      => 'date',
        'total_bruto'          => 'float',
        'total_diskon'         => 'float',
        'total_potongan_item'  => 'float',
        'total_potongan_retur' => 'float',
        'total_ppn'            => 'float',
        'biaya_ongkir'         => 'float',
        'total_tagihan'        => 'float',
        'total_dibayar'        => 'float',
        'sisa_hutang'          => 'float',
        'deleted_st'           => 'boolean',
        'active_st'            => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MstSupplier::class, 'supplier_id', 'supplier_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatHutangDtl::class, 'hutang_id', 'hutang_id');
    }

    public function bayarDetails(): HasMany
    {
        return $this->hasMany(DatBayarHutangDtl::class, 'hutang_id', 'hutang_id');
    }

    public function jurnal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DatJurnalHdr::class, 'ref_no', 'hutang_no')
            ->where('deleted_st', false);
    }

    /**
     * Cek apakah sudah jatuh tempo
     */
    public function isOverdue(): bool
    {
        if ($this->status_pembayaran === 'LUNAS' || $this->status_pembayaran === 'DIBATALKAN') {
            return false;
        }
        return $this->tgl_jatuh_tempo < now()->startOfDay();
    }
}
