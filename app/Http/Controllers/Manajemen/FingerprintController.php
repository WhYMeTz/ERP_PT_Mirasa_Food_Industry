<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Imports\Manajemen\FingerprintImport;
use App\Models\Manajemen\TrxAbsensiHarian;
use App\Services\Manajemen\FingerprintService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Manajemen\TrxFingerprintLog;
use App\Models\MasterData\MstKaryawan;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Support\CariKaryawan;
use Illuminate\Support\Facades\DB;
use App\Exports\Manajemen\FingerprintExport;

class FingerprintController extends Controller
{
    public function index(Request $request)
    {
        // default: periode berjalan (bulan ini)
        $dari   = $request->input('dari') ?: now()->startOfMonth()->toDateString();
        $sampai = $request->input('sampai') ?: now()->endOfMonth()->toDateString();

       $query = TrxAbsensiHarian::query()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->when($request->q, fn ($q, $s) => CariKaryawan::terapkan($q, $s, 'nama', 'id_absen'));

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

        // Filter "Belum Finger Pulang" (klik kartu). Ringkasan di atas tetap dihitung dari seluruh periode.
        $belumPulang = $request->boolean('belum_pulang');
        if ($belumPulang) {
            $query->whereNull('jam_pulang');
        }

        $rows = $query->orderByDesc('tanggal')->orderBy('nama')
            ->paginate(50)->withQueryString();

        return view('manajemen.fingerprint.index', compact('rows', 'dari', 'sampai', 'stat', 'belumPulang'))
        ->with('karyawanList', MstKaryawan::whereNotNull('id_absensi')
            ->orderBy('karyawan_nm')->get(['id_absensi', 'karyawan_nm', 'nik']));
    }

    /** Export Excel: data sesuai filter (q, dari, sampai) yang sedang dipakai, semua halaman. */
    public function export(Request $request)
    {
        $request->validate([
            'dari'   => 'nullable|date',
            'sampai' => 'nullable|date',
            'q'      => 'nullable|string|max:100',
            'belum_pulang' => 'nullable|boolean',
        ]);

        // default: periode berjalan (bulan ini)
        $dari   = $request->input('dari') ?: now()->startOfMonth()->toDateString();
        $sampai = $request->input('sampai') ?: now()->endOfMonth()->toDateString();

        $rows = TrxAbsensiHarian::query()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->when($request->q, fn ($q, $s) => CariKaryawan::terapkan($q, $s, 'nama', 'id_absen'))
            ->when($request->boolean('belum_pulang'), fn ($q) => $q->whereNull('jam_pulang'))
            ->orderBy('tanggal')->orderBy('nama')
            ->get();

        $nama = 'fingerprint_' . Carbon::parse($dari)->format('Ymd') . '-' . Carbon::parse($sampai)->format('Ymd') . '.xlsx';

        return Excel::download(new FingerprintExport($rows, (string) $dari, (string) $sampai, $request->q), $nama);
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

    public function store(Request $request)
    {
        $d = $request->validate([
            'id_absen'     => 'required|string|max:20',
            'tanggal'      => 'required|date',
            'jam_masuk'    => 'nullable|date_format:H:i',
            'jam_pulang'   => 'nullable|date_format:H:i',
            'pulang_besok' => 'nullable|boolean',
            'dikonfirmasi' => 'nullable|boolean',
            'catatan'      => 'required|string|max:255',
        ], [
            'id_absen.required'      => 'ID Absen wajib diisi.',
            'tanggal.required'       => 'Tanggal wajib diisi.',
            'jam_masuk.date_format'  => 'Format jam masuk tidak valid.',
            'jam_pulang.date_format' => 'Format jam pulang tidak valid.',
            'catatan.required'       => 'Alasan input manual wajib diisi.',
        ]);

        if (empty($d['jam_masuk']) && empty($d['jam_pulang'])) {
            throw ValidationException::withMessages(['jam_masuk' => 'Isi minimal jam masuk atau jam pulang.']);
        }

        $id = str_pad(trim($d['id_absen']), 9, '0', STR_PAD_LEFT);
        $k  = MstKaryawan::whereNotNull('id_absensi')->get()
            ->first(fn ($x) => str_pad(trim($x->id_absensi), 9, '0', STR_PAD_LEFT) === $id);

        if (!$k) {
            throw ValidationException::withMessages(['id_absen' => "ID Absen {$id} tidak ditemukan di Master Karyawan."]);
        }

        $tgl = Carbon::parse($d['tanggal'])->toDateString();

        if (TrxAbsensiHarian::where('id_absen', $id)->whereDate('tanggal', $tgl)->exists()) {
            throw ValidationException::withMessages(['id_absen' =>
                "Data {$k->karyawan_nm} pada " . Carbon::parse($tgl)->locale('id')->translatedFormat('d F Y')
                . ' sudah ada. Centang barisnya lalu klik Edit untuk mengubah jamnya.']);
        }

        $masuk  = !empty($d['jam_masuk']) ? Carbon::parse("$tgl {$d['jam_masuk']}") : null;
        $pulang = !empty($d['jam_pulang'])
            ? Carbon::parse("$tgl {$d['jam_pulang']}")->addDays($request->boolean('pulang_besok') ? 1 : 0)
            : null;

        if ($masuk && $pulang && $pulang->lte($masuk)) {
            throw ValidationException::withMessages(['jam_pulang' =>
                'Jam pulang harus setelah jam masuk. Untuk shift malam, centang "Pulang di hari berikutnya".']);
        }

        if ($request->boolean('dikonfirmasi') && (!$masuk || !$pulang)) {
            throw ValidationException::withMessages(['dikonfirmasi' =>
                'Konfirmasi hanya bisa dipakai jika jam masuk dan jam pulang sama-sama diisi.']);
        }

        TrxAbsensiHarian::create([
            'id_absen'    => $id,
            'nama'        => $k->karyawan_nm,
            'tanggal'     => $tgl,
            'masuk_asli'  => null,     // tidak ada scan mesin
            'pulang_asli' => null,
            'jam_masuk'   => $masuk ? FingerprintService::bulatkan($masuk) : null,
            'jam_pulang'  => $pulang ? FingerprintService::bulatkan($pulang) : null,
            'jumlah_scan' => 0,
            'is_manual'   => true,
            'dikonfirmasi' => $request->boolean('dikonfirmasi'),
            'catatan'     => $d['catatan'],
        ]);

        return redirect()
            ->route('manajemen.fingerprint.index', ['q' => $id, 'dari' => $tgl, 'sampai' => $tgl])
            ->with('success', "Absensi manual {$k->karyawan_nm} berhasil ditambahkan. Hitung ulang di Absensi Karyawan agar ikut terhitung.");
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
            'konfirmasi'   => 'nullable|in:ya,tidak',
        ]);

        $jm = $data['jam_masuk'] ?? null;
        $jp = $data['jam_pulang'] ?? null;
        $ct = $data['catatan'] ?? null;
        $kf = $data['konfirmasi'] ?? null;   // 'ya' | 'tidak' | null (tidak diubah)

        if (!$jm && !$jp && !$ct && !$kf) {
            return back()->with('error', 'Isi minimal salah satu: jam masuk, jam pulang, catatan, atau konfirmasi.');
        }

        $berhasil = 0;
        $gagal = [];
        $tanpaJam = [];

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

            if ($kf === 'ya' && (!$masuk || !$pulang)) {
                $tanpaJam[] = $r->nama;   // konfirmasi butuh jam masuk dan pulang
                continue;
            }

            $r->update([
                'jam_masuk'    => $masuk,
                'jam_pulang'   => $pulang,
                // konfirmasi ikut menandai baris sebagai koreksi manual, supaya dipakai AbsensiService
                'is_manual'    => ($jm || $jp || $kf === 'ya') ? true : $r->is_manual,
                'dikonfirmasi' => $kf ? $kf === 'ya' : $r->dikonfirmasi,
                'catatan'      => $ct ?? $r->catatan,
            ]);
            $berhasil++;
        }

        $redirect = back()->with('success', "$berhasil data absensi diperbarui. Hitung ulang di Absensi Karyawan agar ikut terhitung.");

        $pesan = [];
        if ($gagal)    $pesan[] = 'Dilewati (jam pulang tidak valid): ' . implode(', ', $gagal);
        if ($tanpaJam) $pesan[] = 'Dilewati (konfirmasi butuh jam masuk dan jam pulang terisi): ' . implode(', ', $tanpaJam);

        return $pesan ? $redirect->with('error', implode(' | ', $pesan)) : $redirect;
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