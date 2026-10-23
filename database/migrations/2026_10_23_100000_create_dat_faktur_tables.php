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
        if (!Schema::hasTable('dat_faktur_hdr')) {
            Schema::create('dat_faktur_hdr', function (Blueprint $table) {
                $table->id('faktur_id');
                $table->string('faktur_no', 100)->unique()->comment('Nomor Faktur Penjualan Resmi (PM.A / PM.B / MFI)');
                $table->date('faktur_tgl')->comment('Tanggal Penerbitan Faktur Penjualan');
                $table->date('tgl_jatuh_tempo')->comment('Batas Akhir Pelunasan Sesuai TOP');
                $table->foreignId('pengeluaran_id')->nullable()->constrained('dat_pengeluaran_hdr', 'pengeluaran_id')->onDelete('set null')->comment('Referensi Surat Jalan Outbound');
                $table->foreignId('so_id')->constrained('dat_so_hdr', 'so_id')->comment('Referensi Dokumen PO Penjualan');
                $table->foreignId('customer_id')->constrained('mst_customer', 'customer_id')->comment('Mitra Pembeli');
                $table->string('customer_po_no', 100)->nullable()->comment('No. PO SAP Indofood / PO Digital Alfamart / Nota');
                $table->string('top_txt', 50)->nullable()->comment('Terms of Payment (cth: 7 Hari, 1 Nota, Cash)');

                // Perpajakan & Nilai Komersial
                $table->string('ppn_tipe', 20)->default('NON_PPN')->comment('PPN_11, NON_PPN');
                $table->decimal('ppn_persen', 5, 2)->default(0);
                $table->decimal('subtotal_bruto', 16, 4)->default(0);
                $table->decimal('total_diskon', 16, 4)->default(0);
                $table->decimal('total_potongan', 16, 4)->default(0);
                $table->decimal('dpp_nominal', 16, 4)->default(0)->comment('Dasar Pengenaan Pajak');
                $table->decimal('ppn_nominal', 16, 4)->default(0)->comment('Nilai PPN Keluaran');
                $table->decimal('total_tagihan', 16, 4)->default(0)->comment('Total Tagihan Piutang Usaha');

                // Pelunasan & Saldo Piutang
                $table->decimal('total_dibayar', 16, 4)->default(0);
                $table->decimal('sisa_piutang', 16, 4)->default(0);
                $table->string('status_pembayaran', 30)->default('BELUM_LUNAS')->comment('BELUM_LUNAS, SEBAGIAN, LUNAS, BATAL');

                $table->text('catatan_txt')->nullable();

                // 8 Kolom Audit Trail Standar ERP Mirasa
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);
                $table->timestamps();

                $table->index(['customer_id', 'status_pembayaran'], 'idx_faktur_cust_status');
                $table->index(['tgl_jatuh_tempo', 'status_pembayaran'], 'idx_faktur_tempo_status');
            });
        }

        if (!Schema::hasTable('dat_faktur_dtl')) {
            Schema::create('dat_faktur_dtl', function (Blueprint $table) {
                $table->id('faktur_dtl_id');
                $table->foreignId('faktur_id')->constrained('dat_faktur_hdr', 'faktur_id')->cascadeOnDelete();
                $table->unsignedBigInteger('pengeluaran_dtl_id')->nullable();
                $table->foreignId('sodtl_id')->nullable()->constrained('dat_so_dtl', 'sodtl_id')->onDelete('set null');
                $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id');
                $table->string('barang_nm', 200)->comment('Nama Produk Saat Faktur Diterbitkan');
                $table->decimal('qty', 16, 4)->default(0);
                $table->string('satuan', 50)->default('Karton');
                $table->decimal('harga_satuan', 16, 4)->default(0);
                $table->decimal('diskon_nominal', 16, 4)->default(0);
                $table->decimal('subtotal_netto', 16, 4)->default(0);
                $table->decimal('ppn_nominal', 16, 4)->default(0);
                $table->decimal('total_nominal', 16, 4)->default(0);
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
        Schema::dropIfExists('dat_faktur_dtl');
        Schema::dropIfExists('dat_faktur_hdr');
    }
};
