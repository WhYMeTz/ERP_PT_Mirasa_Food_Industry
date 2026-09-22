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

    protected $fillable = [
        'customer_cd',
        'customer_nm',
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
}
