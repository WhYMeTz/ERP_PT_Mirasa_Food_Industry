<?php
// tests/Feature/WhatsAppCloudHttpTest.php — memakai Http::fake, tidak ada pesan sungguhan yang terkirim.
namespace Tests\Feature;

use App\Services\WhatsApp\WhatsAppCloud;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class WhatsAppCloudHttpTest extends TestCase
{
    private function klien(): WhatsAppCloud
    {
        return new WhatsAppCloud([
            'api_version' => 'v25.0', 'graph_url' => 'https://graph.facebook.com', 'access_token' => 'TOKEN',
            'phone_number_id' => '123', 'template_name' => 'slip_pembayaran', 'template_language' => 'id',
            'timeout' => 10, 'no_hrd_info' => 'wa.me/62800',
        ]);
    }

    public function test_slip_template_mengunggah_pdf_lalu_mengirim(): void
    {
        Http::fake([
            'graph.facebook.com/v25.0/123/media'    => Http::response(['id' => 'MEDIA1']),
            'graph.facebook.com/v25.0/123/messages' => Http::response(['messages' => [['id' => 'wamid.ABC']]]),
        ]);

        $id = $this->klien()->kirimSlipTemplate('0812-3456-7890', '%PDF-1.4 tes', 'Slip.pdf', 'DODY', 'Agustus 2026');

        $this->assertSame('wamid.ABC', $id);
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/123/media') && $r->hasHeader('Authorization', 'Bearer TOKEN'));
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/123/messages')
            && $r['to'] === '6281234567890'
            && $r['template']['name'] === 'slip_pembayaran'
            && $r['template']['components'][0]['parameters'][0]['document']['id'] === 'MEDIA1');
    }

    public function test_error_dari_meta_menjadi_exception_yang_jelas(): void
    {
        Http::fake([
            'graph.facebook.com/*/media' => Http::response(['error' => ['message' => 'Invalid token', 'code' => 190]], 401),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid token (code: 190)');
        $this->klien()->kirimSlipTemplate('081234567890', 'x', 'a.pdf', 'A', 'P');
    }

    public function test_nomor_tidak_valid_tidak_memanggil_jaringan(): void
    {
        Http::fake();

        try {
            $this->klien()->kirimTeks('021-5551234', 'hai');
            $this->fail('Seharusnya melempar exception');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('tidak valid', $e->getMessage());
        }
        Http::assertNothingSent();
    }
}
