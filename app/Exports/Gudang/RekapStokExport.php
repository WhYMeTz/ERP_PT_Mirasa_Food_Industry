<?php

namespace App\Exports\Gudang;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapStokExport
{
    protected $items;
    protected ?string $gudangNm;
    protected ?string $statusFilter;
    protected ?string $search;
    protected string  $printedBy;
    protected string  $printedAt;

    const COLOR_HEADER_BG   = 'FF0F766E'; // Teal Mirasa
    const COLOR_HEADER_FG   = 'FFFFFFFF';
    const COLOR_ROW_EVEN    = 'FFF8FAFC';
    const COLOR_TOTAL_BG    = 'FFE2E8F0';
    const COLOR_BORDER      = 'FFCBD5E1';
    const COLOR_GREEN       = 'FF047857';
    const COLOR_AMBER       = 'FFB45309';
    const COLOR_RED         = 'FFDC2626';

    public function __construct($items, ?string $gudangNm, ?string $statusFilter, ?string $search, string $printedBy, string $printedAt)
    {
        $this->items        = $items;
        $this->gudangNm     = $gudangNm;
        $this->statusFilter = $statusFilter;
        $this->search       = $search;
        $this->printedBy    = $printedBy;
        $this->printedAt    = $printedAt;
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
        $sheet->setTitle('Rekap Stok');

        // Judul Utama Laporan
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'PT MIRASA FOOD INDUSTRY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI STOK & VALUASI PERSEDIAAN GUDANG');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0369A1'));

        // Metadata Info
        $sheet->setCellValue('A4', 'Lokasi Gudang: ' . ($this->gudangNm ?: 'Semua Gudang Terdaftar'));
        $sheet->setCellValue('A5', 'Status Filter: ' . ($this->statusFilter ? strtoupper($this->statusFilter) : 'Semua Status'));
        $sheet->setCellValue('H4', 'Dicetak Oleh: ' . $this->printedBy);
        $sheet->setCellValue('H5', 'Tanggal Cetak: ' . $this->printedAt);
        $sheet->getStyle('A4:L5')->getFont()->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));

        // Table Header
        $headers = [
            'A7' => 'NO',
            'B7' => 'KODE BARANG',
            'C7' => 'NAMA BARANG',
            'D7' => 'JENIS BARANG',
            'E7' => 'SATUAN',
            'F7' => 'BATAS MIN',
            'G7' => 'TOTAL MASUK',
            'H7' => 'TOTAL KELUAR',
            'I7' => 'STOK AKHIR',
            'J7' => 'NILAI PERSEDIAAN (RP)',
            'K7' => 'STATUS',
            'L7' => 'LOKASI GUDANG',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A7:L7')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => self::COLOR_HEADER_FG],
                'size'  => 9,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLOR_HEADER_BG],
            ],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(25);

        // Data Rows
        $row = 8;
        $no = 1;

        $sumMasuk = 0;
        $sumKeluar = 0;
        $sumAkhir = 0;
        $sumNilai = 0;

        foreach ($this->items as $item) {
            $minQty = (float) $item->batas_minimum_qty;
            $masuk = (float) $item->total_masuk;
            $keluar = (float) $item->total_keluar;
            $akhir = (float) $item->stok_akhir;
            $nilai = (float) $item->nilai_persediaan;

            $sumMasuk += $masuk;
            $sumKeluar += $keluar;
            $sumAkhir += $akhir;
            $sumNilai += $nilai;

            $statusText = 'AMAN';
            $statusColor = self::COLOR_GREEN;
            if ($akhir <= 0) {
                $statusText = 'HABIS';
                $statusColor = self::COLOR_RED;
            } elseif ($akhir <= $minQty) {
                $statusText = 'RENDAH';
                $statusColor = self::COLOR_AMBER;
            }

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item->barang_cd);
            $sheet->setCellValue('C' . $row, $item->barang_nm);
            $sheet->setCellValue('D' . $row, $item->jenisBarang?->jenis_barang_nm ?? '-');
            $sheet->setCellValue('E' . $row, $item->satuanDasar?->satuan_cd ?? 'KG');
            $sheet->setCellValue('F' . $row, $minQty);
            $sheet->setCellValue('G' . $row, $masuk);
            $sheet->setCellValue('H' . $row, $keluar);
            $sheet->setCellValue('I' . $row, $akhir);
            $sheet->setCellValue('J' . $row, $nilai);
            $sheet->setCellValue('K' . $row, $statusText);
            $sheet->setCellValue('L' . $row, $this->gudangNm ?: 'Gudang Utama Mirasa');

            // Format numbers
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
            $sheet->getStyle('K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K' . $row)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($statusColor));

            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':L' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB(self::COLOR_ROW_EVEN);
            }

            $row++;
        }

        // Summary Row
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL KESELURUHAN PERSEDIAAN:');
        $sheet->setCellValue('G' . $row, $sumMasuk);
        $sheet->setCellValue('H' . $row, $sumKeluar);
        $sheet->setCellValue('I' . $row, $sumAkhir);
        $sheet->setCellValue('J' . $row, $sumNilai);
        $sheet->setCellValue('K' . $row, '-');
        $sheet->setCellValue('L' . $row, '-');

        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLOR_TOTAL_BG],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $sheet->getRowDimension($row)->setRowHeight(22);

        // Border styling for entire table
        $sheet->getStyle('A7:L' . $row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => self::COLOR_BORDER],
                ],
            ],
        ]);

        // Auto-fit columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
