<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreLiniProduksiRequest;
use App\Http\Requests\MasterData\UpdateLiniProduksiRequest;
use App\Services\MasterData\LiniProduksiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiniProduksiController extends Controller
{
    /**
     * Injeksi dependensi LiniProduksiService via Constructor.
     */
    public function __construct(
        protected LiniProduksiService $liniService
    ) {}

    /**
     * Menampilkan daftar master lini produksi / tujuan.
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $liniList = $this->liniService->getAllPaginated($perPage, $search);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master lini produksi berhasil diambil.',
                'data'    => $liniList,
            ]);
        }

        return view('master_data.lini_produksi.index', compact('liniList', 'search'));
    }

    /**
     * Menampilkan form input lini produksi baru.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->canCreateMasterLiniProduksi(), 403, 'Anda tidak memiliki hak akses untuk menambah data lini produksi.');

        return view('master_data.lini_produksi.create');
    }

    /**
     * Menyimpan data lini produksi baru.
     */
    public function store(StoreLiniProduksiRequest $request): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canCreateMasterLiniProduksi(), 403, 'Anda tidak memiliki hak akses untuk menambah data lini produksi.');

        $lini = $this->liniService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data lini produksi baru berhasil disimpan.',
                'data'    => $lini,
            ], 201);
        }

        return redirect()
            ->route('master.lini_produksi.index')
            ->with('success', 'Data lini produksi baru berhasil disimpan.');
    }

    /**
     * Menampilkan form edit data lini produksi.
     */
    public function edit(int $id): View
    {
        abort_unless(auth()->user()->canEditMasterLiniProduksi(), 403, 'Anda tidak memiliki hak akses untuk mengedit data lini produksi.');

        $lini = $this->liniService->getById($id);

        return view('master_data.lini_produksi.edit', compact('lini'));
    }

    /**
     * Memperbarui data lini produksi.
     */
    public function update(UpdateLiniProduksiRequest $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canEditMasterLiniProduksi(), 403, 'Anda tidak memiliki hak akses untuk mengedit data lini produksi.');

        $lini = $this->liniService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data lini produksi berhasil diperbarui.',
                'data'    => $lini,
            ]);
        }

        return redirect()
            ->route('master.lini_produksi.index')
            ->with('success', 'Data lini produksi berhasil diperbarui.');
    }

    /**
     * Menghapus (Soft Delete) data lini produksi.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->canDeleteMasterLiniProduksi(), 403, 'Anda tidak memiliki hak akses untuk menonaktifkan data lini produksi.');

        $this->liniService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data lini produksi berhasil dinonaktifkan.',
                'data'    => null,
            ]);
        }

        return redirect()
            ->route('master.lini_produksi.index')
            ->with('success', 'Data lini produksi berhasil dinonaktifkan.');
    }
}
