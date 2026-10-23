<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penjualan\StoreFakturRequest;
use App\Services\Common\CodeGeneratorService;
use App\Services\Penjualan\FakturPenjualanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FakturPenjualanController extends Controller
{
    public function __construct(
        protected FakturPenjualanService $fakturService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Halaman Utama: Rekapitulasi Dokumen Faktur Penjualan (Commercial Invoice).
     */
    public function index(Request $request): View
    {
        $perPage  = (int) $request->input('per_page', 15);
        $search   = $request->input('search');
        $status   = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $fakturList = $this->fakturService->getAllPaginated(
            perPage: $perPage,
            search: $search,
            status: $status,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );

        $metrics = $this->fakturService->getMetrics();

        return view('penjualan.faktur.index', compact(
            'fakturList',
            'metrics',
            'search',
            'status',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Halaman Penerbitan Faktur Penjualan Baru dari Surat Jalan yang telah sampai / terkirim.
     */
    public function create(Request $request): View
    {
        $uninvoicedSuratJalan = $this->fakturService->getUninvoicedSuratJalan();
        $selectedPengeluaranId = $request->input('pengeluaran_id') ? (int) $request->input('pengeluaran_id') : null;
        $prefilledData = null;

        if ($selectedPengeluaranId) {
            try {
                $prefilledData = $this->fakturService->getSuratJalanDetailForInvoice($selectedPengeluaranId);
            } catch (Exception $e) {
                $selectedPengeluaranId = null;
            }
        }

        return view('penjualan.faktur.create', compact(
            'uninvoicedSuratJalan',
            'selectedPengeluaranId',
            'prefilledData'
        ));
    }

    /**
     * AJAX: Ambil rincian Surat Jalan untuk pembentukan rincian tagihan faktur.
     */
    public function ajaxSuratJalanDetail(int $id): JsonResponse
    {
        try {
            $data = $this->fakturService->getSuratJalanDetailForInvoice($id);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat rincian Surat Jalan: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Simpan Faktur Penjualan resmi, kunci status Surat Jalan, dan akui piutang.
     */
    public function store(StoreFakturRequest $request): RedirectResponse
    {
        try {
            $userName = auth()->user()?->nama_lengkap ?? 'Administrator';
            $faktur = $this->fakturService->createFaktur($request->validated(), $userName);

            return redirect()
                ->route('penjualan.faktur.show', $faktur->faktur_id)
                ->with('toast_success', "Faktur Penjualan {$faktur->faktur_no} berhasil diterbitkan! Pengakuan piutang sebesar Rp " . number_format($faktur->total_tagihan, 0, ',', '.') . " telah tercatat.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menerbitkan Faktur Penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Detail Dokumen Faktur Penjualan Resmi.
     */
    public function show(int $id): View
    {
        $faktur = $this->fakturService->getById($id);

        return view('penjualan.faktur.show', compact('faktur'));
    }

    /**
     * Cetak Lembar Dokumen Faktur Penjualan Resmi (PDF).
     */
    public function exportPdf(Request $request, int $id): Response
    {
        $faktur = $this->fakturService->getById($id);
        $order = $faktur->order;

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $tipeParam = $request->query('tipe');
        $isPpn = $tipeParam === 'ppn' || ($tipeParam !== 'non_ppn' && (
            $faktur->ppn_tipe === 'PPN_11' ||
            $faktur->ppn_nominal > 0 ||
            str_contains(strtoupper($faktur->faktur_no ?? ''), 'PM.A')
        ));

        $viewName = $isPpn ? 'penjualan.so.pdf_faktur_ppn' : 'penjualan.so.pdf_faktur_non_ppn';

        // Berikan $order dan $faktur agar template yang ada maupun yang baru dapat membaca data secara kompatibel
        $pdf = Pdf::loadView($viewName, [
            'faktur'     => $faktur,
            'order'      => $order ?? $faktur, // Fallback ke objek faktur jika order relasi dipanggil langsung
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
            'isPpn'      => $isPpn,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $cleanFakturNo = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace('/', '-', $faktur->faktur_no));
        $prefix = $isPpn ? 'FAKTUR_PPN-' : 'FAKTUR_NON_PPN-';
        $fileName = $prefix . $cleanFakturNo . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Batalkan Faktur Penjualan (Void Invoice).
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Alasan pembatalan faktur penjualan wajib diisi.',
        ]);

        try {
            $userName = auth()->user()?->nama_lengkap ?? 'Administrator';
            $faktur = $this->fakturService->voidFaktur($id, $request->input('reason'), $userName);

            return redirect()
                ->route('penjualan.faktur.show', $faktur->faktur_id)
                ->with('toast_success', "Faktur Penjualan {$faktur->faktur_no} berhasil dibatalkan dan status Surat Jalan dikembalikan menjadi Terkirim.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan faktur penjualan: ' . $e->getMessage());
        }
    }
}
