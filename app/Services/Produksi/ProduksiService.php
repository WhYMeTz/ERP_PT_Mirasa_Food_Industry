<?php

namespace App\Services\Produksi;

use App\Models\Gudang\DatPakaiHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\Produksi\DatProduksiDtl;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProduksiService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator,
        protected StokService $stokService
    ) {}

    /**
     * Mengambil laporan bulanan HPP Harian & Rendemen (Format Excel Asli PT Mirasa).
     */
    public function getMonthlyReport(int $year, int $month): array
    {
        $records = DatProduksiHdr::with(['pemakaianBahan', 'gudang', 'outputs.barang'])
            ->whereYear('produksi_tgl', $year)
            ->whereMonth('produksi_tgl', $month)
            ->where('deleted_st', false)
            ->orderBy('produksi_tgl', 'asc')
            ->orderBy('shift_cd', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Hitung Grand Total Kumulatif Bulanan
        $totalWipSum = 0;
        foreach ($records as $r) {
            $w = (float) $r->total_wip_qty;
            if ($w <= 0 && $r->outputs && $r->outputs->isNotEmpty()) {
                $w = (float) $r->outputs->sum('qty_kg');
            }
            $totalWipSum += $w;
        }

        $totals = [
            'total_karton'                => (int) $records->sum('qty_karton'),
            'singkong_qty'                => (float) $records->sum('singkong_qty'),
            'singkong_nilai'              => (float) $records->sum('singkong_nilai'),
            'minyak_sawit_qty'            => (float) $records->sum('minyak_sawit_qty'),
            'minyak_kelapa_qty'           => (float) $records->sum('minyak_kelapa_qty'),
            'minyak_nilai'                => (float) $records->sum('minyak_nilai'),
            'cng_mmbtu'                   => (float) $records->sum('cng_mmbtu'),
            'cng_nilai'                   => (float) $records->sum('cng_nilai'),
            'tk_jumlah_org'               => (int) $records->sum(function ($r) {
                return $r->tk_jumlah_org ?: ($r->tk_langsung_org + $r->tk_tidak_langsung_org + $r->tk_training_org);
            }),
            'tk_langsung_org'             => (int) $records->sum('tk_langsung_org'),
            'tk_tidak_langsung_org'       => (int) $records->sum('tk_tidak_langsung_org'),
            'tk_training_org'             => (int) $records->sum('tk_training_org'),
            'tk_total_nilai'              => (float) $records->sum('tk_total_nilai'),
            'bumbu_nilai'                 => (float) $records->sum('bumbu_nilai'),
            'karton_baru_nilai'           => (float) $records->sum('karton_baru_nilai'),
            'karton_bekas_nilai'          => (float) $records->sum('karton_bekas_nilai'),
            'plastik_hd_nilai'            => (float) $records->sum('plastik_hd_nilai'),
            'lakban_besar_nilai'          => (float) $records->sum('lakban_besar_nilai'),
            'lakban_kecil_nilai'          => (float) $records->sum('lakban_kecil_nilai'),
            'tali_rafia_nilai'            => (float) $records->sum('tali_rafia_nilai'),
            'fotocopy_nilai'              => (float) $records->sum('fotocopy_nilai'),
            'sarung_tangan_plastik_nilai' => (float) $records->sum('sarung_tangan_plastik_nilai'),
            'sarung_tangan_kain_nilai'    => (float) $records->sum('sarung_tangan_kain_nilai'),
            'qc_pengawasan_nilai'         => (float) $records->sum('qc_pengawasan_nilai'),
            'listrik_air_telp_nilai'      => (float) $records->sum('listrik_air_telp_nilai'),
            'pemeliharaan_mesin_nilai'    => (float) $records->sum('pemeliharaan_mesin_nilai'),
            'penyusutan_mesin_nilai'      => (float) $records->sum('penyusutan_mesin_nilai'),
            'limbah_padat_nilai'          => (float) $records->sum('limbah_padat_nilai'),
            'limbah_kimia_nilai'          => (float) $records->sum('limbah_kimia_nilai'),
            'total_biaya_produksi'        => (float) $records->sum('total_biaya_produksi'),

            // Hasil Output WIP (Kg) Sesuai Format Asli Excel
            'ifl_qty'                     => (float) $records->sum('ifl_qty'),
            'asin_barco_qty'              => (float) $records->sum('asin_barco_qty'),
            'asin_sawit_qty'              => (float) $records->sum('asin_sawit_qty'),
            'no_salt_qty'                 => (float) $records->sum('no_salt_qty'),
            'ucamp_qty'                   => (float) $records->sum('ucamp_qty'),
            'balo_gelombang_qty'          => (float) $records->sum('balo_gelombang_qty'),
            'balqi_qty'                   => (float) $records->sum('balqi_qty'),
            'berko_qty'                   => (float) $records->sum('berko_qty'),
            'berko_me_qty'                => (float) $records->sum('berko_me_qty'),
            'total_berko_qty'             => (float) $records->sum('total_berko_qty'),
            'total_wip_qty'               => (float) $totalWipSum,
        ];

        // Rasio & Rata-rata Tertimbang (Weighted Averages)
        $totals['minyak_rasio_persen'] = $totals['singkong_qty'] > 0
            ? (($totals['minyak_sawit_qty'] + $totals['minyak_kelapa_qty']) / $totals['singkong_qty']) * 100
            : 0;

        $totals['berko_persen'] = $totals['total_wip_qty'] > 0
            ? ($totals['total_berko_qty'] / $totals['total_wip_qty']) * 100
            : 0;

        $totals['rendemen_persen'] = $totals['singkong_qty'] > 0
            ? ($totals['total_wip_qty'] / $totals['singkong_qty']) * 100
            : 0;

        $totals['hpp_per_kg'] = $totals['total_wip_qty'] > 0
            ? ($totals['total_biaya_produksi'] / $totals['total_wip_qty'])
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
                $dayKartonBaru = (float) $dayRecords->sum('karton_baru_nilai');
                $dayKartonBekas = (float) $dayRecords->sum('karton_bekas_nilai');
                $dayPlastikHd = (float) $dayRecords->sum('plastik_hd_nilai');
                $dayLakbanBesar = (float) $dayRecords->sum('lakban_besar_nilai');
                $dayLakbanKecil = (float) $dayRecords->sum('lakban_kecil_nilai');
                $dayTaliRafia = (float) $dayRecords->sum('tali_rafia_nilai');

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
                $dayRendemen = $daySingkongQty > 0 && $dayTotalWip > 0 ? round(($dayTotalWip / $daySingkongQty) * 100, 2) : 0;
                $dayHpp = $dayTotalWip > 0 && $dayTotalBiaya > 0 ? round($dayTotalBiaya / $dayTotalWip, 2) : 0;

                // Shift Metadata
                $shiftCodes = $dayRecords->pluck('shift_cd')->filter()->unique()->values()->all();
                $shiftCodeStr = !empty($shiftCodes) ? implode(' + ', $shiftCodes) : 'A';
                $shiftCount = $dayRecords->count();
                $firstRec = $dayRecords->first();

                $days[] = [
                    'day'                         => $day,
                    'date'                        => $dateObj->format('Y-m-d'),
                    'hari_nm'                     => $hariNm,
                    'has_data'                    => true,
                    'produksi_id'                 => $firstRec->produksi_id,
                    'produksi_ids'                => $dayRecords->pluck('produksi_id')->toArray(),
                    'produksi_no'                 => $shiftCount > 1 ? ($firstRec->produksi_no . " (+{$shiftCount} Shift)") : $firstRec->produksi_no,
                    'shift_cd'                    => $shiftCodeStr,
                    'shift_count'                 => $shiftCount,
                    'shift_records'               => $dayRecords->map(fn($r) => [
                        'produksi_id'  => $r->produksi_id,
                        'produksi_no'  => $r->produksi_no,
                        'shift_cd'     => $r->shift_cd ?? 'A',
                        'total_biaya'  => (float) $r->total_biaya_produksi,
                        'total_wip'    => (float) $r->total_wip_qty,
                        'hpp_per_kg'   => (float) $r->hpp_per_kg,
                    ])->toArray(),
                    'batch_wip_no'                => $dayRecords->pluck('batch_wip_no')->filter()->unique()->implode(', ') ?: '-',
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
                    'bumbu_nilai'                 => $dayBumbuNilai,
                    'karton_baru_nilai'           => $dayKartonBaru,
                    'karton_bekas_nilai'          => $dayKartonBekas,
                    'plastik_hd_nilai'            => $dayPlastikHd,
                    'lakban_besar_nilai'          => $dayLakbanBesar,
                    'lakban_kecil_nilai'          => $dayLakbanKecil,
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
                    'total_wip_qty'               => $dayTotalWip,
                    'rendemen_persen'             => $dayRendemen,
                    'hpp_per_kg'                  => $dayHpp,
                ];
            } else {
                $days[] = [
                    'day'                         => $day,
                    'date'                        => $dateObj->format('Y-m-d'),
                    'hari_nm'                     => $hariNm,
                    'has_data'                    => false,
                    'produksi_id'                 => null,
                    'produksi_ids'                => [],
                    'produksi_no'                 => null,
                    'shift_cd'                    => null,
                    'shift_count'                 => 0,
                    'shift_records'               => [],
                    'batch_wip_no'                => null,
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
                    'bumbu_nilai'                 => 0,
                    'karton_baru_nilai'           => 0,
                    'karton_bekas_nilai'          => 0,
                    'plastik_hd_nilai'            => 0,
                    'lakban_besar_nilai'          => 0,
                    'lakban_kecil_nilai'          => 0,
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
                    'total_wip_qty'               => 0,
                    'rendemen_persen'             => 0,
                    'hpp_per_kg'                  => 0,
                ];
            }
        }

        // Sinkronisasi total output WIP dari daftar baris
        $totals['ifl_qty'] = (float) collect($days)->where('has_data', true)->sum('ifl_qty');
        $totals['asin_barco_qty'] = (float) collect($days)->where('has_data', true)->sum('asin_barco_qty');
        $totals['asin_sawit_qty'] = (float) collect($days)->where('has_data', true)->sum('asin_sawit_qty');
        $totals['no_salt_qty'] = (float) collect($days)->where('has_data', true)->sum('no_salt_qty');
        $totals['ucamp_qty'] = (float) collect($days)->where('has_data', true)->sum('ucamp_qty');
        $totals['balo_gelombang_qty'] = (float) collect($days)->where('has_data', true)->sum('balo_gelombang_qty');
        $totals['balqi_qty'] = (float) collect($days)->where('has_data', true)->sum('balqi_qty');
        $totals['berko_qty'] = (float) collect($days)->where('has_data', true)->sum('berko_qty');
        $totals['berko_me_qty'] = (float) collect($days)->where('has_data', true)->sum('berko_me_qty');
        $totals['total_berko_qty'] = (float) collect($days)->where('has_data', true)->sum('total_berko_qty');
        $totals['total_wip_qty'] = (float) collect($days)->where('has_data', true)->sum('total_wip_qty');

        return [
            'year'    => $year,
            'month'   => $month,
            'records' => $records,
            'days'    => $days,
            'totals'  => $totals,
            'count'   => $records->count(),
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
            $singkongQty = (float) $monthRecords->sum('singkong_qty');
            $singkongNilai = (float) $monthRecords->sum('singkong_nilai');
            $totalWipQty = (float) $monthRecords->sum('total_wip_qty');
            $totalBiaya = (float) $monthRecords->sum('total_biaya_produksi');

            $minyakNilai = (float) $monthRecords->sum('minyak_nilai');
            $cngNilai = (float) $monthRecords->sum('cng_nilai');
            $tkNilai = (float) $monthRecords->sum('tk_total_nilai');

            $kemasanNilai = (float) (
                $monthRecords->sum('karton_baru_nilai') +
                $monthRecords->sum('karton_bekas_nilai') +
                $monthRecords->sum('plastik_hd_nilai') +
                $monthRecords->sum('lakban_besar_nilai') +
                $monthRecords->sum('lakban_kecil_nilai') +
                $monthRecords->sum('tali_rafia_nilai')
            );

            $fohNilai = (float) (
                $monthRecords->sum('sarung_tangan_plastik_nilai') +
                $monthRecords->sum('sarung_tangan_kain_nilai')
            );

            $rendemen = $singkongQty > 0 ? ($totalWipQty / $singkongQty) * 100 : 0;
            $hppPerKg = $totalWipQty > 0 ? ($totalBiaya / $totalWipQty) : 0;

            $months[$m] = [
                'month_num'      => $m,
                'month_name'     => $monthNames[$m],
                'has_data'       => $count > 0,
                'work_days'      => $count,
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

        // Grand Total Tahunan
        $annualSingkongQty = (float) $allRecords->sum('singkong_qty');
        $annualSingkongNilai = (float) $allRecords->sum('singkong_nilai');
        $annualWipQty = (float) $allRecords->sum('total_wip_qty');
        $annualBiaya = (float) $allRecords->sum('total_biaya_produksi');
        $annualWorkDays = $allRecords->count();

        $annualRendemen = $annualSingkongQty > 0 ? ($annualWipQty / $annualSingkongQty) * 100 : 0;
        $annualHppPerKg = $annualWipQty > 0 ? ($annualBiaya / $annualWipQty) : 0;

        $annualTotals = [
            'total_work_days' => $annualWorkDays,
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
     * Sinkronisasi data tenaga kerja (kehadiran & total upah) dari modul Karyawan berdasarkan tanggal.
     * Siap diintegrasikan langsung dengan tabel presensi/absensi & penggajian yang sedang dibuat mentor.
     */
    public function getLaborDataFromKaryawan(string $tgl): array
    {
        $parsedDate = Carbon::parse($tgl);
        $tglFormatted = $parsedDate->format('d/m/Y');

        $result = [
            'tgl'          => $tgl,
            'is_synced'    => false,
            'jumlah_orang' => 0,
            'total_upah'   => 0,
            'source'       => 'none',
            'message'      => "Data presensi per {$tglFormatted} belum tersedia.",
        ];

        // Daftar tabel presensi / absensi / payroll potensial dari modul Karyawan
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
                                'tgl'          => $tgl,
                                'is_synced'    => true,
                                'jumlah_orang' => $count,
                                'total_upah'   => $totalUpah,
                                'source'       => $tableName,
                                'message'      => "Data presensi tersinkronisasi ({$count} orang hadir, total Rp " . number_format($totalUpah, 0, ',', '.') . ").",
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    // Graceful fallback jika terjadi exception saat akses tabel dinamis
                }
            }
        }

        return $result;
    }

    /**
     * Ekstrak & kategorisasi ringkasan biaya bahan dari dokumen Pemakaian Bahan (dat_pakai_hdr).
     */
    public function extractPakaiSummary(int $pakaiId): array
    {
        $pakai = DatPakaiHdr::with(['details.barang.jenisBarang'])->where('pakai_id', $pakaiId)->firstOrFail();

        $summary = [
            'pakai_id'           => $pakai->pakai_id,
            'pakai_no'           => $pakai->pakai_no,
            'pakai_tgl'          => $pakai->pakai_tgl ? $pakai->pakai_tgl->format('Y-m-d') : null,
            'tujuan_pemakaian'   => $pakai->tujuan_pemakaian,
            'gudang_id'          => $pakai->gudang_id,
            'singkong_qty'       => 0,
            'singkong_nilai'     => 0,
            'minyak_sawit_qty'   => 0,
            'minyak_kelapa_qty'  => 0,
            'minyak_nilai'       => 0,
            'bumbu_nilai'        => 0,
            'berko_bahan_qty'    => 0,
            'berko_bahan_nilai'  => 0,
            'karton_baru_nilai'  => 0,
            'karton_bekas_nilai' => 0,
            'plastik_hd_nilai'   => 0,
            'lakban_besar_nilai' => 0,
            'lakban_kecil_nilai' => 0,
            'tali_rafia_nilai'   => 0,
            'varietas_singkong'  => 'STP / MGU',
            'karton_estimasi'    => 0,
        ];

        $varietasList = [];
        $kartonCount = 0;
        $items = [];

        foreach ($pakai->details as $dtl) {
            $barang = $dtl->barang;
            if (!$barang) continue;

            $nm = strtoupper($barang->barang_nm);
            $cd = strtoupper($barang->barang_cd);
            $qty = (float) $dtl->qty_keluar;
            $subtotal = (float) ($dtl->total_harga > 0 ? $dtl->total_harga : ($qty * $dtl->harga_satuan));

            // Hitung sisa stok fisik on hand barang tersebut di gudang asal BPPB
            $sisaStok = (float) \App\Models\Gudang\DatStokBatch::where('gudang_id', $pakai->gudang_id)
                ->where('barang_id', $dtl->barang_id)
                ->where('sisa_qty', '>', 0)
                ->sum('sisa_qty');

            $items[] = [
                'pakai_tgl'  => $pakai->pakai_tgl ? $pakai->pakai_tgl->format('d/m/Y') : '-',
                'barang_cd'  => $barang->barang_cd,
                'barang_nm'  => $barang->barang_nm,
                'qty_keluar' => $qty,
                'satuan_cd'  => $dtl->satuan->satuan_cd ?? ($barang->satuanDasar->satuan_cd ?? 'KG'),
                'sisa_stok'  => $sisaStok,
            ];

            if (str_contains($nm, 'BERKO') || str_starts_with($cd, 'WIP-BRK') || str_starts_with($cd, 'WIP-B')) {
                $summary['berko_bahan_qty'] += $qty;
                $summary['berko_bahan_nilai'] += $subtotal;
            } elseif (str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK')) {
                $summary['singkong_qty'] += $qty;
                $summary['singkong_nilai'] += $subtotal;
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
            } elseif (str_contains($nm, 'BUMBU') || str_contains($nm, 'PERENYAH') || str_contains($nm, 'GARAM') || str_contains($nm, 'SEASONING')) {
                $summary['bumbu_nilai'] += $subtotal;
            } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'BOX')) {
                if (str_contains($nm, 'BEKAS')) {
                    $summary['karton_bekas_nilai'] += $subtotal;
                } else {
                    $summary['karton_baru_nilai'] += $subtotal;
                }
                $kartonCount += (int) $qty;
            } elseif (str_contains($nm, 'PLASTIK') || str_contains($nm, 'HD')) {
                $summary['plastik_hd_nilai'] += $subtotal;
            } elseif (str_contains($nm, 'LAKBAN')) {
                if (str_contains($nm, 'KECIL')) {
                    $summary['lakban_kecil_nilai'] += $subtotal;
                } else {
                    $summary['lakban_besar_nilai'] += $subtotal;
                }
            } elseif (str_contains($nm, 'TALI') || str_contains($nm, 'RAFIA')) {
                $summary['tali_rafia_nilai'] += $subtotal;
            }
        }

        if (!empty($varietasList)) {
            $summary['varietas_singkong'] = implode(' / ', array_unique($varietasList));
        }
        $summary['karton_estimasi'] = $kartonCount;
        $summary['items'] = $items;

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

        // Gas CNG
        $cngMmbtu = (float) ($data['cng_mmbtu'] ?? 0);
        $cngTarif = (float) ($data['cng_tarif'] ?? 226800.00);
        $cngNilai = (float) ($data['cng_nilai'] ?? ($cngMmbtu * $cngTarif));

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

        // Overhead Pabrik (FOH) - Khusus Sarung Tangan Sesuai Ketentuan Mentor
        $sarungPlastik = (float) ($data['sarung_tangan_plastik_nilai'] ?? 0);
        $sarungKain = (float) ($data['sarung_tangan_kain_nilai'] ?? 0);
        $fotocopy = 0;
        $qc = 0;
        $listrik = 0;
        $pemeliharaan = 0;
        $penyusutan = 0;
        $limbahPadat = 0;
        $limbahKimia = 0;

        $totalOverheadNilai = $sarungPlastik + $sarungKain;

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

        // FOH hanya Sarung Tangan (Plastik & Kain) yang diinput sesuai nota belanja fisik.
        // Komponen FOH lainnya (QC, Listrik, Pemeliharaan, Penyusutan, Limbah, Fotocopy) ditiadakan.

        $berkoPersen = $totalWipQty > 0 ? ($totalBerkoQty / $totalWipQty) * 100 : 0;
        $rendemenPersen = $singkongQty > 0 ? ($effectiveOutputKg / $singkongQty) * 100 : 0;
        $hppPerKg = $effectiveOutputKg > 0 ? ($totalBiayaProduksi / $effectiveOutputKg) : 0;

        return array_merge($data, [
            'singkong_qty'                => $singkongQty,
            'singkong_nilai'              => $singkongNilai,
            'minyak_sawit_qty'            => $minyakSawitQty,
            'minyak_kelapa_qty'           => $minyakKelapaQty,
            'minyak_nilai'                => $minyakNilai,
            'minyak_rasio_persen'         => round($minyakRasioPersen, 2),
            'bumbu_nilai'                 => $bumbuNilai,
            'karton_baru_nilai'           => $kartonBaruNilai,
            'karton_bekas_nilai'          => $kartonBekasNilai,
            'plastik_hd_nilai'            => $plastikHdNilai,
            'lakban_besar_nilai'          => $lakbanBesarNilai,
            'lakban_kecil_nilai'          => $lakbanKecilNilai,
            'tali_rafia_nilai'            => $taliRafiaNilai,
            'total_bahan_nilai'           => $totalBahanNilai,
            'cng_mmbtu'                   => $cngMmbtu,
            'cng_tarif'                   => $cngTarif,
            'cng_nilai'                   => $cngNilai,
            'tk_jumlah_org'               => $tkJumlah,
            'tk_langsung_org'             => $tkLangsung,
            'tk_tidak_langsung_org'       => $tkTidakLangsung,
            'tk_training_org'             => $tkTraining,
            'tk_tarif_per_org'            => $tkTarif,
            'tk_total_nilai'              => $tkTotalNilai,
            'fotocopy_nilai'              => 0,
            'sarung_tangan_plastik_nilai' => $sarungPlastik,
            'sarung_tangan_kain_nilai'    => $sarungKain,
            'qc_pengawasan_nilai'         => 0,
            'listrik_air_telp_nilai'      => 0,
            'pemeliharaan_mesin_nilai'    => 0,
            'penyusutan_mesin_nilai'      => 0,
            'limbah_padat_nilai'          => 0,
            'limbah_kimia_nilai'          => 0,
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
            'total_wip_qty'               => $totalWipQty,
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
            $data['hari_nm'] = Carbon::parse($tgl)->isoFormat('dddd'); // Friday, Saturday, etc.

            if (empty($data['produksi_no'])) {
                $data['produksi_no'] = $this->codeGenerator->generateProduksiNo($tgl);
            }

            // Normalisasi Shift & Karton
            $lini = strtoupper($data['lini_produksi'] ?? 'PRODUKSI IFL');
            $shift = strtoupper(trim($data['shift_cd'] ?? 'A'));
            $shift = in_array($shift, ['A', 'B']) ? $shift : 'A';
            $noAwal = !empty($data['no_karton_awal']) ? (int) $data['no_karton_awal'] : 1;
            $qtyKarton = !empty($data['qty_karton']) ? (int) $data['qty_karton'] : 0;
            $noAkhir = !empty($data['no_karton_akhir']) ? (int) $data['no_karton_akhir'] : ($qtyKarton > 0 ? ($noAwal + $qtyKarton - 1) : $noAwal);

            $data['shift_cd'] = $shift;
            $data['no_karton_awal'] = $noAwal;
            $data['no_karton_akhir'] = $noAkhir;
            $data['qty_karton'] = $qtyKarton;
            $data['jam_produksi'] = !empty($data['jam_produksi']) ? trim($data['jam_produksi']) : date('H:i');
            $data['varietas_singkong'] = !empty($data['varietas_singkong']) ? trim($data['varietas_singkong']) : 'STP / MGU';

            $isIfm = str_contains(strtoupper($lini), 'IFM') || str_contains(strtoupper($lini), 'IFL');

            // Generate Batch No jika belum ada
            if (empty($data['batch_wip_no'])) {
                if ($isIfm && $qtyKarton > 0) {
                    $padAwal = str_pad((string) $noAwal, 4, '0', STR_PAD_LEFT);
                    $padAkhir = str_pad((string) $noAkhir, 4, '0', STR_PAD_LEFT);
                    $data['batch_wip_no'] = "{$shift}{$padAwal} - {$shift}{$padAkhir}";
                } else {
                    // Barang Jadi Reguler PT Mirasa: Format Tanggal 'd m Y' seperti di buku Excel persediaan (cth: 02 01 2026)
                    $data['batch_wip_no'] = Carbon::parse($tgl)->format('d m Y');
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
                        // - WIP Curah (Berko, Asin, No Salt, dll) → selalu tanggal (07 10 2026)
                        //   sebab produk curah tidak memiliki nomor karton fisik
                        $isCurahWip = in_array($kategori, ['BERKO', 'ASIN_BARCO', 'ASIN_SAWIT', 'NO_SALT', 'BALO_GELOMBANG']);
                        $batchVarian = ($isIfm && !$isCurahWip)
                            ? $produksi->batch_wip_no
                            : Carbon::parse($tgl)->format('d m Y');

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
                foreach ($data['output_items'] as $item) {
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
                        || str_contains($barangCdUpper, 'ASB') || str_contains($barangNmUpper, 'BARCO')
                        || str_contains($barangCdUpper, 'ASW') || str_contains($barangNmUpper, 'SAWIT')
                        || str_contains($barangCdUpper, 'NSL') || str_contains($barangNmUpper, 'NO SALT')
                        || str_contains($barangCdUpper, 'BLQ') || str_contains($barangNmUpper, 'BALO')
                        || ($barangCdUpper !== 'WIP-FCC' && str_starts_with($barangCdUpper, 'WIP-') && !str_contains($barangCdUpper, 'IFL') && !str_contains($barangCdUpper, 'IFM'));

                    $defaultBatch = $isCurahItem ? Carbon::parse($tgl)->format('d m Y') : $produksi->batch_wip_no;
                    $batchItem = (!empty($item['batch_no']) && $item['batch_no'] !== '-') ? $item['batch_no'] : $defaultBatch;

                    $hppItem = isset($item['hpp_satuan']) && (float)$item['hpp_satuan'] > 0 ? (float)$item['hpp_satuan'] : $hppPerKg;

                    $finalQtyHasil = $qtyHasil > 0 ? $qtyHasil : $qtyKg;
                    $finalQtyKg = $qtyKg > 0 ? $qtyKg : $qtyHasil;
                    $subtotalNilai = round($finalQtyKg * $hppItem, 2);

                    DatProduksiDtl::create([
                        'produksi_id'     => $produksi->produksi_id,
                        'barang_id'       => $barang->barang_id,
                        'jenis_cd'        => $jenisCd,
                        'kategori_output' => $barang->barang_cd,
                        'qty_hasil'       => $finalQtyHasil,
                        'satuan_cd'       => $satuanCd,
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
                            $hppItem
                        );
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
        return DatProduksiHdr::with(['pemakaianBahan', 'gudang', 'outputs.barang'])
            ->where('produksi_id', $id)
            ->firstOrFail();
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
                    if ($modeCng === 'update_tarif' && $cngTarifBaru > 0) {
                        $record->cng_tarif = $cngTarifBaru;
                        $record->cng_nilai = round($record->cng_mmbtu * $cngTarifBaru, 2);
                    } elseif ($modeCng === 'total_tagihan') {
                        $record->cng_tarif = $effectiveCngTarif;
                        $record->cng_nilai = round($record->cng_mmbtu * $effectiveCngTarif, 2);
                    }
                }

                // 3. Rekalkulasi Biaya Overhead Pabrik (Hanya Sarung Tangan)
                $record->total_overhead_nilai = (float) $record->sarung_tangan_plastik_nilai +
                    (float) $record->sarung_tangan_kain_nilai;

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
                $record->hpp_per_kg = $effectiveWip > 0
                    ? round($record->total_biaya_produksi / $effectiveWip, 2)
                    : 0;

                // 6. Audit Trail
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
}

