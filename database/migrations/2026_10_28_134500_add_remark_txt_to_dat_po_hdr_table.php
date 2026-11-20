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
        Schema::table('dat_po_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_po_hdr', 'remark_txt')) {
                $table->text('remark_txt')->nullable()->after('catatan_txt');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_po_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_po_hdr', 'remark_txt')) {
                $table->dropColumn('remark_txt');
            }
        });
    }
};

