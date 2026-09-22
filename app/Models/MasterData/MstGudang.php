<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstGudang extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_gudang';
    protected $primaryKey = 'gudang_id';

    protected $fillable = [
        'gudang_cd',
        'gudang_nm',
        'tipe_gudang_cd',
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
