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
        Schema::table('mst_kas_bank', function (Blueprint $table) {
            $table->unsignedBigInteger('coa_id')->nullable()->after('tipe_akun');
            $table->foreign('coa_id')->references('coa_id')->on('mst_coa')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_kas_bank', function (Blueprint $table) {
            $table->dropForeign(['coa_id']);
            $table->dropColumn('coa_id');
        });
    }
};
