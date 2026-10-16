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
        Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
            $table->string('wip_satuan', 20)->default('KG')->after('wip_qty_kg')->comment('Pilihan satuan pengambilan curah: KG, KARTON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
            $table->dropColumn('wip_satuan');
        });
    }
};
