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
        $satuanKg = DB::table('mst_satuan')->where('satuan_cd', 'KG')->value('satuan_id') ?? 1;
        $jenisWip = DB::table('mst_jenis_barang')->where('jenis_barang_cd', 'WIP')->value('jenis_barang_id') ?? 2;

        $exists = DB::table('mst_barang')->where('barang_cd', 'WIP-RMH')->exists();
        if (!$exists) {
            DB::table('mst_barang')->insert([
                'barang_cd'          => 'WIP-RMH',
                'barang_nm'          => 'KS REMAHAN',
                'jenis_barang_id'    => $jenisWip,
                'satuan_dasar_id'    => $satuanKg,
                'satuan_besar_id'    => null,
                'konversi_qty'       => 1.0000,
                'batas_minimum_qty'  => 50.0000,
                'harga_beli_standar' => 0.0000,
                'harga_jual'         => 0.0000,
                'active_st'          => true,
                'deleted_st'         => false,
                'created_by'         => 'SYSTEM',
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
        DB::table('mst_barang')->where('barang_cd', 'WIP-RMH')->delete();
    }
};

