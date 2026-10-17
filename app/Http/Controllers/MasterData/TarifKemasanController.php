<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstTarifKemasan;
use App\Services\Produksi\TarifKemasanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TarifKemasanController extends Controller
{
    public function __construct(
        protected TarifKemasanService $tarifService
    ) {}

    /**
     * Tampilkan halaman indeks Master Data Standar Harga Kemasan & Packaging
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $query = MstTarifKemasan::active();

        if (!empty($search)) {
            $query->search($search);
        }

        if (!empty($kategori)) {
            $query->kategori($kategori);
        }

        $tarifs = $query->orderBy('kategori_bahan')
            ->orderBy('tarif_kemasan_id')
            ->get();

        $stats = [
            'total_komponen' => MstTarifKemasan::active()->count(),
            'total_karton'   => MstTarifKemasan::active()->kategori('KARTON')->count(),
            'total_roll'     => MstTarifKemasan::active()->kategori('ROLL')->count(),
            'total_lakban'   => MstTarifKemasan::active()->kategori('LAKBAN')->count(),
            'total_bumbu'    => MstTarifKemasan::active()->kategori('BUMBU')->count(),
            'total_wip'      => MstTarifKemasan::active()->kategori('WIP_CURAH')->count(),
        ];

        return view('master_data.tarif_kemasan.index', compact('tarifs', 'stats', 'search', 'kategori'));
    }

    /**
     * Perbarui tarif tunggal dari modal Master Data
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nilai_tarif'          => 'required|numeric|min:0',
            'nilai_tarif_sekunder' => 'nullable|numeric|min:0',
            'berlaku_sejak'        => 'nullable|date',
            'keterangan'           => 'nullable|string|max:255',
        ]);

        $userId = Auth::user()?->username ?? 'SYSTEM';
        $tarif = $this->tarifService->updateTarif($id, $validated, $userId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Standar harga {$tarif->nama_tarif} berhasil diperbarui.",
                'data'    => $tarif,
            ]);
        }

        return redirect()->route('master.tarif_kemasan.index')
            ->with('toast_success', "Standar harga {$tarif->nama_tarif} berhasil diperbarui.");
    }

    /**
     * Perbarui massal beberapa tarif acuan sekaligus
     */
    public function quickUpdate(Request $request): RedirectResponse|JsonResponse
    {
        $rates = $request->input('rates', []);
        $userId = Auth::user()?->username ?? 'SYSTEM';

        $updated = $this->tarifService->quickUpdateRates($rates, $userId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Standar harga acuan kemasan berhasil disinkronkan.',
                'data'    => $updated,
            ]);
        }

        return redirect()->route('master.tarif_kemasan.index')
            ->with('toast_success', 'Standar harga acuan kemasan berhasil disinkronkan.');
    }

    /**
     * Endpoint API JSON untuk integrasi frontend / kalkulator kemasan
     */
    public function apiRates(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->tarifService->getDaftarHargaPackaging(),
            'map'     => $this->tarifService->getTarifMap(),
        ]);
    }
}
