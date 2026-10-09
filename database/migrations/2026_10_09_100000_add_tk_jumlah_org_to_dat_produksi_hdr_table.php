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
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $table->integer('tk_jumlah_org')->default(0)->after('cng_nilai')->comment('Jumlah Total Tenaga Kerja Hadir Harian (Orang)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $table->dropColumn('tk_jumlah_org');
        });
    }
};
