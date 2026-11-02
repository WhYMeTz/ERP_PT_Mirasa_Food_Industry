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
            $table->decimal('cng_tarif_usd', 16, 4)->default(0)->after('cng_mmbtu')->comment('Tarif Gas Alam CNG dalam USD per MMBTU');
            $table->decimal('cng_kurs_jisdor', 16, 2)->default(0)->after('cng_tarif_usd')->comment('Kurs Harian JISDOR (Rp/USD)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $table->dropColumn(['cng_tarif_usd', 'cng_kurs_jisdor']);
        });
    }
};

