<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gudang\StoreTerimaBarangRequest;
use App\Models\Gudang\DatTerimaHdr;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\PoService;
use App\Services\Gudang\TerimaBarangService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\Gudang\TerimaBarangExport;
use App\Exports\Gudang\TerimaBarangTemplate;
use App\Imports\Gudang\TerimaBarangImport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TerimaBarangController extends Controller
{
    public function __construct(
        protected TerimaBarangService $terimaService,
        protected PoService $poService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $search = $request->input('search');
        $viewType = $request->input('view', 'item'); // 'item' (Excel format) atau 'header'

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
            $dataList = $this->terimaService->getAllPaginated($perPage, $search, $effectiveGudang);
        } else {
            $dataList = $this->terimaService->getBarangMasukListPaginated($perPage, $search, $effectiveGudang);
        }

        // Hitung 4 Metrik Operasional Harian
        $today = now()->toDateString();
        $rawCountsQuery = DatTerimaHdr::where('deleted_st', false);
        if (is_array($effectiveGudang)) {
            $rawCountsQuery->whereIn('gudang_id', $effectiveGudang);
        } elseif ($effectiveGudang !== null) {
            $rawCountsQuery->where('gudang_id', $effectiveGudang);
        }

        $rawCounts = $rawCountsQuery->selectRaw("
            COUNT(*) as total_grn,
            COUNT(CASE WHEN terima_tgl = ? THEN 1 END) as today_grn,
            COUNT(CASE WHEN po_id IS NOT NULL THEN 1 END) as po_grn,
            COUNT(CASE WHEN po_id IS NULL THEN 1 END) as direct_grn
        ", [$today])->first();

        $kpiCounts = [
            'total'  => (int) ($rawCounts->total_grn ?? 0),
            'today'  => (int) ($rawCounts->today_grn ?? 0),
            'po'     => (int) ($rawCounts->po_grn ?? 0),
            'direct' => (int) ($rawCounts->direct_grn ?? 0),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data penerimaan barang berhasil diambil.',
                'data'    => $dataList,
                'kpis'    => $kpiCounts,
            ]);
        }

        return view('gudang.terima.index', compact('dataList', 'gudangList', 'search', 'gudangId', 'viewType', 'kpiCounts'));
    }

    public function create(Request $request): View
    {
        $selectedPoId = $request->query('po_id');
        $selectedPo = null;

        if ($selectedPoId) {
            $selectedPo = $this->poService->getById((int) $selectedPoId);
        }

        $supplierList = MstSupplier::active()->with('jenisSupplier')->orderBy('supplier_nm')->get();
        $user = auth()->user();
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $allowedGudangIds = ($user && $user->isSuperAdmin()) ? null : ($user ? $user->getAllowedGudangIds() : []);
        $barangList = MstBarang::active()->bahanBaku()->with(['satuanDasar', 'jenisBarang'])->orderBy('barang_nm')->get();
        $openPoList = $this->poService->getOpenPoList(null, $allowedGudangIds);
        $nextTerimaNo = $this->codeGenerator->generateTerimaNo();

        return view('gudang.terima.create', compact(
            'supplierList',
            'gudangList',
            'barangList',
            'openPoList',
            'selectedPo',
            'nextTerimaNo'
        ));
    }

    public function store(StoreTerimaBarangRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $terima = $this->terimaService->store($request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Penerimaan barang {$terima->terima_no} berhasil diproses dan stok telah ditambahkan.",
                    'data'    => $terima,
                ], 201);
            }

            if ($request->input('redirect_to') === 'po_index') {
                return redirect()
                    ->route('gudang.po.index')
                    ->with('success', "Penerimaan barang {$terima->terima_no} berhasil dicatat. Status dan progres PO telah diperbarui.");
            }

            if ($request->input('redirect_to') === 'po' && $terima->po_id) {
                return redirect()
                    ->route('gudang.po.show', $terima->po_id)
                    ->with('success', "Penerimaan barang {$terima->terima_no} berhasil dicatat. Status dan progres PO telah diperbarui.");
            }

            return redirect()
                ->route('gudang.terima.show', $terima->terima_id)
                ->with('success', "Penerimaan barang {$terima->terima_no} berhasil diproses. Stok gudang dan kartu stok telah diperbarui.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Request $request, int $id): View|JsonResponse
    {
        $terima = $this->terimaService->getById($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Detail penerimaan barang berhasil diambil.',
                'data'    => $terima,
            ]);
        }

        return view('gudang.terima.show', compact('terima'));
    }

    /**
     * Tampilan form edit penerimaan barang.
     */
    public function edit(int $id): View
    {
        $terima = $this->terimaService->getById($id);
        $supplierList = MstSupplier::active()->with('jenisSupplier')->orderBy('supplier_nm')->get();
        $user = auth()->user();
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $allowedGudangIds = ($user && $user->isSuperAdmin()) ? null : ($user ? $user->getAllowedGudangIds() : []);
        $barangList = MstBarang::active()->bahanBaku()->with(['satuanDasar', 'jenisBarang'])->orderBy('barang_nm')->get();
        $openPoList = $this->poService->getOpenPoList(null, $allowedGudangIds);

        // Sertakan PO saat ini jika bukan status OPEN agar dropdown tetap menampilkan PO-nya
        if ($terima->po_id && !$openPoList->contains('po_id', $terima->po_id)) {
            $currentPo = \App\Models\Gudang\DatPoHdr::with(['supplier', 'details.barang.satuanDasar'])->find($terima->po_id);
            if ($currentPo) {
                $openPoList->push($currentPo);
            }
        }

        return view('gudang.terima.edit', compact(
            'terima',
            'supplierList',
            'gudangList',
            'barangList',
            'openPoList'
        ));
    }

    /**
     * Memproses update data penerimaan barang dan penyesuaian saldo stok fisik.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'terima_no'        => 'required|string|max:50|unique:dat_terima_hdr,terima_no,' . $id . ',terima_id',
            'terima_tgl'       => 'required|date',
            'po_id'            => 'nullable|integer|exists:dat_po_hdr,po_id',
            'supplier_id'      => 'required|integer|exists:mst_supplier,supplier_id',
            'gudang_id'        => 'required|integer|exists:mst_gudang,gudang_id',
            'suratjalan_no'    => 'nullable|string|max:100',
            'catatan_txt'      => 'nullable|string',
            'potongan_nominal' => 'nullable|numeric|min:0',
            'ppn_tipe'         => 'nullable|string|in:NON_PPN,PPN_11',

            'items'                 => 'required|array|min:1',
            'items.*.barang_id'     => 'required|integer|exists:mst_barang,barang_id',
            'items.*.podtl_id'      => 'nullable|integer|exists:dat_po_dtl,podtl_id',
            'items.*.batch_no'      => 'nullable|string|max:100',
            'items.*.expired_tgl'   => 'nullable|date',
            'items.*.grade_cd'      => 'nullable|string|max:20',
            'items.*.terima_qty'    => 'required|numeric|min:0',
            'items.*.reject_qty'    => 'nullable|numeric|min:0',
            'items.*.harga_nominal'    => 'nullable|numeric|min:0',
            'items.*.diskon_persen'    => 'nullable|numeric|min:0|max:100',
            'items.*.potongan_nominal' => 'nullable|numeric|min:0',
            'items.*.ppn_tipe'         => 'nullable|string|in:NON_PPN,PPN_11',
            'items.*.catatan_txt'      => 'nullable|string',
        ]);

        try {
            $terima = $this->terimaService->update($id, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Penerimaan barang {$terima->terima_no} berhasil diperbarui dan stok telah disesuaikan.",
                    'data'    => $terima,
                ]);
            }

            return redirect()
                ->route('gudang.terima.show', $terima->terima_id)
                ->with('success', "Penerimaan barang {$terima->terima_no} berhasil diperbarui dan stok telah disesuaikan.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Export Bukti Penerimaan Barang (GRN) ke format PDF resmi.
     */
    public function exportPdf(int $id): Response
    {
        $terima = $this->terimaService->getById($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('gudang.terima.pdf_bukti_terima', [
            'terima'     => $terima,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Staff Gudang'),
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $fileName = 'GRN-' . preg_replace('/[^A-Za-z0-9\-]/', '', $terima->terima_no) . '.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * Export Rekapitulasi / Laporan Barang Masuk ke format PDF (A4 Landscape).
     */
    public function exportRekapPdf(Request $request): Response
    {
        $search = $request->input('search');
        $gudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];

        if ($gudangId !== null) {
            $effectiveGudang = ($user && !$user->canAccessGudang($gudangId)) ? $allowedGudangIds : $gudangId;
        } else {
            $effectiveGudang = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
        }

        $query = \App\Models\Gudang\DatTerimaDtl::with([
            'header.supplier', 
            'header.gudang', 
            'header.po', 
            'barang.jenisBarang', 
            'barang.satuanDasar'
        ])->whereHas('header', function ($q) use ($effectiveGudang) {
            $q->where('deleted_st', false);
            if (is_array($effectiveGudang)) {
                $q->whereIn('gudang_id', $effectiveGudang);
            } elseif ($effectiveGudang !== null) {
                $q->where('gudang_id', $effectiveGudang);
            }
        });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('header', function ($hq) use ($search) {
                      $hq->where('terima_no', 'ILIKE', "%{$search}%")
                         ->orWhere('suratjalan_no', 'ILIKE', "%{$search}%")
                         ->orWhereHas('supplier', function ($sq) use ($search) {
                             $sq->where('supplier_nm', 'ILIKE', "%{$search}%");
                         });
                  });
            });
        }

        $items = $query->orderBy('terimadtl_id', 'desc')->get();

        $gudangNm = null;
        if ($gudangId) {
            $gudangNm = MstGudang::find($gudangId)?->gudang_nm;
        }

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('gudang.terima.pdf_rekap', [
            'items'      => $items,
            'gudangNm'   => $gudangNm,
            'search'     => $search,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Staff Gudang'),
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $fileName = 'Rekap_Barang_Masuk_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * Export Rekapitulasi / Laporan Barang Masuk ke format Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $search = $request->input('search');
        $gudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        $user = auth()->user();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];

        if ($gudangId !== null) {
            $effectiveGudang = ($user && !$user->canAccessGudang($gudangId)) ? $allowedGudangIds : $gudangId;
        } else {
            $effectiveGudang = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
        }

        $query = \App\Models\Gudang\DatTerimaDtl::with([
            'header.supplier', 
            'header.gudang', 
            'header.po', 
            'barang.jenisBarang', 
            'barang.satuanDasar'
        ])->whereHas('header', function ($q) use ($effectiveGudang) {
            $q->where('deleted_st', false);
            if (is_array($effectiveGudang)) {
                $q->whereIn('gudang_id', $effectiveGudang);
            } elseif ($effectiveGudang !== null) {
                $q->where('gudang_id', $effectiveGudang);
            }
        });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('header', function ($hq) use ($search) {
                      $hq->where('terima_no', 'ILIKE', "%{$search}%")
                         ->orWhere('suratjalan_no', 'ILIKE', "%{$search}%")
                         ->orWhereHas('supplier', function ($sq) use ($search) {
                             $sq->where('supplier_nm', 'ILIKE', "%{$search}%");
                         });
                  });
            });
        }

        $items = $query->orderBy('terimadtl_id', 'desc')->get();

        $gudangNm = null;
        if ($gudangId) {
            $gudangNm = MstGudang::find($gudangId)?->gudang_nm;
        }

        $printedBy = auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Staff Gudang');
        $printedAt = now()->translatedFormat('d F Y H:i');

        $export = new TerimaBarangExport($items, $gudangNm, $search, $printedBy, $printedAt);
        return $export->download('Rekap_Barang_Masuk_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Download template Excel kosong untuk Import Barang Masuk.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return (new TerimaBarangTemplate())->download();
    }

    /**
     * Proses upload file Excel dan import data Barang Masuk.
     */
    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:5120', // maks 5 MB
            ],
        ], [
            'import_file.required' => 'File Excel wajib dipilih.',
            'import_file.mimes'    => 'Format file harus .xlsx atau .xls.',
            'import_file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        try {
            $importer = app(TerimaBarangImport::class);
            $importer->import($request->file('import_file'));

            $msg = "Import selesai: {$importer->successCount} GRN berhasil, {$importer->errorCount} GRN gagal.";

            if ($importer->errorCount > 0) {
                $errors = collect($importer->results)
                    ->where('status', 'error')
                    ->pluck('message')
                    ->implode(' | ');
                return redirect()->route('gudang.terima.index')
                    ->with('warning', $msg . ' Kesalahan: ' . $errors);
            }

            return redirect()->route('gudang.terima.index')
                ->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->route('gudang.terima.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Membatalkan / menghapus dokumen penerimaan barang.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperAdmin() && !$user->hasPermission('terima_create'))) {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan penerimaan barang.');
        }

        try {
            $terima = DatTerimaHdr::findOrFail($id);
            $terimaNo = $terima->terima_no;

            $this->terimaService->delete($id);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Penerimaan barang {$terimaNo} berhasil dibatalkan dan stok telah disesuaikan kembali.",
                ]);
            }

            return redirect()
                ->route('gudang.terima.index')
                ->with('success', "Penerimaan barang {$terimaNo} berhasil dibatalkan dan saldo stok telah disesuaikan kembali.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
