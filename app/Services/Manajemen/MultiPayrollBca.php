<?php

namespace App\Services\Manajemen;

use InvalidArgumentException;

/**
 * Pembuat file TXT Multi Payroll KlikBCA Bisnis. Murni (tanpa database) supaya mudah diuji.
 *
 * Header : 0|PY|{corp}|{company}|{id header 8 digit}|{YYYYMMDD}||{rek sumber}|{jumlah 5 digit}||{remarks1}|{remarks2}
 * Detail : 1|{id transaksi}|BCA||{rekening}|{nama}|{jumlah 2 desimal}||{remarks2}||||
 * Setiap baris diakhiri CRLF (termasuk baris terakhir).
 */
class MultiPayrollBca
{
    public const MAKS_ID = 18;
    public const MAKS_JUMLAH = 9999999999999; // 13 digit

    /** Hapus pemisah '|', apostrof, karakter non-ASCII/kontrol; rapikan spasi; potong ke $maks. */
    public static function bersihNama(string $nama, int $maks): string
    {
        $nama = str_replace(["'", '’', '‘', '`', '|'], '', $nama);
        $nama = preg_replace('/[^\x20-\x7E]/', '', $nama);
        $nama = trim(preg_replace('/\s+/', ' ', $nama));

        return rtrim(substr($nama, 0, $maks));
    }

    /**
     * ID transaksi = tahun(4) tanggal(2) bulan(2) jam(2) kode urut(2), lalu '0' di depan sampai 18 karakter.
     */
    public static function idTransaksi(int $tahun, int $tanggal, int $bulan, int $jam, string $kode, int $urut): string
    {
        $isi = sprintf('%04d%02d%02d%02d%s%02d', $tahun, $tanggal, $bulan, $jam, $kode, $urut);

        if (strlen($isi) > self::MAKS_ID) {
            throw new InvalidArgumentException("ID transaksi {$isi} melebihi " . self::MAKS_ID . ' karakter.');
        }

        return str_pad($isi, self::MAKS_ID, '0', STR_PAD_LEFT);
    }

    /**
     * @param array{
     *   header_id:int, tanggal_transfer:string, corporate_id:string, company_code:string,
     *   rekening_sumber:string, remarks1:string, remarks2:string, kode:string,
     *   tahun:int, tanggal:int, bulan:int, jam:int, maks_nama:int, kode_bank?:string
     * } $p   tanggal_transfer berformat Y-m-d
     * @param list<array{rekening:string, nama:string, jumlah:int}> $baris
     */
    public static function buat(array $p, array $baris): string
    {
        if (count($baris) === 0) {
            throw new InvalidArgumentException('Tidak ada baris untuk dibuat.');
        }
        if (count($baris) > 99999) {
            throw new InvalidArgumentException('Jumlah baris melebihi 99999.');
        }

        $tgl = \DateTime::createFromFormat('!Y-m-d', $p['tanggal_transfer']);
        if (! $tgl || $tgl->format('Y-m-d') !== $p['tanggal_transfer']) {
            throw new InvalidArgumentException('Tanggal transfer tidak valid.');
        }

        $bank = $p['kode_bank'] ?? 'BCA';
        $out  = [];
        $out[] = implode('|', [
            '0', 'PY', $p['corporate_id'], $p['company_code'],
            sprintf('%08d', $p['header_id']), $tgl->format('Ymd'), '',
            $p['rekening_sumber'], sprintf('%05d', count($baris)), '',
            self::bersihTeks($p['remarks1']), self::bersihTeks($p['remarks2']),
        ]);

        foreach (array_values($baris) as $i => $b) {
            $rek = preg_replace('/\D/', '', (string) $b['rekening']);
            if ($rek === '') {
                throw new InvalidArgumentException('Baris ' . ($i + 1) . ': rekening kosong.');
            }
            $jumlah = (int) $b['jumlah'];
            if ($jumlah <= 0 || $jumlah > self::MAKS_JUMLAH) {
                throw new InvalidArgumentException('Baris ' . ($i + 1) . ': jumlah tidak valid.');
            }
            $nama = self::bersihNama((string) $b['nama'], $p['maks_nama']);
            if ($nama === '') {
                throw new InvalidArgumentException('Baris ' . ($i + 1) . ': nama kosong.');
            }

            $out[] = implode('|', [
                '1',
                self::idTransaksi($p['tahun'], $p['tanggal'], $p['bulan'], $p['jam'], $p['kode'], $i + 1),
                $bank, '', $rek, $nama, $jumlah . '.00', '', self::bersihTeks($p['remarks2']), '', '', '', '',
            ]);
        }

        return implode("\r\n", $out) . "\r\n";
    }

    private static function bersihTeks(string $t): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace(['|', "'"], '', $t)));
    }
}
