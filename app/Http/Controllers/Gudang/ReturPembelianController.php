<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gudang\StoreReturRequest;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatReturHdr;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstSupplier;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\PoService;
use App\Services\Gudang\ReturPembelianService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturPembelianController extends Controller
{
    public function __construct(
        protected ReturPembelianService $returService,
        protected PoService $poService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $search = $request->input('search');
        $supplierId = $request->input('supplier_id') ? (int) $request->input('supplier_id') : null;
        $barangId = $request->input('barang_id') ? (int) $request->input('barang_id') : null;
        $tindakan = $request->input('tindakan');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $viewType = $request->input('view', 'item'); // 'item' (Excel Grid) atau 'header' (Per Dokumen)
        $kategori = $request->input('kategori'); // BAHAN_BAKU, BAHAN_PENOLONG, KEMASAN

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

        if ($viewType === 'header') {
            $dataList = $this->returService->getAllPaginated(
                $perPage,
                $search,
                $supplierId,
                $effectiveGudang,
                $tindakan,
                $startDate,
                $endDate,
                $barangId,
                $kategori
            );
        } else {
            $dataList = $this->returService->getItemListPaginated(
                $perPage,
                $search,
                $supplierId,
                $effectiveGudang,
                $tindakan,
                $startDate,
                $endDate,
                $barangId,
                $kategori
            );
        }

        // Hitung Ringkasan Kategori
        $ringkasan = $this->returService->getRingkasan($effectiveGudang, $supplierId, $startDate, $endDate);

        // Hitung KPI
        $today = now()->toDateString();
        $kpiQuery = DatReturHdr::where('deleted_st', false);
        if (is_array($effectiveGudang)) {
            $kpiQuery->whereIn('gudang_id', $effectiveGudang);
        } elseif ($effectiveGudang !== null) {
            $kpiQuery->where('gudang_id', $effectiveGudang);
        }

        $kpiRaw = $kpiQuery->selectRaw("
            COUNT(*) as total_retur,
            COUNT(CASE WHEN retur_tgl = ? THEN 1 END) as today_retur,
            COUNT(CASE WHEN tindakan_cd = 'REPLACE' THEN 1 END) as replace_retur,
            COUNT(CASE WHEN tindakan_cd = 'CREDIT_NOTE' THEN 1 END) as credit_retur,
            COALESCE(SUM(total_nominal), 0) as total_nominal
        ", [$today])->first();

        $kpis = [
            'total'         => (int) ($kpiRaw->total_retur ?? 0),
            'today'         => (int) ($kpiRaw->today_retur ?? 0),
            'replace'       => (int) ($kpiRaw->replace_retur ?? 0),
            'credit_note'   => (int) ($kpiRaw->credit_retur ?? 0),
            'total_nominal' => (float) ($kpiRaw->total_nominal ?? 0),
        ];

        $suppliers = MstSupplier::active()->orderBy('supplier_nm')->get();
        $barangList = MstBarang::active()->bahanBaku()->orderBy('barang_nm')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Data retur pembelian berhasil diambil.',
                'data'      => $dataList,
                'kpis'      => $kpis,
                'ringkasan' => $ringkasan,
            ]);
        }

        return view('gudang.retur.index', compact(
            'dataList',
            'gudangList',
            'suppliers',
            'barangList',
            'search',
            'supplierId',
            'barangId',
            'gudangId',
            'tindakan',
            'startDate',
            'endDate',
            'viewType',
            'kategori',
            'ringkasan',
            'kpis'
        ));
    }

    public function create(Request $request): View
    {
        $selectedPoId = $request->query('po_id');
        $selectedPo = null;

        if ($selectedPoId) {
            $selectedPo = DatPoHdr::with(['supplier', 'details.barang.satuanDasar'])->find((int) $selectedPoId);
        }

        $user = auth()->user();
        $userGudangId = ($user && !$user->isSuperAdmin()) ? $user->gudang_id : null;
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $supplierList = MstSupplier::active()->orderBy('supplier_nm')->get();
        $barangList = MstBarang::active()
            ->bahanBaku()
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get()
            ->map(function ($b) {
                $cd = strtoupper((string) ($b->jenisBarang?->jenis_barang_cd ?? ''));
                $nm = strtoupper((string) $b->barang_nm);
                if (in_array($cd, ['BB', 'RAW']) || str_contains($nm, 'SINGKONG') || str_contains($nm, 'UBI')) {
                    $b->kategori_kelompok = 'BAHAN_BAKU';
                } elseif ($cd === 'PACK' || str_contains($nm, 'KARTON') || str_contains($nm, 'PLASTIK') || str_contains($nm, 'LAKBAN')) {
                    $b->kategori_kelompok = 'KEMASAN';
                } else {
                    $b->kategori_kelompok = 'BAHAN_PENOLONG';
                }
                return $b;
            });
        
        // PO yang pernah menerima barang (COMPLETED atau PARTIAL)
        $poList = DatPoHdr::with(['supplier', 'details.barang.satuanDasar'])
            ->whereIn('status_cd', ['PARTIAL', 'COMPLETED', 'CLOSED'])
            ->where('deleted_st', false)
            ->orderBy('po_tgl', 'desc')
            ->get();

        $nextReturNo = $this->codeGenerator->generateReturNo();

        return view('gudang.retur.create', compact(
            'gudangList',
            'userGudangId',
            'supplierList',
            'barangList',
            'poList',
            'selectedPo',
            'nextReturNo'
        ));
    }

    public function store(StoreReturRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $retur = $this->returService->store($request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Retur pembelian {$retur->retur_no} berhasil diproses dan stok telah dikeluarkan.",
                    'data'    => $retur,
                ], 201);
            }

            return redirect()
                ->route('gudang.retur.show', $retur->retur_id)
                ->with('success', "Dokumen retur {$retur->retur_no} berhasil dibuat. Stok fisik telah dikurangi.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(int $id): View|JsonResponse
    {
        $retur = $this->returService->getById($id);

        if (request()->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Detail retur pembelian berhasil diambil.',
                'data'    => $retur,
            ]);
        }

        return view('gudang.retur.show', compact('retur'));
    }

    /**
     * Endpoint API untuk mengambil daftar nomor batch stok fisik yang tersedia di gudang terpilih
     */
    public function getBatches(Request $request): JsonResponse
    {
        $gudangId = (int) $request->input('gudang_id');
        $barangId = $request->input('barang_id') ? (int) $request->input('barang_id') : null;

        if (!$gudangId) {
            return response()->json(['status' => 'error', 'message' => 'Gudang wajib dipilih.'], 422);
        }

        $batches = $this->returService->getAvailableBatches($gudangId, $barangId);

        return response()->json([
            'status' => 'success',
            'data'   => $batches->map(function ($b) {
                return [
                    'stok_id'      => $b->stok_id,
                    'barang_id'    => $b->barang_id,
                    'barang_nm'    => $b->barang?->barang_nm,
                    'barang_cd'    => $b->barang?->barang_cd,
                    'satuan'       => $b->barang?->satuanDasar?->satuan_nm ?? 'Satuan',
                    'batch_no'     => $b->batch_no,
                    'sisa_qty'     => (float) $b->sisa_qty,
                    'harga_satuan' => (float) $b->harga_satuan,
                    'expired_tgl'  => $b->expired_tgl ? $b->expired_tgl->format('d/m/Y') : null,
                ];
            }),
        ]);
    }

    /**
     * Endpoint API untuk mengambil detail barang & batch yang sudah diterima dari dokumen PO terpilih.
     * Digunakan untuk auto-populate baris formulir retur secara otomatis.
     */
    public function getPoData(int $poId): JsonResponse
    {
        $po = DatPoHdr::with([
            'supplier',
            'details.barang.satuanDasar',
            'penerimaan.details'
        ])->findOrFail($poId);

        $items = [];
        foreach ($po->details as $dtl) {
            // Hanya muat item yang kuantitas terimanya sudah lebih dari 0
            if ((float) $dtl->terima_qty > 0) {
                // Kumpulkan batch yang pernah diterima khusus untuk item barang ini
                $receivedBatches = [];
                if ($po->penerimaan) {
                    foreach ($po->penerimaan as $terima) {
                        foreach ($terima->details as $tdtl) {
                            if ($tdtl->podtl_id == $dtl->podtl_id || $tdtl->barang_id == $dtl->barang_id) {
                                $receivedBatches[] = $tdtl->batch_no;
                            }
                        }
                    }
                }

                $items[] = [
                    'podtl_id'      => $dtl->podtl_id,
                    'barang_id'     => $dtl->barang_id,
                    'barang_nm'     => $dtl->barang?->barang_nm,
                    'barang_cd'     => $dtl->barang?->barang_cd,
                    'satuan'        => $dtl->barang?->satuanDasar?->satuan_nm ?? 'Satuan',
                    'terima_qty'    => (float) $dtl->terima_qty,
                    'harga_nominal' => (float) $dtl->harga_nominal,
                    'batches'       => array_values(array_unique($receivedBatches)),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'po_id'       => $po->po_id,
                'po_no'       => $po->po_no,
                'supplier_id' => $po->supplier_id,
                'supplier_nm' => $po->supplier?->supplier_nm,
                'gudang_id'   => $po->gudang_id,
                'items'       => $items,
            ],
        ]);
    }
}

