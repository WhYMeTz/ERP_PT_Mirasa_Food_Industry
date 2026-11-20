<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_slip_wa', function (Blueprint $t) {
            $t->id();
            $t->string('periode', 7);                    // 2026-08
            $t->string('nik', 50)->nullable();
            $t->string('id_absen', 20)->nullable();
            $t->string('nama', 150)->nullable();
            $t->string('nomor', 20)->nullable();         // 628...
            $t->string('sumber', 15)->default('massal'); // massal | permintaan
            $t->string('status', 20);                    // terkirim_api | delivered | read | failed | dilewati
            $t->text('detail')->nullable();
            $t->string('wa_message_id', 120)->nullable()->index();
            $t->string('dikirim_oleh', 100)->nullable();
            $t->timestamps();

            $t->index(['periode', 'nik']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_slip_wa');
    }
};
