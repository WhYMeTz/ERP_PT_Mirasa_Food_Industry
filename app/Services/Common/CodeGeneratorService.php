<?php

namespace App\Services\Common;

use Illuminate\Support\Facades\DB;

class CodeGeneratorService
{
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
     * Generate kode untuk Master Supplier (format: SUP-0001)
     */
    public function generateSupplierCode(): string
    {
        return $this->generate('mst_supplier', 'supplier_cd', 'SUP-', 4);
    }

    /**
     * Generate kode untuk Master Customer (format: CUST-0001)
     */
    public function generateCustomerCode(): string
    {
        return $this->generate('mst_customer', 'customer_cd', 'CUST-', 4);
    }

    /**
     * Generate kode untuk Master Gudang (format: GDG-001 atau GDG-RAW-01)
     */
    public function generateGudangCode(?string $tipeGudang = null): string
    {
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
