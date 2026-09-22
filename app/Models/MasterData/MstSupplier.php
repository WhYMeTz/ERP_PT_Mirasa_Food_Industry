<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstSupplier extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_supplier';
    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'supplier_cd',
        'supplier_nm',
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
