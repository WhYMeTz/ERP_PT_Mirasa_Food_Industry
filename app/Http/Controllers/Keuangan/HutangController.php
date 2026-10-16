<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\DatHutangHdr;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Services\Keuangan\HutangService;
use App\Services\Keuangan\KasBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Exception;

class HutangController extends Controller
{
    protected HutangService $hutangService;
    protected KasBankService $kasBankService;

    public function __construct(HutangService $hutangService, KasBankService $kasBankService)
    {
        $this->hutangService = $hutangService;
        $this->kasBankService = $kasBankService;
    }

    /**
     * Halaman Indeks Daftar Hutang Usaha & Tagihan Pembelian (AP)
     */
    public function index(Request $request): View
    {
        $filters = [
            'status_pembayaran' => $request->input('status_pembayaran'),
            'supplier_id'       => $request->input('supplier_id'),
            'tipe_hutang'       => $request->input('tipe_hutang'),
            'search'            => $request->input('search'),
            'tgl_mulai'         => $request->input('tgl_mulai'),
            'tgl_selesai'       => $request->input('tgl_selesai'),
        ];

        $hutangList = $this->hutangService->getAllPaginated($filters, 15);
        $metrics = $this->hutangService->getSummaryMetrics();
        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();
        $kasBanks = $this->kasBankService->getAllKasBank();

        return view('keuangan.hutang.index', compact('hutangList', 'metrics', 'suppliers', 'kasBanks', 'filters'));
    }

    /**
     * Form Catat Tagihan Pembelian Baru (Tarik dari GRN)
     */
    public function create(Request $request): View
    {
        $unbilledSupplierSummary = $this->hutangService->getUnbilledSupplierSummary();
        $suppliers = MstSupplier::where('deleted_st', false)
            ->where('active_st', true)
            ->get()
            ->sortByDesc(function ($s) use ($unbilledSupplierSummary) {
                return $unbilledSupplierSummary[$s->supplier_id] ?? 0;
            })
            ->values();

        $gudangs = Auth::user()->getAllowedGudangList();
        $selectedSupplierId = $request->input('supplier_id');

        $unbilledGrn = [];
        if ($selectedSupplierId) {
            $unbilledGrn = $this->hutangService->getUnbilledGrnList((int) $selectedSupplierId);
        }

        return view('keuangan.hutang.create', compact('suppliers', 'gudangs', 'selectedSupplierId', 'unbilledGrn', 'unbilledSupplierSummary'));
    }

    /**
     * Simpan Tagihan Pembelian dari GRN
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id'      => 'required|exists:mst_supplier,supplier_id',
            'tgl_tagihan'      => 'required|date',
            'tgl_jatuh_tempo'  => 'required|date|after_or_equal:tgl_tagihan',
            'items'            => 'required|array|min:1',
            'items.*.barang_id'=> 'nullable|exists:mst_barang,barang_id',
            'items.*.qty'      => 'required|numeric|min:0.0001',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ], [
            'supplier_id.required'     => 'Silakan pilih rekanan supplier.',
            'items.required'           => 'Minimal harus ada 1 item barang yang ditagihkan.',
            'tgl_jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal tagihan.',
        ]);

        try {
            $hdr = $this->hutangService->createFromGrn($request->all(), Auth::user());

            return redirect()->route('keuangan.hutang.show', $hdr->hutang_id)
                ->with('success', "Tagihan Hutang Pembelian {$hdr->hutang_no} berhasil dicatat sebesar Rp " . number_format($hdr->total_tagihan, 0, ',', '.') . ".");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat tagihan hutang: ' . $e->getMessage());
        }
    }

    /**
     * Form Khusus Tagihan Gas Utilitas Bulanan (Pasca-Bayar Konsinyasi)
     */
    public function createGas(Request $request): View
    {
        $periode = $request->input('periode', date('Y-m'));
        $gasSummary = $this->hutangService->getGasUsageSummary($periode);
        
        // Cari supplier gas (misal PGN, Samator, Pertamina, atau semua supplier)
        $suppliers = MstSupplier::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('supplier_nm')
            ->get();

        return view('keuangan.hutang.create-gas', compact('periode', 'gasSummary', 'suppliers'));
    }

    /**
     * Simpan Tagihan Gas Utilitas Bulanan
     */
    public function storeGas(Request $request): RedirectResponse
    {
        $request->validate([
            'periode_bulan'    => 'required|string',
            'tgl_tagihan'      => 'required|date',
            'tgl_jatuh_tempo'  => 'required|date',
        ]);

        try {
            $hdr = $this->hutangService->createTagihanGas($request->all(), Auth::user());

            return redirect()->route('keuangan.hutang.show', $hdr->hutang_id)
                ->with('success', "Tagihan Konsinyasi Gas {$hdr->hutang_no} Periode {$hdr->periode_bulan} berhasil diterbitkan sebesar Rp " . number_format($hdr->total_tagihan, 0, ',', '.') . ".");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat tagihan gas: ' . $e->getMessage());
        }
    }

    /**
     * Detail Lembar Tagihan Hutang & Riwayat Pelunasan BKK
     */
    public function show(int $id): View
    {
        $hutang = DatHutangHdr::with([
            'supplier.jenisSupplier',
            'gudang',
            'details.barang.satuanDasar',
            'details.terima',
            'bayarDetails.header.kasBank',
        ])->where('deleted_st', false)->findOrFail($id);

        $kasBanks = $this->kasBankService->getAllKasBank();

        return view('keuangan.hutang.show', compact('hutang', 'kasBanks'));
    }

    /**
     * Perbarui No. Faktur Supplier dan Tanggal Jatuh Tempo oleh tim Keuangan
     */
    public function updateTempo(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'tgl_jatuh_tempo'    => 'required|date',
            'no_faktur_supplier' => 'nullable|string|max:100',
            'catatan'            => 'nullable|string|max:255',
        ]);

        try {
            $this->hutangService->updateTempoDanFaktur($id, $request->all(), Auth::user());

            return redirect()->route('keuangan.hutang.show', $id)
                ->with('success', "Informasi Faktur & Tanggal Jatuh Tempo berhasil diperbarui.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Hapus / Batalkan Tagihan
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $hutang = DatHutangHdr::where('deleted_st', false)->findOrFail($id);
            $this->hutangService->destroy($hutang, Auth::user());

            return redirect()->route('keuangan.hutang.index')
                ->with('success', "Tagihan Hutang {$hutang->hutang_no} berhasil dibatalkan.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan tagihan: ' . $e->getMessage());
        }
    }

    /**
     * AJAX API: Ambil daftar GRN belum ditagih berdasarkan supplier
     */
    public function ajaxUnbilledGrn(Request $request): JsonResponse
    {
        $supplierId = $request->input('supplier_id');
        $grnList = $this->hutangService->getUnbilledGrnList($supplierId ? (int) $supplierId : null);

        return response()->json([
            'status' => 'success',
            'data'   => $grnList,
        ]);
    }

    /**
     * AJAX API: Ambil rekap pemakaian gas bulanan
     */
    public function ajaxGasSummary(Request $request): JsonResponse
    {
        $periode = $request->input('periode', date('Y-m'));
        $summary = $this->hutangService->getGasUsageSummary($periode);

        return response()->json([
            'status' => 'success',
            'data'   => $summary,
        ]);
    }
}
