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
            COUNT(CASE WHEN pakai_tgl = ? THEN 1 END) as today_dokumen,
            COUNT(CASE WHEN status_st = 'DRAFT' THEN 1 END) as draft_dokumen,
            COUNT(CASE WHEN status_st = 'POSTED' THEN 1 END) as posted_dokumen
        ", [$today])->first();

        $kpiCounts = [
            'total'        => (int) ($rawCounts->total_dokumen ?? 0),
            'today'        => (int) ($rawCounts->today_dokumen ?? 0),
            'draft'        => (int) ($rawCounts->draft_dokumen ?? 0),
            'posted'       => (int) ($rawCounts->posted_dokumen ?? 0),
            'singkong_qty' => (float) ($ringkasan['singkong_qty'] ?? 0),
            'total_biaya'  => (float) ($ringkasan['grand_total_nilai'] ?? 0),
        ];

        // Ambil daftar tujuan murni dari Master Lini Produksi aktif + historis transaksi
        $activeLini = MstLiniProduksi::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('lini_id', 'asc')
            ->pluck('lini_nm')
            ->toArray();

        $historicalTujuan = DatPakaiHdr::where('deleted_st', false)
            ->whereNotNull('tujuan_pemakaian')
            ->distinct()
            ->pluck('tujuan_pemakaian')
            ->toArray();

        $tujuanOptions = array_values(array_unique(array_merge($activeLini, $historicalTujuan)));

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

        // Ambil barang peruntukan produksi: Bahan Baku, Penolong, Kemasan, serta khusus Berko (bukan seluruh WIP)
        $barangList = MstBarang::active()
            ->where(function ($query) {
                $query->bahanProduksi()
                      ->orWhere(function ($wip) {
                          $wip->whereHas('jenisBarang', fn($j) => $j->where('jenis_barang_cd', 'WIP'))
                              ->where(function ($b) {
                                  $b->where('barang_nm', 'ILIKE', '%BERKO%')
                                    ->orWhere('barang_cd', 'ILIKE', '%BRK%');
                              });
                      });
            })
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get()
            ->map(function ($b) {
                $cd = strtoupper($b->jenisBarang->jenis_barang_cd ?? '');
                $nm = strtoupper($b->barang_nm ?? '');

                if ($cd === 'BB' || $cd === 'RAW' || str_contains($nm, 'SINGKONG') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR') || str_contains($nm, 'BERKO') || str_starts_with($cd, 'WIP')) {
                    $b->kategori_kelompok = 'BAHAN_BAKU';
                    $b->kategori_label = 'Bahan Baku';
                } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'PLASTIK') || str_contains($nm, 'ROLL') || str_contains($nm, 'LAKBAN') || str_contains($nm, 'RAFIA') || str_contains($nm, 'SARUNG TANGAN') || $cd === 'PACK') {
                    $b->kategori_kelompok = 'KEMASAN';
                    $b->kategori_label = 'Kemasan & Packaging';
                } else {
                    $b->kategori_kelompok = 'BAHAN_PENOLONG';
                    $b->kategori_label = 'Bahan Penolong & Bumbu';
                }

                return $b;
            });

        $autoNo = $this->codeGenerator->generatePakaiNo();

        // Ambil daftar master lini produksi aktif khusus peruntukan Work In Progress (WIP)
        $liniList = MstLiniProduksi::where('deleted_st', false)
            ->where('active_st', true)
            ->where('kategori_lini', 'ILIKE', '%WIP%')
            ->orderBy('lini_nm', 'asc')
            ->get();

        $bomList = $this->bomService->getAllActive();

        // Ambil daftar dokumen produksi (Berko / Lini) aktif untuk referensi tiket kemasan
        $produksiList = \App\Models\Produksi\DatProduksiHdr::where('deleted_st', false)
            ->orderBy('produksi_tgl', 'desc')
            ->orderBy('produksi_id', 'desc')
            ->limit(40)
            ->get();

        return view('gudang.pemakaian.create', compact(
            'gudangList',
            'barangList',
            'userGudangId',
            'autoNo',
            'liniList',
            'bomList',
            'produksiList'
        ));
    }

    /**
     * Simpan transaksi pengeluaran barang.
     */
    public function store(StorePemakaianRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $header = $this->pemakaianService->store($request->validated());

            $msg = "Dokumen pengeluaran {$header->pakai_no} berhasil disimpan sebagai DRAFT (Stok belum dipotong). Silakan lakukan verifikasi fisik sebelum memposting.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                    'data'    => $header,
                ], 201);
            }

            return redirect()->route('gudang.pemakaian.show', $header->pakai_id)
                ->with('success', $msg);
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
     * Form edit transaksi pengeluaran barang (Khusus DRAFT).
     */
    public function edit(int $id): View|RedirectResponse
    {
        $pakai = $this->pemakaianService->getById($id);

        if ($pakai->status_st === 'POSTED') {
            return redirect()->route('gudang.pemakaian.show', $id)
                ->with('error', "Dokumen {$pakai->pakai_no} sudah diposting dan stok telah dipotong. Dokumen tidak dapat diubah.");
        }

        $user = Auth::user();
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $userGudangId = $pakai->gudang_id;

        $barangList = MstBarang::active()
            ->where(function ($query) {
                $query->bahanProduksi()
                      ->orWhere(function ($wip) {
                          $wip->whereHas('jenisBarang', fn($j) => $j->where('jenis_barang_cd', 'WIP'))
                              ->where(function ($b) {
                                  $b->where('barang_nm', 'ILIKE', '%BERKO%')
                                    ->orWhere('barang_cd', 'ILIKE', '%BRK%');
                              });
                      });
            })
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get()
            ->map(function ($b) {
                $cd = strtoupper($b->jenisBarang->jenis_barang_cd ?? '');
                $nm = strtoupper($b->barang_nm ?? '');

                if ($cd === 'BB' || $cd === 'RAW' || str_contains($nm, 'SINGKONG') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR') || str_contains($nm, 'BERKO') || str_starts_with($cd, 'WIP')) {
                    $b->kategori_kelompok = 'BAHAN_BAKU';
                    $b->kategori_label = 'Bahan Baku';
                } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'PLASTIK') || str_contains($nm, 'ROLL') || str_contains($nm, 'LAKBAN') || str_contains($nm, 'RAFIA') || str_contains($nm, 'SARUNG TANGAN') || $cd === 'PACK') {
                    $b->kategori_kelompok = 'KEMASAN';
                    $b->kategori_label = 'Kemasan & Packaging';
                } else {
                    $b->kategori_kelompok = 'BAHAN_PENOLONG';
                    $b->kategori_label = 'Bahan Penolong & Bumbu';
                }

                return $b;
            });

        // Ambil daftar master lini produksi aktif khusus peruntukan Work In Progress (WIP)
        $liniList = MstLiniProduksi::where('deleted_st', false)
            ->where('active_st', true)
            ->where(function ($q) use ($pakai) {
                $q->where('kategori_lini', 'ILIKE', '%WIP%')
                  ->orWhere('lini_nm', $pakai->tujuan_pemakaian);
            })
            ->orderBy('lini_nm', 'asc')
            ->get();

        $bomList = $this->bomService->getAllActive();

        $produksiList = \App\Models\Produksi\DatProduksiHdr::where('deleted_st', false)
            ->orderBy('produksi_tgl', 'desc')
            ->orderBy('produksi_id', 'desc')
            ->limit(40)
            ->get();

        return view('gudang.pemakaian.edit', compact(
            'pakai',
            'gudangList',
            'barangList',
            'userGudangId',
            'liniList',
            'bomList',
            'produksiList'
        ));
    }

    /**
     * Update transaksi pengeluaran barang (Khusus DRAFT).
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'pakai_tgl'        => 'required|date',
            'gudang_id'        => 'required|exists:mst_gudang,gudang_id',
            'tujuan_pemakaian' => 'required|string|max:100',
            'catatan_txt'      => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.barang_id'=> 'required|exists:mst_barang,barang_id',
            'items.*.batch_no' => 'required|string|max:100',
            'items.*.qty_keluar' => 'required|numeric|min:0.01',
            'items.*.grade_cd' => 'nullable|string|max:5',
            'items.*.harga_satuan' => 'nullable|numeric|min:0',
            'items.*.keterangan_txt' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $header = $this->pemakaianService->update($id, $validated, $user);

            $msg = "Dokumen pengeluaran {$header->pakai_no} berhasil diperbarui.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                    'data'    => $header,
                ]);
            }

            return redirect()->route('gudang.pemakaian.show', $header->pakai_id)
                ->with('success', $msg);
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
     * Verifikasi & Posting Dokumen Pemakaian Bahan.
     * Memotong stok fisik gudang dan menerbitkan Jurnal Umum otomatis.
     */
    public function postPemakaian(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $user = Auth::user();
            $header = $this->pemakaianService->postPemakaian($id, $user);

            $msg = "Dokumen pemakaian {$header->pakai_no} BERHASIL DIVERIFIKASI! Stok fisik telah dipotong dan Jurnal Umum otomatis telah diterbitkan.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                    'data'    => $header,
                ]);
            }

            return redirect()->route('gudang.pemakaian.show', $id)
                ->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus dokumen pemakaian (hanya yang berstatus DRAFT).
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $user = Auth::user();
            $this->pemakaianService->delete($id, $user);

            $msg = "Dokumen pengeluaran DRAFT berhasil dibatalkan / dihapus.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ]);
            }

            return redirect()->route('gudang.pemakaian.index')
                ->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
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
     * Dilengkapi deteksi komitmen dokumen DRAFT lain agar tidak terjadi tabrakan / double-claim batch sebelum diposting.
     */
    public function getBatches(Request $request): JsonResponse
    {
        $gudangId = (int) $request->input('gudang_id');
        $barangId = (int) $request->input('barang_id');
        $excludePakaiId = (int) $request->input('exclude_pakai_id', 0);

        $user = Auth::user();
        if ($user && !$user->canAccessGudang($gudangId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses ke gudang ini.',
                'batches' => [],
            ], 403);
        }

        // Ambil alokasi kuantitas yang sedang terikat di dokumen DRAFT lain (belum diposting/acc)
        $draftAllocations = DatPakaiDtl::where('barang_id', $barangId)
            ->whereHas('header', function ($q) use ($gudangId, $excludePakaiId) {
                $q->where('gudang_id', $gudangId)
                  ->where('status_st', 'DRAFT')
                  ->where('deleted_st', false);
                if ($excludePakaiId > 0) {
                    $q->where('pakai_id', '!=', $excludePakaiId);
                }
            })
            ->with('header:pakai_id,pakai_no')
            ->get()
            ->groupBy(function ($d) {
                return trim($d->batch_no) . '_' . strtoupper(trim($d->grade_cd ?: 'A'));
            });

        $batches = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('sisa_qty', '>', 0)
            ->where('deleted_st', false)
            ->when($request->filled('grade_cd'), function ($q) use ($request) {
                $q->where('grade_cd', strtoupper(trim($request->input('grade_cd'))));
            })
            ->orderBy('created_at', 'asc')
            ->orderBy('stok_id', 'asc')
            ->get(['stok_id', 'batch_no', 'grade_cd', 'sisa_qty', 'harga_satuan', 'expired_tgl', 'created_at']);

        $formattedBatches = $batches->values()->map(function ($b, $index) use ($draftAllocations) {
            $gradeCd = strtoupper(trim($b->grade_cd ?: 'A'));
            $lookupKey = trim($b->batch_no) . '_' . $gradeCd;
            $group = $draftAllocations->get($lookupKey);

            $sisaFisik = (float) $b->sisa_qty;
            $draftQty = $group ? (float) $group->sum('qty_keluar') : 0.0;
            $draftDocNos = $group ? $group->pluck('header.pakai_no')->unique()->filter()->values()->toArray() : [];
            $sisaBebas = max(0.0, round($sisaFisik - $draftQty, 4));

            return [
                'stok_id'        => $b->stok_id,
                'batch_no'       => $b->batch_no,
                'grade_cd'       => $gradeCd,
                'sisa_qty'       => $sisaFisik,
                'draft_qty'      => $draftQty,
                'draft_doc_nos'  => $draftDocNos,
                'sisa_bebas'     => $sisaBebas,
                'is_committed'   => $draftQty > 0,
                'is_fully_booked'=> $sisaBebas <= 0.0001,
                'harga_satuan'   => (float) $b->harga_satuan,
                'expired_tgl'    => $b->expired_tgl ? \Carbon\Carbon::parse($b->expired_tgl)->format('d/m/Y') : null,
                'tgl_terima'     => $b->created_at ? $b->created_at->format('d/m/Y') : '-',
                'is_fifo_top'    => $index === 0,
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
        $gradeFilter = $request->input('grade_filter');

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

        $excludePakaiId = (int) $request->input('exclude_pakai_id', 0);

        try {
            $alokasi = $this->bomService->alokasiBahanResepFifo($gudangId, $bomId, $targetQty, $gradeFilter, $excludePakaiId);

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
     * Export Dokumen Bukti Pengeluaran Barang (BPPB) Tunggal ke format PDF resmi.
     */
    public function exportPdf(int $id): Response
    {
        $pakai = $this->pemakaianService->getById($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('gudang.pemakaian.pdf_dokumen', [
            'pakai'      => $pakai,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Petugas Gudang'),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $fileName = 'BPPB_' . str_replace(['/', '\\', ' '], '_', $pakai->pakai_no) . '.pdf';
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
