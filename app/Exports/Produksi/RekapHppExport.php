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
        // HEADER ROW 1, 2, & 3 MENGIKUTI 100% SPREADSHEET EXCEL MIRASA (43 KOLOM)
        // ═════════════════════════════════════════════════════════════════

        // ROW 1: MEGA HEADERS
        $sheet->setCellValue('A1', 'HARI');
        $sheet->mergeCells('A1:A3');
        $sheet->setCellValue('B1', 'TANGGAL');
        $sheet->mergeCells('B1:B3');
        $sheet->getStyle('A1:B3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_ORANGE_HEADER);

        // Mega Header Total Biaya Produksi / KG (C1:AB1) - 26 Kolom
        $sheet->setCellValue('C1', 'TOTAL BIAYA PRODUKSI / KG');
        $sheet->mergeCells('C1:AB1');

        // Total Biaya (AC1:AC3)
        $sheet->setCellValue('AC1', "TOTAL\nBIAYA");
        $sheet->mergeCells('AC1:AC3');
        $sheet->getStyle('AC1:AC3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_HEADER);

        // Total WIP (AD1:AP1) - 13 Kolom Sesuai Excel Asli Mirasa
        $sheet->setCellValue('AD1', 'TOTAL WIP');
        $sheet->mergeCells('AD1:AP1');

        // Harga Pokok Produksi (AQ1:AQ3)
        $sheet->setCellValue('AQ1', "HARGA POKOK\nPRODUKSI");
        $sheet->mergeCells('AQ1:AQ3');
        $sheet->getStyle('AQ1:AQ3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREY_HEADER);

        // ROW 2: KATEGORI BIAYA & SUB-MEGA
        $sheet->setCellValue('C2', 'SINGKONG');
        $sheet->mergeCells('C2:D2');

        $sheet->setCellValue('E2', 'MINYAK GORENG');
        $sheet->mergeCells('E2:H2');

        $sheet->setCellValue('I2', 'CNG');
        $sheet->mergeCells('I2:J2');

        $sheet->setCellValue('K2', 'TENAGA KERJA');
        $sheet->mergeCells('K2:L2');

        $sheet->setCellValue('M2', "BUMBU\nPERENYAH");
        $sheet->mergeCells('M2:M3');

        $sheet->setCellValue('N2', 'KARTON FL');
        $sheet->mergeCells('N2:O2');

        $sheet->setCellValue('P2', "PLASTIK HD\n90x100");
        $sheet->mergeCells('P2:P3');

        $sheet->setCellValue('Q2', 'LAKBAN');
        $sheet->mergeCells('Q2:R2');

        $sheet->setCellValue('S2', "TALI\nRAFIA");
        $sheet->mergeCells('S2:S3');

        // FOH Pos 1: Fotocopy
        $sheet->setCellValue('T2', "FOTO\nCOPY");
        $sheet->mergeCells('T2:T3');

        // FOH Pos 2: Sarung Tangan
        $sheet->setCellValue('U2', 'SARUNG TANGAN');
        $sheet->mergeCells('U2:V2');

        // FOH Pos 3: Pengawasan Mutu QC
        $sheet->setCellValue('W2', "PENGAWASAN\nMUTU");
        $sheet->mergeCells('W2:W3');

        // FOH Pos 4: Listrik & Air - Telp
        $sheet->setCellValue('X2', "LISTRIK &\nAIR - TELP");
        $sheet->mergeCells('X2:X3');

        // FOH Pos 5: Pemeliharaan Mesin
        $sheet->setCellValue('Y2', "PEMLHR\nMESIN");
        $sheet->mergeCells('Y2:Y3');

        // FOH Pos 6: Penyusutan Mesin
        $sheet->setCellValue('Z2', "PENYS\nMESIN");
        $sheet->mergeCells('Z2:Z3');

        // FOH Pos 7 & 8: Limbah Padat & Kimia
        $sheet->setCellValue('AA2', 'B. PNGOLHN LIMBAH');
        $sheet->mergeCells('AA2:AB2');

        // WIP Sub-headers Sesuai Excel Asli Mirasa
        $sheet->setCellValue('AD2', 'IFL');
        
        $sheet->setCellValue('AE2', 'MANUAL');
        $sheet->mergeCells('AE2:AJ2');

        $sheet->setCellValue('AK2', 'BERKO + LENGKET');
        $sheet->mergeCells('AK2:AN2');

        $sheet->setCellValue('AO2', "TOTAL\nKG");
        $sheet->mergeCells('AO2:AO3');

        $sheet->setCellValue('AP2', "RENDEMEN\n%");
        $sheet->mergeCells('AP2:AP3');

        // ROW 3: SUB-KOLOM SPESIFIK
        $sheet->setCellValue('C3', 'KG');
        $sheet->setCellValue('D3', 'Rp');

        $sheet->setCellValue('E3', 'SAWIT');
        $sheet->setCellValue('F3', 'KELAPA');
        $sheet->setCellValue('G3', 'Rp');
        $sheet->setCellValue('H3', '%');

        $sheet->setCellValue('I3', 'MMBTU');
        $sheet->setCellValue('J3', 'Rp');

        $sheet->setCellValue('K3', 'ORANG');
        $sheet->setCellValue('L3', 'Rp');

        $sheet->setCellValue('N3', 'BARU');
        $sheet->setCellValue('O3', 'BEKAS');

        $sheet->setCellValue('Q3', 'BESAR');
        $sheet->setCellValue('R3', 'KECIL');

        $sheet->setCellValue('U3', 'PLASTIK');
        $sheet->setCellValue('V3', 'KAIN');

        $sheet->setCellValue('AA3', "LIMBAH\nPADAT");
        $sheet->setCellValue('AB3', "BAHAN\nKIMIA");

        // WIP Sub-kolom Row 3 Sesuai Excel Asli Mirasa
        $sheet->setCellValue('AD3', 'KG');
        $sheet->setCellValue('AE3', 'ASIN BARC');
        $sheet->setCellValue('AF3', 'ASIN SAWT');
        $sheet->setCellValue('AG3', 'NO SALT');
        $sheet->setCellValue('AH3', 'U/CAMP');
        $sheet->setCellValue('AI3', 'D/LM GELOMBA');
        $sheet->setCellValue('AJ3', 'BAL Q');
        $sheet->setCellValue('AK3', 'BERKO BIASA');
        $sheet->setCellValue('AL3', 'LENGKET (ME)');
        $sheet->setCellValue('AM3', 'TOTAL');
        $sheet->setCellValue('AN3', '%');

        // Fills untuk seluruh Header Hijau (C1:AB3 dan AD1:AP3)
        $sheet->getStyle('C1:AB3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_HEADER);
        $sheet->getStyle('AD1:AP3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_HEADER);

        // Teks Biru untuk Persentase
        $sheet->getStyle('H3')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle('AN3')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle('AP2:AP3')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        // Styling Umum Header Rows 1 to 3
        $sheet->getStyle('A1:AQ3')->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle('A1:AQ3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A1:AQ3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);

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

                // Tenaga Kerja (Pencatatan HPP Terpadu Sesuai Arahan Mentor)
                $sheet->setCellValue('K' . $row, (int) ($d['tk_jumlah_org'] ?? $d['tk_langsung_org']));
                $sheet->setCellValue('L' . $row, (float) $d['tk_total_nilai']);

                // Bahan Pembantu
                $sheet->setCellValue('M' . $row, (float) $d['bumbu_nilai']);
                $sheet->setCellValue('N' . $row, (float) $d['karton_baru_nilai']);
                $sheet->setCellValue('O' . $row, (float) $d['karton_bekas_nilai']);
                $sheet->setCellValue('P' . $row, (float) $d['plastik_hd_nilai']);
                $sheet->setCellValue('Q' . $row, (float) $d['lakban_besar_nilai']);
                $sheet->setCellValue('R' . $row, (float) $d['lakban_kecil_nilai']);
                $sheet->setCellValue('S' . $row, (float) $d['tali_rafia_nilai']);

                // FOH Lengkap Sesuai Lembar Asli Pabrik
                $sheet->setCellValue('T' . $row, (float) $d['fotocopy_nilai']);
                $sheet->setCellValue('U' . $row, (float) $d['sarung_tangan_plastik_nilai']);
                $sheet->setCellValue('V' . $row, (float) $d['sarung_tangan_kain_nilai']);
                $sheet->setCellValue('W' . $row, (float) $d['qc_pengawasan_nilai']);
                $sheet->setCellValue('X' . $row, (float) $d['listrik_air_telp_nilai']);
                $sheet->setCellValue('Y' . $row, (float) $d['pemeliharaan_mesin_nilai']);
                $sheet->setCellValue('Z' . $row, (float) $d['penyusutan_mesin_nilai']);
                $sheet->setCellValue('AA' . $row, (float) $d['limbah_padat_nilai']);
                $sheet->setCellValue('AB' . $row, (float) $d['limbah_kimia_nilai']);

                // Total Biaya
                $sheet->setCellValue('AC' . $row, (float) $d['total_biaya_produksi']);

                // WIP: IFL, MANUAL (6), BERKO (4), TOTAL KG, RENDEMEN %, HPP
                $sheet->setCellValue('AD' . $row, (float) $d['ifl_qty']);
                $sheet->setCellValue('AE' . $row, (float) $d['asin_barco_qty']);
                $sheet->setCellValue('AF' . $row, (float) $d['asin_sawit_qty']);
                $sheet->setCellValue('AG' . $row, (float) $d['no_salt_qty']);
                $sheet->setCellValue('AH' . $row, (float) $d['ucamp_qty']);
                $sheet->setCellValue('AI' . $row, (float) $d['balo_gelombang_qty']);
                $sheet->setCellValue('AJ' . $row, (float) $d['balqi_qty']);
                $sheet->setCellValue('AK' . $row, (float) $d['berko_qty']);
                $sheet->setCellValue('AL' . $row, (float) $d['berko_me_qty']);
                $sheet->setCellValue('AM' . $row, (float) $d['total_berko_qty']);
                $sheet->setCellValue('AN' . $row, ((float) $d['berko_persen']) / 100);
                $sheet->setCellValue('AO' . $row, (float) $d['total_wip_qty']);
                $sheet->setCellValue('AP' . $row, ((float) $d['rendemen_persen']) / 100);

                // HPP / Kg
                $sheet->setCellValue('AQ' . $row, (float) $d['hpp_per_kg']);
            } else {
                // Hari tanpa produksi
                foreach (range('C', 'Z') as $col) $sheet->setCellValue($col . $row, '-');
                foreach (['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ'] as $col) {
                    $sheet->setCellValue($col . $row, '-');
                }
            }

            // Formatting
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:AQ{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Numbers Format
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.0');
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("L{$row}:AC{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AD{$row}:AM{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AN{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AO{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AP{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AQ{$row}")->getNumberFormat()->setFormatCode('#,##0');

            // Warna Sel Tertentu Sesuai Format Asli Pabrik
            $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AN{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AP{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

            if ($hasData && (float) $d['total_biaya_produksi'] > 0) {
                $sheet->getStyle("AC{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_CELL);
                $sheet->getStyle("AC{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
                $sheet->getStyle("AC{$row}")->getFont()->setBold(true);

                $sheet->getStyle("AO{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_CELL);
                $sheet->getStyle("AO{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
                $sheet->getStyle("AO{$row}")->getFont()->setBold(true);
            }

            if ($isSunday) {
                $sheet->getStyle("A{$row}:AQ{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCE4D6');
            }

            $sheet->getStyle("A{$row}:AQ{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);
            $sheet->getStyle("A{$row}:AQ{$row}")->getFont()->setSize(8);
            $sheet->getRowDimension($row)->setRowHeight(18);

            $row++;
        }

        // ═════════════════════════════════════════════════════════════════
        // BARIS TOTAL & RATA-RATA (Sesuai 2 Baris Terakhir Excel Asli)
        // ═════════════════════════════════════════════════════════════════
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

        // FOH Totals
        $sheet->setCellValue('T' . $rowTotal, "=SUM(T{$startDataRow}:T{$endDataRow})");
        $sheet->setCellValue('U' . $rowTotal, "=SUM(U{$startDataRow}:U{$endDataRow})");
        $sheet->setCellValue('V' . $rowTotal, "=SUM(V{$startDataRow}:V{$endDataRow})");
        $sheet->setCellValue('W' . $rowTotal, "=SUM(W{$startDataRow}:W{$endDataRow})");
        $sheet->setCellValue('X' . $rowTotal, "=SUM(X{$startDataRow}:X{$endDataRow})");
        $sheet->setCellValue('Y' . $rowTotal, "=SUM(Y{$startDataRow}:Y{$endDataRow})");
        $sheet->setCellValue('Z' . $rowTotal, "=SUM(Z{$startDataRow}:Z{$endDataRow})");
        $sheet->setCellValue('AA' . $rowTotal, "=SUM(AA{$startDataRow}:AA{$endDataRow})");
        $sheet->setCellValue('AB' . $rowTotal, "=SUM(AB{$startDataRow}:AB{$endDataRow})");

        // Total Biaya
        $sheet->setCellValue('AC' . $rowTotal, "=SUM(AC{$startDataRow}:AC{$endDataRow})");

        // Total WIP
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
        $sheet->setCellValue('AN' . $rowTotal, "=IF(AO{$rowTotal}>0,AM{$rowTotal}/AO{$rowTotal},0)");
        $sheet->setCellValue('AO' . $rowTotal, "=SUM(AO{$startDataRow}:AO{$endDataRow})");
        $sheet->setCellValue('AP' . $rowTotal, "=IF(C{$rowTotal}>0,AO{$rowTotal}/C{$rowTotal},0)");
        $sheet->setCellValue('AQ' . $rowTotal, "=IF(AO{$rowTotal}>0,AC{$rowTotal}/AO{$rowTotal},0)");

        $sheet->getStyle("A{$rowTotal}:AQ{$rowTotal}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_YELLOW_HEADER]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$rowTotal}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$rowTotal}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AN{$rowTotal}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AP{$rowTotal}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

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
        $sheet->setCellValue('AN' . $rowRata, "=AN{$rowTotal}");
        $sheet->setCellValue('AO' . $rowRata, "=AVERAGEIF(AO{$startDataRow}:AO{$endDataRow},\">0\")");
        $sheet->setCellValue('AP' . $rowRata, "=AP{$rowTotal}");
        $sheet->setCellValue('AQ' . $rowRata, "=AQ{$rowTotal}");

        $sheet->getStyle("A{$rowRata}:AQ{$rowRata}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_YELLOW_HEADER]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$rowRata}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$rowRata}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AN{$rowRata}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
        $sheet->getStyle("AP{$rowRata}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        // Format angka baris total & rata-rata
        foreach ([$rowTotal, $rowRata] as $r) {
            $sheet->getStyle("C{$r}:G{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$r}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("L{$r}:AC{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AD{$r}:AM{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AN{$r}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AO{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AP{$r}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AQ{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getRowDimension($r)->setRowHeight(20);
        }

        // Auto width kolom A through AQ
        foreach (range('A', 'Z') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
        foreach (['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
