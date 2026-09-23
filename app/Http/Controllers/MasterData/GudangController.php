<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreGudangRequest;
use App\Http\Requests\MasterData\UpdateGudangRequest;
use App\Services\Common\CodeGeneratorService;
use App\Services\MasterData\GudangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GudangController extends Controller
{
    public function __construct(
        protected GudangService $gudangService,
        protected CodeGeneratorService $codeGeneratorService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $gudangs = $this->gudangService->getAllPaginated($perPage, $search);
        $nextGudangCode = $this->codeGeneratorService->generateGudangCode();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master gudang berhasil diambil.',
                'data'    => $gudangs,
                'next_code' => $nextGudangCode,
            ]);
        }

        return view('master_data.gudang.index', compact('gudangs', 'search', 'nextGudangCode'));
    }

    public function store(StoreGudangRequest $request): RedirectResponse|JsonResponse
    {
        $gudang = $this->gudangService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data gudang berhasil disimpan.',
                'data'    => $gudang,
            ], 201);
        }

        return redirect()
            ->route('master.gudang.index')
            ->with('success', 'Data gudang berhasil disimpan.');
    }

    public function update(UpdateGudangRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $gudang = $this->gudangService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data gudang berhasil diperbarui.',
                'data'    => $gudang,
            ]);
        }

        return redirect()
            ->route('master.gudang.index')
            ->with('success', 'Data gudang berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->gudangService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data gudang berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('master.gudang.index')
            ->with('success', 'Data gudang berhasil dinonaktifkan.');
    }
}
