<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstTarifProduksi;
use App\Services\Produksi\TarifProduksiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TarifProduksiController extends Controller
{
    public function __construct(
        protected TarifProduksiService $tarifService
    ) {}

    /**
     * Tampilkan halaman indeks Master Data Standar Tarif Produksi & FOH
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $query = MstTarifProduksi::active();

        if (!empty($search)) {
            $query->search($search);
        }

        if (!empty($kategori)) {
            $query->kategori($kategori);
        }

        $tarifs = $query->orderBy('kategori')
            ->orderBy('tarif_id')
            ->get();

        $stats = [
            'total_komponen' => MstTarifProduksi::active()->count(),
            'total_foh'      => MstTarifProduksi::active()->kategori('FOH')->count(),
            'total_energi'   => MstTarifProduksi::active()->kategori('ENERGI')->count(),
            'total_tk'       => MstTarifProduksi::active()->kategori('TENAGA_KERJA')->count(),
        ];

        return view('master_data.tarif_produksi.index', compact('tarifs', 'stats', 'search', 'kategori'));
    }

    /**
     * Perbarui tarif tunggal dari modal Master Data
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nilai_tarif' => 'required|numeric|min:0',
            'keterangan'  => 'nullable|string|max:255',
        ]);

        $userId = Auth::user()?->username ?? Auth::user()?->name ?? 'SYSTEM';
        $updated = $this->tarifService->updateTarif($id, $validated, (string) $userId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Tarif {$updated->nama_tarif} berhasil diperbarui menjadi " . number_format($updated->nilai_tarif, 2, ',', '.'),
                'data'    => $updated,
            ]);
        }

        return redirect()->route('master.tarif_produksi.index')
            ->with('toast_success', "Standar tarif {$updated->nama_tarif} berhasil disimpan!");
    }

    /**
     * Endpoint Cepat (Quick Update) dari Form Produksi / Modal Pintas AJAX
     */
    public function quickUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rates' => 'required|array',
            'rates.*' => 'nullable|numeric|min:0',
        ]);

        $userId = Auth::user()?->username ?? Auth::user()?->name ?? 'SYSTEM';
        $updatedMap = $this->tarifService->quickUpdate($validated['rates'], (string) $userId);
        $fohRates = $this->tarifService->getFohRates();
        $energiLaborRates = $this->tarifService->getEnergiLaborRates();

        return response()->json([
            'success'   => true,
            'message'   => 'Standar pengali FOH & tarif produksi berhasil diperbarui secara real-time!',
            'rates_map' => $updatedMap,
            'foh_rates' => $fohRates,
            'energi_tk' => $energiLaborRates,
        ]);
    }

    /**
     * API JSON untuk mengambil semua rate aktif (untuk modal/JS)
     */
    public function apiRates(): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'tarif_map' => $this->tarifService->getTarifMap(),
            'foh_rates' => $this->tarifService->getFohRates(),
            'energi_tk' => $this->tarifService->getEnergiLaborRates(),
            'all'       => $this->tarifService->getAllTarif(),
        ]);
    }
}
