<?php

namespace App\Exports\Gudang;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemakaianExport
{
    protected $items;
    protected ?string $gudangNm;
    protected ?string $search;
    protected ?string $tujuan;
    protected string  $printedBy;
    protected string  $printedAt;

    // Warna tema korporat (Merah Pemakaian / Outbound)
    const COLOR_HEADER_BG = 'FF7F1D1D'; // Dark Red
    const COLOR_HEADER_FG = 'FFFFFFFF';
    const COLOR_ROW_EVEN  = 'FFFAFAFA';
    const COLOR_TOTAL_BG  = 'FFFEE2E2';
    const COLOR_BORDER    = 'FFCBD5E1';
    const COLOR_RED       = 'FFDC2626';
    const COLOR_DARK      = 'FF0F172A';
    const COLOR_MUTED     = 'FF64748B';
    const COLOR_GREEN     = 'FF047857';

    public function __construct(
        $items,
        ?string $gudangNm,
        ?string $search,
        ?string $tujuan,
        string $printedBy,
        string $printedAt
    ) {
        $this->items     = $items;
        $this->gudangNm  = $gudangNm;
        $this->search    = $search;
        $this->tujuan    = $tujuan;
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

    private function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Barang Keluar');

        // ── Lebar Kolom ──────────────────────────────────────────────
        // Kolom A-J: Kolom Utama Persis Sesuai Gambar Operasional
        // Kolom K-N: Kolom Informasi Pelengkap ERP
        $colWidths = [
            'A' => 18,   // No. SPK / Pakai
            'B' => 14,   // Tanggal
            'C' => 18,   // Kode Batch
            'D' => 16,   // Kode Barang
            'E' => 28,   // Nama Barang
            'F' => 20,   // Jenis
            'G' => 22,   // Keterangan (Tujuan Pemakaian)
            'H' => 14,   // Qty Keluar
            'I' => 18,   // Harga Satuan
            'J' => 22,   // Total Harga
            // Kolom Pelengkap
            'K' => 10,   // Satuan
            'L' => 20,   // Gudang Asal
            'M' => 16,   // No. Dokumen
            'N' => 22,   // Catatan
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Kop Surat ───────────────────────────────────────────────
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => self::COLOR_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', 'Pabrik Pengolahan Makanan & Keripik Singkong · Ambartawang, Magelang, Jawa Tengah');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['argb' => self::COLOR_MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->mergeCells('A3:N3');
        $sheet->setCellValue('A3', 'LAPORAN REKAPITULASI PEMAKAIAN BAHAN (OUTBOUND / BARANG KELUAR)');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Info filter
        $filterInfo = 'Gudang: ' . ($this->gudangNm ?? 'Semua Gudang');
        if ($this->tujuan) {
            $filterInfo .= '  |  Tujuan: ' . $this->tujuan;
        }
        if ($this->search) {
            $filterInfo .= '  |  Pencarian: "' . $this->search . '"';
        }
        $filterInfo .= '  |  Dicetak: ' . $this->printedAt . ' oleh ' . $this->printedBy;

        $sheet->mergeCells('A4:N4');
        $sheet->setCellValue('A4', $filterInfo);
        $sheet->getStyle('A4')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => self::COLOR_MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->getRowDimension(5)->setRowHeight(4);

        // ── Header Tabel ─────────────────────────────────────────────
        $headerRow = 6;
        $headers = [
            'A' => 'No. Dokumen',
            'B' => 'Tanggal',
            'C' => 'Kode Batch',
            'D' => 'Kode Barang',
            'E' => 'Nama Barang',
            'F' => 'Jenis',
            'G' => 'Keterangan',
            'H' => 'Qty Keluar',
            'I' => 'Harga Satuan',
            'J' => 'Total Harga',
            'K' => 'Satuan',
            'L' => 'Gudang Asal',
            'M' => 'No. Pakai',
            'N' => 'Catatan',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $headerRow, $label);
        }

        $sheet->getStyle('A' . $headerRow . ':N' . $headerRow)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9.5, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF6B0000']]],
        ]);

        // Alignment header
        $sheet->getStyle('A' . $headerRow . ':B' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $headerRow . ':G' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('H' . $headerRow . ':J' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('K' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L' . $headerRow . ':N' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        // ── Data Rows ────────────────────────────────────────────────
        $row      = $headerRow + 1;
        $no       = 1;
        $totalQty = 0;
        $totalNilai = 0;

        foreach ($this->items as $dtl) {
            $qty       = (float) ($dtl->qty_keluar ?? 0);
            $harga     = (float) ($dtl->harga_satuan ?? 0);
            $total     = (float) ($dtl->total_harga ?: ($qty * $harga));

            $totalQty   += $qty;
            $totalNilai += $total;

            // Keterangan = tujuan pemakaian dari header
            $keterangan = $dtl->header?->tujuan_pemakaian ?? '-';
            $catatan    = $dtl->keterangan_txt ?? $dtl->header?->catatan_txt ?? '-';
            $tglKeluar  = $dtl->header?->pakai_tgl ? Carbon::parse($dtl->header->pakai_tgl)->format('j-M-Y') : '-';

            // Data sel
            $sheet->setCellValue('A' . $row, $dtl->header?->pakai_no ?? '-');
            $sheet->setCellValue('B' . $row, $tglKeluar);
            $sheet->setCellValue('C' . $row, $dtl->batch_no ?? '-');
            $sheet->setCellValue('D' . $row, $dtl->barang?->barang_cd ?? '-');
            $sheet->setCellValue('E' . $row, strtoupper($dtl->barang?->barang_nm ?? '-'));
            $sheet->setCellValue('F' . $row, strtoupper($dtl->barang?->jenisBarang?->jenis_barang_nm ?? '-'));
            $sheet->setCellValue('G' . $row, strtoupper($keterangan));
            $sheet->setCellValue('H' . $row, $qty);
            $sheet->setCellValue('I' . $row, $harga);
            $sheet->setCellValue('J' . $row, $total);
            $sheet->setCellValue('K' . $row, $dtl->barang?->satuanDasar?->satuan_nm ?? '-');
            $sheet->setCellValue('L' . $row, $dtl->header?->gudang?->gudang_nm ?? '-');
            $sheet->setCellValue('M' . $row, $dtl->header?->pakai_no ?? '-');
            $sheet->setCellValue('N' . $row, $catatan !== '-' ? $catatan : '');

            // Format angka
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');

            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row . ':G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $row . ':J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L' . $row . ':N' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Zebra stripe
            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_ROW_EVEN]],
                ]);
            }

            // Bold total harga
            $sheet->getStyle('J' . $row)->getFont()->setBold(true);
            $sheet->getStyle('J' . $row)->getFont()->getColor()->setARGB(self::COLOR_RED);

            // Border
            $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                'font'    => ['size' => 9],
            ]);

            $row++;
            $no++;
        }

        // ── Baris Total ───────────────────────────────────────────────
        if ($no > 1) {
            $totalRow = $row;
            $sheet->mergeCells('A' . $totalRow . ':G' . $totalRow);
            $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN (' . ($no - 1) . ' Baris Data)');
            $sheet->setCellValue('H' . $totalRow, $totalQty);
            $sheet->setCellValue('J' . $totalRow, $totalNilai);

            $sheet->getStyle('H' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $totalRow)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');

            $sheet->getStyle('A' . $totalRow . ':N' . $totalRow)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9.5, 'color' => ['argb' => self::COLOR_DARK]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFFCA5A5']]],
            ]);

            $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $totalRow)->getFont()->getColor()->setARGB(self::COLOR_RED);
            $sheet->getRowDimension($totalRow)->setRowHeight(20);
        }

        // Freeze header
        $sheet->freezePane('A7');
        $sheet->setAutoFilter('A6:N6');

        // Print settings A4 Landscape
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getHeaderFooter()->setOddHeader('&L&B PT. Mirasa Food Industry&R&BHal &P dari &N');
        $sheet->getHeaderFooter()->setOddFooter('&LRekap Barang Keluar&R' . $this->printedAt);

        $spreadsheet->getProperties()
            ->setCreator('ERP PT Mirasa Food Industry')
            ->setTitle('Rekap Barang Keluar')
            ->setSubject('Laporan Rekapitulasi Pemakaian Bahan')
            ->setDescription('Dicetak pada ' . $this->printedAt . ' oleh ' . $this->printedBy);

        return $spreadsheet;
    }
}
