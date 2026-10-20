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
        if (Schema::hasTable('dat_laporan_kemas_operator')) {
            Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_laporan_kemas_operator', 'expired_barang_id')) {
                    $table->foreignId('expired_barang_id')->nullable()->after('lakban_qty')->constrained('mst_barang', 'barang_id')->onDelete('set null')->comment('Kertas/Plastik Expired (Label Batch Kadaluarsa)');
                }
                if (!Schema::hasColumn('dat_laporan_kemas_operator', 'expired_qty')) {
                    $table->decimal('expired_qty', 16, 4)->default(0)->after('expired_barang_id')->comment('Jumlah pemakaian lembar/pcs expired');
                }
                if (!Schema::hasColumn('dat_laporan_kemas_operator', 'kemasan_tambahan_items')) {
                    $table->json('kemasan_tambahan_items')->nullable()->after('kemasan_lain_qty')->comment('Rincian multi kemasan tambahan (array of objects)');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_laporan_kemas_operator')) {
            Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
                if (Schema::hasColumn('dat_laporan_kemas_operator', 'kemasan_tambahan_items')) {
                    $table->dropColumn('kemasan_tambahan_items');
                }
                if (Schema::hasColumn('dat_laporan_kemas_operator', 'expired_qty')) {
                    $table->dropColumn('expired_qty');
                }
                if (Schema::hasColumn('dat_laporan_kemas_operator', 'expired_barang_id')) {
                    $table->dropForeign(['expired_barang_id']);
                    $table->dropColumn('expired_barang_id');
                }
            });
        }
    }
};
