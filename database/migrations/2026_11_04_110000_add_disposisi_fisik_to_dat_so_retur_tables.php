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
        Schema::table('dat_so_retur_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_so_retur_hdr', 'total_qty_masuk_persediaan')) {
                $table->decimal('total_qty_masuk_persediaan', 14, 4)->default(0)->after('total_qty')
                    ->comment('Total kuantitas barang retur yang dialokasikan masuk stok sortir KW 2');
            }
            if (!Schema::hasColumn('dat_so_retur_hdr', 'total_qty_dimusnahkan')) {
                $table->decimal('total_qty_dimusnahkan', 14, 4)->default(0)->after('total_qty_masuk_persediaan')
                    ->comment('Total kuantitas barang retur yang langsung dimusnahkan/scrap');
            }
        });

        Schema::table('dat_so_retur_dtl', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_so_retur_dtl', 'qty_masuk_persediaan')) {
                $table->decimal('qty_masuk_persediaan', 14, 4)->default(0)->after('qty_retur')
                    ->comment('Kuantitas yang masuk ke persediaan sortir KW 2');
            }
            if (!Schema::hasColumn('dat_so_retur_dtl', 'qty_dimusnahkan')) {
                $table->decimal('qty_dimusnahkan', 14, 4)->default(0)->after('qty_masuk_persediaan')
                    ->comment('Kuantitas yang dimusnahkan/scrap di tempat');
            }
            if (!Schema::hasColumn('dat_so_retur_dtl', 'perlakuan_cd')) {
                $table->string('perlakuan_cd', 30)->default('MASUK_PERSEDIAAN')->after('qty_dimusnahkan')
                    ->comment('MASUK_PERSEDIAAN, DIMUSNAHKAN, CAMPUR');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_so_retur_dtl', function (Blueprint $table) {
            if (Schema::hasColumn('dat_so_retur_dtl', 'perlakuan_cd')) {
                $table->dropColumn('perlakuan_cd');
            }
            if (Schema::hasColumn('dat_so_retur_dtl', 'qty_dimusnahkan')) {
                $table->dropColumn('qty_dimusnahkan');
            }
            if (Schema::hasColumn('dat_so_retur_dtl', 'qty_masuk_persediaan')) {
                $table->dropColumn('qty_masuk_persediaan');
            }
        });

        Schema::table('dat_so_retur_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_so_retur_hdr', 'total_qty_dimusnahkan')) {
                $table->dropColumn('total_qty_dimusnahkan');
            }
            if (Schema::hasColumn('dat_so_retur_hdr', 'total_qty_masuk_persediaan')) {
                $table->dropColumn('total_qty_masuk_persediaan');
            }
        });
    }
};

