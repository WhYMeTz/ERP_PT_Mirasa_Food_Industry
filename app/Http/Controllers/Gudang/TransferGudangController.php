<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstGudang;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\TransferGudangService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransferGudangController extends Controller
{
    public function __construct(
        protected TransferGudangService $transferService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Tampilan daftar riwayat dokumen transfer antar-gudang
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'           => $request->input('search'),
            'gudang_asal_id'   => $request->input('gudang_asal_id'),
            'gudang_tujuan_id' => $request->input('gudang_tujuan_id'),
            'status_st'        => $request->input('status_st'),
            'start_date'       => $request->input('start_date'),
            'end_date'         => $request->input('end_date'),
        ];

        $perPage = (int) $request->input('per_page', 15);
        $transfers = $this->transferService->getPaginated($filters, $perPage);

        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        return view('gudang.transfer.index', compact('transfers', 'filters', 'gudangList'));
    }

    /**
     * Formulir pencatatan transfer antar-gudang baru
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $allowedGudangs = $user ? $user->getAllowedGudangList() : collect();

        $allGudangs = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        $suggestedNo = $this->codeGenerator->generateTransferNo();

        return view('gudang.transfer.create', compact('allowedGudangs', 'allGudangs', 'suggestedNo'));
    }

    /**
     * Simpan transaksi transfer antar-gudang
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'transfer_no'      => ['nullable', 'string', 'max:50'],
            'transfer_tgl'     => ['required', 'date'],
            'gudang_asal_id'   => ['required', 'integer', 'exists:mst_gudang,gudang_id'],
            'gudang_tujuan_id' => ['required', 'integer', 'different:gudang_asal_id', 'exists:mst_gudang,gudang_id'],
            'no_surat_jalan'   => ['nullable', 'string', 'max:100'],
            'sopir_nama'       => ['nullable', 'string', 'max:100'],
            'nopol_kendaraan'  => ['nullable', 'string', 'max:50'],
            'catatan_txt'      => ['nullable', 'string', 'max:1000'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.barang_id'=> ['required', 'integer', 'exists:mst_barang,barang_id'],
            'items.*.batch_no' => ['required', 'string', 'max:100'],
            'items.*.grade_cd' => ['nullable', 'string', 'max:10'],
            'items.*.qty_transfer' => ['required', 'numeric', 'min:0.0001'],
            'items.*.catatan_txt'  => ['nullable', 'string', 'max:255'],
        ], [
            'gudang_asal_id.required'     => 'Gudang asal wajib dipilih.',
            'gudang_tujuan_id.required'   => 'Gudang tujuan wajib dipilih.',
            'gudang_tujuan_id.different'  => 'Gudang tujuan tidak boleh sama dengan gudang asal.',
            'items.required'              => 'Minimal harus ada 1 barang yang dipindahkan.',
            'items.*.qty_transfer.min'    => 'Kuantitas transfer harus lebih besar dari 0.',
        ]);

        try {
            $user = Auth::user();
            $transfer = $this->transferService->storeTransfer($request->all(), $user);

            $msg = "Transfer Antar-Gudang '{$transfer->transfer_no}' berhasil dibukukan. Stok gudang asal berkurang dan stok gudang tujuan telah bertambah.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                    'data'    => $transfer,
                ], 201);
            }

            return redirect()
                ->route('gudang.transfer.show', $transfer->transfer_id)
                ->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memproses transfer: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail dokumen transfer & surat jalan
     */
    public function show(int $id): View
    {
        $transfer = $this->transferService->getById($id);

        return view('gudang.transfer.show', compact('transfer'));
    }

    /**
     * Batalkan (VOID) dokumen transfer antar-gudang
     */
    public function void(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'alasan_void' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'alasan_void.required' => 'Alasan pembatalan (VOID) wajib diisi.',
            'alasan_void.min'      => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        try {
            $user = Auth::user();
            $transfer = $this->transferService->voidTransfer($id, $request->input('alasan_void'), $user);

            $msg = "Dokumen transfer '{$transfer->transfer_no}' berhasil dibatalkan (VOID). Mutasi stok telah dikembalikan ke kondisi semula.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg,
                ]);
            }

            return redirect()
                ->route('gudang.transfer.show', $transfer->transfer_id)
                ->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Gagal membatalkan transfer: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Ambil daftar batch yang tersedia di gudang asal
     */
    public function getBatches(Request $request): JsonResponse
    {
        $gudangId = (int) $request->input('gudang_id');
        $barangId = $request->input('barang_id') ? (int) $request->input('barang_id') : null;

        if (!$gudangId) {
            return response()->json(['status' => 'error', 'message' => 'Gudang ID wajib diisi.'], 400);
        }

        $batches = $this->transferService->getAvailableStockBatches($gudangId, $barangId);

        return response()->json([
            'status' => 'success',
            'data'   => $batches,
        ]);
    }
}
