<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreBarangRequest;
use App\Http\Requests\MasterData\UpdateBarangRequest;
use App\Models\MasterData\MstJenisBarang;
use App\Models\MasterData\MstSatuan;
use App\Services\Common\CodeGeneratorService;
use App\Services\MasterData\BarangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarangController extends Controller
{
    /**
     * Injeksi dependensi BarangService & CodeGeneratorService via Constructor.
     */
    public function __construct(
        protected BarangService $barangService,
        protected CodeGeneratorService $codeGeneratorService
    ) {}

    /**
     * Menampilkan daftar barang (Paginated).
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $barangs = $this->barangService->getAllPaginated($perPage, $search);
        $jenisBarangList = MstJenisBarang::active()->orderBy('jenis_barang_nm')->get();
        $satuanList = MstSatuan::active()->orderBy('satuan_nm')->get();
        $nextBarangCode = $this->codeGeneratorService->generateBarangCode();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master barang berhasil diambil.',
                'data'    => $barangs,
                'next_code' => $nextBarangCode,
            ]);
        }

        return view('master_data.barang.index', compact('barangs', 'search', 'jenisBarangList', 'satuanList', 'nextBarangCode'));
    }

    /**
     * Menampilkan form penambahan data barang baru.
     */
    public function create(): View
    {
        $jenisBarangList = MstJenisBarang::active()->orderBy('jenis_barang_nm')->get();
        $satuanList = MstSatuan::active()->orderBy('satuan_nm')->get();
        $nextBarangCode = $this->codeGeneratorService->generateBarangCode();

        return view('master_data.barang.create', compact('jenisBarangList', 'satuanList', 'nextBarangCode'));
    }

    /**
     * Menyimpan data barang baru menggunakan StoreBarangRequest.
     */
    public function store(StoreBarangRequest $request): RedirectResponse|JsonResponse
    {
        $barang = $this->barangService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data barang baru berhasil disimpan.',
                'data'    => $barang,
            ], 201);
        }

        return redirect()
            ->route('master.barang.index')
            ->with('success', 'Data barang baru berhasil disimpan.');
    }

    /**
     * Menampilkan detail satu data barang.
     */
    public function show(Request $request, int $id): View|JsonResponse
    {
        $barang = $this->barangService->getById($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Detail data barang berhasil diambil.',
                'data'    => $barang,
            ]);
        }

        return view('master_data.barang.show', compact('barang'));
    }

    /**
     * Menampilkan form edit data barang.
     */
    public function edit(int $id): View
    {
        $barang = $this->barangService->getById($id);
        $jenisBarangList = MstJenisBarang::active()->orderBy('jenis_barang_nm')->get();
        $satuanList = MstSatuan::active()->orderBy('satuan_nm')->get();

        return view('master_data.barang.edit', compact('barang', 'jenisBarangList', 'satuanList'));
    }

    /**
     * Memperbarui data barang menggunakan UpdateBarangRequest.
     */
    public function update(UpdateBarangRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $barang = $this->barangService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data barang berhasil diperbarui.',
                'data'    => $barang,
            ]);
        }

        return redirect()
            ->route('master.barang.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    /**
     * Menghapus (Soft Delete) data barang via Service Layer.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->barangService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data barang berhasil dinonaktifkan (soft delete).',
            ]);
        }

        return redirect()
            ->route('master.barang.index')
            ->with('success', 'Data barang berhasil dinonaktifkan (soft delete).');
    }
}
