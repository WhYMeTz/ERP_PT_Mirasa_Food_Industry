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
        if (Schema::hasTable('dat_produksi_adjustment_log')) {
            Schema::table('dat_produksi_adjustment_log', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_produksi_adjustment_log', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('produksi_id');
                }
                if (!Schema::hasColumn('dat_produksi_adjustment_log', 'role_cd')) {
                    $table->string('role_cd', 50)->nullable()->after('kategori_komponen');
                }
            });
        }

        if (Schema::hasTable('dat_packaging_adjustment_log')) {
            Schema::table('dat_packaging_adjustment_log', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_packaging_adjustment_log', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('pack_id');
                }
                if (!Schema::hasColumn('dat_packaging_adjustment_log', 'role_cd')) {
                    $table->string('role_cd', 50)->nullable()->after('kategori_komponen');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_produksi_adjustment_log')) {
            Schema::table('dat_produksi_adjustment_log', function (Blueprint $table) {
                if (Schema::hasColumn('dat_produksi_adjustment_log', 'role_cd')) {
                    $table->dropColumn('role_cd');
                }
                if (Schema::hasColumn('dat_produksi_adjustment_log', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
        }

        if (Schema::hasTable('dat_packaging_adjustment_log')) {
            Schema::table('dat_packaging_adjustment_log', function (Blueprint $table) {
                if (Schema::hasColumn('dat_packaging_adjustment_log', 'role_cd')) {
                    $table->dropColumn('role_cd');
                }
                if (Schema::hasColumn('dat_packaging_adjustment_log', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
        }
    }
};
