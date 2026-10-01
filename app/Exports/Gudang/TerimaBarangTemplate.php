<?php

namespace App\Exports\Gudang;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TerimaBarangTemplate
{
    const COLOR_HEADER_BG  = 'FF134E5E';
    const COLOR_HEADER_FG  = 'FFFFFFFF';
    const COLOR_REQUIRED   = 'FFFFF3CD'; // Kuning muda = kolom wajib diisi
    const COLOR_OPTIONAL   = 'FFF0FFF4'; // Hijau muda = kolom opsional
    const COLOR_LOCKED     = 'FFF1F5F9'; // Abu-abu = contoh / informasi
    const COLOR_BORDER     = 'FFCBD5E1';
    const COLOR_DARK       = 'FF0F172A';
    const COLOR_MUTED      = 'FF64748B';
    const COLOR_RED        = 'FFDC2626';

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->build();

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="Template_Import_Barang_Masuk.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    private function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // ── Sheet 1: Template Import ─────────────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Barang Masuk');

        // Lebar kolom (sama persis dengan TerimaBarangExport)
        $colWidths = [
            'A' => 20,  // No. GRN
            'B' => 14,  // Tanggal
            'C' => 18,  // Kode Batch
            'D' => 16,  // Kode Barang
            'E' => 28,  // Nama Barang (auto-fill, terkunci)
            'F' => 20,  // Jenis (auto-fill)
            'G' => 22,  // Keterangan
            'H' => 14,  // Qty Masuk
            'I' => 18,  // Harga Satuan
            'J' => 22,  // Total Harga (auto-calc)
            'K' => 10,  // Satuan (auto-fill)
            'L' => 20,  // Gudang Simpan
            'M' => 26,  // Supplier Pengirim
            'N' => 18,  // No. Surat Jalan
            'O' => 14,  // Tgl Expired
            'P' => 8,   // Grade
            'Q' => 10,  // Diskon %
            'R' => 16,  // Potongan (Rp)
            'S' => 14,  // Pajak
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Baris 1–3: Kop & Judul ──────────────────────────────────
        $sheet->mergeCells('A1:S1');
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY — TEMPLATE IMPORT BARANG MASUK (GRN)');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => self::COLOR_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->mergeCells('A2:S2');
        $sheet->setCellValue('A2', 'Isi template ini dan upload melalui menu Barang Masuk → Import Excel. Satu baris = satu item barang. Baris dengan No. GRN sama dianggap satu dokumen penerimaan.');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => self::COLOR_MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Baris 3: Legenda warna ───────────────────────────────────
        $sheet->setCellValue('A3', 'Kolom WAJIB diisi');
        $sheet->setCellValue('D3', 'Kolom Opsional');
        $sheet->setCellValue('G3', 'Contoh / Hanya referensi (tidak diimpor)');

        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF92400E']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_REQUIRED]],
        ]);
        $sheet->getStyle('B3:C3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_REQUIRED]],
        ]);
        $sheet->getStyle('D3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF14532D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_OPTIONAL]],
        ]);
        $sheet->getStyle('E3:F3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_OPTIONAL]],
        ]);
        $sheet->getStyle('G3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => self::COLOR_MUTED]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_LOCKED]],
        ]);
        $sheet->getStyle('H3:S3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_LOCKED]],
        ]);

        $sheet->getRowDimension(4)->setRowHeight(4);

        // ── Baris 5: Sub-header Kategori ────────────────────────────
        $sheet->mergeCells('A5:J5');
        $sheet->setCellValue('A5', '📋 KOLOM UTAMA — wajib diisi dengan benar');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->mergeCells('K5:S5');
        $sheet->setCellValue('K5', '📎 KOLOM PELENGKAP — opsional');
        $sheet->getStyle('K5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Baris 6: Header Kolom ────────────────────────────────────
        $headerRow = 6;
        $headers = [
            'A' => "No. GRN\n(opsional)",
            'B' => "Tanggal\n(wajib)",
            'C' => "Kode Batch\n(wajib)",
            'D' => "Kode Barang\n(wajib)",
            'E' => "Nama Barang\n(referensi)",
            'F' => "Jenis\n(referensi)",
            'G' => "Keterangan",
            'H' => "Qty Masuk\n(wajib)",
            'I' => "Harga Satuan\n(wajib)",
            'J' => "Total Harga\n(auto)",
            'K' => "Satuan",
            'L' => "Gudang Simpan\n(wajib)",
            'M' => "Supplier\n(wajib)",
            'N' => "No. Surat Jalan",
            'O' => "Tgl Expired",
            'P' => "Grade",
            'Q' => "Diskon %",
            'R' => "Potongan (Rp)",
            'S' => "Pajak\n(NON_PPN/PPN_11)",
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $headerRow, $label);
        }

        $sheet->getStyle('A' . $headerRow . ':S' . $headerRow)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF0D3844']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(30);

        // ── Baris 7-11: Contoh Data ──────────────────────────────────
        $examples = [
            ['GRN-20260901-001', '1-Sep-2026', 'MS-25082026', 'MSW00G-BP2', 'MINYAK SAWIT', 'BAHAN PENOLONG', 'PENERIMAAN RUTIN', 2642.96, 17748.64, '', 'KG', 'Gudang Utama', 'PT Supplier A', 'SJ-2026/001', '', 'A', 0, 0, 'NON_PPN'],
            ['GRN-20260901-001', '1-Sep-2026', 'HD-10082026', 'ID00G-BP1',  'PLASTIK HD',  'BAHAN PENOLONG', 'PENERIMAAN RUTIN', 164.65, 29407.18, '', 'KG', 'Gudang Utama', 'PT Supplier A', 'SJ-2026/001', '', 'A', 0, 0, 'NON_PPN'],
            ['GRN-20260901-002', '1-Sep-2026', 'PR-10082026', 'PRH00G-BP2', 'PERENYAH',    'BAHAN PENOLONG', '',               200,    4661.48, '', 'KG', 'Gudang Utama', 'PT Supplier B', 'SJ-2026/002', '31-Des-2027', 'B', 2, 0, 'PPN_11'],
        ];

        $exRow = 7;
        foreach ($examples as $ex) {
            $col = 'A';
            foreach ($ex as $val) {
                $sheet->setCellValue($col . $exRow, $val);
                $col++;
            }

            // Warnai baris contoh abu-abu agar terlihat sebagai referensi
            $sheet->getStyle('A' . $exRow . ':S' . $exRow)->applyFromArray([
                'fill'   => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                'font'   => ['size' => 9, 'italic' => true, 'color' => ['argb' => 'FF64748B']],
                'borders'=> ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            ]);

            // Formula Total Harga
            $sheet->setCellValue('J' . $exRow, '=H' . $exRow . '*I' . $exRow);
            $sheet->getStyle('I' . $exRow . ':J' . $exRow)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle('H' . $exRow)->getNumberFormat()->setFormatCode('#,##0.00');

            $exRow++;
        }

        // Separator antara contoh dan area input
        $sheet->getRowDimension($exRow)->setRowHeight(6);
        $exRow++;

        // ── Area input kosong (baris 11+) dengan warna per tipe kolom ─
        for ($r = $exRow; $r <= $exRow + 50; $r++) {
            // Kolom wajib: kuning muda
            foreach (['B', 'C', 'D', 'H', 'I', 'L', 'M'] as $c) {
                $sheet->getStyle($c . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_REQUIRED]],
                ]);
            }
            // Kolom opsional: hijau muda
            foreach (['A', 'G', 'N', 'O', 'P', 'Q', 'R', 'S'] as $c) {
                $sheet->getStyle($c . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_OPTIONAL]],
                ]);
            }
            // Kolom referensi: abu-abu
            foreach (['E', 'F', 'J', 'K'] as $c) {
                $sheet->getStyle($c . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_LOCKED]],
                ]);
            }
            // Format angka
            $sheet->getStyle('H' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('I' . $r)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle('J' . $r)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->setCellValue('J' . $r, '=IF(H' . $r . '*I' . $r . '>0,H' . $r . '*I' . $r . ',"")');
            $sheet->getStyle('Q' . $r)->getNumberFormat()->setFormatCode('0.00"%"');
            $sheet->getStyle('R' . $r)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');

            // Border tipis
            $sheet->getStyle('A' . $r . ':S' . $r)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                'font'    => ['size' => 9],
            ]);
        }

        // Freeze header
        $sheet->freezePane('A7');
        $sheet->setAutoFilter('A6:S6');

        // ── Sheet 2: Referensi Kode Barang ───────────────────────────
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
        $refSheet->getColumnDimension('A')->setWidth(18);
        $refSheet->getColumnDimension('B')->setWidth(30);
        $refSheet->getColumnDimension('C')->setWidth(22);
        $refSheet->getColumnDimension('D')->setWidth(12);

        $barangs = MstBarang::with(['jenisBarang', 'satuanDasar'])->where('deleted_st', false)->where('active_st', true)->orderBy('barang_cd')->get();
        $r = 2;
        foreach ($barangs as $b) {
            $refSheet->setCellValue('A' . $r, $b->barang_cd);
            $refSheet->setCellValue('B' . $r, $b->barang_nm);
            $refSheet->setCellValue('C' . $r, $b->jenisBarang?->jenis_barang_nm ?? '-');
            $refSheet->setCellValue('D' . $r, $b->satuanDasar?->satuan_nm ?? '-');
            $refSheet->getStyle('A' . $r . ':D' . $r)->getFont()->setSize(9);
            $r++;
        }

        // ── Sheet 3: Referensi Supplier ──────────────────────────────
        $supSheet = $spreadsheet->createSheet();
        $supSheet->setTitle('Ref. Supplier');

        $supSheet->setCellValue('A1', 'Nama Supplier');
        $supSheet->setCellValue('B1', 'Kode Supplier');

        $supSheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
        ]);
        $supSheet->getColumnDimension('A')->setWidth(32);
        $supSheet->getColumnDimension('B')->setWidth(18);

        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();
        $r = 2;
        foreach ($suppliers as $s) {
            $supSheet->setCellValue('A' . $r, $s->supplier_nm);
            $supSheet->setCellValue('B' . $r, $s->supplier_cd ?? '-');
            $supSheet->getStyle('A' . $r . ':B' . $r)->getFont()->setSize(9);
            $r++;
        }

        // ── Sheet 4: Referensi Gudang ────────────────────────────────
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
            ->setTitle('Template Import Barang Masuk')
            ->setSubject('Template Import GRN — Barang Masuk Gudang');

        return $spreadsheet;
    }
}
