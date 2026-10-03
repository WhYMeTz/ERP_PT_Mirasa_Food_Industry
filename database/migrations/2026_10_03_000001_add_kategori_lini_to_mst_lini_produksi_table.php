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
        Schema::table('mst_lini_produksi', function (Blueprint $table) {
            $table->string('kategori_lini', 50)->default('FINISH GOOD (FG)')->after('lini_nm')->comment('Kelompok Kategori: FINISH GOOD (FG) atau WORK IN PROGRESS (WIP)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_lini_produksi', function (Blueprint $table) {
            $table->dropColumn('kategori_lini');
        });
    }
};
