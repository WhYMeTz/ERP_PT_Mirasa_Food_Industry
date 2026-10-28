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
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $table->decimal('bumbu_qty', 16, 4)->default(0)->after('bumbu_nilai')->comment('Kuantitas Pemakaian Bumbu/Garam (Kg)');
            $table->decimal('karton_baru_qty', 16, 2)->default(0)->after('karton_baru_nilai')->comment('Kuantitas Karton Baru (Pcs Box)');
            $table->decimal('karton_bekas_qty', 16, 2)->default(0)->after('karton_bekas_nilai')->comment('Kuantitas Karton Bekas (Pcs Box)');
            $table->decimal('plastik_hd_qty', 16, 2)->default(0)->after('plastik_hd_nilai')->comment('Kuantitas Plastik HD (Roll/Kg)');
            $table->decimal('lakban_besar_qty', 16, 2)->default(0)->after('lakban_besar_nilai')->comment('Kuantitas Lakban Coklat Besar (Roll)');
            $table->decimal('lakban_kecil_qty', 16, 2)->default(0)->after('lakban_kecil_nilai')->comment('Kuantitas Lakban Bening Kecil (Roll)');
            $table->decimal('tali_rafia_qty', 16, 2)->default(0)->after('tali_rafia_nilai')->comment('Kuantitas Tali Rafia (Roll)');
            $table->decimal('sarung_tangan_plastik_qty', 16, 2)->default(0)->after('sarung_tangan_plastik_nilai')->comment('Kuantitas Sarung Tangan Plastik (Pcs/Box)');
            $table->decimal('sarung_tangan_kain_qty', 16, 2)->default(0)->after('sarung_tangan_kain_nilai')->comment('Kuantitas Sarung Tangan Kain (Pasang)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $table->dropColumn([
                'bumbu_qty',
                'karton_baru_qty',
                'karton_bekas_qty',
                'plastik_hd_qty',
                'lakban_besar_qty',
                'lakban_kecil_qty',
                'tali_rafia_qty',
                'sarung_tangan_plastik_qty',
                'sarung_tangan_kain_qty',
            ]);
        });
    }
};
