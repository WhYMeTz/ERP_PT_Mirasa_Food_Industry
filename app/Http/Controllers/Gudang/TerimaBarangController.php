<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gudang\StoreTerimaBarangRequest;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\PoService;
use App\Services\Gudang\TerimaBarangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $gudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;

        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();

        if ($viewType === 'header') {
            $dataList = $this->terimaService->getAllPaginated($perPage, $search);
        } else {
            $dataList = $this->terimaService->getBarangMasukListPaginated($perPage, $search, $gudangId);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data penerimaan barang berhasil diambil.',
                'data'    => $dataList,
            ]);
        }

        return view('gudang.terima.index', compact('dataList', 'gudangList', 'search', 'gudangId', 'viewType'));
    }

    public function create(Request $request): View
    {
        $selectedPoId = $request->query('po_id');
        $selectedPo = null;

        if ($selectedPoId) {
            $selectedPo = $this->poService->getById((int) $selectedPoId);
        }

        $supplierList = MstSupplier::active()->orderBy('supplier_nm')->get();
        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();
        $barangList = MstBarang::active()->with(['satuanDasar', 'jenisBarang'])->orderBy('barang_nm')->get();
        $openPoList = $this->poService->getOpenPoList();
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
}
