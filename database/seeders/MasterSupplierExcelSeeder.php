<?php

namespace Database\Seeders;

use App\Models\MasterData\MstJenisSupplier;
use App\Models\MasterData\MstSupplier;
use Illuminate\Database\Seeder;

class MasterSupplierExcelSeeder extends Seeder
{
    /**
     * Run database seeds for complete PT Mirasa Excel Master Suppliers.
     */
    public function run(): void
    {
        // 1. Pastikan Kategori / Jenis Supplier Tersedia
        $jenisList = [
            ['jenis_supplier_cd' => 'RAW',     'jenis_supplier_nm' => 'Bahan Baku Singkong (Petani)'],
            ['jenis_supplier_cd' => 'BP',      'jenis_supplier_nm' => 'Bahan Penolong & Industri'],
            ['jenis_supplier_cd' => 'BUMBU',   'jenis_supplier_nm' => 'Bumbu, Perasa & Kimia Pangan'],
            ['jenis_supplier_cd' => 'KEMASAN', 'jenis_supplier_nm' => 'Kemasan, Plastik & Karton Box'],
            ['jenis_supplier_cd' => 'UMUM',    'jenis_supplier_nm' => 'Umum & Peralatan'],
        ];

        $jenisMap = [];
        foreach ($jenisList as $j) {
            $record = MstJenisSupplier::firstOrCreate(
                ['jenis_supplier_cd' => $j['jenis_supplier_cd']],
                ['jenis_supplier_nm' => $j['jenis_supplier_nm']]
            );
            $jenisMap[$j['jenis_supplier_cd']] = $record->jenis_supplier_id;
        }

        $rawId = $jenisMap['RAW'];
        $bpId  = $jenisMap['BP'];
        $bumbuId = $jenisMap['BUMBU'];
        $kemasanId = $jenisMap['KEMASAN'];

        // 2. Daftar 35 Petani Bahan Baku Singkong (SKG-...)
        $petaniSingkong = [
            ['supplier_cd' => 'SKG-UNT',  'supplier_nm' => 'UNTUNG'],
            ['supplier_cd' => 'SKG-UDN',  'supplier_nm' => 'UDIN'],
            ['supplier_cd' => 'SKG-LKM',  'supplier_nm' => 'LUKMAN'],
            ['supplier_cd' => 'SKG-KNTR', 'supplier_nm' => 'KANTRI'],
            ['supplier_cd' => 'SKG-PPL',  'supplier_nm' => 'PPL'],
            ['supplier_cd' => 'SKG-SRT',  'supplier_nm' => 'SURIPTO'],
            ['supplier_cd' => 'SKG-WRM',  'supplier_nm' => 'WARIMEN'],
            ['supplier_cd' => 'SKG-ALD',  'supplier_nm' => 'ALDI'],
            ['supplier_cd' => 'SKG-YTN',  'supplier_nm' => 'YITNO'],
            ['supplier_cd' => 'SKG-WYN',  'supplier_nm' => 'WAYAN'],
            ['supplier_cd' => 'SKG-HRM',  'supplier_nm' => 'HERMAWAN'],
            ['supplier_cd' => 'SKG-SGT',  'supplier_nm' => 'SAGET'],
            ['supplier_cd' => 'SKG-SKB',  'supplier_nm' => 'SUKABUMI'],
            ['supplier_cd' => 'SKG-BRO',  'supplier_nm' => 'BARYANTO'],
            ['supplier_cd' => 'SKG-ARS',  'supplier_nm' => 'ARIS'],
            ['supplier_cd' => 'SKG-GNJ',  'supplier_nm' => 'GANJAR'],
            ['supplier_cd' => 'SKG-EDI',  'supplier_nm' => 'EDI'],
            ['supplier_cd' => 'SKG-BGS',  'supplier_nm' => 'BAGUS'],
            ['supplier_cd' => 'SKG-ZNI',  'supplier_nm' => 'ZAINI'],
            ['supplier_cd' => 'SKG-ASP',  'supplier_nm' => 'ASEP'],
            ['supplier_cd' => 'SKG-HYT',  'supplier_nm' => 'HARYANTO'],
            ['supplier_cd' => 'SKG-AGG',  'supplier_nm' => 'ANGGRI'],
            ['supplier_cd' => 'SKG-RBN',  'supplier_nm' => 'ROBIN'],
            ['supplier_cd' => 'SKG-FTR',  'supplier_nm' => 'FITRI'],
            ['supplier_cd' => 'SKG-BRG',  'supplier_nm' => 'BORONGAN'],
            ['supplier_cd' => 'SKG-KMG',  'supplier_nm' => 'KOMANG'],
            ['supplier_cd' => 'SKG-YLI',  'supplier_nm' => 'YULI'],
            ['supplier_cd' => 'SKG-AMR',  'supplier_nm' => 'AMIRUL'],
            ['supplier_cd' => 'SKG-SHL',  'supplier_nm' => 'SAHLIN'],
            ['supplier_cd' => 'SKG-LDI',  'supplier_nm' => 'LEDI'],
            ['supplier_cd' => 'SKG-ARF',  'supplier_nm' => 'ARIFIN'],
            ['supplier_cd' => 'SKG-PRD',  'supplier_nm' => 'PARDI'],
            ['supplier_cd' => 'SKG-FAT',  'supplier_nm' => 'FATUR'],
            ['supplier_cd' => 'SKG-SRY',  'supplier_nm' => 'SURYO'],
            ['supplier_cd' => 'SKG-TRN',  'supplier_nm' => 'TRIONO'],
        ];

        foreach ($petaniSingkong as $p) {
            MstSupplier::updateOrCreate(
                ['supplier_cd' => $p['supplier_cd']],
                [
                    'supplier_nm'       => $p['supplier_nm'],
                    'jenis_supplier_id' => $rawId,
                    'kontak_no'         => '-',
                    'alamat_txt'        => 'Wonosobo / Mitra Petani Singkong',
                    'active_st'          => true,
                    'created_by'        => 1,
                    'updated_by'        => 1,
                ]
            );
        }

        // 3. Daftar 22 Vendor Bahan Penolong (SUP-...)
        $vendorBahanPenolong = [
            ['supplier_cd' => 'SUP-SMT',    'supplier_nm' => 'PT. SMART TBK',                   'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-BRC',    'supplier_nm' => 'PT. BARCO',                       'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-KARA',   'supplier_nm' => 'PT. KARACOCO NUCIFERA PRATAMA',   'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-BAGS',   'supplier_nm' => 'PT. BASKARA ASRI GHAS',           'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-FDX',    'supplier_nm' => 'PT. FOODEX',                      'jenis_id' => $bumbuId],
            ['supplier_cd' => 'SUP-INDAS',  'supplier_nm' => 'PT. INDO ASIA TIRTA MANUNGGAL',   'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-ANG',    'supplier_nm' => 'CV. ANUGRAH ABADI',               'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-PCO',    'supplier_nm' => 'PT. PESTINDO CENTRAL OPTIMA',     'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-PTNS',   'supplier_nm' => 'CV. PUTRA NUSA JAYA',             'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-PTNG',   'supplier_nm' => 'PT. PUTRA NAGA INDOTAMA',         'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-INDLAB', 'supplier_nm' => 'INDOLAB KARYA SEHATI',           'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-BRTC',   'supplier_nm' => 'PT. BRATACO',                     'jenis_id' => $bumbuId],
            ['supplier_cd' => 'SUP-ANKJS',  'supplier_nm' => 'PT. ANEKA JASUMA PLASTIK',        'jenis_id' => $kemasanId],
            ['supplier_cd' => 'SUP-TRIEV',  'supplier_nm' => 'CV. TRIEVA MAKMUR PLASTINDO',     'jenis_id' => $kemasanId],
            ['supplier_cd' => 'SUP-MTRA',   'supplier_nm' => 'PT. MITRA ADHIKARYA PLASINDO',     'jenis_id' => $kemasanId],
            ['supplier_cd' => 'SUP-DIPA',   'supplier_nm' => 'DIPA PUSPA',                      'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-SRW',    'supplier_nm' => 'PT. SRIWAHANA ADITYAKARTA, TBK',   'jenis_id' => $kemasanId],
            ['supplier_cd' => 'SUP-INPR',   'supplier_nm' => 'INDAH PRINTING',                  'jenis_id' => $kemasanId],
            ['supplier_cd' => 'SUP-ISLN',   'supplier_nm' => 'ISLAND SUN',                      'jenis_id' => $bumbuId],
            ['supplier_cd' => 'SUP-FILA',   'supplier_nm' => 'CV FILA DJAYA MANDIRI',           'jenis_id' => $bpId],
            ['supplier_cd' => 'SUP-TNSJY',  'supplier_nm' => 'TUNAS JAYA',                      'jenis_id' => $kemasanId],
            ['supplier_cd' => 'SUP-PURI',   'supplier_nm' => 'PT. PURINUSA EKA PERSADA',        'jenis_id' => $kemasanId],
        ];

        foreach ($vendorBahanPenolong as $v) {
            MstSupplier::updateOrCreate(
                ['supplier_cd' => $v['supplier_cd']],
                [
                    'supplier_nm'       => $v['supplier_nm'],
                    'jenis_supplier_id' => $v['jenis_id'],
                    'kontak_no'         => '-',
                    'alamat_txt'        => 'Vendor Industri Mitra PT Mirasa',
                    'active_st'          => true,
                    'created_by'        => 1,
                    'updated_by'        => 1,
                ]
            );
        }
    }
}
