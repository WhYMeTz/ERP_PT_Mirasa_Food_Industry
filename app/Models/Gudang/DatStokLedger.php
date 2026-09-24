<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatStokLedger extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_stok_ledger';
    protected $primaryKey = 'ledger_id';

    protected $fillable = [
        'gudang_id',
        'barang_id',
        'batch_no',
        'transaksi_tgl',
        'dokumen_no',
        'tipe_transaksi_cd',
        'qty',
        'saldoakhir_qty',
        'keterangan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'transaksi_tgl'  => 'datetime',
        'qty'            => 'decimal:4',
        'saldoakhir_qty' => 'decimal:4',
        'deleted_st'     => 'boolean',
        'active_st'      => 'boolean',
    ];

    /**
     * Relasi ke Gudang
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
