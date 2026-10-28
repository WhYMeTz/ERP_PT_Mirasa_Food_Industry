<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstGudang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DatPakaiHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_pakai_hdr';
    protected $primaryKey = 'pakai_id';

    protected $fillable = [
        'pakai_no',
        'pakai_tgl',
        'gudang_id',
        'produksi_id',
        'tujuan_pemakaian',
        'catatan_txt',
        'status_st',
        'posted_at',
        'posted_by',
        'jurnal_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'pakai_tgl'  => 'date',
        'posted_at'  => 'datetime',
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    public function isDraft(): bool
    {
        return ($this->status_st ?? 'DRAFT') === 'DRAFT';
    }

    public function isPosted(): bool
    {
        return ($this->status_st ?? '') === 'POSTED';
    }

    /**
     * Relasi ke Jurnal Umum Akuntansi
     */
    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Keuangan\DatJurnalHdr::class, 'jurnal_id', 'jurnal_id');
    }

    /**
     * Relasi ke Gudang Asal
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke Rincian Barang Keluar
     */
    public function details(): HasMany
    {
        return $this->hasMany(DatPakaiDtl::class, 'pakai_id', 'pakai_id');
    }

    /**
     * Relasi ke Laporan Hasil Produksi yang menggunakan dokumen ini (Bahan Baku Singkong/Minyak)
     */
    public function produksi(): HasOne
    {
        return $this->hasOne(\App\Models\Produksi\DatProduksiHdr::class, 'pakai_id', 'pakai_id')
            ->where('deleted_st', false);
    }

    /**
     * Relasi ke Tiket Produksi Terkait (jika pengeluaran kemasan ditujukan untuk batch produksi tertentu)
     */
    public function produksiTerkait(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Produksi\DatProduksiHdr::class, 'produksi_id', 'produksi_id');
    }

    /**
     * Relasi ke Dokumen Pengemasan (Packaging) yang menggunakan bahan dari pengeluaran ini
     */
    public function packaging(): HasOne
    {
        return $this->hasOne(\App\Models\Produksi\DatPackagingHdr::class, 'pakai_id', 'pakai_id')
            ->where('deleted_st', false);
    }
}
