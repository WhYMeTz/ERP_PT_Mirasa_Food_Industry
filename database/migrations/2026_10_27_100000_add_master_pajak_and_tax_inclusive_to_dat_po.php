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
            $table->boolean('is_harga_termasuk_pajak')
                ->default(false)
                ->after('status_cd')
                ->comment('Apakah harga item pada dokumen PO sudah termasuk PPN (Tax-Inclusive Pricing)');
        });

        Schema::table('dat_po_dtl', function (Blueprint $table) {
            $table->foreignId('pajak_id')
                ->nullable()
                ->after('subtotal_netto')
                ->constrained('mst_pajak', 'pajak_id')
                ->nullOnDelete();

            $table->boolean('is_harga_termasuk_pajak')
                ->default(false)
                ->after('pajak_id')
                ->comment('Apakah harga item baris ini sudah termasuk PPN');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_po_hdr', function (Blueprint $table) {
            $table->dropColumn('is_harga_termasuk_pajak');
        });

        Schema::table('dat_po_dtl', function (Blueprint $table) {
            $table->dropForeign(['pajak_id']);
            $table->dropColumn(['pajak_id', 'is_harga_termasuk_pajak']);
        });
    }
};
