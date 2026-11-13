<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('dat_packaging_hdr') && !Schema::hasColumn('dat_packaging_hdr', 'is_hpp_final')) {
            Schema::table('dat_packaging_hdr', function (Blueprint $table) {
                $table->boolean('is_hpp_final')->default(false)->after('hpp_per_pcs')->comment('Flag status finalisasi HPP Barang Jadi oleh Keuangan');
                $table->timestamp('hpp_finalized_at')->nullable()->after('is_hpp_final');
                $table->string('hpp_finalized_by', 100)->nullable()->after('hpp_finalized_at');
            });

            // Inisialisasi record yang sudah memiliki HPP per karton valid
            DB::table('dat_packaging_hdr')
                ->where('hpp_per_karton', '>', 0)
                ->where('deleted_st', false)
                ->update([
                    'is_hpp_final'      => true,
                    'hpp_finalized_at'  => now(),
                    'hpp_finalized_by'  => 'System (Migration Init)',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_packaging_hdr') && Schema::hasColumn('dat_packaging_hdr', 'is_hpp_final')) {
            Schema::table('dat_packaging_hdr', function (Blueprint $table) {
                $table->dropColumn(['is_hpp_final', 'hpp_finalized_at', 'hpp_finalized_by']);
            });
        }
    }
};

