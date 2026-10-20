<?php

namespace App\Imports\Manajemen;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class FingerprintImport implements ToCollection, WithStartRow
{
    public array $rows = [];

    public function startRow(): int
    {
        return 3; // baris 1 kosong (filter), baris 2 header
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $r) {
            $idAbsen = trim((string) ($r[0] ?? ''));
            $waktu   = $r[3] ?? null;
            if ($idAbsen === '' || $waktu === null || $waktu === '') {
                continue;
            }

            try {
                $scan = is_numeric($waktu)
                    ? Carbon::instance(Date::excelToDateTimeObject($waktu))
                    : Carbon::parse(str_replace('/', '-', (string) $waktu));
            } catch (\Throwable $e) {
                continue; // lewati baris yang formatnya rusak
            }

            $this->rows[] = [
                'id_absen' => str_pad($idAbsen, 9, '0', STR_PAD_LEFT),
                'id_mesin' => (string) ($r[1] ?? ''),
                'nama'     => trim((string) ($r[2] ?? '')),
                'scan_at'  => $scan->format('Y-m-d H:i:s'),
            ];
        }
    }
}