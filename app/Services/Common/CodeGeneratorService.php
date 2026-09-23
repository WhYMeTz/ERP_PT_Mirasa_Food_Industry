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
     * Generate kode untuk Master Supplier:
     * - Jika ada $name: format "SUP-{AKRONIM}-01" (contoh: SUP-SMN-01)
     * - Jika tanpa $name: format "SUP-0001"
     */
    public function generateSupplierCode(?string $name = null): string
    {
        if (!empty($name)) {
            $acronym = $this->extractAcronym($name);
            if (!empty($acronym)) {
                return $this->generate('mst_supplier', 'supplier_cd', 'SUP-' . $acronym . '-', 2);
            }
        }

        return $this->generate('mst_supplier', 'supplier_cd', 'SUP-', 4);
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
     * Generate kode untuk Master Barang (format: BRG-RAW-0001 atau BRG-0001)
     */
    public function generateBarangCode(?string $jenisCd = null): string
    {
        if (!empty($jenisCd)) {
            $prefix = 'BRG-' . strtoupper(trim($jenisCd)) . '-';
            return $this->generate('mst_barang', 'barang_cd', $prefix, 4);
        }

        return $this->generate('mst_barang', 'barang_cd', 'BRG-', 4);
    }
}
