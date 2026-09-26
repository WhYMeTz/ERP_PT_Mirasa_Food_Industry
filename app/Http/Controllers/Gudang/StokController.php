<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Services\Gudang\StokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StokController extends Controller
{
    public function __construct(
        protected StokService $stokService
    ) {}

    /**
     * Tampilan dashboard monitoring stok gudang (Ringkasan per-Barang 2-Level & Detail per-Batch).
     */
    public function index(Request $request): View|JsonResponse
    {
        $viewType = $request->input('view', 'split'); // 'split' (Master-Detail), 'summary' (Tabel Ringkas), 'batch' (Sheet)
        $defaultPerPage = ($viewType === 'split') ? 100 : 20;
        $perPage = (int) $request->input('per_page', $defaultPerPage);

        $search = $request->input('search');
        $status = $request->input('status'); // 'tersedia', 'menipis', 'habis', 'aman', or null for all

        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];
        $gudangList = $user ? $user->getAllowedGudangList() : collect();

        $requestedGudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        // Validasi hak akses gudang:
        if ($requestedGudangId !== null) {
            if ($user && !$user->canAccessGudang($requestedGudangId)) {
                // Jika tidak punya akses ke gudang yang diminta, fallback ke daftar gudang yang diizinkan
                $effectiveGudang = $allowedGudangIds;
                $gudangId = null;
            } else {
                $effectiveGudang = $requestedGudangId;
                $gudangId = $requestedGudangId;
            }
        } else {
            // Pilihan "Semua Gudang":
            // Superadmin dapat melihat keseluruhan gudang.
            // Admin Gudang HANYA melihat gudang-gudang yang di-assign padanya ($allowedGudangIds).
            $effectiveGudang = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
            $gudangId = null;
        }

        $kpiMetrics = $this->stokService->getStokKpiMetrics($effectiveGudang);

        if ($viewType === 'batch') {
            $stokList = $this->stokService->getMonitoringStok($perPage, $effectiveGudang, $search, $status);
            $summaryList = null;
            $dataForJson = $stokList;
        } else {
            $summaryList = $this->stokService->getStokSummaryByBarang($perPage, $effectiveGudang, $search, $status);
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

        return view('gudang.stok.index', compact(
            'summaryList',
            'stokList',
            'gudangList',
            'search',
            'gudangId',
            'status',
            'viewType',
            'kpiMetrics'
        ));
    }


    /**
     * Tampilan audit kartu stok (Stock Ledger) per barang dan mutasi IN / OUT.
     */
    public function ledger(Request $request): View|JsonResponse
    {
        $barangId = (int) $request->input('barang_id', 1);
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

        $selectedBarang = MstBarang::with(['satuanDasar', 'jenisBarang'])->find($barangId)
            ?? MstBarang::with(['satuanDasar', 'jenisBarang'])->first();

        $barangList = MstBarang::active()->orderBy('barang_nm')->get();

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

        return view('gudang.stok.ledger', compact(
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
}
