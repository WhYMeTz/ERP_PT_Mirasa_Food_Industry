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
            if (!Schema::hasColumn('dat_so_hdr', 'top_txt')) {
                $table->string('top_txt', 50)->nullable()->after('customer_po_no')->comment('Syarat Pembayaran / Terms of Payment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_so_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_so_hdr', 'top_txt')) {
                $table->dropColumn('top_txt');
            }
        });
    }
};
