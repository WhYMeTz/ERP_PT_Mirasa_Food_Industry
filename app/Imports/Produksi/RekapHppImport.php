<?php

namespace App\Imports\Produksi;

use App\Models\MasterData\MstGudang;
use App\Models\Produksi\DatProduksiHarian;
use App\Services\Common\CodeGeneratorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class RekapHppImport
{
    public int $successCount = 0;
    public int $skipCount    = 0;
    public array $errors     = [];

    const DATA_START_ROW = 7;

    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Import Buku Rekapitulasi HPP Harian format persis spreadsheet PT Mirasa
     */
    public function import(UploadedFile $file, int $defaultGudangId = 1): void
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        if ($highestRow < self::DATA_START_ROW) {
            throw new Exception('Berkas Excel tidak memiliki data kalender produksi (dimulai dari baris ke-7).');
        }

        // Cari gudang aktif default
        $gudang = MstGudang::where('gudang_id', $defaultGudangId)->where('deleted_st', false)->first()
            ?: MstGudang::where('deleted_st', false)->first();

        $username = Auth::user()?->username ?? 'IMPORT_EXCEL';

        DB::transaction(function () use ($sheet, $highestRow, $gudang, $username) {
            for ($r = self::DATA_START_ROW; $r <= $highestRow; $r++) {
                $cellA = trim((string) $sheet->getCell('A' . $r)->getValue());

                // Berhenti jika sudah sampai baris TOTAL atau RATA-RATA
                $upperA = strtoupper($cellA);
                if (str_contains($upperA, 'TOTAL') || str_contains($upperA, 'RATA') || str_contains($upperA, 'GRAND')) {
                    break;
                }

                $rawTgl = $sheet->getCell('B' . $r)->getValue();
                if (empty($rawTgl) || $rawTgl === '-' || $rawTgl === '.') {
                    $this->skipCount++;
                    continue;
                }

                // Parse tanggal
                $tgl = null;
                if (is_numeric($rawTgl)) {
                    $tgl = Carbon::instance(Date::excelToDateTimeObject($rawTgl))->format('Y-m-d');
                } else {
                    try {
                        // Coba format d/m/y atau d/m/Y
                        $rawTglClean = trim(str_replace('-', '/', (string) $rawTgl));
                        $tgl = Carbon::createFromFormat('d/m/y', $rawTglClean)->format('Y-m-d');
                    } catch (\Exception $e) {
                        try {
                            $tgl = Carbon::parse($rawTgl)->format('Y-m-d');
                        } catch (\Exception $e2) {
                            $this->skipCount++;
                            continue;
                        }
                    }
                }

                // Ambil nilai-nilai numerik
                $singkongQty  = (float) $this->cleanNum($sheet->getCell('C' . $r)->getValue());
                $singkongRp   = (float) $this->cleanNum($sheet->getCell('D' . $r)->getValue());
                $minyakSawit  = (float) $this->cleanNum($sheet->getCell('E' . $r)->getValue());
                $minyakKelapa = (float) $this->cleanNum($sheet->getCell('F' . $r)->getValue());
                $minyakRp     = (float) $this->cleanNum($sheet->getCell('G' . $r)->getValue());
                $minyakRasio  = (float) $this->cleanPercent($sheet->getCell('H' . $r)->getValue(), $singkongQty, $minyakSawit + $minyakKelapa);

                $cngMmbtu     = (float) $this->cleanNum($sheet->getCell('I' . $r)->getValue());
                $cngRp        = (float) $this->cleanNum($sheet->getCell('J' . $r)->getValue());

                $tkLangsung   = (int) $this->cleanNum($sheet->getCell('K' . $r)->getValue());
                $tkTdkLangsung= (int) $this->cleanNum($sheet->getCell('L' . $r)->getValue());
                $tkTraining   = (int) $this->cleanNum($sheet->getCell('M' . $r)->getValue());
                $tkTotalRp    = (float) $this->cleanNum($sheet->getCell('N' . $r)->getValue());

                $bumbuRp      = (float) $this->cleanNum($sheet->getCell('O' . $r)->getValue());
                $kartonBaruRp = (float) $this->cleanNum($sheet->getCell('P' . $r)->getValue());
                $kartonBksRp  = (float) $this->cleanNum($sheet->getCell('Q' . $r)->getValue());
                $plastikHdRp  = (float) $this->cleanNum($sheet->getCell('R' . $r)->getValue());
                $lakbanBsrRp  = (float) $this->cleanNum($sheet->getCell('S' . $r)->getValue());
                $lakbanKclRp  = (float) $this->cleanNum($sheet->getCell('T' . $r)->getValue());
                $taliRafiaRp  = (float) $this->cleanNum($sheet->getCell('U' . $r)->getValue());
                $fotocopyRp   = (float) $this->cleanNum($sheet->getCell('V' . $r)->getValue());
                $sarungPlstkRp= (float) $this->cleanNum($sheet->getCell('W' . $r)->getValue());
                $sarungKainRp = (float) $this->cleanNum($sheet->getCell('X' . $r)->getValue());

                $qcRp         = (float) $this->cleanNum($sheet->getCell('Y' . $r)->getValue());
                $listrikRp    = (float) $this->cleanNum($sheet->getCell('Z' . $r)->getValue());
                $pmlhrMesinRp = (float) $this->cleanNum($sheet->getCell('AA' . $r)->getValue());
                $penysMesinRp = (float) $this->cleanNum($sheet->getCell('AB' . $r)->getValue());
                $limbahPadatRp= (float) $this->cleanNum($sheet->getCell('AC' . $r)->getValue());
                $bahanKimiaRp = (float) $this->cleanNum($sheet->getCell('AD' . $r)->getValue());

                $totalBiaya   = (float) $this->cleanNum($sheet->getCell('AE' . $r)->getValue());
                $totalWipQty  = (float) $this->cleanNum($sheet->getCell('AF' . $r)->getValue());
                $rendemenPct  = (float) $this->cleanPercent($sheet->getCell('AG' . $r)->getValue(), $singkongQty, $totalWipQty);
                $hppPerKg     = (float) $this->cleanNum($sheet->getCell('AH' . $r)->getValue());

                // Lewati baris hari libur / tanpa produksi sama sekali
                if ($singkongQty <= 0 && $totalWipQty <= 0 && $totalBiaya <= 0) {
                    $this->skipCount++;
                    continue;
                }

                // Kalkulasi otomatis jika total biaya atau HPP kosong
                if ($totalBiaya <= 0) {
                    $totalBiaya = $singkongRp + $minyakRp + $cngRp + $tkTotalRp
                                + $bumbuRp + $kartonBaruRp + $kartonBksRp + $plastikHdRp
                                + $lakbanBsrRp + $lakbanKclRp + $taliRafiaRp + $fotocopyRp
                                + $sarungPlstkRp + $sarungKainRp + $qcRp + $listrikRp
                                + $pmlhrMesinRp + $penysMesinRp + $limbahPadatRp + $bahanKimiaRp;
                }

                if ($rendemenPct <= 0 && $singkongQty > 0 && $totalWipQty > 0) {
                    $rendemenPct = round(($totalWipQty / $singkongQty) * 100, 2);
                }

                if ($hppPerKg <= 0 && $totalWipQty > 0 && $totalBiaya > 0) {
                    $hppPerKg = round($totalBiaya / $totalWipQty, 2);
                }

                // Cari apakah sudah ada data produksi pada tanggal ini
                $produksi = DatProduksiHarian::whereDate('produksi_tgl', $tgl)
                    ->where('deleted_st', false)
                    ->first();

                if (!$produksi) {
                    $noProduksi = $this->codeGenerator->generateKodeProduksi($tgl);
                    $produksi = new DatProduksiHarian();
                    $produksi->produksi_no   = $noProduksi;
                    $produksi->produksi_tgl  = $tgl;
                    $produksi->gudang_id     = $gudang->gudang_id;
                    $produksi->shift_cd      = 'A';
                    $produksi->lini_produksi = 'Lini Penggorengan & Keripik';
                    $produksi->batch_wip_no  = 'A / ' . sprintf('%04d', $this->successCount + 1);
                    $produksi->status_cd     = 'POSTED';
                    $produksi->created_by    = $username;
                }

                // Update kolom data
                $produksi->singkong_qty                = $singkongQty;
                $produksi->singkong_nilai              = $singkongRp;
                $produksi->minyak_sawit_qty            = $minyakSawit;
                $produksi->minyak_kelapa_qty           = $minyakKelapa;
                $produksi->minyak_nilai                = $minyakRp;
                $produksi->minyak_rasio_persen         = $minyakRasio;

                $produksi->cng_mmbtu                   = $cngMmbtu;
                $produksi->cng_nilai                   = $cngRp;

                $produksi->tk_langsung_org             = $tkLangsung;
                $produksi->tk_tidak_langsung_org       = $tkTdkLangsung;
                $produksi->tk_training_org             = $tkTraining;
                $produksi->tk_total_nilai              = $tkTotalRp;

                $produksi->bumbu_nilai                 = $bumbuRp;
                $produksi->karton_baru_nilai           = $kartonBaruRp;
                $produksi->karton_bekas_nilai          = $kartonBksRp;
                $produksi->plastik_hd_nilai            = $plastikHdRp;
                $produksi->lakban_besar_nilai          = $lakbanBsrRp;
                $produksi->lakban_kecil_nilai          = $lakbanKclRp;
                $produksi->tali_rafia_nilai            = $taliRafiaRp;
                $produksi->fotocopy_nilai              = $fotocopyRp;
                $produksi->sarung_tangan_plastik_nilai = $sarungPlstkRp;
                $produksi->sarung_tangan_kain_nilai    = $sarungKainRp;

                $produksi->qc_pengawasan_nilai         = $qcRp;
                $produksi->listrik_air_telp_nilai      = $listrikRp;
                $produksi->pemeliharaan_mesin_nilai    = $pmlhrMesinRp;
                $produksi->penyusutan_mesin_nilai      = $penysMesinRp;
                $produksi->limbah_padat_nilai          = $limbahPadatRp;
                $produksi->limbah_kimia_nilai          = $bahanKimiaRp;

                $produksi->total_biaya_produksi        = $totalBiaya;
                $produksi->total_wip_qty               = $totalWipQty;
                $produksi->rendemen_persen             = $rendemenPct;
                $produksi->hpp_per_kg                  = $hppPerKg;

                $produksi->updated_by                  = $username;
                $produksi->catatan_txt                 = 'Import Excel Rekap HPP Harian tanggal ' . date('d/m/Y H:i');
                $produksi->save();

                $this->successCount++;
            }
        });
    }

    protected function cleanNum($val): float
    {
        if (is_null($val) || $val === '' || $val === '-' || $val === '.') {
            return 0.0;
        }
        if (is_numeric($val)) {
            return (float) $val;
        }
        $cleaned = str_replace(['Rp', 'rp', ' ', '.', ','], ['', '', '', '', '.'], (string) $val);
        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }

    protected function cleanPercent($val, float $divider = 0, float $numerator = 0): float
    {
        if (is_null($val) || $val === '' || $val === '-' || $val === '.') {
            return ($divider > 0 && $numerator > 0) ? round(($numerator / $divider) * 100, 2) : 0.0;
        }
        if (is_numeric($val)) {
            // Jika format desimal Excel 0.3283 maka jadikan 32.83%
            $f = (float) $val;
            return $f <= 1.0 && $f > 0 ? round($f * 100, 2) : round($f, 2);
        }
        $str = str_replace(['%', ' '], '', (string) $val);
        $clean = str_replace(',', '.', $str);
        return is_numeric($clean) ? (float) $clean : 0.0;
    }
}
