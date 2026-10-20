<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mst_karyawan', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_karyawan', 'id_absensi')) {
                $table->string('id_absensi', 50)->nullable();
            }
            if (!Schema::hasColumn('mst_karyawan', 'kelompok')) {
                $table->string('kelompok', 50)->nullable();
            }
            if (!Schema::hasColumn('mst_karyawan', 'shift')) {
                $table->string('shift', 50)->nullable();
            }
            if (!Schema::hasColumn('mst_karyawan', 'status_kerja')) {
                $table->string('status_kerja', 50)->nullable();
            }
            if (!Schema::hasColumn('mst_karyawan', 'tgl_masuk')) {
                $table->date('tgl_masuk')->nullable();
            }
            if (!Schema::hasColumn('mst_karyawan', 'bank_name')) {
                $table->string('bank_name', 50)->nullable();
            }
            if (!Schema::hasColumn('mst_karyawan', 'no_rekening')) {
                $table->string('no_rekening', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mst_karyawan', function (Blueprint $table) {
            $table->dropColumn([
                'id_absensi', 'kelompok', 'shift', 'status_kerja',
                'tgl_masuk', 'bank_name', 'no_rekening',
            ]);
        });
    }
};