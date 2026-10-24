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
        Schema::table('dat_hutang_dtl', function (Blueprint $table) {
            $table->string('ppn_tipe', 20)->default('NON_PPN')->after('ppn_nominal');
            $table->decimal('dpp_nominal', 16, 4)->default(0)->after('ppn_tipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_hutang_dtl', function (Blueprint $table) {
            $table->dropColumn(['ppn_tipe', 'dpp_nominal']);
        });
    }
};
