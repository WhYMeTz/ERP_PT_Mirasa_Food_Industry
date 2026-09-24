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
        Schema::create('mst_karyawan', function (Blueprint $table) {
            $table->id('karyawan_id');
            $table->string('nik', 50)->unique()->comment('Nomor Induk Karyawan');
            $table->string('karyawan_nm', 100);
            $table->string('departemen_cd', 50)->comment('PRODUKSI, GUDANG, PURCHASING, FINANCE, QC, HRD, MANAJEMEN');
            $table->string('jabatan_nm', 100);
            $table->string('telepon_no', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat_txt')->nullable();

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_karyawan');
    }
};
