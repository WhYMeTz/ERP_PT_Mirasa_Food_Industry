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
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_packaging_hdr', 'jurnal_id')) {
                $table->foreignId('jurnal_id')->nullable()->after('status_cd')
                    ->constrained('dat_jurnal_hdr', 'jurnal_id')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_packaging_hdr', 'jurnal_id')) {
                $table->dropForeign(['jurnal_id']);
                $table->dropColumn('jurnal_id');
            }
        });
    }
};
