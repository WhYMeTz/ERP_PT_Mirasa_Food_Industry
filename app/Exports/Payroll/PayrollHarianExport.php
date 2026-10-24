<?php

namespace App\Exports\Payroll;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollHarianExport implements FromArray, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithStyles, WithStrictNullComparison
{
    public function __construct(private array $laporan)
    {
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'IFM (orang)', 'IFM (Rp)', 'Manual (orang)', 'Manual (Rp)', 'Packing (orang)', 'Packing (Rp)',
            'Total Langsung (orang)', 'Total Langsung (Rp)', 'Tidak Langsung (orang)', 'Tidak Langsung (Rp)',
            'Total (orang)', 'Total (Rp)', 'Perlu Dicek',
        ];
    }

    public function array(): array
    {
        $data = [];
        foreach (array_reverse($this->laporan['hari']) as $h) {
            $data[] = $this->baris(Carbon::parse($h['tanggal'])->format('d-m-Y'), $h, $h['perlu']);
        }
        $data[] = $this->baris('TOTAL', $this->laporan['total'], $this->laporan['ringkasan']['perlu']);

        return $data;
    }

    private function baris(string $label, array $h, int $perlu): array
    {
        $p = fn (array $s) => [$s['orang'], $s['biaya']];

        return array_merge(
            [$label],
            $p($h['kel']['Langsung IFM']), $p($h['kel']['Langsung Manual']), $p($h['kel']['Langsung Packing']),
            $p($h['langsung']), $p($h['tidak']), $p($h['total']),
            [$perlu]
        );
    }

    public function columnFormats(): array
    {
        $rp = '#,##0';

        return ['C' => $rp, 'E' => $rp, 'G' => $rp, 'I' => $rp, 'K' => $rp, 'M' => $rp];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('B2');
        $akhir = count($this->laporan['hari']) + 2;

        return [
            1      => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']]],
            $akhir => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }
}