<?php

namespace App\Services\Common;

use Illuminate\Support\Facades\DB;

class CodeGeneratorService
{
    /**
     * Kata-kata umum (legalitas/awalan) yang diabaikan saat membuat singkatan nama.
     */
    protected array $defaultStopwords = [
        'pt', 'cv', 'ud', 'tbk', 'toko', 'pd', 'fa', 'kelompok', 'tani',
        'distributor', 'agen', 'gudang', 'dan', 'yang', 'di', 'ke', 'dari',
        'lantai', 'area', 'pabrik', 'indonesia', 'jaya'
    ];

    /**
     * Generate kode urut otomatis dengan prefix tertentu secara aman.
     *
     * @param string $table Nama tabel target
     * @param string $column Nama kolom kode (misal: supplier_cd, barang_cd)
     * @param string $prefix Awalan kode (misal: SUP-, CUST-, BRG-)
     * @param int $padding Panjang digit angka (default: 4 -> 0001)
     * @return string
     */
    public function generate(string $table, string $column, string $prefix, int $padding = 4): string
    {
        $existingCodes = DB::table($table)
            ->where($column, 'LIKE', $prefix . '%')
            ->pluck($column);

        $maxNumber = 0;
        $prefixLen = strlen($prefix);

        foreach ($existingCodes as $code) {
            $suffix = substr($code, $prefixLen);
            if (preg_match('/^(\d+)/', $suffix, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        do {
            $maxNumber++;
            $generated = $prefix . str_pad((string) $maxNumber, $padding, '0', STR_PAD_LEFT);
            $exists = DB::table($table)->where($column, $generated)->exists();
        } while ($exists);

        return $generated;
    }

    /**
     * Membuat akronim / singkatan huruf depan dari nama entitas secara cerdas.
     * Contoh: "PT Sawit Murni Nusantara" -> "SMN"
     * Contoh: "Distributor Sumber Rezeki" -> "SR"
     */
    public function extractAcronym(string $name, array $customStopwords = []): string
    {
        $stopwords = array_merge($this->defaultStopwords, $customStopwords);

        // Bersihkan karakter khusus kecuali huruf dan angka
        $cleaned = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $name);
        $words = array_values(array_filter(explode(' ', trim($cleaned))));

        if (empty($words)) {
            return '';
        }

        // Filter kata-kata stopword umum (PT, CV, Toko, dll.)
        $filtered = [];
        foreach ($words as $w) {
            if (!in_array(strtolower($w), $stopwords)) {
                $filtered[] = $w;
            }
        }

        // Jika seluruh kata terfilter (misal namanya cuma "PT"), gunakan kata asli
        if (empty($filtered)) {
            $filtered = $words;
        }

        // Jika hanya 1 kata tersisa, ambil 3-4 huruf pertama
        if (count($filtered) === 1) {
            return strtoupper(substr($filtered[0], 0, 4));
        }

        // Ambil huruf depan dari setiap kata (maksimal 4 karakter)
        $acronym = '';
        foreach (array_slice($filtered, 0, 4) as $w) {
            if (!empty($w)) {
                $acronym .= strtoupper($w[0]);
            }
        }

        return $acronym;
    }

    /**
     * Generate kode untuk Master Supplier sesuai standar PT Mirasa Food:
     * - Petani Singkong (Jenis RAW/SKG): SKG-[INISIAL] (contoh: SKG-STR untuk Sutrisno)
     * - Vendor Bahan Penolong (Jenis BP/BUMBU/KEMASAN): SUP-[AKRONIM] (contoh: SUP-SMT untuk PT Smart)
     */
    public function generateSupplierCode(?string $name = null, ?string $jenisSupplierCd = null): string
    {
        $jenisClean = strtoupper(trim((string) $jenisSupplierCd));
        $nameClean = strtoupper(trim((string) $name));

        // 1. Supplier Bahan Baku / Petani Singkong (Prefix: SKG-)
        if (in_array($jenisClean, ['RAW', 'SKG', 'BB']) || (!empty($nameClean) && $this->isPetaniSingkong($nameClean))) {
            return $this->generatePetaniCode($nameClean);
        }

        // 2. Supplier Bahan Penolong / Vendor Perusahaan (Prefix: SUP-)
        return $this->generateVendorCode($nameClean);
    }

    protected function isPetaniSingkong(string $name): bool
    {
        if (str_contains($name, 'PT') || str_contains($name, 'CV') || str_contains($name, 'UD') || str_contains($name, 'TBK')) {
            return false;
        }
        return true;
    }

    protected function generatePetaniCode(string $name): string
    {
        if (empty($name)) {
            return $this->generate('mst_supplier', 'supplier_cd', 'SKG-', 3);
        }

        $words = array_values(array_filter(explode(' ', preg_replace('/[^A-Z0-9\s]/', '', $name))));
        $firstWord = $words[0] ?? $name;

        if (strlen($firstWord) <= 4) {
            $code = $firstWord;
        } else {
            $firstChar = substr($firstWord, 0, 1);
            $rest = substr($firstWord, 1);
            $consonants = preg_replace('/[AEIOU]/', '', $rest);
            $code = substr($firstChar . $consonants, 0, 4);
            if (strlen($code) < 3) {
                $code = substr($firstWord, 0, 3);
            }
        }

        $candidate = 'SKG-' . $code;
        $exists = DB::table('mst_supplier')->where('supplier_cd', $candidate)->exists();
        if (!$exists) {
            return $candidate;
        }

        return $this->generate('mst_supplier', 'supplier_cd', 'SKG-' . $code . '-', 2);
    }

    protected function generateVendorCode(string $name): string
    {
        if (empty($name)) {
            return $this->generate('mst_supplier', 'supplier_cd', 'SUP-', 4);
        }

        $acronym = $this->extractAcronym($name);
        if (empty($acronym)) {
            $words = array_values(array_filter(explode(' ', preg_replace('/[^A-Z0-9\s]/', '', $name))));
            $acronym = substr($words[0] ?? 'VND', 0, 4);
        }

        $candidate = 'SUP-' . $acronym;
        $exists = DB::table('mst_supplier')->where('supplier_cd', $candidate)->exists();
        if (!$exists) {
            return $candidate;
        }

        return $this->generate('mst_supplier', 'supplier_cd', 'SUP-' . $acronym . '-', 2);
    }

    /**
     * Generate kode untuk Master Customer:
     * - Jika ada $name: format "CUST-{AKRONIM}-01" (contoh: CUST-SR-01)
     * - Jika tanpa $name: format "CUST-0001"
     */
    public function generateCustomerCode(?string $name = null): string
    {
        if (!empty($name)) {
            $acronym = $this->extractAcronym($name);
            if (!empty($acronym)) {
                return $this->generate('mst_customer', 'customer_cd', 'CUST-' . $acronym . '-', 2);
            }
        }

        return $this->generate('mst_customer', 'customer_cd', 'CUST-', 4);
    }

    /**
     * Generate kode untuk Master Gudang:
     * - Jika ada $name: format "GDG-{AKRONIM}-01" (contoh: GDG-BB-01)
     * - Jika ada $tipeGudang: format "GDG-{TIPE}-01"
     * - Default: format "GDG-001"
     */
    public function generateGudangCode(?string $tipeGudang = null, ?string $name = null): string
    {
        if (!empty($name)) {
            $acronym = $this->extractAcronym($name, ['gudang']);
            if (!empty($acronym)) {
                return $this->generate('mst_gudang', 'gudang_cd', 'GDG-' . $acronym . '-', 2);
            }
        }

        if (!empty($tipeGudang)) {
            $prefix = 'GDG-' . strtoupper($tipeGudang) . '-';
            return $this->generate('mst_gudang', 'gudang_cd', $prefix, 2);
        }

        return $this->generate('mst_gudang', 'gudang_cd', 'GDG-', 3);
    }

    /**
     * Generate kode untuk Master Barang sesuai rumpun dan standar PT Mirasa Food:
     * - Bahan Penolong (BP):
     *   * Bumbu (nama mengandung BUMBU): B[inisial]00G-BP{max+1} (contoh: BJB00G-BP6)
     *   * Karton (nama mengandung KARTON): K[inisial]00G-BP{max+1} (contoh: KEB00G-BP10)
     *   * Lakban (nama mengandung LAKBAN): L[inisial]00G-BP{max+1} (contoh: LKB00G-BP4)
     *   * Plastik (nama mengandung PLASTIK): P[inisial]00G-BP{max+1} (contoh: PKT00G-BP5)
     *   * Roll (nama mengandung ROLL): R[inisial]00G-BP{max+1}
     *   * Minyak (nama mengandung MINYAK): M[inisial]00G-BP{max+1}
     *   * Sarung Tangan: ST[inisial]00G-BP{max+1}
     * - Bahan Baku (BB):
     *   * BB-[INISIAL][001] (contoh: BB-SK003, BB-UU007)
     * - WIP:
     *   * WIP-[AKRONIM] (contoh: WIP-KSS)
     * - Finish Good (FG):
     *   * FG-[AKRONIM][001] (contoh: FG-JB006)
     */
    public function generateBarangCode(?string $jenisCd = null, ?string $name = null): string
    {
        $jenisClean = strtoupper(trim((string) $jenisCd));
        $nameClean = strtoupper(trim((string) $name));

        // 1. Rumpun Bahan Penolong (BP, PACK, SUPP, atau jika nama barang mengindikasikan bahan penolong)
        if (in_array($jenisClean, ['BP', 'PACK', 'SUPP']) || (!empty($nameClean) && $this->isBahanPenolongName($nameClean))) {
            return $this->generateBahanPenolongCode($nameClean);
        }

        // 2. Rumpun Bahan Baku (BB atau RAW)
        if (in_array($jenisClean, ['BB', 'RAW']) || (!empty($nameClean) && $this->isBahanBakuName($nameClean))) {
            return $this->generateBahanBakuCode($nameClean);
        }

        // 3. Rumpun WIP (Work In Process)
        if ($jenisClean === 'WIP') {
            return $this->generateWipCode($nameClean);
        }

        // 4. Rumpun Finish Good (FG)
        if ($jenisClean === 'FG') {
            return $this->generateFgCode($nameClean);
        }

        // Fallback default jika nama kosong
        if (!empty($jenisClean)) {
            $prefix = 'BRG-' . $jenisClean . '-';
            return $this->generate('mst_barang', 'barang_cd', $prefix, 4);
        }

        return $this->generate('mst_barang', 'barang_cd', 'BRG-', 4);
    }

    protected function isBahanPenolongName(string $name): bool
    {
        $keywords = ['BUMBU', 'KARTON', 'LAKBAN', 'PLASTIK', 'ROLL', 'MINYAK', 'SARUNG TANGAN', 'RAFIA', 'PERENYAH'];
        foreach ($keywords as $k) {
            if (str_contains($name, $k)) return true;
        }
        return false;
    }

    protected function isBahanBakuName(string $name): bool
    {
        $keywords = ['SINGKONG', 'UBI', 'OPAK', 'PUYUR'];
        foreach ($keywords as $k) {
            if (str_contains($name, $k)) return true;
        }
        return false;
    }

    protected function generateBahanPenolongCode(string $name): string
    {
        $firstLetter = 'B';
        $prefixPattern = '%00G-BP%';

        if (str_contains($name, 'BUMBU')) {
            $firstLetter = 'B';
            $prefixPattern = 'B%00G-BP%';
        } elseif (str_contains($name, 'KARTON')) {
            $firstLetter = 'K';
            $prefixPattern = 'K%00G-BP%';
        } elseif (str_contains($name, 'LAKBAN')) {
            $firstLetter = 'L';
            $prefixPattern = 'L%00G-BP%';
        } elseif (str_contains($name, 'PLASTIK')) {
            $firstLetter = 'P';
            $prefixPattern = 'P%00G-BP%';
        } elseif (str_contains($name, 'ROLL')) {
            $firstLetter = 'R';
            $prefixPattern = 'R%00G-BP%';
        } elseif (str_contains($name, 'MINYAK')) {
            $firstLetter = 'M';
            $prefixPattern = 'M%00G-BP%';
        } elseif (str_contains($name, 'SARUNG TANGAN')) {
            $firstLetter = 'ST';
            $prefixPattern = 'ST%00G-BP%';
        } elseif (str_contains($name, 'RAFIA')) {
            $firstLetter = 'TR';
            $prefixPattern = 'TR%00G-BP%';
        } elseif (str_contains($name, 'PERENYAH')) {
            $firstLetter = 'PR';
            $prefixPattern = 'PR%00G-BP%';
        }

        // Buat 3 huruf depan inisial (contoh: BUMBU JAGUNG BAKAR -> BJB)
        $words = array_values(array_filter(explode(' ', preg_replace('/[^A-Z0-9\s]/', '', $name))));
        $initial = '';
        if (count($words) >= 3) {
            $initial = substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
        } elseif (count($words) === 2) {
            $initial = substr($words[0], 0, 1) . substr($words[1], 0, 2);
        } elseif (count($words) === 1 && !empty($words[0])) {
            $initial = substr($words[0], 0, 3);
        } else {
            $initial = $firstLetter . 'XX';
        }

        // Pastikan huruf pertama cocok dengan rumpun jika bukan multi-huruf (seperti ST)
        if (strlen($firstLetter) === 1 && !str_starts_with($initial, $firstLetter)) {
            $initial = $firstLetter . substr($initial, 1);
        }

        // Cari nomor urut BP terakhir untuk rumpun ini
        $existingCodes = DB::table('mst_barang')
            ->where('barang_cd', 'LIKE', $prefixPattern)
            ->pluck('barang_cd');

        $maxNumber = 0;
        foreach ($existingCodes as $cd) {
            if (preg_match('/-BP(\d+)$/i', $cd, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        do {
            $maxNumber++;
            $candidate = $initial . '00G-BP' . $maxNumber;
            $exists = DB::table('mst_barang')->where('barang_cd', $candidate)->exists();
        } while ($exists);

        return $candidate;
    }

    protected function generateBahanBakuCode(string $name): string
    {
        $sub = 'SK';
        if (str_contains($name, 'SINGKONG')) {
            $sub = 'SK';
        } elseif (str_contains($name, 'OPAK')) {
            $sub = 'OP';
        } elseif (str_contains($name, 'PUYUR')) {
            $sub = 'PY';
        } elseif (str_contains($name, 'UBI UNGU')) {
            $sub = 'UU';
        } elseif (str_contains($name, 'UBI')) {
            $sub = 'UB';
        } else {
            $words = array_values(array_filter(explode(' ', preg_replace('/[^A-Z0-9\s]/', '', $name))));
            $sub = count($words) >= 2 ? substr($words[0], 0, 1) . substr($words[1], 0, 1) : (substr($name, 0, 2) ?: 'BB');
        }

        $prefix = 'BB-' . $sub;
        return $this->generate('mst_barang', 'barang_cd', $prefix, 3);
    }

    protected function generateWipCode(string $name): string
    {
        $words = array_values(array_filter(explode(' ', preg_replace('/[^A-Z0-9\s]/', '', $name))));
        $acronym = '';
        if (count($words) >= 3) {
            $acronym = substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
        } elseif (count($words) === 2) {
            $acronym = substr($words[0], 0, 1) . substr($words[1], 0, 2);
        } elseif (count($words) === 1 && !empty($words[0])) {
            $acronym = substr($words[0], 0, 3);
        } else {
            $acronym = 'WIP';
        }

        $candidate = 'WIP-' . $acronym;
        $exists = DB::table('mst_barang')->where('barang_cd', $candidate)->exists();
        if (!$exists) {
            return $candidate;
        }

        return $this->generate('mst_barang', 'barang_cd', 'WIP-' . $acronym . '-', 2);
    }

    protected function generateFgCode(string $name): string
    {
        $words = array_values(array_filter(explode(' ', preg_replace('/[^A-Z0-9\s]/', '', $name))));
        $acronym = '';
        if (count($words) >= 2) {
            $acronym = substr($words[0], 0, 1) . substr($words[1], 0, 1);
        } elseif (count($words) === 1 && !empty($words[0])) {
            $acronym = substr($words[0], 0, 2);
        } else {
            $acronym = 'FG';
        }

        $prefix = 'FG-' . $acronym;
        return $this->generate('mst_barang', 'barang_cd', $prefix, 3);
    }

    /**
     * Generate Nomor Purchase Order (format: PO-YYYYMM-0001)
     */
    public function generatePoNo(): string
    {
        $prefix = 'PO-' . date('Ym') . '-';
        return $this->generate('dat_po_hdr', 'po_no', $prefix, 4);
    }

    /**
     * Generate Nomor Penerimaan Barang (Good Receipt) (format: GR-YYYYMM-0001)
     */
    public function generateTerimaNo(): string
    {
        $prefix = 'GR-' . date('Ym') . '-';
        return $this->generate('dat_terima_hdr', 'terima_no', $prefix, 4);
    }

    /**
     * Mengekstrak inisial akronim barang sesuai standar Excel PT Mirasa Food:
     * Contoh:
     * - "MINYAK SAWIT"  -> "MS"
     * - "MINYAK KELAPA" -> "MK"
     * - "PERENYAH"      -> "PR"
     * - "PLASTIK HD"    -> "HD"
     * - "LAKBAN KECIL"  -> "LK"
     * - "LAKBAN SEDANG" -> "LS"
     * - "SINGKONG"      -> "SK"
     * - "UBI UNGU"      -> "UU"
     */
    public function extractBarangAcronym(?string $name = null, ?string $code = null): string
    {
        $nameUpper = strtoupper(trim((string) $name));
        $codeUpper = strtoupper(trim((string) $code));

        // 1. Kamus Khusus untuk Komoditas Utama PT Mirasa
        $dictionary = [
            'MINYAK SAWIT'  => 'MS',
            'MINYAK KELAPA' => 'MK',
            'PERENYAH'      => 'PR',
            'PLASTIK HD'    => 'HD',
            'LAKBAN KECIL'  => 'LK',
            'LAKBAN SEDANG' => 'LS',
            'SINGKONG'      => 'SK',
            'UBI UNGU'      => 'UU',
        ];

        foreach ($dictionary as $key => $acronym) {
            if (str_contains($nameUpper, $key)) {
                return $acronym;
            }
        }

        // 2. Jika kode barang Mirasa berformat seperti "MSW00G-BP2", ambil 2 huruf depan jika bukan "BRG"
        if (!empty($codeUpper) && !str_starts_with($codeUpper, 'BRG-')) {
            $codeClean = preg_replace('/[^A-Z]/', '', $codeUpper);
            if (strlen($codeClean) >= 2) {
                return substr($codeClean, 0, 2);
            }
        }

        // 3. Jika nama memiliki 2 kata atau lebih, ambil huruf pertama dari 2 kata pertama
        if (!empty($nameUpper)) {
            $words = array_values(array_filter(explode(' ', preg_replace('/[^A-Z0-9\s]/', '', $nameUpper))));
            if (count($words) >= 2) {
                return substr($words[0], 0, 1) . substr($words[1], 0, 1);
            } elseif (count($words) === 1) {
                $w = $words[0];
                if (strlen($w) >= 2) {
                    return substr($w, 0, 2);
                }
                return $w;
            }
        }

        // 4. Fallback dari kode barang
        if (!empty($codeUpper)) {
            $clean = preg_replace('/[^A-Z0-9]/', '', str_replace('BRG-', '', $codeUpper));
            return substr($clean, 0, 3) ?: 'BRG';
        }

        return 'BRG';
    }

    /**
     * Generate Nomor Batch Otomatis sesuai standar PT Mirasa Food:
     * Format: [INISIAL_BARANG]-[DDMMYYYY]-[NO_URUT]
     * Contoh: MS-25082026-01
     *
     * Fitur Utama:
     * 1. Nomor urut (-01, -02, dst) mandiri per barang dan per tanggal fisik.
     * 2. Beda barang = nomor urut me-reset kembali ke 01.
     * 3. Ganti tanggal = nomor urut me-reset kembali ke 01.
     */
    public function generateBatchNo(?string $prefixKey = null, ?string $date = null, ?string $barangNm = null): string
    {
        $acronym = $this->extractBarangAcronym($barangNm, $prefixKey);
        $dateFormatted = date('dmY', strtotime($date ?? date('Y-m-d')));
        $prefix = $acronym . '-' . $dateFormatted . '-';

        // Cari nomor urut terakhir khusus untuk prefix barang & tanggal ini
        $existingCodesDtl = DB::table('dat_terima_dtl')
            ->where('batch_no', 'LIKE', $prefix . '%')
            ->pluck('batch_no')
            ->toArray();

        $existingCodesStok = DB::table('dat_stok_batch')
            ->where('batch_no', 'LIKE', $prefix . '%')
            ->pluck('batch_no')
            ->toArray();

        $existingCodes = array_unique(array_merge($existingCodesDtl, $existingCodesStok));

        $maxNumber = 0;
        $prefixLen = strlen($prefix);

        foreach ($existingCodes as $code) {
            $suffix = substr($code, $prefixLen);
            if (preg_match('/^(\d+)/', $suffix, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        do {
            $maxNumber++;
            $generated = $prefix . str_pad((string) $maxNumber, 2, '0', STR_PAD_LEFT);
            $existsInDtl = DB::table('dat_terima_dtl')->where('batch_no', $generated)->exists();
            $existsInStok = DB::table('dat_stok_batch')->where('batch_no', $generated)->exists();
        } while ($existsInDtl || $existsInStok);

        return $generated;
    }

    /**
     * Generate Nomor Pengeluaran / Pemakaian Barang (format: OUT-YYYYMM-0001)
     */
    public function generatePakaiNo(): string
    {
        $prefix = 'OUT-' . date('Ym') . '-';
        return $this->generate('dat_pakai_hdr', 'pakai_no', $prefix, 4);
    }
}
