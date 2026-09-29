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
        Schema::table('dat_terima_dtl', function (Blueprint $table) {
            $table->decimal('potongan_nominal', 18, 2)->default(0)->after('diskon_nominal')->comment('Potongan khusus item ini (Rp)');
            $table->string('ppn_tipe', 10)->default('NON_PPN')->after('subtotal_netto')->comment('NON_PPN atau PPN_11');
            $table->decimal('ppn_persen', 5, 2)->default(0)->after('ppn_tipe');
            $table->decimal('ppn_nominal', 18, 2)->default(0)->after('ppn_persen');
            $table->decimal('subtotal_tagihan', 18, 2)->default(0)->after('ppn_nominal')->comment('Subtotal netto + PPN item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_terima_dtl', function (Blueprint $table) {
            $table->dropColumn(['potongan_nominal', 'ppn_tipe', 'ppn_persen', 'ppn_nominal', 'subtotal_tagihan']);
        });
    }
};
