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
        // 1. Tambahkan kolom grade_cd pada dat_stok_batch & sesuaikan unique constraint
        if (Schema::hasTable('dat_stok_batch')) {
            Schema::table('dat_stok_batch', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_stok_batch', 'grade_cd')) {
                    $table->string('grade_cd', 20)->default('A')->after('batch_no')->comment('Grade mutu bahan: A, B, REJECT');
                }
            });

            // Lepas constraint lama & pasang constraint baru yang menyertakan grade_cd
            Schema::table('dat_stok_batch', function (Blueprint $table) {
                $table->dropUnique('uq_stok_gudang_barang_batch');
                $table->unique(['gudang_id', 'barang_id', 'batch_no', 'grade_cd'], 'uq_stok_gudang_barang_batch_grade');
            });
        }

        // 2. Tambahkan kolom grade_cd pada dat_stok_ledger (Kartu Stok)
        if (Schema::hasTable('dat_stok_ledger')) {
            Schema::table('dat_stok_ledger', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_stok_ledger', 'grade_cd')) {
                    $table->string('grade_cd', 20)->default('A')->after('batch_no');
                    $table->index(['barang_id', 'gudang_id', 'grade_cd', 'transaksi_tgl'], 'idx_ledger_barang_gudang_grade_tgl');
                }
            });
        }

        // 3. Tambahkan kolom grade_cd pada dat_pakai_dtl (Pengeluaran Barang ke Produksi)
        if (Schema::hasTable('dat_pakai_dtl')) {
            Schema::table('dat_pakai_dtl', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_pakai_dtl', 'grade_cd')) {
                    $table->string('grade_cd', 20)->default('A')->after('batch_no')->comment('Grade mutu bahan yang dipakai');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_pakai_dtl')) {
            Schema::table('dat_pakai_dtl', function (Blueprint $table) {
                if (Schema::hasColumn('dat_pakai_dtl', 'grade_cd')) {
                    $table->dropColumn('grade_cd');
                }
            });
        }

        if (Schema::hasTable('dat_stok_ledger')) {
            Schema::table('dat_stok_ledger', function (Blueprint $table) {
                if (Schema::hasColumn('dat_stok_ledger', 'grade_cd')) {
                    $table->dropIndex('idx_ledger_barang_gudang_grade_tgl');
                    $table->dropColumn('grade_cd');
                }
            });
        }

        if (Schema::hasTable('dat_stok_batch')) {
            Schema::table('dat_stok_batch', function (Blueprint $table) {
                $table->dropUnique('uq_stok_gudang_barang_batch_grade');
                $table->unique(['gudang_id', 'barang_id', 'batch_no'], 'uq_stok_gudang_barang_batch');
                if (Schema::hasColumn('dat_stok_batch', 'grade_cd')) {
                    $table->dropColumn('grade_cd');
                }
            });
        }
    }
};
