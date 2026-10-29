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
        if (Schema::hasTable('mst_supplier') && !Schema::hasColumn('mst_supplier', 'email')) {
            Schema::table('mst_supplier', function (Blueprint $table) {
                $table->string('email', 150)->nullable()->after('kontak_no')->comment('Email resmi supplier untuk pengiriman dokumen PO & invoice');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mst_supplier') && Schema::hasColumn('mst_supplier', 'email')) {
            Schema::table('mst_supplier', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }
};
