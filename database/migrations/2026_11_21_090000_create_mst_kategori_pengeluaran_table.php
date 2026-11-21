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
        // 1. MASTER KATEGORI PENGELUARAN KAS KELUAR
        Schema::create('mst_kategori_pengeluaran', function (Blueprint $table) {
            $table->id('kategori_id');
            $table->string('kode_kategori', 30)->unique();
            $table->string('nama_kategori', 100);
            $table->foreignId('coa_id_default')
                ->nullable()
                ->constrained('mst_coa', 'coa_id')
                ->onDelete('set null')
                ->comment('Akun COA beban default untuk otomatisasi verifikasi jurnal');
            $table->string('keterangan', 255)->nullable();

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
            $table->integer('version_no')->default(1);

            $table->index(['active_st', 'deleted_st'], 'idx_kat_pengeluaran_active');
            $table->index('coa_id_default', 'idx_kat_pengeluaran_coa');
        });

        // 2. TAMBAHKAN KOLOM KATEGORI_ID DI HEADER BKK
        Schema::table('dat_kas_keluar_hdr', function (Blueprint $table) {
            $table->foreignId('kategori_id')
                ->nullable()
                ->after('tipe_penerima')
                ->constrained('mst_kategori_pengeluaran', 'kategori_id')
                ->onDelete('set null');

            $table->index('kategori_id', 'idx_bkk_kategori_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_kas_keluar_hdr', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropIndex('idx_bkk_kategori_id');
            $table->dropColumn('kategori_id');
        });

        Schema::dropIfExists('mst_kategori_pengeluaran');
    }
};
