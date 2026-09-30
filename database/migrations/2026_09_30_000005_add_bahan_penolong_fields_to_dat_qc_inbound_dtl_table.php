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
        Schema::table('dat_qc_inbound_dtl', function (Blueprint $table) {
            // Pemeriksaan Kondisi Isi Bahan Penolong (MSG, Garam, Perenyah)
            $table->boolean('isi_kering')->default(true)->after('status_raw_material');
            $table->boolean('isi_basah')->default(false)->after('isi_kering');
            $table->boolean('isi_gumpal')->default(false)->after('isi_basah');
            $table->boolean('isi_berminyak')->default(false)->after('isi_gumpal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_qc_inbound_dtl', function (Blueprint $table) {
            $table->dropColumn([
                'isi_kering',
                'isi_basah',
                'isi_gumpal',
                'isi_berminyak',
            ]);
        });
    }
};
