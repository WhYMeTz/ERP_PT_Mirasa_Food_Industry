<?php
// app/Services/Manajemen/AbsensiLaporan.php
namespace App\Services\Manajemen;

/** Perhitungan murni untuk laporan absensi (rekap per karyawan), dipakai Excel dan PDF. */
class AbsensiLaporan
{
    /**
     * Rekap per karyawan (kunci: id_absen), urut nama.
     *
     * @param iterable<array> $baris tiap item: id_absen, nik, nama, departemen, kelompok, kehadiran, total_jam, potongan_jam, lembur_jam, status
     * @return list<array>
     */
    public static function rekap(iterable $baris): array
    {
        $out = [];

        foreach ($baris as $r) {
            $k = (string) $r['id_absen'];
            if (! isset($out[$k])) {
                $out[$k] = [
                    'id_absen' => $k, 'nik' => $r['nik'] ?? null, 'nama' => $r['nama'],
                    'departemen' => $r['departemen'] ?? null, 'kelompok' => $r['kelompok'] ?? null,
                    'hari_data' => 0, 'kehadiran' => 0.0, 'total_jam' => 0.0, 'potongan' => 0.0, 'lembur' => 0.0,
                    'perlu' => 0, 'tidak_hadir' => 0,
                ];
            }

            $out[$k]['hari_data']++;
            $out[$k]['kehadiran'] += (float) ($r['kehadiran'] ?? 0);
            $out[$k]['total_jam'] += (float) ($r['total_jam'] ?? 0);
            $out[$k]['potongan']  += (float) ($r['potongan_jam'] ?? 0);
            $out[$k]['lembur']    += (float) ($r['lembur_jam'] ?? 0);

            $status = $r['status'] ?? '';
            if ($status === 'Perlu dicek' || $status === 'Tanpa jadwal') {
                $out[$k]['perlu']++;
            } elseif ($status === 'Tidak hadir') {
                $out[$k]['tidak_hadir']++;
            }
            if (! empty($r['kelompok'])) {
                $out[$k]['kelompok'] = $r['kelompok']; // yang terakhir dipakai
            }
        }

        foreach ($out as &$o) {
            $o['kehadiran'] = round($o['kehadiran'], 4);
            $o['total_jam'] = round($o['total_jam'], 2);
            $o['potongan']  = round($o['potongan'], 2);
            $o['lembur']    = round($o['lembur'], 2);
        }
        unset($o);

        uasort($out, fn ($a, $b) => strnatcasecmp($a['nama'], $b['nama']));

        return array_values($out);
    }

    /** Jumlah seluruh kolom angka dari hasil rekap(). */
    public static function total(array $rekap): array
    {
        $t = ['karyawan' => count($rekap), 'hari_data' => 0, 'kehadiran' => 0.0, 'total_jam' => 0.0, 'potongan' => 0.0, 'lembur' => 0.0, 'perlu' => 0, 'tidak_hadir' => 0];

        foreach ($rekap as $r) {
            foreach (['hari_data', 'kehadiran', 'total_jam', 'potongan', 'lembur', 'perlu', 'tidak_hadir'] as $f) {
                $t[$f] += $r[$f];
            }
        }
        foreach (['kehadiran' => 4, 'total_jam' => 2, 'potongan' => 2, 'lembur' => 2] as $f => $d) {
            $t[$f] = round($t[$f], $d);
        }

        return $t;
    }
}
