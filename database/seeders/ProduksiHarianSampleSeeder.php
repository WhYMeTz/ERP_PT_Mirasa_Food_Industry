<?php

namespace Database\Seeders;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\Produksi\DatProduksiDtl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProduksiHarianSampleSeeder extends Seeder
{
    /**
     * Seed sample production data from PT Mirasa January 2026 Excel sheet.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Ambil Gudang Produksi / Transit
            $gudang = MstGudang::first();
            $gudangId = $gudang ? $gudang->gudang_id : 1;

            // Ambil Master Barang WIP
            $barangBarco = MstBarang::where('barang_cd', 'WIP-ASB')->first();
            $barangSawit = MstBarang::where('barang_cd', 'WIP-ASW')->first();
            $barangBerko = MstBarang::where('barang_cd', 'WIP-BRK')->first();

            // DATA 1: JUMAT, 02/01/2026
            $prod1 = DatProduksiHdr::updateOrCreate(
                ['produksi_no' => 'PRD-20260102-0001'],
                [
                    'produksi_tgl'                => '2026-01-02',
                    'hari_nm'                     => 'Friday',
                    'gudang_id'                   => $gudangId,
                    'lini_produksi'               => 'PRODUKSI IFM',
                    'status_cd'                   => 'POSTED',

                    'singkong_qty'                => 56710.0000,
                    'singkong_nilai'              => 110419960.00,
                    'minyak_sawit_qty'            => 5095.0000,
                    'minyak_kelapa_qty'           => 90.0000,
                    'minyak_nilai'                => 85668098.00,
                    'minyak_rasio_persen'         => 26.34,

                    'bumbu_nilai'                 => 1441886.00,
                    'karton_baru_nilai'           => 2176517.00,
                    'karton_bekas_nilai'          => 4772604.00,
                    'plastik_hd_nilai'            => 2359998.00,
                    'lakban_besar_nilai'          => 86065.00,
                    'lakban_kecil_nilai'          => 165258.00,
                    'tali_rafia_nilai'            => 66310.00,
                    'total_bahan_nilai'           => 207156696.00,

                    'cng_mmbtu'                   => 191.1160,
                    'cng_tarif'                   => 226799.98,
                    'cng_nilai'                   => 43345109.00,

                    'tk_langsung_org'             => 42,
                    'tk_tidak_langsung_org'       => 233,
                    'tk_training_org'             => 0,
                    'tk_tarif_per_org'            => 91300.00,
                    'tk_total_nilai'              => 25107500.00,

                    'fotocopy_nilai'              => 166824.00,
                    'sarung_tangan_plastik_nilai' => 36001.00,
                    'sarung_tangan_kain_nilai'    => 0.00,
                    'qc_pengawasan_nilai'         => 983531.00,
                    'listrik_air_telp_nilai'      => 4404928.00,
                    'pemeliharaan_mesin_nilai'    => 459388.00,
                    'penyusutan_mesin_nilai'      => 1307701.00,
                    'limbah_padat_nilai'          => 360000.00,
                    'limbah_kimia_nilai'          => 887481.00,
                    'total_overhead_nilai'        => 8605852.00,

                    'total_biaya_produksi'        => 284215157.00,

                    'asin_barco_qty'              => 17874.0000,
                    'asin_sawit_qty'              => 777.0000,
                    'no_salt_qty'                 => 0.0000,
                    'balo_gelombang_qty'          => 0.0000,
                    'berko_qty'                   => 988.6200,
                    'berko_me_qty'                => 42.8100,
                    'total_berko_qty'             => 1031.4300,
                    'berko_persen'                => 5.24,
                    'total_wip_qty'               => 19682.4300,

                    'rendemen_persen'             => 34.71,
                    'hpp_per_kg'                  => 14440.04,
                    'batch_wip_no'                => 'WIP-020126-01',
                    'catatan_txt'                 => 'Produksi Keripik Singkong IFM. Rendemen bagus 34.71% di atas target 33%.',
                    'created_by'                  => 'SYSTEM',
                ]
            );

            // DATA 2: SABTU, 03/01/2026
            $prod2 = DatProduksiHdr::updateOrCreate(
                ['produksi_no' => 'PRD-20260103-0001'],
                [
                    'produksi_tgl'                => '2026-01-03',
                    'hari_nm'                     => 'Saturday',
                    'gudang_id'                   => $gudangId,
                    'lini_produksi'               => 'PRODUKSI IFM',
                    'status_cd'                   => 'POSTED',

                    'singkong_qty'                => 61075.0000,
                    'singkong_nilai'              => 83322650.00,
                    'minyak_sawit_qty'            => 5235.0000,
                    'minyak_kelapa_qty'           => 108.0000,
                    'minyak_nilai'                => 88822809.00,
                    'minyak_rasio_persen'         => 25.38,

                    'bumbu_nilai'                 => 873870.00,
                    'karton_baru_nilai'           => 2622310.00,
                    'karton_bekas_nilai'          => 4868755.00,
                    'plastik_hd_nilai'            => 2486082.00,
                    'lakban_besar_nilai'          => 200818.00,
                    'lakban_kecil_nilai'          => 210846.00,
                    'tali_rafia_nilai'            => 59090.00,
                    'total_bahan_nilai'           => 183467230.00,

                    'cng_mmbtu'                   => 217.8890,
                    'cng_tarif'                   => 226799.98,
                    'cng_nilai'                   => 49417225.00,

                    'tk_langsung_org'             => 39,
                    'tk_tidak_langsung_org'       => 238,
                    'tk_training_org'             => 0,
                    'tk_tarif_per_org'            => 91300.00,
                    'tk_total_nilai'              => 25290100.00,

                    'fotocopy_nilai'              => 172760.00,
                    'sarung_tangan_plastik_nilai' => 18000.00,
                    'sarung_tangan_kain_nilai'    => 0.00,
                    'qc_pengawasan_nilai'         => 1052154.00,
                    'listrik_air_telp_nilai'      => 4712268.00,
                    'pemeliharaan_mesin_nilai'    => 491440.00,
                    'penyusutan_mesin_nilai'      => 1398941.00,
                    'limbah_padat_nilai'          => 360000.00,
                    'limbah_kimia_nilai'          => 949402.00,
                    'total_overhead_nilai'        => 9154965.00,

                    'total_biaya_produksi'        => 267329521.00,

                    'asin_barco_qty'              => 18510.0000,
                    'asin_sawit_qty'              => 1568.0000,
                    'no_salt_qty'                 => 0.0000,
                    'balo_gelombang_qty'          => 0.0000,
                    'berko_qty'                   => 908.1500,
                    'berko_me_qty'                => 69.5600,
                    'total_berko_qty'             => 977.7100,
                    'berko_persen'                => 4.64,
                    'total_wip_qty'               => 21055.7100,

                    'rendemen_persen'             => 34.48,
                    'hpp_per_kg'                  => 12696.30,
                    'batch_wip_no'                => 'WIP-030126-01',
                    'catatan_txt'                 => 'Produksi Keripik Singkong IFM. Rendemen optimal 34.48%.',
                    'created_by'                  => 'SYSTEM',
                ]
            );

            // Simpan detail output jika barang terdaftar
            if ($barangBarco) {
                DatProduksiDtl::updateOrCreate(
                    ['produksi_id' => $prod1->produksi_id, 'kategori_output' => 'ASIN_BARCO'],
                    ['barang_id' => $barangBarco->barang_id, 'qty_kg' => 17874.0000, 'batch_no' => 'WIP-020126-01-ASB', 'hpp_satuan' => 14440.04, 'total_nilai' => 17874.0000 * 14440.04]
                );
            }
            if ($barangSawit) {
                DatProduksiDtl::updateOrCreate(
                    ['produksi_id' => $prod1->produksi_id, 'kategori_output' => 'ASIN_SAWIT'],
                    ['barang_id' => $barangSawit->barang_id, 'qty_kg' => 777.0000, 'batch_no' => 'WIP-020126-01-ASW', 'hpp_satuan' => 14440.04, 'total_nilai' => 777.0000 * 14440.04]
                );
            }
            if ($barangBerko) {
                DatProduksiDtl::updateOrCreate(
                    ['produksi_id' => $prod1->produksi_id, 'kategori_output' => 'BERKO'],
                    ['barang_id' => $barangBerko->barang_id, 'qty_kg' => 1031.4300, 'batch_no' => 'WIP-020126-01-BRK', 'hpp_satuan' => 14440.04, 'total_nilai' => 1031.4300 * 14440.04]
                );
            }
        });
    }
}
