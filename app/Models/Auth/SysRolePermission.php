<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class SysRolePermission extends Model
{
    protected $table = 'sys_role_permissions';
    protected $primaryKey = 'permission_id';

    protected $fillable = [
        'role_cd',
        'permission_cd',
        'allowed_st',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allowed_st' => 'boolean',
    ];
}
