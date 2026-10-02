<?php

namespace App\Exports\Produksi;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapHppTemplate
{
    const COLOR_ORANGE_HEADER = 'FFF4B084'; // Header Hari & Tanggal
    const COLOR_GREEN_HEADER  = 'FF92D050'; // Header Total Biaya Produksi, Total WIP & seluruh sub-header hijau
    const COLOR_YELLOW_HEADER = 'FFFFC000'; // Header Total Biaya & Baris TOTAL / RATA-RATA
    const COLOR_GREY_HEADER   = 'FFF2F2F2'; // Header Harga Pokok Produksi
    const COLOR_YELLOW_CELL   = 'FFFFFF00'; // Background sel Total Biaya & Total WIP Kg
    const COLOR_RED_TEXT      = 'FFC00000'; // Pos FOH (Foto Copy, QC, Listrik, Mesin, Limbah)
    const COLOR_BLUE_TEXT     = 'FF002060'; // Persentase (Minyak %, Rendemen %)
    const COLOR_BORDER        = 'FF000000';

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->build();

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="Template_Rekap_HPP_Produksi_Harian.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function save(string $filePath): void
    {
        $spreadsheet = $this->build();
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    protected function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap HPP Mirasa');
        $sheet->setShowGridLines(true);

        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        // ═════════════════════════════════════════════════════════════════
        // HEADER ROW 1, 2, & 3 MENGIKUTI 100% SPREADSHEET EXCEL MIRASA
        // ═════════════════════════════════════════════════════════════════

        // ROW 1: MEGA HEADERS
        $sheet->setCellValue('A1', 'HARI');
        $sheet->mergeCells('A1:A3');
        $sheet->setCellValue('B1', 'TANGGAL');
        $sheet->mergeCells('B1:B3');
        $sheet->getStyle('A1:B3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_ORANGE_HEADER);

        // Mega Header Total Biaya Produksi / KG (C1:AD1)
        $sheet->setCellValue('C1', 'TOTAL BIAYA PRODUKSI / KG');
        $sheet->mergeCells('C1:AD1');

        // Total Biaya (AE1:AE3)
        $sheet->setCellValue('AE1', "TOTAL\nBIAYA");
        $sheet->mergeCells('AE1:AE3');
        $sheet->getStyle('AE1:AE3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_HEADER);

        // Total WIP (AF1:AG1)
        $sheet->setCellValue('AF1', 'TOTAL WIP');
        $sheet->mergeCells('AF1:AG1');

        // Harga Pokok Produksi (AH1:AH3)
        $sheet->setCellValue('AH1', "HARGA POKOK\nPRODUKSI");
        $sheet->mergeCells('AH1:AH3');
        $sheet->getStyle('AH1:AH3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREY_HEADER);

        // ROW 2: KATEGORI BIAYA & SUB-MEGA
        $sheet->setCellValue('C2', 'SINGKONG');
        $sheet->mergeCells('C2:D2');

        $sheet->setCellValue('E2', 'MINYAK GORENG');
        $sheet->mergeCells('E2:H2');

        $sheet->setCellValue('I2', 'CNG');
        $sheet->mergeCells('I2:J2');

        $sheet->setCellValue('K2', 'TENAGA KERJA');
        $sheet->mergeCells('K2:N2');

        $sheet->setCellValue('O2', "BUMBU\nPERENYAH");
        $sheet->mergeCells('O2:O3');

        $sheet->setCellValue('P2', 'KARTON FL');
        $sheet->mergeCells('P2:Q2');

        $sheet->setCellValue('R2', "PLASTIK HD\n90x100");
        $sheet->mergeCells('R2:R3');

        $sheet->setCellValue('S2', 'LAKBAN');
        $sheet->mergeCells('S2:T2');

        $sheet->setCellValue('U2', "TALI\nRAFIA");
        $sheet->mergeCells('U2:U3');

        $sheet->setCellValue('V2', "FOTO\nCOPY");
        $sheet->mergeCells('V2:V3');

        $sheet->setCellValue('W2', 'SARUNG TANGAN');
        $sheet->mergeCells('W2:X2');

        $sheet->setCellValue('Y2', "PENGAWASAN\nMUTU");
        $sheet->mergeCells('Y2:Y3');

        $sheet->setCellValue('Z2', "LISTRIK &\nAIR - TELP");
        $sheet->mergeCells('Z2:Z3');

        $sheet->setCellValue('AA2', "PEMLHR\nMESIN");
        $sheet->mergeCells('AA2:AA3');

        $sheet->setCellValue('AB2', "PENYS\nMESIN");
        $sheet->mergeCells('AB2:AB3');

        $sheet->setCellValue('AC2', 'B. PNGOLHN LIMBAH');
        $sheet->mergeCells('AC2:AD2');

        // WIP Sub-headers
        $sheet->setCellValue('AF2', "TOTAL\nKG");
        $sheet->mergeCells('AF2:AF3');

        $sheet->setCellValue('AG2', "RENDEMEN\n%");
        $sheet->mergeCells('AG2:AG3');

        // ROW 3: SUB-KOLOM SPESIFIK
        $sheet->setCellValue('C3', 'KG');
        $sheet->setCellValue('D3', 'Rp');

        $sheet->setCellValue('E3', 'SAWIT');
        $sheet->setCellValue('F3', 'KELAPA');
        $sheet->setCellValue('G3', 'Rp');
        $sheet->setCellValue('H3', '%');

        $sheet->setCellValue('I3', 'MMBTU');
        $sheet->setCellValue('J3', 'Rp');

        $sheet->setCellValue('K3', 'LANGSUNG');
        $sheet->setCellValue('L3', "TIDAK\nLANGSUNG");
        $sheet->setCellValue('M3', 'TRAINING');
        $sheet->setCellValue('N3', 'Rp');

        $sheet->setCellValue('P3', 'BARU');
        $sheet->setCellValue('Q3', 'BEKAS');

        $sheet->setCellValue('S3', 'BESAR');
        $sheet->setCellValue('T3', 'KECIL');

        $sheet->setCellValue('W3', 'PLASTIK');
        $sheet->setCellValue('X3', 'KAIN');

        $sheet->setCellValue('AC3', "LIMBAH\nPADAT");
        $sheet->setCellValue('AD3', "BAHAN\nKIMIA");

        // Fills untuk seluruh Header Hijau (C1:AD3 dan AF1:AG3)
        $sheet->getStyle('C1:AD3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_HEADER);
        $sheet->getStyle('AF1:AG3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_HEADER);

        // Teks Merah untuk Pos FOH
        $sheet->getStyle('V2:V3')->getFont()->getColor()->setARGB(self::COLOR_RED_TEXT);
        $sheet->getStyle('Y2:AB3')->getFont()->getColor()->setARGB(self::COLOR_RED_TEXT);
        $sheet->getStyle('AC2:AD3')->getFont()->getColor()->setARGB(self::COLOR_RED_TEXT);

        // Teks Biru untuk Persentase
        $sheet->getStyle('H3')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle('AG2:AG3')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        // Styling Umum Header Rows 1 to 3
        $sheet->getStyle('A1:AH3')->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle('A1:AH3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A1:AH3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);

        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(2)->setRowHeight(28);
        $sheet->getRowDimension(3)->setRowHeight(24);

        // ═════════════════════════════════════════════════════════════════
        // DATA ROWS: BARIS 4 S/D 34 (31 HARI KALENDER)
        // ═════════════════════════════════════════════════════════════════
        $dayNames = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
        $daysInMonth = 31;

        $row = 4;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateObj = Carbon::createFromDate($currentYear, $currentMonth, $d);
            $dayName = $dayNames[$dateObj->dayOfWeek] ?? 'Day';
            $isSunday = ($dateObj->dayOfWeek === 0);

            $sheet->setCellValue('A' . $row, $dayName);
            $sheet->setCellValue('B' . $row, $dateObj->format('d/m/y'));

            // Rumus-rumus baris
            $sheet->setCellValue('H' . $row, "=IF(C{$row}>0,(E{$row}+F{$row})/C{$row},0)");
            $sheet->setCellValue('AE' . $row, "=SUM(D{$row},G{$row},J{$row},N{$row}:AD{$row})");
            $sheet->setCellValue('AG' . $row, "=IF(C{$row}>0,AF{$row}/C{$row},0)");
            $sheet->setCellValue('AH' . $row, "=IF(AF{$row}>0,AE{$row}/AF{$row},0)");

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:AH{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("K{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Numbers Format
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.0');
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("N{$row}:AE{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AF{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AG{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AH{$row}")->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AG{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AE{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_CELL);
            $sheet->getStyle("AE{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AE{$row}")->getFont()->setBold(true);

            $sheet->getStyle("AF{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_CELL);
            $sheet->getStyle("AF{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AF{$row}")->getFont()->setBold(true);

            if ($isSunday) {
                $sheet->getStyle("A{$row}:AH{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCE4D6');
            }

            $sheet->getStyle("A{$row}:AH{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);
            $sheet->getStyle("A{$row}:AH{$row}")->getFont()->setSize(8);
            $sheet->getRowDimension($row)->setRowHeight(18);

            $row++;
        }

        // ═════════════════════════════════════════════════════════════════
        // BARIS TOTAL & RATA - RATA (Row 35 & 36)
        // ═════════════════════════════════════════════════════════════════
        $startDataRow = 4;
        $endDataRow = $row - 1;

        $rowTotal = $row;
        $sheet->setCellValue('A' . $rowTotal, 'T O T A L');
        $sheet->mergeCells("A{$rowTotal}:B{$rowTotal}");

        $sheet->setCellValue('C' . $rowTotal, "=SUM(C{$startDataRow}:C{$endDataRow})");
        $sheet->setCellValue('D' . $rowTotal, "=SUM(D{$startDataRow}:D{$endDataRow})");
        $sheet->setCellValue('E' . $rowTotal, "=SUM(E{$startDataRow}:E{$endDataRow})");
        $sheet->setCellValue('F' . $rowTotal, "=SUM(F{$startDataRow}:F{$endDataRow})");
        $sheet->setCellValue('G' . $rowTotal, "=SUM(G{$startDataRow}:G{$endDataRow})");
        $sheet->setCellValue('H' . $rowTotal, "=IF(C{$rowTotal}>0,(E{$rowTotal}+F{$rowTotal})/C{$rowTotal},0)");
        $sheet->setCellValue('I' . $rowTotal, "=SUM(I{$startDataRow}:I{$endDataRow})");
        $sheet->setCellValue('J' . $rowTotal, "=SUM(J{$startDataRow}:J{$endDataRow})");
        $sheet->setCellValue('K' . $rowTotal, "=SUM(K{$startDataRow}:K{$endDataRow})");
        $sheet->setCellValue('L' . $rowTotal, "=SUM(L{$startDataRow}:L{$endDataRow})");
        $sheet->setCellValue('M' . $rowTotal, "=SUM(M{$startDataRow}:M{$endDataRow})");
        $sheet->setCellValue('N' . $rowTotal, "=SUM(N{$startDataRow}:N{$endDataRow})");
        $sheet->setCellValue('O' . $rowTotal, "=SUM(O{$startDataRow}:O{$endDataRow})");
        $sheet->setCellValue('P' . $rowTotal, "=SUM(P{$startDataRow}:P{$endDataRow})");
        $sheet->setCellValue('Q' . $rowTotal, "=SUM(Q{$startDataRow}:Q{$endDataRow})");
        $sheet->setCellValue('R' . $rowTotal, "=SUM(R{$startDataRow}:R{$endDataRow})");
        $sheet->setCellValue('S' . $rowTotal, "=SUM(S{$startDataRow}:S{$endDataRow})");
        $sheet->setCellValue('T' . $rowTotal, "=SUM(T{$startDataRow}:T{$endDataRow})");
        $sheet->setCellValue('U' . $rowTotal, "=SUM(U{$startDataRow}:U{$endDataRow})");
        $sheet->setCellValue('V' . $rowTotal, "=SUM(V{$startDataRow}:V{$endDataRow})");
        $sheet->setCellValue('W' . $rowTotal, "=SUM(W{$startDataRow}:W{$endDataRow})");
        $sheet->setCellValue('X' . $rowTotal, "=SUM(X{$startDataRow}:X{$endDataRow})");
        $sheet->setCellValue('Y' . $rowTotal, "=SUM(Y{$startDataRow}:Y{$endDataRow})");
        $sheet->setCellValue('Z' . $rowTotal, "=SUM(Z{$startDataRow}:Z{$endDataRow})");
        $sheet->setCellValue('AA' . $rowTotal, "=SUM(AA{$startDataRow}:AA{$endDataRow})");
        $sheet->setCellValue('AB' . $rowTotal, "=SUM(AB{$startDataRow}:AB{$endDataRow})");
        $sheet->setCellValue('AC' . $rowTotal, "=SUM(AC{$startDataRow}:AC{$endDataRow})");
        $sheet->setCellValue('AD' . $rowTotal, "=SUM(AD{$startDataRow}:AD{$endDataRow})");
        $sheet->setCellValue('AE' . $rowTotal, "=SUM(AE{$startDataRow}:AE{$endDataRow})");
        $sheet->setCellValue('AF' . $rowTotal, "=SUM(AF{$startDataRow}:AF{$endDataRow})");
        $sheet->setCellValue('AG' . $rowTotal, "=IF(C{$rowTotal}>0,AF{$rowTotal}/C{$rowTotal},0)");
        $sheet->setCellValue('AH' . $rowTotal, "=IF(AF{$rowTotal}>0,AE{$rowTotal}/AF{$rowTotal},0)");

        $sheet->getStyle("A{$rowTotal}:AH{$rowTotal}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_YELLOW_HEADER]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$rowTotal}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$rowTotal}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AG{$rowTotal}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        $rowRata = $rowTotal + 1;
        $sheet->setCellValue('A' . $rowRata, 'R A T A - R A T A');
        $sheet->mergeCells("A{$rowRata}:B{$rowRata}");

        $sheet->setCellValue('C' . $rowRata, "=AVERAGEIF(C{$startDataRow}:C{$endDataRow},\">0\")");
        $sheet->setCellValue('D' . $rowRata, "=AVERAGEIF(D{$startDataRow}:D{$endDataRow},\">0\")");
        $sheet->setCellValue('E' . $rowRata, "=AVERAGEIF(E{$startDataRow}:E{$endDataRow},\">0\")");
        $sheet->setCellValue('F' . $rowRata, "=AVERAGEIF(F{$startDataRow}:F{$endDataRow},\">0\")");
        $sheet->setCellValue('G' . $rowRata, "=AVERAGEIF(G{$startDataRow}:G{$endDataRow},\">0\")");
        $sheet->setCellValue('H' . $rowRata, "=H{$rowTotal}");
        $sheet->setCellValue('I' . $rowRata, "=AVERAGEIF(I{$startDataRow}:I{$endDataRow},\">0\")");
        $sheet->setCellValue('J' . $rowRata, "=AVERAGEIF(J{$startDataRow}:J{$endDataRow},\">0\")");
        $sheet->setCellValue('K' . $rowRata, "=AVERAGEIF(K{$startDataRow}:K{$endDataRow},\">0\")");
        $sheet->setCellValue('L' . $rowRata, "=AVERAGEIF(L{$startDataRow}:L{$endDataRow},\">0\")");
        $sheet->setCellValue('M' . $rowRata, "=AVERAGEIF(M{$startDataRow}:M{$endDataRow},\">0\")");
        $sheet->setCellValue('N' . $rowRata, "=AVERAGEIF(N{$startDataRow}:N{$endDataRow},\">0\")");
        $sheet->setCellValue('O' . $rowRata, "=AVERAGEIF(O{$startDataRow}:O{$endDataRow},\">0\")");
        $sheet->setCellValue('P' . $rowRata, "=AVERAGEIF(P{$startDataRow}:P{$endDataRow},\">0\")");
        $sheet->setCellValue('Q' . $rowRata, "=AVERAGEIF(Q{$startDataRow}:Q{$endDataRow},\">0\")");
        $sheet->setCellValue('R' . $rowRata, "=AVERAGEIF(R{$startDataRow}:R{$endDataRow},\">0\")");
        $sheet->setCellValue('S' . $rowRata, "=AVERAGEIF(S{$startDataRow}:S{$endDataRow},\">0\")");
        $sheet->setCellValue('T' . $rowRata, "=AVERAGEIF(T{$startDataRow}:T{$endDataRow},\">0\")");
        $sheet->setCellValue('U' . $rowRata, "=AVERAGEIF(U{$startDataRow}:U{$endDataRow},\">0\")");
        $sheet->setCellValue('V' . $rowRata, "=AVERAGEIF(V{$startDataRow}:V{$endDataRow},\">0\")");
        $sheet->setCellValue('W' . $rowRata, "=AVERAGEIF(W{$startDataRow}:W{$endDataRow},\">0\")");
        $sheet->setCellValue('X' . $rowRata, "=AVERAGEIF(X{$startDataRow}:X{$endDataRow},\">0\")");
        $sheet->setCellValue('Y' . $rowRata, "=AVERAGEIF(Y{$startDataRow}:Y{$endDataRow},\">0\")");
        $sheet->setCellValue('Z' . $rowRata, "=AVERAGEIF(Z{$startDataRow}:Z{$endDataRow},\">0\")");
        $sheet->setCellValue('AA' . $rowRata, "=AVERAGEIF(AA{$startDataRow}:AA{$endDataRow},\">0\")");
        $sheet->setCellValue('AB' . $rowRata, "=AVERAGEIF(AB{$startDataRow}:AB{$endDataRow},\">0\")");
        $sheet->setCellValue('AC' . $rowRata, "=AVERAGEIF(AC{$startDataRow}:AC{$endDataRow},\">0\")");
        $sheet->setCellValue('AD' . $rowRata, "=AVERAGEIF(AD{$startDataRow}:AD{$endDataRow},\">0\")");
        $sheet->setCellValue('AE' . $rowRata, "=AVERAGEIF(AE{$startDataRow}:AE{$endDataRow},\">0\")");
        $sheet->setCellValue('AF' . $rowRata, "=AVERAGEIF(AF{$startDataRow}:AF{$endDataRow},\">0\")");
        $sheet->setCellValue('AG' . $rowRata, "=AG{$rowTotal}");
        $sheet->setCellValue('AH' . $rowRata, "=AH{$rowTotal}");

        $sheet->getStyle("A{$rowRata}:AH{$rowRata}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_YELLOW_HEADER]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$rowRata}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$rowRata}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AG{$rowRata}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        foreach ([$rowTotal, $rowRata] as $r) {
            $sheet->getStyle("C{$r}:G{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$r}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K{$r}:M{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("N{$r}:AE{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AF{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AG{$r}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AH{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getRowDimension($r)->setRowHeight(20);
        }

        foreach (range('A', 'Z') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setAutoSize(true);
        $sheet->getColumnDimension('AE')->setAutoSize(true);
        $sheet->getColumnDimension('AF')->setAutoSize(true);
        $sheet->getColumnDimension('AG')->setAutoSize(true);
        $sheet->getColumnDimension('AH')->setAutoSize(true);

        return $spreadsheet;
    }
}
