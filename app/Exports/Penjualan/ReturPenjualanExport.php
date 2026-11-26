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

class ReturPenjualanExport
{
    protected Collection $returns;
    protected ?string $search;
    protected array $filters;
    protected ?string $customerNm;
    protected string $printedBy;
    protected string $printedAt;

    const COLOR_HEADER_BG = 'FF0F172A'; // Navy Mirasa
    const COLOR_HEADER_FG = 'FFFFFFFF';
    const COLOR_ROW_EVEN  = 'FFF8FAFC';
    const COLOR_TOTAL_BG  = 'FFE2E8F0';
    const COLOR_BORDER    = 'FFCBD5E1';
    const COLOR_GREEN     = 'FF047857';
    const COLOR_RED       = 'FFDC2626';

    public function __construct(
        Collection $returns,
        ?string $search,
        array $filters,
        ?string $customerNm,
        string $printedBy,
        string $printedAt
    ) {
        $this->returns    = $returns;
        $this->search     = $search;
        $this->filters    = $filters;
        $this->customerNm = $customerNm;
        $this->printedBy  = $printedBy;
        $this->printedAt  = $printedAt;
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
        $sheet->setTitle('Rekap Retur Penjualan');

        // ── 1. Lebar Kolom Baku ──────────────────────────────────────────
        $colWidths = [
            'A' => 6,   // No
            'B' => 22,  // No. Retur
            'C' => 14,  // Tanggal Retur
            'D' => 20,  // Ref. PO Penjualan
            'E' => 18,  // Ref. Faktur
            'F' => 18,  // Ref. Surat Jalan
            'G' => 16,  // Kode Customer
            'H' => 30,  // Nama Mitra Customer
            'I' => 22,  // Tindakan Komersial
            'J' => 16,  // Kode Barang
            'K' => 28,  // Nama Produk Keripik
            'L' => 18,  // No. Batch Produksi
            'M' => 22,  // Kondisi / Alasan
            'N' => 20,  // Disposisi Fisik
            'O' => 14,  // Qty Retur
            'P' => 12,  // Satuan
            'Q' => 18,  // Harga Satuan (Rp)
            'R' => 20,  // Subtotal Retur (Rp)
            'S' => 16,  // Status Retur
            'T' => 30,  // Catatan
        ];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // ── 2. Kop Surat Laporan Resmi ─────────────────────────────────
        $sheet->mergeCells('A1:T1');
        $sheet->setCellValue('A1', 'PT MIRASA FOOD INDUSTRY');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->mergeCells('A2:T2');
        $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI RETUR PENJUALAN (CUSTOMER RETURNS)');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF0369A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Keterangan Filter Aktif
        $filterTexts = [];
        if (!empty($this->search)) {
            $filterTexts[] = 'Pencarian: "' . $this->search . '"';
        }
        if (!empty($this->customerNm)) {
            $filterTexts[] = 'Customer: ' . $this->customerNm;
        }
        if (!empty($this->filters['tindakan_cd'])) {
            $filterTexts[] = 'Tindakan: ' . ($this->filters['tindakan_cd'] === 'POTONG_TAGIHAN' ? 'Potong Tagihan' : 'Tukar Guling');
        }
        if (!empty($this->filters['status_cd'])) {
            $filterTexts[] = 'Status: ' . ($this->filters['status_cd'] === 'CONFIRMED' ? 'Terkonfirmasi' : 'Dibatalkan (Void)');
        }
        if (empty($filterTexts)) {
            $filterTexts[] = 'Parameter: Seluruh Data';
        }

        $infoLeft  = implode(' | ', $filterTexts);
        $infoRight = 'Dicetak: ' . $this->printedAt . ' oleh ' . $this->printedBy;

        $sheet->mergeCells('A4:L4');
        $sheet->setCellValue('A4', $infoLeft);
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['size' => 9, 'italic' => true, 'color' => ['argb' => 'FF475569']],
        ]);

        $sheet->mergeCells('M4:T4');
        $sheet->setCellValue('M4', $infoRight);
        $sheet->getStyle('M4')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => 'FF475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        $sheet->getRowDimension(5)->setRowHeight(6);

        // ── 3. Table Headers ───────────────────────────────────────────
        $headerRow = 6;
        $headers = [
            'A' => 'NO',
            'B' => 'NO. RETUR',
            'C' => 'TANGGAL',
            'D' => 'REF. NO. SO',
            'E' => 'REF. NO. FAKTUR',
            'F' => 'REF. SURAT JALAN',
            'G' => 'KODE CUSTOMER',
            'H' => 'NAMA MITRA CUSTOMER',
            'I' => 'TINDAKAN KOMERSIAL',
            'J' => 'KODE BARANG',
            'K' => 'NAMA PRODUK JADI',
            'L' => 'NO. BATCH PRODUKSI',
            'M' => 'KONDISI / ALASAN',
            'N' => 'DISPOSISI FISIK',
            'O' => 'QTY RETUR',
            'P' => 'SATUAN',
            'Q' => 'HARGA SATUAN (RP)',
            'R' => 'SUBTOTAL RETUR (RP)',
            'S' => 'STATUS RETUR',
            'T' => 'CATATAN KHUSUS',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
        }

        $sheet->getStyle('A' . $headerRow . ':T' . $headerRow)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        // ── 4. Render Data Rows ─────────────────────────────────────────
        $currentRow = $headerRow + 1;
        $no = 1;
        $totalQtySum = 0;
        $totalNilaiSum = 0;

        foreach ($this->returns as $retur) {
            $soNo = $retur->order?->so_no ?? '-';
            $fakturNo = $retur->order?->faktur_no ?: ($retur->order?->faktur?->first()?->faktur_no ?: '-');
            $sjNo = $retur->order?->surat_jalan_no ?: ($retur->order?->pengeluaran?->first()?->pengeluaran_no ?: '-');
            $custCd = $retur->customer?->customer_cd ?? '-';
            $custNm = $retur->customer?->customer_nm ?? ($retur->order?->customer?->customer_nm ?? '-');
            $tglStr = $retur->retur_tgl ? $retur->retur_tgl->format('d/m/Y') : '-';
            $tindakanStr = $retur->tindakan_cd === 'POTONG_TAGIHAN' ? 'Potong Tagihan' : 'Tukar Guling';
            $statusStr = $retur->status_cd === 'CONFIRMED' ? 'Terkonfirmasi' : 'Dibatalkan (Void)';

            if ($retur->details->isEmpty()) {
                // Baris header tanpa detail
                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $retur->retur_no);
                $sheet->setCellValue('C' . $currentRow, $tglStr);
                $sheet->setCellValue('D' . $currentRow, $soNo);
                $sheet->setCellValue('E' . $currentRow, $fakturNo);
                $sheet->setCellValue('F' . $currentRow, $sjNo);
                $sheet->setCellValue('G' . $currentRow, $custCd);
                $sheet->setCellValue('H' . $currentRow, $custNm);
                $sheet->setCellValue('I' . $currentRow, $tindakanStr);
                $sheet->setCellValue('J' . $currentRow, '-');
                $sheet->setCellValue('K' . $currentRow, '-');
                $sheet->setCellValue('L' . $currentRow, '-');
                $sheet->setCellValue('M' . $currentRow, $retur->alasan_txt ?: '-');
                $sheet->setCellValue('N' . $currentRow, '-');
                $sheet->setCellValue('O' . $currentRow, (float)$retur->total_qty);
                $sheet->setCellValue('P' . $currentRow, 'Karton');
                $sheet->setCellValue('Q' . $currentRow, 0);
                $sheet->setCellValue('R' . $currentRow, (float)$retur->total_nilai);
                $sheet->setCellValue('S' . $currentRow, $statusStr);
                $sheet->setCellValue('T' . $currentRow, $retur->catatan_txt ?? '');

                $totalQtySum += (float)$retur->total_qty;
                $totalNilaiSum += (float)$retur->total_nilai;
                $currentRow++;
                continue;
            }

            foreach ($retur->details as $dtl) {
                $batchNo = $dtl->production_batch_no ?: ($dtl->batch_no && !str_starts_with($dtl->batch_no, 'RET-') ? $dtl->batch_no : '-');
                $qty = (float) $dtl->qty_retur;
                $harga = (float) $dtl->harga_satuan;
                $subtotal = (float) $dtl->subtotal_nilai;

                $disposisi = match ($dtl->perlakuan_cd) {
                    'DIMUSNAHKAN' => 'Dimusnahkan Resmi',
                    'CAMPUR'      => 'KW2: ' . number_format((float)$dtl->qty_masuk_persediaan, 0) . ' / Musnah: ' . number_format((float)$dtl->qty_dimusnahkan, 0),
                    default       => 'Masuk Sortir KW 2',
                };

                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $retur->retur_no);
                $sheet->setCellValue('C' . $currentRow, $tglStr);
                $sheet->setCellValue('D' . $currentRow, $soNo);
                $sheet->setCellValue('E' . $currentRow, $fakturNo);
                $sheet->setCellValue('F' . $currentRow, $sjNo);
                $sheet->setCellValue('G' . $currentRow, $custCd);
                $sheet->setCellValue('H' . $currentRow, $custNm);
                $sheet->setCellValue('I' . $currentRow, $tindakanStr);
                $sheet->setCellValue('J' . $currentRow, $dtl->barang?->barang_cd ?? '-');
                $sheet->setCellValue('K' . $currentRow, $dtl->barang?->barang_nm ?? '-');
                $sheet->setCellValue('L' . $currentRow, $batchNo);
                $sheet->setCellValue('M' . $currentRow, $dtl->kondisi_label);
                $sheet->setCellValue('N' . $currentRow, $disposisi);
                $sheet->setCellValue('O' . $currentRow, $qty);
                $sheet->setCellValue('P' . $currentRow, $dtl->satuan ?: ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton'));
                $sheet->setCellValue('Q' . $currentRow, $harga);
                $sheet->setCellValue('R' . $currentRow, $subtotal);
                $sheet->setCellValue('S' . $currentRow, $statusStr);
                $sheet->setCellValue('T' . $currentRow, $dtl->catatan_txt ?: ($retur->alasan_txt ?? ''));

                if ($retur->status_cd === 'CONFIRMED') {
                    $totalQtySum += $qty;
                    $totalNilaiSum += $subtotal;
                }

                // Format & Alignments per baris
                $isEven = ($currentRow % 2 === 0);
                if ($isEven) {
                    $sheet->getStyle('A' . $currentRow . ':T' . $currentRow)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_ROW_EVEN]],
                    ]);
                }

                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('J' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('L' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('P' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('S' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('O' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('Q' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('R' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle('O' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('Q' . $currentRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle('R' . $currentRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');

                // Color status text
                if ($retur->status_cd === 'CONFIRMED') {
                    $sheet->getStyle('S' . $currentRow)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLOR_GREEN))->setBold(true);
                } else {
                    $sheet->getStyle('S' . $currentRow)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLOR_RED))->setItalic(true);
                }

                $sheet->getStyle('A' . $currentRow . ':T' . $currentRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                    'font'    => ['size' => 9],
                ]);

                $currentRow++;
            }
        }

        // ── 5. Total Row ───────────────────────────────────────────────
        $sheet->mergeCells('A' . $currentRow . ':N' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'TOTAL KESELURUHAN (STATUS TERKONFIRMASI):');
        $sheet->setCellValue('O' . $currentRow, $totalQtySum);
        $sheet->setCellValue('P' . $currentRow, 'Karton');
        $sheet->setCellValue('Q' . $currentRow, '');
        $sheet->setCellValue('R' . $currentRow, $totalNilaiSum);
        $sheet->setCellValue('S' . $currentRow, '');
        $sheet->setCellValue('T' . $currentRow, '');

        $sheet->getStyle('A' . $currentRow . ':T' . $currentRow)->applyFromArray([
            'font'    => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
            'borders' => [
                'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF0F172A']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF0F172A']],
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]],
            ],
        ]);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('O' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('P' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('R' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('O' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('R' . $currentRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getRowDimension($currentRow)->setRowHeight(22);

        return $spreadsheet;
    }
}
