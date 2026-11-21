<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi dat_pengeluaran_hdr agar mendukung pengeluaran Non-PO (Sampel/Promosi/Hadiah)
        Schema::table('dat_pengeluaran_hdr', function (Blueprint $table) {
            $table->string('tipe_pengeluaran_cd', 30)->default('PENJUALAN')->after('pengeluaran_tgl');
            $table->string('penerima_nm', 150)->nullable()->after('customer_id');
            $table->string('keperluan_txt', 255)->nullable()->after('catatan_txt');
            $table->string('otorisasi_oleh', 100)->nullable()->after('keperluan_txt');
        });

        // Di PostgreSQL, jadikan so_id dan customer_id nullable
        DB::statement('ALTER TABLE dat_pengeluaran_hdr ALTER COLUMN so_id DROP NOT NULL');
        DB::statement('ALTER TABLE dat_pengeluaran_hdr ALTER COLUMN customer_id DROP NOT NULL');

        // 2. Modifikasi dat_pengeluaran_dtl agar sodtl_id nullable untuk item non-PO
        DB::statement('ALTER TABLE dat_pengeluaran_dtl ALTER COLUMN sodtl_id DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_pengeluaran_hdr', function (Blueprint $table) {
            $table->dropColumn(['tipe_pengeluaran_cd', 'penerima_nm', 'keperluan_txt', 'otorisasi_oleh']);
        });

        DB::statement('ALTER TABLE dat_pengeluaran_hdr ALTER COLUMN so_id SET NOT NULL');
        DB::statement('ALTER TABLE dat_pengeluaran_hdr ALTER COLUMN customer_id SET NOT NULL');
        DB::statement('ALTER TABLE dat_pengeluaran_dtl ALTER COLUMN sodtl_id SET NOT NULL');
    }
};
