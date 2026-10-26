<?php
// app/Exports/Payroll/MultiPayrollExport.php
namespace App\Exports\Payroll;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Satu file Excel, satu sheet per bagian (+ sheet "Dilewati").
 * FromArray ikut dipasang hanya supaya Excel::download() mengenalinya sebagai export;
 * isi sebenarnya diambil dari sheets() (WithMultipleSheets didahulukan).
 */
class MultiPayrollExport implements FromArray, WithMultipleSheets
{
    /** @param list<MultiPayrollSheet> $sheets */
    public function __construct(private array $sheets)
    {
    }

    public function sheets(): array
    {
        return $this->sheets;
    }

    public function array(): array
    {
        return [];
    }
}
