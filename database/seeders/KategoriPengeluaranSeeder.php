<?php

namespace Database\Seeders;

use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKategoriPengeluaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriPengeluaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'kode_kategori' => 'LISTRIK_AIR',
                'nama_kategori' => 'Listrik & Air PLN/PDAM',
                'coa_code'      => '6102',
                'keterangan'    => 'Tagihan listrik PLN, air PDAM, dan utilitas pabrik',
            ],
            [
                'kode_kategori' => 'BBM_TRANSPORT',
                'nama_kategori' => 'Bahan Bakar & Solar Mesin/Armada',
                'coa_code'      => '6107',
                'keterangan'    => 'BBM kendaraan operasional, solar genset / mesin produksi',
            ],
            [
                'kode_kategori' => 'PEMELIHARAAN_MESIN',
                'nama_kategori' => 'Pemeliharaan & Servis Mesin Pabrik',
                'coa_code'      => '6106',
                'keterangan'    => 'Servis berkala, perbaikan mesin, sparepart, dan bengkel',
            ],
            [
                'kode_kategori' => 'PERLENGKAPAN_ATK',
                'nama_kategori' => 'Perlengkapan & ATK Pabrik',
                'coa_code'      => '6100',
                'keterangan'    => 'Alat tulis kantor, kertas, pita ribbon, lakban, kardus penolong',
            ],
            [
                'kode_kategori' => 'KONSUMSI_RUMAH_TANGGA',
                'nama_kategori' => 'Konsumsi, Dapur & Lembur',
                'coa_code'      => '6105',
                'keterangan'    => 'Snack rapat, makan siang karyawan lembur, kopi, teh, dapur mess',
            ],
            [
                'kode_kategori' => 'RISET_QC',
                'nama_kategori' => 'Riset Formula Baru & Pengujian QC',
                'coa_code'      => '6110', // 6115 atau 6110
                'keterangan'    => 'Pengujian laboratorium, reagen QC, uji rasa, sampel formulasi',
            ],
            [
                'kode_kategori' => 'PENGIRIMAN_EKSPEDISI',
                'nama_kategori' => 'Pengiriman & Ekspedisi Kilat',
                'coa_code'      => '6111',
                'keterangan'    => 'Ongkos kirim paket dokumen, sampel ke buyer, kargo ekspedisi kilat',
            ],
            [
                'kode_kategori' => 'BIAYA_OPERASIONAL',
                'nama_kategori' => 'Operasional Umum Lainnya',
                'coa_code'      => '6110',
                'keterangan'    => 'Biaya tak terduga, retribusi lingkungan, kebersihan, parkir, dan lain-lain',
            ],
        ];

        DB::transaction(function () use ($categories) {
            foreach ($categories as $cat) {
                $coa = MstCoa::where('kode_akun', $cat['coa_code'])->first();
                $coaId = $coa?->coa_id;

                MstKategoriPengeluaran::updateOrCreate(
                    ['kode_kategori' => $cat['kode_kategori']],
                    [
                        'nama_kategori'  => $cat['nama_kategori'],
                        'coa_id_default' => $coaId,
                        'keterangan'     => $cat['keterangan'],
                        'active_st'      => true,
                        'deleted_st'     => false,
                        'version_no'     => 1,
                        'created_by'     => 'SEEDER',
                        'updated_by'     => 'SEEDER',
                    ]
                );
            }
        });
    }
}
