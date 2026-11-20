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
        if (Schema::hasTable('dat_packaging_hdr')) {
            Schema::table('dat_packaging_hdr', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_packaging_hdr', 'shift')) {
                    $table->string('shift', 10)->default('A')->after('pack_tgl')->comment('Shift Kerja Kemas (A / B / C)');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'operator_nama')) {
                    $table->string('operator_nama', 150)->nullable()->after('shift')->comment('Nama Operator / Leader Kemas');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'karyawan_id')) {
                    $table->foreignId('karyawan_id')->nullable()->after('operator_nama')->constrained('mst_karyawan', 'karyawan_id')->nullOnDelete()->comment('Ref Master Karyawan');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'jumlah_personil')) {
                    $table->integer('jumlah_personil')->default(5)->after('karyawan_id')->comment('Jumlah Personil Packing');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'jumlah_mesin')) {
                    $table->integer('jumlah_mesin')->default(2)->after('jumlah_personil')->comment('Jumlah Mesin Aktif');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'hasil_pack_qty')) {
                    $table->decimal('hasil_pack_qty', 16, 4)->default(0)->after('hasil_karton_qty')->comment('Sisa Eceran / Tambahan Pack (Bungkus)');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'satuan_hasil')) {
                    $table->string('satuan_hasil', 20)->default('KARTON')->after('hasil_pack_qty')->comment('Satuan Hasil Utama (KARTON / BAL / PACK)');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_packaging_hdr')) {
            Schema::table('dat_packaging_hdr', function (Blueprint $table) {
                $cols = ['shift', 'operator_nama', 'karyawan_id', 'jumlah_personil', 'jumlah_mesin', 'hasil_pack_qty', 'satuan_hasil'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('dat_packaging_hdr', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

