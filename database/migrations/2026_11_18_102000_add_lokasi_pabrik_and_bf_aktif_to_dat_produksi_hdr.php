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
            if (!Schema::hasColumn('dat_produksi_hdr', 'lokasi_pabrik')) {
                $table->string('lokasi_pabrik', 20)->default('BARAT')->after('lini_produksi');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'bf_aktif_count')) {
                $table->integer('bf_aktif_count')->nullable()->after('lokasi_pabrik');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'kulitan_qty')) {
                $table->decimal('kulitan_qty', 16, 4)->nullable()->default(0)->after('singkong_qty');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'kupasan_qty')) {
                $table->decimal('kupasan_qty', 16, 4)->nullable()->default(0)->after('kulitan_qty');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'sisa_singkong_qty')) {
                $table->decimal('sisa_singkong_qty', 16, 4)->nullable()->default(0)->after('kupasan_qty');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'reject_singkong_qty')) {
                $table->decimal('reject_singkong_qty', 16, 4)->nullable()->default(0)->after('sisa_singkong_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $cols = ['lokasi_pabrik', 'bf_aktif_count', 'kulitan_qty', 'kupasan_qty', 'sisa_singkong_qty', 'reject_singkong_qty'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('dat_produksi_hdr', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
