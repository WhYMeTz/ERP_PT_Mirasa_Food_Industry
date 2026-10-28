<?php
// app/Exports/Absensi/AbsensiExport.php
namespace App\Exports\Absensi;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Satu file Excel: sheet "Rekap per Karyawan" dan sheet "Detail". */
class AbsensiExport implements FromArray, WithMultipleSheets
{
    /** @param list<AbsensiSheet> $sheets */
    public function __construct(private array $sheets)
    {
    }

    public function sheets(): array
    {
        return $this->sheets;
    }

    // Hanya agar Excel::download() mengenali kelas ini; isi diambil dari sheets().
    public function array(): array
    {
        return [];
    }
}
