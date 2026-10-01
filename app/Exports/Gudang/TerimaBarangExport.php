<?php

namespace App\Exports\Gudang;

use App\Models\Gudang\DatTerimaDtl;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TerimaBarangExport
{
    protected $items;
    protected ?string $gudangNm;
    protected ?string $search;
    protected string  $printedBy;
    protected string  $printedAt;

    // Warna tema korporat (Teal Mirasa)
    const COLOR_HEADER_BG   = 'FF134E5E'; // Dark Teal
    const COLOR_HEADER_FG   = 'FFFFFFFF';
    const COLOR_SUBHEADER   = 'FFECFDF5';
    const COLOR_ROW_EVEN    = 'FFFAFAFA';
    const COLOR_TOTAL_BG    = 'FFE2E8F0';
    const COLOR_BORDER      = 'FFCBD5E1';
    const COLOR_GREEN       = 'FF047857';
    const COLOR_RED         = 'FFDC2626';
    const COLOR_BLUE        = 'FF0284C7';
    const COLOR_DARK        = 'FF0F172A';
    const COLOR_MUTED       = 'FF64748B';

    public function __construct($items, ?string $gudangNm, ?string $search, string $printedBy, string $printedAt)
    {
        $this->items     = $items;
        $this->gudangNm  = $gudangNm;
        $this->search    = $search;
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
        $sheet->setTitle('Rekap Barang Masuk');

        // ── Lebar Kolom ──────────────────────────────────────────────
        // Kolom A-J: No. GRN diletakkan sebelum Tanggal, diikuti 8 kolom gambar operasional
        // Kolom K-T: Kolom Informasi Pelengkap Audit & Warehouse ERP
        $colWidths = [
            'A' => 18,   // No. GRN (digeser sebelum Tanggal)
            'B' => 14,   // Tanggal (contoh: 1-Sep-2026)
            'C' => 18,   // Kode Batch (contoh: MS-25082026)
            'D' => 16,   // Kode Barang (contoh: MSW00G-BP2)
            'E' => 28,   // Nama Barang (contoh: MINYAK SAWIT)
            'F' => 20,   // Jenis (contoh: BAHAN PENOLONG)
            'G' => 22,   // Keterangan (contoh: SALDO AWAL / PO)
            'H' => 14,   // Qty Masuk (contoh: 10.844,73)
            'I' => 18,   // Harga Satuan (contoh: Rp 17.748,64)
            'J' => 22,   // Total Harga (contoh: Rp 192.479.162,68)
            // Kolom Pelengkap
            'K' => 10,   // Satuan
            'L' => 20,   // Gudang Simpan
            'M' => 26,   // Supplier Pengirim
            'N' => 18,   // Ref. PO
            'O' => 14,   // Tgl Expired
            'P' => 8,    // Grade
            'Q' => 10,   // Diskon %
            'R' => 16,   // Potongan Item (Rp)
            'S' => 12,   // Pajak
            'T' => 22,   // Total Tagihan (Rp)
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Baris 1: Judul Perusahaan ───────────────────────────────
        $sheet->mergeCells('A1:T1');
        $sheet->setCellValue('A1', 'PT. MIRASA FOOD INDUSTRY');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => self::COLOR_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Baris 2: Sub-judul ──────────────────────────────────────
        $sheet->mergeCells('A2:T2');
        $sheet->setCellValue('A2', 'Pabrik Pengolahan Makanan & Keripik Singkong · Ambartawang, Magelang, Jawa Tengah');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['argb' => self::COLOR_MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Baris 3: Judul Laporan ──────────────────────────────────
        $sheet->mergeCells('A3:T3');
        $sheet->setCellValue('A3', 'LAPORAN REKAPITULASI BARANG MASUK (GOODS RECEIPT NOTE)');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Baris 4: Info Filter ─────────────────────────────────────
        $filterInfo = 'Gudang: ' . ($this->gudangNm ?? 'Semua Gudang');
        if ($this->search) {
            $filterInfo .= '  |  Pencarian: "' . $this->search . '"';
        }
        $filterInfo .= '  |  Dicetak: ' . $this->printedAt . ' oleh ' . $this->printedBy;

        $sheet->mergeCells('A4:T4');
        $sheet->setCellValue('A4', $filterInfo);
        $sheet->getStyle('A4')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => self::COLOR_MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Garis pemisah kop
        $sheet->getRowDimension(5)->setRowHeight(4);

        // ── Baris 6: Header Tabel ────────────────────────────────────
        $headerRow = 6;
        $headers = [
            'A' => 'No. GRN',
            'B' => 'Tanggal',
            'C' => 'Kode Batch',
            'D' => 'Kode Barang',
            'E' => 'Nama Barang',
            'F' => 'Jenis',
            'G' => 'Keterangan',
            'H' => 'Qty Masuk',
            'I' => 'Harga Satuan',
            'J' => 'Total Harga',
            // Kolom Informasi Pelengkap ERP
            'K' => 'Satuan',
            'L' => 'Gudang Simpan',
            'M' => 'Supplier Pengirim',
            'N' => 'Ref. PO',
            'O' => 'Tgl Expired',
            'P' => 'Grade',
            'Q' => 'Diskon %',
            'R' => 'Potongan Item',
            'S' => 'Pajak',
            'T' => 'Total Tagihan',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $headerRow, $label);
        }

        $sheet->getStyle('A' . $headerRow . ':T' . $headerRow)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9.5, 'color' => ['argb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF0D3844']]],
        ]);

        // Penyesuaian alignment header
        $sheet->getStyle('A' . $headerRow . ':B' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $headerRow . ':G' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('H' . $headerRow . ':J' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('K' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L' . $headerRow . ':M' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('N' . $headerRow . ':P' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('Q' . $headerRow . ':R' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('S' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('T' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        // ── Data Rows ────────────────────────────────────────────────
        $row          = $headerRow + 1;
        $no           = 1;
        $totalQty     = 0;
        $totalHpp     = 0;
        $totalPotongan = 0;
        $totalTagihan = 0;

        foreach ($this->items as $dtl) {
            $hargaAwal    = (float) ($dtl->harga_nominal ?? 0);
            $diskonPct    = (float) ($dtl->diskon_persen ?? 0);
            $potonganItem = (float) ($dtl->potongan_nominal ?? 0);
            $hargaNet     = (float) ($dtl->harga_netto ?: ($hargaAwal * (1 - $diskonPct / 100)));
            $qty          = (float) $dtl->terima_qty;
            $hpp          = (float) ($dtl->subtotal_netto ?: max(0, ($qty * $hargaNet) - $potonganItem));
            $isItemPpn    = ($dtl->ppn_tipe === 'PPN_11');
            $tagihan      = (float) ($dtl->subtotal_tagihan ?: ($hpp + ($isItemPpn ? round($hpp * 0.11) : 0)));

            $totalQty      += $qty;
            $totalHpp      += $hpp;
            $totalPotongan += $potonganItem;
            $totalTagihan  += $tagihan;

            // Penentuan Keterangan cerdas
            $keterangan = $dtl->catatan_txt;
            if (empty($keterangan)) {
                $keterangan = $dtl->header?->catatan_txt;
            }
            if (empty($keterangan)) {
                $keterangan = $dtl->header?->po ? ('PO: ' . $dtl->header->po->po_no) : 'PENERIMAAN MASUK';
            }

            // Format tanggal gambar (contoh: 1-Sep-2026)
            $tglMasuk = $dtl->header?->terima_tgl ? Carbon::parse($dtl->header->terima_tgl)->format('j-M-Y') : '-';
            $tglExp   = $dtl->expired_tgl ? Carbon::parse($dtl->expired_tgl)->format('j-M-Y') : '-';

            // ── Isi Data Sel ─────────────────────────────────────────
            // No. GRN lalu Tanggal dan kolom gambar operasional
            $sheet->setCellValue('A' . $row, $dtl->header?->terima_no ?? '-');
            $sheet->setCellValue('B' . $row, $tglMasuk);
            $sheet->setCellValue('C' . $row, $dtl->batch_no ?? '-');
            $sheet->setCellValue('D' . $row, $dtl->barang?->barang_cd ?? '-');
            $sheet->setCellValue('E' . $row, strtoupper($dtl->barang?->barang_nm ?? '-'));
            $sheet->setCellValue('F' . $row, strtoupper($dtl->barang?->jenisBarang?->jenis_barang_nm ?? '-'));
            $sheet->setCellValue('G' . $row, strtoupper($keterangan));
            $sheet->setCellValue('H' . $row, $qty);
            $sheet->setCellValue('I' . $row, $hargaNet ?: $hargaAwal);
            $sheet->setCellValue('J' . $row, $hpp);

            // Kolom Informasi Pelengkap ERP
            $sheet->setCellValue('K' . $row, $dtl->barang?->satuanDasar?->satuan_nm ?? '-');
            $sheet->setCellValue('L' . $row, $dtl->header?->gudang?->gudang_nm ?? '-');
            $sheet->setCellValue('M' . $row, $dtl->header?->supplier?->supplier_nm ?? '-');
            $sheet->setCellValue('N' . $row, $dtl->header?->po?->po_no ?? 'Non-PO');
            $sheet->setCellValue('O' . $row, $tglExp);
            $sheet->setCellValue('P' . $row, $dtl->grade_cd ?? 'A');
            $sheet->setCellValue('Q' . $row, $diskonPct > 0 ? $diskonPct / 100 : null);
            $sheet->setCellValue('R' . $row, $potonganItem > 0 ? $potonganItem : null);
            $sheet->setCellValue('S' . $row, $isItemPpn ? 'PPN 11%' : 'Non-Pajak');
            $sheet->setCellValue('T' . $row, $tagihan);

            // ── Format Angka & Tampilan ──────────────────────────────
            // Qty: 10.844,73
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            // Harga & Total: Rp 17.748,64 & Rp 192.479.162,68
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle('Q' . $row)->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle('R' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('T' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');

            // ── Alignment Sel Data ───────────────────────────────────
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('M' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('N' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('O' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('P' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('Q' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('R' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('S' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('T' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Warna baris zebra (selang-seling halus)
            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $row . ':T' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_ROW_EVEN]],
                ]);
            }

            // Penekanan visual pada sel Total Harga
            $sheet->getStyle('J' . $row)->getFont()->setBold(true);
            $sheet->getStyle('T' . $row)->getFont()->setBold(true);

            // Border tipis tiap baris
            $sheet->getStyle('A' . $row . ':T' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                'font'    => ['size' => 9],
            ]);

            $row++;
            $no++;
        }

        // ── Baris Total Rekapitulasi ──────────────────────────────────
        if ($no > 1) {
            $totalRow = $row;
            $sheet->mergeCells('A' . $totalRow . ':G' . $totalRow);
            $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN (' . ($no - 1) . ' Baris Data)');
            $sheet->setCellValue('H' . $totalRow, $totalQty);
            $sheet->setCellValue('J' . $totalRow, $totalHpp);
            $sheet->setCellValue('R' . $totalRow, $totalPotongan > 0 ? $totalPotongan : null);
            $sheet->setCellValue('T' . $totalRow, $totalTagihan);

            $sheet->getStyle('H' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $totalRow)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
            $sheet->getStyle('R' . $totalRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('T' . $totalRow)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');

            $sheet->getStyle('A' . $totalRow . ':T' . $totalRow)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9.5, 'color' => ['argb' => self::COLOR_DARK]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF94A3B8']]],
            ]);

            $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('R' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('T' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle('J' . $totalRow)->getFont()->getColor()->setARGB(self::COLOR_GREEN);
            $sheet->getStyle('T' . $totalRow)->getFont()->getColor()->setARGB(self::COLOR_HEADER_BG);
            $sheet->getRowDimension($totalRow)->setRowHeight(20);
        }

        // ── Freeze header pada baris 7 ───────────────────────────────
        $sheet->freezePane('A7');

        // ── Auto-filter untuk kemudahan sortir ─────────────────────────
        $sheet->setAutoFilter('A6:T6');

        // ── Pengaturan Cetak Landscape A4 ─────────────────────────────
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getHeaderFooter()->setOddHeader('&L&B PT. Mirasa Food Industry&R&BHal &P dari &N');
        $sheet->getHeaderFooter()->setOddFooter('&LRekap Barang Masuk&R' . $this->printedAt);

        // ── Document Properties ──────────────────────────────────────
        $spreadsheet->getProperties()
            ->setCreator('ERP PT Mirasa Food Industry')
            ->setTitle('Rekap Barang Masuk GRN')
            ->setSubject('Laporan Rekapitulasi Penerimaan Barang')
            ->setDescription('Dicetak pada ' . $this->printedAt . ' oleh ' . $this->printedBy);

        return $spreadsheet;
    }
}
