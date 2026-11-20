<?php
// app/Services/Manajemen/SlipWaService.php
namespace App\Services\Manajemen;

use App\Models\Manajemen\TrxSlipWa;
use App\Models\MasterData\MstKaryawan;
use App\Services\WhatsApp\WhatsAppCloud;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Throwable;

/** Mengirim slip gaji (PDF) ke WhatsApp karyawan lewat WhatsApp Cloud API. */
class SlipWaService
{
    private WhatsAppCloud $wa;

    /** WhatsAppCloud sengaja tidak diminta lewat konstruktor: Laravel tidak tahu cara membuatnya (butuh array konfigurasi). */
    public function __construct(private PayrollService $payroll, ?WhatsAppCloud $wa = null)
    {
        $this->wa = $wa ?? WhatsAppCloud::dariConfig();
    }

    // ---------- murni (diuji di tests/Unit/SlipWaServiceTest) ----------

    /** Null = boleh dikirim; selain itu alasan dilewati. */
    public static function putusan(string $statusPeriode, bool $siap, ?string $nomor, ?string $statusTerakhir, bool $ulang): ?string
    {
        if ($statusPeriode === 'draft') {
            return 'Periode masih Draft. Finalkan payroll dulu.';
        }
        if (! $siap) {
            return 'Slip belum siap (komponen gaji belum lengkap).';
        }
        if ($nomor === null) {
            return 'Nomor WhatsApp kosong atau bukan nomor seluler. Perbaiki di Master Karyawan.';
        }
        if (! $ulang && in_array($statusTerakhir, TrxSlipWa::BERHASIL, true)) {
            return 'Sudah terkirim sebelumnya.';
        }

        return null;
    }

    public static function namaFile(string $periode, string $nama): string
    {
        $aman = trim((string) preg_replace('/[^A-Za-z0-9]+/', '_', $nama), '_');

        return 'Slip_Gaji_' . $periode . '_' . ($aman !== '' ? $aman : 'Karyawan') . '.pdf';
    }

    // ---------- pengiriman ----------

    /** @return array{ok:bool,status:string,pesan:string} */
    public function kirimSatu(string $periode, string $idAbsen, bool $ulang, ?string $oleh): array
    {
        $kurang = $this->wa->yangKurang();
        if ($kurang) {
            return $this->hasil(false, 'error', 'Pengaturan WhatsApp belum lengkap di .env: ' . implode(', ', $kurang));
        }

        $row = $this->payroll->baris($periode, $idAbsen);
        if (! $row) {
            return $this->hasil(false, 'error', 'Data payroll karyawan tidak ditemukan pada periode ini.');
        }

        $nik   = $row['nik'] ?? null;
        $nama  = (string) $row['nama'];
        $kar   = $nik ? MstKaryawan::where('nik', $nik)->first() : null;
        $nomor = WhatsAppCloud::nomor($kar?->telepon_no);

        $terakhir = TrxSlipWa::where('periode', $periode)->where('nik', $nik)->orderByDesc('id')->value('status');
        $alasan   = self::putusan((string) ($row['status'] ?? 'draft'), (bool) ($row['siap'] ?? false), $nomor, $terakhir, $ulang);

        $dasar = ['periode' => $periode, 'nik' => $nik, 'id_absen' => $idAbsen, 'nama' => $nama,
            'nomor' => $nomor, 'sumber' => 'massal', 'dikirim_oleh' => $oleh];

        if ($alasan !== null) {
            if ($terakhir === null || ! in_array($terakhir, TrxSlipWa::BERHASIL, true)) {
                TrxSlipWa::create($dasar + ['status' => 'dilewati', 'detail' => $alasan]);
            }

            return $this->hasil(false, 'dilewati', $alasan);
        }

        try {
            $pdf  = Pdf::loadView('manajemen.payroll.slip_pdf', ['r' => $row, 'periode' => $periode])->setPaper('a4', 'portrait')->output();
            $bulan = Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y');
            $id   = $this->wa->kirimSlipTemplate($nomor, $pdf, self::namaFile($periode, $nama), $nama, $bulan);

            TrxSlipWa::create($dasar + ['status' => 'terkirim_api', 'wa_message_id' => $id, 'detail' => 'Diterima WhatsApp API.']);

            return $this->hasil(true, 'terkirim_api', 'Terkirim ke WhatsApp ' . $nomor . '.');
        } catch (Throwable $e) {
            TrxSlipWa::create($dasar + ['status' => 'failed', 'detail' => mb_substr($e->getMessage(), 0, 500)]);

            return $this->hasil(false, 'failed', $e->getMessage());
        }
    }

    private function hasil(bool $ok, string $status, string $pesan): array
    {
        return ['ok' => $ok, 'status' => $status, 'pesan' => $pesan];
    }
}
