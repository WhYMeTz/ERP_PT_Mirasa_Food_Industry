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
        Schema::table('mst_barang', function (Blueprint $table) {
            $table->decimal('batas_minimum_qty', 16, 4)->default(0.0000)->after('konversi_qty')->comment('Batas safety stock minimum bahan di gudang');
        });

        Schema::table('dat_terima_dtl', function (Blueprint $table) {
            $table->string('grade_cd', 20)->nullable()->after('batch_no')->comment('Grade mutu bahan: A, B, REJECT');
            $table->decimal('reject_qty', 16, 4)->default(0.0000)->after('terima_qty')->comment('Kuantitas afkir/rusak saat penimbangan bongkar muat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_barang', function (Blueprint $table) {
            $table->dropColumn('batas_minimum_qty');
        });

        Schema::table('dat_terima_dtl', function (Blueprint $table) {
            $table->dropColumn(['grade_cd', 'reject_qty']);
        });
    }
};
