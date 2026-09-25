<?php

namespace Database\Seeders;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstJenisBarang;
use App\Models\MasterData\MstSatuan;
use Illuminate\Database\Seeder;

class MasterBarangExcelSeeder extends Seeder
{
    /**
     * Run the database seeds for complete PT Mirasa Excel Master Items.
     */
    public function run(): void
    {
        // 1. Pastikan Kategori Utama Tersedia
        $jenisList = [
            ['jenis_barang_cd' => 'BB',   'jenis_barang_nm' => 'Bahan Baku & Setengah Jadi'],
            ['jenis_barang_cd' => 'BP',   'jenis_barang_nm' => 'Bahan Penolong & Kemasan'],
            ['jenis_barang_cd' => 'WIP',  'jenis_barang_nm' => 'Work in Progress / Olahan Penggorengan'],
            ['jenis_barang_cd' => 'FG',   'jenis_barang_nm' => 'Barang Jadi (Finished Goods)'],
            ['jenis_barang_cd' => 'RAW',  'jenis_barang_nm' => 'Bahan Baku Mentah'],
            ['jenis_barang_cd' => 'PACK', 'jenis_barang_nm' => 'Kemasan & Packaging'],
            ['jenis_barang_cd' => 'SUPP', 'jenis_barang_nm' => 'Bahan Penolong & Bumbu'],
        ];

        $jenisMap = [];
        foreach ($jenisList as $j) {
            $record = MstJenisBarang::firstOrCreate(
                ['jenis_barang_cd' => $j['jenis_barang_cd']],
                ['jenis_barang_nm' => $j['jenis_barang_nm']]
            );
            $jenisMap[$j['jenis_barang_cd']] = $record->jenis_barang_id;
        }

        // 2. Pastikan Semua Satuan di Excel Tersedia
        $satuanList = [
            'KG'           => 'Kilogram',
            'LITER'        => 'Liter',
            'LEMBAR'       => 'Lembar',
            'ROLL'         => 'Roll',
            'PACK'         => 'Pack',
            'BALL 5KG'     => 'Ball 5 Kg',
            'KARTON 7KG'   => 'Karton 7 Kg',
            'KARTON 6KG'   => 'Karton 6 Kg',
            'KARTON 5KG'   => 'Karton 5 Kg',
            'KARTON 3,8KG' => 'Karton 3,8 Kg',
            'KARTON'       => 'Karton',
            'BUNGKUS'      => 'Bungkus',
            'PCS'          => 'Pieces',
            'SAK'          => 'Sak',
        ];

        $satuanMap = [];
        foreach ($satuanList as $cd => $nm) {
            $record = MstSatuan::firstOrCreate(
                ['satuan_cd' => $cd],
                ['satuan_nm' => $nm]
            );
            $satuanMap[$cd] = $record->satuan_id;
        }

        // 3. Seluruh Daftar 59 Item Master Barang Berdasarkan Foto Excel PT Mirasa
        $barangData = [
            // --- BAHAN BAKU (BB) ---
            [
                'barang_cd' => 'BB-SK001',
                'barang_nm' => 'SINGKONG KUPAS',
                'jenis_cd'  => 'BB',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BB-SK002',
                'barang_nm' => 'SINGKONG KULIT',
                'jenis_cd'  => 'BB',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BB-OP003',
                'barang_nm' => 'OPAK MENTAH',
                'jenis_cd'  => 'BB',
                'satuan_cd' => 'BALL 5KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BB-PY004',
                'barang_nm' => 'PUYUR MENTAH',
                'jenis_cd'  => 'BB',
                'satuan_cd' => 'BALL 5KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BB-UU005',
                'barang_nm' => 'UBI UNGU',
                'jenis_cd'  => 'BB',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BB-UB006',
                'barang_nm' => 'UBI',
                'jenis_cd'  => 'BB',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],

            // --- BAHAN PENOLONG (BP) - MINYAK & PERENYAH ---
            [
                'barang_cd' => 'MSW00G-BP2',
                'barang_nm' => 'MINYAK SAWIT',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 17748.64,
            ],
            [
                'barang_cd' => 'MKP00G-BP1',
                'barang_nm' => 'MINYAK KELAPA',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LITER',
                'harga'     => 38520.77,
            ],
            [
                'barang_cd' => 'PRH00G-BP2',
                'barang_nm' => 'PERENYAH',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 4661.48,
            ],

            // --- BAHAN PENOLONG (BP) - KELUARGA BUMBU (B...00G-BP) ---
            [
                'barang_cd' => 'BBL00G-BP1',
                'barang_nm' => 'BUMBU BALADO',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BCS00G-BP2',
                'barang_nm' => 'BUMBU CHILLI SEAS',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BCL00G-BP3',
                'barang_nm' => 'BUMBU CHILLI LEMON',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BSS00G-BP4',
                'barang_nm' => 'BUMBU SEASALT',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'BBQ00G-BP5',
                'barang_nm' => 'BUMBU BBQ',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],

            // --- BAHAN PENOLONG (BP) - KELUARGA PLASTIK & ROLL ---
            [
                'barang_cd' => 'PHD00G-BP1',
                'barang_nm' => 'PLASTIK HD',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 29407.18,
            ],
            [
                'barang_cd' => 'PEA00G-BP2',
                'barang_nm' => 'PLASTIK EXPORT RASA ASIN',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'PEC00G-BP3',
                'barang_nm' => 'PLASTIK EXPORT CHILLI',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'PNG00G-BP4',
                'barang_nm' => 'PLASTIK NAGINDO',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'RXX00G-BP1',
                'barang_nm' => 'ROLL PING-PING',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'ROLL',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'RMX00G-BP2',
                'barang_nm' => 'ROLL MAKSI',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'ROLL',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'RXX00G-BP3',
                'barang_nm' => 'ROLL PING-PING 2000',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'ROLL',
                'harga'     => 0,
            ],

            // --- BAHAN PENOLONG (BP) - KELUARGA KARTON (K...00G-BP) ---
            [
                'barang_cd' => 'KIF00G-BP1',
                'barang_nm' => 'KARTON POLOS IFM',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'KJB00G-BP2',
                'barang_nm' => 'KARTON JUMBO 20',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'KJK00G-BP3',
                'barang_nm' => 'KARTON JUMBO 10',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'KEA00G-BP4',
                'barang_nm' => 'KARTON EKSPORT ASIN',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'KEC00G-BP5',
                'barang_nm' => 'KARTON EKSPORT CHILLI',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'KXX00G-BP6',
                'barang_nm' => 'KARTON PING-PING',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'KMX00G-BP7',
                'barang_nm' => 'KARTON MAKSI',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'KXB00G-BP8',
                'barang_nm' => 'KARTON PING-PING 2000',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'KNG00G-BP9',
                'barang_nm' => 'KARTON NAGINDO',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'LEMBAR',
                'harga'     => 0,
            ],

            // --- BAHAN PENOLONG (BP) - LAKBAN, SARUNG TANGAN, RAFIA ---
            [
                'barang_cd' => 'LBS00G-BP1',
                'barang_nm' => 'LAKBAN BESAR',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'ROLL',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'LSD00G-BP2',
                'barang_nm' => 'LAKBAN SEDANG',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'ROLL',
                'harga'     => 51406.74,
            ],
            [
                'barang_cd' => 'LKC00G-BP3',
                'barang_nm' => 'LAKBAN KECIL',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'ROLL',
                'harga'     => 6140.59,
            ],
            [
                'barang_cd' => 'TRF00G-BP1',
                'barang_nm' => 'RAFIA',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'STK00G-BP1',
                'barang_nm' => 'SARUNG TANGAN KAIN',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'PACK',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'STP00G-BP2',
                'barang_nm' => 'SARUNG TANGAN PLASTIK',
                'jenis_cd'  => 'BP',
                'satuan_cd' => 'PACK',
                'harga'     => 0,
            ],

            // --- WORK IN PROCESS (WIP) ---
            [
                'barang_cd' => 'WIP-FCC',
                'barang_nm' => 'KERIPIK SINGKONG TANPA BUMBU',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 6KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-BRK',
                'barang_nm' => 'KS BERKO',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-ASB',
                'barang_nm' => 'KS ASIN BARCO',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 7KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-ASW',
                'barang_nm' => 'KS ASIN SAWIT',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 7KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-BLQ',
                'barang_nm' => 'KS BALQI',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 7KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-NSL',
                'barang_nm' => 'KS NO SALT',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 7KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-UCM',
                'barang_nm' => 'KS UCAMP',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 7KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-UBU',
                'barang_nm' => 'KERIPIK UBI UNGU',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 5KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-UBI',
                'barang_nm' => 'KERIPIK UBI',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 5KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-OPA',
                'barang_nm' => 'OPAK RASA ASIN',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 5KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-OPB',
                'barang_nm' => 'OPAK RASA BALADO',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 5KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-PYA',
                'barang_nm' => 'PUYUR RASA ASIN',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 3,8KG',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'WIP-PYB',
                'barang_nm' => 'PUYUR RASA BALADO',
                'jenis_cd'  => 'WIP',
                'satuan_cd' => 'KARTON 3,8KG',
                'harga'     => 0,
            ],

            // --- FINISH GOOD (FG) ---
            [
                'barang_cd' => 'FG-XX001',
                'barang_nm' => 'PING-PING 500',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'KARTON',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-MX002',
                'barang_nm' => 'MAKSI 500',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'KARTON',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-XX003',
                'barang_nm' => 'PING-PING 2000',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'KARTON',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-JB004',
                'barang_nm' => 'KS JUMBO 10',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'KARTON',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-JB005',
                'barang_nm' => 'KS JUMBO 20',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'KARTON',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-EA006',
                'barang_nm' => 'KS EKSPOR RASA ASIN',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'KARTON',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-EC007',
                'barang_nm' => 'KS EKSPOR RASA CHILLI',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'KARTON',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-NA008',
                'barang_nm' => 'KS NAGINDO',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'KARTON',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-ECA09',
                'barang_nm' => 'KS ECERAN ASIN 500GR',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'BUNGKUS',
                'harga'     => 0,
            ],
            [
                'barang_cd' => 'FG-ECB10',
                'barang_nm' => 'KS ECERAN BALADO 500GR',
                'jenis_cd'  => 'FG',
                'satuan_cd' => 'BUNGKUS',
                'harga'     => 0,
            ],
        ];

        foreach ($barangData as $item) {
            $jenisId = $jenisMap[$item['jenis_cd']] ?? $jenisMap['BP'] ?? 1;
            $satuanId = $satuanMap[$item['satuan_cd']] ?? $satuanMap['KG'] ?? 1;

            $existing = MstBarang::where('barang_cd', $item['barang_cd'])->first();
            if ($existing) {
                $existing->update([
                    'barang_nm'          => $item['barang_nm'],
                    'jenis_barang_id'    => $jenisId,
                    'satuan_dasar_id'    => $satuanId,
                    'harga_beli_standar' => $item['harga'] > 0 ? $item['harga'] : $existing->harga_beli_standar,
                    'batas_minimum_qty'  => $existing->batas_minimum_qty ?: 100,
                    'active_st'          => true,
                ]);
            } else {
                MstBarang::create([
                    'barang_cd'          => $item['barang_cd'],
                    'barang_nm'          => $item['barang_nm'],
                    'jenis_barang_id'    => $jenisId,
                    'satuan_dasar_id'    => $satuanId,
                    'harga_beli_standar' => $item['harga'],
                    'batas_minimum_qty'  => 100,
                    'konversi_qty'       => 1,
                    'created_by'         => 1,
                    'updated_by'         => 1,
                    'active_st'          => true,
                ]);
            }
        }
    }
}
