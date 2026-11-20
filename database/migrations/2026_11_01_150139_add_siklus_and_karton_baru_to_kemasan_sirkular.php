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
        // 1. Tambahkan kolom akumulasi pada dat_kemasan_sirkular_hdr
        Schema::table('dat_kemasan_sirkular_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_kemasan_sirkular_hdr', 'total_baru_qty')) {
                $table->decimal('total_baru_qty', 16, 4)->default(0)->after('total_kembali_qty')->comment('Total kardus masih baru (belum putar)');
            }
            if (!Schema::hasColumn('dat_kemasan_sirkular_hdr', 'total_afkir_siklus_qty')) {
                $table->decimal('total_afkir_siklus_qty', 16, 4)->default(0)->after('total_rusak_qty')->comment('Total kardus reject akibat mencapai limit 5x putaran');
            }
        });

        // 2. Tambahkan rincian status baru dan siklus putaran pada dat_kemasan_sirkular_dtl
        Schema::table('dat_kemasan_sirkular_dtl', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_kemasan_sirkular_dtl', 'baru_qty')) {
                $table->decimal('baru_qty', 16, 4)->default(0)->after('kembali_qty')->comment('Kardus masih baru / belum pernah dipakai ulang');
            }
            if (!Schema::hasColumn('dat_kemasan_sirkular_dtl', 'puteran_ke')) {
                $table->unsignedTinyInteger('puteran_ke')->default(1)->after('mulus_qty')->comment('Siklus pemakaian karton (1 s/d 5)');
            }
            if (!Schema::hasColumn('dat_kemasan_sirkular_dtl', 'afkir_siklus_qty')) {
                $table->decimal('afkir_siklus_qty', 16, 4)->default(0)->after('rusak_qty')->comment('Kuantitas dialihkan ke reject karena sudah 5x putaran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_kemasan_sirkular_dtl', function (Blueprint $table) {
            if (Schema::hasColumn('dat_kemasan_sirkular_dtl', 'afkir_siklus_qty')) {
                $table->dropColumn('afkir_siklus_qty');
            }
            if (Schema::hasColumn('dat_kemasan_sirkular_dtl', 'puteran_ke')) {
                $table->dropColumn('puteran_ke');
            }
            if (Schema::hasColumn('dat_kemasan_sirkular_dtl', 'baru_qty')) {
                $table->dropColumn('baru_qty');
            }
        });

        Schema::table('dat_kemasan_sirkular_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_kemasan_sirkular_hdr', 'total_afkir_siklus_qty')) {
                $table->dropColumn('total_afkir_siklus_qty');
            }
            if (Schema::hasColumn('dat_kemasan_sirkular_hdr', 'total_baru_qty')) {
                $table->dropColumn('total_baru_qty');
            }
        });
    }
};
