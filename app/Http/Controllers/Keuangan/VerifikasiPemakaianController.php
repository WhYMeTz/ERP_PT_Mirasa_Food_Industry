<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Gudang\DatPakaiHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstGudang;
use App\Services\Gudang\PemakaianService;
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
        protected PemakaianService $pemakaianService
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
     * Layar inspeksi / pemeriksaan mendalam dokumen pemakaian bahan sebelum posting.
     */
    public function show(int $id): View
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

        // Kumpulkan data batch & cek ketersediaan stok fisik saat ini
        $gudangId = $pakai->gudang_id;
        $itemsCheck = [];
        $canApprove = true;
        $totalNominal = 0;

        foreach ($pakai->details as $dtl) {
            $totalNominal += (float) $dtl->total_harga;

            // Cari batch fisik saat ini di gudang
            $stokBatch = DatStokBatch::where('gudang_id', $gudangId)
                ->where('barang_id', $dtl->barang_id)
                ->where('batch_no', $dtl->batch_no)
                ->where('deleted_st', false)
                ->first();

            $currentStock = $stokBatch ? (float) $stokBatch->sisa_qty : 0;
            $qtyKeluar = (float) $dtl->qty_keluar;

            // Jika masih draft, cek apakah stok cukup untuk dipotong
            $isStockSufficient = $pakai->isPosted() ? true : ($currentStock >= $qtyKeluar);
            if (!$isStockSufficient) {
                $canApprove = false;
            }

            // Cek apakah batch ini batch tertua (FIFO check)
            $oldestBatch = DatStokBatch::where('gudang_id', $gudangId)
                ->where('barang_id', $dtl->barang_id)
                ->where('sisa_qty', '>', 0)
                ->where('deleted_st', false)
                ->orderBy('created_at', 'asc')
                ->orderBy('stok_id', 'asc')
                ->first();

            $isFifoMatch = true;
            $fifoNote = 'Sesuai urutan FIFO';
            if ($oldestBatch && $oldestBatch->batch_no !== $dtl->batch_no && !$pakai->isPosted()) {
                $isFifoMatch = false;
                $fifoNote = "Ada batch tertua: {$oldestBatch->batch_no} ({$oldestBatch->sisa_qty} tersisa)";
            }

            $itemsCheck[] = [
                'detail'            => $dtl,
                'current_stock'     => $currentStock,
                'is_stock_ok'       => $isStockSufficient,
                'is_fifo_ok'        => $isFifoMatch,
                'fifo_note'         => $fifoNote,
                'harga_satuan'      => (float) $dtl->harga_satuan,
                'total_harga'       => (float) $dtl->total_harga,
            ];
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
}
