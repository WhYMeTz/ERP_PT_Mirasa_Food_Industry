<?php

namespace App\Http\Controllers\Manajemen;

use App\Exports\Absensi\AbsensiExport;
use App\Exports\Absensi\AbsensiSheet;
use App\Http\Controllers\Controller;
use App\Models\Manajemen\TrxAbsensiKaryawan;
use App\Models\MasterData\MstKaryawan;
use App\Services\Manajemen\AbsensiLaporan;
use App\Services\Manajemen\AbsensiService;
use App\Models\Manajemen\MstKodeShift;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Manajemen\TrxLemburKonfirmasi;
use App\Support\CariKaryawan;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AbsensiController extends Controller
{
    /** Jam lembur per hari yang masuk "Lembur 1"; kelebihannya masuk "Lembur 2". */
    private const BATAS_LEMBUR_1 = 1.0;

    public function index(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);

        $base  = $this->kueriDasar($request, $dari, $sampai);
        $query = $this->kueriStatus($request, clone $base);

        $stat = [
            'baris'    => (clone $query)->count(),
            'perlu'    => (clone $base)->whereIn('status', ['Perlu dicek', 'Tanpa jadwal'])->count(),
            'potongan' => round((clone $query)->sum('potongan_jam'), 2),
            'lembur'   => round((clone $query)->sum('lembur_jam'), 2),
            'ragu'     => (clone $base)->where('lembur_ragu', '>', 0)->whereNull('lembur_konfirmasi')->count(),
        ];

        $rows = $query->orderBy('tanggal')->orderBy('nama')->paginate(50)->withQueryString();

        return view('manajemen.absensi.index', [
            'rows' => $rows, 'stat' => $stat, 'dari' => $dari, 'sampai' => $sampai,
            'departemen' => MstKaryawan::DEPARTEMEN,
            'kelompoks'  => TrxAbsensiKaryawan::whereNotNull('kelompok_kerja')->distinct()->orderBy('kelompok_kerja')->pluck('kelompok_kerja'),
            'shifts'     => TrxAbsensiKaryawan::whereNotNull('kode_shift')->distinct()->orderBy('kode_shift')->pluck('kode_shift'),
            'shiftMap'   => MstKodeShift::all()->keyBy('kode'),
        ]);
    }

    public function hitung(Request $request, AbsensiService $service)
    {
        $d = $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
        ]);

        $h = $service->hitung($d['dari'], $d['sampai']);

        return redirect()
            ->route('manajemen.absensi.index', ['dari' => $d['dari'], 'sampai' => $d['sampai']])
            ->with('success', "Hitung selesai: {$h['total']} baris. Perlu dicek: {$h['perlu_dicek']}, tidak hadir: {$h['tidak_hadir']}, tanpa jadwal: {$h['tanpa_jadwal']}. Lembur perlu konfirmasi: {$h['lembur_ragu']}.");
    }

    // ===== KONFIRMASI LEMBUR =====

    /** Konfirmasi satu baris: lembur ragu (scan pulang < 30 menit setelah jadwal) dihitung atau tidak. */
    public function konfirmasiLembur(Request $request)
    {
        $d = $request->validate([
            'id_absen' => 'required|string',
            'tanggal'  => 'required|date',
            'hitung'   => 'required|in:0,1',
        ]);

        $this->simpanKonfirmasi($d['tanggal'], $d['id_absen'], (bool) $d['hitung']);

        return back()->with('success', 'Lembur ' . ($d['hitung'] ? 'dihitung.' : 'tidak dihitung.'));
    }

    /** Konfirmasi massal untuk semua baris belum dikonfirmasi pada filter periode / cari / kelompok / shift. */
    public function konfirmasiLemburMassal(Request $request)
    {
        $request->validate(['hitung' => 'required|in:0,1', 'ids' => 'nullable|string']);
        [$dari, $sampai] = $this->rentang($request);

        // ids (opsional) = karyawan yang dicentang; kosong = semua pada filter
        $ids = collect(explode(',', (string) $request->ids))->map(fn ($v) => trim($v))->filter()->unique()->values();

        $rows = $this->kueriDasar($request, $dari, $sampai)
            ->where('lembur_ragu', '>', 0)->whereNull('lembur_konfirmasi')
            ->when($ids->isNotEmpty(), fn ($q) => $q->whereIn('id_absen', $ids->all()))
            ->get();

        DB::transaction(fn () => $rows->each(
            fn ($r) => $this->simpanKonfirmasi($r->tanggal->toDateString(), $r->id_absen, (bool) $request->hitung)
        ));

        return back()->with('success', $rows->count() . ' lembur ' . ($request->hitung ? 'dihitung.' : 'tidak dihitung.'));
    }

    private function simpanKonfirmasi(string $tanggal, string $idAbsen, bool $hitung): void
    {
        TrxLemburKonfirmasi::updateOrCreate(
            ['tanggal' => $tanggal, 'id_absen' => $idAbsen],
            ['hitung' => $hitung, 'oleh' => auth()->id()]
        );

        // langsung perbarui barisnya, tanpa perlu Hitung Ulang
        TrxAbsensiKaryawan::where('tanggal', $tanggal)->where('id_absen', $idAbsen)->where('lembur_ragu', '>', 0)
            ->update([
                'lembur_konfirmasi' => $hitung,
                'lembur_jam'        => $hitung ? DB::raw('lembur_ragu') : 0,
            ]);
    }

    private function catatanLembur($r): ?string
    {
        if ($r->lembur_ragu <= 0) {
            return $r->catatan;
        }
        $n = rtrim(rtrim(number_format((float) $r->lembur_ragu, 2, ',', ''), '0'), ',');
        $t = is_null($r->lembur_konfirmasi)
            ? "Lembur {$n} jam BELUM dikonfirmasi (belum dihitung)"
            : ($r->lembur_konfirmasi ? "Lembur {$n} jam dikonfirmasi: dihitung" : "Lembur {$n} jam dikonfirmasi: tidak dihitung");

        return trim(($r->catatan ? $r->catatan . ' | ' : '') . $t);
    }

    // ===== EXPORT & CETAK =====

    /** Export Excel: sheet Rekap per Karyawan + sheet Detail, mengikuti filter yang aktif di halaman. */
    public function exportExcel(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);
        $rows = $this->ambilBaris($request, $dari, $sampai);

        if ($rows->isEmpty()) {
            return back()->with('error', 'Tidak ada data absensi untuk diekspor pada filter ini.');
        }

        $dept = MstKaryawan::DEPARTEMEN;
        $jam  = fn ($d, $tgl) => $d ? $d->format('H:i') . ($d->toDateString() !== $tgl->toDateString() ? ' (+1)' : '') : '';

        // --- sheet Detail ---
        $detail = $rows->map(fn ($r) => [
            ExcelDate::dateTimeToExcel($r->tanggal),
            $r->tanggal->locale('id')->translatedFormat('l'),
            $r->id_absen, $r->nik, $r->nama,
            $dept[$r->departemen_cd] ?? $r->departemen_cd,
            $r->kelompok_kerja, $r->kode_shift,
            $r->jadwal_masuk ? $r->jadwal_masuk->format('H:i') : '',
            $r->jadwal_pulang ? $r->jadwal_pulang->format('H:i') : '',
            $r->masuk_finger ? $r->masuk_finger->format('H:i:s') : '',
            $jam($r->masuk_bulat, $r->tanggal),
            $r->pulang_finger ? $r->pulang_finger->format('H:i:s') . ($r->pulang_finger->toDateString() !== $r->tanggal->toDateString() ? ' (+1)' : '') : '',
            $jam($r->pulang_bulat, $r->tanggal),
            (float) $r->istirahat, (float) $r->potongan_jam, (float) $r->total_jam, (float) $r->lembur_jam, round((float) $r->kehadiran, 2),
            $r->status, $this->catatanLembur($r),
        ])->all();

        $detail[] = ['TOTAL', '', '', '', $rows->count() . ' baris', '', '', '', '', '', '', '', '', '',
            '', round($rows->sum('potongan_jam'), 2), round($rows->sum('total_jam'), 2), round($rows->sum('lembur_jam'), 2), round($rows->sum('kehadiran'), 2), '', ''];

        $kolomDetail = ['Tanggal', 'Hari', 'ID Absen', 'ID Karyawan', 'Nama', 'Bagian / Divisi', 'Kelompok', 'Shift',
            'Jadwal Masuk', 'Jadwal Pulang', 'Masuk Finger', 'Masuk Bulat', 'Pulang Finger', 'Pulang Bulat',
            'Istirahat', 'Potongan', 'Total Jam', 'Lembur', 'Kehadiran', 'Status', 'Catatan'];

        // --- sheet Rekap ---
        $rekap = AbsensiLaporan::rekap($this->keBaris($rows, $dept));
        $t     = AbsensiLaporan::total($rekap);

        // Hari biasa: Lembur 1 = jam lembur pertama (maks BATAS_LEMBUR_1), Lembur 2 = kelebihannya.
        // Hari Minggu (shift jenis 'minggu'): seluruh lembur masuk Lembur 2.
        $shiftMap = MstKodeShift::all()->keyBy('kode');
        $minggu   = fn ($x) => ($shiftMap[$x->kode_shift]->jenis ?? null) === 'minggu';
        $lembur = $rows->groupBy('id_absen')->map(fn ($g) => [
            round($g->sum(fn ($x) => $minggu($x) ? 0 : min((float) $x->lembur_jam, self::BATAS_LEMBUR_1)), 2),
            round($g->sum(fn ($x) => $minggu($x) ? (float) $x->lembur_jam : max(0, (float) $x->lembur_jam - self::BATAS_LEMBUR_1)), 2),
        ])->all();

        $isiRekap = [];
        $totalL1 = $totalL2 = 0;
        foreach ($rekap as $i => $r) {
            [$l1, $l2] = $lembur[$r['id_absen']] ?? [0, 0];
            $totalL1 += $l1;
            $totalL2 += $l2;
            $isiRekap[] = [$i + 1, $r['nik'], $r['id_absen'], $r['nama'], $r['departemen'], $r['kelompok'],
                $r['hari_data'], $r['kehadiran'], $r['total_jam'], $r['potongan'], $r['lembur'], $l1, $l2, $r['perlu'], $r['tidak_hadir']];
        }
        $isiRekap[] = ['TOTAL', '', '', $t['karyawan'] . ' karyawan', '', '', $t['hari_data'], $t['kehadiran'], $t['total_jam'], $t['potongan'], $t['lembur'], $totalL1, $totalL2, $t['perlu'], $t['tidak_hadir']];

        $filter = $this->teksFilter($request, $rows);

        $sheets = [
            new AbsensiSheet('Rekap per Karyawan',
                ['No', 'ID Karyawan', 'ID Absen', 'Nama', 'Bagian / Divisi', 'Kelompok', 'Jumlah Hari Data', 'Total Kehadiran (hari)', 'Total Jam', 'Total Potongan (jam)', 'Total Lembur (jam)', 'Lembur 1 (jam)', 'Lembur 2 (jam)', 'Perlu Dicek', 'Tidak Hadir'],
                $isiRekap, ['C' => '@', 'H' => '0.0', 'I' => '0.0', 'J' => '0.0', 'K' => '0.0', 'L' => '0.0', 'M' => '0.0'], true, $dari, $sampai, $filter),
            new AbsensiSheet('Detail', $kolomDetail, $detail,
                ['A' => 'dd/mm/yyyy', 'C' => '@'], true, $dari, $sampai, $filter),
        ];

        return Excel::download(new AbsensiExport($sheets), "Absensi_{$dari}_sd_{$sampai}.xlsx");
    }

    /** Cetak PDF rekap per karyawan (detail per hari cukup lewat Export Excel). */
    public function cetakPdf(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);
        $rows = $this->ambilBaris($request, $dari, $sampai);

        $dept  = MstKaryawan::DEPARTEMEN;
        $rekap = AbsensiLaporan::rekap($this->keBaris($rows, $dept));

        $filter = $this->teksFilter($request, $rows);

        $pdf = Pdf::loadView('manajemen.absensi.pdf_report', [
            'rekap' => $rekap, 'total' => AbsensiLaporan::total($rekap),
            'dari' => $dari, 'sampai' => $sampai, 'filter' => $filter,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("laporan_absensi_rekap_{$dari}_sd_{$sampai}.pdf");
    }

    /** Kartu absensi satu karyawan. */
    public function kartu(Request $request)
    {
        [$dari, $sampai] = $this->periodeKartu($request);
        $id = str_pad(trim((string) $request->query('id_absen')), 9, '0', STR_PAD_LEFT);

        $rows = TrxAbsensiKaryawan::where('id_absen', $id)
            ->whereBetween('tanggal', [$dari, $sampai])->orderBy('tanggal')->get();
        abort_if($rows->isEmpty(), 404, 'Tidak ada data absensi karyawan ini pada periode tersebut.');

        return view('manajemen.absensi.kartu', $this->dataKartu(collect([$id => $rows]), $dari, $sampai));
    }

    /** Kartu semua karyawan sesuai filter (periode, cari, kelompok, shift). Filter status sengaja diabaikan. */
    public function kartuSemua(Request $request)
    {
        [$dari, $sampai] = $this->periodeKartu($request);

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($v) => trim($v))->filter()
            ->map(fn ($v) => str_pad($v, 9, '0', STR_PAD_LEFT))
            ->unique()->values();

        $query = $ids->isNotEmpty()
            ? TrxAbsensiKaryawan::whereBetween('tanggal', [$dari, $sampai])->whereIn('id_absen', $ids->all())
            : $this->kueriDasar($request, $dari, $sampai);

        $perKaryawan = $query->orderBy('tanggal')->get()->groupBy('id_absen');
        abort_if($perKaryawan->isEmpty(), 404, 'Tidak ada data absensi pada pilihan ini.');

        $perKaryawan = $perKaryawan->sortBy(fn ($g) => $g->first()->nama, SORT_NATURAL | SORT_FLAG_CASE);

        return view('manajemen.absensi.kartu', $this->dataKartu($perKaryawan, $dari, $sampai));
    }

    private function periodeKartu(Request $request): array
    {
        [$dari] = $this->rentang($request);
        $awal = Carbon::parse($dari)->startOfMonth();

        return [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()];
    }

    private function dataKartu(iterable $perKaryawan, string $dari, string $sampai): array
    {
        $master = MstKaryawan::whereNotNull('id_absensi')->get()
            ->keyBy(fn ($k) => str_pad(trim($k->id_absensi), 9, '0', STR_PAD_LEFT));
        $shiftMap = MstKodeShift::all()->keyBy('kode');
        $hari = collect(CarbonPeriod::create($dari, $sampai)->toArray());

        $kartu = [];
        foreach ($perKaryawan as $id => $rows) {
            $m     = $master[$id] ?? null;
            $awal  = $rows->first();
            $dept  = $m?->departemen_cd ?? $awal->departemen_cd;
            $valid = $rows->whereIn('status', ['OK', 'Tidak hadir']);   // yang belum valid tidak ikut dijumlah

            $kartu[] = [
                'id_absen'   => $id,
                'nama'       => $m?->karyawan_nm ?? $awal->nama,
                'nik'        => $m?->nik ?? $awal->nik ?? '-',
                'departemen' => $dept ? (MstKaryawan::DEPARTEMEN[$dept] ?? $dept) : '-',
                'divisi'     => $m?->jabatan_nm ?: '-',
                'kelompok'   => $rows->pluck('kelompok_kerja')->filter()->last() ?? '-',
                'rows'       => $rows->keyBy(fn ($r) => $r->tanggal->toDateString()),
                'ringkas'    => [
                    'hari_hadir'  => $rows->where('status', 'OK')->where('kehadiran', '>', 0)->count(),
                    'kehadiran'   => round($valid->sum('kehadiran'), 4),
                    'potongan'    => round($valid->sum('potongan_jam'), 2),
                    'lembur'      => round($valid->sum('lembur_jam'), 2),
                    'total_jam'   => round($valid->filter(fn ($r) => ($shiftMap[$r->kode_shift]->jenis ?? 'jam') !== 'minggu')->sum('total_jam'), 2),
                    'tidak_hadir' => $rows->where('status', 'Tidak hadir')->count(),
                    'perlu'       => $rows->whereIn('status', ['Perlu dicek', 'Tanpa jadwal'])->count(),
                    'lambat'      => $rows->where('status', 'OK')
                        ->filter(fn ($r) => $r->masuk_bulat && $r->jadwal_masuk && $r->masuk_bulat->gt($r->jadwal_masuk))->count(),
                ],
            ];
        }

        return compact('kartu', 'hari', 'dari', 'sampai', 'shiftMap');
    }

    // ===== BANTU =====

    private function rentang(Request $request): array
    {
        return [
            $request->input('dari', TrxAbsensiKaryawan::min('tanggal') ?? now()->startOfMonth()->toDateString()),
            $request->input('sampai', TrxAbsensiKaryawan::max('tanggal') ?? now()->toDateString()),
        ];
    }

    /** Filter periode + cari + kelompok + shift (tanpa status), dipakai halaman dan laporan. */
    private function kueriDasar(Request $request, string $dari, string $sampai)
    {
        $query = TrxAbsensiKaryawan::query()->whereBetween('tanggal', [$dari, $sampai]);

        return CariKaryawan::terapkan($query, $request->q, 'nama', 'id_absen', 'nik')
            ->when($request->kelompok, fn ($q, $v) => $q->where('kelompok_kerja', $v))
            ->when($request->shift, fn ($q, $v) => $q->where('kode_shift', $v));
    }

    private function kueriStatus(Request $request, \Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->when($request->status, fn ($q, $v) => match ($v) {
            'perlu'   => $q->whereIn('status', ['Perlu dicek', 'Tanpa jadwal']),
            'ragu'    => $q->where('lembur_ragu', '>', 0)->whereNull('lembur_konfirmasi'),
            default   => $q->where('status', $v),
        })->when($request->lembur, fn ($q, $v) => match ($v) {
            'belum'    => $q->where('lembur_ragu', '>', 0)->whereNull('lembur_konfirmasi'),
            'dihitung' => $q->where('lembur_ragu', '>', 0)->where('lembur_konfirmasi', true),
            'tidak'    => $q->where('lembur_ragu', '>', 0)->where('lembur_konfirmasi', false),
            'ragu'     => $q->where('lembur_ragu', '>', 0),
            default    => $q,
        });
    }

    private function jumlahRagu($rows): int
    {
        return $rows ? $rows->filter(fn ($r) => $r->lembur_ragu > 0 && is_null($r->lembur_konfirmasi))->count() : 0;
    }

    /** Ringkasan filter aktif untuk header Excel dan PDF. */
    private function teksFilter(Request $request, $rows = null): string
    {
        return collect([
            $request->q ? 'Cari: ' . $request->q : null,
            $request->status ? 'Status: ' . match ($request->status) { 'perlu' => 'Perlu dicek + Tanpa jadwal', 'ragu' => 'Lembur belum dikonfirmasi', default => $request->status } : null,
            $request->kelompok ? 'Kelompok: ' . $request->kelompok : null,
            $request->shift ? 'Shift: ' . $request->shift : null,
            $request->lembur ? 'Konfirmasi lembur: ' . match ($request->lembur) {
                'belum' => 'belum dikonfirmasi', 'dihitung' => 'dihitung', 'tidak' => 'tidak dihitung', default => 'semua kasus 30 menit',
            } : null,
            ($n = $this->jumlahRagu($rows)) ? "PERHATIAN: {$n} lembur belum dikonfirmasi (belum dihitung)" : null,
        ])->filter()->implode(' | ');
    }

    private function ambilBaris(Request $request, string $dari, string $sampai)
    {
        return $this->kueriStatus($request, $this->kueriDasar($request, $dari, $sampai))
            ->orderBy('tanggal')->orderBy('nama')->get();
    }

    /** Ubah model menjadi array sederhana untuk AbsensiLaporan. */
    private function keBaris(iterable $rows, array $dept): array
    {
        return $rows->map(fn ($r) => [
            'id_absen' => $r->id_absen, 'nik' => $r->nik, 'nama' => $r->nama,
            'departemen' => $dept[$r->departemen_cd] ?? $r->departemen_cd, 'kelompok' => $r->kelompok_kerja,
            'kehadiran' => $r->kehadiran, 'total_jam' => $r->total_jam, 'potongan_jam' => $r->potongan_jam,
            'lembur_jam' => $r->lembur_jam, 'status' => $r->status,
        ])->all();
    }
}
