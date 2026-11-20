<?php

namespace Database\Seeders;

use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstPengaturanJurnal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateCoaBaruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $newAccounts = [
            // AKTIVA - KAS & BANK (1100 - 1112)
            ['kode_akun' => '1100', 'nama_akun' => 'Kas Besar', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1101', 'nama_akun' => 'Bank BCA', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1102', 'nama_akun' => 'Kas BCA USD', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1103', 'nama_akun' => 'Kas Penjualan A', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1104', 'nama_akun' => 'Kas Penjualan B', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1105', 'nama_akun' => 'Bank BCA (Cadangan)', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1106', 'nama_akun' => 'Kas Operasional', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1107', 'nama_akun' => 'Kas Ditangan', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1108', 'nama_akun' => 'Kas Pembelian', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1109', 'nama_akun' => 'Kas Ditangan Jkt', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1110', 'nama_akun' => 'Kas Penjualan JKT A', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1111', 'nama_akun' => 'Kas Operasional JKT B', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],
            ['kode_akun' => '1112', 'nama_akun' => 'Kas Penjualan JKT B', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'KAS & BANK', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => true],

            // AKTIVA - PIUTANG & UANG MUKA (1120 - 1122)
            ['kode_akun' => '1120', 'nama_akun' => 'Piutang Dagang', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'PIUTANG', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1121', 'nama_akun' => 'Piutang Lainnya', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'PIUTANG', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1122', 'nama_akun' => 'Uang Muka Pembelian', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'UANG MUKA', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],

            // AKTIVA - PAJAK & BIAYA DIBAYAR DIMUKA (1130 - 1132)
            ['kode_akun' => '1130', 'nama_akun' => 'PPN Masukan', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'PAJAK DIBAYAR DIMUKA', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1131', 'nama_akun' => 'PPH 25 dibayar dimuka', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'PAJAK DIBAYAR DIMUKA', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1132', 'nama_akun' => 'Asuransi dibayar dimuka', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'BIAYA DIBAYAR DIMUKA', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],

            // AKTIVA - PERSEDIAAN (1150 - 1153)
            ['kode_akun' => '1150', 'nama_akun' => 'Persediaan Bahan Baku', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'PERSEDIAAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1151', 'nama_akun' => 'Persediaan WIP', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'PERSEDIAAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1152', 'nama_akun' => 'Persediaan FG', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'PERSEDIAAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1153', 'nama_akun' => 'Persediaan Bahan Penolong & Kemasan', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'PERSEDIAAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],

            // AKTIVA TETAP (1200 - 1204)
            ['kode_akun' => '1200', 'nama_akun' => 'Tanah', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKTIVA TETAP', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1201', 'nama_akun' => 'Bangunan', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKTIVA TETAP', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1202', 'nama_akun' => 'Bangunan dalam Proses', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKTIVA TETAP', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1203', 'nama_akun' => 'Mesin', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKTIVA TETAP', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '1204', 'nama_akun' => 'Kendaraan', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKTIVA TETAP', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],

            // AKUMULASI PENYUSUTAN (1211 - 1215)
            ['kode_akun' => '1211', 'nama_akun' => 'Akumulasi penyusutan bangunan', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKUMULASI PENYUSUTAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '1212', 'nama_akun' => 'Akumulasi penyusutan bangunan dalam proses', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKUMULASI PENYUSUTAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '1213', 'nama_akun' => 'Akumulasi penyusutan mesin', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKUMULASI PENYUSUTAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '1214', 'nama_akun' => 'Akumulasi penyusutan kendaraan', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKUMULASI PENYUSUTAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '1215', 'nama_akun' => 'Akumulasi penyusutan inventaris kantor', 'kategori_akun' => 'AKTIVA', 'sub_kategori' => 'AKUMULASI PENYUSUTAN', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],

            // PASIVA - HUTANG JANGKA PENDEK (2100 - 2201)
            ['kode_akun' => '2100', 'nama_akun' => 'Hutang Usaha', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '2101', 'nama_akun' => 'Hutang lainnya', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '2102', 'nama_akun' => 'Hutang Gaji', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '2103', 'nama_akun' => 'Hutang PPN', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '2104', 'nama_akun' => 'Hutang PPH 21', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '2105', 'nama_akun' => 'Hutang pajak', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '2106', 'nama_akun' => 'Uang Muka Penjualan', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '2107', 'nama_akun' => 'Hutang PPH 22', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '2201', 'nama_akun' => 'PPN Keluaran', 'kategori_akun' => 'PASIVA', 'sub_kategori' => 'HUTANG LANCAR', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],

            // MODAL (3100 - 3201)
            ['kode_akun' => '3100', 'nama_akun' => 'Modal', 'kategori_akun' => 'MODAL', 'sub_kategori' => 'MODAL', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '3110', 'nama_akun' => 'Prive', 'kategori_akun' => 'MODAL', 'sub_kategori' => 'MODAL', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '3200', 'nama_akun' => 'Laba Ditahan', 'kategori_akun' => 'MODAL', 'sub_kategori' => 'MODAL', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '3201', 'nama_akun' => 'Laba Periode berjalan', 'kategori_akun' => 'MODAL', 'sub_kategori' => 'MODAL', 'posisi_keuangan' => 'NERACA', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],

            // PENDAPATAN (4100 - 4101)
            ['kode_akun' => '4100', 'nama_akun' => 'Pendapatan Usaha', 'kategori_akun' => 'PENDAPATAN', 'sub_kategori' => 'PENDAPATAN UTAMA', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
            ['kode_akun' => '4101', 'nama_akun' => 'Retur Penjualan', 'kategori_akun' => 'PENDAPATAN', 'sub_kategori' => 'PENDAPATAN UTAMA', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],

            // HPP / BIAYA PRODUKSI (5000 - 5205)
            ['kode_akun' => '5000', 'nama_akun' => 'HPP', 'kategori_akun' => 'HPP', 'sub_kategori' => 'HARGA POKOK PENJUALAN', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5100', 'nama_akun' => 'Pemakaian bahan baku', 'kategori_akun' => 'HPP', 'sub_kategori' => 'BIAYA PRODUKSI', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5101', 'nama_akun' => 'Pemakaian bahan penolong', 'kategori_akun' => 'HPP', 'sub_kategori' => 'BIAYA PRODUKSI', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5102', 'nama_akun' => 'Pemakaian CNG', 'kategori_akun' => 'HPP', 'sub_kategori' => 'BIAYA PRODUKSI', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5103', 'nama_akun' => 'Beban gaji langsung', 'kategori_akun' => 'HPP', 'sub_kategori' => 'BIAYA PRODUKSI', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5104', 'nama_akun' => 'Beban listrik', 'kategori_akun' => 'HPP', 'sub_kategori' => 'BIAYA PRODUKSI', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5105', 'nama_akun' => 'Beban air', 'kategori_akun' => 'HPP', 'sub_kategori' => 'BIAYA PRODUKSI', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5201', 'nama_akun' => 'Beban penyusutan bangunan', 'kategori_akun' => 'HPP', 'sub_kategori' => 'PENYUSUTAN PABRIK', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5202', 'nama_akun' => 'Beban penyusutan bangunan dalam proses', 'kategori_akun' => 'HPP', 'sub_kategori' => 'PENYUSUTAN PABRIK', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5203', 'nama_akun' => 'Beban penyusutan mesin', 'kategori_akun' => 'HPP', 'sub_kategori' => 'PENYUSUTAN PABRIK', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5204', 'nama_akun' => 'Beban penyusutan kendaraan', 'kategori_akun' => 'HPP', 'sub_kategori' => 'PENYUSUTAN PABRIK', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '5205', 'nama_akun' => 'Beban penyusutan inventaris kantor', 'kategori_akun' => 'HPP', 'sub_kategori' => 'PENYUSUTAN PABRIK', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],

            // BEBAN OPERASIONAL (6100 - 6114)
            ['kode_akun' => '6100', 'nama_akun' => 'Beban Perlengkapan', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6101', 'nama_akun' => 'Beban Gaji', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6102', 'nama_akun' => 'Beban air & Listrik', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6103', 'nama_akun' => 'Beban Pulsa & Internet', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6104', 'nama_akun' => 'Biaya Marketing', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6105', 'nama_akun' => 'Beban rumah tangga', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6106', 'nama_akun' => 'Beban Pemeliharaan', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6107', 'nama_akun' => 'Beban BBM', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6108', 'nama_akun' => 'Beban adm kantor', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6109', 'nama_akun' => 'Biaya Adm Bank', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6110', 'nama_akun' => 'Beban lain-lain', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6111', 'nama_akun' => 'Beban pengiriman paket & dokumen', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6112', 'nama_akun' => 'Beban Angkut', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6113', 'nama_akun' => 'Beban Sewa', 'kategori_akun' => 'BEBAN', 'sub_kategori' => 'BEBAN OPERASIONAL', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'DEBET', 'is_kas_bank' => false],
            ['kode_akun' => '6114', 'nama_akun' => 'pendapatan lain-lain', 'kategori_akun' => 'PENDAPATAN', 'sub_kategori' => 'PENDAPATAN LAIN', 'posisi_keuangan' => 'LABA/RUGI', 'saldo_normal' => 'KREDIT', 'is_kas_bank' => false],
        ];

        DB::transaction(function () use ($newAccounts) {
            $inserted = 0;
            $updated = 0;

            foreach ($newAccounts as $acc) {
                $existing = MstCoa::where('kode_akun', $acc['kode_akun'])->first();

                if ($existing) {
                    $existing->update([
                        'nama_akun'       => $acc['nama_akun'],
                        'kategori_akun'   => $acc['kategori_akun'],
                        'sub_kategori'    => $acc['sub_kategori'],
                        'posisi_keuangan' => $acc['posisi_keuangan'],
                        'saldo_normal'    => $acc['saldo_normal'],
                        'is_kas_bank'     => $acc['is_kas_bank'],
                        'active_st'       => true,
                        'deleted_st'      => false,
                        'updated_by'      => 'SYSTEM_SYNC',
                    ]);
                    $updated++;
                } else {
                    MstCoa::create(array_merge($acc, [
                        'saldo_awal'     => 0,
                        'saldo_berjalan' => 0,
                        'keterangan'     => 'Sinkronisasi Master COA Baru 2026',
                        'created_by'     => 'SYSTEM_SYNC',
                        'updated_by'     => 'SYSTEM_SYNC',
                        'active_st'      => true,
                        'deleted_st'     => false,
                        'version_no'     => 1,
                    ]));
                    $inserted++;
                }
            }

            // Update Pemetaan Pengaturan Jurnal Otomatis agar menunjuk COA yang benar
            $coaMap = MstCoa::where('deleted_st', false)
                ->pluck('coa_id', 'kode_akun')
                ->toArray();

            $settingUpdates = [
                'PEMBELIAN_AKUN_PERSEDIAAN'   => $coaMap['1150'] ?? null, // Persediaan Bahan Baku
                'PEMBELIAN_AKUN_HUTANG'       => $coaMap['2100'] ?? null, // Hutang Usaha
                'PEMBELIAN_AKUN_PPN_MASUKAN'  => $coaMap['1130'] ?? null, // PPN Masukan
                'PEMBELIAN_AKUN_PPH_22'       => $coaMap['2107'] ?? ($coaMap['2105'] ?? null), // Hutang PPh 22 / Pajak
                'HPP_WIP_AKUN_WIP'            => $coaMap['1151'] ?? null, // Persediaan WIP
                'HPP_WIP_AKUN_BAHAN'          => $coaMap['1150'] ?? null, // Persediaan Bahan Baku
                'HPP_WIP_AKUN_GAS'            => $coaMap['2100'] ?? null, // Akrual Gas CNG / Hutang Usaha
                'HPP_WIP_AKUN_TENAGA_KERJA'   => $coaMap['2102'] ?? null, // Hutang Gaji
                'HPP_WIP_AKUN_OVERHEAD'       => $coaMap['2100'] ?? null, // Overhead
                'HPP_FG_AKUN_FG'              => $coaMap['1152'] ?? null, // Persediaan FG
                'HPP_FG_AKUN_WIP'             => $coaMap['1151'] ?? null, // Persediaan WIP
                'HPP_FG_AKUN_KEMASAN'         => $coaMap['1153'] ?? null, // Persediaan Bahan Penolong & Kemasan
                'HPP_FG_AKUN_TENAGA_KERJA'    => $coaMap['2102'] ?? null, // Hutang Gaji
                'HPP_FG_AKUN_OVERHEAD'        => $coaMap['2100'] ?? null, // Overhead Kemas
            ];

            foreach ($settingUpdates as $kunci => $coaId) {
                if ($coaId) {
                    MstPengaturanJurnal::where('kunci', $kunci)
                        ->where('tipe_nilai', 'COA')
                        ->update(['nilai_coa_id' => $coaId]);
                }
            }

            // Tambahkan setting baru untuk Pembelian Bahan Penolong jika belum ada
            if (isset($coaMap['1153'])) {
                MstPengaturanJurnal::firstOrCreate(
                    ['kunci' => 'PEMBELIAN_AKUN_PENOLONG'],
                    [
                        'label'        => 'Akun Persediaan Bahan Penolong & Kemasan (Debet)',
                        'grup'         => 'PEMBELIAN',
                        'tipe_nilai'   => 'COA',
                        'nilai_coa_id' => $coaMap['1153'],
                        'deskripsi'    => 'Akun debet otomatis saat mencatat pembelian bahan penolong, bumbu, plastik, dan karton.',
                        'urutan'       => 2,
                        'created_by'   => 'SYSTEM_SYNC',
                        'updated_by'   => 'SYSTEM_SYNC',
                        'active_st'    => true,
                        'deleted_st'   => false,
                    ]
                );

                MstPengaturanJurnal::firstOrCreate(
                    ['kunci' => 'HPP_WIP_AKUN_PENOLONG'],
                    [
                        'label'        => 'Persediaan Bahan Penolong Terpakai (Minyak & Bumbu)',
                        'grup'         => 'HPP_WIP',
                        'tipe_nilai'   => 'COA',
                        'nilai_coa_id' => $coaMap['1153'],
                        'deskripsi'    => 'Pengeluaran fisik minyak goreng, bumbu rendam, dan perenyah saat proses penggorengan keripik curah.',
                        'urutan'       => 3,
                        'created_by'   => 'SYSTEM_SYNC',
                        'updated_by'   => 'SYSTEM_SYNC',
                        'active_st'    => true,
                        'deleted_st'   => false,
                    ]
                );
            }

            // Setting Ongkos Angkut / Ekspedisi Pembelian
            if (isset($coaMap['6112'])) {
                MstPengaturanJurnal::firstOrCreate(
                    ['kunci' => 'PEMBELIAN_AKUN_ONGKIR'],
                    [
                        'label'        => 'Biaya Ongkos Angkut / Kirim Pembelian (Debet)',
                        'grup'         => 'PEMBELIAN',
                        'tipe_nilai'   => 'COA',
                        'nilai_coa_id' => $coaMap['6112'],
                        'deskripsi'    => 'Akun beban ongkos kirim/ekspedisi saat ada biaya angkut yang ditambahkan ke tagihan pembelian.',
                        'urutan'       => 3,
                        'created_by'   => 'SYSTEM_SYNC',
                        'updated_by'   => 'SYSTEM_SYNC',
                        'active_st'    => true,
                        'deleted_st'   => false,
                    ]
                );
            }

            // Setting Uang Muka Pembelian (Down Payment / DP)
            if (isset($coaMap['1122'])) {
                MstPengaturanJurnal::firstOrCreate(
                    ['kunci' => 'PEMBELIAN_AKUN_UANG_MUKA'],
                    [
                        'label'        => 'Penyelesaian Uang Muka Pembelian / DP (Kredit)',
                        'grup'         => 'PEMBELIAN',
                        'tipe_nilai'   => 'COA',
                        'nilai_coa_id' => $coaMap['1122'],
                        'deskripsi'    => 'Akun uang muka/DP pembelian yang dikreditkan saat tagihan supplier terbit (mengurangi kewajiban hutang).',
                        'urutan'       => 5,
                        'created_by'   => 'SYSTEM_SYNC',
                        'updated_by'   => 'SYSTEM_SYNC',
                        'active_st'    => true,
                        'deleted_st'   => false,
                    ]
                );
            }

            // Nonaktifkan FOH pada HPP WIP sesuai kebutuhan pabrik Mirasa
            MstPengaturanJurnal::where('kunci', 'HPP_WIP_AKUN_OVERHEAD')->update([
                'deleted_st' => true,
                'active_st'  => false,
            ]);

            echo "Berhasil sinkronisasi COA Baru: {$inserted} akun baru dibuat, {$updated} akun diperbarui." . PHP_EOL;
        });
    }
}
