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
        // 1. Tambah kolom telepon / kontak pada mst_gudang jika belum ada
        if (!Schema::hasColumn('mst_gudang', 'telepon')) {
            Schema::table('mst_gudang', function (Blueprint $table) {
                $table->string('telepon', 50)->nullable()->after('alamat_txt');
            });
        }

        // 2. Selaraskan data mst_gudang dengan 3 entitas resmi operasional Mirasa
        // Gudang 1: PT Mirasa Food Industry (Pusat)
        DB::table('mst_gudang')->where('gudang_id', 1)->update([
            'gudang_cd'      => 'MFI-PST',
            'gudang_nm'      => 'PT Mirasa Food Industry',
            'tipe_gudang_cd' => 'Pusat',
            'alamat_txt'     => 'Jalan Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah',
            'telepon'        => '6287880809279',
            'active_st'      => true,
            'deleted_st'     => false,
            'updated_at'     => now(),
            'updated_by'     => 'MIGRATION',
        ]);

        // Gudang 3: PT Mirasa Food Industry (Cabang)
        $gudang3Exists = DB::table('mst_gudang')->where('gudang_id', 3)->exists();
        if ($gudang3Exists) {
            DB::table('mst_gudang')->where('gudang_id', 3)->update([
                'gudang_cd'      => 'MFI-CBG',
                'gudang_nm'      => 'PT Mirasa Food Industry',
                'tipe_gudang_cd' => 'Cabang',
                'alamat_txt'     => 'Jl. Kosambi Baru No.35, RT.5/RW.1, Duri Kosambi, Kec. Cengkareng, Kota Jakarta Barat',
                'telepon'        => '6287880809279',
                'active_st'      => true,
                'deleted_st'     => false,
                'updated_at'     => now(),
                'updated_by'     => 'MIGRATION',
            ]);
        } else {
            DB::table('mst_gudang')->insert([
                'gudang_id'      => 3,
                'gudang_cd'      => 'MFI-CBG',
                'gudang_nm'      => 'PT Mirasa Food Industry',
                'tipe_gudang_cd' => 'Cabang',
                'alamat_txt'     => 'Jl. Kosambi Baru No.35, RT.5/RW.1, Duri Kosambi, Kec. Cengkareng, Kota Jakarta Barat',
                'telepon'        => '6287880809279',
                'active_st'      => true,
                'deleted_st'     => false,
                'created_at'     => now(),
                'created_by'     => 'MIGRATION',
            ]);
        }

        // Gudang 5: CV Bahtera Mandiri Bersama (Anak Perusahaan)
        $gudang5Exists = DB::table('mst_gudang')->where('gudang_id', 5)->exists();
        if ($gudang5Exists) {
            DB::table('mst_gudang')->where('gudang_id', 5)->update([
                'gudang_cd'      => 'CV-BMB',
                'gudang_nm'      => 'CV Bahtera Mandiri Bersama',
                'tipe_gudang_cd' => 'Anak Perusahaan',
                'alamat_txt'     => 'Jl. Munggur No.1, RT.01/RW.05, Karanganyar, Kec. Mungkid, Kab. Magelang, Jawa Tengah',
                'telepon'        => '6285124666420',
                'active_st'      => true,
                'deleted_st'     => false,
                'updated_at'     => now(),
                'updated_by'     => 'MIGRATION',
            ]);
        } else {
            DB::table('mst_gudang')->insert([
                'gudang_id'      => 5,
                'gudang_cd'      => 'CV-BMB',
                'gudang_nm'      => 'CV Bahtera Mandiri Bersama',
                'tipe_gudang_cd' => 'Anak Perusahaan',
                'alamat_txt'     => 'Jl. Munggur No.1, RT.01/RW.05, Karanganyar, Kec. Mungkid, Kab. Magelang, Jawa Tengah',
                'telepon'        => '6285124666420',
                'active_st'      => true,
                'deleted_st'     => false,
                'created_at'     => now(),
                'created_by'     => 'MIGRATION',
            ]);
        }

        // Nonaktifkan entitas dummy yang tidak digunakan lagi (ID 2 dan 4)
        DB::table('mst_gudang')->whereIn('gudang_id', [2, 4])->update([
            'active_st'  => false,
            'deleted_st' => true,
            'deleted_at' => now(),
            'deleted_by' => 'MIGRATION',
        ]);

        // Bersihkan sys_user_gudang dari ID yang nonaktif
        DB::table('sys_user_gudang')->whereIn('gudang_id', [2, 4])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mst_gudang', 'telepon')) {
            Schema::table('mst_gudang', function (Blueprint $table) {
                $table->dropColumn('telepon');
            });
        }
    }
};
