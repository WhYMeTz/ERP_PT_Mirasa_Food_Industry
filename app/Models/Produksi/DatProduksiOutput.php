<?php

namespace App\Models\Produksi;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatProduksiOutput extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_produksi_output';
    protected $primaryKey = 'output_id';

    protected $fillable = [
        'produksi_id',
        'barang_id',
        'kategori_output',
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
        'qty_kg'      => 'decimal:4',
        'hpp_satuan'  => 'decimal:2',
        'total_nilai' => 'decimal:2',
        'deleted_st'  => 'boolean',
        'active_st'   => 'boolean',
    ];

    /**
     * Relasi ke Header Produksi Harian
     */
    public function produksi(): BelongsTo
    {
        return $this->belongsTo(DatProduksiHarian::class, 'produksi_id', 'produksi_id');
    }

    /**
     * Relasi ke Master Barang WIP
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
