<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah harga beli standar ke master barang
        Schema::table('mst_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_barang', 'harga_beli_standar')) {
                $table->decimal('harga_beli_standar', 16, 4)->default(0)->after('batas_minimum_qty');
            }
        });

        // 2. Tambah qty_awal dan harga_satuan ke dat_stok_batch untuk menyelaraskan dengan Sheet "Lacak Stok"
        Schema::table('dat_stok_batch', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_stok_batch', 'qty_awal')) {
                $table->decimal('qty_awal', 16, 4)->default(0)->after('expired_tgl');
            }
            if (!Schema::hasColumn('dat_stok_batch', 'harga_satuan')) {
                $table->decimal('harga_satuan', 16, 4)->default(0)->after('qty_awal');
            }
        });

        // Backfill qty_awal dari sisa_qty untuk data yang sudah ada
        DB::statement("UPDATE dat_stok_batch SET qty_awal = sisa_qty WHERE qty_awal = 0 AND sisa_qty > 0");

        // 3. Tabel Header Barang Keluar / Pemakaian Bahan (Sheet "Barang Keluar")
        if (!Schema::hasTable('dat_pakai_hdr')) {
            Schema::create('dat_pakai_hdr', function (Blueprint $table) {
                $table->id('pakai_id');
                $table->string('pakai_no', 100)->unique();
                $table->date('pakai_tgl');
                $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
                $table->string('tujuan_pemakaian', 150)->comment('Contoh: PRODUKSI IFM, PACKING EKSPOR, SEASONING XX, GMP');
                $table->text('catatan_txt')->nullable();

                // 8 Kolom Audit Trail Standar ERP Mirasa
                $table->timestamp('created_at')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
            });
        }

        // 4. Tabel Detail Barang Keluar / Pemakaian Bahan
        if (!Schema::hasTable('dat_pakai_dtl')) {
            Schema::create('dat_pakai_dtl', function (Blueprint $table) {
                $table->id('pakaidtl_id');
                $table->foreignId('pakai_id')->constrained('dat_pakai_hdr', 'pakai_id')->onDelete('cascade');
                $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
                $table->string('batch_no', 100);
                $table->decimal('qty_keluar', 16, 4);
                $table->decimal('harga_satuan', 16, 4)->default(0);
                $table->decimal('total_harga', 16, 4)->default(0);
                $table->text('keterangan_txt')->nullable();

                // 8 Kolom Audit Trail Standar ERP Mirasa
                $table->timestamp('created_at')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_pakai_dtl');
        Schema::dropIfExists('dat_pakai_hdr');

        Schema::table('dat_stok_batch', function (Blueprint $table) {
            $table->dropColumn(['qty_awal', 'harga_satuan']);
        });

        Schema::table('mst_barang', function (Blueprint $table) {
            $table->dropColumn(['harga_beli_standar']);
        });
    }
};
