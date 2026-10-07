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
            if (!Schema::hasColumn('dat_qc_inbound_hdr', 'posisi_bak')) {
                // BELAKANG (Pintu Bak), TENGAH (Tengah Muatan), DEPAN (Dekat Kabin)
                $table->string('posisi_bak', 30)->nullable()->after('tahap_uji');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_qc_inbound_hdr', 'posisi_bak')) {
                $table->dropColumn('posisi_bak');
            }
        });
    }
};
