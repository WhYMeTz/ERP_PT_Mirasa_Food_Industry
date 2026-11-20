<?php

use App\Models\Manajemen\TrxAbsensiKaryawan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_lembur_konfirmasi', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal');
            $t->string('id_absen', 20);
            $t->boolean('hitung');                       // true = dihitung lembur, false = tidak
            $t->unsignedBigInteger('oleh')->nullable();  // user yang mengonfirmasi
            $t->timestamps();
            $t->unique(['tanggal', 'id_absen']);
        });

        Schema::table((new TrxAbsensiKaryawan)->getTable(), function (Blueprint $t) {
            $t->decimal('lembur_ragu', 5, 2)->default(0)->after('lembur_jam');
            $t->boolean('lembur_konfirmasi')->nullable()->after('lembur_ragu');
        });
    }

    public function down(): void
    {
        Schema::table((new TrxAbsensiKaryawan)->getTable(), function (Blueprint $t) {
            $t->dropColumn(['lembur_ragu', 'lembur_konfirmasi']);
        });
        Schema::dropIfExists('trx_lembur_konfirmasi');
    }
};
