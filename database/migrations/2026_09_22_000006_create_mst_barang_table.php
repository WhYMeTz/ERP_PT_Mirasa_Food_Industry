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
        Schema::create('mst_barang', function (Blueprint $table) {
            $table->id('barang_id');
            $table->string('barang_cd', 50)->unique();
            $table->string('barang_nm', 150);

            // Relasi Foreign Keys
            $table->foreignId('jenis_barang_id')->constrained('mst_jenis_barang', 'jenis_barang_id')->onDelete('restrict');
            $table->foreignId('satuan_dasar_id')->constrained('mst_satuan', 'satuan_id')->onDelete('restrict');
            $table->foreignId('satuan_besar_id')->nullable()->constrained('mst_satuan', 'satuan_id')->onDelete('restrict');

            // Konversi Qty (wajib decimal 16,4)
            $table->decimal('konversi_qty', 16, 4)->default(1.0000)->comment('Nilai konversi: 1 satuan besar = X satuan dasar');

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
        Schema::dropIfExists('mst_barang');
    }
};
