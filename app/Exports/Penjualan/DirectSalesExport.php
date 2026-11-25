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

class DirectSalesExport
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
        $sheet->setTitle('Penjualan Langsung Pabrik');

        // ── 1. Lebar Kolom ──────────────────────────────────────────────
        $colWidths = [
            'A' => 6,   // No
            'B' => 18,  // No. Transaksi
            'C' => 18,  // No. Faktur
            'D' => 18,  // No. Surat Jalan
            'E' => 14,  // Tanggal
            'F' => 10,  // Waktu
            'G' => 24,  // Pelanggan / Customer
            'H' => 20,  // Pembeli / Sales
            'I' => 16,  // Nopol Kendaraan
            'J' => 14,  // Metode Bayar
            'K' => 16,  // Kode Produk
            'L' => 30,  // Nama Produk Keripik / Olahan
            'M' => 12,  // Qty Terjual
            'N' => 10,  // Satuan
            'O' => 16,  // Harga Satuan (Rp)
            'P' => 18,  // Subtotal Bruto (Rp)
            'Q' => 16,  // Diskon Item (Rp)
            'R' => 16,  // PPN (Rp)
            'S' => 20,  // Total Tagihan (Rp)
            'T' => 16,  // Status
            'U' => 16,  // Kasir / Petugas
            'V' => 35,  // Catatan / Keterangan
        ];

        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // ── 2. Header Dokumen & Identitas Perusahaan ────────────────────
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY');
        $sheet->setCellValue('A2', 'REKAPITULASI PENJUALAN LANGSUNG GUDANG PABRIK (DIRECT SALES - NON-PO)');
        $sheet->setCellValue('A3', 'Kawasan Industri Candi Blok XI No. 8, Krapyak, Semarang &bull; Divisi Penjualan & Kasir Pabrik');

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
            'F' => 'WAKTU',
            'G' => 'PELANGGAN / CUSTOMER',
            'H' => 'PEMBELI / SALES',
            'I' => 'NOPOL KENDARAAN',
            'J' => 'METODE BAYAR',
            'K' => 'KODE PRODUK',
            'L' => 'NAMA PRODUK',
            'M' => 'QTY TERJUAL',
            'N' => 'SATUAN',
            'O' => 'HARGA SATUAN (RP)',
            'P' => 'SUBTOTAL BRUTO (RP)',
            'Q' => 'DISKON ITEM (RP)',
            'R' => 'PPN (RP)',
            'S' => 'TOTAL TAGIHAN (RP)',
            'T' => 'STATUS',
            'U' => 'KASIR',
            'V' => 'CATATAN / KETERANGAN',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
        }

        $sheet->getRowDimension($headerRow)->setRowHeight(24);
        $headerRange = 'A' . $headerRow . ':V' . $headerRow;
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
            $waktu = $order->created_dt ? date('H:i', strtotime($order->created_dt)) : '-';
            $customerNm = $order->customer?->customer_nm ?? 'Pelanggan Umum';
            $pembeli = $order->supir_nm ?: '-';
            $caraBayar = $order->top_txt ?: 'TUNAI';
            $nopol = $order->nopol_kendaraan ?: '-';
            $kasir = $order->created_by ?: 'Kasir';
            $catatan = $order->catatan_txt ?: '-';

            $firstPengeluaran = $order->pengeluaran ? $order->pengeluaran->first() : null;
            $firstFaktur = $order->faktur;
            $fakturNo = $firstFaktur?->faktur_no ?: ($order->faktur_no ?: '-');
            $sjNo = $firstPengeluaran?->pengeluaran_no ?: ($order->surat_jalan_no ?: '-');

            if ($details->isEmpty()) {
                // Baris tanpa detail
                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $order->so_no);
                $sheet->setCellValue('C' . $currentRow, $fakturNo);
                $sheet->setCellValue('D' . $currentRow, $sjNo);
                $sheet->setCellValue('E' . $currentRow, $tgl);
                $sheet->setCellValue('F' . $currentRow, $waktu);
                $sheet->setCellValue('G' . $currentRow, $customerNm);
                $sheet->setCellValue('H' . $currentRow, $pembeli);
                $sheet->setCellValue('I' . $currentRow, $nopol);
                $sheet->setCellValue('J' . $currentRow, $caraBayar);
                $sheet->setCellValue('K' . $currentRow, '-');
                $sheet->setCellValue('L' . $currentRow, '-');
                $sheet->setCellValue('M' . $currentRow, 0);
                $sheet->setCellValue('N' . $currentRow, '-');
                $sheet->setCellValue('O' . $currentRow, 0);
                $sheet->setCellValue('P' . $currentRow, (float) $order->subtotal_bruto);
                $sheet->setCellValue('Q' . $currentRow, (float) $order->diskon_total);
                $sheet->setCellValue('R' . $currentRow, (float) $order->ppn_nominal);
                $sheet->setCellValue('S' . $currentRow, (float) $order->total_tagihan);
                $sheet->setCellValue('T' . $currentRow, 'Lunas & Selesai');
                $sheet->setCellValue('U' . $currentRow, $kasir);
                $sheet->setCellValue('V' . $currentRow, $catatan);

                $grandBruto   += (float) $order->subtotal_bruto;
                $grandDiskon  += (float) $order->diskon_total;
                $grandPpn     += (float) $order->ppn_nominal;
                $grandTagihan += (float) $order->total_tagihan;

                $currentRow++;
                continue;
            }

            foreach ($details as $detail) {
                $barang = $detail->barang;
                $qty = (float) ($detail->pesan_qty ?: $detail->qty);
                $satuan = $detail->satuan ?: ($barang?->satuanDasar?->satuan_nm ?? 'Karton');
                $harga = (float) $detail->harga_satuan;
                $subtotal = round($qty * $harga, 2);
                $diskonItem = (float) ($detail->diskon_nominal ?? 0);
                $ppnItem = (float) ($detail->ppn_nominal ?? 0);
                $tagihanItem = (float) ($detail->subtotal_tagihan ?? ($subtotal - $diskonItem + $ppnItem));

                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $order->so_no);
                $sheet->setCellValue('C' . $currentRow, $fakturNo);
                $sheet->setCellValue('D' . $currentRow, $sjNo);
                $sheet->setCellValue('E' . $currentRow, $tgl);
                $sheet->setCellValue('F' . $currentRow, $waktu);
                $sheet->setCellValue('G' . $currentRow, $customerNm);
                $sheet->setCellValue('H' . $currentRow, $pembeli);
                $sheet->setCellValue('I' . $currentRow, $nopol);
                $sheet->setCellValue('J' . $currentRow, $caraBayar);
                $sheet->setCellValue('K' . $currentRow, $barang?->barang_cd ?: '-');
                $sheet->setCellValue('L' . $currentRow, $barang?->barang_nm ?: ($detail->barang_nm ?? '-'));
                $sheet->setCellValue('M' . $currentRow, $qty);
                $sheet->setCellValue('N' . $currentRow, $satuan);
                $sheet->setCellValue('O' . $currentRow, $harga);
                $sheet->setCellValue('P' . $currentRow, $subtotal);
                $sheet->setCellValue('Q' . $currentRow, $diskonItem);
                $sheet->setCellValue('R' . $currentRow, $ppnItem);
                $sheet->setCellValue('S' . $currentRow, $tagihanItem);
                $sheet->setCellValue('T' . $currentRow, 'Lunas & Selesai');
                $sheet->setCellValue('U' . $currentRow, $kasir);
                $sheet->setCellValue('V' . $currentRow, $catatan);

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
            $sheet->getStyle('E' . ($headerRow + 1) . ':F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . ($headerRow + 1) . ':H' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('I' . ($headerRow + 1) . ':K' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L' . ($headerRow + 1) . ':L' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('M' . ($headerRow + 1) . ':M' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('N' . ($headerRow + 1) . ':N' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('O' . ($headerRow + 1) . ':S' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('T' . ($headerRow + 1) . ':T' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('U' . ($headerRow + 1) . ':V' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Number formats
            $sheet->getStyle('M' . ($headerRow + 1) . ':M' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('O' . ($headerRow + 1) . ':S' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0');

            // Zebra striping
            for ($r = $headerRow + 1; $r <= $lastDataRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle('A' . $r . ':V' . $r)->getFill()->applyFromArray([
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => self::COLOR_ROW_EVEN],
                    ]);
                }
            }

            $tableRange = 'A' . ($headerRow + 1) . ':V' . $lastDataRow;
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
        $sheet->setCellValue('A' . $totalRow, 'TOTAL AKUMULASI PENJUALAN LANGSUNG');
        $sheet->mergeCells('A' . $totalRow . ':L' . $totalRow);

        $sheet->setCellValue('M' . $totalRow, $grandQty);
        $sheet->setCellValue('O' . $totalRow, '-');
        $sheet->setCellValue('P' . $totalRow, $grandBruto);
        $sheet->setCellValue('Q' . $totalRow, $grandDiskon);
        $sheet->setCellValue('R' . $totalRow, $grandPpn);
        $sheet->setCellValue('S' . $totalRow, $grandTagihan);

        $sheet->getRowDimension($totalRow)->setRowHeight(22);
        $totalRange = 'A' . $totalRow . ':V' . $totalRow;
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
        $sheet->getStyle('M' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('M' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('P' . $totalRow . ':S' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('P' . $totalRow . ':S' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('S' . $totalRow)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLOR_GREEN));

        // ── 7. Page Setup & Gridlines ───────────────────────────────────
        $sheet->setShowGridLines(true);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        return $spreadsheet;
    }
}
