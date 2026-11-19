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
        // 1. Tambah gudang_tujuan_id pada dat_pakai_hdr (jika belum ada)
        if (Schema::hasTable('dat_pakai_hdr') && !Schema::hasColumn('dat_pakai_hdr', 'gudang_tujuan_id')) {
            Schema::table('dat_pakai_hdr', function (Blueprint $table) {
                $table->unsignedBigInteger('gudang_tujuan_id')->nullable()->after('gudang_id');
                $table->foreign('gudang_tujuan_id')->references('gudang_id')->on('mst_gudang')->onDelete('set null');
            });
        }

        // 2. Buat tabel dat_transfer_hdr (Header Dokumen Transfer Antar-Gudang)
        if (!Schema::hasTable('dat_transfer_hdr')) {
            Schema::create('dat_transfer_hdr', function (Blueprint $table) {
                $table->bigIncrements('transfer_id');
                $table->string('transfer_no', 50)->unique();
                $table->date('transfer_tgl');
                $table->unsignedBigInteger('gudang_asal_id');
                $table->unsignedBigInteger('gudang_tujuan_id');
                $table->string('no_surat_jalan', 100)->nullable();
                $table->string('sopir_nama', 100)->nullable();
                $table->string('nopol_kendaraan', 50)->nullable();
                $table->text('catatan_txt')->nullable();
                $table->string('status_st', 20)->default('POSTED'); // POSTED, VOID
                $table->decimal('total_qty', 16, 4)->default(0);
                $table->decimal('total_nilai', 16, 4)->default(0);
                
                // 8 Kolom Audit Trail Wajib ERP Mirasa
                $table->string('created_by', 100)->default('SYSTEM');
                $table->timestamp('created_at')->useCurrent();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);

                $table->foreign('gudang_asal_id')->references('gudang_id')->on('mst_gudang');
                $table->foreign('gudang_tujuan_id')->references('gudang_id')->on('mst_gudang');

                $table->index(['transfer_tgl', 'status_st']);
                $table->index('gudang_asal_id');
                $table->index('gudang_tujuan_id');
            });
        }

        // 3. Buat tabel dat_transfer_dtl (Detail Rincian Barang & Batch yang Dipindahkan)
        if (!Schema::hasTable('dat_transfer_dtl')) {
            Schema::create('dat_transfer_dtl', function (Blueprint $table) {
                $table->bigIncrements('transfer_dtl_id');
                $table->unsignedBigInteger('transfer_id');
                $table->unsignedBigInteger('barang_id');
                $table->string('batch_no', 100);
                $table->string('grade_cd', 10)->default('A');
                $table->date('expired_tgl')->nullable();
                $table->decimal('qty_transfer', 16, 4);
                $table->decimal('harga_satuan', 16, 4)->default(0);
                $table->decimal('total_harga', 16, 4)->default(0);
                $table->string('catatan_txt', 255)->nullable();

                // 8 Kolom Audit Trail Wajib ERP Mirasa
                $table->string('created_by', 100)->default('SYSTEM');
                $table->timestamp('created_at')->useCurrent();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);

                $table->foreign('transfer_id')->references('transfer_id')->on('dat_transfer_hdr')->onDelete('cascade');
                $table->foreign('barang_id')->references('barang_id')->on('mst_barang');

                $table->index(['transfer_id', 'barang_id']);
                $table->index('batch_no');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_transfer_dtl');
        Schema::dropIfExists('dat_transfer_hdr');

        if (Schema::hasTable('dat_pakai_hdr') && Schema::hasColumn('dat_pakai_hdr', 'gudang_tujuan_id')) {
            Schema::table('dat_pakai_hdr', function (Blueprint $table) {
                $table->dropForeign(['gudang_tujuan_id']);
                $table->dropColumn('gudang_tujuan_id');
            });
        }
    }
};
