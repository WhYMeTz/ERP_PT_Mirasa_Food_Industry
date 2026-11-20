<?php
// app/Services/WhatsApp/WebhookService.php
namespace App\Services\WhatsApp;

use App\Models\Manajemen\TrxPayroll;
use App\Models\Manajemen\TrxSlipWa;
use App\Models\MasterData\MstKaryawan;
use App\Services\Manajemen\PayrollService;
use App\Services\Manajemen\SlipWaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Memproses isi webhook WhatsApp Cloud API.
 *  - statuses : memperbarui status pesan keluar (terkirim_api -> delivered -> read, atau failed) di trx_slip_wa.
 *  - messages : membaca permintaan "SLIP" / "SLIP September 2026", mencocokkan nomor pengirim dengan Master Karyawan,
 *               lalu membalas dengan PDF slip (dokumen bebas, boleh karena karyawan yang memulai chat) atau teks bantuan.
 * Padanan di PHP dari webhook_server.py.
 */
class WebhookService
{
    private const BULAN = [
        'januari' => 1, 'jan' => 1, 'january' => 1,
        'februari' => 2, 'feb' => 2, 'february' => 2, 'pebruari' => 2,
        'maret' => 3, 'mar' => 3, 'march' => 3,
        'april' => 4, 'apr' => 4,
        'mei' => 5, 'may' => 5,
        'juni' => 6, 'jun' => 6, 'june' => 6,
        'juli' => 7, 'jul' => 7, 'july' => 7,
        'agustus' => 8, 'agu' => 8, 'agt' => 8, 'aug' => 8, 'august' => 8,
        'september' => 9, 'sep' => 9, 'sept' => 9,
        'oktober' => 10, 'okt' => 10, 'oct' => 10, 'october' => 10,
        'november' => 11, 'nov' => 11, 'nopember' => 11,
        'desember' => 12, 'des' => 12, 'dec' => 12, 'december' => 12,
    ];

    private WhatsAppCloud $wa;
    private PayrollService $payroll;

    public function __construct(?WhatsAppCloud $wa = null, ?PayrollService $payroll = null)
    {
        $this->wa      = $wa ?? WhatsAppCloud::dariConfig();
        $this->payroll = $payroll ?? app(PayrollService::class);
    }

    // ---------- murni (diuji di tests/Unit/WebhookServiceTest) ----------

    /** "2026-08", "Agustus 2026", "agu 2026" -> "2026-08". Null bila tidak dikenali. */
    public static function periode(string $teks): ?string
    {
        $t = mb_strtolower(trim($teks));
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $t, $m)) {
            return $m[1] . '-' . $m[2];
        }
        if (preg_match('/^([a-z]+)\s+(\d{4})$/', $t, $m) && isset(self::BULAN[$m[1]])) {
            return sprintf('%s-%02d', $m[2], self::BULAN[$m[1]]);
        }

        return null;
    }

    /**
     * Baca isi pesan teks.
     * @return array{slip:bool, periode:?string, salah:bool}  slip=false bila bukan permintaan SLIP;
     *         periode=null bila tidak disebut; salah=true bila periode disebut tetapi tidak dikenali.
     */
    public static function baca(string $teks): array
    {
        $bagian = preg_split('/\s+/', trim($teks), 2);
        if (mb_strtolower($bagian[0] ?? '') !== 'slip') {
            return ['slip' => false, 'periode' => null, 'salah' => false];
        }

        $sisa = trim($bagian[1] ?? '');
        if ($sisa === '') {
            return ['slip' => true, 'periode' => null, 'salah' => false];
        }

        $p = self::periode($sisa);

        return ['slip' => true, 'periode' => $p, 'salah' => $p === null];
    }

    /** Status baru hanya boleh menggantikan yang lama bila lebih maju (status dari Meta bisa datang tidak berurutan). */
    public static function naikStatus(?string $lama, string $baru): bool
    {
        if ($baru === 'failed') {
            return true;
        }
        $urut = ['terkirim_api' => 1, 'delivered' => 2, 'read' => 3];

        return ($urut[$baru] ?? 0) > ($urut[$lama] ?? 0);
    }

    // ---------- pemrosesan ----------

    public function proses(array $data): void
    {
        foreach ($data['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $v   = $change['value'] ?? [];
                $pid = (string) ($v['metadata']['phone_number_id'] ?? '');
                if ($pid !== '' && $pid !== (string) config('whatsapp.phone_number_id')) {
                    continue; // event untuk nomor lain
                }
                foreach ($v['statuses'] ?? [] as $s) {
                    $this->status((array) $s);
                }
                foreach ($v['messages'] ?? [] as $m) {
                    $this->pesan((array) $m);
                }
            }
        }
    }

    private function status(array $s): void
    {
        $id   = (string) ($s['id'] ?? '');
        $peta = ['sent' => 'terkirim_api', 'delivered' => 'delivered', 'read' => 'read', 'failed' => 'failed'];
        $baru = $peta[strtolower((string) ($s['status'] ?? ''))] ?? null;
        if ($id === '' || $baru === null) {
            return;
        }

        $rec = TrxSlipWa::where('wa_message_id', $id)->orderByDesc('id')->first();
        if (! $rec || ! self::naikStatus($rec->status, $baru)) {
            return;
        }

        if ($baru === 'failed') {
            $e      = (array) (($s['errors'][0] ?? []));
            $detail = trim(($e['code'] ?? '') . ' ' . ($e['title'] ?? $e['message'] ?? ''));
            $rinci  = $e['error_data']['details'] ?? '';
            $detail = trim($detail . ($rinci !== '' ? ' - ' . $rinci : ''));
            $detail = $detail !== '' ? $detail : 'Meta melaporkan pesan gagal dikirim.';
        } else {
            $detail = ['terkirim_api' => 'Diterima WhatsApp API.', 'delivered' => 'Sampai di HP penerima.', 'read' => 'Sudah dibaca penerima.'][$baru];
        }

        $rec->update(['status' => $baru, 'detail' => mb_substr($detail, 0, 500)]);
        Log::info("Status pesan keluar {$id}: {$baru}");
    }

    private function pesan(array $m): void
    {
        $id    = (string) ($m['id'] ?? '');
        $nomor = WhatsAppCloud::nomor((string) ($m['from'] ?? ''));
        if ($id === '' || $nomor === null) {
            return;
        }
        $tipe = (string) ($m['type'] ?? '');
        $isi  = $tipe === 'text' ? trim((string) ($m['text']['body'] ?? '')) : '';

        // Meta bisa mengirim event yang sama lebih dari sekali; message_id unik mencegah balasan ganda.
        $baru = DB::table('trx_wa_masuk')->insertOrIgnore([
            'message_id' => $id, 'nomor' => $nomor, 'tipe' => $tipe, 'isi' => $isi,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        if ($baru === 0) {
            return;
        }

        $ctx = []; // diisi balas(): periode, nik, nama
        try {
            [$status, $detail] = $this->balas($nomor, $tipe, $isi, $ctx);
        } catch (Throwable $e) {
            Log::error('Gagal membalas permintaan SLIP: ' . $e->getMessage());
            [$status, $detail] = ['gagal', mb_substr($e->getMessage(), 0, 500)];
            try {
                $this->wa->kirimTeks($nomor, 'Maaf, slip belum dapat dikirim karena kendala sistem. Silakan coba lagi atau hubungi Admin Slip: ' . $this->hrd());
            } catch (Throwable) {
                // balasan darurat pun gagal; sudah tercatat di log
            }
        }

        DB::table('trx_wa_masuk')->where('message_id', $id)
            ->update([
                'status_balasan' => $status,
                'detail'         => $detail,
                'periode'        => $ctx['periode'] ?? null,
                'nik'            => $ctx['nik'] ?? null,
                'nama'           => isset($ctx['nama']) ? mb_substr((string) $ctx['nama'], 0, 150) : null,
                'updated_at'     => now(),
            ]);
    }

    /** @return array{0:string,1:string} [status_balasan, keterangan] */
    private function balas(string $nomor, string $tipe, string $isi, array &$ctx): array
    {
        $hrd = $this->hrd();

        if ($tipe !== 'text') {
            $this->wa->kirimTeks($nomor, 'Pesan diterima, tetapi format ini belum didukung. Untuk meminta slip gaji, kirim: SLIP atau SLIP September 2026.');

            return ['bantuan', 'Format pesan tidak didukung.'];
        }

        $r = self::baca($isi);
        if (! $r['slip'] || $r['salah']) {
            $this->wa->kirimTeks($nomor, 'Maaf, format pesan belum sesuai. Untuk meminta slip gaji, kirim *SLIP* atau *SLIP September 2026* (contoh: SLIP Agustus 2026).');

            return ['bantuan', $r['salah'] ? 'Periode tidak dikenali.' : 'Bukan permintaan SLIP.'];
        }

        $karyawan = $this->karyawanDariNomor($nomor);
        if ($karyawan->isEmpty()) {
            $this->wa->kirimTeks($nomor, "Nomor WhatsApp Anda belum cocok dengan data karyawan. Silakan hubungi Admin Slip untuk verifikasi: {$hrd}");

            return ['tidak_ditemukan', 'Nomor tidak cocok dengan Master Karyawan.'];
        }
        if ($karyawan->pluck('nik')->unique()->count() > 1) {
            $this->wa->kirimTeks($nomor, "Nomor WhatsApp Anda terdaftar pada lebih dari satu karyawan. Silakan hubungi Admin Slip: {$hrd}");

            return ['ambigu', 'Satu nomor terdaftar pada beberapa karyawan.'];
        }
        $nik = $karyawan->first()->nik;
        $ctx['nik']  = $nik;
        $ctx['nama'] = $karyawan->first()->karyawan_nm ?? null;
        if ($r['periode'] !== null) {
            $ctx['periode'] = $r['periode']; // periode yang diminta karyawan
        }

        // hanya periode yang sudah final atau dibayar yang boleh dikirim
        $q = TrxPayroll::where('nik', $nik)->whereIn('status', ['final', 'dibayar']);
        if ($r['periode'] !== null) {
            $q->where('periode', $r['periode']);
        }
        $p = $q->orderByDesc('periode')->first();
        if (! $p) {
            $this->wa->kirimTeks($nomor, 'Slip untuk periode tersebut belum tersedia. Silakan hubungi Admin Slip: ' . $hrd);

            return ['slip_belum_ada', 'Tidak ada payroll final untuk ' . ($r['periode'] ?? 'periode terbaru') . '.'];
        }

        $ctx['periode'] = $p->periode;
        $ctx['nama']    = $p->nama ?: ($ctx['nama'] ?? null);

        $row = $this->payroll->baris($p->periode, (string) $p->id_absen);
        if (! $row || ! ($row['siap'] ?? false)) {
            $this->wa->kirimTeks($nomor, 'Slip untuk periode tersebut belum siap. Silakan hubungi Admin Slip: ' . $hrd);

            return ['slip_belum_ada', 'Baris payroll belum siap.'];
        }

        $pdf     = Pdf::loadView('manajemen.payroll.slip_pdf', ['r' => $row, 'periode' => $p->periode])->setPaper('a4', 'portrait')->output();
        $bulan   = Carbon::createFromFormat('!Y-m', $p->periode)->locale('id')->translatedFormat('F Y');
        $caption = "Assalamualaikum Wr. Wb.\n\nKepada: {$p->nama},\n\nBerikut slip gaji periode {$bulan}. Mohon diperiksa. Jika ada pertanyaan, silakan hubungi Admin Slip: {$hrd}";
        $idKirim = $this->wa->kirimSlipDokumen($nomor, $pdf, SlipWaService::namaFile($p->periode, (string) $p->nama), $caption);

        TrxSlipWa::create([
            'periode' => $p->periode, 'nik' => $nik, 'id_absen' => $p->id_absen, 'nama' => $p->nama,
            'nomor' => $nomor, 'sumber' => 'permintaan', 'status' => 'terkirim_api',
            'wa_message_id' => $idKirim, 'detail' => 'Balasan atas permintaan SLIP.',
        ]);

        return ['slip_terkirim', 'Slip ' . $p->periode . ' dikirim.'];
    }

    /** Karyawan yang nomor teleponnya sama dengan nomor pengirim (format nomor di master bisa beragam). */
    private function karyawanDariNomor(string $nomor): Collection
    {
        return MstKaryawan::whereNotNull('telepon_no')->get()
            ->filter(fn ($k) => filled($k->nik) && WhatsAppCloud::nomor((string) $k->telepon_no) === $nomor)
            ->values();
    }

    private function hrd(): string
    {
        return (string) config('whatsapp.no_hrd_info');
    }
}
