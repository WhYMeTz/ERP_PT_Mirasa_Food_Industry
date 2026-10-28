<?php
// app/Exports/Payroll/MultiPayrollBankSheet.php
namespace App\Exports\Payroll;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Satu sheet bagian dengan tampilan sama seperti Excel Multi Payroll lama:
 * header hitam berhuruf putih, semua sel bergaris, kolom rata tengah, lebar kolom tetap.
 */
class MultiPayrollBankSheet implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithColumnFormatting, WithStyles, WithStrictNullComparison
{
    public const KOLOM = ['No', 'Transaction ID', 'Transfer Type', 'Beneficiary ID', 'Credited Account', 'Receiver Name', 'Amount',
                          'NIP', 'Remark', 'Beneficiary email address', 'Receiver Swift Code', 'Receiver Cust Type', 'Receiver Cust Residence'];

    public function __construct(private string $judul, private array $baris)
    {
    }

    public function title(): string
    {
        return substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $this->judul), 0, 31);
    }

    public function headings(): array
    {
        return self::KOLOM;
    }

    public function array(): array
    {
        return $this->baris;
    }

    public function columnFormats(): array
    {
        return ['B' => '@', 'E' => '@', 'G' => '#,##0.00'];
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 22, 'C' => 15, 'D' => 20, 'E' => 20, 'F' => 38, 'G' => 18, 'H' => 10, 'I' => 16, 'J' => 28, 'K' => 22, 'L' => 20, 'M' => 26];
    }

        public function styles(Worksheet $sheet): ?array
    {
        $akhir  = count($this->baris) + 1;
        $huruf  = Coordinate::stringFromColumnIndex(count(self::KOLOM));
        $tengah = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];

        return [
            "A1:{$huruf}{$akhir}" => ['borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '000000']]]],
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            "A2:C{$akhir}" => $tengah,
            "E2:E{$akhir}" => $tengah,
            "H2:I{$akhir}" => $tengah,
            "F2:F{$akhir}" => ['alignment' => ['horizontal' => 'left', 'vertical' => 'center']],
            "G2:G{$akhir}" => ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']],
        ];
    }
}
