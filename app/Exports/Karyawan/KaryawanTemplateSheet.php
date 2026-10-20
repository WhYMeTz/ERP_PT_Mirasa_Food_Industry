<?php

namespace App\Exports\Karyawan;

use App\Models\MasterData\MstKaryawan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class KaryawanTemplateSheet implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithEvents
{
    // Jumlah baris yang disiapkan (format teks + dropdown)
    public const MAX_ROW = 500;

    // Kolom wajib diisi (judulnya diberi warna merah)
    public const REQUIRED = ['A', 'C', 'G'];

    // HARUS sama dengan $departemenList di KaryawanController::index()
    public const DEPARTEMEN = [
        'PRODUKSI'   => 'Departemen Produksi',
        'GUDANG'     => 'Departemen Gudang & Logistik',
        'PURCHASING' => 'Departemen Pengadaan (Purchasing)',
        'FINANCE'    => 'Departemen Keuangan & Kasir',
        'QC'         => 'Departemen Quality Control (QC)',
        'HRD'        => 'Departemen SDM / HRD',
        'MANAJEMEN'  => 'Manajemen / Direksi',
        'UMUM'       => 'Departemen Umum',
        'DRIVER'     => 'Driver',
        'CLEANING'   => 'Cleaning Service',
        'SECURITY'   => 'Security',
    ];

    public function title(): string
    {
        return 'Template Import';
    }

    public function headings(): array
    {
        return [
            'ID Karyawan', 'ID Absensi', 'Nama Karyawan', 'Kelompok', 'Shift',
            'Status Karyawan', 'Bagian/Divisi', 'Jabatan', 'Tanggal Masuk Kerja',
            'No. HP', 'Email', 'Bank', 'No. Rekening', 'Alamat',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16, 'B' => 14, 'C' => 28, 'D' => 20, 'E' => 14, 'F' => 17, 'G' => 18,
            'H' => 24, 'I' => 21, 'J' => 17, 'K' => 30, 'L' => 10, 'M' => 18, 'N' => 34,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $max   = self::MAX_ROW;

                // Baris judul
                $sheet->getStyle('A1:N1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1F4E78'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);
                foreach (self::REQUIRED as $col) {
                    $sheet->getStyle("{$col}1")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('C00000');
                }
                $sheet->getRowDimension(1)->setRowHeight(32);

                // Garis tabel seperti formulir
                $sheet->getStyle("A1:N{$max}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('C8D2DC');

                // ID Absensi (B), Tanggal Masuk (I), No. HP (J), No. Rekening (M) berformat teks
                foreach (['B', 'I', 'J', 'M'] as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$max}")
                        ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                }

                // Dropdown pilihan
                $lists = [
                    'D' => implode(',', array_keys(MstKaryawan::KELOMPOK)),
                    'E' => 'SHIFT A,SHIFT B,NON SHIFT',
                    'F' => 'Tetap,Kontrak,Harian',
                    'G' => implode(',', array_keys(self::DEPARTEMEN)),
                ];

                foreach ($lists as $col => $items) {
                    for ($row = 2; $row <= $max; $row++) {
                        $validation = $sheet->getCell("{$col}{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(true);
                        $validation->setShowDropDown(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setErrorTitle('Pilihan tidak valid');
                        $validation->setError('Pilih salah satu nilai dari daftar dropdown.');
                        $validation->setFormula1('"' . $items . '"');
                    }
                }

                $sheet->freezePane('A2');
            },
        ];
    }
}