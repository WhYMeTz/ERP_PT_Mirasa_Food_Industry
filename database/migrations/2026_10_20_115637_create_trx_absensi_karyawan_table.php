<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_absensi_karyawan', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->index();
            $t->string('id_absen', 20)->index();
            $t->string('nik', 50)->nullable();
            $t->string('nama', 100)->nullable();
            $t->string('departemen_cd', 50)->nullable();
            $t->string('kelompok_kerja', 50)->nullable();
            $t->string('kode_shift', 5)->nullable();
            $t->dateTime('jadwal_masuk')->nullable();
            $t->dateTime('jadwal_pulang')->nullable();
            $t->dateTime('masuk_finger')->nullable();
            $t->dateTime('masuk_bulat')->nullable();
            $t->dateTime('pulang_finger')->nullable();
            $t->dateTime('pulang_bulat')->nullable();
            $t->decimal('istirahat', 3, 1)->default(0);
            $t->boolean('istirahat_bawaan')->default(false);
            $t->decimal('standar_jam', 5, 2)->default(0);
            $t->decimal('potongan_jam', 5, 2)->default(0);
            $t->decimal('total_jam', 5, 2)->default(0);
            $t->decimal('lembur_jam', 5, 2)->default(0);
            $t->decimal('kehadiran', 5, 4)->default(0);
            $t->string('status', 20)->default('OK')->index();
            $t->string('catatan', 255)->nullable();
            $t->boolean('is_manual')->default(false);
            $t->timestamps();
            $t->unique(['tanggal', 'id_absen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_absensi_karyawan');
    }
};
