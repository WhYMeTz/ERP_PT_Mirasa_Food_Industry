<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Gudang\DatAdjustmentHdr;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Services\Gudang\AdjustmentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdjustmentController extends Controller
{
    public function __construct(
        protected AdjustmentService $adjustmentService
    ) {}

    /**
     * Menampilkan Tabel Penyesuaian Persediaan (Stock Adjustment / Opname)
     * Sesuai Blueprint: Pencarian (Nama Barang, Kode Barang) & Filter (Tanggal, Nama, Kode, Gudang).
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'       => $request->input('search'),
            'barang_nm'    => $request->input('barang_nm'),
            'barang_cd'    => $request->input('barang_cd'),
            'tgl_mulai'    => $request->input('tgl_mulai'),
            'tgl_selesai'  => $request->input('tgl_selesai'),
            'adj_tgl'      => $request->input('adj_tgl'),
            'gudang_id'    => $request->input('gudang_id'),
            'tipe_selisih' => $request->input('tipe_selisih'),
        ];

        $user = auth()->user();
        $gudangList = $user ? $user->getAllowedGudangList() : MstGudang::active()->get();

        $details = $this->adjustmentService->getDetailsQuery($filters)->paginate(15)->withQueryString();
        $metrics = $this->adjustmentService->getMetrics($filters);

        return view('gudang.adjustment.index', compact('details', 'metrics', 'gudangList', 'filters'));
    }

    /**
     * Menampilkan Form Input Penyesuaian Stok Baru.
     */
    public function create(): View
    {
        $user = auth()->user();
        $gudangList = $user ? $user->getAllowedGudangList() : MstGudang::active()->get();

        // Ambil daftar barang aktif untuk dropdown autocomplete/select
        $barangList = MstBarang::with('satuanDasar')
            ->active()
            ->orderBy('barang_nm')
            ->get();

        return view('gudang.adjustment.create', compact('gudangList', 'barangList'));
    }

    /**
     * Menyimpan dokumen penyesuaian stok baru dan memperbarui stok batch & ledger.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gudang_id'             => 'required|exists:mst_gudang,gudang_id',
            'adj_tgl'               => 'required|date',
            'kategori_adj'          => 'required|string|max:50',
            'catatan_txt'           => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.barang_id'     => 'required|exists:mst_barang,barang_id',
            'items.*.batch_no'      => 'nullable|string|max:100',
            'items.*.stok_sistem_qty' => 'required|numeric',
            'items.*.harga_satuan'  => 'required|numeric|min:0',
            'items.*.stok_fisik_qty'  => 'required|numeric|min:0',
            'items.*.alasan_txt'    => 'nullable|string|max:255',
        ]);

        try {
            $header = $this->adjustmentService->storeAdjustment($validated, auth()->user()?->name);

            return redirect()->route('gudang.adjustment.index')
                ->with('success', "Penyesuaian stok berhasil disimpan dan diposting dengan nomor dokumen {$header->adj_no}.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan penyesuaian stok: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan Detail Dokumen Penyesuaian Stok.
     */
    public function show(int $id): View
    {
        $header = DatAdjustmentHdr::with(['gudang', 'details.barang.satuanDasar'])->findOrFail($id);

        return view('gudang.adjustment.show', compact('header'));
    }

    /**
     * Membatalkan (Void) Dokumen Penyesuaian Stok.
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:255',
        ]);

        try {
            $header = $this->adjustmentService->voidAdjustment($id, $request->input('alasan_batal'), auth()->user()?->name);

            return redirect()->route('gudang.adjustment.index')
                ->with('success', "Dokumen {$header->adj_no} berhasil dibatalkan dan mutasi stok telah dikembalikan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint untuk mengambil informasi real-time sisa stok sistem dan harga satuan barang di gudang.
     */
    public function ajaxItemInfo(Request $request): JsonResponse
    {
        $gudangId = (int) $request->input('gudang_id');
        $barangId = (int) $request->input('barang_id');

        if (!$gudangId || !$barangId) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap.'], 400);
        }

        try {
            $info = $this->adjustmentService->getStokAndHargaInfo($gudangId, $barangId);

            return response()->json(['success' => true, 'data' => $info]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
