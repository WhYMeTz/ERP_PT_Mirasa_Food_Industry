<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_absensi_harian', function (Blueprint $t) {
            $t->boolean('is_manual')->default(false);
            $t->string('catatan', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trx_absensi_harian', function (Blueprint $t) {
            $t->dropColumn(['is_manual', 'catatan']);
        });
    }
};
