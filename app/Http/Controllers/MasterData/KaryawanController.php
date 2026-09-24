<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreKaryawanRequest;
use App\Http\Requests\MasterData\UpdateKaryawanRequest;
use App\Services\Common\CodeGeneratorService;
use App\Services\MasterData\KaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    public function __construct(
        protected KaryawanService $karyawanService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $departemen = $request->input('departemen');

        $karyawanList = $this->karyawanService->getAllPaginated($perPage, $search, $departemen);
        $nextNik = $this->codeGenerator->generate('mst_karyawan', 'nik', 'KRY-');

        $departemenList = [
            'PRODUKSI'   => 'Departemen Produksi',
            'GUDANG'     => 'Departemen Gudang & Logistik',
            'PURCHASING' => 'Departemen Pengadaan (Purchasing)',
            'FINANCE'    => 'Departemen Keuangan & Kasir',
            'QC'         => 'Departemen Quality Control (QC)',
            'HRD'        => 'Departemen SDM / HRD',
            'MANAJEMEN'  => 'Manajemen / Direksi',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Data karyawan berhasil diambil.',
                'data'      => $karyawanList,
                'next_nik'  => $nextNik,
            ]);
        }

        return view('master_data.karyawan.index', compact('karyawanList', 'search', 'departemen', 'nextNik', 'departemenList'));
    }

    public function store(StoreKaryawanRequest $request): RedirectResponse|JsonResponse
    {
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

    public function update(UpdateKaryawanRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $karyawan = $this->karyawanService->update($id, $request->validated());

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

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->karyawanService->delete($id);

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
}
