<?php

namespace App\Exports\Karyawan;

use App\Models\MasterData\MstKaryawan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Illuminate\Support\Collection;

class KaryawanExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithCustomStartCell,
    WithColumnWidths,
    WithColumnFormatting,
    WithTitle,
    WithEvents,
    WithCustomValueBinder
{
    private const HEADER_ROW  = 5;
    private const LAST_COLUMN = 'O';

    // Warna header tabel (hex tanpa #). Ubah di sini jika ingin warna lain.
    private const HEADER_COLOR = '1F4E78';
    private const STRIPE_COLOR = 'EEF3F8';

    protected $collectionData;
    protected int $no = 0;

    public function __construct(Collection $collectionData)
    {
        $this->collectionData = $collectionData;
    }

    public function collection(): Collection
    {
        return $this->collectionData;
    }

    public function startCell(): string
    {
        return 'A' . self::HEADER_ROW;
    }

    public function title(): string
    {
        return 'Data Karyawan';
    }

    public function headings(): array
    {
        return [
            'No', 'ID Karyawan', 'ID Absensi', 'Nama Karyawan', 'Kelompok', 'Shift',
            'Status', 'Bagian/Divisi', 'Jabatan', 'Tanggal Masuk Kerja',
            'Kontak/No. Telp', 'Email', 'Bank', 'No. Rekening', 'Alamat',
        ];
    }

    /**
     * @param MstKaryawan $karyawan
     */
    public function map($karyawan): array
    {
        $this->no++;

        return [
            $this->no,
            $karyawan->nik,
            $karyawan->id_absensi,
            $karyawan->karyawan_nm,
            MstKaryawan::KELOMPOK[$karyawan->kelompok] ?? $karyawan->kelompok,
            $karyawan->shift,
            $karyawan->status_kerja,
            $karyawan->departemen_cd,
            $karyawan->jabatan_nm,
            $karyawan->tgl_masuk ? Date::dateTimeToExcel(Carbon::parse($karyawan->tgl_masuk)) : null,
            $karyawan->telepon_no,
            $karyawan->email,
            $karyawan->bank_name,
            $karyawan->no_rekening,
            $karyawan->alamat_txt,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,  'B' => 16, 'C' => 14, 'D' => 28, 'E' => 18,
            'F' => 13, 'G' => 12, 'H' => 18, 'I' => 24, 'J' => 16,
            'K' => 17, 'L' => 30, 'M' => 9,  'N' => 18, 'O' => 34,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    /**
     * ID Absensi (C), No. Telp (K) dan No. Rekening (N) selalu disimpan sebagai teks
     * agar angka nol di depan tidak hilang dan tidak berubah jadi angka ilmiah.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if (
            in_array($cell->getColumn(), ['C', 'K', 'N'], true)
            && $cell->getRow() > self::HEADER_ROW
            && $value !== null && $value !== ''
        ) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $last    = self::LAST_COLUMN;
                $head    = self::HEADER_ROW;
                $total   = $this->collectionData->count();
                $lastRow = $head + $total;

                // ===== Judul laporan =====
                $periode = Carbon::now()->locale('id')->translatedFormat('F Y');

                $sheet->mergeCells("A1:{$last}1");
                $sheet->mergeCells("A2:{$last}2");
                $sheet->mergeCells("A3:{$last}3");
                $sheet->setCellValue('A1', 'MIRASA FOOD MAGELANG');
                $sheet->setCellValue('A2', 'DATA KARYAWAN');
                $sheet->setCellValue('A3', 'PERIODE ' . strtoupper($periode));

                $sheet->getStyle("A1:{$last}3")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A3')->getFont()->setSize(11)->getColor()->setRGB('555555');
                $sheet->getRowDimension(1)->setRowHeight(26);

                // ===== Header tabel =====
                $headRange = "A{$head}:{$last}{$head}";
                $sheet->getStyle($headRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => self::HEADER_COLOR],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);
                $sheet->getRowDimension($head)->setRowHeight(32);

                // ===== Isi tabel =====
                $tableRange = "A{$head}:{$last}{$lastRow}";
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'B7C4D1'],
                        ],
                    ],
                ]);

                if ($total > 0) {
                    $bodyStart = $head + 1;

                    $sheet->getStyle("A{$bodyStart}:{$last}{$lastRow}")->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Kolom rata tengah: No, ID Karyawan, ID Absensi, Shift, Status, Tgl Masuk
                    foreach (['A', 'B', 'C', 'F', 'G', 'J'] as $col) {
                        $sheet->getStyle("{$col}{$bodyStart}:{$col}{$lastRow}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Kolom rata kiri untuk teks dan angka-sebagai-teks
                    foreach (['K', 'N'] as $col) {
                        $sheet->getStyle("{$col}{$bodyStart}:{$col}{$lastRow}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }

                    // Baris selang-seling
                    for ($row = $bodyStart; $row <= $lastRow; $row++) {
                        if (($row - $bodyStart) % 2 === 1) {
                            $sheet->getStyle("A{$row}:{$last}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB(self::STRIPE_COLOR);
                        }
                    }
                }

                // ===== Total =====
                $footerRow = $lastRow + 2;
                $sheet->setCellValue("A{$footerRow}", 'Total Karyawan: ' . $total . ' orang');
                $sheet->getStyle("A{$footerRow}")->getFont()->setBold(true);

                // ===== Kenyamanan pemakaian & cetak =====
                $sheet->freezePane('A' . ($head + 1));
                $sheet->setAutoFilter($tableRange);

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($head, $head);
            },
        ];
    }
}