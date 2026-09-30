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

        // KPI Ringkasan Metrik
        $kpi = [
            'total_so'       => DatSoHdr::active()->count(),
            'so_bulan_ini'   => DatSoHdr::active()->whereMonth('so_tgl', date('m'))->whereYear('so_tgl', date('Y'))->count(),
            'total_omzet'    => DatSoHdr::active()->where('status_cd', '!=', 'CANCELLED')->sum('total_tagihan'),
            'so_pending'     => DatSoHdr::active()->whereIn('status_cd', ['APPROVED', 'PROCESSING', 'PARTIAL'])->count(),
        ];

        // Master data untuk filter pencarian (Hanya Barang Hasil Produksi / FG)
        $customers = MstCustomer::active()->orderBy('customer_nm')->get(['customer_id', 'customer_cd', 'customer_nm']);
        $barangs = MstBarang::produkJadi()->active()->orderBy('barang_nm')->get(['barang_id', 'barang_cd', 'barang_nm']);

        return view('penjualan.so.index', compact('orders', 'kpi', 'customers', 'barangs', 'search', 'status', 'filters'));
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

        return view('penjualan.so.create', compact('soNo', 'fakturNo', 'suratJalanNo', 'customers', 'barangs'));
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
        return view('penjualan.so.show', compact('order'));
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

        return view('penjualan.so.edit', compact('order', 'customers', 'barangs'));
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
