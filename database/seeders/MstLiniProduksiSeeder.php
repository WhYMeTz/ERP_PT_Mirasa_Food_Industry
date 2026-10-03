<?php

namespace Database\Seeders;

use App\Models\MasterData\MstLiniProduksi;
use Illuminate\Database\Seeder;

class MstLiniProduksiSeeder extends Seeder
{
    /**
     * Run the database seeds for Master Lini Produksi / Tujuan.
     * Mengambil 100% data resmi dari Buku Excel Rekapitulasi Persediaan PT Mirasa (Januari 2026):
     * Terdiri dari 8 varian Finish Good (FG) dan 10 varian Work In Progress (WIP).
     */
    public function run(): void
    {
        $liniData = [
            // --- KELOMPOK 1: FINISH GOOD (FG) ---
            [
                'lini_cd'       => 'IFM',
                'lini_nm'       => 'PRODUKSI IFM',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'IFM',
                'keterangan'    => 'Indofood IFM (WIP-FCC Keripik Singkong Netto 6 Kg/Box)',
            ],
            [
                'lini_cd'       => 'PING-PING',
                'lini_nm'       => 'PRODUKSI PING-PING',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'Finished Good Keripik Singkong Ping-Ping',
            ],
            [
                'lini_cd'       => 'MAKSI',
                'lini_nm'       => 'PRODUKSI MAKSI',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'Finished Good Maksi',
            ],
            [
                'lini_cd'       => 'JUMBO-20',
                'lini_nm'       => 'PRODUKSI JUMBO 20',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'Finished Good Jumbo 20',
            ],
            [
                'lini_cd'       => 'JUMBO-10',
                'lini_nm'       => 'PRODUKSI JUMBO 10',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'Finished Good Jumbo 10',
            ],
            [
                'lini_cd'       => 'EKSPOR-ASIN',
                'lini_nm'       => 'PRODUKSI EKSPOR ASIN',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'Finished Good Ekspor Rasa Asin',
            ],
            [
                'lini_cd'       => 'EKSPOR-CHILLI',
                'lini_nm'       => 'PRODUKSI EKSPOR CHILLI',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'Finished Good Ekspor Rasa Chilli',
            ],
            [
                'lini_cd'       => 'MAKSI-1000',
                'lini_nm'       => 'PRODUKSI MAKSI 1000',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'Finished Good Maksi 1000',
            ],

            // --- KELOMPOK 2: WORK IN PROGRESS (WIP / SETENGAH JADI) ---
            [
                'lini_cd'       => 'WIP-BERKO',
                'lini_nm'       => 'PRODUKSI BERKO',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Keripik Singkong Berko',
            ],
            [
                'lini_cd'       => 'WIP-BERKO-JKT',
                'lini_nm'       => 'PRODUKSI BERKO JKT',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Berko Jakarta',
            ],
            [
                'lini_cd'       => 'WIP-BERKO-BUMBU',
                'lini_nm'       => 'PRODUKSI BERKO BERBUMBU',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Berko Berbumbu',
            ],
            [
                'lini_cd'       => 'WIP-ASIN-BARCO',
                'lini_nm'       => 'PRODUKSI ASIN BARCO',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Asin Minyak Barco',
            ],
            [
                'lini_cd'       => 'WIP-BALQI',
                'lini_nm'       => 'PRODUKSI BALQI',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Balqi',
            ],
            [
                'lini_cd'       => 'WIP-ASIN-SAWIT',
                'lini_nm'       => 'PRODUKSI ASIN SAWIT',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Asin Minyak Sawit',
            ],
            [
                'lini_cd'       => 'WIP-NO-SALT',
                'lini_nm'       => 'PRODUKSI NO SALT',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Tanpa Garam / No Salt',
            ],
            [
                'lini_cd'       => 'WIP-CHILLI-LEMON',
                'lini_nm'       => 'PRODUKSI KSU CHILLI LEMON',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP KSU Chilli Lemon',
            ],
            [
                'lini_cd'       => 'WIP-ASIN-JB',
                'lini_nm'       => 'PRODUKSI ASIN JB',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Asin JB',
            ],
            [
                'lini_cd'       => 'WIP-PEDAS-JB',
                'lini_nm'       => 'PRODUKSI PEDAS JB',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'WIP Pedas JB',
            ],
        ];

        foreach ($liniData as $item) {
            MstLiniProduksi::updateOrCreate(
                ['lini_cd' => $item['lini_cd']],
                [
                    'lini_nm'       => $item['lini_nm'],
                    'kategori_lini' => $item['kategori_lini'],
                    'tipe_batch'    => $item['tipe_batch'],
                    'keterangan'    => $item['keterangan'],
                    'active_st'     => true,
                    'deleted_st'    => false,
                ]
            );
        }
    }
}
