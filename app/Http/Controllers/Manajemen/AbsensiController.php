<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Models\Manajemen\TrxAbsensiKaryawan;
use App\Models\MasterData\MstKaryawan;
use App\Services\Manajemen\AbsensiService;
use App\Models\Manajemen\MstKodeShift;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $dari   = $request->input('dari', TrxAbsensiKaryawan::min('tanggal') ?? now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', TrxAbsensiKaryawan::max('tanggal') ?? now()->toDateString());

        $base = TrxAbsensiKaryawan::query()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) =>
                $w->where('id_absen', 'like', "%$s%")->orWhere('nama', 'like', "%$s%")->orWhere('nik', 'like', "%$s%")))
            ->when($request->kelompok, fn ($q, $v) => $q->where('kelompok_kerja', $v))
            ->when($request->shift, fn ($q, $v) => $q->where('kode_shift', $v));

        $query = (clone $base)->when($request->status, fn ($q, $v) => $v === 'perlu'
            ? $q->whereIn('status', ['Perlu dicek', 'Tanpa jadwal'])
            : $q->where('status', $v));

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
    
}