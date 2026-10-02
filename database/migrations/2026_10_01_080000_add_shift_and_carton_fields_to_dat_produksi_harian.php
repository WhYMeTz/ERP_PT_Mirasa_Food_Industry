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
        Schema::table('dat_produksi_harian', function (Blueprint $table) {
            $table->string('shift_cd', 10)->nullable()->after('lini_produksi')->comment('Shift Kerja: A (Pagi/Siang) atau B (Malam)');
            $table->string('jam_produksi', 10)->nullable()->after('shift_cd')->comment('Jam Operasional Packing / Cetak Stiker (Contoh: 14:03)');
            $table->string('varietas_singkong', 100)->nullable()->after('jam_produksi')->comment('Varietas Singkong Mentah (Contoh: STP, MGU, atau STP / MGU)');
            $table->integer('qty_karton')->nullable()->after('varietas_singkong')->comment('Jumlah Karton Selesai Dikemas');
            $table->integer('no_karton_awal')->nullable()->after('qty_karton')->comment('Nomor Seri Karton Awal (Contoh: 1 atau 1591)');
            $table->integer('no_karton_akhir')->nullable()->after('no_karton_awal')->comment('Nomor Seri Karton Akhir (Contoh: 1590 atau 2959)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_harian', function (Blueprint $table) {
            $table->dropColumn([
                'shift_cd',
                'jam_produksi',
                'varietas_singkong',
                'qty_karton',
                'no_karton_awal',
                'no_karton_akhir',
            ]);
        });
    }
};
