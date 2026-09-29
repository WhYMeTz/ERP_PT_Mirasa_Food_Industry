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
        Schema::table('dat_terima_hdr', function (Blueprint $table) {
            $table->decimal('subtotal_nominal', 16, 4)->default(0)->after('suratjalan_no')->comment('Subtotal nilai barang setelah diskon item');
            $table->decimal('potongan_nominal', 16, 4)->default(0)->after('subtotal_nominal')->comment('Potongan harga faktur / cash discount global');
            $table->decimal('dpp_nominal', 16, 4)->default(0)->after('potongan_nominal')->comment('Dasar Pengenaan Pajak (DPP)');
            $table->string('ppn_tipe', 20)->default('NON_PPN')->after('dpp_nominal')->comment('Tipe PPN: NON_PPN, PPN_11');
            $table->decimal('ppn_persen', 5, 2)->default(0)->after('ppn_tipe')->comment('Tarif PPN (0 atau 11.00)');
            $table->decimal('ppn_nominal', 16, 4)->default(0)->after('ppn_persen')->comment('Nominal PPN');
            $table->decimal('total_tagihan', 16, 4)->default(0)->after('ppn_nominal')->comment('Total akhir tagihan supplier (DPP + PPN)');
        });

        Schema::table('dat_terima_dtl', function (Blueprint $table) {
            $table->decimal('diskon_persen', 5, 2)->default(0)->after('harga_nominal')->comment('Diskon dagang per item (%)');
            $table->decimal('diskon_nominal', 16, 4)->default(0)->after('diskon_persen')->comment('Nominal diskon per unit');
            $table->decimal('harga_netto', 16, 4)->default(0)->after('diskon_nominal')->comment('Harga bersih per unit setelah diskon (HPP Stok)');
            $table->decimal('subtotal_netto', 16, 4)->default(0)->after('harga_netto')->comment('Subtotal netto baris item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_terima_hdr', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_nominal',
                'potongan_nominal',
                'dpp_nominal',
                'ppn_tipe',
                'ppn_persen',
                'ppn_nominal',
                'total_tagihan',
            ]);
        });

        Schema::table('dat_terima_dtl', function (Blueprint $table) {
            $table->dropColumn([
                'diskon_persen',
                'diskon_nominal',
                'harga_netto',
                'subtotal_netto',
            ]);
        });
    }
};
