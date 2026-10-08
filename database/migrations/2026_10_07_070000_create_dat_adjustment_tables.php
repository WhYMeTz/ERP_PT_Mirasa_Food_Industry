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
        // 1. Header Dokumen Penyesuaian Stok (Stock Adjustment / Opname)
        Schema::create('dat_adjustment_hdr', function (Blueprint $table) {
            $table->id('adj_id');
            $table->string('adj_no', 100)->unique();
            $table->date('adj_tgl');
            $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
            $table->string('kategori_adj', 50)->default('OPNAME_RUTIN')->comment('OPNAME_RUTIN, SUSUT_MINYAK, SUSUT_ALAMI, KERUSAKAN, SELISIH_TIMBANG, LAINNYA');
            $table->text('catatan_txt')->nullable();
            $table->integer('total_item')->default(0);
            $table->decimal('total_selisih_qty', 16, 4)->default(0);
            $table->decimal('total_selisih_nilai', 16, 4)->default(0);
            $table->string('status_cd', 20)->default('POSTED')->comment('DRAFT, POSTED, VOID');

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
            $table->integer('version_no')->default(1);

            $table->index(['gudang_id', 'adj_tgl'], 'idx_adj_gudang_tgl');
        });

        // 2. Detail Rincian Barang Penyesuaian Stok (Sesuai Blueprint Kolom a-l)
        Schema::create('dat_adjustment_dtl', function (Blueprint $table) {
            $table->id('adjdtl_id');
            $table->foreignId('adj_id')->constrained('dat_adjustment_hdr', 'adj_id')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
            $table->string('batch_no', 100)->nullable();

            // Sesuai Blueprint:
            // d. Persediaan Awal (Sistem)
            $table->decimal('stok_sistem_qty', 16, 4)->default(0);
            // e, h, k. @Harga satuan
            $table->decimal('harga_satuan', 16, 4)->default(0);
            // f. Total Persediaan Awal (d * e)
            $table->decimal('total_sistem_nilai', 16, 4)->default(0);

            // g. Persediaan Gudang (Fisik Riil)
            $table->decimal('stok_fisik_qty', 16, 4)->default(0);
            // i. Total Persediaan Gudang (g * h)
            $table->decimal('total_fisik_nilai', 16, 4)->default(0);

            // j. Selisih Persediaan (g - d)
            $table->decimal('selisih_qty', 16, 4)->default(0);
            // l. Total Selisih (j * k)
            $table->decimal('total_selisih_nilai', 16, 4)->default(0);

            $table->string('alasan_txt', 255)->nullable()->comment('Alasan spesifik selisih per item (misal: residu minyak, susut singkong)');

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
            $table->integer('version_no')->default(1);

            $table->index(['adj_id', 'barang_id'], 'idx_adjdtl_adj_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_adjustment_dtl');
        Schema::dropIfExists('dat_adjustment_hdr');
    }
};
