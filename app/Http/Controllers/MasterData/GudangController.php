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
        abort_unless($request->user()->canAccessPerusahaan(), 403, 'Anda tidak memiliki hak akses untuk melihat master perusahaan.');

        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $gudangs = $this->gudangService->getAllPaginated($perPage, $search);
        $nextGudangCode = $this->codeGeneratorService->generateGudangCode();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master perusahaan berhasil diambil.',
                'data'    => $gudangs,
                'next_code' => $nextGudangCode,
            ]);
        }

        return view('master_data.perusahaan.index', compact('gudangs', 'search', 'nextGudangCode'));
    }

    public function store(StoreGudangRequest $request): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canCreateMasterPerusahaan(), 403, 'Anda tidak memiliki hak akses untuk menambah entitas perusahaan.');

        $gudang = $this->gudangService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data entitas perusahaan berhasil disimpan.',
                'data'    => $gudang,
            ], 201);
        }

        return redirect()
            ->route('master.perusahaan.index')
            ->with('success', 'Data entitas perusahaan berhasil disimpan.');
    }

    public function update(UpdateGudangRequest $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canEditMasterPerusahaan(), 403, 'Anda tidak memiliki hak akses untuk mengedit entitas perusahaan.');

        $gudang = $this->gudangService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data entitas perusahaan berhasil diperbarui.',
                'data'    => $gudang,
            ]);
        }

        return redirect()
            ->route('master.perusahaan.index')
            ->with('success', 'Data entitas perusahaan berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canDeleteMasterPerusahaan(), 403, 'Anda tidak memiliki hak akses untuk menonaktifkan entitas perusahaan.');

        $this->gudangService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Entitas perusahaan berhasil dinonaktifkan.',
                'data'    => null,
            ]);
        }

        return redirect()
            ->route('master.perusahaan.index')
            ->with('success', 'Entitas perusahaan berhasil dinonaktifkan.');
    }
}
