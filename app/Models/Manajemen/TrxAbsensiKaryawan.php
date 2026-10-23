<?php
// app/Models/Manajemen/TrxAbsensiKaryawan.php
namespace App\Models\Manajemen;

use Illuminate\Database\Eloquent\Model;

class TrxAbsensiKaryawan extends Model
{
    protected $table = 'trx_absensi_karyawan';
    protected $guarded = [];
    protected $casts = [
        'tanggal' => 'date',
        'jadwal_masuk' => 'datetime', 'jadwal_pulang' => 'datetime',
        'masuk_finger' => 'datetime', 'masuk_bulat' => 'datetime',
        'pulang_finger' => 'datetime', 'pulang_bulat' => 'datetime',
        'istirahat' => 'float', 'standar_jam' => 'float', 'potongan_jam' => 'float',
        'total_jam' => 'float', 'lembur_jam' => 'float', 'kehadiran' => 'float',
        'istirahat_bawaan' => 'boolean', 'is_manual' => 'boolean',
    ];

    /** Halaman tempat masalah ini sebaiknya diperbaiki: 'jadwal', 'fingerprint', atau null jika OK. */
    public function getSumberMasalahAttribute(): ?string
    {
        if ($this->status === 'OK') {
            return null;
        }
        if ($this->status === 'Tanpa jadwal') {
            return 'jadwal';
        }
        if ($this->departemen_cd === 'SECURITY' && $this->kode_shift === 'L') {
            return 'jadwal';
        }

        return 'fingerprint';
    }
}