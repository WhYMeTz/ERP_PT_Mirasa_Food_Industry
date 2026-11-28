<?php

namespace App\Models\Produksi;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatPackagingBufferLog extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_packaging_buffer_log';
    protected $primaryKey = 'buffer_id';

    protected $fillable = [
        'barang_jadi_id',
        'pack_id',
        'laporan_id',
        'transaksi_tgl',
        'tipe_transaksi',
        'qty_pack',
        'saldo_akhir_pack',
        'keterangan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'transaksi_tgl'    => 'date',
        'qty_pack'         => 'decimal:4',
        'saldo_akhir_pack' => 'decimal:4',
        'deleted_st'       => 'boolean',
        'active_st'        => 'boolean',
        'version_no'       => 'integer',
    ];

    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_jadi_id', 'barang_id');
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(DatPackagingHdr::class, 'pack_id', 'pack_id');
    }

    public function laporanOperator(): BelongsTo
    {
        return $this->belongsTo(DatLaporanKemasOperator::class, 'laporan_id', 'laporan_id');
    }
}
