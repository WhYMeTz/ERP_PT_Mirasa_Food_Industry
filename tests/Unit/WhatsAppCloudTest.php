<?php
// tests/Unit/WhatsAppCloudTest.php
namespace Tests\Unit;

use App\Services\WhatsApp\WhatsAppCloud;
use PHPUnit\Framework\TestCase;

class WhatsAppCloudTest extends TestCase
{
    private function klien(array $ganti = []): WhatsAppCloud
    {
        return new WhatsAppCloud($ganti + [
            'api_version' => 'v25.0', 'graph_url' => 'https://graph.facebook.com', 'access_token' => 'TOKEN',
            'phone_number_id' => '123', 'template_name' => 'slip_pembayaran', 'template_language' => 'id',
            'timeout' => 60, 'no_hrd_info' => 'wa.me/6280000000000',
        ]);
    }

    public function test_nomor_dinormalkan_dan_divalidasi(): void
    {
        $this->assertSame('6281234567890', WhatsAppCloud::nomor('0812-3456-7890'));
        $this->assertSame('6281234567890', WhatsAppCloud::nomor('+62 812 3456 7890'));
        $this->assertSame('6281234567890', WhatsAppCloud::nomor('81234567890'));
        $this->assertNull(WhatsAppCloud::nomor(null));
        $this->assertNull(WhatsAppCloud::nomor('021-5551234'));
        $this->assertNull(WhatsAppCloud::nomor('0812'));
    }

    public function test_pengaturan_yang_kurang_terdeteksi(): void
    {
        $this->assertSame([], $this->klien()->yangKurang());
        $this->assertSame(['WHATSAPP_ACCESS_TOKEN'], $this->klien(['access_token' => ''])->yangKurang());
        $this->assertSame(['WHATSAPP_PHONE_NUMBER_ID', 'WHATSAPP_TEMPLATE_NAME'],
            $this->klien(['phone_number_id' => null, 'template_name' => ' '])->yangKurang());
    }

    public function test_payload_template_sama_dengan_versi_python(): void
    {
        $p = $this->klien()->payloadTemplate('6281234567890', 'MEDIA1', 'Slip.pdf', 'DODY WIDHIYANT', 'Agustus 2026');

        $this->assertSame('template', $p['type']);
        $this->assertSame('slip_pembayaran', $p['template']['name']);
        $this->assertSame('id', $p['template']['language']['code']);

        [$header, $body] = $p['template']['components'];
        $this->assertSame('header', $header['type']);
        $this->assertSame('MEDIA1', $header['parameters'][0]['document']['id']);
        $this->assertSame('Slip.pdf', $header['parameters'][0]['document']['filename']);

        $this->assertSame(['name', 'month_gaji', 'no_hrd_info'], array_column($body['parameters'], 'parameter_name'));
        $this->assertSame(['DODY WIDHIYANT', 'Agustus 2026', 'wa.me/6280000000000'], array_column($body['parameters'], 'text'));
    }

    public function test_payload_dokumen_dan_teks(): void
    {
        $d = $this->klien()->payloadDokumen('62812', 'M', 'a.pdf', 'Halo');
        $this->assertSame('document', $d['type']);
        $this->assertSame('Halo', $d['document']['caption']);

        $t = $this->klien()->payloadTeks('62812', 'Pesan');
        $this->assertSame('text', $t['type']);
        $this->assertSame('Pesan', $t['text']['body']);
    }

    public function test_pesan_error_meta_terbaca(): void
    {
        $body = ['error' => ['message' => 'Invalid token', 'code' => 190, 'error_data' => ['details' => 'Sesi berakhir']]];
        $this->assertSame('WhatsApp API error: Invalid token (code: 190) - Sesi berakhir', WhatsAppCloud::pesanError($body, 401));
        $this->assertSame('WhatsApp API error: HTTP 502: gateway', WhatsAppCloud::pesanError('gateway', 502));
    }
}
