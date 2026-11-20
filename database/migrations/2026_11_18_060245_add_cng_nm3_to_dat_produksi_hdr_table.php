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
            if (!Schema::hasColumn('dat_produksi_hdr', 'cng_nm3')) {
                $table->decimal('cng_nm3', 16, 4)->default(0)->after('cng_mmbtu')->comment('Konsumsi Gas Alam CNG Fisik Meteran (Nm3)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_produksi_hdr', 'cng_nm3')) {
                $table->dropColumn('cng_nm3');
            }
        });
    }
};
