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
        if (!Schema::hasTable('dat_packaging_hdr')) {
            Schema::create('dat_packaging_hdr', function (Blueprint $table) {
                $table->id('pack_id');
                $table->string('pack_no', 50)->unique()->comment('Nomor Dokumen Pengemasan PCK-YYYYMM-XXXX');
                $table->date('pack_tgl')->comment('Tanggal Pelaksanaan Pengemasan');
                $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->comment('Gudang Tujuan Simpan Barang Jadi (FG)');
                $table->foreignId('barang_jadi_id')->constrained('mst_barang', 'barang_id')->comment('Produk Jadi Target (FG)');
                $table->foreignId('bom_id')->nullable()->constrained('mst_bom_hdr', 'bom_id')->nullOnDelete()->comment('Formula Resep Kemasan Acuan');
                
                // Kuantitas Output
                $table->decimal('target_karton_qty', 16, 4)->default(0)->comment('Target Karton');
                $table->decimal('hasil_karton_qty', 16, 4)->default(0)->comment('Hasil Karton Aktual');
                $table->decimal('isi_per_karton_pcs', 16, 4)->default(1)->comment('Jumlah Bungkus/Pcs per Karton');
                $table->decimal('hasil_pcs_qty', 16, 4)->default(0)->comment('Total Pcs/Bungkus Jadi (hasil_karton * isi_per_karton)');
                
                // Identitas Batch Barang Jadi
                $table->string('batch_no', 100)->comment('Nomor Batch Barang Jadi Tercipta');
                $table->date('expired_tgl')->nullable()->comment('Tanggal Kadaluarsa Produk Jadi');
                
                // Komponen Biaya Akuntansi & HPP
                $table->decimal('total_biaya_wip', 16, 4)->default(0)->comment('Total Nilai Curah Keripik (WIP) yang Terpakai');
                $table->decimal('total_biaya_kemasan', 16, 4)->default(0)->comment('Total Nilai Plastik, Kardus, Lakban, dll');
                $table->decimal('total_biaya_tenaga_kerja', 16, 4)->default(0)->comment('Upah Borongan / Harian Pekerja Packing');
                $table->decimal('total_biaya_lain', 16, 4)->default(0)->comment('Biaya Tambahan / Overhead Kemas Lainnya');
                $table->decimal('total_biaya_produksi', 16, 4)->default(0)->comment('Total Akumulasi Biaya Packaging (WIP + Kemasan + TK + Lain)');
                $table->decimal('hpp_per_karton', 16, 4)->default(0)->comment('HPP Pokok per Karton Jadi');
                $table->decimal('hpp_per_pcs', 16, 4)->default(0)->comment('HPP Pokok per Bungkus/Pcs Jadi');
                
                $table->text('catatan_txt')->nullable()->comment('Keterangan / Berita Acara Packaging');
                $table->string('status_cd', 20)->default('POSTED')->comment('POSTED / VOID');
                
                // 8 Kolom Standar Audit Trail ERP Mirasa
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dat_packaging_dtl')) {
            Schema::create('dat_packaging_dtl', function (Blueprint $table) {
                $table->id('packdtl_id');
                $table->foreignId('pack_id')->constrained('dat_packaging_hdr', 'pack_id')->cascadeOnDelete();
                $table->string('kategori_bahan', 30)->comment('WIP / KEMASAN / BUMBU / LAIN');
                $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id');
                $table->string('batch_no', 100)->nullable()->comment('Batch Asal Stok yang Dipotong');
                
                // Kuantitas Standar BOM vs Aktual Fisik
                $table->decimal('qty_standar', 16, 4)->default(0)->comment('Hitungan Teoretis Resep BOM');
                $table->decimal('qty_aktual', 16, 4)->default(0)->comment('Kuantitas Riil Terpakai');
                $table->string('satuan_cd', 20)->default('PCS');
                
                // Valuasi Biaya
                $table->decimal('harga_satuan', 16, 4)->default(0)->comment('Harga Pokok Satuan Bahan');
                $table->decimal('subtotal_nilai', 16, 4)->default(0)->comment('qty_aktual * harga_satuan');
                
                $table->text('catatan_txt')->nullable();
                
                // 8 Kolom Standar Audit Trail
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_packaging_dtl');
        Schema::dropIfExists('dat_packaging_hdr');
    }
};
