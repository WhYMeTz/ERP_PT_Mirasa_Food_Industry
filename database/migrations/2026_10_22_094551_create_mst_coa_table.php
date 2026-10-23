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
        Schema::create('mst_coa', function (Blueprint $table) {
            $table->id('coa_id');
            $table->string('kode_akun', 50)->unique();
            $table->string('nama_akun', 150);
            $table->string('kategori_akun', 50)->comment('AKTIVA, PASIVA, MODAL, PENDAPATAN, HPP, BEBAN');
            $table->string('sub_kategori', 100)->nullable();
            $table->string('posisi_keuangan', 30)->default('NERACA')->comment('NERACA, LABA/RUGI');
            $table->string('saldo_normal', 10)->default('DEBET')->comment('DEBET, KREDIT');
            $table->decimal('saldo_awal', 16, 4)->default(0);
            $table->decimal('saldo_berjalan', 16, 4)->default(0);
            $table->boolean('is_kas_bank')->default(false)->comment('True jika akun ini adalah Kas Tunai atau Rekening Bank');
            $table->text('keterangan')->nullable();

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

            $table->index(['kategori_akun', 'active_st'], 'idx_coa_kategori_active');
            $table->index(['posisi_keuangan', 'active_st'], 'idx_coa_posisi_active');
            $table->index('is_kas_bank', 'idx_coa_is_kas_bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_coa');
    }
};
