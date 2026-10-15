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
        // 1. Tambahkan produksi_id ke dat_pakai_hdr (Pengeluaran Gudang Terkait Tiket Produksi)
        Schema::table('dat_pakai_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_pakai_hdr', 'produksi_id')) {
                $table->foreignId('produksi_id')
                    ->nullable()
                    ->after('gudang_id')
                    ->constrained('dat_produksi_hdr', 'produksi_id')
                    ->nullOnDelete()
                    ->comment('Link Tiket Referensi Produksi Berko (Goreng)');
            }
        });

        // 2. Tambahkan pakai_id ke dat_packaging_hdr (Dokumen Kemas Terkait Pengeluaran Kemasan Gudang)
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_packaging_hdr', 'pakai_id')) {
                $table->foreignId('pakai_id')
                    ->nullable()
                    ->after('produksi_id')
                    ->constrained('dat_pakai_hdr', 'pakai_id')
                    ->nullOnDelete()
                    ->comment('Link Dokumen Pengeluaran Bahan Kemas dari Gudang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_packaging_hdr', 'pakai_id')) {
                $table->dropForeign(['pakai_id']);
                $table->dropColumn('pakai_id');
            }
        });

        Schema::table('dat_pakai_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_pakai_hdr', 'produksi_id')) {
                $table->dropForeign(['produksi_id']);
                $table->dropColumn('produksi_id');
            }
        });
    }
};
