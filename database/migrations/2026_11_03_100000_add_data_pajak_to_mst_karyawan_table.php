<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mst_karyawan', function (Blueprint $t) {
            $t->string('npwp_nik', 16)->nullable()->after('no_rekening'); // NPWP / NIK 16 digit untuk BP21
            $t->string('status_ptkp', 5)->nullable()->after('npwp_nik');   // TK/0, K/1, HB/2, dst.
        });
    }

    public function down(): void
    {
        Schema::table('mst_karyawan', function (Blueprint $t) {
            $t->dropColumn(['npwp_nik', 'status_ptkp']);
        });
    }
};
