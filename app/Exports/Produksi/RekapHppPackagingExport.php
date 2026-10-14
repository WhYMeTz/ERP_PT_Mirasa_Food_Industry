<?php

namespace App\Exports\Produksi;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapHppPackagingExport
{
    protected array $report;
    protected int   $year;
    protected int   $month;
    protected string $monthName;
    protected string $printedBy;
    protected string $printedAt;

    // Warna Resmi Spreadsheet Mirasa
    const COLOR_HEADER_BG   = 'FF0284C7'; // Sky Blue 600
    const COLOR_HEADER_TEXT = 'FFFFFFFF'; // White
    const COLOR_SUB_BG      = 'FFF0F9FF'; // Light Sky Blue
    const COLOR_TOTAL_BG    = 'FFFEF3C7'; // Amber Light
    const COLOR_BORDER      = 'FFCBD5E1'; // Slate 300

    public function __construct(
        array $report,
        int $year,
        int $month,
        string $monthName,
        string $printedBy,
        string $printedAt
    ) {
        $this->report    = $report;
        $this->year      = $year;
        $this->month     = $month;
        $this->monthName = $monthName;
        $this->printedBy = $printedBy;
        $this->printedAt = $printedAt;
    }

    public function download(string $filename): StreamedResponse
    {
        $spreadsheet = $this->build();

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    protected function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('HPP Kemas ' . substr($this->monthName, 0, 3) . ' ' . $this->year);
        $sheet->setShowGridLines(true);

        // Judul Laporan
        $sheet->setCellValue('A1', 'PT MIRASA FOOD INDUSTRY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue('A2', 'BUKU REKAPITULASI HARGA POKOK PRODUKSI (HPP) - PENGEMASAN (PACKAGING)');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FF0284C7');

        $sheet->setCellValue('A3', 'Periode: ' . strtoupper($this->monthName) . ' ' . $this->year . ' | Dicetak oleh: ' . $this->printedBy . ' pada ' . $this->printedAt);
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setARGB('FF64748B');

        // Header Tabel Kolom (Row 5)
        $headers = [
            'A5' => 'HARI',
            'B5' => 'TANGGAL',
            'C5' => 'DOKUMEN PCK',
            'D5' => 'TARGET PRODUK JADI (FG)',
            'E5' => 'BATCH NO',
            'F5' => 'BIAYA CURAH (WIP)',
            'G5' => 'BIAYA KEMASAN',
            'H5' => 'UPAH PACKING',
            'I5' => 'BIAYA LAIN/FOH',
            'J5' => 'TOTAL BIAYA KEMAS',
            'K5' => 'HASIL (KARTON)',
            'L5' => 'HASIL (PCS)',
            'M5' => 'HPP / KARTON',
            'N5' => 'HPP / PCS',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A5:N5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => self::COLOR_HEADER_TEXT],
                'size' => 9,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLOR_HEADER_BG],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // Isi Data Harian (Row 6 dst.)
        $row = 6;
        foreach ($this->report['days'] as $d) {
            $hasData = $d['has_data'] ?? false;

            $sheet->setCellValue('A' . $row, $d['hari_nm']);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($d['date'])));
            $sheet->setCellValue('C' . $row, $hasData ? ($d['primary_pack_no'] ?? '-') : '-');
            $sheet->setCellValue('D' . $row, $hasData ? ($d['primary_product_nm'] ?? '-') : '-');
            $sheet->setCellValue('E' . $row, $hasData ? ($d['primary_batch_no'] ?? '-') : '-');

            if ($hasData) {
                $sheet->setCellValue('F' . $row, (float) $d['total_biaya_wip']);
                $sheet->setCellValue('G' . $row, (float) $d['total_biaya_kemasan']);
                $sheet->setCellValue('H' . $row, (float) $d['total_biaya_tenaga_kerja']);
                $sheet->setCellValue('I' . $row, (float) $d['total_biaya_lain']);
                $sheet->setCellValue('J' . $row, (float) $d['total_biaya_produksi']);
                $sheet->setCellValue('K' . $row, (float) $d['hasil_karton']);
                $sheet->setCellValue('L' . $row, (float) $d['hasil_pcs']);
                $sheet->setCellValue('M' . $row, (float) $d['hpp_per_karton']);
                $sheet->setCellValue('N' . $row, (float) $d['hpp_per_pcs']);
            } else {
                $sheet->setCellValue('F' . $row, 0);
                $sheet->setCellValue('G' . $row, 0);
                $sheet->setCellValue('H' . $row, 0);
                $sheet->setCellValue('I' . $row, 0);
                $sheet->setCellValue('J' . $row, 0);
                $sheet->setCellValue('K' . $row, 0);
                $sheet->setCellValue('L' . $row, 0);
                $sheet->setCellValue('M' . $row, 0);
                $sheet->setCellValue('N' . $row, 0);
            }

            // Number formatting
            $sheet->getStyle('F' . $row . ':J' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M' . $row . ':N' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // Alignment
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row . ':E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('F' . $row . ':N' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Styling jika ada data
            if ($hasData) {
                $sheet->getStyle('J' . $row)->getFont()->setBold(true);
                $sheet->getStyle('M' . $row . ':N' . $row)->getFont()->setBold(true);
            } else {
                $sheet->getStyle('A' . $row . ':N' . $row)->getFont()->getColor()->setARGB('FF94A3B8');
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        // Baris Grand Total
        $tot = $this->report['totals'];
        $sheet->setCellValue('A' . $row, 'TOTAL / RATA-RATA');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('F' . $row, (float) $tot['total_biaya_wip']);
        $sheet->setCellValue('G' . $row, (float) $tot['total_biaya_kemasan']);
        $sheet->setCellValue('H' . $row, (float) $tot['total_biaya_tenaga_kerja']);
        $sheet->setCellValue('I' . $row, (float) $tot['total_biaya_lain']);
        $sheet->setCellValue('J' . $row, (float) $tot['total_biaya_produksi']);
        $sheet->setCellValue('K' . $row, (float) $tot['total_karton']);
        $sheet->setCellValue('L' . $row, (float) $tot['total_pcs']);
        $sheet->setCellValue('M' . $row, (float) $tot['rata_hpp_karton']);
        $sheet->setCellValue('N' . $row, (float) $tot['rata_hpp_pcs']);

        $sheet->getStyle('F' . $row . ':J' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('M' . $row . ':N' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLOR_TOTAL_BG],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Border seluruh tabel data
        $sheet->getStyle('A5:N' . $row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => self::COLOR_BORDER],
                ],
            ],
        ]);

        // Auto width kolom
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
