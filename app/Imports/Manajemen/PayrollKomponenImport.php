<?php

namespace App\Imports\Manajemen;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/** Membaca file Excel jadi daftar baris (baris pertama = judul kolom). Pemrosesan isinya ada di PayrollService::importKomponen. */
class PayrollKomponenImport implements ToCollection, WithHeadingRow
{
    public Collection $baris;

    public function __construct()
    {
        $this->baris = collect();
    }

    public function collection(Collection $rows): void
    {
        $this->baris = $rows;
    }
}