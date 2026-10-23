<?php

namespace Tests\Unit;

use App\Services\Manajemen\PayrollHitung;
use PHPUnit\Framework\TestCase;

class PayrollHarianTest extends TestCase
{
    public function test_jumlah_harian_sama_dengan_hitungan_bulanan(): void
    {
        $gaji = 97300;
        $abs = [
            ['status' => 'OK', 'jenis' => 'jam', 'standar_jam' => 7, 'potongan_jam' => 0.5, 'lembur_jam' => 0],
            ['status' => 'OK', 'jenis' => 'jam', 'standar_jam' => 7, 'potongan_jam' => 0, 'lembur_jam' => 2.5],
            ['status' => 'OK', 'jenis' => 'minggu', 'lembur_jam' => 6],
            ['status' => 'OK', 'jenis' => 'driver', 'kehadiran' => 1],
            ['status' => 'Tidak hadir', 'jenis' => 'jam', 'standar_jam' => 7, 'potongan_jam' => 7, 'lembur_jam' => 0],
            ['status' => 'Perlu dicek', 'jenis' => 'jam'],
        ];

        $bulanan = PayrollHitung::hitung(['gaji_harian' => $gaji], $abs, 6);

        $pokok = $h1 = $h2 = $k = 0.0;
        foreach ($abs as $r) {
            $h = PayrollHitung::hitungHari($gaji, $r);
            if ($h === null) {
                continue;
            }
            $pokok += $h['pokok'];
            $h1    += $h['honor1'];
            $h2    += $h['honor2'];
            $k     += $h['k'];
        }

        $this->assertSame($bulanan['gaji_pokok'], (int) round($pokok));
        $this->assertSame($bulanan['honor_lembur_1'], (int) round($h1));
        $this->assertSame($bulanan['honor_lembur_2'], (int) round($h2));
        $this->assertSame($bulanan['tunjangan_masa_kerja'], (int) round(PayrollHitung::tunjanganMasaKerjaPerHari(6) * $k));
    }

    public function test_status_belum_valid_tidak_dihitung(): void
    {
        $this->assertNull(PayrollHitung::hitungHari(97300, ['status' => 'Perlu dicek']));
        $this->assertNull(PayrollHitung::hitungHari(97300, ['status' => 'Tanpa jadwal']));
    }

    public function test_hari_minggu_semua_jam_jadi_lembur_dua(): void
    {
        $h = PayrollHitung::hitungHari(70000, ['status' => 'OK', 'jenis' => 'minggu', 'lembur_jam' => 4]);
        $this->assertEqualsWithDelta(0, $h['pokok'], 0.001);
        $this->assertEqualsWithDelta(70000 / 7 * 2 * 4, $h['honor2'], 0.001);
    }
}