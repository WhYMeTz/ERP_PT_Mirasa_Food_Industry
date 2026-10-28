<?php

namespace Tests\Unit;

use App\Services\Manajemen\JadwalService;
use PHPUnit\Framework\TestCase;

class JadwalManualTest extends TestCase
{
    private function karyawan(): array
    {
        return [
            ['id' => '000000504', 'nama' => 'DINI MARDIANI', 'kelompok' => 'Langsung Packing'],
            ['id' => '25', 'nama' => 'WARDOYO', 'kelompok' => 'Security'],
        ];
    }

    public function test_hanya_hari_yang_dipilih_dan_semua_karyawan(): void
    {
        // 2026-08-03 = Senin ... 2026-08-09 = Minggu; pilih Senin-Sabtu
        $rows = (new JadwalService())->susunBaris($this->karyawan(), '2026-08-03', '2026-08-09', [1, 2, 3, 4, 5, 6], 'A', null, null);

        $this->assertSame(12, count($rows));                       // 6 hari x 2 karyawan
        $this->assertSame('2026-08-03', $rows[0]['tanggal']);
        $this->assertSame('2026-08-08', end($rows)['tanggal']);    // Sabtu, Minggu dilewati
    }

    public function test_id_dipadkan_dan_kelompok_bawaan_karyawan(): void
    {
        $rows = (new JadwalService())->susunBaris($this->karyawan(), '2026-08-03', '2026-08-03', [1], 'A', null, null);

        $this->assertSame('000000025', $rows[1]['id_absen']);
        $this->assertSame('Langsung Packing', $rows[0]['kelompok_kerja']);
        $this->assertSame('Security', $rows[1]['kelompok_kerja']);
        $this->assertSame(null, $rows[0]['istirahat_jam']);
    }

    public function test_kelompok_dan_istirahat_dari_form_menimpa_bawaan(): void
    {
        $rows = (new JadwalService())->susunBaris($this->karyawan(), '2026-08-03', '2026-08-03', [1], 'B', 'Tidak Langsung', 1.5);

        $this->assertSame('Tidak Langsung', $rows[0]['kelompok_kerja']);
        $this->assertSame('Tidak Langsung', $rows[1]['kelompok_kerja']);
        $this->assertSame(1.5, $rows[0]['istirahat_jam']);
        $this->assertSame('B', $rows[0]['kode_shift']);
    }

    public function test_hari_tidak_cocok_menghasilkan_kosong(): void
    {
        // satu hari Senin tetapi hanya Minggu yang dipilih
        $this->assertSame([], (new JadwalService())->susunBaris($this->karyawan(), '2026-08-03', '2026-08-03', [7], 'A', null, null));
    }
}
