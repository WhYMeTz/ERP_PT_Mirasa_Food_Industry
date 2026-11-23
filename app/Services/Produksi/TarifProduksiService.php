<?php

namespace App\Services\Produksi;

use App\Models\MasterData\MstTarifProduksi;
use App\Models\Produksi\DatKursHarian;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TarifProduksiService
{
    private const CACHE_KEY_ALL = 'mst_tarif_produksi_all';
    private const CACHE_KEY_MAP = 'mst_tarif_produksi_map';
    private const CACHE_TTL = 3600; // 1 Jam

    /**
     * Dapatkan semua tarif produksi aktif
     */
    public function getAllTarif(): Collection
    {
        return MstTarifProduksi::active()
            ->orderBy('kategori')
            ->orderBy('tarif_id')
            ->get();
    }

    /**
     * Dapatkan peta kode_tarif => nilai_tarif
     *
     * @return array<string, float>
     */
    public function getTarifMap(): array
    {
        return MstTarifProduksi::active()
            ->pluck('nilai_tarif', 'kode_tarif')
            ->map(fn ($val) => (float) $val)
            ->toArray();
    }

    /**
     * Dapatkan standar pengali FOH terformat untuk UI & Service
     *
     * @return array<string, float>
     */
    public function getFohRates(): array
    {
        $map = $this->getTarifMap();

        return [
            'qc'           => (float) ($map['FOH_QC'] ?? 49.97),
            'listrik'      => (float) ($map['FOH_LISTRIK'] ?? 223.80),
            'pemeliharaan' => (float) ($map['FOH_PEMELIHARAAN'] ?? 23.34),
            'penyusutan'   => (float) ($map['FOH_PENYUSUTAN'] ?? 66.44),
            'kimia'        => (float) ($map['FOH_KIMIA_IPAL'] ?? 45.09),
            'fotocopy'     => (float) ($map['FOH_FOTOCOPY'] ?? 28.00),
            'limbah_padat' => (float) ($map['FOH_LIMBAH_PADAT'] ?? 180000.00),
        ];
    }

    /**
     * Format tanggal ke bahasa Indonesia lengkap (contoh: 18 November 2026)
     * agar tidak terjadi salah tafsir antara bulan Oktober dan November.
     */
    private function formatIndonesianDate(Carbon $carbon): string
    {
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $carbon->format('j') . ' ' . ($bulanIndo[(int)$carbon->format('n')] ?? '') . ' ' . $carbon->format('Y');
    }

    /**
     * Dapatkan rincian lengkap kurs resmi JISDOR Bank Indonesia dengan penanganan cerdas:
     * 1. Jika tanggal target sudah dirilis resmi di Bank Indonesia -> gunakan kurs tanggal tersebut.
     * 2. Jika di web Bank Indonesia kurs tanggal target belum muncul (misal hari ini sebelum jam rilis sore 16:15 WIB,
     *    atau hari libur/akhir pekan) -> WAJIB menggunakan kurs resmi hari kerja sebelumnya yang sudah terbit di BI,
     *    serta mencantumkan tanggal riil kurs tersebut secara transparan (TIDAK BOLEH mengarang tanggal target).
     *
     * @return array{rate: float, date: string, raw_date: string, is_exact: bool, keterangan_acuan: string}
     */
    public function getJisdorRateDetail(?string $date = null, bool $allowAutoSync = false): array
    {
        $targetDate = $date ?: date('Y-m-d');
        $targetCarbon = Carbon::parse($targetDate)->timezone(config('app.timezone', 'Asia/Jakarta'));
        $targetDateFmt = $this->formatIndonesianDate($targetCarbon);

        // 1. Cek terlebih dahulu apakah tanggal target SUDAH ADA di database lokal (sumber BANK_INDONESIA_JISDOR)
        $exactRecord = DatKursHarian::active()
            ->whereDate('tanggal', $targetDate)
            ->where('sumber', 'BANK_INDONESIA_JISDOR')
            ->first();

        if ($exactRecord && $exactRecord->kurs_tengah > 0) {
            return [
                'rate'             => (float) $exactRecord->kurs_tengah,
                'date'             => $targetDateFmt,
                'raw_date'         => $targetDate,
                'is_exact'         => true,
                'keterangan_acuan' => 'Sesuai tanggal SPK produksi',
            ];
        }

        // 2. Jika belum ada di database lokal dan auto-sync diperbolehkan (misal trigger manual tombol Sync), coba sinkronisasi dari portal BI
        if ($allowAutoSync) {
            $this->syncFromBankIndonesia($targetDate);

            // Periksa ulang setelah percobaan sinkronisasi
            $exactRecord = DatKursHarian::active()
                ->whereDate('tanggal', $targetDate)
                ->where('sumber', 'BANK_INDONESIA_JISDOR')
                ->first();

            if ($exactRecord && $exactRecord->kurs_tengah > 0) {
                return [
                    'rate'             => (float) $exactRecord->kurs_tengah,
                    'date'             => $targetDateFmt,
                    'raw_date'         => $targetDate,
                    'is_exact'         => true,
                    'keterangan_acuan' => 'Sesuai tanggal SPK produksi (Sinkronisasi Baru BI)',
                ];
            }
        }

        // 3. JIKA TANGGAL TARGET BELUM RILIS DI WEB BI:
        // Gunakan kurs hari kerja bursa sebelumnya yang sudah terbit di web BI (< targetDate)
        $prevRecord = DatKursHarian::active()
            ->whereDate('tanggal', '<', $targetDate)
            ->orderByDesc('tanggal')
            ->first();

        if ($prevRecord && $prevRecord->kurs_tengah > 0) {
            $prevCarbon = Carbon::parse($prevRecord->tanggal)->timezone(config('app.timezone', 'Asia/Jakarta'));
            $prevDateFmt = $this->formatIndonesianDate($prevCarbon);

            $ket = $targetCarbon->isToday()
                ? "Kurs bursa terakhir (tgl {$targetDateFmt} belum rilis di BI)"
                : ($targetCarbon->isWeekend() ? "Kurs bursa terakhir BI (akhir pekan)" : "Kurs bursa terakhir BI (tgl {$targetDateFmt} belum rilis)");

            return [
                'rate'             => (float) $prevRecord->kurs_tengah,
                'date'             => $prevDateFmt,
                'raw_date'         => $prevCarbon->format('Y-m-d'),
                'is_exact'         => false,
                'keterangan_acuan' => $ket,
            ];
        }

        // 4. Fallback ke master tarif jika tabel dat_kurs_harian kosong sama sekali
        $map = $this->getTarifMap();
        $fallbackRate = (float) ($map['KURS_JISDOR'] ?? 18252.00);

        return [
            'rate'             => $fallbackRate,
            'date'             => $targetDateFmt,
            'raw_date'         => $targetDate,
            'is_exact'         => false,
            'keterangan_acuan' => 'Standar acuan master data',
        ];
    }

    /**
     * Dapatkan kurs JISDOR bursa paling terakhir yang diterbitkan oleh Bank Indonesia.
     */
    public function getLatestJisdorRateDetail(bool $allowAutoSync = true): array
    {
        if ($allowAutoSync) {
            $this->syncFromBankIndonesia();
        }

        $latestRecord = DatKursHarian::active()
            ->where('sumber', 'BANK_INDONESIA_JISDOR')
            ->orderByDesc('tanggal')
            ->first();

        if ($latestRecord && $latestRecord->kurs_tengah > 0) {
            $latestCarbon = Carbon::parse($latestRecord->tanggal)->timezone(config('app.timezone', 'Asia/Jakarta'));
            return [
                'rate'             => (float) $latestRecord->kurs_tengah,
                'date'             => $this->formatIndonesianDate($latestCarbon),
                'raw_date'         => $latestCarbon->format('Y-m-d'),
                'is_exact'         => true,
                'keterangan_acuan' => 'Kurs bursa terakhir resmi Bank Indonesia',
            ];
        }

        return $this->getJisdorRateDetail(date('Y-m-d'), false);
    }

    /**
     * Dapatkan nilai nominal kurs resmi JISDOR Bank Indonesia harian.
     */
    public function getJisdorRate(?string $date = null, bool $allowAutoSync = false): float
    {
        $detail = $this->getJisdorRateDetail($date, $allowAutoSync);
        return (float) $detail['rate'];
    }

    /**
     * Sinkronisasi data resmi JISDOR langsung dari portal resmi Bank Indonesia.
     * Mengambil tabel kurs resmi BI (baik halaman utama terkini maupun pencarian historis)
     * dan menyimpannya ke dat_kurs_harian & mst_tarif_produksi.
     *
     * @return array<string, float>
     */
    public function syncFromBankIndonesia(?string $targetDate = null): array
    {
        try {
            $cookieJar = new \GuzzleHttp\Cookie\CookieJar();
            $client = new \GuzzleHttp\Client([
                'cookies'         => $cookieJar,
                'verify'          => false,
                'connect_timeout' => 2.5,
                'timeout'         => 5,
                'headers'         => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
            ]);

            $url = 'https://www.bi.go.id/id/statistik/informasi-kurs/jisdor/default.aspx';
            $res = $client->get($url);
            $html = (string) $res->getBody();

            $synced = [];
            // 1. Ekstrak baris tabel kurs terkini dari halaman awal BI (5-10 hari bursa terakhir)
            $this->parseAndSaveJisdorRows($html, $synced);

            // 2. Jika tanggal target historis yang diminta belum ada di tabel terkini BI, lakukan pencarian form SharePoint BI
            if ($targetDate && !isset($synced[$targetDate])) {
                $targetCarbon = Carbon::parse($targetDate)->timezone(config('app.timezone', 'Asia/Jakarta'));
                $today = Carbon::today(config('app.timezone', 'Asia/Jakarta'));

                // Hanya cari historis ke belakang jika targetDate < hari ini
                if ($targetCarbon->lt($today)) {
                    $fromDate = $targetCarbon->copy()->subDays(7)->format('d/m/Y');
                    $toDate = $targetCarbon->copy()->addDays(7)->min($today)->format('d/m/Y');

                    preg_match('/id="__VIEWSTATE"\s+value="([^"]+)"/', $html, $vs);
                    preg_match('/id="__VIEWSTATEGENERATOR"\s+value="([^"]+)"/', $html, $vsg);
                    preg_match('/id="__EVENTVALIDATION"\s+value="([^"]+)"/', $html, $ev);

                    if (!empty($vs[1])) {
                        $postRes = $client->post($url, [
                            'form_params' => [
                                '__VIEWSTATE'          => $vs[1],
                                '__VIEWSTATEGENERATOR' => $vsg[1] ?? '',
                                '__EVENTVALIDATION'    => $ev[1] ?? '',
                                'ctl00$ctl51$g_8e393016_c37f_48c2_9aa2_ab195a9c1128$ctl00$TextBoxFrom' => $fromDate,
                                'ctl00$ctl51$g_8e393016_c37f_48c2_9aa2_ab195a9c1128$ctl00$TextBoxDateTo' => $toDate,
                                'ctl00$ctl51$g_8e393016_c37f_48c2_9aa2_ab195a9c1128$ctl00$ButtonCari' => 'Cari',
                            ]
                        ]);
                        $searchHtml = (string) $postRes->getBody();
                        $this->parseAndSaveJisdorRows($searchHtml, $synced);
                    }
                }
            }

            if (!empty($synced)) {
                // Urutkan tanggal descending agar tanggal terbaru berada di atas
                krsort($synced);
                $latestDate = array_key_first($synced);
                $latestRate = $synced[$latestDate];

                MstTarifProduksi::updateOrCreate(
                    ['kode_tarif' => 'KURS_JISDOR'],
                    [
                        'kategori'     => 'ENERGI',
                        'nama_tarif'   => 'Kurs Harian Bank Indonesia (JISDOR)',
                        'satuan_basis' => 'PER_USD',
                        'satuan_label' => 'Rp / USD',
                        'nilai_tarif'  => $latestRate,
                        'keterangan'   => "Kurs referensi harian resmi Bank Indonesia JISDOR ({$latestDate})",
                        'updated_by'   => 'SYSTEM',
                        'updated_dt'   => now(),
                    ]
                );

                Cache::forget('mst_tarif_produksi_all');
                Cache::forget('mst_tarif_produksi_map');
            }

            return $synced;
        } catch (\Throwable $e) {
            Log::warning("Gagal auto-sync JISDOR dari portal BI: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse HTML tabel kurs JISDOR dari Bank Indonesia dan simpan ke dat_kurs_harian
     */
    private function parseAndSaveJisdorRows(string $html, array &$synced): void
    {
        $monthMap = [
            'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
            'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
            'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
        ];

        preg_match_all('/<tr[^>]*>.*?<\/tr>/is', $html, $rows);
        foreach ($rows[0] as $r) {
            if (str_contains($r, 'table-search')) continue;
            preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $r, $cells);
            if (!empty($cells[1]) && count($cells[1]) >= 2) {
                $c0 = trim(strip_tags($cells[1][0])); // contoh: "18 November 2026"
                $c1 = trim(strip_tags($cells[1][1])); // contoh: "Rp 18.252,00"

                if (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/', $c0, $mDate)) {
                    $day = str_pad($mDate[1], 2, '0', STR_PAD_LEFT);
                    $monthIndo = strtolower($mDate[2]);
                    $year = $mDate[3];
                    $monthNum = $monthMap[$monthIndo] ?? null;

                    if ($monthNum) {
                        $isoDate = "{$year}-{$monthNum}-{$day}";
                        $cleanRate = preg_replace('/[^\d,]/', '', $c1);
                        $floatRate = (float) str_replace(',', '.', $cleanRate);

                        if ($floatRate > 10000) {
                            $synced[$isoDate] = $floatRate;

                            DatKursHarian::updateOrCreate(
                                ['tanggal' => $isoDate],
                                [
                                    'mata_uang'   => 'USD',
                                    'kurs_tengah' => $floatRate,
                                    'sumber'      => 'BANK_INDONESIA_JISDOR',
                                    'catatan'     => "Kurs resmi JISDOR Bank Indonesia per {$c0}",
                                    'updated_by'  => 'AUTO_SYNC_BI',
                                    'updated_dt'  => now(),
                                ]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Dapatkan tarif acuan Energi dan Tenaga Kerja
     *
     * @return array<string, float>
     */
    public function getEnergiLaborRates(?string $date = null): array
    {
        $map = $this->getTarifMap();

        $cngUsd = (float) ($map['TARIF_CNG_USD'] ?? 14.50);
        $kursJisdor = $this->getJisdorRate($date);
        $cngTarif = (float) ($cngUsd > 0 && $kursJisdor > 0 ? round($cngUsd * $kursJisdor, 2) : ($map['TARIF_CNG'] ?? 263001.00));

        return [
            'cng_tarif_usd'    => $cngUsd,
            'cng_kurs_jisdor'  => $kursJisdor,
            'cng_tarif'        => $cngTarif,
            'tk_tarif_per_org' => (float) ($map['TARIF_TK_HARIAN'] ?? 91300.00),
        ];
    }

    /**
     * Perbarui 1 tarif tunggal (Audit & Transaction)
     */
    public function updateTarif(int $id, array $data, string $userId): MstTarifProduksi
    {
        return DB::transaction(function () use ($id, $data, $userId) {
            $tarif = MstTarifProduksi::findOrFail($id);

            $tarif->update([
                'nilai_tarif'  => (float) ($data['nilai_tarif'] ?? $tarif->nilai_tarif),
                'keterangan'   => $data['keterangan'] ?? $tarif->keterangan,
                'updated_by'   => $userId,
                'version_no'   => $tarif->version_no + 1,
            ]);

            $this->clearCache();

            Log::info("Tarif Produksi ID {$id} ({$tarif->kode_tarif}) diupdate oleh {$userId} menjadi {$tarif->nilai_tarif}");

            return $tarif;
        });
    }

    /**
     * Pembaruan cepat kolektif (Quick Update) dari form produksi atau batch modal
     *
     * @param array<string, mixed> $rates [ 'FOH_QC' => 50.00, 'TARIF_CNG' => 230000, ... ]
     */
    public function quickUpdate(array $rates, string $userId): array
    {
        return DB::transaction(function () use ($rates, $userId) {
            $updated = [];

            foreach ($rates as $kodeTarif => $nilai) {
                if ($nilai === null || $nilai === '') {
                    continue;
                }

                $numericVal = (float) $nilai;
                if ($numericVal < 0) {
                    continue;
                }

                $tarif = MstTarifProduksi::where('kode_tarif', $kodeTarif)->first();
                if ($tarif) {
                    $tarif->update([
                        'nilai_tarif' => $numericVal,
                        'updated_by'  => $userId,
                        'version_no'  => $tarif->version_no + 1,
                    ]);
                    $updated[$kodeTarif] = $numericVal;
                }
            }

            $this->clearCache();

            return $this->getTarifMap();
        });
    }

    /**
     * Hapus cache saat ada perubahan data
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
        Cache::forget(self::CACHE_KEY_MAP);
    }
}
