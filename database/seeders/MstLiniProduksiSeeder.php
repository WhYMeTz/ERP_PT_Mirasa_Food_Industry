<?php

namespace Database\Seeders;

use App\Models\MasterData\MstLiniProduksi;
use Illuminate\Database\Seeder;

class MstLiniProduksiSeeder extends Seeder
{
    /**
     * Run the database seeds for Master Lini Produksi / Tujuan.
     * Disesuaikan dengan Buku Excel Rekapitulasi Persediaan Resmi PT Mirasa (Januari 2026).
     */
    public function run(): void
    {
        $liniData = [
            [
                'lini_cd'    => 'IFM',
                'lini_nm'    => 'PRODUKSI IFM',
                'tipe_batch' => 'IFM',
                'keterangan' => 'Indofood IFM (WIP-FCC Keripik Singkong Netto 6 Kg/Box)',
            ],
            [
                'lini_cd'    => 'PING-PING',
                'lini_nm'    => 'PRODUKSI PING-PING',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Finished Good Keripik Singkong Ping-Ping',
            ],
            [
                'lini_cd'    => 'MAKSI',
                'lini_nm'    => 'PRODUKSI MAKSI',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Finished Good Maksi',
            ],
            [
                'lini_cd'    => 'JUMBO-20',
                'lini_nm'    => 'PRODUKSI JUMBO 20',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Finished Good Jumbo 20',
            ],
            [
                'lini_cd'    => 'JUMBO-10',
                'lini_nm'    => 'PRODUKSI JUMBO 10',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Finished Good Jumbo 10',
            ],
            [
                'lini_cd'    => 'EKSPOR-ASIN',
                'lini_nm'    => 'PRODUKSI EKSPOR ASIN',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Finished Good Ekspor Rasa Asin',
            ],
            [
                'lini_cd'    => 'EKSPOR-CHILLI',
                'lini_nm'    => 'PRODUKSI EKSPOR CHILLI',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Finished Good Ekspor Rasa Chilli',
            ],
            [
                'lini_cd'    => 'MAKSI-1000',
                'lini_nm'    => 'PRODUKSI MAKSI 1000',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Finished Good Maksi 1000',
            ],
            [
                'lini_cd'    => 'LAINNYA',
                'lini_nm'    => 'PRODUKSI LAINNYA',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Lini Produksi / Tujuan Lainnya',
            ],
        ];

        foreach ($liniData as $item) {
            MstLiniProduksi::updateOrCreate(
                ['lini_cd' => $item['lini_cd']],
                [
                    'lini_nm'    => $item['lini_nm'],
                    'tipe_batch' => $item['tipe_batch'],
                    'keterangan' => $item['keterangan'],
                    'active_st'  => true,
                    'deleted_st' => false,
                ]
            );
        }
    }
}
