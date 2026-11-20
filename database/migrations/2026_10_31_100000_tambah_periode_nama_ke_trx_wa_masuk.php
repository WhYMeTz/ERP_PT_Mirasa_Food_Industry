<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_wa_masuk', function (Blueprint $t) {
            $t->string('periode', 7)->nullable()->after('isi');   // 2026-08: periode slip yang diminta / dikirim
            $t->string('nik', 50)->nullable()->after('periode');  // karyawan yang cocok dengan nomor pengirim
            $t->string('nama', 150)->nullable()->after('nik');

            $t->index('periode');
        });
    }

    public function down(): void
    {
        Schema::table('trx_wa_masuk', function (Blueprint $t) {
            $t->dropIndex(['periode']);
            $t->dropColumn(['periode', 'nik', 'nama']);
        });
    }
};
