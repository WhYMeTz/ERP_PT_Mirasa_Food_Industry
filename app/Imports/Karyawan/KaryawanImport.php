<?php

namespace App\Imports\Karyawan;

use App\Models\MasterData\MstKaryawan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class KaryawanImport implements ToModel, WithHeadingRow
{
    /**
     * @param int $headingRow Nomor baris tempat judul kolom berada.
     *                        1 = file hasil Export sistem.
     *                        Untuk Excel pabrik (judul di bawah baris judul laporan), isi sesuai barisnya.
     */
    public function __construct(protected int $headingRow = 1)
    {
    }

    public function headingRow(): int
    {
        return $this->headingRow;
    }

    public function model(array $row): ?Model
    {
        $nik  = $this->get($row, ['id_karyawan', 'nik']);
        $nama = $this->get($row, ['nama_karyawan', 'nama']);

        // Lewati baris kosong / tanpa NIK atau nama
        if (empty($nik) || empty($nama)) {
            return null;
        }

        $bagian = $this->upper($this->get($row, ['bagian', 'bagian_divisi', 'departemen']));

        $karyawan = MstKaryawan::firstOrNew(['nik' => trim((string) $nik)]);

        $karyawan->fill([
            'id_absensi'    => $this->text($this->get($row, ['id_absensi', 'id_absen'])),
            'karyawan_nm'   => trim((string) $nama),
            'kelompok'      => $this->upper($this->get($row, ['kelompok'])),
            'shift'         => $this->normalizeShift($this->get($row, ['shift'])),
            'status_kerja'  => $this->normalizeStatus($this->get($row, ['status_karyawan', 'status'])),
            'departemen_cd' => $bagian,
            'jabatan_nm'    => $this->text($this->get($row, ['jabatan', 'jabatan_kerja'])) ?? ($bagian ?? '-'),
            'tgl_masuk'     => $this->parseDate($this->get($row, ['tanggal_masuk', 'tanggal_masuk_kerja', 'tgl_masuk'])),
            'telepon_no'    => $this->text($this->get($row, ['no_hp', 'kontak_no_telp', 'telepon'])),
            'email'         => $this->text($this->get($row, ['email'])),
            'bank_name'     => $this->text($this->get($row, ['nama_bank', 'bank'])) ?? 'BCA',
            'no_rekening'   => $this->text($this->get($row, ['no_rekening'])),
            'alamat_txt'    => $this->text($this->get($row, ['alamat'])),
        ]);

        if (!$karyawan->exists) {
            $karyawan->active_st  = true;
            $karyawan->deleted_st = false;
        }

        $karyawan->save();

        // Sudah disimpan sendiri (update jika NIK sudah ada), jadi tidak perlu dikembalikan
        return null;
    }

    /**
     * Ambil nilai dari beberapa kemungkinan nama kolom (tidak peka huruf besar/kecil,
     * garis bawah, atau tanda baca).
     */
    private function get(array $row, array $names): mixed
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[preg_replace('/[^a-z0-9]/', '', strtolower((string) $key))] = $value;
        }

        foreach ($names as $name) {
            $key = preg_replace('/[^a-z0-9]/', '', strtolower($name));
            if (isset($normalized[$key]) && $normalized[$key] !== '') {
                return $normalized[$key];
            }
        }

        return null;
    }

    private function text(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    private function upper(mixed $value): ?string
    {
        $text = $this->text($value);

        return $text === null ? null : mb_substr(strtoupper($text), 0, 50);
    }

    private function normalizeShift(mixed $value): ?string
    {
        $shift = $this->upper($value);

        return in_array($shift, ['A', 'B'], true) ? 'SHIFT ' . $shift : $shift;
    }

    private function normalizeStatus(mixed $value): string
    {
        $status = $this->text($value);

        return $status === null ? 'Tetap' : ucwords(strtolower($status));
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::createFromFormat('d/m/Y', trim((string) $value))->format('Y-m-d');
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }
}