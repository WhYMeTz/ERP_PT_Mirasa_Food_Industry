<?php

namespace App\Services\Produksi;

use App\Models\MasterData\MstTarifProduksi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
     * Menggunakan tabel historis dat_kurs_harian & master tarif sebagai Single Source of Truth.
     */
    public function getJisdorRate(?string $date = null): float
    {
        $targetDate = $date ?: date('Y-m-d');

        // 1. Cek tanggal spesifik di dat_kurs_harian
        $exactRecord = \App\Models\Produksi\DatKursHarian::active()
            ->where('tanggal', $targetDate)
            ->first();

        if ($exactRecord && $exactRecord->kurs_tengah > 0) {
            return (float) $exactRecord->kurs_tengah;
        }

        // 2. Jika belum rilis atau hari libur/akhir pekan, ambil kurs hari kerja terakhir <= targetDate
        $latestRecord = \App\Models\Produksi\DatKursHarian::active()
            ->where('tanggal', '<=', $targetDate)
            ->orderByDesc('tanggal')
            ->first();

        if ($latestRecord && $latestRecord->kurs_tengah > 0) {
            return (float) $latestRecord->kurs_tengah;
        }

        // 3. Fallback ke master tarif produksi atau default 18138.00
        $map = $this->getTarifMap();
        return (float) ($map['KURS_JISDOR'] ?? 18138.00);
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
