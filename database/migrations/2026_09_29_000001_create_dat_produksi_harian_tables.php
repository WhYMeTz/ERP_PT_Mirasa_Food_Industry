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
        // 1. Tabel Utama Kalkulasi Biaya Produksi & HPP Harian (Sesuai Excel Asli PT Mirasa)
        if (!Schema::hasTable('dat_produksi_harian')) {
            Schema::create('dat_produksi_harian', function (Blueprint $table) {
                $table->id('produksi_id');
                $table->string('produksi_no', 100)->unique();
                $table->date('produksi_tgl');
                $table->string('hari_nm', 30)->nullable();
                $table->foreignId('pakai_id')->nullable()->constrained('dat_pakai_hdr', 'pakai_id')->onDelete('set null');
                $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
                $table->string('lini_produksi', 100)->default('PRODUKSI IFM');
                $table->string('status_cd', 30)->default('DRAFT'); // DRAFT, POSTED, CANCELLED

                // --- A. BIAYA BAHAN BAKU & KEMASAN (DIRECT MATERIALS) ---
                $table->decimal('singkong_qty', 16, 4)->default(0)->comment('Kuantitas Singkong Mentah (Kg)');
                $table->decimal('singkong_nilai', 16, 2)->default(0)->comment('Nilai Rupiah Singkong');
                
                $table->decimal('minyak_sawit_qty', 16, 4)->default(0)->comment('Kuantitas Minyak Sawit (Kg)');
                $table->decimal('minyak_kelapa_qty', 16, 4)->default(0)->comment('Kuantitas Minyak Kelapa (Kg)');
                $table->decimal('minyak_nilai', 16, 2)->default(0)->comment('Nilai Rupiah Minyak Goreng');
                $table->decimal('minyak_rasio_persen', 8, 2)->default(0)->comment('Rasio Pemakaian Minyak vs Singkong (%)');

                $table->decimal('bumbu_nilai', 16, 2)->default(0)->comment('Nilai Rupiah Bumbu Perenyah');
                $table->decimal('karton_baru_nilai', 16, 2)->default(0)->comment('Nilai Karton IFL Baru');
                $table->decimal('karton_bekas_nilai', 16, 2)->default(0)->comment('Nilai Karton IFL Bekas');
                $table->decimal('plastik_hd_nilai', 16, 2)->default(0)->comment('Nilai Plastik HD 90x100');
                $table->decimal('lakban_besar_nilai', 16, 2)->default(0)->comment('Nilai Lakban Besar');
                $table->decimal('lakban_kecil_nilai', 16, 2)->default(0)->comment('Nilai Lakban Kecil');
                $table->decimal('tali_rafia_nilai', 16, 2)->default(0)->comment('Nilai Tali Rafia');
                $table->decimal('total_bahan_nilai', 16, 2)->default(0)->comment('Total Seluruh Biaya Bahan');

                // --- B. ENERGI & GAS CNG (UTILITIES) ---
                $table->decimal('cng_mmbtu', 16, 4)->default(0)->comment('Konsumsi Gas CNG (MMBTU)');
                $table->decimal('cng_tarif', 16, 2)->default(0)->comment('Tarif Gas per MMBTU');
                $table->decimal('cng_nilai', 16, 2)->default(0)->comment('Total Nilai Rupiah CNG');

                // --- C. TENAGA KERJA (DIRECT & INDIRECT LABOR) ---
                $table->integer('tk_langsung_org')->default(0)->comment('Jumlah Tenaga Kerja Langsung (Orang)');
                $table->integer('tk_tidak_langsung_org')->default(0)->comment('Jumlah Tenaga Kerja Tidak Langsung (Orang)');
                $table->integer('tk_training_org')->default(0)->comment('Jumlah Tenaga Kerja Training (Orang)');
                $table->decimal('tk_tarif_per_org', 16, 2)->default(91300.00)->comment('Tarif Upah Standar per Orang');
                $table->decimal('tk_total_nilai', 16, 2)->default(0)->comment('Total Upah Tenaga Kerja Harian');

                // --- D. BIAYA OVERHEAD PABRIK (FACTORY OVERHEAD / FOH) ---
                $table->decimal('fotocopy_nilai', 16, 2)->default(0)->comment('Biaya Fotocopy & ATK Produksi');
                $table->decimal('sarung_tangan_plastik_nilai', 16, 2)->default(0)->comment('Biaya Sarung Tangan Plastik');
                $table->decimal('sarung_tangan_kain_nilai', 16, 2)->default(0)->comment('Biaya Sarung Tangan Kain');
                $table->decimal('qc_pengawasan_nilai', 16, 2)->default(0)->comment('Biaya Pengawasan Mutu (QC)');
                $table->decimal('listrik_air_telp_nilai', 16, 2)->default(0)->comment('Biaya Listrik, Air & Telepon');
                $table->decimal('pemeliharaan_mesin_nilai', 16, 2)->default(0)->comment('Biaya Pemeliharaan Mesin');
                $table->decimal('penyusutan_mesin_nilai', 16, 2)->default(0)->comment('Biaya Penyusutan Mesin');
                $table->decimal('limbah_padat_nilai', 16, 2)->default(0)->comment('Biaya Pengolahan Limbah Padat');
                $table->decimal('limbah_kimia_nilai', 16, 2)->default(0)->comment('Biaya Pengolahan Limbah Bahan Kimia');
                $table->decimal('total_overhead_nilai', 16, 2)->default(0)->comment('Total Biaya Overhead Pabrik');

                // --- TOTAL BIAYA PRODUKSI HARIAN (KOLOM KUNING EMAS EXCEL) ---
                $table->decimal('total_biaya_produksi', 16, 2)->default(0)->comment('Total Biaya Produksi Harian (Bahan + CNG + TK + FOH)');

                // --- E. HASIL TIMBANGAN OUTPUT WIP (HASIL JADI) ---
                $table->decimal('asin_barco_qty', 16, 4)->default(0)->comment('WIP Asin Barco (Kg)');
                $table->decimal('asin_sawit_qty', 16, 4)->default(0)->comment('WIP Asin Sawit (Kg)');
                $table->decimal('no_salt_qty', 16, 4)->default(0)->comment('WIP No Salt (Kg)');
                $table->decimal('balo_gelombang_qty', 16, 4)->default(0)->comment('WIP Balo Gelombang (Kg)');
                $table->decimal('berko_qty', 16, 4)->default(0)->comment('WIP Berko (Kg)');
                $table->decimal('berko_me_qty', 16, 4)->default(0)->comment('WIP Berko ME (Kg)');
                $table->decimal('total_berko_qty', 16, 4)->default(0)->comment('Total Berko (Kg)');
                $table->decimal('berko_persen', 8, 2)->default(0)->comment('Rasio Berko (%)');
                $table->decimal('total_wip_qty', 16, 4)->default(0)->comment('Total Seluruh Hasil WIP (Kg)');

                // --- F. METRIK KUNCI MANUFAKTUR ---
                $table->decimal('rendemen_persen', 8, 2)->default(0)->comment('Rendemen = (Total WIP / Singkong) * 100%');
                $table->decimal('hpp_per_kg', 16, 2)->default(0)->comment('HPP per Kg = Total Biaya / Total WIP');
                $table->string('batch_wip_no', 100)->nullable()->comment('Nomor Batch Fisik WIP Masuk Gudang');
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

        // 2. Tabel Detail Output WIP per Barang (Untuk integrasi ke DatStokBatch & StokService)
        if (!Schema::hasTable('dat_produksi_output')) {
            Schema::create('dat_produksi_output', function (Blueprint $table) {
                $table->id('output_id');
                $table->foreignId('produksi_id')->constrained('dat_produksi_harian', 'produksi_id')->onDelete('cascade');
                $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
                $table->string('kategori_output', 50)->comment('ASIN_BARCO, ASIN_SAWIT, NO_SALT, BALO_GELOMBANG, BERKO, BERKO_ME');
                $table->decimal('qty_kg', 16, 4)->default(0);
                $table->string('batch_no', 100);
                $table->decimal('hpp_satuan', 16, 2)->default(0);
                $table->decimal('total_nilai', 16, 2)->default(0);
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
        Schema::dropIfExists('dat_produksi_output');
        Schema::dropIfExists('dat_produksi_harian');
    }
};
