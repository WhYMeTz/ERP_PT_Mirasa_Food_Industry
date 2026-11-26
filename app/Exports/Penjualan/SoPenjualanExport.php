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

class SoPenjualanExport
{
    protected Collection $orders;
    protected ?string $search;
    protected ?string $status;
    protected array $filters;
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
        ?string $status,
        array $filters,
        string $printedBy,
        string $printedAt
    ) {
        $this->orders    = $orders;
        $this->search    = $search;
        $this->status    = $status;
        $this->filters   = $filters;
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
        $sheet->setTitle('Rekap PO Penjualan');

        // ── 1. Lebar Kolom ──────────────────────────────────────────────
        $colWidths = [
            'A' => 6,   // No
            'B' => 18,  // No. PO Penjualan
            'C' => 18,  // Ref PO Customer
            'D' => 13,  // Tgl PO
            'E' => 13,  // Estimasi Kirim
            'F' => 16,  // Kode Customer
            'G' => 26,  // Nama Customer
            'H' => 16,  // TOP / Termin
            'I' => 16,  // Kode Barang
            'J' => 30,  // Nama Produk
            'K' => 13,  // Qty Pesanan
            'L' => 13,  // Qty Terkirim
            'M' => 13,  // Sisa Qty
            'N' => 10,  // Satuan
            'O' => 16,  // Harga Satuan (Rp)
            'P' => 18,  // Subtotal Bruto (Rp)
            'Q' => 15,  // Diskon (Rp)
            'R' => 15,  // PPN (Rp)
            'S' => 20,  // Total Tagihan Item (Rp)
            'T' => 18,  // Status Pengiriman
            'U' => 24,  // No. Surat Jalan
            'V' => 24,  // No. Faktur
            'W' => 32,  // Catatan
        ];

        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // ── 2. Header Dokumen & Identitas Perusahaan ────────────────────
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY');
        $sheet->setCellValue('A2', 'REKAPITULASI PESAN ORDER (PO) PENJUALAN PRODUK JADI & SETENGAH JADI');
        $sheet->setCellValue('A3', 'Kawasan Industri Candi Blok XI No. 8, Krapyak, Semarang &bull; Divisi Penjualan, Keuangan & Logistik');

        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF047857'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        // ── 3. Panel Metadata Filter ────────────────────────────────────
        $metaRow = 5;
        $sheet->setCellValue('A' . $metaRow, 'Parameter Filter Aktif:');
        $sheet->getStyle('A' . $metaRow)->getFont()->setBold(true)->setSize(9);

        $periodeText = 'Semua Tanggal';
        $dFrom = $this->filters['date_from'] ?? null;
        $dTo   = $this->filters['date_to'] ?? null;
        if ($dFrom && $dTo) {
            $periodeText = date('d/m/Y', strtotime($dFrom)) . ' s/d ' . date('d/m/Y', strtotime($dTo));
        } elseif ($dFrom) {
            $periodeText = 'Sejak ' . date('d/m/Y', strtotime($dFrom));
        } elseif ($dTo) {
            $periodeText = 'Hingga ' . date('d/m/Y', strtotime($dTo));
        }

        $statusText = $this->status ?: 'Semua Status';
        $metaItems = [
            ['Periode Tanggal:', $periodeText, 'Kata Kunci Pencarian:', $this->search ?: '(Semua Data)'],
            ['Status Pesanan:', $statusText, 'Total Dokumen PO:', $this->orders->count() . ' Dokumen'],
            ['Dicetak Oleh / Pada:', $this->printedBy . ' | ' . $this->printedAt, 'Filter Spesifik:', $this->formatSpecificFilters()],
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
        $headerRow = $metaRow + 5;
        $headers = [
            'A' => 'NO',
            'B' => 'NO. PO PENJUALAN',
            'C' => 'REF PO CUSTOMER',
            'D' => 'TGL PO',
            'E' => 'ESTIMASI KIRIM',
            'F' => 'KODE CUSTOMER',
            'G' => 'NAMA CUSTOMER',
            'H' => 'TERMIN (TOP)',
            'I' => 'KODE BARANG',
            'J' => 'NAMA PRODUK',
            'K' => 'QTY PESAN',
            'L' => 'QTY KIRIM',
            'M' => 'SISA QTY',
            'N' => 'SATUAN',
            'O' => 'HARGA SATUAN (RP)',
            'P' => 'SUBTOTAL BRUTO (RP)',
            'Q' => 'DISKON ITEM (RP)',
            'R' => 'PPN (RP)',
            'S' => 'TOTAL TAGIHAN (RP)',
            'T' => 'STATUS PENGIRIMAN',
            'U' => 'SURAT JALAN TERKAIT',
            'V' => 'FAKTUR TERKAIT',
            'W' => 'CATATAN / KETERANGAN',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
        }

        $sheet->getRowDimension($headerRow)->setRowHeight(24);
        $headerRange = 'A' . $headerRow . ':W' . $headerRow;
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

        $grandPesanQty = 0;
        $grandKirimQty = 0;
        $grandSisaQty  = 0;
        $grandBruto    = 0;
        $grandDiskon   = 0;
        $grandPpn      = 0;
        $grandTagihan  = 0;

        foreach ($this->orders as $order) {
            $details = $order->details;
            $tglSo = $order->so_tgl ? date('d/m/Y', strtotime($order->so_tgl)) : '-';
            $tglKirim = $order->tgl_kirim_estimasi ? date('d/m/Y', strtotime($order->tgl_kirim_estimasi)) : '-';
            $customer = $order->customer;
            $custCd = $customer?->customer_cd ?: '-';
            $custNm = $customer?->customer_nm ?: 'Pelanggan Umum';
            $top = $order->top_txt ?: '-';
            $refPo = $order->customer_po_no ?: '-';
            $catatan = $order->catatan_txt ?: '-';

            // Ambil daftar surat jalan & faktur
            $validSjs = $order->pengeluaran ? $order->pengeluaran->where('status_cd', '!=', 'BATAL') : collect();
            $sjList = $validSjs->pluck('pengeluaran_no')->filter()->implode(', ') ?: ($order->surat_jalan_no ?: '-');

            $fakturList = $validSjs->map(fn($p) => $p->faktur?->faktur_no)->filter()->unique()->implode(', ') ?: ($order->faktur_no ?: '-');

            $statusLabel = $this->getStatusLabel($order->status_cd);

            if ($details->isEmpty()) {
                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $order->so_no);
                $sheet->setCellValue('C' . $currentRow, $refPo);
                $sheet->setCellValue('D' . $currentRow, $tglSo);
                $sheet->setCellValue('E' . $currentRow, $tglKirim);
                $sheet->setCellValue('F' . $currentRow, $custCd);
                $sheet->setCellValue('G' . $currentRow, $custNm);
                $sheet->setCellValue('H' . $currentRow, $top);
                $sheet->setCellValue('I' . $currentRow, '-');
                $sheet->setCellValue('J' . $currentRow, '-');
                $sheet->setCellValue('K' . $currentRow, 0);
                $sheet->setCellValue('L' . $currentRow, 0);
                $sheet->setCellValue('M' . $currentRow, 0);
                $sheet->setCellValue('N' . $currentRow, '-');
                $sheet->setCellValue('O' . $currentRow, 0);
                $sheet->setCellValue('P' . $currentRow, (float) $order->subtotal_bruto);
                $sheet->setCellValue('Q' . $currentRow, (float) $order->diskon_total);
                $sheet->setCellValue('R' . $currentRow, (float) $order->ppn_nominal);
                $sheet->setCellValue('S' . $currentRow, (float) $order->total_tagihan);
                $sheet->setCellValue('T' . $currentRow, $statusLabel);
                $sheet->setCellValue('U' . $currentRow, $sjList);
                $sheet->setCellValue('V' . $currentRow, $fakturList);
                $sheet->setCellValue('W' . $currentRow, $catatan);

                $grandBruto   += (float) $order->subtotal_bruto;
                $grandDiskon  += (float) $order->diskon_total;
                $grandPpn     += (float) $order->ppn_nominal;
                $grandTagihan += (float) $order->total_tagihan;

                $currentRow++;
                continue;
            }

            foreach ($details as $detail) {
                $barang = $detail->barang;
                $pesanQty = (float) $detail->pesan_qty;
                $kirimQty = (float) $detail->kirim_qty;
                $sisaQty  = max(0, $pesanQty - $kirimQty);
                $satuan = $detail->satuan ?: ($barang?->satuanDasar?->satuan_nm ?? 'Karton');
                $harga = (float) $detail->harga_satuan;
                $subtotal = round($pesanQty * $harga, 2);
                $diskonItem = (float) ($detail->diskon_nominal ?? 0);
                $ppnItem = (float) ($detail->ppn_nominal ?? 0);
                $tagihanItem = (float) ($detail->subtotal_tagihan ?? ($subtotal - $diskonItem + $ppnItem));

                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $order->so_no);
                $sheet->setCellValue('C' . $currentRow, $refPo);
                $sheet->setCellValue('D' . $currentRow, $tglSo);
                $sheet->setCellValue('E' . $currentRow, $tglKirim);
                $sheet->setCellValue('F' . $currentRow, $custCd);
                $sheet->setCellValue('G' . $currentRow, $custNm);
                $sheet->setCellValue('H' . $currentRow, $top);
                $sheet->setCellValue('I' . $currentRow, $barang?->barang_cd ?: '-');
                $sheet->setCellValue('J' . $currentRow, $barang?->barang_nm ?: ($detail->barang_nm ?? '-'));
                $sheet->setCellValue('K' . $currentRow, $pesanQty);
                $sheet->setCellValue('L' . $currentRow, $kirimQty);
                $sheet->setCellValue('M' . $currentRow, $sisaQty);
                $sheet->setCellValue('N' . $currentRow, $satuan);
                $sheet->setCellValue('O' . $currentRow, $harga);
                $sheet->setCellValue('P' . $currentRow, $subtotal);
                $sheet->setCellValue('Q' . $currentRow, $diskonItem);
                $sheet->setCellValue('R' . $currentRow, $ppnItem);
                $sheet->setCellValue('S' . $currentRow, $tagihanItem);
                $sheet->setCellValue('T' . $currentRow, $statusLabel);
                $sheet->setCellValue('U' . $currentRow, $sjList);
                $sheet->setCellValue('V' . $currentRow, $fakturList);
                $sheet->setCellValue('W' . $currentRow, $catatan);

                $grandPesanQty += $pesanQty;
                $grandKirimQty += $kirimQty;
                $grandSisaQty  += $sisaQty;
                $grandBruto    += $subtotal;
                $grandDiskon   += $diskonItem;
                $grandPpn      += $ppnItem;
                $grandTagihan  += $tagihanItem;

                $currentRow++;
            }
        }

        // Format styling isi tabel
        $lastDataRow = $currentRow - 1;
        if ($lastDataRow >= $headerRow + 1) {
            // Alignment
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . ($headerRow + 1) . ':C' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . ($headerRow + 1) . ':E' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . ($headerRow + 1) . ':H' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('I' . ($headerRow + 1) . ':I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . ($headerRow + 1) . ':J' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('K' . ($headerRow + 1) . ':M' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('N' . ($headerRow + 1) . ':N' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('O' . ($headerRow + 1) . ':S' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('T' . ($headerRow + 1) . ':T' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('U' . ($headerRow + 1) . ':W' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Number formats
            $sheet->getStyle('K' . ($headerRow + 1) . ':M' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('O' . ($headerRow + 1) . ':S' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0');

            // Zebra striping
            for ($r = $headerRow + 1; $r <= $lastDataRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle('A' . $r . ':W' . $r)->getFill()->applyFromArray([
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => self::COLOR_ROW_EVEN],
                    ]);
                }
            }

            $tableRange = 'A' . ($headerRow + 1) . ':W' . $lastDataRow;
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
        $sheet->setCellValue('A' . $totalRow, 'TOTAL AKUMULASI PO PENJUALAN');
        $sheet->mergeCells('A' . $totalRow . ':J' . $totalRow);

        $sheet->setCellValue('K' . $totalRow, $grandPesanQty);
        $sheet->setCellValue('L' . $totalRow, $grandKirimQty);
        $sheet->setCellValue('M' . $totalRow, $grandSisaQty);
        $sheet->setCellValue('O' . $totalRow, '-');
        $sheet->setCellValue('P' . $totalRow, $grandBruto);
        $sheet->setCellValue('Q' . $totalRow, $grandDiskon);
        $sheet->setCellValue('R' . $totalRow, $grandPpn);
        $sheet->setCellValue('S' . $totalRow, $grandTagihan);

        $sheet->getRowDimension($totalRow)->setRowHeight(22);
        $totalRange = 'A' . $totalRow . ':W' . $totalRow;
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
        $sheet->getStyle('K' . $totalRow . ':M' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('K' . $totalRow . ':M' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');

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

    protected function getStatusLabel(string $statusCd): string
    {
        return match ($statusCd) {
            'DRAFT'      => 'Draft',
            'APPROVED'   => 'Menunggu Kirim',
            'PROCESSING' => 'Dalam Proses',
            'PARTIAL'    => 'Kirim Sebagian',
            'COMPLETED'  => 'Terkirim (100%)',
            'CANCELLED'  => 'Dibatalkan',
            default      => $statusCd,
        };
    }

    protected function formatSpecificFilters(): string
    {
        $parts = [];
        if (!empty($this->filters['barang_cd'])) $parts[] = 'Kd Barang: ' . $this->filters['barang_cd'];
        if (!empty($this->filters['barang_nm'])) $parts[] = 'Nm Barang: ' . $this->filters['barang_nm'];
        if (!empty($this->filters['customer_cd'])) $parts[] = 'Kd Cust: ' . $this->filters['customer_cd'];
        if (!empty($this->filters['customer_nm'])) $parts[] = 'Nm Cust: ' . $this->filters['customer_nm'];

        return empty($parts) ? '(Tidak Ada)' : implode(' | ', $parts);
    }
}
