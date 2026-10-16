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
    protected array  $report;
    protected int    $year;
    protected int    $month;
    protected string $monthName;
    protected string $printedBy;
    protected string $printedAt;

    // Palette Warna Identik Format Excel Standar Mirasa
    const COLOR_YELLOW_MAIN = 'FFFFE600'; // Kuning Terang Header & Total
    const COLOR_YELLOW_SOFT = 'FFFFF9C3'; // Kuning Lembut (Bahan Keripik)
    const COLOR_AMBER_SOFT  = 'FFFFF3C7'; // Amber Lembut (Karton)
    const COLOR_ORANGE_SOFT = 'FFFFEDD5'; // Oranye Lembut (Bumbu Balado)
    const COLOR_MINT_SOFT   = 'FFECFDF5'; // Hijau Mint Lembut (Biaya Produksi / Roll)
    const COLOR_GOLD_TOTAL  = 'FFFFF08A'; // Kuning Emas (Total Biaya)
    const COLOR_PURPLE_SOFT = 'FFF3E8FF'; // Ungu Lembut (Hasil Produksi)
    const COLOR_GREEN_HPP   = 'FFD1FAE5'; // Hijau Emerald (HPP)
    const COLOR_GRAY_HEADER = 'FFF1F5F9'; // Abu-abu Header Hari & Tanggal
    const COLOR_BORDER      = 'FFCBD5E1'; // Slate 300 Border
    const COLOR_BORDER_DARK = 'FF94A3B8'; // Slate 400 Border

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

        $dh = $this->report['daftar_harga'] ?? [];

        // ══════════════════════════════════════════════════════════════════
        // 1. JUDUL LAPORAN UTAMA
        // ══════════════════════════════════════════════════════════════════
        $sheet->setCellValue('A1', 'HARGA POKOK PRODUKSI PING-PING & MAKSI');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF0F172A');

        $sheet->setCellValue('A2', 'BULAN ' . strtoupper($this->monthName) . ' ' . $this->year);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FF334155');

        // ══════════════════════════════════════════════════════════════════
        // 2. PANEL ACUAN DAFTAR HARGA (ROWS 4 - 8)
        // ══════════════════════════════════════════════════════════════════
        // Box "DAFTAR HARGA" (Col A & B)
        $sheet->setCellValue('A4', "DAFTAR\nHARGA");
        $sheet->mergeCells('A4:B5');
        $sheet->getStyle('A4:B5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_YELLOW_MAIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER_DARK]]],
        ]);

        // Sub-Tabel Parameter 1: Harga WIP / Curah
        $sheet->setCellValue('C4', 'Harga Kemasan/Kg');
        $sheet->setCellValue('C5', 10000);
        $sheet->setCellValue('C6', 'Harga WIP');
        $sheet->setCellValue('C7', (float) ($dh['harga_wip'] ?? 29500));
        $sheet->setCellValue('D6', $dh['tgl_wip'] ?? '20/01/2026');

        // Sub-Tabel Parameter 2: Karton / Dus
        $sheet->setCellValue('D4', 'Harga karton/dus');
        $sheet->mergeCells('D4:E4');
        $sheet->setCellValue('D5', (float) ($dh['karton_kk'] ?? 1358.3));
        $sheet->setCellValue('E5', (float) ($dh['karton_mk'] ?? 1350.0));

        // Sub-Tabel Parameter 3: Bumbu Balado
        $sheet->setCellValue('G4', 'Harga bumbu/kg');
        $sheet->mergeCells('G4:H4');
        $sheet->setCellValue('G5', (float) ($dh['bumbu_balado'] ?? 39450));
        $sheet->setCellValue('G6', 'pemakaian bumbu setiap keripik 5 % berko');
        $sheet->mergeCells('G6:I6');

        // Sub-Tabel Parameter 4: Harga Roll Kemasan
        $sheet->setCellValue('J4', 'Harga ROLL');
        $sheet->mergeCells('J4:K4');
        $sheet->setCellValue('J5', 'per roll XX');
        $sheet->setCellValue('K5', (float) ($dh['roll_kk_unit'] ?? 364839));
        $sheet->setCellValue('J7', 'per roll MX');
        $sheet->setCellValue('K7', (float) ($dh['roll_mk_unit'] ?? 384471));
        $sheet->setCellValue('J8', 'out 3% per roll XX');
        $sheet->setCellValue('K8', (float) ($dh['roll_kk_out3'] ?? 353894));
        $sheet->setCellValue('J9', 'out 3% per roll MX');
        $sheet->setCellValue('K9', (float) ($dh['roll_mk_out3'] ?? 372937));

        // Sub-Tabel Parameter 5: Lakban
        $sheet->setCellValue('M4', 'Harga LAKBAN');
        $sheet->mergeCells('M4:N4');
        $sheet->setCellValue('M5', 'per roll');
        $sheet->setCellValue('N5', (float) ($dh['lakban_roll'] ?? 45000));
        $sheet->setCellValue('M7', 'per gr');
        $sheet->setCellValue('N7', (float) ($dh['lakban_per_gr'] ?? 106.63));

        // Sub-Tabel Parameter 6: Kertas Expired
        $sheet->setCellValue('P4', 'Harga KERTAS EXP');
        $sheet->setCellValue('P5', 'per lembar');
        $sheet->setCellValue('Q5', (float) ($dh['kertas_exp_lbr'] ?? 100));

        // Sub-Tabel Parameter 7: Roll XX + MX
        $sheet->setCellValue('S4', 'ROLL XX + MX');
        $sheet->setCellValue('S5', 'per kg');
        $sheet->setCellValue('T5', (float) ($dh['roll_per_kg'] ?? 43482));
        $sheet->setCellValue('S6', 'per gr');
        $sheet->setCellValue('T6', (float) ($dh['kerusakan_gr'] ?? 43.48));

        // Border & styling parameter acuan
        $sheet->getStyle('C4:U9')->applyFromArray([
            'font' => ['size' => 8],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('C4:U4')->getFont()->setBold(true);
        $sheet->getStyle('C5')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('C7')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D5:E5')->getNumberFormat()->setFormatCode('#,##0.0');
        $sheet->getStyle('G5')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K5:K9')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('N5:N7')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('Q5')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('T5:T6')->getNumberFormat()->setFormatCode('#,##0.00');

        // Red accents as in Excel
        $sheet->getStyle('C5')->getFont()->getColor()->setARGB('FFDC2626');
        $sheet->getStyle('C7')->getFont()->getColor()->setARGB('FFDC2626');
        $sheet->getStyle('D5:E5')->getFont()->getColor()->setARGB('FFDC2626');
        $sheet->getStyle('G5')->getFont()->getColor()->setARGB('FFDC2626');
        $sheet->getStyle('N5:N7')->getFont()->getColor()->setARGB('FFDC2626');
        $sheet->getStyle('T5:T6')->getFont()->getColor()->setARGB('FFDC2626');

        // ══════════════════════════════════════════════════════════════════
        // 3. MULTI-LEVEL TABLE HEADER (ROWS 11 - 13)
        // ══════════════════════════════════════════════════════════════════
        // LEVEL 1 (Row 11)
        $sheet->setCellValue('A11', "HARI");
        $sheet->mergeCells('A11:A13');

        $sheet->setCellValue('B11', "TANGGAL");
        $sheet->mergeCells('B11:B13');

        $sheet->setCellValue('C11', 'BAHAN KERIPIK');
        $sheet->mergeCells('C11:E12');

        $sheet->setCellValue('F11', 'KARTON');
        $sheet->mergeCells('F11:H12');

        $sheet->setCellValue('I11', 'BUMBU BALADO');
        $sheet->mergeCells('I11:J12');

        $sheet->setCellValue('K11', 'TOTAL BIAYA PRODUKSI/ KG');
        $sheet->mergeCells('K11:W11');

        $sheet->setCellValue('X11', "TOTAL\nBIAYA");
        $sheet->mergeCells('X11:X13');

        $sheet->setCellValue('Y11', 'HASIL PRODUKSI');
        $sheet->mergeCells('Y11:AB12');

        $sheet->setCellValue('AC11', "HARGA\nPOKOK\nPRODUKSI");
        $sheet->mergeCells('AC11:AC13');

        // LEVEL 2 (Row 12 - Sub groups di bawah TOTAL BIAYA PRODUKSI)
        $sheet->setCellValue('K12', 'PENGGUNAAN ROLL KG');
        $sheet->mergeCells('K12:M12');

        $sheet->setCellValue('N12', 'LAKBAN GR');
        $sheet->mergeCells('N12:Q12');

        $sheet->setCellValue('R12', 'KERTAS EXPIRED');
        $sheet->mergeCells('R12:S12');

        $sheet->setCellValue('T12', 'KERUSAKAN PLASTIK');
        $sheet->mergeCells('T12:U12');

        $sheet->setCellValue('V12', 'KERUSAKAN KARTON');
        $sheet->mergeCells('V12:W12');

        // LEVEL 3 (Row 13 - Rincian Sub-Kolom)
        $subHeaders = [
            'C13'  => "BERKO (KG)",
            'D13'  => "WIP (KG)",
            'E13'  => "Rupiah",
            'F13'  => "XX",
            'G13'  => "MX",
            'H13'  => "Rupiah",
            'I13'  => "BALADO",
            'J13'  => "Rupiah",
            'K13'  => "XX",
            'L13'  => "MX",
            'M13'  => "Rupiah",
            'N13'  => "ATAS",
            'O13'  => "BAWAH",
            'P13'  => "JUMLAH",
            'Q13'  => "Rupiah",
            'R13'  => "BUNGKUS",
            'S13'  => "Rupiah",
            'T13'  => "JUMLAH",
            'U13'  => "Rupiah",
            'V13'  => "JUMLAH",
            'W13'  => "Rupiah",
            'Y13'  => "PING-PING",
            'Z13'  => "MAKSI",
            'AA13' => "JUMLAH",
            'AB13' => "GRAMASI",
        ];
        foreach ($subHeaders as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Dimensions & Base Styling
        $sheet->getRowDimension(11)->setRowHeight(24);
        $sheet->getRowDimension(12)->setRowHeight(22);
        $sheet->getRowDimension(13)->setRowHeight(24);

        $sheet->getStyle('A11:AC13')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => self::COLOR_BORDER_DARK],
                ],
            ],
        ]);

        // Background Fills Per Kelompok Kolom
        $sheet->getStyle('A11:B13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GRAY_HEADER);
        $sheet->getStyle('C11:E13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_YELLOW_SOFT);
        $sheet->getStyle('F11:H13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_AMBER_SOFT);
        $sheet->getStyle('I11:J13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_ORANGE_SOFT);
        $sheet->getStyle('K11:W13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_MINT_SOFT);
        $sheet->getStyle('X11:X13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GOLD_TOTAL);
        $sheet->getStyle('Y11:AB13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_PURPLE_SOFT);
        $sheet->getStyle('AC11:AC13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_GREEN_HPP);

        // ══════════════════════════════════════════════════════════════════
        // 4. DATA HARIAN DENGAN RUMUS EXCEL ASLI
        // ══════════════════════════════════════════════════════════════════
        $row = 14;
        $dataStartRow = $row;

        foreach ($this->report['days'] as $d) {
            $hasData = $d['has_data'] ?? false;

            $sheet->setCellValue('A' . $row, $d['hari_nm']);
            $sheet->setCellValue('B' . $row, date('d/m/y', strtotime($d['date'])));

            if ($hasData) {
                // 1. Bahan Keripik
                $sheet->setCellValue('C' . $row, (float) $d['berko_kg']);
                $sheet->setCellValue('D' . $row, (float) $d['wip_kg']);
                $sheet->setCellValue('E' . $row, (float) $d['bahan_keripik_nilai']);

                // 2. Karton
                $sheet->setCellValue('F' . $row, (float) $d['karton_kk_qty']);
                $sheet->setCellValue('G' . $row, (float) $d['karton_mk_qty']);
                $sheet->setCellValue('H' . $row, (float) $d['karton_nilai']);

                // 3. Bumbu Balado
                $sheet->setCellValue('I' . $row, (float) $d['balado_kg']);
                $sheet->setCellValue('J' . $row, (float) $d['balado_nilai']);

                // 4. Penggunaan Roll
                $sheet->setCellValue('K' . $row, (float) $d['roll_kk_kg']);
                $sheet->setCellValue('L' . $row, (float) $d['roll_mk_kg']);
                $sheet->setCellValue('M' . $row, (float) $d['roll_nilai']);

                // 5. Lakban
                $sheet->setCellValue('N' . $row, (float) $d['lakban_atas_gr']);
                $sheet->setCellValue('O' . $row, (float) $d['lakban_bawah_gr']);
                $sheet->setCellValue('P' . $row, "=N{$row}+O{$row}");
                $sheet->setCellValue('Q' . $row, (float) $d['lakban_nilai']);

                // 6. Kertas Expired
                $sheet->setCellValue('R' . $row, (float) $d['kertas_exp_qty']);
                $sheet->setCellValue('S' . $row, (float) $d['kertas_exp_nilai']);

                // 7. Kerusakan Plastik
                $sheet->setCellValue('T' . $row, (float) $d['kerusakan_plastik_qty']);
                $sheet->setCellValue('U' . $row, (float) $d['kerusakan_plastik_nilai']);

                // 8. Kerusakan Karton
                $sheet->setCellValue('V' . $row, (float) $d['kerusakan_karton_qty']);
                $sheet->setCellValue('W' . $row, (float) $d['kerusakan_karton_nilai']);

                // 9. TOTAL BIAYA (Formula Excel Penjumlahan Seluruh Komponen Biaya)
                $sheet->setCellValue('X' . $row, "=E{$row}+H{$row}+J{$row}+M{$row}+Q{$row}+S{$row}+U{$row}+W{$row}");

                // 10. Hasil Produksi
                $sheet->setCellValue('Y' . $row, (float) $d['hasil_ping_ping']);
                $sheet->setCellValue('Z' . $row, (float) $d['hasil_maksi']);
                $sheet->setCellValue('AA' . $row, "=Y{$row}+Z{$row}");
                $sheet->setCellValue('AB' . $row, (float) $d['gramasi_kg']);

                // 11. HPP Per Unit
                $sheet->setCellValue('AC' . $row, "=IF(AA{$row}>0, X{$row}/AA{$row}, 0)");
            } else {
                // Hari tanpa produksi diisi 0 agar rumus konsisten
                foreach (range('C', 'W') as $col) {
                    $sheet->setCellValue($col . $row, 0);
                }
                $sheet->setCellValue('X' . $row, 0);
                $sheet->setCellValue('Y' . $row, 0);
                $sheet->setCellValue('Z' . $row, 0);
                $sheet->setCellValue('AA' . $row, 0);
                $sheet->setCellValue('AB' . $row, 0);
                $sheet->setCellValue('AC' . $row, 0);

                $sheet->getStyle('A' . $row . ':AC' . $row)->getFont()->getColor()->setARGB('FF94A3B8');
            }

            // Number formatting
            // Kuantitas Desimal (Kg, Gram)
            $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('K' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('N' . $row . ':P' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('T' . $row)->getNumberFormat()->setFormatCode('#,##0.0');
            $sheet->getStyle('AB' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Kuantitas Integer (Dus, Bungkus, Pcs)
            $sheet->getStyle('F' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('R' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('V' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('Y' . $row . ':AA' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // Nilai Rupiah
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('Q' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('S' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('U' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('W' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('X' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('AC' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Alignment
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row . ':AC' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Styling Kolom Kunci
            $sheet->getStyle('X' . $row)->getFont()->setBold(true);
            $sheet->getStyle('X' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFBEB');
            $sheet->getStyle('AA' . $row)->getFont()->setBold(true);
            $sheet->getStyle('AC' . $row)->getFont()->setBold(true);

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        $dataEndRow = $row - 1;

        // ══════════════════════════════════════════════════════════════════
        // 5. FOOTER: BARIS TOTAL (SUM) & RATA-RATA (AVERAGE)
        // ══════════════════════════════════════════════════════════════════
        // BARIS TOTAL
        $totRow = $row;
        $sheet->setCellValue('A' . $totRow, 'TOTAL');
        $sheet->mergeCells('A' . $totRow . ':B' . $totRow);

        $colsToSum = ['C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB'];
        foreach ($colsToSum as $colLetter) {
            $sheet->setCellValue($colLetter . $totRow, "=SUM({$colLetter}{$dataStartRow}:{$colLetter}{$dataEndRow})");
        }
        // Formula HPP Total (Total Biaya / Total Unit)
        $sheet->setCellValue('AC' . $totRow, "=IF(AA{$totRow}>0, X{$totRow}/AA{$totRow}, 0)");

        $sheet->getStyle('A' . $totRow . ':AC' . $totRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLOR_YELLOW_MAIN],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A' . $totRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $totRow . ':AC' . $totRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // BARIS RATA-RATA
        $avgRow = $row + 1;
        $sheet->setCellValue('A' . $avgRow, 'RATA - RATA');
        $sheet->mergeCells('A' . $avgRow . ':B' . $avgRow);

        foreach ($colsToSum as $colLetter) {
            $sheet->setCellValue($colLetter . $avgRow, "=AVERAGE({$colLetter}{$dataStartRow}:{$colLetter}{$dataEndRow})");
        }
        $sheet->setCellValue('AC' . $avgRow, "=AVERAGE(AC{$dataStartRow}:AC{$dataEndRow})");

        $sheet->getStyle('A' . $avgRow . ':AC' . $avgRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLOR_YELLOW_MAIN],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A' . $avgRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $avgRow . ':AC' . $avgRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Number Formatting Footer
        foreach ([$totRow, $avgRow] as $r) {
            $sheet->getStyle('C' . $r . ':D' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('I' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('K' . $r . ':L' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('N' . $r . ':P' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('T' . $r)->getNumberFormat()->setFormatCode('#,##0.0');
            $sheet->getStyle('AB' . $r)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle('F' . $r . ':G' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('R' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('V' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('Y' . $r . ':AA' . $r)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('E' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('J' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('Q' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('S' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('U' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('W' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('X' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('AC' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getRowDimension($r)->setRowHeight(22);
        }

        // ══════════════════════════════════════════════════════════════════
        // 6. BORDER SELURUH TABEL
        // ══════════════════════════════════════════════════════════════════
        $sheet->getStyle('A11:AC' . $avgRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => self::COLOR_BORDER],
                ],
            ],
        ]);

        // Border tebal atas dan bawah footer
        $sheet->getStyle('A' . $totRow . ':AC' . $totRow)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FFB45309');
        $sheet->getStyle('A' . $avgRow . ':AC' . $avgRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setARGB('FFB45309');

        // ══════════════════════════════════════════════════════════════════
        // 7. LEBAR KOLOM IDEAL
        // ══════════════════════════════════════════════════════════════════
        $colWidths = [
            'A'  => 8,   // HARI
            'B'  => 11,  // TANGGAL
            'C'  => 12,  // BERKO (KG)
            'D'  => 12,  // WIP (KG)
            'E'  => 15,  // RUPIAH
            'F'  => 9,   // XX
            'G'  => 9,   // MX
            'H'  => 14,  // RUPIAH
            'I'  => 11,  // BALADO
            'J'  => 14,  // RUPIAH
            'K'  => 9,   // ROLL XX
            'L'  => 9,   // ROLL MX
            'M'  => 14,  // RUPIAH
            'N'  => 9,   // ATAS
            'O'  => 9,   // BAWAH
            'P'  => 10,  // JUMLAH
            'Q'  => 14,  // RUPIAH
            'R'  => 10,  // BUNGKUS
            'S'  => 13,  // RUPIAH
            'T'  => 12,  // KERUSAKAN PLASTIK
            'U'  => 13,  // RUPIAH
            'V'  => 12,  // KERUSAKAN KARTON
            'W'  => 13,  // RUPIAH
            'X'  => 16,  // TOTAL BIAYA
            'Y'  => 12,  // PING-PING
            'Z'  => 12,  // MAKSI
            'AA' => 12,  // JUMLAH
            'AB' => 12,  // GRAMASI
            'AC' => 14,  // HPP
        ];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        return $spreadsheet;
    }
}
