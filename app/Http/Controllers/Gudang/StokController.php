<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstJenisBarang;
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
        $viewType = $request->input('view', 'split');
        $defaultPerPage = ($viewType === 'split') ? 100 : 20;
        $perPage = (int) $request->input('per_page', $defaultPerPage);

        $search = $request->input('search');
        $status = $request->input('status'); // 'tersedia', 'menipis', 'habis', 'aman', or null for all
        $jenisBarangId = $request->input('jenis_barang_id') ? (int) $request->input('jenis_barang_id') : null;

        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $jenisBarangList = MstJenisBarang::active()->orderBy('jenis_barang_nm')->get();

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
            $stokList = $this->stokService->getMonitoringStok($perPage, $effectiveGudang, $search, $status, $jenisBarangId);
            $summaryList = null;
            $dataForJson = $stokList;
        } else {
            $summaryList = $this->stokService->getStokSummaryByBarang($perPage, $effectiveGudang, $search, $status, $jenisBarangId);
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

    /**
     * Halaman Rekapitulasi Stok Komoditas & Valuasi Persediaan (Blueprint 10 - Rekap Stok).
     * Mendukung Snapshot Harian (Daily Balance Sheet seperti Excel PT Mirasa) & Split Tab:
     * - Tab 'hasil_produksi': Stok Gudang Jadi (FG & WIP)
     * - Tab 'bahan': Stok Bahan Baku & Penolong (RAW, BP, PACK, SUPP)
     */
    public function rekap(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $jenisBarangList = MstJenisBarang::active()->orderBy('jenis_barang_nm')->get();

        $requestedGudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        // Validasi hak akses gudang:
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

        $tab = $request->input('tab', 'hasil_produksi');
        if (!in_array($tab, ['hasil_produksi', 'bahan'])) {
            $tab = 'hasil_produksi';
        }
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        
        $dateObj = \Carbon\Carbon::parse($tanggal);
        $prevDate = $dateObj->copy()->subDay()->toDateString();
        $nextDate = $dateObj->copy()->addDay()->toDateString();
        $isToday = $dateObj->isToday();

        $dayNameIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ][$dateObj->format('l')] ?? $dateObj->format('l');
        $monthNameIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$dateObj->month] ?? $dateObj->format('F');
        $formattedDateIndo = $dayNameIndo . ', ' . $dateObj->format('d') . ' ' . $monthNameIndo . ' ' . $dateObj->format('Y');

        $filters = [
            'tab'             => $tab,
            'tanggal'         => $tanggal,
            'gudang_id'       => $gudangId,
            'search'          => $request->input('search'),
            'status'          => $request->input('status', 'all'), // 'all', 'bergerak', 'aman', 'rendah', 'habis'
            'jenis_cd'        => $request->input('jenis_cd', 'all'), // 'all', 'FG', 'WIP', 'RAW', 'BP', 'PACK', 'SUPP'
            'jenis_barang_id' => $request->input('jenis_barang_id') ? (int) $request->input('jenis_barang_id') : null,
        ];

        $perPage = (int) $request->input('per_page', 50);
        $rekapList = $this->stokService->getRekapStok($filters, $perPage, $effectiveGudang);
        $totals = $this->stokService->getRekapStokTotals($filters, $effectiveGudang);

        $selectedGudang = $gudangId ? MstGudang::find($gudangId) : null;

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'tab'    => $tab,
                'tanggal'=> $tanggal,
                'totals' => $totals,
                'data'   => $rekapList,
            ]);
        }

        return view('gudang.stok.rekap', compact(
            'rekapList',
            'totals',
            'gudangList',
            'selectedGudang',
            'jenisBarangList',
            'filters',
            'gudangId',
            'tab',
            'tanggal',
            'prevDate',
            'nextDate',
            'isToday',
            'formattedDateIndo'
        ));
    }

    /**
     * Export Excel Rekap Stok (Blueprint 10)
     */
    public function exportRekapExcel(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];
        $requestedGudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        if ($requestedGudangId !== null && $user && !$user->canAccessGudang($requestedGudangId)) {
            $effectiveGudang = $allowedGudangIds;
            $gudangId = null;
        } else {
            $effectiveGudang = $requestedGudangId ?: (($user && $user->isSuperAdmin()) ? null : $allowedGudangIds);
            $gudangId = $requestedGudangId;
        }

        $tab = $request->input('tab', 'hasil_produksi');
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        $filters = [
            'tab'             => $tab,
            'tanggal'         => $tanggal,
            'gudang_id'       => $gudangId,
            'search'          => $request->input('search'),
            'status'          => $request->input('status', 'all'),
            'jenis_cd'        => $request->input('jenis_cd', 'all'),
            'jenis_barang_id' => $request->input('jenis_barang_id') ? (int) $request->input('jenis_barang_id') : null,
        ];

        // Ambil semua data (tanpa paginasi untuk export)
        $items = $this->stokService->getRekapStok($filters, 10000, $effectiveGudang)->items();

        $selectedGudang = $gudangId ? MstGudang::find($gudangId) : null;
        $gudangNm = $selectedGudang ? $selectedGudang->gudang_nm : 'Semua Gudang';

        $exporter = new \App\Exports\Gudang\RekapStokExport(
            $items,
            $gudangNm,
            $filters['status'],
            $filters['search'],
            $user ? $user->name : 'Staff Gudang',
            \Carbon\Carbon::now()->format('d/m/Y H:i')
        );

        $tabPrefix = $tab === 'hasil_produksi' ? 'Gudang_Jadi' : 'Bahan_Baku';
        $filename = 'Rekap_Stok_' . $tabPrefix . '_' . $tanggal . '_' . date('His') . '.xlsx';
        return $exporter->download($filename);
    }

    /**
     * Export PDF Rekap Stok (Blueprint 10)
     */
    public function exportRekapPdf(Request $request): \Illuminate\Http\Response
    {
        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];
        $requestedGudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        if ($requestedGudangId !== null && $user && !$user->canAccessGudang($requestedGudangId)) {
            $effectiveGudang = $allowedGudangIds;
            $gudangId = null;
        } else {
            $effectiveGudang = $requestedGudangId ?: (($user && $user->isSuperAdmin()) ? null : $allowedGudangIds);
            $gudangId = $requestedGudangId;
        }

        $tab = $request->input('tab', 'hasil_produksi');
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        $filters = [
            'tab'             => $tab,
            'tanggal'         => $tanggal,
            'gudang_id'       => $gudangId,
            'search'          => $request->input('search'),
            'status'          => $request->input('status', 'all'),
            'jenis_cd'        => $request->input('jenis_cd', 'all'),
            'jenis_barang_id' => $request->input('jenis_barang_id') ? (int) $request->input('jenis_barang_id') : null,
        ];

        $items = $this->stokService->getRekapStok($filters, 10000, $effectiveGudang)->items();
        $totals = $this->stokService->getRekapStokTotals($filters, $effectiveGudang);
        $selectedGudang = $gudangId ? MstGudang::find($gudangId) : null;
        $gudangNm = $selectedGudang ? $selectedGudang->gudang_nm : 'Semua Gudang';

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gudang.stok.pdf_rekap', [
            'items'          => $items,
            'totals'         => $totals,
            'selectedGudang' => $selectedGudang,
            'gudangNm'       => $gudangNm,
            'filters'        => $filters,
            'logoBase64'     => $logoBase64,
            'printedBy'      => $user ? ($user->karyawan?->karyawan_nm ?? ($user->name ?? 'Staff Gudang')) : 'Staff Gudang',
            'printedAt'      => \Carbon\Carbon::now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $tabPrefix = $tab === 'hasil_produksi' ? 'Gudang_Jadi' : 'Bahan_Baku';
        $filename = 'Rekap_Stok_' . $tabPrefix . '_' . $tanggal . '_' . date('His') . '.pdf';
        return $pdf->stream($filename);
    }
}
