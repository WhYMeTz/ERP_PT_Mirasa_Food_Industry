<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penjualan\StorePengeluaranRequest;
use App\Services\Common\CodeGeneratorService;
use App\Services\Penjualan\PengeluaranBarangService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PengeluaranBarangController extends Controller
{
    public function __construct(
        protected PengeluaranBarangService $pengeluaranService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Halaman Utama: Daftar Dokumen Pengeluaran Barang (Surat Jalan Outbound).
     */
    public function index(Request $request): View
    {
        $perPage  = (int) $request->input('per_page', 15);
        $search   = $request->input('search');
        $status   = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $suratJalanList = $this->pengeluaranService->getAllPaginated(
            perPage: $perPage,
            search: $search,
            status: $status,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );

        $metrics = $this->pengeluaranService->getMetrics();

        return view('penjualan.pengeluaran.index', compact(
            'suratJalanList',
            'metrics',
            'search',
            'status',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Halaman Buat Dokumen Pengeluaran Barang / Surat Jalan Baru.
     */
    public function create(Request $request): View
    {
        $readyOrders = $this->pengeluaranService->getReadyToShipOrders();
        $selectedSoId = $request->input('so_id') ? (int) $request->input('so_id') : null;
        $prefilledData = null;

        if ($selectedSoId) {
            try {
                $prefilledData = $this->pengeluaranService->getSoDetailForShipping($selectedSoId);
            } catch (Exception $e) {
                $selectedSoId = null;
            }
        }

        $defaultSjNo = $this->codeGenerator->generateSuratJalanNo(now()->toDateString());

        return view('penjualan.pengeluaran.create', compact(
            'readyOrders',
            'selectedSoId',
            'prefilledData',
            'defaultSjNo'
        ));
    }

    /**
     * AJAX: Ambil rincian data PO & stok fisik Gudang FG untuk form Surat Jalan.
     */
    public function ajaxSoDetail(int $id): JsonResponse
    {
        try {
            $data = $this->pengeluaranService->getSoDetailForShipping($id);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat rincian pesanan: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Simpan Pengeluaran Barang, potong stok FEFO di Gudang FG, dan terbitkan Surat Jalan.
     */
    public function store(StorePengeluaranRequest $request): RedirectResponse
    {
        try {
            $userName = auth()->user()?->nama_lengkap ?? 'Administrator';
            $pengeluaran = $this->pengeluaranService->createPengeluaran($request->validated(), $userName);

            return redirect()
                ->route('penjualan.pengeluaran.show', $pengeluaran->pengeluaran_id)
                ->with('toast_success', "Surat Jalan {$pengeluaran->pengeluaran_no} berhasil diterbitkan! Stok Gudang Barang Jadi (FG) telah otomatis dipotong via FEFO.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pengeluaran barang: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Detail Dokumen Pengeluaran Barang / Lembar Surat Jalan.
     */
    public function show(int $id): View
    {
        $pengeluaran = $this->pengeluaranService->getById($id);

        return view('penjualan.pengeluaran.show', compact('pengeluaran'));
    }

    /**
     * Cetak Lembar Dokumen Surat Jalan Resmi (PDF).
     * Otomatis memilih template IFM (Cap Payung), Ber-PPN, atau Non-PPN.
     */
    public function exportPdf(Request $request, int $id): Response
    {
        $pengeluaran = $this->pengeluaranService->getById($id);
        $order = $pengeluaran->order;

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $tipeParam = $request->query('tipe');
        $custNmUpper = strtoupper($pengeluaran->customer?->customer_nm ?? '');
        $custCdUpper = strtoupper($pengeluaran->customer?->customer_cd ?? '');
        $klasifikasi = $pengeluaran->customer?->klasifikasi;

        $isIfm = $tipeParam === 'ifm' || ($tipeParam !== 'ppn' && $tipeParam !== 'non_ppn' && (
            $klasifikasi === 'IFM' ||
            str_contains($custNmUpper, 'INDOFOOD') ||
            str_contains($custNmUpper, 'IFM') ||
            str_contains($custNmUpper, 'FORTUNA') ||
            str_contains($custCdUpper, 'IFM') ||
            str_contains($custCdUpper, 'INDOFOOD')
        ));

        if ($isIfm) {
            $viewName = 'penjualan.so.pdf_surat_jalan_ifm';
            $prefix = 'SURAT_JALAN_IFM-';
            $isPpn = false;
        } else {
            $fakturUpper = strtoupper($order?->faktur_no ?? '');
            $isPpn = $tipeParam === 'ppn' || ($tipeParam !== 'non_ppn' && (
                str_contains($fakturUpper, 'PM.A') ||
                ($order && $order->ppn_nominal > 0) ||
                ($order && $order->ppn_tipe === 'PPN_11')
            ));

            $viewName = $isPpn ? 'penjualan.so.pdf_surat_jalan_ppn' : 'penjualan.so.pdf_surat_jalan_non_ppn';
            $prefix = $isPpn ? 'SURAT_JALAN_PPN-' : 'SURAT_JALAN_NON_PPN-';
        }

        $pdf = Pdf::loadView($viewName, [
            'order'        => $order,
            'pengeluaran'  => $pengeluaran,
            'logoBase64'   => $logoBase64,
            'printedAt'    => now()->translatedFormat('d F Y H:i'),
            'printedBy'    => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
            'isPpn'        => $isPpn,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $cleanSjNo = preg_replace('/[^A-Za-z0-9\-]/', '', $pengeluaran->pengeluaran_no);
        $fileName = $prefix . $cleanSjNo . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Batalkan Pengiriman Surat Jalan & Kembalikan Stok Fisik ke Gudang FG.
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Alasan pembatalan pengiriman wajib diisi.',
        ]);

        try {
            $userName = auth()->user()?->nama_lengkap ?? 'Administrator';
            $pengeluaran = $this->pengeluaranService->voidPengeluaran($id, $request->input('reason'), $userName);

            return redirect()
                ->route('penjualan.pengeluaran.show', $pengeluaran->pengeluaran_id)
                ->with('toast_success', "Surat Jalan {$pengeluaran->pengeluaran_no} berhasil dibatalkan dan seluruh stok fisik telah dikembalikan ke Gudang FG.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan pengiriman: ' . $e->getMessage());
        }
    }
}
