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
        // 1. Header Penerimaan Barang (Good Receipt)
        Schema::create('dat_terima_hdr', function (Blueprint $table) {
            $table->id('terima_id');
            $table->string('terima_no', 50)->unique();
            $table->date('terima_tgl');
            $table->foreignId('po_id')->nullable()->constrained('dat_po_hdr', 'po_id')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('mst_supplier', 'supplier_id')->onDelete('restrict');
            $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
            $table->string('suratjalan_no', 100)->nullable();
            $table->string('status_cd', 25)->default('COMPLETED'); // DRAFT, COMPLETED, CANCELLED
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

        // 2. Detail Penerimaan Barang (Good Receipt Items)
        Schema::create('dat_terima_dtl', function (Blueprint $table) {
            $table->id('terimadtl_id');
            $table->foreignId('terima_id')->constrained('dat_terima_hdr', 'terima_id')->onDelete('cascade');
            $table->foreignId('podtl_id')->nullable()->constrained('dat_po_dtl', 'podtl_id')->nullOnDelete();
            $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
            $table->string('batch_no', 100);
            $table->date('expired_tgl')->nullable();
            $table->decimal('terima_qty', 16, 4);
            $table->decimal('harga_nominal', 16, 4)->default(0);
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
        Schema::dropIfExists('dat_terima_dtl');
        Schema::dropIfExists('dat_terima_hdr');
    }
};
