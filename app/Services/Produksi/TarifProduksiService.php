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
     * Dapatkan tarif acuan Energi dan Tenaga Kerja
     *
     * @return array<string, float>
     */
    public function getEnergiLaborRates(): array
    {
        $map = $this->getTarifMap();

        return [
            'cng_tarif'        => (float) ($map['TARIF_CNG'] ?? 226800.00),
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
