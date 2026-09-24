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
        // 1. Header Purchase Order
        Schema::create('dat_po_hdr', function (Blueprint $table) {
            $table->id('po_id');
            $table->string('po_no', 50)->unique();
            $table->date('po_tgl');
            $table->foreignId('supplier_id')->constrained('mst_supplier', 'supplier_id')->onDelete('restrict');
            $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
            $table->string('status_cd', 25)->default('DRAFT'); // DRAFT, APPROVED, PARTIAL, COMPLETED, CANCELLED
            $table->decimal('total_nominal', 16, 4)->default(0);
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

        // 2. Detail Purchase Order
        Schema::create('dat_po_dtl', function (Blueprint $table) {
            $table->id('podtl_id');
            $table->foreignId('po_id')->constrained('dat_po_hdr', 'po_id')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
            $table->decimal('pesan_qty', 16, 4);
            $table->decimal('harga_nominal', 16, 4)->default(0);
            $table->decimal('subtotal_nominal', 16, 4)->default(0);
            $table->decimal('terima_qty', 16, 4)->default(0);
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
        Schema::dropIfExists('dat_po_dtl');
        Schema::dropIfExists('dat_po_hdr');
    }
};
