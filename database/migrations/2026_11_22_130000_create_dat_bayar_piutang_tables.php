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
        if (!Schema::hasTable('dat_bayar_piutang_hdr')) {
            Schema::create('dat_bayar_piutang_hdr', function (Blueprint $table) {
                $table->id('bayar_id');
                $table->string('bkm_no', 50)->unique()->comment('Nomor Bukti Kas Masuk AR (BKM-YYYYMM-XXXX)');
                $table->date('tgl_bayar')->comment('Tanggal Penerimaan Kas / Pembayaran');
                $table->foreignId('customer_id')->constrained('mst_customer', 'customer_id')->comment('Pelanggan / Customer');
                $table->foreignId('kas_bank_id')->constrained('mst_kas_bank', 'kas_bank_id')->comment('Rekening Kas / Bank Penerima');
                $table->string('metode_bayar', 50)->comment('CASH, TRANSFER, GIRO, LAINNYA');
                $table->string('no_referensi_bank', 100)->nullable()->comment('No Bukti Transfer / Kliring / Giro');
                $table->decimal('total_bayar', 16, 4)->default(0)->comment('Total Kas Diterima');
                $table->string('diterima_dari', 150)->comment('Penyetor / PIC Customer');
                $table->text('catatan')->nullable()->comment('Catatan Tambahan Pelunasan');
                $table->string('status_cd', 20)->default('POSTED')->comment('POSTED, VOID');

                // 8 Kolom Audit Trail Standar ERP Mirasa
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['customer_id', 'status_cd'], 'idx_bayar_piutang_cust_status');
                $table->index(['tgl_bayar', 'status_cd'], 'idx_bayar_piutang_tgl_status');
                $table->index('kas_bank_id', 'idx_bayar_piutang_kas_bank');
            });
        }

        if (!Schema::hasTable('dat_bayar_piutang_dtl')) {
            Schema::create('dat_bayar_piutang_dtl', function (Blueprint $table) {
                $table->id('bayardt_id');
                $table->foreignId('bayar_id')->constrained('dat_bayar_piutang_hdr', 'bayar_id')->cascadeOnDelete();
                $table->foreignId('faktur_id')->constrained('dat_faktur_hdr', 'faktur_id')->comment('Referensi Faktur Penjualan');
                $table->decimal('nominal_bayar', 16, 4)->default(0)->comment('Nominal Alokasi ke Faktur Ini');
                $table->decimal('sisa_piutang_sebelum', 16, 4)->default(0)->comment('Sisa Piutang Sebelum Bayar');
                $table->decimal('sisa_piutang_sesudah', 16, 4)->default(0)->comment('Sisa Piutang Setelah Bayar');
                $table->string('catatan', 255)->nullable();

                // 8 Kolom Audit Trail Standar ERP Mirasa
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['bayar_id', 'faktur_id'], 'idx_bayar_piutang_hdr_faktur');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_bayar_piutang_dtl');
        Schema::dropIfExists('dat_bayar_piutang_hdr');
    }
};
