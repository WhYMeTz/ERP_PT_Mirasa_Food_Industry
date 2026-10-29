<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Gudang\DatPoHdr;
use App\Services\Gudang\PoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PoApprovalController extends Controller
{
    public function __construct(
        protected PoService $poService
    ) {}

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
            return view('gudang.po.approval_result', [
                'po'        => $po,
                'isSuccess' => true,
                'action'    => 'ALREADY_APPROVED',
                'message'   => "Purchase Order #{$po->po_no} telah disetujui resmi oleh {$po->approved_by} pada " . ($po->approved_at ? \Carbon\Carbon::parse($po->approved_at)->translatedFormat('d F Y H:i') : '') . " WIB. Dokumen sah dan tidak memerlukan tindakan lagi.",
            ]);
        }

        // 2. Jika dokumen sudah REJECTED sebelumnya
        if ($po->isRejected()) {
            return view('gudang.po.approval_result', [
                'po'        => $po,
                'isSuccess' => true,
                'action'    => 'ALREADY_REJECTED',
                'message'   => "Purchase Order #{$po->po_no} telah ditolak oleh {$po->rejected_by}. Alasan: {$po->reject_reason}",
            ]);
        }

        if ($action === 'reject') {
            return view('gudang.po.reject_form', compact('po', 'token'));
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

        return view('gudang.po.approval_result', [
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

        return view('gudang.po.approval_result', [
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
