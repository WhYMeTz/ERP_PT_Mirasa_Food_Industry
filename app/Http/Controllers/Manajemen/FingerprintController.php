<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Imports\Manajemen\FingerprintImport;
use App\Models\Manajemen\TrxAbsensiHarian;
use App\Services\Manajemen\FingerprintService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Manajemen\TrxFingerprintLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FingerprintController extends Controller
{
    public function index(Request $request)
    {
        $dari   = $request->input('dari', TrxAbsensiHarian::min('tanggal') ?? now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', TrxAbsensiHarian::max('tanggal') ?? now()->toDateString());

        $query = TrxAbsensiHarian::query()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) =>
                $w->where('id_absen', 'like', "%$s%")->orWhere('nama', 'like', "%$s%")));

        $perHari = (clone $query)->whereNotNull('jam_pulang')
            ->get(['tanggal', 'jam_masuk', 'jam_pulang'])
            ->groupBy(fn ($r) => $r->tanggal->toDateString())
            ->map(function ($g) {
                $mulai   = $g->sortBy('jam_masuk')->first()->jam_masuk;
                $selesai = $g->sortByDesc('jam_pulang')->first()->jam_pulang;
                $menit   = max(0, $mulai->diffInMinutes($selesai, false));

                return min($menit / 60, 24);
            });

        $stat = [
                    'karyawan'         => (clone $query)->distinct()->count('id_absen'),
                    'hari_data'        => (clone $query)->count(),
                    'belum_pulang'     => (clone $query)->whereNull('jam_pulang')->count(),
                    'jam_operasional'  => round($perHari->sum(), 1),
                    'hari_operasional' => $perHari->count(),
                    'rata_operasional' => $perHari->count() ? round($perHari->avg(), 1) : 0,
                ];

        $rows = $query->orderByDesc('tanggal')->orderBy('nama')
            ->paginate(50)->withQueryString();

        return view('manajemen.fingerprint.index', compact('rows', 'dari', 'sampai', 'stat'));
    }

    public function import(Request $request, FingerprintService $service)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new FingerprintImport();
        Excel::import($import, $request->file('file'));

        if (empty($import->rows)) {
            return back()->with('error', 'Tidak ada data scan yang terbaca. Cek format file dan sheet pertama.');
        }

        $hasil = $service->simpanLog($import->rows);

        return back()->with('success',
            "Impor selesai: {$hasil['scan']} scan diproses menjadi {$hasil['hari_karyawan']} data absensi harian.");
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'ids'          => 'required|array|min:1',
            'ids.*'        => 'integer',
            'jam_masuk'    => 'nullable|date_format:H:i',
            'jam_pulang'   => 'nullable|date_format:H:i',
            'pulang_besok' => 'nullable|boolean',
            'catatan'      => 'nullable|string|max:255',
        ]);

        $jm = $data['jam_masuk'] ?? null;
        $jp = $data['jam_pulang'] ?? null;
        $ct = $data['catatan'] ?? null;

        if (!$jm && !$jp && !$ct) {
            return back()->with('error', 'Isi minimal salah satu: jam masuk, jam pulang, atau catatan.');
        }

        $berhasil = 0;
        $gagal = [];

        foreach (TrxAbsensiHarian::whereIn('id', $data['ids'])->get() as $r) {
            $tgl    = $r->tanggal->toDateString();
            $masuk  = $jm ? Carbon::parse("$tgl $jm") : $r->jam_masuk;
            $pulang = $jp
                ? Carbon::parse("$tgl $jp")->addDays($request->boolean('pulang_besok') ? 1 : 0)
                : $r->jam_pulang;

            if ($masuk && $pulang && $pulang->lte($masuk)) {
                $gagal[] = $r->nama;   // pulang tidak boleh sebelum/sama dengan masuk
                continue;
            }

            $r->update([
                'jam_masuk'  => $masuk,
                'jam_pulang' => $pulang,
                'is_manual'  => ($jm || $jp) ? true : $r->is_manual,
                'catatan'    => $ct ?? $r->catatan,
            ]);
            $berhasil++;
        }

        $redirect = back()->with('success', "$berhasil data absensi diperbarui.");

        return $gagal
            ? $redirect->with('error', 'Dilewati (jam pulang tidak valid): ' . implode(', ', $gagal))
            : $redirect;
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $rows = TrxAbsensiHarian::whereIn('id', $data['ids'])->get();

        DB::transaction(function () use ($rows) {
            foreach ($rows as $r) {
                TrxFingerprintLog::where('id_absen', $r->id_absen)
                    ->whereDate('scan_at', $r->tanggal->toDateString())
                    ->delete();
            }
            TrxAbsensiHarian::whereIn('id', $rows->pluck('id'))->delete();
        });

        return back()->with('success', $rows->count() . ' data absensi dihapus.');
    }
}