<?php

namespace App\Services\Manajemen;

use App\Models\Manajemen\MstKodeShift;
use App\Models\Manajemen\TrxAbsensiKaryawan;
use App\Models\Manajemen\TrxPayroll;
use App\Models\MasterData\MstKaryawan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Biaya gaji harian per tanggal dan kelompok kerja.
 * - Selalu dihitung langsung dari absensi terbaru (tidak memakai snapshot Payroll).
 * - Karyawan tanpa gaji harian tidak ditampilkan sama sekali.
 * - Absensi berstatus "Perlu dicek" / "Tanpa jadwal" tidak dijumlahkan, hanya dihitung jumlahnya.
 */
class PayrollHarianService
{
    public const LANGSUNG = ['Langsung IFM', 'Langsung Manual', 'Langsung Packing'];
    public const TIDAK    = 'Tidak Langsung';
    

    public function __construct(private PayrollService $payroll)
    {
    }

    public function laporan(string $periode): array
    {
        [$awal, $akhir] = PayrollService::rentang($periode);

        $baris  = $this->baris($periode, $awal, $akhir);
        $normal = $baris->where('flag', false);
        $perTgl = $normal->groupBy('tanggal');
        $perlu  = $baris->where('flag', true)->groupBy('tanggal')->map(fn ($g) => $g->count());

        $hari = [];
        for ($d = Carbon::parse($akhir); $d->toDateString() >= $awal; $d->subDay()) {
            $tgl = $d->toDateString();
            $h   = ['tanggal' => $tgl, 'perlu' => (int) ($perlu[$tgl] ?? 0)] + $this->ringkas($perTgl->get($tgl, collect()));

            // tanggal kosong (tidak ada yang masuk, tidak ada biaya, tidak ada yang perlu dicek) tidak ditampilkan
            if ($h['total']['orang'] === 0 && $h['total']['biaya'] === 0 && $h['perlu'] === 0) {
                continue;
            }

            $hari[] = $h;
        }

        $total = $this->ringkas($normal);
        $status = $this->payroll->statusPeriode($periode);

        return [
            'periode'   => $periode,
            'status'    => $status,
            'hari'      => $hari,
            'total'     => $total,
            'ringkasan' => [
                'biaya'      => $total['total']['biaya'],
                'langsung'   => $total['langsung']['biaya'],
                'tidak'      => $total['tidak']['biaya'],
                'orang_hari' => $total['total']['orang'],
                'hari_kerja' => collect($hari)->where('total.orang', '>', 0)->count(),
                'perlu'      => $baris->where('flag', true)->count(),
            ],
            'selisih'   => $status === 'draft' ? null : $this->selisihFinal($periode, $normal),
        ];
    }

    public function rincian(string $tanggal, ?string $kelompok = null): array
    {
        $periode = substr($tanggal, 0, 7);
        $semua   = $this->baris($periode, $tanggal, $tanggal);

        $normal = $semua->where('flag', false);
        if ($kelompok === 'langsung') {
            $normal = $normal->whereIn('kelompok', self::LANGSUNG);
        } elseif ($kelompok) {
            $normal = $normal->where('kelompok', $kelompok);
        }

        return [
            'tanggal'  => $tanggal,
            'periode'  => $periode,
            'kelompok' => $kelompok,
            'rows'     => $normal->sortBy([['kelompok', 'asc'], ['nama', 'asc']])->values(),
            'flag'     => $semua->where('flag', true)->values(),
            'total'    => $this->sel($normal),
        ];
    }

    // ---------------------------------------------------------------------------------------------

    /** Satu baris per karyawan per tanggal. Karyawan tanpa gaji harian sudah dibuang di sini. */
    private function baris(string $periode, string $dari, string $sampai): Collection
    {
        [, $akhirPeriode] = PayrollService::rentang($periode);

        $absensi = TrxAbsensiKaryawan::whereBetween('tanggal', [$dari, $sampai])->orderBy('tanggal')->orderBy('nama')->get();
        $gaji    = $this->gajiHarian($periode, $absensi->pluck('id_absen')->unique()->values());

        $karyawan = MstKaryawan::whereNotNull('id_absensi')->get()
            ->keyBy(fn ($k) => str_pad(trim($k->id_absensi), 9, '0', STR_PAD_LEFT));
        $shifts = MstKodeShift::all()->keyBy('kode');

        $tunjHari = collect($gaji)->map(function ($g, $id) use ($karyawan, $akhirPeriode) {
            $tgl = $karyawan->get($id)?->tgl_masuk;
            $tgl = $tgl ? substr((string) $tgl, 0, 10) : null;
            return PayrollHitung::tunjanganMasaKerjaPerHari(PayrollHitung::masaKerjaTahun($tgl, $akhirPeriode));
        });

        return $absensi
            ->filter(fn ($r) => ($gaji[$r->id_absen] ?? 0) > 0)
            ->map(function ($r) use ($gaji, $tunjHari, $shifts) {
                $h    = PayrollHitung::hitungHari($gaji[$r->id_absen], $this->payroll->petaBaris($r, $shifts));
                $base = [
                    'tanggal'    => $r->tanggal->toDateString(),
                    'id_absen'   => $r->id_absen,
                    'nik'        => $r->nik,
                    'nama'       => $r->nama,
                    'kelompok'      => in_array($r->kelompok_kerja, self::LANGSUNG, true) ? $r->kelompok_kerja : self::TIDAK,
                    'kelompok_asli' => $r->kelompok_kerja ?? '-',
                    'kode_shift' => $r->kode_shift,
                    'status'     => $r->status,
                    'catatan'    => $r->catatan,
                    'masuk'      => $r->status === 'OK',
                ];

                if ($h === null) {   // Perlu dicek / Tanpa jadwal
                    return $base + ['flag' => true, 'k' => 0, 'l1' => 0, 'l2' => 0, 'pokok' => 0, 'tunj' => 0, 'honor1' => 0, 'honor2' => 0, 'total' => 0];
                }

                $tunj = ($tunjHari[$r->id_absen] ?? 0) * $h['k'];

                return $base + [
                    'flag' => false, 'k' => $h['k'], 'l1' => $h['l1'], 'l2' => $h['l2'],
                    'pokok' => $h['pokok'], 'tunj' => $tunj, 'honor1' => $h['honor1'], 'honor2' => $h['honor2'],
                    'total' => $h['pokok'] + $tunj + $h['honor1'] + $h['honor2'],
                ];
            })->values();
    }

    /** Gaji harian per karyawan: isian periode ini, atau isian periode terakhir sebelumnya (sama seperti Payroll Draft). */
    private function gajiHarian(string $periode, Collection $ids): array
    {
        $ini  = TrxPayroll::where('periode', $periode)->whereIn('id_absen', $ids)->pluck('gaji_harian', 'id_absen');
        $lalu = TrxPayroll::where('periode', '<', $periode)->whereIn('id_absen', $ids)
            ->orderByDesc('periode')->get()->unique('id_absen')->pluck('gaji_harian', 'id_absen');

        $hasil = [];
        foreach ($ids as $id) {
            $hasil[$id] = (float) ($ini->has($id) ? $ini[$id] : $lalu->get($id, 0));
        }

        return $hasil;
    }

    private function ringkas(Collection $x): array
    {
        $kel = [];
        foreach (self::LANGSUNG as $k) {
            $kel[$k] = $this->sel($x->where('kelompok', $k));
        }

        return [
            'kel'      => $kel,
            'langsung' => $this->sel($x->whereIn('kelompok', self::LANGSUNG)),
            'tidak'    => $this->sel($x->where('kelompok', self::TIDAK)),
            'total'    => $this->sel($x),
        ];
    }

    /** Jumlah orang masuk dan biaya. Dijumlahkan dulu, baru dibulatkan (sama seperti Payroll). */
    private function sel(Collection $x): array
    {
        return [
            'orang' => $x->where('masuk', true)->count(),
            'biaya' => (int) round($x->sum('total')),
        ];
    }

    /** Selisih biaya harian terkini terhadap snapshot Payroll yang sudah Final. */
    private function selisihFinal(string $periode, Collection $normal): int
    {
        $snap = TrxPayroll::where('periode', $periode)->where('status', '!=', 'draft')->get();
        $angkaFinal = $snap->sum(fn ($p) => (int) (
            ($p->hasil['gaji_pokok'] ?? 0) + ($p->hasil['honor_lembur_1'] ?? 0)
            + ($p->hasil['honor_lembur_2'] ?? 0) + ($p->hasil['tunjangan_masa_kerja'] ?? 0)
        ));
        $terkini = $normal->groupBy('id_absen')->sum(fn ($g) =>
            round($g->sum('pokok')) + round($g->sum('honor1')) + round($g->sum('honor2')) + round($g->sum('tunj')));

        return (int) round($terkini - $angkaFinal);
    }
}