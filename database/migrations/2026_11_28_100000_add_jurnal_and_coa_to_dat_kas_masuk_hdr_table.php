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
        Schema::table('dat_kas_masuk_hdr', function (Blueprint $table) {
            $table->foreignId('jurnal_id')
                ->nullable()
                ->after('catatan')
                ->constrained('dat_jurnal_hdr', 'jurnal_id')
                ->onDelete('set null');

            $table->foreignId('coa_id')
                ->nullable()
                ->after('kas_bank_id')
                ->constrained('mst_coa', 'coa_id')
                ->onDelete('restrict');

            $table->string('status_cd', 20)->default('DRAFT')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_kas_masuk_hdr', function (Blueprint $table) {
            $table->dropForeign(['jurnal_id']);
            $table->dropForeign(['coa_id']);
            $table->dropColumn(['jurnal_id', 'coa_id']);
            $table->string('status_cd', 20)->default('POSTED')->change();
        });
    }
};
