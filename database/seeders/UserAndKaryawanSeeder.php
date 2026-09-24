<?php

namespace Database\Seeders;

use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstKaryawan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAndKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Gudang Unit Magelang tersedia
        $gudangMagelang = MstGudang::firstOrCreate(
            ['gudang_cd' => 'GDG-MGL'],
            [
                'gudang_nm'      => 'Gudang Bahan Baku Magelang',
                'tipe_gudang_cd' => 'RAW',
                'alamat_txt'     => 'Jl. Raya Magelang - Secang Km. 7, Magelang, Jawa Tengah',
            ]
        );

        $gudangRawPusat = MstGudang::where('gudang_cd', 'GDG-RAW')->first() ?? $gudangMagelang;

        // 2. Data Master Karyawan
        $karyawanData = [
            [
                'nik'           => 'KRY-0001',
                'karyawan_nm'   => 'Ahmad Kurniawan',
                'departemen_cd' => 'MANAJEMEN',
                'jabatan_nm'    => 'Direktur Operasional Pabrik',
                'telepon_no'    => '081234567801',
                'email'         => 'admin@mirasa.co.id',
                'alamat_txt'    => 'Magelang Tengah',
            ],
            [
                'nik'           => 'KRY-0002',
                'karyawan_nm'   => 'Bambang Wijaya',
                'departemen_cd' => 'PRODUKSI',
                'jabatan_nm'    => 'Supervisor Produksi Unit Magelang',
                'telepon_no'    => '081234567802',
                'email'         => 'produksi.mgl@mirasa.co.id',
                'alamat_txt'    => 'Secang, Kab. Magelang',
            ],
            [
                'nik'           => 'KRY-0003',
                'karyawan_nm'   => 'Joko Santoso',
                'departemen_cd' => 'GUDANG',
                'jabatan_nm'    => 'Kepala Gudang Bahan Baku',
                'telepon_no'    => '081234567803',
                'email'         => 'gudang.raw@mirasa.co.id',
                'alamat_txt'    => 'Muntilan, Magelang',
            ],
            [
                'nik'           => 'KRY-0004',
                'karyawan_nm'   => 'Siti Rahayu',
                'departemen_cd' => 'PURCHASING',
                'jabatan_nm'    => 'Staff Pengadaan Bahan Baku Hasil Tani',
                'telepon_no'    => '081234567804',
                'email'         => 'purchasing@mirasa.co.id',
                'alamat_txt'    => 'Wonosobo',
            ],
        ];

        $karyawanMap = [];
        foreach ($karyawanData as $k) {
            $record = MstKaryawan::firstOrCreate(
                ['nik' => $k['nik']],
                $k
            );
            $karyawanMap[$k['nik']] = $record;
        }

        // 3. Akun Pengguna Sistem (Users) dengan Hak Akses & Penugasan Gudang
        $userData = [
            [
                'name'        => 'Super Administrator',
                'email'       => 'admin@mirasa.co.id',
                'password'    => Hash::make('password123'),
                'karyawan_id' => $karyawanMap['KRY-0001']->karyawan_id,
                'role_cd'     => 'SUPERADMIN',
                'gudang_id'   => null, // Bebas akses seluruh gudang
                'active_st'   => true,
            ],
            [
                'name'        => 'Bambang (Produksi Magelang)',
                'email'       => 'produksi.mgl@mirasa.co.id',
                'password'    => Hash::make('password123'),
                'karyawan_id' => $karyawanMap['KRY-0002']->karyawan_id,
                'role_cd'     => 'STAFF_PRODUKSI',
                'gudang_id'   => $gudangMagelang->gudang_id, // Terkunci ke Gudang Magelang!
                'active_st'   => true,
            ],
            [
                'name'        => 'Joko (Gudang Bahan Baku)',
                'email'       => 'gudang.raw@mirasa.co.id',
                'password'    => Hash::make('password123'),
                'karyawan_id' => $karyawanMap['KRY-0003']->karyawan_id,
                'role_cd'     => 'ADMIN_GUDANG',
                'gudang_id'   => $gudangRawPusat->gudang_id, // Terkunci ke Gudang Bahan Baku
                'active_st'   => true,
            ],
            [
                'name'        => 'Siti (Purchasing Pusat)',
                'email'       => 'purchasing@mirasa.co.id',
                'password'    => Hash::make('password123'),
                'karyawan_id' => $karyawanMap['KRY-0004']->karyawan_id,
                'role_cd'     => 'PURCHASING',
                'gudang_id'   => null, // Bebas order ke gudang mana saja
                'active_st'   => true,
            ],
        ];

        foreach ($userData as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }
    }
}
