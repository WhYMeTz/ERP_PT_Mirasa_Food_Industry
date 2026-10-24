<?php

namespace App\Models\Keuangan;

use App\Models\Gudang\DatTerimaHdr;
use App\Models\MasterData\MstBarang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatHutangDtl extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_hutang_dtl';
    protected $primaryKey = 'hutangdtl_id';

    protected $fillable = [
        'hutang_id',
        'terima_id',
        'terima_dtl_id',
        'barang_id',
        'deskripsi',
        'qty',
        'satuan',
        'harga_satuan',
        'subtotal',
        'diskon_persen',
        'diskon_nominal',
        'potongan_nominal',
        'ppn_nominal',
        'ppn_tipe',
        'dpp_nominal',
        'total_netto',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'qty'              => 'float',
        'harga_satuan'     => 'float',
        'subtotal'         => 'float',
        'diskon_persen'    => 'float',
        'diskon_nominal'   => 'float',
        'potongan_nominal' => 'float',
        'ppn_nominal'      => 'float',
        'dpp_nominal'      => 'float',
        'total_netto'      => 'float',
        'deleted_st'       => 'boolean',
        'active_st'        => 'boolean',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(DatHutangHdr::class, 'hutang_id', 'hutang_id');
    }

    public function terima(): BelongsTo
    {
        return $this->belongsTo(DatTerimaHdr::class, 'terima_id', 'terima_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_id', 'barang_id');
    }
}
