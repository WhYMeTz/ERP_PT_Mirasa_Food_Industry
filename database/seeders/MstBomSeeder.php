<?php

namespace Database\Seeders;

use App\Models\MasterData\MstBarang;
use App\Models\Produksi\MstBomDtl;
use App\Models\Produksi\MstBomHdr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MstBomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(MasterBarangExcelSeeder::class);

        DB::transaction(function () {
            // Ambil ID barang berdasarkan kode
            $barangMap = MstBarang::pluck('barang_id', 'barang_cd');
            $requiredCodes = [
                'WIP-FCC',
                'BB-SK001',
                'MSW00G-BP2',
                'FG-XX001',
                'FG-XX003',
            ];
            $missingCodes = array_diff($requiredCodes, $barangMap->keys()->all());

            if ($missingCodes !== []) {
                throw new \RuntimeException(
                    'Data BOM tidak dapat dibuat. Kode barang wajib belum tersedia: '
                    .implode(', ', $missingCodes)
                );
            }

            MstBomDtl::truncate();
            MstBomHdr::query()->delete();

            // 1. FORMULA STANDAR INDOFOOD (IFM) - KERIPIK SINGKONG POLOS TANPA BUMBU
            $targetIfmId = $barangMap['WIP-FCC'];
            $singkongId = $barangMap['BB-SK001'];
            $minyakSawitId = $barangMap['MSW00G-BP2'];
            $perenyahId = $barangMap['PRH00G-BP2'] ?? null;
            $kartonIfmId = $barangMap['KIF00G-BP1'] ?? null;
            $lakbanId = $barangMap['LBS00G-BP1'] ?? null;

            $bomIfm = MstBomHdr::create([
                'bom_no'           => 'BOM-IFM-001',
                'bom_nm'           => 'Produksi Keripik Singkong IFM / Indofood (Per 100 Karton)',
                'barang_jadi_id'   => $targetIfmId,
                'batch_ukuran_qty' => 100.0000,
                'catatan_txt'      => 'Formula resep standar pasokan Indofood (IFM). Termasuk singkong kupas, minyak sawit standar QC, perenyah, dan karton polos IFM.',
                'created_by'       => 'SYSTEM',
            ]);

            MstBomDtl::create([
                'bom_id'           => $bomIfm->bom_id,
                'barang_mentah_id' => $singkongId,
                'kebutuhan_qty'    => 500.0000,
                'catatan_txt'      => 'Singkong Kupas Grade A (5 kg per karton)',
                'created_by'       => 'SYSTEM',
            ]);

            MstBomDtl::create([
                'bom_id'           => $bomIfm->bom_id,
                'barang_mentah_id' => $minyakSawitId,
                'kebutuhan_qty'    => 150.0000,
                'catatan_txt'      => 'Minyak Sawit Goreng Standar Pabrik (1.5 kg per karton)',
                'created_by'       => 'SYSTEM',
            ]);

            if ($perenyahId) {
                MstBomDtl::create([
                    'bom_id'           => $bomIfm->bom_id,
                    'barang_mentah_id' => $perenyahId,
                    'kebutuhan_qty'    => 5.0000,
                    'catatan_txt'      => 'Perenyah / Crisping Agent Kimia Pangan Standar',
                    'created_by'       => 'SYSTEM',
                ]);
            }

            if ($kartonIfmId) {
                MstBomDtl::create([
                    'bom_id'           => $bomIfm->bom_id,
                    'barang_mentah_id' => $kartonIfmId,
                    'kebutuhan_qty'    => 100.0000,
                    'catatan_txt'      => 'Karton Kemasan Polos Khusus IFM',
                    'created_by'       => 'SYSTEM',
                ]);
            }

            if ($lakbanId) {
                MstBomDtl::create([
                    'bom_id'           => $bomIfm->bom_id,
                    'barang_mentah_id' => $lakbanId,
                    'kebutuhan_qty'    => 2.0000,
                    'catatan_txt'      => 'Lakban Coklat Besar Perekat Karton',
                    'created_by'       => 'SYSTEM',
                ]);
            }

            // 2. FORMULA STANDAR PING-PING 500 (Per 100 Karton)
            $targetPp500Id = $barangMap['FG-XX001'];
            $bumbuBaladoId = $barangMap['BBL00G-BP1'] ?? null;
            $rollPpId = $barangMap['RXX00G-BP1'] ?? null;
            $kartonPpId = $barangMap['KXX00G-BP6'] ?? null;

            $bomPp500 = MstBomHdr::create([
                'bom_no'           => 'BOM-PP-500',
                'bom_nm'           => 'Produksi Keripik Singkong Ping-Ping 500 (Per 100 Karton)',
                'barang_jadi_id'   => $targetPp500Id,
                'batch_ukuran_qty' => 100.0000,
                'catatan_txt'      => 'Formula resep kemasan retail Ping-Ping 500 gram. Rasa Balado khas Mirasa.',
                'created_by'       => 'SYSTEM',
            ]);

            MstBomDtl::create([
                'bom_id'           => $bomPp500->bom_id,
                'barang_mentah_id' => $singkongId,
                'kebutuhan_qty'    => 400.0000,
                'catatan_txt'      => 'Singkong Segar Terpilih',
                'created_by'       => 'SYSTEM',
            ]);

            MstBomDtl::create([
                'bom_id'           => $bomPp500->bom_id,
                'barang_mentah_id' => $minyakSawitId,
                'kebutuhan_qty'    => 120.0000,
                'catatan_txt'      => 'Minyak Sawit Goreng',
                'created_by'       => 'SYSTEM',
            ]);

            if ($bumbuBaladoId) {
                MstBomDtl::create([
                    'bom_id'           => $bomPp500->bom_id,
                    'barang_mentah_id' => $bumbuBaladoId,
                    'kebutuhan_qty'    => 25.0000,
                    'catatan_txt'      => 'Bumbu Tabur Balado Spesial',
                    'created_by'       => 'SYSTEM',
                ]);
            }

            if ($rollPpId) {
                MstBomDtl::create([
                    'bom_id'           => $bomPp500->bom_id,
                    'barang_mentah_id' => $rollPpId,
                    'kebutuhan_qty'    => 10.0000,
                    'catatan_txt'      => 'Roll Plastik Kemasan Ping-Ping',
                    'created_by'       => 'SYSTEM',
                ]);
            }

            if ($kartonPpId) {
                MstBomDtl::create([
                    'bom_id'           => $bomPp500->bom_id,
                    'barang_mentah_id' => $kartonPpId,
                    'kebutuhan_qty'    => 100.0000,
                    'catatan_txt'      => 'Kardus Master Box Ping-Ping',
                    'created_by'       => 'SYSTEM',
                ]);
            }

            // 3. FORMULA STANDAR PING-PING 2000 (Per 100 Karton)
            $targetPp2000Id = $barangMap['FG-XX003'];
            $rollPp2000Id = $barangMap['RXX00G-BP3'] ?? null;
            $kartonPp2000Id = $barangMap['KXB00G-BP8'] ?? null;

            $bomPp2000 = MstBomHdr::create([
                'bom_no'           => 'BOM-PP-2000',
                'bom_nm'           => 'Produksi Keripik Singkong Ping-Ping 2000 (Per 100 Karton)',
                'barang_jadi_id'   => $targetPp2000Id,
                'batch_ukuran_qty' => 100.0000,
                'catatan_txt'      => 'Formula resep kemasan jumbo retail Ping-Ping 2000.',
                'created_by'       => 'SYSTEM',
            ]);

            MstBomDtl::create([
                'bom_id'           => $bomPp2000->bom_id,
                'barang_mentah_id' => $singkongId,
                'kebutuhan_qty'    => 450.0000,
                'catatan_txt'      => 'Singkong Pilihan',
                'created_by'       => 'SYSTEM',
            ]);

            MstBomDtl::create([
                'bom_id'           => $bomPp2000->bom_id,
                'barang_mentah_id' => $minyakSawitId,
                'kebutuhan_qty'    => 130.0000,
                'catatan_txt'      => 'Minyak Sawit',
                'created_by'       => 'SYSTEM',
            ]);

            if ($bumbuBaladoId) {
                MstBomDtl::create([
                    'bom_id'           => $bomPp2000->bom_id,
                    'barang_mentah_id' => $bumbuBaladoId,
                    'kebutuhan_qty'    => 30.0000,
                    'catatan_txt'      => 'Bumbu Balado',
                    'created_by'       => 'SYSTEM',
                ]);
            }

            if ($rollPp2000Id) {
                MstBomDtl::create([
                    'bom_id'           => $bomPp2000->bom_id,
                    'barang_mentah_id' => $rollPp2000Id,
                    'kebutuhan_qty'    => 10.0000,
                    'catatan_txt'      => 'Roll Ping-Ping 2000',
                    'created_by'       => 'SYSTEM',
                ]);
            }

            if ($kartonPp2000Id) {
                MstBomDtl::create([
                    'bom_id'           => $bomPp2000->bom_id,
                    'barang_mentah_id' => $kartonPp2000Id,
                    'kebutuhan_qty'    => 100.0000,
                    'catatan_txt'      => 'Kardus Master Box Ping-Ping 2000',
                    'created_by'       => 'SYSTEM',
                ]);
            }
        });
    }
}
