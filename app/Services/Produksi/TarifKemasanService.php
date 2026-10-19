<?php

namespace App\Services\Produksi;

use App\Models\MasterData\MstTarifKemasan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TarifKemasanService
{
    private const CACHE_KEY_ALL = 'mst_tarif_kemasan_all';
    private const CACHE_KEY_MAP = 'mst_tarif_kemasan_map';
    private const CACHE_TTL = 3600; // 1 Jam

    /**
     * Dapatkan semua standar tarif kemasan & packaging aktif
     */
    public function getAllTarif(): Collection
    {
        return MstTarifKemasan::active()
            ->orderBy('kategori_bahan')
            ->orderBy('tarif_kemasan_id')
            ->get();
    }

    /**
     * Dapatkan peta kode_tarif => nilai_tarif
     *
     * @return array<string, float>
     */
    public function getTarifMap(): array
    {
        return MstTarifKemasan::active()
            ->pluck('nilai_tarif', 'kode_tarif')
            ->map(fn ($val) => (float) $val)
            ->toArray();
    }

    /**
     * Dapatkan peta kode_tarif => model MstTarifKemasan utuh
     */
    public function getTarifModelMap(): array
    {
        $all = $this->getAllTarif();
        $map = [];
        foreach ($all as $item) {
            $map[$item->kode_tarif] = $item;
        }
        return $map;
    }

    /**
     * Dapatkan struktur daftar harga terformat untuk HPP Packaging (Header Excel & Kalkulasi HPP)
     *
     * @return array<string, mixed>
     */
    public function getDaftarHargaPackaging(): array
    {
        $models = $this->getTarifModelMap();
        $map = $this->getTarifMap();

        $wipCurah = $models['WIP_CURAH'] ?? null;
        $tglWip = $wipCurah && $wipCurah->berlaku_sejak 
            ? Carbon::parse($wipCurah->berlaku_sejak)->format('d/m/Y') 
            : '20/01/2026';

        $kartonXxVal = (float) ($map['KARTON_XX'] ?? 1338.90);
        $kartonMxVal = (float) ($map['KARTON_MX'] ?? 1322.60);
        $rollXxVal   = (float) ($map['ROLL_XX'] ?? 364830.00);
        $rollMxVal   = (float) ($map['ROLL_MX'] ?? 364427.00);
        $lakbanRoll  = (float) ($map['LAKBAN_ROLL'] ?? 45880.00);
        $lakbanGr    = (float) ($map['LAKBAN_GRAM'] ?? 36.70);
        $bumbuVal    = (float) ($map['BUMBU_BALADO'] ?? 38499.00);
        $expLbr      = (float) ($map['KERTAS_EXP'] ?? 250.00);
        $wipKg       = (float) ($map['WIP_CURAH'] ?? 25500.00);
        $berkoKg     = (float) ($map['WIP_BERKO'] ?? 10000.00);
        $rollGabKg   = (float) ($map['ROLL_XX_MX'] ?? 43402.00);
        $rollGabGr   = round($rollGabKg / 1000, 2);

        return [
            'harga_wip'      => $wipKg,
            'tgl_wip'        => $tglWip,
            'karton_kk'      => $kartonXxVal,
            'karton_mk'      => $kartonMxVal,
            'karton_xx'      => $kartonXxVal,
            'karton_mx'      => $kartonMxVal,
            'bumbu_balado'   => $bumbuVal,
            'bumbu_rasio'    => '5% Berko',
            'roll_kk_unit'   => $rollXxVal,
            'roll_mk_unit'   => $rollMxVal,
            'roll_xx_unit'   => $rollXxVal,
            'roll_mx_unit'   => $rollMxVal,
            'roll_per_kg'    => $rollGabKg,
            'roll_per_gr'    => $rollGabGr > 0 ? $rollGabGr : 43.40,
            'lakban_roll'    => $lakbanRoll,
            'lakban_per_gr'  => $lakbanGr,
            'kertas_exp_lbr' => $expLbr,
            'kerusakan_gr'   => $rollGabGr > 0 ? $rollGabGr : 43.40,
            'berko_kg'       => $berkoKg,
        ];
    }

    /**
     * Perbarui 1 tarif kemasan tunggal (Audit Trail & Transaksi Database)
     */
    public function updateTarif(int $id, array $data, string $userId): MstTarifKemasan
    {
        return DB::transaction(function () use ($id, $data, $userId) {
            $tarif = MstTarifKemasan::findOrFail($id);

            $tarif->update([
                'nilai_tarif'          => (float) ($data['nilai_tarif'] ?? $tarif->nilai_tarif),
                'nilai_tarif_sekunder' => isset($data['nilai_tarif_sekunder']) && $data['nilai_tarif_sekunder'] !== '' ? (float) $data['nilai_tarif_sekunder'] : $tarif->nilai_tarif_sekunder,
                'berlaku_sejak'        => !empty($data['berlaku_sejak']) ? $data['berlaku_sejak'] : $tarif->berlaku_sejak,
                'keterangan'           => $data['keterangan'] ?? $tarif->keterangan,
                'updated_by'           => $userId,
                'version_no'           => $tarif->version_no + 1,
            ]);

            $this->clearCache();
            Log::info("Tarif Kemasan ID {$id} ({$tarif->kode_tarif}) diupdate oleh {$userId} menjadi Rp {$tarif->nilai_tarif}");

            return $tarif;
        });
    }

    /**
     * Perbarui massal tarif kemasan (Quick Matrix Update)
     *
     * @param array<string, mixed> $rates [ 'KARTON_XX' => 1341.77, 'LAKBAN_GRAM' => 36.70, ... ]
     */
    public function quickUpdateRates(array $rates, string $userId): array
    {
        return DB::transaction(function () use ($rates, $userId) {
            $updated = [];

            foreach ($rates as $kodeTarif => $nilai) {
                if ($nilai === null || $nilai === '') {
                    continue;
                }

                if (is_numeric($nilai)) {
                    $numericVal = (float) $nilai;
                } else {
                    $clean = trim(str_replace(['Rp', ' '], '', (string) $nilai));
                    if (str_contains($clean, ',') && str_contains($clean, '.')) {
                        $clean = str_replace('.', '', $clean);
                        $clean = str_replace(',', '.', $clean);
                    } elseif (str_contains($clean, ',')) {
                        $clean = str_replace(',', '.', $clean);
                    }
                    $numericVal = (float) $clean;
                }

                $tarif = MstTarifKemasan::where('kode_tarif', $kodeTarif)->first();
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
            Log::info("Quick Update Tarif Kemasan oleh {$userId}: " . json_encode($updated));

            return $this->getTarifMap();
        });
    }

    /**
     * Bersihkan cache tarif kemasan
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
        Cache::forget(self::CACHE_KEY_MAP);
    }
}
