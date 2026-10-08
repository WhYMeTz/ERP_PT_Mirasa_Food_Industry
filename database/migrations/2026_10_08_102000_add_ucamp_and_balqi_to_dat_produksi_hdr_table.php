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
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $table->decimal('ucamp_qty', 16, 4)->default(0)->after('no_salt_qty')->comment('WIP Olahan Untuk Bumbu Campur / UCAMP (Kg)');
            $table->decimal('balqi_qty', 16, 4)->default(0)->after('balo_gelombang_qty')->comment('WIP Kemasan Bal / Balqi (Kg)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $table->dropColumn(['ucamp_qty', 'balqi_qty']);
        });
    }
};
