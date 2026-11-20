<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        $wipId = DB::table('mst_jenis_barang')->where('jenis_barang_cd', 'WIP')->value('jenis_barang_id') ?? 4;
        $fgId = DB::table('mst_jenis_barang')->where('jenis_barang_cd', 'FG')->value('jenis_barang_id') ?? 5;
        $kgId = DB::table('mst_satuan')->where('satuan_cd', 'KG')->value('satuan_id') ?? 3;

        $items = [
            [
                'barang_cd'          => 'WIP-FCC-HLD',
                'barang_nm'          => 'KERIPIK SINGKONG IFL (HOLD QC GUDANG)',
                'jenis_barang_id'    => $wipId, // WIP
                'satuan_dasar_id'    => $kgId, // KG
                'konversi_qty'       => 1.0000,
                'batas_minimum_qty'  => 0,
                'harga_beli_standar' => 0,
                'harga_jual'         => 0,
                'grade_cd'           => 'HOLD',
                'active_st'          => true,
                'deleted_st'         => false,
                'created_by'         => 1,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'barang_cd'          => 'FG-EXP-HLD',
                'barang_nm'          => 'KS EKSPOR (HOLD QC GUDANG)',
                'jenis_barang_id'    => $fgId, // FG
                'satuan_dasar_id'    => $kgId, // KG
                'konversi_qty'       => 1.0000,
                'batas_minimum_qty'  => 0,
                'harga_beli_standar' => 0,
                'harga_jual'         => 0,
                'grade_cd'           => 'HOLD',
                'active_st'          => true,
                'deleted_st'         => false,
                'created_by'         => 1,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'barang_cd'          => 'WIP-AFK',
                'barang_nm'          => 'KERIPIK AFKIR GORENG (LANGSUNG AFKIR)',
                'jenis_barang_id'    => $wipId, // WIP
                'satuan_dasar_id'    => $kgId, // KG
                'konversi_qty'       => 1.0000,
                'batas_minimum_qty'  => 0,
                'harga_beli_standar' => 0,
                'harga_jual'         => 0,
                'grade_cd'           => 'KW2',
                'active_st'          => true,
                'deleted_st'         => false,
                'created_by'         => 1,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
        ];

        foreach ($items as $item) {
            $exists = DB::table('mst_barang')->where('barang_cd', $item['barang_cd'])->exists();
            if (!$exists) {
                DB::table('mst_barang')->insert($item);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mst_barang')
            ->whereIn('barang_cd', ['WIP-FCC-HLD', 'FG-EXP-HLD', 'WIP-AFK'])
            ->delete();
    }
};
