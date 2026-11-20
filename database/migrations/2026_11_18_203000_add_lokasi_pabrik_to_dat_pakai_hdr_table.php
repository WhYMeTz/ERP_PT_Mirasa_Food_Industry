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
        Schema::table('dat_pakai_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_pakai_hdr', 'lokasi_pabrik')) {
                $table->string('lokasi_pabrik', 20)->nullable()->after('gudang_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_pakai_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_pakai_hdr', 'lokasi_pabrik')) {
                $table->dropColumn('lokasi_pabrik');
            }
        });
    }
};
