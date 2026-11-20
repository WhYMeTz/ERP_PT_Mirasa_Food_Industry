<?php

namespace App\Exports\Manajemen;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
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

/**
 * Export data Finger Print (absensi harian) sesuai filter yang sedang dipakai di layar.
 */
class FingerprintExport extends DefaultValueBinder implements
    FromArray,
    WithColumnWidths,
    WithColumnFormatting,
    WithTitle,
    WithEvents,
    WithCustomValueBinder
{
    private const HEADER_ROW  = 5;
    private const LAST_COLUMN = 'K';
    private const HEADER_COLOR = '1F4E78';
    private const STRIPE_COLOR = 'EEF3F8';

    private const HEADINGS = [
        'No', 'Tanggal', 'ID Absen', 'Nama Karyawan', 'Scan Pertama', 'Jam Masuk',
        'Scan Terakhir', 'Jam Pulang', 'Total Jam Kerja', 'Total Jam (angka)', 'Keterangan',
    ];

    public function __construct(
        protected Collection $rows,
        protected string $dari,
        protected string $sampai,
        protected ?string $cari = null
    ) {
    }

    public function title(): string
    {
        return 'Finger Print';
    }

    public function array(): array
    {
        $data = [
            ['LAPORAN FINGER PRINT KARYAWAN'],
            ['PT Mirasa Food Industry'],
            ['Periode: ' . Carbon::parse($this->dari)->locale('id')->translatedFormat('d F Y')
                . ' s/d ' . Carbon::parse($this->sampai)->locale('id')->translatedFormat('d F Y')
                . ($this->cari ? '   |   Pencarian: ' . $this->cari : '')],
            [''],   // baris kosong (array kosong [] dibuang oleh Laravel Excel sehingga baris naik satu)
            self::HEADINGS,
        ];

        foreach ($this->rows->values() as $i => $r) {
            $menit = ($r->jam_masuk && $r->jam_pulang) ? max(0, $r->jam_masuk->diffInMinutes($r->jam_pulang, false)) : null;

            $ket = [];
            if ($r->is_manual) {
                $ket[] = 'Manual' . ($r->catatan ? ': ' . $r->catatan : '');
            } elseif ($r->catatan) {
                $ket[] = $r->catatan;
            }
            if (! $r->jam_pulang) {
                $ket[] = 'Belum finger pulang';
            }

            $data[] = [
                $i + 1,
                $r->tanggal ? Date::dateTimeToExcel($r->tanggal) : null,
                (string) $r->id_absen,
                $r->nama,
                $r->masuk_asli?->format('H:i:s'),
                $r->jam_masuk?->format('H:i'),
                $r->pulang_asli?->format('H:i:s'),
                $r->jam_pulang?->format('H:i'),
                $r->total_jam_label ?: null,
                $menit !== null ? round($menit / 60, 2) : null,
                implode(' | ', $ket) ?: null,
            ];
        }

        return $data;
    }

    /** ID Absen tetap teks supaya angka 0 di depan tidak hilang. */
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value) && $value !== '' && ctype_digit($value) && $value[0] === '0') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'B' => 'dd/mm/yyyy',
            'J' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function columnWidths(): array
    {
        // Kolom dilebihkan ~3 karakter supaya tombol filter di header tidak menutupi teks yang rata tengah
        return ['A' => 9, 'B' => 16, 'C' => 16, 'D' => 32, 'E' => 17, 'F' => 15, 'G' => 17, 'H' => 15, 'I' => 21, 'J' => 17, 'K' => 40];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s    = $event->sheet->getDelegate();
                $head = self::HEADER_ROW;
                $last = $head + $this->rows->count();
                $col  = self::LAST_COLUMN;

                foreach (['1', '2', '3'] as $r) {
                    $s->mergeCells("A{$r}:{$col}{$r}");
                }
                $s->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB(self::HEADER_COLOR);
                $s->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                $s->getStyle('A3')->getFont()->setSize(10)->getColor()->setRGB('64748B');
                $s->getStyle("A1:{$col}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                $s->getStyle("A{$head}:{$col}{$head}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_COLOR]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $s->getRowDimension($head)->setRowHeight(24);

                if ($last > $head) {
                    $s->getStyle("A{$head}:{$col}{$last}")->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

                    for ($r = $head + 1; $r <= $last; $r++) {
                        if (($r - $head) % 2 === 0) {
                            $s->getStyle("A{$r}:{$col}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::STRIPE_COLOR);
                        }
                    }

                    $s->getStyle("A" . ($head + 1) . ":C{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $s->getStyle("E" . ($head + 1) . ":J{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $s->freezePane('A' . ($head + 1));
                $s->setAutoFilter("A{$head}:{$col}" . max($head, $last));

                $s->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)->setFitToHeight(0);
                $s->getSheetView()->setZoomScale(100);
                $s->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($head, $head);
            },
        ];
    }
}
