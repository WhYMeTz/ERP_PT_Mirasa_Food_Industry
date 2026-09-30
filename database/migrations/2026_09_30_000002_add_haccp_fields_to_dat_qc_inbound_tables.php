<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Standar Formulir HACCP PT Mirasa Food Industry: MFI/HACCP-04/FRM-03/048/VIII/2021
     * Checklist Standar Kebeterimaan Bahan Baku Singkong (Pengujian I & II)
     */
    public function up(): void
    {
        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            $table->string('negara_produsen', 50)->default('Indonesia')->after('supplier_id');
            $table->string('lokasi_panen', 100)->nullable()->after('negara_produsen');
            $table->decimal('umur_singkong_bln', 4, 1)->nullable()->after('lokasi_panen'); // misal 9.5 bulan
            $table->date('tgl_panen')->nullable()->after('umur_singkong_bln');
            $table->decimal('jumlah_sample_kg', 10, 2)->default(0)->after('tgl_panen');
            $table->boolean('bebas_cemaran_st')->default(true)->after('sopir_nama'); // true = Tidak ada cemaran, najis / kotoran
            $table->boolean('angkut_barang_haram_st')->default(false)->after('bebas_cemaran_st'); // false = Tidak, true = Ya
            $table->string('komentar_transportasi', 255)->nullable()->after('angkut_barang_haram_st');
            $table->string('qc_supervisor_nama', 100)->nullable()->after('petugas_qc_nama');
        });

        Schema::table('dat_qc_inbound_dtl', function (Blueprint $table) {
            // Pengujian I: Standar Diameter & Fisik
            $table->string('status_raw_material', 20)->default('OK')->after('barang_id'); // OK vs TDK_STD
            $table->decimal('diameter_kurang_4cm_persen', 5, 2)->default(0)->after('status_raw_material'); // Standar Max 5.0%
            $table->decimal('diameter_lebih_4cm_persen', 5, 2)->default(100)->after('diameter_kurang_4cm_persen'); // Standar Min 95.0%
            
            // Checkbox Kondisi Visual Singkong
            $table->boolean('kondisi_segar')->default(true)->after('diameter_lebih_4cm_persen');
            $table->boolean('kondisi_layu')->default(false)->after('kondisi_segar');
            $table->boolean('kondisi_basah')->default(false)->after('kondisi_layu');
            $table->boolean('kondisi_terkelupas')->default(false)->after('kondisi_basah');
            $table->boolean('kondisi_busuk')->default(false)->after('kondisi_terkelupas');
            $table->boolean('kondisi_berjamur')->default(false)->after('kondisi_busuk');
            $table->boolean('kondisi_lembek')->default(false)->after('kondisi_berjamur');

            // Pengujian II: Uji Goreng (Hasil Fryer Lab Lapangan QC)
            $table->string('fryer_rasa', 30)->default('TIDAK_PAHIT')->after('kondisi_lembek'); // TIDAK_PAHIT vs PAHIT
            $table->string('fryer_tekstur', 30)->default('RENYAH')->after('fryer_rasa'); // RENYAH vs ALOT
            $table->string('fryer_penampakan', 30)->default('TIDAK_OILSOAKED')->after('fryer_tekstur'); // TIDAK_OILSOAKED vs OILSOAKED

            // Defect Frying (%)
            $table->decimal('defect_breakage_persen', 5, 2)->default(0)->after('fryer_penampakan');
            $table->decimal('defect_cluster_persen', 5, 2)->default(0)->after('defect_breakage_persen');
            $table->decimal('defect_foldover_persen', 5, 2)->default(0)->after('defect_cluster_persen');
            $table->decimal('defect_oilsoaked_persen', 5, 2)->default(0)->after('defect_foldover_persen');
            $table->decimal('defect_gambos_persen', 5, 2)->default(0)->after('defect_oilsoaked_persen'); // Gambos / Kopong
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_qc_inbound_dtl', function (Blueprint $table) {
            $table->dropColumn([
                'status_raw_material',
                'diameter_kurang_4cm_persen',
                'diameter_lebih_4cm_persen',
                'kondisi_segar',
                'kondisi_layu',
                'kondisi_basah',
                'kondisi_terkelupas',
                'kondisi_busuk',
                'kondisi_berjamur',
                'kondisi_lembek',
                'fryer_rasa',
                'fryer_tekstur',
                'fryer_penampakan',
                'defect_breakage_persen',
                'defect_cluster_persen',
                'defect_foldover_persen',
                'defect_oilsoaked_persen',
                'defect_gambos_persen',
            ]);
        });

        Schema::table('dat_qc_inbound_hdr', function (Blueprint $table) {
            $table->dropColumn([
                'negara_produsen',
                'lokasi_panen',
                'umur_singkong_bln',
                'tgl_panen',
                'jumlah_sample_kg',
                'bebas_cemaran_st',
                'angkut_barang_haram_st',
                'komentar_transportasi',
                'qc_supervisor_nama',
            ]);
        });
    }
};
