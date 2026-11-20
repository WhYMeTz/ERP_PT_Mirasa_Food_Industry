<?php
// app/Http/Controllers/WhatsAppWebhookController.php
namespace App\Http\Controllers\Manajemen;

use App\Services\WhatsApp\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Throwable;

/** Pintu masuk webhook WhatsApp Cloud API: verifikasi (GET) dan penerimaan event (POST). Tanpa login dan tanpa CSRF. */
class WhatsAppWebhookController extends Controller
{
    /** Meta memanggil ini sekali saat Callback URL didaftarkan. */
    public function verifikasi(Request $request)
    {
        // PHP mengubah titik pada nama parameter menjadi garis bawah: hub.mode -> hub_mode
        $mode      = $request->query('hub_mode', $request->query('hub.mode'));
        $tokenMeta = (string) $request->query('hub_verify_token', $request->query('hub.verify_token', ''));
        $tantangan = (string) $request->query('hub_challenge', $request->query('hub.challenge', ''));
        $token     = (string) config('whatsapp.verify_token');

        if ($mode === 'subscribe' && $token !== '' && hash_equals($token, $tokenMeta)) {
            return response($tantangan, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Verifikasi webhook WhatsApp ditolak (verify token tidak cocok).');

        return response('Verification failed', 403);
    }

    /** Meta mengirim pesan masuk dan status pesan keluar ke sini. Balas 200 secepatnya, proses sesudah respons. */
    public function terima(Request $request, WebhookService $svc)
    {
        $mentah = $request->getContent();
        if (! $this->tandatanganSah($request, $mentah)) {
            return response('Invalid signature', 403);
        }

        $data = json_decode($mentah, true);
        $data = is_array($data) ? $data : [];

        app()->terminating(function () use ($svc, $data) {
            try {
                $svc->proses($data);
            } catch (Throwable $e) {
                Log::error('Webhook WhatsApp gagal diproses: ' . $e->getMessage());
            }
        });

        return response('EVENT_RECEIVED', 200);
    }

    private function tandatanganSah(Request $request, string $mentah): bool
    {
        $rahasia = (string) config('whatsapp.app_secret');
        if ($rahasia === '') {
            // Tanpa App Secret, tanda tangan tidak bisa diperiksa. Hanya diizinkan saat pengembangan lokal.
            Log::warning('META_APP_SECRET kosong: tanda tangan webhook tidak divalidasi.');

            return app()->environment('local');
        }

        $harapan = 'sha256=' . hash_hmac('sha256', $mentah, $rahasia);

        return hash_equals($harapan, (string) $request->header('X-Hub-Signature-256', ''));
    }
}
