<?php

namespace App\Services\Pajak;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * Membuat berkas XML "Bp21Bulk" (unggah massal Bukti Potong BP21 / PPh 21 Non-Final di Coretax).
 * Format mengikuti berkas hasil lembar "BP21 Excel to XML" perusahaan: indentasi tab, baris CRLF.
 */
class Bp21Xml
{
    private const NL = "\r\n";

    /**
     * @param array{tin:string} $pemotong  NPWP pemotong (16 digit)
     * @param array<int,array<string,mixed>> $baris  tiap baris:
     *   masa, tahun, tin, ptkp, kode_objek, gross, rate, no_dok, tgl_dok, tgl_potong, tku_penerima?, tku_pemotong?,
     *   fasilitas? ('N/A'), deemed? (100), dokumen? ('Other')
     */
    public function buat(array $pemotong, array $baris): string
    {
        $tinPemotong = $this->digits($pemotong['tin'] ?? '', 'NPWP pemotong');

        $x  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . self::NL;
        $x .= '<Bp21Bulk xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . self::NL;
        $x .= "\t<TIN>{$tinPemotong}</TIN>" . self::NL;
        $x .= "\t<ListOfBp21>" . self::NL;

        foreach ($baris as $i => $r) {
            $x .= $this->satuBaris($r, $tinPemotong, $i + 1);
        }

        $x .= "\t</ListOfBp21>" . self::NL;
        $x .= '</Bp21Bulk>';

        return $x;
    }

    private function satuBaris(array $r, string $tinPemotong, int $no): string
    {
        $tin = $this->digits($r['tin'] ?? '', "NPWP/NIK penerima baris {$no}");

        $kolom = [
            'TaxPeriodMonth'  => (string) (int) $r['masa'],
            'TaxPeriodYear'   => (string) (int) $r['tahun'],
            'CounterpartTin'  => $tin,
            'IDPlaceOfBusinessActivityOfIncomeRecipient' => $r['tku_penerima'] ?? ($tin . '000000'),
            'StatusTaxExemption' => (string) $r['ptkp'],
            'TaxCertificate'  => (string) ($r['fasilitas'] ?? 'N/A'),
            'TaxObjectCode'   => (string) $r['kode_objek'],
            'Gross'           => $this->angka((float) $r['gross']),
            'Deemed'          => $this->angka((float) ($r['deemed'] ?? 100)),
            'Rate'            => $this->angka((float) $r['rate']),
            'Document'        => (string) ($r['dokumen'] ?? 'Other'),
            'DocumentNumber'  => (string) $r['no_dok'],
            'DocumentDate'    => $this->tanggal($r['tgl_dok']),
            'IDPlaceOfBusinessActivity' => $r['tku_pemotong'] ?? ($tinPemotong . '000000'),
            'WithholdingDate' => $this->tanggal($r['tgl_potong']),
        ];

        $x = "\t\t<Bp21>" . self::NL;
        foreach ($kolom as $nama => $nilai) {
            $x .= "\t\t\t<{$nama}>" . htmlspecialchars((string) $nilai, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</{$nama}>" . self::NL;
        }

        return $x . "\t\t</Bp21>" . self::NL;
    }

    /** Angka dengan maksimal 15 digit signifikan, titik desimal, tanpa nol di belakang (sama seperti ekspor Excel). */
    private function angka(float $nilai): string
    {
        return sprintf('%.15g', $nilai);
    }

    private function tanggal(mixed $nilai): string
    {
        if ($nilai instanceof DateTimeInterface) {
            return $nilai->format('Y-m-d');
        }

        $t = strtotime((string) $nilai);
        if ($t === false) {
            throw new InvalidArgumentException('Tanggal tidak valid: ' . (string) $nilai);
        }

        return date('Y-m-d', $t);
    }

    private function digits(string $nilai, string $label): string
    {
        $bersih = preg_replace('/\D/', '', $nilai);

        if (strlen($bersih) !== 16) {
            throw new InvalidArgumentException("{$label} harus 16 digit (diisi: \"{$nilai}\").");
        }

        return $bersih;
    }
}
