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
            if (!Schema::hasColumn('dat_po_hdr', 'term_of_payment')) {
                $table->string('term_of_payment', 100)->nullable()->after('remark_txt');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_po_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_po_hdr', 'term_of_payment')) {
                $table->dropColumn('term_of_payment');
            }
        });
    }
};

