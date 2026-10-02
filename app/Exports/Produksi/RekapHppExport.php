<?php

namespace App\Exports\Produksi;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapHppExport
{
    protected array $report;
    protected int   $year;
    protected int   $month;
    protected string $monthName;
    protected string $printedBy;
    protected string $printedAt;

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
        $sheet->setTitle('Rekap HPP ' . substr($this->monthName, 0, 3));
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('A1', 'PT MIRASA FOOD INDUSTRY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'BUKU REKAPITULASI HPP HARIAN & RENDEMEN PRODUKSI');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $sheet->setCellValue('A3', "Periode: Bulan {$this->monthName} {$this->year} | Dicetak: {$this->printedAt} oleh {$this->printedBy}");
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);

        // Header Baris 5 & 6 (Struktur Persis Excel PT Mirasa)
        // Group Header
        $sheet->setCellValue('A5', 'HARI');
        $sheet->mergeCells('A5:A6');

        $sheet->setCellValue('B5', 'TANGGAL');
        $sheet->mergeCells('B5:B6');

        $sheet->setCellValue('C5', 'SHIFT & BATCH');
        $sheet->mergeCells('C5:C6');

        $sheet->setCellValue('D5', 'SINGKONG');
        $sheet->mergeCells('D5:E5');
        $sheet->setCellValue('D6', 'KG');
        $sheet->setCellValue('E6', 'RP');

        $sheet->setCellValue('F5', 'MINYAK GORENG');
        $sheet->mergeCells('F5:I5');
        $sheet->setCellValue('F6', 'SAWIT');
        $sheet->setCellValue('G6', 'KELAPA');
        $sheet->setCellValue('H6', 'RP');
        $sheet->setCellValue('I6', '%');

        $sheet->setCellValue('J5', 'GAS CNG');
        $sheet->mergeCells('J5:K5');
        $sheet->setCellValue('J6', 'MMBTU');
        $sheet->setCellValue('K6', 'RP');

        $sheet->setCellValue('L5', 'TENAGA KERJA');
        $sheet->mergeCells('L5:O5');
        $sheet->setCellValue('L6', 'LANGSUNG');
        $sheet->setCellValue('M6', 'TDK LANGSUNG');
        $sheet->setCellValue('N6', 'TRAINING');
        $sheet->setCellValue('O6', 'TOTAL RP');

        $sheet->setCellValue('P5', 'BUMBU');
        $sheet->mergeCells('P5:P6');

        $sheet->setCellValue('Q5', 'KARTON');
        $sheet->mergeCells('Q5:R5');
        $sheet->setCellValue('Q6', 'BARU');
        $sheet->setCellValue('R6', 'BEKAS');

        $sheet->setCellValue('S5', 'PLASTIK HD');
        $sheet->mergeCells('S5:S6');

        $sheet->setCellValue('T5', 'LAKBAN');
        $sheet->mergeCells('T5:U5');
        $sheet->setCellValue('T6', 'BESAR');
        $sheet->setCellValue('U6', 'KECIL');

        $sheet->setCellValue('V5', 'TALI RAFIA');
        $sheet->mergeCells('V5:V6');

        $sheet->setCellValue('W5', 'TOTAL OVERHEAD (FOH)');
        $sheet->mergeCells('W5:W6');

        $sheet->setCellValue('X5', 'TOTAL BIAYA (RP)');
        $sheet->mergeCells('X5:X6');

        $sheet->setCellValue('Y5', 'HASIL WIP (KG)');
        $sheet->mergeCells('Y5:AC5');
        $sheet->setCellValue('Y6', 'ASIN BARCO');
        $sheet->setCellValue('Z6', 'ASIN SAWIT');
        $sheet->setCellValue('AA6', 'NO SALT');
        $sheet->setCellValue('AB6', 'BALO');
        $sheet->setCellValue('AC6', 'BERKO');

        $sheet->setCellValue('AD5', 'TOTAL WIP (KG)');
        $sheet->mergeCells('AD5:AD6');

        $sheet->setCellValue('AE5', 'RENDEMEN %');
        $sheet->mergeCells('AE5:AE6');

        $sheet->setCellValue('AF5', 'HPP/KG (RP)');
        $sheet->mergeCells('AF5:AF6');

        // Style Headers
        $sheet->getStyle('A5:AF6')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A5:AF6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5:AF6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Header Background Colors
        $sheet->getStyle('A5:C6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle('D5:W6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFA3E635'); // Hijau Muda Biaya
        $sheet->getStyle('X5:X6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFACC15'); // Kuning Total Biaya
        $sheet->getStyle('Y5:AC6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF86EFAC'); // Hijau Output
        $sheet->getStyle('AD5:AD6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4ADE80');
        $sheet->getStyle('AE5:AE6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF22C55E');
        $sheet->getStyle('AF5:AF6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0284C7'); // Biru HPP
        $sheet->getStyle('AF5:AF6')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Render Data Rows
        $row = 7;
        foreach ($this->report['records'] as $idx => $r) {
            $sheet->setCellValue('A' . $row, $r->hari_nm ?? '-');
            $sheet->setCellValue('B' . $row, Carbon::parse($r->produksi_tgl)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, ($r->shift_cd ? 'Shift ' . $r->shift_cd . ' ' : '') . ($r->batch_wip_no ?? '-'));

            $sheet->setCellValue('D' . $row, (float) $r->singkong_qty);
            $sheet->setCellValue('E' . $row, (float) $r->singkong_nilai);

            $sheet->setCellValue('F' . $row, (float) $r->minyak_sawit_qty);
            $sheet->setCellValue('G' . $row, (float) $r->minyak_kelapa_qty);
            $sheet->setCellValue('H' . $row, (float) $r->minyak_nilai);
            $sheet->setCellValue('I' . $row, (float) $r->minyak_rasio_persen);

            $sheet->setCellValue('J' . $row, (float) $r->cng_mmbtu);
            $sheet->setCellValue('K' . $row, (float) $r->cng_nilai);

            $sheet->setCellValue('L' . $row, (int) $r->tk_langsung_org);
            $sheet->setCellValue('M' . $row, (int) $r->tk_tidak_langsung_org);
            $sheet->setCellValue('N' . $row, (int) $r->tk_training_org);
            $sheet->setCellValue('O' . $row, (float) $r->tk_total_nilai);

            $sheet->setCellValue('P' . $row, (float) $r->bumbu_nilai);
            $sheet->setCellValue('Q' . $row, (float) $r->karton_baru_nilai);
            $sheet->setCellValue('R' . $row, (float) $r->karton_bekas_nilai);
            $sheet->setCellValue('S' . $row, (float) $r->plastik_hd_nilai);
            $sheet->setCellValue('T' . $row, (float) $r->lakban_besar_nilai);
            $sheet->setCellValue('U' . $row, (float) $r->lakban_kecil_nilai);
            $sheet->setCellValue('V' . $row, (float) $r->tali_rafia_nilai);
            $sheet->setCellValue('W' . $row, (float) $r->total_overhead_nilai);

            $sheet->setCellValue('X' . $row, (float) $r->total_biaya_produksi);

            $sheet->setCellValue('Y' . $row, (float) $r->asin_barco_qty);
            $sheet->setCellValue('Z' . $row, (float) $r->asin_sawit_qty);
            $sheet->setCellValue('AA' . $row, (float) $r->no_salt_qty);
            $sheet->setCellValue('AB' . $row, (float) $r->balo_gelombang_qty);
            $sheet->setCellValue('AC' . $row, (float) $r->total_berko_qty);

            $sheet->setCellValue('AD' . $row, (float) $r->total_wip_qty);
            $sheet->setCellValue('AE' . $row, (float) $r->rendemen_persen);
            $sheet->setCellValue('AF' . $row, (float) $r->hpp_per_kg);

            // Numbers Formats
            $sheet->getStyle("D{$row}:D{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("E{$row}:E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("H{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$row}:I{$row}")->getNumberFormat()->setFormatCode('0.00"%"');
            $sheet->getStyle("J{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("K{$row}:K{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("O{$row}:X{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("Y{$row}:AD{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AE{$row}:AE{$row}")->getNumberFormat()->setFormatCode('0.00"%"');
            $sheet->getStyle("AF{$row}:AF{$row}")->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle("A{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}:AF{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

            $row++;
        }

        // Total Row
        $tot = $this->report['totals'];
        $sheet->setCellValue('A' . $row, 'TOTAL BULAN INI');
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue('D' . $row, $tot['singkong_qty']);
        $sheet->setCellValue('E' . $row, $tot['singkong_nilai']);
        $sheet->setCellValue('H' . $row, $tot['minyak_nilai']);
        $sheet->setCellValue('K' . $row, $tot['cng_nilai']);
        $sheet->setCellValue('O' . $row, $tot['tk_total_nilai']);
        $sheet->setCellValue('X' . $row, $tot['total_biaya_produksi']);
        $sheet->setCellValue('AD' . $row, $tot['total_wip_qty']);
        $sheet->setCellValue('AE' . $row, $tot['rendemen_persen']);
        $sheet->setCellValue('AF' . $row, $tot['hpp_per_kg']);

        $sheet->getStyle("A{$row}:AF{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:AF{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A{$row}:AF{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("O{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("X{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("AD{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("AE{$row}")->getNumberFormat()->setFormatCode('0.00"%"');
        $sheet->getStyle("AF{$row}")->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'Z') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setAutoSize(true);
        $sheet->getColumnDimension('AE')->setAutoSize(true);
        $sheet->getColumnDimension('AF')->setAutoSize(true);

        return $spreadsheet;
    }
}
