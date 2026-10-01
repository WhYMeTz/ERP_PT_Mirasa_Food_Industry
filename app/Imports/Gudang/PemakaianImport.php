<?php

namespace App\Imports\Gudang;

use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Services\Gudang\PemakaianService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\UploadedFile;

class PemakaianImport
{
    protected PemakaianService $service;

    public array $results      = [];
    public int   $successCount = 0;
    public int   $errorCount   = 0;

    // Kolom template (baris 7 ke bawah, huruf kolom Excel)
    // A = No. Dokumen   B = Tanggal       C = Kode Batch
    // D = Kode Barang   G = Keterangan    H = Qty Keluar
    // I = Harga Satuan  L = Gudang Asal   N = Catatan

    const DATA_START_ROW = 7;

    public function __construct(PemakaianService $service)
    {
        $this->service = $service;
    }

    /**
     * Proses file Excel pemakaian yang di-upload.
     * Baris dikelompokkan berdasarkan No. Dokumen (kolom A),
     * lalu memanggil PemakaianService::store() per dokumen.
     */
    public function import(UploadedFile $file): void
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();
        $highestRow  = $sheet->getHighestDataRow();

        if ($highestRow < self::DATA_START_ROW) {
            throw new Exception('File tidak memiliki data pemakaian (baris data dimulai dari baris 7).');
        }

        // ── Baca semua baris valid ────────────────────────────────────
        $rows = collect();
        for ($rowNum = self::DATA_START_ROW; $rowNum <= $highestRow; $rowNum++) {
            $kodeBarang = trim((string) $sheet->getCell('D' . $rowNum)->getValue());
            $qty        = $sheet->getCell('H' . $rowNum)->getValue();

            if (empty($kodeBarang) && (is_null($qty) || $qty === '' || $qty == 0)) {
                continue; // Skip baris kosong
            }

            $rows->push([
                'row_num'    => $rowNum,
                'no_dokumen' => trim((string) $sheet->getCell('A' . $rowNum)->getValue()),
                'tanggal'    => $sheet->getCell('B' . $rowNum)->getValue(),
                'batch_no'   => trim((string) $sheet->getCell('C' . $rowNum)->getValue()),
                'kode_barang'=> $kodeBarang,
                'keterangan' => trim((string) $sheet->getCell('G' . $rowNum)->getValue()),
                'qty_keluar' => $qty,
                'harga'      => $sheet->getCell('I' . $rowNum)->getValue(),
                'gudang'     => trim((string) $sheet->getCell('L' . $rowNum)->getValue()),
                'catatan'    => trim((string) $sheet->getCell('N' . $rowNum)->getValue()),
            ]);
        }

        if ($rows->isEmpty()) {
            throw new Exception('Tidak ada baris data yang valid di file Excel.');
        }

        // ── Kelompokkan per No. Dokumen ──────────────────────────────
        // Jika No. Dokumen kosong, kelompokkan per gudang+tanggal+keterangan
        $groups = $rows->groupBy(function ($row) {
            return !empty($row['no_dokumen'])
                ? $row['no_dokumen']
                : ($row['gudang'] . '|' . $row['tanggal'] . '|' . ($row['keterangan'] ?: 'PRODUKSI'));
        });

        foreach ($groups as $groupKey => $groupRows) {
            $firstRow = $groupRows->first();
            try {
                $this->processGroup($groupKey, $groupRows);
                $this->successCount++;
                $this->results[] = [
                    'status'  => 'success',
                    'doc'     => $firstRow['no_dokumen'] ?: 'AUTO',
                    'rows'    => $groupRows->pluck('row_num')->toArray(),
                    'message' => 'Dokumen ' . ($firstRow['no_dokumen'] ?: 'baru') . ' berhasil diimport (' . $groupRows->count() . ' item).',
                ];
            } catch (Exception $e) {
                $this->errorCount++;
                $this->results[] = [
                    'status'  => 'error',
                    'doc'     => $firstRow['no_dokumen'] ?: $groupKey,
                    'rows'    => $groupRows->pluck('row_num')->toArray(),
                    'message' => 'Baris ' . implode(',', $groupRows->pluck('row_num')->toArray()) . ': ' . $e->getMessage(),
                ];
            }
        }
    }

    /**
     * Proses satu grup baris (satu dokumen pengeluaran) ke PemakaianService::store()
     */
    private function processGroup(string $groupKey, Collection $rows): void
    {
        $firstRow = $rows->first();

        // ── Resolve Gudang ───────────────────────────────────────────
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

        // ── Parse Tanggal ────────────────────────────────────────────
        $tglRaw = $firstRow['tanggal'];
        try {
            if (is_numeric($tglRaw)) {
                $tglStr = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tglRaw)->format('Y-m-d');
            } elseif (!empty($tglRaw)) {
                $tglStr = Carbon::parse($tglRaw)->format('Y-m-d');
            } else {
                $tglStr = now()->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            $tglStr = now()->format('Y-m-d');
        }

        // ── Tujuan Pemakaian ─────────────────────────────────────────
        $tujuan = !empty($firstRow['keterangan']) ? strtoupper($firstRow['keterangan']) : 'PRODUKSI';

        // ── Build Items ───────────────────────────────────────────────
        $items = [];
        foreach ($rows as $row) {
            $kodeBarang = $row['kode_barang'];
            if (empty($kodeBarang)) continue;

            $barang = MstBarang::where('barang_cd', 'ILIKE', $kodeBarang)
                        ->orWhere('barang_nm', 'ILIKE', "%{$kodeBarang}%")
                        ->where('deleted_st', false)
                        ->first();
            if (!$barang) {
                throw new Exception("Barang '{$kodeBarang}' di baris {$row['row_num']} tidak ditemukan.");
            }

            $batchNo = $row['batch_no'];
            if (empty($batchNo)) {
                throw new Exception("Kode Batch di baris {$row['row_num']} wajib diisi.");
            }

            $qty = (float) ($row['qty_keluar'] ?? 0);
            if ($qty <= 0) {
                throw new Exception("Qty Keluar di baris {$row['row_num']} harus lebih dari 0.");
            }

            // Validasi batch ada di gudang
            $stokBatch = DatStokBatch::where('gudang_id', $gudang->gudang_id)
                ->where('barang_id', $barang->barang_id)
                ->where('batch_no', $batchNo)
                ->where('deleted_st', false)
                ->first();

            if (!$stokBatch) {
                throw new Exception("Batch '{$batchNo}' untuk barang '{$barang->barang_nm}' tidak ditemukan di gudang '{$gudang->gudang_nm}' (baris {$row['row_num']}).");
            }

            if ((float) $stokBatch->sisa_qty < $qty) {
                throw new Exception("Stok batch '{$batchNo}' tidak mencukupi. Sisa: " . number_format($stokBatch->sisa_qty, 2) . ", Dibutuhkan: " . number_format($qty, 2) . " (baris {$row['row_num']}).");
            }

            $items[] = [
                'barang_id'      => $barang->barang_id,
                'batch_no'       => $batchNo,
                'qty_keluar'     => $qty,
                'harga_satuan'   => (float) ($row['harga'] ?? 0),
                'keterangan_txt' => !empty($row['catatan']) ? $row['catatan'] : $tujuan,
            ];
        }

        if (empty($items)) {
            throw new Exception("Grup dokumen '{$groupKey}' tidak memiliki item yang valid.");
        }

        $this->service->store([
            'pakai_no'        => $firstRow['no_dokumen'] ?: '',
            'pakai_tgl'       => $tglStr,
            'gudang_id'       => $gudang->gudang_id,
            'tujuan_pemakaian'=> $tujuan,
            'catatan_txt'     => null,
            'items'           => $items,
        ]);
    }
}
