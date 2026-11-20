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
        Schema::table('dat_hutang_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_hutang_hdr', 'uang_muka_nominal')) {
                $table->decimal('uang_muka_nominal', 16, 4)->default(0)->after('biaya_ongkir')->comment('Uang muka / DP yang telah dibayarkan');
            }
            if (!Schema::hasColumn('dat_hutang_hdr', 'ongkir_tarif_per_kg')) {
                $table->decimal('ongkir_tarif_per_kg', 16, 4)->default(0)->after('biaya_ongkir')->comment('Tarif ongkir per KG netto diturunkan');
            }
            if (!Schema::hasColumn('dat_hutang_hdr', 'tonase_kg')) {
                $table->decimal('tonase_kg', 16, 4)->default(0)->after('biaya_ongkir')->comment('Total tonase netto KG barang yang diturunkan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_hutang_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_hutang_hdr', 'uang_muka_nominal')) {
                $table->dropColumn('uang_muka_nominal');
            }
            if (Schema::hasColumn('dat_hutang_hdr', 'ongkir_tarif_per_kg')) {
                $table->dropColumn('ongkir_tarif_per_kg');
            }
            if (Schema::hasColumn('dat_hutang_hdr', 'tonase_kg')) {
                $table->dropColumn('tonase_kg');
            }
        });
    }
};
