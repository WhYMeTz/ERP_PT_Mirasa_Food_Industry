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
        Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_laporan_kemas_operator', 'lakban_barang_id')) {
                $table->foreignId('lakban_barang_id')
                    ->nullable()
                    ->after('karton_rusak_lembar')
                    ->constrained('mst_barang', 'barang_id')
                    ->onDelete('set null')
                    ->comment('Jenis lakban yang dipakai (Lakban Besar/Kecil/Timbangan Gram)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
            if (Schema::hasColumn('dat_laporan_kemas_operator', 'lakban_barang_id')) {
                $table->dropForeign(['lakban_barang_id']);
                $table->dropColumn('lakban_barang_id');
            }
        });
    }
};
