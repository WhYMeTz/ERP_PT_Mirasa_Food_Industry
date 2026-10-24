<?php

namespace App\Services\Manajemen;

/**
 * Membaca dan memvalidasi baris dari file Excel import komponen payroll. Murni, tanpa database.
 * Aturan: kolom kosong = tidak diubah. Satu saja baris salah = seluruh import dibatalkan.
 */
class PayrollImportParser
{
    /** Judul kolom di file (sudah dalam bentuk slug) => kolom komponen payroll. */
    public const KOLOM = [
        'gaji_harian'          => 'gaji_harian',
        'tunjangan_jabatan'    => 'tunjangan_jabatan',
        'insentif'             => 'insentif',
        'bpjs_kesehatan'       => 'potongan_bpjs_kes',
        'bpjs_ketenagakerjaan' => 'potongan_bpjs_tk',
        'kasbon_lainnya'       => 'potongan_lainnya',
    ];

    private const NAMA = [
        'gaji_harian' => 'Gaji Harian', 'tunjangan_jabatan' => 'Tunjangan Jabatan', 'insentif' => 'Insentif',
        'bpjs_kesehatan' => 'BPJS Kesehatan', 'bpjs_ketenagakerjaan' => 'BPJS Ketenagakerjaan', 'kasbon_lainnya' => 'Kasbon Lainnya',
    ];

    /** Bentuk baku ID Karyawan: tanpa spasi, huruf besar. Angka dari Excel (3305771.0) dijadikan teks tanpa ".0". */
    public static function kunci(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_float($v) && floor($v) == $v) {
            $v = sprintf('%.0f', $v);
        }
        return strtoupper(preg_replace('/\s+/u', '', (string) $v));
    }

    /** Nominal rupiah: 97300, "97.300", "97,300", "Rp 97.300". Mengembalikan null bila bukan nominal yang sah. */
    public static function rupiah(mixed $v): ?float
    {
        if (is_int($v) || is_float($v)) {
            return $v >= 0 ? (float) round($v) : null;
        }
        $s = trim(str_ireplace('rp', '', (string) $v));
        if (!preg_match('/^(\d{1,3}([.,]\d{3})+|\d+)$/', $s)) {
            return null;
        }
        return (float) preg_replace('/\D/', '', $s);
    }

    private static function kosong(mixed $v): bool
    {
        return $v === null || trim((string) $v) === '';
    }

    /**
     * @param array $baris      baris file (judul kolom sudah berupa slug), baris pertama data = baris Excel ke-2
     * @param array $kunciValid ['MNT1N1003' => true, ...] karyawan yang boleh diimpor
     * @return array{perubahan:array, errors:array, kosong:int}
     */
    public static function baca(array $baris, array $kunciValid): array
    {
        $hasil = ['perubahan' => [], 'errors' => [], 'kosong' => 0];

        if (!$baris) {
            $hasil['errors'][] = 'File kosong atau tidak ada baris data.';
            return $hasil;
        }
        $judul = array_keys((array) $baris[0]);
        if (!in_array('id_karyawan', $judul, true)) {
            $hasil['errors'][] = 'Kolom "ID Karyawan" tidak ditemukan pada baris judul. Gunakan file dari tombol Unduh Template.';
            return $hasil;
        }
        $kolomAda = array_values(array_intersect(array_keys(self::KOLOM), $judul));
        if (!$kolomAda && !in_array('catatan', $judul, true)) {
            $hasil['errors'][] = 'Tidak ada kolom komponen (Gaji Harian, Tunjangan Jabatan, dst) di file ini. Gunakan file dari tombol Unduh Template.';
            return $hasil;
        }

        $terlihat = [];
        foreach (array_values($baris) as $i => $r) {
            $r   = (array) $r;
            $no  = $i + 2;
            $isi = array_filter($r, fn ($x) => !self::kosong($x));
            if (!$isi) {
                $hasil['kosong']++;
                continue;
            }

            $kunci = self::kunci($r['id_karyawan'] ?? null);
            if ($kunci === '') {
                $hasil['errors'][] = "Baris {$no}: ID Karyawan kosong.";
                continue;
            }
            if (!isset($kunciValid[$kunci])) {
                $hasil['errors'][] = "Baris {$no}: ID Karyawan {$kunci} tidak ditemukan atau tidak punya absensi pada periode ini.";
                continue;
            }
            if (isset($terlihat[$kunci])) {
                $hasil['errors'][] = "Baris {$no}: ID Karyawan {$kunci} sudah muncul di baris {$terlihat[$kunci]}.";
                continue;
            }
            $terlihat[$kunci] = $no;

            $komponen = [];
            $salah    = false;
            foreach ($kolomAda as $slug) {
                if (self::kosong($r[$slug] ?? null)) {
                    continue;   // kosong = tidak diubah
                }
                $nilai = self::rupiah($r[$slug]);
                if ($nilai === null) {
                    $hasil['errors'][] = "Baris {$no}: {$kunci}, kolom " . self::NAMA[$slug] . " berisi \"{$r[$slug]}\", bukan nominal rupiah yang sah.";
                    $salah = true;
                    continue;
                }
                $komponen[self::KOLOM[$slug]] = $nilai;
            }
            if ($salah) {
                continue;
            }

            $catatan = self::kosong($r['catatan'] ?? null) ? null : trim((string) $r['catatan']);
            if ($catatan !== null && mb_strlen($catatan) > 255) {
                $hasil['errors'][] = "Baris {$no}: {$kunci}, catatan lebih dari 255 karakter.";
                continue;
            }

            $hasil['perubahan'][$kunci] = ['komponen' => $komponen, 'catatan' => $catatan, 'baris' => $no];
        }

        return $hasil;
    }
}