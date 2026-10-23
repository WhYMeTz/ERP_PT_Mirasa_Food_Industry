<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Keuangan\StoreCoaRequest;
use App\Http\Requests\Keuangan\UpdateCoaRequest;
use App\Services\Keuangan\CoaService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CoaController extends Controller
{
    public function __construct(
        protected CoaService $coaService
    ) {}

    /**
     * Halaman Indeks Bagan Akun (Chart of Accounts / COA)
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'      => $request->input('search'),
            'kategori'    => $request->input('kategori', 'ALL'),
            'is_kas_bank' => $request->input('is_kas_bank'),
        ];

        $accounts = $this->coaService->getAll($filters);
        $summaryCounts = $this->coaService->getSummaryCounts();

        return view('keuangan.coa.index', compact('accounts', 'summaryCounts', 'filters'));
    }

    /**
     * Simpan Akun Baru
     */
    public function store(StoreCoaRequest $request): RedirectResponse
    {
        try {
            $coa = $this->coaService->store($request->validated(), Auth::user());

            return redirect()->route('keuangan.coa.index', ['kategori' => $request->input('kategori_akun')])
                ->with('success', "Akun {$coa->kode_akun} - {$coa->nama_akun} berhasil ditambahkan ke Bagan Akun (COA).");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan akun: ' . $e->getMessage());
        }
    }

    /**
     * Perbarui Akun
     */
    public function update(UpdateCoaRequest $request, int $id): RedirectResponse
    {
        try {
            $coa = $this->coaService->update($id, $request->validated(), Auth::user());

            return redirect()->route('keuangan.coa.index', ['kategori' => $coa->kategori_akun])
                ->with('success', "Akun {$coa->kode_akun} - {$coa->nama_akun} berhasil diperbarui.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui akun: ' . $e->getMessage());
        }
    }

    /**
     * Hapus (Nonaktifkan) Akun
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->coaService->delete($id, Auth::user());

            return redirect()->route('keuangan.coa.index')
                ->with('success', "Akun berhasil dihapus dari Bagan Akun (COA).");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }
}
