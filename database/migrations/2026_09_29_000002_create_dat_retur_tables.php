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
        // 1. Header Retur Pembelian (Purchase Return to Supplier)
        Schema::create('dat_retur_hdr', function (Blueprint $table) {
            $table->id('retur_id');
            $table->string('retur_no', 50)->unique();
            $table->date('retur_tgl');
            $table->foreignId('supplier_id')->constrained('mst_supplier', 'supplier_id')->onDelete('restrict');
            $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
            $table->foreignId('po_id')->nullable()->constrained('dat_po_hdr', 'po_id')->nullOnDelete();
            $table->foreignId('terima_id')->nullable()->constrained('dat_terima_hdr', 'terima_id')->nullOnDelete();
            
            // Tindakan penanganan: REPLACE (Minta Kirim Ulang) vs CREDIT_NOTE (Potong Tagihan / Tidak Ganti)
            $table->string('tindakan_cd', 25)->default('REPLACE');
            $table->string('suratjalan_supplier_no', 100)->nullable()->comment('No surat jalan asal barang');
            $table->decimal('total_nominal', 16, 4)->default(0);
            $table->string('status_cd', 25)->default('COMPLETED'); // DRAFT, COMPLETED, CANCELLED
            $table->text('alasan_txt')->nullable()->comment('Alasan umum retur / kendala lapangan');

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
        });

        // 2. Detail Retur Pembelian (Item Batch yang Dikembalikan)
        Schema::create('dat_retur_dtl', function (Blueprint $table) {
            $table->id('returdtl_id');
            $table->foreignId('retur_id')->constrained('dat_retur_hdr', 'retur_id')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
            $table->foreignId('podtl_id')->nullable()->constrained('dat_po_dtl', 'podtl_id')->nullOnDelete();
            $table->string('batch_no', 100)->comment('Batch yang ditarik dari stok fisik');
            $table->decimal('retur_qty', 16, 4);
            $table->decimal('harga_satuan', 16, 4)->default(0);
            $table->decimal('subtotal_nominal', 16, 4)->default(0);
            $table->string('alasan_reject', 255)->nullable()->comment('Cacat fisik: Busuk, berserat, kutu, kemasan bocor, dll');
            $table->text('catatan_txt')->nullable();

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_retur_dtl');
        Schema::dropIfExists('dat_retur_hdr');
    }
};
