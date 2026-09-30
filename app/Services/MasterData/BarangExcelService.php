<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstJenisBarang;
use App\Models\MasterData\MstSatuan;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangExcelService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengekspor seluruh data Master Barang ke file Excel (.xlsx).
     */
    public function export(): StreamedResponse
    {
        $barangs = MstBarang::with(['jenisBarang', 'satuanDasar'])
            ->where('deleted_st', false)
            ->orderBy('barang_cd', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Barang');

        // 1. Judul Laporan
        $sheet->setCellValue('A1', 'DATA MASTER BARANG - PT MIRASA FOOD INDUSTRY');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0f172a'));
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->setCellValue('A2', 'Diekspor pada: ' . date('d F Y, H:i') . ' WIB | Total: ' . $barangs->count() . ' Barang');
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748b'));
        $sheet->getRowDimension(2)->setRowHeight(20);

        // 2. Header Tabel
        $headers = [
            'A3' => 'NO',
            'B3' => 'KODE BARANG',
            'C3' => 'NAMA BARANG',
            'D3' => 'KODE JENIS',
            'E3' => 'KATEGORI / JENIS',
            'F3' => 'SATUAN',
            'G3' => 'BATAS MIN STOK',
            'H3' => 'HARGA BELI STANDAR (RP)',
            'I3' => 'DESKRIPSI / SPESIFIKASI',
            'J3' => 'STATUS',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A3:J3';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0284C7'); // Mirasa Primary Blue
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(26);

        // 3. Isi Data
        $row = 4;
        foreach ($barangs as $idx => $b) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValueExplicit('B' . $row, $b->barang_cd, DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $b->barang_nm);
            $sheet->setCellValue('D' . $row, $b->jenisBarang->jenis_barang_cd ?? '-');
            $sheet->setCellValue('E' . $row, $b->jenisBarang->jenis_barang_nm ?? '-');
            $sheet->setCellValue('F' . $row, $b->satuanDasar->satuan_nm ?? ($b->satuanDasar->satuan_cd ?? '-'));
            $sheet->setCellValue('G' . $row, (float) $b->batas_minimum_qty);
            $sheet->setCellValue('H' . $row, (float) $b->harga_beli_standar);
            $sheet->setCellValue('I' . $row, $b->deskripsi_txt ?? '-');
            $sheet->setCellValue('J' . $row, $b->active_st ? 'Aktif' : 'Nonaktif');

            // Format Angka
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Alignments
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra Striping halus
            if ($row % 2 == 1) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8FAFC');
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 4) {
            $tableRange = "A3:J{$lastRow}";
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('CBD5E1');
        }

        // Auto-fit kolom
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Master_Barang_PT_Mirasa_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Membuat template Excel kosong untuk diisi oleh pengguna saat Import.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: FORMULIR IMPORT
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Barang');

        // Petunjuk di atas
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT MASTER BARANG - PT MIRASA FOOD INDUSTRY');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0f172a'));
        $sheet->setCellValue('A2', 'Petunjuk: Isi data barang mulai baris ke-4. Kode Barang boleh dikosongkan (sistem akan otomatis membuat kode). Lihat sheet "PANDUAN_REFERENSI" untuk kode jenis & satuan.');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748b'));

        // Headers
        $headers = [
            'A3' => 'KODE BARANG (OPSIONAL)',
            'B3' => 'NAMA BARANG (*WAJIB)',
            'C3' => 'KODE JENIS (*WAJIB)',
            'D3' => 'KODE SATUAN (*WAJIB)',
            'E3' => 'BATAS MIN STOK (ANGKA)',
            'F3' => 'HARGA BELI STANDAR (RP)',
            'G3' => 'DESKRIPSI / KETERANGAN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A3:G3')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0284C7');
        $sheet->getStyle('A3:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(26);

        // Contoh Baris 1 & 2
        $samples = [
            ['BB-SK099', 'SINGKONG KELINCI GRADE SUPER', 'BB', 'KG', 1000, 2500, 'Singkong kupas pilihan petani Temanggung'],
            ['', 'BUMBU BALADO SPECIAL 500G', 'BP', 'BUNGKUS', 50, 15000, 'Bumbu balado racikan khusus maksi'],
        ];

        $r = 4;
        foreach ($samples as $s) {
            $sheet->setCellValueExplicit('A' . $r, $s[0], DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $r, $s[1]);
            $sheet->setCellValue('C' . $r, $s[2]);
            $sheet->setCellValue('D' . $r, $s[3]);
            $sheet->setCellValue('E' . $r, $s[4]);
            $sheet->setCellValue('F' . $r, $s[5]);
            $sheet->setCellValue('G' . $r, $s[6]);
            $sheet->getStyle("A{$r}:G{$r}")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748b'));
            $r++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // SHEET 2: PANDUAN REFERENSI KODE JENIS & SATUAN
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('PANDUAN_REFERENSI');

        // Tabel 1: Jenis Barang
        $sheetRef->setCellValue('A1', 'KODE JENIS BARANG');
        $sheetRef->setCellValue('B1', 'NAMA KATEGORI');
        $sheetRef->getStyle('A1:B1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheetRef->getStyle('A1:B1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('059669'); // Emerald

        $jenisList = MstJenisBarang::where('deleted_st', false)->orderBy('jenis_barang_cd')->get();
        $r = 2;
        foreach ($jenisList as $j) {
            $sheetRef->setCellValue('A' . $r, $j->jenis_barang_cd);
            $sheetRef->setCellValue('B' . $r, $j->jenis_barang_nm);
            $r++;
        }

        // Tabel 2: Satuan Barang
        $sheetRef->setCellValue('D1', 'KODE SATUAN');
        $sheetRef->setCellValue('E1', 'NAMA SATUAN');
        $sheetRef->getStyle('D1:E1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheetRef->getStyle('D1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D97706'); // Amber

        $satuanList = MstSatuan::where('deleted_st', false)->orderBy('satuan_cd')->get();
        $rSat = 2;
        foreach ($satuanList as $s) {
            $sheetRef->setCellValue('D' . $rSat, $s->satuan_cd);
            $sheetRef->setCellValue('E' . $rSat, $s->satuan_nm);
            $rSat++;
        }

        foreach (['A', 'B', 'D', 'E'] as $c) {
            $sheetRef->getColumnDimension($c)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Template_Import_Barang_PT_Mirasa.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Memproses Import file Excel ke database Master Barang.
     * Fitur UPSERT cerdas:
     * - Jika kode barang sudah ada: update data
     * - Jika kode barang belum ada / kosong: tambah baru + auto generate kode
     */
    public function import(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 4) {
            throw new Exception("File Excel kosong atau tidak memiliki baris data setelah header.");
        }

        // Cache Master Referensi untuk efisiensi
        $jenisMap = MstJenisBarang::where('deleted_st', false)
            ->get()
            ->keyBy(fn($item) => strtoupper(trim($item->jenis_barang_cd)));

        $satuanMap = MstSatuan::where('deleted_st', false)
            ->get()
            ->keyBy(fn($item) => strtoupper(trim($item->satuan_cd)));

        $inserted = 0;
        $updated = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $jenisMap, $satuanMap, &$inserted, &$updated, &$errors) {
            $userId = Auth::id() ?? 'SYSTEM';

            // Mulai dari baris ke-4 (melewati judul dan header)
            foreach ($rows as $rowIndex => $col) {
                if ($rowIndex < 4) continue;

                $kodeBarang = trim((string) ($col['A'] ?? ''));
                $namaBarang = trim((string) ($col['B'] ?? ''));
                $kodeJenis  = strtoupper(trim((string) ($col['C'] ?? '')));
                $kodeSatuan = strtoupper(trim((string) ($col['D'] ?? '')));
                $batasMin   = (float) ($col['E'] ?? 0);
                $hargaBeli  = (float) ($col['F'] ?? 0);
                $deskripsi  = trim((string) ($col['G'] ?? ''));

                // Lewati baris kosong total
                if (empty($namaBarang) && empty($kodeBarang)) {
                    continue;
                }

                // Validasi Nama Barang Wajib
                if (empty($namaBarang)) {
                    $errors[] = "Baris {$rowIndex}: Nama Barang tidak boleh kosong.";
                    continue;
                }

                // Cek atau buat Jenis Barang
                $jenisId = null;
                if (!empty($kodeJenis)) {
                    if (isset($jenisMap[$kodeJenis])) {
                        $jenisId = $jenisMap[$kodeJenis]->jenis_barang_id;
                    } else {
                        // Buat otomatis jika belum ada
                        $newJenis = MstJenisBarang::create([
                            'jenis_barang_cd' => $kodeJenis,
                            'jenis_barang_nm' => $kodeJenis,
                            'created_by'      => $userId,
                        ]);
                        $jenisMap[$kodeJenis] = $newJenis;
                        $jenisId = $newJenis->jenis_barang_id;
                    }
                } else {
                    $defaultJenis = $jenisMap->first();
                    $jenisId = $defaultJenis ? $defaultJenis->jenis_barang_id : 1;
                }

                // Cek atau buat Satuan Dasar
                $satuanId = null;
                if (!empty($kodeSatuan)) {
                    if (isset($satuanMap[$kodeSatuan])) {
                        $satuanId = $satuanMap[$kodeSatuan]->satuan_id;
                    } else {
                        // Buat otomatis jika belum ada
                        $newSatuan = MstSatuan::create([
                            'satuan_cd'  => $kodeSatuan,
                            'satuan_nm'  => $kodeSatuan,
                            'created_by' => $userId,
                        ]);
                        $satuanMap[$kodeSatuan] = $newSatuan;
                        $satuanId = $newSatuan->satuan_id;
                    }
                } else {
                    $defaultSatuan = $satuanMap->first();
                    $satuanId = $defaultSatuan ? $defaultSatuan->satuan_id : 1;
                }

                // Auto-generate kode jika dikosongkan
                if (empty($kodeBarang)) {
                    $kodeBarang = $this->codeGenerator->generateBarangCode($kodeJenis, $namaBarang);
                }

                // Cek apakah barang sudah ada di database (UPSERT)
                $existing = MstBarang::where('barang_cd', $kodeBarang)->first();

                if ($existing) {
                    $existing->update([
                        'barang_nm'          => $namaBarang,
                        'jenis_barang_id'    => $jenisId,
                        'satuan_dasar_id'    => $satuanId,
                        'batas_minimum_qty'  => $batasMin,
                        'harga_beli_standar' => $hargaBeli,
                        'deskripsi_txt'      => $deskripsi ?: $existing->deskripsi_txt,
                        'deleted_st'         => false,
                        'active_st'          => true,
                        'updated_by'         => (string) $userId,
                    ]);
                    $updated++;
                } else {
                    MstBarang::create([
                        'barang_cd'          => $kodeBarang,
                        'barang_nm'          => $namaBarang,
                        'jenis_barang_id'    => $jenisId,
                        'satuan_dasar_id'    => $satuanId,
                        'batas_minimum_qty'  => $batasMin,
                        'harga_beli_standar' => $hargaBeli,
                        'deskripsi_txt'      => $deskripsi,
                        'active_st'          => true,
                        'created_by'         => (string) $userId,
                    ]);
                    $inserted++;
                }
            }
        });

        return [
            'success'  => true,
            'inserted' => $inserted,
            'updated'  => $updated,
            'total'    => $inserted + $updated,
            'errors'   => $errors,
        ];
    }
}
