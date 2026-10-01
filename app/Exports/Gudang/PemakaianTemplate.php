<?php

namespace App\Exports\Gudang;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemakaianTemplate
{
    const COLOR_HEADER_BG = 'FF134E5E'; // Dark Teal (sama dengan PemakaianExport)
    const COLOR_HEADER_FG = 'FFFFFFFF';
    const COLOR_REQUIRED  = 'FFFFF3CD'; // Kuning muda = wajib diisi
    const COLOR_OPTIONAL  = 'FFF0FFF4'; // Hijau muda = opsional
    const COLOR_LOCKED    = 'FFF1F5F9'; // Abu-abu = referensi/auto
    const COLOR_BORDER    = 'FFCBD5E1';
    const COLOR_DARK      = 'FF0F172A';
    const COLOR_MUTED     = 'FF64748B';

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->build();

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="Template_Import_Pemakaian_Bahan.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    private function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // ── Sheet 1: Template Import Pemakaian ──────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Barang Keluar');

        // Lebar kolom (sama persis dengan PemakaianExport)
        $colWidths = [
            'A' => 20,  // No. Dokumen (pakai_no)
            'B' => 14,  // Tanggal
            'C' => 18,  // Kode Batch
            'D' => 16,  // Kode Barang
            'E' => 28,  // Nama Barang (auto-fill, referensi)
            'F' => 20,  // Jenis (referensi)
            'G' => 22,  // Keterangan / Tujuan Pemakaian
            'H' => 14,  // Qty Keluar
            'I' => 18,  // Harga Satuan (opsional – auto dari stok)
            'J' => 22,  // Total Harga (auto-calc)
            'K' => 10,  // Satuan (referensi)
            'L' => 20,  // Gudang Asal
            'M' => 18,  // No. Pakai (opsional = No. Dokumen)
            'N' => 22,  // Catatan item
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Baris 1: Kop ─────────────────────────────────────────────
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY — TEMPLATE IMPORT PEMAKAIAN BAHAN (BARANG KELUAR)');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => self::COLOR_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', 'Isi template ini dan upload di menu Pemakaian Bahan → Import Excel. Satu baris = satu item barang keluar. Baris dengan No. Dokumen sama dianggap satu dokumen pengeluaran.');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => self::COLOR_MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Baris 3: Legenda warna ────────────────────────────────────
        $sheet->setCellValue('A3', 'Kolom WAJIB diisi');
        $sheet->setCellValue('C3', 'Kolom Opsional');
        $sheet->setCellValue('E3', 'Referensi / tidak diimport');

        $sheet->getStyle('A3:B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => '92400E']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_REQUIRED]],
        ]);
        $sheet->getStyle('C3:D3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => '14532D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_OPTIONAL]],
        ]);
        $sheet->getStyle('E3:N3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => self::COLOR_MUTED]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_LOCKED]],
        ]);

        $sheet->getRowDimension(4)->setRowHeight(4);

        // ── Baris 5: Sub-header Kategori ─────────────────────────────
        $sheet->mergeCells('A5:J5');
        $sheet->setCellValue('A5', '📋 KOLOM UTAMA — wajib diisi dengan benar');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->mergeCells('K5:N5');
        $sheet->setCellValue('K5', '📎 KOLOM PELENGKAP — opsional');
        $sheet->getStyle('K5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Baris 6: Header Kolom ─────────────────────────────────────
        $headerRow = 6;
        $headers = [
            'A' => "No. Dokumen\n(opsional)",
            'B' => "Tanggal\n(wajib)",
            'C' => "Kode Batch\n(wajib)",
            'D' => "Kode Barang\n(wajib)",
            'E' => "Nama Barang\n(referensi)",
            'F' => "Jenis\n(referensi)",
            'G' => "Keterangan / Tujuan\n(wajib)",
            'H' => "Qty Keluar\n(wajib)",
            'I' => "Harga Satuan\n(opsional)",
            'J' => "Total Harga\n(auto)",
            'K' => "Satuan",
            'L' => "Gudang Asal\n(wajib)",
            'M' => "No. Pakai",
            'N' => "Catatan Item",
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $headerRow, $label);
        }

        $sheet->getStyle('A' . $headerRow . ':N' . $headerRow)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '0D3844']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(30);

        // ── Baris 7–9: Contoh Data ────────────────────────────────────
        $tujuanOptions = 'PRODUKSI IFM / PRODUKSI PING-PING / PACKING EKSPOR / PACKING JUMBO / PACKING XX 500 / PACKING XX2000 / SEASONING XX 2000 / AFKIR ULANG / SAMPLE LAB / QC';

        $examples = [
            ['PKI-20260901-001', '1-Sep-2026', 'MS-25082026', 'MSW00G-BP2', 'MINYAK SAWIT', 'BAHAN PENOLONG', 'PRODUKSI IFM', 100, '', '', 'KG', 'Gudang Utama', 'PKI-20260901-001', ''],
            ['PKI-20260901-001', '1-Sep-2026', 'HD-10082026', 'ID00G-BP1',  'PLASTIK HD',  'BAHAN PENOLONG', 'PRODUKSI IFM', 50.5, '', '', 'KG', 'Gudang Utama', 'PKI-20260901-001', ''],
            ['PKI-20260901-002', '1-Sep-2026', 'PR-10082026', 'PRH00G-BP2', 'PERENYAH',    'BAHAN PENOLONG', 'PACKING EKSPOR', 200, '', '', 'KG', 'Gudang Utama', 'PKI-20260901-002', 'Untuk batch ekspor'],
        ];

        $exRow = 7;
        foreach ($examples as $ex) {
            $col = 'A';
            foreach ($ex as $val) {
                $sheet->setCellValue($col . $exRow, $val);
                $col++;
            }
            $sheet->setCellValue('J' . $exRow, '=IF(H' . $exRow . '*I' . $exRow . '>0,H' . $exRow . '*I' . $exRow . ',"")');
            $sheet->getStyle('I' . $exRow . ':J' . $exRow)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle('H' . $exRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $exRow . ':N' . $exRow)->applyFromArray([
                'fill'   => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                'font'   => ['size' => 9, 'italic' => true, 'color' => ['argb' => '64748B']],
                'borders'=> ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            ]);
            $exRow++;
        }

        $sheet->getRowDimension($exRow)->setRowHeight(6);
        $exRow++;

        // ── Area Input Kosong (baris 11+) ─────────────────────────────
        for ($r = $exRow; $r <= $exRow + 50; $r++) {
            // Kolom wajib = kuning
            foreach (['B', 'C', 'D', 'G', 'H', 'L'] as $c) {
                $sheet->getStyle($c . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_REQUIRED]],
                ]);
            }
            // Kolom opsional = hijau
            foreach (['A', 'I', 'M', 'N'] as $c) {
                $sheet->getStyle($c . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_OPTIONAL]],
                ]);
            }
            // Kolom referensi = abu-abu
            foreach (['E', 'F', 'J', 'K'] as $c) {
                $sheet->getStyle($c . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_LOCKED]],
                ]);
            }

            // Formula otomatis:
            // 1. Nama Barang (Kolom E), Jenis (Kolom F), Satuan (Kolom K) otomatis terisi via VLOOKUP saat Kode Barang (D) diisi
            $sheet->setCellValue('E' . $r, '=IF(D' . $r . '="","",IFERROR(VLOOKUP(D' . $r . ",'Ref. Kode Barang'!\$A\$2:\$D\$1000,2,FALSE),\"\"))");
            $sheet->setCellValue('F' . $r, '=IF(D' . $r . '="","",IFERROR(VLOOKUP(D' . $r . ",'Ref. Kode Barang'!\$A\$2:\$D\$1000,3,FALSE),\"\"))");
            $sheet->setCellValue('K' . $r, '=IF(D' . $r . '="","",IFERROR(VLOOKUP(D' . $r . ",'Ref. Kode Barang'!\$A\$2:\$D\$1000,4,FALSE),\"\"))");
            // 2. Total Harga (Kolom J) = Qty Keluar (H) * Harga Satuan (I)
            $sheet->setCellValue('J' . $r, '=IF(H' . $r . '*I' . $r . '>0,H' . $r . '*I' . $r . ',"")');

            $sheet->getStyle('A' . $r . ':N' . $r)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                'font'    => ['size' => 9],
            ]);
        }

        // Petunjuk tujuan di baris keterangan bawah
        $noteRow = $exRow + 52;
        $sheet->mergeCells('A' . $noteRow . ':N' . $noteRow);
        $sheet->setCellValue('A' . $noteRow, '📌 Contoh Tujuan Pemakaian: ' . $tujuanOptions);
        $sheet->getStyle('A' . $noteRow)->applyFromArray([
            'font'      => ['size' => 8, 'italic' => true, 'color' => ['argb' => self::COLOR_MUTED]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF9C3']],
            'alignment' => ['wrapText' => true, 'horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getRowDimension($noteRow)->setRowHeight(20);

        $sheet->freezePane('A7');
        $sheet->setAutoFilter('A6:N6');

        // ── Sheet 2: Referensi Kode Barang ────────────────────────────
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Ref. Kode Barang');
        $refSheet->setCellValue('A1', 'Kode Barang');
        $refSheet->setCellValue('B1', 'Nama Barang');
        $refSheet->setCellValue('C1', 'Jenis');
        $refSheet->setCellValue('D1', 'Satuan');
        $refSheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
        ]);
        foreach (['A' => 18, 'B' => 32, 'C' => 22, 'D' => 12] as $c => $w) {
            $refSheet->getColumnDimension($c)->setWidth($w);
        }

        $barangs = MstBarang::with(['jenisBarang', 'satuanDasar'])
            ->where('deleted_st', false)->where('active_st', true)->orderBy('barang_cd')->get();
        $r = 2;
        foreach ($barangs as $b) {
            $refSheet->setCellValue('A' . $r, $b->barang_cd);
            $refSheet->setCellValue('B' . $r, $b->barang_nm);
            $refSheet->setCellValue('C' . $r, $b->jenisBarang?->jenis_barang_nm ?? '-');
            $refSheet->setCellValue('D' . $r, $b->satuanDasar?->satuan_nm ?? '-');
            $refSheet->getStyle('A' . $r . ':D' . $r)->getFont()->setSize(9);
            $r++;
        }

        // ── Sheet 3: Referensi Gudang ──────────────────────────────────
        $gudSheet = $spreadsheet->createSheet();
        $gudSheet->setTitle('Ref. Gudang');
        $gudSheet->setCellValue('A1', 'Nama Gudang');
        $gudSheet->setCellValue('B1', 'Kode Gudang');
        $gudSheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
        ]);
        $gudSheet->getColumnDimension('A')->setWidth(28);
        $gudSheet->getColumnDimension('B')->setWidth(16);

        $gudangs = MstGudang::where('deleted_st', false)->where('active_st', true)->orderBy('gudang_nm')->get();
        $r = 2;
        foreach ($gudangs as $g) {
            $gudSheet->setCellValue('A' . $r, $g->gudang_nm);
            $gudSheet->setCellValue('B' . $r, $g->gudang_cd ?? '-');
            $gudSheet->getStyle('A' . $r . ':B' . $r)->getFont()->setSize(9);
            $r++;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getProperties()
            ->setCreator('ERP PT Mirasa Food Industry')
            ->setTitle('Template Import Pemakaian Bahan')
            ->setSubject('Template Import Barang Keluar (Outbound)');

        return $spreadsheet;
    }
}
