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
        Schema::table('dat_produksi_dtl', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_produksi_dtl', 'grade_cd')) {
                $table->string('grade_cd', 20)->default('KW1')->nullable()->after('satuan_cd');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_dtl', function (Blueprint $table) {
            if (Schema::hasColumn('dat_produksi_dtl', 'grade_cd')) {
                $table->dropColumn('grade_cd');
            }
        });
    }
};

