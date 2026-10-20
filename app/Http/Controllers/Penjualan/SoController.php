<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penjualan\StoreSoRequest;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstCustomer;
use App\Models\Penjualan\DatSoHdr;
use App\Services\Common\CodeGeneratorService;
use App\Services\Penjualan\SoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SoController extends Controller
{
    public function __construct(
        protected SoService $soService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Halaman index daftar PO Penjualan dengan fitur search, multi-filter, & KPI.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $filters = [
            'barang_id'   => $request->query('barang_id'),
            'barang_cd'   => $request->query('barang_cd'),
            'barang_nm'   => $request->query('barang_nm'),
            'customer_id' => $request->query('customer_id'),
            'customer_cd' => $request->query('customer_cd'),
            'customer_nm' => $request->query('customer_nm'),
            'date_from'   => $request->query('date_from'),
            'date_to'     => $request->query('date_to'),
        ];

        $orders = $this->soService->getAllPaginated(15, $search, $status, $filters);

        // Status Counts & Metrik Konsisten dengan PO Pembelian
        $rawCounts = DatSoHdr::where('deleted_st', false)
            ->selectRaw("
                COUNT(*) as all_count,
                COUNT(CASE WHEN status_cd = 'APPROVED' THEN 1 END) as approved_count,
                COUNT(CASE WHEN status_cd IN ('PROCESSING', 'PARTIAL') THEN 1 END) as partial_count,
                COUNT(CASE WHEN status_cd = 'COMPLETED' THEN 1 END) as completed_count,
                SUM(CASE WHEN status_cd != 'CANCELLED' THEN total_tagihan ELSE 0 END) as total_omzet
            ")
            ->first();

        $statusCounts = [
            'all'         => (int) ($rawCounts->all_count ?? 0),
            'approved'    => (int) ($rawCounts->approved_count ?? 0),
            'partial'     => (int) ($rawCounts->partial_count ?? 0),
            'completed'   => (int) ($rawCounts->completed_count ?? 0),
            'total_omzet' => (float) ($rawCounts->total_omzet ?? 0),
        ];

        // KPI Ringkasan Metrik
        $kpi = [
            'total_so'       => $statusCounts['all'],
            'so_bulan_ini'   => DatSoHdr::active()->whereMonth('so_tgl', date('m'))->whereYear('so_tgl', date('Y'))->count(),
            'total_omzet'    => $statusCounts['total_omzet'],
            'so_pending'     => DatSoHdr::active()->whereIn('status_cd', ['APPROVED', 'PROCESSING', 'PARTIAL'])->count(),
        ];

        // Master data untuk filter pencarian (Hanya Barang Hasil Produksi / FG)
        $customers = MstCustomer::active()->orderBy('customer_nm')->get(['customer_id', 'customer_cd', 'customer_nm']);
        $barangs = MstBarang::produkJadi()->active()->orderBy('barang_nm')->get(['barang_id', 'barang_cd', 'barang_nm']);

        return view('penjualan.so.index', compact('orders', 'kpi', 'statusCounts', 'customers', 'barangs', 'search', 'status', 'filters'));
    }

    /**
     * Form tambah PO Penjualan baru.
     */
    public function create(): View
    {
        $soNo = $this->codeGenerator->generateSoNo();
        $fakturNo = $this->codeGenerator->generateFakturNo();
        $suratJalanNo = $this->codeGenerator->generateSuratJalanNo();

        $customers = MstCustomer::active()->orderBy('customer_nm')->get();
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->produkJadi()
            ->active()
            ->orderBy('barang_nm')
            ->get();

        $stockMap = $this->soService->getStockAvailabilityMap($barangs->pluck('barang_id')->toArray());

        return view('penjualan.so.create', compact('soNo', 'fakturNo', 'suratJalanNo', 'customers', 'barangs', 'stockMap'));
    }

    /**
     * Simpan data PO Penjualan baru.
     */
    public function store(StoreSoRequest $request): RedirectResponse
    {
        try {
            $order = $this->soService->store($request->validated());

            return redirect()
                ->route('penjualan.so.show', $order->so_id)
                ->with('success', "PO Penjualan {$order->so_no} berhasil dibuat.");
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat pesanan penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan detail dokumen PO Penjualan.
     */
    public function show(int $id): View
    {
        $order = $this->soService->getById($id);
        $stockMap = $this->soService->getStockAvailabilityMap($order->details->pluck('barang_id')->toArray());
        $kasBanks = \App\Models\Keuangan\MstKasBank::active()->orderBy('nama_akun')->get();

        return view('penjualan.so.show', compact('order', 'stockMap', 'kasBanks'));
    }

    /**
     * Form edit dokumen PO Penjualan.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $order = $this->soService->getById($id);

        if (in_array($order->status_cd, ['COMPLETED', 'CANCELLED'])) {
            return redirect()
                ->route('penjualan.so.show', $order->so_id)
                ->with('error', "Dokumen status {$order->status_cd} tidak dapat diedit.");
        }

        $customers = MstCustomer::active()->orderBy('customer_nm')->get();
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->produkJadi()
            ->active()
            ->orderBy('barang_nm')
            ->get();

        $stockMap = $this->soService->getStockAvailabilityMap($barangs->pluck('barang_id')->toArray());

        return view('penjualan.so.edit', compact('order', 'customers', 'barangs', 'stockMap'));
    }

    /**
     * Update data dokumen PO Penjualan.
     */
    public function update(StoreSoRequest $request, int $id): RedirectResponse
    {
        try {
            $order = $this->soService->update($id, $request->validated());

            return redirect()
                ->route('penjualan.so.show', $order->so_id)
                ->with('success', "PO Penjualan {$order->so_no} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Konfirmasi Pengiriman Surat Jalan & Pemotongan Stok Barang Jadi (FEFO).
     */
    public function confirmDelivery(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'tgl_kirim_aktual' => 'required|date',
            'supir_nm'         => 'nullable|string|max:100',
            'nopol_kendaraan'  => 'nullable|string|max:30',
        ], [
            'tgl_kirim_aktual.required' => 'Tanggal pengiriman wajib diisi.',
        ]);

        try {
            $order = $this->soService->confirmDelivery($id, $validated, auth()->user()?->nama_lengkap);

            return redirect()
                ->route('penjualan.so.show', $order->so_id)
                ->with('toast_success', "Pengiriman barang {$order->so_no} berhasil dikonfirmasi! Stok Gudang Barang Jadi (FG) telah otomatis dipotong via FEFO dengan Surat Jalan {$order->surat_jalan_no}.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses pengiriman: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan Pengiriman Barang & Kembalikan Stok ke Gudang FG.
     */
    public function voidDelivery(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Alasan pembatalan pengiriman wajib diisi.',
        ]);

        try {
            $order = $this->soService->voidDelivery($id, $request->input('reason'), auth()->user()?->nama_lengkap);

            return redirect()
                ->route('penjualan.so.show', $order->so_id)
                ->with('toast_success', "Pengiriman barang {$order->so_no} berhasil dibatalkan dan seluruh stok fisik telah dikembalikan ke Gudang Barang Jadi.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan pengiriman: ' . $e->getMessage());
        }
    }

    /**
     * Catat Penerimaan Pembayaran Kas Masuk (BKM) dari Pelanggan.
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
            $bkm = $this->soService->recordPayment($id, $validated, auth()->user());

            return redirect()
                ->route('penjualan.so.show', $id)
                ->with('toast_success', "Pembayaran sebesar Rp " . number_format((float) $validated['nominal'], 0, ',', '.') . " berhasil dicatat dengan Bukti Kas Masuk {$bkm->bkm_no}!");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint untuk mengecek ketersediaan stok fisik barang jadi real-time.
     */
    public function ajaxStockCheck(Request $request): \Illuminate\Http\JsonResponse
    {
        $barangId = (int) $request->input('barang_id');
        if (!$barangId) {
            return response()->json(['success' => false, 'message' => 'Barang ID wajib diisi.'], 400);
        }

        $stockInfo = $this->soService->checkStockItem($barangId);

        return response()->json([
            'success' => true,
            'data'    => $stockInfo,
        ]);
    }

    /**
     * Batalkan dokumen PO Penjualan.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        try {
            $order = $this->soService->cancel($id, $request->input('reason'));
            return redirect()
                ->route('penjualan.so.show', $order->so_id)
                ->with('success', "PO Penjualan {$order->so_no} telah dibatalkan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete dokumen PO Penjualan.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->soService->delete($id);
            return redirect()
                ->route('penjualan.so.index')
                ->with('success', 'Dokumen PO Penjualan berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Export Faktur Penjualan / Invoice Penjualan ke format PDF.
     */
    public function exportFakturPdf(int $id): Response
    {
        $order = $this->soService->getById($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('penjualan.so.pdf_faktur', [
            'order'      => $order,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $fileName = 'FAKTUR-' . preg_replace('/[^A-Za-z0-9\-]/', '', $order->so_no) . '.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * Export Surat Jalan Pengiriman Barang (Delivery Order) ke format PDF.
     */
    public function exportSuratJalanPdf(int $id): Response
    {
        $order = $this->soService->getById($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('penjualan.so.pdf_surat_jalan', [
            'order'      => $order,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $fileName = 'SURAT_JALAN-' . preg_replace('/[^A-Za-z0-9\-]/', '', $order->so_no) . '.pdf';
        return $pdf->stream($fileName);
    }
}
