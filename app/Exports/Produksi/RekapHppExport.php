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
    const COLOR_GREEN_MEGA    = 'FFA9D08E'; // Header Total Biaya Produksi & Total WIP
    const COLOR_GREEN_SUB     = 'FFC6E0B4'; // Sub-header Biaya
    const COLOR_YELLOW_HEADER = 'FFFFC000'; // Header Total Biaya & Bottom Totals
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

        // Baris 1: Judul Laporan
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        $sheet->setCellValue('A2', "BUKU REKAPITULASI BIAYA PRODUKSI & RENDEMEN — BULAN " . strtoupper($this->monthName) . " {$this->year}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $sheet->setCellValue('A3', "Dicetak: {$this->printedAt} | User: {$this->printedBy}");
        $sheet->getStyle('A3')->getFont()->setSize(8)->setItalic(true);

        // ═════════════════════════════════════════════════════════════════
        // HEADER TINGKAT 1 (Row 5) & HEADER TINGKAT 2 (Row 6)
        // ═════════════════════════════════════════════════════════════════

        // Kolom A & B: HARI & TANGGAL (Warna Orange)
        $sheet->setCellValue('A5', 'HARI');
        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('B5', 'TANGGAL');
        $sheet->mergeCells('B5:B6');
        $sheet->getStyle('A5:B6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_ORANGE_HEADER);

        // MEGA HEADER: TOTAL BIAYA PRODUKSI / KG (Kolom C s/d AD)
        $sheet->setCellValue('C5', 'TOTAL BIAYA PRODUKSI / KG');
        $sheet->mergeCells('C5:AD5');
        $sheet->getStyle('C5:AD5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_MEGA);

        // Sub-Header Biaya (Row 6)
        // Singkong (C-D)
        $sheet->setCellValue('C6', 'SINGKONG (KG)');
        $sheet->setCellValue('D6', 'SINGKONG (RP)');

        // Minyak Goreng (E-H)
        $sheet->setCellValue('E6', 'MINYAK SAWIT');
        $sheet->setCellValue('F6', 'MINYAK KELAPA');
        $sheet->setCellValue('G6', 'MINYAK (RP)');
        $sheet->setCellValue('H6', 'MINYAK (%)');

        // Gas CNG (I-J)
        $sheet->setCellValue('I6', 'CNG (MMBTU)');
        $sheet->setCellValue('J6', 'CNG (RP)');

        // Tenaga Kerja (K-N)
        $sheet->setCellValue('K6', 'TK LANGSUNG');
        $sheet->setCellValue('L6', 'TK TDK LANGSUNG');
        $sheet->setCellValue('M6', 'TK TRAINING');
        $sheet->setCellValue('N6', 'TK TOTAL (RP)');

        // Bahan Pembantu & Pengemas (O-X)
        $sheet->setCellValue('O6', 'BUMBU PERENYAH');
        $sheet->setCellValue('P6', 'KARTON BARU');
        $sheet->setCellValue('Q6', 'KARTON BEKAS');
        $sheet->setCellValue('R6', 'PLASTIK HD 90x100');
        $sheet->setCellValue('S6', 'LAKBAN BESAR');
        $sheet->setCellValue('T6', 'LAKBAN KECIL');
        $sheet->setCellValue('U6', 'TALI RAFIA');
        $sheet->setCellValue('V6', 'FOTO COPY');
        $sheet->setCellValue('W6', 'SARUNG TGN PLSTK');
        $sheet->setCellValue('X6', 'SARUNG TGN KAIN');

        // FOH Pos Biaya Merah (Y-AD)
        $sheet->setCellValue('Y6', 'PEMERIKSAAN MUTU');
        $sheet->setCellValue('Z6', 'LISTRIK & AIR + TELP');
        $sheet->setCellValue('AA6', 'PEMLHR MESIN');
        $sheet->setCellValue('AB6', 'PENYS MESIN');
        $sheet->setCellValue('AC6', 'LIMBAH PADAT');
        $sheet->setCellValue('AD6', 'BAHAN KIMIA');

        // Set Warna Sub-header C6:AD6 (Hijau Muda)
        $sheet->getStyle('C6:AD6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_SUB);

        // Teks Merah untuk Pos FOH
        $sheet->getStyle('Y6:AD6')->getFont()->getColor()->setARGB(self::COLOR_RED_TEXT);
        // Teks Biru untuk Minyak %
        $sheet->getStyle('H6')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        // Kolom AE: TOTAL BIAYA (Warna Kuning)
        $sheet->setCellValue('AE5', "TOTAL\nBIAYA");
        $sheet->mergeCells('AE5:AE6');
        $sheet->getStyle('AE5:AE6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_HEADER);

        // MEGA HEADER: TOTAL WIP (AF-AG) (Warna Hijau Tua)
        $sheet->setCellValue('AF5', 'TOTAL WIP');
        $sheet->mergeCells('AF5:AG5');
        $sheet->getStyle('AF5:AG5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_MEGA);

        $sheet->setCellValue('AF6', 'TOTAL KG');
        $sheet->setCellValue('AG6', 'RENDEMEN %');
        $sheet->getStyle('AF6:AG6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_SUB);
        $sheet->getStyle('AG6')->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

        // Kolom AH: HARGA POKOK PRODUKSI (HPP/KG)
        $sheet->setCellValue('AH5', "HARGA POKOK\nPRODUKSI");
        $sheet->mergeCells('AH5:AH6');
        $sheet->getStyle('AH5:AH6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');

        // Style Umum Header Row 5 & 6
        $sheet->getStyle('A5:AH6')->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle('A5:AH6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getStyle('A5:AH6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);
        $sheet->getRowDimension(5)->setRowHeight(24);
        $sheet->getRowDimension(6)->setRowHeight(32);

        // ═════════════════════════════════════════════════════════════════
        // RENDER BARIS KALENDER 1 S/D 31 (Row 7 ke bawah)
        // ═════════════════════════════════════════════════════════════════
        $row = 7;
        $days = $this->report['days'] ?? [];

        foreach ($days as $d) {
            $isSunday = in_array(strtolower($d['hari_nm']), ['minggu', 'ahad']);
            $hasData = !empty($d['has_data']);

            $sheet->setCellValue('A' . $row, $d['hari_nm']);
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

                // Total WIP & Rendemen
                $sheet->setCellValue('AF' . $row, (float) $d['total_wip_qty']);
                $sheet->setCellValue('AG' . $row, ((float) $d['rendemen_persen']) / 100);

                // HPP / Kg
                $sheet->setCellValue('AH' . $row, (float) $d['hpp_per_kg']);
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
            }

            // Formatting
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
            $sheet->getStyle("N{$row}:AD{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AE{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("AF{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("AG{$row}")->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle("AH{$row}")->getNumberFormat()->setFormatCode('#,##0');

            // Warna Sel Tertentu Sesuai Screenshot Asli
            $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);
            $sheet->getStyle("AG{$row}")->getFont()->getColor()->setARGB(self::COLOR_BLUE_TEXT);

            if ($hasData && (float) $d['total_biaya_produksi'] > 0) {
                $sheet->getStyle("AE{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_CELL);
                $sheet->getStyle("AE{$row}")->getFont()->setBold(true);

                $sheet->getStyle("AF{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_CELL);
                $sheet->getStyle("AF{$row}")->getFont()->setBold(true);
            }

            if ($isSunday) {
                $sheet->getStyle("A{$row}:AH{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCE4D6');
            }

            $sheet->getStyle("A{$row}:AH{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);
            $sheet->getStyle("A{$row}:AH{$row}")->getFont()->setSize(8);
            $sheet->getRowDimension($row)->setRowHeight(18);

            $row++;
        }

        // ═════════════════════════════════════════════════════════════════
        // BARIS TOTAL & RATA-RATA (Sesuai 2 Baris Terakhir Excel Asli)
        // ═════════════════════════════════════════════════════════════════
        $tot = $this->report['totals'];
        $startDataRow = 7;
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

        // Format angka baris total & rata-rata
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

        return $spreadsheet;
    }
}
