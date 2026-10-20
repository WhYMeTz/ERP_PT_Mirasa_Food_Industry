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
        // 1. Tambah kolom top_hari di mst_supplier (Term of Payment default per supplier)
        if (Schema::hasTable('mst_supplier') && !Schema::hasColumn('mst_supplier', 'top_hari')) {
            Schema::table('mst_supplier', function (Blueprint $table) {
                $table->integer('top_hari')->default(14)->after('alamat_txt')->comment('Term of Payment dalam hari (misal: 0=COD, 14, 30)');
            });
        }

        // 2. Tambah kolom tgl_jatuh_tempo di dat_terima_hdr (opsional jika tertulis di Surat Jalan fisik)
        if (Schema::hasTable('dat_terima_hdr') && !Schema::hasColumn('dat_terima_hdr', 'tgl_jatuh_tempo')) {
            Schema::table('dat_terima_hdr', function (Blueprint $table) {
                $table->date('tgl_jatuh_tempo')->nullable()->after('suratjalan_no')->comment('Tanggal jatuh tempo fisik jika tertera pada Surat Jalan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mst_supplier') && Schema::hasColumn('mst_supplier', 'top_hari')) {
            Schema::table('mst_supplier', function (Blueprint $table) {
                $table->dropColumn('top_hari');
            });
        }

        if (Schema::hasTable('dat_terima_hdr') && Schema::hasColumn('dat_terima_hdr', 'tgl_jatuh_tempo')) {
            Schema::table('dat_terima_hdr', function (Blueprint $table) {
                $table->dropColumn('tgl_jatuh_tempo');
            });
        }
    }
};
