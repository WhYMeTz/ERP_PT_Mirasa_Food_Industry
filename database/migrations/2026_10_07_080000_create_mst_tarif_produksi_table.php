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
        Schema::create('mst_tarif_produksi', function (Blueprint $table) {
            $table->id('tarif_id');
            $table->string('kategori', 50)->comment('Kategori: FOH, TENAGA_KERJA, ENERGI');
            $table->string('kode_tarif', 50)->unique()->comment('Kode Unik Tarif: FOH_QC, TARIF_CNG, dll');
            $table->string('nama_tarif', 150)->comment('Nama Tarif / Komponen Biaya');
            $table->string('satuan_basis', 50)->comment('Basis Perhitungan: PER_KG_WIP, PER_SHIFT, PER_MMBTU, PER_ORANG');
            $table->string('satuan_label', 50)->comment('Label Satuan untuk Tampilan UI, misal: Rp / Kg WIP');
            $table->decimal('nilai_tarif', 16, 4)->default(0)->comment('Besaran Nilai Rupiah / Pengali Standar');
            $table->string('keterangan', 255)->nullable()->comment('Penjelasan Komponen Biaya');

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

        // Seed data awal sesuai acuan Excel pabrik PT Mirasa Food Industry
        $now = now();
        $initialRates = [
            // Kategori: FOH (Factory Overhead)
            [
                'kategori'     => 'FOH',
                'kode_tarif'   => 'FOH_QC',
                'nama_tarif'   => 'Pengawasan Mutu (QC)',
                'satuan_basis' => 'PER_KG_WIP',
                'satuan_label' => 'Rp / Kg WIP',
                'nilai_tarif'  => 49.9700,
                'keterangan'   => 'Standar biaya quality control dikalikan total KG WIP',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],
            [
                'kategori'     => 'FOH',
                'kode_tarif'   => 'FOH_LISTRIK',
                'nama_tarif'   => 'Listrik, Air & Telepon',
                'satuan_basis' => 'PER_KG_WIP',
                'satuan_label' => 'Rp / Kg WIP',
                'nilai_tarif'  => 223.8000,
                'keterangan'   => 'Standar utilitas listrik & air pabrik dikalikan total KG WIP',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],
            [
                'kategori'     => 'FOH',
                'kode_tarif'   => 'FOH_PEMELIHARAAN',
                'nama_tarif'   => 'Pemeliharaan Mesin',
                'satuan_basis' => 'PER_KG_WIP',
                'satuan_label' => 'Rp / Kg WIP',
                'nilai_tarif'  => 23.3400,
                'keterangan'   => 'Biaya perawatan & suku cadang mesin fryer/cutter dikalikan total KG WIP',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],
            [
                'kategori'     => 'FOH',
                'kode_tarif'   => 'FOH_PENYUSUTAN',
                'nama_tarif'   => 'Penyusutan Mesin & Gedung',
                'satuan_basis' => 'PER_KG_WIP',
                'satuan_label' => 'Rp / Kg WIP',
                'nilai_tarif'  => 66.4400,
                'keterangan'   => 'Biaya depresiasi aktiva pabrik dikalikan total KG WIP',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],
            [
                'kategori'     => 'FOH',
                'kode_tarif'   => 'FOH_KIMIA_IPAL',
                'nama_tarif'   => 'Bahan Kimia IPAL',
                'satuan_basis' => 'PER_KG_WIP',
                'satuan_label' => 'Rp / Kg WIP',
                'nilai_tarif'  => 45.0900,
                'keterangan'   => 'Pengolahan limbah cair IPAL dikalikan total KG WIP',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],
            [
                'kategori'     => 'FOH',
                'kode_tarif'   => 'FOH_FOTOCOPY',
                'nama_tarif'   => 'Fotocopy & ATK Pabrik',
                'satuan_basis' => 'PER_KG_WIP',
                'satuan_label' => 'Rp / Kg WIP',
                'nilai_tarif'  => 28.0000,
                'keterangan'   => 'Biaya administrasi produksi & form dikalikan total KG WIP',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],
            [
                'kategori'     => 'FOH',
                'kode_tarif'   => 'FOH_LIMBAH_PADAT',
                'nama_tarif'   => 'Pengolahan Limbah Padat',
                'satuan_basis' => 'PER_SHIFT',
                'satuan_label' => 'Rp / Shift (Flat)',
                'nilai_tarif'  => 180000.0000,
                'keterangan'   => 'Biaya pembuangan kulit & ampas singkong per shift kerja',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],

            // Kategori: ENERGI
            [
                'kategori'     => 'ENERGI',
                'kode_tarif'   => 'TARIF_CNG',
                'nama_tarif'   => 'Gas Alam (CNG)',
                'satuan_basis' => 'PER_MMBTU',
                'satuan_label' => 'Rp / MMBTU',
                'nilai_tarif'  => 226800.0000,
                'keterangan'   => 'Harga pembelian gas alam per unit MMBTU flow meter',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],

            // Kategori: TENAGA KERJA
            [
                'kategori'     => 'TENAGA_KERJA',
                'kode_tarif'   => 'TARIF_TK_HARIAN',
                'nama_tarif'   => 'Upah Tenaga Kerja Harian',
                'satuan_basis' => 'PER_ORANG',
                'satuan_label' => 'Rp / Orang / Hari',
                'nilai_tarif'  => 91300.0000,
                'keterangan'   => 'Standar tarif upah harian karyawan produksi langsung/training',
                'created_at'   => $now,
                'created_by'   => 'SYSTEM',
                'active_st'    => true,
                'deleted_st'   => false,
                'version_no'   => 1,
            ],
        ];

        DB::table('mst_tarif_produksi')->insert($initialRates);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_tarif_produksi');
    }
};
