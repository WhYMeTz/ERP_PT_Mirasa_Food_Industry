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
        Schema::create('dat_kurs_harian', function (Blueprint $table) {
            $table->bigIncrements('kurs_id');
            $table->date('tanggal')->unique()->index();
            $table->string('mata_uang', 10)->default('USD');
            $table->decimal('kurs_tengah', 16, 2);
            $table->string('sumber', 50)->default('BANK_INDONESIA_JISDOR');
            $table->text('catatan')->nullable();

            // 8 Kolom Audit Trail Wajib AGENTS.md
            $table->string('created_by', 50)->nullable()->default('SYSTEM');
            $table->timestamp('created_dt')->useCurrent();
            $table->string('updated_by', 50)->nullable();
            $table->timestamp('updated_dt')->nullable();
            $table->boolean('active_st')->default(true);
            $table->boolean('deleted_st')->default(false);
            $table->integer('version_no')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_kurs_harian');
    }
};
