<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_barang', function (Blueprint $table) {
            $table->decimal('harga_jual', 16, 4)->default(0)->after('harga_beli_standar')->comment('Harga Jual Standar / Spreading / Eceran Umum');
            $table->decimal('harga_jual_idm_alfa', 16, 4)->nullable()->after('harga_jual')->comment('Harga Jual Khusus Indomaret & Alfamart');
            $table->decimal('harga_jual_alfamidi', 16, 4)->nullable()->after('harga_jual_idm_alfa')->comment('Harga Jual Khusus Alfamidi');
            $table->decimal('harga_jual_ifm_smg', 16, 4)->nullable()->after('harga_jual_alfamidi')->comment('Harga Jual Khusus Indofood Semarang');
            $table->decimal('harga_jual_ifm_ckl', 16, 4)->nullable()->after('harga_jual_ifm_smg')->comment('Harga Jual Khusus Indofood Cikokol');
        });

        // ══════════════════════════════════════════════════════════════════════
        // SEEDING DATA HARGA STANDAR SESUAI MATRIKS RESMI EXCEL PT MIRASA
        // ══════════════════════════════════════════════════════════════════════
        // 1. PING-PING 500: Rp 15.000 / Karton
        DB::table('mst_barang')->where('barang_id', 57)->update([
            'harga_jual' => 15000.0000,
        ]);

        // 2. PING-PING 2000: Rp 58.000 / Karton
        DB::table('mst_barang')->where('barang_id', 59)->update([
            'harga_jual' => 58000.0000,
        ]);

        // 3. MAKSI 500: Rp 15.000 / Karton
        DB::table('mst_barang')->where('barang_id', 58)->update([
            'harga_jual' => 15000.0000,
        ]);

        // 4. KS JUMBO (ID 60 & 61):
        //    - Spreading / Pasar / Eceran: Rp 12.000 / Pcs
        //    - Indomaret & Alfamart: Rp 11.000 / Pcs
        //    - Alfamidi: Rp 9.500 / Pcs
        DB::table('mst_barang')->whereIn('barang_id', [60, 61])->update([
            'harga_jual'          => 12000.0000,
            'harga_jual_idm_alfa' => 11000.0000,
            'harga_jual_alfamidi' => 9500.0000,
        ]);

        // 5. KERIPIK SINGKONG TANPA BUMBU IFL (ID 44):
        //    - Indofood Semarang: Rp 28.300 / KG
        //    - Indofood Cikokol/Cikupa: Rp 29.200 / KG
        DB::table('mst_barang')->where('barang_id', 44)->update([
            'harga_jual'         => 28300.0000,
            'harga_jual_ifm_smg' => 28300.0000,
            'harga_jual_ifm_ckl' => 29200.0000,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_barang', function (Blueprint $table) {
            $table->dropColumn([
                'harga_jual',
                'harga_jual_idm_alfa',
                'harga_jual_alfamidi',
                'harga_jual_ifm_smg',
                'harga_jual_ifm_ckl',
            ]);
        });
    }
};
