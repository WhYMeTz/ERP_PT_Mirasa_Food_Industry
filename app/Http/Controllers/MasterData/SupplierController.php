<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreSupplierRequest;
use App\Http\Requests\MasterData\UpdateSupplierRequest;
use App\Services\Common\CodeGeneratorService;
use App\Services\MasterData\JenisSupplierService;
use App\Services\MasterData\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        protected SupplierService $supplierService,
        protected JenisSupplierService $jenisSupplierService,
        protected CodeGeneratorService $codeGeneratorService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $suppliers = $this->supplierService->getAllPaginated($perPage, $search);
        $jenisSupplierList = $this->jenisSupplierService->getAllActive();
        $nextSupplierCode = $this->codeGeneratorService->generateSupplierCode();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master supplier berhasil diambil.',
                'data'    => $suppliers,
                'next_code' => $nextSupplierCode,
            ]);
        }

        return view('master_data.supplier.index', compact('suppliers', 'search', 'jenisSupplierList', 'nextSupplierCode'));
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|JsonResponse
    {
        $supplier = $this->supplierService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data supplier berhasil disimpan.',
                'data'    => $supplier,
            ], 201);
        }

        return redirect()
            ->route('master.supplier.index')
            ->with('success', 'Data supplier berhasil disimpan.');
    }

    public function update(UpdateSupplierRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $supplier = $this->supplierService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data supplier berhasil diperbarui.',
                'data'    => $supplier,
            ]);
        }

        return redirect()
            ->route('master.supplier.index')
            ->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->supplierService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data supplier berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('master.supplier.index')
            ->with('success', 'Data supplier berhasil dinonaktifkan.');
    }
}
