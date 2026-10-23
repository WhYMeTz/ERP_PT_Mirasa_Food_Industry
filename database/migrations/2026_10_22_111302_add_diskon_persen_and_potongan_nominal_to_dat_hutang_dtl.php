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
        Schema::table('dat_hutang_dtl', function (Blueprint $table) {
            $table->decimal('diskon_persen', 5, 2)->default(0)->after('diskon_nominal')->comment('Persentase diskon item (%)');
            $table->decimal('potongan_nominal', 16, 4)->default(0)->after('diskon_persen')->comment('Potongan khusus mutu/afkir item (Rp)');
        });

        Schema::table('dat_hutang_hdr', function (Blueprint $table) {
            $table->decimal('total_potongan_item', 16, 4)->default(0)->after('total_diskon')->comment('Akumulasi potongan mutu item (Rp)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_hutang_dtl', function (Blueprint $table) {
            $table->dropColumn(['diskon_persen', 'potongan_nominal']);
        });

        Schema::table('dat_hutang_hdr', function (Blueprint $table) {
            $table->dropColumn(['total_potongan_item']);
        });
    }
};
