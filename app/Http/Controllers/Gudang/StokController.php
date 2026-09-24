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
     * Tampilan dashboard monitoring stok gudang per nomor batch & tanggal kadaluarsa.
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $search = $request->input('search');
        $status = $request->input('status'); // 'tersedia', 'habis', or null for all
        $gudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        $stokList = $this->stokService->getMonitoringStok($perPage, $gudangId, $search, $status);
        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data monitoring stok berhasil diambil.',
                'data'    => $stokList,
            ]);
        }

        return view('gudang.stok.index', compact('stokList', 'gudangList', 'search', 'gudangId', 'status'));
    }

    /**
     * Tampilan audit kartu stok (Stock Ledger) per barang dan mutasi IN / OUT.
     */
    public function ledger(Request $request): View|JsonResponse
    {
        $barangId = (int) $request->input('barang_id', 1);
        $gudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = (int) $request->input('per_page', 25);

        $selectedBarang = MstBarang::with(['satuanDasar', 'jenisBarang'])->find($barangId)
            ?? MstBarang::with(['satuanDasar', 'jenisBarang'])->first();

        $barangList = MstBarang::active()->orderBy('barang_nm')->get();
        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();

        $ledgerList = $selectedBarang
            ? $this->stokService->getKartuStok($selectedBarang->barang_id, $gudangId, $startDate, $endDate, $perPage)
            : null;

        $totalStok = $selectedBarang
            ? $this->stokService->getTotalStock($selectedBarang->barang_id, $gudangId)
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
