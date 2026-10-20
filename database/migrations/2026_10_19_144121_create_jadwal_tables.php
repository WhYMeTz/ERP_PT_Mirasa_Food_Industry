<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aturan tiap kode shift (A-M, OFF)
        Schema::create('mst_kode_shift', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 5)->unique();
            $t->string('hari', 30);                       // Senin-Jumat, Sabtu, dst (keterangan)
            $t->time('jam_masuk')->nullable();
            $t->time('jam_pulang')->nullable();
            $t->boolean('lintas_hari')->default(false);   // pulang di hari berikutnya (shift K)
            $t->decimal('istirahat_default', 3, 1)->default(0);
            $t->decimal('standar_jam', 4, 1)->nullable(); // standar jam kerja setelah istirahat
            $t->string('jenis', 20)->default('normal');   // normal, security, minggu, driver, libur
            $t->string('keterangan', 255)->nullable();
            $t->timestamps();
        });

        // Jadwal per karyawan per tanggal
        Schema::create('trx_jadwal_karyawan', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->index();
            $t->string('id_absen', 20)->index();
            $t->string('nama', 100)->nullable();
            $t->string('kelompok_kerja', 50)->nullable();
            $t->string('kode_shift', 5);
            $t->decimal('istirahat_jam', 3, 1)->nullable(); // null = pakai bawaan shift
            $t->timestamps();
            $t->unique(['tanggal', 'id_absen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_jadwal_karyawan');
        Schema::dropIfExists('mst_kode_shift');
    }
};
