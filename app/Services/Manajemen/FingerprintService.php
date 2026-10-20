<?php

namespace App\Services\Manajemen;

use App\Models\Manajemen\TrxAbsensiHarian;
use App\Models\Manajemen\TrxFingerprintLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FingerprintService
{
    const STEP_MENIT = 30;        // interval pembulatan
    const MIN_KERJA_MENIT = 120;  // selisih scan pertama-terakhir minimal agar dianggap ada jam pulang

    /** Bulatkan ke kelipatan 30 menit terdekat (07:15 naik ke 07:30). */
    public static function bulatkan(Carbon $dt): Carbon
    {
        $step   = self::STEP_MENIT * 60;
        $detik  = $dt->secondsSinceMidnight();
        $hasil  = (int) (floor($detik / $step + 0.5) * $step);

        return $dt->copy()->startOfDay()->addSeconds($hasil);
    }

    /** Simpan scan mentah, lalu hitung ulang tanggal yang terpengaruh. */
    public function simpanLog(array $rows): array
    {
        // Buang duplikat (id_absen + scan_at sama) dalam file yang sama
        $rows = collect($rows)
            ->unique(fn ($r) => $r['id_absen'] . '|' . $r['scan_at'])
            ->values()
            ->all();

        $now  = now();
        $data = array_map(fn ($r) => $r + ['created_at' => $now, 'updated_at' => $now], $rows);

        foreach (array_chunk($data, 500) as $chunk) {
            TrxFingerprintLog::upsert($chunk, ['id_absen', 'scan_at'], ['id_mesin', 'nama', 'updated_at']);
        }

        $dari   = collect($rows)->min('scan_at');
        $sampai = collect($rows)->max('scan_at');

        $jumlah = $this->hitungUlang(Carbon::parse($dari)->toDateString(), Carbon::parse($sampai)->toDateString());

        return ['scan' => count($rows), 'hari_karyawan' => $jumlah];
    }

    /** Hitung jam masuk/pulang per karyawan per tanggal dari log mentah. */
    public function hitungUlang(string $dari, string $sampai): int
    {
        $grup = TrxFingerprintLog::query()
            ->select('id_absen', DB::raw('MAX(nama) as nama'), DB::raw('DATE(scan_at) as tanggal'),
                DB::raw('MIN(scan_at) as pertama'), DB::raw('MAX(scan_at) as terakhir'),
                DB::raw('COUNT(*) as jumlah'))
            ->whereBetween(DB::raw('DATE(scan_at)'), [$dari, $sampai])
            ->groupBy('id_absen', DB::raw('DATE(scan_at)'))
            ->get();

        $now  = now();
        $rows = [];

        $manual = TrxAbsensiHarian::where('is_manual', true)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->get()
            ->mapWithKeys(fn ($m) => [$m->id_absen . '|' . $m->tanggal->toDateString() => true]);

        foreach ($grup as $g) {
            if (isset($manual[$g->id_absen . '|' . $g->tanggal])) {
                    continue; // jangan timpa data yang sudah diedit manual
                }
            $pertama = Carbon::parse($g->pertama);
            $terakhir = Carbon::parse($g->terakhir);
            $adaPulang = $pertama->diffInMinutes($terakhir) >= self::MIN_KERJA_MENIT;

            $rows[] = [
                'id_absen'    => $g->id_absen,
                'nama'        => $g->nama,
                'tanggal'     => $g->tanggal,
                'masuk_asli'  => $pertama,
                'pulang_asli' => $adaPulang ? $terakhir : null,
                'jam_masuk'   => self::bulatkan($pertama),
                'jam_pulang'  => $adaPulang ? self::bulatkan($terakhir) : null,
                'jumlah_scan' => $g->jumlah,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            TrxAbsensiHarian::upsert($chunk, ['id_absen', 'tanggal'], [
                'nama', 'masuk_asli', 'pulang_asli', 'jam_masuk', 'jam_pulang', 'jumlah_scan', 'updated_at',
            ]);
        }

        return count($rows);
    }
}