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
        if (!auth()->user()?->canAccessFaktur()) {
            abort(403, 'Akses ditolak: Dokumen faktur penjualan dan tagihan piutang hanya dapat diakses oleh Departemen Keuangan, Sales/Penjualan, atau Manajemen.');
        }

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
        $kasBanks = \App\Models\Keuangan\MstKasBank::active()->orderBy('nama_akun')->get();

        return view('penjualan.faktur.index', compact(
            'fakturList',
            'metrics',
            'kasBanks',
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
        if (!auth()->user()?->canAccessFaktur()) {
            abort(403, 'Akses ditolak: Dokumen faktur penjualan dan tagihan piutang hanya dapat diakses oleh Departemen Keuangan, Sales/Penjualan, atau Manajemen.');
        }

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
        if (!auth()->user()?->canAccessFaktur()) {
            abort(403, 'Akses ditolak: Dokumen faktur penjualan dan tagihan piutang hanya dapat diakses oleh Departemen Keuangan, Sales/Penjualan, atau Manajemen.');
        }

        $faktur = $this->fakturService->getById($id);
        $kasBanks = \App\Models\Keuangan\MstKasBank::active()->orderBy('nama_akun')->get();

        return view('penjualan.faktur.show', compact('faktur', 'kasBanks'));
    }

    /**
     * Catat Penerimaan Pembayaran Kas Masuk (BKM) langsung untuk Faktur Penjualan ini.
     */
    public function recordPayment(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'kas_bank_id'       => 'required|exists:mst_kas_bank,kas_bank_id',
            'tgl_terima'        => 'required|date',
            'nominal'           => 'required|numeric|min:1',
            'metode_terima'     => 'required|string|in:TRANSFER,CASH,GIRO_CEK,EDC_QRIS',
            'penyetor_nama'     => 'nullable|string|max:150',
            'no_referensi_bank' => 'nullable|string|max:100',
            'catatan'           => 'nullable|string|max:255',
        ], [
            'kas_bank_id.required' => 'Pilih rekening kas/bank tujuan penerimaan.',
            'tgl_terima.required'  => 'Tanggal pembayaran wajib diisi.',
            'nominal.required'     => 'Nominal pembayaran wajib diisi.',
            'nominal.min'          => 'Nominal pembayaran minimal Rp 1.',
        ]);

        try {
            $bkm = $this->fakturService->recordPayment($id, $validated, auth()->user());

            return back()->with('toast_success', "Pembayaran sebesar Rp " . number_format((float) $validated['nominal'], 0, ',', '.') . " berhasil dicatat dengan Bukti Kas Masuk {$bkm->bkm_no}!");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Lembar Dokumen Faktur Penjualan Resmi (PDF).
     */
    public function exportPdf(Request $request, int $id): Response
    {
        if (!auth()->user()?->canAccessFaktur()) {
            abort(403, 'Akses ditolak: Dokumen faktur penjualan dan tagihan piutang hanya dapat diakses oleh Departemen Keuangan, Sales/Penjualan, atau Manajemen.');
        }

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
        if (!auth()->user()?->canAccessFaktur()) {
            return back()->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi untuk membatalkan (void) Faktur Penjualan.');
        }

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
