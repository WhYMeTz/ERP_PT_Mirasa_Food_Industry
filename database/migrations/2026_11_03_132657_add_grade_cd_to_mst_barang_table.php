<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_barang', 'grade_cd')) {
                $table->string('grade_cd', 10)->default('KW1')->nullable()->after('jenis_barang_id');
            }
        });

        // Set default untuk data yang sudah ada
        DB::table('mst_barang')->whereNull('grade_cd')->update(['grade_cd' => 'KW1']);

        // Set KW2 untuk barang-barang sortiran, berko, dan lengket yang sudah ada
        DB::table('mst_barang')->whereIn('barang_cd', ['WIP-LGT', 'WIP-BRK', 'WIP-RMH', 'WIP-BJK'])->update(['grade_cd' => 'KW2']);

        // Daftarkan master item resmi: KS CURAH SORTIR (KW 2) untuk penampungan produk gagal rilis / sortiran (Poin 23 & 24)
        if (!DB::table('mst_barang')->where('barang_cd', 'WIP-KW2')->exists()) {
            $wipJenisId = DB::table('mst_jenis_barang')->where('jenis_barang_cd', 'WIP')->value('jenis_barang_id') ?? 4;
            $kgSatuanId = DB::table('mst_satuan')->where('satuan_cd', 'KG')->value('satuan_id') ?? 3;

            DB::table('mst_barang')->insert([
                'barang_cd'          => 'WIP-KW2',
                'barang_nm'          => 'KS CURAH SORTIR (KW 2)',
                'jenis_barang_id'    => $wipJenisId, // WIP
                'satuan_dasar_id'    => $kgSatuanId, // KG
                'grade_cd'           => 'KW2',
                'konversi_qty'       => 1.0000,
                'active_st'          => true,
                'deleted_st'         => false,
                'batas_minimum_qty'  => 50.0000,
                'harga_beli_standar' => 0.0000,
                'harga_jual'         => 15000.0000,
                'created_by'         => 'SYSTEM',
                'updated_by'         => 'SYSTEM',
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
        DB::table('mst_barang')->where('barang_cd', 'WIP-KW2')->delete();

        Schema::table('mst_barang', function (Blueprint $table) {
            if (Schema::hasColumn('mst_barang', 'grade_cd')) {
                $table->dropColumn('grade_cd');
            }
        });
    }
};
