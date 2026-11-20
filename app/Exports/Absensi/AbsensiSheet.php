<?php
// app/Exports/Absensi/AbsensiSheet.php
namespace App\Exports\Absensi;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Satu sheet laporan absensi. Baris 1-3 berisi judul, nama perusahaan, dan periode;
 * baris 4 kosong; header tabel di baris 5; data mulai baris 6 (sama seperti FingerprintExport).
 */
class AbsensiSheet implements
    FromArray,
    WithHeadings,
    WithTitle,
    WithCustomStartCell,
    WithColumnWidths,
    WithColumnFormatting,
    WithStyles,
    WithEvents,
    WithStrictNullComparison
{
    private const HEADER_ROW   = 5;
    private const HEADER_COLOR = '1F4E78';
    private const STRIPE_COLOR = 'EEF3F8';

    /**
     * @param array<int,string>    $kolom
     * @param array<int,array>     $baris       baris terakhir = baris TOTAL bila $adaTotal
     * @param array<string,string> $format      kolom => format angka/tanggal
     * @param ?string              $dari        awal periode (Y-m-d) untuk baris periode di header
     * @param ?string              $sampai      akhir periode (Y-m-d)
     * @param ?string              $filter      teks filter aktif (cari, status, kelompok, shift)
     */
    public function __construct(
        private string $judul,
        private array $kolom,
        private array $baris,
        private array $format = [],
        private bool $adaTotal = false,
        private ?string $dari = null,
        private ?string $sampai = null,
        private ?string $filter = null
    ) {
    }

    public function title(): string
    {
        return substr($this->judul, 0, 31);
    }

    public function startCell(): string
    {
        return 'A' . self::HEADER_ROW;
    }

    public function headings(): array
    {
        return $this->kolom;
    }

    public function array(): array
    {
        return $this->baris;
    }

    public function columnFormats(): array
    {
        return $this->format;
    }

    /**
     * Lebar kolom dihitung dari isi, ditambah ruang untuk tombol filter di header
     * (pengganti ShouldAutoSize, supaya judul di baris 1-3 tidak ikut melebarkan kolom A).
     */
    public function columnWidths(): array
    {
        $lebar = [];

        foreach ($this->kolom as $i => $nama) {
            $huruf   = Coordinate::stringFromColumnIndex($i + 1);
            $adaTgl  = str_contains(strtolower($this->format[$huruf] ?? ''), 'yy');
            $maks    = mb_strlen($nama);

            foreach ($this->baris as $b) {
                $v = $b[$i] ?? '';
                if ($adaTgl && is_numeric($v)) {
                    $len = 10;
                } elseif (is_float($v)) {
                    $len = strlen(number_format($v, 2));
                } else {
                    $len = mb_strlen((string) $v);
                }
                $maks = max($maks, $len);
            }

            $lebar[$huruf] = min(max($maks + 6, 10), 50);
        }

        return $lebar;
    }

    public function styles(Worksheet $sheet): ?array
    {
        $head  = self::HEADER_ROW;
        $huruf = Coordinate::stringFromColumnIndex(count($this->kolom));
        $akhir = $head + count($this->baris);

        $gaya = [
            $head => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => self::HEADER_COLOR]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ],
            "A{$head}:{$huruf}{$akhir}" => ['borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E1']]]],
        ];

        if ($this->adaTotal && $akhir > $head) {
            $gaya[$akhir] = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DDEBF7']],
            ];
        }

        return $gaya;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s         = $event->sheet->getDelegate();
                $head      = self::HEADER_ROW;
                $col       = Coordinate::stringFromColumnIndex(count($this->kolom));
                $akhir     = $head + count($this->baris);
                $dataAkhir = ($this->adaTotal && $akhir > $head) ? $akhir - 1 : $akhir;   // baris TOTAL tidak ikut filter/selang-seling

                // Blok judul (ditulis di sini, setelah data, supaya tidak menggeser baris tabel)
                $s->setCellValue('A1', 'LAPORAN ABSENSI KARYAWAN');
                $s->setCellValue('A2', 'PT Mirasa Food Industry');
                $s->setCellValue('A3', $this->teksPeriode());
                foreach ([1, 2, 3] as $r) {
                    $s->mergeCells("A{$r}:{$col}{$r}");
                }
                $s->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB(self::HEADER_COLOR);
                $s->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                $s->getStyle('A3')->getFont()->setSize(10)->getColor()->setRGB('64748B');
                $s->getStyle("A1:{$col}3")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                $s->getRowDimension($head)->setRowHeight(36);

                // Baris selang-seling
                for ($r = $head + 2; $r <= $dataAkhir; $r += 2) {
                    $s->getStyle("A{$r}:{$col}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::STRIPE_COLOR);
                }

                $s->freezePane('A' . ($head + 1));
                $s->setAutoFilter("A{$head}:{$col}" . max($head, $dataAkhir));

                $s->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)->setFitToHeight(0);
                $s->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($head, $head);
            },
        ];
    }

    private function teksPeriode(): string
    {
        if (!$this->dari || !$this->sampai) {
            return '';
        }

        $fmt = fn ($t) => Carbon::parse($t)->locale('id')->translatedFormat('d F Y');

        return 'Periode: ' . $fmt($this->dari) . ' s/d ' . $fmt($this->sampai)
            . ($this->filter ? '   |   Filter: ' . $this->filter : '');
    }
}
