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
        Schema::create('mst_tarif_kemasan', function (Blueprint $table) {
            $table->id('tarif_kemasan_id');
            $table->string('kategori_bahan', 50)->comment('Kategori: KARTON, ROLL, LAKBAN, BUMBU, WIP_CURAH, LAIN');
            $table->string('kode_tarif', 50)->unique()->comment('Kode Unik: KARTON_XX, KARTON_MX, ROLL_XX, dll');
            $table->string('nama_tarif', 150)->comment('Nama Bahan / Komponen Kemasan');
            $table->string('satuan_basis', 50)->comment('Basis Satuan: PER_LEMBAR, PER_ROLL, PER_KG, PER_GRAM');
            $table->string('satuan_label', 50)->comment('Label Satuan UI: Rp / Lembar, Rp / Roll, Rp / Kg, Rp / Gram');
            $table->decimal('nilai_tarif', 16, 4)->default(0)->comment('Besaran Nilai Acuan Rupiah Utama');
            $table->decimal('nilai_tarif_sekunder', 16, 4)->nullable()->comment('Nilai Alternatif / Konversi Satuan');
            $table->string('satuan_sekunder_label', 50)->nullable()->comment('Label Satuan Alternatif, misal: Rp / Kg');
            $table->date('berlaku_sejak')->nullable()->comment('Tanggal Acuan Efektif Nota Supplier');
            $table->string('keterangan', 255)->nullable()->comment('Catatan Tambahan & Formula');

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
        });

        // Seed data awal sesuai matriks Excel Daftar Harga HPP Ping-Ping & Maksi PT Mirasa Food Industry
        $now = now();
        $initialRates = [
            [
                'kategori_bahan'         => 'KARTON',
                'kode_tarif'             => 'KARTON_XX',
                'nama_tarif'             => 'Karton Box Ping-Ping (XX)',
                'satuan_basis'           => 'PER_LEMBAR',
                'satuan_label'           => 'Rp / Lembar',
                'nilai_tarif'            => 1338.9000,
                'nilai_tarif_sekunder'   => 1341.7700,
                'satuan_sekunder_label'  => 'Rp / Lbr (Update 20/01)',
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Standar biaya karton box kemasan Ping-Ping per lembar',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'KARTON',
                'kode_tarif'             => 'KARTON_MX',
                'nama_tarif'             => 'Karton Box Maksi (MX)',
                'satuan_basis'           => 'PER_LEMBAR',
                'satuan_label'           => 'Rp / Lembar',
                'nilai_tarif'            => 1322.6000,
                'nilai_tarif_sekunder'   => 1333.9300,
                'satuan_sekunder_label'  => 'Rp / Lbr (Update 20/01)',
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Standar biaya karton box kemasan Maksi per lembar',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'BUMBU',
                'kode_tarif'             => 'BUMBU_BALADO',
                'nama_tarif'             => 'Bumbu Balado & Perasa Kemas',
                'satuan_basis'           => 'PER_KG',
                'satuan_label'           => 'Rp / Kg',
                'nilai_tarif'            => 38499.0000,
                'nilai_tarif_sekunder'   => null,
                'satuan_sekunder_label'  => null,
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Harga acuan bumbu kemasan (pemakaian bumbu setiap harinya berbeda %)',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'ROLL',
                'kode_tarif'             => 'ROLL_XX',
                'nama_tarif'             => 'Roll Plastik Kemasan Ping-Ping (XX)',
                'satuan_basis'           => 'PER_ROLL',
                'satuan_label'           => 'Rp / Roll',
                'nilai_tarif'            => 364830.0000,
                'nilai_tarif_sekunder'   => 44819.0000,
                'satuan_sekunder_label'  => 'Rp / Kg',
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Harga roll plastik cetak varian Ping-Ping XX',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'ROLL',
                'kode_tarif'             => 'ROLL_MX',
                'nama_tarif'             => 'Roll Plastik Kemasan Maksi (MX)',
                'satuan_basis'           => 'PER_ROLL',
                'satuan_label'           => 'Rp / Roll',
                'nilai_tarif'            => 364427.0000,
                'nilai_tarif_sekunder'   => 41985.0000,
                'satuan_sekunder_label'  => 'Rp / Kg',
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Harga roll plastik cetak varian Maksi MX',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'ROLL',
                'kode_tarif'             => 'ROLL_XX_MX',
                'nama_tarif'             => 'Roll Gabungan Plastik (XX + MX)',
                'satuan_basis'           => 'PER_KG',
                'satuan_label'           => 'Rp / Kg',
                'nilai_tarif'            => 43402.0000,
                'nilai_tarif_sekunder'   => 43.4000,
                'satuan_sekunder_label'  => 'Rp / Gram',
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Rata-rata tertimbang biaya plastik kemas per kilogram & per gram',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'LAKBAN',
                'kode_tarif'             => 'LAKBAN_ROLL',
                'nama_tarif'             => 'Lakban Segel Dus Pabrik',
                'satuan_basis'           => 'PER_ROLL',
                'satuan_label'           => 'Rp / Roll',
                'nilai_tarif'            => 45880.0000,
                'nilai_tarif_sekunder'   => 28688.0000,
                'satuan_sekunder_label'  => 'Rp / Roll (Opsi Reguler)',
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Harga lakban segel per roll kemasan pabrik',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'LAKBAN',
                'kode_tarif'             => 'LAKBAN_GRAM',
                'nama_tarif'             => 'Lakban Segel Timbangan (Gram)',
                'satuan_basis'           => 'PER_GRAM',
                'satuan_label'           => 'Rp / Gram',
                'nilai_tarif'            => 36.7000,
                'nilai_tarif_sekunder'   => 33.0000,
                'satuan_sekunder_label'  => 'Rp / Gram (Opsi 2)',
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Tarif pengali timbangan lakban seal atas + bawah per gram',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'LAIN',
                'kode_tarif'             => 'KERTAS_EXP',
                'nama_tarif'             => 'Kertas Expired Date & Label',
                'satuan_basis'           => 'PER_LEMBAR',
                'satuan_label'           => 'Rp / Lembar',
                'nilai_tarif'            => 250.0000,
                'nilai_tarif_sekunder'   => null,
                'satuan_sekunder_label'  => null,
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Standar biaya cetak kertas exp date per lembar',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'WIP_CURAH',
                'kode_tarif'             => 'WIP_BERKO',
                'nama_tarif'             => 'Keripik Singkong Curah (Berko)',
                'satuan_basis'           => 'PER_KG',
                'satuan_label'           => 'Rp / Kg',
                'nilai_tarif'            => 10000.0000,
                'nilai_tarif_sekunder'   => null,
                'satuan_sekunder_label'  => null,
                'berlaku_sejak'          => '2026-01-01',
                'keterangan'             => 'Acuan nilai bahan curah berko untuk repack / olah bumbu',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
            [
                'kategori_bahan'         => 'WIP_CURAH',
                'kode_tarif'             => 'WIP_CURAH',
                'nama_tarif'             => 'Bahan Baku WIP Matang Kemas',
                'satuan_basis'           => 'PER_KG',
                'satuan_label'           => 'Rp / Kg',
                'nilai_tarif'            => 25500.0000,
                'nilai_tarif_sekunder'   => null,
                'satuan_sekunder_label'  => null,
                'berlaku_sejak'          => '2026-01-20',
                'keterangan'             => 'Acuan nilai bahan baku keripik curah WIP matang',
                'created_at'             => $now,
                'created_by'             => 'SYSTEM',
                'active_st'              => true,
                'deleted_st'             => false,
                'version_no'             => 1,
            ],
        ];

        DB::table('mst_tarif_kemasan')->insert($initialRates);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_tarif_kemasan');
    }
};
