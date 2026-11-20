<?php
// tests/Unit/WebhookServiceTest.php
namespace Tests\Unit;

use App\Services\WhatsApp\WebhookService;
use PHPUnit\Framework\TestCase;

class WebhookServiceTest extends TestCase
{
    public function test_periode_dikenali_dari_berbagai_format(): void
    {
        $this->assertSame('2026-09', WebhookService::periode('September 2026'));
        $this->assertSame('2026-08', WebhookService::periode('agustus 2026'));
        $this->assertSame('2026-08', WebhookService::periode('Agu 2026'));
        $this->assertSame('2026-12', WebhookService::periode('2026-12'));
        $this->assertNull(WebhookService::periode('2026-13'));
        $this->assertNull(WebhookService::periode('bulan lalu'));
        $this->assertNull(WebhookService::periode('September'));
    }

    public function test_pesan_slip_dibaca(): void
    {
        $this->assertSame(['slip' => true, 'periode' => null, 'salah' => false], WebhookService::baca('SLIP'));
        $this->assertSame(['slip' => true, 'periode' => null, 'salah' => false], WebhookService::baca('  slip  '));
        $this->assertSame(['slip' => true, 'periode' => '2026-09', 'salah' => false], WebhookService::baca('Slip September 2026'));
        $this->assertSame(['slip' => true, 'periode' => null, 'salah' => true], WebhookService::baca('slip kemarin'));
        $this->assertFalse(WebhookService::baca('halo')['slip']);
        $this->assertFalse(WebhookService::baca('')['slip']);
    }

    public function test_status_hanya_boleh_maju(): void
    {
        $this->assertTrue(WebhookService::naikStatus('terkirim_api', 'delivered'));
        $this->assertTrue(WebhookService::naikStatus('delivered', 'read'));
        $this->assertTrue(WebhookService::naikStatus(null, 'terkirim_api'));
        $this->assertFalse(WebhookService::naikStatus('read', 'delivered'));   // datang terlambat
        $this->assertFalse(WebhookService::naikStatus('delivered', 'terkirim_api'));
        $this->assertTrue(WebhookService::naikStatus('delivered', 'failed'));  // gagal selalu dicatat
        $this->assertTrue(WebhookService::naikStatus('failed', 'delivered'));
    }
}
