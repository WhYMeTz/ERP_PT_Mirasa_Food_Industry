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
        if (Schema::hasTable('dat_so_dtl') && !Schema::hasColumn('dat_so_dtl', 'satuan')) {
            Schema::table('dat_so_dtl', function (Blueprint $table) {
                $table->string('satuan', 50)->nullable()->default('Karton')->after('kirim_qty');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_so_dtl') && Schema::hasColumn('dat_so_dtl', 'satuan')) {
            Schema::table('dat_so_dtl', function (Blueprint $table) {
                $table->dropColumn('satuan');
            });
        }
    }
};
