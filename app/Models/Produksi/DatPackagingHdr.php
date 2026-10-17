<?php

namespace App\Models\Produksi;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatPackagingHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_packaging_hdr';
    protected $primaryKey = 'pack_id';

    protected $fillable = [
        'pack_no',
        'pack_tgl',
        'gudang_id',
        'barang_jadi_id',
        'bom_id',
        'produksi_id',
        'pakai_id',
        'target_karton_qty',
        'hasil_karton_qty',
        'isi_per_karton_pcs',
        'gramatur_gr',
        'hasil_pcs_qty',
        'batch_no',
        'expired_tgl',
        'total_biaya_wip',
        'total_biaya_kemasan',
        'lakban_atas_gr',
        'lakban_bawah_gr',
        'lakban_jumlah_gr',
        'lakban_nilai',
        'total_biaya_tenaga_kerja',
        'total_biaya_lain',
        'total_biaya_produksi',
        'hpp_per_karton',
        'hpp_per_pcs',
        'catatan_txt',
        'status_cd',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'pack_tgl'                 => 'date',
        'expired_tgl'              => 'date',
        'target_karton_qty'        => 'decimal:4',
        'hasil_karton_qty'         => 'decimal:4',
        'isi_per_karton_pcs'       => 'decimal:4',
        'gramatur_gr'              => 'decimal:4',
        'hasil_pcs_qty'            => 'decimal:4',
        'total_biaya_wip'          => 'decimal:4',
        'total_biaya_kemasan'      => 'decimal:4',
        'lakban_atas_gr'           => 'decimal:4',
        'lakban_bawah_gr'          => 'decimal:4',
        'lakban_jumlah_gr'         => 'decimal:4',
        'lakban_nilai'             => 'decimal:4',
        'total_biaya_tenaga_kerja' => 'decimal:4',
        'total_biaya_lain'         => 'decimal:4',
        'total_biaya_produksi'     => 'decimal:4',
        'hpp_per_karton'           => 'decimal:4',
        'hpp_per_pcs'              => 'decimal:4',
        'deleted_st'               => 'boolean',
        'active_st'                => 'boolean',
        'version_no'               => 'integer',
    ];

    /**
     * Relasi ke Gudang Penyimpanan Barang Jadi (FG)
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke Master Barang Jadi (FG)
     */
    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_jadi_id', 'barang_id');
    }

    /**
     * Relasi ke Master BOM Resep Kemasan Acuan
     */
    public function bom(): BelongsTo
    {
        return $this->belongsTo(MstBomHdr::class, 'bom_id', 'bom_id');
    }

    /**
     * Relasi ke Dokumen Produksi WIP Asal
     */
    public function produksi(): BelongsTo
    {
        return $this->belongsTo(DatProduksiHdr::class, 'produksi_id', 'produksi_id');
    }

    /**
     * Relasi ke Dokumen Pengeluaran Bahan Kemas dari Gudang
     */
    public function pemakaian(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Gudang\DatPakaiHdr::class, 'pakai_id', 'pakai_id');
    }

    /**
     * Relasi ke Rincian Bahan yang Dikonsumsi
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatPackagingDtl::class, 'pack_id', 'pack_id');
    }

    /**
     * Scope data aktif (belum dihapus secara soft-delete)
     */
    public function scopeActive($query)
    {
        return $query->where('deleted_st', false);
    }
}
