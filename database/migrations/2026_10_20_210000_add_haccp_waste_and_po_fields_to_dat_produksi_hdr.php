<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom pencatatan limbah fisik HACCP (Lengket, Berko IFL, Berko Manual)
     * dan pemenuhan PO IFL sesuai formulir MFI/HACCP-04/FRM-03/045/VIII/2021.
     */
    public function up(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_produksi_hdr', 'waste_lengket_qty')) {
                $table->decimal('waste_lengket_qty', 16, 4)->default(0)->after('total_berko_qty')->comment('Limbah Singkong Lengket (Kg)');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'berko_ifl_qty')) {
                $table->decimal('berko_ifl_qty', 16, 4)->default(0)->after('waste_lengket_qty')->comment('Remah/Berko Lini IFL (Kg)');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'berko_non_ifl_qty')) {
                $table->decimal('berko_non_ifl_qty', 16, 4)->default(0)->after('berko_ifl_qty')->comment('Remah/Berko Lini Manual Non-IFL (Kg)');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'waste_total_qty')) {
                $table->decimal('waste_total_qty', 16, 4)->default(0)->after('berko_non_ifl_qty')->comment('Total Limbah/Waste (Kg)');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'waste_lengket_persen')) {
                $table->decimal('waste_lengket_persen', 8, 2)->default(0)->after('berko_persen')->comment('Persentase Lengket (%)');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'po_ifl_no')) {
                $table->string('po_ifl_no', 50)->nullable()->after('catatan_txt')->comment('Nomor Dokumen PO Buyer IFL');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'po_ifl_realisasi_qty')) {
                $table->decimal('po_ifl_realisasi_qty', 16, 4)->default(0)->after('po_ifl_no')->comment('Realisasi Pengiriman PO IFL (Kg)');
            }
            if (!Schema::hasColumn('dat_produksi_hdr', 'po_ifl_terpenuhi_st')) {
                $table->boolean('po_ifl_terpenuhi_st')->nullable()->after('po_ifl_realisasi_qty')->comment('Status Pemenuhan PO IFL (1: Terpenuhi, 0: Tidak)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_produksi_hdr', function (Blueprint $table) {
            $columns = [
                'waste_lengket_qty',
                'berko_ifl_qty',
                'berko_non_ifl_qty',
                'waste_total_qty',
                'waste_lengket_persen',
                'po_ifl_no',
                'po_ifl_realisasi_qty',
                'po_ifl_terpenuhi_st',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('dat_produksi_hdr', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
