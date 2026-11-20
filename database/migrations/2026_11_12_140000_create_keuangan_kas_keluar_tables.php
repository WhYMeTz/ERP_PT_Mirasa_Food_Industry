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
        // 1. HEADER BUKTI KAS KELUAR OPERASIONAL (BKK)
        Schema::create('dat_kas_keluar_hdr', function (Blueprint $table) {
            $table->id('bkk_id');
            $table->string('bkk_no', 100)->unique();
            $table->date('tgl_keluar');
            $table->foreignId('kas_bank_id')->constrained('mst_kas_bank', 'kas_bank_id')->onDelete('restrict');
            $table->foreignId('coa_id')->nullable()->constrained('mst_coa', 'coa_id')->onDelete('restrict')->comment('Akun Beban / Pos Biaya');
            $table->string('kategori_pengeluaran', 50)->default('BIAYA_OPERASIONAL')->comment('OPERASIONAL, LISTRIK_AIR, BBM, SERVIS_MESIN, ATK_PERLENGKAPAN, KONSUMSI, PENGIRIMAN, LAINNYA');
            $table->string('penerima_nama', 150)->comment('Pihak/Orang/Vendor yang menerima pembayaran');
            $table->string('metode_bayar', 30)->default('CASH')->comment('CASH, TRANSFER, GIRO, LAINNYA');
            $table->string('no_referensi_bank', 100)->nullable()->comment('No ref mutasi transfer / no struk');
            $table->decimal('total_keluar', 16, 4)->default(0);
            $table->text('catatan')->nullable();
            $table->foreignId('jurnal_id')->nullable()->constrained('dat_jurnal_hdr', 'jurnal_id')->onDelete('set null');
            $table->string('status_cd', 20)->default('POSTED')->comment('POSTED, VOID');
            $table->string('alasan_void', 255)->nullable();
            $table->string('void_by', 100)->nullable();
            $table->timestamp('void_dt')->nullable();

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

            $table->index(['tgl_keluar', 'kas_bank_id'], 'idx_bkk_tgl_kas');
            $table->index('kategori_pengeluaran', 'idx_bkk_kategori');
            $table->index('coa_id', 'idx_bkk_coa');
        });

        // 2. DETAIL RINCIAN PENGELUARAN KAS KELUAR
        Schema::create('dat_kas_keluar_dtl', function (Blueprint $table) {
            $table->id('bkkdt_id');
            $table->foreignId('bkk_id')->constrained('dat_kas_keluar_hdr', 'bkk_id')->onDelete('cascade');
            $table->foreignId('coa_id')->nullable()->constrained('mst_coa', 'coa_id')->onDelete('restrict')->comment('Akun COA spesifik jika multi-akun');
            $table->string('uraian', 255)->comment('Deskripsi pengeluaran');
            $table->decimal('qty', 16, 4)->default(1);
            $table->string('satuan', 20)->nullable();
            $table->decimal('harga_satuan', 16, 4)->default(0);
            $table->decimal('nominal', 16, 4)->default(0);
            $table->string('catatan', 255)->nullable();

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

            $table->index('bkk_id', 'idx_bkkdt_hdr');
            $table->index('coa_id', 'idx_bkkdt_coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_kas_keluar_dtl');
        Schema::dropIfExists('dat_kas_keluar_hdr');
    }
};

