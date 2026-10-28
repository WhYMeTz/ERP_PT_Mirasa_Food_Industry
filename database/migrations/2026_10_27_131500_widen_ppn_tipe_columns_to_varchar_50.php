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
        // 1. Detail Purchase Order (Sebelumnya varchar(10), sering error jika kode pajak seperti PPH-22-KOMODITAS)
        if (Schema::hasTable('dat_po_dtl') && Schema::hasColumn('dat_po_dtl', 'ppn_tipe')) {
            Schema::table('dat_po_dtl', function (Blueprint $table) {
                $table->string('ppn_tipe', 50)->default('NON_PPN')->change();
            });
        }

        // 2. Detail Penerimaan Barang (Sebelumnya varchar(10))
        if (Schema::hasTable('dat_terima_dtl') && Schema::hasColumn('dat_terima_dtl', 'ppn_tipe')) {
            Schema::table('dat_terima_dtl', function (Blueprint $table) {
                $table->string('ppn_tipe', 50)->default('NON_PPN')->change();
            });
        }

        // 3. Header Penerimaan Barang (Sebelumnya varchar(20))
        if (Schema::hasTable('dat_terima_hdr') && Schema::hasColumn('dat_terima_hdr', 'ppn_tipe')) {
            Schema::table('dat_terima_hdr', function (Blueprint $table) {
                $table->string('ppn_tipe', 50)->default('NON_PPN')->change();
            });
        }

        // 4. Detail Hutang Usaha (Sebelumnya varchar(20))
        if (Schema::hasTable('dat_hutang_dtl') && Schema::hasColumn('dat_hutang_dtl', 'ppn_tipe')) {
            Schema::table('dat_hutang_dtl', function (Blueprint $table) {
                $table->string('ppn_tipe', 50)->default('NON_PPN')->change();
            });
        }

        // 5. Header Sales Order (Sebelumnya varchar(20))
        if (Schema::hasTable('dat_so_hdr') && Schema::hasColumn('dat_so_hdr', 'ppn_tipe')) {
            Schema::table('dat_so_hdr', function (Blueprint $table) {
                $table->string('ppn_tipe', 50)->default('NON_PPN')->change();
            });
        }

        // 6. Detail Sales Order (Sebelumnya varchar(20))
        if (Schema::hasTable('dat_so_dtl') && Schema::hasColumn('dat_so_dtl', 'ppn_tipe')) {
            Schema::table('dat_so_dtl', function (Blueprint $table) {
                $table->string('ppn_tipe', 50)->default('NON_PPN')->change();
            });
        }

        // 7. Header Faktur Penjualan (Sebelumnya varchar(20))
        if (Schema::hasTable('dat_faktur_hdr') && Schema::hasColumn('dat_faktur_hdr', 'ppn_tipe')) {
            Schema::table('dat_faktur_hdr', function (Blueprint $table) {
                $table->string('ppn_tipe', 50)->default('NON_PPN')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_po_dtl') && Schema::hasColumn('dat_po_dtl', 'ppn_tipe')) {
            Schema::table('dat_po_dtl', function (Blueprint $table) {
                $table->string('ppn_tipe', 10)->default('NON_PPN')->change();
            });
        }

        if (Schema::hasTable('dat_terima_dtl') && Schema::hasColumn('dat_terima_dtl', 'ppn_tipe')) {
            Schema::table('dat_terima_dtl', function (Blueprint $table) {
                $table->string('ppn_tipe', 10)->default('NON_PPN')->change();
            });
        }

        if (Schema::hasTable('dat_terima_hdr') && Schema::hasColumn('dat_terima_hdr', 'ppn_tipe')) {
            Schema::table('dat_terima_hdr', function (Blueprint $table) {
                $table->string('ppn_tipe', 20)->default('NON_PPN')->change();
            });
        }

        if (Schema::hasTable('dat_hutang_dtl') && Schema::hasColumn('dat_hutang_dtl', 'ppn_tipe')) {
            Schema::table('dat_hutang_dtl', function (Blueprint $table) {
                $table->string('ppn_tipe', 20)->default('NON_PPN')->change();
            });
        }

        if (Schema::hasTable('dat_so_hdr') && Schema::hasColumn('dat_so_hdr', 'ppn_tipe')) {
            Schema::table('dat_so_hdr', function (Blueprint $table) {
                $table->string('ppn_tipe', 20)->default('NON_PPN')->change();
            });
        }

        if (Schema::hasTable('dat_so_dtl') && Schema::hasColumn('dat_so_dtl', 'ppn_tipe')) {
            Schema::table('dat_so_dtl', function (Blueprint $table) {
                $table->string('ppn_tipe', 20)->default('NON_PPN')->change();
            });
        }

        if (Schema::hasTable('dat_faktur_hdr') && Schema::hasColumn('dat_faktur_hdr', 'ppn_tipe')) {
            Schema::table('dat_faktur_hdr', function (Blueprint $table) {
                $table->string('ppn_tipe', 20)->default('NON_PPN')->change();
            });
        }
    }
};
