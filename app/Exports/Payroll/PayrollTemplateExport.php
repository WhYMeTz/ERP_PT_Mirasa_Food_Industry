<?php

namespace App\Exports\Payroll;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Template import komponen: berisi karyawan periode ini beserta nilai komponen yang sekarang berlaku. */
class PayrollTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithStyles, WithStrictNullComparison
{
    public function __construct(private Collection $rows)
    {
    }

    public function headings(): array
    {
        return ['ID Karyawan', 'Nama', 'Gaji Harian', 'Tunjangan Jabatan', 'Insentif', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Kasbon Lainnya', 'Catatan'];
    }

    public function array(): array
    {
        return $this->rows->values()->map(fn ($r) => [
            (string) ($r['nik'] ?? $r['id_absen']),
            $r['nama'],
            (int) round($r['input']['gaji_harian']),
            (int) round($r['input']['tunjangan_jabatan']),
            (int) round($r['input']['insentif']),
            (int) round($r['input']['potongan_bpjs_kes']),
            (int) round($r['input']['potongan_bpjs_tk']),
            (int) round($r['input']['potongan_lainnya']),
            $r['catatan'] ?? '',
        ])->all();
    }

    public function columnFormats(): array
    {
        $rupiah = '#,##0';
        return ['A' => '@', 'C' => $rupiah, 'D' => $rupiah, 'E' => $rupiah, 'F' => $rupiah, 'G' => $rupiah, 'H' => $rupiah];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('C2');
        return [1 => [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']],
        ]];
    }
}