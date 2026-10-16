<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $satuanLembar = \App\Models\MasterData\MstSatuan::where('satuan_cd', 'LEMBAR')->orWhere('satuan_cd', 'lembar')->first()?->satuan_id ?? 14;
        $satuanGram = \App\Models\MasterData\MstSatuan::where('satuan_cd', 'GRAM')->first()?->satuan_id ?? 7;
        $satuanPcs = \App\Models\MasterData\MstSatuan::where('satuan_cd', 'PCS')->first()?->satuan_id ?? 8;
        $jenisBp = \App\Models\MasterData\MstJenisBarang::where('jenis_barang_cd', 'BP')->orWhere('jenis_barang_cd', 'PACK')->first()?->jenis_barang_id ?? 3;

        $items = [
            [
                'barang_cd'          => 'EXP00G-BP1',
                'barang_nm'          => 'KERTAS EXPIRED (LABEL BATCH)',
                'jenis_barang_id'    => $jenisBp,
                'satuan_dasar_id'    => $satuanLembar,
                'satuan_besar_id'    => $satuanLembar,
                'konversi_qty'       => 1,
                'batas_minimum_qty'  => 500,
                'harga_beli_standar' => 213.00,
            ],
            [
                'barang_cd'          => 'RSK-PLS01',
                'barang_nm'          => 'KERUSAKAN PLASTIK (AFKIR ROLL)',
                'jenis_barang_id'    => $jenisBp,
                'satuan_dasar_id'    => $satuanGram,
                'satuan_besar_id'    => $satuanGram,
                'konversi_qty'       => 1,
                'batas_minimum_qty'  => 0,
                'harga_beli_standar' => 41.37,
            ],
            [
                'barang_cd'          => 'RSK-KTN01',
                'barang_nm'          => 'KERUSAKAN KARTON (AFKIR DUS)',
                'jenis_barang_id'    => $jenisBp,
                'satuan_dasar_id'    => $satuanLembar,
                'satuan_besar_id'    => $satuanLembar,
                'konversi_qty'       => 1,
                'batas_minimum_qty'  => 0,
                'harga_beli_standar' => 1418.00,
            ],
            [
                'barang_cd'          => 'LKB-GR01',
                'barang_nm'          => 'LAKBAN TIMBANGAN (GRAM)',
                'jenis_barang_id'    => $jenisBp,
                'satuan_dasar_id'    => $satuanGram,
                'satuan_besar_id'    => $satuanGram,
                'konversi_qty'       => 1,
                'batas_minimum_qty'  => 1000,
                'harga_beli_standar' => 35.00,
            ],
        ];

        foreach ($items as $item) {
            \App\Models\MasterData\MstBarang::updateOrCreate(
                ['barang_cd' => $item['barang_cd']],
                array_merge($item, [
                    'active_st'  => true,
                    'deleted_st' => false,
                    'created_by' => 'SYSTEM',
                    'updated_by' => 'SYSTEM',
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\MasterData\MstBarang::whereIn('barang_cd', [
            'EXP00G-BP1', 'RSK-PLS01', 'RSK-KTN01', 'LKB-GR01'
        ])->delete();
    }
};
