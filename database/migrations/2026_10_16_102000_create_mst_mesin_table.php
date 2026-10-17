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
        Schema::create('mst_mesin', function (Blueprint $table) {
            $table->id('mesin_id');
            $table->string('mesin_cd', 50)->unique()->comment('Kode Identifikasi Mesin (cth: FRY-IFL-01, PKG-CS-01)');
            $table->string('mesin_nm', 100)->comment('Nama Mesin Operasional Pabrik');
            $table->string('kategori_mesin', 30)->default('PRODUKSI')->comment('Kategori: PRODUKSI (Penggorengan/Pengolahan) atau PACKAGING (Pengemasan)');
            $table->string('jenis_proses', 50)->nullable()->comment('Jenis Proses: PENGGORENGAN, PEMOTONGAN, PENIRISAN, PENGEMASAN_SEALER, PENGEMASAN_OTOMATIS, PENGEMASAN_KARTON');
            $table->decimal('kapasitas_per_jam', 12, 2)->nullable()->comment('Kapasitas Output Standar Mesin');
            $table->string('satuan_kapasitas', 30)->default('KG/JAM')->comment('Satuan Kapasitas (cth: KG/JAM, KEMASAN/MENIT, KARTON/MENIT)');
            $table->string('tipe_energi', 30)->nullable()->comment('Sumber Energi: GAS_CNG, LPG, LISTRIK, SOLAR, MANUAL');
            $table->string('status_kondisi', 30)->default('SIAP')->comment('Status Kesiapan: SIAP, MAINTENANCE, RUSAK');
            $table->string('keterangan', 255)->nullable()->comment('Catatan Tambahan Spesifikasi Mesin');

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

        // Seed data awal mesin operasional pabrik PT Mirasa Food Industry
        $now = now();
        DB::table('mst_mesin')->insert([
            // ── KATEGORI: PRODUKSI (PENGGORENGAN & PENGOLAHAN BAHAN) ──
            [
                'mesin_cd'          => 'FRY-IFL-01',
                'mesin_nm'          => 'Continuous Fryer IFL 01',
                'kategori_mesin'    => 'PRODUKSI',
                'jenis_proses'      => 'PENGGORENGAN',
                'kapasitas_per_jam' => 150.00,
                'satuan_kapasitas'  => 'KG/JAM',
                'tipe_energi'       => 'GAS_CNG',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Mesin Continuous Fryer utama jalur IFL untuk IFM & Keripik Reguler.',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'FRY-MNL-01',
                'mesin_nm'          => 'Wajan Penggorengan Manual 01',
                'kategori_mesin'    => 'PRODUKSI',
                'jenis_proses'      => 'PENGGORENGAN',
                'kapasitas_per_jam' => 50.00,
                'satuan_kapasitas'  => 'KG/JAM',
                'tipe_energi'       => 'LPG',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Wajan manual penggorengan regu 1 (keripik singkong / olahan bumbu).',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'FRY-MNL-02',
                'mesin_nm'          => 'Wajan Penggorengan Manual 02',
                'kategori_mesin'    => 'PRODUKSI',
                'jenis_proses'      => 'PENGGORENGAN',
                'kapasitas_per_jam' => 50.00,
                'satuan_kapasitas'  => 'KG/JAM',
                'tipe_energi'       => 'LPG',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Wajan manual penggorengan regu 2.',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'SLC-01',
                'mesin_nm'          => 'Mesin Slicer Singkong 01',
                'kategori_mesin'    => 'PRODUKSI',
                'jenis_proses'      => 'PEMOTONGAN',
                'kapasitas_per_jam' => 300.00,
                'satuan_kapasitas'  => 'KG/JAM',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Mesin perajang singkong gelombang / rata jalur IFL.',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'SLC-02',
                'mesin_nm'          => 'Mesin Slicer Singkong 02',
                'kategori_mesin'    => 'PRODUKSI',
                'jenis_proses'      => 'PEMOTONGAN',
                'kapasitas_per_jam' => 300.00,
                'satuan_kapasitas'  => 'KG/JAM',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Mesin perajang singkong jalur manual / cadangan.',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'SPN-01',
                'mesin_nm'          => 'Mesin Spinner Peniris Minyak 01',
                'kategori_mesin'    => 'PRODUKSI',
                'jenis_proses'      => 'PENIRISAN',
                'kapasitas_per_jam' => 120.00,
                'satuan_kapasitas'  => 'KG/JAM',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Sentrifugal peniris minyak hasil gorengan manual.',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],

            // ── KATEGORI: PACKAGING (PENGEMASAN BARANG JADI M1 s/d M7) ──
            [
                'mesin_cd'          => 'PKG-M1',
                'mesin_nm'          => 'Mesin Kemas 1 (M1)',
                'kategori_mesin'    => 'PACKAGING',
                'jenis_proses'      => 'PENGEMASAN_SEALER',
                'kapasitas_per_jam' => 40.00,
                'satuan_kapasitas'  => 'KEMASAN/MENIT',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Lini pengemasan retail Mesin 1 (M1)',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'PKG-M2',
                'mesin_nm'          => 'Mesin Kemas 2 (M2)',
                'kategori_mesin'    => 'PACKAGING',
                'jenis_proses'      => 'PENGEMASAN_SEALER',
                'kapasitas_per_jam' => 40.00,
                'satuan_kapasitas'  => 'KEMASAN/MENIT',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Lini pengemasan retail Mesin 2 (M2)',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'PKG-M3',
                'mesin_nm'          => 'Mesin Kemas 3 (M3)',
                'kategori_mesin'    => 'PACKAGING',
                'jenis_proses'      => 'PENGEMASAN_SEALER',
                'kapasitas_per_jam' => 40.00,
                'satuan_kapasitas'  => 'KEMASAN/MENIT',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Lini pengemasan retail Mesin 3 (M3)',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'PKG-M4',
                'mesin_nm'          => 'Mesin Kemas 4 (M4)',
                'kategori_mesin'    => 'PACKAGING',
                'jenis_proses'      => 'PENGEMASAN_SEALER',
                'kapasitas_per_jam' => 40.00,
                'satuan_kapasitas'  => 'KEMASAN/MENIT',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Lini pengemasan retail Mesin 4 (M4)',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'PKG-M5',
                'mesin_nm'          => 'Mesin Kemas 5 (M5)',
                'kategori_mesin'    => 'PACKAGING',
                'jenis_proses'      => 'PENGEMASAN_SEALER',
                'kapasitas_per_jam' => 40.00,
                'satuan_kapasitas'  => 'KEMASAN/MENIT',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Lini pengemasan retail Mesin 5 (M5)',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'PKG-M6',
                'mesin_nm'          => 'Mesin Kemas 6 (M6)',
                'kategori_mesin'    => 'PACKAGING',
                'jenis_proses'      => 'PENGEMASAN_SEALER',
                'kapasitas_per_jam' => 40.00,
                'satuan_kapasitas'  => 'KEMASAN/MENIT',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Lini pengemasan retail Mesin 6 (M6)',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
            [
                'mesin_cd'          => 'PKG-M7',
                'mesin_nm'          => 'Mesin Kemas 7 (M7)',
                'kategori_mesin'    => 'PACKAGING',
                'jenis_proses'      => 'PENGEMASAN_SEALER',
                'kapasitas_per_jam' => 40.00,
                'satuan_kapasitas'  => 'KEMASAN/MENIT',
                'tipe_energi'       => 'LISTRIK',
                'status_kondisi'    => 'SIAP',
                'keterangan'        => 'Lini pengemasan retail Mesin 7 (M7)',
                'created_at'        => $now,
                'created_by'        => 'SYSTEM',
                'active_st'         => true,
                'deleted_st'        => false,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_mesin');
    }
};
