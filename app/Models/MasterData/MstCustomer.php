<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstCustomer extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_customer';
    protected $primaryKey = 'customer_id';

    public const KLASIFIKASI_IFM = 'IFM';
    public const KLASIFIKASI_MANUAL = 'MANUAL';
    public const KLASIFIKASI_IDM_ALFA = 'IDM_ALFA';

    protected $fillable = [
        'customer_cd',
        'customer_nm',
        'klasifikasi',
        'wilayah',
        'kode_pelanggan',
        'top_txt',
        'default_ppn',
        'kontak_no',
        'alamat_txt',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'deleted_st' => 'boolean',
        'active_st' => 'boolean',
    ];

    public function isIfm(): bool
    {
        return $this->klasifikasi === self::KLASIFIKASI_IFM;
    }

    public function isManual(): bool
    {
        return $this->klasifikasi === self::KLASIFIKASI_MANUAL;
    }

    public function isModernTrade(): bool
    {
        return $this->klasifikasi === self::KLASIFIKASI_IDM_ALFA;
    }
}
