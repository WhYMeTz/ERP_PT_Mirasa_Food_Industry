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
        // 1. Tambah kolom biaya ongkos angkut pada Header Tagihan Hutang (AP)
        if (Schema::hasTable('dat_hutang_hdr') && !Schema::hasColumn('dat_hutang_hdr', 'biaya_ongkir')) {
            Schema::table('dat_hutang_hdr', function (Blueprint $table) {
                $table->decimal('biaya_ongkir', 16, 4)->default(0)->after('total_ppn')->comment('Ongkos kirim / biaya angkut ekspedisi vendor');
            });
        }

        // 2. Header Jurnal Akuntansi (General Journal Header)
        if (!Schema::hasTable('dat_jurnal_hdr')) {
            Schema::create('dat_jurnal_hdr', function (Blueprint $table) {
                $table->id('jurnal_id');
                $table->string('jurnal_no', 100)->unique();
                $table->date('tgl_jurnal');
                $table->string('tipe_transaksi', 50)->comment('HUTANG_PEMBELIAN, BAYAR_HUTANG, KAS_MASUK, JURNAL_UMUM, HPP_PRODUKSI, PENJUALAN');
                $table->string('ref_no', 100)->nullable()->comment('No invoice AP, no BKK, no BKM, atau no PO');
                $table->unsignedBigInteger('ref_id')->nullable();
                $table->text('keterangan')->nullable();
                $table->decimal('total_debet', 16, 4)->default(0);
                $table->decimal('total_kredit', 16, 4)->default(0);
                $table->boolean('is_balanced')->default(true);

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

                $table->index(['tgl_jurnal', 'tipe_transaksi'], 'idx_jurnal_tgl_tipe');
                $table->index('ref_no', 'idx_jurnal_ref');
            });
        }

        // 3. Detail Baris Jurnal Akuntansi (General Journal Detail - Double Entry)
        if (!Schema::hasTable('dat_jurnal_dtl')) {
            Schema::create('dat_jurnal_dtl', function (Blueprint $table) {
                $table->id('jurnaldtl_id');
                $table->foreignId('jurnal_id')->constrained('dat_jurnal_hdr', 'jurnal_id')->onDelete('cascade');
                $table->foreignId('coa_id')->constrained('mst_coa', 'coa_id')->onDelete('restrict');
                $table->decimal('debet', 16, 4)->default(0);
                $table->decimal('kredit', 16, 4)->default(0);
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

                $table->index(['jurnal_id', 'coa_id'], 'idx_jurnaldtl_jurnal_coa');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_jurnal_dtl');
        Schema::dropIfExists('dat_jurnal_hdr');

        if (Schema::hasTable('dat_hutang_hdr') && Schema::hasColumn('dat_hutang_hdr', 'biaya_ongkir')) {
            Schema::table('dat_hutang_hdr', function (Blueprint $table) {
                $table->dropColumn('biaya_ongkir');
            });
        }
    }
};
