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
            $table->decimal('ifl_qty', 16, 4)->default(0)->after('total_biaya_produksi')->comment('WIP Hasil Lini IFL Indofood (Kg)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $table->dropColumn('ifl_qty');
        });
    }
};
