<?php

namespace Tests\Unit;

use App\Services\Manajemen\AbsensiService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Aturan final:
 * - Pembulatan scan ke kelipatan 30 menit terdekat (07:15 naik ke 07:30). Masuk lebih awal dari jadwal tidak dihitung.
 * - Jam hadir = jam di dalam jadwal (lembur tidak ikut). Istirahat tidak dikurangkan dari jam hadir.
 * - Standar mengikuti Kode Shift (7 jam, Sabtu 5,5 jam), bukan jam pulang - jam masuk - istirahat.
 * - Kehadiran = jam hadir / standar, maksimal 1. Potongan = standar - jam hadir (tidak kurang dari 0).
 */
class AbsensiHitungTest extends TestCase
{
    /** $std = standar jam dari Kode Shift. Di aplikasi nilainya selalu diteruskan, jadi di tes pun begitu. */
    private function h(string $jm, string $jp, float $ist, string $sm, string $sp, ?float $std = 7): array
    {
        return AbsensiService::hitungShift(
            Carbon::parse("2026-08-28 $jm"), Carbon::parse("2026-08-28 $jp"), $ist,
            Carbon::parse("2026-08-28 $sm"), Carbon::parse("2026-08-28 $sp"),
            $std,
        );
    }

    public function test_contoh_1_nur_faris(): void
    {
        // 04:57 -> 05:00, 12:01 -> 12:00 : hadir 7 jam = standar 7 jam
        $r = $this->h('05:00', '13:00', 1, '04:57:00', '12:01:00');
        $this->assertEqualsWithDelta(7, $r['standar_jam'], 0.001);
        $this->assertEqualsWithDelta(7, $r['total_jam'], 0.001);
        $this->assertEqualsWithDelta(0, $r['potongan_jam'], 0.001);
        $this->assertEqualsWithDelta(0, $r['lembur_jam'], 0.001);
        $this->assertEqualsWithDelta(1, $r['kehadiran'], 0.0001);
    }

    public function test_contoh_2_fahri_udin(): void
    {
        // hadir sampai jam pulang jadwal (8 jam), sisanya 3 jam lembur
        $r = $this->h('06:00', '14:00', 1, '06:10:00', '17:01:00');
        $this->assertEqualsWithDelta(8, $r['total_jam'], 0.001);
        $this->assertEqualsWithDelta(0, $r['potongan_jam'], 0.001);
        $this->assertEqualsWithDelta(3, $r['lembur_jam'], 0.001);
        $this->assertEqualsWithDelta(1, $r['kehadiran'], 0.0001);
    }

    public function test_datang_terlalu_awal_tidak_dihitung(): void
    {
        $r = $this->h('07:00', '15:00', 1, '06:40:00', '15:00:00');
        $this->assertSame('07:00', $r['masuk_bulat']->format('H:i'));
        $this->assertEqualsWithDelta(0, $r['potongan_jam'], 0.001);
        $this->assertEqualsWithDelta(1, $r['kehadiran'], 0.0001);
    }

    public function test_terlambat_tertutup_lembur(): void
    {
        $r = $this->h('06:00', '14:00', 1, '07:10:00', '17:01:00');
        $this->assertEqualsWithDelta(0, $r['potongan_jam'], 0.001);
        $this->assertEqualsWithDelta(3, $r['lembur_jam'], 0.001);
        $this->assertEqualsWithDelta(1, $r['kehadiran'], 0.0001);
    }

    public function test_terlambat_sejam_tanpa_lembur_tidak_dipotong(): void
    {
        // 07:10 -> 07:00. Hadir 07:00-14:00 = 7 jam = standar, jadi tidak ada potongan
        $r = $this->h('06:00', '14:00', 1, '07:10:00', '14:00:00');
        $this->assertEqualsWithDelta(7, $r['total_jam'], 0.001);
        $this->assertEqualsWithDelta(0, $r['potongan_jam'], 0.001);
        $this->assertEqualsWithDelta(1, $r['kehadiran'], 0.0001);
    }

    public function test_terlambat_lebih_dari_sejam_tanpa_lembur_dipotong(): void
    {
        // 07:40 -> 07:30. Hadir 07:30-14:00 = 6,5 jam, kurang 0,5 jam dari standar 7
        $r = $this->h('06:00', '14:00', 1, '07:40:00', '14:00:00');
        $this->assertEqualsWithDelta(6.5, $r['total_jam'], 0.001);
        $this->assertEqualsWithDelta(0.5, $r['potongan_jam'], 0.001);
        $this->assertEqualsWithDelta(0.9286, $r['kehadiran'], 0.0001);
    }

    public function test_jumat_istirahat_1_5_tetap_standar_7_dan_hadir_penuh(): void
    {
        // Jumat 07:00-15:00 istirahat 1,5 jam, tetap standar 7 jam (dari Kode Shift)
        $r = $this->h('07:00', '15:00', 1.5, '06:40:00', '15:00:00');
        $this->assertEqualsWithDelta(7, $r['standar_jam'], 0.001);
        $this->assertEqualsWithDelta(8, $r['total_jam'], 0.001);
        $this->assertEqualsWithDelta(0, $r['potongan_jam'], 0.001);
        $this->assertEqualsWithDelta(1, $r['kehadiran'], 0.0001);
    }

    public function test_sabtu_tanpa_istirahat_standar_5_5(): void
    {
        // 12:09 -> 12:00, tetapi jadwal mulai 12:30. Hadir 12:30-17:30 = 5 jam dari standar 5,5
        $r = $this->h('12:30', '18:00', 0, '12:09:00', '17:30:00', 5.5);
        $this->assertEqualsWithDelta(5.5, $r['standar_jam'], 0.001);
        $this->assertEqualsWithDelta(5, $r['total_jam'], 0.001);
        $this->assertEqualsWithDelta(0.5, $r['potongan_jam'], 0.001);
        $this->assertEqualsWithDelta(0.9091, $r['kehadiran'], 0.0001);
    }
}