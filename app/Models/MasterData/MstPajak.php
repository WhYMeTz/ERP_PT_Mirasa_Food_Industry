<?php

namespace App\Models\MasterData;

use App\Models\Keuangan\MstCoa;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MstPajak extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_pajak';
    protected $primaryKey = 'pajak_id';

    protected $fillable = [
        'pajak_cd',
        'pajak_nm',
        'kategori_pajak',
        'sifat_pajak',
        'tarif_persen',
        'coa_id',
        'coa_pembelian_id',
        'coa_penjualan_id',
        'is_pembelian',
        'is_penjualan',
        'keterangan',
        'is_default',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'tarif_persen' => 'float',
        'is_pembelian' => 'boolean',
        'is_penjualan' => 'boolean',
        'is_default'   => 'boolean',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
        'version_no'   => 'integer',
    ];

    /**
     * Relasi ke Akun Pajak Pembelian (PPN Masukan / COA Pembelian)
     */
    public function coaPembelian(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'coa_pembelian_id', 'coa_id');
    }

    /**
     * Relasi ke Akun Pajak Penjualan (PPN Keluaran / COA Penjualan)
     */
    public function coaPenjualan(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'coa_penjualan_id', 'coa_id');
    }

    /**
     * Relasi fallback umum (kompatibilitas)
     */
    public function coa(): BelongsTo
    {
        return $this->coaPembelian_id ? $this->coaPembelian() : $this->coaPenjualan();
    }

    /**
     * Scope untuk mengambil hanya kategori PPN
     */
    public function scopePpn($query)
    {
        return $query->where('kategori_pajak', 'PPN');
    }

    /**
     * Scope untuk pajak yang berlaku pada modul Pembelian (PO / Faktur Pembelian)
     */
    public function scopePembelian($query)
    {
        return $query->where('is_pembelian', true);
    }

    /**
     * Scope untuk pajak yang berlaku pada modul Penjualan (SO / Faktur Penjualan)
     */
    public function scopePenjualan($query)
    {
        return $query->where('is_penjualan', true);
    }

    /**
     * Scope untuk mengambil pajak pemotongan (PPh withholding)
     */
    public function scopeWithholding($query)
    {
        return $query->where('sifat_pajak', 'POTONG');
    }

    /**
     * Dapatkan format badge sifat pajak
     */
    public function getSifatBadgeAttribute(): string
    {
        return $this->sifat_pajak === 'TAMBAH'
            ? '<span class="badge badge-tambah">Tambah Tagihan (PPN)</span>'
            : '<span class="badge badge-potong">Potong Pembayaran (PPh)</span>';
    }

    /**
     * Dapatkan format tarif yang mudah dibaca
     */
    public function getTarifFormattedAttribute(): string
    {
        return number_format($this->tarif_persen, 2, ',', '.') . '%';
    }
}
