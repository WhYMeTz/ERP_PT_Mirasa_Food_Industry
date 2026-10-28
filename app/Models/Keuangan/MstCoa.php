<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstCoa extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_coa';
    protected $primaryKey = 'coa_id';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'kategori_akun',
        'sub_kategori',
        'posisi_keuangan',
        'saldo_normal',
        'saldo_awal',
        'saldo_berjalan',
        'is_kas_bank',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'saldo_awal'     => 'float',
        'saldo_berjalan' => 'float',
        'is_kas_bank'    => 'boolean',
        'deleted_st'     => 'boolean',
        'active_st'      => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }

    public function scopeKasBank(Builder $query): Builder
    {
        return $query->where('is_kas_bank', true);
    }

    public function getFormattedSaldoBerjalanAttribute(): string
    {
        return 'Rp ' . number_format($this->saldo_berjalan, 0, ',', '.');
    }

    public function getFormattedSaldoAwalAttribute(): string
    {
        return 'Rp ' . number_format($this->saldo_awal, 0, ',', '.');
    }

    public function jurnalDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DatJurnalDtl::class, 'coa_id', 'coa_id')
            ->where('deleted_st', false);
    }

    public function getCoaCdAttribute(): string
    {
        return $this->kode_akun ?? '';
    }

    public function getCoaNmAttribute(): string
    {
        return $this->nama_akun ?? '';
    }
}
