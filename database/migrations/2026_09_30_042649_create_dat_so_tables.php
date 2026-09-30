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
        if (!Schema::hasTable('dat_so_hdr')) {
            Schema::create('dat_so_hdr', function (Blueprint $table) {
                $table->id('so_id');
                $table->string('so_no', 50)->unique();
                $table->date('so_tgl');
                $table->foreignId('customer_id')->constrained('mst_customer', 'customer_id');
                $table->string('customer_po_no', 100)->nullable();
                $table->date('tgl_kirim_estimasi')->nullable();
                $table->string('status_cd', 20)->default('APPROVED'); // APPROVED, PROCESSING, PARTIAL, COMPLETED, CANCELLED
                $table->text('catatan_txt')->nullable();

                // Komersial & Nilai Tagihan
                $table->decimal('subtotal_bruto', 15, 4)->default(0);
                $table->decimal('diskon_total', 15, 4)->default(0);
                $table->decimal('potongan_nominal', 15, 4)->default(0);
                $table->decimal('dpp_nominal', 15, 4)->default(0);
                $table->string('ppn_tipe', 20)->default('NON_PPN'); // NON_PPN, PPN_11, MIXED
                $table->decimal('ppn_persen', 5, 2)->default(0);
                $table->decimal('ppn_nominal', 15, 4)->default(0);
                $table->decimal('total_tagihan', 15, 4)->default(0);

                // Audit Trails
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dat_so_dtl')) {
            Schema::create('dat_so_dtl', function (Blueprint $table) {
                $table->id('sodtl_id');
                $table->foreignId('so_id')->constrained('dat_so_hdr', 'so_id')->cascadeOnDelete();
                $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id');
                $table->decimal('pesan_qty', 14, 4);
                $table->decimal('kirim_qty', 14, 4)->default(0);
                $table->decimal('harga_satuan', 15, 4)->default(0);

                // Diskon & Potongan per Baris
                $table->decimal('diskon_persen', 5, 2)->default(0);
                $table->decimal('diskon_nominal', 15, 4)->default(0);
                $table->decimal('potongan_nominal', 15, 4)->default(0);
                $table->decimal('harga_netto', 15, 4)->default(0);
                $table->decimal('subtotal_netto', 15, 4)->default(0);

                // Pajak per Baris
                $table->string('ppn_tipe', 20)->default('NON_PPN'); // NON_PPN, PPN_11
                $table->decimal('ppn_persen', 5, 2)->default(0);
                $table->decimal('ppn_nominal', 15, 4)->default(0);
                $table->decimal('subtotal_tagihan', 15, 4)->default(0);

                $table->text('catatan_txt')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_so_dtl');
        Schema::dropIfExists('dat_so_hdr');
    }
};
