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
        Schema::table('mst_supplier', function (Blueprint $table) {
            $table->foreignId('jenis_supplier_id')
                ->nullable()
                ->after('supplier_nm')
                ->constrained('mst_jenis_supplier', 'jenis_supplier_id')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_supplier', function (Blueprint $table) {
            $table->dropForeign(['jenis_supplier_id']);
            $table->dropColumn('jenis_supplier_id');
        });
    }
};
