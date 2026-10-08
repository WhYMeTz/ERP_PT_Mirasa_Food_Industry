<?php

namespace App\Exports\Produksi;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
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

    // Warna Resmi Spreadsheet Mirasa
    const COLOR_ORANGE_HEADER = 'FFF4B084'; // Header Hari & Tanggal
    const COLOR_GREEN_HEADER  = 'FF92D050'; // Header Total Biaya Produksi, Total WIP & seluruh sub-header hijau
    const COLOR_YELLOW_HEADER = 'FFFFC000'; // Header Total Biaya & Bottom Totals
    const COLOR_GREY_HEADER   = 'FFF2F2F2'; // Header Harga Pokok Produksi
    const COLOR_YELLOW_CELL   = 'FFFFFF00'; // Background angka Total Biaya & Total WIP Kg
    const COLOR_RED_TEXT      = 'FFC00000'; // Pos Biaya FOH (QC, Listrik, Mesin, Limbah)
    const COLOR_BLUE_TEXT     = 'FF002060'; // Persentase (Minyak % dan Rendemen %)
    const COLOR_BORDER        = 'FF000000'; // Border hitam tipis presisi akuntansi

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
        $sheet->setTitle(substr($this->monthName, 0, 3) . ' ' . $this->year);
        $sheet->setShowGridLines(true);

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

        // Total WIP (AF1:AR1) - 13 Kolom Sesuai Excel Asli Mirasa
        $sheet->setCellValue('AF1', 'TOTAL WIP');
        $sheet->mergeCells('AF1:AR1');

        // Harga Pokok Produksi (AS1:AS3)
        $sheet->setCellValue('AS1', "HARGA POKOK\nPRODUKSI");
        $sheet->mergeCells('AS1:AS3');
        $sheet->getStyle('AS1:AS3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREY_HEADER);

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

        // WIP Sub-headers Sesuai Excel
        $sheet->setCellValue('AF2', 'IFL');
        
        $sheet->setCellValue('AG2', 'MANUAL');
        $sheet->mergeCells('AG2:AL2');

        $sheet->setCellValue('AM2', 'BERKO + BERKO ME');
        $sheet->mergeCells('AM2:AP2');

        $sheet->setCellValue('AQ2', "TOTAL\nKG");
        $sheet->mergeCells('AQ2:AQ3');

        $sheet->setCellValue('AR2', "RENDEMEN\n%");
        $sheet->mergeCells('AR2:AR3');

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

        // WIP Sub-kolom Row 3 Sesuai Excel Asli Mirasa
        $sheet->setCellValue('AF3', 'KG');
        $sheet->setCellValue('AG3', 'ASIN BARC');
        $sheet->setCellValue('AH3', 'ASIN SAWT');
        $sheet->setCellValue('AI3', 'NO SALT');
        $sheet->setCellValue('AJ3', 'U/CAMP');
        $sheet->setCellValue('AK3', 'D/LM GELOMBA');
        $sheet->setCellValue('AL3', 'BAL Q');
        $sheet->setCellValue('AM3', 'BERKO');
        $sheet->setCellValue('AN3', 'BERKO ME');
        $sheet->setCellValue('AO3', 'TOTAL');
        $sheet->setCellValue('AP3', '%');

        // Fills untuk seluruh Header Hijau (C1:AD3 dan AF1:AR3)
        $sheet->getStyle('C1:AD3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_HEADER);
        $sheet->getStyle('AF1:AR3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_HEADER);

        // Teks Merah untuk Pos FOH
        $sheet->getStyle('V2:V3')->getFont()->getColor()->setARGB(self::COLOR_RED_TEXT);
        $sheet->getStyle('Y2:AB3')->getFont()->getColor()->setARGB(self::COLOR_RED_TEXT);
        $sheet->getStyle('AC2:AD3')->getFont()->getColor()->setARGB(self::COLOR_RED_TEXT);

        // Teks Biru untuk Persentase
        $sheet->getStyle('H3')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle('AP3')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle('AR2:AR3')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        // Styling Umum Header Rows 1 to 3
        $sheet->getStyle('A1:AS3')->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle('A1:AS3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A1:AS3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);

        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(2)->setRowHeight(28);
        $sheet->getRowDimension(3)->setRowHeight(24);

        // ═════════════════════════════════════════════════════════════════
        // RENDER BARIS KALENDER 1 S/D 31 (Row 4 ke bawah)
        // ═════════════════════════════════════════════════════════════════
        $row = 4;
        $days = $this->report['days'] ?? [];

        foreach ($days as $d) {
            $isSunday = in_array(strtolower($d['hari_nm']), ['minggu', 'ahad']);
            $hasData = !empty($d['has_data']);

            $sheet->setCellValue('A' . $row, Carbon::parse($d['date'])->format('l'));
            $sheet->setCellValue('B' . $row, Carbon::parse($d['date'])->format('d/m/y'));

            if ($hasData) {
                // Singkong
                $sheet->setCellValue('C' . $row, (float) $d['singkong_qty']);
                $sheet->setCellValue('D' . $row, (float) $d['singkong_nilai']);

                // Minyak
                $sheet->setCellValue('E' . $row, (float) $d['minyak_sawit_qty']);
                $sheet->setCellValue('F' . $row, (float) $d['minyak_kelapa_qty']);
                $sheet->setCellValue('G' . $row, (float) $d['minyak_nilai']);
                $sheet->setCellValue('H' . $row, ((float) $d['minyak_rasio_persen']) / 100);

                // CNG
                $sheet->setCellValue('I' . $row, (float) $d['cng_mmbtu']);
                $sheet->setCellValue('J' . $row, (float) $d['cng_nilai']);

                // Tenaga Kerja
                $sheet->setCellValue('K' . $row, (int) $d['tk_langsung_org']);
                $sheet->setCellValue('L' . $row, (int) $d['tk_tidak_langsung_org']);
                $sheet->setCellValue('M' . $row, (int) $d['tk_training_org']);
                $sheet->setCellValue('N' . $row, (float) $d['tk_total_nilai']);

                // Bahan Pembantu
                $sheet->setCellValue('O' . $row, (float) $d['bumbu_nilai']);
                $sheet->setCellValue('P' . $row, (float) $d['karton_baru_nilai']);
                $sheet->setCellValue('Q' . $row, (float) $d['karton_bekas_nilai']);
                $sheet->setCellValue('R' . $row, (float) $d['plastik_hd_nilai']);
                $sheet->setCellValue('S' . $row, (float) $d['lakban_besar_nilai']);
                $sheet->setCellValue('T' . $row, (float) $d['lakban_kecil_nilai']);
                $sheet->setCellValue('U' . $row, (float) $d['tali_rafia_nilai']);
                $sheet->setCellValue('V' . $row, (float) $d['fotocopy_nilai']);
                $sheet->setCellValue('W' . $row, (float) $d['sarung_tangan_plastik_nilai']);
                $sheet->setCellValue('X' . $row, (float) $d['sarung_tangan_kain_nilai']);

                // FOH
                $sheet->setCellValue('Y' . $row, (float) $d['qc_pengawasan_nilai']);
                $sheet->setCellValue('Z' . $row, (float) $d['listrik_air_telp_nilai']);
                $sheet->setCellValue('AA' . $row, (float) $d['pemeliharaan_mesin_nilai']);
                $sheet->setCellValue('AB' . $row, (float) $d['penyusutan_mesin_nilai']);
                $sheet->setCellValue('AC' . $row, (float) $d['limbah_padat_nilai']);
                $sheet->setCellValue('AD' . $row, (float) $d['limbah_kimia_nilai']);

                // Total Biaya
                $sheet->setCellValue('AE' . $row, (float) $d['total_biaya_produksi']);

                // WIP: IFL, MANUAL (6), BERKO (4), TOTAL KG, RENDEMEN %, HPP
                $sheet->setCellValue('AF' . $row, (float) $d['ifl_qty']);
                $sheet->setCellValue('AG' . $row, (float) $d['asin_barco_qty']);
                $sheet->setCellValue('AH' . $row, (float) $d['asin_sawit_qty']);
                $sheet->setCellValue('AI' . $row, (float) $d['no_salt_qty']);
                $sheet->setCellValue('AJ' . $row, (float) $d['ucamp_qty']);
                $sheet->setCellValue('AK' . $row, (float) $d['balo_gelombang_qty']);
                $sheet->setCellValue('AL' . $row, (float) $d['balqi_qty']);
                $sheet->setCellValue('AM' . $row, (float) $d['berko_qty']);
                $sheet->setCellValue('AN' . $row, (float) $d['berko_me_qty']);
                $sheet->setCellValue('AO' . $row, (float) $d['total_berko_qty']);
                $sheet->setCellValue('AP' . $row, ((float) $d['berko_persen']) / 100);
                $sheet->setCellValue('AQ' . $row, (float) $d['total_wip_qty']);
                $sheet->setCellValue('AR' . $row, ((float) $d['rendemen_persen']) / 100);

                // HPP / Kg
                $sheet->setCellValue('AS' . $row, (float) $d['hpp_per_kg']);
            } else {
                // Hari tanpa produksi
                foreach (range('C', 'Z') as $col) $sheet->setCellValue($col . $row, '-');
                $sheet->setCellValue('AA' . $row, '-');
                $sheet->setCellValue('AB' . $row, '-');
                $sheet->setCellValue('AC' . $row, '-');
                $sheet->setCellValue('AD' . $row, '-');
                $sheet->setCellValue('AE' . $row, '-');
                $sheet->setCellValue('AF' . $row, '-');
                $sheet->setCellValue('AG' . $row, '-');
                $sheet->setCellValue('AH' . $row, '-');
                $sheet->setCellValue('AI' . $row, '-');
                $sheet->setCellValue('AJ' . $row, '-');
                $sheet->setCellValue('AK' . $row, '-');
                $sheet->setCellValue('AL' . $row, '-');
                $sheet->setCellValue('AM' . $row, '-');
                $sheet->setCellValue('AN' . $row, '-');
                $sheet->setCellValue('AO' . $row, '-');
                $sheet->setCellValue('AP' . $row, '-');
                $sheet->setCellValue('AQ' . $row, '-');
                $sheet->setCellValue('AR' . $row, '-');
                $sheet->setCellValue('AS' . $row, '-');
            }

            // Formatting
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:AS{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("K{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Numbers Format
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.0');
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("N{$row}:AD{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AE{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AF{$row}:AO{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AP{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AQ{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AR{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AS{$row}")->getNumberFormat()->setFormatCode('#,##0');

            // Warna Sel Tertentu Sesuai Screenshot Asli
            $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AP{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AR{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

            if ($hasData && (float) $d['total_biaya_produksi'] > 0) {
                $sheet->getStyle("AE{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_CELL);
                $sheet->getStyle("AE{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
                $sheet->getStyle("AE{$row}")->getFont()->setBold(true);

                $sheet->getStyle("AQ{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_CELL);
                $sheet->getStyle("AQ{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
                $sheet->getStyle("AQ{$row}")->getFont()->setBold(true);
            }

            if ($isSunday) {
                $sheet->getStyle("A{$row}:AS{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCE4D6');
            }

            $sheet->getStyle("A{$row}:AS{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);
            $sheet->getStyle("A{$row}:AQ{$row}")->getFont()->setSize(8);
            $sheet->getRowDimension($row)->setRowHeight(18);

            $row++;
        }

        // ═════════════════════════════════════════════════════════════════
        // BARIS TOTAL & RATA-RATA (Sesuai 2 Baris Terakhir Excel Asli)
        // ═════════════════════════════════════════════════════════════════
        $tot = $this->report['totals'];
        $startDataRow = 4;
        $endDataRow = $row - 1;

        // 1. BARIS TOTAL
        $rowTotal = $row;
        $sheet->setCellValue('A' . $rowTotal, 'T O T A L');
        $sheet->mergeCells("A{$rowTotal}:B{$rowTotal}");

        // Formula SUM kolom-kolom numerik
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
        $sheet->setCellValue('AG' . $rowTotal, "=SUM(AG{$startDataRow}:AG{$endDataRow})");
        $sheet->setCellValue('AH' . $rowTotal, "=SUM(AH{$startDataRow}:AH{$endDataRow})");
        $sheet->setCellValue('AI' . $rowTotal, "=SUM(AI{$startDataRow}:AI{$endDataRow})");
        $sheet->setCellValue('AJ' . $rowTotal, "=SUM(AJ{$startDataRow}:AJ{$endDataRow})");
        $sheet->setCellValue('AK' . $rowTotal, "=SUM(AK{$startDataRow}:AK{$endDataRow})");
        $sheet->setCellValue('AL' . $rowTotal, "=SUM(AL{$startDataRow}:AL{$endDataRow})");
        $sheet->setCellValue('AM' . $rowTotal, "=SUM(AM{$startDataRow}:AM{$endDataRow})");
        $sheet->setCellValue('AN' . $rowTotal, "=SUM(AN{$startDataRow}:AN{$endDataRow})");
        $sheet->setCellValue('AO' . $rowTotal, "=SUM(AO{$startDataRow}:AO{$endDataRow})");
        $sheet->setCellValue('AP' . $rowTotal, "=IF(AQ{$rowTotal}>0,AO{$rowTotal}/AQ{$rowTotal},0)");
        $sheet->setCellValue('AQ' . $rowTotal, "=SUM(AQ{$startDataRow}:AQ{$endDataRow})");
        $sheet->setCellValue('AR' . $rowTotal, "=IF(C{$rowTotal}>0,AQ{$rowTotal}/C{$rowTotal},0)");
        $sheet->setCellValue('AS' . $rowTotal, "=IF(AQ{$rowTotal}>0,AE{$rowTotal}/AQ{$rowTotal},0)");

        $sheet->getStyle("A{$rowTotal}:AS{$rowTotal}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_YELLOW_HEADER]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$rowTotal}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$rowTotal}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AP{$rowTotal}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AR{$rowTotal}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        // 2. BARIS RATA - RATA
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
        $sheet->setCellValue('AG' . $rowRata, "=AVERAGEIF(AG{$startDataRow}:AG{$endDataRow},\">0\")");
        $sheet->setCellValue('AH' . $rowRata, "=AVERAGEIF(AH{$startDataRow}:AH{$endDataRow},\">0\")");
        $sheet->setCellValue('AI' . $rowRata, "=AVERAGEIF(AI{$startDataRow}:AI{$endDataRow},\">0\")");
        $sheet->setCellValue('AJ' . $rowRata, "=AVERAGEIF(AJ{$startDataRow}:AJ{$endDataRow},\">0\")");
        $sheet->setCellValue('AK' . $rowRata, "=AVERAGEIF(AK{$startDataRow}:AK{$endDataRow},\">0\")");
        $sheet->setCellValue('AL' . $rowRata, "=AVERAGEIF(AL{$startDataRow}:AL{$endDataRow},\">0\")");
        $sheet->setCellValue('AM' . $rowRata, "=AVERAGEIF(AM{$startDataRow}:AM{$endDataRow},\">0\")");
        $sheet->setCellValue('AN' . $rowRata, "=AVERAGEIF(AN{$startDataRow}:AN{$endDataRow},\">0\")");
        $sheet->setCellValue('AO' . $rowRata, "=AVERAGEIF(AO{$startDataRow}:AO{$endDataRow},\">0\")");
        $sheet->setCellValue('AP' . $rowRata, "=AP{$rowTotal}");
        $sheet->setCellValue('AQ' . $rowRata, "=AVERAGEIF(AQ{$startDataRow}:AQ{$endDataRow},\">0\")");
        $sheet->setCellValue('AR' . $rowRata, "=AR{$rowTotal}");
        $sheet->setCellValue('AS' . $rowRata, "=AS{$rowTotal}");

        $sheet->getStyle("A{$rowRata}:AS{$rowRata}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_YELLOW_HEADER]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$rowRata}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$rowRata}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AP{$rowRata}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AR{$rowRata}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        // Format angka baris total & rata-rata
        foreach ([$rowTotal, $rowRata] as $r) {
            $sheet->getStyle("C{$r}:G{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$r}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K{$r}:M{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("N{$r}:AE{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AF{$r}:AO{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AP{$r}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AQ{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AR{$r}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AS{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getRowDimension($r)->setRowHeight(20);
        }

        // Auto width kolom
        foreach (range('A', 'Z') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setAutoSize(true);
        $sheet->getColumnDimension('AE')->setAutoSize(true);
        $sheet->getColumnDimension('AF')->setAutoSize(true);
        $sheet->getColumnDimension('AG')->setAutoSize(true);
        $sheet->getColumnDimension('AH')->setAutoSize(true);
        $sheet->getColumnDimension('AI')->setAutoSize(true);
        $sheet->getColumnDimension('AJ')->setAutoSize(true);
        $sheet->getColumnDimension('AK')->setAutoSize(true);
        $sheet->getColumnDimension('AL')->setAutoSize(true);
        $sheet->getColumnDimension('AM')->setAutoSize(true);
        $sheet->getColumnDimension('AN')->setAutoSize(true);
        $sheet->getColumnDimension('AO')->setAutoSize(true);
        $sheet->getColumnDimension('AP')->setAutoSize(true);
        $sheet->getColumnDimension('AQ')->setAutoSize(true);
        $sheet->getColumnDimension('AR')->setAutoSize(true);
        $sheet->getColumnDimension('AS')->setAutoSize(true);

        return $spreadsheet;
    }
}
