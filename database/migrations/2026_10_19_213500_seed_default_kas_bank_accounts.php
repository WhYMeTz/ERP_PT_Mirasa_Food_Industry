<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('mst_kas_bank')->count() === 0) {
            $now = now();
            DB::table('mst_kas_bank')->insert([
                [
                    'kode_akun'      => 'KAS-01',
                    'nama_akun'      => 'Kas Kecil Kasir Pabrik',
                    'tipe_akun'      => 'KAS',
                    'nomor_rekening' => null,
                    'atas_nama'      => 'Bendahara Pabrik',
                    'bank_nama'      => 'TUNAI',
                    'cabang'         => 'Pabrik Mirasa',
                    'saldo_awal'     => 10000000,
                    'saldo_berjalan' => 10000000,
                    'keterangan'     => 'Kas operasional tunai pabrik untuk penerimaan langsung & belanja harian',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                    'created_by'     => 'SYSTEM',
                    'updated_by'     => 'SYSTEM',
                    'active_st'      => true,
                    'deleted_st'     => false,
                    'version_no'     => 1,
                ],
                [
                    'kode_akun'      => 'BANK-BCA',
                    'nama_akun'      => 'BCA Operasional Utama',
                    'tipe_akun'      => 'BANK',
                    'nomor_rekening' => '0188992211',
                    'atas_nama'      => 'PT Mirasa Food Industry',
                    'bank_nama'      => 'BCA',
                    'cabang'         => 'KCU Malang',
                    'saldo_awal'     => 150000000,
                    'saldo_berjalan' => 150000000,
                    'keterangan'     => 'Rekening utama penerimaan transfer penjualan & pelunasan piutang customer',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                    'created_by'     => 'SYSTEM',
                    'updated_by'     => 'SYSTEM',
                    'active_st'      => true,
                    'deleted_st'     => false,
                    'version_no'     => 1,
                ],
                [
                    'kode_akun'      => 'BANK-MANDIRI',
                    'nama_akun'      => 'Mandiri Giro Bisnis',
                    'tipe_akun'      => 'BANK',
                    'nomor_rekening' => '1440019283746',
                    'atas_nama'      => 'PT Mirasa Food Industry',
                    'bank_nama'      => 'MANDIRI',
                    'cabang'         => 'KC Malang Sutoyo',
                    'saldo_awal'     => 85000000,
                    'saldo_berjalan' => 85000000,
                    'keterangan'     => 'Rekening cadangan operasional & penampungan giro pelanggan',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                    'created_by'     => 'SYSTEM',
                    'updated_by'     => 'SYSTEM',
                    'active_st'      => true,
                    'deleted_st'     => false,
                    'version_no'     => 1,
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mst_kas_bank')->whereIn('kode_akun', ['KAS-01', 'BANK-BCA', 'BANK-MANDIRI'])->delete();
    }
};
