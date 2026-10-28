<?php

namespace App\Services\Manajemen;

use App\Models\Manajemen\TrxJadwalKaryawan;
use Illuminate\Support\Facades\DB;

class JadwalService
{
    public function simpan(array $rows): array
    {
        $now  = now();
        $data = array_map(fn ($r) => $r + ['created_at' => $now, 'updated_at' => $now], $rows);

        DB::transaction(function () use ($data) {
            foreach (array_chunk($data, 500) as $chunk) {
                TrxJadwalKaryawan::upsert(
                    $chunk,
                    ['tanggal', 'id_absen'],
                    ['nama', 'kelompok_kerja', 'kode_shift', 'istirahat_jam', 'updated_at']
                );
            }
        });

        return [
            'baris'  => count($rows),
            'dari'   => collect($rows)->min('tanggal'),
            'sampai' => collect($rows)->max('tanggal'),
        ];
    }

    /**
     * Susun baris jadwal untuk input manual: setiap karyawan x setiap tanggal di rentang yang harinya dipilih.
     *
     * @param list<array{id:string, nama:?string, kelompok:?string}> $karyawan  id = ID absen 9 digit
     * @param list<int> $hari  nomor hari ISO: 1 = Senin ... 7 = Minggu
     * @param string|null $kelompok  kosong = pakai kelompok masing-masing karyawan
     * @param float|null $istirahat  null = pakai bawaan shift
     */
    public function susunBaris(array $karyawan, string $dari, string $sampai, array $hari, string $kodeShift, ?string $kelompok, ?float $istirahat): array
    {
        $rows = [];
        $tgl  = new \DateTimeImmutable($dari);
        $akhir = new \DateTimeImmutable($sampai);

        for (; $tgl <= $akhir; $tgl = $tgl->modify('+1 day')) {
            if (! in_array((int) $tgl->format('N'), $hari, true)) {
                continue;
            }
            foreach ($karyawan as $k) {
                $rows[] = [
                    'tanggal'        => $tgl->format('Y-m-d'),
                    'id_absen'       => str_pad((string) $k['id'], 9, '0', STR_PAD_LEFT),
                    'nama'           => ($k['nama'] ?? '') !== '' ? $k['nama'] : null,
                    'kelompok_kerja' => ($kelompok !== null && $kelompok !== '') ? $kelompok : (($k['kelompok'] ?? '') !== '' ? $k['kelompok'] : null),
                    'kode_shift'     => $kodeShift,
                    'istirahat_jam'  => $istirahat,
                ];
            }
        }

        return $rows;
    }
}
