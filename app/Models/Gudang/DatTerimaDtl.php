<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatTerimaDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_terima_dtl';
    protected $primaryKey = 'terimadtl_id';

    protected $fillable = [
        'terima_id',
        'podtl_id',
        'qcdtl_id',
        'barang_id',
        'batch_no',
        'expired_tgl',
        'terima_qty',
        'reject_qty',
        'grade_cd',
        'kadar_air_persen',
        'refraksi_persen',
        'harga_nominal',
        'diskon_persen',
        'diskon_nominal',
        'potongan_nominal',
        'harga_netto',
        'subtotal_netto',
        'ppn_tipe',
        'ppn_persen',
        'ppn_nominal',
        'subtotal_tagihan',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'expired_tgl'      => 'date',
        'terima_qty'       => 'decimal:4',
        'reject_qty'       => 'decimal:4',
        'kadar_air_persen' => 'decimal:2',
        'refraksi_persen'  => 'decimal:2',
        'harga_nominal'    => 'decimal:4',
        'diskon_persen'    => 'decimal:2',
        'diskon_nominal'   => 'decimal:4',
        'potongan_nominal' => 'decimal:4',
        'harga_netto'      => 'decimal:4',
        'subtotal_netto'   => 'decimal:4',
        'ppn_persen'       => 'decimal:2',
        'ppn_nominal'      => 'decimal:4',
        'subtotal_tagihan' => 'decimal:4',
        'deleted_st'       => 'boolean',
        'active_st'        => 'boolean',
    ];

    /**
     * Relasi ke Header Terima
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(DatTerimaHdr::class, 'terima_id', 'terima_id');
    }

    /**
     * Relasi ke Detail PO asal (jika ada)
     */
    public function poDetail(): BelongsTo
    {
        return $this->belongsTo(DatPoDtl::class, 'podtl_id', 'podtl_id');
    }

    /**
     * Relasi ke Master Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
