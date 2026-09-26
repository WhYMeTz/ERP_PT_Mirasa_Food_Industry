<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produksi\StoreBomRequest;
use App\Http\Requests\Produksi\UpdateBomRequest;
use App\Models\MasterData\MstBarang;
use App\Services\Common\CodeGeneratorService;
use App\Services\Produksi\BomService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BomController extends Controller
{
    public function __construct(
        protected BomService $bomService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Tampilan daftar master resep (Bill of Materials).
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);

        $bomList = $this->bomService->getAllPaginated($perPage, $search);

        return view('master_data.resep.index', compact('bomList', 'search'));
    }

    /**
     * Form tambah formula resep (BOM) baru.
     */
    public function create(): View
    {
        $autoNo = $this->codeGenerator->generate('mst_bom_hdr', 'bom_no', 'BOM-');

        // Barang output/target jadi (FG & WIP)
        $barangJadiList = MstBarang::active()
            ->with(['satuanDasar', 'jenisBarang'])
            ->whereHas('jenisBarang', function ($q) {
                $q->whereIn('jenis_barang_cd', ['FG', 'WIP', 'JUAL']);
            })
            ->orWhereDoesntHave('jenisBarang')
            ->orderBy('barang_nm')
            ->get();

        if ($barangJadiList->isEmpty()) {
            $barangJadiList = MstBarang::active()->with(['satuanDasar', 'jenisBarang'])->orderBy('barang_nm')->get();
        }

        // Seluruh bahan baku / penolong (Mentah, Bumbu, Kemasan, dll)
        $bahanBakuList = MstBarang::active()
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get();

        return view('master_data.resep.create', compact('autoNo', 'barangJadiList', 'bahanBakuList'));
    }

    /**
     * Simpan formula resep baru ke database.
     */
    public function store(StoreBomRequest $request): RedirectResponse
    {
        try {
            $bom = $this->bomService->store($request->validated());

            return redirect()->route('master.resep.index')
                ->with('success', "Formula Resep {$bom->bom_no} ({$bom->bom_nm}) berhasil disimpan.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilan detail satu formula resep BOM.
     */
    public function show(int $id): View
    {
        $bom = $this->bomService->getById($id);

        return view('master_data.resep.show', compact('bom'));
    }

    /**
     * Form edit formula resep BOM.
     */
    public function edit(int $id): View
    {
        $bom = $this->bomService->getById($id);

        $barangJadiList = MstBarang::active()
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get();

        $bahanBakuList = MstBarang::active()
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get();

        return view('master_data.resep.edit', compact('bom', 'barangJadiList', 'bahanBakuList'));
    }

    /**
     * Perbarui formula resep BOM.
     */
    public function update(UpdateBomRequest $request, int $id): RedirectResponse
    {
        try {
            $bom = $this->bomService->update($id, $request->validated());

            return redirect()->route('master.resep.index')
                ->with('success', "Formula Resep {$bom->bom_no} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus formula resep (Soft Delete).
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->bomService->delete($id);

            return redirect()->route('master.resep.index')
                ->with('success', "Formula Resep berhasil dihapus.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
