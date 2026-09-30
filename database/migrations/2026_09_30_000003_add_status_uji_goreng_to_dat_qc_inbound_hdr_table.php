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
        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            // SELESAI (Pengujian I & II lengkap) vs MENUNGGU_LAB (Pengujian I selesai, uji goreng menyusul)
            $table->string('status_uji_goreng', 30)->default('SELESAI')->after('status_qc');
            $table->timestamp('tgl_uji_goreng')->nullable()->after('status_uji_goreng');
            $table->string('petugas_uji_goreng', 100)->nullable()->after('tgl_uji_goreng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            $table->dropColumn(['status_uji_goreng', 'tgl_uji_goreng', 'petugas_uji_goreng']);
        });
    }
};
