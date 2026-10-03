<?php

namespace Database\Seeders;

use App\Models\MasterData\MstLiniProduksi;
use Illuminate\Database\Seeder;

class MstLiniProduksiSeeder extends Seeder
{
    /**
     * Run the database seeds for Master Lini Produksi / Tujuan.
     */
    public function run(): void
    {
        $liniData = [
            [
                'lini_cd'    => 'IFM',
                'lini_nm'    => 'PRODUKSI IFM',
                'tipe_batch' => 'IFM',
                'keterangan' => 'Indofood IFM (WIP-FCC Keripik Singkong)',
            ],
            [
                'lini_cd'    => 'PP2000',
                'lini_nm'    => 'PRODUKSI PING-PING 2000',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Produksi Keripik Ping-Ping 2000',
            ],
            [
                'lini_cd'    => 'PP-UMUM',
                'lini_nm'    => 'PRODUKSI PING-PING',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Produksi Keripik Ping-Ping (Umum / Retail)',
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
