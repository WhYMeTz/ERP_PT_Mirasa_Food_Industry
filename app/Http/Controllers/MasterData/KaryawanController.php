<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreKaryawanRequest;
use App\Http\Requests\MasterData\UpdateKaryawanRequest;
use App\Services\Common\CodeGeneratorService;
use App\Services\MasterData\KaryawanService;
use App\Exports\Karyawan\KaryawanExport;
use App\Imports\Karyawan\KaryawanImport;
use Maatwebsite\Excel\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\MasterData\MstKaryawan;
use App\Models\User;
use App\Exports\Karyawan\KaryawanTemplateExport;

class KaryawanController extends Controller
{
    public function __construct(
        protected KaryawanService $karyawanService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canAccessKaryawan(), 403, 'Anda tidak memiliki hak akses untuk melihat master karyawan.');

        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $departemen = $request->input('departemen');

        // Filter baru untuk pencarian lengkap
        $kelompok = $request->input('kelompok');
        $shift = $request->input('shift');

        $karyawanList = $this->karyawanService->getAllPaginated($perPage, $search, $departemen, $kelompok, $shift);
        $nextNik = $this->codeGenerator->generate('mst_karyawan', 'nik', 'KRY-');

        $departemenList = [
            'MANAJEMEN'  => 'Manajemen / Direksi',    
            'PRODUKSI'   => 'Departemen Produksi',
            'GUDANG'     => 'Departemen Gudang & Logistik',
            'PURCHASING' => 'Departemen Pengadaan (Purchasing)',
            'FINANCE'    => 'Departemen Keuangan & Kasir',
            'QC'         => 'Departemen Quality Control (QC)',
            'HRD'        => 'Departemen SDM / HRD',
            'UMUM'       => 'Departemen Umum',
            'DRIVER'     => 'Driver',
            'CLEANING'   => 'Cleaning Service',
            'SECURITY'   => 'Security',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Data karyawan berhasil diambil.',
                'data'      => $karyawanList,
                'next_nik'  => $nextNik,
            ]);
        }

        return view('master_data.karyawan.index', compact(
            'karyawanList', 'search', 'departemen', 'kelompok', 'shift', 'nextNik', 'departemenList'
        ));
    }

    public function store(StoreKaryawanRequest $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canCreateMasterKaryawan(), 403, 'Anda tidak memiliki hak akses untuk menambah karyawan baru.');

        $karyawan = $this->karyawanService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data karyawan berhasil disimpan.',
                'data'    => $karyawan,
            ], 201);
        }

        return redirect()
            ->route('master.karyawan.index')
            ->with('success', "Data karyawan {$karyawan->karyawan_nm} ({$karyawan->nik}) berhasil disimpan.");
    }

    public function update(UpdateKaryawanRequest $request, int $karyawan_id): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canEditMasterKaryawan(), 403, 'Anda tidak memiliki hak akses untuk mengedit data karyawan.');

        $karyawan = $this->karyawanService->update($karyawan_id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data karyawan berhasil diperbarui.',
                'data'    => $karyawan,
            ]);
        }

        return redirect()
            ->route('master.karyawan.index')
            ->with('success', "Data karyawan {$karyawan->karyawan_nm} berhasil diperbarui.");
    }

    public function destroy(Request $request, int $karyawan_id): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canDeleteMasterKaryawan(), 403, 'Anda tidak memiliki hak akses untuk menonaktifkan data karyawan.');

        $this->karyawanService->delete($karyawan_id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data karyawan berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('master.karyawan.index')
            ->with('success', 'Data karyawan berhasil dinonaktifkan.');
    }

    /**
     * EDIT MASSAL: ubah SATU kolom yang sama untuk beberapa karyawan yang dicentang.
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canEditMasterKaryawan(), 403, 'Anda tidak memiliki hak akses untuk mengedit data karyawan.');

        // Kolom yang boleh diubah massal => [label, aturan nilai]
        $kolom = [
            'status_kerja'  => ['Status Karyawan', ['required', Rule::in(['Tetap', 'Kontrak', 'Harian', 'Borong'])]],
            'shift'         => ['Shift',           ['required', Rule::in(['SHIFT A', 'SHIFT B', 'NON SHIFT'])]],
            'kelompok'      => ['Kelompok',        ['required', Rule::in(array_keys(MstKaryawan::KELOMPOK))]],
            'departemen_cd' => ['Departemen',      ['required', Rule::in(array_keys(MstKaryawan::DEPARTEMEN))]],
            'jabatan_nm'    => ['Divisi / Bagian', ['required', Rule::in(MstKaryawan::DIVISI)]],
            'bank_name'     => ['Bank',            ['required', 'string', 'max:50']],
            'status_ptkp'   => ['Status PTKP',     ['required', Rule::in(MstKaryawan::PTKP)]],
        ];

        $validator = Validator::make($request->all(), [
            'ids'   => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
            'field' => ['required', Rule::in(array_keys($kolom))],
        ], [
            'ids.required' => 'Pilih minimal satu karyawan terlebih dahulu.',
            'ids.min'      => 'Pilih minimal satu karyawan terlebih dahulu.',
            'field.in'     => 'Kolom yang dipilih tidak boleh diubah secara massal.',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $field = $request->input('field');

        $nilai = Validator::make($request->all(), ['value' => $kolom[$field][1]], [
            'value.required' => 'Nilai baru wajib diisi.',
            'value.in'       => 'Nilai baru tidak valid untuk kolom ' . $kolom[$field][0] . '.',
        ]);

        if ($nilai->fails()) {
            return back()->with('error', $nilai->errors()->first());
        }

        $value  = (string) $request->input('value');
        $jumlah = $this->karyawanService->bulkUpdate($request->input('ids'), $field, $value);

        return back()->with('success', "{$jumlah} karyawan berhasil diubah: {$kolom[$field][0]} menjadi \"{$value}\".");
    }

    /**
     * 1. FUNGSI EXPORT KE EXCEL
     */
    public function exportExcel(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canAccessKaryawan(), 403, 'Anda tidak memiliki hak akses.');

        $search = $request->input('search');
        $departemen = $request->input('departemen');
        $kelompok = $request->input('kelompok');
        $shift = $request->input('shift');

        $karyawanData = $this->karyawanService->getAllFilteredQuery($search, $departemen, $kelompok, $shift)->get();

        // NPWP/NIK bersifat pribadi: hanya ikut diekspor untuk yang berhak mengedit karyawan
        return app(Excel::class)->download(
            new KaryawanExport($karyawanData, (bool) $user->canEditMasterKaryawan()),
            'master_data_karyawan_' . date('YmdHis') . '.xlsx'
        );
    }

    /**
     * 2. FUNGSI IMPORT DATA DARI EXCEL
     */
    public function importExcel(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canCreateMasterKaryawan(), 403, 'Anda tidak memiliki hak akses.');

        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new KaryawanImport(1, (bool) $user->canEditMasterKaryawan());

        try {
            DB::transaction(fn () => app(Excel::class)->import($import, $request->file('file_excel')));
        } catch (\Exception $e) {
            return redirect()
                ->route('master.karyawan.index')
                ->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }

        $pesan = "Import selesai: {$import->ditambah} karyawan baru, {$import->diperbarui} diperbarui"
            . ($import->tanpaPerubahan ? ", {$import->tanpaPerubahan} tanpa perubahan" : '') . '.';

        $redirect = redirect()->route('master.karyawan.index')->with('success', $pesan);

        if ($import->ditolak) {
            $daftar = collect($import->ditolak)->take(8)->map(fn ($d) => "{$d['nik']}: {$d['alasan']}")->implode(' | ');
            $lebih  = count($import->ditolak) > 8 ? ' | dan ' . (count($import->ditolak) - 8) . ' lainnya' : '';

            $redirect->with('error', count($import->ditolak) . " baris ditolak (tidak diubah): {$daftar}{$lebih}");
        }

        return $redirect;
    }

    /**
     * 3. FUNGSI CETAK LAPORAN PDF
     */
    public function cetakPdf(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canAccessKaryawan(), 403, 'Anda tidak memiliki hak akses.');

        $search = $request->input('search');
        $departemen = $request->input('departemen');
        $kelompok = $request->input('kelompok');
        $shift = $request->input('shift');

        $karyawanList = $this->karyawanService->getAllFilteredQuery($search, $departemen, $kelompok, $shift)->get();

        // NPWP/NIK + PTKP ikut dicetak hanya untuk yang berhak mengedit karyawan
        $denganPajak = (bool) $user->canEditMasterKaryawan();

        $pdf = Pdf::loadView('master_data.karyawan.pdf_report', compact('karyawanList', 'denganPajak'))
                  ->setPaper('a4', 'landscape');

        return $pdf->stream('laporan_karyawan_' . date('Ymd') . '.pdf');
    }

    /**
     * 4. Unduh template pengisian untuk import data karyawan
     */
    public function downloadTemplate(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(!$user?->canCreateMasterKaryawan(), 403, 'Anda tidak memiliki hak akses.');

        return app(Excel::class)->download(
            new KaryawanTemplateExport(),
            'template_import_karyawan.xlsx'
        );
    }

    /**
     * 5. HAPUS MASSAL: nonaktifkan beberapa karyawan sekaligus
     */
    public function hapusMassal(Request $request)
    {
        $user = $request->user();
        abort_unless(
            $user->isSuperAdmin() || $user->canDo('master_karyawan_delete') || $user->canDo('master_karyawan_manage'),
            403
        );

        $data = $request->validate([
            'ids'   => 'required|array|min:1|max:200',
            'ids.*' => 'integer',
        ], [
            'ids.required' => 'Pilih minimal satu karyawan.',
            'ids.max'      => 'Maksimal 200 karyawan sekali proses.',
        ]);

        $rows = MstKaryawan::with('user')
            ->whereIn('karyawan_id', $data['ids'])
            ->where('deleted_st', false)
            ->get();

        $punyaAkun = $rows->filter(fn ($k) => $k->user !== null);
        $dihapus   = $rows->diff($punyaAkun);

        DB::transaction(function () use ($dihapus, $user) {
            foreach ($dihapus as $k) {
                $k->forceFill([
                    'deleted_st' => true,
                    'active_st'  => false,
                    'deleted_at' => now(),
                    'deleted_by' => $user->name,
                ])->save();
            }
        });

        $pesan = $dihapus->count() . ' karyawan dinonaktifkan.';

        if ($punyaAkun->isNotEmpty()) {
            return back()
                ->with('success', $pesan)
                ->with('error', $punyaAkun->count() . ' karyawan dilewati karena punya akun sistem: '
                    . $punyaAkun->pluck('karyawan_nm')->take(10)->implode(', ')
                    . ($punyaAkun->count() > 10 ? ', dst' : '')
                    . '. Nonaktifkan satu per satu setelah akunnya diurus.');
        }

        return back()->with('success', $pesan);
    }
}
