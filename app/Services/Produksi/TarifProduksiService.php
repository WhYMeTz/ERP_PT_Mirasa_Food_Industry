<?php

namespace App\Services\Produksi;

use App\Models\MasterData\MstTarifProduksi;
use App\Models\Produksi\DatKursHarian;
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
     * Dapatkan kurs resmi JISDOR Bank Indonesia harian.
     * Menggunakan tabel historis dat_kurs_harian & master tarif sebagai Single Source of Truth,
     * serta otomatis melakukan auto-sync ke portal Bank Indonesia jika data belum ada atau sudah kedaluwarsa.
     */
    public function getJisdorRate(?string $date = null, bool $allowAutoSync = true): float
    {
        $targetDate = $date ?: date('Y-m-d');

        // 1. Cek tanggal spesifik di dat_kurs_harian
        $exactRecord = DatKursHarian::active()
            ->where('tanggal', $targetDate)
            ->first();

        if ($exactRecord && $exactRecord->kurs_tengah > 0) {
            return (float) $exactRecord->kurs_tengah;
        }

        // 2. Jika belum ada dan autoSync diaktifkan, otomatis ambil data resmi portal Bank Indonesia
        if ($allowAutoSync) {
            $synced = $this->syncFromBankIndonesia();
            if (isset($synced[$targetDate]) && $synced[$targetDate] > 0) {
                return (float) $synced[$targetDate];
            }
        }

        // 3. Jika hari libur/akhir pekan atau jam rilis BI belum tercapai, ambil kurs hari kerja terakhir <= targetDate
        $latestRecord = DatKursHarian::active()
            ->where('tanggal', '<=', $targetDate)
            ->orderByDesc('tanggal')
            ->first();

        if ($latestRecord && $latestRecord->kurs_tengah > 0) {
            return (float) $latestRecord->kurs_tengah;
        }

        // 4. Fallback ke master tarif produksi
        $map = $this->getTarifMap();
        return (float) ($map['KURS_JISDOR'] ?? 18161.00);
    }

    /**
     * Sinkronisasi data resmi JISDOR langsung dari portal resmi Bank Indonesia.
     * Mengambil tabel kurs resmi BI (10 hari kerja terakhir) dan menyimpannya ke dat_kurs_harian & mst_tarif_produksi.
     *
     * @return array<string, float>
     */
    public function syncFromBankIndonesia(): array
    {
        $monthMap = [
            'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
            'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
            'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
        ];

        try {
            $response = Http::timeout(6)
                ->withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get('https://www.bi.go.id/id/statistik/informasi-kurs/jisdor/default.aspx');

            if (!$response->successful()) {
                return [];
            }

            $html = $response->body();
            preg_match_all('/<tr[^>]*>.*?<\/tr>/is', $html, $rows);

            $synced = [];
            foreach ($rows[0] as $r) {
                if (str_contains($r, 'table-search')) continue;
                preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $r, $cells);
                if (!empty($cells[1]) && count($cells[1]) >= 2) {
                    $c0 = trim(strip_tags($cells[1][0])); // contoh: "2 November 2026"
                    $c1 = trim(strip_tags($cells[1][1])); // contoh: "Rp 18.161,00"

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

            if (!empty($synced)) {
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
