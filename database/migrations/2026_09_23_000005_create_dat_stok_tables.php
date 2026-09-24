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
        // 1. Saldo Stok per Batch & Gudang
        Schema::create('dat_stok_batch', function (Blueprint $table) {
            $table->id('stok_id');
            $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
            $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
            $table->string('batch_no', 100);
            $table->date('expired_tgl')->nullable();
            $table->decimal('sisa_qty', 16, 4)->default(0);

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);

            // Unique constraint kombinasi gudang + barang + nomor batch
            $table->unique(['gudang_id', 'barang_id', 'batch_no'], 'uq_stok_gudang_barang_batch');
        });

        // 2. Histori Kartu Stok Mutasi (Stock Ledger)
        Schema::create('dat_stok_ledger', function (Blueprint $table) {
            $table->id('ledger_id');
            $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
            $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
            $table->string('batch_no', 100);
            $table->timestamp('transaksi_tgl');
            $table->string('dokumen_no', 100);
            $table->string('tipe_transaksi_cd', 10); // 'IN' atau 'OUT'
            $table->decimal('qty', 16, 4);
            $table->decimal('saldoakhir_qty', 16, 4);
            $table->text('keterangan_txt')->nullable();

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);

            // Index pencarian kartu stok
            $table->index(['barang_id', 'gudang_id', 'transaksi_tgl'], 'idx_ledger_barang_gudang_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_stok_ledger');
        Schema::dropIfExists('dat_stok_batch');
    }
};
