<?php

namespace App\Services\Manajemen;

use App\Models\Manajemen\TrxPayroll;
use App\Models\MasterData\MstKaryawan;
use Illuminate\Support\Collection;

/**
 * Mengambil data payroll yang sudah Final/Dibayar dan mengelompokkannya per bagian
 * untuk file Multi Payroll BCA. Penentu bagian = isian Jabatan di data karyawan.
 */
class MultiPayrollService
{
    /**
     * Ubah isian Divisi/Jabatan menjadi kunci bagian di config, atau null bila tidak dikenali.
     * Selain kunci bagian itu sendiri, 'alias' di config ikut dikenali (mis. DRIVER masuk UMUM).
     */
    public static function kunciBagian(?string $jabatan, array $bagian): ?string
    {
        $norm = fn ($t) => strtoupper(trim(preg_replace('/\s+/', ' ', (string) $t)));
        $j = $norm($jabatan);

        if ($j === '') {
            return null;
        }
        if (array_key_exists($j, $bagian)) {
            return $j;
        }
        foreach ($bagian as $kunci => $cfg) {
            if (in_array($j, array_map($norm, $cfg['alias'] ?? []), true)) {
                return $kunci;
            }
        }

        return null;
    }

    /**
     * Nilai satu karyawan: bagian, rekening, nama, jumlah, dan alasan dilewati (null = layak masuk file).
     *
     * @param array|null $kar  ['nama'=>, 'jabatan'=>, 'bank'=>, 'rekening'=>] dari data karyawan, null bila tidak ketemu
     */
    public static function nilai(string $nama, ?string $jabatanPayroll, ?array $kar, int $bersih, array $bagian): array
    {
        $jabatan = $kar['jabatan'] ?? $jabatanPayroll;
        $kunci   = self::kunciBagian($jabatan, $bagian);
        $rek     = preg_replace('/\D/', '', (string) ($kar['rekening'] ?? ''));
        $bank    = strtoupper(trim((string) ($kar['bank'] ?? '')));

        $alasan = null;
        if ($kar === null)                    $alasan = 'Data karyawan tidak ditemukan';
        elseif ($kunci === null)              $alasan = 'Bagian tidak dikenali (' . ($jabatan ?: 'kosong') . ')';
        elseif ($rek === '')                  $alasan = 'Tidak ada nomor rekening';
        elseif ($bank !== '' && $bank !== 'BCA') $alasan = 'Bank bukan BCA (' . $bank . ')';
        elseif ($bersih <= 0)                 $alasan = 'Gaji bersih nol atau minus';

        return [
            'bagian'   => $kunci,
            'nama'     => ($kar['nama'] ?? '') !== '' ? $kar['nama'] : $nama,
            'rekening' => $rek,
            'jumlah'   => $bersih,
            'alasan'   => $alasan,
        ];
    }

    /** Periode siap bila ada payroll dan tidak ada yang masih draft. */
    public function periodeSiap(string $periode): bool
    {
        $status = TrxPayroll::where('periode', $periode)->distinct()->pluck('status');

        return $status->isNotEmpty() && ! $status->contains('draft');
    }

    /**
     * Semua karyawan periode itu, sudah dinilai. Urut nama.
     *
     * @return Collection<int, array> tiap item: nik, id_absen, nama, bagian, rekening, jumlah, alasan
     */
    public function semua(string $periode): Collection
    {
        $bagian = config('multipayroll.bagian', []);
        $rows   = TrxPayroll::where('periode', $periode)->whereIn('status', ['final', 'dibayar'])->get();

        $kar = MstKaryawan::whereIn('nik', $rows->pluck('nik')->filter()->unique()->all())->get()
            ->keyBy(fn ($k) => (string) $k->nik);

        return $rows->map(function (TrxPayroll $p) use ($kar, $bagian) {
            $k = $kar->get((string) $p->nik);
            $data = self::nilai(
                (string) $p->nama,
                $p->jabatan,
                $k ? ['nama' => $k->karyawan_nm, 'jabatan' => $k->jabatan_nm, 'bank' => $k->bank_name, 'rekening' => $k->no_rekening] : null,
                (int) round($p->hasil['gaji_bersih'] ?? 0),
                $bagian
            );

            return ['nik' => $p->nik, 'id_absen' => $p->id_absen] + $data;
        })->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** Ringkasan per bagian untuk satu periode: jumlah layak, total, dan jumlah dilewati. */
    public function ringkasan(string $periode): array
    {
        $semua = $this->semua($periode);
        $hasil = [];

        foreach (array_keys(config('multipayroll.bagian', [])) as $b) {
            $item = $semua->where('bagian', $b);
            $ok   = $item->whereNull('alasan');
            $hasil[$b] = [
                'layak'    => $ok->count(),
                'total'    => (int) $ok->sum('jumlah'),
                'dilewati' => $item->whereNotNull('alasan')->count(),
            ];
        }

        $hasil['_tanpa_bagian'] = $semua->whereNull('bagian')->count();

        return $hasil;
    }

    /** @return array{layak:Collection, dilewati:Collection, total:int} untuk satu bagian. */
    public function untukBagian(string $periode, string $bagian): array
    {
        $item = $this->semua($periode)->where('bagian', $bagian)->values();
        $ok   = $item->whereNull('alasan')->values();

        return [
            'layak'    => $ok,
            'dilewati' => $item->whereNotNull('alasan')->values(),
            'total'    => (int) $ok->sum('jumlah'),
        ];
    }
}
