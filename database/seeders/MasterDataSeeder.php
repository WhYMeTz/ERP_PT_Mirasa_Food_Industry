<?php

namespace Database\Seeders;

use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstJenisBarang;
use App\Models\MasterData\MstJenisSupplier;
use App\Models\MasterData\MstSatuan;
use App\Models\MasterData\MstSupplier;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Master Jenis Barang
        $jenisData = [
            ['jenis_barang_cd' => 'RAW', 'jenis_barang_nm' => 'Bahan Baku Mentah (Singkong, Minyak)'],
            ['jenis_barang_cd' => 'WIP', 'jenis_barang_nm' => 'Work in Progress / Setengah Jadi'],
            ['jenis_barang_cd' => 'FG',  'jenis_barang_nm' => 'Barang Jadi (Finished Goods)'],
            ['jenis_barang_cd' => 'PACK', 'jenis_barang_nm' => 'Bahan Kemasan & Packaging'],
            ['jenis_barang_cd' => 'SUPP', 'jenis_barang_nm' => 'Bahan Pembantu & Bumbu'],
        ];

        foreach ($jenisData as $jenis) {
            MstJenisBarang::firstOrCreate(
                ['jenis_barang_cd' => $jenis['jenis_barang_cd']],
                ['jenis_barang_nm' => $jenis['jenis_barang_nm']]
            );
        }

        // 2. Master Jenis Supplier
        $jenisSupplierData = [
            ['jenis_supplier_cd' => 'RAW',       'jenis_supplier_nm' => 'Bahan Baku Mentah (Singkong, Minyak)'],
            ['jenis_supplier_cd' => 'BUMBU',     'jenis_supplier_nm' => 'Bumbu, Perasa & Bahan Penolong'],
            ['jenis_supplier_cd' => 'KEMASAN',   'jenis_supplier_nm' => 'Kemasan, Plastik & Karton'],
            ['jenis_supplier_cd' => 'SPAREPART', 'jenis_supplier_nm' => 'Suku Cadang Mesin & Peralatan'],
            ['jenis_supplier_cd' => 'UMUM',      'jenis_supplier_nm' => 'Jasa Angkut, Bahan Bakar & Umum'],
        ];

        $jenisSupplierMap = [];
        foreach ($jenisSupplierData as $js) {
            $record = MstJenisSupplier::firstOrCreate(
                ['jenis_supplier_cd' => $js['jenis_supplier_cd']],
                ['jenis_supplier_nm' => $js['jenis_supplier_nm']]
            );
            $jenisSupplierMap[$js['jenis_supplier_cd']] = $record->jenis_supplier_id;
        }

        // 3. Master Satuan
        $satuanData = [
            ['satuan_cd' => 'KG',   'satuan_nm' => 'Kilogram'],
            ['satuan_cd' => 'GRAM', 'satuan_nm' => 'Gram'],
            ['satuan_cd' => 'PCS',  'satuan_nm' => 'Pieces / Butir'],
            ['satuan_cd' => 'SAK',  'satuan_nm' => 'Sak (50 Kg)'],
            ['satuan_cd' => 'BAL',  'satuan_nm' => 'Bal'],
            ['satuan_cd' => 'DUS',  'satuan_nm' => 'Dus / Karton'],
            ['satuan_cd' => 'LTR',  'satuan_nm' => 'Liter'],
        ];

        foreach ($satuanData as $satuan) {
            MstSatuan::firstOrCreate(
                ['satuan_cd' => $satuan['satuan_cd']],
                ['satuan_nm' => $satuan['satuan_nm']]
            );
        }

        // 4. Master Gudang
        $gudangData = [
            ['gudang_cd' => 'GDG-RAW', 'gudang_nm' => 'Gudang Bahan Baku Singkong', 'tipe_gudang_cd' => 'RAW', 'alamat_txt' => 'Pabrik Blok A (Sisi Timur)'],
            ['gudang_cd' => 'GDG-PROD', 'gudang_nm' => 'Lantai Operasional Produksi', 'tipe_gudang_cd' => 'WIP', 'alamat_txt' => 'Area Penggorengan & Bumbu'],
            ['gudang_cd' => 'GDG-FG', 'gudang_nm' => 'Gudang Barang Jadi (Siap Kirim)', 'tipe_gudang_cd' => 'FG', 'alamat_txt' => 'Pabrik Blok C (Dekat Loading Dock)'],
            ['gudang_cd' => 'GDG-TRANSIT', 'gudang_nm' => 'Gudang Karantina & Sortir', 'tipe_gudang_cd' => 'TRANSIT', 'alamat_txt' => 'Pabrik Blok B'],
        ];

        foreach ($gudangData as $gudang) {
            MstGudang::firstOrCreate(
                ['gudang_cd' => $gudang['gudang_cd']],
                $gudang
            );
        }

        // 5. Master Supplier
        $supplierData = [
            [
                'supplier_cd'       => 'SUP-TANI-01',
                'supplier_nm'       => 'Kelompok Tani Singkong Makmur',
                'jenis_supplier_id' => $jenisSupplierMap['RAW'] ?? null,
                'kontak_no'         => '081234567891',
                'alamat_txt'        => 'Kec. Kertek, Wonosobo',
            ],
            [
                'supplier_cd'       => 'SUP-MINYAK-01',
                'supplier_nm'       => 'PT Sawit Murni Nusantara',
                'jenis_supplier_id' => $jenisSupplierMap['RAW'] ?? null,
                'kontak_no'         => '024-7654321',
                'alamat_txt'        => 'Kawasan Industri Candi, Semarang',
            ],
            [
                'supplier_cd'       => 'SUP-BUMBU-01',
                'supplier_nm'       => 'CV Rempah Alami Sejahtera',
                'jenis_supplier_id' => $jenisSupplierMap['BUMBU'] ?? null,
                'kontak_no'         => '085799887766',
                'alamat_txt'        => 'Jl. Magelang KM 10, Sleman',
            ],
        ];

        foreach ($supplierData as $supplier) {
            $existing = MstSupplier::where('supplier_cd', $supplier['supplier_cd'])->first();
            if ($existing) {
                $existing->update([
                    'jenis_supplier_id' => $supplier['jenis_supplier_id'],
                    'kontak_no'         => $supplier['kontak_no'],
                    'alamat_txt'        => $supplier['alamat_txt'],
                ]);
            } else {
                MstSupplier::create($supplier);
            }
        }

        // 6. Master Customer
        $customerData = [
            ['customer_cd' => 'CUST-INDOFOOD', 'customer_nm' => 'PT Indofood CBP Sukses Makmur Tbk', 'kontak_no' => '021-57958822', 'alamat_txt' => 'Kawasan Industri Indofood, Cikarang'],
            ['customer_cd' => 'CUST-DIST-01', 'customer_nm' => 'Distributor Sumber Rezeki Snack', 'kontak_no' => '081399882211', 'alamat_txt' => 'Pusat Grosir Pasar Johar, Semarang'],
        ];

        foreach ($customerData as $customer) {
            MstCustomer::firstOrCreate(
                ['customer_cd' => $customer['customer_cd']],
                $customer
            );
        }
    }
}
