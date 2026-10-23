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
        Schema::table('mst_customer', function (Blueprint $table) {
            $table->string('top_txt', 50)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_customer', function (Blueprint $table) {
            $table->string('top_txt', 50)->nullable(false)->default('Tinggal 1 Nota')->change();
        });
    }
};
