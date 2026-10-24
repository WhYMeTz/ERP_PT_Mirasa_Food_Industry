<?php

namespace App\Support;

/** Angka rupiah menjadi kata, mis. 2695051 => "Dua Juta Enam Ratus Sembilan Puluh Lima Ribu Lima Puluh Satu Rupiah". */
class Terbilang
{
    private const SATUAN = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

    public static function rupiah(int $angka): string
    {
        if ($angka === 0) {
            return 'Nol Rupiah';
        }
        $teks = trim(preg_replace('/\s+/', ' ', self::kata(abs($angka))));
        return ucwords(($angka < 0 ? 'minus ' : '') . $teks) . ' Rupiah';
    }

    private static function kata(int $n): string
    {
        if ($n < 12) {
            return ' ' . self::SATUAN[$n];
        }
        if ($n < 20) {
            return self::kata($n - 10) . ' belas';
        }
        if ($n < 100) {
            return self::kata(intdiv($n, 10)) . ' puluh' . self::kata($n % 10);
        }
        if ($n < 200) {
            return ' seratus' . self::kata($n - 100);
        }
        if ($n < 1000) {
            return self::kata(intdiv($n, 100)) . ' ratus' . self::kata($n % 100);
        }
        if ($n < 2000) {
            return ' seribu' . self::kata($n - 1000);
        }
        if ($n < 1000000) {
            return self::kata(intdiv($n, 1000)) . ' ribu' . self::kata($n % 1000);
        }
        if ($n < 1000000000) {
            return self::kata(intdiv($n, 1000000)) . ' juta' . self::kata($n % 1000000);
        }
        if ($n < 1000000000000) {
            return self::kata(intdiv($n, 1000000000)) . ' miliar' . self::kata($n % 1000000000);
        }
        return self::kata(intdiv($n, 1000000000000)) . ' triliun' . self::kata($n % 1000000000000);
    }
}