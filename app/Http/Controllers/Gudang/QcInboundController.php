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
     * Otomatis memisahkan Tampilan Lapangan Mobile vs Web Admin Gudang Desktop
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'            => $request->input('search'),
            'kategori_barang'   => $request->input('kategori_barang'),
            'status_qc'         => $request->input('status_qc'),
            'status_uji_goreng' => $request->input('status_uji_goreng'),
            'supplier_id'       => $request->input('supplier_id'),
            'tgl_mulai'         => $request->input('tgl_mulai'),
            'tgl_selesai'       => $request->input('tgl_selesai'),
        ];

        $inspeksiList = $this->qcService->getAllPaginated($filters, 15);
        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();

        $isMobileReq = $request->input('view') === 'mobile';

        // Jika mode mobile smartphone
        if ($isMobileReq) {
            return view('gudang.qc.index-mobile', compact('inspeksiList', 'suppliers', 'filters'));
        }

        // Metrik Statistik Operasional Gudang & QC Inbound (Khusus Web Admin Desktop)
        $countSiap = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)->where('status_qc', 'SIAP_GUDANG')->count();
        $countFryer = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)->where('status_uji_goreng', 'MENUNGGU_LAB')->count();
        $countSelesai = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)->where('status_qc', 'DITERIMA_GUDANG')->count();
        $countReject = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)->where('status_qc', 'DITOLAK_TOTAL')->count();

        return view('gudang.qc.index', compact('inspeksiList', 'suppliers', 'filters', 'countSiap', 'countFryer', 'countSelesai', 'countReject'));
    }

    /**
     * Halaman Antrean Tiket QC Inbound Khusus Admin Gudang (Dialihkan terpadu ke Riwayat QC Inbound)
     */
    public function gudangAntrean(Request $request): RedirectResponse
    {
        return redirect()->route('qc.inbound.index', array_merge(['status_qc' => 'SIAP_GUDANG'], $request->all()));
    }

    /**
     * Cetak Lembar Checklist Mutu HACCP Resmi (A4) - Dialihkan langsung ke Direct Print pada Indeks QC
     */
    public function gudangHaccpCetak(int $id): RedirectResponse
    {
        return redirect()->route('qc.inbound.index', ['direct_print' => $id]);
    }

    /**
     * Formulir Uji QC Masuk (Mobile-First / Google Form Style)
     */
    public function create(Request $request): View
    {
        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();
        $gudangs = MstGudang::where('deleted_st', false)->where('active_st', true)->orderBy('gudang_nm')->get();
        
        // Ambil seluruh barang yang dibeli (Bahan Baku, Bahan Penolong, Plastik, Karton, dll; kecuali FG & WIP)
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->bahanProduksi()
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

        // Mendukung Pengujian II Singkong: Ambil data QC asal jika diteruskan via parent_qc_id
        $parentQc = null;
        if ($request->filled('parent_qc_id')) {
            $parentQc = \App\Models\Gudang\DatQcInboundHdr::with([
                'supplier',
                'gudang',
                'po',
                'details.barang',
                'terima.details'
            ])->where('deleted_st', false)->find($request->input('parent_qc_id'));
        }

        // Ambil daftar batch singkong aktif yang masih ada stok di gudang untuk Pengujian II
        $activeBatches = \App\Models\Gudang\DatStokBatch::with(['barang', 'gudang'])
            ->where('deleted_st', false)
            ->where('sisa_qty', '>', 0)
            ->whereHas('barang', function ($b) {
                $b->where('barang_nm', 'ilike', '%singkong%')
                  ->orWhere('barang_cd', 'ilike', '%SK%');
            })
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get()
            ->map(function ($stok) {
                $terimaDtl = \App\Models\Gudang\DatTerimaDtl::with(['header.supplier', 'header.po', 'header.qcInbound'])
                    ->where('batch_no', $stok->batch_no)
                    ->where('deleted_st', false)
                    ->first();

                $terimaHdr = $terimaDtl?->header;
                $qcAsal    = $terimaHdr?->qcInbound;
                $supplier  = $terimaHdr?->supplier ?? $qcAsal?->supplier;
                $po        = $terimaHdr?->po ?? $qcAsal?->po;

                return [
                    'stok_id'              => $stok->stok_id,
                    'batch_no'             => $stok->batch_no,
                    'barang_id'            => $stok->barang_id,
                    'barang_nm'            => $stok->barang?->barang_nm ?? 'Singkong',
                    'gudang_id'            => $stok->gudang_id,
                    'gudang_nm'            => $stok->gudang?->gudang_nm ?? '-',
                    'sisa_qty'             => (float) $stok->sisa_qty,
                    'supplier_id'          => $supplier?->supplier_id,
                    'supplier_nm'          => $supplier?->supplier_nm ?? 'Supplier',
                    'po_id'                => $po?->po_id,
                    'po_no'                => $po?->po_no ?? 'Non-PO',
                    'parent_qc_id'         => $qcAsal?->qc_id,
                    'parent_qc_no'         => $qcAsal?->qc_no,
                    'plat_nomor_truk'      => $qcAsal?->plat_nomor_truk,
                    'sopir_nama'           => $qcAsal?->sopir_nama,
                    'lokasi_panen'         => $qcAsal?->lokasi_panen,
                    'umur_singkong_bln'    => (float)($qcAsal?->umur_singkong_bln ?? 0),
                    'tgl_panen'            => $qcAsal?->tgl_panen?->format('Y-m-d'),
                    'surat_jalan_supplier' => $qcAsal?->surat_jalan_supplier,
                    'tgl_masuk'            => $stok->created_at ? $stok->created_at->format('d/m/Y') : '-',
                ];
            });

        $defaultTahap = $request->input('tahap') == 2 || $parentQc ? 'PENGUJIAN_2' : 'PENGUJIAN_1';

        return view('gudang.qc.create', compact(
            'suppliers',
            'gudangs',
            'barangs',
            'pos',
            'selectedPo',
            'parentQc',
            'activeBatches',
            'defaultTahap'
        ));
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
            'items.*.qty_timbang_gross' => 'nullable|numeric|min:0',
            'items.*.kadar_air_persen'  => 'nullable|numeric|min:0|max:100',
            'items.*.refraksi_persen'   => 'nullable|numeric|min:0|max:100',
            'items.*.qty_reject'        => 'nullable|numeric|min:0',
        ], [
            'supplier_id.required'       => 'Silakan pilih mitra supplier pengirim.',
            'gudang_id.required'         => 'Silakan tentukan gudang bongkar muat.',
            'items.required'             => 'Minimal harus ada 1 komoditas yang diuji.',
            'items.*.barang_id.required' => 'Komoditas barang harus dipilih.',
        ]);

        try {
            $user = Auth::user();
            $qc = $this->qcService->store($request->all(), $user);

            $redirectParams = ['id' => $qc->qc_id];
            if ($request->input('view') === 'mobile' || ($user?->isQc() && !$user?->isSuperAdmin() && !$user?->isGudang() && $request->input('view') !== 'desktop')) {
                $redirectParams['view'] = 'mobile';
            }

            return redirect()->route('qc.inbound.show', $redirectParams)
                ->with('success', "Inspeksi QC {$qc->qc_no} berhasil dicatat & diteruskan ke antrean Gudang.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat inspeksi QC: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan dokumen lembar hasil uji QC
     * Desktop: Dokumen Lembar Cetak HACCP Resmi PT Mirasa
     * Mobile: Kartu Ringkasan Mobile untuk Lapangan
     */
    public function show(Request $request, int $id): View
    {
        $qc = \App\Models\Gudang\DatQcInboundHdr::with([
            'supplier',
            'gudang',
            'po',
            'details.barang.satuanDasar',
            'details.poDetail',
            'terima',
        ])->where('deleted_st', false)->findOrFail($id);

        $isMobileReq = $request->input('view') === 'mobile';

        // Jika mode mobile smartphone
        if ($isMobileReq) {
            return view('gudang.qc.show-mobile', compact('qc'));
        }

        // Web Admin Desktop: Lembar Dokumen HACCP Format Cetak Resmi
        return view('gudang.qc.show', compact('qc'));
    }

    /**
     * Berita Acara Penolakan dialihkan ke wewenang Bagian Gudang (Retur Pembelian Inbound)
     */
    public function beritaAcara(int $id): RedirectResponse
    {
        return redirect()->route('qc.inbound.show', $id)
            ->with('error', 'Administrasi Berita Acara Penolakan dan Retur Bahan Baku dikelola langsung oleh Tim Gudang melalui Modul Retur Gudang.');
    }

    /**
     * Formulir Koreksi / Edit Tiket QC Inbound
     * Mendukung tampilan mobile edit biasa untuk smartphone QC dan formulir HACCP desktop
     */
    public function edit(Request $request, int $id): View|RedirectResponse
    {
        $qc = \App\Models\Gudang\DatQcInboundHdr::with([
            'supplier',
            'gudang',
            'po',
            'details.barang.satuanDasar',
            'details.poDetail',
            'terima',
        ])->where('deleted_st', false)->findOrFail($id);

        $user = Auth::user();
        if ($qc->terima && !$user->isSuperAdmin() && !$user->isGudang()) {
            return redirect()->route('qc.inbound.show', $id)
                ->with('error', "Tiket QC #{$qc->qc_no} sudah diproses ke Penerimaan Barang (GRN #{$qc->terima->terima_no}). Hanya Admin Gudang atau Super Administrator yang berhak mengedit tiket yang sudah ditarik ke gudang.");
        }

        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();
        $gudangs = MstGudang::where('deleted_st', false)->where('active_st', true)->orderBy('gudang_nm')->get();
        
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->bahanProduksi()
            ->where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('barang_nm')
            ->get();

        $pos = DatPoHdr::with(['supplier', 'details.barang'])
            ->where('deleted_st', false)
            ->where(function($q) use ($qc) {
                $q->whereIn('status_cd', ['APPROVED', 'PARTIAL']);
                if ($qc->po_id) {
                    $q->orWhere('po_id', $qc->po_id);
                }
            })
            ->orderBy('po_tgl', 'desc')
            ->get();

        $isMobileReq = $request->input('view') === 'mobile';

        // Jika mode mobile smartphone
        if ($isMobileReq) {
            return view('gudang.qc.edit-mobile', compact('qc', 'suppliers', 'gudangs', 'barangs', 'pos'));
        }

        // Web Admin Desktop: Formulir Dokumen HACCP Interaktif (Semua Kolom Bisa Diedit)
        return view('gudang.qc.edit', compact('qc', 'suppliers', 'gudangs', 'barangs', 'pos'));
    }

    /**
     * Memperbarui data tiket inspeksi QC (Koreksi)
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'supplier_id'               => 'required|exists:mst_supplier,supplier_id',
            'gudang_id'                 => 'required|exists:mst_gudang,gudang_id',
            'items'                     => 'required|array|min:1',
            'items.*.barang_id'         => 'required|exists:mst_barang,barang_id',
            'items.*.qty_timbang_gross' => 'nullable|numeric|min:0',
            'items.*.kadar_air_persen'  => 'nullable|numeric|min:0|max:100',
            'items.*.refraksi_persen'   => 'nullable|numeric|min:0|max:100',
            'items.*.qty_reject'        => 'nullable|numeric|min:0',
        ], [
            'supplier_id.required'       => 'Silakan pilih mitra supplier pengirim.',
            'gudang_id.required'         => 'Silakan tentukan gudang bongkar muat.',
            'items.required'             => 'Minimal harus ada 1 komoditas yang diuji.',
            'items.*.barang_id.required' => 'Komoditas barang harus dipilih.',
        ]);

        try {
            $qc = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)->findOrFail($id);
            $user = Auth::user();
            $updatedQc = $this->qcService->update($qc, $request->all(), $user);

            $msg = "Tiket QC {$updatedQc->qc_no} berhasil diperbarui.";
            if (($user->isSuperAdmin() || $user->isGudang()) && $updatedQc->terima) {
                $msg .= " Sinkronisasi otomatis ke Penerimaan Barang (#{$updatedQc->terima->terima_no}) & Batch Stok selesai tanpa unpost.";
            }

            // Jika user klik tombol "Simpan & Langsung Cetak A4"
            if ($request->input('and_print') == '1') {
                return redirect()->route('qc.inbound.index', ['direct_print' => $updatedQc->qc_id])
                    ->with('success', $msg);
            }

            if ($request->input('view') === 'mobile') {
                return redirect()->route('qc.inbound.show', ['id' => $updatedQc->qc_id, 'view' => 'mobile'])
                    ->with('success', $msg);
            }

            // Web Admin Desktop: Kembali langsung ke Indeks Riwayat QC Inbound (tanpa harus masuk halaman detail)
            return redirect()->route('qc.inbound.index')
                ->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui tiket QC: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus tiket QC
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $qc = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)->findOrFail($id);
            $user = Auth::user();
            $this->qcService->destroy($qc, $user);

            $isMobile = request('view') === 'mobile';
            $redirectParams = $isMobile ? ['view' => 'mobile'] : [];

            return redirect()->route('qc.inbound.index', $redirectParams)
                ->with('success', "Tiket QC #{$qc->qc_no} berhasil dibatalkan / dihapus.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus tiket QC: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui hasil uji goreng (Pengujian II) susulan
     */
    public function updateUjiGoreng(Request $request, int $id): RedirectResponse
    {
        try {
            $user = Auth::user();
            $this->qcService->updateUjiGoreng($id, $request->all(), $user);

            return redirect()->route('qc.inbound.show', $id)
                ->with('success', 'Hasil uji goreng lab (Pengujian II) berhasil disimpan & diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui hasil uji goreng: ' . $e->getMessage());
        }
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
