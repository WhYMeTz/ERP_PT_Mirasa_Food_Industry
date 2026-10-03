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
        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            // PENGUJIAN_1 (Kedatangan Bahan Baru) vs PENGUJIAN_2 (Uji Susulan Mutu Masuk Produksi)
            $table->string('tahap_uji', 20)->default('PENGUJIAN_1')->after('kategori_barang');
            $table->foreignId('parent_qc_id')->nullable()->after('tahap_uji')->constrained('dat_qc_inbound_hdr', 'qc_id')->nullOnDelete();
            $table->string('batch_no', 50)->nullable()->after('parent_qc_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            $table->dropForeign(['parent_qc_id']);
            $table->dropColumn(['tahap_uji', 'parent_qc_id', 'batch_no']);
        });
    }
};
