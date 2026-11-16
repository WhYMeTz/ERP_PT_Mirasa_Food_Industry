<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstPengaturanJurnal;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PengaturanJurnalService
{
    protected const CACHE_KEY = 'pengaturan_jurnal_map';
    protected const CACHE_TTL = 3600; // 1 jam

    /**
     * Ambil seluruh konfigurasi pemetaan jurnal yang dikelompokkan berdasarkan grup.
     */
    public function getAllGrouped(): Collection
    {
        return MstPengaturanJurnal::active()
            ->with('coa')
            ->orderBy('grup')
            ->orderBy('urutan')
            ->get()
            ->groupBy('grup');
    }

    /**
     * Dapatkan kode akun COA berdasarkan kunci setting (dengan fallback kode default).
     */
    public function getCoaKode(string $kunci, string $defaultKode): string
    {
        $map = $this->getSettingsMap();
        if (isset($map[$kunci]['coa_kode']) && !empty($map[$kunci]['coa_kode'])) {
            return $map[$kunci]['coa_kode'];
        }

        return $defaultKode;
    }

    /**
     * Dapatkan ID COA berdasarkan kunci setting.
     */
    public function getCoaId(string $kunci, ?int $defaultCoaId = null): ?int
    {
        $map = $this->getSettingsMap();
        if (isset($map[$kunci]['coa_id']) && !empty($map[$kunci]['coa_id'])) {
            return (int) $map[$kunci]['coa_id'];
        }

        return $defaultCoaId;
    }

    /**
     * Dapatkan format template teks/keterangan berdasarkan kunci setting.
     */
    public function getTemplate(string $kunci, string $defaultTemplate): string
    {
        $map = $this->getSettingsMap();
        if (isset($map[$kunci]['text']) && !empty(trim($map[$kunci]['text']))) {
            return trim($map[$kunci]['text']);
        }

        return $defaultTemplate;
    }

    /**
     * Render template teks dengan array pengganti (placeholder).
     */
    public function renderTemplate(string $kunci, string $defaultTemplate, array $replacements = []): string
    {
        $template = $this->getTemplate($kunci, $defaultTemplate);
        foreach ($replacements as $key => $val) {
            $template = str_replace('{' . $key . '}', (string) $val, $template);
        }
        return $template;
    }

    /**
     * Simpan pembaruan pengaturan jurnal secara massal.
     */
    public function updateBatch(array $payload, ?User $user = null): void
    {
        DB::transaction(function () use ($payload, $user) {
            $actor = $user?->name ?? 'SYSTEM';

            foreach ($payload as $kunci => $val) {
                $setting = MstPengaturanJurnal::where('kunci', $kunci)
                    ->where('deleted_st', false)
                    ->first();

                if (!$setting) {
                    continue;
                }

                if ($setting->tipe_nilai === 'COA') {
                    $setting->nilai_coa_id = !empty($val) ? (int) $val : null;
                } else {
                    $setting->nilai_text = !empty(trim($val)) ? trim($val) : null;
                }

                $setting->updated_by = $actor;
                $setting->version_no = ((int) ($setting->version_no ?? 1)) + 1;
                $setting->save();
            }

            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * Ambil peta setting ber-cache untuk efisiensi transaksi harian.
     */
    protected function getSettingsMap(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $records = MstPengaturanJurnal::active()
                ->with('coa')
                ->get();

            $map = [];
            foreach ($records as $r) {
                $map[$r->kunci] = [
                    'tipe'     => $r->tipe_nilai,
                    'coa_id'   => $r->nilai_coa_id,
                    'coa_kode' => $r->coa?->kode_akun,
                    'coa_nama' => $r->coa?->nama_akun,
                    'text'     => $r->nilai_text,
                ];
            }
            return $map;
        });
    }

    /**
     * Bersihkan cache secara eksplisit.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
