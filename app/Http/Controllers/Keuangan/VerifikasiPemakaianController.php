<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Gudang\DatPakaiHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstLiniProduksi;
use App\Models\Produksi\DatProduksiHdr;
use App\Services\Gudang\PemakaianService;
use App\Services\Produksi\BomService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiPemakaianController extends Controller
{
    public function __construct(
        protected PemakaianService $pemakaianService,
        protected BomService $bomService
    ) {}

    /**
     * Tampilan daftar antrean dokumen pemakaian bahan yang menunggu verifikasi & riwayat posting.
     */
    public function index(Request $request): View
    {
        $statusTab = $request->input('tab', 'DRAFT'); // DRAFT | POSTED
        $search = $request->input('search');
        $gudangId = $request->input('gudang_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = (int) $request->input('per_page', 15);

        $query = DatPakaiHdr::with(['gudang', 'details.barang.satuanDasar', 'jurnal'])
            ->where('deleted_st', false);

        if ($statusTab === 'DRAFT') {
            $query->where('status_st', 'DRAFT');
        } else {
            $query->where('status_st', 'POSTED');
        }

        if (!empty($gudangId)) {
            $query->where('gudang_id', (int) $gudangId);
        }

        if (!empty($startDate)) {
            $query->whereDate('pakai_tgl', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('pakai_tgl', '<=', $endDate);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('pakai_no', 'ILIKE', "%{$search}%")
                  ->orWhere('tujuan_pemakaian', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('details.barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  });
            });
        }

        $dataList = $query->orderBy('pakai_tgl', 'desc')
            ->orderBy('pakai_id', 'desc')
            ->paginate($perPage);

        // KPI Metrik
        $countDraft = DatPakaiHdr::where('status_st', 'DRAFT')->where('deleted_st', false)->count();
        $countPostedThisMonth = DatPakaiHdr::where('status_st', 'POSTED')
            ->where('deleted_st', false)
            ->whereMonth('posted_at', now()->month)
            ->whereYear('posted_at', now()->year)
            ->count();

        // Hitung total rupiah draft
        $totalNominalDraft = DB::table('dat_pakai_hdr as h')
            ->join('dat_pakai_dtl as d', 'h.pakai_id', '=', 'd.pakai_id')
            ->where('h.status_st', 'DRAFT')
            ->where('h.deleted_st', false)
            ->sum('d.total_harga');

        // Hitung total rupiah posted bulan ini
        $totalNominalPostedMonth = DB::table('dat_pakai_hdr as h')
            ->join('dat_pakai_dtl as d', 'h.pakai_id', '=', 'd.pakai_id')
            ->where('h.status_st', 'POSTED')
            ->where('h.deleted_st', false)
            ->whereMonth('h.posted_at', now()->month)
            ->whereYear('h.posted_at', now()->year)
            ->sum('d.total_harga');

        $kpis = [
            'count_draft'                => $countDraft,
            'count_posted_month'         => $countPostedThisMonth,
            'total_nominal_draft'        => (float) $totalNominalDraft,
            'total_nominal_posted_month' => (float) $totalNominalPostedMonth,
        ];

        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();

        return view('keuangan.verifikasi_pemakaian.index', compact(
            'dataList',
            'statusTab',
            'kpis',
            'gudangList',
            'search',
            'gudangId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Layar inspeksi / pemeriksaan mendalam dokumen pemakaian bahan.
     * Jika masih DRAFT, langsung menyajikan stasiun edit & penyesuaian bahan lengkap di tempat (in-place).
     * Jika sudah POSTED, menyajikan tampilan dokumen resmi terkunci (read-only).
     */
    public function show(Request $request, int $id): View|JsonResponse
    {
        $pakai = DatPakaiHdr::with([
            'gudang',
            'details.barang.satuanDasar',
            'details.barang.jenisBarang',
            'jurnal.details.coa'
        ])
        ->where('pakai_id', $id)
        ->where('deleted_st', false)
        ->firstOrFail();

        $gudangId = $pakai->gudang_id;
        $totalNominal = (float) $pakai->details->sum('total_harga');

        // Jika DRAFT, siapkan seluruh kelengkapan Master Data untuk koreksi langsung di tempat
        if ($pakai->isDraft()) {
            $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();

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

            $liniList = MstLiniProduksi::where('deleted_st', false)
                ->where('active_st', true)
                ->orderBy('lini_id', 'asc')
                ->get();

            $bomList = $this->bomService->getAllActive();

            $produksiList = DatProduksiHdr::where('deleted_st', false)
                ->orderBy('produksi_tgl', 'desc')
                ->orderBy('produksi_id', 'desc')
                ->limit(40)
                ->get();

            // Kumpulkan data batch stok untuk setiap item detail yang sudah ada
            $initialItems = [];
            foreach ($pakai->details as $dtl) {
                $batches = DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $dtl->barang_id)
                    ->where('deleted_st', false)
                    ->where('sisa_qty', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('stok_id', 'asc')
                    ->get()
                    ->map(function ($sb, $idx) {
                        return [
                            'stok_id'      => $sb->stok_id,
                            'batch_no'     => $sb->batch_no,
                            'grade_cd'     => $sb->grade_cd ?? 'A',
                            'sisa_qty'     => (float) $sb->sisa_qty,
                            'harga_satuan' => (float) $sb->harga_satuan,
                            'is_fifo_top'  => ($idx === 0),
                            'tgl_terima'   => $sb->created_at ? $sb->created_at->format('d/m/Y') : '-',
                            'expired_tgl'  => $sb->expired_tgl ? \Carbon\Carbon::parse($sb->expired_tgl)->format('d/m/Y') : '-',
                        ];
                    })
                    ->toArray();

                $hasCurBatch = collect($batches)->contains('batch_no', $dtl->batch_no);
                if (!$hasCurBatch) {
                    $curBatchObj = DatStokBatch::where('gudang_id', $gudangId)
                        ->where('barang_id', $dtl->barang_id)
                        ->where('batch_no', $dtl->batch_no)
                        ->first();
                    if ($curBatchObj) {
                        $batches[] = [
                            'stok_id'      => $curBatchObj->stok_id,
                            'batch_no'     => $curBatchObj->batch_no,
                            'grade_cd'     => $curBatchObj->grade_cd ?? 'A',
                            'sisa_qty'     => (float) $curBatchObj->sisa_qty,
                            'harga_satuan' => (float) $curBatchObj->harga_satuan,
                            'is_fifo_top'  => false,
                            'tgl_terima'   => $curBatchObj->created_at ? $curBatchObj->created_at->format('d/m/Y') : '-',
                            'expired_tgl'  => $curBatchObj->expired_tgl ? \Carbon\Carbon::parse($curBatchObj->expired_tgl)->format('d/m/Y') : '-',
                        ];
                    }
                }

                $initialItems[] = [
                    'barang_id'      => $dtl->barang_id,
                    'barang_cd'      => $dtl->barang?->barang_cd,
                    'barang_nm'      => $dtl->barang?->barang_nm,
                    'satuan_nm'      => $dtl->barang?->satuanDasar?->satuan_nm ?? 'Kg',
                    'batch_no'       => $dtl->batch_no,
                    'grade_cd'       => $dtl->grade_cd ?? 'A',
                    'qty_keluar'     => (float) $dtl->qty_keluar,
                    'harga_satuan'   => (float) $dtl->harga_satuan,
                    'total_harga'    => (float) $dtl->total_harga,
                    'keterangan_txt' => $dtl->keterangan_txt,
                    'all_batches'    => $batches,
                ];
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'       => 'success',
                    'pakai'        => [
                        'pakai_id'         => $pakai->pakai_id,
                        'pakai_no'         => $pakai->pakai_no,
                        'pakai_tgl'        => $pakai->pakai_tgl ? \Carbon\Carbon::parse($pakai->pakai_tgl)->format('Y-m-d') : '',
                        'pakai_tgl_fmt'    => $pakai->pakai_tgl ? \Carbon\Carbon::parse($pakai->pakai_tgl)->format('d/m/Y') : '',
                        'gudang_id'        => $pakai->gudang_id,
                        'gudang_nm'        => $pakai->gudang?->gudang_nm ?? '-',
                        'tujuan_pemakaian' => $pakai->tujuan_pemakaian,
                        'catatan_txt'      => $pakai->catatan_txt ?? '',
                        'status_st'        => $pakai->status_st,
                        'created_by'       => $pakai->created_by ?? '-',
                        'produksi_id'      => $pakai->produksi_id,
                    ],
                    'initialItems' => $initialItems,
                    'totalNominal' => (float) $totalNominal,
                    'gudangList'   => $gudangList,
                    'barangList'   => $barangList,
                ]);
            }

            return view('keuangan.verifikasi_pemakaian.show', compact(
                'pakai',
                'gudangList',
                'barangList',
                'liniList',
                'bomList',
                'produksiList',
                'initialItems',
                'totalNominal'
            ));
        }

        // Jika POSTED, siapkan data inspeksi statis
        $itemsCheck = [];
        $canApprove = false;

        foreach ($pakai->details as $dtl) {
            $stokBatch = DatStokBatch::where('gudang_id', $gudangId)
                ->where('barang_id', $dtl->barang_id)
                ->where('batch_no', $dtl->batch_no)
                ->where('deleted_st', false)
                ->first();

            $currentStock = $stokBatch ? (float) $stokBatch->sisa_qty : 0;

            $itemsCheck[] = [
                'detail'            => $dtl,
                'barang_nm'         => $dtl->barang?->barang_nm ?? '-',
                'barang_cd'         => $dtl->barang?->barang_cd ?? '-',
                'satuan_nm'         => $dtl->barang?->satuanDasar?->satuan_nm ?? 'Kg',
                'batch_no'          => $dtl->batch_no,
                'grade_cd'          => $dtl->grade_cd ?? 'A',
                'qty_keluar'        => (float) $dtl->qty_keluar,
                'current_stock'     => $currentStock,
                'is_stock_ok'       => true,
                'is_fifo_ok'        => true,
                'fifo_note'         => 'Sudah Terposting',
                'harga_satuan'      => (float) $dtl->harga_satuan,
                'total_harga'       => (float) $dtl->total_harga,
            ];
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'       => 'success',
                'pakai'        => [
                    'pakai_id'         => $pakai->pakai_id,
                    'pakai_no'         => $pakai->pakai_no,
                    'pakai_tgl'        => $pakai->pakai_tgl ? \Carbon\Carbon::parse($pakai->pakai_tgl)->format('Y-m-d') : '',
                    'pakai_tgl_fmt'    => $pakai->pakai_tgl ? \Carbon\Carbon::parse($pakai->pakai_tgl)->format('d/m/Y') : '',
                    'gudang_id'        => $pakai->gudang_id,
                    'gudang_nm'        => $pakai->gudang?->gudang_nm ?? '-',
                    'tujuan_pemakaian' => $pakai->tujuan_pemakaian,
                    'catatan_txt'      => $pakai->catatan_txt ?? '',
                    'status_st'        => $pakai->status_st,
                    'created_by'       => $pakai->created_by ?? '-',
                    'jurnal_id'        => $pakai->jurnal_id,
                ],
                'itemsCheck'   => $itemsCheck,
                'totalNominal' => (float) $totalNominal,
            ]);
        }

        return view('keuangan.verifikasi_pemakaian.show', compact(
            'pakai',
            'itemsCheck',
            'canApprove',
            'totalNominal'
        ));
    }

    /**
     * Eksekusi persetujuan dokumen pemakaian: potong stok fisik & terbitkan jurnal umum.
     */
    public function approve(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $user = Auth::user();
            $header = $this->pemakaianService->postPemakaian($id, $user);

            $msg = "Dokumen pemakaian {$header->pakai_no} BERHASIL DISETUJUI & DIPOSTING! Stok fisik gudang telah dipotong dan Jurnal Umum otomatis telah diterbitkan.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                    'data'    => $header,
                ]);
            }

            return redirect()->route('keuangan.verifikasi_pemakaian.show', $id)
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
     * Tolak atau batalkan pengeluaran bahan (Hanya jika masih DRAFT).
     */
    public function reject(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $reason = trim($request->input('alasan_reject', 'Ditolak saat verifikasi keuangan'));
            if (empty($reason)) {
                throw new Exception("Alasan penolakan wajib diisi.");
            }

            $user = Auth::user();
            $pakai = DatPakaiHdr::where('pakai_id', $id)->firstOrFail();

            if ($pakai->status_st === 'POSTED') {
                throw new Exception("Dokumen yang sudah diposting tidak dapat ditolak.");
            }

            $actor = $user?->name ?? 'SYSTEM';
            $pakai->update([
                'catatan_txt' => "[DITOLAK - " . now()->format('d/m/Y H:i') . " oleh {$actor}]: " . $reason . " | Catatan lama: " . $pakai->catatan_txt,
                'deleted_st'  => true,
                'deleted_by'  => $actor,
                'deleted_at'  => now(),
            ]);

            $msg = "Dokumen pemakaian {$pakai->pakai_no} telah DITOLAK / DIBATALKAN.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ]);
            }

            return redirect()->route('keuangan.verifikasi_pemakaian.index', ['tab' => 'DRAFT'])
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
     * Layar pengeditan rincian pemakaian bahan oleh verifikator/supervisor.
     * Memungkinkan koreksi batch, timbangan, atau barang langsung di tempat.
     */
    /**
     * Halaman edit dialihkan langsung ke show() karena tampilan detail dan pengeditan
     * telah disatukan secara terpadu (All-in-One Inspection & Edit Station).
     */
    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('keuangan.verifikasi_pemakaian.show', $id);
    }

    /**
     * Simpan pembaruan data pemakaian bahan dan/atau langsung verifikasi posting.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $pakai = DatPakaiHdr::where('pakai_id', $id)->where('deleted_st', false)->firstOrFail();
        if (!$pakai->isDraft()) {
            throw new Exception("Dokumen pemakaian {$pakai->pakai_no} sudah diposting dan tidak dapat diubah.");
        }

        $validated = $request->validate([
            'pakai_tgl'              => ['required', 'date'],
            'gudang_id'              => ['required', 'exists:mst_gudang,gudang_id'],
            'produksi_id'            => ['nullable', 'exists:dat_produksi_hdr,produksi_id'],
            'tujuan_pemakaian'       => ['required', 'string', 'max:150'],
            'catatan_txt'            => ['nullable', 'string', 'max:1000'],
            'action_type'            => ['nullable', 'string', 'in:save_draft,save_and_approve'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.barang_id'      => ['required', 'exists:mst_barang,barang_id'],
            'items.*.batch_no'       => ['required', 'string', 'max:100'],
            'items.*.grade_cd'       => ['nullable', 'string', 'max:10'],
            'items.*.qty_keluar'     => ['required', 'numeric', 'min:0.0001'],
            'items.*.harga_satuan'   => ['nullable', 'numeric', 'min:0'],
            'items.*.keterangan_txt' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $user = Auth::user();
            $actionType = $request->input('action_type', 'save_draft');

            $updatedHeader = DB::transaction(function () use ($id, $validated, $user, $actionType) {
                $hdr = $this->pemakaianService->update($id, $validated, $user);

                if ($actionType === 'save_and_approve') {
                    $hdr = $this->pemakaianService->postPemakaian($id, $user);
                }

                return $hdr;
            });

            $msg = ($actionType === 'save_and_approve')
                ? "Perubahan berhasil disimpan dan dokumen {$updatedHeader->pakai_no} LANGSUNG DISETUJUI & DIPOSTING! Stok fisik telah dipotong dan Jurnal Umum otomatis telah diterbitkan."
                : "Perubahan rincian pemakaian bahan pada dokumen {$updatedHeader->pakai_no} berhasil disimpan sebagai DRAFT.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                    'data'    => $updatedHeader,
                ]);
            }

            return redirect()->route('keuangan.verifikasi_pemakaian.show', $id)
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
}
