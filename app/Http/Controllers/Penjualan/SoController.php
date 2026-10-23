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
        $fakturNo = $this->codeGenerator->generateFakturManualNo(null, true);
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
        $gudangs = \App\Models\MasterData\MstGudang::active()->orderBy('gudang_nm')->get();

        return view('penjualan.so.show', compact('order', 'stockMap', 'kasBanks', 'gudangs'));
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
     * Catat Retur Penjualan (Customer Return) untuk SO ini.
     */
    public function storeReturn(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'retur_tgl'           => 'required|date',
            'gudang_id'           => 'required|exists:mst_gudang,gudang_id',
            'tindakan_cd'         => 'required|in:POTONG_TAGIHAN,TUKAR_GULING',
            'alasan_txt'          => 'nullable|string|max:500',
            'items'               => 'required|array|min:1',
            'items.*.sodtl_id'    => 'required|integer',
            'items.*.qty_retur'   => 'required|numeric|min:0',
            'items.*.kondisi_cd'  => 'nullable|string|max:50',
            'items.*.catatan_txt' => 'nullable|string|max:255',
        ], [
            'retur_tgl.required'   => 'Tanggal retur wajib diisi.',
            'gudang_id.required'   => 'Pilih gudang tujuan barang masuk.',
            'tindakan_cd.required' => 'Pilih tindakan komersial retur.',
            'items.required'       => 'Rincian barang retur wajib diisi.',
        ]);

        try {
            $retur = $this->soService->recordSalesReturn($id, $validated, auth()->user());

            $actionMsg = $retur->tindakan_cd === 'POTONG_TAGIHAN'
                ? "Sisa tagihan SO otomatis dikurangi Rp " . number_format((float) $retur->total_nilai, 0, ',', '.')
                : "Stok telah dicatat untuk ditukar guling secara fisik";

            return redirect()
                ->route('penjualan.so.show', $id)
                ->with('toast_success', "Retur Penjualan {$retur->retur_no} ({$retur->total_qty} unit) berhasil dicatat! {$actionMsg}.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses retur penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan Dokumen Retur Penjualan (Void).
     */
    public function voidReturn(Request $request, int $returId): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Alasan pembatalan retur wajib diisi.',
        ]);

        try {
            $retur = $this->soService->voidSalesReturn($returId, $request->input('reason'), auth()->user()?->nama_lengkap);

            return redirect()
                ->route('penjualan.so.show', $retur->so_id)
                ->with('toast_success', "Dokumen Retur {$retur->retur_no} berhasil dibatalkan (VOID) dan stok telah disesuaikan kembali.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
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
     * AJAX endpoint untuk menghasilkan nomor faktur real-time sesuai klasifikasi customer, PPN, dan tanggal.
     */
    public function ajaxGenerateFakturNo(Request $request): \Illuminate\Http\JsonResponse
    {
        $customerId = $request->input('customer_id');
        $isPpn = filter_var($request->input('is_ppn', false), FILTER_VALIDATE_BOOLEAN);
        $date = $request->input('date') ?: date('Y-m-d');

        $klasifikasi = $request->input('klasifikasi');
        $wilayah = $request->input('wilayah');

        if ($customerId) {
            $customer = MstCustomer::find($customerId);
            if ($customer) {
                $klasifikasi = $customer->klasifikasi ?: $klasifikasi;
                $wilayah = $customer->wilayah ?: ($customer->alamat_txt ?: $customer->customer_nm);

                if (empty($klasifikasi)) {
                    $nmUpper = strtoupper($customer->customer_nm);
                    $cdUpper = strtoupper($customer->customer_cd);
                    if (str_contains($nmUpper, 'INDOFOOD') || str_contains($nmUpper, 'IFM') || str_contains($nmUpper, 'FORTUNA') || str_contains($cdUpper, 'IFM')) {
                        $klasifikasi = 'IFM';
                    } elseif (str_contains($nmUpper, 'ALFA') || str_contains($nmUpper, 'INDOMARET') || str_contains($nmUpper, 'MIDI') || str_contains($cdUpper, 'IDM') || str_contains($cdUpper, 'ALFA')) {
                        $klasifikasi = 'IDM_ALFA';
                    } else {
                        $klasifikasi = 'MANUAL';
                    }
                }
            }
        }

        $fakturNo = $this->codeGenerator->generateFakturByKlasifikasi($klasifikasi, $wilayah, $isPpn, $date);

        return response()->json([
            'success'     => true,
            'faktur_no'   => $fakturNo,
            'klasifikasi' => $klasifikasi,
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
     * Otomatis menggunakan template Ber-PPN jika pesanan ber-PPN atau diminta via ?tipe=ppn.
     */
    public function exportFakturPdf(Request $request, int $id): Response
    {
        $order = $this->soService->getById($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $tipeParam = $request->query('tipe');
        $fakturUpper = strtoupper($order->faktur_no ?? '');
        $isPpn = $tipeParam === 'ppn' || ($tipeParam !== 'non_ppn' && (
            str_contains($fakturUpper, 'PM.A') ||
            $order->ppn_nominal > 0 ||
            $order->ppn_tipe === 'PPN_11'
        ));

        $viewName = $isPpn ? 'penjualan.so.pdf_faktur_ppn' : 'penjualan.so.pdf_faktur_non_ppn';

        $pdf = Pdf::loadView($viewName, [
            'order'      => $order,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
            'isPpn'      => $isPpn,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $prefix = $isPpn ? 'FAKTUR_PPN-' : 'FAKTUR_NON_PPN-';
        $fileName = $prefix . preg_replace('/[^A-Za-z0-9\-]/', '', $order->so_no) . '.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * Export Surat Jalan Pengiriman Barang (Delivery Order) ke format PDF.
     * Otomatis menggunakan template Ber-PPN jika pesanan ber-PPN atau diminta via ?tipe=ppn.
     */
    public function exportSuratJalanPdf(Request $request, int $id): Response
    {
        $order = $this->soService->getById($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $tipeParam = $request->query('tipe');
        $custNmUpper = strtoupper($order->customer?->customer_nm ?? '');
        $custCdUpper = strtoupper($order->customer?->customer_cd ?? '');
        $klasifikasi = $order->customer?->klasifikasi;
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
            $fakturUpper = strtoupper($order->faktur_no ?? '');
            $isPpn = $tipeParam === 'ppn' || ($tipeParam !== 'non_ppn' && (
                str_contains($fakturUpper, 'PM.A') ||
                $order->ppn_nominal > 0 ||
                $order->ppn_tipe === 'PPN_11'
            ));
            $viewName = $isPpn ? 'penjualan.so.pdf_surat_jalan_ppn' : 'penjualan.so.pdf_surat_jalan_non_ppn';
            $prefix = $isPpn ? 'SURAT_JALAN_PPN-' : 'SURAT_JALAN_NON_PPN-';
        }

        $pdf = Pdf::loadView($viewName, [
            'order'      => $order,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
            'isPpn'      => $isPpn,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $fileName = $prefix . preg_replace('/[^A-Za-z0-9\-]/', '', $order->so_no) . '.pdf';
        return $pdf->stream($fileName);
    }
}
