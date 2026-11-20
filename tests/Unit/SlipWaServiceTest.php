<?php
// tests/Unit/SlipWaServiceTest.php
namespace Tests\Unit;

use App\Services\Manajemen\SlipWaService;
use PHPUnit\Framework\TestCase;

class SlipWaServiceTest extends TestCase
{
    public function test_draft_tidak_boleh_dikirim(): void
    {
        $this->assertStringContainsString('Draft', SlipWaService::putusan('draft', true, '6281234567890', null, false));
    }

    public function test_slip_belum_siap_atau_nomor_kosong_dilewati(): void
    {
        $this->assertStringContainsString('belum siap', SlipWaService::putusan('final', false, '6281234567890', null, false));
        $this->assertStringContainsString('Nomor WhatsApp', SlipWaService::putusan('final', true, null, null, false));
    }

    public function test_yang_sudah_terkirim_tidak_dikirim_ulang_kecuali_diminta(): void
    {
        foreach (['terkirim_api', 'delivered', 'read'] as $st) {
            $this->assertStringContainsString('Sudah terkirim', SlipWaService::putusan('final', true, '6281234567890', $st, false));
            $this->assertNull(SlipWaService::putusan('final', true, '6281234567890', $st, true));
        }
        // gagal atau dilewati boleh dicoba lagi
        $this->assertNull(SlipWaService::putusan('dibayar', true, '6281234567890', 'failed', false));
        $this->assertNull(SlipWaService::putusan('final', true, '6281234567890', 'dilewati', false));
        $this->assertNull(SlipWaService::putusan('final', true, '6281234567890', null, false));
    }

    public function test_nama_file_aman(): void
    {
        $this->assertSame('Slip_Gaji_2026-08_DODY_WIDHIYANT.pdf', SlipWaService::namaFile('2026-08', 'DODY WIDHIYANT'));
        $this->assertSame('Slip_Gaji_2026-08_A_B_C.pdf', SlipWaService::namaFile('2026-08', 'A/B\\C'));
        $this->assertSame('Slip_Gaji_2026-08_Karyawan.pdf', SlipWaService::namaFile('2026-08', '###'));
    }
}
