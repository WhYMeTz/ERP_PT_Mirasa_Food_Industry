<?php

namespace App\Exports\Karyawan;

use App\Models\MasterData\MstKaryawan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KaryawanPetunjukSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    private const NAVY = '1F4E78';

    public function title(): string
    {
        return 'Petunjuk Pengisian';
    }

    public function array(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return ['A' => 26, 'B' => 11, 'C' => 66, 'D' => 28];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s = $event->sheet->getDelegate();
                $s->setShowGridlines(false);

                // ===== Judul =====
                $s->mergeCells('A1:D1');
                $s->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE IMPORT DATA KARYAWAN');
                $s->getStyle('A1')->getFont()->setBold(true)->setSize(15);
                $s->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $s->getRowDimension(1)->setRowHeight(28);

                $s->mergeCells('A2:D2');
                $s->setCellValue('A2', 'Baca petunjuk ini sebelum mengisi sheet "Template Import".');
                $s->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('555555');
                $s->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row = 4;

                // ===== Langkah pengisian =====
                $this->section($s, $row, 'LANGKAH PENGISIAN');
                $row++;

                $steps = [
                    'Isi data di sheet "Template Import" mulai dari baris 2. Jangan mengubah, menghapus, atau menambah kolom pada baris judul (baris 1).',
                    'Kolom dengan judul MERAH wajib diisi. Kolom dengan judul BIRU boleh dikosongkan.',
                    'Satu baris mewakili satu karyawan. Baris yang ID Karyawan atau Namanya kosong akan dilewati.',
                    'Jika ID Karyawan sudah terdaftar di sistem, datanya akan DIPERBARUI (tidak dobel).',
                    'Untuk Kelompok, Shift, Status, dan Bagian/Divisi, pilih dari dropdown agar penulisannya sesuai.',
                    'Tulis tanggal dengan format hari/bulan/tahun, misalnya 07/06/2023. Kolom ID Absensi, No. HP, dan No. Rekening sudah berformat teks agar angka nol di depan tidak hilang.',
                    'Simpan sebagai file .xlsx, lalu unggah lewat Kelola Data > Import dari Excel (maksimal 5 MB). Sebaiknya uji dengan beberapa baris dulu.',
                ];

                foreach ($steps as $i => $text) {
                    $s->mergeCells("A{$row}:D{$row}");
                    $s->setCellValueExplicit("A{$row}", ($i + 1) . '. ' . $text, DataType::TYPE_STRING);
                    $s->getStyle("A{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    $s->getRowDimension($row)->setRowHeight(32);
                    $row++;
                }

                $row++;

                // ===== Keterangan kolom =====
                $this->section($s, $row, 'KETERANGAN KOLOM');
                $row++;

                foreach (['Kolom', 'Wajib', 'Aturan Pengisian', 'Contoh'] as $i => $label) {
                    $col = chr(65 + $i);
                    $s->setCellValue("{$col}{$row}", $label);
                }
                $s->getStyle("A{$row}:D{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCE6F1']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $headerRow = $row;
                $row++;

                $kelompok = implode(', ', array_keys(MstKaryawan::KELOMPOK));
                $kodeBagian = implode(', ', array_keys(KaryawanTemplateSheet::DEPARTEMEN));

                $columns = [
                    ['ID Karyawan', 'Ya', 'ID unik karyawan, maksimal 50 karakter. Ditulis huruf kapital. Jika sudah terdaftar, data karyawan tersebut diperbarui.', 'DRV1N19'],
                    ['ID Absensi', 'Tidak', 'ID di mesin absen. Ditulis apa adanya, angka nol di depan dipertahankan.', '000000019'],
                    ['Nama Karyawan', 'Ya', 'Nama lengkap, maksimal 100 karakter.', 'JOKO WIGUNO'],
                    ['Kelompok', 'Tidak', 'Pilih salah satu: ' . $kelompok . '.', 'LANGSUNG IFM'],
                    ['Shift', 'Tidak', 'Pilih salah satu: SHIFT A, SHIFT B, NON SHIFT. Penulisan "A" atau "B" saja juga diterima.', 'SHIFT A'],
                    ['Status Karyawan', 'Tidak', 'Pilih salah satu: Tetap, Kontrak, Harian. Jika kosong, dianggap Tetap.', 'Tetap'],
                    ['Bagian/Divisi', 'Ya', 'Isi dengan KODE bagian (lihat daftar di bawah): ' . $kodeBagian . '.', 'PRODUKSI'],
                    ['Jabatan', 'Tidak', 'Jabatan kerja. Jika kosong, otomatis diisi kode bagian.', 'OPERATOR PACKING'],
                    ['Tanggal Masuk Kerja', 'Tidak', 'Format hari/bulan/tahun (dd/mm/yyyy).', '07/06/2023'],
                    ['No. HP', 'Tidak', 'Nomor telepon/WhatsApp, hanya angka.', '081234567801'],
                    ['Email', 'Tidak', 'Alamat email yang valid.', 'joko@mirasa.co.id'],
                    ['Bank', 'Tidak', 'Nama bank. Jika kosong, diisi BCA.', 'BCA'],
                    ['No. Rekening', 'Tidak', 'Angka tanpa spasi atau tanda baca.', '1040871639'],
                    ['Alamat', 'Tidak', 'Alamat domisili, bebas.', 'Ambartawang, Magelang'],
                ];

                $firstDataRow = $row;
                foreach ($columns as $item) {
                    foreach ($item as $i => $value) {
                        $col = chr(65 + $i);
                        $s->setCellValueExplicit("{$col}{$row}", $value, DataType::TYPE_STRING);
                    }
                    if ($item[1] === 'Ya') {
                        $s->getStyle("B{$row}")->getFont()->setBold(true)->getColor()->setRGB('C00000');
                    }
                    $row++;
                }
                $lastDataRow = $row - 1;

                $s->getStyle("A{$firstDataRow}:D{$lastDataRow}")->getAlignment()
                    ->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $s->getStyle("B{$firstDataRow}:B{$lastDataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $s->getStyle("A{$headerRow}:D{$lastDataRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B7C4D1');

                $row += 1;

                // ===== Daftar kode bagian =====
                $this->section($s, $row, 'DAFTAR KODE BAGIAN / DIVISI');
                $row++;

                $s->setCellValue("A{$row}", 'Kode (isi di template)');
                $s->mergeCells("B{$row}:C{$row}");
                $s->setCellValue("B{$row}", 'Nama Bagian');
                $s->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCE6F1']],
                ]);
                $deptHeader = $row;
                $row++;

                foreach (KaryawanTemplateSheet::DEPARTEMEN as $kode => $nama) {
                    $s->setCellValueExplicit("A{$row}", $kode, DataType::TYPE_STRING);
                    $s->mergeCells("B{$row}:C{$row}");
                    $s->setCellValueExplicit("B{$row}", $nama, DataType::TYPE_STRING);
                    $row++;
                }
                $s->getStyle("A{$deptHeader}:C" . ($row - 1))->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B7C4D1');
            },
        ];
    }

    private function section(Worksheet $s, int $row, string $text): void
    {
        $s->mergeCells("A{$row}:D{$row}");
        $s->setCellValue("A{$row}", $text);
        $s->getStyle("A{$row}:D{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::NAVY]],
        ]);
    }
}