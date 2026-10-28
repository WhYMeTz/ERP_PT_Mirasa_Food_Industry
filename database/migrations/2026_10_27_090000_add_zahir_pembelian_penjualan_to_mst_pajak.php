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
        Schema::table('mst_pajak', function (Blueprint $table) {
            $table->foreignId('coa_pembelian_id')->nullable()->after('tarif_persen')
                ->constrained('mst_coa', 'coa_id')->nullOnDelete()
                ->comment('Akun COA Pajak Pembelian (PPN Masukan)');
            
            $table->foreignId('coa_penjualan_id')->nullable()->after('coa_pembelian_id')
                ->constrained('mst_coa', 'coa_id')->nullOnDelete()
                ->comment('Akun COA Pajak Penjualan (PPN Keluaran)');
            
            $table->boolean('is_pembelian')->default(true)->after('coa_penjualan_id')
                ->comment('Toggle switch: Akun Pajak Pembelian aktif');
            
            $table->boolean('is_penjualan')->default(true)->after('is_pembelian')
                ->comment('Toggle switch: Akun Pajak Penjualan aktif');
            
            $table->string('kategori_pajak', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_pajak', function (Blueprint $table) {
            $table->dropForeign(['coa_pembelian_id']);
            $table->dropForeign(['coa_penjualan_id']);
            $table->dropColumn(['coa_pembelian_id', 'coa_penjualan_id', 'is_pembelian', 'is_penjualan']);
        });
    }
};
