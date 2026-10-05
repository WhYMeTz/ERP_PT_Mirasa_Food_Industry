<?php

namespace App\Models\Produksi;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Detail Baris Hasil Produksi (dat_produksi_dtl).
 * Mengelola rincian varian produk jadi (FG) dan setengah jadi (WIP) per transaksi produksi.
 */
class DatProduksiDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_produksi_dtl';
    protected $primaryKey = 'output_id';

    protected $fillable = [
        'produksi_id',
        'barang_id',
        'jenis_cd',
        'kategori_output',
        'qty_hasil',
        'satuan_cd',
        'qty_kg',
        'batch_no',
        'hpp_satuan',
        'total_nilai',
        'keterangan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'qty_hasil'   => 'decimal:4',
        'qty_kg'      => 'decimal:4',
        'hpp_satuan'  => 'decimal:2',
        'total_nilai' => 'decimal:2',
        'deleted_st'  => 'boolean',
        'active_st'   => 'boolean',
    ];

    /**
     * Relasi ke Header Produksi
     */
    public function produksi(): BelongsTo
    {
        return $this->belongsTo(DatProduksiHdr::class, 'produksi_id', 'produksi_id');
    }

    /**
     * Alias header() untuk kemudahan akses
     */
    public function header(): BelongsTo
    {
        return $this->produksi();
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
