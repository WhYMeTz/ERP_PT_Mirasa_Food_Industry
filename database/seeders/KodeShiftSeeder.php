<?php

namespace Database\Seeders;

use App\Models\Manajemen\MstKodeShift;
use Illuminate\Database\Seeder;

class KodeShiftSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // kode, hari, masuk, pulang, lintas, istirahat, standar, jenis, keterangan
            ['A', 'Senin-Jumat', '07:00', '15:00', false, 1, 7, 'normal', null],
            ['B', 'Senin-Jumat', '15:00', '23:00', false, 1, 7, 'normal', null],
            ['C', 'Senin-Jumat', '06:00', '14:00', false, 1, 7, 'normal', null],
            ['D', 'Senin-Jumat', '05:00', '13:00', false, 1, 7, 'normal', null],
            ['E', 'Senin-Jumat', '08:00', '16:00', false, 1, 7, 'normal', null],
            ['F', 'Senin-Jumat', '13:00', '21:00', false, 1, 7, 'normal', null],
            ['G', 'Sabtu', '12:30', '18:00', false, 0, 5.5, 'normal', null],
            ['H', 'Sabtu', '08:00', '13:30', false, 0, 5.5, 'normal', null],
            ['I', 'Sabtu', '07:00', '12:30', false, 0, 5.5, 'normal', null],
            ['J', 'Senin-Minggu', '07:00', '19:00', false, 0, 12, 'security', 'Khusus security'],
            ['K', 'Senin-Minggu', '19:00', '07:00', true, 0, 12, 'security', 'Khusus security, pulang hari berikutnya'],
            ['L', 'Minggu', null, null, false, 0, null, 'minggu', 'Seluruh jam kerja = lembur (kecuali security)'],
            ['M', 'Semua hari', null, null, false, 0, null, 'driver', 'Driver: 1x finger = 1 hari hadir'],
            ['OFF', '-', null, null, false, 0, null, 'libur', 'Libur / tidak dijadwalkan'],
        ];

        foreach ($data as [$kode, $hari, $masuk, $pulang, $lintas, $ist, $std, $jenis, $ket]) {
            MstKodeShift::updateOrCreate(['kode' => $kode], [
                'hari' => $hari, 'jam_masuk' => $masuk, 'jam_pulang' => $pulang,
                'lintas_hari' => $lintas, 'istirahat_default' => $ist,
                'standar_jam' => $std, 'jenis' => $jenis, 'keterangan' => $ket,
            ]);
        }
    }
}