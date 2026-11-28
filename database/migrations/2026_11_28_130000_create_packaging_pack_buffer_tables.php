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
        // 1. Tambah kolom tarik_pack_lalu_qty pada dat_packaging_hdr
        if (Schema::hasTable('dat_packaging_hdr')) {
            Schema::table('dat_packaging_hdr', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_packaging_hdr', 'tarik_pack_lalu_qty')) {
                    $table->decimal('tarik_pack_lalu_qty', 16, 4)->default(0)->after('hasil_pack_qty')->comment('Jumlah sisa pack pengemasan sebelumnya yang ditarik/digabungkan ke dokumen ini');
                }
            });
        }

        // 2. Tambah kolom tarik_pack_lalu_qty pada dat_laporan_kemas_operator
        if (Schema::hasTable('dat_laporan_kemas_operator')) {
            Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_laporan_kemas_operator', 'tarik_pack_lalu_qty')) {
                    $table->decimal('tarik_pack_lalu_qty', 16, 4)->default(0)->after('hasil_pack_qty')->comment('Jumlah sisa pack pengemasan sebelumnya yang ditarik/digabungkan oleh operator');
                }
            });
        }

        // 3. Buat tabel mutasi buffer sisa kemas per produk
        if (!Schema::hasTable('dat_packaging_buffer_log')) {
            Schema::create('dat_packaging_buffer_log', function (Blueprint $table) {
                $table->id('buffer_id');
                $table->foreignId('barang_jadi_id')->constrained('mst_barang', 'barang_id')->cascadeOnDelete();
                $table->foreignId('pack_id')->nullable()->constrained('dat_packaging_hdr', 'pack_id')->cascadeOnDelete();
                $table->foreignId('laporan_id')->nullable()->constrained('dat_laporan_kemas_operator', 'laporan_id')->nullOnDelete();
                $table->date('transaksi_tgl');
                $table->string('tipe_transaksi', 20)->default('IN')->comment('IN (Sisa Baru), OUT (Ditarik Kemas), ADJUST (Penyesuaian Fisik/Sampel/Afkir)');
                $table->decimal('qty_pack', 16, 4)->default(0);
                $table->decimal('saldo_akhir_pack', 16, 4)->default(0);
                $table->string('keterangan_txt', 500)->nullable();

                // 8 Kolom Standar Audit Trail PT Mirasa Food Industry
                $table->timestamp('created_at')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);

                $table->index(['barang_jadi_id', 'transaksi_tgl'], 'idx_pkg_buffer_barang_tgl');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_packaging_buffer_log');

        if (Schema::hasTable('dat_laporan_kemas_operator') && Schema::hasColumn('dat_laporan_kemas_operator', 'tarik_pack_lalu_qty')) {
            Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
                $table->dropColumn('tarik_pack_lalu_qty');
            });
        }

        if (Schema::hasTable('dat_packaging_hdr') && Schema::hasColumn('dat_packaging_hdr', 'tarik_pack_lalu_qty')) {
            Schema::table('dat_packaging_hdr', function (Blueprint $table) {
                $table->dropColumn('tarik_pack_lalu_qty');
            });
        }
    }
};
