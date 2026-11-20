<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'coa_id',
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

    public function coa(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'coa_id', 'coa_id');
    }

    public function getCoaKodeAkun(): string
    {
        if ($this->coa_id && $this->coa) {
            return $this->coa->kode_akun;
        }

        if ($this->coa_id) {
            $coa = MstCoa::find($this->coa_id);
            if ($coa) {
                return $coa->kode_akun;
            }
        }

        $directCoa = MstCoa::where('kode_akun', $this->kode_akun)->first();
        if ($directCoa) {
            return $directCoa->kode_akun;
        }

        $upperName = strtoupper($this->nama_akun . ' ' . $this->bank_nama . ' ' . $this->kode_akun);
        if (str_contains($upperName, 'BCA')) {
            return '1103';
        }
        if (str_contains($upperName, 'MANDIRI') || str_contains($upperName, 'MDR')) {
            return '1107';
        }
        if ($this->tipe_akun === 'KAS') {
            return '1100';
        }

        return '1100';
    }

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
