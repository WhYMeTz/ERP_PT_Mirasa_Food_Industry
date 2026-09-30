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
            $table->decimal('subtotal_bruto', 16, 4)->default(0)->after('status_cd')->comment('Subtotal bruto sebelum diskon dan potongan');
            $table->decimal('diskon_total', 16, 4)->default(0)->after('subtotal_bruto')->comment('Akumulasi diskon item');
            $table->decimal('potongan_nominal', 16, 4)->default(0)->after('diskon_total')->comment('Akumulasi potongan nominal item');
            $table->decimal('dpp_nominal', 16, 4)->default(0)->after('potongan_nominal')->comment('Dasar Pengenaan Pajak (DPP)');
            $table->decimal('ppn_nominal', 16, 4)->default(0)->after('dpp_nominal')->comment('Akumulasi nilai PPN');
            $table->decimal('total_tagihan', 16, 4)->default(0)->after('total_nominal')->comment('Total akhir pesanan PO (DPP + PPN)');
        });

        Schema::table('dat_po_dtl', function (Blueprint $table) {
            $table->decimal('diskon_persen', 5, 2)->default(0)->after('harga_nominal')->comment('Diskon dagang per item (%)');
            $table->decimal('diskon_nominal', 16, 4)->default(0)->after('diskon_persen')->comment('Nominal diskon per unit');
            $table->decimal('potongan_nominal', 16, 4)->default(0)->after('diskon_nominal')->comment('Potongan langsung per baris (Rp)');
            $table->decimal('harga_netto', 16, 4)->default(0)->after('potongan_nominal')->comment('Harga bersih per unit setelah diskon');
            $table->decimal('subtotal_netto', 16, 4)->default(0)->after('harga_netto')->comment('Subtotal netto baris item sebelum PPN');
            $table->string('ppn_tipe', 10)->default('NON_PPN')->after('subtotal_nominal')->comment('NON_PPN atau PPN_11');
            $table->decimal('ppn_persen', 5, 2)->default(0)->after('ppn_tipe')->comment('Tarif PPN (0 atau 11.00)');
            $table->decimal('ppn_nominal', 16, 4)->default(0)->after('ppn_persen')->comment('Nominal PPN item');
            $table->decimal('subtotal_tagihan', 16, 4)->default(0)->after('ppn_nominal')->comment('Subtotal netto + PPN item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_po_hdr', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_bruto',
                'diskon_total',
                'potongan_nominal',
                'dpp_nominal',
                'ppn_nominal',
                'total_tagihan',
            ]);
        });

        Schema::table('dat_po_dtl', function (Blueprint $table) {
            $table->dropColumn([
                'diskon_persen',
                'diskon_nominal',
                'potongan_nominal',
                'harga_netto',
                'subtotal_netto',
                'ppn_tipe',
                'ppn_persen',
                'ppn_nominal',
                'subtotal_tagihan',
            ]);
        });
    }
};
