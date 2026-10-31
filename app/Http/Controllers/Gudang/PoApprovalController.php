<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatStokBatch;
use App\Services\Gudang\PoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PoApprovalController extends Controller
{
    public function __construct(
        protected PoService $poService
    ) {}

    /**
     * Dashboard & Portal Khusus Eksekutif / Atasan (InvestIQ Style - Merah & Putih)
     * Optimized with single query for better performance
     */
    public function approvalCenter(Request $request): View
    {
        $statusFilter = $request->query('status', 'ALL');
        $activeView   = $request->query('view', 'dashboard');

        // Statistik KPI Utama (Live Data dari Database) - Optimized with single query
        $baseQuery = DatPoHdr::where('deleted_st', false);
        
        $stats = $baseQuery->selectRaw("
            COUNT(*) as total_count,
            COUNT(CASE WHEN status_cd = 'PENDING_APPROVAL' THEN 1 END) as pending_count,
            SUM(CASE WHEN status_cd = 'PENDING_APPROVAL' THEN total_tagihan ELSE 0 END) as pending_total_rp,
            COUNT(CASE WHEN status_cd = 'REVISION' THEN 1 END) as revision_count,
            COUNT(CASE WHEN status_cd IN ('ORDERED', 'PARTIAL') THEN 1 END) as ordered_count,
            COUNT(CASE WHEN status_cd IN ('COMPLETED', 'CLOSED') THEN 1 END) as completed_count
        ")->first();

        $totalPoCount = (int) ($stats->total_count ?? 0);
        $pendingCount = (int) ($stats->pending_count ?? 0);
        $pendingTotalRp = (float) ($stats->pending_total_rp ?? 0);
        $revisionCount = (int) ($stats->revision_count ?? 0);
        $orderedCount = (int) ($stats->ordered_count ?? 0);
        $completedCount = (int) ($stats->completed_count ?? 0);

        // Approved this month stats
        $monthStats = DatPoHdr::where('deleted_st', false)
            ->whereIn('status_cd', ['APPROVED', 'COMPLETED', 'PARTIAL', 'ORDERED'])
            ->whereMonth('po_tgl', now()->month)
            ->whereYear('po_tgl', now()->year)
            ->selectRaw('COUNT(*) as count, SUM(total_tagihan) as total_rp')
            ->first();

        $approvedThisMonthCount = (int) ($monthStats->count ?? 0);
        $approvedThisMonthRp = (float) ($monthStats->total_rp ?? 0);

        // Antrean PO dengan eager loading optimized
        $query = DatPoHdr::with([
            'supplier:supplier_id,supplier_nm,supplier_cd,email,alamat_txt',
            'approver:id,name,email',
            'details' => function($q) {
                $q->select('po_id', 'barang_id', 'pesan_qty', 'harga_nominal', 'subtotal_tagihan')
                  ->with('barang:barang_id,barang_nm,barang_cd,jenis_barang_id')
                  ->limit(5); // Limit untuk performance
            }
        ])->where('deleted_st', false);

        if ($statusFilter === 'PENDING_APPROVAL') {
            $query->where('status_cd', 'PENDING_APPROVAL');
        } elseif ($statusFilter === 'REVISION') {
            $query->where('status_cd', 'REVISION');
        } elseif ($statusFilter === 'HISTORY') {
            $query->whereIn('status_cd', ['APPROVED', 'COMPLETED', 'REJECTED', 'PARTIAL']);
        }

        $poList = $query->orderByRaw("CASE 
                WHEN status_cd = 'PENDING_APPROVAL' THEN 1 
                WHEN status_cd = 'REVISION' THEN 2 
                ELSE 3 
            END")
            ->orderByDesc('po_tgl')
            ->orderByDesc('po_id')
            ->paginate(20); // Increase per page for better UX

        return view('approval.po.dashboard', compact(
            'totalPoCount',
            'pendingCount',
            'pendingTotalRp',
            'revisionCount',
            'orderedCount',
            'completedCount',
            'approvedThisMonthCount',
            'approvedThisMonthRp',
            'poList',
            'statusFilter',
            'activeView'
        ));
    }

    /**
     * Menangani aksi persetujuan / penolakan langsung dari link email atasan
     */
    public function directAction(Request $request, string $token): View
    {
        $action = $request->query('action', 'approve');
        $po = DatPoHdr::where('approval_token', $token)
            ->with(['supplier', 'gudang', 'approver', 'details.barang.satuanDasar'])
            ->firstOrFail();

        // 1. Jika dokumen sudah APPROVED sebelumnya
        if ($po->isApproved()) {
            return view('approval.po.approval_result', [
                'po'        => $po,
                'isSuccess' => true,
                'action'    => 'ALREADY_APPROVED',
                'message'   => "Purchase Order #{$po->po_no} telah disetujui resmi oleh {$po->approved_by} pada " . ($po->approved_at ? \Carbon\Carbon::parse($po->approved_at)->translatedFormat('d F Y H:i') : '') . " WIB. Dokumen sah dan tidak memerlukan tindakan lagi.",
            ]);
        }

        // 2. Jika dokumen sudah REJECTED sebelumnya
        if ($po->isRejected()) {
            return view('approval.po.approval_result', [
                'po'        => $po,
                'isSuccess' => true,
                'action'    => 'ALREADY_REJECTED',
                'message'   => "Purchase Order #{$po->po_no} telah ditolak oleh {$po->rejected_by}. Alasan: {$po->reject_reason}",
            ]);
        }

        if ($action === 'reject') {
            return view('approval.po.reject_form', compact('po', 'token'));
        }

        // Jalankan persetujuan (Approve) atas nama atasan yang ditugaskan
        try {
            $approverName = $po->approver?->name ?: 'Pimpinan / Direksi';
            $po = $this->poService->approvePo($token, $approverName, 'Disetujui langsung melalui tautan email resmi.');
            $isSuccess = true;
            $message = "Purchase Order #{$po->po_no} berhasil disetujui resmi oleh {$approverName}.";
        } catch (Exception $e) {
            $isSuccess = false;
            $message = $e->getMessage();
        }

        return view('approval.po.approval_result', [
            'po'        => $po,
            'isSuccess' => $isSuccess,
            'action'    => 'APPROVED',
            'message'   => $message,
        ]);
    }

    /**
     * Proses penolakan dokumen PO dengan alasan dari form atasan
     */
    public function processReject(Request $request, string $token): View
    {
        $request->validate([
            'reject_reason' => 'required|string|min:3|max:500',
        ], [
            'reject_reason.required' => 'Alasan penolakan PO wajib diisi.',
            'reject_reason.min'      => 'Alasan penolakan minimal 3 karakter.',
        ]);

        try {
            $poModel = DatPoHdr::where('approval_token', $token)->with('approver')->first();
            $rejectorName = $poModel?->approver?->name ?: 'Pimpinan / Direksi';

            $po = $this->poService->rejectPo($token, $rejectorName, $request->input('reject_reason'));
            $isSuccess = true;
            $message = "Purchase Order #{$po->po_no} telah ditolak oleh {$rejectorName}. Notifikasi akan diteruskan ke tim pengadaan.";
        } catch (Exception $e) {
            $po = DatPoHdr::where('approval_token', $token)->first();
            $isSuccess = false;
            $message = $e->getMessage();
        }

        return view('approval.po.approval_result', [
            'po'        => $po,
            'isSuccess' => $isSuccess,
            'action'    => 'REJECTED',
            'message'   => $message,
        ]);
    }

    /**
     * Halaman Publik Verifikasi Keabsahan Dokumen PO (Hasil Scan QR Code)
     */
    public function verifyQr(string $token): View
    {
        $po = DatPoHdr::where('approval_token', $token)
            ->with(['supplier', 'gudang', 'approver', 'details.barang.satuanDasar'])
            ->first();

        return view('gudang.po.public_verify', compact('po', 'token'));
    }

    /**
     * Kirim ulang email permohonan approval ke Atasan (Triggered by Admin)
     */
    public function resendApprovalEmail(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $po = DatPoHdr::findOrFail($id);
            if (!$po->approver_id) {
                throw new Exception("Atasan (Approver) belum ditentukan pada PO ini.");
            }

            $sent = $this->poService->sendApprovalEmail($po);
            if (!$sent) {
                throw new Exception("Gagal mengirim email. Pastikan atasan memiliki alamat email aktif.");
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Email permohonan persetujuan PO {$po->po_no} berhasil dikirim ulang ke {$po->approver?->email}.",
                ]);
            }

            return back()->with('success', "Email permohonan persetujuan PO {$po->po_no} berhasil dikirim ke {$po->approver?->email}.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Kirim dokumen resmi Purchase Order ke Supplier via Email
     */
    public function sendToSupplier(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'supplier_email' => 'required|email|max:150',
            'custom_notes'   => 'nullable|string|max:1000',
            'cc_email'       => 'nullable|string|max:255',
        ], [
            'supplier_email.required' => 'Email supplier tujuan wajib diisi.',
            'supplier_email.email'    => 'Format email supplier tidak valid.',
        ]);

        try {
            $po = $this->poService->sendToSupplier(
                $id,
                $request->input('supplier_email'),
                $request->input('custom_notes'),
                $request->input('cc_email')
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Purchase Order {$po->po_no} berhasil dikirimkan ke email supplier ({$po->supplier_email_sent}).",
                    'data'    => $po,
                ]);
            }

            return back()->with('success', "Purchase Order {$po->po_no} berhasil dikirimkan ke email supplier ({$po->supplier_email_sent}).");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Halaman Khusus Verifikasi & Persetujuan Dokumen PO bagi Atasan (Manager / CEO)
     */
    public function review(int $id): View
    {
        $po = DatPoHdr::with([
            'supplier.jenisSupplier',
            'gudang',
            'approver',
            'details.barang.satuanDasar',
            'details.barang.jenisBarang',
            'details.pajak'
        ])->findOrFail($id);

        // Ambil stok riil gudang saat ini untuk setiap item komoditas
        $gudangId = $po->gudang_id;
        $stokQuery = DatStokBatch::query()->where('deleted_st', false)->where('sisa_qty', '>', 0);
        if ($gudangId) {
            $stokQuery->where('gudang_id', $gudangId);
        }
        $stockPerBarang = $stokQuery->groupBy('barang_id')
            ->selectRaw('barang_id, SUM(sisa_qty) as total_sisa')
            ->pluck('total_sisa', 'barang_id');

        $hasPriceIncrease = false;
        $hasCriticalStock = false;
        $hasOverstock = false;
        $analyzedItems = [];

        foreach ($po->details as $dtl) {
            $currentStock = (float) ($stockPerBarang[$dtl->barang_id] ?? 0);
            $minStock = (float) ($dtl->barang?->batas_minimum_qty ?? 0);
            $stdPrice = (float) ($dtl->barang?->harga_beli_standar ?? 0);
            $proposedPrice = (float) ($dtl->harga_nominal ?? 0);
            $proposedQty = (float) ($dtl->pesan_qty ?? 0);

            $diff = $proposedPrice - $stdPrice;
            $diffPct = $stdPrice > 0 ? round(($diff / $stdPrice) * 100, 1) : 0;

            if ($proposedPrice > $stdPrice && $stdPrice > 0) {
                $hasPriceIncrease = true;
            }

            if ($minStock > 0 && $currentStock < $minStock) {
                $stockStatus = 'CRITICAL';
                $hasCriticalStock = true;
            } elseif ($proposedQty > 0 && $currentStock > ($proposedQty * 3) && $currentStock > 1000) {
                $stockStatus = 'OVERSTOCK';
                $hasOverstock = true;
            } elseif ($minStock > 0 && $currentStock <= ($minStock * 1.25)) {
                $stockStatus = 'LOW';
            } else {
                $stockStatus = 'NORMAL';
            }

            // Rekomendasi kelayakan
            if ($stockStatus === 'CRITICAL') {
                $recommendation = 'CRITICAL';
            } elseif ($stockStatus === 'OVERSTOCK') {
                $recommendation = 'OVERSTOCK_CAUTION';
            } elseif ($diffPct > 5) {
                $recommendation = 'PRICE_CAUTION';
            } else {
                $recommendation = 'RECOMMENDED';
            }

            $analyzedItems[] = [
                'dtl'            => $dtl,
                'nama_barang'    => $dtl->barang?->barang_nm ?? ($dtl->barang_nm ?? 'Komoditas'),
                'satuan'         => $dtl->satuan_nm ?? ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Unit'),
                'pesan_qty'      => $proposedQty,
                'stok_riil'      => $currentStock,
                'batas_min'      => $minStock,
                'harga_pesan'    => $proposedPrice,
                'harga_std'      => $stdPrice,
                'price_diff'     => $diff,
                'price_diff_pct' => $diffPct,
                'stock_status'   => $stockStatus,
                'recommendation' => $recommendation,
            ];
        }

        $user = Auth::user();
        $canApprove = $user ? (method_exists($user, 'canApprovePo') ? $user->canApprovePo() : true) : true;

        return view('approval.po.review', compact('po', 'analyzedItems', 'hasPriceIncrease', 'hasCriticalStock', 'hasOverstock', 'canApprove'));
    }

    /**
     * Minta Tinjauan / Arahan Revisi ke staf pengadaan oleh Atasan
     */
    public function requestRevision(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'revision_notes' => 'required|string|min:3|max:1000',
        ], [
            'revision_notes.required' => 'Catatan arahan tinjauan/revisi wajib diisi agar staf mengetahui penyesuaian yang harus dilakukan.',
            'revision_notes.min'      => 'Catatan arahan minimal 3 karakter.',
        ]);

        try {
            $user = Auth::user();
            abort_unless($user && $user->canApprovePo(), 403, 'Anda tidak memiliki wewenang untuk meninjau Purchase Order.');

            $po = $this->poService->requestRevision($id, $user->name, $request->input('revision_notes'));

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Arahan tinjauan untuk PO {$po->po_no} berhasil dikirimkan ke staf pengadaan.",
                    'data'    => $po,
                ]);
            }

            return redirect()->route('gudang.po.show', $po->po_id)
                ->with('success', "Arahan tinjauan untuk PO {$po->po_no} berhasil dicatat. Status dokumen kini 'BUTUH REVISI'.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Tolak PO langsung dari website oleh Atasan
     */
    public function rejectInternal(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'reject_reason' => 'required|string|min:3|max:1000',
        ], [
            'reject_reason.required' => 'Alasan penolakan pengadaan PO wajib diisi.',
            'reject_reason.min'      => 'Alasan penolakan minimal 3 karakter.',
        ]);

        try {
            $user = Auth::user();
            abort_unless($user && $user->canApprovePo(), 403, 'Anda tidak memiliki wewenang untuk menolak Purchase Order.');

            $po = $this->poService->rejectPo($id, $user->name, $request->input('reject_reason'));

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Purchase Order {$po->po_no} berhasil ditolak secara resmi.",
                    'data'    => $po,
                ]);
            }

            return redirect()->route('gudang.po.show', $po->po_id)
                ->with('success', "Purchase Order {$po->po_no} telah ditolak.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Kirim salinan dokumen PDF resmi ke email Atasan (Manual On-Demand)
     */
    public function sendAtasanCopy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $po = DatPoHdr::with(['approver', 'supplier'])->findOrFail($id);
            $user = Auth::user();
            $targetEmail = $po->approver?->email ?: ($user?->email ?: null);

            if (!$targetEmail) {
                throw new Exception("Alamat email atasan tidak ditemukan pada profil akun.");
            }

            // Gunakan mail dispatch atau kirim arsip PDF
            $sent = $this->poService->sendApprovalEmail($po);
            if (!$sent) {
                throw new Exception("Gagal mengirimkan salinan email ke {$targetEmail}.");
            }

            $msg = "Salinan dokumen PO {$po->po_no} berhasil dikirimkan ke email atasan ({$targetEmail}).";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ]);
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Setujui PO langsung dari dashboard ERP oleh Atasan yang sedang login
     */
    public function approveInternal(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $user = \Illuminate\Support\Facades\Auth::user();
            abort_unless($user && $user->canApprovePo(), 403, 'Anda tidak memiliki wewenang untuk menyetujui Purchase Order.');

            $po = $this->poService->approvePo($id, $user->name, $request->input('notes', 'Disetujui melalui web ERP.'));

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Purchase Order {$po->po_no} berhasil disetujui resmi.",
                    'data'    => $po,
                ]);
            }

            return back()->with('success', "Purchase Order {$po->po_no} berhasil disetujui resmi.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
