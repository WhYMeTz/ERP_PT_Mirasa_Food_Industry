<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstKasBank extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_kas_bank';
    protected $primaryKey = 'kas_bank_id';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe_akun',
        'nomor_rekening',
        'atas_nama',
        'bank_nama',
        'cabang',
        'saldo_awal',
        'saldo_berjalan',
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
        'deleted_st'     => 'boolean',
        'active_st'      => 'boolean',
    ];

    public function mutasiList(): HasMany
    {
        return $this->hasMany(DatKasMutasi::class, 'kas_bank_id', 'kas_bank_id')->orderBy('tgl_mutasi', 'desc')->orderBy('mutasi_id', 'desc');
    }

    public function getKasBankNmAttribute(): string
    {
        return $this->nama_akun ?? '';
    }

    public function bayarList(): HasMany
    {
        return $this->hasMany(DatBayarHutangHdr::class, 'kas_bank_id', 'kas_bank_id');
    }
}
