<?php

namespace Database\Seeders;

use App\Models\Keuangan\MstCoa;
use App\Models\MasterData\MstPajak;
use Illuminate\Database\Seeder;

class MstPajakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari COA terkait dari mst_coa
        $coaPpnMasukan  = MstCoa::where('kode_akun', '1130')->orWhere('nama_akun', 'ILIKE', '%PPN Masukan%')->first();
        $coaPpnKeluaran = MstCoa::where('kode_akun', '2201')->orWhere('nama_akun', 'ILIKE', '%PPN Keluaran%')->first();
        $coaHutangPph23 = MstCoa::where('kode_akun', '2105')->orWhere('nama_akun', 'ILIKE', '%PPH 23%')->first();
        $coaHutangPpn   = MstCoa::where('kode_akun', '2103')->orWhere('nama_akun', 'ILIKE', '%Hutang PPN%')->first();

        $pajakData = [
            [
                'pajak_cd'         => 'PPN-11',
                'pajak_nm'         => 'PPN 11% (Standar Nasional)',
                'sifat_pajak'      => 'TAMBAH',
                'tarif_persen'     => 11.00,
                'is_pembelian'     => true,
                'coa_pembelian_id' => $coaPpnMasukan?->coa_id,
                'is_penjualan'     => true,
                'coa_penjualan_id' => $coaPpnKeluaran?->coa_id,
                'keterangan'       => 'Tarif PPN umum 11% sesuai UU HPP untuk transaksi jual-beli barang kena pajak dan jasa kena pajak.',
                'is_default'       => true,
            ],
            [
                'pajak_cd'         => 'NON-PPN',
                'pajak_nm'         => 'Bebas Pajak (Non-PPN / Petani)',
                'sifat_pajak'      => 'TAMBAH',
                'tarif_persen'     => 0.00,
                'is_pembelian'     => true,
                'coa_pembelian_id' => null,
                'is_penjualan'     => true,
                'coa_penjualan_id' => null,
                'keterangan'       => 'Objek tidak kena PPN atau pembelian langsung komoditas pertanian singkong/ubi dari petani non-PKP.',
                'is_default'       => false,
            ],
            [
                'pajak_cd'         => 'PPH-23-JASA',
                'pajak_nm'         => 'PPh Pasal 23 Jasa & Sewa Alat (2%)',
                'sifat_pajak'      => 'POTONG',
                'tarif_persen'     => 2.00,
                'is_pembelian'     => true,
                'coa_pembelian_id' => $coaHutangPph23?->coa_id,
                'is_penjualan'     => false,
                'coa_penjualan_id' => null,
                'keterangan'       => 'Pemotongan PPh 23 sebesar 2% atas jasa ekspedisi/angkut, jasa teknik, jasa perbaikan mesin, dan sewa alat/kendaraan ber-NPWP.',
                'is_default'       => false,
            ],
            [
                'pajak_cd'         => 'PPH-4-2',
                'pajak_nm'         => 'PPh Pasal 4 Ayat 2 Sewa Gedung/Tanah (10%)',
                'sifat_pajak'      => 'POTONG',
                'tarif_persen'     => 10.00,
                'is_pembelian'     => true,
                'coa_pembelian_id' => $coaHutangPpn?->coa_id,
                'is_penjualan'     => false,
                'coa_penjualan_id' => null,
                'keterangan'       => 'Pemotongan PPh Final 10% atas sewa tanah, bangunan, atau gudang penumpukan hasil panen.',
                'is_default'       => false,
            ],
            [
                'pajak_cd'         => 'PPH-21-AHLI',
                'pajak_nm'         => 'PPh Pasal 21 Tenaga Ahli / Borongan (5%)',
                'sifat_pajak'      => 'POTONG',
                'tarif_persen'     => 5.00,
                'is_pembelian'     => true,
                'coa_pembelian_id' => null,
                'is_penjualan'     => false,
                'coa_penjualan_id' => null,
                'keterangan'       => 'Pemotongan PPh 21 tarif efektif atas imbalan jasa tenaga ahli, konsultan, atau borongan luar non-karyawan.',
                'is_default'       => false,
            ],
            [
                'pajak_cd'         => 'PPH-22-KOMODITAS',
                'pajak_nm'         => 'PPh Pasal 22 Pedagang Pengumpul (0.25%)',
                'sifat_pajak'      => 'POTONG',
                'tarif_persen'     => 0.25,
                'is_pembelian'     => true,
                'coa_pembelian_id' => null,
                'is_penjualan'     => false,
                'coa_penjualan_id' => null,
                'keterangan'       => 'Pemotongan PPh 22 atas pembelian bahan baku untuk keperluan industri dari pedagang pengumpul.',
                'is_default'       => false,
            ],
        ];

        foreach ($pajakData as $data) {
            MstPajak::updateOrCreate(
                ['pajak_cd' => $data['pajak_cd']],
                array_merge($data, [
                    'active_st'  => true,
                    'deleted_st' => false,
                    'created_by' => 'SEEDER',
                    'updated_by' => 'SEEDER',
                ])
            );
        }
    }
}
