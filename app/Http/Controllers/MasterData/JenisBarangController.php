<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreJenisBarangRequest;
use App\Http\Requests\MasterData\UpdateJenisBarangRequest;
use App\Services\MasterData\JenisBarangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JenisBarangController extends Controller
{
    public function __construct(
        protected JenisBarangService $jenisBarangService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $jenisList = $this->jenisBarangService->getAllPaginated($perPage, $search);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data jenis barang berhasil diambil.',
                'data'    => $jenisList,
            ]);
        }

        return view('master_data.jenis.index', compact('jenisList', 'search'));
    }

    public function store(StoreJenisBarangRequest $request): RedirectResponse|JsonResponse
    {
        $jenis = $this->jenisBarangService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data jenis barang berhasil disimpan.',
                'data'    => $jenis,
            ], 201);
        }

        return redirect()
            ->route('master.jenis.index')
            ->with('success', 'Data jenis barang berhasil disimpan.');
    }

    public function update(UpdateJenisBarangRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $jenis = $this->jenisBarangService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data jenis barang berhasil diperbarui.',
                'data'    => $jenis,
            ]);
        }

        return redirect()
            ->route('master.jenis.index')
            ->with('success', 'Data jenis barang berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->jenisBarangService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data jenis barang berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('master.jenis.index')
            ->with('success', 'Data jenis barang berhasil dinonaktifkan.');
    }
}
