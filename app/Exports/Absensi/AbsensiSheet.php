<?php
// app/Exports/Absensi/AbsensiSheet.php
namespace App\Exports\Absensi;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting, WithStyles, WithStrictNullComparison
{
    /**
     * @param array<int,string>    $kolom
     * @param array<int,array>     $baris       baris terakhir = baris TOTAL bila $adaTotal
     * @param array<string,string> $format      kolom => format angka/tanggal
     */
    public function __construct(
        private string $judul,
        private array $kolom,
        private array $baris,
        private array $format = [],
        private bool $adaTotal = false
    ) {
    }

    public function title(): string
    {
        return substr($this->judul, 0, 31);
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
        $huruf = Coordinate::stringFromColumnIndex(count($this->kolom));
        $akhir = count($this->baris) + 1;

        $gaya = [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            "A1:{$huruf}{$akhir}" => ['borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'BFBFBF']]]],
        ];

        if ($this->adaTotal && $akhir > 1) {
            $gaya[$akhir] = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DDEBF7']],
            ];
        }

        return $gaya;
    }
}
