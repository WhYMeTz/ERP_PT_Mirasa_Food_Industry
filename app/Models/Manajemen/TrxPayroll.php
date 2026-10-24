<?php

namespace App\Models\Manajemen;

use Illuminate\Database\Eloquent\Model;

class TrxPayroll extends Model
{
    protected $table = 'trx_payroll';
    protected $guarded = [];
    protected $casts = [
        'hasil'      => 'array',
        'final_at'   => 'datetime',
        'dibayar_at' => 'datetime',
    ];
}