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
        $bpId = DB::table('mst_jenis_barang')->where('jenis_barang_cd', 'BP')->value('jenis_barang_id');
        if ($bpId === null) {
            $bpId = DB::table('mst_jenis_barang')->insertGetId([
                'jenis_barang_cd' => 'BP',
                'jenis_barang_nm' => 'Bahan Penolong & Kemasan',
                'active_st'       => true,
            ], 'jenis_barang_id');
        }

        $kgId = DB::table('mst_satuan')->where('satuan_cd', 'KG')->value('satuan_id');
        if ($kgId === null) {
            $kgId = DB::table('mst_satuan')->insertGetId([
                'satuan_cd' => 'KG',
                'satuan_nm' => 'Kilogram',
                'active_st' => true,
            ], 'satuan_id');
        }

        // 1. Tambah MSG jika belum ada berdasarkan kode uniknya.
        $existsMsg = DB::table('mst_barang')->where('barang_cd', 'MSG00G-BP1')->exists();
        if (!$existsMsg) {
            DB::table('mst_barang')->insert([
                'barang_cd'          => 'MSG00G-BP1',
                'barang_nm'          => 'MSG (MONOSODIUM GLUTAMAT)',
                'jenis_barang_id'    => $bpId, // BP (Bahan Penolong)
                'satuan_dasar_id'    => $kgId, // KG
                'konversi_qty'       => 1.0000,
                'batas_minimum_qty'  => 50.0000,
                'harga_beli_standar' => 25000.0000,
                'active_st'          => 1,
                'created_by'         => 1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        // 2. Tambah GARAM jika belum ada berdasarkan kode uniknya.
        $existsGaram = DB::table('mst_barang')->where('barang_cd', 'GRM00G-BP1')->exists();
        if (!$existsGaram) {
            DB::table('mst_barang')->insert([
                'barang_cd'          => 'GRM00G-BP1',
                'barang_nm'          => 'GARAM HALUS BERYODIUM',
                'jenis_barang_id'    => $bpId, // BP (Bahan Penolong)
                'satuan_dasar_id'    => $kgId, // KG
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
