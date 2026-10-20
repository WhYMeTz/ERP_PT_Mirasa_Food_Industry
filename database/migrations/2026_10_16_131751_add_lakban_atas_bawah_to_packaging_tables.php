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
        if (Schema::hasTable('dat_laporan_kemas_operator')) {
            Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_laporan_kemas_operator', 'lakban_atas_gr')) {
                    $table->decimal('lakban_atas_gr', 16, 4)->default(0)->after('karton_rusak_lembar')->comment('Timbangan lakban atas karton (gram)');
                }
                if (!Schema::hasColumn('dat_laporan_kemas_operator', 'lakban_bawah_gr')) {
                    $table->decimal('lakban_bawah_gr', 16, 4)->default(0)->after('lakban_atas_gr')->comment('Timbangan lakban bawah karton (gram)');
                }
            });
        }

        if (Schema::hasTable('dat_packaging_hdr')) {
            Schema::table('dat_packaging_hdr', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_packaging_hdr', 'lakban_atas_gr')) {
                    $table->decimal('lakban_atas_gr', 16, 4)->default(0)->after('total_biaya_kemasan')->comment('Total pemakaian lakban atas (gram)');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'lakban_bawah_gr')) {
                    $table->decimal('lakban_bawah_gr', 16, 4)->default(0)->after('lakban_atas_gr')->comment('Total pemakaian lakban bawah (gram)');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'lakban_jumlah_gr')) {
                    $table->decimal('lakban_jumlah_gr', 16, 4)->default(0)->after('lakban_bawah_gr')->comment('Total pemakaian lakban atas + bawah (gram)');
                }
                if (!Schema::hasColumn('dat_packaging_hdr', 'lakban_nilai')) {
                    $table->decimal('lakban_nilai', 16, 4)->default(0)->after('lakban_jumlah_gr')->comment('Nilai rupiah lakban');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_laporan_kemas_operator')) {
            Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
                $cols = ['lakban_atas_gr', 'lakban_bawah_gr'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('dat_laporan_kemas_operator', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('dat_packaging_hdr')) {
            Schema::table('dat_packaging_hdr', function (Blueprint $table) {
                $cols = ['lakban_atas_gr', 'lakban_bawah_gr', 'lakban_jumlah_gr', 'lakban_nilai'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('dat_packaging_hdr', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
