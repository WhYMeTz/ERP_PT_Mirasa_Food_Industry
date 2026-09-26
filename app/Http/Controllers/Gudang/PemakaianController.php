<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gudang\StorePemakaianRequest;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\PemakaianService;
use App\Services\Gudang\StokService;
use App\Services\Produksi\BomService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PemakaianController extends Controller
{
    public function __construct(
        protected PemakaianService $pemakaianService,
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator,
        protected BomService $bomService
    ) {}

    /**
     * Tampilan daftar pengeluaran / pemakaian barang.
     * Mendukung tampilan format Sheet "Barang Keluar" per-item ataupun per-dokumen.
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $search = $request->input('search');
        $viewType = $request->input('view', 'item'); // 'item' (Excel view) atau 'header'
        $tujuan = $request->input('tujuan');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $user = Auth::user();
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

        if ($viewType === 'header') {
            $dataList = $this->pemakaianService->getAllPaginated($perPage, $search, $effectiveGudang, $tujuan, $startDate, $endDate);
        } else {
            $dataList = $this->pemakaianService->getBarangKeluarListPaginated($perPage, $search, $effectiveGudang, $tujuan, $startDate, $endDate);
        }

        $ringkasan = $this->pemakaianService->getRingkasanPengeluaran($effectiveGudang, $tujuan, $startDate, $endDate);

        $tujuanOptions = [
            'PRODUKSI IFM',
            'PRODUKSI PING-PING',
            'PRODUKSI BWF',
            'PRODUKSI ASIN BARCO',
            'PACKING EKSPOR',
            'PACKING JUMBO',
            'PACKING XX 500',
            'PACKING XX2000',
            'SEASONING XX 2000',
            'AFKIR ULANG',
            'SAMPLE LAB / QC',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Data barang keluar berhasil diambil.',
                'ringkasan' => $ringkasan,
                'data'      => $dataList,
            ]);
        }

        return view('gudang.pemakaian.index', compact(
            'dataList', 'gudangList', 'search', 'gudangId', 'viewType',
            'tujuan', 'startDate', 'endDate', 'ringkasan', 'tujuanOptions'
        ));
    }

    /**
     * Form pencatatan pengeluaran / pemakaian barang ke produksi / packing.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $userGudangId = ($gudangList->count() === 1 && !$user?->isSuperAdmin()) ? $gudangList->first()->gudang_id : null;

        $barangList = MstBarang::active()
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get();

        $autoNo = $this->codeGenerator->generatePakaiNo();

        // Opsi tujuan pemakaian standar operasional pabrik Mirasa
        $tujuanOptions = [
            'PRODUKSI IFM',
            'PRODUKSI PING-PING',
            'PRODUKSI BWF',
            'PRODUKSI ASIN BARCO',
            'PACKING EKSPOR',
            'PACKING JUMBO',
            'PACKING XX 500',
            'PACKING XX2000',
            'SEASONING XX 2000',
            'BAHAN XX2000',
            'GMP',
            'AFKIR ULANG',
            'SAMPLE LAB / QC',
        ];

        $bomList = $this->bomService->getAllActive();

        return view('gudang.pemakaian.create', compact(
            'gudangList',
            'barangList',
            'userGudangId',
            'autoNo',
            'tujuanOptions',
            'bomList'
        ));
    }

    /**
     * Simpan transaksi pengeluaran barang.
     */
    public function store(StorePemakaianRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $header = $this->pemakaianService->store($request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Pengeluaran barang {$header->pakai_no} berhasil diproses.",
                    'data'    => $header,
                ], 201);
            }

            return redirect()->route('gudang.pemakaian.index')
                ->with('success', "Pengeluaran barang {$header->pakai_no} berhasil diproses dan stok telah dipotong.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilan detail dokumen pengeluaran barang.
     */
    public function show(int $id): View
    {
        $pakai = $this->pemakaianService->getById($id);
        return view('gudang.pemakaian.show', compact('pakai'));
    }

    /**
     * Endpoint AJAX untuk mengambil daftar batch aktif (sisa > 0) diurutkan FIFO (paling lama dibeli).
     * Batch yang sudah habis (sisa_qty = 0) dieliminasi total dari dropdown.
     */
    public function getBatches(Request $request): JsonResponse
    {
        $gudangId = (int) $request->input('gudang_id');
        $barangId = (int) $request->input('barang_id');

        $user = Auth::user();
        if ($user && !$user->canAccessGudang($gudangId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses ke gudang ini.',
                'batches' => [],
            ], 403);
        }

        $batches = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('sisa_qty', '>', 0)
            ->where('deleted_st', false)
            ->orderByRaw('expired_tgl ASC NULLS LAST')
            ->orderBy('created_at', 'asc')
            ->orderBy('stok_id', 'asc')
            ->get(['stok_id', 'batch_no', 'sisa_qty', 'harga_satuan', 'expired_tgl', 'created_at']);

        $formattedBatches = $batches->values()->map(function ($b, $index) {
            return [
                'stok_id'      => $b->stok_id,
                'batch_no'     => $b->batch_no,
                'sisa_qty'     => (float) $b->sisa_qty,
                'harga_satuan' => (float) $b->harga_satuan,
                'expired_tgl'  => $b->expired_tgl ? \Carbon\Carbon::parse($b->expired_tgl)->format('d/m/Y') : null,
                'tgl_terima'   => $b->created_at ? $b->created_at->format('d/m/Y') : '-',
                'is_fifo_top'  => $index === 0,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'batches' => $formattedBatches,
        ]);
    }

    /**
     * Endpoint AJAX untuk kalkulasi dan alokasi kebutuhan bahan baku resep produksi (BOM)
     * secara otomatis ke Batch Stok Fisik Gudang berdasarkan prinsip FIFO / FEFO.
     */
    public function alokasiResepFifo(Request $request): JsonResponse
    {
        $gudangId = (int) $request->input('gudang_id');
        $bomId = (int) $request->input('bom_id');
        $targetQty = (float) $request->input('target_qty', 100);

        if (!$gudangId || !$bomId || $targetQty <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Parameter gudang_id, bom_id, dan target_qty (> 0) wajib diisi.',
            ], 422);
        }

        $user = Auth::user();
        if ($user && !$user->canAccessGudang($gudangId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses ke gudang ini.',
            ], 403);
        }

        try {
            $alokasi = $this->bomService->alokasiBahanResepFifo($gudangId, $bomId, $targetQty);

            return response()->json([
                'status'  => 'success',
                'message' => "Kebutuhan resep {$alokasi['bom_nm']} berhasil dihitung & dialokasikan ke batch FIFO.",
                'data'    => $alokasi,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

