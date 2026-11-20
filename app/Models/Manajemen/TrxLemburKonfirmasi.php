<?php

namespace App\Models\Manajemen;

use Illuminate\Database\Eloquent\Model;

class TrxLemburKonfirmasi extends Model
{
    protected $table = 'trx_lembur_konfirmasi';

    protected $fillable = ['tanggal', 'id_absen', 'hitung', 'oleh'];

    protected $casts = ['tanggal' => 'date', 'hitung' => 'boolean'];
}
