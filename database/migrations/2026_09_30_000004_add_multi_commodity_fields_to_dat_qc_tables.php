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
        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            $table->string('kategori_barang', 50)->default('SINGKONG')->after('gudang_id');
            $table->string('nomor_do', 100)->nullable()->after('surat_jalan_supplier');
            $table->string('nama_jenis', 150)->nullable()->after('kategori_barang');
            $table->string('nama_produsen', 150)->nullable()->after('negara_produsen');
            $table->decimal('jumlah_surat_jalan', 12, 2)->nullable()->after('jumlah_sample_kg');
            $table->decimal('jumlah_di_pabrik', 12, 2)->nullable()->after('jumlah_surat_jalan');
            $table->integer('jumlah_sample_pcs')->nullable()->after('jumlah_sample_kg');
            $table->decimal('jumlah_sample_gr', 10, 2)->nullable()->after('jumlah_sample_kg');

            // Pertanyaan Standar Audit Halal & Transportasi (Sesuai Form Cheklist HACCP MFI)
            $table->boolean('terdaftar_lppom_st')->default(false)->after('angkut_barang_haram_st');
            $table->string('komentar_lppom', 255)->nullable()->after('terdaftar_lppom_st');
            $table->boolean('ada_sertifikat_halal_st')->default(false)->after('komentar_lppom');
            $table->string('komentar_sertifikat', 255)->nullable()->after('ada_sertifikat_halal_st');
            $table->boolean('sertifikat_halal_berlaku_st')->default(false)->after('komentar_sertifikat');
            $table->string('komentar_berlaku', 255)->nullable()->after('sertifikat_halal_berlaku_st');
        });

        Schema::table('dat_qc_inbound_dtl', function (Blueprint $table) {
            // Evaluasi Wadah / Kemasan Umum (Plastik & Karton)
            $table->string('kemasan_kondisi', 20)->nullable()->default('OK')->after('status_raw_material');
            $table->boolean('kemasan_kotor')->default(false)->after('kemasan_kondisi');
            $table->boolean('kemasan_apek')->default(false)->after('kemasan_kotor');
            $table->boolean('kemasan_basah')->default(false)->after('kemasan_apek');
            $table->boolean('kemasan_sobek')->default(false)->after('kemasan_basah');
            $table->boolean('kemasan_jamur')->default(false)->after('kemasan_sobek');
            $table->boolean('kemasan_berminyak')->default(false)->after('kemasan_jamur');
            $table->boolean('kemasan_berdebu')->default(false)->after('kemasan_berminyak');

            // Komoditas Minyak Goreng (Form MFI/HACCP-04/FRM-03/029/VIII/2021)
            $table->string('tipe_wadah_minyak', 20)->nullable()->default('TANGKI')->after('kemasan_berdebu');
            $table->string('kondisi_tangki_jerigen', 20)->nullable()->default('OK')->after('tipe_wadah_minyak');
            $table->decimal('ffa_coa', 6, 3)->nullable()->after('kondisi_tangki_jerigen');
            $table->decimal('ffa_qc', 6, 3)->nullable()->after('ffa_coa');
            $table->boolean('minyak_jernih_st')->default(true)->after('ffa_qc');
            $table->boolean('tangki_bersih_st')->default(true)->after('minyak_jernih_st');

            // Komoditas Plastik (Form MFI/HACCP-04/FRM-03/030/VIII/2021)
            $table->string('ketebalan_analisa', 50)->nullable()->after('tangki_bersih_st');
            $table->string('ketebalan_standar', 50)->nullable()->after('ketebalan_analisa');
            $table->string('keutuhan_analisa', 50)->nullable()->default('Tidak Sobek')->after('ketebalan_standar');
            $table->string('keutuhan_standar', 50)->nullable()->default('Tidak Sobek')->after('keutuhan_analisa');

            // Komoditas Karton (Form MFI/HACCP-04/FRM-03/031/VIII/2021)
            $table->string('dimensi_panjang_analisa', 50)->nullable()->after('keutuhan_standar');
            $table->string('dimensi_panjang_standar', 50)->nullable()->after('dimensi_panjang_analisa');
            $table->string('dimensi_lebar_analisa', 50)->nullable()->after('dimensi_panjang_standar');
            $table->string('dimensi_lebar_standar', 50)->nullable()->after('dimensi_lebar_analisa');
            $table->string('dimensi_tinggi_analisa', 50)->nullable()->after('dimensi_lebar_standar');
            $table->string('dimensi_tinggi_standar', 50)->nullable()->after('dimensi_tinggi_analisa');
            $table->string('spesifikasi_analisa', 100)->nullable()->after('dimensi_tinggi_standar');
            $table->string('spesifikasi_standar', 100)->nullable()->after('spesifikasi_analisa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_qc_inbound_dtl', function (Blueprint $table) {
            $table->dropColumn([
                'kemasan_kondisi',
                'kemasan_kotor',
                'kemasan_apek',
                'kemasan_basah',
                'kemasan_sobek',
                'kemasan_jamur',
                'kemasan_berminyak',
                'kemasan_berdebu',
                'tipe_wadah_minyak',
                'kondisi_tangki_jerigen',
                'ffa_coa',
                'ffa_qc',
                'minyak_jernih_st',
                'tangki_bersih_st',
                'ketebalan_analisa',
                'ketebalan_standar',
                'keutuhan_analisa',
                'keutuhan_standar',
                'dimensi_panjang_analisa',
                'dimensi_panjang_standar',
                'dimensi_lebar_analisa',
                'dimensi_lebar_standar',
                'dimensi_tinggi_analisa',
                'dimensi_tinggi_standar',
                'spesifikasi_analisa',
                'spesifikasi_standar',
            ]);
        });

        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            $table->dropColumn([
                'kategori_barang',
                'nomor_do',
                'nama_jenis',
                'nama_produsen',
                'jumlah_surat_jalan',
                'jumlah_di_pabrik',
                'jumlah_sample_pcs',
                'jumlah_sample_gr',
                'terdaftar_lppom_st',
                'komentar_lppom',
                'ada_sertifikat_halal_st',
                'komentar_sertifikat',
                'sertifikat_halal_berlaku_st',
                'komentar_berlaku',
            ]);
        });
    }
};
