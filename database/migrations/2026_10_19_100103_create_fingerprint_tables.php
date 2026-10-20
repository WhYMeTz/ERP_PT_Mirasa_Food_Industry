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
        // Data mentah dari mesin
        Schema::create('trx_fingerprint_log', function (Blueprint $t) {
            $t->id();
            $t->string('id_absen', 20)->index();
            $t->string('id_mesin', 10)->nullable();
            $t->string('nama', 100)->nullable();
            $t->dateTime('scan_at');
            $t->timestamps();
            $t->unique(['id_absen', 'scan_at']); // cegah duplikat saat impor ulang
        });

        // Hasil olahan per karyawan per hari
        Schema::create('trx_absensi_harian', function (Blueprint $t) {
            $t->id();
            $t->string('id_absen', 20)->index();
            $t->string('nama', 100)->nullable();
            $t->date('tanggal')->index();
            $t->dateTime('masuk_asli')->nullable();
            $t->dateTime('pulang_asli')->nullable();
            $t->dateTime('jam_masuk')->nullable();   // sudah dibulatkan
            $t->dateTime('jam_pulang')->nullable();  // sudah dibulatkan
            $t->unsignedSmallInteger('jumlah_scan')->default(0);
            $t->timestamps();
            $t->unique(['id_absen', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_absensi_harian');
        Schema::dropIfExists('trx_fingerprint_log');
    }
};
