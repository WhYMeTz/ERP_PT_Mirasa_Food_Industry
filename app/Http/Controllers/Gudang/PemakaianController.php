<?php

namespace App\Http\Controllers\Gudang;

use App\Exports\Gudang\PemakaianExport;
use App\Exports\Gudang\PemakaianTemplate;
use App\Imports\Gudang\PemakaianImport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gudang\StorePemakaianRequest;
use App\Models\Gudang\DatPakaiDtl;
use App\Models\Gudang\DatPakaiHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstLiniProduksi;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\PemakaianService;
use App\Services\Gudang\StokService;
use App\Services\Produksi\BomService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $kategori = $request->input('kategori');
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
            $dataList = $this->pemakaianService->getBarangKeluarListPaginated($perPage, $search, $effectiveGudang, $tujuan, $startDate, $endDate, $kategori);
        }

        $ringkasan = $this->pemakaianService->getRingkasanPengeluaran($effectiveGudang, $tujuan, $startDate, $endDate);

        // 4 Metrik Operasional Harian
        $today = now()->toDateString();
        $rawCountsQuery = DatPakaiHdr::where('deleted_st', false);
        if (is_array($effectiveGudang)) {
            $rawCountsQuery->whereIn('gudang_id', $effectiveGudang);
        } elseif ($effectiveGudang !== null) {
            $rawCountsQuery->where('gudang_id', $effectiveGudang);
        }

        $rawCounts = $rawCountsQuery->selectRaw("
            COUNT(*) as total_dokumen,
            COUNT(CASE WHEN pakai_tgl = ? THEN 1 END) as today_dokumen
        ", [$today])->first();

        $kpiCounts = [
            'total'        => (int) ($rawCounts->total_dokumen ?? 0),
            'today'        => (int) ($rawCounts->today_dokumen ?? 0),
            'singkong_qty' => (float) ($ringkasan['singkong_qty'] ?? 0),
            'total_biaya'  => (float) ($ringkasan['grand_total_nilai'] ?? 0),
        ];

        // Ambil daftar tujuan dari Master Lini Produksi aktif + operasional khusus + historis
        $activeLini = MstLiniProduksi::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('lini_id', 'asc')
            ->pluck('lini_nm')
            ->toArray();

        $opsiKhusus = [
            'SAMPLE LAB / QC',
            'AFKIR ULANG',
            'PACKING / REPACKING',
            'BUFFER STOK LANTAI PRODUKSI',
        ];

        $historicalTujuan = DatPakaiHdr::where('deleted_st', false)
            ->whereNotNull('tujuan_pemakaian')
            ->distinct()
            ->pluck('tujuan_pemakaian')
            ->toArray();

        $tujuanOptions = array_values(array_unique(array_merge($activeLini, $opsiKhusus, $historicalTujuan)));

        if ($request->wantsJson()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Data barang keluar berhasil diambil.',
                'ringkasan' => $ringkasan,
                'kpis'      => $kpiCounts,
                'data'      => $dataList,
            ]);
        }

        return view('gudang.pemakaian.index', compact(
            'dataList', 'gudangList', 'search', 'gudangId', 'viewType',
            'tujuan', 'kategori', 'startDate', 'endDate', 'ringkasan', 'tujuanOptions', 'kpiCounts'
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

        // Hanya ambil barang peruntukan produksi (Bahan Baku, Penolong, Kemasan; bukan Barang Jadi FG / WIP)
        $barangList = MstBarang::active()
            ->bahanProduksi()
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get()
            ->map(function ($b) {
                $cd = strtoupper($b->jenisBarang->jenis_barang_cd ?? '');
                $nm = strtoupper($b->barang_nm ?? '');

                if ($cd === 'BB' || $cd === 'RAW' || str_contains($nm, 'SINGKONG') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR')) {
                    $b->kategori_kelompok = 'BAHAN_BAKU';
                    $b->kategori_label = '🌾 Bahan Baku';
                } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'PLASTIK') || str_contains($nm, 'ROLL') || str_contains($nm, 'LAKBAN') || str_contains($nm, 'RAFIA') || str_contains($nm, 'SARUNG TANGAN') || $cd === 'PACK') {
                    $b->kategori_kelompok = 'KEMASAN';
                    $b->kategori_label = '📦 Kemasan & Packaging';
                } else {
                    $b->kategori_kelompok = 'BAHAN_PENOLONG';
                    $b->kategori_label = '🧂 Bahan Penolong & Bumbu';
                }

                return $b;
            });

        $autoNo = $this->codeGenerator->generatePakaiNo();

        // Ambil daftar master lini produksi aktif dan keperluan operasional khusus
        $liniList = MstLiniProduksi::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('lini_id', 'asc')
            ->get();

        $opsiKhusus = [
            'SAMPLE LAB / QC',
            'AFKIR ULANG',
            'PACKING / REPACKING',
            'BUFFER STOK LANTAI PRODUKSI',
        ];

        $bomList = $this->bomService->getAllActive();

        return view('gudang.pemakaian.create', compact(
            'gudangList',
            'barangList',
            'userGudangId',
            'autoNo',
            'liniList',
            'opsiKhusus',
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

    /**
     * Export Rekapitulasi Barang Keluar (Pemakaian) ke format PDF (A4 Landscape).
     */
    public function exportRekapPdf(Request $request): Response
    {
        $search    = $request->input('search');
        $tujuan    = $request->input('tujuan');
        $kategori  = $request->input('kategori');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $gudangId  = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        $user = Auth::user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];

        if ($gudangId !== null) {
            $effectiveGudang = ($user && !$user->canAccessGudang($gudangId)) ? $allowedGudangIds : $gudangId;
        } else {
            $effectiveGudang = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
        }

        $query = DatPakaiDtl::with(['header.gudang', 'barang.jenisBarang', 'barang.satuanDasar'])
            ->whereHas('header', function ($q) use ($effectiveGudang, $tujuan, $startDate, $endDate) {
                $q->where('deleted_st', false);
                if (is_array($effectiveGudang)) {
                    $q->whereIn('gudang_id', $effectiveGudang);
                } elseif ($effectiveGudang !== null) {
                    $q->where('gudang_id', $effectiveGudang);
                }
                if (!empty($tujuan)) {
                    $q->where('tujuan_pemakaian', $tujuan);
                }
                if (!empty($startDate)) {
                    $q->whereDate('pakai_tgl', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $q->whereDate('pakai_tgl', '<=', $endDate);
                }
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhere('keterangan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', fn($bq) => $bq->where('barang_nm', 'ILIKE', "%{$search}%")->orWhere('barang_cd', 'ILIKE', "%{$search}%"));
            });
        }

        $items = $query->orderBy('pakaidtl_id', 'desc')->get();

        if ($items->isNotEmpty()) {
            $gudangIds = $items->pluck('header.gudang_id')->filter()->unique()->toArray();
            $barangIds = $items->pluck('barang_id')->filter()->unique()->toArray();

            $totalStokMap = DatStokBatch::whereIn('gudang_id', $gudangIds)
                ->whereIn('barang_id', $barangIds)
                ->where('deleted_st', false)
                ->selectRaw('gudang_id, barang_id, SUM(sisa_qty) as total_sisa')
                ->groupBy('gudang_id', 'barang_id')
                ->get()
                ->keyBy(fn($r) => $r->gudang_id . '_' . $r->barang_id);

            foreach ($items as $dtl) {
                $gId = $dtl->header?->gudang_id;
                $bId = $dtl->barang_id;
                $dtl->sisa_gudang_qty = (float) ($totalStokMap->get($gId . '_' . $bId)?->total_sisa ?? 0);
            }
        }

        $gudangNm = $gudangId ? MstGudang::find($gudangId)?->gudang_nm : null;

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('gudang.pemakaian.pdf_rekap', [
            'items'      => $items,
            'gudangNm'   => $gudangNm,
            'search'     => $search,
            'tujuan'     => $tujuan,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => Auth::user()?->karyawan?->karyawan_nm ?? (Auth::user()?->nama_lengkap ?? 'Staff Gudang'),
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $fileName = 'Rekap_Barang_Keluar_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * Export Rekapitulasi Barang Keluar (Pemakaian) ke format Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $search    = $request->input('search');
        $tujuan    = $request->input('tujuan');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $gudangId  = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        $user = Auth::user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];

        if ($gudangId !== null) {
            $effectiveGudang = ($user && !$user->canAccessGudang($gudangId)) ? $allowedGudangIds : $gudangId;
        } else {
            $effectiveGudang = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
        }

        $query = DatPakaiDtl::with(['header.gudang', 'barang.jenisBarang', 'barang.satuanDasar'])
            ->whereHas('header', function ($q) use ($effectiveGudang, $tujuan, $startDate, $endDate) {
                $q->where('deleted_st', false);
                if (is_array($effectiveGudang)) {
                    $q->whereIn('gudang_id', $effectiveGudang);
                } elseif ($effectiveGudang !== null) {
                    $q->where('gudang_id', $effectiveGudang);
                }
                if (!empty($tujuan)) {
                    $q->where('tujuan_pemakaian', $tujuan);
                }
                if (!empty($startDate)) {
                    $q->whereDate('pakai_tgl', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $q->whereDate('pakai_tgl', '<=', $endDate);
                }
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhere('keterangan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', fn($bq) => $bq->where('barang_nm', 'ILIKE', "%{$search}%")->orWhere('barang_cd', 'ILIKE', "%{$search}%"));
            });
        }

        $items = $query->orderBy('pakaidtl_id', 'desc')->get();

        if ($items->isNotEmpty()) {
            $gudangIds = $items->pluck('header.gudang_id')->filter()->unique()->toArray();
            $barangIds = $items->pluck('barang_id')->filter()->unique()->toArray();

            $totalStokMap = DatStokBatch::whereIn('gudang_id', $gudangIds)
                ->whereIn('barang_id', $barangIds)
                ->where('deleted_st', false)
                ->selectRaw('gudang_id, barang_id, SUM(sisa_qty) as total_sisa')
                ->groupBy('gudang_id', 'barang_id')
                ->get()
                ->keyBy(fn($r) => $r->gudang_id . '_' . $r->barang_id);

            foreach ($items as $dtl) {
                $gId = $dtl->header?->gudang_id;
                $bId = $dtl->barang_id;
                $dtl->sisa_gudang_qty = (float) ($totalStokMap->get($gId . '_' . $bId)?->total_sisa ?? 0);
            }
        }

        $gudangNm  = $gudangId ? MstGudang::find($gudangId)?->gudang_nm : null;
        $printedBy = Auth::user()?->karyawan?->karyawan_nm ?? (Auth::user()?->nama_lengkap ?? 'Staff Gudang');
        $printedAt = now()->translatedFormat('d F Y H:i');

        $export = new PemakaianExport($items, $gudangNm, $search, $tujuan, $printedBy, $printedAt);
        return $export->download('Rekap_Barang_Keluar_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Download template Excel kosong untuk Import Pemakaian Bahan.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return (new PemakaianTemplate())->download();
    }

    /**
     * Proses upload file Excel dan import data Pemakaian Bahan.
     */
    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:5120',
            ],
        ], [
            'import_file.required' => 'File Excel wajib dipilih.',
            'import_file.mimes'    => 'Format file harus .xlsx atau .xls.',
            'import_file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        try {
            $importer = app(PemakaianImport::class);
            $importer->import($request->file('import_file'));

            $msg = "Import selesai: {$importer->successCount} dokumen berhasil, {$importer->errorCount} dokumen gagal.";

            if ($importer->errorCount > 0) {
                $errors = collect($importer->results)
                    ->where('status', 'error')
                    ->pluck('message')
                    ->implode(' | ');
                return redirect()->route('gudang.pemakaian.index')
                    ->with('warning', $msg . ' Kesalahan: ' . $errors);
            }

            return redirect()->route('gudang.pemakaian.index')
                ->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->route('gudang.pemakaian.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }
}
