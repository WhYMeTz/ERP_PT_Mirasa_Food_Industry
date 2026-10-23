<?php

namespace App\Exports\Karyawan;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KaryawanTemplateExport implements Export, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new KaryawanTemplateSheet(),
            new KaryawanPetunjukSheet(),
        ];
    }
}