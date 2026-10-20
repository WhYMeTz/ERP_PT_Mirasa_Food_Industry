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
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            $table->foreignId('produksi_id')
                ->nullable()
                ->after('bom_id')
                ->constrained('dat_produksi_hdr', 'produksi_id')
                ->nullOnDelete()
                ->comment('Referensi Dokumen Lembar Produksi WIP Asal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            $table->dropForeign(['produksi_id']);
            $table->dropColumn('produksi_id');
        });
    }
};
