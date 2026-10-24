<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Models\Manajemen\MstKodeShift;
use App\Models\Manajemen\TrxJadwalKaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KodeShiftController extends Controller
{
    public function index()
    {
        return view('manajemen.kode_shift.index', [
            'shifts' => MstKodeShift::orderBy('id')->get(),
            'pakai'  => TrxJadwalKaryawan::select('kode_shift', DB::raw('COUNT(*) as n'))
                            ->groupBy('kode_shift')->pluck('n', 'kode_shift'),
        ]);
    }

    public function store(Request $request)
    {
        MstKodeShift::create($this->payload($request));

        return back()->with('success', 'Kode shift ditambahkan. Langsung bisa dipakai di jadwal dan absensi.');
    }

    public function update(Request $request, MstKodeShift $kodeShift)
    {
        $data = $this->payload($request, $kodeShift);

        // kode yang sudah dipakai di jadwal tidak boleh diganti namanya (jadwal lama akan yatim)
        if (TrxJadwalKaryawan::where('kode_shift', $kodeShift->kode)->exists()) {
            $data['kode'] = $kodeShift->kode;
        }

        $kodeShift->update($data);

        return back()->with('success', "Kode {$kodeShift->kode} diperbarui. Hitung ulang absensi pada periode yang terpengaruh.");
    }

    public function destroy(MstKodeShift $kodeShift)
    {
        $n = TrxJadwalKaryawan::where('kode_shift', $kodeShift->kode)->count();

        if ($n > 0) {
            return back()->with('error', "Kode {$kodeShift->kode} sudah dipakai di $n baris jadwal. Nonaktifkan saja, jangan dihapus.");
        }

        $kodeShift->delete();

        return back()->with('success', "Kode {$kodeShift->kode} dihapus.");
    }

    private function payload(Request $r, ?MstKodeShift $ada = null): array
    {
        $d = $r->validate([
            'kode'              => ['required', 'string', 'max:5', 'regex:/^[A-Za-z0-9]+$/', Rule::unique('mst_kode_shift', 'kode')->ignore($ada?->id)],
            'hari'              => 'required|string|max:30',
            'jenis'             => 'required|in:normal,security,minggu,driver,libur',
            'jam_masuk'         => 'nullable|date_format:H:i',
            'jam_pulang'        => 'nullable|date_format:H:i',
            'istirahat_default' => 'required|numeric|min:0|max:3',
            'keterangan'        => 'nullable|string|max:255',
            'istirahat_jumat' => 'nullable|numeric|min:0|max:3',
            'istirahat_sabtu' => 'nullable|numeric|min:0|max:3',
        ]);

        $d['kode'] = strtoupper($d['kode']);
        $d['aktif'] = $r->boolean('aktif');
        $berjam = in_array($d['jenis'], ['normal', 'security']);
        $lintas = $berjam && $r->boolean('lintas_hari');

        if ($berjam) {
            if (empty($d['jam_masuk']) || empty($d['jam_pulang'])) {
                throw ValidationException::withMessages(['jam_masuk' => 'Jam masuk dan jam pulang wajib diisi untuk jenis ini.']);
            }
            $m = Carbon::createFromFormat('H:i', $d['jam_masuk']);
            $p = Carbon::createFromFormat('H:i', $d['jam_pulang']);
            if ($lintas) {
                $p->addDay();
            }
            if ($p->lte($m)) {
                throw ValidationException::withMessages(['jam_pulang' => 'Jam pulang harus setelah jam masuk. Untuk shift malam, centang "pulang hari berikutnya".']);
            }
            $d['standar_jam'] = round($m->diffInMinutes($p) / 60 - (float) $d['istirahat_default'], 1);
        } else {
            $d['jam_masuk'] = $d['jam_pulang'] = $d['standar_jam'] = null;
            $d['istirahat_jumat'] = $d['istirahat_sabtu'] = null;
        }

        $d['lintas_hari'] = $lintas;

        return $d;
    }
}