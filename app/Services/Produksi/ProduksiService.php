<?php

namespace App\Services\Produksi;

use App\Models\Gudang\DatPakaiHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\Gudang\DatStokLedger;
use App\Models\MasterData\MstBarang;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\Produksi\DatProduksiDtl;
use App\Models\Produksi\DatProduksiAdjustmentLog;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use App\Services\Manajemen\PayrollHarianService;
use App\Services\Keuangan\JurnalService;
use App\Services\Keuangan\PengaturanJurnalService;
use App\Services\Produksi\TarifProduksiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProduksiService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator,
        protected StokService $stokService,
        protected TarifProduksiService $tarifService,
        protected JurnalService $jurnalService,
        protected PengaturanJurnalService $pengaturanJurnalService
    ) {}

    /**
     * Mengambil laporan bulanan HPP Harian & Rendemen (Format Excel Asli PT Mirasa).
     */
    public function getMonthlyReport(int $year, int $month, ?string $lokasiPabrik = null): array
    {
        $allRecords = DatProduksiHdr::with(['pemakaianBahan.details.barang', 'gudang', 'outputs.barang'])
            ->whereYear('produksi_tgl', $year)
            ->whereMonth('produksi_tgl', $month)
            ->where('deleted_st', false)
            ->orderBy('produksi_tgl', 'asc')
            ->orderBy('shift_cd', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $isBmbFn = fn($r) => in_array(strtoupper(trim((string)($r->lokasi_pabrik ?? 'MFI'))), ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);

        $countTotalHari = $allRecords->groupBy(fn($r) => Carbon::parse($r->produksi_tgl)->toDateString())->count();
        $countBaratHari = $allRecords->filter(fn($r) => !$isBmbFn($r))->groupBy(fn($r) => Carbon::parse($r->produksi_tgl)->toDateString())->count();
        $countTimurHari = $allRecords->filter($isBmbFn)->groupBy(fn($r) => Carbon::parse($r->produksi_tgl)->toDateString())->count();

        // Filter jika dipilih spesifik MFI atau BMB
        $records = $allRecords;
        if (!empty($lokasiPabrik) && in_array(strtoupper($lokasiPabrik), ['MFI', 'BMB', 'BARAT', 'TIMUR'])) {
            $isTargetBmb = in_array(strtoupper($lokasiPabrik), ['BMB', 'TIMUR']);
            $records = $allRecords->filter(function ($r) use ($isBmbFn, $isTargetBmb) {
                return $isTargetBmb ? $isBmbFn($r) : !$isBmbFn($r);
            });
        }

        // Filter dokumen yang telah selesai dilakukan penarikan & finalisasi HPP oleh Finance
        $finalizedRecords = $records->filter(function ($r) {
            return ((float) $r->cng_nilai > 0 || (float) $r->tk_total_nilai > 0 || str_contains($r->catatan_txt ?? '', '[HPP FINAL') || str_contains($r->catatan_txt ?? '', 'HPP Finance'));
        });
        $hasFinalized = $finalizedRecords->isNotEmpty();

        // Hitung Grand Total Kumulatif Bulanan
        $totalWipSum = 0;
        foreach ($records as $r) {
            $w = (float) $r->total_wip_qty;
            if ($w <= 0 && $r->outputs && $r->outputs->isNotEmpty()) {
                $w = (float) $r->outputs->sum('qty_kg');
            }
            $totalWipSum += $w;
        }

        // Hitung Grand Total WIP khusus dari dokumen yang telah final HPP
        $finalizedWipSum = 0;
        foreach ($finalizedRecords as $r) {
            $w = (float) $r->total_wip_qty;
            if ($w <= 0 && $r->outputs && $r->outputs->isNotEmpty()) {
                $w = (float) $r->outputs->sum('qty_kg');
            }
            $finalizedWipSum += $w;
        }

        $totals = [
            'total_karton'                => $hasFinalized ? (int) $finalizedRecords->sum('qty_karton') : 0,
            'singkong_qty'                => $hasFinalized ? (float) $finalizedRecords->sum('singkong_qty') : 0,
            'singkong_nilai'              => $hasFinalized ? (float) $finalizedRecords->sum('singkong_nilai') : 0,
            'minyak_sawit_qty'            => $hasFinalized ? (float) $finalizedRecords->sum('minyak_sawit_qty') : 0,
            'minyak_kelapa_qty'           => $hasFinalized ? (float) $finalizedRecords->sum('minyak_kelapa_qty') : 0,
            'minyak_nilai'                => $hasFinalized ? (float) $finalizedRecords->sum('minyak_nilai') : 0,
            'cng_nm3'                     => $hasFinalized ? (float) $finalizedRecords->sum('cng_nm3') : 0,
            'cng_mmbtu'                   => $hasFinalized ? (float) $finalizedRecords->sum('cng_mmbtu') : 0,
            'cng_nilai'                   => $hasFinalized ? (float) $finalizedRecords->sum('cng_nilai') : 0,
            'tk_jumlah_org'               => $hasFinalized ? (int) $finalizedRecords->sum(function ($r) {
                return $r->tk_jumlah_org ?: ($r->tk_langsung_org + $r->tk_tidak_langsung_org + $r->tk_training_org);
            }) : 0,
            'tk_langsung_org'             => $hasFinalized ? (int) $finalizedRecords->sum('tk_langsung_org') : 0,
            'tk_tidak_langsung_org'       => $hasFinalized ? (int) $finalizedRecords->sum('tk_tidak_langsung_org') : 0,
            'tk_training_org'             => $hasFinalized ? (int) $finalizedRecords->sum('tk_training_org') : 0,
            'tk_total_nilai'              => $hasFinalized ? (float) $finalizedRecords->sum('tk_total_nilai') : 0,
            'bumbu_nilai'                 => $hasFinalized ? (float) $finalizedRecords->sum('bumbu_nilai') : 0,
            'karton_baru_nilai'           => $hasFinalized ? (float) $finalizedRecords->sum('karton_baru_nilai') : 0,
            'karton_bekas_nilai'          => $hasFinalized ? (float) $finalizedRecords->sum('karton_bekas_nilai') : 0,
            'plastik_hd_nilai'            => $hasFinalized ? (float) $finalizedRecords->sum('plastik_hd_nilai') : 0,
            'lakban_besar_nilai'          => $hasFinalized ? (float) $finalizedRecords->sum('lakban_besar_nilai') : 0,
            'lakban_kecil_nilai'          => $hasFinalized ? (float) $finalizedRecords->sum('lakban_kecil_nilai') : 0,
            'tali_rafia_nilai'            => $hasFinalized ? (float) $finalizedRecords->sum('tali_rafia_nilai') : 0,
            'fotocopy_nilai'              => $hasFinalized ? (float) $finalizedRecords->sum('fotocopy_nilai') : 0,
            'sarung_tangan_plastik_nilai' => $hasFinalized ? (float) $finalizedRecords->sum('sarung_tangan_plastik_nilai') : 0,
            'sarung_tangan_kain_nilai'    => $hasFinalized ? (float) $finalizedRecords->sum('sarung_tangan_kain_nilai') : 0,
            'qc_pengawasan_nilai'         => $hasFinalized ? (float) $finalizedRecords->sum('qc_pengawasan_nilai') : 0,
            'listrik_air_telp_nilai'      => $hasFinalized ? (float) $finalizedRecords->sum('listrik_air_telp_nilai') : 0,
            'pemeliharaan_mesin_nilai'    => $hasFinalized ? (float) $finalizedRecords->sum('pemeliharaan_mesin_nilai') : 0,
            'penyusutan_mesin_nilai'      => $hasFinalized ? (float) $finalizedRecords->sum('penyusutan_mesin_nilai') : 0,
            'limbah_padat_nilai'          => $hasFinalized ? (float) $finalizedRecords->sum('limbah_padat_nilai') : 0,
            'limbah_kimia_nilai'          => $hasFinalized ? (float) $finalizedRecords->sum('limbah_kimia_nilai') : 0,
            'total_biaya_produksi'        => $hasFinalized ? (float) $finalizedRecords->sum('total_biaya_produksi') : 0,

            // Hasil Output WIP (Kg) Sesuai Format Asli Excel - Hanya akumulasi jika sudah final HPP
            'ifl_qty'                     => $hasFinalized ? (float) $finalizedRecords->sum('ifl_qty') : 0,
            'asin_barco_qty'              => $hasFinalized ? (float) $finalizedRecords->sum('asin_barco_qty') : 0,
            'asin_sawit_qty'              => $hasFinalized ? (float) $finalizedRecords->sum('asin_sawit_qty') : 0,
            'no_salt_qty'                 => $hasFinalized ? (float) $finalizedRecords->sum('no_salt_qty') : 0,
            'ucamp_qty'                   => $hasFinalized ? (float) $finalizedRecords->sum('ucamp_qty') : 0,
            'balo_gelombang_qty'          => $hasFinalized ? (float) $finalizedRecords->sum('balo_gelombang_qty') : 0,
            'balqi_qty'                   => $hasFinalized ? (float) $finalizedRecords->sum('balqi_qty') : 0,
            'berko_qty'                   => $hasFinalized ? (float) $finalizedRecords->sum('berko_qty') : 0,
            'berko_me_qty'                => $hasFinalized ? (float) $finalizedRecords->sum('berko_me_qty') : 0,
            'total_berko_qty'             => $hasFinalized ? (float) $finalizedRecords->sum('total_berko_qty') : 0,
            'total_wip_qty'               => $hasFinalized ? (float) $finalizedWipSum : 0,
            'finalized_wip_qty'           => (float) $finalizedWipSum,
        ];

        // Rasio & Rata-rata Tertimbang (Weighted Averages)
        $totals['minyak_rasio_persen'] = $totals['singkong_qty'] > 0
            ? (($totals['minyak_sawit_qty'] + $totals['minyak_kelapa_qty']) / $totals['singkong_qty']) * 100
            : 0;

        $totals['berko_persen'] = $totals['total_wip_qty'] > 0
            ? ($totals['total_berko_qty'] / $totals['total_wip_qty']) * 100
            : 0;

        $totals['berko_biasa_persen'] = $totals['total_wip_qty'] > 0
            ? ($totals['berko_qty'] / $totals['total_wip_qty']) * 100
            : 0;

        $totals['berko_me_persen'] = $totals['total_wip_qty'] > 0
            ? ($totals['berko_me_qty'] / $totals['total_wip_qty']) * 100
            : 0;

        $totals['rendemen_persen'] = $totals['singkong_qty'] > 0
            ? ($totals['total_wip_qty'] / $totals['singkong_qty']) * 100
            : 0;

        // HPP per kg dihitung riil hanya dari dokumen yang sudah difinalisasi HPP oleh Finance
        $totals['hpp_per_kg'] = ($hasFinalized && $finalizedWipSum > 0)
            ? ($totals['total_biaya_produksi'] / $finalizedWipSum)
            : 0;

        // Susun daftar baris kalender per hari (1 s/d 28/30/31) seperti format spreadsheet Mirasa
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dayNamesIndo = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $days = [];
        $calcEntityData = function($eRecords, $entityCd) {
            if ($eRecords->isEmpty()) {
                return [
                    'exists'          => false,
                    'entity_cd'       => $entityCd,
                    'entity_nm'       => $entityCd === 'BMB' ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry',
                    'produksi_id'     => null,
                    'produksi_no'     => null,
                    'is_hpp_final'    => false,
                    'bf_aktif_count'  => 0,
                    'singkong_qty'    => 0,
                    'singkong_nilai'  => 0,
                    'minyak_qty'      => 0,
                    'minyak_nilai'    => 0,
                    'cng_mmbtu'       => 0,
                    'cng_nilai'       => 0,
                    'tk_jumlah_org'   => 0,
                    'tk_total_nilai'  => 0,
                    'total_wip_qty'   => 0,
                    'rendemen_persen' => 0,
                    'total_biaya'     => 0,
                    'hpp_per_kg'      => 0,
                ];
            }

            $first = $eRecords->first();
            $singkongQty = (float) $eRecords->sum('singkong_qty');
            $singkongNilai = (float) $eRecords->sum('singkong_nilai');
            $sawitQty = (float) $eRecords->sum('minyak_sawit_qty');
            $kelapaQty = (float) $eRecords->sum('minyak_kelapa_qty');
            $minyakNilai = (float) $eRecords->sum('minyak_nilai');
            $cngMmbtu = (float) $eRecords->sum('cng_mmbtu');
            $cngNilai = (float) $eRecords->sum('cng_nilai');
            $tkJumlah = (int) $eRecords->sum(fn($r) => $r->tk_jumlah_org ?: ($r->tk_langsung_org + $r->tk_tidak_langsung_org + $r->tk_training_org));
            $tkNilai = (float) $eRecords->sum('tk_total_nilai');
            $bfAktif = (int) $eRecords->sum('bf_aktif_count');

            $wipSum = 0;
            foreach ($eRecords as $rec) {
                $w = (float) $rec->total_wip_qty;
                if ($w <= 0 && $rec->outputs && $rec->outputs->isNotEmpty()) {
                    $w = (float) $rec->outputs->sum('qty_kg');
                }
                $wipSum += $w;
            }

            $rendemen = $singkongQty > 0 ? round(($wipSum / $singkongQty) * 100, 2) : 0;
            $totalBiaya = (float) $eRecords->sum('total_biaya_produksi');
            if ($totalBiaya <= 0) {
                $totalBiaya = (float) $eRecords->sum(function($r) {
                    return (float)$r->singkong_nilai + (float)$r->minyak_nilai + (float)$r->cng_nilai
                        + (float)$r->tk_total_nilai + (float)$r->bumbu_nilai + (float)$r->karton_baru_nilai
                        + (float)$r->karton_bekas_nilai + (float)$r->plastik_hd_nilai + (float)$r->lakban_besar_nilai
                        + (float)$r->lakban_kecil_nilai + (float)$r->tali_rafia_nilai + (float)$r->fotocopy_nilai
                        + (float)$r->sarung_tangan_plastik_nilai + (float)$r->sarung_tangan_kain_nilai
                        + (float)$r->qc_pengawasan_nilai + (float)$r->listrik_air_telp_nilai
                        + (float)$r->pemeliharaan_mesin_nilai + (float)$r->penyusutan_mesin_nilai
                        + (float)$r->limbah_padat_nilai + (float)$r->limbah_kimia_nilai;
                });
            }
            $hppPerKg = $wipSum > 0 ? round($totalBiaya / $wipSum, 2) : 0;
            $isFinal = $eRecords->contains(fn($r) => (float)$r->cng_nilai > 0 || (float)$r->tk_total_nilai > 0 || str_contains($r->catatan_txt ?? '', '[HPP FINAL'));

            return [
                'exists'          => true,
                'entity_cd'       => $entityCd,
                'entity_nm'       => $entityCd === 'BMB' ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry',
                'produksi_id'     => $first->produksi_id,
                'produksi_no'     => $first->produksi_no,
                'is_hpp_final'    => $isFinal,
                'bf_aktif_count'  => $bfAktif,
                'singkong_qty'    => $singkongQty,
                'singkong_nilai'  => $singkongNilai,
                'minyak_qty'      => $sawitQty + $kelapaQty,
                'minyak_nilai'    => $minyakNilai,
                'cng_mmbtu'       => $cngMmbtu,
                'cng_nilai'       => $cngNilai,
                'tk_jumlah_org'   => $tkJumlah,
                'tk_total_nilai'  => $tkNilai,
                'total_wip_qty'   => $wipSum,
                'rendemen_persen' => $rendemen,
                'total_biaya'     => $totalBiaya,
                'hpp_per_kg'      => $hppPerKg,
            ];
        };

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = Carbon::createFromDate($year, $month, $day);
            $dayOfWeek = $dateObj->dayOfWeek;
            $hariNm = $dayNamesIndo[$dayOfWeek] ?? 'Senin';

            $dayRecords = $records->filter(function ($r) use ($dateObj) {
                return Carbon::parse($r->produksi_tgl)->isSameDay($dateObj);
            });

            if ($dayRecords->isNotEmpty()) {
                // KONSOLIDASI HARIAN (AKUMULASI SHIFT A + B KE DALAM 1 BARIS TANGGAL SESUAI SPREADSHEET EXCEL)
                $daySingkongQty = (float) $dayRecords->sum('singkong_qty');
                $daySingkongNilai = (float) $dayRecords->sum('singkong_nilai');
                $daySawitQty = (float) $dayRecords->sum('minyak_sawit_qty');
                $dayKelapaQty = (float) $dayRecords->sum('minyak_kelapa_qty');
                $dayMinyakNilai = (float) $dayRecords->sum('minyak_nilai');
                $dayMinyakRasio = $daySingkongQty > 0 ? (($daySawitQty + $dayKelapaQty) / $daySingkongQty) * 100 : 0;

                $dayCngMmbtu = (float) $dayRecords->sum('cng_mmbtu');
                $dayCngNm3 = (float) $dayRecords->sum('cng_nm3');
                if ($dayCngNm3 <= 0 && $dayCngMmbtu > 0) {
                    $dayCngNm3 = round($dayCngMmbtu * 27.921, 4);
                }
                $dayCngNilai = (float) $dayRecords->sum('cng_nilai');
                $cngFirstRec = $dayRecords->first();
                $dayCngKursJisdor = (float) ($cngFirstRec->cng_kurs_jisdor > 0 ? $cngFirstRec->cng_kurs_jisdor : 0);
                $dayCngTarifUsd = (float) ($cngFirstRec->cng_tarif_usd > 0 ? $cngFirstRec->cng_tarif_usd : 14.50);
                $dayCngTarif = (float) ($cngFirstRec->cng_tarif > 0 ? $cngFirstRec->cng_tarif : 0);
                if ($dayCngKursJisdor <= 0) {
                    $dayCngKursJisdor = (float) $this->tarifService->getJisdorRate($dateObj->format('Y-m-d'), false);
                    if ($dayCngKursJisdor <= 0) {
                        $dayCngKursJisdor = 18138.00;
                    }
                }
                if ($dayCngTarif <= 0) {
                    $dayCngTarif = round($dayCngTarifUsd * $dayCngKursJisdor, 2);
                }

                $dayBfAktif = (int) $dayRecords->sum('bf_aktif_count');
                $dayCngPerBf = $dayBfAktif > 0 ? round($dayCngMmbtu / $dayBfAktif, 3) : 0;

                // Komparasi Entitas (MFI vs BMB) untuk Audit
                $mfiRecords = $dayRecords->filter(fn($r) => !$isBmbFn($r));
                $bmbRecords = $dayRecords->filter($isBmbFn);
                $mfiSummary = $calcEntityData($mfiRecords, 'MFI');
                $bmbSummary = $calcEntityData($bmbRecords, 'BMB');

                $dayTkJumlah = (int) $dayRecords->sum(function ($r) {
                    return $r->tk_jumlah_org ?: ($r->tk_langsung_org + $r->tk_tidak_langsung_org + $r->tk_training_org);
                });
                $dayTkLangsung = (int) $dayRecords->sum('tk_langsung_org');
                $dayTkTidakLangsung = (int) $dayRecords->sum('tk_tidak_langsung_org');
                $dayTkTraining = (int) $dayRecords->sum('tk_training_org');
                $dayTkTotalNilai = (float) $dayRecords->sum('tk_total_nilai');

                $dayBumbuNilai = (float) $dayRecords->sum('bumbu_nilai');
                $dayBumbuQty = (float) $dayRecords->sum('bumbu_qty');
                $dayKartonBaru = (float) $dayRecords->sum('karton_baru_nilai');
                $dayKartonBaruQty = (float) $dayRecords->sum('karton_baru_qty');
                $dayKartonBekas = (float) $dayRecords->sum('karton_bekas_nilai');
                $dayKartonBekasQty = (float) $dayRecords->sum('karton_bekas_qty');
                $dayPlastikHd = (float) $dayRecords->sum('plastik_hd_nilai');
                $dayPlastikHdQty = (float) $dayRecords->sum('plastik_hd_qty');
                $dayLakbanBesar = (float) $dayRecords->sum('lakban_besar_nilai');
                $dayLakbanBesarQty = (float) $dayRecords->sum('lakban_besar_qty');
                $dayLakbanKecil = (float) $dayRecords->sum('lakban_kecil_nilai');
                $dayLakbanKecilQty = (float) $dayRecords->sum('lakban_kecil_qty');
                $dayTaliRafia = (float) $dayRecords->sum('tali_rafia_nilai');
                $dayTaliRafiaQty = (float) $dayRecords->sum('tali_rafia_qty');

                $dayFotocopy = (float) $dayRecords->sum('fotocopy_nilai');
                $daySarungPlastik = (float) $dayRecords->sum('sarung_tangan_plastik_nilai');
                $daySarungKain = (float) $dayRecords->sum('sarung_tangan_kain_nilai');
                $dayQc = (float) $dayRecords->sum('qc_pengawasan_nilai');
                $dayListrik = (float) $dayRecords->sum('listrik_air_telp_nilai');
                $dayPemeliharaan = (float) $dayRecords->sum('pemeliharaan_mesin_nilai');
                $dayPenyusutan = (float) $dayRecords->sum('penyusutan_mesin_nilai');
                $dayLimbahPadat = (float) $dayRecords->sum('limbah_padat_nilai');
                $dayLimbahKimia = (float) $dayRecords->sum('limbah_kimia_nilai');
                $dayTotalBiaya = (float) $dayRecords->sum('total_biaya_produksi');

                // Output Hasil Produksi WIP (Total Akumulatif)
                $dayIfl = 0;
                $dayAsinBarco = (float) $dayRecords->sum('asin_barco_qty');
                $dayAsinSawit = (float) $dayRecords->sum('asin_sawit_qty');
                $dayNoSalt = (float) $dayRecords->sum('no_salt_qty');
                $dayUcamp = (float) $dayRecords->sum('ucamp_qty');
                $dayBalo = (float) $dayRecords->sum('balo_gelombang_qty');
                $dayBalqi = (float) $dayRecords->sum('balqi_qty');
                $dayBerko = (float) $dayRecords->sum('berko_qty');
                $dayBerkoMe = (float) $dayRecords->sum('berko_me_qty');
                $dayTotalBerko = (float) $dayRecords->sum('total_berko_qty');
                if ($dayTotalBerko <= 0 && ($dayBerko > 0 || $dayBerkoMe > 0)) {
                    $dayTotalBerko = $dayBerko + $dayBerkoMe;
                }

                $dayTotalWip = 0;
                foreach ($dayRecords as $rec) {
                    $recWip = (float) $rec->total_wip_qty;
                    if ($recWip <= 0 && $rec->outputs && $rec->outputs->isNotEmpty()) {
                        $recWip = (float) $rec->outputs->sum('qty_kg');
                    }
                    $dayTotalWip += $recWip;

                    $recIfl = (float) $rec->ifl_qty;
                    if ($recIfl <= 0) {
                        $liniUpper = strtoupper($rec->lini_produksi ?? '');
                        if (str_contains($liniUpper, 'IFL') || str_contains($liniUpper, 'IFM')) {
                            $manualSum = (float) $rec->asin_barco_qty + (float) $rec->asin_sawit_qty + (float) $rec->no_salt_qty + (float) $rec->balo_gelombang_qty + (float) $rec->ucamp_qty + (float) $rec->balqi_qty;
                            if ($manualSum == 0 && $recWip > 0) {
                                $recIfl = max(0, $recWip - (float) $rec->total_berko_qty);
                            }
                        }
                    }
                    $dayIfl += $recIfl;
                }

                if ($dayTotalWip <= 0) {
                    $dayTotalWip = $dayIfl + $dayAsinBarco + $dayAsinSawit + $dayNoSalt + $dayUcamp + $dayBalo + $dayBalqi + $dayTotalBerko;
                }

                $dayBerkoPersen = $dayTotalWip > 0 ? round(($dayTotalBerko / $dayTotalWip) * 100, 2) : 0;
                $dayBerkoBiasaPersen = $dayTotalWip > 0 ? round(($dayBerko / $dayTotalWip) * 100, 2) : 0;
                $dayBerkoMePersen = $dayTotalWip > 0 ? round(($dayBerkoMe / $dayTotalWip) * 100, 2) : 0;
                $dayRendemen = $daySingkongQty > 0 && $dayTotalWip > 0 ? round(($dayTotalWip / $daySingkongQty) * 100, 2) : 0;
                $dayHpp = $dayTotalWip > 0 && $dayTotalBiaya > 0 ? round($dayTotalBiaya / $dayTotalWip, 2) : 0;

                // Shift Metadata
                $shiftCodes = $dayRecords->pluck('shift_cd')->filter()->unique()->values()->all();
                $shiftCodeStr = !empty($shiftCodes) ? implode(' + ', $shiftCodes) : 'A';
                $shiftCount = $dayRecords->count();
                $firstRec = $dayRecords->first();

                // Lini & Batch Metadata
                $liniList = $dayRecords->pluck('lini_produksi')->filter()->unique()->values()->all();
                $liniStr = !empty($liniList) ? implode(', ', $liniList) : 'Produksi Reguler';

                // Ekstraksi Batch & Akumulasi HPP per Komoditas (Utama vs Berko)
                $mainBatches = [];
                $berkoBatches = [];
                $dayUtamaKg = 0;
                $dayUtamaVal = 0;
                $dayBerkoKg = 0;
                $dayBerkoVal = 0;

                foreach ($dayRecords as $rec) {
                    if ($rec->outputs && $rec->outputs->isNotEmpty()) {
                        foreach ($rec->outputs as $out) {
                            $bNo = trim($out->batch_no ?? '');
                            $qty = (float) $out->qty_kg;
                            $hpp = (float) $out->hpp_satuan;
                            $val = (float) $out->total_nilai;
                            if ($val <= 0 && $hpp > 0) {
                                $val = $qty * $hpp;
                            }

                            $barangNmUpper = strtoupper($out->barang?->barang_nm ?? '');
                            $barangCdUpper = strtoupper($out->barang?->barang_cd ?? '');
                            $kategoriUpper = strtoupper($out->kategori_output ?? '');

                            $isBerko = str_contains($barangNmUpper, 'BERKO') 
                                || str_contains($barangNmUpper, 'LENGKET') 
                                || str_contains($barangCdUpper, 'BRK') 
                                || str_contains($barangCdUpper, 'LGT') 
                                || str_contains($kategoriUpper, 'BERKO') 
                                || str_contains($kategoriUpper, 'BRK')
                                || str_contains($kategoriUpper, 'LGT')
                                || str_contains($kategoriUpper, 'LENGKET');

                            if ($isBerko) {
                                if (!empty($bNo) && $bNo !== '-') $berkoBatches[] = $bNo;
                                $dayBerkoKg += $qty;
                                $dayBerkoVal += $val;
                            } else {
                                if (!empty($bNo) && $bNo !== '-') $mainBatches[] = $bNo;
                                $dayUtamaKg += $qty;
                                $dayUtamaVal += $val;
                            }
                        }
                    }

                    // Fallback batch utama dari header jika outputs kosong
                    if (empty($mainBatches) && !empty($rec->batch_wip_no) && $rec->batch_wip_no !== '-') {
                        $mainBatches[] = $rec->batch_wip_no;
                    }

                    // Jika ada produksi Berko pada hari itu tetapi batch di output belum tercatat, ambil dari tanggal fisik
                    if ((float)$rec->total_berko_qty > 0 && empty($berkoBatches)) {
                        $berkoBatches[] = Carbon::parse($rec->produksi_tgl)->format('d m Y');
                    }
                }

                $mainBatches = array_values(array_unique(array_filter($mainBatches)));
                $berkoBatches = array_values(array_unique(array_filter($berkoBatches)));
                $allBatches = array_values(array_unique(array_merge($mainBatches, $berkoBatches)));

                $dayHppUtama = $dayUtamaKg > 0 && $dayUtamaVal > 0 ? round($dayUtamaVal / $dayUtamaKg, 2) : $dayHpp;
                $dayHppBerko = $dayBerkoKg > 0 && $dayBerkoVal > 0 ? round($dayBerkoVal / $dayBerkoKg, 2) : $dayHpp;
                $hasBerko = ($dayTotalBerko > 0 || $dayBerkoKg > 0);
                $hasDifferentiatedHpp = ($dayBerkoKg > 0 && $dayUtamaKg > 0 && abs($dayHppUtama - $dayHppBerko) > 0.01);

                // Rincian Label Bumbu / Seasoning Harian (Transparansi Audit HPP)
                $dayBumbuNames = [];
                foreach ($dayRecords as $rec) {
                    if ($rec->pemakaianBahan && $rec->pemakaianBahan->details) {
                        foreach ($rec->pemakaianBahan->details as $dtl) {
                            $bNm = $dtl->barang?->barang_nm ?? '';
                            $bCd = $dtl->barang?->barang_cd ?? '';
                            if (str_contains(strtoupper($bNm), 'BUMBU') || str_contains(strtoupper($bNm), 'GARAM') || str_contains(strtoupper($bNm), 'SEASONING') || str_contains(strtoupper($bNm), 'PERENYAH') || str_starts_with(strtoupper($bCd), 'BP-BMB')) {
                                $clean = trim(preg_replace('/^(BUMBU\s+(TABUR\s+)?|BUBUK\s+)/i', '', $bNm));
                                if (!empty($clean)) {
                                    $dayBumbuNames[] = ucwords(strtolower($clean));
                                }
                            }
                        }
                    }
                    if (empty($dayBumbuNames) && $rec->outputs) {
                        foreach ($rec->outputs as $out) {
                            $oNm = strtoupper($out->barang?->barang_nm ?? '');
                            $oCd = strtoupper($out->barang?->barang_cd ?? '');
                            if (str_contains($oNm, 'BARCO') || str_contains($oCd, 'ASB')) {
                                $dayBumbuNames[] = 'Barco';
                            } elseif (str_contains($oNm, 'SAWIT') || str_contains($oCd, 'ASW')) {
                                $dayBumbuNames[] = 'Sawit';
                            } elseif (str_contains($oNm, 'BALQI') || str_contains($oNm, 'BALADO') || str_contains($oCd, 'BLQ') || str_contains($oCd, 'BBB')) {
                                $dayBumbuNames[] = 'Balado';
                            } elseif (str_contains($oNm, 'CHILLI') || str_contains($oCd, 'KCL')) {
                                $dayBumbuNames[] = 'Chilli';
                            }
                        }
                    }
                }
                $dayBumbuNames = array_values(array_unique(array_filter($dayBumbuNames)));
                $dayBumbuLabel = !empty($dayBumbuNames) ? implode(', ', $dayBumbuNames) : null;

                // Status Penetapan HPP per SPK / Shift Harian
                $dayShiftRecords = $dayRecords->map(fn($r) => [
                    'produksi_id'     => $r->produksi_id,
                    'produksi_no'     => $r->produksi_no,
                    'shift_cd'        => $r->shift_cd ?? 'A',
                    'lini_produksi'   => $r->lini_produksi ?? 'Produksi Reguler',
                    'batch_wip_no'    => $r->batch_wip_no ?? '-',
                    'singkong_qty'    => (float) $r->singkong_qty,
                    'singkong_nilai'  => (float) $r->singkong_nilai,
                    'total_berko_qty' => (float) $r->total_berko_qty,
                    'total_biaya'     => (float) $r->total_biaya_produksi,
                    'total_wip'       => (float) ($r->total_wip_qty ?: ($r->outputs ? $r->outputs->sum('qty_kg') : 0)),
                    'hpp_per_kg'      => (float) $r->hpp_per_kg,
                    'is_hpp_final'    => ((float) $r->cng_nilai > 0 || (float) $r->tk_total_nilai > 0 || str_contains($r->catatan_txt ?? '', '[HPP FINAL') || str_contains($r->catatan_txt ?? '', 'HPP Finance')),
                    'output_summary'  => $r->outputs && $r->outputs->isNotEmpty()
                        ? $r->outputs->map(fn($o) => ($o->barang?->barang_nm ?? $o->kategori_output) . ' (' . number_format($o->qty_kg, 0, ',', '.') . ' kg)')->implode(', ')
                        : ($r->total_wip_qty > 0 ? number_format($r->total_wip_qty, 0, ',', '.') . ' kg' : 'Produksi WIP'),
                ]);

                $finalizedCount = $dayShiftRecords->where('is_hpp_final', true)->count();
                $pendingCount = $dayShiftRecords->where('is_hpp_final', false)->count();
                $totalSpkCount = $dayShiftRecords->count();

                if ($pendingCount === 0) {
                    $dayHppStatus = 'all_final';
                    $isDayHppFinal = true;
                } elseif ($finalizedCount > 0) {
                    $dayHppStatus = 'partial'; // Sebagian shift/lini sudah ditarik & final, sebagian belum!
                    $isDayHppFinal = false;
                } else {
                    $dayHppStatus = 'none'; // Belum ada dokumen yang difinalisasi
                    $isDayHppFinal = false;
                }

                $pendingSpks = $dayShiftRecords->where('is_hpp_final', false)->values()->toArray();
                $finalizedSpks = $dayShiftRecords->where('is_hpp_final', true)->values()->toArray();

                $days[] = [
                    'day'                         => $day,
                    'date'                        => $dateObj->format('Y-m-d'),
                    'hari_nm'                     => $hariNm,
                    'has_data'                    => true,
                    'hpp_status'                  => $dayHppStatus,
                    'is_hpp_final'                => $isDayHppFinal,
                    'is_hpp_partial'              => ($dayHppStatus === 'partial'),
                    'finalized_spk_count'         => $finalizedCount,
                    'pending_spk_count'           => $pendingCount,
                    'total_spk_count'             => $totalSpkCount,
                    'pending_spks'                => $pendingSpks,
                    'finalized_spks'              => $finalizedSpks,
                    'produksi_id'                 => $firstRec->produksi_id,
                    'produksi_ids'                => $dayRecords->pluck('produksi_id')->toArray(),
                    'produksi_no'                 => $shiftCount > 1 ? ($firstRec->produksi_no . " (+{$shiftCount} Shift)") : $firstRec->produksi_no,
                    'primary_produksi_no'         => $firstRec->produksi_no,
                    'shift_cd'                    => $shiftCodeStr,
                    'shift_count'                 => $shiftCount,
                    'lini_produksi'               => $liniStr,
                    'lini_list'                   => $liniList,
                    'batch_wip_no'                => !empty($allBatches) ? implode(', ', $allBatches) : '-',
                    'batch_list'                  => $allBatches,
                    'main_batches'                => $mainBatches,
                    'berko_batches'               => $berkoBatches,
                    'berko_batch_no'              => !empty($berkoBatches) ? implode(', ', $berkoBatches) : null,
                    'shift_records'               => $dayShiftRecords->toArray(),
                    'singkong_qty'                => $daySingkongQty,
                    'singkong_nilai'              => $daySingkongNilai,
                    'minyak_sawit_qty'            => $daySawitQty,
                    'minyak_kelapa_qty'           => $dayKelapaQty,
                    'minyak_nilai'                => $dayMinyakNilai,
                    'minyak_rasio_persen'         => $dayMinyakRasio,
                    'cng_nm3'                     => $dayCngNm3,
                    'cng_mmbtu'                   => $dayCngMmbtu,
                    'cng_nilai'                   => $dayCngNilai,
                    'bf_aktif_count'              => $dayBfAktif,
                    'cng_per_bf'                  => $dayCngPerBf,
                    'cng_kurs_jisdor'             => $dayCngKursJisdor,
                    'cng_tarif_usd'               => $dayCngTarifUsd,
                    'cng_tarif'                   => $dayCngTarif,
                    'tk_jumlah_org'               => $dayTkJumlah,
                    'tk_langsung_org'             => $dayTkLangsung,
                    'tk_tidak_langsung_org'       => $dayTkTidakLangsung,
                    'tk_training_org'             => $dayTkTraining,
                    'tk_total_nilai'              => $dayTkTotalNilai,
                    'bumbu_qty'                   => $dayBumbuQty,
                    'bumbu_nilai'                 => $dayBumbuNilai,
                    'bumbu_label'                 => $dayBumbuLabel,
                    'karton_baru_qty'             => $dayKartonBaruQty,
                    'karton_baru_nilai'           => $dayKartonBaru,
                    'karton_bekas_qty'            => $dayKartonBekasQty,
                    'karton_bekas_nilai'          => $dayKartonBekas,
                    'plastik_hd_qty'              => $dayPlastikHdQty,
                    'plastik_hd_nilai'            => $dayPlastikHd,
                    'lakban_besar_qty'            => $dayLakbanBesarQty,
                    'lakban_besar_nilai'          => $dayLakbanBesar,
                    'lakban_kecil_qty'            => $dayLakbanKecilQty,
                    'lakban_kecil_nilai'          => $dayLakbanKecil,
                    'tali_rafia_qty'              => $dayTaliRafiaQty,
                    'tali_rafia_nilai'            => $dayTaliRafia,
                    'fotocopy_nilai'              => $dayFotocopy,
                    'sarung_tangan_plastik_nilai' => $daySarungPlastik,
                    'sarung_tangan_kain_nilai'    => $daySarungKain,
                    'qc_pengawasan_nilai'         => $dayQc,
                    'listrik_air_telp_nilai'      => $dayListrik,
                    'pemeliharaan_mesin_nilai'    => $dayPemeliharaan,
                    'penyusutan_mesin_nilai'      => $dayPenyusutan,
                    'limbah_padat_nilai'          => $dayLimbahPadat,
                    'limbah_kimia_nilai'          => $dayLimbahKimia,
                    'total_biaya_produksi'        => $dayTotalBiaya,
                    'ifl_qty'                     => $dayIfl,
                    'asin_barco_qty'              => $dayAsinBarco,
                    'asin_sawit_qty'              => $dayAsinSawit,
                    'no_salt_qty'                 => $dayNoSalt,
                    'ucamp_qty'                   => $dayUcamp,
                    'balo_gelombang_qty'          => $dayBalo,
                    'balqi_qty'                   => $dayBalqi,
                    'berko_qty'                   => $dayBerko,
                    'berko_me_qty'                => $dayBerkoMe,
                    'total_berko_qty'             => $dayTotalBerko,
                    'berko_persen'                => $dayBerkoPersen,
                    'berko_biasa_persen'          => $dayBerkoBiasaPersen,
                    'berko_me_persen'             => $dayBerkoMePersen,
                    'total_wip_qty'               => $dayTotalWip,
                    'rendemen_persen'             => $dayRendemen,
                    'hpp_per_kg'                  => $dayHpp,
                    'hpp_utama'                   => $dayHppUtama,
                    'hpp_berko'                   => $dayHppBerko,
                    'has_berko'                   => $hasBerko,
                    'has_differentiated_hpp'      => $hasDifferentiatedHpp,
                    'day_utama_kg'                => $dayUtamaKg,
                    'day_berko_kg'                => $dayBerkoKg,
                    'entity_summary'              => [
                        'has_mfi'   => $mfiSummary['exists'],
                        'has_bmb'   => $bmbSummary['exists'],
                        'is_multi'  => $mfiSummary['exists'] && $bmbSummary['exists'],
                        'mfi'       => $mfiSummary,
                        'bmb'       => $bmbSummary,
                    ],
                ];
            } else {
                $days[] = [
                    'day'                         => $day,
                    'date'                        => $dateObj->format('Y-m-d'),
                    'hari_nm'                     => $hariNm,
                    'has_data'                    => false,
                    'is_hpp_final'                => false,
                    'has_berko'                   => false,
                    'has_differentiated_hpp'      => false,
                    'hpp_utama'                   => 0,
                    'hpp_berko'                   => 0,
                    'day_utama_kg'                => 0,
                    'day_berko_kg'                => 0,
                    'produksi_id'                 => null,
                    'produksi_ids'                => [],
                    'produksi_no'                 => null,
                    'shift_cd'                    => null,
                    'shift_count'                 => 0,
                    'shift_records'               => [],
                    'lini_produksi'               => null,
                    'lini_list'                   => [],
                    'batch_wip_no'                => null,
                    'batch_list'                  => [],
                    'main_batches'                => [],
                    'berko_batches'               => [],
                    'berko_batch_no'              => null,
                    'primary_produksi_no'         => null,
                    'singkong_qty'                => 0,
                    'singkong_nilai'              => 0,
                    'minyak_sawit_qty'            => 0,
                    'minyak_kelapa_qty'           => 0,
                    'minyak_nilai'                => 0,
                    'minyak_rasio_persen'         => 0,
                    'cng_mmbtu'                   => 0,
                    'cng_nilai'                   => 0,
                    'bf_aktif_count'              => 0,
                    'cng_per_bf'                  => 0,
                    'tk_jumlah_org'               => 0,
                    'tk_langsung_org'             => 0,
                    'tk_tidak_langsung_org'       => 0,
                    'tk_training_org'             => 0,
                    'tk_total_nilai'              => 0,
                    'bumbu_qty'                   => 0,
                    'bumbu_nilai'                 => 0,
                    'bumbu_label'                 => null,
                    'karton_baru_qty'             => 0,
                    'karton_baru_nilai'           => 0,
                    'karton_bekas_qty'            => 0,
                    'karton_bekas_nilai'          => 0,
                    'plastik_hd_qty'              => 0,
                    'plastik_hd_nilai'            => 0,
                    'lakban_besar_qty'            => 0,
                    'lakban_besar_nilai'          => 0,
                    'lakban_kecil_qty'            => 0,
                    'lakban_kecil_nilai'          => 0,
                    'tali_rafia_qty'              => 0,
                    'tali_rafia_nilai'            => 0,
                    'fotocopy_nilai'              => 0,
                    'sarung_tangan_plastik_nilai' => 0,
                    'sarung_tangan_kain_nilai'    => 0,
                    'qc_pengawasan_nilai'         => 0,
                    'listrik_air_telp_nilai'      => 0,
                    'pemeliharaan_mesin_nilai'    => 0,
                    'penyusutan_mesin_nilai'      => 0,
                    'limbah_padat_nilai'          => 0,
                    'limbah_kimia_nilai'          => 0,
                    'total_biaya_produksi'        => 0,
                    'ifl_qty'                     => 0,
                    'asin_barco_qty'              => 0,
                    'asin_sawit_qty'              => 0,
                    'no_salt_qty'                 => 0,
                    'ucamp_qty'                   => 0,
                    'balo_gelombang_qty'          => 0,
                    'balqi_qty'                   => 0,
                    'berko_qty'                   => 0,
                    'berko_me_qty'                => 0,
                    'total_berko_qty'             => 0,
                    'berko_persen'                => 0,
                    'berko_biasa_persen'          => 0,
                    'berko_me_persen'             => 0,
                    'total_wip_qty'               => 0,
                    'rendemen_persen'             => 0,
                    'hpp_per_kg'                  => 0,
                    'entity_summary'              => [
                        'has_mfi'   => false,
                        'has_bmb'   => false,
                        'is_multi'  => false,
                        'mfi'       => $calcEntityData(collect(), 'MFI'),
                        'bmb'       => $calcEntityData(collect(), 'BMB'),
                    ],
                ];
            }
        }

        $activeDays = collect($days)->where('has_data', true);
        $finalizedDaysCount = $activeDays->where('is_hpp_final', true)->count();
        $pendingDaysCount = $activeDays->where('is_hpp_final', false)->count();

        // Sinkronisasi total output WIP & metrik fisik: HANYA mengakumulasi dokumen yang telah ditarik & difinalisasi HPP
        if ($hasFinalized && $finalizedWipSum > 0) {
            $totals['ifl_qty'] = (float) $finalizedRecords->sum('ifl_qty');
            $totals['asin_barco_qty'] = (float) $finalizedRecords->sum('asin_barco_qty');
            $totals['asin_sawit_qty'] = (float) $finalizedRecords->sum('asin_sawit_qty');
            $totals['no_salt_qty'] = (float) $finalizedRecords->sum('no_salt_qty');
            $totals['ucamp_qty'] = (float) $finalizedRecords->sum('ucamp_qty');
            $totals['balo_gelombang_qty'] = (float) $finalizedRecords->sum('balo_gelombang_qty');
            $totals['balqi_qty'] = (float) $finalizedRecords->sum('balqi_qty');
            $totals['berko_qty'] = (float) $finalizedRecords->sum('berko_qty');
            $totals['berko_me_qty'] = (float) $finalizedRecords->sum('berko_me_qty');
            $totals['total_berko_qty'] = (float) $finalizedRecords->sum('total_berko_qty');
            $totals['total_wip_qty'] = (float) $finalizedWipSum;
            $totals['singkong_qty'] = (float) $finalizedRecords->sum('singkong_qty');
            $totals['singkong_nilai'] = (float) $finalizedRecords->sum('singkong_nilai');
            $totals['total_biaya_produksi'] = (float) $finalizedRecords->sum('total_biaya_produksi');
            $totals['berko_persen'] = $totals['total_wip_qty'] > 0
                ? ($totals['total_berko_qty'] / $totals['total_wip_qty']) * 100
                : 0;
            $totals['berko_biasa_persen'] = $totals['total_wip_qty'] > 0
                ? ($totals['berko_qty'] / $totals['total_wip_qty']) * 100
                : 0;
            $totals['berko_me_persen'] = $totals['total_wip_qty'] > 0
                ? ($totals['berko_me_qty'] / $totals['total_wip_qty']) * 100
                : 0;
            $totals['rendemen_persen'] = $totals['singkong_qty'] > 0
                ? ($totals['total_wip_qty'] / $totals['singkong_qty']) * 100
                : 0;
            $totals['hpp_per_kg'] = $totals['total_biaya_produksi'] / $finalizedWipSum;
        } else {
            // Ketika belum difinalisasi HPP, seluruh metrik fisik dan finansial belum ditarik
            $totals['ifl_qty'] = 0;
            $totals['asin_barco_qty'] = 0;
            $totals['asin_sawit_qty'] = 0;
            $totals['no_salt_qty'] = 0;
            $totals['ucamp_qty'] = 0;
            $totals['balo_gelombang_qty'] = 0;
            $totals['balqi_qty'] = 0;
            $totals['berko_qty'] = 0;
            $totals['berko_me_qty'] = 0;
            $totals['total_berko_qty'] = 0;
            $totals['total_wip_qty'] = 0;
            $totals['singkong_qty'] = 0;
            $totals['singkong_nilai'] = 0;
            $totals['berko_persen'] = 0;
            $totals['berko_biasa_persen'] = 0;
            $totals['berko_me_persen'] = 0;
            $totals['rendemen_persen'] = 0;
            $totals['total_biaya_produksi'] = 0;
            $totals['hpp_per_kg'] = 0;
        }

        // Agregasi seluruh dokumen SPK yang belum difinalisasi HPP pada bulan ini
        $pendingSpksInMonth = [];
        foreach ($days as $dayItem) {
            if (!empty($dayItem['has_data']) && !empty($dayItem['pending_spks'])) {
                foreach ($dayItem['pending_spks'] as $pSpk) {
                    $pendingSpksInMonth[] = array_merge($pSpk, [
                        'day'            => $dayItem['day'],
                        'date'           => $dayItem['date'],
                        'hari_nm'        => $dayItem['hari_nm'],
                        'is_partial_day' => ($dayItem['hpp_status'] === 'partial'),
                    ]);
                }
            }
        }

        return [
            'year'              => $year,
            'month'             => $month,
            'lokasi_pabrik'     => $lokasiPabrik ? strtoupper($lokasiPabrik) : 'ALL',
            'count_total_hari'  => $countTotalHari,
            'count_barat_hari'  => $countBaratHari,
            'count_timur_hari'  => $countTimurHari,
            'count_mfi_hari'    => $countBaratHari,
            'count_bmb_hari'    => $countTimurHari,
            'records'           => $records,
            'days'              => $days,
            'totals'            => $totals,
            'count'             => $activeDays->count(),
            'total_spk_count'   => $records->count(),
            'finalized_count'   => $finalizedDaysCount,
            'pending_count'     => $pendingDaysCount,
            'is_all_final'      => ($finalizedDaysCount > 0 && $pendingDaysCount === 0),
            'pending_spks'      => $pendingSpksInMonth,
            'has_pending_hpp'   => !empty($pendingSpksInMonth),
            'pending_spk_count' => count($pendingSpksInMonth),
        ];
    }

    /**
     * Mengambil laporan tahunan konsolidasi HPP per Bulan (Januari s/d Desember).
     */
    public function getYearlyReport(int $year): array
    {
        $allRecords = DatProduksiHdr::whereYear('produksi_tgl', $year)
            ->where('deleted_st', false)
            ->orderBy('produksi_tgl', 'asc')
            ->get();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthRecords = $allRecords->filter(function ($r) use ($m) {
                return Carbon::parse($r->produksi_tgl)->month === $m;
            });

            $count = $monthRecords->count();

            $finalizedMonthRecords = $monthRecords->filter(function ($r) {
                return ((float) $r->cng_nilai > 0 || (float) $r->tk_total_nilai > 0 || str_contains($r->catatan_txt ?? '', '[HPP FINAL') || str_contains($r->catatan_txt ?? '', 'HPP Finance'));
            });
            $hasFinalizedMonth = $finalizedMonthRecords->isNotEmpty();
            $finalizedWipMonth = (float) $finalizedMonthRecords->sum('total_wip_qty');

            $singkongQty = $hasFinalizedMonth ? (float) $finalizedMonthRecords->sum('singkong_qty') : 0;
            $totalWipQty = $hasFinalizedMonth ? $finalizedWipMonth : 0;

            $singkongNilai = $hasFinalizedMonth ? (float) $finalizedMonthRecords->sum('singkong_nilai') : 0;
            $totalBiaya = $hasFinalizedMonth ? (float) $finalizedMonthRecords->sum('total_biaya_produksi') : 0;
            $minyakNilai = $hasFinalizedMonth ? (float) $finalizedMonthRecords->sum('minyak_nilai') : 0;
            $cngNilai = $hasFinalizedMonth ? (float) $finalizedMonthRecords->sum('cng_nilai') : 0;
            $tkNilai = $hasFinalizedMonth ? (float) $finalizedMonthRecords->sum('tk_total_nilai') : 0;

            $kemasanNilai = $hasFinalizedMonth ? (float) (
                $finalizedMonthRecords->sum('karton_baru_nilai') +
                $finalizedMonthRecords->sum('karton_bekas_nilai') +
                $finalizedMonthRecords->sum('plastik_hd_nilai') +
                $finalizedMonthRecords->sum('lakban_besar_nilai') +
                $finalizedMonthRecords->sum('lakban_kecil_nilai') +
                $finalizedMonthRecords->sum('tali_rafia_nilai')
            ) : 0;

            $fohNilai = $hasFinalizedMonth ? (float) (
                $finalizedMonthRecords->sum('sarung_tangan_plastik_nilai') +
                $finalizedMonthRecords->sum('sarung_tangan_kain_nilai')
            ) : 0;

            $rendemen = ($hasFinalizedMonth && $singkongQty > 0) ? ($totalWipQty / $singkongQty) * 100 : 0;
            $hppPerKg = ($hasFinalizedMonth && $finalizedWipMonth > 0) ? ($totalBiaya / $finalizedWipMonth) : 0;

            $months[$m] = [
                'month_num'      => $m,
                'month_name'     => $monthNames[$m],
                'has_data'       => $count > 0,
                'work_days'      => $count,
                'finalized_days' => $finalizedMonthRecords->count(),
                'singkong_qty'   => $singkongQty,
                'singkong_nilai' => $singkongNilai,
                'total_wip_qty'  => $totalWipQty,
                'rendemen'       => $rendemen,
                'minyak_nilai'   => $minyakNilai,
                'cng_nilai'      => $cngNilai,
                'tk_nilai'       => $tkNilai,
                'kemasan_nilai'  => $kemasanNilai,
                'foh_nilai'      => $fohNilai,
                'total_biaya'    => $totalBiaya,
                'hpp_per_kg'     => $hppPerKg,
            ];
        }

        // Grand Total Tahunan: Hanya mengakumulasi dari data yang telah final HPP
        $finalizedAllRecords = $allRecords->filter(function ($r) {
            return ((float) $r->cng_nilai > 0 || (float) $r->tk_total_nilai > 0 || str_contains($r->catatan_txt ?? '', '[HPP FINAL') || str_contains($r->catatan_txt ?? '', 'HPP Finance'));
        });
        $hasFinalizedAll = $finalizedAllRecords->isNotEmpty();
        $finalizedAnnualWip = (float) $finalizedAllRecords->sum('total_wip_qty');

        $annualSingkongQty = $hasFinalizedAll ? (float) $finalizedAllRecords->sum('singkong_qty') : 0;
        $annualSingkongNilai = $hasFinalizedAll ? (float) $finalizedAllRecords->sum('singkong_nilai') : 0;
        $annualWipQty = $hasFinalizedAll ? $finalizedAnnualWip : 0;
        $annualBiaya = $hasFinalizedAll ? (float) $finalizedAllRecords->sum('total_biaya_produksi') : 0;
        $annualWorkDays = $allRecords->count();

        $annualRendemen = ($hasFinalizedAll && $annualSingkongQty > 0) ? ($annualWipQty / $annualSingkongQty) * 100 : 0;
        $annualHppPerKg = ($hasFinalizedAll && $finalizedAnnualWip > 0) ? ($annualBiaya / $finalizedAnnualWip) : 0;

        $annualTotals = [
            'total_work_days' => $annualWorkDays,
            'finalized_days'  => $finalizedAllRecords->count(),
            'singkong_qty'    => $annualSingkongQty,
            'singkong_nilai'  => $annualSingkongNilai,
            'total_wip_qty'   => $annualWipQty,
            'total_biaya'     => $annualBiaya,
            'rendemen'        => $annualRendemen,
            'hpp_per_kg'      => $annualHppPerKg,
        ];

        return [
            'year'          => $year,
            'months'        => $months,
            'annual_totals' => $annualTotals,
            'total_records' => $allRecords->count(),
        ];
    }

    /**
     * Cari nomor karton awal yang disarankan berdasarkan tanggal dan shift.
     * Shift A: default 1 atau melanjutkan max hari itu jika ada.
     * Shift B: otomatis melanjutkan nomor karton akhir dari Shift A hari itu.
     */
    public function getNextKartonAwal(string $tgl, string $shift = 'A'): array
    {
        $shiftUpper = strtoupper(trim($shift));

        // Cari rekaman produksi pada tanggal tersebut
        $records = DatProduksiHdr::whereDate('produksi_tgl', $tgl)
            ->where('deleted_st', false)
            ->whereNotNull('no_karton_akhir')
            ->orderBy('no_karton_akhir', 'desc')
            ->get();

        if ($shiftUpper === 'B') {
            // Cek apakah sudah ada Shift A hari ini
            $shiftA = $records->firstWhere('shift_cd', 'A');
            if ($shiftA && $shiftA->no_karton_akhir > 0) {
                return [
                    'next_no_awal'   => $shiftA->no_karton_akhir + 1,
                    'last_shift'     => 'A',
                    'last_no_akhir'  => $shiftA->no_karton_akhir,
                    'source_desc'    => "Melanjutkan Shift A (Karton {$shiftA->no_karton_awal}-{$shiftA->no_karton_akhir})",
                ];
            }
        }

        // Jika Shift A atau tidak ada Shift A sebelumnya
        $maxAkhir = $records->max('no_karton_akhir');
        if ($maxAkhir && $maxAkhir > 0) {
            return [
                'next_no_awal'  => $maxAkhir + 1,
                'last_shift'    => $records->first()?->shift_cd,
                'last_no_akhir' => $maxAkhir,
                'source_desc'   => "Melanjutkan batch terakhir hari ini (Karton {$maxAkhir})",
            ];
        }

        // Default awal
        return [
            'next_no_awal'  => 1,
            'last_shift'    => null,
            'last_no_akhir' => 0,
            'source_desc'   => "Awal batch baru (Karton 1)",
        ];
    }

    /**
     * Sinkronisasi data tenaga kerja (kehadiran & total upah) dari modul Karyawan (PayrollHarianService)
     * berdasarkan tanggal produksi dan lini produksi (Langsung IFM, Langsung Manual, Langsung Packing).
     */
    public function getLaborDataFromKaryawan(string $tgl, ?string $lini = null): array
    {
        $parsedDate = Carbon::parse($tgl);
        $tglFormatted = $parsedDate->format('d/m/Y');

        // Petakan lini produksi ke kelompok kerja di PayrollHarianService
        $targetKelompok = null;
        $kelompokLabel = 'Semua Lini Langsung';

        if (!empty($lini)) {
            $liniUpper = strtoupper(trim($lini));
            if (str_contains($liniUpper, 'IFM') || str_contains($liniUpper, 'IFL') || str_contains($liniUpper, 'INDOFOOD')) {
                $targetKelompok = 'Langsung IFM';
                $kelompokLabel = 'Langsung IFM';
            } elseif (str_contains($liniUpper, 'PACKING') || str_contains($liniUpper, 'KEMAS') || str_contains($liniUpper, 'PACK')) {
                $targetKelompok = 'Langsung Packing';
                $kelompokLabel = 'Langsung Packing';
            } else {
                $targetKelompok = 'Langsung Manual';
                $kelompokLabel = 'Langsung Manual';
            }
        } else {
            $targetKelompok = 'langsung';
        }

        try {
            $payrollHarian = app(PayrollHarianService::class);
            // Panggil rincian tanpa menyentuh/mengubah kode PayrollHarianService sama sekali
            $rincian = $payrollHarian->rincian($tgl);
            $rows = collect($rincian['rows'] ?? []);

            $isIfm = !empty($lini) && (str_contains(strtoupper($lini), 'IFM') || str_contains(strtoupper($lini), 'IFL'));
            $isManualOnly = !empty($lini) && str_contains(strtoupper($lini), 'MANUAL');
            $isPacking = !empty($lini) && (str_contains(strtoupper($lini), 'PACK') || str_contains(strtoupper($lini), 'KEMAS'));

            // 1. Karyawan Langsung Produksi / Pengemasan
            $prodRows = $rows->filter(function ($row) use ($isIfm, $isManualOnly, $isPacking) {
                if (empty($row['masuk'])) {
                    return false;
                }
                $kAsli = strtoupper((string) ($row['kelompok_asli'] ?? $row['kelompok'] ?? ''));

                if (str_contains($kAsli, 'TIDAK')) {
                    return false;
                }

                if ($isPacking) {
                    return str_contains($kAsli, 'PACK') || str_contains($kAsli, 'KEMAS');
                }

                // Untuk Dapur / Masak: pastikan bukan packaging
                if (str_contains($kAsli, 'PACK') || str_contains($kAsli, 'KEMAS')) {
                    return false;
                }

                if ($isIfm) {
                    return str_contains($kAsli, 'IFM') || str_contains($kAsli, 'IFL');
                }
                if ($isManualOnly) {
                    return str_contains($kAsli, 'MANUAL');
                }
                return str_contains($kAsli, 'MANUAL') || str_contains($kAsli, 'IFM') || str_contains($kAsli, 'IFL') || str_contains($kAsli, 'LANGSUNG');
            });

            // Jika filter spesifik kosong tapi ada pekerja produksi langsung lain (selain packing & non-langsung)
            if ($prodRows->count() === 0 && !$isPacking) {
                $prodRows = $rows->filter(function ($row) {
                    if (empty($row['masuk'])) {
                        return false;
                    }
                    $kAsli = strtoupper((string) ($row['kelompok_asli'] ?? $row['kelompok'] ?? ''));
                    return !str_contains($kAsli, 'PACK') && !str_contains($kAsli, 'KEMAS') && !str_contains($kAsli, 'TIDAK');
                });
            }

            // 2. Karyawan Tidak Langsung Pabrik (Mandor, Mekanik, QC, Kebersihan)
            $tidakRows = $rows->filter(function ($row) {
                if (empty($row['masuk'])) {
                    return false;
                }
                $kAsli = strtoupper((string) ($row['kelompok_asli'] ?? $row['kelompok'] ?? ''));
                return str_contains($kAsli, 'TIDAK');
            });

            $langsungOrang = $prodRows->count();
            $langsungBiaya = (float) $prodRows->sum('total');

            // Karyawan tidak langsung (mandor, QC, kebersihan) diperlakukan sebagai operasional perusahaan,
            // sehingga untuk HPP Produksi murni hanya menggunakan Biaya Tenaga Kerja Langsung (BTKL).
            $totalOrang = $langsungOrang;
            $totalBiaya = (float) $langsungBiaya;

            $prodLabel = $isPacking ? 'Karyawan Langsung Packing' : ($isIfm ? 'Karyawan Langsung IFM' : 'Karyawan Langsung Manual');

            if ($totalOrang > 0 || $totalBiaya > 0) {
                return [
                    'tgl'                     => $tgl,
                    'tgl_format'              => $tglFormatted,
                    'is_synced'               => true,
                    'tk_langsung_org'         => $langsungOrang,
                    'tk_langsung_nilai'       => $langsungBiaya,
                    'tk_langsung_label'       => $prodLabel,
                    'tk_tidak_langsung_org'   => 0,
                    'tk_tidak_langsung_nilai' => 0,
                    'tk_tidak_langsung_label' => '',
                    'tk_jumlah_org'           => $totalOrang,
                    'tk_total_nilai'          => $totalBiaya,
                    'kelompok'                => $kelompokLabel,
                    'source'                  => 'payroll_harian',
                    'message'                 => "Data presensi tersinkronisasi ({$prodLabel}: {$langsungOrang} orang / Rp " . number_format($langsungBiaya, 0, ',', '.') . ").",
                ];
            }
        } catch (\Throwable $e) {
            // Graceful fallback jika terjadi exception saat akses PayrollHarianService
        }

        // Fallback jika menggunakan skema tabel dinamis alternatif
        $possibleTables = [
            'dat_absensi', 'dat_presensi', 'dat_kehadiran', 'dat_absensi_karyawan',
            'dat_gaji_karyawan', 'dat_payroll', 'dat_payroll_harian'
        ];

        foreach ($possibleTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                try {
                    $q = DB::table($tableName);
                    $dateCol = Schema::hasColumn($tableName, 'tanggal') ? 'tanggal'
                        : (Schema::hasColumn($tableName, 'tgl') ? 'tgl'
                        : (Schema::hasColumn($tableName, 'presensi_tgl') ? 'presensi_tgl'
                        : (Schema::hasColumn($tableName, 'absensi_tgl') ? 'absensi_tgl' : null)));

                    if ($dateCol) {
                        $records = $q->whereDate($dateCol, $tgl);

                        if (Schema::hasColumn($tableName, 'deleted_st')) {
                            $records->where('deleted_st', false);
                        }

                        if (Schema::hasColumn($tableName, 'status')) {
                            $records->whereIn('status', ['HADIR', 'H', 'MASUK', 'PRESENT']);
                        } elseif (Schema::hasColumn($tableName, 'status_kehadiran')) {
                            $records->whereIn('status_kehadiran', ['HADIR', 'H', 'MASUK', 'PRESENT']);
                        }

                        $count = (int) $records->count();

                        $wageCol = null;
                        foreach (['total_gaji', 'gaji_harian', 'upah', 'nominal_upah', 'nominal_gaji', 'total_upah'] as $col) {
                            if (Schema::hasColumn($tableName, $col)) {
                                $wageCol = $col;
                                break;
                            }
                        }

                        $totalUpah = $wageCol ? (float) $records->sum($wageCol) : 0;

                        if ($count > 0 || $totalUpah > 0) {
                            return [
                                'tgl'                     => $tgl,
                                'tgl_format'              => $tglFormatted,
                                'is_synced'               => true,
                                'tk_langsung_org'         => $count,
                                'tk_langsung_nilai'       => $totalUpah,
                                'tk_langsung_label'       => $prodLabel,
                                'tk_jumlah_org'           => $count,
                                'tk_total_nilai'          => $totalUpah,
                                'kelompok'                => $kelompokLabel,
                                'source'                  => $tableName,
                                'message'                 => "Data presensi tersinkronisasi ({$count} orang hadir, total Rp " . number_format($totalUpah, 0, ',', '.') . ").",
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    // Graceful fallback
                }
            }
        }

        return [
            'tgl'                     => $tgl,
            'tgl_format'              => $tglFormatted,
            'is_synced'               => false,
            'tk_langsung_org'         => 0,
            'tk_langsung_nilai'       => 0,
            'tk_langsung_label'       => $isPacking ? 'Karyawan Langsung Packing' : ($isIfm ? 'Karyawan Langsung IFM' : 'Karyawan Langsung Manual'),
            'tk_tidak_langsung_org'   => 0,
            'tk_tidak_langsung_nilai' => 0,
            'tk_tidak_langsung_label' => 'Karyawan Tidak Langsung (Pendukung)',
            'tk_jumlah_org'           => 0,
            'tk_total_nilai'          => 0,
            'kelompok'                => $kelompokLabel,
            'source'                  => 'none',
            'message'                 => "Data presensi per {$tglFormatted}" . (!empty($lini) ? " [{$kelompokLabel}]" : "") . " belum tersedia di modul Karyawan.",
        ];
    }

    /**
     * Ekstrak & kategorisasi ringkasan biaya bahan dari dokumen Pemakaian Bahan (dat_pakai_hdr).
     */
    public function extractPakaiSummary(int $pakaiId): array
    {
        $pakai = DatPakaiHdr::with(['details.barang.jenisBarang', 'details.barang.satuanDasar', 'gudang', 'gudangTujuan'])->where('pakai_id', $pakaiId)->firstOrFail();

        if (!in_array($pakai->status_st, ['POSTED', 'DRAFT'])) {
            throw new Exception("Dokumen BPPB [{$pakai->pakai_no}] tidak valid untuk diproses (Status: {$pakai->status_st}).");
        }

        $summary = [
            'pakai_id'           => $pakai->pakai_id,
            'pakai_no'           => $pakai->pakai_no,
            'pakai_tgl'          => $pakai->pakai_tgl ? $pakai->pakai_tgl->format('Y-m-d') : null,
            'pakai_tgl_formatted'=> $pakai->pakai_tgl ? $pakai->pakai_tgl->format('d/m/Y') : '-',
            'gudang_nm'          => $pakai->gudang?->gudang_nm ?? 'Gudang Bahan Baku',
            'tujuan_pemakaian'   => $pakai->tujuan_pemakaian,
            'gudang_id'          => $pakai->gudang_id,
            'gudang_tujuan_id'   => $pakai->gudang_tujuan_id,
            'gudang_tujuan_nm'   => $pakai->gudangTujuan?->gudang_nm,
            'lokasi_pabrik'      => $pakai->lokasi_pabrik,
            'total_item_count'   => 0,
            'total_bahan_qty'    => 0,
            'total_bahan_nilai'  => 0,
            'singkong_qty'       => 0,
            'singkong_nilai'     => 0,
            'minyak_sawit_qty'   => 0,
            'minyak_kelapa_qty'  => 0,
            'minyak_nilai'       => 0,
            'bumbu_qty'          => 0,
            'bumbu_nilai'        => 0,
            'berko_bahan_qty'    => 0,
            'berko_bahan_nilai'  => 0,
            'karton_baru_qty'             => 0,
            'karton_baru_nilai'           => 0,
            'karton_bekas_qty'            => 0,
            'karton_bekas_nilai'          => 0,
            'plastik_hd_qty'              => 0,
            'plastik_hd_nilai'            => 0,
            'lakban_besar_qty'            => 0,
            'lakban_besar_nilai'          => 0,
            'lakban_kecil_qty'            => 0,
            'lakban_kecil_nilai'          => 0,
            'tali_rafia_qty'              => 0,
            'tali_rafia_nilai'            => 0,
            'sarung_tangan_plastik_nilai' => 0,
            'sarung_tangan_plastik_qty'   => 0,
            'sarung_tangan_kain_nilai'    => 0,
            'sarung_tangan_kain_qty'       => 0,
            'varietas_singkong'           => 'STP / MGU',
            'karton_estimasi'             => 0,
        ];

        $varietasList = [];
        $kartonCount = 0;
        $items = [];
        $singkongGrades = [];

        foreach ($pakai->details as $dtl) {
            $barang = $dtl->barang;
            if (!$barang) continue;

            $nm = strtoupper($barang->barang_nm);
            $cd = strtoupper($barang->barang_cd);
            $qty = (float) $dtl->qty_keluar;
            $subtotal = (float) ($dtl->total_harga > 0 ? $dtl->total_harga : ($qty * $dtl->harga_satuan));
            $dtlGrade = !empty($dtl->grade_cd) ? strtoupper(trim($dtl->grade_cd)) : ($barang->grade_cd ?? '-');

            // Deteksi kelompok kategori bahan untuk kemudahan visualisasi manufaktur
            $kategori = 'Bahan Penolong';
            if (str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK')) {
                $kategori = 'Bahan Utama';
            } elseif (str_contains($nm, 'MINYAK')) {
                $kategori = 'Minyak';
            } elseif (str_contains($nm, 'BUMBU') || str_contains($nm, 'PERENYAH') || str_contains($nm, 'GARAM') || str_contains($nm, 'MSG') || str_contains($nm, 'SEASONING')) {
                $kategori = 'Bumbu & Perasa';
            } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'BOX') || str_contains($nm, 'PLASTIK') || str_contains($nm, 'LAKBAN') || str_contains($nm, 'RAFIA')) {
                $kategori = 'Bahan Kemasan';
            } elseif (str_contains($nm, 'SARUNG') || str_contains($nm, 'GLOVE')) {
                $kategori = 'Alat Penolong';
            } elseif (str_contains($nm, 'BERKO') || str_contains($nm, 'LENGKET')) {
                $kategori = 'Rework / BS';
            }

            // Hitung sisa stok fisik on hand barang tersebut di gudang asal BPPB
            $sisaStok = (float) \App\Models\Gudang\DatStokBatch::where('gudang_id', $pakai->gudang_id)
                ->where('barang_id', $dtl->barang_id)
                ->where('sisa_qty', '>', 0)
                ->sum('sisa_qty');

            $summary['total_bahan_qty'] += $qty;
            $summary['total_bahan_nilai'] += $subtotal;

            $items[] = [
                'pakai_tgl'    => $pakai->pakai_tgl ? $pakai->pakai_tgl->format('d/m/Y') : '-',
                'barang_cd'    => $barang->barang_cd,
                'barang_nm'    => $barang->barang_nm,
                'kategori'     => $kategori,
                'grade_cd'     => $dtlGrade,
                'batch_no'     => $dtl->batch_no ?? '-',
                'qty_keluar'   => $qty,
                'satuan_cd'    => $barang->satuanDasar?->satuan_cd ?? 'KG',
                'harga_satuan' => (float) $dtl->harga_satuan,
                'subtotal'     => $subtotal,
                'sisa_stok'    => $sisaStok,
            ];

            if (str_contains($nm, 'BERKO') || str_contains($nm, 'LENGKET') || str_starts_with($cd, 'WIP-BRK') || str_starts_with($cd, 'WIP-LGT') || str_starts_with($cd, 'WIP-B')) {
                $summary['berko_bahan_qty'] += $qty;
                $summary['berko_bahan_nilai'] += $subtotal;
            } elseif (str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK')) {
                $summary['singkong_qty'] += $qty;
                $summary['singkong_nilai'] += $subtotal;
                $singkongGrades[$dtlGrade] = ($singkongGrades[$dtlGrade] ?? 0) + $qty;
                if (str_contains($nm, 'TAPE') || str_contains($cd, 'STP')) {
                    $varietasList[] = 'STP';
                } elseif (str_contains($nm, 'MANGGU') || str_contains($cd, 'MGU')) {
                    $varietasList[] = 'MGU';
                }
            } elseif (str_contains($nm, 'MINYAK')) {
                if (str_contains($nm, 'KELAPA')) {
                    $summary['minyak_kelapa_qty'] += $qty;
                } else {
                    $summary['minyak_sawit_qty'] += $qty;
                }
                $summary['minyak_nilai'] += $subtotal;
            } elseif (str_contains($nm, 'BUMBU') || str_contains($nm, 'PERENYAH') || str_contains($nm, 'GARAM') || str_contains($nm, 'SEASONING') || str_contains($nm, 'MSG') || str_contains($nm, 'GLUTAMAT') || str_contains($nm, 'PENYEDAP') || str_starts_with($cd, 'MSG') || str_starts_with($cd, 'BMB')) {
                $summary['bumbu_qty'] += $qty;
                $summary['bumbu_nilai'] += $subtotal;
            } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'BOX')) {
                // POIN 28: Amortisasi Karton IFM (Dibagi 5) & Karton Manual (Rp 0)
                if (str_contains($nm, 'REJECT') || str_contains($nm, 'RUSAK') || str_contains($cd, 'REJ') || str_contains($nm, 'MANUAL')) {
                    $subtotal = 0; // Manual karton tidak masuk hitungan HPP
                    $summary['karton_bekas_qty'] += $qty;
                    $summary['karton_bekas_nilai'] += 0;
                } elseif (str_contains($nm, 'PUTERAN') || str_contains($nm, 'BEKAS') || str_contains($cd, 'PUT')) {
                    $summary['karton_bekas_qty'] += $qty;
                    $summary['karton_bekas_nilai'] += $subtotal;
                } else {
                    if (str_contains($nm, 'IFM') || $cd === 'KIF00G-BP1') {
                        $subtotal = round($subtotal / 5, 2); // Khusus IFM baru langsung dibagi 5
                    }
                    $summary['karton_baru_qty'] += $qty;
                    $summary['karton_baru_nilai'] += $subtotal;
                }
                $kartonCount += (int) $qty;
            } elseif (str_contains($nm, 'SARUNG') || str_contains($nm, 'GLOVE') || str_starts_with($cd, 'STP') || str_starts_with($cd, 'STK') || str_starts_with($cd, 'ST-')) {
                if (str_contains($nm, 'KAIN') || str_starts_with($cd, 'STK')) {
                    $summary['sarung_tangan_kain_nilai'] += $subtotal;
                    $summary['sarung_tangan_kain_qty'] += $qty;
                } else {
                    $summary['sarung_tangan_plastik_nilai'] += $subtotal;
                    $summary['sarung_tangan_plastik_qty'] += $qty;
                }
            } elseif (str_contains($nm, 'PLASTIK') || str_contains($nm, 'HD')) {
                $summary['plastik_hd_qty'] += $qty;
                $summary['plastik_hd_nilai'] += $subtotal;
            } elseif (str_contains($nm, 'LAKBAN')) {
                if (str_contains($nm, 'KECIL')) {
                    $summary['lakban_kecil_qty'] += $qty;
                    $summary['lakban_kecil_nilai'] += $subtotal;
                } else {
                    $summary['lakban_besar_qty'] += $qty;
                    $summary['lakban_besar_nilai'] += $subtotal;
                }
            } elseif (str_contains($nm, 'TALI') || str_contains($nm, 'RAFIA')) {
                $summary['tali_rafia_qty'] += $qty;
                $summary['tali_rafia_nilai'] += $subtotal;
            }
        }

        if (!empty($varietasList)) {
            $summary['varietas_singkong'] = implode(' / ', array_unique($varietasList));
        }

        if (!empty($singkongGrades)) {
            $gradeParts = [];
            foreach ($singkongGrades as $gName => $gQty) {
                $gLabel = ($gName !== '-' && !empty($gName)) ? "Grade {$gName}" : "Tanpa Grade";
                $gradeParts[] = "{$gLabel}: " . number_format($gQty, 2, ',', '.') . " kg";
            }
            $summary['singkong_grade_desc'] = implode(' + ', $gradeParts);
            $summary['singkong_grades'] = $singkongGrades;
        } else {
            $summary['singkong_grade_desc'] = '-';
            $summary['singkong_grades'] = [];
        }

        $summary['karton_estimasi'] = $kartonCount;
        $summary['items'] = $items;
        $summary['total_item_count'] = count($items);

        return $summary;
    }

    /**
     * Hitung seluruh formula turunan (Rasio Minyak, Tenaga Kerja, Total Biaya, Rendemen, HPP/Kg).
     */
    public function calculateFields(array $data): array
    {
        $singkongQty = (float) ($data['singkong_qty'] ?? 0);
        $singkongNilai = (float) ($data['singkong_nilai'] ?? 0);

        $minyakSawitQty = (float) ($data['minyak_sawit_qty'] ?? 0);
        $minyakKelapaQty = (float) ($data['minyak_kelapa_qty'] ?? 0);
        $minyakNilai = (float) ($data['minyak_nilai'] ?? 0);
        $minyakRasioPersen = $singkongQty > 0
            ? (($minyakSawitQty + $minyakKelapaQty) / $singkongQty) * 100
            : 0;

        $bumbuNilai = (float) ($data['bumbu_nilai'] ?? 0);
        $kartonBaruNilai = (float) ($data['karton_baru_nilai'] ?? 0);
        $kartonBekasNilai = (float) ($data['karton_bekas_nilai'] ?? 0);
        $plastikHdNilai = (float) ($data['plastik_hd_nilai'] ?? 0);
        $lakbanBesarNilai = (float) ($data['lakban_besar_nilai'] ?? 0);
        $lakbanKecilNilai = (float) ($data['lakban_kecil_nilai'] ?? 0);
        $taliRafiaNilai = (float) ($data['tali_rafia_nilai'] ?? 0);

        $berkoBahanNilai = (float) ($data['berko_bahan_nilai'] ?? 0);
        $totalBahanNilai = $singkongNilai + $berkoBahanNilai + $minyakNilai + $bumbuNilai + $kartonBaruNilai +
            $kartonBekasNilai + $plastikHdNilai + $lakbanBesarNilai + $lakbanKecilNilai + $taliRafiaNilai;

        // Gas CNG (Mendukung Kurs JISDOR & Tarif USD per MMBTU)
        $cngMmbtu = (float) ($data['cng_mmbtu'] ?? 0);
        $cngNm3 = (float) ($data['cng_nm3'] ?? 0);
        if ($cngNm3 <= 0 && $cngMmbtu > 0) {
            $cngNm3 = round($cngMmbtu * 27.921, 4);
        } elseif ($cngNm3 > 0 && $cngMmbtu <= 0) {
            $cngMmbtu = round($cngNm3 / 27.921, 4);
        }
        $cngTarifUsd = (float) ($data['cng_tarif_usd'] ?? 0);
        $cngKursJisdor = (float) ($data['cng_kurs_jisdor'] ?? 0);
        $cngTarif = (float) ($data['cng_tarif'] ?? 0);

        if ($cngTarifUsd > 0 && $cngKursJisdor > 0) {
            $cngTarif = round($cngTarifUsd * $cngKursJisdor, 2);
        } elseif ($cngTarif <= 0) {
            $cngTarif = 226800.00;
        }

        $cngNilai = (float) ($data['cng_nilai'] ?? 0);
        if ($cngNilai <= 0 && $cngMmbtu > 0) {
            $cngNilai = round($cngMmbtu * $cngTarif, 2);
        }

        // Tenaga Kerja (Pencatatan HPP Harian Terpadu Sesuai Arahan Mentor)
        // Jumlah orang dari absensi & total rupiah akumulasi gaji aktual (tiap orang beda jam kerja & gaji)
        $tkJumlah = (int) ($data['tk_jumlah_org'] ?? ($data['tk_langsung_org'] ?? 0));
        if ($tkJumlah === 0 && (!empty($data['tk_tidak_langsung_org']) || !empty($data['tk_training_org']))) {
            $tkJumlah = (int) (($data['tk_langsung_org'] ?? 0) + ($data['tk_tidak_langsung_org'] ?? 0) + ($data['tk_training_org'] ?? 0));
        }
        $tkLangsung = $tkJumlah;
        $tkTidakLangsung = (int) ($data['tk_tidak_langsung_org'] ?? 0);
        $tkTraining = (int) ($data['tk_training_org'] ?? 0);
        $tkTotalNilai = isset($data['tk_total_nilai']) ? (float) $data['tk_total_nilai'] : 0;
        $tkTarif = $tkJumlah > 0 ? round($tkTotalNilai / $tkJumlah, 2) : 0;

        // Overhead Pabrik (FOH)
        $fotocopy = (float) ($data['fotocopy_nilai'] ?? 0);
        $sarungPlastik = (float) ($data['sarung_tangan_plastik_nilai'] ?? 0);
        $sarungKain = (float) ($data['sarung_tangan_kain_nilai'] ?? 0);
        $qc = (float) ($data['qc_pengawasan_nilai'] ?? 0);
        $listrik = (float) ($data['listrik_air_telp_nilai'] ?? 0);
        $pemeliharaan = (float) ($data['pemeliharaan_mesin_nilai'] ?? 0);
        $penyusutan = (float) ($data['penyusutan_mesin_nilai'] ?? 0);
        $limbahPadat = (float) ($data['limbah_padat_nilai'] ?? 0);
        $limbahKimia = (float) ($data['limbah_kimia_nilai'] ?? 0);

        $totalOverheadNilai = $fotocopy + $sarungPlastik + $sarungKain + $qc + $listrik +
            $pemeliharaan + $penyusutan + $limbahPadat + $limbahKimia;

        // Total Biaya Produksi (Kolom Kuning)
        $totalBiayaProduksi = $totalBahanNilai + $cngNilai + $tkTotalNilai + $totalOverheadNilai;

        // Output WIP & Barang Jadi (Tabel Terpadu Sesuai Format Asli Excel Mirasa)
        $iflQty = (float) ($data['ifl_qty'] ?? 0);
        $asinBarcoQty = (float) ($data['asin_barco_qty'] ?? 0);
        $asinSawitQty = (float) ($data['asin_sawit_qty'] ?? 0);
        $noSaltQty = (float) ($data['no_salt_qty'] ?? 0);
        $ucampQty = (float) ($data['ucamp_qty'] ?? 0);
        $baloQty = (float) ($data['balo_gelombang_qty'] ?? 0);
        $balqiQty = (float) ($data['balqi_qty'] ?? 0);
        $berkoQty = (float) ($data['berko_qty'] ?? 0);
        $berkoMeQty = (float) ($data['berko_me_qty'] ?? 0);

        // Ekstraksi dari baris tabel terpadu jika ada
        $totalOutputKg = 0;
        $uncategorizedQty = 0;
        $wasteLengketFromItems = 0;
        if (!empty($data['output_items']) && is_array($data['output_items'])) {
            foreach ($data['output_items'] as $item) {
                $itemKg = (float) ($item['qty_kg'] ?? ($item['qty_hasil'] ?? 0));
                if ($itemKg <= 0) continue;
                $totalOutputKg += $itemKg;

                $barangId = (int) ($item['barang_id'] ?? 0);
                $isMatched = false;
                if ($barangId > 0) {
                    $b = MstBarang::find($barangId);
                    if ($b) {
                        $cd = strtoupper($b->barang_cd);
                        $nm = strtoupper($b->barang_nm);
                        if (str_contains($cd, 'IFL') || str_contains($nm, 'IFL') || str_contains($nm, 'INDOFOOD') || str_contains($cd, 'IFM') || str_contains($cd, 'FCC')) { $iflQty += $itemKg; $isMatched = true; }
                        elseif (str_contains($cd, 'ASB') || str_contains($nm, 'BARCO')) { $asinBarcoQty += $itemKg; $isMatched = true; }
                        elseif (str_contains($cd, 'ASW') || str_contains($nm, 'SAWIT')) { $asinSawitQty += $itemKg; $isMatched = true; }
                        elseif (str_contains($cd, 'NSL') || str_contains($nm, 'NO SALT') || str_contains($nm, 'TAWAR')) { $noSaltQty += $itemKg; $isMatched = true; }
                        elseif (str_contains($cd, 'UCM') || str_contains($nm, 'UCAMP') || str_contains($nm, 'U/CAMP') || str_contains($nm, 'CAMPUR')) { $ucampQty += $itemKg; $isMatched = true; }
                        elseif (str_contains($cd, 'BLQ') || str_contains($nm, 'BALQI') || str_contains($nm, 'BAL Q') || str_contains($nm, 'BAL-Q')) { $balqiQty += $itemKg; $isMatched = true; }
                        elseif (str_contains($nm, 'BALO') || str_contains($nm, 'GELOMBANG')) { $baloQty += $itemKg; $isMatched = true; }
                        elseif (str_contains($cd, 'LGT') || str_contains($nm, 'LENGKET')) { 
                            $berkoMeQty += $itemKg; 
                            $wasteLengketFromItems += $itemKg;
                            $isMatched = true; 
                        }
                        elseif (str_contains($cd, 'BRK-ME') || str_contains($nm, 'BERKO ME')) { $berkoMeQty += $itemKg; $isMatched = true; }
                        elseif (str_contains($cd, 'BRK') || str_contains($nm, 'BERKO')) { $berkoQty += $itemKg; $isMatched = true; }
                    }
                }
                if (!$isMatched) {
                    $uncategorizedQty += $itemKg;
                }
            }
        }

        $totalBerkoQty = $berkoQty + $berkoMeQty;
        $totalManualQty = $asinBarcoQty + $asinSawitQty + $noSaltQty + $ucampQty + $baloQty + $balqiQty;

        // Fallback jika lini IFL/IFM dipilih tapi kolom spesifik belum terisi
        $liniUpper = strtoupper($data['lini_produksi'] ?? '');
        if ($iflQty <= 0 && (str_contains($liniUpper, 'IFL') || str_contains($liniUpper, 'IFM'))) {
            if ($totalManualQty == 0) {
                if ($totalOutputKg > 0) {
                    $iflQty = max(0, $totalOutputKg - $totalBerkoQty);
                } elseif (!empty($data['total_wip_qty']) && (float)$data['total_wip_qty'] > 0) {
                    $iflQty = max(0, (float)$data['total_wip_qty'] - $totalBerkoQty);
                }
            }
        }

        $totalWipQty = $iflQty + $totalManualQty + $totalBerkoQty + $uncategorizedQty;
        $effectiveOutputKg = $totalOutputKg > 0 ? $totalOutputKg : $totalWipQty;
        if ($totalWipQty <= 0 && $effectiveOutputKg > 0) {
            $totalWipQty = $effectiveOutputKg;
        }

        // FOH dialihkan ke Jurnal Keuangan / Beban Periodik (tidak di-injeksi ke HPP produksi harian)
        $totalOverheadNilai = $fotocopy + $sarungPlastik + $sarungKain + $qc + $listrik +
            $pemeliharaan + $penyusutan + $limbahPadat + $limbahKimia;
        $totalBiayaProduksi = $totalBahanNilai + $cngNilai + $tkTotalNilai + $totalOverheadNilai;

        $berkoPersen = $totalWipQty > 0 ? ($totalBerkoQty / $totalWipQty) * 100 : 0;
        $berkoBiasaPersen = $totalWipQty > 0 ? ($berkoQty / $totalWipQty) * 100 : 0;
        $berkoMePersen = $totalWipQty > 0 ? ($berkoMeQty / $totalWipQty) * 100 : 0;
        $rendemenPersen = $singkongQty > 0 ? ($effectiveOutputKg / $singkongQty) * 100 : 0;
        $hppPerKg = $effectiveOutputKg > 0 ? ($totalBiayaProduksi / $effectiveOutputKg) : 0;

        // Metrik Limbah Fisik HACCP (Formulir MFI/HACCP-04/FRM-03/045/VIII/2021)
        // Auto-sinkronisasi dari baris tabel jika form card-3B dilepas
        $wasteLengketQty = (float) ($data['waste_lengket_qty'] ?? 0);
        if ($wasteLengketQty <= 0 && $wasteLengketFromItems > 0) {
            $wasteLengketQty = $wasteLengketFromItems;
        }

        $berkoIflQty = (float) ($data['berko_ifl_qty'] ?? 0);
        $berkoNonIflQty = (float) ($data['berko_non_ifl_qty'] ?? 0);
        if ($berkoIflQty <= 0 && $berkoNonIflQty <= 0 && $totalBerkoQty > 0) {
            if (str_contains($liniUpper, 'IFL') || str_contains($liniUpper, 'IFM')) {
                $berkoIflQty = $totalBerkoQty;
            } else {
                $berkoNonIflQty = $totalBerkoQty;
            }
        }

        $wasteTotalQty = isset($data['waste_total_qty']) && (float) $data['waste_total_qty'] > 0
            ? (float) $data['waste_total_qty']
            : ($wasteLengketQty + $berkoIflQty + $berkoNonIflQty);
        $wasteLengketPersen = $singkongQty > 0 ? ($wasteLengketQty / $singkongQty) * 100 : 0;
        $poIflNo = !empty($data['po_ifl_no']) ? trim($data['po_ifl_no']) : null;
        $poIflRealisasiQty = (float) ($data['po_ifl_realisasi_qty'] ?? 0);
        $poIflTerpenuhiSt = isset($data['po_ifl_terpenuhi_st']) && $data['po_ifl_terpenuhi_st'] !== ''
            ? (bool) $data['po_ifl_terpenuhi_st']
            : null;

        return array_merge($data, [
            'singkong_qty'                => $singkongQty,
            'singkong_nilai'              => $singkongNilai,
            'minyak_sawit_qty'            => $minyakSawitQty,
            'minyak_kelapa_qty'           => $minyakKelapaQty,
            'minyak_nilai'                => $minyakNilai,
            'minyak_rasio_persen'         => round($minyakRasioPersen, 2),
            'bumbu_qty'                   => (float) ($data['bumbu_qty'] ?? 0),
            'bumbu_nilai'                 => $bumbuNilai,
            'karton_baru_qty'             => (float) ($data['karton_baru_qty'] ?? 0),
            'karton_baru_nilai'           => $kartonBaruNilai,
            'karton_bekas_qty'            => (float) ($data['karton_bekas_qty'] ?? 0),
            'karton_bekas_nilai'          => $kartonBekasNilai,
            'plastik_hd_qty'              => (float) ($data['plastik_hd_qty'] ?? 0),
            'plastik_hd_nilai'            => $plastikHdNilai,
            'lakban_besar_qty'            => (float) ($data['lakban_besar_qty'] ?? 0),
            'lakban_besar_nilai'          => $lakbanBesarNilai,
            'lakban_kecil_qty'            => (float) ($data['lakban_kecil_qty'] ?? 0),
            'lakban_kecil_nilai'          => $lakbanKecilNilai,
            'tali_rafia_qty'              => (float) ($data['tali_rafia_qty'] ?? 0),
            'tali_rafia_nilai'            => $taliRafiaNilai,
            'total_bahan_nilai'           => $totalBahanNilai,
            'cng_nm3'                     => $cngNm3,
            'cng_mmbtu'                   => $cngMmbtu,
            'cng_tarif_usd'               => $cngTarifUsd,
            'cng_kurs_jisdor'             => $cngKursJisdor,
            'cng_tarif'                   => $cngTarif,
            'cng_nilai'                   => $cngNilai,
            'tk_jumlah_org'               => $tkJumlah,
            'tk_langsung_org'             => $tkLangsung,
            'tk_tidak_langsung_org'       => $tkTidakLangsung,
            'tk_training_org'             => $tkTraining,
            'tk_tarif_per_org'            => $tkTarif,
            'tk_total_nilai'              => $tkTotalNilai,
            'fotocopy_nilai'              => $fotocopy,
            'sarung_tangan_plastik_qty'   => (float) ($data['sarung_tangan_plastik_qty'] ?? 0),
            'sarung_tangan_plastik_nilai' => $sarungPlastik,
            'sarung_tangan_kain_qty'      => (float) ($data['sarung_tangan_kain_qty'] ?? 0),
            'sarung_tangan_kain_nilai'    => $sarungKain,
            'qc_pengawasan_nilai'         => $qc,
            'listrik_air_telp_nilai'      => $listrik,
            'pemeliharaan_mesin_nilai'    => $pemeliharaan,
            'penyusutan_mesin_nilai'      => $penyusutan,
            'limbah_padat_nilai'          => $limbahPadat,
            'limbah_kimia_nilai'          => $limbahKimia,
            'total_overhead_nilai'        => $totalOverheadNilai,
            'total_biaya_produksi'        => $totalBiayaProduksi,
            'ifl_qty'                     => $iflQty,
            'asin_barco_qty'              => $asinBarcoQty,
            'asin_sawit_qty'              => $asinSawitQty,
            'no_salt_qty'                 => $noSaltQty,
            'ucamp_qty'                   => $ucampQty,
            'balo_gelombang_qty'          => $baloQty,
            'balqi_qty'                   => $balqiQty,
            'berko_qty'                   => $berkoQty,
            'berko_me_qty'                => $berkoMeQty,
            'total_berko_qty'             => $totalBerkoQty,
            'berko_persen'                => round($berkoPersen, 2),
            'berko_biasa_persen'          => round($berkoBiasaPersen, 2),
            'berko_me_persen'             => round($berkoMePersen, 2),
            'total_wip_qty'               => $totalWipQty,
            'waste_lengket_qty'           => $wasteLengketQty,
            'berko_ifl_qty'               => $berkoIflQty,
            'berko_non_ifl_qty'           => $berkoNonIflQty,
            'waste_total_qty'             => $wasteTotalQty,
            'waste_lengket_persen'        => round($wasteLengketPersen, 2),
            'po_ifl_no'                   => $poIflNo,
            'po_ifl_realisasi_qty'        => $poIflRealisasiQty,
            'po_ifl_terpenuhi_st'         => $poIflTerpenuhiSt,
            'rendemen_persen'             => round($rendemenPersen, 2),
            'hpp_per_kg'                  => round($hppPerKg, 2),
        ]);
    }

    /**
     * Menyimpan data kalkulasi produksi harian & menyuntik stok fisik WIP jika status POSTED.
     */
    public function store(array $data): DatProduksiHdr
    {
        return DB::transaction(function () use ($data) {
            $tgl = $data['produksi_tgl'] ?? date('Y-m-d');
            $dayNamesIndo = [
                0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
            ];
            $data['hari_nm'] = $dayNamesIndo[Carbon::parse($tgl)->dayOfWeek] ?? 'Senin';
            $lokasiRaw = strtoupper(trim((string)($data['lokasi_pabrik'] ?? 'MFI')));
            $isBmb = in_array($lokasiRaw, ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);
            $data['lokasi_pabrik'] = $isBmb ? 'BMB' : 'MFI';

            if (!isset($data['bf_aktif_count']) || $data['bf_aktif_count'] === '' || $data['bf_aktif_count'] === null) {
                $data['bf_aktif_count'] = null;
            } else {
                $data['bf_aktif_count'] = (int) $data['bf_aktif_count'];
            }

            $prodNo = !empty($data['produksi_no']) ? trim($data['produksi_no']) : null;
            if (empty($prodNo) || DatProduksiHdr::where('produksi_no', $prodNo)->exists()) {
                $data['produksi_no'] = $this->codeGenerator->generateProduksiNo($tgl, null, $data['lokasi_pabrik']);
            } else {
                $data['produksi_no'] = $prodNo;
            }

            // Validasi keberadaan Dokumen BPPB (Pengeluaran Bahan Baku Gudang)
            if (!empty($data['pakai_id'])) {
                $pakaiHdr = DatPakaiHdr::find($data['pakai_id']);
                if (!$pakaiHdr || !in_array($pakaiHdr->status_st, ['POSTED', 'DRAFT'])) {
                    throw new Exception("Dokumen BPPB [{$pakaiHdr?->pakai_no}] tidak ditemukan atau status tidak valid untuk diproses.");
                }
            }

            // Cek apakah produk utama yang dihasilkan adalah standar Indofood IFM / WIP-FCC
            $lini = strtoupper($data['lini_produksi'] ?? 'PRODUKSI IFM');
            $isIfm = false;
            if (!empty($data['output_items']) && is_array($data['output_items'])) {
                $firstItemId = (int) ($data['output_items'][0]['barang_id'] ?? 0);
                if ($firstItemId > 0) {
                    $firstBarang = MstBarang::find($firstItemId);
                    if ($firstBarang) {
                        $bCd = strtoupper($firstBarang->barang_cd ?? '');
                        $bNm = strtoupper($firstBarang->barang_nm ?? '');
                        $isIfm = str_contains($bCd, 'FCC') || str_contains($bCd, 'IFM') || str_contains($bNm, 'WIP-FCC') || (str_contains($bNm, 'IFM') && !str_contains($bNm, 'MANUAL'));
                    }
                }
            }
            if (!$isIfm && !empty($lini) && str_contains(strtoupper($lini), 'IFM')) {
                $isIfm = true;
            }

            // Normalisasi Shift & Karton
            $shift = strtoupper(trim($data['shift_cd'] ?? 'A'));
            $shift = in_array($shift, ['A', 'B']) ? $shift : 'A';
            $qtyKarton = !empty($data['qty_karton']) ? (int) $data['qty_karton'] : 0;
            $hasKartonInput = !empty($data['no_karton_awal']) && (int) $data['no_karton_awal'] > 0;

            if ($isIfm && $hasKartonInput) {
                $noAwal = (int) $data['no_karton_awal'];
                $noAkhir = !empty($data['no_karton_akhir']) ? (int) $data['no_karton_akhir'] : ($qtyKarton > 0 ? ($noAwal + $qtyKarton - 1) : $noAwal);
            } elseif ($isIfm && $qtyKarton > 0) {
                $noAwal = 1;
                $noAkhir = $qtyKarton;
            } else {
                $noAwal = $hasKartonInput ? (int) $data['no_karton_awal'] : null;
                $noAkhir = (!empty($data['no_karton_akhir']) && (int) $data['no_karton_akhir'] > 0) ? (int) $data['no_karton_akhir'] : null;
            }

            $data['shift_cd'] = $shift;
            $data['no_karton_awal'] = $noAwal;
            $data['no_karton_akhir'] = $noAkhir;
            $data['qty_karton'] = $qtyKarton;
            $data['jam_produksi'] = !empty($data['jam_produksi']) ? trim($data['jam_produksi']) : date('H:i');
            $data['varietas_singkong'] = !empty($data['varietas_singkong']) ? trim($data['varietas_singkong']) : 'STP / MGU';

            // Opsi Penomoran Batch: Sertakan (Default) vs Tangguhkan (Buffer Stock IFM)
            $isBatchExcluded = (!empty($data['batch_option']) && $data['batch_option'] === 'exclude') 
                || (!empty($data['batch_wip_no']) && in_array(strtoupper(trim($data['batch_wip_no'])), ['BELUM TERBIT', 'PENDING']));

            if ($isBatchExcluded) {
                $data['batch_wip_no'] = 'BELUM TERBIT';
                $data['no_karton_awal'] = null;
                $data['no_karton_akhir'] = null;
            } else {
                // Generate Batch No jika belum ada
                if (empty($data['batch_wip_no'])) {
                    if ($isIfm && $qtyKarton > 0 && !empty($noAwal) && !empty($noAkhir)) {
                        $padAwal = str_pad((string) $noAwal, 4, '0', STR_PAD_LEFT);
                        $padAkhir = str_pad((string) $noAkhir, 4, '0', STR_PAD_LEFT);
                        $data['batch_wip_no'] = "{$shift}{$padAwal} - {$shift}{$padAkhir}";
                    } else {
                        // Barang Jadi Reguler PT Mirasa: Format Tanggal Murni 'd m Y'
                        $baseDate = Carbon::parse($tgl)->format('d m Y');
                        $data['batch_wip_no'] = $baseDate;
                    }
                } else {
                    // Jika bukan IFM karton dan batch_wip_no mengandung suffix -01/-02 dari form lama, bersihkan ke format tanggal murni
                    if (!$isIfm && preg_match('/^\d{2}\s+\d{2}\s+\d{4}-\d+$/', trim($data['batch_wip_no']))) {
                        $data['batch_wip_no'] = preg_replace('/-\d+$/', '', trim($data['batch_wip_no']));
                    }
                }
            }

            // Tanggal Kedaluwarsa Produk (Fallback jika tidak diisi manual oleh operator)
            if (empty($data['exp_date'])) {
                $data['exp_date'] = $isIfm
                    ? Carbon::parse($tgl)->addMonths(6)->toDateString()
                    : Carbon::parse($tgl)->addYear()->subDay()->toDateString();
            }

            // Hitung semua turunan biaya, rendemen, dan HPP/kg
            $calculated = $this->calculateFields($data);

            // Simpan Header Produksi Harian
            $produksi = DatProduksiHdr::create($calculated);

            // Mapping Master Barang WIP Sesuai Standar Pabrik
            $wipMap = [
                'IFL'             => ['code' => 'WIP-FCC', 'fallback' => 'WIP-IFL', 'qty' => (float) $produksi->ifl_qty],
                'ASIN_BARCO'      => ['code' => 'WIP-ASB', 'qty' => (float) $produksi->asin_barco_qty],
                'ASIN_SAWIT'      => ['code' => 'WIP-ASW', 'qty' => (float) $produksi->asin_sawit_qty],
                'NO_SALT'         => ['code' => 'WIP-NSL', 'qty' => (float) $produksi->no_salt_qty],
                'BALO_GELOMBANG'  => ['code' => 'WIP-BLQ', 'qty' => (float) $produksi->balo_gelombang_qty],
                'BERKO'           => ['code' => 'WIP-BRK', 'qty' => (float) $produksi->total_berko_qty],
            ];

            $gudangId = (int) $produksi->gudang_id;
            $hppPerKg = (float) $produksi->hpp_per_kg;

            // Hitung Tanggal Kedaluwarsa Sesuai Input Operator / Standar Lini:
            $expiredDate = !empty($produksi->exp_date)
                ? Carbon::parse($produksi->exp_date)->toDateString()
                : ($isIfm
                    ? Carbon::parse($tgl)->addMonths(6)->toDateString()
                    : Carbon::parse($tgl)->addYear()->subDay()->toDateString());

            $hasOutputItems = !empty($data['output_items']) && is_array($data['output_items']) && count(array_filter($data['output_items'], fn($it) => !empty($it['barang_id']))) > 0;

            if (!$hasOutputItems) {
                foreach ($wipMap as $kategori => $info) {
                    if ($info['qty'] <= 0) continue;

                    $barang = MstBarang::where('barang_cd', $info['code'])->first();
                    if (!$barang && !empty($info['fallback'])) {
                        $barang = MstBarang::where('barang_cd', $info['fallback'])->first();
                    }
                    if (!$barang) {
                        $barang = MstBarang::where('barang_nm', 'LIKE', "%{$info['code']}%")->first();
                    }
                    if (!$barang && $kategori === 'IFL') {
                        $barang = MstBarang::where('barang_cd', 'LIKE', '%IFL%')->orWhere('barang_nm', 'LIKE', '%IFL%')->first();
                    }

                    if ($barang) {
                        // Aturan Batch Per Jenis Output:
                        // - Kemasan Karton (FCC/IFL/IFM) → range karton (A0001 - A0243)
                        // - WIP Curah (Berko, Asin, No Salt, dll) → format tanggal urut (DD MM YYYY-01, -02)
                        $isCurahWip = in_array($kategori, ['BERKO', 'ASIN_BARCO', 'ASIN_SAWIT', 'NO_SALT', 'BALO_GELOMBANG']);
                        $baseDate = Carbon::parse($tgl)->format('d m Y');
                        $batchVarian = ($isIfm && !$isCurahWip)
                            ? $produksi->batch_wip_no
                            : $baseDate;

                        $subtotalNilai = round($info['qty'] * $hppPerKg, 2);

                        DatProduksiDtl::create([
                            'produksi_id'     => $produksi->produksi_id,
                            'barang_id'       => $barang->barang_id,
                            'jenis_cd'        => 'WIP',
                            'kategori_output' => $kategori,
                            'qty_hasil'       => $info['qty'],
                            'satuan_cd'       => 'KG',
                            'qty_kg'          => $info['qty'],
                            'batch_no'        => $batchVarian,
                            'hpp_satuan'      => $hppPerKg,
                            'total_nilai'     => $subtotalNilai,
                            'keterangan_txt'  => "Hasil Olahan {$kategori} Produksi {$produksi->produksi_no}",
                        ]);

                        // Jika status POSTED, otomatis tambahkan stok fisik & kartu stok
                        if ($produksi->status_cd === 'POSTED') {
                            $this->stokService->addStock(
                                $gudangId,
                                $barang->barang_id,
                                $batchVarian,
                                $info['qty'],
                                $expiredDate,
                                $produksi->produksi_no,
                                "Hasil Produksi Harian {$produksi->produksi_no} ({$kategori})",
                                $hppPerKg
                            );
                        }
                    }
                }
            }

            // Output Dinamis: Barang Jadi (Finish Good / FG) atau Varian Kustom (Sesuai Blueprint 9.B)
            if (!empty($data['output_items']) && is_array($data['output_items'])) {
                // Proteksi Konsolidasi Backend: Otomatis gabungkan kuantitas jika ada barang_id yang sama dalam 1 dokumen
                $mergedItems = [];
                foreach ($data['output_items'] as $it) {
                    $bId = (int) ($it['barang_id'] ?? 0);
                    if ($bId <= 0) continue;
                    if (isset($mergedItems[$bId])) {
                        $mergedItems[$bId]['qty_hasil'] = (float)($mergedItems[$bId]['qty_hasil'] ?? 0) + (float)($it['qty_hasil'] ?? 0);
                        $mergedItems[$bId]['qty_kg'] = (float)($mergedItems[$bId]['qty_kg'] ?? 0) + (float)($it['qty_kg'] ?? 0);
                    } else {
                        $mergedItems[$bId] = $it;
                    }
                }
                $outputItemsToProcess = array_values($mergedItems);

                foreach ($outputItemsToProcess as $item) {
                    $itemBarangId = (int) ($item['barang_id'] ?? 0);
                    $qtyHasil = (float) ($item['qty_hasil'] ?? 0);
                    $qtyKg = (float) ($item['qty_kg'] ?? 0);

                    if ($itemBarangId <= 0 || ($qtyHasil <= 0 && $qtyKg <= 0)) {
                        continue;
                    }

                    $barang = MstBarang::with(['jenisBarang', 'satuanDasar'])->find($itemBarangId);
                    if (!$barang) continue;

                    $jenisCd = !empty($item['jenis_cd']) ? strtoupper($item['jenis_cd']) : ($barang->jenisBarang->jenis_barang_cd ?? 'FG');
                    $satuanCd = !empty($item['satuan_cd']) ? $item['satuan_cd'] : ($barang->satuanDasar->satuan_cd ?? 'KARTON');

                    // Deteksi produk curah (Berko, Asin Barco/Sawit, No Salt, Balo) vs kemasan karton urut (IFM)
                    $barangCdUpper = strtoupper($barang->barang_cd);
                    $barangNmUpper = strtoupper($barang->barang_nm);
                    $isCurahItem = str_contains($barangCdUpper, 'BRK') || str_contains($barangNmUpper, 'BERKO')
                        || str_contains($barangCdUpper, 'LGT') || str_contains($barangNmUpper, 'LENGKET')
                        || str_contains($barangCdUpper, 'RMH') || str_contains($barangNmUpper, 'REMAHAN')
                        || str_contains($barangCdUpper, 'ASB') || str_contains($barangNmUpper, 'BARCO')
                        || str_contains($barangCdUpper, 'ASW') || str_contains($barangNmUpper, 'SAWIT')
                        || str_contains($barangCdUpper, 'NSL') || str_contains($barangNmUpper, 'NO SALT')
                        || str_contains($barangCdUpper, 'BLQ') || str_contains($barangNmUpper, 'BALO')
                        || ($barangCdUpper !== 'WIP-FCC' && str_starts_with($barangCdUpper, 'WIP-') && !str_contains($barangCdUpper, 'IFL') && !str_contains($barangCdUpper, 'IFM'));

                    $baseDate = Carbon::parse($tgl)->format('d m Y');
                    if ($isCurahItem) {
                        // Bersihkan suffix -01/-02 jika terbawa dari form lama
                        $cleanBatch = !empty($item['batch_no']) ? preg_replace('/-\d+$/', '', trim($item['batch_no'])) : '';
                        if (empty($cleanBatch) || $cleanBatch === '-') {
                            $batchItem = $baseDate;
                        } else {
                            $batchItem = $cleanBatch;
                        }
                    } else {
                        if ($isBatchExcluded) {
                            $batchItem = 'BELUM TERBIT';
                        } else {
                            $batchItem = (!empty($item['batch_no']) && $item['batch_no'] !== '-') ? $item['batch_no'] : $produksi->batch_wip_no;
                        }
                    }

                    $hppItem = isset($item['hpp_satuan']) && (float)$item['hpp_satuan'] > 0 ? (float)$item['hpp_satuan'] : $hppPerKg;

                    $finalQtyHasil = $qtyHasil > 0 ? $qtyHasil : $qtyKg;
                    $finalQtyKg = $qtyKg > 0 ? $qtyKg : $qtyHasil;
                    $subtotalNilai = round($finalQtyKg * $hppItem, 2);
                    $isHold = str_contains(strtoupper($barang->barang_cd), 'HLD')
                        || str_contains(strtoupper($barang->barang_nm), 'HOLD')
                        || (($item['grade_cd'] ?? '') === 'HOLD');

                    $isAfkir = str_contains(strtoupper($barang->barang_cd), 'AFK')
                        || str_contains(strtoupper($barang->barang_nm), 'AFKIR')
                        || (!empty($item['grade_cd']) && ($item['grade_cd'] === 'AFKIR' || $item['grade_cd'] === 'KW2'))
                        || ($barang->grade_cd === 'AFKIR' || $barang->grade_cd === 'KW2')
                        || str_contains(strtoupper($barang->barang_nm), 'BERKO')
                        || str_contains(strtoupper($barang->barang_nm), 'REMAHAN')
                        || str_contains(strtoupper($barang->barang_nm), 'LENGKET')
                        || str_starts_with(strtoupper($barang->barang_cd), 'WIP-BRK');

                    $itemGradeCd = $isHold ? 'HOLD' : ($isAfkir ? 'AFKIR' : 'RILIS');

                    if ($isAfkir) {
                        $hppItem = 0;
                        $subtotalNilai = 0;
                    }

                    DatProduksiDtl::create([
                        'produksi_id'     => $produksi->produksi_id,
                        'barang_id'       => $barang->barang_id,
                        'jenis_cd'        => $jenisCd,
                        'kategori_output' => $barang->barang_cd,
                        'qty_hasil'       => $finalQtyHasil,
                        'satuan_cd'       => $satuanCd,
                        'grade_cd'        => $itemGradeCd,
                        'qty_kg'          => $finalQtyKg,
                        'batch_no'        => $batchItem,
                        'hpp_satuan'      => $hppItem,
                        'total_nilai'     => $subtotalNilai,
                        'keterangan_txt'  => $item['keterangan_txt'] ?? "Hasil Produksi {$jenisCd} {$barang->barang_nm} ({$produksi->produksi_no})",
                    ]);

                    if ($produksi->status_cd === 'POSTED') {
                        $this->stokService->addStock(
                            $gudangId,
                            $barang->barang_id,
                            $batchItem,
                            $finalQtyHasil,
                            $expiredDate,
                            $produksi->produksi_no,
                            "Hasil Produksi {$jenisCd} {$produksi->produksi_no}",
                            $hppItem,
                            $itemGradeCd
                        );
                    }
                }
            }

            // Sinkronisasi integritas dokumen BPPB
            if (!empty($produksi->pakai_id)) {
                $pakaiHdr = DatPakaiHdr::find($produksi->pakai_id);
                if ($pakaiHdr) {
                    if (!in_array($pakaiHdr->status_st, ['POSTED', 'DRAFT'])) {
                        throw new Exception("Dokumen BPPB [{$pakaiHdr->pakai_no}] berstatus tidak valid.");
                    }
                    // Jika BPPB sebelumnya masih DRAFT, saat produksi berhasil terbit maka status BPPB diaktifkan POSTED
                    if ($pakaiHdr->status_st === 'DRAFT' && $produksi->status_cd === 'POSTED') {
                        $pakaiHdr->update([
                            'status_st'  => 'POSTED',
                            'posted_at'  => now(),
                            'posted_by'  => auth()->user()?->name ?? 'SYSTEM PRODUKSI',
                            'updated_by' => auth()->user()?->name ?? 'SYSTEM PRODUKSI',
                        ]);
                    }
                }
            }

            return $produksi;
        });
    }

    /**
     * Mengambil 1 data produksi harian beserta relasi.
     */
    public function getById(int $id): DatProduksiHdr
    {
        return DatProduksiHdr::with(['pemakaianBahan', 'gudang', 'outputs.barang', 'adjustmentLogs', 'jurnal'])
            ->where('produksi_id', $id)
            ->firstOrFail();
    }

    /**
     * Menerbitkan nomor batch resmi untuk produksi IFM yang sebelumnya berstatus Buffer Stock (PENDING).
     *
     * @param int $produksiId
     * @param array $data ['tgl_keberangkatan' => 'Y-m-d', 'shift_cd' => 'A'|'B', 'no_karton_awal' => int]
     * @return DatProduksiHdr
     * @throws Exception
     */
    public function assignBatch(int $produksiId, array $data): DatProduksiHdr
    {
        return DB::transaction(function () use ($produksiId, $data) {
            $produksi = DatProduksiHdr::with(['outputs.barang', 'gudang'])->findOrFail($produksiId);

            $tglBerangkat = $data['tgl_keberangkatan'] ?? date('Y-m-d');
            $shift = strtoupper($data['shift_cd'] ?? ($produksi->shift_cd ?: 'A'));
            $noAwal = (int) ($data['no_karton_awal'] ?? 1);
            $qtyKarton = (int) ($produksi->qty_karton > 0 ? $produksi->qty_karton : ($data['qty_karton'] ?? 0));
            $noAkhir = $qtyKarton > 0 ? ($noAwal + $qtyKarton - 1) : $noAwal;

            $padAwal = str_pad((string) $noAwal, 4, '0', STR_PAD_LEFT);
            $padAkhir = str_pad((string) $noAkhir, 4, '0', STR_PAD_LEFT);
            $newBatchNo = "{$shift}{$padAwal} - {$shift}{$padAkhir}";
            $newExpDate = Carbon::parse($tglBerangkat)->addMonths(6)->toDateString();

            // 1. Update Header Produksi
            $produksi->shift_cd = $shift;
            $produksi->no_karton_awal = $noAwal;
            $produksi->no_karton_akhir = $noAkhir;
            $produksi->batch_wip_no = $newBatchNo;
            $produksi->exp_date = $newExpDate;
            $produksi->updated_by = Auth::id() ?? 1;
            $produksi->updated_dt = now();
            $produksi->save();

            // 2. Update Detail Produksi
            $affectedBarangIds = [];
            foreach ($produksi->outputs as $dtl) {
                if (in_array($dtl->batch_no, ['BELUM TERBIT', 'PENDING']) || empty($dtl->batch_no) || str_contains(strtoupper($dtl->barang?->barang_cd ?? ''), 'FCC')) {
                    $dtl->batch_no = $newBatchNo;
                    $dtl->updated_by = Auth::id() ?? 1;
                    $dtl->updated_dt = now();
                    $dtl->save();
                    $affectedBarangIds[] = $dtl->barang_id;
                }
            }

            // 3. Update DatStokBatch dan DatStokLedger di Gudang jika sudah POSTED
            $gudangId = (int) $produksi->gudang_id;
            
            // Cari stok batch BELUM TERBIT / PENDING untuk barang yang diproduksi di dokumen ini
            $pendingBatches = DatStokBatch::where('gudang_id', $gudangId)
                ->whereIn('batch_no', ['BELUM TERBIT', 'PENDING'])
                ->whereIn('barang_id', $affectedBarangIds)
                ->get();

            foreach ($pendingBatches as $stk) {
                $existingNewBatch = DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $stk->barang_id)
                    ->where('batch_no', $newBatchNo)
                    ->where('grade_cd', $stk->grade_cd)
                    ->first();

                if ($existingNewBatch) {
                    $existingNewBatch->qty_awal += $stk->qty_awal;
                    $existingNewBatch->sisa_qty += $stk->sisa_qty;
                    $existingNewBatch->expired_tgl = $newExpDate;
                    $existingNewBatch->save();
                    $stk->delete();
                } else {
                    $stk->batch_no = $newBatchNo;
                    $stk->expired_tgl = $newExpDate;
                    $stk->save();
                }
            }

            // Update catatan ledger kartu stok terkait nomor dokumen produksi ini
            DatStokLedger::where('dokumen_no', $produksi->produksi_no)
                ->whereIn('batch_no', ['BELUM TERBIT', 'PENDING'])
                ->update(['batch_no' => $newBatchNo]);

            return $produksi;
        });
    }

    /**
     * Hapus data produksi harian (Soft Delete) dan sesuaikan stok fisik jika POSTED.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $produksi = $this->getById($id);

            // Jika status POSTED, sesuaikan saldo stok WIP yang sempat dicatat
            if ($produksi->status_cd === 'POSTED') {
                $outputs = DatProduksiDtl::where('produksi_id', $produksi->produksi_id)
                    ->where('deleted_st', false)
                    ->get();

                foreach ($outputs as $out) {
                    try {
                        $qtyToDeduct = $out->qty_hasil > 0 ? (float) $out->qty_hasil : (float) $out->qty_kg;
                        $this->stokService->deductStock(
                            (int) $produksi->gudang_id,
                            (int) $out->barang_id,
                            $out->batch_no,
                            $qtyToDeduct,
                            $produksi->produksi_no,
                            "Pembatalan Dokumen Produksi {$produksi->produksi_no}"
                        );
                    } catch (Exception $e) {
                        // Lanjutkan jika ada penyesuaian parsial
                    }
                }
            }

            // Batalkan Jurnal Umum jika dokumen HPP WIP sudah pernah diterbitkan
            if ($produksi->jurnal_id) {
                $this->jurnalService->batalkanJurnal($produksi->produksi_no, Auth::user());
            }

            DatProduksiDtl::where('produksi_id', $produksi->produksi_id)->delete();
            return $produksi->delete();
        });
    }


    /**
     * Mengambil daftar rincian hasil barang produksi (Point 9 - Hasil Barang Produksi)
     * Dilengkapi paginasi, filter lengkap (Gudang, Nama Barang, Kode Barang, Kode Batch, Jenis), dan sisa stok riil on-hand.
     */
    /**
     * Mengambil daftar Dokumen Produksi Header (1 Baris per Dokumen SPK Produksi)
     * lengkap dengan eager-loading rincian hasil output fisik (WIP/FG),
     * kalkulasi akumulasi berat, dan status sisa stok batch.
     */
    public function getDokumenProduksiList(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = DatProduksiHdr::with([
            'gudang',
            'details.barang.satuan',
            'details.barang.jenisBarang',
            'jurnal'
        ])->where('deleted_st', false);

        if (!empty($filters['gudang_id'])) {
            $query->where('gudang_id', $filters['gudang_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('produksi_no', 'like', $search)
                  ->orWhere('batch_wip_no', 'like', $search)
                  ->orWhere('lini_produksi', 'like', $search)
                  ->orWhere('po_ifl_no', 'like', $search)
                  ->orWhereHas('details.barang', function ($b) use ($search) {
                      $b->where('barang_cd', 'like', $search)
                        ->orWhere('barang_nm', 'like', $search);
                  })
                  ->orWhereHas('details', function ($d) use ($search) {
                      $d->where('batch_no', 'like', $search);
                  });
            });
        }

        if (!empty($filters['barang_cd'])) {
            $query->whereHas('details.barang', function ($b) use ($filters) {
                $b->where('barang_cd', 'like', '%' . $filters['barang_cd'] . '%');
            });
        }

        if (!empty($filters['barang_nm'])) {
            $query->whereHas('details.barang', function ($b) use ($filters) {
                $b->where('barang_nm', 'like', '%' . $filters['barang_nm'] . '%');
            });
        }

        if (!empty($filters['batch_no'])) {
            $batchSearch = '%' . $filters['batch_no'] . '%';
            $query->where(function ($q) use ($batchSearch) {
                $q->where('batch_wip_no', 'like', $batchSearch)
                  ->orWhereHas('details', function ($d) use ($batchSearch) {
                      $d->where('batch_no', 'like', $batchSearch);
                  });
            });
        }

        if (!empty($filters['jenis_cd'])) {
            $targetJenis = strtoupper($filters['jenis_cd']);
            $query->whereHas('details', function ($q) use ($targetJenis) {
                if ($targetJenis === 'FG') {
                    $q->where('jenis_cd', 'FG')
                      ->orWhereHas('barang.jenisBarang', fn($j) => $j->where('jenis_barang_cd', 'FG'));
                } elseif ($targetJenis === 'WIP') {
                    $q->where('jenis_cd', 'WIP')
                      ->orWhereNull('jenis_cd')
                      ->orWhereHas('barang.jenisBarang', fn($j) => $j->where('jenis_barang_cd', 'WIP'));
                }
            });
        }

        if (!empty($filters['tgl_dari'])) {
            $query->whereDate('produksi_tgl', '>=', $filters['tgl_dari']);
        }

        if (!empty($filters['tgl_sampai'])) {
            $query->whereDate('produksi_tgl', '<=', $filters['tgl_sampai']);
        }

        if (!empty($filters['pabrik']) && in_array(strtoupper($filters['pabrik']), ['MFI', 'BMB', 'BARAT', 'TIMUR'])) {
            $isBmbFilter = in_array(strtoupper($filters['pabrik']), ['BMB', 'TIMUR']);
            if (!$isBmbFilter) {
                $query->where(function ($q) {
                    $q->whereIn('lokasi_pabrik', ['MFI', 'BARAT'])->orWhereNull('lokasi_pabrik');
                });
            } else {
                $query->whereIn('lokasi_pabrik', ['BMB', 'TIMUR']);
            }
        }

        // Urutkan transaksi produksi dari yang terbaru
        $paginator = $query->orderByDesc('produksi_tgl')
            ->orderByDesc('produksi_id')
            ->paginate($perPage)
            ->withQueryString();

        // Attach live sisa stok on-hand ke seluruh rincian output dokumen
        $allDetails = $paginator->getCollection()->pluck('details')->flatten();
        $this->attachSisaStok($allDetails);

        return $paginator;
    }

    public function getHasilProduksiList(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = DatProduksiDtl::with(['produksi.gudang', 'barang.satuan', 'barang.jenisBarang'])
            ->where('deleted_st', false);

        if (!empty($filters['gudang_id'])) {
            $query->whereHas('produksi', function ($q) use ($filters) {
                $q->where('gudang_id', $filters['gudang_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'like', $search)
                  ->orWhereHas('barang', function ($b) use ($search) {
                      $b->where('barang_cd', 'like', $search)
                        ->orWhere('barang_nm', 'like', $search);
                  })
                  ->orWhereHas('produksi', function ($p) use ($search) {
                      $p->where('produksi_no', 'like', $search);
                  });
            });
        }

        // Filter Spesifik Sesuai Blueprint 9.B:
        if (!empty($filters['barang_cd'])) {
            $query->whereHas('barang', function ($b) use ($filters) {
                $b->where('barang_cd', 'like', '%' . $filters['barang_cd'] . '%');
            });
        }

        if (!empty($filters['barang_nm'])) {
            $query->whereHas('barang', function ($b) use ($filters) {
                $b->where('barang_nm', 'like', '%' . $filters['barang_nm'] . '%');
            });
        }

        if (!empty($filters['batch_no'])) {
            $query->where('batch_no', 'like', '%' . $filters['batch_no'] . '%');
        }

        if (!empty($filters['jenis_cd'])) {
            $targetJenis = strtoupper($filters['jenis_cd']);
            if ($targetJenis === 'FG') {
                $query->where(function ($q) {
                    $q->where('jenis_cd', 'FG')
                      ->orWhereHas('barang.jenisBarang', fn($j) => $j->where('jenis_barang_cd', 'FG'));
                });
            } elseif ($targetJenis === 'WIP') {
                $query->where(function ($q) {
                    $q->where('jenis_cd', 'WIP')
                      ->orWhereNull('jenis_cd')
                      ->orWhereHas('barang.jenisBarang', fn($j) => $j->where('jenis_barang_cd', 'WIP'));
                });
            }
        }

        if (!empty($filters['kategori'])) {
            $query->where('kategori_output', $filters['kategori']);
        }

        if (!empty($filters['grade_cd'])) {
            $targetGrade = strtoupper($filters['grade_cd']);
            if ($targetGrade === 'KW2') {
                $query->where(function ($q) {
                    $q->where('grade_cd', 'KW2')
                      ->orWhereHas('barang', fn($b) => $b->where('grade_cd', 'KW2'))
                      ->orWhere('kategori_output', 'KW2')
                      ->orWhereHas('produksi', fn($p) => $p->where('lini_produksi', 'ILIKE', '%SORTIR%')->orWhere('lini_produksi', 'ILIKE', '%REWORK%'));
                });
            } elseif ($targetGrade === 'KW1') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('grade_cd', 'KW1')
                            ->orWhere(function ($sub2) {
                                $sub2->whereNull('grade_cd')
                                     ->whereHas('barang', fn($b) => $b->where('grade_cd', 'KW1')->orWhereNull('grade_cd'));
                            });
                    })
                    ->where(function ($sub3) {
                        $sub3->whereNull('grade_cd')->orWhere('grade_cd', '!=', 'KW2');
                    })
                    ->where(function ($sub4) {
                        $sub4->whereNull('kategori_output')->orWhere('kategori_output', '!=', 'KW2');
                    })
                    ->whereDoesntHave('barang', fn($b) => $b->where('grade_cd', 'KW2'))
                    ->whereDoesntHave('produksi', fn($p) => $p->where('lini_produksi', 'ILIKE', '%SORTIR%')->orWhere('lini_produksi', 'ILIKE', '%REWORK%'));
                });
            }
        }

        if (!empty($filters['tgl_dari'])) {
            $query->whereHas('produksi', function ($p) use ($filters) {
                $p->whereDate('produksi_tgl', '>=', $filters['tgl_dari']);
            });
        }

        if (!empty($filters['tgl_sampai'])) {
            $query->whereHas('produksi', function ($p) use ($filters) {
                $p->whereDate('produksi_tgl', '<=', $filters['tgl_sampai']);
            });
        }


        // Urutkan dari produksi terbaru
        $paginator = $query->orderByDesc('output_id')->paginate($perPage)->withQueryString();

        // Attach live sisa stok on hand dari DatStokBatch
        $this->attachSisaStok($paginator->items());

        return $paginator;
    }

    /**
     * Mengambil data transaksi produksi header untuk Formulir HACCP MFI-04 (FORM DATA HASIL PRODUKSI).
     */
    public function getHaccpExportData(array $filters = []): Collection
    {
        $query = DatProduksiHdr::with(['gudang', 'outputs.barang'])
            ->where('deleted_st', false);

        if (!empty($filters['gudang_id'])) {
            $query->where('gudang_id', $filters['gudang_id']);
        }

        if (!empty($filters['tgl_dari'])) {
            $query->whereDate('produksi_tgl', '>=', $filters['tgl_dari']);
        }

        if (!empty($filters['tgl_sampai'])) {
            $query->whereDate('produksi_tgl', '<=', $filters['tgl_sampai']);
        }

        if (!empty($filters['pabrik']) && in_array(strtoupper($filters['pabrik']), ['MFI', 'BMB', 'BARAT', 'TIMUR'])) {
            $isBmbFilter = in_array(strtoupper($filters['pabrik']), ['BMB', 'TIMUR']);
            if (!$isBmbFilter) {
                $query->where(function ($q) {
                    $q->whereIn('lokasi_pabrik', ['MFI', 'BARAT'])->orWhereNull('lokasi_pabrik');
                });
            } else {
                $query->whereIn('lokasi_pabrik', ['BMB', 'TIMUR']);
            }
        }

        if (!empty($filters['batch_no'])) {
            $batchSearch = '%' . $filters['batch_no'] . '%';
            $query->where(function ($q) use ($batchSearch) {
                $q->where('batch_wip_no', 'like', $batchSearch)
                  ->orWhereHas('outputs', function ($o) use ($batchSearch) {
                      $o->where('batch_no', 'like', $batchSearch);
                  });
            });
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('produksi_no', 'like', $search)
                  ->orWhere('batch_wip_no', 'like', $search)
                  ->orWhere('po_ifl_no', 'like', $search)
                  ->orWhere('lini_produksi', 'like', $search);
            });
        }

        return $query->orderBy('produksi_tgl', 'asc')
            ->orderBy('shift_cd', 'asc')
            ->orderBy('produksi_id', 'asc')
            ->get();
    }

    /**
     * Mengambil seluruh hasil produksi untuk keperluan Export Excel
     */
    public function getAllHasilProduksi(array $filters = []): Collection
    {
        $query = DatProduksiDtl::with(['produksi.gudang', 'barang.satuan', 'barang.jenisBarang'])
            ->where('deleted_st', false);

        if (!empty($filters['gudang_id'])) {
            $query->whereHas('produksi', function ($q) use ($filters) {
                $q->where('gudang_id', $filters['gudang_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'like', $search)
                  ->orWhereHas('barang', function ($b) use ($search) {
                      $b->where('barang_cd', 'like', $search)
                        ->orWhere('barang_nm', 'like', $search);
                  })
                  ->orWhereHas('produksi', function ($p) use ($search) {
                      $p->where('produksi_no', 'like', $search);
                  });
            });
        }

        if (!empty($filters['barang_cd'])) {
            $query->whereHas('barang', function ($b) use ($filters) {
                $b->where('barang_cd', 'like', '%' . $filters['barang_cd'] . '%');
            });
        }

        if (!empty($filters['barang_nm'])) {
            $query->whereHas('barang', function ($b) use ($filters) {
                $b->where('barang_nm', 'like', '%' . $filters['barang_nm'] . '%');
            });
        }

        if (!empty($filters['batch_no'])) {
            $query->where('batch_no', 'like', '%' . $filters['batch_no'] . '%');
        }

        if (!empty($filters['jenis_cd'])) {
            $targetJenis = strtoupper($filters['jenis_cd']);
            if ($targetJenis === 'FG') {
                $query->where(function ($q) {
                    $q->where('jenis_cd', 'FG')
                      ->orWhereHas('barang.jenisBarang', fn($j) => $j->where('jenis_barang_cd', 'FG'));
                });
            } elseif ($targetJenis === 'WIP') {
                $query->where(function ($q) {
                    $q->where('jenis_cd', 'WIP')
                      ->orWhereNull('jenis_cd')
                      ->orWhereHas('barang.jenisBarang', fn($j) => $j->where('jenis_barang_cd', 'WIP'));
                });
            }
        }

        if (!empty($filters['batch_no'])) {
            $query->where('batch_no', 'like', '%' . $filters['batch_no'] . '%');
        }

        if (!empty($filters['kategori'])) {
            $query->where('kategori_output', $filters['kategori']);
        }

        if (!empty($filters['grade_cd'])) {
            $targetGrade = strtoupper($filters['grade_cd']);
            if ($targetGrade === 'KW2') {
                $query->where(function ($q) {
                    $q->where('grade_cd', 'KW2')
                      ->orWhereHas('barang', fn($b) => $b->where('grade_cd', 'KW2'))
                      ->orWhere('kategori_output', 'KW2')
                      ->orWhereHas('produksi', fn($p) => $p->where('lini_produksi', 'ILIKE', '%SORTIR%')->orWhere('lini_produksi', 'ILIKE', '%REWORK%'));
                });
            } elseif ($targetGrade === 'KW1') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('grade_cd', 'KW1')
                            ->orWhere(function ($sub2) {
                                $sub2->whereNull('grade_cd')
                                     ->whereHas('barang', fn($b) => $b->where('grade_cd', 'KW1')->orWhereNull('grade_cd'));
                            });
                    })
                    ->where(function ($sub3) {
                        $sub3->whereNull('grade_cd')->orWhere('grade_cd', '!=', 'KW2');
                    })
                    ->where(function ($sub4) {
                        $sub4->whereNull('kategori_output')->orWhere('kategori_output', '!=', 'KW2');
                    })
                    ->whereDoesntHave('barang', fn($b) => $b->where('grade_cd', 'KW2'))
                    ->whereDoesntHave('produksi', fn($p) => $p->where('lini_produksi', 'ILIKE', '%SORTIR%')->orWhere('lini_produksi', 'ILIKE', '%REWORK%'));
                });
            }
        }

        if (!empty($filters['tgl_dari'])) {
            $query->whereHas('produksi', function ($p) use ($filters) {
                $p->whereDate('produksi_tgl', '>=', $filters['tgl_dari']);
            });
        }

        if (!empty($filters['tgl_sampai'])) {
            $query->whereHas('produksi', function ($p) use ($filters) {
                $p->whereDate('produksi_tgl', '<=', $filters['tgl_sampai']);
            });
        }

        $items = $query->orderByDesc('output_id')->get();
        $this->attachSisaStok($items);

        return $items;
    }

    /**
     * Helper privat untuk melampirkan sisa stok on-hand ke item output
     */
    protected function attachSisaStok($items): void
    {
        foreach ($items as $item) {
            $gudangId = $item->produksi?->gudang_id;
            $barangId = $item->barang_id;
            $batchNo  = $item->batch_no;

            if ($gudangId && $barangId && $batchNo) {
                $stokBatch = DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $barangId)
                    ->where('batch_no', $batchNo)
                    ->where('deleted_st', false)
                    ->first();

                $item->sisa_stok = $stokBatch ? (float) $stokBatch->sisa_qty : (float) $item->qty_kg;
            } else {
                $item->sisa_stok = (float) $item->qty_kg;
            }
        }
    }

    /**
     * Penyesuaian Biaya Utilitas Bulanan (Listrik, Air & Gas CNG).
     * Memperbarui seluruh transaksi produksi pada bulan & tahun tertentu,
     * lalu menghitung ulang total biaya produksi & HPP/kg secara presisi.
     */
    public function adjustMonthlyUtilities(int $year, int $month, array $payload, string $userId): array
    {
        return DB::transaction(function () use ($year, $month, $payload, $userId) {
            $records = DatProduksiHdr::whereYear('produksi_tgl', $year)
                ->whereMonth('produksi_tgl', $month)
                ->where('deleted_st', false)
                ->get();

            if ($records->isEmpty()) {
                throw new Exception("Tidak ada catatan produksi pada periode bulan " . $month . " tahun " . $year . " untuk disesuaikan.");
            }

            $count = $records->count();
            $adjustListrik = !empty($payload['adjust_listrik']);
            $adjustCng = !empty($payload['adjust_cng']);

            $totalListrik = (float) ($payload['total_listrik_air'] ?? 0);
            $modeListrik = $payload['mode_alokasi_listrik'] ?? 'tarif_per_kg';
            $listrikTarifPerKg = (float) ($payload['listrik_tarif_per_kg'] ?? 223.80);

            $modeCng = $payload['mode_cng'] ?? 'update_tarif';
            $cngTarifBaru = (float) ($payload['cng_tarif_baru'] ?? 0);
            $totalCngTagihan = (float) ($payload['total_cng_tagihan'] ?? 0);

            $totalWipBulanIni = (float) $records->sum('total_wip_qty');
            $totalMmbtuBulanIni = (float) $records->sum('cng_mmbtu');

            // Hitung tarif rata-rata riil CNG jika mode total tagihan
            $effectiveCngTarif = 0;
            if ($adjustCng && $modeCng === 'total_tagihan') {
                if ($totalMmbtuBulanIni > 0) {
                    $effectiveCngTarif = round($totalCngTagihan / $totalMmbtuBulanIni, 2);
                } else {
                    $effectiveCngTarif = 0;
                }
            }

            foreach ($records as $record) {
                // 1. Alokasi Listrik & Air
                if ($adjustListrik) {
                    if ($modeListrik === 'tarif_per_kg' && $listrikTarifPerKg > 0) {
                        $record->listrik_air_telp_nilai = round($record->total_wip_qty * $listrikTarifPerKg, 2);
                    } elseif ($modeListrik === 'total_tagihan' || $modeListrik === 'proporsional_wip') {
                        $record->listrik_air_telp_nilai = $totalWipBulanIni > 0
                            ? round($totalListrik * ($record->total_wip_qty / $totalWipBulanIni), 2)
                            : round($totalListrik / $count, 2);
                    } elseif ($modeListrik === 'bagi_rata') {
                        $record->listrik_air_telp_nilai = round($totalListrik / $count, 2);
                    }
                }

                // 2. Alokasi Gas CNG
                if ($adjustCng) {
                    if (!empty($payload['cng_tarif_usd'])) {
                        $record->cng_tarif_usd = (float) $payload['cng_tarif_usd'];
                    }
                    if (!empty($payload['cng_kurs_jisdor'])) {
                        $record->cng_kurs_jisdor = (float) $payload['cng_kurs_jisdor'];
                    }

                    if ($modeCng === 'total_tagihan') {
                        if ($totalMmbtuBulanIni > 0) {
                            $record->cng_tarif = $effectiveCngTarif;
                            $record->cng_nilai = round($record->cng_mmbtu * $effectiveCngTarif, 2);
                        } else {
                            $record->cng_nilai = $totalWipBulanIni > 0
                                ? round($totalCngTagihan * ($record->total_wip_qty / $totalWipBulanIni), 2)
                                : round($totalCngTagihan / $count, 2);
                            if ($record->cng_mmbtu > 0) {
                                $record->cng_tarif = round($record->cng_nilai / $record->cng_mmbtu, 2);
                            }
                        }
                    } elseif ($modeCng === 'update_tarif' && $cngTarifBaru > 0) {
                        $record->cng_tarif = $cngTarifBaru;
                        $record->cng_nilai = round($record->cng_mmbtu * $cngTarifBaru, 2);
                    } elseif ($modeCng === 'proporsional_wip') {
                        $record->cng_nilai = $totalWipBulanIni > 0
                            ? round($totalCngTagihan * ($record->total_wip_qty / $totalWipBulanIni), 2)
                            : round($totalCngTagihan / $count, 2);
                        if ($record->cng_mmbtu > 0) {
                            $record->cng_tarif = round($record->cng_nilai / $record->cng_mmbtu, 2);
                        }
                    } elseif ($modeCng === 'bagi_rata') {
                        $record->cng_nilai = round($totalCngTagihan / $count, 2);
                        if ($record->cng_mmbtu > 0) {
                            $record->cng_tarif = round($record->cng_nilai / $record->cng_mmbtu, 2);
                        }
                    }
                }

                // 3. Rekalkulasi Biaya Overhead Pabrik
                $record->total_overhead_nilai = (float) $record->fotocopy_nilai +
                    (float) $record->sarung_tangan_plastik_nilai +
                    (float) $record->sarung_tangan_kain_nilai +
                    (float) $record->qc_pengawasan_nilai +
                    (float) $record->listrik_air_telp_nilai +
                    (float) $record->pemeliharaan_mesin_nilai +
                    (float) $record->penyusutan_mesin_nilai +
                    (float) $record->limbah_padat_nilai +
                    (float) $record->limbah_kimia_nilai;

                // 4. Rekalkulasi Total Biaya Produksi (Bahan + CNG + TK + FOH)
                $record->total_biaya_produksi = (float) $record->total_bahan_nilai +
                    (float) $record->cng_nilai +
                    (float) $record->tk_total_nilai +
                    (float) $record->total_overhead_nilai;

                // 5. Rekalkulasi HPP per Kg
                $effectiveWip = (float) $record->total_wip_qty;
                if ($effectiveWip <= 0) {
                    $detailWip = (float) DatProduksiDtl::where('produksi_id', $record->produksi_id)->where('deleted_st', false)->sum('qty_kg');
                    if ($detailWip > 0) {
                        $effectiveWip = $detailWip;
                        $record->total_wip_qty = $detailWip;
                    }
                }
                $newHppPerKg = $effectiveWip > 0
                    ? round($record->total_biaya_produksi / $effectiveWip, 2)
                    : 0;
                $record->hpp_per_kg = $newHppPerKg;

                // 6. Sinkronisasi Otomatis ke Detail Output Produksi & Saldo Batch Gudang
                $outputDetails = DatProduksiDtl::where('produksi_id', $record->produksi_id)
                    ->where('deleted_st', false)
                    ->get();

                foreach ($outputDetails as $outDtl) {
                    $outDtl->hpp_satuan = $newHppPerKg;
                    $outDtl->total_nilai = round($outDtl->qty_hasil * $newHppPerKg, 2);
                    $outDtl->save();

                    // Perbarui harga satuan di DatStokBatch gudang jika batch cocok
                    if (!empty($outDtl->batch_no)) {
                        DatStokBatch::where('barang_id', $outDtl->barang_id)
                            ->where('batch_no', $outDtl->batch_no)
                            ->where('deleted_st', false)
                            ->update([
                                'harga_satuan' => $newHppPerKg,
                                'updated_by'   => $userId,
                            ]);
                    }
                }

                // 7. Audit Trail
                $record->updated_by = $userId;
                $record->save();
            }

            return [
                'count'               => $count,
                'adjust_listrik'      => $adjustListrik,
                'total_listrik'       => $totalListrik,
                'adjust_cng'          => $adjustCng,
                'effective_cng_tarif' => $modeCng === 'total_tagihan' ? $effectiveCngTarif : $cngTarifBaru,
            ];
        });
    }

    /**
     * Penyesuaian Pemakaian Gas CNG Harian & Balancing Bulanan.
     * Mengoreksi MMBTU dan biaya gas per tanggal produksi spesifik
     * dengan mengunci Kurs JISDOR masing-masing tanggal tersebut,
     * lalu memperbarui HPP/kg dan audit trail akuntansi secara presisi.
     */
    public function adjustDailyCng(int $year, int $month, array $dailyAdjustments, string $alasan, string $userId): array
    {
        return DB::transaction(function () use ($year, $month, $dailyAdjustments, $alasan, $userId) {
            $updatedDaysCount = 0;
            $updatedRecordsCount = 0;
            $totalSelisihBiaya = 0;
            $totalSelisihMmbtu = 0;

            if (empty($dailyAdjustments) || !is_array($dailyAdjustments)) {
                throw new Exception("Tidak ada data penyesuaian gas harian yang dikirimkan.");
            }

            $alasanClean = trim($alasan);
            if (empty($alasanClean)) {
                $alasanClean = "Penyesuaian & rekonsiliasi gas CNG harian periode {$month}/{$year}";
            }

            $authUser = Auth::user();

            foreach ($dailyAdjustments as $dateStr => $adj) {
                if (!isset($adj['new_mmbtu']) && !isset($adj['new_nilai'])) {
                    continue;
                }

                $newMmbtu = isset($adj['new_mmbtu']) ? round((float) $adj['new_mmbtu'], 4) : 0;
                $oldMmbtu = isset($adj['old_mmbtu']) ? round((float) $adj['old_mmbtu'], 4) : null;
                $diffMmbtu = $oldMmbtu !== null ? ($newMmbtu - $oldMmbtu) : 0;

                $newNilai = isset($adj['new_nilai']) ? round((float) $adj['new_nilai'], 2) : null;
                $oldNilai = isset($adj['old_nilai']) ? round((float) $adj['old_nilai'], 2) : null;
                $diffNilai = ($newNilai !== null && $oldNilai !== null) ? ($newNilai - $oldNilai) : 0;

                // Ambil semua lembar produksi aktif pada tanggal ini
                $records = DatProduksiHdr::whereDate('produksi_tgl', $dateStr)
                    ->where('deleted_st', false)
                    ->orderBy('shift_cd', 'asc')
                    ->get();

                if ($records->isEmpty()) {
                    continue;
                }

                $dayOldMmbtu = (float) $records->sum('cng_mmbtu');
                if ($oldMmbtu === null) {
                    $diffMmbtu = $newMmbtu - $dayOldMmbtu;
                }

                $dayOldNilai = (float) $records->sum('cng_nilai');
                if ($oldNilai === null && $newNilai !== null) {
                    $diffNilai = $newNilai - $dayOldNilai;
                }

                // Jika tidak ada perubahan kuantitas MMBTU maupun nilai Rupiah, lewati
                if (abs($diffMmbtu) < 0.0001 && abs($diffNilai) < 1 && empty($adj['force_update'])) {
                    continue;
                }

                $updatedDaysCount++;
                $totalSelisihMmbtu += $diffMmbtu;

                // Tentukan Kurs JISDOR & Tarif USD untuk tanggal ini
                $firstRec = $records->first();
                $cngKursJisdor = (float) ($firstRec->cng_kurs_jisdor > 0 ? $firstRec->cng_kurs_jisdor : 0);
                if ($cngKursJisdor <= 0) {
                    $cngKursJisdor = (float) $this->tarifService->getJisdorRate($dateStr, false);
                    if ($cngKursJisdor <= 0) {
                        $cngKursJisdor = 18138.00;
                    }
                }
                $cngTarifUsd = (float) ($firstRec->cng_tarif_usd > 0 ? $firstRec->cng_tarif_usd : 14.50);
                if (!empty($adj['cng_tarif_usd']) && (float) $adj['cng_tarif_usd'] > 0) {
                    $cngTarifUsd = (float) $adj['cng_tarif_usd'];
                }
                if (!empty($adj['cng_kurs_jisdor']) && (float) $adj['cng_kurs_jisdor'] > 0) {
                    $cngKursJisdor = (float) $adj['cng_kurs_jisdor'];
                }

                $cngTarifRp = round($cngTarifUsd * $cngKursJisdor, 2);

                $dayTotalWip = (float) $records->sum('total_wip_qty');
                $recordCount = $records->count();

                // Alokasikan ke masing-masing dokumen produksi pada tanggal tersebut
                foreach ($records as $record) {
                    $recOldBiaya = (float) $record->total_biaya_produksi;
                    $recOldHpp = (float) $record->hpp_per_kg;
                    $recOldMmbtu = (float) $record->cng_mmbtu;
                    $recOldCngNilai = (float) $record->cng_nilai;

                    // Alokasi proporsional jika multiple records di hari yang sama
                    $recWip = (float) $record->total_wip_qty;
                    $proportion = ($recordCount === 1) ? 1.0 : ($dayTotalWip > 0 ? ($recWip / $dayTotalWip) : (1 / $recordCount));

                    if ($newNilai !== null && abs($diffNilai) >= 1) {
                        $recNewCngNilai = round($newNilai * $proportion, 2);
                        $recNewMmbtu = $cngTarifRp > 0 ? round($recNewCngNilai / $cngTarifRp, 4) : round($newMmbtu * $proportion, 4);
                    } else {
                        $recNewMmbtu = ($recordCount === 1) ? $newMmbtu : round($newMmbtu * $proportion, 4);
                        $recNewCngNilai = round($recNewMmbtu * $cngTarifRp, 2);
                    }

                    $diffCngNilai = $recNewCngNilai - $recOldCngNilai;
                    $totalSelisihBiaya += $diffCngNilai;

                    // Update field Gas CNG di Header Produksi
                    $record->cng_mmbtu = $recNewMmbtu;
                    $record->cng_nm3 = round($recNewMmbtu * 27.921, 4);
                    $record->cng_tarif_usd = $cngTarifUsd;
                    $record->cng_kurs_jisdor = $cngKursJisdor;
                    $record->cng_tarif = $cngTarifRp;
                    $record->cng_nilai = $recNewCngNilai;

                    // Rekalkulasi Total Biaya Produksi: Bahan + CNG + TK + FOH
                    $recNewTotalBiaya = round(
                        (float) $record->total_bahan_nilai +
                        $recNewCngNilai +
                        (float) $record->tk_total_nilai +
                        (float) $record->total_overhead_nilai,
                        2
                    );
                    $record->total_biaya_produksi = $recNewTotalBiaya;

                    // Rekalkulasi HPP per Kg
                    $effectiveWip = (float) $record->total_wip_qty;
                    if ($effectiveWip <= 0 && $record->outputs && $record->outputs->isNotEmpty()) {
                        $effectiveWip = (float) $record->outputs->sum('qty_kg');
                    }
                    $recNewHpp = $effectiveWip > 0 ? round($recNewTotalBiaya / $effectiveWip, 2) : 0;
                    $record->hpp_per_kg = $recNewHpp;

                    $record->version_no = ((int) $record->version_no) + 1;
                    $record->updated_by = $userId;
                    $record->save();

                    // Sinkronisasi ke Output WIP (DatProduksiDtl)
                    $outputDetails = DatProduksiDtl::where('produksi_id', $record->produksi_id)
                        ->where('deleted_st', false)
                        ->get();
                    foreach ($outputDetails as $outDtl) {
                        $outDtl->hpp_satuan = $recNewHpp;
                        $outDtl->total_nilai = round($outDtl->qty_hasil * $recNewHpp, 2);
                        $outDtl->version_no = ((int) $outDtl->version_no) + 1;
                        $outDtl->updated_by = $userId;
                        $outDtl->save();
                    }

                    // Sinkronisasi ke Saldo Batch Gudang
                    if (!empty($record->batch_wip_no)) {
                        $stokBatch = DatStokBatch::where('batch_no', $record->batch_wip_no)
                            ->where('deleted_st', false)
                            ->first();
                        if ($stokBatch) {
                            $stokBatch->harga_satuan = $recNewHpp;
                            $stokBatch->total_nilai = round($stokBatch->sisa_qty * $recNewHpp, 2);
                            $stokBatch->updated_by = $userId;
                            $stokBatch->save();
                        }
                    }

                    // Buat Log Audit Trail Penyesuaian
                    $adjNo = $this->codeGenerator->generateBeritaAcaraHppNo();
                    DatProduksiAdjustmentLog::create([
                        'adj_no'               => $adjNo,
                        'produksi_id'          => $record->produksi_id,
                        'user_id'              => $authUser?->id,
                        'adj_tgl'              => now()->format('Y-m-d'),
                        'kategori_komponen'    => 'ENERGI',
                        'role_cd'              => $authUser?->role_cd ?? 'SUPERADMIN',
                        'total_biaya_sebelum'  => $recOldBiaya,
                        'total_biaya_sesudah'  => $recNewTotalBiaya,
                        'selisih_biaya'        => $diffCngNilai,
                        'hpp_sebelum'          => $recOldHpp,
                        'hpp_sesudah'          => $recNewHpp,
                        'selisih_hpp'          => $recNewHpp - $recOldHpp,
                        'alasan_txt'           => $alasanClean . (!empty($adj['catatan']) ? " (" . trim($adj['catatan']) . ")" : ""),
                        'perubahan_field_json' => [
                            'cng_mmbtu' => [
                                'label' => 'Konsumsi Gas Alam (CNG)',
                                'old'   => $recOldMmbtu,
                                'new'   => $recNewMmbtu,
                                'unit'  => 'MMBTU',
                                'type'  => 'QUANTITY',
                                'group' => 'ENERGI',
                            ],
                            'cng_nilai' => [
                                'label' => 'Biaya Gas Alam (CNG)',
                                'old'   => $recOldCngNilai,
                                'new'   => $recNewCngNilai,
                                'type'  => 'NOMINAL',
                                'group' => 'ENERGI',
                            ],
                            'cng_kurs_jisdor' => [
                                'label' => 'Kurs JISDOR BI Tanggal ' . date('d/m/Y', strtotime($dateStr)),
                                'old'   => (float) $record->cng_kurs_jisdor,
                                'new'   => $cngKursJisdor,
                                'type'  => 'RATE',
                                'group' => 'ENERGI',
                            ],
                        ],
                        'created_by'           => $userId,
                        'updated_by'           => $userId,
                        'deleted_st'           => false,
                        'active_st'            => true,
                        'version_no'           => 1,
                    ]);

                    $updatedRecordsCount++;
                }
            }

            return [
                'updated_days'    => $updatedDaysCount,
                'updated_records' => $updatedRecordsCount,
                'selisih_mmbtu'   => round($totalSelisihMmbtu, 4),
                'selisih_biaya'   => round($totalSelisihBiaya, 2),
            ];
        });
    }

    /**
     * Penyesuaian / Koreksi Biaya Harian Dokumen Tertentu (Audit Trail HPP Standar Akuntansi).
     * Menerapkan prinsip akuntansi: mencatat log berita acara, menghitung selisih,
     * mengupdate lembar kerja, dan mensinkronkan nilai HPP pada kartu stok gudang.
     */
    public function adjustSingleProductionHpp(int $produksiId, array $data, string $userId): DatProduksiAdjustmentLog
    {
        return DB::transaction(function () use ($produksiId, $data, $userId) {
            $produksi = DatProduksiHdr::with(['outputs.barang'])->findOrFail($produksiId);

            $oldTotalBiaya = (float) $produksi->total_biaya_produksi;
            $oldHppPerKg = (float) $produksi->hpp_per_kg;
            $oldTotalWip = (float) $produksi->total_wip_qty;
            $oldSingkongQty = (float) $produksi->singkong_qty;
            $oldRendemen = (float) $produksi->rendemen_persen;

            $alasan = trim($data['alasan_txt'] ?? '');
            if (strlen($alasan) < 5) {
                throw new Exception("Alasan adjustment / dasar dokumen wajib diisi minimal 5 karakter untuk keperluan audit trail akuntansi.");
            }

            // Catat snapshot field lama vs baru
            $changedFields = [];
            $kategoriList = [];

            // 1. KOREKSI KUANTITAS OUTPUT HASIL PRODUKSI (WIP)
            $newTotalWip = $oldTotalWip;
            $hasOutputAdjustment = false;
            if (!empty($data['output_items']) && is_array($data['output_items'])) {
                $accumulatedWip = 0;
                foreach ($produksi->outputs as $out) {
                    $outId = $out->output_id;
                    if (isset($data['output_items'][$outId]['qty_kg'])) {
                        $newQtyKg = round((float) $data['output_items'][$outId]['qty_kg'], 4);
                        $oldQtyKg = round((float) $out->qty_kg, 4);
                        $accumulatedWip += $newQtyKg;

                        if (abs($newQtyKg - $oldQtyKg) > 0.0001) {
                            $barangNm = $out->barang?->barang_nm ?? ($out->kategori_output ?? "Output #{$outId}");
                            $changedFields["output_item_{$outId}"] = [
                                'label' => "Output {$barangNm}",
                                'old'   => $oldQtyKg,
                                'new'   => $newQtyKg,
                                'unit'  => 'Kg',
                                'type'  => 'QUANTITY',
                                'group' => 'OUTPUT',
                            ];
                            $kategoriList[] = 'OUTPUT';
                            $hasOutputAdjustment = true;

                            // Update kuantitas output pada baris
                            $diffQty = $newQtyKg - $oldQtyKg;
                            $out->qty_kg = $newQtyKg;
                            $out->qty_hasil = $newQtyKg;
                            $out->version_no = ((int) $out->version_no) + 1;
                            $out->updated_by = $userId;
                            $out->save();

                            // Perbarui kuantitas fisik batch stok gudang jika berstatus POSTED
                            if (!empty($out->batch_no)) {
                                $stokBatch = DatStokBatch::where('barang_id', $out->barang_id)
                                    ->where('batch_no', $out->batch_no)
                                    ->where('deleted_st', false)
                                    ->first();
                                if ($stokBatch) {
                                    $stokBatch->masuk_qty = (float) $stokBatch->masuk_qty + $diffQty;
                                    $stokBatch->sisa_qty = max(0, (float) $stokBatch->sisa_qty + $diffQty);
                                    $stokBatch->updated_by = $userId;
                                    $stokBatch->save();
                                }
                            }
                        }
                    } else {
                        $accumulatedWip += (float) $out->qty_kg;
                    }
                }
                if ($hasOutputAdjustment || $accumulatedWip > 0) {
                    $newTotalWip = $accumulatedWip;
                }
            } elseif (isset($data['total_wip_qty']) && $data['total_wip_qty'] !== '') {
                $inputTotalWip = round((float) $data['total_wip_qty'], 4);
                if (abs($inputTotalWip - $oldTotalWip) > 0.0001) {
                    $changedFields['total_wip_qty'] = [
                        'label' => 'Total Output Hasil WIP',
                        'old'   => $oldTotalWip,
                        'new'   => $inputTotalWip,
                        'unit'  => 'Kg',
                        'type'  => 'QUANTITY',
                        'group' => 'OUTPUT',
                    ];
                    $kategoriList[] = 'OUTPUT';
                    $newTotalWip = $inputTotalWip;

                    // Jika output tunggal, sinkronkan langsung ke output item
                    if ($produksi->outputs && $produksi->outputs->count() === 1) {
                        $singleOut = $produksi->outputs->first();
                        $diffQty = $newTotalWip - (float) $singleOut->qty_kg;
                        $singleOut->qty_kg = $newTotalWip;
                        $singleOut->qty_hasil = $newTotalWip;
                        $singleOut->version_no = ((int) $singleOut->version_no) + 1;
                        $singleOut->updated_by = $userId;
                        $singleOut->save();

                        if (!empty($singleOut->batch_no)) {
                            $stokBatch = DatStokBatch::where('barang_id', $singleOut->barang_id)
                                ->where('batch_no', $singleOut->batch_no)
                                ->where('deleted_st', false)
                                ->first();
                            if ($stokBatch) {
                                $stokBatch->masuk_qty = (float) $stokBatch->masuk_qty + $diffQty;
                                $stokBatch->sisa_qty = max(0, (float) $stokBatch->sisa_qty + $diffQty);
                                $stokBatch->updated_by = $userId;
                                $stokBatch->save();
                            }
                        }
                    }
                }
            }
            $produksi->total_wip_qty = $newTotalWip;

            // 2. KOREKSI KUANTITAS KOMPONEN BIAYA INPUT (Singkong, Minyak, Bumbu, Kemasan, Energi, Tenaga Kerja, APD)
            $qtyDefinitions = [
                // BBL
                'singkong_qty' => [
                    'label' => 'Kuantitas Singkong Mentah Basah',
                    'unit'  => 'Kg',
                    'group' => 'BBL',
                    'precision' => 4,
                ],
                'minyak_qty' => [
                    'label' => 'Kuantitas Minyak Goreng Terpakai',
                    'unit'  => 'Kg',
                    'group' => 'BBL',
                    'precision' => 4,
                    'target_column' => 'minyak_sawit_qty',
                    'old_val_fn' => function($prod) {
                        return (float) ($prod->minyak_sawit_qty + $prod->minyak_kelapa_qty);
                    }
                ],
                'bumbu_qty' => [
                    'label' => 'Kuantitas Bumbu & Perenyah',
                    'unit'  => 'Kg',
                    'group' => 'BBL',
                    'precision' => 4,
                ],

                // Kemasan & Packing
                'karton_baru_qty' => [
                    'label' => 'Kuantitas Karton Box Baru',
                    'unit'  => 'Pcs Box',
                    'group' => 'KEMASAN',
                    'precision' => 2,
                ],
                'karton_bekas_qty' => [
                    'label' => 'Kuantitas Karton Box Bekas',
                    'unit'  => 'Pcs Box',
                    'group' => 'KEMASAN',
                    'precision' => 2,
                ],
                'plastik_hd_qty' => [
                    'label' => 'Kuantitas Plastik HD / Inner Bag',
                    'unit'  => 'Roll / Kg',
                    'group' => 'KEMASAN',
                    'precision' => 2,
                ],
                'lakban_besar_qty' => [
                    'label' => 'Kuantitas Lakban Coklat Besar',
                    'unit'  => 'Roll',
                    'group' => 'KEMASAN',
                    'precision' => 2,
                ],
                'lakban_kecil_qty' => [
                    'label' => 'Kuantitas Lakban Bening Kecil',
                    'unit'  => 'Roll',
                    'group' => 'KEMASAN',
                    'precision' => 2,
                ],
                'tali_rafia_qty' => [
                    'label' => 'Kuantitas Tali Rafia / Strapping',
                    'unit'  => 'Roll',
                    'group' => 'KEMASAN',
                    'precision' => 2,
                ],

                // Tenaga Kerja & Energi
                'cng_mmbtu' => [
                    'label' => 'Konsumsi Gas Alam (CNG)',
                    'unit'  => 'MMBTU',
                    'group' => 'ENERGI',
                    'precision' => 4,
                ],
                'tk_jumlah_org' => [
                    'label' => 'Jumlah Tenaga Kerja Hadir',
                    'unit'  => 'Orang',
                    'group' => 'BTKL',
                    'precision' => 0,
                ],

                // APD Overhead (FOH)
                'sarung_tangan_plastik_qty' => [
                    'label' => 'Kuantitas Sarung Tangan Plastik',
                    'unit'  => 'Pcs / Box',
                    'group' => 'FOH',
                    'precision' => 2,
                ],
                'sarung_tangan_kain_qty' => [
                    'label' => 'Kuantitas Sarung Tangan Kain',
                    'unit'  => 'Pasang',
                    'group' => 'FOH',
                    'precision' => 2,
                ],
            ];

            foreach ($qtyDefinitions as $qKey => $qDef) {
                if (isset($data[$qKey]) && $data[$qKey] !== '') {
                    $newQty = round((float) $data[$qKey], $qDef['precision'] ?? 2);
                    $oldQty = isset($qDef['old_val_fn'])
                        ? round((float) $qDef['old_val_fn']($produksi), $qDef['precision'] ?? 2)
                        : round((float) ($produksi->$qKey ?? 0), $qDef['precision'] ?? 2);

                    if (abs($newQty - $oldQty) > 0.0001) {
                        $changedFields[$qKey] = [
                            'label' => $qDef['label'],
                            'old'   => $oldQty,
                            'new'   => $newQty,
                            'unit'  => $qDef['unit'],
                            'type'  => 'QUANTITY',
                            'group' => $qDef['group'],
                        ];
                        $targetCol = $qDef['target_column'] ?? $qKey;
                        $produksi->$targetCol = $newQty;
                        if ($qKey === 'tk_jumlah_org') {
                            $produksi->tk_langsung_org = (int) $newQty;
                        }
                        $kategoriList[] = $qDef['group'];
                    }
                }
            }

            if (isset($data['cng_tarif_usd']) && $data['cng_tarif_usd'] !== '') {
                $produksi->cng_tarif_usd = (float) $data['cng_tarif_usd'];
            }
            if (isset($data['cng_kurs_jisdor']) && $data['cng_kurs_jisdor'] !== '') {
                $produksi->cng_kurs_jisdor = (float) $data['cng_kurs_jisdor'];
            }
            if (isset($data['cng_tarif']) && $data['cng_tarif'] !== '') {
                $produksi->cng_tarif = (float) $data['cng_tarif'];
            }

            // Penanganan Alokasi Tambahan Stok Fisik Gudang dari Batch Lain (Universal Multi-Barang)
            $additionalBatches = $data['additional_batches'] ?? [];
            if (!empty($data['singkong_stok_action']) && $data['singkong_stok_action'] === 'deduct_batch' && empty($additionalBatches['singkong'])) {
                $additionalBatches['singkong'] = [
                    'batch_no' => trim($data['additional_batch_no'] ?? ''),
                    'qty'      => (float) ($data['additional_batch_qty'] ?? 0),
                ];
            }

            $componentLabels = [
                'singkong'     => ['label' => 'Singkong Mentah Basah', 'group' => 'BBL', 'unit' => 'Kg'],
                'minyak'       => ['label' => 'Minyak Goreng', 'group' => 'BBL', 'unit' => 'Kg'],
                'bumbu'        => ['label' => 'Bumbu Masak & Perenyah', 'group' => 'BBL', 'unit' => 'Kg'],
                'karton_baru'  => ['label' => 'Karton Box Baru', 'group' => 'KEMASAN', 'unit' => 'Pcs'],
                'karton_bekas' => ['label' => 'Karton Box Bekas', 'group' => 'KEMASAN', 'unit' => 'Pcs'],
                'plastik_hd'   => ['label' => 'Plastik HD / Inner Bag', 'group' => 'KEMASAN', 'unit' => 'Roll'],
                'lakban_besar' => ['label' => 'Lakban Besar', 'group' => 'KEMASAN', 'unit' => 'Roll'],
                'lakban_kecil' => ['label' => 'Lakban Kecil', 'group' => 'KEMASAN', 'unit' => 'Roll'],
                'tali_rafia'   => ['label' => 'Tali Rafia / Strapping', 'group' => 'KEMASAN', 'unit' => 'Roll'],
            ];

            foreach ($additionalBatches as $cKey => $bInfo) {
                $bNo = trim($bInfo['batch_no'] ?? '');
                $bQty = round((float) ($bInfo['qty'] ?? 0), 4);

                if (!empty($bNo) && $bQty > 0) {
                    $stokBatch = DatStokBatch::where('batch_no', $bNo)
                        ->where('deleted_st', false)
                        ->first();

                    $cDef = $componentLabels[$cKey] ?? ['label' => $cKey, 'group' => 'BAHAN', 'unit' => 'Unit'];

                    if (!$stokBatch) {
                        throw new Exception("Nomor batch gudang '{$bNo}' untuk {$cDef['label']} tidak ditemukan di sistem.");
                    }

                    if ((float) $stokBatch->sisa_qty < $bQty) {
                        throw new Exception("Sisa stok gudang untuk {$cDef['label']} (Batch '{$bNo}') tidak mencukupi (Tersedia: " . number_format($stokBatch->sisa_qty, 2) . " {$cDef['unit']}, Diminta: " . number_format($bQty, 2) . " {$cDef['unit']}).");
                    }

                    // Potong stok fisik gudang secara resmi
                    $this->stokService->deductStock(
                        (int) $stokBatch->gudang_id,
                        (int) $stokBatch->barang_id,
                        $bNo,
                        $bQty,
                        $produksi->produksi_no,
                        "Pengambilan Tambahan {$cDef['label']} Produksi: " . $alasan
                    );

                    $changedFields["{$cKey}_additional_batch"] = [
                        'label' => "Potong Stok Gudang ({$cDef['label']})",
                        'old'   => '-',
                        'new'   => "Batch: {$bNo} (-" . number_format($bQty, 2) . " {$cDef['unit']})",
                        'type'  => 'STOCK_DEDUCTION',
                        'group' => $cDef['group']
                    ];
                    $kategoriList[] = $cDef['group'];
                }
            }

            // 3. KOREKSI 20 KOMPONEN NILAI NOMINAL
            $fieldDefinitions = [
                // 1. Biaya Bahan Baku Langsung (BBL)
                'singkong_nilai'              => ['label' => 'Bahan Baku Singkong Mentah', 'group' => 'BBL'],
                'minyak_nilai'                => ['label' => 'Minyak Goreng (Sawit & Kelapa)', 'group' => 'BBL'],
                'bumbu_nilai'                 => ['label' => 'Bumbu Masak & Perenyah', 'group' => 'BBL'],

                // 2. Biaya Kemasan & Pembantu
                'karton_baru_nilai'           => ['label' => 'Karton Box Baru', 'group' => 'KEMASAN'],
                'karton_bekas_nilai'          => ['label' => 'Karton Box Bekas', 'group' => 'KEMASAN'],
                'plastik_hd_nilai'            => ['label' => 'Plastik HD / Inner Bag', 'group' => 'KEMASAN'],
                'lakban_besar_nilai'          => ['label' => 'Lakban Coklat Besar', 'group' => 'KEMASAN'],
                'lakban_kecil_nilai'          => ['label' => 'Lakban Kecil', 'group' => 'KEMASAN'],
                'tali_rafia_nilai'            => ['label' => 'Tali Rafia / Strapping Band', 'group' => 'KEMASAN'],

                // 3. Tenaga Kerja Langsung (BTKL)
                'tk_total_nilai'              => ['label' => 'Upah Tenaga Kerja Langsung (BTKL)', 'group' => 'BTKL'],

                // 4. Biaya Utilitas & Energi
                'cng_nilai'                   => ['label' => 'Bahan Bakar Gas Alam (CNG)', 'group' => 'ENERGI'],
                'listrik_air_telp_nilai'      => ['label' => 'Listrik PLN, Air & Utilitas', 'group' => 'UTILITAS'],

                // 5. Biaya Overhead Pabrik (FOH / BOP)
                'pemeliharaan_mesin_nilai'    => ['label' => 'Pemeliharaan Mesin & Genset', 'group' => 'FOH'],
                'qc_pengawasan_nilai'         => ['label' => 'Pengawasan Mutu (QC)', 'group' => 'FOH'],
                'penyusutan_mesin_nilai'      => ['label' => 'Penyusutan Mesin Pabrik', 'group' => 'FOH'],
                'sarung_tangan_plastik_nilai' => ['label' => 'Sarung Tangan Plastik Operator', 'group' => 'FOH'],
                'sarung_tangan_kain_nilai'    => ['label' => 'Sarung Tangan Kain Operator', 'group' => 'FOH'],
                'fotocopy_nilai'              => ['label' => 'Dokumen & ATK Produksi', 'group' => 'FOH'],
                'limbah_padat_nilai'          => ['label' => 'Pengolahan Limbah Padat', 'group' => 'FOH'],
                'limbah_kimia_nilai'          => ['label' => 'Pengolahan Limbah Kimia Pabrik', 'group' => 'FOH'],
            ];

            // Deteksi perubahan pada seluruh komponen nominal
            foreach ($fieldDefinitions as $fieldKey => $fDef) {
                if (isset($data[$fieldKey])) {
                    $newVal = round((float) $data[$fieldKey], 2);
                    $oldVal = round((float) ($produksi->$fieldKey ?? 0), 2);

                    if (abs($newVal - $oldVal) > 0.01) {
                        $changedFields[$fieldKey] = [
                            'label' => $fDef['label'],
                            'old'   => $oldVal,
                            'new'   => $newVal,
                            'type'  => 'NOMINAL',
                            'group' => $fDef['group'],
                        ];
                        $produksi->$fieldKey = $newVal;
                        $kategoriList[] = $fDef['group'];
                    }
                }
            }

            if (empty($changedFields)) {
                throw new Exception("Tidak ada perubahan nilai nominal biaya maupun kuantitas yang terdeteksi.");
            }

            // 4. REKALKULASI SUB-TOTAL BIAYA & METRIK MANUFAKTUR
            $produksi->total_bahan_nilai = (float) $produksi->singkong_nilai +
                (float) $produksi->minyak_nilai +
                (float) $produksi->bumbu_nilai +
                (float) $produksi->karton_baru_nilai +
                (float) $produksi->karton_bekas_nilai +
                (float) $produksi->plastik_hd_nilai +
                (float) $produksi->lakban_besar_nilai +
                (float) $produksi->lakban_kecil_nilai +
                (float) $produksi->tali_rafia_nilai;

            $produksi->total_overhead_nilai = (float) $produksi->fotocopy_nilai +
                (float) $produksi->sarung_tangan_plastik_nilai +
                (float) $produksi->sarung_tangan_kain_nilai +
                (float) $produksi->qc_pengawasan_nilai +
                (float) $produksi->listrik_air_telp_nilai +
                (float) $produksi->pemeliharaan_mesin_nilai +
                (float) $produksi->penyusutan_mesin_nilai +
                (float) $produksi->limbah_padat_nilai +
                (float) $produksi->limbah_kimia_nilai;

            // Total Biaya Produksi: Bahan + CNG + TK + FOH
            $newTotalBiaya = round(
                (float) $produksi->total_bahan_nilai +
                (float) $produksi->cng_nilai +
                (float) $produksi->tk_total_nilai +
                (float) $produksi->total_overhead_nilai,
                2
            );
            $produksi->total_biaya_produksi = $newTotalBiaya;
            $selisihBiaya = $newTotalBiaya - $oldTotalBiaya;

            // Rekalkulasi Rendemen Singkong (%) = (Total WIP Qty / Singkong Qty) * 100%
            $effectiveSingkong = (float) $produksi->singkong_qty;
            $produksi->rendemen_persen = $effectiveSingkong > 0 
                ? round(($produksi->total_wip_qty / $effectiveSingkong) * 100, 2) 
                : 0;

            // Rekalkulasi HPP per Kg = Total Biaya / Total WIP Qty
            $effectiveWip = (float) $produksi->total_wip_qty;
            if ($effectiveWip <= 0 && $produksi->outputs && $produksi->outputs->isNotEmpty()) {
                $effectiveWip = (float) $produksi->outputs->sum('qty_kg');
            }
            $newHppPerKg = $effectiveWip > 0 ? round($newTotalBiaya / $effectiveWip, 2) : 0;
            $produksi->hpp_per_kg = $newHppPerKg;
            $selisihHpp = $newHppPerKg - $oldHppPerKg;

            // Update version_no & audit
            $produksi->version_no = ((int) $produksi->version_no) + 1;
            $produksi->updated_by = $userId;
            $produksi->save();

            // Generate Nomor Berita Acara Penyesuaian Standar PT Mirasa: {seq:3}/BA/MFI/{mm}/{yy}
            $adjNo = $this->codeGenerator->generateBeritaAcaraHppNo();

            $kategoriUnique = array_values(array_unique($kategoriList));
            $kategoriStr = count($kategoriUnique) === 1 ? $kategoriUnique[0] : (count($kategoriUnique) > 1 ? 'MULTI' : 'LAINNYA');

            $authUser = auth()->user();
            $logUserId = $authUser?->id;
            $logRoleCd = $authUser?->role_cd ?? 'SUPERADMIN';

            // Simpan ke Log Audit Trail Akuntansi
            $log = DatProduksiAdjustmentLog::create([
                'adj_no'               => $adjNo,
                'produksi_id'          => $produksi->produksi_id,
                'user_id'              => $logUserId,
                'adj_tgl'              => now()->format('Y-m-d'),
                'kategori_komponen'    => $kategoriStr,
                'role_cd'              => $logRoleCd,
                'total_biaya_sebelum'  => $oldTotalBiaya,
                'total_biaya_sesudah'  => $newTotalBiaya,
                'selisih_biaya'        => $selisihBiaya,
                'hpp_sebelum'          => $oldHppPerKg,
                'hpp_sesudah'          => $newHppPerKg,
                'selisih_hpp'          => $selisihHpp,
                'alasan_txt'           => $alasan,
                'perubahan_field_json' => $changedFields,
                'created_by'           => $userId,
                'updated_by'           => $userId,
            ]);

            // Sinkronisasi otomatis HPP satuan ke detail output dan kartu stok gudang
            // Mempertahankan alokasi rasio mutu / by-product costing jika dokumen memiliki produk sampingan (Berko, Lengket, Remahan)
            $outputs = $produksi->outputs;
            $hasDifferentiatedHpp = false;
            if ($outputs && $outputs->count() > 1) {
                $firstHpp = (float) $outputs->first()->hpp_satuan;
                foreach ($outputs as $out) {
                    if (abs((float)$out->hpp_satuan - $firstHpp) > 1.0) {
                        $hasDifferentiatedHpp = true;
                        break;
                    }
                }
            }

            if ($hasDifferentiatedHpp) {
                // Temukan produk utama (bukan berko/lengket/remahan)
                $primaryOut = $outputs->first(function ($o) {
                    $cd = strtoupper($o->barang?->barang_cd ?? $o->kategori_output ?? '');
                    $nm = strtoupper($o->barang?->barang_nm ?? '');
                    return !str_contains($cd, 'BRK') && !str_contains($nm, 'BERKO')
                        && !str_contains($cd, 'LGT') && !str_contains($nm, 'LENGKET')
                        && !str_contains($cd, 'RMH') && !str_contains($nm, 'REMAHAN');
                }) ?? $outputs->first();

                // Hitung total nilai modal produk sampingan yang bernilai tetap
                $otherTotalNilai = 0;
                foreach ($outputs as $out) {
                    if ($out->output_id !== $primaryOut->output_id) {
                        $qty = (float) ($out->qty_hasil > 0 ? $out->qty_hasil : $out->qty_kg);
                        $otherTotalNilai += ($qty * (float) $out->hpp_satuan);
                    }
                }

                // Produk utama otomatis menyerap selisih biaya penyesuaian
                $primaryCost = max(0, $newTotalBiaya - $otherTotalNilai);
                $primaryQty = (float) ($primaryOut->qty_hasil > 0 ? $primaryOut->qty_hasil : $primaryOut->qty_kg);
                $primaryPrice = $primaryQty > 0 ? round($primaryCost / $primaryQty, 2) : 0;

                foreach ($outputs as $out) {
                    if ($out->output_id === $primaryOut->output_id) {
                        $out->hpp_satuan = $primaryPrice;
                        $out->total_nilai = round($primaryQty * $primaryPrice, 2);
                    } else {
                        $qty = (float) ($out->qty_hasil > 0 ? $out->qty_hasil : $out->qty_kg);
                        $out->total_nilai = round($qty * (float) $out->hpp_satuan, 2);
                    }
                    $out->save();

                    if (!empty($out->batch_no)) {
                        DatStokBatch::where('barang_id', $out->barang_id)
                            ->where('batch_no', $out->batch_no)
                            ->where('deleted_st', false)
                            ->update([
                                'harga_satuan' => (float) $out->hpp_satuan,
                                'updated_by'   => $userId,
                            ]);
                    }
                }
            } else {
                // Output tunggal atau pembebanan HPP rata (Equal)
                foreach ($outputs as $out) {
                    $out->hpp_satuan = $newHppPerKg;
                    $out->total_nilai = round((float) $out->qty_hasil * $newHppPerKg, 2);
                    $out->save();

                    if (!empty($out->batch_no)) {
                        DatStokBatch::where('barang_id', $out->barang_id)
                            ->where('batch_no', $out->batch_no)
                            ->where('deleted_st', false)
                            ->update([
                                'harga_satuan' => $newHppPerKg,
                                'updated_by'   => $userId,
                            ]);
                    }
                }
            }

            // Sinkronkan Jurnal Umum jika dokumen HPP WIP sudah pernah diterbitkan
            if ($produksi->jurnal_id) {
                $this->syncJournalHppWip($produksi, $userId);
            }

            return $log;
        });
    }

    /**
     * Menyimpan transaksi Sortir Ulang / Rework KW 2 (Poin 23 & Poin 24).
     * Memotong stok batch asal, menambah stok [WIP-KW2], limbah lengket, remahan,
     * serta mencatat pemulihan kemasan dus sirkular.
     */
    public function storeRework(array $data): DatProduksiHdr
    {
        return DB::transaction(function () use ($data) {
            $tgl = $data['produksi_tgl'] ?? date('Y-m-d');
            $gudangId = (int) $data['gudang_id'];
            $barangId = (int) $data['barang_id'];
            $batchNo = trim($data['batch_no']);
            $qtyRework = (float) $data['qty_rework'];
            $qtyKw2 = (float) $data['qty_kw2'];
            $qtyLengket = (float) ($data['qty_lengket'] ?? 0);
            $qtyRemahan = (float) ($data['qty_remahan'] ?? 0);
            $dusDibongkar = (int) ($data['dus_dibongkar'] ?? 0);
            $dusPuteran = (int) ($data['dus_puteran'] ?? 0);
            $dusRusak = (int) ($data['dus_rusak'] ?? 0);
            $alasanQc = trim($data['alasan_qc']);
            $catatan = trim($data['catatan_txt'] ?? '');

            // 1. Validasi Barang Asal
            $sourceBarang = MstBarang::findOrFail($barangId);

            // 2. Cek Stok Batch Asal
            $sourceBatch = DatStokBatch::where('gudang_id', $gudangId)
                ->where('barang_id', $barangId)
                ->where('batch_no', $batchNo)
                ->first();

            if (!$sourceBatch || (float) $sourceBatch->sisa_qty < $qtyRework) {
                $tersedia = $sourceBatch ? (float) $sourceBatch->sisa_qty : 0;
                throw new Exception("Sisa stok batch {$batchNo} untuk {$sourceBarang->barang_nm} tidak mencukupi (Tersedia: {$tersedia}, Diminta: {$qtyRework}).");
            }

            $hppSatuan = (float) $sourceBatch->harga_satuan;

            // 3. Generate Nomor Dokumen Rework & Nomor Batch Baru Output KW 2
            $docNo = $this->codeGenerator->generateProduksiNo($tgl);
            $outputBatchNo = 'RWK-' . Carbon::parse($tgl)->format('ymd') . '-' . rand(100, 999);
            $expiredDate = Carbon::parse($tgl)->addMonths(6)->toDateString();

            // 4. Potong Stok Barang Asal
            $this->stokService->deductStock(
                $gudangId,
                $barangId,
                $batchNo,
                $qtyRework,
                $docNo,
                "Rework / Sortir Ulang Batch {$batchNo}: {$alasanQc}"
            );

            // 5. Tambah Stok Hasil Sortir (Fallback ke Remahan jika tidak ada WIP khusus)
            $barangKw2 = MstBarang::where('barang_cd', 'WIP-RMH')->first() ?? MstBarang::first();
            if (!$barangKw2) {
                throw new Exception("Master Barang untuk penampungan sortir tidak ditemukan.");
            }

            $this->stokService->addStock(
                $gudangId,
                $barangKw2->barang_id,
                $outputBatchNo,
                $qtyKw2,
                $expiredDate,
                $docNo,
                "Hasil Sortir KW 2 dari batch {$batchNo} ({$sourceBarang->barang_nm})",
                $hppSatuan,
                'KW2'
            );

            // 6. Tambah Stok Afkir Lengket jika ada (Item ID: 76 - WIP-LGT)
            if ($qtyLengket > 0) {
                $barangLgt = MstBarang::where('barang_cd', 'WIP-LGT')->first() ?? MstBarang::find(76);
                if ($barangLgt) {
                    $this->stokService->addStock(
                        $gudangId,
                        $barangLgt->barang_id,
                        $outputBatchNo,
                        $qtyLengket,
                        $expiredDate,
                        $docNo,
                        "Afkir Lengket dari Sortir Ulang batch {$batchNo}",
                        0,
                        'KW2'
                    );
                }
            }

            // 7. Tambah Stok Afkir Remahan jika ada (Item ID: 81 - WIP-RMH)
            if ($qtyRemahan > 0) {
                $barangRmh = MstBarang::where('barang_cd', 'WIP-RMH')->first() ?? MstBarang::find(81);
                if ($barangRmh) {
                    $this->stokService->addStock(
                        $gudangId,
                        $barangRmh->barang_id,
                        $outputBatchNo,
                        $qtyRemahan,
                        $expiredDate,
                        $docNo,
                        "Afkir Remahan dari Sortir Ulang batch {$batchNo}",
                        0,
                        'KW2'
                    );
                }
            }

            // 8. Kemasan Sirkular: Dus Puteran (Item ID: 82) & Afkir Dus (Item ID: 79)
            if ($dusPuteran > 0) {
                $barangPuteran = MstBarang::where('barang_cd', 'KIF00G-PUT')->first() ?? MstBarang::find(82);
                if ($barangPuteran) {
                    $this->stokService->addStock(
                        $gudangId,
                        $barangPuteran->barang_id,
                        'PUT-' . Carbon::parse($tgl)->format('ymd'),
                        $dusPuteran,
                        null,
                        $docNo,
                        "Pengembalian Dus Puteran Layak Pakai dari Rework batch {$batchNo}"
                    );
                }
            }

            if ($dusRusak > 0) {
                $barangDusRusak = MstBarang::where('barang_cd', 'RSK-KTN01')->first() ?? MstBarang::find(79);
                if ($barangDusRusak) {
                    $this->stokService->addStock(
                        $gudangId,
                        $barangDusRusak->barang_id,
                        'REJ-' . Carbon::parse($tgl)->format('ymd'),
                        $dusRusak,
                        null,
                        $docNo,
                        "Afkir Dus Rusak dari Rework batch {$batchNo}"
                    );
                }
            }

            // 9. Simpan Header Produksi Rework
            $totalNilai = round($qtyKw2 * $hppSatuan, 2);
            $userId = Auth::user()->user_id ?? 'SYSTEM';
            $headerData = [
                'produksi_no'           => $docNo,
                'produksi_tgl'          => $tgl,
                'hari_nm'               => $dayNamesIndo[Carbon::parse($tgl)->dayOfWeek] ?? 'Senin',
                'gudang_id'             => $gudangId,
                'lini_produksi'         => 'SORTIR & REWORK KW 2',
                'status_cd'             => 'POSTED',
                'batch_wip_no'          => $outputBatchNo,
                'total_wip_qty'         => $qtyKw2,
                'waste_lengket_qty'     => $qtyLengket,
                'waste_total_qty'       => $qtyLengket + $qtyRemahan,
                'hpp_per_kg'            => $hppSatuan,
                'total_biaya_produksi'  => $totalNilai,
                'catatan_txt'           => "Sortir Ulang dari {$sourceBarang->barang_nm} (Batch {$batchNo}). Alasan QC: {$alasanQc}." . ($catatan ? " Catatan: {$catatan}" : ""),
                'created_by'            => $userId,
                'active_st'             => true,
                'deleted_st'            => false,
                'version_no'            => 1,
            ];
            $produksiHdr = DatProduksiHdr::create($headerData);

            // 10. Simpan Detail Produksi: KW 2
            DatProduksiDtl::create([
                'produksi_id'     => $produksiHdr->produksi_id,
                'barang_id'       => $barangKw2->barang_id,
                'jenis_cd'        => 'WIP',
                'kategori_output' => 'KW2',
                'qty_hasil'       => $qtyKw2,
                'satuan_cd'       => 'KG',
                'qty_kg'          => $qtyKw2,
                'batch_no'        => $outputBatchNo,
                'hpp_satuan'      => $hppSatuan,
                'total_nilai'     => $totalNilai,
                'keterangan_txt'  => "Hasil Sortir KW 2 dari batch {$batchNo}",
            ]);

            // Simpan Detail Produksi: Lengket (jika ada)
            if ($qtyLengket > 0 && isset($barangLgt)) {
                DatProduksiDtl::create([
                    'produksi_id'     => $produksiHdr->produksi_id,
                    'barang_id'       => $barangLgt->barang_id,
                    'jenis_cd'        => 'WIP',
                    'kategori_output' => 'LENGKET',
                    'qty_hasil'       => $qtyLengket,
                    'satuan_cd'       => 'KG',
                    'qty_kg'          => $qtyLengket,
                    'batch_no'        => $outputBatchNo,
                    'hpp_satuan'      => 0,
                    'total_nilai'     => 0,
                    'keterangan_txt'  => "Afkir Lengket dari Sortir Ulang batch {$batchNo}",
                ]);
            }

            // Simpan Detail Produksi: Remahan (jika ada)
            if ($qtyRemahan > 0 && isset($barangRmh)) {
                DatProduksiDtl::create([
                    'produksi_id'     => $produksiHdr->produksi_id,
                    'barang_id'       => $barangRmh->barang_id,
                    'jenis_cd'        => 'WIP',
                    'kategori_output' => 'REMAHAN',
                    'qty_hasil'       => $qtyRemahan,
                    'satuan_cd'       => 'KG',
                    'qty_kg'          => $qtyRemahan,
                    'batch_no'        => $outputBatchNo,
                    'hpp_satuan'      => 0,
                    'total_nilai'     => 0,
                    'keterangan_txt'  => "Afkir Remahan dari Sortir Ulang batch {$batchNo}",
                ]);
            }

            return $produksiHdr;
        });
    }

    /**
     * Dapatkan format batch standar harian (DD MM YYYY) untuk tanggal produksi.
     */
    public function getNextBatchSequence(string $tgl): string
    {
        return Carbon::parse($tgl)->format('d m Y');
    }

    /**
     * Ekstraksi ringkasan dokumen produksi fisik untuk ditarik ke Formulir Kalkulasi HPP (Finance).
     */
    /**
     * Ekstraksi ringkasan dokumen produksi fisik untuk ditarik ke Formulir Kalkulasi HPP (Finance).
     * Mendukung Konsolidasi Harian (menggabungkan SPK Pabrik Barat + Timur pada tanggal yang sama).
     */
    public function getProduksiDataForHpp(int $id): array
    {
        $primaryProd = DatProduksiHdr::findOrFail($id);
        $prodDate = $primaryProd->produksi_tgl ? Carbon::parse($primaryProd->produksi_tgl)->format('Y-m-d') : date('Y-m-d');
        $isBmb = in_array(strtoupper(trim((string)$primaryProd->lokasi_pabrik)), ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);
        $entitasCode = $isBmb ? 'BMB' : 'MFI';
        $entitasNm = $isBmb ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry';

        // Cari seluruh dokumen fisik produksi milik ENTITAS INI pada tanggal tersebut (Konsolidasi per Entitas)
        $docQuery = DatProduksiHdr::with([
            'pemakaianBahan.details.barang.satuanDasar',
            'pemakaianBahan.details.barang.jenisBarang',
            'pemakaianBahan.gudang',
            'gudang',
            'outputs.barang.satuanDasar',
            'outputs.barang.jenisBarang',
        ])
        ->whereDate('produksi_tgl', $prodDate)
        ->where('deleted_st', false);

        if ($isBmb) {
            $docQuery->whereIn('lokasi_pabrik', ['BMB', 'TIMUR']);
        } else {
            $docQuery->where(function ($q) {
                $q->whereIn('lokasi_pabrik', ['MFI', 'BARAT'])->orWhereNull('lokasi_pabrik');
            });
        }

        $allDocs = $docQuery->orderBy('shift_cd', 'asc')->orderBy('produksi_id', 'asc')->get();
        if ($allDocs->isEmpty()) {
            $allDocs = collect([$primaryProd]);
        }

        // Hitung total kuantitas fisik gabungan seluruh dokumen pada tanggal ini
        $totalWipAll = 0;
        $totalSingkongAll = 0;
        $totalMinyakSawitAll = 0;
        $totalMinyakKelapaAll = 0;
        $totalBumbuQtyAll = 0;
        $totalBumbuNilaiAll = 0;
        $totalKartonBaruQtyAll = 0;
        $totalKartonBaruNilaiAll = 0;
        $totalKartonBekasQtyAll = 0;
        $totalKartonBekasNilaiAll = 0;
        $totalPlastikHdQtyAll = 0;
        $totalPlastikHdNilaiAll = 0;
        $totalLakbanBesarQtyAll = 0;
        $totalLakbanBesarNilaiAll = 0;
        $totalLakbanKecilQtyAll = 0;
        $totalLakbanKecilNilaiAll = 0;
        $totalTaliRafiaQtyAll = 0;
        $totalTaliRafiaNilaiAll = 0;

        foreach ($allDocs as $d) {
            $wip = (float) ($d->total_wip_qty ?: ($d->outputs ? $d->outputs->sum('qty_kg') : 0));
            $totalWipAll += $wip;
            $totalSingkongAll += (float) $d->singkong_qty;
            $totalMinyakSawitAll += (float) $d->minyak_sawit_qty;
            $totalMinyakKelapaAll += (float) $d->minyak_kelapa_qty;
            $totalBumbuQtyAll += (float) $d->bumbu_qty;
            $totalBumbuNilaiAll += (float) $d->bumbu_nilai;
            $totalKartonBaruQtyAll += (float) $d->karton_baru_qty;
            $totalKartonBaruNilaiAll += (float) $d->karton_baru_nilai;
            $totalKartonBekasQtyAll += (float) $d->karton_bekas_qty;
            $totalKartonBekasNilaiAll += (float) $d->karton_bekas_nilai;
            $totalPlastikHdQtyAll += (float) $d->plastik_hd_qty;
            $totalPlastikHdNilaiAll += (float) $d->plastik_hd_nilai;
            $totalLakbanBesarQtyAll += (float) $d->lakban_besar_qty;
            $totalLakbanBesarNilaiAll += (float) $d->lakban_besar_nilai;
            $totalLakbanKecilQtyAll += (float) $d->lakban_kecil_qty;
            $totalLakbanKecilNilaiAll += (float) $d->lakban_kecil_nilai;
            $totalTaliRafiaQtyAll += (float) $d->tali_rafia_qty;
            $totalTaliRafiaNilaiAll += (float) $d->tali_rafia_nilai;
        }

        // Hitung total produksi fisik seluruh entitas pada tanggal ini untuk kalkulator gas & monitoring multi-pabrik
        $allPlantDocsOnDate = DatProduksiHdr::whereDate('produksi_tgl', $prodDate)->where('deleted_st', false)->get();
        $totalWipWholePlant = (float) $allPlantDocsOnDate->sum(function ($d) {
            return (float) ($d->total_wip_qty ?: ($d->outputs ? $d->outputs->sum('qty_kg') : 0));
        });
        $hasOtherEntity = $allPlantDocsOnDate->contains(function ($d) use ($isBmb) {
            $dBmb = in_array(strtoupper(trim((string)$d->lokasi_pabrik)), ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);
            return $dBmb !== $isBmb;
        });
        $entityGasRatioPct = ($totalWipWholePlant > 0 && $totalWipAll > 0) ? round(($totalWipAll / $totalWipWholePlant) * 100, 2) : 100;

        $countMfiDocs = $allPlantDocsOnDate->filter(fn($d) => !in_array(strtoupper(trim((string)$d->lokasi_pabrik)), ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']))->count();
        $countBmbDocs = $allPlantDocsOnDate->filter(fn($d) => in_array(strtoupper(trim((string)$d->lokasi_pabrik)), ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']))->count();
        $hasBothEntities = ($countMfiDocs > 0 && $countBmbDocs > 0);

        $sameDaySpks = $allPlantDocsOnDate->map(function ($other) use ($primaryProd, $isBmb) {
            $otherIsBmb = in_array(strtoupper(trim((string)$other->lokasi_pabrik)), ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);
            $otherPabrikCd = $otherIsBmb ? 'BMB' : 'MFI';
            $otherPabrikNm = $otherIsBmb ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry';
            $isFinal = ((float)$other->cng_nilai > 0 || (float)$other->tk_total_nilai > 0 || str_contains($other->catatan_txt ?? '', '[HPP FINAL') || str_contains($other->catatan_txt ?? '', 'HPP Finance'));

            return [
                'produksi_id'     => $other->produksi_id,
                'produksi_no'     => $other->produksi_no,
                'lokasi_pabrik'   => $otherPabrikCd,
                'pabrik_nm'       => $otherPabrikNm,
                'shift_cd'        => $other->shift_cd ?? 'A',
                'lini_produksi'   => $other->lini_produksi ?? 'Lini Manual',
                'total_wip_qty'   => (float) ($other->total_wip_qty ?: ($other->outputs ? $other->outputs->sum('qty_kg') : 0)),
                'hpp_per_kg'      => (float) $other->hpp_per_kg,
                'is_current_doc'  => ((int)$other->produksi_id === (int)$primaryProd->produksi_id),
                'is_same_entity'  => ($otherIsBmb === $isBmb),
                'is_hpp_final'    => $isFinal,
            ];
        })->values()->toArray();

        // Bangun dokumen_list untuk Card 1
        $dokumenList = [];
        $outputs = [];
        $pemakaianItems = [];
        $totalPemakaianNilaiAll = 0;
        $calcSingkongNilaiAll = 0;
        $calcMinyakNilaiAll = 0;
        $calcBumbuNilaiAll = 0;
        $calcKemasanNilaiAll = 0;

        foreach ($allDocs as $doc) {
            $wipDoc = (float) ($doc->total_wip_qty ?: ($doc->outputs ? $doc->outputs->sum('qty_kg') : 0));
            $ratio = $totalWipAll > 0 ? ($wipDoc / $totalWipAll) : ($allDocs->count() > 0 ? (1 / $allDocs->count()) : 1);
            $rawPabrik = strtoupper(trim((string)($doc->lokasi_pabrik ?? 'MFI')));
            $isBmb = in_array($rawPabrik, ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);
            $pabrikCd = $isBmb ? 'BMB' : 'MFI';
            $pabrikNm = $isBmb ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry';

            $docBppbNilai = 0;
            if ($doc->pemakaianBahan && $doc->pemakaianBahan->details) {
                foreach ($doc->pemakaianBahan->details as $dtl) {
                    if ($dtl->deleted_st) continue;
                    $barang = $dtl->barang;
                    $nm = $barang?->barang_nm ?? 'Bahan';
                    $cd = $barang?->barang_cd ?? '-';
                    $satuan = $barang?->satuanDasar?->satuan_nm ?? ($barang?->satuan?->satuan_nm ?? 'KG');
                    $qty = (float) $dtl->qty_keluar;
                    $harga = (float) $dtl->harga_satuan;
                    $subtotal = (float) $dtl->total_harga;
                    if ($subtotal <= 0 && $qty > 0 && $harga > 0) {
                        $subtotal = round($qty * $harga, 2);
                    }
                    $docBppbNilai += $subtotal;
                    $totalPemakaianNilaiAll += $subtotal;

                    $jb = $barang?->jenisBarang;
                    $kategoriBahan = $jb?->jenis_barang_nm ?? ($jb?->jenis_barang_cd ?? 'Bahan Penolong');

                    $nmUpper = strtoupper($nm);
                    $cdUpper = strtoupper($cd);
                    if (str_contains($nmUpper, 'SINGKONG') || str_starts_with($cdUpper, 'BB-SK') || str_contains($cdUpper, 'SNG')) {
                        $calcSingkongNilaiAll += $subtotal;
                    } elseif (str_contains($nmUpper, 'MINYAK') || str_contains($cdUpper, 'MSW') || str_contains($cdUpper, 'MKL')) {
                        $calcMinyakNilaiAll += $subtotal;
                    } elseif (str_contains($nmUpper, 'KARTON') || str_contains($nmUpper, 'PLASTIK') || str_contains($nmUpper, 'LAKBAN') || str_contains($nmUpper, 'RAFIA') || str_contains($nmUpper, 'SARUNG')) {
                        $calcKemasanNilaiAll += $subtotal;
                    } else {
                        $calcBumbuNilaiAll += $subtotal;
                    }

                    $pemakaianItems[] = [
                        'produksi_id'     => $doc->produksi_id,
                        'produksi_no'     => $doc->produksi_no,
                        'lokasi_pabrik'   => $pabrikCd,
                        'pabrik_nm'       => $pabrikNm,
                        'pakaidtl_id'     => $dtl->pakaidtl_id,
                        'barang_id'       => $dtl->barang_id,
                        'barang_nm'       => $nm,
                        'barang_cd'       => $cd,
                        'kategori_bahan'  => $kategoriBahan,
                        'jenis_barang_nm' => $kategoriBahan,
                        'jenis_barang_cd' => $jb?->jenis_barang_cd ?? 'BP',
                        'batch_no'        => $dtl->batch_no ?: '-',
                        'grade_cd'        => $dtl->grade_cd ?: '-',
                        'qty_keluar'      => $qty,
                        'satuan_cd'       => $satuan,
                        'harga_satuan'    => $harga,
                        'subtotal_nilai'  => $subtotal,
                        'keterangan_txt'  => $dtl->keterangan_txt,
                    ];
                }
            }

            foreach ($doc->outputs as $out) {
                $barang = $out->barang;
                $outputs[] = [
                    'produksi_id'     => $doc->produksi_id,
                    'produksi_no'     => $doc->produksi_no,
                    'lokasi_pabrik'   => $pabrikCd,
                    'pabrik_nm'       => $pabrikNm,
                    'output_id'       => $out->output_id,
                    'barang_id'       => $out->barang_id,
                    'barang_nm'       => $barang?->barang_nm ?? ($out->kategori_output ?? 'Output WIP'),
                    'barang_cd'       => $barang?->barang_cd ?? 'WIP',
                    'kategori_output' => $out->kategori_output,
                    'grade_cd'        => $out->grade_cd ?? 'KW1',
                    'batch_no'        => $out->batch_no ?: ($doc->batch_wip_no ?: '-'),
                    'qty_hasil'       => (float) $out->qty_hasil,
                    'satuan_cd'       => $out->satuan_cd ?: ($barang?->satuanDasar?->satuan_nm ?? 'KG'),
                    'qty_kg'          => (float) $out->qty_kg,
                    'hpp_satuan'      => (float) ($out->hpp_satuan > 0 ? $out->hpp_satuan : $doc->hpp_per_kg),
                    'total_nilai'     => (float) $out->total_nilai,
                ];
            }

            $dokumenList[] = [
                'produksi_id'     => $doc->produksi_id,
                'produksi_no'     => $doc->produksi_no,
                'lokasi_pabrik'   => $pabrikCd,
                'pabrik_nm'       => $pabrikNm,
                'shift_cd'        => $doc->shift_cd ?? 'A',
                'lini_produksi'   => $doc->lini_produksi ?? 'PRODUKSI MANUAL',
                'pakai_no'        => $doc->pemakaianBahan?->pakai_no ?? '-',
                'bppb_nilai'      => $docBppbNilai,
                'singkong_qty'    => (float) $doc->singkong_qty,
                'wip_qty'         => $wipDoc,
                'proporsi_persen' => round($ratio * 100, 2),
                'ratio'           => $ratio,
                'bf_aktif_count'  => (int) ($doc->bf_aktif_count ?? 0),
            ];
        }

        $singkongNilai = $calcSingkongNilaiAll > 0 ? $calcSingkongNilaiAll : (float) $allDocs->sum('singkong_nilai');
        $minyakNilai = $calcMinyakNilaiAll > 0 ? $calcMinyakNilaiAll : (float) $allDocs->sum('minyak_nilai');
        $bumbuNilai = $calcBumbuNilaiAll > 0 ? $calcBumbuNilaiAll : (float) $allDocs->sum('bumbu_nilai');

        // Utilitas Gas CNG
        $cngMmbtu = (float) $allDocs->sum('cng_mmbtu');
        $cngTarifUsd = (float) ($primaryProd->cng_tarif_usd > 0 ? $primaryProd->cng_tarif_usd : 14.50);
        $prodCarbon = $primaryProd->produksi_tgl ? Carbon::parse($primaryProd->produksi_tgl)->timezone(config('app.timezone', 'Asia/Jakarta')) : Carbon::today(config('app.timezone', 'Asia/Jakarta'));
        $prodDateStr = $prodCarbon->format('Y-m-d');
        $tarifProdService = app(\App\Services\Produksi\TarifProduksiService::class);
        $jisdorDetail = $tarifProdService->getJisdorRateDetail($prodDateStr, true);
        $prodDateJisdor = (float) $jisdorDetail['rate'];
        $kursEffectiveDateFmt = $jisdorDetail['date'];
        $keteranganAcuan = $jisdorDetail['keterangan_acuan'];

        $cngKursJisdor = (float) ($primaryProd->cng_nilai > 0 && $primaryProd->cng_kurs_jisdor > 0 ? $primaryProd->cng_kurs_jisdor : ($prodDateJisdor ?: 18252.00));
        $cngTarif = (float) ($primaryProd->cng_nilai > 0 && $primaryProd->cng_tarif > 0 ? $primaryProd->cng_tarif : round($cngTarifUsd * $cngKursJisdor, 2));
        $cngNilai = (float) ($allDocs->sum('cng_nilai') > 0 ? $allDocs->sum('cng_nilai') : round($cngMmbtu * $cngTarif));

        // Tenaga Kerja
        $laborData = $this->getLaborDataFromKaryawan($primaryProd->produksi_tgl, $primaryProd->lini_produksi);
        $tkJumlahOrg = (int) $allDocs->sum('tk_jumlah_org');
        $tkTotalNilai = (float) $allDocs->sum('tk_total_nilai');
        $tkLangsungOrg = (int) $allDocs->sum('tk_langsung_org');
        $tkLangsungNilai = (float) $allDocs->sum('tk_langsung_nilai');
        $tkTidakLangsungOrg = (int) $allDocs->sum('tk_tidak_langsung_org');
        $tkTidakLangsungNilai = (float) $allDocs->sum('tk_tidak_langsung_nilai');
        $tkTarifPerOrg = $tkJumlahOrg > 0 ? round($tkTotalNilai / $tkJumlahOrg) : 0;

        $dayNamesIndo = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
        ];
        $tglCarbon = Carbon::parse($primaryProd->produksi_tgl)->timezone(config('app.timezone', 'Asia/Jakarta'));
        $hariIndo = $dayNamesIndo[$tglCarbon->dayOfWeek] ?? 'Senin';

        $rendemenPersen = $totalSingkongAll > 0 ? round(($totalWipAll / $totalSingkongAll) * 100, 2) : 0;

        return [
            'produksi_id'          => $primaryProd->produksi_id,
            'produksi_no'          => $primaryProd->produksi_no,
            'is_konsolidasi_harian'=> count($dokumenList) > 1,
            'dokumen_list'         => $dokumenList,
            'dokumen_count'        => count($dokumenList),
            'lokasi_pabrik'        => $entitasCode,
            'entitas_cd'           => $entitasCode,
            'entitas_nm'           => $entitasNm,
            'total_wip_plant_all'  => $totalWipWholePlant,
            'has_other_entity'     => $hasOtherEntity,
            'entity_gas_ratio_pct' => $entityGasRatioPct,
            'produksi_tgl'         => $tglCarbon->format('Y-m-d'),
            'produksi_tgl_fmt'     => $tglCarbon->format('d/m/Y'),
            'hari_nm'              => $hariIndo,
            'shift_cd'             => $primaryProd->shift_cd,
            'lini_produksi'        => $primaryProd->lini_produksi,
            'gudang_id'            => $primaryProd->gudang_id,
            'gudang_nm'            => $primaryProd->gudang?->gudang_nm ?? 'Gudang Utama',
            'pakai_id'             => $primaryProd->pakai_id,
            'pakai_no'             => $primaryProd->pemakaianBahan?->pakai_no ?? '-',
            'total_bahan_nilai'    => $totalPemakaianNilaiAll,
            'pemakaian_bppb'       => [
                'pakai_no'    => $allDocs->pluck('pemakaianBahan.pakai_no')->filter()->implode(', ') ?: '-',
                'pakai_tgl'   => $tglCarbon->format('d/m/Y'),
                'gudang_nm'   => 'Gudang Bahan Baku',
                'total_nilai' => $totalPemakaianNilaiAll,
            ],
            'pemakaian_items'      => $pemakaianItems,
            'outputs'              => $outputs,
            'varietas_singkong'    => $primaryProd->varietas_singkong,
            'qty_karton'           => $allDocs->sum('qty_karton'),
            'batch_wip_no'         => $primaryProd->batch_wip_no,

            // Kuantitas Fisik Bahan (Gabungan Harian)
            'singkong_qty'         => $totalSingkongAll,
            'singkong_nilai'       => $singkongNilai,
            'minyak_sawit_qty'     => $totalMinyakSawitAll,
            'minyak_kelapa_qty'    => $totalMinyakKelapaAll,
            'minyak_nilai'         => $minyakNilai,
            'bumbu_qty'            => $totalBumbuQtyAll,
            'bumbu_nilai'          => $bumbuNilai,
            'total_wip_qty'        => $totalWipAll,
            'rendemen_persen'      => $rendemenPersen,

            // Kemasan
            'karton_baru_qty'      => $totalKartonBaruQtyAll,
            'karton_baru_nilai'    => $totalKartonBaruNilaiAll,
            'karton_bekas_qty'     => $totalKartonBekasQtyAll,
            'karton_bekas_nilai'   => $totalKartonBekasNilaiAll,
            'plastik_hd_qty'       => $totalPlastikHdQtyAll,
            'plastik_hd_nilai'     => $totalPlastikHdNilaiAll,
            'lakban_besar_qty'     => $totalLakbanBesarQtyAll,
            'lakban_besar_nilai'   => $totalLakbanBesarNilaiAll,
            'lakban_kecil_qty'     => $totalLakbanKecilQtyAll,
            'lakban_kecil_nilai'   => $totalLakbanKecilNilaiAll,
            'tali_rafia_qty'       => $totalTaliRafiaQtyAll,
            'tali_rafia_nilai'     => $totalTaliRafiaNilaiAll,

            // Gas CNG
            'cng_mmbtu'            => $cngMmbtu,
            'cng_tarif_usd'        => $cngTarifUsd,
            'cng_kurs_jisdor'      => $cngKursJisdor,
            'cng_kurs_date'        => $kursEffectiveDateFmt,
            'cng_kurs_ket'         => $keteranganAcuan,
            'cng_tarif'            => $cngTarif,
            'cng_nilai'            => $cngNilai,

            // Tenaga Kerja
            'tk_jumlah_org'           => $tkJumlahOrg,
            'tk_tarif_per_org'        => $tkTarifPerOrg,
            'tk_total_nilai'          => $tkTotalNilai,
            'tk_langsung_org'         => $tkLangsungOrg,
            'tk_langsung_nilai'       => $tkLangsungNilai,
            'tk_tidak_langsung_org'   => $tkTidakLangsungOrg,
            'tk_tidak_langsung_nilai' => $tkTidakLangsungNilai,
            'labor_sync'              => $laborData,
            'same_day_spks'           => $sameDaySpks,
            'has_both_entities'       => $hasBothEntities,
            'count_mfi_docs'          => $countMfiDocs,
            'count_bmb_docs'          => $countBmbDocs,
        ];
    }

    /**
     * Simpan Lembar Kalkulasi HPP Produksi Khusus Bagian Keuangan / Costing (Tahap 3).
     * Mendukung Konsolidasi Harian Multi-Pabrik (Barat + Timur) dengan 1 Lembar Jurnal Umum.
     */
    public function saveHppCalculation(int $produksiId, array $data, string $userId): DatProduksiHdr
    {
        return DB::transaction(function () use ($produksiId, $data, $userId) {
            $primaryProd = DatProduksiHdr::findOrFail($produksiId);
            $targetDate = $primaryProd->produksi_tgl ? Carbon::parse($primaryProd->produksi_tgl)->format('Y-m-d') : date('Y-m-d');

            $isBmb = in_array(strtoupper(trim((string)$primaryProd->lokasi_pabrik)), ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);

            // Ambil seluruh dokumen fisik produksi milik ENTITAS INI pada tanggal tersebut
            $docQuery = DatProduksiHdr::with(['outputs.barang', 'gudang', 'pemakaianBahan.details'])
                ->whereDate('produksi_tgl', $targetDate)
                ->where('deleted_st', false);

            if ($isBmb) {
                $docQuery->whereIn('lokasi_pabrik', ['BMB', 'TIMUR']);
            } else {
                $docQuery->where(function ($q) {
                    $q->whereIn('lokasi_pabrik', ['MFI', 'BARAT'])->orWhereNull('lokasi_pabrik');
                });
            }

            $allDocs = $docQuery->orderBy('shift_cd', 'asc')
                ->orderBy('produksi_id', 'asc')
                ->get();

            if ($allDocs->isEmpty()) {
                $allDocs = collect([$primaryProd]);
            }

            $totalWipAll = (float) $allDocs->sum(function ($d) {
                return (float) ($d->total_wip_qty ?: ($d->outputs ? $d->outputs->sum('qty_kg') : 0));
            });
            if ($totalWipAll <= 0) $totalWipAll = 1;

            $totalCngMmbtu = (float) ($data['cng_mmbtu'] ?? 0);
            $totalCngTarifUsd = (float) ($data['cng_tarif_usd'] ?? 14.50);
            $totalCngKursJisdor = (float) ($data['cng_kurs_jisdor'] ?? 18103.00);
            $totalCngTarif = (float) ($data['cng_tarif'] ?? round($totalCngTarifUsd * $totalCngKursJisdor, 2));
            $totalCngNilai = (float) ($data['cng_nilai'] ?? round($totalCngMmbtu * $totalCngTarif));

            $totalTkJumlah = (int) ($data['tk_jumlah_org'] ?? 0);
            $totalTkNilai = (float) ($data['tk_total_nilai'] ?? 0);
            $cngSplitInputs = $data['cng_split'] ?? []; // [produksi_id => mmbtu_value]

            // Batalkan jurnal lama jika ada dari dokumen-dokumen pada tanggal ini
            $userObj = Auth::user();
            foreach ($allDocs as $d) {
                if ($d->jurnal_id) {
                    $this->jurnalService->batalkanJurnal($d->produksi_no, $userObj);
                    $d->jurnal_id = null;
                }
            }

            $grandTotalBiayaAll = 0;
            $grandTotalBahanAll = 0;
            $grandTotalCngNilaiAll = 0;
            $grandTotalTkNilaiAll = 0;
            $grandTotalOverheadAll = 0;

            // Simpan alokasi ke masing-masing dokumen
            foreach ($allDocs as $doc) {
                $wipDoc = (float) ($doc->total_wip_qty ?: ($doc->outputs ? $doc->outputs->sum('qty_kg') : 0));
                $ratio = $totalWipAll > 0 ? ($wipDoc / $totalWipAll) : 1;

                // Tentukan alokasi MMBtu gas (manual override vs proporsional otomatis)
                if (isset($cngSplitInputs[$doc->produksi_id]) && is_numeric($cngSplitInputs[$doc->produksi_id])) {
                    $docMmbtu = (float) $cngSplitInputs[$doc->produksi_id];
                } else {
                    $docMmbtu = round($totalCngMmbtu * $ratio, 4);
                }

                $docCngNilai = round($docMmbtu * $totalCngTarif);
                $docTkNilai = round($totalTkNilai * $ratio);
                $docTkJumlah = (int) round($totalTkJumlah * $ratio);

                $doc->cng_mmbtu = $docMmbtu;
                $doc->cng_tarif_usd = $totalCngTarifUsd;
                $doc->cng_kurs_jisdor = $totalCngKursJisdor;
                $doc->cng_tarif = $totalCngTarif;
                $doc->cng_nilai = $docCngNilai;

                $doc->tk_jumlah_org = $docTkJumlah;
                $doc->tk_total_nilai = $docTkNilai;

                // Hitung total bahan untuk dokumen ini
                if ($allDocs->count() === 1) {
                    if (isset($data['singkong_nilai'])) $doc->singkong_nilai = (float) $data['singkong_nilai'];
                    if (isset($data['minyak_nilai'])) $doc->minyak_nilai = (float) $data['minyak_nilai'];
                    if (isset($data['bumbu_nilai'])) $doc->bumbu_nilai = (float) $data['bumbu_nilai'];
                    if (isset($data['karton_baru_nilai'])) $doc->karton_baru_nilai = (float) $data['karton_baru_nilai'];
                    if (isset($data['karton_bekas_nilai'])) $doc->karton_bekas_nilai = (float) $data['karton_bekas_nilai'];
                    if (isset($data['plastik_hd_nilai'])) $doc->plastik_hd_nilai = (float) $data['plastik_hd_nilai'];
                    if (isset($data['lakban_besar_nilai'])) $doc->lakban_besar_nilai = (float) $data['lakban_besar_nilai'];
                    if (isset($data['lakban_kecil_nilai'])) $doc->lakban_kecil_nilai = (float) $data['lakban_kecil_nilai'];
                    if (isset($data['tali_rafia_nilai'])) $doc->tali_rafia_nilai = (float) $data['tali_rafia_nilai'];
                }

                $docTotalBahan = (float)$doc->singkong_nilai + (float)$doc->minyak_nilai + (float)$doc->bumbu_nilai +
                    (float)$doc->karton_baru_nilai + (float)$doc->karton_bekas_nilai + (float)$doc->plastik_hd_nilai +
                    (float)$doc->lakban_besar_nilai + (float)$doc->lakban_kecil_nilai + (float)$doc->tali_rafia_nilai;
                $doc->total_bahan_nilai = $docTotalBahan;

                $docTotalBiaya = round($docTotalBahan + $docCngNilai + $docTkNilai + (float)($doc->total_overhead_nilai ?? 0), 2);
                $doc->total_biaya_produksi = $docTotalBiaya;

                $grandTotalBiayaAll += $docTotalBiaya;
                $grandTotalBahanAll += $docTotalBahan;
                $grandTotalCngNilaiAll += $docCngNilai;
                $grandTotalTkNilaiAll += $docTkNilai;
                $grandTotalOverheadAll += (float)($doc->total_overhead_nilai ?? 0);
            }

            // Hitung HPP Konsolidasi Seragam (Total Seluruh Biaya / Total Seluruh WIP)
            $consolidatedHppPerKg = round($grandTotalBiayaAll / $totalWipAll, 2);

            // Audit text
            $auditFinanceTag = "[HPP FINAL KONSOLIDASI " . date('d/m/Y H:i') . " oleh " . $userId . "]";
            $customOutputPrices = $data['output_hpp'] ?? [];

            foreach ($allDocs as $doc) {
                $doc->hpp_per_kg = $consolidatedHppPerKg;
                $doc->hpp_finalized_at = now();
                $doc->hpp_finalized_by = $userId;
                if (!empty($data['catatan_hpp'])) {
                    $doc->catatan_txt = trim(($doc->catatan_txt ? $doc->catatan_txt . "\n" : '') . $auditFinanceTag . ": " . $data['catatan_hpp']);
                } else {
                    $doc->catatan_txt = trim(($doc->catatan_txt ? $doc->catatan_txt . "\n" : '') . $auditFinanceTag);
                }
                $doc->updated_by = $userId;
                $doc->version_no = ((int) ($doc->version_no ?? 1)) + 1;
                $doc->save();

                // Update outputs
                if ($doc->outputs && $doc->outputs->isNotEmpty()) {
                    foreach ($doc->outputs as $out) {
                        $itemHpp = isset($customOutputPrices[$out->output_id]) && is_numeric($customOutputPrices[$out->output_id])
                            ? (float) $customOutputPrices[$out->output_id]
                            : $consolidatedHppPerKg;
                        $out->hpp_satuan = $itemHpp;
                        $out->total_nilai = round((float) $out->qty_kg * $itemHpp, 2);
                        $out->updated_by = $userId;
                        $out->save();

                        if (!empty($out->batch_no) && $out->barang_id) {
                            DatStokBatch::where('barang_id', $out->barang_id)
                                ->where('batch_no', $out->batch_no)
                                ->where('deleted_st', false)
                                ->update([
                                    'harga_satuan' => $itemHpp,
                                    'updated_by'   => $userId,
                                ]);
                        }
                    }
                }
            }

            // Terbitkan 1 Lembar Jurnal Umum Konsolidasi Harian untuk Tanggal Tersebut
            $tglFormat = Carbon::parse($targetDate)->format('d/m/Y');
            $totalKgStr = number_format($totalWipAll, 2, ',', '.');
            $spkListStr = $allDocs->pluck('produksi_no')->implode(', ');

            $keteranganJurnal = "Biaya Produksi & HPP WIP Konsolidasi ({$tglFormat}) - ({$totalKgStr} kg WIP) [{$spkListStr}]";

            $akunWip = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_WIP', '1151');
            $akunBahanBaku = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_BAHAN', '1150');
            $akunBahanPenolong = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_PENOLONG', '1153');
            $akunGas = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_GAS', '2100');
            $akunTk = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_TENAGA_KERJA', '2102');

            $grandTotalSingkong = (float) $allDocs->sum('singkong_nilai');
            $grandTotalPenolong = max(0, $grandTotalBahanAll - $grandTotalSingkong);
            $grandTotalBiayaKonsolidasi = $grandTotalSingkong + $grandTotalPenolong + $grandTotalCngNilaiAll + $grandTotalTkNilaiAll;

            $entries = [];
            $entriesMap = [
                'HPP_WIP_AKUN_WIP'          => [
                    'kode_akun'  => $akunWip,
                    'debet'      => $grandTotalBiayaKonsolidasi,
                    'kredit'     => 0,
                    'keterangan' => $keteranganJurnal,
                ],
                'HPP_WIP_AKUN_BAHAN'        => ($grandTotalSingkong > 0) ? [
                    'kode_akun'  => $akunBahanBaku,
                    'debet'      => 0,
                    'kredit'     => $grandTotalSingkong,
                    'keterangan' => $keteranganJurnal,
                ] : null,
                'HPP_WIP_AKUN_PENOLONG'     => ($grandTotalPenolong > 0) ? [
                    'kode_akun'  => $akunBahanPenolong,
                    'debet'      => 0,
                    'kredit'     => $grandTotalPenolong,
                    'keterangan' => $keteranganJurnal,
                ] : null,
                'HPP_WIP_AKUN_GAS'          => ($grandTotalCngNilaiAll > 0) ? [
                    'kode_akun'  => $akunGas,
                    'debet'      => 0,
                    'kredit'     => $grandTotalCngNilaiAll,
                    'keterangan' => $keteranganJurnal,
                ] : null,
                'HPP_WIP_AKUN_TENAGA_KERJA' => ($grandTotalTkNilaiAll > 0) ? [
                    'kode_akun'  => $akunTk,
                    'debet'      => 0,
                    'kredit'     => $grandTotalTkNilaiAll,
                    'keterangan' => $keteranganJurnal,
                ] : null,
            ];

            $entries = $this->pengaturanJurnalService->orderEntries('HPP_WIP', $entriesMap);

            $jurnal = $this->jurnalService->catatJurnal(
                'HPP_WIP',
                $targetDate,
                $primaryProd->produksi_no,
                $primaryProd->produksi_id,
                $keteranganJurnal,
                $entries,
                $userObj
            );

            // Kaitkan seluruh dokumen pada tanggal ini ke Jurnal Konsolidasi yang sama
            foreach ($allDocs as $doc) {
                $doc->update([
                    'jurnal_id'        => $jurnal->jurnal_id,
                    'hpp_finalized_at' => now(),
                    'hpp_finalized_by' => $userId ?: ($userObj?->name ?? 'SYSTEM'),
                ]);
            }

            return $primaryProd;
        });
    }

    /**
     * Terbitkan atau sinkronkan Jurnal Umum Otomatis saat HPP WIP difinalisasi / disesuaikan.
     * Format Keterangan seragam tanpa kode dokumen:
     * "Biaya Produksi & HPP WIP (dd/mm/yyyy) - Shift {Shift} ({Qty} kg WIP)"
     */
    public function syncJournalHppWip(DatProduksiHdr $produksi, ?string $userId = null): ?\App\Models\Keuangan\DatJurnalHdr
    {
        $singkongNilai = (float) ($produksi->singkong_nilai ?? 0);
        $totalBahan = (float) $produksi->total_bahan_nilai;
        $penolongNilai = max(0, $totalBahan - $singkongNilai);
        $cngNilai = (float) $produksi->cng_nilai;
        $tkNilai = (float) $produksi->tk_total_nilai;

        $totalBiayaWip = $singkongNilai + $penolongNilai + $cngNilai + $tkNilai;

        if ($totalBiayaWip <= 0) {
            return null;
        }

        $userObj = Auth::user();

        // Batalkan jurnal lama jika sebelumnya sudah pernah diterbitkan (koreksi/re-kalkulasi)
        if ($produksi->jurnal_id) {
            $this->jurnalService->batalkanJurnal($produksi->produksi_no, $userObj);
            $produksi->jurnal_id = null;
        }

        // Ambil konfigurasi akun dari Pengaturan Pemetaan Jurnal Otomatis
        $akunWip = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_WIP', '1151');
        $akunBahanBaku = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_BAHAN', '1150');
        $akunBahanPenolong = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_PENOLONG', '1153');
        $akunGas = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_GAS', '2100');
        $akunTk = $this->pengaturanJurnalService->getCoaKode('HPP_WIP_AKUN_TENAGA_KERJA', '2102');

        // Format keterangan seragam sesuai template dinamis (tanpa kode dokumen):
        $tglFormat = $produksi->produksi_tgl ? Carbon::parse($produksi->produksi_tgl)->format('d/m/Y') : date('d/m/Y');
        $effectiveKg = (float) ($produksi->total_wip_qty ?: ($produksi->outputs ? $produksi->outputs->sum('qty_kg') : 0));
        $totalKgStr = number_format($effectiveKg, 2, ',', '.');
        $shiftStr = !empty($produksi->shift_cd) ? (string) $produksi->shift_cd : '1';

        $keteranganJurnal = $this->pengaturanJurnalService->renderTemplate(
            'HPP_WIP_TEMPLATE_KET',
            'Biaya Produksi & HPP WIP ({tgl}) - Shift {shift} ({total_kg} kg WIP)',
            [
                'tgl'      => $tglFormat,
                'shift'    => $shiftStr,
                'total_kg' => $totalKgStr,
                'lini'     => $produksi->lini_produksi ?? '-',
            ]
        );

        $entries = [];

        // 1. DEBET: Persediaan WIP (Konfigurasi: HPP_WIP_AKUN_WIP)
        $entriesMap = [
            'HPP_WIP_AKUN_WIP'          => [
                'kode_akun'  => $akunWip,
                'debet'      => $totalBiayaWip,
                'kredit'     => 0,
                'keterangan' => $keteranganJurnal,
            ],
            'HPP_WIP_AKUN_BAHAN'        => ($singkongNilai > 0) ? [
                'kode_akun'  => $akunBahanBaku,
                'debet'      => 0,
                'kredit'     => $singkongNilai,
                'keterangan' => $keteranganJurnal,
            ] : null,
            'HPP_WIP_AKUN_PENOLONG'     => ($penolongNilai > 0) ? [
                'kode_akun'  => $akunBahanPenolong,
                'debet'      => 0,
                'kredit'     => $penolongNilai,
                'keterangan' => $keteranganJurnal,
            ] : null,
            'HPP_WIP_AKUN_GAS'          => ($cngNilai > 0) ? [
                'kode_akun'  => $akunGas,
                'debet'      => 0,
                'kredit'     => $cngNilai,
                'keterangan' => $keteranganJurnal,
            ] : null,
            'HPP_WIP_AKUN_TENAGA_KERJA' => ($tkNilai > 0) ? [
                'kode_akun'  => $akunTk,
                'debet'      => 0,
                'kredit'     => $tkNilai,
                'keterangan' => $keteranganJurnal,
            ] : null,
        ];

        $entries = $this->pengaturanJurnalService->orderEntries('HPP_WIP', $entriesMap);

        $tglJurnal = $produksi->produksi_tgl ? Carbon::parse($produksi->produksi_tgl)->format('Y-m-d') : date('Y-m-d');

        $jurnal = $this->jurnalService->catatJurnal(
            'HPP_WIP',
            $tglJurnal,
            $produksi->produksi_no,
            $produksi->produksi_id,
            $keteranganJurnal,
            $entries,
            $userObj
        );

        $produksi->update([
            'jurnal_id'        => $jurnal->jurnal_id,
            'hpp_finalized_at' => now(),
            'hpp_finalized_by' => $userId ?: ($userObj?->name ?? 'SYSTEM'),
        ]);

        return $jurnal;
    }

    /**
     * Mengambil struktur data HPP Terpadu End-to-End (Hulu ke Hilir)
     * Mengaitkan Dokumen Dapur (SPK Fisik & WIP) dengan seluruh turunan Pengemasan (Finish Goods).
     *
     * @param int $produksiId
     * @return array
     */
    public function getHppTerpaduData(int $produksiId): array
    {
        $produksi = DatProduksiHdr::with([
            'pemakaianBahan.details.barang.satuanDasar',
            'gudang',
            'outputs.barang.satuanDasar',
            'packagings' => function ($q) {
                $q->where('deleted_st', false)
                  ->where('status_cd', '!=', 'VOID')
                  ->with([
                      'barangJadi.satuanDasar',
                      'gudang',
                      'details.barang.satuanDasar'
                  ])
                  ->orderBy('pack_tgl', 'asc')
                  ->orderBy('pack_id', 'asc');
            }
        ])
        ->where('deleted_st', false)
        ->findOrFail($produksiId);

        // 1. Ekstraksi Bahan Baku Dapur (BPPB)
        $bahanBakuItems = [];
        $totalBahanNilai = 0;
        if ($produksi->pemakaianBahan && $produksi->pemakaianBahan->details) {
            foreach ($produksi->pemakaianBahan->details as $dtl) {
                if ($dtl->deleted_st) continue;
                $nm = $dtl->barang?->barang_nm ?? 'Bahan';
                $qty = (float) $dtl->qty_keluar;
                $harga = (float) $dtl->harga_satuan;
                $subtotal = (float) ($dtl->total_harga ?: ($qty * $harga));
                $totalBahanNilai += $subtotal;

                // Kategori bahan: Singkong, Minyak, Bumbu, dll.
                $kategori = 'Bumbu & Penolong';
                $u = strtoupper($nm);
                if (str_contains($u, 'SINGKONG') || str_contains($u, 'UBI')) {
                    $kategori = 'Singkong Mentah';
                } elseif (str_contains($u, 'MINYAK') || str_contains($u, 'SAWIT') || str_contains($u, 'GORENG')) {
                    $kategori = 'Minyak Goreng';
                }

                $bahanBakuItems[] = [
                    'barang_cd'   => $dtl->barang?->barang_cd ?? '-',
                    'barang_nm'   => $nm,
                    'kategori'    => $kategori,
                    'batch_no'    => $dtl->batch_no ?? '-',
                    'qty'         => $qty,
                    'satuan'      => $dtl->barang?->satuanDasar?->satuan_nm ?? 'kg',
                    'harga_satuan'=> $harga,
                    'subtotal'    => $subtotal,
                ];
            }
        }

        // 2. Operasional Dapur (Gas CNG & Upah Tenaga Kerja Regu)
        $cngNilai = (float) $produksi->cng_nilai;
        $upahDapurNilai = (float) $produksi->tk_total_nilai;
        $totalDapurNilai = (float) $produksi->total_biaya_produksi;
        if ($totalDapurNilai <= 0) {
            $totalDapurNilai = $totalBahanNilai + $cngNilai + $upahDapurNilai;
        }

        // 3. Output Curah Masak Dapur & Pelacakan Pemakaian di Kemasan
        $outputsList = [];
        $totalWipKgProduced = 0;

        foreach ($produksi->outputs as $out) {
            if ($out->deleted_st) continue;
            $outId = $out->output_id;
            $barangId = $out->barang_id;
            $outKg = (float) $out->qty_kg;
            $hppPerKg = (float) ($out->hpp_satuan ?: $produksi->hpp_per_kg);
            $subtotalOut = (float) ($out->total_nilai ?: ($outKg * $hppPerKg));
            $totalWipKgProduced += $outKg;

            // Hitung berapa kg output ini yang sudah diambil oleh dokumen pengemasan
            $consumedKg = 0;
            $consumedInPackagings = [];

            foreach ($produksi->packagings as $pkg) {
                foreach ($pkg->details as $pDtl) {
                    if ($pDtl->deleted_st) continue;
                    if (strtoupper($pDtl->kategori_bahan) === 'WIP') {
                        // Cocokkan berdasarkan barang_id atau jika hanya 1 output
                        if ($pDtl->barang_id == $barangId || $produksi->outputs->count() === 1) {
                            $taken = (float) $pDtl->qty_aktual;
                            $consumedKg += $taken;
                            $consumedInPackagings[] = [
                                'pack_id'  => $pkg->pack_id,
                                'pack_no'  => $pkg->pack_no,
                                'fg_nm'    => $pkg->barangJadi?->barang_nm ?? 'Barang Jadi',
                                'qty_kg'   => $taken,
                            ];
                        }
                    }
                }
            }

            $remainingKg = max(0, $outKg - $consumedKg);

            $outputsList[] = [
                'output_id'       => $outId,
                'barang_id'       => $barangId,
                'barang_nm'       => $out->barang?->barang_nm ?? ($out->kategori_output === 'berko' ? 'Keripik Berko' : 'Keripik Curah Utama'),
                'kategori_output' => $out->kategori_output,
                'batch_no'        => $out->batch_no ?? $produksi->batch_wip_no,
                'qty_kg'          => $outKg,
                'hpp_per_kg'      => $hppPerKg,
                'subtotal_nilai'  => $subtotalOut,
                'consumed_kg'     => $consumedKg,
                'remaining_kg'    => $remainingKg,
                'consumed_in'     => $consumedInPackagings,
                'is_fully_packed' => $remainingKg <= 0.05,
            ];
        }

        // 4. Detail Turunan Pengemasan (Packagings)
        $packagingBranches = [];
        $totalBiayaKemasKumulatif = 0;
        $totalHasilKartonKumulatif = 0;
        $totalHasilPcsKumulatif = 0;

        foreach ($produksi->packagings as $pkg) {
            $packTotalBiaya = (float) $pkg->total_biaya_produksi;
            $totalBiayaKemasKumulatif += $packTotalBiaya;
            $totalHasilKartonKumulatif += (float) $pkg->hasil_karton_qty;
            $totalHasilPcsKumulatif += (float) $pkg->hasil_pcs_qty;

            // Rincian WIP yang diambil
            $wipTaken = [];
            $kemasanMaterials = [];
            foreach ($pkg->details as $pDtl) {
                if ($pDtl->deleted_st) continue;
                $isWip = strtoupper($pDtl->kategori_bahan) === 'WIP';
                $itemData = [
                    'barang_cd'      => $pDtl->barang?->barang_cd ?? '-',
                    'barang_nm'      => $pDtl->barang?->barang_nm ?? ($isWip ? 'Curah WIP' : 'Bahan Kemas'),
                    'qty'            => (float) $pDtl->qty_aktual,
                    'satuan'         => $pDtl->satuan_cd ?: ($pDtl->barang?->satuanDasar?->satuan_nm ?? ($isWip ? 'kg' : 'pcs')),
                    'harga_satuan'   => (float) $pDtl->harga_satuan,
                    'subtotal_nilai' => (float) $pDtl->subtotal_nilai,
                ];
                if ($isWip) {
                    $wipTaken[] = $itemData;
                } else {
                    $kemasanMaterials[] = $itemData;
                }
            }

            $packagingBranches[] = [
                'pack_id'                 => $pkg->pack_id,
                'pack_no'                 => $pkg->pack_no,
                'pack_tgl'                => $pkg->pack_tgl,
                'shift'                   => $pkg->shift,
                'operator_nama'           => $pkg->operator_nama,
                'barang_jadi_cd'          => $pkg->barangJadi?->barang_cd ?? '-',
                'barang_jadi_nm'          => $pkg->barangJadi?->barang_nm ?? 'Produk Jadi (FG)',
                'batch_no'                => $pkg->batch_no,
                'expired_tgl'             => $pkg->expired_tgl,
                'target_karton_qty'       => (float) $pkg->target_karton_qty,
                'hasil_karton_qty'        => (float) $pkg->hasil_karton_qty,
                'hasil_pack_qty'          => (float) $pkg->hasil_pack_qty,
                'hasil_pcs_qty'           => (float) $pkg->hasil_pcs_qty,
                'total_biaya_wip'         => (float) $pkg->total_biaya_wip,
                'total_biaya_kemasan'     => (float) $pkg->total_biaya_kemasan,
                'total_biaya_tenaga_kerja'=> (float) $pkg->total_biaya_tenaga_kerja,
                'total_biaya_lain'        => (float) $pkg->total_biaya_lain,
                'total_biaya_produksi'    => $packTotalBiaya,
                'hpp_per_karton'          => (float) $pkg->hpp_per_karton,
                'hpp_per_pcs'             => (float) $pkg->hpp_per_pcs,
                'is_hpp_final'            => (bool) $pkg->is_hpp_final,
                'wip_taken'               => $wipTaken,
                'kemasan_materials'       => $kemasanMaterials,
                'jumlah_personil'         => (int) $pkg->jumlah_personil,
            ];
        }

        // 5. Cek SPK Sibling (Lini/Shift lain di tanggal yang sama) untuk fitur Line Switcher
        $siblingSpks = DatProduksiHdr::where('produksi_tgl', $produksi->produksi_tgl)
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'VOID')
            ->orderBy('produksi_id', 'asc')
            ->select(['produksi_id', 'produksi_no', 'shift_cd', 'lini_produksi', 'batch_wip_no', 'total_biaya_produksi', 'hpp_per_kg'])
            ->get()
            ->map(function ($s) use ($produksiId) {
                return [
                    'produksi_id'          => $s->produksi_id,
                    'produksi_no'          => $s->produksi_no,
                    'shift_cd'             => $s->shift_cd,
                    'lini_produksi'        => $s->lini_produksi ?: 'REGULER',
                    'batch_wip_no'         => $s->batch_wip_no,
                    'total_biaya_produksi' => (float) $s->total_biaya_produksi,
                    'hpp_per_kg'           => (float) $s->hpp_per_kg,
                    'is_current'           => (int) $s->produksi_id === (int) $produksiId,
                ];
            })
            ->toArray();

        return [
            'produksi'                    => $produksi,
            'bahan_baku_items'            => $bahanBakuItems,
            'total_bahan_nilai'           => $totalBahanNilai,
            'cng_nilai'                   => $cngNilai,
            'upah_dapur_nilai'            => $upahDapurNilai,
            'total_dapur_nilai'           => $totalDapurNilai,
            'total_wip_kg_produced'       => $totalWipKgProduced,
            'rendemen_persen'             => (float) $produksi->rendemen_persen,
            'outputs_list'                => $outputsList,
            'packaging_branches'          => $packagingBranches,
            'packagingBranches'           => $packagingBranches,
            'sibling_spks'                => $siblingSpks,
            'total_biaya_kemas_kumulatif' => $totalBiayaKemasKumulatif,
            'total_hasil_karton_kumulatif'=> $totalHasilKartonKumulatif,
            'total_hasil_pcs_kumulatif'   => $totalHasilPcsKumulatif,
            'grand_total_biaya_pabrik'    => $totalDapurNilai + $totalBiayaKemasKumulatif,
        ];
    }
}

