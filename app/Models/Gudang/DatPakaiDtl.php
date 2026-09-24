<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatPakaiDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_pakai_dtl';
    protected $primaryKey = 'pakaidtl_id';

    protected $fillable = [
        'pakai_id',
        'barang_id',
        'batch_no',
        'qty_keluar',
        'harga_satuan',
        'total_harga',
        'keterangan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'qty_keluar'   => 'decimal:4',
        'harga_satuan' => 'decimal:4',
        'total_harga'  => 'decimal:4',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
    ];

    /**
     * Relasi ke Header Pemakaian
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatPakaiHdr::class, 'pakai_id', 'pakai_id');
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
