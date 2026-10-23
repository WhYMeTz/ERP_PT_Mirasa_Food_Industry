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
        if (!Schema::hasTable('dat_so_retur_hdr')) {
            Schema::create('dat_so_retur_hdr', function (Blueprint $table) {
                $table->id('retur_id');
                $table->string('retur_no', 50)->unique()->comment('Nomor Dokumen Retur Penjualan');
                $table->date('retur_tgl')->comment('Tanggal Penerimaan Retur Barang');
                $table->foreignId('so_id')->constrained('dat_so_hdr', 'so_id')->comment('Referensi SO Asal');
                $table->foreignId('customer_id')->constrained('mst_customer', 'customer_id')->comment('Customer Terkait');
                $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->comment('Gudang Tujuan Masuk Retur (Default Gudang FG)');
                $table->string('tindakan_cd', 30)->default('POTONG_TAGIHAN')->comment('POTONG_TAGIHAN (Credit Note) atau TUKAR_GULING (Ganti Barang)');
                $table->decimal('total_qty', 14, 4)->default(0)->comment('Total Kuantitas Barang Diretur');
                $table->decimal('total_nilai', 15, 4)->default(0)->comment('Total Nilai Nominal Retur');
                $table->text('alasan_txt')->nullable()->comment('Alasan Retur / Berita Acara');
                $table->string('status_cd', 20)->default('CONFIRMED')->comment('CONFIRMED, VOID');

                // Audit Trails
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dat_so_retur_dtl')) {
            Schema::create('dat_so_retur_dtl', function (Blueprint $table) {
                $table->id('retur_dtl_id');
                $table->foreignId('retur_id')->constrained('dat_so_retur_hdr', 'retur_id')->cascadeOnDelete();
                $table->foreignId('sodtl_id')->constrained('dat_so_dtl', 'sodtl_id')->comment('Referensi Baris Item SO');
                $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id');
                $table->string('batch_no', 50)->comment('Nomor Batch Retur Masuk');
                $table->decimal('qty_retur', 14, 4)->default(0);
                $table->decimal('harga_satuan', 15, 4)->default(0);
                $table->decimal('subtotal_nilai', 15, 4)->default(0);
                $table->string('kondisi_cd', 30)->nullable()->comment('RUSAK_KEMASAN, BOCOR_KEMPES, EXPIRED, CACAT_PRODUKSI, LAINNYA');
                $table->text('catatan_txt')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_so_retur_dtl');
        Schema::dropIfExists('dat_so_retur_hdr');
    }
};
