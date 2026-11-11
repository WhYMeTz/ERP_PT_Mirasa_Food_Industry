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
        protected TarifProduksiService $tarifService
    ) {}

    /**
     * Mengambil laporan bulanan HPP Harian & Rendemen (Format Excel Asli PT Mirasa).
     */
    public function getMonthlyReport(int $year, int $month): array
    {
        $records = DatProduksiHdr::with(['pemakaianBahan.details.barang', 'gudang', 'outputs.barang'])
            ->whereYear('produksi_tgl', $year)
            ->whereMonth('produksi_tgl', $month)
            ->where('deleted_st', false)
            ->orderBy('produksi_tgl', 'asc')
            ->orderBy('shift_cd', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

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
                $dayCngNilai = (float) $dayRecords->sum('cng_nilai');

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
                    'cng_mmbtu'                   => $dayCngMmbtu,
                    'cng_nilai'                   => $dayCngNilai,
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

            // 1. Karyawan Langsung Produksi (Pengolahan / Penggorengan)
            $prodRows = $rows->filter(function ($row) use ($isIfm, $isManualOnly) {
                if (empty($row['masuk'])) {
                    return false;
                }
                $kAsli = strtoupper((string) ($row['kelompok_asli'] ?? $row['kelompok'] ?? ''));

                // Pastikan bukan packaging dan bukan tidak langsung
                if (str_contains($kAsli, 'PACK') || str_contains($kAsli, 'KEMAS') || str_contains($kAsli, 'TIDAK')) {
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
            if ($prodRows->count() === 0) {
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

            $prodLabel = $isIfm ? 'Karyawan Langsung IFM' : ($isManualOnly ? 'Karyawan Langsung Manual' : 'Karyawan Langsung Produksi');

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
                                'tgl'            => $tgl,
                                'tgl_format'     => $tglFormatted,
                                'is_synced'      => true,
                                'tk_jumlah_org'  => $count,
                                'tk_total_nilai' => $totalUpah,
                                'kelompok'       => $kelompokLabel,
                                'source'         => $tableName,
                                'message'        => "Data presensi tersinkronisasi ({$count} orang hadir, total Rp " . number_format($totalUpah, 0, ',', '.') . ").",
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
            'tk_langsung_label'       => 'Karyawan Langsung Produksi',
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
        $pakai = DatPakaiHdr::with(['details.barang.jenisBarang', 'details.barang.satuanDasar', 'gudang'])->where('pakai_id', $pakaiId)->firstOrFail();

        $summary = [
            'pakai_id'           => $pakai->pakai_id,
            'pakai_no'           => $pakai->pakai_no,
            'pakai_tgl'          => $pakai->pakai_tgl ? $pakai->pakai_tgl->format('Y-m-d') : null,
            'pakai_tgl_formatted'=> $pakai->pakai_tgl ? $pakai->pakai_tgl->format('d/m/Y') : '-',
            'gudang_nm'          => $pakai->gudang?->gudang_nm ?? 'Gudang Bahan Baku',
            'tujuan_pemakaian'   => $pakai->tujuan_pemakaian,
            'gudang_id'          => $pakai->gudang_id,
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

            $prodNo = !empty($data['produksi_no']) ? trim($data['produksi_no']) : null;
            if (empty($prodNo) || DatProduksiHdr::where('produksi_no', $prodNo)->exists()) {
                $data['produksi_no'] = $this->codeGenerator->generateProduksiNo($tgl);
            } else {
                $data['produksi_no'] = $prodNo;
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

            // Auto-posting Dokumen Pemakaian Bahan (BPPB) jika berstatus DRAFT saat Laporan Produksi diposting
            if (!empty($produksi->pakai_id)) {
                $pakaiHdr = DatPakaiHdr::find($produksi->pakai_id);
                if ($pakaiHdr && $pakaiHdr->isDraft() && $produksi->status_cd === 'POSTED') {
                    $pemakaianService = app(\App\Services\Gudang\PemakaianService::class);
                    $pemakaianService->postPemakaian($pakaiHdr->pakai_id, Auth::user());
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
        return DatProduksiHdr::with(['pemakaianBahan', 'gudang', 'outputs.barang', 'adjustmentLogs'])
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
            'details.barang.jenisBarang'
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

                    if ($modeCng === 'update_tarif' && $cngTarifBaru > 0) {
                        $record->cng_tarif = $cngTarifBaru;
                        $record->cng_nilai = round($record->cng_mmbtu * $cngTarifBaru, 2);
                    } elseif ($modeCng === 'proporsional_wip') {
                        $record->cng_nilai = $totalWipBulanIni > 0
                            ? round($totalCngTagihan * ($record->total_wip_qty / $totalWipBulanIni), 2)
                            : round($totalCngTagihan / $count, 2);
                        if ($record->cng_mmbtu > 0) {
                            $record->cng_tarif = round($record->cng_nilai / $record->cng_mmbtu, 2);
                        }
                    } elseif ($modeCng === 'total_tagihan') {
                        $record->cng_tarif = $effectiveCngTarif;
                        $record->cng_nilai = round($record->cng_mmbtu * $effectiveCngTarif, 2);
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
    public function getProduksiDataForHpp(int $id): array
    {
        $prod = DatProduksiHdr::with([
            'pemakaianBahan.details.barang.satuanDasar',
            'pemakaianBahan.details.barang.jenisBarang',
            'pemakaianBahan.gudang',
            'gudang',
            'outputs.barang.satuanDasar',
            'outputs.barang.jenisBarang',
        ])->findOrFail($id);

        $outputs = [];
        foreach ($prod->outputs as $out) {
            $barang = $out->barang;
            $outputs[] = [
                'output_id'       => $out->output_id,
                'barang_id'       => $out->barang_id,
                'barang_nm'       => $barang?->barang_nm ?? ($out->kategori_output ?? 'Output WIP'),
                'barang_cd'       => $barang?->barang_cd ?? 'WIP',
                'kategori_output' => $out->kategori_output,
                'grade_cd'        => $out->grade_cd ?? 'KW1',
                'batch_no'        => $out->batch_no ?: ($prod->batch_wip_no ?: '-'),
                'qty_hasil'       => (float) $out->qty_hasil,
                'satuan_cd'       => $out->satuan_cd ?: ($barang?->satuanDasar?->satuan_nm ?? 'KG'),
                'qty_kg'          => (float) $out->qty_kg,
                'hpp_satuan'      => (float) ($out->hpp_satuan > 0 ? $out->hpp_satuan : $prod->hpp_per_kg),
                'total_nilai'     => (float) $out->total_nilai,
            ];
        }

        // Ekstraksi rincian barang pemakaian gudang (BPPB)
        $pemakaianItems = [];
        $totalPemakaianNilai = 0;
        $calcSingkongNilai = 0;
        $calcMinyakNilai = 0;
        $calcBumbuNilai = 0;
        $calcBumbuQty = 0;
        $calcKemasanNilai = 0;

        if ($prod->pemakaianBahan && $prod->pemakaianBahan->details) {
            foreach ($prod->pemakaianBahan->details as $dtl) {
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
                $totalPemakaianNilai += $subtotal;

                // Klasifikasi resmi kategori barang dari Master Data (mst_jenis_barang)
                $jb = $barang?->jenisBarang;
                $kategoriBahan = $jb?->jenis_barang_nm ?? ($jb?->jenis_barang_cd ?? 'Bahan Penolong');

                // Akumulasi nilai komponen biaya bahan untuk formulir HPP
                $nmUpper = strtoupper($nm);
                $cdUpper = strtoupper($cd);
                if (str_contains($nmUpper, 'SINGKONG') || str_starts_with($cdUpper, 'BB-SK') || str_contains($cdUpper, 'SNG')) {
                    $calcSingkongNilai += $subtotal;
                } elseif (str_contains($nmUpper, 'MINYAK') || str_contains($cdUpper, 'MSW') || str_contains($cdUpper, 'MKL')) {
                    $calcMinyakNilai += $subtotal;
                } elseif (str_contains($nmUpper, 'KARTON') || str_contains($nmUpper, 'PLASTIK') || str_contains($nmUpper, 'LAKBAN') || str_contains($nmUpper, 'RAFIA') || str_contains($nmUpper, 'SARUNG')) {
                    $calcKemasanNilai += $subtotal;
                } else {
                    // Seluruh bumbu, garam, MSG, perenyah, maupun bahan penolong olahan lainnya
                    $calcBumbuNilai += $subtotal;
                    $calcBumbuQty += $qty;
                }

                $pemakaianItems[] = [
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

        // Kalkulasi nilai bahan: Prioritaskan akumulasi riil dari BPPB jika dokumen BPPB terhubung
        $singkongNilai = $calcSingkongNilai > 0 ? $calcSingkongNilai : (float) $prod->singkong_nilai;
        $minyakNilai = $calcMinyakNilai > 0 ? $calcMinyakNilai : (float) $prod->minyak_nilai;
        $bumbuNilai = $calcBumbuNilai > 0 ? $calcBumbuNilai : (float) $prod->bumbu_nilai;
        $bumbuQty = $calcBumbuQty > 0 ? $calcBumbuQty : (float) $prod->bumbu_qty;

        // Utilitas Gas CNG (Gunakan Kurs Terkini Bank Indonesia)
        $cngMmbtu = (float) $prod->cng_mmbtu;
        $cngTarifUsd = (float) ($prod->cng_tarif_usd > 0 ? $prod->cng_tarif_usd : 14.50);
        $latestRecord = \App\Models\Produksi\DatKursHarian::active()->orderByDesc('tanggal')->first();
        $latestJisdor = $latestRecord ? (float) $latestRecord->kurs_tengah : app(\App\Services\Produksi\TarifProduksiService::class)->getJisdorRate(null, true);
        $cngKursJisdor = (float) ($prod->cng_nilai > 0 && $prod->cng_kurs_jisdor > 0 ? $prod->cng_kurs_jisdor : ($latestJisdor ?: 18103.00));
        $cngTarif = (float) ($prod->cng_nilai > 0 && $prod->cng_tarif > 0 ? $prod->cng_tarif : round($cngTarifUsd * $cngKursJisdor, 2));
        $cngNilai = (float) ($prod->cng_nilai > 0 ? $prod->cng_nilai : round($cngMmbtu * $cngTarif));

        // Tenaga Kerja (Prioritas: Data Tersimpan SPK -> Modul Manajemen Karyawan / Absensi -> Standar Regu Pabrik)
        $laborData = $this->getLaborDataFromKaryawan($prod->produksi_tgl, $prod->lini_produksi);
        if ($laborData['is_synced'] && ($laborData['tk_jumlah_org'] > 0 || $laborData['tk_total_nilai'] > 0)) {
            $tkLangsungOrg = (int) ($prod->tk_langsung_org ?: ($laborData['tk_langsung_org'] ?? 0));
            $tkTidakLangsungOrg = (int) ($prod->tk_tidak_langsung_org ?: ($laborData['tk_tidak_langsung_org'] ?? 0));
            $tkLangsungNilai = (float) ($laborData['tk_langsung_nilai'] ?? 0);
            $tkTidakLangsungNilai = (float) ($laborData['tk_tidak_langsung_nilai'] ?? 0);

            $tkJumlahOrg = (int) ($prod->tk_jumlah_org ?: ($tkLangsungOrg + $tkTidakLangsungOrg));
            $tkTotalNilai = (float) ($prod->tk_total_nilai > 0 ? $prod->tk_total_nilai : ($tkLangsungNilai + $tkTidakLangsungNilai));
            $tkTarifPerOrg = (float) ($prod->tk_tarif_per_org > 0 ? $prod->tk_tarif_per_org : ($tkJumlahOrg > 0 ? round($tkTotalNilai / $tkJumlahOrg) : 0));
        } else {
            $tkLangsungOrg = (int) ($prod->tk_langsung_org ?: ($prod->tk_jumlah_org ?: 0));
            $tkTidakLangsungOrg = (int) ($prod->tk_tidak_langsung_org ?: 0);
            $tkLangsungNilai = (float) ($prod->tk_total_nilai > 0 ? $prod->tk_total_nilai : 0);
            $tkTidakLangsungNilai = 0;
            $tkJumlahOrg = (int) ($prod->tk_jumlah_org ?: ($tkLangsungOrg + $tkTidakLangsungOrg));
            $tkTotalNilai = (float) ($prod->tk_total_nilai > 0 ? $prod->tk_total_nilai : 0);
            $tkTarifPerOrg = (float) ($prod->tk_tarif_per_org > 0 ? $prod->tk_tarif_per_org : 0);
        }

        $dayNamesIndo = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $tglCarbon = Carbon::parse($prod->produksi_tgl);
        $hariIndo = $dayNamesIndo[$tglCarbon->dayOfWeek] ?? 'Senin';

        return [
            'produksi_id'          => $prod->produksi_id,
            'produksi_no'          => $prod->produksi_no,
            'produksi_tgl'         => $prod->produksi_tgl,
            'produksi_tgl_fmt'     => $tglCarbon->format('d/m/Y'),
            'hari_nm'              => $hariIndo,
            'shift_cd'             => $prod->shift_cd,
            'lini_produksi'        => $prod->lini_produksi,
            'gudang_id'            => $prod->gudang_id,
            'gudang_nm'            => $prod->gudang?->gudang_nm ?? 'Gudang Utama',
            'pakai_id'             => $prod->pakai_id,
            'pakai_no'             => $prod->pemakaianBahan?->pakai_no ?? '-',
            'pemakaian_bppb'       => $prod->pemakaianBahan ? [
                'pakai_no'    => $prod->pemakaianBahan->pakai_no,
                'pakai_tgl'   => Carbon::parse($prod->pemakaianBahan->pakai_tgl)->format('d/m/Y'),
                'gudang_nm'   => $prod->pemakaianBahan->gudang?->gudang_nm ?? 'Gudang Bahan Baku',
                'total_nilai' => $totalPemakaianNilai,
            ] : null,
            'pemakaian_items'      => $pemakaianItems,
            'varietas_singkong'    => $prod->varietas_singkong,
            'qty_karton'           => $prod->qty_karton,
            'batch_wip_no'         => $prod->batch_wip_no,

            // Kuantitas Fisik Bahan (dari BPPB)
            'singkong_qty'         => (float) $prod->singkong_qty,
            'singkong_nilai'       => $singkongNilai,
            'minyak_sawit_qty'     => (float) $prod->minyak_sawit_qty,
            'minyak_kelapa_qty'    => (float) $prod->minyak_kelapa_qty,
            'minyak_nilai'         => $minyakNilai,
            'bumbu_qty'            => $bumbuQty,
            'bumbu_nilai'          => $bumbuNilai,

            // Kemasan
            'karton_baru_qty'      => (float) $prod->karton_baru_qty,
            'karton_baru_nilai'    => (float) $prod->karton_baru_nilai,
            'karton_bekas_qty'     => (float) $prod->karton_bekas_qty,
            'karton_bekas_nilai'   => (float) $prod->karton_bekas_nilai,
            'plastik_hd_qty'       => (float) $prod->plastik_hd_qty,
            'plastik_hd_nilai'     => (float) $prod->plastik_hd_nilai,
            'lakban_besar_qty'     => (float) $prod->lakban_besar_qty,
            'lakban_besar_nilai'   => (float) $prod->lakban_besar_nilai,
            'lakban_kecil_qty'     => (float) $prod->lakban_kecil_qty,
            'lakban_kecil_nilai'   => (float) $prod->lakban_kecil_nilai,
            'tali_rafia_qty'       => (float) $prod->tali_rafia_qty,
            'tali_rafia_nilai'     => (float) $prod->tali_rafia_nilai,

            // Gas CNG
            'cng_mmbtu'            => $cngMmbtu,
            'cng_tarif_usd'        => $cngTarifUsd,
            'cng_kurs_jisdor'      => $cngKursJisdor,
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

            // Metrik Fisik & Hasil
            'total_wip_qty'        => (float) $prod->total_wip_qty,
            'rendemen_persen'      => (float) $prod->rendemen_persen,
            'total_biaya_produksi' => (float) $prod->total_biaya_produksi,
            'hpp_per_kg'           => (float) $prod->hpp_per_kg,
            'outputs'              => $outputs,

            // Dokumen SPK lain pada hari yang sama (Untuk notifikasi shift/lini lain yang belum difinalisasi)
            'same_day_spks'        => DatProduksiHdr::whereDate('produksi_tgl', $prod->produksi_tgl)
                ->where('deleted_st', false)
                ->orderBy('shift_cd', 'asc')
                ->orderBy('produksi_id', 'asc')
                ->get()
                ->map(function ($other) use ($prod) {
                    $isFinal = ((float)$other->cng_nilai > 0 || (float)$other->tk_total_nilai > 0 || str_contains($other->catatan_txt ?? '', '[HPP FINAL') || str_contains($other->catatan_txt ?? '', 'HPP Finance'));
                    return [
                        'produksi_id'   => $other->produksi_id,
                        'produksi_no'   => $other->produksi_no,
                        'shift_cd'      => $other->shift_cd ?? 'A',
                        'lini_produksi' => $other->lini_produksi ?? 'Lini Manual',
                        'total_wip_qty' => (float) ($other->total_wip_qty ?: ($other->outputs ? $other->outputs->sum('qty_kg') : 0)),
                        'hpp_per_kg'    => (float) $other->hpp_per_kg,
                        'is_current'    => ($other->produksi_id === $prod->produksi_id),
                        'is_hpp_final'  => $isFinal,
                    ];
                })->values()->toArray(),
        ];
    }

    /**
     * Simpan Lembar Kalkulasi HPP Produksi Khusus Bagian Keuangan / Costing (Tahap 3).
     */
    public function saveHppCalculation(int $produksiId, array $data, string $userId): DatProduksiHdr
    {
        return DB::transaction(function () use ($produksiId, $data, $userId) {
            $produksi = DatProduksiHdr::with(['outputs.barang', 'gudang'])->findOrFail($produksiId);

            // Update biaya bahan baku & kemasan jika disesuaikan Finance
            if (isset($data['singkong_nilai'])) $produksi->singkong_nilai = (float) $data['singkong_nilai'];
            if (isset($data['minyak_nilai'])) $produksi->minyak_nilai = (float) $data['minyak_nilai'];
            if (isset($data['bumbu_nilai'])) $produksi->bumbu_nilai = (float) $data['bumbu_nilai'];
            if (isset($data['karton_baru_nilai'])) $produksi->karton_baru_nilai = (float) $data['karton_baru_nilai'];
            if (isset($data['karton_bekas_nilai'])) $produksi->karton_bekas_nilai = (float) $data['karton_bekas_nilai'];
            if (isset($data['plastik_hd_nilai'])) $produksi->plastik_hd_nilai = (float) $data['plastik_hd_nilai'];
            if (isset($data['lakban_besar_nilai'])) $produksi->lakban_besar_nilai = (float) $data['lakban_besar_nilai'];
            if (isset($data['lakban_kecil_nilai'])) $produksi->lakban_kecil_nilai = (float) $data['lakban_kecil_nilai'];
            if (isset($data['tali_rafia_nilai'])) $produksi->tali_rafia_nilai = (float) $data['tali_rafia_nilai'];

            // Update Gas CNG
            if (isset($data['cng_mmbtu'])) $produksi->cng_mmbtu = (float) $data['cng_mmbtu'];
            if (isset($data['cng_tarif_usd'])) $produksi->cng_tarif_usd = (float) $data['cng_tarif_usd'];
            if (isset($data['cng_kurs_jisdor'])) $produksi->cng_kurs_jisdor = (float) $data['cng_kurs_jisdor'];
            if (isset($data['cng_tarif'])) $produksi->cng_tarif = (float) $data['cng_tarif'];
            if (isset($data['cng_nilai'])) $produksi->cng_nilai = (float) $data['cng_nilai'];

            // Update Tenaga Kerja
            if (isset($data['tk_langsung_org'])) $produksi->tk_langsung_org = (int) $data['tk_langsung_org'];
            if (isset($data['tk_tidak_langsung_org'])) $produksi->tk_tidak_langsung_org = (int) $data['tk_tidak_langsung_org'];
            if (isset($data['tk_jumlah_org'])) {
                $produksi->tk_jumlah_org = (int) $data['tk_jumlah_org'];
            } elseif (isset($data['tk_langsung_org']) || isset($data['tk_tidak_langsung_org'])) {
                $produksi->tk_jumlah_org = (int) (($data['tk_langsung_org'] ?? 0) + ($data['tk_tidak_langsung_org'] ?? 0));
            }
            if (isset($data['tk_tarif_per_org'])) $produksi->tk_tarif_per_org = (float) $data['tk_tarif_per_org'];
            if (isset($data['tk_total_nilai'])) $produksi->tk_total_nilai = (float) $data['tk_total_nilai'];

            // Subtotal Bahan
            $produksi->total_bahan_nilai = (float) $produksi->singkong_nilai +
                (float) $produksi->minyak_nilai +
                (float) $produksi->bumbu_nilai +
                (float) $produksi->karton_baru_nilai +
                (float) $produksi->karton_bekas_nilai +
                (float) $produksi->plastik_hd_nilai +
                (float) $produksi->lakban_besar_nilai +
                (float) $produksi->lakban_kecil_nilai +
                (float) $produksi->tali_rafia_nilai;

            // Total Biaya Produksi: Bahan + CNG + TK + FOH
            $newTotalBiaya = round(
                (float) $produksi->total_bahan_nilai +
                (float) $produksi->cng_nilai +
                (float) $produksi->tk_total_nilai +
                (float) ($produksi->total_overhead_nilai ?? 0),
                2
            );
            $produksi->total_biaya_produksi = $newTotalBiaya;

            // Rendemen Singkong (%) = (Total WIP Qty / Singkong Qty) * 100%
            $effectiveSingkong = (float) $produksi->singkong_qty;
            $produksi->rendemen_persen = $effectiveSingkong > 0 
                ? round(($produksi->total_wip_qty / $effectiveSingkong) * 100, 2) 
                : 0;

            // HPP per Kg = Total Biaya / Total WIP Qty
            $effectiveWip = (float) $produksi->total_wip_qty;
            if ($effectiveWip <= 0 && $produksi->outputs && $produksi->outputs->isNotEmpty()) {
                $effectiveWip = (float) $produksi->outputs->sum('qty_kg');
            }
            $newHppPerKg = $effectiveWip > 0 ? round($newTotalBiaya / $effectiveWip, 2) : 0;
            $produksi->hpp_per_kg = $newHppPerKg;

            // Catatan Keuangan / Finance & Audit Penetapan HPP
            $auditFinanceTag = "[HPP FINAL " . date('d/m/Y H:i') . " oleh " . $userId . "]";
            if (!empty($data['catatan_hpp'])) {
                $prefix = $auditFinanceTag . ": ";
                $produksi->catatan_txt = trim(($produksi->catatan_txt ? $produksi->catatan_txt . "\n" : '') . $prefix . $data['catatan_hpp']);
            } else {
                $produksi->catatan_txt = trim(($produksi->catatan_txt ? $produksi->catatan_txt . "\n" : '') . $auditFinanceTag);
            }

            $produksi->updated_by = $userId;
            $produksi->version_no = ((int) ($produksi->version_no ?? 1)) + 1;
            $produksi->save();

            // Alokasi HPP per Output Barang
            if ($produksi->outputs && $produksi->outputs->isNotEmpty()) {
                $customOutputPrices = $data['output_hpp'] ?? []; // [output_id => hpp_satuan]
                foreach ($produksi->outputs as $out) {
                    $outId = $out->output_id;
                    $itemHpp = isset($customOutputPrices[$outId]) && is_numeric($customOutputPrices[$outId])
                        ? (float) $customOutputPrices[$outId] 
                        : $newHppPerKg;
                    $out->hpp_satuan = $itemHpp;
                    $out->total_nilai = round((float) $out->qty_kg * $itemHpp, 2);
                    $out->updated_by = $userId;
                    $out->version_no = ((int) ($out->version_no ?? 1)) + 1;
                    $out->save();

                    // Update harga satuan di kartu stok batch
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

            return $produksi;
        });
    }
}

