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
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AbsensiController extends Controller
{
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
            ->with('success', "Hitung selesai: {$h['total']} baris. Perlu dicek: {$h['perlu_dicek']}, tidak hadir: {$h['tidak_hadir']}, tanpa jadwal: {$h['tanpa_jadwal']}.");
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
            (float) $r->istirahat, (float) $r->potongan_jam, (float) $r->total_jam, (float) $r->lembur_jam, (float) $r->kehadiran,
            $r->status, $r->catatan,
        ])->all();

        $detail[] = ['TOTAL', '', '', '', $rows->count() . ' baris', '', '', '', '', '', '', '', '', '',
            '', round($rows->sum('potongan_jam'), 2), round($rows->sum('total_jam'), 2), round($rows->sum('lembur_jam'), 2), round($rows->sum('kehadiran'), 4), '', ''];

        $kolomDetail = ['Tanggal', 'Hari', 'ID Absen', 'ID Karyawan', 'Nama', 'Bagian / Divisi', 'Kelompok', 'Shift',
            'Jadwal Masuk', 'Jadwal Pulang', 'Masuk Finger', 'Masuk Bulat', 'Pulang Finger', 'Pulang Bulat',
            'Istirahat', 'Potongan', 'Total Jam', 'Lembur', 'Kehadiran', 'Status', 'Catatan'];

        // --- sheet Rekap ---
        $rekap = AbsensiLaporan::rekap($this->keBaris($rows, $dept));
        $t     = AbsensiLaporan::total($rekap);

        $isiRekap = [];
        foreach ($rekap as $i => $r) {
            $isiRekap[] = [$i + 1, $r['nik'], $r['id_absen'], $r['nama'], $r['departemen'], $r['kelompok'],
                $r['hari_data'], $r['kehadiran'], $r['total_jam'], $r['potongan'], $r['lembur'], $r['perlu'], $r['tidak_hadir']];
        }
        $isiRekap[] = ['TOTAL', '', '', $t['karyawan'] . ' karyawan', '', '', $t['hari_data'], $t['kehadiran'], $t['total_jam'], $t['potongan'], $t['lembur'], $t['perlu'], $t['tidak_hadir']];

        $sheets = [
            new AbsensiSheet('Rekap per Karyawan',
                ['No', 'ID Karyawan', 'ID Absen', 'Nama', 'Bagian / Divisi', 'Kelompok', 'Jumlah Hari Data', 'Total Kehadiran (hari)', 'Total Jam', 'Total Potongan (jam)', 'Total Lembur (jam)', 'Perlu Dicek', 'Tidak Hadir'],
                $isiRekap, ['C' => '@', 'H' => '0.0000', 'I' => '0.00', 'J' => '0.00', 'K' => '0.00'], true),
            new AbsensiSheet('Detail', $kolomDetail, $detail,
                ['A' => 'dd/mm/yyyy', 'C' => '@', 'O' => '0.00', 'P' => '0.00', 'Q' => '0.00', 'R' => '0.00', 'S' => '0.0000'], true),
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

        $filter = collect([
            $request->q ? 'Cari: ' . $request->q : null,
            $request->status ? 'Status: ' . ($request->status === 'perlu' ? 'Perlu dicek + Tanpa jadwal' : $request->status) : null,
            $request->kelompok ? 'Kelompok: ' . $request->kelompok : null,
            $request->shift ? 'Shift: ' . $request->shift : null,
        ])->filter()->implode(' | ');

        $pdf = Pdf::loadView('manajemen.absensi.pdf_report', [
            'rekap' => $rekap, 'total' => AbsensiLaporan::total($rekap),
            'dari' => $dari, 'sampai' => $sampai, 'filter' => $filter,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("laporan_absensi_rekap_{$dari}_sd_{$sampai}.pdf");
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
        return TrxAbsensiKaryawan::query()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) =>
                $w->where('id_absen', 'like', "%$s%")->orWhere('nama', 'like', "%$s%")->orWhere('nik', 'like', "%$s%")))
            ->when($request->kelompok, fn ($q, $v) => $q->where('kelompok_kerja', $v))
            ->when($request->shift, fn ($q, $v) => $q->where('kode_shift', $v));
    }

    private function kueriStatus(Request $request, $query)
    {
        return $query->when($request->status, fn ($q, $v) => $v === 'perlu'
            ? $q->whereIn('status', ['Perlu dicek', 'Tanpa jadwal'])
            : $q->where('status', $v));
    }

    private function ambilBaris(Request $request, string $dari, string $sampai)
    {
        return $this->kueriStatus($request, $this->kueriDasar($request, $dari, $sampai))
            ->orderBy('tanggal')->orderBy('nama')->get();
    }

    /** Ubah model menjadi array sederhana untuk AbsensiLaporan. */
    private function keBaris($rows, array $dept): array
    {
        return $rows->map(fn ($r) => [
            'id_absen' => $r->id_absen, 'nik' => $r->nik, 'nama' => $r->nama,
            'departemen' => $dept[$r->departemen_cd] ?? $r->departemen_cd, 'kelompok' => $r->kelompok_kerja,
            'kehadiran' => $r->kehadiran, 'total_jam' => $r->total_jam, 'potongan_jam' => $r->potongan_jam,
            'lembur_jam' => $r->lembur_jam, 'status' => $r->status,
        ])->all();
    }
}
