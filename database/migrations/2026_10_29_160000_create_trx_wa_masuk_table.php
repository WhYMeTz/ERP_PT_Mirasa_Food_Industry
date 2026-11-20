<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_wa_masuk', function (Blueprint $t) {
            $t->id();
            $t->string('message_id', 120)->unique();       // wamid dari Meta; unik agar event ganda diabaikan
            $t->string('nomor', 20);                       // pengirim, 628...
            $t->string('tipe', 20)->nullable();            // text | image | ...
            $t->text('isi')->nullable();
            $t->string('status_balasan', 20)->nullable();  // slip_terkirim | bantuan | tidak_ditemukan | ambigu | slip_belum_ada | gagal
            $t->text('detail')->nullable();
            $t->timestamps();

            $t->index('nomor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_wa_masuk');
    }
};
