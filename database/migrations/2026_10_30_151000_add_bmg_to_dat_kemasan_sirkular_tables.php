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
        // 1. Tambah total_bmg_qty pada header
        Schema::table('dat_kemasan_sirkular_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_kemasan_sirkular_hdr', 'total_bmg_qty')) {
                $table->decimal('total_bmg_qty', 16, 4)->default(0)->after('total_mulus_qty');
            }
        });

        // 2. Tambah rincian BMG pada detail
        Schema::table('dat_kemasan_sirkular_dtl', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_kemasan_sirkular_dtl', 'barang_bmg_id')) {
                $table->unsignedBigInteger('barang_bmg_id')->nullable()->after('barang_mulus_id');
                $table->foreign('barang_bmg_id')->references('barang_id')->on('mst_barang')->nullOnDelete();
            }
            if (!Schema::hasColumn('dat_kemasan_sirkular_dtl', 'bmg_qty')) {
                $table->decimal('bmg_qty', 16, 4)->default(0)->after('mulus_qty');
            }
            if (!Schema::hasColumn('dat_kemasan_sirkular_dtl', 'batch_no_bmg')) {
                $table->string('batch_no_bmg', 50)->nullable()->after('batch_no_mulus');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_kemasan_sirkular_dtl', function (Blueprint $table) {
            if (Schema::hasColumn('dat_kemasan_sirkular_dtl', 'barang_bmg_id')) {
                $table->dropForeign(['barang_bmg_id']);
                $table->dropColumn(['barang_bmg_id', 'bmg_qty', 'batch_no_bmg']);
            }
        });

        Schema::table('dat_kemasan_sirkular_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_kemasan_sirkular_hdr', 'total_bmg_qty')) {
                $table->dropColumn('total_bmg_qty');
            }
        });
    }
};

