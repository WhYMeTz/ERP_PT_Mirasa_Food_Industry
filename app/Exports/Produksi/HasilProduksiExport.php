<?php

namespace App\Exports\Produksi;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HasilProduksiExport
{
    protected $items;
    protected ?string $gudangNm;
    protected ?string $search;
    protected ?string $batchNo;
    protected string  $printedBy;
    protected string  $printedAt;

    const COLOR_HEADER_BG = 'FF0284C7'; // Cyan/Blue Brand Produksi
    const COLOR_HEADER_FG = 'FFFFFFFF';
    const COLOR_ROW_EVEN  = 'FFF8FAFC';
    const COLOR_TOTAL_BG  = 'FFE2E8F0';
    const COLOR_BORDER    = 'FFCBD5E1';

    public function __construct(
        $items,
        ?string $gudangNm,
        ?string $search,
        ?string $batchNo,
        string $printedBy,
        string $printedAt
    ) {
        $this->items     = $items;
        $this->gudangNm  = $gudangNm;
        $this->search    = $search;
        $this->batchNo   = $batchNo;
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
        $sheet->setTitle('Hasil Barang Produksi');
        $sheet->setShowGridLines(true);

        // Judul Laporan
        $sheet->setCellValue('A1', 'PT MIRASA FOOD INDUSTRY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'LAPORAN HASIL BARANG PRODUKSI & PERSEDIAAN WIP');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $metaText = "Dicetak Oleh: {$this->printedBy} | Tanggal Cetak: {$this->printedAt}";
        if ($this->gudangNm) $metaText .= " | Gudang: {$this->gudangNm}";
        if ($this->batchNo)  $metaText .= " | Filter Batch: {$this->batchNo}";
        $sheet->setCellValue('A3', $metaText);
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);

        // Header Tabel Kolom (Row 5)
        $headers = [
            'A' => 'NO',
            'B' => 'TANGGAL',
            'C' => 'NO. PRODUKSI',
            'D' => 'KODE BATCH',
            'E' => 'GUDANG SIMPAN',
            'F' => 'JENIS BARANG',
            'G' => 'KODE BARANG',
            'H' => 'NAMA BARANG',
            'I' => 'QTY HASIL',
            'J' => 'SATUAN',
            'K' => 'BERAT (KG)',
            'L' => 'HPP SATUAN (RP)',
            'M' => 'TOTAL NILAI (RP)',
            'N' => 'SISA STOK (KG)',
        ];

        foreach ($headers as $col => $title) {
            $cell = $col . '5';
            $sheet->setCellValue($cell, $title);
            $sheet->getStyle($cell)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLOR_HEADER_FG));
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_HEADER_BG);
            $sheet->getStyle($cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            if (in_array($col, ['A', 'B', 'C', 'D', 'F', 'G', 'J'])) {
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } elseif (in_array($col, ['I', 'K', 'L', 'M', 'N'])) {
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Render Data Rows
        $row = 6;
        $totalQtyKg = 0;
        $totalNilai = 0;

        foreach ($this->items as $idx => $item) {
            $prod = $item->produksi;
            $barang = $item->barang;
            $jenisText = ($item->jenis_cd === 'FG' || ($barang && $barang->isFinishGood())) ? 'Finish Good (FG)' : 'WIP (Setengah Jadi)';
            $qtyHasilVal = $item->qty_hasil > 0 ? (float) $item->qty_hasil : (float) $item->qty_kg;
            $satuanVal = $item->satuan_cd ?: ($barang?->satuan?->satuan_nm ?? 'KG');

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $prod ? Carbon::parse($prod->produksi_tgl)->format('d/m/Y') : '-');
            $sheet->setCellValue('C' . $row, $prod->produksi_no ?? '-');
            $sheet->setCellValue('D' . $row, $item->batch_no ?? ($prod->batch_wip_no ?? '-'));
            $sheet->setCellValue('E' . $row, $prod->gudang?->gudang_nm ?? 'Gudang Utama');
            $sheet->setCellValue('F' . $row, $jenisText);
            $sheet->setCellValue('G' . $row, $barang->barang_cd ?? '-');
            $sheet->setCellValue('H' . $row, $barang->barang_nm ?? '-');
            $sheet->setCellValue('I' . $row, $qtyHasilVal);
            $sheet->setCellValue('J' . $row, $satuanVal);
            $sheet->setCellValue('K' . $row, (float) $item->qty_kg);
            $sheet->setCellValue('L' . $row, (float) $item->hpp_satuan);
            $sheet->setCellValue('M' . $row, (float) $item->total_nilai);
            $sheet->setCellValue('N' . $row, (float) ($item->sisa_stok ?? $item->qty_kg));

            $totalQtyKg += (float) $item->qty_kg;
            $totalNilai += (float) $item->total_nilai;

            // Formats
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('N' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            if ($idx % 2 === 1) {
                $sheet->getStyle("A{$row}:N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_ROW_EVEN);
            }

            $sheet->getStyle("A{$row}:N{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);
            $sheet->getRowDimension($row)->setRowHeight(20);

            $row++;
        }

        // Summary Row Total
        $sheet->setCellValue('A' . $row, 'TOTAL HASIL PRODUKSI (KG)');
        $sheet->mergeCells("A{$row}:J{$row}");
        $sheet->setCellValue('K' . $row, $totalQtyKg);
        $sheet->setCellValue('M' . $row, $totalNilai);

        $sheet->getStyle("A{$row}:N{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_TOTAL_BG);
        $sheet->getStyle("A{$row}:N{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getRowDimension($row)->setRowHeight(22);

        // Auto Size Kolom
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
