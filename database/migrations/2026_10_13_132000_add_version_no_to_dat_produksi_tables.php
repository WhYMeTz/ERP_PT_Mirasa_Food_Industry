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
        if (Schema::hasTable('dat_produksi_hdr') && !Schema::hasColumn('dat_produksi_hdr', 'version_no')) {
            Schema::table('dat_produksi_hdr', function (Blueprint $table) {
                $table->integer('version_no')->default(1)->after('active_st');
            });
        }

        if (Schema::hasTable('dat_produksi_dtl') && !Schema::hasColumn('dat_produksi_dtl', 'version_no')) {
            Schema::table('dat_produksi_dtl', function (Blueprint $table) {
                $table->integer('version_no')->default(1)->after('active_st');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_produksi_hdr') && Schema::hasColumn('dat_produksi_hdr', 'version_no')) {
            Schema::table('dat_produksi_hdr', function (Blueprint $table) {
                $table->dropColumn('version_no');
            });
        }

        if (Schema::hasTable('dat_produksi_dtl') && Schema::hasColumn('dat_produksi_dtl', 'version_no')) {
            Schema::table('dat_produksi_dtl', function (Blueprint $table) {
                $table->dropColumn('version_no');
            });
        }
    }
};
