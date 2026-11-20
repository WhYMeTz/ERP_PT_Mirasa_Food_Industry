<?php
// app/Models/Manajemen/TrxJadwalKaryawan.php
namespace App\Models\Manajemen;

use Illuminate\Database\Eloquent\Model;

class TrxJadwalKaryawan extends Model
{
    /** Pilihan jam istirahat yang diizinkan (jam). Ubah di sini bila nanti perlu pilihan lain. */
    public const ISTIRAHAT = [0.0, 0.5, 1.0, 1.5, 2.0];

    protected $table = 'trx_jadwal_karyawan';
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];
}