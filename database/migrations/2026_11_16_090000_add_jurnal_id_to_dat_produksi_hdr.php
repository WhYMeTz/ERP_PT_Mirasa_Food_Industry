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
            if (!Schema::hasColumn('dat_produksi_hdr', 'jurnal_id')) {
                $table->foreignId('jurnal_id')->nullable()->after('catatan_txt')
                    ->constrained('dat_jurnal_hdr', 'jurnal_id')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'hpp_finalized_at')) {
                $table->timestamp('hpp_finalized_at')->nullable()->after('jurnal_id');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'hpp_finalized_by')) {
                $table->string('hpp_finalized_by', 100)->nullable()->after('hpp_finalized_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_produksi_hdr', 'jurnal_id')) {
                $table->dropForeign(['jurnal_id']);
                $table->dropColumn('jurnal_id');
            }
            if (Schema::hasColumn('dat_produksi_hdr', 'hpp_finalized_by')) {
                $table->dropColumn('hpp_finalized_by');
            }
            if (Schema::hasColumn('dat_produksi_hdr', 'hpp_finalized_at')) {
                $table->dropColumn('hpp_finalized_at');
            }
        });
    }
};
