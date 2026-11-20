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
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_packaging_hdr', 'gudang_asal_id')) {
                $table->foreignId('gudang_asal_id')->nullable()->after('gudang_id')->constrained('mst_gudang', 'gudang_id')->nullOnDelete()->comment('Gudang Asal Pengambilan Bahan Kemasan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_packaging_hdr', 'gudang_asal_id')) {
                $table->dropForeign(['gudang_asal_id']);
                $table->dropColumn('gudang_asal_id');
            }
        });
    }
};
