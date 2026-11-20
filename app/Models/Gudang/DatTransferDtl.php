<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatTransferDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_transfer_dtl';
    protected $primaryKey = 'transfer_dtl_id';

    protected $fillable = [
        'transfer_id',
        'barang_id',
        'batch_no',
        'grade_cd',
        'expired_tgl',
        'qty_transfer',
        'harga_satuan',
        'total_harga',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'expired_tgl'  => 'date',
        'qty_transfer' => 'decimal:4',
        'harga_satuan' => 'decimal:4',
        'total_harga'  => 'decimal:4',
        'deleted_st'   => 'boolean',
        'active_st'    => 'boolean',
        'version_no'   => 'integer',
    ];

    /**
     * Relasi ke Header Transfer
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatTransferHdr::class, 'transfer_id', 'transfer_id');
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
