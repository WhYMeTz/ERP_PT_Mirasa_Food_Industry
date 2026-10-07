<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstJenisBarang;
use App\Services\Gudang\StokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProduksiStokController extends Controller
{
    public function __construct(
        protected StokService $stokService
    ) {}

    /**
     * Dashboard monitoring stok hasil produksi: Olahan Setengah Jadi (WIP) & Barang Jadi (FG).
     */
    public function index(Request $request): View|JsonResponse
    {
        $viewType = $request->input('view', 'split');
        $defaultPerPage = ($viewType === 'split') ? 100 : 20;
        $perPage = (int) $request->input('per_page', $defaultPerPage);

        $search = $request->input('search');
        $status = $request->input('status');
        $jenisBarangId = $request->input('jenis_barang_id') ? (int) $request->input('jenis_barang_id') : null;

        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];
        $gudangList = $user ? $user->getAllowedGudangList() : collect();

        // Hanya jenis barang WIP dan FG
        $jenisBarangList = MstJenisBarang::active()
            ->whereIn('jenis_barang_cd', ['WIP', 'FG'])
            ->orderBy('jenis_barang_nm')
            ->get();

        $requestedGudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        if ($requestedGudangId !== null) {
            if ($user && !$user->canAccessGudang($requestedGudangId)) {
                $effectiveGudang = $allowedGudangIds;
                $gudangId = null;
            } else {
                $effectiveGudang = $requestedGudangId;
                $gudangId = $requestedGudangId;
            }
        } else {
            $effectiveGudang = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
            $gudangId = null;
        }

        // Scope produksi: hanya hitung & tampilkan WIP dan FG
        $kpiMetrics = $this->stokService->getStokKpiMetrics($effectiveGudang, 'produksi');

        if ($viewType === 'batch') {
            $stokList = $this->stokService->getMonitoringStok($perPage, $effectiveGudang, $search, $status, $jenisBarangId, 'produksi');
            $summaryList = null;
            $dataForJson = $stokList;
        } else {
            $summaryList = $this->stokService->getStokSummaryByBarang($perPage, $effectiveGudang, $search, $status, $jenisBarangId, 'produksi');
            $stokList = null;
            $dataForJson = $summaryList;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status'      => 'success',
                'view'        => $viewType,
                'kpi_metrics' => $kpiMetrics,
                'data'        => $dataForJson,
            ]);
        }

        return view('produksi.stok.index', compact(
            'summaryList',
            'stokList',
            'gudangList',
            'jenisBarangList',
            'jenisBarangId',
            'search',
            'gudangId',
            'status',
            'viewType',
            'kpiMetrics'
        ));
    }

    /**
     * Tampilan audit kartu stok (Stock Ledger) khusus hasil produksi (WIP & FG).
     */
    public function ledger(Request $request): View|JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = (int) $request->input('per_page', 25);

        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];
        $gudangList = $user ? $user->getAllowedGudangList() : collect();

        $requestedGudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        if ($requestedGudangId !== null) {
            if ($user && !$user->canAccessGudang($requestedGudangId)) {
                $effectiveGudang = $allowedGudangIds;
                $gudangId = null;
            } else {
                $effectiveGudang = $requestedGudangId;
                $gudangId = $requestedGudangId;
            }
        } else {
            $effectiveGudang = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
            $gudangId = null;
        }

        // Hanya barang hasil produksi (WIP & FG)
        $barangList = MstBarang::active()
            ->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['WIP', 'FG']))
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get();

        $requestedBarangId = $request->input('barang_id') ? (int) $request->input('barang_id') : null;
        $selectedBarang = ($requestedBarangId ? $barangList->firstWhere('barang_id', $requestedBarangId) : null)
            ?? $barangList->first();
        $barangId = $selectedBarang?->barang_id;

        $ledgerList = $selectedBarang
            ? $this->stokService->getKartuStok($selectedBarang->barang_id, $effectiveGudang, $startDate, $endDate, $perPage)
            : null;

        $totalStok = $selectedBarang
            ? $this->stokService->getTotalStock($selectedBarang->barang_id, $effectiveGudang)
            : 0;

        if ($request->wantsJson()) {
            return response()->json([
                'status'     => 'success',
                'barang'     => $selectedBarang,
                'total_stok' => $totalStok,
                'data'       => $ledgerList,
            ]);
        }

        return view('produksi.stok.ledger', compact(
            'ledgerList',
            'selectedBarang',
            'barangList',
            'gudangList',
            'barangId',
            'gudangId',
            'startDate',
            'endDate',
            'totalStok'
        ));
    }

    /**
     * Endpoint API JSON untuk melihat isi dan penelusuran (traceability) batch WIP / FG.
     */
    public function batchDetail(Request $request, string $batchNo): JsonResponse
    {
        $barangId = $request->input('barang_id') ? (int) $request->input('barang_id') : null;
        $data = $this->stokService->getBatchTraceability($batchNo, $barangId);
        return response()->json($data);
    }
}
