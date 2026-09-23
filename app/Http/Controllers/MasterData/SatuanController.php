<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreSatuanRequest;
use App\Http\Requests\MasterData\UpdateSatuanRequest;
use App\Services\MasterData\SatuanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SatuanController extends Controller
{
    /**
     * Injeksi dependensi SatuanService via Constructor.
     */
    public function __construct(
        protected SatuanService $satuanService
    ) {}

    /**
     * Menampilkan daftar master satuan.
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $satuans = $this->satuanService->getAllPaginated($perPage, $search);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master satuan berhasil diambil.',
                'data'    => $satuans,
            ]);
        }

        return view('master_data.satuan.index', compact('satuans', 'search'));
    }

    /**
     * Menampilkan form input satuan baru.
     */
    public function create(): View
    {
        return view('master_data.satuan.create');
    }

    /**
     * Menyimpan data satuan baru via StoreSatuanRequest.
     */
    public function store(StoreSatuanRequest $request): RedirectResponse|JsonResponse
    {
        $satuan = $this->satuanService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data satuan baru berhasil disimpan.',
                'data'    => $satuan,
            ], 201);
        }

        return redirect()
            ->route('master.satuan.index')
            ->with('success', 'Data satuan baru berhasil disimpan.');
    }

    /**
     * Menampilkan form edit data satuan.
     */
    public function edit(int $id): View
    {
        $satuan = $this->satuanService->getById($id);

        return view('master_data.satuan.edit', compact('satuan'));
    }

    /**
     * Memperbarui data satuan via UpdateSatuanRequest.
     */
    public function update(UpdateSatuanRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $satuan = $this->satuanService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data satuan berhasil diperbarui.',
                'data'    => $satuan,
            ]);
        }

        return redirect()
            ->route('master.satuan.index')
            ->with('success', 'Data satuan berhasil diperbarui.');
    }

    /**
     * Menghapus (Soft Delete) data satuan.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->satuanService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data satuan berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('master.satuan.index')
            ->with('success', 'Data satuan berhasil dinonaktifkan.');
    }
}
