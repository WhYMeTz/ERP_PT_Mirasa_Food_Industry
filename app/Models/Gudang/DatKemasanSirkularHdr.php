<?php

namespace App\Models\Gudang;

use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstGudang;
use App\Models\Penjualan\DatPengeluaranHdr;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatKemasanSirkularHdr extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_kemasan_sirkular_hdr';
    protected $primaryKey = 'kemasan_sirkular_id';

    protected $fillable = [
        'sirkular_no',
        'sirkular_tgl',
        'customer_id',
        'pengeluaran_id',
        'gudang_id',
        'surat_jalan_customer_no',
        'supir_nm',
        'nopol_kendaraan',
        'ekspedisi_nm',
        'total_kirim_qty',
        'total_kembali_qty',
        'total_mulus_qty',
        'total_bmg_qty',
        'total_rusak_qty',
        'total_selisih_qty',
        'status_cd',
        'catatan_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'sirkular_tgl'      => 'date',
        'total_kirim_qty'   => 'decimal:4',
        'total_kembali_qty' => 'decimal:4',
        'total_mulus_qty'   => 'decimal:4',
        'total_bmg_qty'     => 'decimal:4',
        'total_rusak_qty'   => 'decimal:4',
        'total_selisih_qty' => 'decimal:4',
        'deleted_st'        => 'boolean',
        'active_st'         => 'boolean',
        'version_no'        => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MstCustomer::class, 'customer_id');
    }

    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(DatPengeluaranHdr::class, 'pengeluaran_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DatKemasanSirkularDtl::class, 'kemasan_sirkular_id');
    }
}

