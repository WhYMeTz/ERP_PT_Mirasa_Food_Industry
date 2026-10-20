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
            $table->unsignedBigInteger('wip_barang_id')->nullable()->after('produksi_id');
            $table->unsignedBigInteger('output_id')->nullable()->after('wip_barang_id');
            $table->string('wip_batch_no', 100)->nullable()->after('output_id');

            $table->foreign('wip_barang_id')
                ->references('barang_id')
                ->on('mst_barang')
                ->nullOnDelete();

            $table->foreign('output_id')
                ->references('output_id')
                ->on('dat_produksi_dtl')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
            $table->dropForeign(['output_id']);
            $table->dropForeign(['wip_barang_id']);
            $table->dropColumn(['wip_batch_no', 'output_id', 'wip_barang_id']);
        });
    }
};
