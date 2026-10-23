<?php
// app/Http/Controllers/Manajemen/JadwalController.php
namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Imports\Manajemen\JadwalImport;
use App\Models\Manajemen\MstKodeShift;
use App\Models\Manajemen\TrxJadwalKaryawan;
use App\Services\Manajemen\JadwalService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $dari   = $request->input('dari', TrxJadwalKaryawan::min('tanggal') ?? now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', TrxJadwalKaryawan::max('tanggal') ?? now()->toDateString());

        $query = TrxJadwalKaryawan::query()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) =>
                $w->where('id_absen', 'like', "%$s%")->orWhere('nama', 'like', "%$s%")))
            ->when($request->kelompok, fn ($q, $k) => $q->where('kelompok_kerja', $k))
            ->when($request->shift, fn ($q, $s) => $q->where('kode_shift', $s));

        $stat = [
            'baris'         => (clone $query)->count(),
            'karyawan'      => (clone $query)->distinct()->count('id_absen'),
            'hari'          => (clone $query)->distinct()->count('tanggal'),
            'tanpa_istirahat' => (clone $query)->whereNull('istirahat_jam')->count(),
        ];

        $rows = $query->orderBy('tanggal')->orderBy('nama')->paginate(50)->withQueryString();

        return view('manajemen.jadwal.index', [
            'rows'      => $rows,
            'stat'      => $stat,
            'dari'      => $dari,
            'sampai'    => $sampai,
            'shiftMap'  => MstKodeShift::all()->keyBy('kode'),
            'kelompoks' => TrxJadwalKaryawan::whereNotNull('kelompok_kerja')->distinct()->orderBy('kelompok_kerja')->pluck('kelompok_kerja'),
        ]);
    }

    public function import(Request $request, JadwalService $service)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new JadwalImport(MstKodeShift::pluck('kode')->all());
        Excel::import($import, $request->file('file'));

        if (empty($import->rows)) {
            return back()
                ->with('error', 'Tidak ada baris jadwal yang valid. Cek sheet pertama dan format kolomnya.')
                ->with('jadwal_errors', array_slice($import->errors, 0, 20));
        }

        $hasil = $service->simpan($import->rows);
        $lewat = count($import->errors);

        return redirect()
            ->route('manajemen.jadwal.index', ['dari' => $hasil['dari'], 'sampai' => $hasil['sampai']])
            ->with('success', "Impor selesai: {$hasil['baris']} baris jadwal disimpan" . ($lewat ? ", $lewat baris dilewati." : '.'))
            ->with('jadwal_errors', array_slice($import->errors, 0, 20));
    }
}