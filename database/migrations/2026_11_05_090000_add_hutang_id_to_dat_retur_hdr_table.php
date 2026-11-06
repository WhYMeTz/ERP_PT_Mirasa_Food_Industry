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
        Schema::table('dat_retur_hdr', function (Blueprint $table) {
            $table->foreignId('hutang_id')
                ->nullable()
                ->after('terima_id')
                ->constrained('dat_hutang_hdr', 'hutang_id')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_retur_hdr', function (Blueprint $table) {
            $table->dropForeign(['hutang_id']);
            $table->dropColumn('hutang_id');
        });
    }
};

