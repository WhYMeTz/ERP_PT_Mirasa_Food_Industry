<?php

namespace App\Services\Manajemen;

use App\Models\Manajemen\MstKodeShift;
use App\Models\Manajemen\TrxAbsensiKaryawan;
use App\Models\Manajemen\TrxFingerprintLog;
use App\Models\Manajemen\TrxJadwalKaryawan;
use App\Models\MasterData\MstKaryawan;
use App\Models\Manajemen\TrxAbsensiHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AbsensiService
{
    const SEBELUM_MASUK_JAM  = 4;    // jendela scan: sekian jam sebelum jam masuk jadwal
    const SESUDAH_PULANG_JAM = 6;    // ... dan sekian jam sesudah jam pulang jadwal
    const MIN_SELISIH_MENIT  = 120;  // scan pertama-terakhir minimal agar dianggap lengkap

    private static function menit(Carbon $dari, Carbon $ke): float
    {
        return ($ke->getTimestamp() - $dari->getTimestamp()) / 60;
    }

    
    public static function hitungShift(Carbon $jMasuk, Carbon $jPulang, float $istirahat, Carbon $scanMasuk, Carbon $scanPulang, ?float $standarShift = null): array
    {
        $bMasuk      = FingerprintService::bulatkan($scanMasuk);
        $masukBulat  = $bMasuk->lt($jMasuk) ? $jMasuk->copy() : $bMasuk;   // masuk lebih awal tidak dihitung
        $pulangBulat = FingerprintService::bulatkan($scanPulang);

        // standar mengikuti Kode Shift (mis. 7 jam), bukan jam pulang - jam masuk - istirahat
        $standar = $standarShift ?? (self::menit($jMasuk, $jPulang) / 60 - $istirahat);

        // jam hadir di dalam jadwal: istirahat tidak dikurangkan, lembur tidak ikut
        $akhirHitung = $pulangBulat->gt($jPulang) ? $jPulang : $pulangBulat;
        $hadir       = max(0, self::menit($masukBulat, $akhirHitung) / 60);

        $lembur    = max(0, self::menit($jPulang, $pulangBulat)) / 60;
        $potongan  = max(0, $standar - $hadir);
        $kehadiran = $standar > 0 ? max(0, min(1, $hadir / $standar)) : 0;

        return [
            'masuk_bulat'  => $masukBulat,
            'pulang_bulat' => $pulangBulat,
            'standar_jam'  => round($standar, 2),
            'potongan_jam' => round($potongan, 2),
            'total_jam'    => round($hadir, 2),
            'lembur_jam'   => round($lembur, 2),
            'kehadiran'    => round($kehadiran, 4),
        ];
    }

    public function hitung(string $dari, string $sampai): array
    {
        // jadwal mulai sehari sebelumnya, agar scan pulang shift K (lintas hari) tidak dianggap "tanpa jadwal"
        $jadwal = TrxJadwalKaryawan::whereBetween('tanggal', [Carbon::parse($dari)->subDay()->toDateString(), $sampai])->get();
        $shifts = MstKodeShift::all()->keyBy('kode');

        $karyawan = MstKaryawan::whereNotNull('id_absensi')->get()
            ->keyBy(fn ($k) => str_pad(trim($k->id_absensi), 9, '0', STR_PAD_LEFT));

        $logs = TrxFingerprintLog::whereBetween('scan_at', [
                Carbon::parse($dari)->subDay()->startOfDay(),
                Carbon::parse($sampai)->addDays(2)->endOfDay(),
            ])->orderBy('scan_at')->get()->groupBy('id_absen');

        $manual = TrxAbsensiKaryawan::where('is_manual', true)->whereBetween('tanggal', [$dari, $sampai])
            ->get()->mapWithKeys(fn ($m) => [$m->tanggal->toDateString() . '|' . $m->id_absen => true]);

        // Koreksi jam yang dibuat di halaman Finger Print
        $koreksi = TrxAbsensiHarian::where('is_manual', true)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->get()
            ->keyBy(fn ($m) => $m->tanggal->toDateString() . '|' . $m->id_absen);

        $now = now();
        $rows = [];
        $dipakai = [];

        foreach ($jadwal as $j) {
            $tgl   = $j->tanggal->toDateString();
            $key   = $tgl . '|' . $j->id_absen;
            $shift = $shifts[$j->kode_shift] ?? null;
            if (!$shift) {
                continue;
            }

            $k    = $karyawan[$j->id_absen] ?? null;
            $dept = $k?->departemen_cd;
            $jenis = ($dept === 'DRIVER' || $shift->jenis === 'driver') ? 'driver'
                   : ($shift->jenis === 'minggu' ? 'minggu' : ($shift->jenis === 'libur' ? 'libur' : 'jam'));

            $jm = $jp = null;
            if ($jenis === 'jam') {
                $jm = Carbon::parse("$tgl {$shift->jam_masuk}");
                $jp = Carbon::parse("$tgl {$shift->jam_pulang}");
                if ($shift->lintas_hari) {
                    $jp->addDay();
                }
                $awal  = $jm->copy()->subHours(self::SEBELUM_MASUK_JAM);
                $akhir = $jp->copy()->addHours(self::SESUDAH_PULANG_JAM);
            } else {
                $awal  = Carbon::parse($tgl)->startOfDay();
                $akhir = Carbon::parse($tgl)->endOfDay();
            }

            $scans = collect($logs->get($j->id_absen, []))->filter(fn ($l) => $l->scan_at->between($awal, $akhir))->values();
            foreach ($scans as $s) {
                $dipakai[$s->id] = true;
            }

            $kor = $koreksi[$key] ?? null;
            if ($kor && ($kor->jam_masuk || $kor->jam_pulang)) {
                $m = $kor->jam_masuk ?? $scans->first()?->scan_at;
                $p = $kor->jam_pulang ?? ($scans->count() > 1 ? $scans->last()->scan_at : null);
                $scans = collect([
                    $m ? (object) ['id' => null, 'scan_at' => $m] : null,
                    $p ? (object) ['id' => null, 'scan_at' => $p] : null,
                ])->filter()->values();
            }

            if ($jenis === 'libur' || $tgl < $dari || isset($manual[$key])) {
                continue; // OFF, di luar periode (hanya untuk menandai scan), atau sudah dikoreksi manual
            }

            $ist = (float) ($j->istirahat_jam ?? self::istirahatBawaan($shift, $j->tanggal));
            $row = [
                'tanggal' => $tgl, 'id_absen' => $j->id_absen, 'nik' => $k?->nik,
                'nama' => $k?->karyawan_nm ?? $j->nama, 'departemen_cd' => $dept,
                'kelompok_kerja' => $j->kelompok_kerja, 'kode_shift' => $j->kode_shift,
                'jadwal_masuk' => $jm, 'jadwal_pulang' => $jp,
                'masuk_finger' => $scans->first()?->scan_at, 'masuk_bulat' => null,
                'pulang_finger' => $scans->count() > 1 ? $scans->last()->scan_at : null, 'pulang_bulat' => null,
                'istirahat' => $ist, 'istirahat_bawaan' => $j->istirahat_jam === null,
                'standar_jam' => 0, 'potongan_jam' => 0, 'total_jam' => 0, 'lembur_jam' => 0, 'kehadiran' => 0,
                'status' => 'OK', 'catatan' => null, 'is_manual' => false,
                'created_at' => $now, 'updated_at' => $now,
            ];

            $lengkap = $scans->count() > 1
                && self::menit($scans->first()->scan_at, $scans->last()->scan_at) >= self::MIN_SELISIH_MENIT;

            if ($jenis === 'driver') {
                if ($scans->isEmpty()) {
                    $row['status'] = 'Tidak hadir';
                } else {
                    $row['kehadiran'] = 1;
                    $row['catatan']   = 'Driver: 1x finger = 1 hari hadir';
                }
            } elseif ($jenis === 'minggu') {
                if ($dept === 'SECURITY') {
                    $row['status'] = 'Perlu dicek';
                    $row['catatan'] = 'Security dijadwalkan kode L, seharusnya J atau K';
                } elseif ($scans->isEmpty()) {
                    $row['status'] = 'Tidak hadir';
                } elseif (!$lengkap) {
                    $row['status'] = 'Perlu dicek';
                    $row['catatan'] = 'Scan tidak lengkap';
                } else {
                    $row['masuk_bulat']  = FingerprintService::bulatkan($scans->first()->scan_at);
                    $row['pulang_bulat'] = FingerprintService::bulatkan($scans->last()->scan_at);
                    $total = max(0, self::menit($row['masuk_bulat'], $row['pulang_bulat']) / 60 - $ist);
                    $row['total_jam'] = $row['lembur_jam'] = round($total, 2); // Minggu: seluruh jam = lembur
                    $row['catatan'] = 'Hari Minggu: seluruh jam kerja = lembur';
                }
            } else { // jam normal / security
                $std = $shift->standar_jam !== null
                    ? (float) $shift->standar_jam
                    : self::menit($jm, $jp) / 60 - $ist;
                $row['standar_jam'] = round($std, 2);
                if ($scans->isEmpty()) {
                    $row['status'] = 'Tidak hadir';
                    $row['potongan_jam'] = $row['standar_jam'];
                } elseif (!$lengkap) {
                    $row['status'] = 'Perlu dicek';
                    $row['catatan'] = 'Scan tidak lengkap (hanya 1x atau selisih terlalu pendek)';
                } else {
                    $h = self::hitungShift($jm, $jp, $ist, $scans->first()->scan_at, $scans->last()->scan_at, $std);
                    if ($h['pulang_bulat']->lte($h['masuk_bulat'])) {
                        $row['status'] = 'Perlu dicek';
                        $row['catatan'] = 'Jam pulang tidak setelah jam masuk';
                    } else {
                        $row = array_merge($row, $h);
                    }
                }
            }

            $rows[$key] = $row;

            if ($kor ?? null) {
                $row['catatan'] = trim(($row['catatan'] ? $row['catatan'] . ' | ' : '') . 'Jam dikoreksi manual di Finger Print');
            }
        }

        // Scan yang tidak masuk jadwal mana pun
        foreach ($logs as $id => $list) {
            $sisa = $list->filter(fn ($l) => !isset($dipakai[$l->id]))->groupBy(fn ($l) => $l->scan_at->toDateString());
            foreach ($sisa as $tgl => $g) {
                $key = $tgl . '|' . $id;
                if ($tgl < $dari || $tgl > $sampai || isset($manual[$key]) || isset($rows[$key])) {
                    continue;
                }
                $k = $karyawan[$id] ?? null;
                $rows[$key] = [
                    'tanggal' => $tgl, 'id_absen' => $id, 'nik' => $k?->nik,
                    'nama' => $k?->karyawan_nm ?? $g->first()->nama, 'departemen_cd' => $k?->departemen_cd,
                    'kelompok_kerja' => null, 'kode_shift' => null, 'jadwal_masuk' => null, 'jadwal_pulang' => null,
                    'masuk_finger' => $g->first()->scan_at, 'masuk_bulat' => null,
                    'pulang_finger' => $g->count() > 1 ? $g->last()->scan_at : null, 'pulang_bulat' => null,
                    'istirahat' => 0, 'istirahat_bawaan' => false,
                    'standar_jam' => 0, 'potongan_jam' => 0, 'total_jam' => 0, 'lembur_jam' => 0, 'kehadiran' => 0,
                    'status' => 'Tanpa jadwal', 'catatan' => 'Ada scan tetapi tidak ada jadwal', 'is_manual' => false,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }

        $rows = array_values($rows);

        DB::transaction(function () use ($rows, $dari, $sampai) {
            TrxAbsensiKaryawan::where('is_manual', false)->whereBetween('tanggal', [$dari, $sampai])->delete();
            foreach (array_chunk($rows, 300) as $chunk) {
                TrxAbsensiKaryawan::insert($chunk);
            }
        });

        $per = collect($rows)->countBy('status');

        return [
            'total'        => count($rows),
            'perlu_dicek'  => $per['Perlu dicek'] ?? 0,
            'tidak_hadir'  => $per['Tidak hadir'] ?? 0,
            'tanpa_jadwal' => $per['Tanpa jadwal'] ?? 0,
        ];
    }
    private static function istirahatBawaan(MstKodeShift $s, Carbon $tgl): float
    {
        $khusus = match ($tgl->dayOfWeek) {
            Carbon::FRIDAY   => $s->istirahat_jumat,
            Carbon::SATURDAY => $s->istirahat_sabtu,
            default          => null,
        };

        return (float) ($khusus ?? $s->istirahat_default);
    }
}