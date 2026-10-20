<?php

namespace App\Imports\Manajemen;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class JadwalImport implements ToCollection, WithStartRow
{
    public array $rows = [];    // baris valid
    public array $errors = [];  // pesan baris yang dilewati

    public function __construct(private array $kodeValid) {}

    public function startRow(): int
    {
        return 2; // baris 1 = header
    }

    public function collection(Collection $collection): void
    {
        $unik = [];

        foreach ($collection->values() as $n => $r) {
            $baris = $n + $this->startRow();

            if ($r->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->isEmpty()) {
                continue; // baris kosong
            }

            $tgl   = $this->parseTanggal($r[0] ?? null);
            $id    = trim((string) ($r[1] ?? ''));
            $shift = strtoupper(trim((string) ($r[4] ?? '')));

            if (!$tgl)                                { $this->errors[] = "Baris $baris: tanggal tidak valid."; continue; }
            if ($id === '')                           { $this->errors[] = "Baris $baris: ID_Absen kosong."; continue; }
            if (!in_array($shift, $this->kodeValid))  { $this->errors[] = "Baris $baris: kode shift '$shift' tidak dikenal."; continue; }

            $ist = $r[5] ?? null;
            if ($ist === null || trim((string) $ist) === '') {
                $ist = null;
            } else {
                $ist = str_replace(',', '.', trim((string) $ist));
                if (!is_numeric($ist) || !in_array((float) $ist, [0.0, 1.0, 1.5], true)) {
                    $this->errors[] = "Baris $baris: istirahat harus 0, 1, atau 1,5.";
                    continue;
                }
                $ist = (float) $ist;
            }

            $id  = str_pad($id, 9, '0', STR_PAD_LEFT);
            $key = $tgl->toDateString() . '|' . $id;

            // baris terakhir untuk kunci yang sama yang dipakai (hindari duplikat dalam satu file)
            $unik[$key] = [
                'tanggal'        => $tgl->toDateString(),
                'id_absen'       => $id,
                'nama'           => trim((string) ($r[2] ?? '')) ?: null,
                'kelompok_kerja' => trim((string) ($r[3] ?? '')) ?: null,
                'kode_shift'     => $shift,
                'istirahat_jam'  => $ist,
            ];
        }

        $this->rows = array_values($unik);
    }

    private function parseTanggal($v): ?Carbon
    {
        if ($v === null || trim((string) $v) === '') {
            return null;
        }

        try {
            if (is_numeric($v)) {
                return Carbon::instance(Date::excelToDateTimeObject($v))->startOfDay();
            }
            $s = trim((string) $v);
            try {
                return Carbon::createFromFormat('d/m/Y', $s)->startOfDay();
            } catch (\Throwable) {
                return Carbon::parse($s)->startOfDay();
            }
        } catch (\Throwable) {
            return null;
        }
    }
}