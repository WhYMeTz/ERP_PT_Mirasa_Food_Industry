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
        Schema::create('dat_laporan_kemas_operator', function (Blueprint $table) {
            $table->id('laporan_id');
            $table->string('laporan_no', 100)->unique()->comment('Nomor unik laporan: LKO-YYYYMM-XXXX');
            $table->date('tgl_kemas')->comment('Tanggal pelaksanaan pengemasan di lapangan');
            $table->string('shift', 10)->default('1')->comment('Shift 1, Shift 2, Shift 3');

            // 1. Personil & Operator
            $table->foreignId('karyawan_id')->nullable()->constrained('mst_karyawan', 'karyawan_id')->onDelete('set null');
            $table->string('operator_nama', 150)->comment('Nama penanggung jawab / leader pengemasan');
            $table->integer('jumlah_personil')->default(1)->comment('Jumlah kru / operator kerja');
            $table->integer('jumlah_mesin')->default(1)->comment('Jumlah mesin kemas yang aktif beroperasi');

            // 2. Bahan WIP Curah Dapur
            $table->foreignId('produksi_id')->nullable()->constrained('dat_produksi_hdr', 'produksi_id')->onDelete('set null')->comment('Tautan lembar produksi masak curah');
            $table->decimal('wip_qty_kg', 16, 4)->default(0)->comment('Jumlah Kg keripik curah matang yang diambil untuk dikemas');

            // 3. Output Barang Jadi (FG)
            $table->foreignId('barang_jadi_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict')->comment('Produk jadi yang dikemas');
            $table->decimal('hasil_karton_qty', 16, 4)->default(0)->comment('Jumlah karton dus jadi');
            $table->decimal('hasil_pack_qty', 16, 4)->default(0)->comment('Jumlah sisa pack / eceran jika ada');
            $table->string('satuan_hasil', 30)->default('KARTON')->comment('Satuan utama: KARTON, BAL, PACK');

            // 4. Pemakaian Bumbu Tabur (Seasoning)
            $table->foreignId('bumbu_barang_id')->nullable()->constrained('mst_barang', 'barang_id')->onDelete('set null')->comment('Jenis bumbu yang ditaburkan');
            $table->decimal('bumbu_qty_kg', 16, 4)->default(0)->comment('Berat bumbu yang habis dipakai (Kg)');

            // 5. Pemakaian Plastik Utama & Kerusakan (Afkir)
            $table->foreignId('plastik_barang_id')->nullable()->constrained('mst_barang', 'barang_id')->onDelete('set null')->comment('Bahan plastik roll / pouch pembungkus');
            $table->decimal('plastik_qty', 16, 4)->default(0)->comment('Jumlah plastik yang terpakai');
            $table->decimal('plastik_rusak_pcs', 16, 4)->default(0)->comment('Jumlah plastik yang rusak / reject / afkir (pcs)');

            // 6. Pemakaian Karton Box & Kerusakan (Afkir Dus)
            $table->foreignId('karton_barang_id')->nullable()->constrained('mst_barang', 'barang_id')->onDelete('set null')->comment('Dus karton pembungkus luar');
            $table->decimal('karton_qty', 16, 4)->default(0)->comment('Jumlah lembar dus yang terpakai');
            $table->decimal('karton_rusak_lembar', 16, 4)->default(0)->comment('Jumlah dus karton yang rusak / sobek (lembar)');

            // 7. Pemakaian Lakban & Kemasan Tambahan
            $table->decimal('lakban_qty', 16, 4)->default(0)->comment('Pemakaian lakban selotip (gram atau roll)');
            $table->foreignId('kemasan_lain_barang_id')->nullable()->constrained('mst_barang', 'barang_id')->onDelete('set null')->comment('Kemasan opsional: plastik bal, kertas expired, dll');
            $table->decimal('kemasan_lain_qty', 16, 4)->default(0);

            // 8. Catatan & Status
            $table->text('catatan')->nullable()->comment('Catatan kendala lapangan dari operator');
            $table->string('status_cd', 20)->default('SUBMITTED')->comment('DRAFT, SUBMITTED (menunggu office), PROCESSED (sudah jadi HPP FG)');
            $table->foreignId('pack_id')->nullable()->constrained('dat_packaging_hdr', 'pack_id')->onDelete('set null')->comment('Tautan ke lembar HPP FG setelah ditarik admin office');

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

            // Indexes
            $table->index(['tgl_kemas', 'status_cd'], 'idx_lko_tgl_status');
            $table->index('barang_jadi_id', 'idx_lko_barang_jadi');
            $table->index('produksi_id', 'idx_lko_produksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_laporan_kemas_operator');
    }
};
