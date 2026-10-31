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
        // 1. Header Penerimaan Kemasan Sirkular (Karton, Palet, Wadah Balikan)
        Schema::create('dat_kemasan_sirkular_hdr', function (Blueprint $table) {
            $table->id('kemasan_sirkular_id');
            $table->string('sirkular_no', 50)->unique(); // Contoh: PKS-202610-0001
            $table->date('sirkular_tgl');
            
            // Relasi Pelacakan Pengiriman Balikan
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('pengeluaran_id')->nullable(); // Referensi Surat Jalan Pengiriman (DO / Delivery Order)
            $table->unsignedBigInteger('gudang_id'); // Gudang Penerima (misal: Gudang Kemasan)
            
            // Dokumen Fisik & Ekspedisi
            $table->string('surat_jalan_customer_no', 100)->nullable(); // No. Surat Jalan Balikan dari Buyer/Customer
            $table->string('supir_nm', 100)->nullable();
            $table->string('nopol_kendaraan', 20)->nullable();
            $table->string('ekspedisi_nm', 100)->nullable();

            // Total Kuantitas & Rekonsiliasi
            $table->decimal('total_kirim_qty', 16, 4)->default(0); // Total wadah yang dulu dikirim di Surat Jalan
            $table->decimal('total_kembali_qty', 16, 4)->default(0); // Total wadah fisik yang dibawa pulang
            $table->decimal('total_mulus_qty', 16, 4)->default(0); // Total layak putar ulang (Puteran)
            $table->decimal('total_rusak_qty', 16, 4)->default(0); // Total reject / afval (Dialihkan ke Manual)
            $table->decimal('total_selisih_qty', 16, 4)->default(0); // Hilang / tertinggal = Kirim - Kembali

            $table->string('status_cd', 20)->default('COMPLETED'); // DRAFT, COMPLETED, VOID
            $table->text('catatan_txt')->nullable();

            // 8 Kolom Audit Trail Wajib PT Mirasa
            $table->string('created_by', 50)->default('SYSTEM');
            $table->string('updated_by', 50)->default('SYSTEM');
            $table->string('deleted_by', 50)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
            $table->integer('version_no')->default(1);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('customer_id')->references('customer_id')->on('mst_customer')->nullOnDelete();
            $table->foreign('pengeluaran_id')->references('pengeluaran_id')->on('dat_pengeluaran_hdr')->nullOnDelete();
            $table->foreign('gudang_id')->references('gudang_id')->on('mst_gudang')->restrictOnDelete();
        });

        // 2. Detail Rincian Hasil Sortir per Jenis Kemasan
        Schema::create('dat_kemasan_sirkular_dtl', function (Blueprint $table) {
            $table->id('kemasan_sirkular_dtl_id');
            $table->unsignedBigInteger('kemasan_sirkular_id');
            
            $table->unsignedBigInteger('barang_asal_id'); // Misal: KIF00G-BP1 (Karton Baru IFM yg dikirim)
            $table->unsignedBigInteger('barang_mulus_id')->nullable(); // Target stok mulus: KIF00G-PUT (Puteran)
            $table->unsignedBigInteger('barang_rusak_id')->nullable(); // Target stok rusak: KMS-KTN-REJ (Reject)

            $table->decimal('kirim_qty', 16, 4)->default(0);
            $table->decimal('kembali_qty', 16, 4)->default(0);
            $table->decimal('mulus_qty', 16, 4)->default(0);
            $table->decimal('rusak_qty', 16, 4)->default(0);
            $table->decimal('selisih_qty', 16, 4)->default(0); // kirim_qty - kembali_qty

            $table->string('batch_no_mulus', 50)->nullable(); // Nomor batch stok mulus
            $table->string('batch_no_rusak', 50)->nullable(); // Nomor batch stok rusak
            $table->text('catatan_txt')->nullable();

            // 8 Kolom Audit Trail
            $table->string('created_by', 50)->default('SYSTEM');
            $table->string('updated_by', 50)->default('SYSTEM');
            $table->string('deleted_by', 50)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
            $table->integer('version_no')->default(1);
            $table->timestamps();

            $table->foreign('kemasan_sirkular_id')->references('kemasan_sirkular_id')->on('dat_kemasan_sirkular_hdr')->cascadeOnDelete();
            $table->foreign('barang_asal_id')->references('barang_id')->on('mst_barang')->restrictOnDelete();
            $table->foreign('barang_mulus_id')->references('barang_id')->on('mst_barang')->nullOnDelete();
            $table->foreign('barang_rusak_id')->references('barang_id')->on('mst_barang')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_kemasan_sirkular_dtl');
        Schema::dropIfExists('dat_kemasan_sirkular_hdr');
    }
};
