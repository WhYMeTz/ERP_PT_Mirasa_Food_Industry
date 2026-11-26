<?php

namespace App\Exports\Penjualan;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjualanBahanPenolongExport
{
    protected Collection $orders;
    protected ?string $search;
    protected ?string $startDate;
    protected ?string $endDate;
    protected string $printedBy;
    protected string $printedAt;

    const COLOR_HEADER_BG = 'FF0F172A'; // Navy Mirasa
    const COLOR_HEADER_FG = 'FFFFFFFF';
    const COLOR_ROW_EVEN  = 'FFF8FAFC';
    const COLOR_TOTAL_BG  = 'FFE2E8F0';
    const COLOR_BORDER    = 'FFCBD5E1';
    const COLOR_GREEN     = 'FF047857';

    public function __construct(
        Collection $orders,
        ?string $search,
        ?string $startDate,
        ?string $endDate,
        string $printedBy,
        string $printedAt
    ) {
        $this->orders    = $orders;
        $this->search    = $search;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
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
        $sheet->setTitle('Penjualan Bahan Penolong');

        // ── 1. Lebar Kolom ──────────────────────────────────────────────
        $colWidths = [
            'A' => 6,   // No
            'B' => 20,  // No. Transaksi
            'C' => 18,  // No. Faktur
            'D' => 18,  // No. Surat Jalan
            'E' => 14,  // Tanggal
            'F' => 25,  // Pembeli / Customer
            'G' => 16,  // Nopol Kendaraan
            'H' => 14,  // Cara Bayar
            'I' => 16,  // Kode Barang
            'J' => 32,  // Nama Bahan / Bumbu
            'K' => 16,  // Kategori
            'L' => 12,  // Qty Terjual
            'M' => 10,  // Satuan
            'N' => 16,  // Harga Satuan (Rp)
            'O' => 18,  // Subtotal Bruto (Rp)
            'P' => 16,  // Diskon Item (Rp)
            'Q' => 16,  // PPN (Rp)
            'R' => 20,  // Total Tagihan (Rp)
            'S' => 16,  // Status
            'T' => 16,  // Kasir / Dicatat Oleh
            'U' => 35,  // Catatan / Keterangan
        ];

        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // ── 2. Header Dokumen & Identitas Perusahaan ────────────────────
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY');
        $sheet->setCellValue('A2', 'REKAPITULASI PENJUALAN BAHAN PENOLONG, KEMASAN & BUMBU (NON-CORE SALES)');
        $sheet->setCellValue('A3', 'Kawasan Industri Candi Blok XI No. 8, Krapyak, Semarang &bull; Divisi Penjualan & Finance');

        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF047857'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        // ── 3. Panel Metadata Filter ────────────────────────────────────
        $metaRow = 5;
        $sheet->setCellValue('A' . $metaRow, 'Parameter Filter Aktif:');
        $sheet->getStyle('A' . $metaRow)->getFont()->setBold(true)->setSize(9);

        $periodeText = 'Semua Tanggal';
        if ($this->startDate && $this->endDate) {
            $periodeText = date('d/m/Y', strtotime($this->startDate)) . ' s/d ' . date('d/m/Y', strtotime($this->endDate));
        } elseif ($this->startDate) {
            $periodeText = 'Sejak ' . date('d/m/Y', strtotime($this->startDate));
        } elseif ($this->endDate) {
            $periodeText = 'Hingga ' . date('d/m/Y', strtotime($this->endDate));
        }

        $metaItems = [
            ['Periode Tanggal:', $periodeText, 'Kata Kunci Pencarian:', $this->search ?: '(Semua Data)'],
            ['Total Transaksi:', $this->orders->count() . ' Transaksi', 'Dicetak Oleh / Pada:', $this->printedBy . ' | ' . $this->printedAt],
        ];

        foreach ($metaItems as $idx => $m) {
            $r = $metaRow + 1 + $idx;
            $sheet->setCellValue('A' . $r, $m[0]);
            $sheet->setCellValue('B' . $r, $m[1]);
            $sheet->setCellValue('D' . $r, $m[2]);
            $sheet->setCellValue('E' . $r, $m[3]);

            $sheet->getStyle('A' . $r)->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));
            $sheet->getStyle('B' . $r)->getFont()->setSize(9);
            $sheet->getStyle('D' . $r)->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));
            $sheet->getStyle('E' . $r)->getFont()->setSize(9);
        }

        // ── 4. Tabel Header ─────────────────────────────────────────────
        $headerRow = $metaRow + 4;
        $headers = [
            'A' => 'NO',
            'B' => 'NO. TRANSAKSI',
            'C' => 'NO. FAKTUR',
            'D' => 'NO. SURAT JALAN',
            'E' => 'TANGGAL',
            'F' => 'PEMBELI / CUSTOMER',
            'G' => 'NOPOL KENDARAAN',
            'H' => 'CARA BAYAR',
            'I' => 'KODE BARANG',
            'J' => 'NAMA BAHAN / BUMBU',
            'K' => 'KATEGORI',
            'L' => 'QTY TERJUAL',
            'M' => 'SATUAN',
            'N' => 'HARGA SATUAN (RP)',
            'O' => 'SUBTOTAL BRUTO (RP)',
            'P' => 'DISKON ITEM (RP)',
            'Q' => 'PPN (RP)',
            'R' => 'TOTAL TAGIHAN (RP)',
            'S' => 'STATUS',
            'T' => 'KASIR',
            'U' => 'CATATAN / KETERANGAN',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
        }

        $sheet->getRowDimension($headerRow)->setRowHeight(24);
        $headerRange = 'A' . $headerRow . ':U' . $headerRow;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => self::COLOR_HEADER_FG],
                'size'  => 9,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLOR_HEADER_BG],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF334155'],
                ],
            ],
        ]);

        // ── 5. Isi Baris Data ───────────────────────────────────────────
        $currentRow = $headerRow + 1;
        $no = 1;

        $grandQty      = 0;
        $grandBruto    = 0;
        $grandDiskon   = 0;
        $grandPpn      = 0;
        $grandTagihan  = 0;

        foreach ($this->orders as $order) {
            $details = $order->details;
            $tgl = $order->so_tgl ? date('d/m/Y', strtotime($order->so_tgl)) : '-';
            $pembeli = $order->supir_nm ?: ($order->customer?->customer_nm ?? 'Pelanggan Umum');
            $caraBayar = $order->top_txt ?: 'TUNAI';
            $nopol = $order->nopol_kendaraan ?: '-';
            $kasir = $order->created_by ?: 'Kasir';
            $catatan = $order->catatan_txt ?: '-';

            if ($details->isEmpty()) {
                // Baris tanpa detail
                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $order->so_no);
                $sheet->setCellValue('C' . $currentRow, $order->faktur_no ?: '-');
                $sheet->setCellValue('D' . $currentRow, $order->surat_jalan_no ?: '-');
                $sheet->setCellValue('E' . $currentRow, $tgl);
                $sheet->setCellValue('F' . $currentRow, $pembeli);
                $sheet->setCellValue('G' . $currentRow, $nopol);
                $sheet->setCellValue('H' . $currentRow, $caraBayar);
                $sheet->setCellValue('I' . $currentRow, '-');
                $sheet->setCellValue('J' . $currentRow, '-');
                $sheet->setCellValue('K' . $currentRow, '-');
                $sheet->setCellValue('L' . $currentRow, 0);
                $sheet->setCellValue('M' . $currentRow, '-');
                $sheet->setCellValue('N' . $currentRow, 0);
                $sheet->setCellValue('O' . $currentRow, (float) $order->subtotal_bruto);
                $sheet->setCellValue('P' . $currentRow, (float) $order->diskon_total);
                $sheet->setCellValue('Q' . $currentRow, (float) $order->ppn_nominal);
                $sheet->setCellValue('R' . $currentRow, (float) $order->total_tagihan);
                $sheet->setCellValue('S' . $currentRow, 'Lunas & Selesai');
                $sheet->setCellValue('T' . $currentRow, $kasir);
                $sheet->setCellValue('U' . $currentRow, $catatan);

                $grandBruto   += (float) $order->subtotal_bruto;
                $grandDiskon  += (float) $order->diskon_total;
                $grandPpn     += (float) $order->ppn_nominal;
                $grandTagihan += (float) $order->total_tagihan;

                $currentRow++;
                continue;
            }

            foreach ($details as $dIdx => $detail) {
                $barang = $detail->barang;
                $qty = (float) $detail->pesan_qty;
                $satuan = $detail->satuan ?: ($barang?->satuanDasar?->satuan_nm ?? 'Pcs');
                $harga = (float) $detail->harga_satuan;
                $subtotal = round($qty * $harga, 2);
                $diskonItem = (float) ($detail->diskon_nominal ?? 0);
                $ppnItem = (float) ($detail->ppn_nominal ?? 0);
                $tagihanItem = (float) ($detail->subtotal ?? ($subtotal - $diskonItem + $ppnItem));

                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $order->so_no);
                $sheet->setCellValue('C' . $currentRow, $order->faktur_no ?: '-');
                $sheet->setCellValue('D' . $currentRow, $order->surat_jalan_no ?: '-');
                $sheet->setCellValue('E' . $currentRow, $tgl);
                $sheet->setCellValue('F' . $currentRow, $pembeli);
                $sheet->setCellValue('G' . $currentRow, $nopol);
                $sheet->setCellValue('H' . $currentRow, $caraBayar);
                $sheet->setCellValue('I' . $currentRow, $barang?->barang_cd ?: '-');
                $sheet->setCellValue('J' . $currentRow, $barang?->barang_nm ?: ($detail->barang_nm ?? '-'));
                $sheet->setCellValue('K' . $currentRow, $barang?->jenisBarang?->jenis_barang_nm ?: 'Bahan Penolong');
                $sheet->setCellValue('L' . $currentRow, $qty);
                $sheet->setCellValue('M' . $currentRow, $satuan);
                $sheet->setCellValue('N' . $currentRow, $harga);
                $sheet->setCellValue('O' . $currentRow, $subtotal);
                $sheet->setCellValue('P' . $currentRow, $diskonItem);
                $sheet->setCellValue('Q' . $currentRow, $ppnItem);
                $sheet->setCellValue('R' . $currentRow, $tagihanItem);
                $sheet->setCellValue('S' . $currentRow, 'Lunas & Selesai');
                $sheet->setCellValue('T' . $currentRow, $kasir);
                $sheet->setCellValue('U' . $currentRow, $catatan);

                $grandQty     += $qty;
                $grandBruto   += $subtotal;
                $grandDiskon  += $diskonItem;
                $grandPpn     += $ppnItem;
                $grandTagihan += $tagihanItem;

                $currentRow++;
            }
        }

        // Format styling isi tabel
        $lastDataRow = $currentRow - 1;
        if ($lastDataRow >= $headerRow + 1) {
            // Alignment
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . ($headerRow + 1) . ':D' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . ($headerRow + 1) . ':E' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('G' . ($headerRow + 1) . ':I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . ($headerRow + 1) . ':J' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('K' . ($headerRow + 1) . ':K' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L' . ($headerRow + 1) . ':L' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('M' . ($headerRow + 1) . ':M' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('N' . ($headerRow + 1) . ':R' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('S' . ($headerRow + 1) . ':S' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('T' . ($headerRow + 1) . ':U' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Number formats
            $sheet->getStyle('L' . ($headerRow + 1) . ':L' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('N' . ($headerRow + 1) . ':R' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0');

            // Zebra striping & Borders
            for ($r = $headerRow + 1; $r <= $lastDataRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle('A' . $r . ':U' . $r)->getFill()->applyFromArray([
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => self::COLOR_ROW_EVEN],
                    ]);
                }
            }

            $tableRange = 'A' . ($headerRow + 1) . ':U' . $lastDataRow;
            $sheet->getStyle($tableRange)->applyFromArray([
                'font' => ['size' => 9],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => self::COLOR_BORDER],
                    ],
                ],
            ]);
        }

        // ── 6. Baris Total Akumulasi (Grand Total) ──────────────────────
        $totalRow = $currentRow;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL AKUMULASI PENJUALAN');
        $sheet->mergeCells('A' . $totalRow . ':K' . $totalRow);

        $sheet->setCellValue('L' . $totalRow, $grandQty);
        $sheet->setCellValue('N' . $totalRow, '-');
        $sheet->setCellValue('O' . $totalRow, $grandBruto);
        $sheet->setCellValue('P' . $totalRow, $grandDiskon);
        $sheet->setCellValue('Q' . $totalRow, $grandPpn);
        $sheet->setCellValue('R' . $totalRow, $grandTagihan);

        $sheet->getRowDimension($totalRow)->setRowHeight(22);
        $totalRange = 'A' . $totalRow . ':U' . $totalRow;
        $sheet->getStyle($totalRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FF0F172A'],
                'size'  => 9,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLOR_TOTAL_BG],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF94A3B8'],
                ],
            ],
        ]);

        $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('L' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('O' . $totalRow . ':R' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('O' . $totalRow . ':R' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('R' . $totalRow)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLOR_GREEN));

        // ── 7. Page Setup & Gridlines ───────────────────────────────────
        $sheet->setShowGridLines(true);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        return $spreadsheet;
    }
}
