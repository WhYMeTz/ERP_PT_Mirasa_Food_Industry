<?php

namespace App\Imports\Gudang;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\TerimaBarangService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\UploadedFile;

class TerimaBarangImport
{
    protected TerimaBarangService  $service;
    protected CodeGeneratorService $codeGenerator;

    /** Hasil import: daftar GRN yang berhasil atau error per baris */
    public array $results = [];
    public int   $successCount = 0;
    public int   $errorCount   = 0;

    // Kolom template (baris 7 ke bawah, 1-indexed sesuai template xlsx)
    // A=1  B=2  C=3  D=4  E=5  F=6  G=7  H=8  I=9  J=10
    // K=11 L=12 M=13 N=14 O=15 P=16 Q=17 R=18 S=19
    const COL_NO_GRN          = 'A'; // No. GRN (opsional – auto-generate jika kosong)
    const COL_TANGGAL         = 'B'; // Tanggal (format: d-M-Y atau Y-m-d)
    const COL_KODE_BATCH      = 'C'; // Kode Batch (wajib)
    const COL_KODE_BARANG     = 'D'; // Kode Barang (wajib – lookup ke mst_barang)
    const COL_QTY_MASUK       = 'H'; // Qty Masuk (wajib)
    const COL_HARGA_SATUAN    = 'I'; // Harga Satuan / harga_nominal
    const COL_GUDANG          = 'L'; // Nama/Kode Gudang (wajib)
    const COL_SUPPLIER        = 'M'; // Nama Supplier (wajib – lookup ke mst_supplier)
    const COL_SURATJALAN      = 'N'; // No. Surat Jalan / Ref PO
    const COL_EXPIRED         = 'O'; // Tgl Expired (opsional)
    const COL_GRADE           = 'P'; // Grade (opsional)
    const COL_DISKON          = 'Q'; // Diskon % (opsional)
    const COL_POTONGAN        = 'R'; // Potongan Rp (opsional)
    const COL_PPN             = 'S'; // Pajak (NON_PPN / PPN_11)
    const COL_CATATAN         = 'G'; // Keterangan / Catatan

    // Baris awal data (setelah header tabel di baris 6)
    const DATA_START_ROW = 7;

    public function __construct(TerimaBarangService $service, CodeGeneratorService $codeGenerator)
    {
        $this->service       = $service;
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Proses file Excel yang di-upload.
     * Mengelompokkan baris berdasarkan No. GRN,
     * lalu memanggil TerimaBarangService::store() per dokumen GRN.
     */
    public function import(UploadedFile $file): void
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        if ($highestRow < self::DATA_START_ROW) {
            throw new Exception('File tidak memiliki data barang masuk (baris data dimulai dari baris 7).');
        }

        // ── Baca semua baris ke dalam collection ────────────────────
        $rows = collect();
        for ($rowNum = self::DATA_START_ROW; $rowNum <= $highestRow; $rowNum++) {
            $kodeBarang = trim((string) $sheet->getCell('D' . $rowNum)->getValue());
            $qty        = $sheet->getCell('H' . $rowNum)->getValue();

            // Skip baris kosong
            if (empty($kodeBarang) && (is_null($qty) || $qty === '' || $qty == 0)) {
                continue;
            }

            $rows->push([
                'row_num'    => $rowNum,
                'no_grn'     => trim((string) $sheet->getCell('A' . $rowNum)->getValue()),
                'tanggal'    => $sheet->getCell('B' . $rowNum)->getValue(),
                'batch_no'   => trim((string) $sheet->getCell('C' . $rowNum)->getValue()),
                'kode_barang'=> $kodeBarang,
                'keterangan' => trim((string) $sheet->getCell('G' . $rowNum)->getValue()),
                'qty_masuk'  => $qty,
                'harga'      => $sheet->getCell('I' . $rowNum)->getValue(),
                'gudang'     => trim((string) $sheet->getCell('L' . $rowNum)->getValue()),
                'supplier'   => trim((string) $sheet->getCell('M' . $rowNum)->getValue()),
                'suratjalan' => trim((string) $sheet->getCell('N' . $rowNum)->getValue()),
                'expired'    => $sheet->getCell('O' . $rowNum)->getValue(),
                'grade'      => trim((string) $sheet->getCell('P' . $rowNum)->getValue()),
                'diskon'     => $sheet->getCell('Q' . $rowNum)->getValue(),
                'potongan'   => $sheet->getCell('R' . $rowNum)->getValue(),
                'ppn'        => trim(strtoupper((string) $sheet->getCell('S' . $rowNum)->getValue())),
                'catatan'    => trim((string) $sheet->getCell('G' . $rowNum)->getValue()),
            ]);
        }

        if ($rows->isEmpty()) {
            throw new Exception('Tidak ada baris data yang valid di file Excel.');
        }

        // ── Kelompokkan per No. GRN (jika kosong, akan auto-generate per grup supplier+tgl) ─
        $groups = $rows->groupBy(function ($row) {
            // Kelompokkan berdasarkan No. GRN jika ada, atau supplier+tanggal jika tidak
            return !empty($row['no_grn']) ? $row['no_grn'] : ($row['supplier'] . '|' . $row['tanggal']);
        });

        foreach ($groups as $grnKey => $groupRows) {
            $firstRow = $groupRows->first();
            try {
                $this->processGroup($grnKey, $groupRows);
                $this->successCount++;
                $this->results[] = [
                    'status'  => 'success',
                    'grn'     => $firstRow['no_grn'] ?: 'AUTO',
                    'rows'    => $groupRows->pluck('row_num')->toArray(),
                    'message' => 'GRN ' . ($firstRow['no_grn'] ?: 'baru') . ' berhasil diimport (' . $groupRows->count() . ' item).',
                ];
            } catch (Exception $e) {
                $this->errorCount++;
                $this->results[] = [
                    'status'  => 'error',
                    'grn'     => $firstRow['no_grn'] ?: $grnKey,
                    'rows'    => $groupRows->pluck('row_num')->toArray(),
                    'message' => 'Baris ' . implode(',', $groupRows->pluck('row_num')->toArray()) . ': ' . $e->getMessage(),
                ];
            }
        }
    }

    /**
     * Proses satu grup baris (satu dokumen GRN) ke TerimaBarangService::store()
     */
    private function processGroup(string $grnKey, Collection $rows): void
    {
        $firstRow = $rows->first();

        // ── Resolve Gudang ──────────────────────────────────────────
        $gudangNm = $firstRow['gudang'];
        if (empty($gudangNm)) {
            throw new Exception("Kolom Gudang (kolom L) wajib diisi.");
        }
        $gudang = MstGudang::where('gudang_nm', 'ILIKE', "%{$gudangNm}%")
                    ->orWhere('gudang_cd', 'ILIKE', $gudangNm)
                    ->where('deleted_st', false)
                    ->first();
        if (!$gudang) {
            throw new Exception("Gudang '{$gudangNm}' tidak ditemukan di master data.");
        }

        // ── Resolve Supplier ────────────────────────────────────────
        $supplierNm = $firstRow['supplier'];
        if (empty($supplierNm)) {
            throw new Exception("Kolom Supplier (kolom M) wajib diisi.");
        }
        $supplier = MstSupplier::where('supplier_nm', 'ILIKE', "%{$supplierNm}%")
                      ->orWhere('supplier_cd', 'ILIKE', $supplierNm)
                      ->where('deleted_st', false)
                      ->first();
        if (!$supplier) {
            throw new Exception("Supplier '{$supplierNm}' tidak ditemukan di master data.");
        }

        // ── Parse Tanggal ────────────────────────────────────────────
        $tglRaw = $firstRow['tanggal'];
        try {
            if (is_numeric($tglRaw)) {
                // Excel serial date
                $tglStr = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tglRaw)->format('Y-m-d');
            } elseif (!empty($tglRaw)) {
                $tglStr = Carbon::parse($tglRaw)->format('Y-m-d');
            } else {
                $tglStr = now()->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            $tglStr = now()->format('Y-m-d');
        }

        // ── Resolve No. GRN ──────────────────────────────────────────
        $noGrn = $firstRow['no_grn'];
        // Jika No. GRN diisi tapi sudah ada, lewati generate (akan divalidasi service)
        if (empty($noGrn)) {
            $noGrn = ''; // Biarkan service generate
        }

        // ── Build Items ──────────────────────────────────────────────
        $items = [];
        foreach ($rows as $row) {
            $kodeBarang = $row['kode_barang'];
            if (empty($kodeBarang)) continue;

            $barang = MstBarang::where('barang_cd', 'ILIKE', $kodeBarang)
                        ->orWhere('barang_nm', 'ILIKE', "%{$kodeBarang}%")
                        ->where('deleted_st', false)
                        ->first();
            if (!$barang) {
                throw new Exception("Barang dengan kode '{$kodeBarang}' di baris {$row['row_num']} tidak ditemukan.");
            }

            $qty = (float) ($row['qty_masuk'] ?? 0);
            if ($qty <= 0) {
                throw new Exception("Qty di baris {$row['row_num']} harus lebih dari 0.");
            }

            $batchNo = $row['batch_no'];
            if (empty($batchNo)) {
                throw new Exception("Kode Batch di baris {$row['row_num']} wajib diisi.");
            }

            // Expired date
            $expiredStr = null;
            if (!empty($row['expired'])) {
                try {
                    $expRaw = $row['expired'];
                    if (is_numeric($expRaw)) {
                        $expiredStr = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expRaw)->format('Y-m-d');
                    } else {
                        $expiredStr = Carbon::parse($expRaw)->format('Y-m-d');
                    }
                } catch (\Throwable $e) {
                    $expiredStr = null;
                }
            }

            $harga    = max(0, (float) ($row['harga'] ?? 0));
            $diskon   = max(0, min(100, (float) ($row['diskon'] ?? 0)));
            $potongan = max(0, (float) ($row['potongan'] ?? 0));
            $ppnTipe  = ($row['ppn'] === 'PPN_11') ? 'PPN_11' : 'NON_PPN';

            $items[] = [
                'barang_id'       => $barang->barang_id,
                'batch_no'        => $batchNo,
                'expired_tgl'     => $expiredStr,
                'grade_cd'        => !empty($row['grade']) ? strtoupper($row['grade']) : null,
                'terima_qty'      => $qty,
                'reject_qty'      => 0,
                'harga_nominal'   => $harga,
                'diskon_persen'   => $diskon,
                'potongan_nominal'=> $potongan,
                'ppn_tipe'        => $ppnTipe,
                'catatan_txt'     => $row['catatan'] ?: null,
            ];
        }

        if (empty($items)) {
            throw new Exception("Grup GRN '{$grnKey}' tidak memiliki item yang valid.");
        }

        $this->service->store([
            'terima_no'   => $noGrn,
            'terima_tgl'  => $tglStr,
            'gudang_id'   => $gudang->gudang_id,
            'supplier_id' => $supplier->supplier_id,
            'suratjalan_no'=> $firstRow['suratjalan'] ?: null,
            'catatan_txt'  => null,
            'items'        => $items,
        ]);
    }
}
