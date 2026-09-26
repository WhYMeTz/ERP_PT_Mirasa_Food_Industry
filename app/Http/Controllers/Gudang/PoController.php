<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gudang\StorePoRequest;
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

        $user = Auth::user();
        $allowedGudangIds = ($user && $user->isSuperAdmin()) ? null : ($user ? $user->getAllowedGudangIds() : []);

        $poList = $this->poService->getAllPaginated($perPage, $search, $status, $allowedGudangIds);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Purchase Order berhasil diambil.',
                'data'    => $poList,
            ]);
        }

        return view('gudang.po.index', compact('poList', 'search', 'status'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $supplierList = MstSupplier::active()->orderBy('supplier_nm')->get();
        $gudangList = $user ? $user->getAllowedGudangList() : collect();
        $allowedGudangIds = $user ? $user->getAllowedGudangIds() : [];

        // Aturan Global PROSES_GUDANG: PO hanya boleh memuat Bahan Baku & Bahan Penolong
        $barangList = MstBarang::active()->bahanBaku()->with(['satuanDasar', 'jenisBarang'])->orderBy('barang_nm')->get();
        $nextPoNo = $this->codeGenerator->generatePoNo();

        // Cek penugasan lokasi gudang akun user yang login
        $assignedGudangId = count($allowedGudangIds) === 1 ? $allowedGudangIds[0] : null;
        $isGudangLocked = count($allowedGudangIds) === 1 && !$user?->isSuperAdmin();

        // Peringatan Stok Minimum (Reorder Point Alert)
        $effectiveGudangForMin = ($user && $user->isSuperAdmin()) ? null : $allowedGudangIds;
        $belowMinimumList = $this->poService->getBarangBelowMinimum($effectiveGudangForMin);

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
