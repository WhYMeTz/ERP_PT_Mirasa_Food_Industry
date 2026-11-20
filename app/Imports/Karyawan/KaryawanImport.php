<?php

namespace App\Imports\Karyawan;

use App\Models\MasterData\MstKaryawan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Import data karyawan. Karyawan dicocokkan lewat ID Karyawan (NIK).
 *
 *  - Karyawan yang sudah ada : HANYA kolom yang ada di file dan terisi yang diubah.
 *                              Kolom yang tidak ada di file, atau selnya kosong, tidak disentuh.
 *  - Karyawan baru           : wajib Nama, Departemen, dan Divisi / Bagian.
 *
 * Jadi file boleh hanya berisi "ID Karyawan" + satu-dua kolom yang ingin dilengkapi (mis. No. HP atau NPWP/NIK).
 */
class KaryawanImport implements ToModel, WithHeadingRow
{
    /** Baris yang ditolak: ['nik' => ..., 'alasan' => ...] */
    public array $ditolak = [];

    public int $ditambah = 0;
    public int $diperbarui = 0;
    public int $tanpaPerubahan = 0;

    /** Penulisan lain => kode divisi baku */
    private const ALIAS_DIVISI = [
        'POT RENDAM' => 'POT.RENDAM', 'POT. RENDAM' => 'POT.RENDAM', 'POTONG RENDAM' => 'POT.RENDAM',
        'QUALITY CONTROL' => 'QUALITYCONTROL', 'QC' => 'QUALITYCONTROL',
    ];

    /**
     * @param int  $headingRow  Nomor baris judul kolom. 1 = file hasil Export / Template sistem.
     * @param bool $bolehPajak  Boleh mengubah NPWP/NIK dan Status PTKP (hanya yang berhak mengedit karyawan).
     */
    public function __construct(protected int $headingRow = 1, protected bool $bolehPajak = true)
    {
    }

    public function headingRow(): int
    {
        return $this->headingRow;
    }

    public function model(array $row): ?Model
    {
        $nik = $this->text($this->get($row, ['id_karyawan', 'nik']));

        // Lewati baris kosong
        if ($nik === null) {
            return null;
        }

        $baca = $this->baca($row, $this->bolehPajak);

        if ($baca['masalah']) {
            $this->ditolak[] = ['nik' => $nik, 'alasan' => implode('; ', $baca['masalah'])];
            return null;
        }

        $data     = $baca['data'];
        $karyawan = MstKaryawan::firstOrNew(['nik' => $nik]);

        if (! $karyawan->exists) {
            $kurang = [];
            foreach (['karyawan_nm' => 'Nama', 'departemen_cd' => 'Departemen', 'jabatan_nm' => 'Divisi / Bagian'] as $kolom => $label) {
                if (empty($data[$kolom])) {
                    $kurang[] = $label;
                }
            }
            if ($kurang) {
                $this->ditolak[] = ['nik' => $nik, 'alasan' => 'ID tidak ditemukan di data karyawan. Untuk karyawan baru wajib diisi: ' . implode(', ', $kurang) . '.'];
                return null;
            }

            $data += ['status_kerja' => 'Tetap', 'bank_name' => 'BCA'];
            $karyawan->active_st  = true;
            $karyawan->deleted_st = false;
        }

        $karyawan->fill($data);

        if (! $karyawan->exists) {
            $karyawan->save();
            $this->ditambah++;
        } elseif ($karyawan->isDirty()) {
            $karyawan->save();
            $this->diperbarui++;
        } else {
            $this->tanpaPerubahan++;
        }

        return null;
    }

    /**
     * Baca satu baris: hanya kolom yang ada dan terisi yang masuk ke 'data'.
     *
     * @return array{data: array<string,mixed>, masalah: array<int,string>}
     */
    public function baca(array $row, bool $bolehPajak = true): array
    {
        $data    = [];
        $masalah = [];
        $isi     = function (string $kolom, mixed $nilai) use (&$data) {
            if ($nilai !== null) {
                $data[$kolom] = $nilai;
            }
        };

        $isi('id_absensi', $this->text($this->get($row, ['id_absensi', 'id_absen'])));
        $isi('karyawan_nm', $this->text($this->get($row, ['nama_karyawan', 'nama'])));
        $isi('kelompok', $this->upper($this->get($row, ['kelompok'])));
        $isi('shift', $this->normalizeShift($this->get($row, ['shift'])));
        $isi('departemen_cd', $this->upper($this->get($row, ['departemen', 'bagian'])));
        $isi('telepon_no', $this->text($this->get($row, ['no_hp', 'kontak_no_telp', 'telepon'])));
        $isi('email', $this->text($this->get($row, ['email'])));
        $isi('bank_name', $this->text($this->get($row, ['nama_bank', 'bank'])));
        $isi('no_rekening', $this->text($this->get($row, ['no_rekening'])));
        $isi('alamat_txt', $this->text($this->get($row, ['alamat'])));

        $status = $this->text($this->get($row, ['status_karyawan', 'status']));
        if ($status !== null) {
            $status = ucwords(strtolower($status));
            in_array($status, ['Tetap', 'Kontrak', 'Harian', 'Borong'], true)
                ? $data['status_kerja'] = $status
                : $masalah[] = 'Status Karyawan "' . $status . '" tidak dikenal (Tetap, Kontrak, Harian, Borong).';
        }

        $divisiMentah = $this->text($this->get($row, ['divisi_bagian', 'divisi', 'jabatan', 'jabatan_kerja', 'bagian_divisi']));
        if ($divisiMentah !== null) {
            $divisi = $this->normalizeDivisi($divisiMentah);
            $divisi === null
                ? $masalah[] = 'Divisi / Bagian "' . $divisiMentah . '" tidak ada di daftar.'
                : $data['jabatan_nm'] = $divisi;
        }

        $tglMentah = $this->get($row, ['tanggal_masuk', 'tanggal_masuk_kerja', 'tgl_masuk']);
        if ($tglMentah !== null) {
            $tgl = $this->parseDate($tglMentah);
            $tgl === null
                ? $masalah[] = 'Tanggal masuk "' . $tglMentah . '" tidak terbaca.'
                : $data['tgl_masuk'] = $tgl;
        }

        if ($bolehPajak) {
            $npwpMentah = $this->get($row, ['npwp_nik', 'npwp', 'nik_npwp', 'nomor_npwp']);
            if ($npwpMentah !== null) {
                $npwp = $this->angka($npwpMentah);
                strlen($npwp) === 16
                    ? $data['npwp_nik'] = $npwp
                    : $masalah[] = 'NPWP/NIK harus 16 digit angka (terbaca "' . $npwp . '").';
            }

            $ptkpMentah = $this->text($this->get($row, ['status_ptkp', 'ptkp']));
            if ($ptkpMentah !== null) {
                $ptkp = $this->normalizePtkp($ptkpMentah);
                $ptkp === null
                    ? $masalah[] = 'Status PTKP "' . $ptkpMentah . '" tidak dikenal.'
                    : $data['status_ptkp'] = $ptkp;
            }
        }

        return ['data' => $data, 'masalah' => $masalah];
    }

    /** "tk0", "TK-0", "TK 0", "K / 1" => "TK/0", "K/1"; null bila tidak dikenal. */
    private function normalizePtkp(string $teks): ?string
    {
        if (! preg_match('/^(TK|K|HB)\s*[\/\-]?\s*([0-3])$/i', trim($teks), $m)) {
            return null;
        }
        $nilai = strtoupper($m[1]) . '/' . $m[2];

        return in_array($nilai, MstKaryawan::PTKP, true) ? $nilai : null;
    }

    /** Hanya angka. Sel bertipe angka di Excel (mis. 3.3E+15) diubah tanpa notasi ilmiah. */
    private function angka(mixed $nilai): string
    {
        if (is_float($nilai) || is_int($nilai)) {
            return sprintf('%.0f', $nilai);
        }

        return preg_replace('/\D/', '', (string) $nilai);
    }

    /** Kode divisi baku, atau null bila kosong / tidak dikenal. */
    private function normalizeDivisi(mixed $value): ?string
    {
        $teks = $this->upper($value);
        if ($teks === null) {
            return null;
        }
        $teks = self::ALIAS_DIVISI[$teks] ?? $teks;

        return in_array($teks, MstKaryawan::DIVISI, true) ? $teks : null;
    }

    /**
     * Ambil nilai dari beberapa kemungkinan nama kolom (tidak peka huruf besar/kecil,
     * garis bawah, atau tanda baca). Null bila kolomnya tidak ada atau selnya kosong.
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

        $teks = trim((string) $value);

        return $teks === '' ? null : $teks;
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
