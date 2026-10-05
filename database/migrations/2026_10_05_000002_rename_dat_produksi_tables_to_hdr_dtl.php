<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Standarisasi tabel produksi agar konsisten dengan konvensi ERP PT Mirasa (_hdr dan _dtl).
     */
    public function up(): void
    {
        // 1. Rename tabel dat_produksi_harian -> dat_produksi_hdr
        if (Schema::hasTable('dat_produksi_harian') && !Schema::hasTable('dat_produksi_hdr')) {
            Schema::rename('dat_produksi_harian', 'dat_produksi_hdr');
        }

        // 2. Rename tabel dat_produksi_output -> dat_produksi_dtl
        if (Schema::hasTable('dat_produksi_output') && !Schema::hasTable('dat_produksi_dtl')) {
            Schema::rename('dat_produksi_output', 'dat_produksi_dtl');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan nama tabel semula
        if (Schema::hasTable('dat_produksi_dtl') && !Schema::hasTable('dat_produksi_output')) {
            Schema::rename('dat_produksi_dtl', 'dat_produksi_output');
        }

        if (Schema::hasTable('dat_produksi_hdr') && !Schema::hasTable('dat_produksi_harian')) {
            Schema::rename('dat_produksi_hdr', 'dat_produksi_harian');
        }
    }
};
