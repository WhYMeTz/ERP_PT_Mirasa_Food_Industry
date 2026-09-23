<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreJenisSupplierRequest;
use App\Http\Requests\MasterData\UpdateJenisSupplierRequest;
use App\Services\MasterData\JenisSupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JenisSupplierController extends Controller
{
    public function __construct(
        protected JenisSupplierService $jenisSupplierService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $jenisSupplierList = $this->jenisSupplierService->getAllPaginated($perPage, $search);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data jenis supplier berhasil diambil.',
                'data'    => $jenisSupplierList,
            ]);
        }

        return view('master_data.jenis_supplier.index', compact('jenisSupplierList', 'search'));
    }

    public function store(StoreJenisSupplierRequest $request): RedirectResponse|JsonResponse
    {
        $jenisSupplier = $this->jenisSupplierService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data jenis supplier berhasil disimpan.',
                'data'    => $jenisSupplier,
            ], 201);
        }

        return redirect()
            ->route('master.jenis_supplier.index')
            ->with('success', 'Data jenis supplier berhasil disimpan.');
    }

    public function update(UpdateJenisSupplierRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $jenisSupplier = $this->jenisSupplierService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data jenis supplier berhasil diperbarui.',
                'data'    => $jenisSupplier,
            ]);
        }

        return redirect()
            ->route('master.jenis_supplier.index')
            ->with('success', 'Data jenis supplier berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->jenisSupplierService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data jenis supplier berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('master.jenis_supplier.index')
            ->with('success', 'Data jenis supplier berhasil dinonaktifkan.');
    }
}
