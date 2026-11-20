<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Koreksi jam di halaman Finger Print (Tambah Manual / Edit)
        Schema::table('trx_absensi_harian', function (Blueprint $t) {
            $t->boolean('dikonfirmasi')->default(false)->after('is_manual');
        });

        // Hasil hitung di halaman Absensi Karyawan (untuk ikon ceklis)
        Schema::table('trx_absensi_karyawan', function (Blueprint $t) {
            $t->boolean('dikonfirmasi')->default(false)->after('is_manual');
        });
    }

    public function down(): void
    {
        Schema::table('trx_absensi_harian', fn (Blueprint $t) => $t->dropColumn('dikonfirmasi'));
        Schema::table('trx_absensi_karyawan', fn (Blueprint $t) => $t->dropColumn('dikonfirmasi'));
    }
};
