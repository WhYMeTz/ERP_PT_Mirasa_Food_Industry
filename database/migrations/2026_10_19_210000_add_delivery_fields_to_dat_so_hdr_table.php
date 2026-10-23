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
        Schema::table('dat_so_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_so_hdr', 'tgl_kirim_aktual')) {
                $table->date('tgl_kirim_aktual')->nullable()->after('tgl_kirim_estimasi')->comment('Tanggal riil pengiriman / Surat Jalan diberangkatkan');
            }
            if (!Schema::hasColumn('dat_so_hdr', 'supir_nm')) {
                $table->string('supir_nm', 100)->nullable()->after('tgl_kirim_aktual')->comment('Nama Supir / Pengemudi Armada Ekspedisi');
            }
            if (!Schema::hasColumn('dat_so_hdr', 'nopol_kendaraan')) {
                $table->string('nopol_kendaraan', 30)->nullable()->after('supir_nm')->comment('Nomor Polisi Kendaraan Pengiriman');
            }
            if (!Schema::hasColumn('dat_so_hdr', 'total_terkirim_qty')) {
                $table->decimal('total_terkirim_qty', 14, 4)->default(0)->after('nopol_kendaraan')->comment('Akumulasi kuantitas karton terkirim');
            }
            if (!Schema::hasColumn('dat_so_hdr', 'version_no')) {
                $table->integer('version_no')->default(1)->after('active_st')->comment('Optimistic locking & audit trail');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_so_hdr', function (Blueprint $table) {
            $cols = ['tgl_kirim_aktual', 'supir_nm', 'nopol_kendaraan', 'total_terkirim_qty', 'version_no'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('dat_so_hdr', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
