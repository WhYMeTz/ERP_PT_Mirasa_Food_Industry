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
        if (Schema::hasTable('dat_produksi_output')) {
            Schema::table('dat_produksi_output', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_produksi_output', 'jenis_cd')) {
                    $table->string('jenis_cd', 20)->default('WIP')->after('barang_id')->comment('WIP atau FG');
                }
                if (!Schema::hasColumn('dat_produksi_output', 'qty_hasil')) {
                    $table->decimal('qty_hasil', 16, 4)->default(0)->after('kategori_output')->comment('Qty dalam satuan kemasan/hasil (Karton/Dus/Kg)');
                }
                if (!Schema::hasColumn('dat_produksi_output', 'satuan_cd')) {
                    $table->string('satuan_cd', 50)->default('KG')->after('qty_hasil')->comment('Satuan hasil produksi');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_produksi_output')) {
            Schema::table('dat_produksi_output', function (Blueprint $table) {
                if (Schema::hasColumn('dat_produksi_output', 'satuan_cd')) {
                    $table->dropColumn('satuan_cd');
                }
                if (Schema::hasColumn('dat_produksi_output', 'qty_hasil')) {
                    $table->dropColumn('qty_hasil');
                }
                if (Schema::hasColumn('dat_produksi_output', 'jenis_cd')) {
                    $table->dropColumn('jenis_cd');
                }
            });
        }
    }
};
