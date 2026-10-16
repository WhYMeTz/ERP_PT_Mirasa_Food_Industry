<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        // 1. Daftarkan Peran Khusus: OPERATOR_PACKAGING
        $exists = DB::table('sys_roles')->where('role_cd', 'OPERATOR_PACKAGING')->exists();
        if (!$exists) {
            DB::table('sys_roles')->insert([
                'role_cd'    => 'OPERATOR_PACKAGING',
                'role_nm'    => 'Operator Kemas Lapangan (Mobile)',
                'desc_txt'   => 'Akses khusus mobile shopfloor untuk input formulir dan riwayat laporan pengemasan harian.',
                'is_system'  => true,
                'created_by' => 'SYSTEM_INIT',
                'created_dt' => $now,
                'updated_by' => 'SYSTEM_INIT',
                'updated_dt' => $now,
                'active_st'  => true,
                'deleted_st' => false,
                'version_no' => 1,
            ]);
        }

        // 2. Buat Akun Pengguna Contoh Khusus Operator Lapangan jika belum ada
        $userExists = DB::table('users')->where('email', 'operator.kemas@mirasa.co.id')->exists();
        if (!$userExists) {
            DB::table('users')->insert([
                'name'        => 'Siti Aminah (Operator Kemas)',
                'email'       => 'operator.kemas@mirasa.co.id',
                'password'    => Hash::make('password123'),
                'role_cd'     => 'OPERATOR_PACKAGING',
                'active_st'   => true,
                'deleted_st'  => false,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('email', 'operator.kemas@mirasa.co.id')->delete();
        DB::table('sys_roles')->where('role_cd', 'OPERATOR_PACKAGING')->delete();
    }
};
