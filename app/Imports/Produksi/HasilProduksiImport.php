<?php

namespace App\Imports\Produksi;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\Produksi\DatProduksiDtl;
use App\Models\Produksi\DatProduksiOutput;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class HasilProduksiImport
{
    public array $results = [];
    public int   $successCount = 0;
    public int   $errorCount   = 0;

    const DATA_START_ROW = 7;

    public function __construct(
        protected CodeGeneratorService $codeGenerator,
        protected StokService $stokService
    ) {}

    /**
     * Import file excel hasil barang produksi
     */
    public function import(UploadedFile $file): void
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        if ($highestRow < self::DATA_START_ROW) {
            throw new Exception('File Excel tidak memiliki baris data hasil produksi (dimulai dari baris 7).');
        }

        // Cache master data
        $barangMap = MstBarang::where('deleted_st', false)
            ->get()
            ->keyBy(fn($b) => strtoupper(trim($b->barang_cd)));

        $gudangMap = MstGudang::where('deleted_st', false)
            ->get();

        $rows = collect();

        for ($r = self::DATA_START_ROW; $r <= $highestRow; $r++) {
            $kodeBarang = trim((string) $sheet->getCell('D' . $r)->getValue());
            $qtyRaw     = $sheet->getCell('G' . $r)->getValue();

            // Lewati baris kosong
            if (empty($kodeBarang) && (is_null($qtyRaw) || $qtyRaw === '' || $qtyRaw == 0)) {
                continue;
            }

            $tglVal = $sheet->getCell('B' . $r)->getValue();
            $tgl = null;
            if (is_numeric($tglVal)) {
                $tgl = Carbon::instance(Date::excelToDateTimeObject($tglVal))->format('Y-m-d');
            } elseif (!empty($tglVal)) {
                try {
                    $tgl = Carbon::parse($tglVal)->format('Y-m-d');
                } catch (\Exception $e) {
                    $tgl = date('Y-m-d');
                }
            } else {
                $tgl = date('Y-m-d');
            }

            $rows->push([
                'row_num'        => $r,
                'no_dokumen'     => trim((string) $sheet->getCell('A' . $r)->getValue()),
                'tanggal'        => $tgl,
                'batch_no'       => trim((string) $sheet->getCell('C' . $r)->getValue()),
                'kode_barang'    => $kodeBarang,
                'kategori'       => trim((string) $sheet->getCell('F' . $r)->getValue()),
                'qty'            => (float) $qtyRaw,
                'hpp_satuan'     => (float) $sheet->getCell('I' . $r)->getValue(),
                'gudang_input'   => trim((string) $sheet->getCell('J' . $r)->getValue()),
                'shift'          => strtoupper(trim((string) $sheet->getCell('K' . $r)->getValue())) ?: 'A',
                'karton_awal'    => (int) $sheet->getCell('L' . $r)->getValue() ?: null,
                'karton_akhir'   => (int) $sheet->getCell('M' . $r)->getValue() ?: null,
                'catatan'        => trim((string) $sheet->getCell('N' . $r)->getValue()),
            ]);
        }

        if ($rows->isEmpty()) {
            throw new Exception('Tidak ada baris data valid yang dapat diimpor.');
        }

        // Kelompokkan per No Dokumen atau (Tanggal + Shift + Gudang)
        $grouped = $rows->groupBy(function ($item) {
            if (!empty($item['no_dokumen'])) {
                return $item['no_dokumen'];
            }
            return $item['tanggal'] . '_' . $item['shift'] . '_' . $item['gudang_input'];
        });

        foreach ($grouped as $groupKey => $items) {
            try {
                DB::transaction(function () use ($items, $barangMap, $gudangMap) {
                    $firstItem = $items->first();

                    // Resolve Gudang
                    $gudang = null;
                    if (!empty($firstItem['gudang_input'])) {
                        $inputGudangUpper = strtoupper($firstItem['gudang_input']);
                        $gudang = $gudangMap->first(function ($g) use ($inputGudangUpper) {
                            return strtoupper($g->gudang_nm) === $inputGudangUpper
                                || strtoupper($g->gudang_cd ?? '') === $inputGudangUpper
                                || (string) $g->gudang_id === $inputGudangUpper;
                        });
                    }

                    if (!$gudang) {
                        $gudang = $gudangMap->first(); // Default ke gudang pertama jika tidak match
                    }

                    // Tentukan atau generate Nomor Produksi
                    $tglParsed = Carbon::parse($firstItem['tanggal']);
                    $noProduksi = !empty($firstItem['no_dokumen'])
                        ? $firstItem['no_dokumen']
                        : $this->codeGenerator->generateKodeProduksi($tglParsed->format('Y-m-d'));

                    // Cek apakah header produksi sudah ada
                    $produksi = DatProduksiHdr::where('produksi_no', $noProduksi)->first();

                    if (!$produksi) {
                        $shift = in_array($firstItem['shift'], ['A', 'B']) ? $firstItem['shift'] : 'A';
                        $batchWip = !empty($firstItem['batch_no']) ? $firstItem['batch_no'] : ($shift . ' / 0001');

                        $produksi = DatProduksiHdr::create([
                            'produksi_no'         => $noProduksi,
                            'produksi_tgl'        => $firstItem['tanggal'],
                            'gudang_id'           => $gudang->gudang_id,
                            'shift_cd'            => $shift,
                            'lini_produksi'       => 'Lini Penggorengan & Keripik',
                            'batch_wip_no'        => $batchWip,
                            'no_karton_awal'      => $firstItem['karton_awal'] ?? 1,
                            'no_karton_akhir'     => $firstItem['karton_akhir'] ?? count($items),
                            'qty_karton'          => ($firstItem['karton_akhir'] && $firstItem['karton_awal'])
                                ? ($firstItem['karton_akhir'] - $firstItem['karton_awal'] + 1)
                                : count($items),
                            'status_cd'           => 'POSTED',
                            'catatan_txt'         => 'Import Excel Hasil Produksi tanggal ' . date('d/m/Y H:i'),
                            'created_by'          => Auth::user()?->username ?? 'IMPORT_EXCEL',
                        ]);
                    }

                    $totalWipBatch = 0;
                    $totalNilaiBatch = 0;

                    foreach ($items as $row) {
                        $kdBrd = strtoupper($row['kode_barang']);
                        $barang = $barangMap->get($kdBrd);

                        if (!$barang) {
                            throw new Exception("Baris #{$row['row_num']}: Kode barang '{$row['kode_barang']}' tidak ditemukan di Master Data Barang.");
                        }

                        if ($row['qty'] <= 0) {
                            throw new Exception("Baris #{$row['row_num']}: Kuantitas barang harus lebih dari 0.");
                        }

                        $batchItem = !empty($row['batch_no']) ? $row['batch_no'] : $produksi->batch_wip_no;
                        $hppSatuan = $row['hpp_satuan'] > 0 ? $row['hpp_satuan'] : (float) ($barang->harga_beli_pokok ?? 14500);
                        $totalNilai = round($row['qty'] * $hppSatuan, 2);

                        // Kategori & Jenis mapping
                        $kat = !empty($row['kategori']) ? strtoupper($row['kategori']) : 'ASIN_BARCO';
                        $jenisCd = (strtoupper($barang->jenis_barang ?? '') === 'FG' || $barang->isFinishGood()) ? 'FG' : 'WIP';
                        $satuanCd = $barang->satuan?->satuan_nm ?? ($jenisCd === 'FG' ? 'DUS' : 'KG');

                        DatProduksiDtl::create([
                            'produksi_id'     => $produksi->produksi_id,
                            'barang_id'       => $barang->barang_id,
                            'jenis_cd'        => $jenisCd,
                            'kategori_output' => $kat,
                            'qty_kg'          => $row['qty'],
                            'qty_hasil'       => $row['qty'],
                            'satuan_cd'       => $satuanCd,
                            'batch_no'        => $batchItem,
                            'hpp_satuan'      => $hppSatuan,
                            'total_nilai'     => $totalNilai,
                            'keterangan_txt'  => $row['catatan'] ?: 'Import Excel',
                            'created_by'      => Auth::user()?->username ?? 'IMPORT_EXCEL',
                        ]);

                        // Masukkan ke stok fisik & audit ledger
                        $expiredDate = Carbon::parse($produksi->produksi_tgl)->addMonths(6)->format('Y-m-d');
                        $this->stokService->addStock(
                            (int) $produksi->gudang_id,
                            (int) $barang->barang_id,
                            $batchItem,
                            (float) $row['qty'],
                            $expiredDate,
                            $produksi->produksi_no,
                            "Hasil Produksi {$produksi->produksi_no} (Import Excel)",
                            $hppSatuan
                        );

                        $totalWipBatch += $row['qty'];
                        $totalNilaiBatch += $totalNilai;
                        $this->successCount++;
                    }

                    // Update akumulasi WIP di header produksi jika belum ada biaya riil
                    $produksi->total_wip_qty = (float) $produksi->total_wip_qty + $totalWipBatch;
                    if ($produksi->total_biaya_produksi <= 0) {
                        $produksi->total_biaya_produksi = $totalNilaiBatch;
                        $produksi->hpp_per_kg = $totalWipBatch > 0 ? round($totalNilaiBatch / $totalWipBatch, 2) : 0;
                    }
                    $produksi->save();

                    $this->results[] = [
                        'status'  => 'success',
                        'message' => "Dokumen {$produksi->produksi_no} berhasil diimpor (" . count($items) . " item output).",
                    ];
                });
            } catch (Exception $e) {
                $this->errorCount++;
                $this->results[] = [
                    'status'  => 'error',
                    'message' => "Grup [{$groupKey}]: " . $e->getMessage(),
                ];
            }
        }
    }
}
