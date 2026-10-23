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
            $table->string('klasifikasi', 20)->default('MANUAL')->after('customer_nm');
            $table->string('wilayah', 100)->nullable()->after('klasifikasi');
            $table->string('kode_pelanggan', 50)->nullable()->after('wilayah');
            $table->string('top_txt', 50)->default('Tinggal 1 Nota')->after('kode_pelanggan');
            $table->string('default_ppn', 20)->default('NON_PPN')->after('top_txt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_customer', function (Blueprint $table) {
            $table->dropColumn(['klasifikasi', 'wilayah', 'kode_pelanggan', 'top_txt', 'default_ppn']);
        });
    }
};
