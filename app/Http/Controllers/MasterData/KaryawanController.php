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

        return app(Excel::class)->download(
            new KaryawanExport($karyawanData),
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

        try {
            app(Excel::class)->import(new KaryawanImport, $request->file('file_excel'));
            
            return redirect()
                ->route('master.karyawan.index')
                ->with('success', 'Massive import data karyawan berhasil dilakukan.');
        } catch (\Exception $e) {
            return redirect()
                ->route('master.karyawan.index')
                ->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
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

        $pdf = Pdf::loadView('master_data.karyawan.pdf_report', compact('karyawanList'))
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
}
