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
            $table->date('exp_date')->nullable()->after('batch_wip_no')->comment('Tanggal Kedaluwarsa Produk (Expired Date)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_harian', function (Blueprint $table) {
            $table->dropColumn('exp_date');
        });
    }
};
