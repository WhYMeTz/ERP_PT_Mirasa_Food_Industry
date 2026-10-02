<?php

namespace App\Exports\Produksi;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HasilProduksiTemplate
{
    const COLOR_HEADER_BG = 'FF0284C7'; // Cyan/Blue Brand Produksi
    const COLOR_HEADER_FG = 'FFFFFFFF';
    const COLOR_REQUIRED  = 'FFFFF3CD'; // Kuning muda = wajib
    const COLOR_OPTIONAL  = 'FFF0FFF4'; // Hijau muda = opsional
    const COLOR_LOCKED    = 'FFF1F5F9'; // Abu-abu = referensi
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
        $response->headers->set('Content-Disposition', 'attachment; filename="Template_Import_Hasil_Produksi.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    private function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // ── Sheet 1: Template Hasil Produksi ────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Produksi');

        $colWidths = [
            'A' => 20, // No. Dokumen
            'B' => 14, // Tanggal
            'C' => 18, // Kode Batch
            'D' => 16, // Kode Barang
            'E' => 28, // Nama Barang (referensi)
            'F' => 22, // Kategori Output
            'G' => 16, // Qty Hasil (Kg)
            'H' => 12, // Satuan (referensi)
            'I' => 18, // HPP/Kg (opsional)
            'J' => 20, // Gudang Simpan
            'K' => 10, // Shift
            'L' => 15, // Karton Awal
            'M' => 15, // Karton Akhir
            'N' => 25, // Catatan
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Kop
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY — TEMPLATE IMPORT HASIL BARANG PRODUKSI (WIP)');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => self::COLOR_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', 'Isi data hasil produksi barang (WIP) mulai baris ke-7. Satu baris = satu item hasil produksi. Baris dengan No. Dokumen / Tanggal & Shift yang sama dikelompokkan dalam satu lembar kerja produksi.');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => self::COLOR_MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Legenda
        $sheet->setCellValue('A3', 'Kolom WAJIB diisi');
        $sheet->setCellValue('C3', 'Kolom Opsional');
        $sheet->setCellValue('E3', 'Referensi / Panduan');

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

        // Header Sub-Section
        $sheet->mergeCells('A5:G5');
        $sheet->setCellValue('A5', '📦 RINCIAN HASIL BARANG PRODUKSI (OUTPUT)');
        $sheet->getStyle('A5')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->mergeCells('H5:N5');
        $sheet->setCellValue('H5', '📎 LOKASI GUDANG, BATCH, KARTON & KETERANGAN');
        $sheet->getStyle('H5')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '0369A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Kolom Header Baris 6
        $headers = [
            'A' => "No. Dokumen\n(opsional)",
            'B' => "Tanggal\n(YYYY-MM-DD)",
            'C' => "Kode Batch\n(misal: A0001)",
            'D' => "Kode Barang\n(wajib)",
            'E' => "Nama Barang\n(referensi)",
            'F' => "Kategori Output\n(Pilih Kategori)",
            'G' => "Qty Hasil (Kg)\n(wajib)",
            'H' => "Satuan\n(KG)",
            'I' => "HPP Satuan (Rp)\n(opsional)",
            'J' => "Gudang Simpan\n(wajib)",
            'K' => "Shift\n(A / B)",
            'L' => "No Karton Awal\n(opsional)",
            'M' => "No Karton Akhir\n(opsional)",
            'N' => "Catatan / Keterangan\n(opsional)",
        ];

        foreach ($headers as $col => $label) {
            $cell = $col . '6';
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => self::COLOR_HEADER_FG]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]],
            ]);
        }
        $sheet->getRowDimension(6)->setRowHeight(36);

        // Contoh Data Baris 7 & 8
        $sampleData = [
            [
                'A' => 'PROD-20261002-001',
                'B' => date('Y-m-d'),
                'C' => 'A0001',
                'D' => 'BRG-WIP-001',
                'E' => 'KERIPIK SINGKONG ASIN BARCO (WIP)',
                'F' => 'ASIN_BARCO',
                'G' => 350.00,
                'H' => 'KG',
                'I' => 14500,
                'J' => 'Gudang WIP & Jadi',
                'K' => 'A',
                'L' => 1,
                'M' => 25,
                'N' => 'Hasil shift pagi',
            ],
            [
                'A' => 'PROD-20261002-001',
                'B' => date('Y-m-d'),
                'C' => 'A0001',
                'D' => 'BRG-WIP-004',
                'E' => 'KERIPIK SINGKONG BERKO (WIP)',
                'F' => 'BERKO',
                'G' => 85.50,
                'H' => 'KG',
                'I' => 13500,
                'J' => 'Gudang WIP & Jadi',
                'K' => 'A',
                'L' => 26,
                'M' => 30,
                'N' => 'Sortiran berko',
            ],
        ];

        $r = 7;
        foreach ($sampleData as $item) {
            foreach ($item as $col => $val) {
                $sheet->setCellValue($col . $r, $val);
            }
            $sheet->getStyle('A' . $r . ':N' . $r)->applyFromArray([
                'font'    => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            ]);
            $sheet->getStyle('A' . $r . ':D' . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('K' . $r . ':M' . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }

        // Format 50 Baris Kosong Siap Isi
        for ($row = 9; $row <= 50; $row++) {
            $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                'font'    => ['size' => 9],
            ]);
            // Warnai background kolom wajib
            $sheet->getStyle('B' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFDFDEA');
            $sheet->getStyle('F' . $row . ':G' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFDFDEA');
            $sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFDFDEA');

            // Format formula nama barang dari Sheet 2 jika diisi kode barang
            $sheet->setCellValue('E' . $row, "=IFERROR(VLOOKUP(D{$row}, 'Ref. Kode Barang'!\$A\$3:\$B\$500, 2, FALSE), \"\")");
        }

        // ── Sheet 2: Ref. Kode Barang ──────────────────────────────
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Ref. Kode Barang');
        $refSheet->getColumnDimension('A')->setWidth(18);
        $refSheet->getColumnDimension('B')->setWidth(35);
        $refSheet->getColumnDimension('C')->setWidth(18);
        $refSheet->getColumnDimension('D')->setWidth(12);

        $refSheet->mergeCells('A1:D1');
        $refSheet->setCellValue('A1', 'DAFTAR REFERENSI KODE BARANG — PT MIRASA FOOD INDUSTRY');
        $refSheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);

        $refSheet->setCellValue('A2', 'KODE BARANG');
        $refSheet->setCellValue('B2', 'NAMA BARANG');
        $refSheet->setCellValue('C2', 'KATEGORI');
        $refSheet->setCellValue('D2', 'SATUAN');
        $refSheet->getStyle('A2:D2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
        ]);

        $barangList = MstBarang::with(['satuan', 'jenisBarang'])
            ->where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('barang_nm')
            ->get();

        $refRow = 3;
        foreach ($barangList as $b) {
            $refSheet->setCellValue('A' . $refRow, $b->barang_cd);
            $refSheet->setCellValue('B' . $refRow, $b->barang_nm);
            $refSheet->setCellValue('C' . $refRow, $b->jenisBarang?->jenis_barang_nm ?? 'WIP / Barang');
            $refSheet->setCellValue('D' . $refRow, $b->satuan?->satuan_nm ?? 'KG');
            $refRow++;
        }

        // ── Sheet 3: Ref. Gudang ──────────────────────────────────
        $gudangSheet = $spreadsheet->createSheet();
        $gudangSheet->setTitle('Ref. Gudang');
        $gudangSheet->getColumnDimension('A')->setWidth(18);
        $gudangSheet->getColumnDimension('B')->setWidth(30);

        $gudangSheet->mergeCells('A1:B1');
        $gudangSheet->setCellValue('A1', 'DAFTAR REFERENSI GUDANG');
        $gudangSheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);

        $gudangSheet->setCellValue('A2', 'KODE GUDANG');
        $gudangSheet->setCellValue('B2', 'NAMA GUDANG');
        $gudangSheet->getStyle('A2:B2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_FG], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
        ]);

        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        $gRow = 3;
        foreach ($gudangList as $g) {
            $gudangSheet->setCellValue('A' . $gRow, $g->gudang_cd ?? ('GD-' . $g->gudang_id));
            $gudangSheet->setCellValue('B' . $gRow, $g->gudang_nm);
            $gRow++;
        }

        // Set active sheet kembali ke Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
