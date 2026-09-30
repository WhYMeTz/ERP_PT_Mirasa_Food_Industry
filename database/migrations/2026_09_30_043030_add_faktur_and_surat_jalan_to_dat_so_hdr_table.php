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
        Schema::table('dat_so_hdr', function (Blueprint $table) {
            $table->string('faktur_no', 50)->nullable()->after('so_no');
            $table->string('surat_jalan_no', 50)->nullable()->after('faktur_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_so_hdr', function (Blueprint $table) {
            $table->dropColumn(['faktur_no', 'surat_jalan_no']);
        });
    }
};
