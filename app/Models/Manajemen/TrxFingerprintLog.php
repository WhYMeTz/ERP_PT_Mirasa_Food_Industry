<?php

namespace App\Models\Manajemen;
use Illuminate\Database\Eloquent\Model;

class TrxFingerprintLog extends Model
{
    protected $table = 'trx_fingerprint_log';
    protected $guarded = [];
    protected $casts = ['scan_at' => 'datetime'];
}
