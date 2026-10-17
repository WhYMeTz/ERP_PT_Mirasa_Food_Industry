<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreMesinRequest;
use App\Http\Requests\MasterData\UpdateMesinRequest;
use App\Services\MasterData\MesinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MesinController extends Controller
{
    /**
     * Injeksi dependensi MesinService via Constructor.
     */
    public function __construct(
        protected MesinService $mesinService
    ) {}

    /**
     * Menampilkan daftar master mesin operasional pabrik (Produksi & Packaging).
     */
    public function index(Request $request): View|JsonResponse
    {
        abort_unless(auth()->user()->canAccessMesin(), 403, 'Anda tidak memiliki hak akses untuk melihat data master mesin.');

        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $kategori = $request->input('kategori', 'SEMUA');

        $mesinList = $this->mesinService->getAllPaginated($perPage, $search, $kategori);
        $summary = $this->mesinService->getSummaryCounts();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master mesin berhasil dimuat.',
                'summary' => $summary,
                'data'    => $mesinList,
            ]);
        }

        return view('master_data.mesin.index', compact('mesinList', 'summary', 'search', 'kategori'));
    }

    /**
     * Form input mesin baru (dialihkan ke index dengan modal create).
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('master.mesin.index');
    }

    /**
     * Mengambil detail satu data mesin (JSON / View).
     */
    public function show(int $id): JsonResponse
    {
        $mesin = $this->mesinService->getById($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail data mesin berhasil diambil.',
            'data'    => $mesin,
        ]);
    }

    /**
     * Menyimpan data mesin baru.
     */
    public function store(StoreMesinRequest $request): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canCreateMasterMesin(), 403, 'Anda tidak memiliki hak akses untuk menambah data mesin.');

        $mesin = $this->mesinService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Mesin '{$mesin->mesin_nm}' berhasil ditambahkan ke sistem.",
                'data'    => $mesin,
            ], 201);
        }

        return redirect()
            ->route('master.mesin.index', ['kategori' => $mesin->kategori_mesin])
            ->with('success', "Mesin '{$mesin->mesin_nm}' ({$mesin->mesin_cd}) berhasil ditambahkan.");
    }

    /**
     * Mengambil data untuk modal edit.
     */
    public function edit(int $id): JsonResponse
    {
        abort_unless(auth()->user()->canEditMasterMesin(), 403, 'Anda tidak memiliki hak akses untuk mengubah data mesin.');

        $mesin = $this->mesinService->getById($id);

        return response()->json([
            'status' => 'success',
            'data'   => $mesin,
        ]);
    }

    /**
     * Memperbarui data mesin yang sudah ada.
     */
    public function update(UpdateMesinRequest $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canEditMasterMesin(), 403, 'Anda tidak memiliki hak akses untuk mengubah data mesin.');

        $mesin = $this->mesinService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Perubahan data mesin '{$mesin->mesin_nm}' berhasil disimpan.",
                'data'    => $mesin,
            ]);
        }

        return redirect()
            ->route('master.mesin.index', ['kategori' => $mesin->kategori_mesin])
            ->with('success', "Data mesin '{$mesin->mesin_nm}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / nonaktif mesin.
     */
    public function toggleStatus(Request $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canEditMasterMesin(), 403, 'Anda tidak memiliki hak akses untuk mengubah status mesin.');

        $mesin = $this->mesinService->toggleStatus($id);
        $statusText = $mesin->active_st ? 'diaktifkan kembali' : 'dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'status'    => 'success',
                'message'   => "Mesin '{$mesin->mesin_nm}' berhasil {$statusText}.",
                'active_st' => $mesin->active_st,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', "Status mesin '{$mesin->mesin_nm}' berhasil {$statusText}.");
    }

    /**
     * Menghapus (Soft Delete) data mesin.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canDeleteMasterMesin(), 403, 'Anda tidak memiliki hak akses untuk menonaktifkan / menghapus mesin.');

        $this->mesinService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data mesin berhasil dinonaktifkan dari sistem.',
                'data'    => null,
            ]);
        }

        return redirect()
            ->route('master.mesin.index')
            ->with('success', 'Data mesin berhasil dinonaktifkan dari sistem.');
    }
}
