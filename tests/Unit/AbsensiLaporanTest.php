<?php

namespace Tests\Unit;

use App\Services\Manajemen\AbsensiLaporan;
use PHPUnit\Framework\TestCase;

class AbsensiLaporanTest extends TestCase
{
    private function baris(): array
    {
        $b = fn ($id, $nama, $hadir, $jam, $pot, $lem, $st, $kel = 'Langsung Packing') => [
            'id_absen' => $id, 'nik' => 'N' . $id, 'nama' => $nama, 'departemen' => 'Produksi', 'kelompok' => $kel,
            'kehadiran' => $hadir, 'total_jam' => $jam, 'potongan_jam' => $pot, 'lembur_jam' => $lem, 'status' => $st,
        ];

        return [
            $b('2', 'DINI', 0.5, 3.5, 3.5, 0, 'OK'),
            $b('1', 'ANDI', 1, 8, 0, 0.5, 'OK'),
            $b('2', 'DINI', 1, 8, 0, 0, 'Perlu dicek', 'Tidak Langsung'),
            $b('1', 'ANDI', 0, 0, 7, 0, 'Tidak hadir'),
        ];
    }

    public function test_rekap_per_karyawan_dijumlahkan_dan_urut_nama(): void
    {
        $r = AbsensiLaporan::rekap($this->baris());

        $this->assertSame(2, count($r));
        $this->assertSame('ANDI', $r[0]['nama']);
        $this->assertSame(2, $r[0]['hari_data']);
        $this->assertSame(1.0, $r[0]['kehadiran']);
        $this->assertSame(8.0, $r[0]['total_jam']);
        $this->assertSame(7.0, $r[0]['potongan']);
        $this->assertSame(0.5, $r[0]['lembur']);
        $this->assertSame(1, $r[0]['tidak_hadir']);
        $this->assertSame(1.5, $r[1]['kehadiran']);
        $this->assertSame(1, $r[1]['perlu']);
        $this->assertSame('Tidak Langsung', $r[1]['kelompok']);
    }

    public function test_total_keseluruhan(): void
    {
        $t = AbsensiLaporan::total(AbsensiLaporan::rekap($this->baris()));

        $this->assertSame(2, $t['karyawan']);
        $this->assertSame(4, $t['hari_data']);
        $this->assertSame(2.5, $t['kehadiran']);
        $this->assertSame(19.5, $t['total_jam']);
        $this->assertSame(10.5, $t['potongan']);
        $this->assertSame(0.5, $t['lembur']);
    }
}
