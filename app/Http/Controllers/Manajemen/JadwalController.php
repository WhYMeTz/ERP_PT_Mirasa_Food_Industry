<?php
// app/Http/Controllers/Manajemen/JadwalController.php
namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Imports\Manajemen\JadwalImport;
use App\Models\Manajemen\MstKodeShift;
use App\Models\Manajemen\TrxJadwalKaryawan;
use App\Services\Manajemen\JadwalService;
use App\Models\MasterData\MstKaryawan;
use App\Services\Manajemen\AbsensiService;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $periode = TrxJadwalKaryawan::whereBetween('tanggal', [$dari, $sampai]);

        // jadwal dihubungkan ke master karyawan lewat ID absen (9 digit, sama seperti di AbsensiService)
        $denganKaryawan = TrxJadwalKaryawan::whereBetween('tanggal', [$dari, $sampai])
            ->join('mst_karyawan as k', fn ($j) => $j->whereRaw("LPAD(TRIM(k.id_absensi), 9, '0') = trx_jadwal_karyawan.id_absen"));

        $hitungShift = fn (string $nilai) => (clone $denganKaryawan)
            ->whereRaw('UPPER(TRIM(k.shift)) = ?', [$nilai])
            ->distinct()
            ->count('trx_jadwal_karyawan.id_absen');

        $stat = [
            'langsung'       => (clone $periode)->where('kelompok_kerja', 'like', 'Langsung%')->distinct()->count('id_absen'),
            'tidak_langsung' => (clone $periode)->where('kelompok_kerja', 'Tidak Langsung')->distinct()->count('id_absen'),
            'shift_a'        => $hitungShift('SHIFT A'),
            'shift_b'        => $hitungShift('SHIFT B'),
            'non_shift'      => $hitungShift('NON SHIFT'),
        ];

        $rows = $query->orderBy('tanggal')->orderBy('nama')->paginate(50)->withQueryString();

        return view('manajemen.jadwal.index', [
            'rows'      => $rows,
            'stat'      => $stat,
            'dari'      => $dari,
            'sampai'    => $sampai,
            'shiftMap'  => MstKodeShift::all()->keyBy('kode'),
            'kelompoks' => $kelompoks = TrxJadwalKaryawan::whereNotNull('kelompok_kerja')->distinct()->orderBy('kelompok_kerja')->pluck('kelompok_kerja'),
            'kelompokOpsi' => collect(MstKaryawan::KELOMPOK)->values()->merge($kelompoks)->unique()->sort()->values(),
            'karyawanOpsi' => MstKaryawan::whereNotNull('id_absensi')->orderBy('karyawan_nm')->get()->map(fn ($k) => [
                'id'       => str_pad(trim($k->id_absensi), 9, '0', STR_PAD_LEFT),
                'nama'     => $k->karyawan_nm,
                'kelompok' => MstKaryawan::KELOMPOK[$k->kelompok] ?? $k->kelompok,
                'shift'    => $k->shift,
            ])->values(),
        ]);
    }

    public function import(Request $request, JadwalService $service)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $import = new JadwalImport(MstKodeShift::where('aktif', true)->pluck('kode')->all());
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

    /** Tambah jadwal manual: beberapa karyawan x rentang tanggal x hari terpilih, satu shift. */
    public function store(Request $request, JadwalService $service, AbsensiService $absensi)
    {
        $data = $request->validate([
            'karyawan'       => ['required', 'array', 'min:1'],
            'karyawan.*'     => ['string', 'max:20'],
            'dari'           => ['required', 'date'],
            'sampai'         => ['required', 'date', 'after_or_equal:dari'],
            'hari'           => ['required', 'array', 'min:1'],
            'hari.*'         => ['integer', 'between:1,7'],
            'kode_shift'     => ['required', Rule::exists('mst_kode_shift', 'kode')->where('aktif', true)],
            'kelompok_kerja' => ['nullable', 'string', 'max:50'],
            'istirahat_jam'  => ['nullable', 'string'],   // kosong/'bawaan' = bawaan shift, atau 0 / 1 / 1.5
        ], [
            'karyawan.required' => 'Pilih minimal satu karyawan.',
            'hari.required'     => 'Pilih minimal satu hari.',
            'sampai.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal dari.',
        ]);

        $ist = $data['istirahat_jam'] ?? null;
        if (blank($ist) || $ist === 'bawaan') {
            $ist = null;
        } elseif (is_numeric($ist) && in_array((float) $ist, [0.0, 1.0, 1.5], true)) {
            $ist = (float) $ist;
        } else {
            return back()->withInput()->with('error', 'Istirahat harus 0, 1, atau 1,5 jam.');
        }

        // ID absen -> data karyawan (sama cara dengan AbsensiService: 9 digit)
        $master = MstKaryawan::whereNotNull('id_absensi')->get()
            ->keyBy(fn ($k) => str_pad(trim($k->id_absensi), 9, '0', STR_PAD_LEFT));

        $karyawan = [];
        foreach (array_unique($data['karyawan']) as $id) {
            $id = str_pad(trim($id), 9, '0', STR_PAD_LEFT);
            $k  = $master->get($id);
            if (! $k) {
                return back()->withInput()->with('error', "ID absen {$id} tidak ditemukan di Master Karyawan.");
            }
            $karyawan[] = ['id' => $id, 'nama' => $k->karyawan_nm, 'kelompok' => MstKaryawan::KELOMPOK[$k->kelompok] ?? $k->kelompok];
        }

        $hari = array_map('intval', $data['hari']);
        $rows = $service->susunBaris($karyawan, $data['dari'], $data['sampai'], $hari, $data['kode_shift'], $data['kelompok_kerja'] ?? null, $ist);

        if (! $rows) {
            return back()->withInput()->with('error', 'Tidak ada tanggal yang cocok dengan hari yang dipilih.');
        }
        if (count($rows) > 5000) {
            return back()->withInput()->with('error', 'Terlalu banyak baris (' . count($rows) . '). Maksimal 5.000 baris sekali simpan, perkecil rentang atau jumlah karyawan.');
        }

        // hitung berapa baris yang menimpa jadwal lama (tanggal + ID absen sama)
        $ada = TrxJadwalKaryawan::whereBetween('tanggal', [$data['dari'], $data['sampai']])
            ->whereIn('id_absen', array_column($karyawan, 'id'))
            ->get(['tanggal', 'id_absen'])
            ->map(fn ($r) => $r->tanggal->toDateString() . '|' . $r->id_absen)
            ->flip();
        $menimpa = collect($rows)->filter(fn ($r) => $ada->has($r['tanggal'] . '|' . $r['id_absen']))->count();

        $hasil = null;
        DB::transaction(function () use ($service, $absensi, $rows, &$hasil) {
            $hasil = $service->simpan($rows);
            $absensi->hitung($hasil['dari'], $hasil['sampai']);
        });

        return redirect()
            ->route('manajemen.jadwal.index', ['dari' => $hasil['dari'], 'sampai' => $hasil['sampai']])
            ->with('success', "{$hasil['baris']} baris jadwal disimpan" . ($menimpa ? " ({$menimpa} menimpa jadwal lama)" : '') . ", absensi {$hasil['dari']} s.d {$hasil['sampai']} dihitung ulang.");
    }

    public function template()
    {
        $shifts   = MstKodeShift::where('aktif', true)->orderBy('id')->get();
        $kelompok = array_values(MstKaryawan::KELOMPOK);

        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet()->setTitle('Jadwal');
        $ws->fromArray(['Tanggal', 'ID_Absen', 'Nama (opsional)', 'Kelompok_Kerja', 'Kode_Shift', 'Istirahat_Jam'], null, 'A1');
        $ws->getStyle('A1:F1')->getFont()->setBold(true);
        $ws->getStyle('A1:F1')->getFill()->setFillType('solid')->getStartColor()->setRGB('9DC3E6');
        $ws->getStyle('A2:A301')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $ws->getStyle('B2:B301')->getNumberFormat()->setFormatCode('@');
        $ws->fromArray([['2026-09-01', '000000512', 'CONTOH NAMA', $kelompok[0] ?? '', $shifts->first()->kode ?? 'A', 1]], null, 'A2');
        foreach (['A' => 13, 'B' => 13, 'C' => 26, 'D' => 20, 'E' => 12, 'F' => 14] as $c => $w) {
            $ws->getColumnDimension($c)->setWidth($w);
        }
        $ws->freezePane('A2');

        // sheet daftar (sumber dropdown)
        $dl = $ss->createSheet()->setTitle('Daftar');
        $dl->setCellValue('A1', 'Kode_Shift');
        $dl->setCellValue('B1', 'Kelompok_Kerja');
        foreach ($shifts as $i => $s) { $dl->setCellValue('A' . ($i + 2), $s->kode); }
        foreach ($kelompok as $i => $k) { $dl->setCellValue('B' . ($i + 2), $k); }
        $nS = max(1, $shifts->count()) + 1;
        $nK = max(1, count($kelompok)) + 1;

        $dropdown = function (string $range, string $formula, bool $ketat) use ($ws) {
            $dv = $ws->getCell(explode(':', $range)[0])->getDataValidation();
            $dv->setType(DataValidation::TYPE_LIST)->setFormula1($formula)->setSqref($range);
            $dv->setAllowBlank(true)->setShowDropDown(false)->setShowErrorMessage(true);
            $dv->setErrorStyle($ketat ? DataValidation::STYLE_STOP : DataValidation::STYLE_WARNING);
            $dv->setError($ketat ? 'Pilih kode shift dari daftar.' : 'Kelompok di luar daftar. Lanjutkan?');
        };
        $dropdown('E2:E301', "Daftar!\$A\$2:\$A\${$nS}", true);
        $dropdown('D2:D301', "Daftar!\$B\$2:\$B\${$nK}", false);

        // sheet petunjuk, tabel kode shift diambil dari database
        $pt = $ss->createSheet()->setTitle('Petunjuk');
        $baris = [
            ['PETUNJUK PENGISIAN'],
            ['Satu baris = satu karyawan pada satu tanggal. Hanya isi karyawan yang dijadwalkan pada tanggal itu.'],
            ['Kelompok_Kerja, Kode_Shift, dan Istirahat_Jam ditentukan per hari, boleh berbeda tiap hari untuk orang yang sama.'],
            ['Istirahat_Jam: 0, 1, atau 1,5. Kosong = memakai bawaan shift.'],
            ['ID_Absen harus sama dengan ID Absensi di Master Karyawan (9 digit, format teks).'],
            ['Driver: kelompok tetap Tidak Langsung. Aturan driver dikenali dari Bagian/Divisi = Driver di Master Karyawan.'],
            ['Hapus baris contoh sebelum impor. Impor ulang pada Tanggal + ID_Absen yang sama akan menimpa baris lama.'],
            [],
            ['Kode', 'Hari', 'Masuk', 'Pulang', 'Istirahat', 'Standar', 'Keterangan'],
        ];
        foreach ($shifts as $s) {
            $baris[] = [$s->kode, $s->hari, $s->jam_masuk ? substr($s->jam_masuk, 0, 5) : '-',
                ($s->jam_pulang ? substr($s->jam_pulang, 0, 5) : '-') . ($s->lintas_hari ? ' (+1 hari)' : ''),
                (float) $s->istirahat_default, $s->standar_jam ?? '-', $s->keterangan];
        }
        $pt->fromArray($baris, null, 'A1');
        $pt->getStyle('A1')->getFont()->setBold(true);
        $pt->getStyle('A9:G9')->getFont()->setBold(true);
        $pt->getColumnDimension('A')->setWidth(8);
        $pt->getColumnDimension('B')->setWidth(16);
        $pt->getColumnDimension('G')->setWidth(48);

        $ss->setActiveSheetIndex(0);

        return response()->streamDownload(
            fn () => (new Xlsx($ss))->save('php://output'),
            'template_import_jadwal.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function bulkUpdate(Request $request, AbsensiService $absensi)
    {
        $data = $request->validate([
            'ids'            => ['required', 'array', 'min:1'],
            'ids.*'          => ['integer', 'exists:trx_jadwal_karyawan,id'],
            'kelompok_kerja' => ['nullable', 'string', 'max:50'],
            'kode_shift'     => ['nullable', 'exists:mst_kode_shift,kode'],
            'istirahat_jam'  => ['nullable', 'string'],   // kosong = tidak diubah, 'bawaan' = pakai bawaan shift, atau angka
        ]);

        $update = [];
        if (filled($data['kelompok_kerja'] ?? null)) {
            $update['kelompok_kerja'] = $data['kelompok_kerja'];
        }
        if (filled($data['kode_shift'] ?? null)) {
            $update['kode_shift'] = $data['kode_shift'];
        }
        $ist = $data['istirahat_jam'] ?? null;
        if ($ist === 'bawaan') {
            $update['istirahat_jam'] = null;
        } elseif (filled($ist)) {
            if (!is_numeric($ist) || $ist < 0 || $ist > 8) {
                return back()->with('error', 'Istirahat harus berupa angka 0 sampai 8 jam.');
            }
            $update['istirahat_jam'] = (float) $ist;
        }

        if (!$update) {
            return back()->with('error', 'Tidak ada kolom yang diubah.');
        }

        $ids = $data['ids'];
        $range = TrxJadwalKaryawan::whereIn('id', $ids)->selectRaw('MIN(tanggal) as dari, MAX(tanggal) as sampai')->first();

        DB::transaction(function () use ($ids, $update, $range, $absensi) {
            TrxJadwalKaryawan::whereIn('id', $ids)->update($update);
            $absensi->hitung($range->dari, $range->sampai);
        });

        return back()->with('success', count($ids) . " baris jadwal diperbarui, absensi {$range->dari} s.d {$range->sampai} dihitung ulang.");
    }

    public function bulkDelete(Request $request, AbsensiService $absensi)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:trx_jadwal_karyawan,id'],
        ]);

        $ids = $data['ids'];
        $range = TrxJadwalKaryawan::whereIn('id', $ids)->selectRaw('MIN(tanggal) as dari, MAX(tanggal) as sampai')->first();

        DB::transaction(function () use ($ids, $range, $absensi) {
            TrxJadwalKaryawan::whereIn('id', $ids)->delete();
            $absensi->hitung($range->dari, $range->sampai);
        });

        return back()->with('success', count($ids) . " baris jadwal dihapus, absensi {$range->dari} s.d {$range->sampai} dihitung ulang.");
    }
}