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
        // 1. HEADER BUKTI KAS MASUK (BKM)
        Schema::create('dat_kas_masuk_hdr', function (Blueprint $table) {
            $table->id('bkm_id');
            $table->string('bkm_no', 100)->unique();
            $table->date('tgl_terima');
            $table->foreignId('kas_bank_id')->constrained('mst_kas_bank', 'kas_bank_id')->onDelete('restrict');
            $table->string('kategori_penerimaan', 50)->comment('PENJUALAN_LIMBAH, SETORAN_MODAL, PENDAPATAN_LAIN, PENJUALAN_TUNAI, PELUNASAN_PIUTANG');
            $table->foreignId('customer_id')->nullable()->constrained('mst_customer', 'customer_id')->onDelete('restrict');
            $table->string('penyetor_nama', 150)->nullable()->comment('Pihak/orang/perusahaan yang menyetor/mentransfer');
            $table->string('metode_terima', 30)->default('TRANSFER')->comment('TRANSFER, CASH, GIRO, LAINNYA');
            $table->string('no_referensi_bank', 100)->nullable()->comment('No ref mutasi transfer / no cek');
            $table->decimal('total_terima', 16, 4)->default(0);
            $table->text('catatan')->nullable();
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

            $table->index(['tgl_terima', 'kas_bank_id'], 'idx_bkm_tgl_kas');
            $table->index('kategori_penerimaan', 'idx_bkm_kategori');
        });

        // 2. DETAIL BUKTI KAS MASUK (RINCIAN PENERIMAAN / ALOKASI)
        Schema::create('dat_kas_masuk_dtl', function (Blueprint $table) {
            $table->id('bkmdt_id');
            $table->foreignId('bkm_id')->constrained('dat_kas_masuk_hdr', 'bkm_id')->onDelete('cascade');
            $table->foreignId('so_id')->nullable()->constrained('dat_so_hdr', 'so_id')->onDelete('restrict');
            $table->string('uraian', 255)->comment('Deskripsi item penerimaan');
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

            $table->index('bkm_id', 'idx_bkmdt_hdr');
            $table->index('so_id', 'idx_bkmdt_so');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_kas_masuk_dtl');
        Schema::dropIfExists('dat_kas_masuk_hdr');
    }
};
