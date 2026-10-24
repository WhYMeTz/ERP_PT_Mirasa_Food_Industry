<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_payroll', function (Blueprint $t) {
            $t->id();
            $t->char('periode', 7)->index();                  // 2026-10
            $t->string('id_absen', 20);
            $t->string('nik', 50)->nullable();
            $t->string('nama', 100);
            $t->string('kelompok_kerja', 50)->nullable();
            $t->string('jabatan', 100)->nullable();

            // komponen yang diinput admin
            $t->decimal('gaji_harian', 12, 2)->default(0);
            $t->decimal('tunjangan_jabatan', 12, 2)->default(0);
            $t->decimal('insentif', 12, 2)->default(0);
            $t->decimal('potongan_bpjs_kes', 12, 2)->default(0);
            $t->decimal('potongan_bpjs_tk', 12, 2)->default(0);
            $t->decimal('potongan_lainnya', 12, 2)->default(0);   // kasbon
            $t->string('catatan', 255)->nullable();

            // status periode: draft, final, dibayar
            $t->string('status', 10)->default('draft')->index();
            $t->json('hasil')->nullable();                    // snapshot saat final
            $t->decimal('total_pendapatan', 14, 2)->nullable();
            $t->decimal('total_potongan', 14, 2)->nullable();
            $t->decimal('gaji_bersih', 14, 2)->nullable();
            $t->timestamp('final_at')->nullable();
            $t->string('final_oleh', 100)->nullable();
            $t->timestamp('dibayar_at')->nullable();
            $t->timestamps();

            $t->unique(['periode', 'id_absen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_payroll');
    }
};