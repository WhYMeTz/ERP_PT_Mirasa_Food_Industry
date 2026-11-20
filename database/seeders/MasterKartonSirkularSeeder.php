<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstJenisBarang;
use App\Models\MasterData\MstSatuan;
use Illuminate\Support\Facades\DB;

class MasterKartonSirkularSeeder extends Seeder
{
    /**
     * Run the database seeds untuk Master Data Kemasan Karton Sirkular (PT Mirasa Food Industry).
     */
    public function run(): void
    {
        // 1. Ambil ID Jenis Barang Kemasan / Bahan Penolong
        $jenisId = MstJenisBarang::where('jenis_barang_cd', 'BP')->value('jenis_barang_id') 
            ?? MstJenisBarang::where('jenis_barang_cd', 'PACK')->value('jenis_barang_id') 
            ?? 3;

        // 2. Ambil ID Satuan Lembar / PCS
        $satuanId = MstSatuan::where('satuan_cd', 'LEMBAR')->value('satuan_id') 
            ?? MstSatuan::where('satuan_cd', 'PCS')->value('satuan_id') 
            ?? 14;

        DB::transaction(function () use ($jenisId, $satuanId) {
            // A. Update nama Karton Baru IFM (ID 29 jika ada) agar spesifik
            $kartonBaru = MstBarang::where('barang_cd', 'KIF00G-BP1')->first();
            if ($kartonBaru) {
                $kartonBaru->update([
                    'barang_nm'          => 'KARTON POLOS IFM (BARU)',
                    'harga_beli_standar' => 8741.00,
                    'active_st'          => true,
                    'deleted_st'         => false,
                ]);
            } else {
                MstBarang::create([
                    'barang_cd'          => 'KIF00G-BP1',
                    'barang_nm'          => 'KARTON POLOS IFM (BARU)',
                    'jenis_barang_id'    => $jenisId,
                    'satuan_dasar_id'    => $satuanId,
                    'harga_beli_standar' => 8741.00,
                    'batas_minimum_qty'  => 100,
                    'konversi_qty'       => 1.0,
                    'active_st'          => true,
                    'deleted_st'         => false,
                    'created_by'         => '1',
                ]);
            }

            // B. Daftarkan Karton Puteran IFM (Harga Beli / 5 = Rp 1.748,20)
            MstBarang::updateOrCreate(
                ['barang_cd' => 'KIF00G-PUT'],
                [
                    'barang_nm'          => 'KARTON POLOS IFM (PUTERAN)',
                    'jenis_barang_id'    => $jenisId,
                    'satuan_dasar_id'    => $satuanId,
                    'harga_beli_standar' => 1748.20, // Rp 8.741 / 5 (Amortisasi 20%)
                    'batas_minimum_qty'  => 50,
                    'konversi_qty'       => 1.0,
                    'active_st'          => true,
                    'deleted_st'         => false,
                    'created_by'         => '1',
                ]
            );

            // C. Daftarkan Karton BMG (Retur Pengiriman dari Ekspedisi - Pending Sortir)
            MstBarang::updateOrCreate(
                ['barang_cd' => 'KIF00G-BMG'],
                [
                    'barang_nm'          => 'KARTON POLOS IFM (BMG RETUR)',
                    'jenis_barang_id'    => $jenisId,
                    'satuan_dasar_id'    => $satuanId,
                    'harga_beli_standar' => 0.00,
                    'batas_minimum_qty'  => 0,
                    'konversi_qty'       => 1.0,
                    'active_st'          => true,
                    'deleted_st'         => false,
                    'created_by'         => '1',
                ]
            );

            // D. Daftarkan Karton Reject / Rusak (Khusus Alokasi Produksi Manual)
            MstBarang::updateOrCreate(
                ['barang_cd' => 'KMS-KTN-REJ'],
                [
                    'barang_nm'          => 'KARTON REJECT / RUSAK (MANUAL)',
                    'jenis_barang_id'    => $jenisId,
                    'satuan_dasar_id'    => $satuanId,
                    'harga_beli_standar' => 0.00, // Rp 0 (Tidak masuk hitungan HPP Manual)
                    'batas_minimum_qty'  => 0,
                    'konversi_qty'       => 1.0,
                    'active_st'          => true,
                    'deleted_st'         => false,
                    'created_by'         => '1',
                ]
            );
        });
    }
}

