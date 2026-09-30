<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah MSG jika belum ada
        $existsMsg = DB::table('mst_barang')->where('barang_nm', 'ILIKE', '%MSG%')->exists();
        if (!$existsMsg) {
            DB::table('mst_barang')->insert([
                'barang_cd'          => 'MSG00G-BP1',
                'barang_nm'          => 'MSG (MONOSODIUM GLUTAMAT)',
                'jenis_barang_id'    => 9, // BP (Bahan Penolong)
                'satuan_dasar_id'    => 3, // KG
                'konversi_qty'       => 1.0000,
                'batas_minimum_qty'  => 50.0000,
                'harga_beli_standar' => 25000.0000,
                'active_st'          => 1,
                'created_by'         => 1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        // 2. Tambah GARAM jika belum ada
        $existsGaram = DB::table('mst_barang')->where('barang_nm', 'ILIKE', '%GARAM%')->exists();
        if (!$existsGaram) {
            DB::table('mst_barang')->insert([
                'barang_cd'          => 'GRM00G-BP1',
                'barang_nm'          => 'GARAM HALUS BERYODIUM',
                'jenis_barang_id'    => 9, // BP (Bahan Penolong)
                'satuan_dasar_id'    => 3, // KG
                'konversi_qty'       => 1.0000,
                'batas_minimum_qty'  => 100.0000,
                'harga_beli_standar' => 6000.0000,
                'active_st'          => 1,
                'created_by'         => 1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mst_barang')->whereIn('barang_cd', ['MSG00G-BP1', 'GRM00G-BP1'])->delete();
    }
};
