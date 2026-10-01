<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gudang\StorePoRequest;
use App\Models\Gudang\DatPoHdr;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstJenisSupplier;
use App\Models\MasterData\MstSupplier;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\PoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PoController extends Controller
{
    public function __construct(
        protected PoService $poService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');

        $user = Auth::user();
        $allowedGudangIds = ($user && $user->isSuperAdmin()) ? null : ($user ? $user->getAllowedGudangIds() : []);

        $poList = $this->poService->getAllPaginated($perPage, $search, $status, $allowedGudangIds);

        $rawCountsQuery = DatPoHdr::where('deleted_st', false);
        if (is_array($allowedGudangIds)) {
            $rawCountsQuery->whereIn('gudang_id', $allowedGudangIds);
        } elseif ($allowedGudangIds !== null) {
            $rawCountsQuery->where('gudang_id', $allowedGudangIds);
        }

        $rawCounts = $rawCountsQuery->selectRaw("
                COUNT(*) as all_count,
                COUNT(CASE WHEN status_cd = 'APPROVED' THEN 1 END) as approved_count,
                COUNT(CASE WHEN status_cd = 'PARTIAL' THEN 1 END) as partial_count,
                COUNT(CASE WHEN status_cd IN ('COMPLETED', 'CLOSED') THEN 1 END) as completed_count
            ")
            ->first();

        $statusCounts = [
            'all'       => (int) ($rawCounts->all_count ?? 0),
            'approved'  => (int) ($rawCounts->approved_count ?? 0),
            'partial'   => (int) ($rawCounts->partial_count ?? 0),
            'completed' => (int) ($rawCounts->completed_count ?? 0),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status'       => 'success',
                'message'      => 'Data Purchase Order berhasil diambil.',
                'status_counts'=> $statusCounts,
                'data'         => $poList,
            ]);
        }

        return view('gudang.po.index', compact('poList', 'search', 'status', 'statusCounts'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $supplierList = MstSupplier::active()
            ->with('jenisSupplier')
            ->orderBy('supplier_nm')
            ->get();
        $jenisSupplierList = MstJenisSupplier::active()
            ->orderBy('jenis_supplier_nm')
            ->get();
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];

        // Aturan Global PROSES_GUDANG: PO hanya boleh memuat Bahan Baku & Bahan Penolong (bukan WIP atau FG)
        $barangList = MstBarang::active()
            ->bahanBaku()
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get();
        $nextPoNo = $this->codeGenerator->generatePoNo();

        // Cek penugasan lokasi gudang akun user yang login
        $assignedGudangId = count($allowedGudangIds) === 1 ? $allowedGudangIds[0] : null;
        $isGudangLocked = count($allowedGudangIds) === 1 && !$user?->isSuperAdmin();

        // Peringatan Stok Minimum (Reorder Point Alert)
        $effectiveGudangForMin = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
        $belowMinimumList = $this->poService->getBarangBelowMinimum($effectiveGudangForMin);

        return view('gudang.po.create', compact(
            'supplierList',
            'jenisSupplierList',
            'gudangList',
            'barangList',
            'nextPoNo',
            'assignedGudangId',
            'isGudangLocked',
            'belowMinimumList'
        ));
    }

    public function store(StorePoRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $data = $request->validated();

            // Kunci gudang ke user jika ada penugasan default
            $user = Auth::user();
            if (!empty($user?->gudang_id) && !$user?->isSuperAdmin()) {
                $data['gudang_id'] = $user->gudang_id;
            }

            $result = $this->poService->store($data);

            if ($result instanceof \Illuminate\Support\Collection) {
                $count = $result->count();
                $poListStr = $result->map(fn($p) => "{$p->po_no} (" . ($p->supplier?->supplier_nm ?? 'Supplier') . ")")->implode(', ');

                if ($request->wantsJson()) {
                    return response()->json([
                        'status'  => 'success',
                        'message' => "⚡ Auto-Split Berhasil! Menerbitkan {$count} Purchase Order secara otomatis per supplier: {$poListStr}.",
                        'data'    => $result,
                    ], 201);
                }

                return redirect()
                    ->route('gudang.po.index')
                    ->with('success', "⚡ Auto-Split Berhasil! Menerbitkan {$count} Purchase Order secara otomatis per supplier: {$poListStr}.");
            }

            $po = $result;

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Dokumen Purchase Order {$po->po_no} berhasil dibuat.",
                    'data'    => $po,
                ], 201);
            }

            return redirect()
                ->route('gudang.po.show', $po->po_id)
                ->with('success', "Dokumen Purchase Order {$po->po_no} berhasil disimpan.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Request $request, int $id): View|JsonResponse
    {
        $po = $this->poService->getById($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Detail Purchase Order berhasil diambil.',
                'data'    => $po,
            ]);
        }

        return view('gudang.po.show', compact('po'));
    }

    public function cancel(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $reason = $request->input('alasan_batal', 'Dibatalkan oleh pengguna.');
            $po = $this->poService->cancel($id, $reason);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Purchase Order {$po->po_no} berhasil dibatalkan.",
                ]);
            }

            return redirect()
                ->route('gudang.po.index')
                ->with('success', "Purchase Order {$po->po_no} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function forceClose(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'closed_reason' => 'required|string|min:5|max:500',
        ], [
            'closed_reason.required' => 'Alasan penutupan PO wajib diisi.',
            'closed_reason.min'      => 'Alasan penutupan minimal 5 karakter.',
        ]);

        try {
            $user = Auth::user();
            $userName = $user ? ($user->name ?? $user->username) : 'Petugas';
            $po = $this->poService->forceClose($id, $request->input('closed_reason'), $userName);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Purchase Order {$po->po_no} berhasil ditutup.",
                    'data'    => $po,
                ]);
            }

            return redirect()
                ->route('gudang.po.show', $po->po_id)
                ->with('success', "Purchase Order {$po->po_no} berhasil ditutup.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Export Dokumen Resmi Purchase Order (Surat Permintaan Barang MFI/HACCP-04/FRM-03/050/VIII/2021) ke PDF.
     */
    public function exportPdf(int $id): \Illuminate\Http\Response
    {
        $po = $this->poService->getById($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gudang.po.pdf', [
            'po'         => $po,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->name ?? 'Staff Purchasing'),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $cleanPoNo = preg_replace('/[^A-Za-z0-9\-]/', '', $po->po_no);
        $fileName = 'PO-' . $cleanPoNo . '.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * Form Ubah Dokumen Purchase Order
     */
    public function edit(int $id): View|RedirectResponse
    {
        $user = Auth::user();
        $po = $this->poService->getById($id);

        // Hanya status final (COMPLETED, CLOSED, CANCELLED) yang tidak dapat diedit sama sekali (kecuali Super Admin)
        if (in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED']) && !$user?->isSuperAdmin()) {
            return redirect()
                ->route('gudang.po.show', $po->po_id)
                ->with('error', "Purchase Order {$po->po_no} dengan status {$po->status_cd} sudah selesai/ditutup dan tidak dapat diedit.");
        }

        $supplierList = MstSupplier::active()
            ->with('jenisSupplier')
            ->orderBy('supplier_nm')
            ->get();
        $jenisSupplierList = MstJenisSupplier::active()
            ->orderBy('jenis_supplier_nm')
            ->get();
        $gudangList = $user ? $user->getAllowedGudangList() : collect();

        // Bahan Baku & Bahan Penolong
        $barangList = MstBarang::active()
            ->bahanBaku()
            ->with(['satuanDasar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get();

        return view('gudang.po.edit', compact(
            'po',
            'supplierList',
            'jenisSupplierList',
            'gudangList',
            'barangList'
        ));
    }

    /**
     * Simpan Perubahan Purchase Order
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'po_tgl'                   => 'required|date',
            'supplier_id'              => 'required|exists:mst_supplier,supplier_id',
            'gudang_id'                => 'required|exists:mst_gudang,gudang_id',
            'tgl_estimasi_datang'      => 'nullable|date',
            'catatan_txt'              => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.podtl_id'         => 'nullable|integer',
            'items.*.barang_id'        => 'required|exists:mst_barang,barang_id',
            'items.*.pesan_qty'        => 'required|numeric|min:0.01',
            'items.*.harga_nominal'    => 'required|numeric|min:0',
            'items.*.diskon_persen'    => 'nullable|numeric|min:0|max:100',
            'items.*.potongan_nominal' => 'nullable|numeric|min:0',
            'items.*.ppn_tipe'         => 'nullable|string|in:NON_PPN,PPN_11',
            'items.*.catatan_txt'      => 'nullable|string|max:255',
        ], [
            'items.required'           => 'Minimal harus ada 1 item barang yang dipesan.',
            'items.min'                => 'Minimal harus ada 1 item barang yang dipesan.',
            'supplier_id.required'     => 'Supplier mitra wajib dipilih.',
            'gudang_id.required'       => 'Gudang tujuan wajib dipilih.',
        ]);

        try {
            $data = $request->all();
            $po = $this->poService->update($id, $data);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Dokumen Purchase Order {$po->po_no} berhasil diperbarui.",
                    'data'    => $po,
                ]);
            }

            return redirect()
                ->route('gudang.po.show', $po->po_id)
                ->with('success', "Dokumen Purchase Order {$po->po_no} berhasil diperbarui.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Hapus Dokumen Purchase Order
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $po = DatPoHdr::findOrFail($id);
            $poNo = $po->po_no;

            $this->poService->delete($id);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Purchase Order {$poNo} berhasil dihapus dari sistem.",
                ]);
            }

            return redirect()
                ->route('gudang.po.index')
                ->with('success', "Purchase Order {$poNo} berhasil dihapus dari sistem.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
