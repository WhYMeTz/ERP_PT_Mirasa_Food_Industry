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
        Schema::create('sys_roles', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_cd', 50)->unique()->comment('Kode peran huruf kapital (e.g. ADMIN_GUDANG, SALES)');
            $table->string('role_nm', 100)->comment('Nama deskriptif peran');
            $table->text('desc_txt')->nullable()->comment('Deskripsi tugas dan wewenang peran');
            $table->boolean('is_system')->default(false)->comment('Apakah peran bawaan sistem yang tidak boleh dihapus');
            
            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_dt')->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_dt')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('active_st')->default(true);
            $table->boolean('deleted_st')->default(false);
            $table->integer('version_no')->default(1);
        });

        // Seed data peran bawaan awal
        $now = now();
        $initialRoles = [
            [
                'role_cd'    => 'SUPERADMIN',
                'role_nm'    => 'Super Administrator (Akses Penuh)',
                'desc_txt'   => 'Otoritas tertinggi, akses mutlak ke semua modul konfigurasi sistem dan database.',
                'is_system'  => true,
                'created_by' => 'SYSTEM_INIT',
                'created_dt' => $now,
                'updated_by' => 'SYSTEM_INIT',
                'updated_dt' => $now,
                'active_st'  => true,
                'deleted_st' => false,
                'version_no' => 1,
            ],
            [
                'role_cd'    => 'ADMIN_GUDANG',
                'role_nm'    => 'Admin / Petugas Gudang',
                'desc_txt'   => 'Pengelolaan stok fisik, penerimaan bahan baku, mutasi antar gudang, dan barang keluar.',
                'is_system'  => true,
                'created_by' => 'SYSTEM_INIT',
                'created_dt' => $now,
                'updated_by' => 'SYSTEM_INIT',
                'updated_dt' => $now,
                'active_st'  => true,
                'deleted_st' => false,
                'version_no' => 1,
            ],
            [
                'role_cd'    => 'STAFF_PRODUKSI',
                'role_nm'    => 'Staff / Operator Produksi',
                'desc_txt'   => 'Input pemakaian bahan harian, hasil produksi finished goods, dan efisiensi rendemen.',
                'is_system'  => true,
                'created_by' => 'SYSTEM_INIT',
                'created_dt' => $now,
                'updated_by' => 'SYSTEM_INIT',
                'updated_dt' => $now,
                'active_st'  => true,
                'deleted_st' => false,
                'version_no' => 1,
            ],
            [
                'role_cd'    => 'PURCHASING',
                'role_nm'    => 'Purchasing / Pengadaan Bahan',
                'desc_txt'   => 'Pembuatan Purchase Order (PO), pemantauan jadwal kedatangan, dan evaluasi pemasok.',
                'is_system'  => true,
                'created_by' => 'SYSTEM_INIT',
                'created_dt' => $now,
                'updated_by' => 'SYSTEM_INIT',
                'updated_dt' => $now,
                'active_st'  => true,
                'deleted_st' => false,
                'version_no' => 1,
            ],
            [
                'role_cd'    => 'FINANCE',
                'role_nm'    => 'Finance & Akuntansi',
                'desc_txt'   => 'Pemeriksaan tagihan faktur, hutang pembelian bahan, dan pembayaran operasional.',
                'is_system'  => true,
                'created_by' => 'SYSTEM_INIT',
                'created_dt' => $now,
                'updated_by' => 'SYSTEM_INIT',
                'updated_dt' => $now,
                'active_st'  => true,
                'deleted_st' => false,
                'version_no' => 1,
            ],
            [
                'role_cd'    => 'QC',
                'role_nm'    => 'Quality Control (QC)',
                'desc_txt'   => 'Pemeriksaan mutu bahan baku datang, sampling laboratorium, dan persetujuan grading.',
                'is_system'  => true,
                'created_by' => 'SYSTEM_INIT',
                'created_dt' => $now,
                'updated_by' => 'SYSTEM_INIT',
                'updated_dt' => $now,
                'active_st'  => true,
                'deleted_st' => false,
                'version_no' => 1,
            ],
        ];

        DB::table('sys_roles')->insert($initialRoles);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_roles');
    }
};
