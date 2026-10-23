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
        if (!Schema::hasTable('dat_pengeluaran_hdr')) {
            Schema::create('dat_pengeluaran_hdr', function (Blueprint $table) {
                $table->id('pengeluaran_id');
                $table->string('pengeluaran_no', 50)->unique()->comment('Nomor Dokumen Surat Jalan / Pengeluaran Barang');
                $table->date('pengeluaran_tgl')->comment('Tanggal Riil Pengeluaran Fisik / Keberangkatan');
                $table->foreignId('so_id')->constrained('dat_so_hdr', 'so_id')->comment('Referensi Dokumen PO Penjualan');
                $table->foreignId('customer_id')->constrained('mst_customer', 'customer_id')->comment('Mitra Pembeli / Customer');
                $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->comment('Gudang Asal Barang Jadi (GDG-FG)');
                $table->string('supir_nm', 100)->nullable()->comment('Nama Supir / Pengemudi Armada');
                $table->string('nopol_kendaraan', 30)->nullable()->comment('Nomor Polisi Kendaraan / Truk');
                $table->string('ekspedisi_nm', 100)->nullable()->comment('Nama Ekspedisi / Jenis Armada');
                $table->decimal('total_qty', 14, 4)->default(0)->comment('Total Karton / Qty Barang Keluar');
                $table->string('status_cd', 20)->default('TERKIRIM')->comment('TERKIRIM (Dalam Pengiriman), SAMPAI_TUJUAN (Tiba di Pembeli), BATAL (Void)');
                $table->text('catatan_txt')->nullable()->comment('Catatan Operasional / Instruksi Muat');

                // Audit Trails (Standar AGENTS.md)
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dat_pengeluaran_dtl')) {
            Schema::create('dat_pengeluaran_dtl', function (Blueprint $table) {
                $table->id('pengeluaran_dtl_id');
                $table->foreignId('pengeluaran_id')->constrained('dat_pengeluaran_hdr', 'pengeluaran_id')->cascadeOnDelete();
                $table->foreignId('sodtl_id')->constrained('dat_so_dtl', 'sodtl_id')->comment('Referensi Baris Item PO');
                $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->comment('Produk Barang Jadi / Setengah Jadi');
                $table->string('batch_no', 50)->nullable()->comment('Nomor Batch Produksi (FEFO Allocation)');
                $table->decimal('kirim_qty', 14, 4)->default(0)->comment('Kuantitas Barang Keluar');
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
        Schema::dropIfExists('dat_pengeluaran_dtl');
        Schema::dropIfExists('dat_pengeluaran_hdr');
    }
};
