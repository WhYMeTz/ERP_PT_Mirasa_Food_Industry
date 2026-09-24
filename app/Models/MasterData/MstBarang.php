<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MstBarang extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_barang';
    protected $primaryKey = 'barang_id';

    protected $fillable = [
        'barang_cd',
        'barang_nm',
        'jenis_barang_id',
        'satuan_dasar_id',
        'satuan_besar_id',
        'konversi_qty',
        'batas_minimum_qty',
        'harga_beli_standar',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'konversi_qty'       => 'decimal:4',
        'batas_minimum_qty'  => 'decimal:4',
        'harga_beli_standar' => 'decimal:4',
        'deleted_st'         => 'boolean',
        'active_st'          => 'boolean',
    ];

    /**
     * Scope barang khusus Bahan Baku & Bahan Penolong (bukan barang jadi atau WIP)
     */
    public function scopeBahanBaku($query)
    {
        return $query->whereHas('jenisBarang', function ($q) {
            $q->whereIn('jenis_barang_cd', ['RAW', 'SUPP', 'PACK', 'BUMBU']);
        });
    }

    /**
     * Relasi ke Jenis Barang
     */
    public function jenisBarang(): BelongsTo
    {
        return $this->belongsTo(MstJenisBarang::class, 'jenis_barang_id', 'jenis_barang_id');
    }

    /**
     * Relasi ke Satuan Dasar (misal: KG, PCS)
     */
    public function satuanDasar(): BelongsTo
    {
        return $this->belongsTo(MstSatuan::class, 'satuan_dasar_id', 'satuan_id');
    }

    /**
     * Relasi ke Satuan Besar (misal: SAK, DUS, BAL)
     */
    public function satuanBesar(): BelongsTo
    {
        return $this->belongsTo(MstSatuan::class, 'satuan_besar_id', 'satuan_id');
    }
}
