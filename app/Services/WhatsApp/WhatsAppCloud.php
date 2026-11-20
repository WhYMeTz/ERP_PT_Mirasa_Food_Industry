<?php
// app/Services/WhatsApp/WhatsAppCloud.php
namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien WhatsApp Business Cloud API (Meta).
 * Padanan di PHP dari whatsapp_api_sender.py: unggah PDF -> kirim template slip_pembayaran (header dokumen
 * + 3 variabel: name, month_gaji, no_hrd_info), atau kirim dokumen/teks bebas (untuk balasan permintaan "SLIP").
 */
class WhatsAppCloud
{
    /** @param array<string,mixed> $cfg isi config('whatsapp') */
    public function __construct(private array $cfg)
    {
    }

    public static function dariConfig(): self
    {
        return new self((array) config('whatsapp'));
    }

    // ---------- murni (mudah diuji) ----------

    /** Nama pengaturan wajib yang belum diisi. */
    public function yangKurang(): array
    {
        $wajib = [
            'WHATSAPP_ACCESS_TOKEN'    => 'access_token',
            'WHATSAPP_PHONE_NUMBER_ID' => 'phone_number_id',
            'WHATSAPP_TEMPLATE_NAME'   => 'template_name',
        ];
        $kurang = [];
        foreach ($wajib as $env => $kunci) {
            if (trim((string) ($this->cfg[$kunci] ?? '')) === '') {
                $kurang[] = $env;
            }
        }

        return $kurang;
    }

    /** Nomor HP Indonesia -> format internasional tanpa plus (62812...). Null bila bukan nomor seluler yang valid. */
    public static function nomor(?string $hp): ?string
    {
        $n = preg_replace('/\D+/', '', (string) $hp);
        if ($n === '') {
            return null;
        }
        if (str_starts_with($n, '62')) {
            // sudah internasional
        } elseif (str_starts_with($n, '0')) {
            $n = '62' . substr($n, 1);
        } elseif (str_starts_with($n, '8')) {
            $n = '62' . $n;
        } else {
            return null;
        }

        return preg_match('/^628\d{8,11}$/', $n) ? $n : null;
    }

    /** Pesan template slip: header = dokumen PDF, body = 3 variabel bernama. */
    public function payloadTemplate(string $to, string $mediaId, string $namaFile, string $nama, string $periode): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'     => (string) $this->cfg['template_name'],
                'language' => ['code' => (string) ($this->cfg['template_language'] ?? 'id')],
                'components' => [
                    [
                        'type'       => 'header',
                        'parameters' => [['type' => 'document', 'document' => ['id' => $mediaId, 'filename' => $namaFile]]],
                    ],
                    [
                        'type'       => 'body',
                        'parameters' => [
                            ['type' => 'text', 'parameter_name' => 'name', 'text' => $nama],
                            ['type' => 'text', 'parameter_name' => 'month_gaji', 'text' => $periode],
                            ['type' => 'text', 'parameter_name' => 'no_hrd_info', 'text' => (string) ($this->cfg['no_hrd_info'] ?? '')],
                        ],
                    ],
                ],
            ],
        ];
    }

    /** Dokumen bebas + keterangan. Hanya diterima WhatsApp bila penerima membalas dalam 24 jam terakhir. */
    public function payloadDokumen(string $to, string $mediaId, string $namaFile, string $keterangan): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'document',
            'document'          => ['id' => $mediaId, 'filename' => $namaFile, 'caption' => $keterangan],
        ];
    }

    public function payloadTeks(string $to, string $isi): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'text',
            'text'              => ['preview_url' => false, 'body' => $isi],
        ];
    }

    /** Ubah balasan error Meta menjadi kalimat yang bisa dibaca. */
    public static function pesanError(mixed $body, int $status): string
    {
        if (is_array($body)) {
            $e = $body['error'] ?? [];
            $pesan = $e['message'] ?? json_encode($body);
            $teks = 'WhatsApp API error: ' . $pesan;
            if (! empty($e['code'])) {
                $teks .= ' (code: ' . $e['code'] . ')';
            }
            if (! empty($e['error_data']['details'])) {
                $teks .= ' - ' . $e['error_data']['details'];
            }

            return $teks;
        }

        return "WhatsApp API error: HTTP {$status}: " . (is_scalar($body) ? $body : json_encode($body));
    }

    private function alamat(string $bagian): string
    {
        return rtrim((string) $this->cfg['graph_url'], '/') . '/' . $this->cfg['api_version'] . '/' . $this->cfg['phone_number_id'] . '/' . $bagian;
    }

    // ---------- yang memanggil jaringan ----------

    /** Unggah PDF (isi biner) -> media id. */
    public function unggahPdf(string $isiPdf, string $namaFile): string
    {
        $resp = Http::withToken((string) $this->cfg['access_token'])
            ->timeout((int) ($this->cfg['timeout'] ?? 60))
            ->attach('file', $isiPdf, $namaFile, ['Content-Type' => 'application/pdf'])
            ->post($this->alamat('media'), ['messaging_product' => 'whatsapp', 'type' => 'application/pdf']);

        if (! $resp->successful()) {
            throw new RuntimeException(self::pesanError($resp->json() ?? $resp->body(), $resp->status()));
        }
        $id = $resp->json('id');
        if (! $id) {
            throw new RuntimeException('Unggah PDF berhasil tetapi media id tidak ditemukan.');
        }

        return (string) $id;
    }

    /** Kirim payload pesan; mengembalikan id pesan WhatsApp (wamid...). */
    public function kirim(array $payload): string
    {
        $resp = Http::withToken((string) $this->cfg['access_token'])
            ->timeout((int) ($this->cfg['timeout'] ?? 60))
            ->asJson()
            ->post($this->alamat('messages'), $payload);

        if (! $resp->successful()) {
            throw new RuntimeException(self::pesanError($resp->json() ?? $resp->body(), $resp->status()));
        }

        return (string) ($resp->json('messages.0.id') ?? '');
    }

    /** Kirim slip lewat template (untuk pengiriman massal / proaktif). */
    public function kirimSlipTemplate(string $nomorHp, string $isiPdf, string $namaFile, string $nama, string $periode): string
    {
        $to = self::nomor($nomorHp) ?? throw new RuntimeException('Nomor WhatsApp tidak valid: ' . $nomorHp);
        $media = $this->unggahPdf($isiPdf, $namaFile);

        return $this->kirim($this->payloadTemplate($to, $media, $namaFile, $nama, $periode));
    }

    /** Kirim slip sebagai dokumen bebas (balasan atas permintaan karyawan, di dalam jendela 24 jam). */
    public function kirimSlipDokumen(string $nomorHp, string $isiPdf, string $namaFile, string $keterangan): string
    {
        $to = self::nomor($nomorHp) ?? throw new RuntimeException('Nomor WhatsApp tidak valid: ' . $nomorHp);
        $media = $this->unggahPdf($isiPdf, $namaFile);

        return $this->kirim($this->payloadDokumen($to, $media, $namaFile, $keterangan));
    }

    public function kirimTeks(string $nomorHp, string $isi): string
    {
        $to = self::nomor($nomorHp) ?? throw new RuntimeException('Nomor WhatsApp tidak valid: ' . $nomorHp);

        return $this->kirim($this->payloadTeks($to, $isi));
    }
}
