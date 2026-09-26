<?php

namespace App\Models\Auth;

use App\Models\MasterData\MstGudang;
use App\Models\User;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SysUserGudang extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'sys_user_gudang';
    protected $primaryKey = 'user_gudang_id';

    protected $fillable = [
        'user_id',
        'gudang_id',
        'is_primary',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }
}
