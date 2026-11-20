<?php

namespace Database\Seeders;

use App\Models\MasterData\MstLiniProduksi;
use Illuminate\Database\Seeder;

class MstLiniProduksiSeeder extends Seeder
{
    /**
     * Run the database seeds for Master Lini Produksi.
     * Hanya lini produksi aktif resmi pabrik PT Mirasa Food Industry: IFM dan MANUAL.
     */
    public function run(): void
    {
        $liniData = [
            [
                'lini_cd'       => 'IFM',
                'lini_nm'       => 'PRODUKSI IFM',
                'kategori_lini' => 'WORK IN PROGRESS (WIP)',
                'tipe_batch'    => 'IFM',
                'keterangan'    => 'Indofood IFM (WIP-FCC Keripik Singkong Netto 6 Kg/Box)',
            ],
            [
                'lini_cd'       => 'MANUAL',
                'lini_nm'       => 'PRODUKSI MANUAL',
                'kategori_lini' => 'FINISH GOOD (FG)',
                'tipe_batch'    => 'REGULER',
                'keterangan'    => 'Produksi Barang Jadi Manual Pabrik Mirasa',
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
