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
        Schema::table('dat_kas_keluar_hdr', function (Blueprint $table) {
            $table->foreignId('pemohon_karyawan_id')
                ->nullable()
                ->after('coa_id')
                ->constrained('mst_karyawan', 'karyawan_id')
                ->onDelete('set null');
            $table->string('departemen_pemohon', 50)->nullable()->after('pemohon_karyawan_id');
            $table->string('tipe_penerima', 30)->default('PIHAK_KETIGA')->after('departemen_pemohon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_kas_keluar_hdr', function (Blueprint $table) {
            $table->dropForeign(['pemohon_karyawan_id']);
            $table->dropColumn(['pemohon_karyawan_id', 'departemen_pemohon', 'tipe_penerima']);
        });
    }
};
