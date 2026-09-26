<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gudang\StorePoRequest;
use App\Models\Gudang\DatPoHdr;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
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

        $poList = $this->poService->getAllPaginated($perPage, $search, $status);

        $rawCounts = DatPoHdr::where('deleted_st', false)
            ->selectRaw("
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
        $supplierList = MstSupplier::active()->orderBy('supplier_nm')->get();
        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();
        // Aturan Global PROSES_GUDANG: PO hanya boleh memuat Bahan Baku & Bahan Penolong
        $barangList = MstBarang::active()->bahanBaku()->with(['satuanDasar', 'jenisBarang'])->orderBy('barang_nm')->get();
        $nextPoNo = $this->codeGenerator->generatePoNo();

        // Cek penugasan lokasi gudang/pabrik akun user yang login
        $user = Auth::user();
        $assignedGudangId = $user?->gudang_id ?? null;
        $isGudangLocked = !empty($assignedGudangId) && !$user?->isSuperAdmin();

        // Peringatan Stok Minimum (Reorder Point Alert)
        $belowMinimumList = $this->poService->getBarangBelowMinimum($assignedGudangId);

        return view('gudang.po.create', compact(
            'supplierList',
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

            $po = $this->poService->store($data);

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
}
