<?php

namespace App\Services\Produksi;

use App\Models\Gudang\DatPakaiHdr;
use App\Models\MasterData\MstBarang;
use App\Models\Produksi\DatProduksiHarian;
use App\Models\Produksi\DatProduksiOutput;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

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
        $records = DatProduksiHarian::with(['pemakaianBahan', 'gudang', 'outputs.barang'])
            ->whereYear('produksi_tgl', $year)
            ->whereMonth('produksi_tgl', $month)
            ->where('deleted_st', false)
            ->orderBy('produksi_tgl', 'asc')
            ->get();

        // Hitung Grand Total Kumulatif Bulanan
        $totals = [
            'singkong_qty'                => (float) $records->sum('singkong_qty'),
            'singkong_nilai'              => (float) $records->sum('singkong_nilai'),
            'minyak_sawit_qty'            => (float) $records->sum('minyak_sawit_qty'),
            'minyak_kelapa_qty'           => (float) $records->sum('minyak_kelapa_qty'),
            'minyak_nilai'                => (float) $records->sum('minyak_nilai'),
            'cng_mmbtu'                   => (float) $records->sum('cng_mmbtu'),
            'cng_nilai'                   => (float) $records->sum('cng_nilai'),
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

            // Hasil Output WIP (Kg)
            'asin_barco_qty'              => (float) $records->sum('asin_barco_qty'),
            'asin_sawit_qty'              => (float) $records->sum('asin_sawit_qty'),
            'no_salt_qty'                 => (float) $records->sum('no_salt_qty'),
            'balo_gelombang_qty'          => (float) $records->sum('balo_gelombang_qty'),
            'berko_qty'                   => (float) $records->sum('berko_qty'),
            'berko_me_qty'                => (float) $records->sum('berko_me_qty'),
            'total_berko_qty'             => (float) $records->sum('total_berko_qty'),
            'total_wip_qty'               => (float) $records->sum('total_wip_qty'),
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

        return [
            'year'    => $year,
            'month'   => $month,
            'records' => $records,
            'totals'  => $totals,
            'count'   => $records->count(),
        ];
    }

    /**
     * Ekstrak & kategorisasi ringkasan biaya bahan dari dokumen Pemakaian Bahan (dat_pakai_hdr).
     */
    public function extractPakaiSummary(int $pakaiId): array
    {
        $pakai = DatPakaiHdr::with(['details.barang.jenisBarang'])->where('pakai_id', $pakaiId)->firstOrFail();

        $summary = [
            'singkong_qty'       => 0,
            'singkong_nilai'     => 0,
            'minyak_sawit_qty'   => 0,
            'minyak_kelapa_qty'  => 0,
            'minyak_nilai'       => 0,
            'bumbu_nilai'        => 0,
            'karton_baru_nilai'  => 0,
            'karton_bekas_nilai' => 0,
            'plastik_hd_nilai'   => 0,
            'lakban_besar_nilai' => 0,
            'lakban_kecil_nilai' => 0,
            'tali_rafia_nilai'   => 0,
        ];

        foreach ($pakai->details as $dtl) {
            $barang = $dtl->barang;
            if (!$barang) continue;

            $nm = strtoupper($barang->barang_nm);
            $cd = strtoupper($barang->barang_cd);
            $qty = (float) $dtl->qty_keluar;
            $subtotal = (float) ($dtl->total_harga > 0 ? $dtl->total_harga : ($qty * $dtl->harga_satuan));

            if (str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK')) {
                $summary['singkong_qty'] += $qty;
                $summary['singkong_nilai'] += $subtotal;
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

        $totalBahanNilai = $singkongNilai + $minyakNilai + $bumbuNilai + $kartonBaruNilai +
            $kartonBekasNilai + $plastikHdNilai + $lakbanBesarNilai + $lakbanKecilNilai + $taliRafiaNilai;

        // Gas CNG
        $cngMmbtu = (float) ($data['cng_mmbtu'] ?? 0);
        $cngTarif = (float) ($data['cng_tarif'] ?? 0);
        $cngNilai = (float) ($data['cng_nilai'] ?? ($cngMmbtu * $cngTarif));

        // Tenaga Kerja
        $tkLangsung = (int) ($data['tk_langsung_org'] ?? 0);
        $tkTidakLangsung = (int) ($data['tk_tidak_langsung_org'] ?? 0);
        $tkTraining = (int) ($data['tk_training_org'] ?? 0);
        $tkTarif = (float) ($data['tk_tarif_per_org'] ?? 91300.00);
        $tkTotalNilai = isset($data['tk_total_nilai']) && (float) $data['tk_total_nilai'] > 0
            ? (float) $data['tk_total_nilai']
            : (($tkLangsung + $tkTidakLangsung + $tkTraining) * $tkTarif);

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

        // Output WIP
        $asinBarcoQty = (float) ($data['asin_barco_qty'] ?? 0);
        $asinSawitQty = (float) ($data['asin_sawit_qty'] ?? 0);
        $noSaltQty = (float) ($data['no_salt_qty'] ?? 0);
        $baloQty = (float) ($data['balo_gelombang_qty'] ?? 0);
        $berkoQty = (float) ($data['berko_qty'] ?? 0);
        $berkoMeQty = (float) ($data['berko_me_qty'] ?? 0);
        $totalBerkoQty = $berkoQty + $berkoMeQty;

        $totalWipQty = $asinBarcoQty + $asinSawitQty + $noSaltQty + $baloQty + $totalBerkoQty;

        $berkoPersen = $totalWipQty > 0 ? ($totalBerkoQty / $totalWipQty) * 100 : 0;
        $rendemenPersen = $singkongQty > 0 ? ($totalWipQty / $singkongQty) * 100 : 0;
        $hppPerKg = $totalWipQty > 0 ? ($totalBiayaProduksi / $totalWipQty) : 0;

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
            'tk_langsung_org'             => $tkLangsung,
            'tk_tidak_langsung_org'       => $tkTidakLangsung,
            'tk_training_org'             => $tkTraining,
            'tk_tarif_per_org'            => $tkTarif,
            'tk_total_nilai'              => $tkTotalNilai,
            'fotocopy_nilai'              => $fotocopy,
            'sarung_tangan_plastik_nilai' => $sarungPlastik,
            'sarung_tangan_kain_nilai'    => $sarungKain,
            'qc_pengawasan_nilai'         => $qc,
            'listrik_air_telp_nilai'      => $listrik,
            'pemeliharaan_mesin_nilai'    => $pemeliharaan,
            'penyusutan_mesin_nilai'      => $penyusutan,
            'limbah_padat_nilai'          => $limbahPadat,
            'limbah_kimia_nilai'          => $limbahKimia,
            'total_overhead_nilai'        => $totalOverheadNilai,
            'total_biaya_produksi'        => $totalBiayaProduksi,
            'asin_barco_qty'              => $asinBarcoQty,
            'asin_sawit_qty'              => $asinSawitQty,
            'no_salt_qty'                 => $noSaltQty,
            'balo_gelombang_qty'          => $baloQty,
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
    public function store(array $data): DatProduksiHarian
    {
        return DB::transaction(function () use ($data) {
            $tgl = $data['produksi_tgl'] ?? date('Y-m-d');
            $data['hari_nm'] = Carbon::parse($tgl)->isoFormat('dddd'); // Friday, Saturday, etc.

            if (empty($data['produksi_no'])) {
                $data['produksi_no'] = $this->codeGenerator->generateProduksiNo($tgl);
            }

            if (empty($data['batch_wip_no'])) {
                $data['batch_wip_no'] = $this->codeGenerator->generateWipBatchNo(null, $tgl);
            }

            // Hitung semua turunan biaya, rendemen, dan HPP/kg
            $calculated = $this->calculateFields($data);

            // Simpan Header Produksi Harian
            $produksi = DatProduksiHarian::create($calculated);

            // Mapping Master Barang WIP
            $wipMap = [
                'ASIN_BARCO'      => ['code' => 'WIP-ASB', 'qty' => (float) $produksi->asin_barco_qty],
                'ASIN_SAWIT'      => ['code' => 'WIP-ASW', 'qty' => (float) $produksi->asin_sawit_qty],
                'NO_SALT'         => ['code' => 'WIP-NSL', 'qty' => (float) $produksi->no_salt_qty],
                'BALO_GELOMBANG'  => ['code' => 'WIP-BLQ', 'qty' => (float) $produksi->balo_gelombang_qty],
                'BERKO'           => ['code' => 'WIP-BRK', 'qty' => (float) $produksi->total_berko_qty],
            ];

            $gudangId = (int) $produksi->gudang_id;
            $hppPerKg = (float) $produksi->hpp_per_kg;

            foreach ($wipMap as $kategori => $info) {
                if ($info['qty'] <= 0) continue;

                $barang = MstBarang::where('barang_cd', $info['code'])->first();
                if (!$barang) {
                    $barang = MstBarang::where('barang_nm', 'LIKE', "%{$info['code']}%")->first();
                }

                if ($barang) {
                    $batchVarian = $produksi->batch_wip_no . '-' . substr($info['code'], 4);
                    $subtotalNilai = round($info['qty'] * $hppPerKg, 2);

                    DatProduksiOutput::create([
                        'produksi_id'     => $produksi->produksi_id,
                        'barang_id'       => $barang->barang_id,
                        'kategori_output' => $kategori,
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
                            Carbon::parse($tgl)->addMonths(6)->toDateString(), // Expired date default 6 bulan
                            $produksi->produksi_no,
                            "Hasil Produksi Harian {$produksi->produksi_no} ({$kategori})",
                            $hppPerKg
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
    public function getById(int $id): DatProduksiHarian
    {
        return DatProduksiHarian::with(['pemakaianBahan', 'gudang', 'outputs.barang'])
            ->where('produksi_id', $id)
            ->firstOrFail();
    }

    /**
     * Hapus data produksi harian (Soft Delete).
     */
    public function delete(int $id): bool
    {
        $produksi = $this->getById($id);
        return $produksi->delete();
    }
}
