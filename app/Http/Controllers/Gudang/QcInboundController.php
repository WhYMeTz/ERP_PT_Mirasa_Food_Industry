<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Gudang\DatPoHdr;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Services\Gudang\QcInboundService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QcInboundController extends Controller
{
    public function __construct(
        protected QcInboundService $qcService
    ) {}

    /**
     * Menampilkan daftar tiket inspeksi QC bahan masuk
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'      => $request->input('search'),
            'status_qc'   => $request->input('status_qc'),
            'supplier_id' => $request->input('supplier_id'),
            'tgl_mulai'   => $request->input('tgl_mulai'),
            'tgl_selesai' => $request->input('tgl_selesai'),
        ];

        $inspeksiList = $this->qcService->getAllPaginated($filters, 15);
        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();

        return view('gudang.qc.index', compact('inspeksiList', 'suppliers', 'filters'));
    }

    /**
     * Formulir Uji QC Masuk (Mobile-First / Google Form Style)
     */
    public function create(Request $request): View
    {
        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();
        $gudangs = MstGudang::where('deleted_st', false)->where('active_st', true)->orderBy('gudang_nm')->get();
        
        // Hanya ambil barang bahan baku, bahan penolong, dan bumbu
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->bahanBaku()
            ->where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('barang_nm')
            ->get();

        // PO aktif yang siap diterima (APPROVED atau PARTIAL)
        $pos = DatPoHdr::with(['supplier', 'details.barang'])
            ->where('deleted_st', false)
            ->whereIn('status_cd', ['APPROVED', 'PARTIAL'])
            ->orderBy('po_tgl', 'desc')
            ->get();

        $selectedPoId = $request->input('po_id');
        $selectedPo = $selectedPoId ? $pos->firstWhere('po_id', $selectedPoId) : null;

        return view('gudang.qc.create', compact('suppliers', 'gudangs', 'barangs', 'pos', 'selectedPo'));
    }

    /**
     * Menyimpan hasil uji inspeksi QC
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id'               => 'required|exists:mst_supplier,supplier_id',
            'gudang_id'                 => 'required|exists:mst_gudang,gudang_id',
            'items'                     => 'required|array|min:1',
            'items.*.barang_id'         => 'required|exists:mst_barang,barang_id',
            'items.*.qty_timbang_gross' => 'required|numeric|min:0.0001',
            'items.*.kadar_air_persen'  => 'nullable|numeric|min:0|max:100',
            'items.*.refraksi_persen'   => 'nullable|numeric|min:0|max:100',
            'items.*.qty_reject'        => 'nullable|numeric|min:0',
        ], [
            'supplier_id.required'               => 'Silakan pilih mitra supplier pengirim.',
            'gudang_id.required'                 => 'Silakan tentukan gudang bongkar muat.',
            'items.required'                     => 'Minimal harus ada 1 komoditas yang diuji.',
            'items.*.barang_id.required'         => 'Komoditas barang harus dipilih.',
            'items.*.qty_timbang_gross.required' => 'Berat timbangan kotor (gross) wajib diisi.',
            'items.*.qty_timbang_gross.min'      => 'Berat timbangan kotor harus lebih besar dari 0.',
        ]);

        try {
            $user = Auth::user();
            $qc = $this->qcService->store($request->all(), $user);

            return redirect()->route('qc.inbound.show', $qc->qc_id)
                ->with('success', "Inspeksi QC {$qc->qc_no} berhasil dicatat & diteruskan ke antrean Gudang.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat inspeksi QC: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan dokumen lembar hasil uji QC
     */
    public function show(int $id): View
    {
        $qc = \App\Models\Gudang\DatQcInboundHdr::with([
            'supplier',
            'gudang',
            'po',
            'details.barang.satuanDasar',
            'details.poDetail',
            'terima',
        ])->where('deleted_st', false)->findOrFail($id);

        return view('gudang.qc.show', compact('qc'));
    }

    /**
     * Cetak Berita Acara Penolakan Bahan Baku Singkong (HACCP Form: MFI/HACCP-04/FRM-03/041/VIII/2021)
     */
    public function beritaAcara(int $id): View
    {
        $qc = \App\Models\Gudang\DatQcInboundHdr::with([
            'supplier',
            'gudang',
            'po',
            'details.barang.satuanDasar',
        ])->where('deleted_st', false)->findOrFail($id);

        return view('gudang.qc.berita_acara', compact('qc'));
    }

    /**
     * API AJAX untuk Admin Gudang: Mengambil daftar tiket QC status SIAP_GUDANG
     */
    public function getSiapGudang(): JsonResponse
    {
        $tickets = $this->qcService->getSiapGudangTickets();
        return response()->json([
            'status'  => 'success',
            'tickets' => $tickets,
        ]);
    }

    /**
     * API AJAX untuk Admin Gudang: Mengambil detail item tiket QC untuk ditarik ke form terima
     */
    public function getTicketData(int $id): JsonResponse
    {
        try {
            $data = $this->qcService->getTicketData($id);
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tiket QC tidak ditemukan: ' . $e->getMessage(),
            ], 404);
        }
    }
}
