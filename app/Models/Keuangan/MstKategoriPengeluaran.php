<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstKategoriPengeluaran extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_kategori_pengeluaran';
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'coa_id_default',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    /**
     * Akun COA beban default untuk otomatisasi verifikasi jurnal
     */
    public function coaDefault(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'coa_id_default', 'coa_id');
    }

    /**
     * Riwayat transaksi kas keluar dalam kategori ini
     */
    public function kasKeluar(): HasMany
    {
        return $this->hasMany(DatKasKeluarHdr::class, 'kategori_id', 'kategori_id');
    }

    /**
     * Scope untuk kategori aktif yang belum dihapus
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }
}
