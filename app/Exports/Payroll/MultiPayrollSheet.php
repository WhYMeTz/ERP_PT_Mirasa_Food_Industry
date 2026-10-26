<?php
// app/Exports/Payroll/MultiPayrollSheet.php
namespace App\Exports\Payroll;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MultiPayrollSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting, WithStyles, WithStrictNullComparison
{
    /**
     * @param array<int,string> $kolom   judul kolom
     * @param array<int,array>  $baris
     * @param array<string,string> $format  kolom => format, mis. ['B' => '@', 'G' => '#,##0.00']
     */
    public function __construct(
        private string $judul,
        private array $kolom,
        private array $baris,
        private array $format = []
    ) {
    }

    public function title(): string
    {
        // Nama sheet Excel: maksimal 31 karakter dan tanpa \ / ? * [ ] :
        return substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $this->judul), 0, 31);
    }

    public function headings(): array
    {
        return $this->kolom;
    }

    public function array(): array
    {
        return $this->baris;
    }

    public function columnFormats(): array
    {
        return $this->format;
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E293B']],
            ],
        ];
    }
}
