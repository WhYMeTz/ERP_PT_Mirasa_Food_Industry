<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mst_supplier') && Schema::hasColumn('mst_supplier', 'top_hari')) {
            Schema::table('mst_supplier', function (Blueprint $table) {
                $table->integer('top_hari')->nullable()->default(14)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mst_supplier') && Schema::hasColumn('mst_supplier', 'top_hari')) {
            Schema::table('mst_supplier', function (Blueprint $table) {
                $table->integer('top_hari')->default(14)->change();
            });
        }
    }
};
