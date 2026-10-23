<?php

namespace App\Exports\Payroll;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Hasil payroll satu periode: satu baris per karyawan, plus baris TOTAL di bawahnya. */
class PayrollExport implements FromArray, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithStyles, WithStrictNullComparison
{
    private const STATUS = ['draft' => 'Draft', 'final' => 'Final', 'dibayar' => 'Dibayar'];

    /** Kolom rupiah yang dijumlahkan di baris TOTAL (indeks 0 = kolom A). */
    private const KOLOM_JUMLAH = [9, 11, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22];

    public function __construct(private Collection $rows)
    {
    }

    public function headings(): array
    {
        return [
            'No', 'ID Karyawan', 'ID Absen', 'Nama', 'Kelompok', 'Devisi', 'Masa Kerja (thn)', 'Kehadiran (hari)',
            'Gaji Harian', 'Gaji Pokok', 'Lembur I (jam)', 'Lembur I (Rp)', 'Lembur II (jam)', 'Lembur II (Rp)',
            'Tunj. Masa Kerja', 'Tunj. Jabatan', 'Insentif', 'Total Pendapatan',
            'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Kasbon / Lainnya', 'Total Potongan',
            'Gaji Bersih', 'Status', 'Catatan',
        ];
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->rows->values() as $i => $r) {
            $h    = $r['hasil'];
            $siap = $r['siap'];
            $rp   = fn ($key) => $siap ? (int) ($h[$key] ?? 0) : '';

            $data[] = [
                $i + 1,
                (string) ($r['nik'] ?? $r['id_absen']),
                (string) $r['id_absen'],
                $r['nama'],
                $r['kelompok'] ?? '',
                $r['jabatan'] ?? '',
                (int) ($h['masa_kerja_tahun'] ?? 0),
                (float) ($h['total_kehadiran'] ?? 0),
                $siap ? (int) round($r['input']['gaji_harian']) : '',
                $rp('gaji_pokok'),
                (float) ($h['jam_lembur_1'] ?? 0),
                $rp('honor_lembur_1'),
                (float) ($h['jam_lembur_2'] ?? 0),
                $rp('honor_lembur_2'),
                $rp('tunjangan_masa_kerja'),
                $rp('tunjangan_jabatan'),
                $rp('insentif'),
                $rp('total_pendapatan'),
                $rp('potongan_bpjs_kes'),
                $rp('potongan_bpjs_tk'),
                $rp('potongan_lainnya'),
                $rp('total_potongan'),
                $rp('gaji_bersih'),
                $siap ? (self::STATUS[$r['status']] ?? $r['status']) : 'Belum diisi',
                $r['catatan'] ?? '',
            ];
        }

        if ($data) {
            $total = array_fill(0, 25, '');
            $total[3] = 'TOTAL';
            foreach (self::KOLOM_JUMLAH as $k) {
                $total[$k] = array_sum(array_map(fn ($row) => is_numeric($row[$k]) ? $row[$k] : 0, $data));
            }
            $data[] = $total;
        }

        return $data;
    }

    public function columnFormats(): array
    {
        $rupiah = '#,##0';
        return [
            'B' => '@', 'C' => '@',
            'H' => 'General', 'K' => 'General', 'M' => 'General',
            'I' => $rupiah, 'J' => $rupiah, 'L' => $rupiah, 'N' => $rupiah, 'O' => $rupiah, 'P' => $rupiah,
            'Q' => $rupiah, 'R' => $rupiah, 'S' => $rupiah, 'T' => $rupiah, 'U' => $rupiah, 'V' => $rupiah, 'W' => $rupiah,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('E2');
        $akhir = $this->rows->count() + 2;   // baris TOTAL

        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']],
            ],
        ];
        if ($this->rows->isNotEmpty()) {
            $styles[$akhir] = ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']]];
        }
        return $styles;
    }
}