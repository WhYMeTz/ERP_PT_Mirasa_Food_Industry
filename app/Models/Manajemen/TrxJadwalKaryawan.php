<?php
// app/Models/Manajemen/TrxJadwalKaryawan.php
namespace App\Models\Manajemen;

use Illuminate\Database\Eloquent\Model;

class TrxJadwalKaryawan extends Model
{
    protected $table = 'trx_jadwal_karyawan';
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];
}