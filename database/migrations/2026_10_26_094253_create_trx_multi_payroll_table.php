<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat file Multi Payroll BCA yang pernah diunduh. Satu baris = satu file.
        // header_id unik supaya nomor header tidak pernah dobel.
        Schema::create('trx_multi_payroll', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('header_id')->unique();
            $t->char('periode', 7)->index();            // periode gaji, mis. 2026-08
            $t->string('bagian', 50);                   // mis. SORTIR AC
            $t->string('kode', 10);                     // kode di ID transaksi, mis. AC
            $t->date('tanggal_transfer');
            $t->string('remarks1', 50)->nullable();
            $t->string('remarks2', 50)->nullable();
            $t->unsignedInteger('jumlah_baris');
            $t->unsignedBigInteger('total');            // total rupiah yang ditransfer
            $t->string('nama_file', 150);
            $t->string('dibuat_oleh', 100)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_multi_payroll');
    }
};