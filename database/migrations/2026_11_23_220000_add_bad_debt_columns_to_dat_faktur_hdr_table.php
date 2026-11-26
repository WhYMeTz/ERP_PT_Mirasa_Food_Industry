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
        Schema::table('dat_faktur_hdr', function (Blueprint $table) {
            $table->date('tgl_macet')->nullable()->after('catatan_txt');
            $table->text('alasan_macet')->nullable()->after('tgl_macet');
            $table->string('macet_by', 150)->nullable()->after('alasan_macet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_faktur_hdr', function (Blueprint $table) {
            $table->dropColumn(['tgl_macet', 'alasan_macet', 'macet_by']);
        });
    }
};
