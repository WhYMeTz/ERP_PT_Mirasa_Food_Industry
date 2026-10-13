<?php

namespace App\Http\Controllers\Produksi;

use App\Exports\Produksi\RekapHppPackagingExport;
use App\Http\Controllers\Controller;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\Produksi\DatPackagingHdr;
use App\Services\Produksi\PackagingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PackagingController extends Controller
{
    public function __construct(
        protected PackagingService $packagingService
    ) {}

    /**
     * Halaman index daftar riwayat Pengemasan Barang Jadi (Packaging & HPP).
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'         => $request->input('search'),
            'pack_tgl'       => $request->input('pack_tgl'),
            'barang_jadi_id' => $request->input('barang_jadi_id'),
            'status_cd'      => $request->input('status_cd'),
        ];

        $dataList = $this->packagingService->getAllPaginated(15, $filters);
        $metrics = $this->packagingService->getMetrics($filters);

        // Ambil daftar barang jadi aktif untuk filter
        $barangJadiList = MstBarang::active()
            ->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['FG', 'WIP']))
            ->orderBy('barang_nm')
            ->get(['barang_id', 'barang_cd', 'barang_nm']);

        return view('produksi.packaging.index', compact('dataList', 'metrics', 'filters', 'barangJadiList'));
    }

    /**
     * Form tambah pencatatan pengemasan baru (Smart Backflush BOM).
     */
    public function create(): View
    {
        $user = auth()->user();
        $gudangList = $user ? $user->getAllowedGudangList() : MstGudang::active()->get();

        // Cari Gudang FG sebagai rekomendasi default
        $defaultGudang = MstGudang::where('tipe_gudang_cd', 'FG')->active()->first()
            ?? MstGudang::active()->first();

        // Ambil daftar barang jadi untuk dropdown target kemas
        $barangJadiList = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->active()
            ->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['FG', 'WIP']))
            ->orderBy('barang_nm')
            ->get();

        return view('produksi.packaging.create', compact('gudangList', 'defaultGudang', 'barangJadiList'));
    }

    /**
     * AJAX endpoint untuk mengambil pratinjau resep BOM, tarikan stok WIP & estimasi HPP.
     */
    public function ajaxBomPreview(Request $request): JsonResponse
    {
        $barangJadiId = (int) $request->input('barang_jadi_id');
        $targetKartonQty = (float) $request->input('target_karton_qty', 100);

        if (!$barangJadiId) {
            return response()->json(['success' => false, 'message' => 'Barang jadi target wajib dipilih.'], 400);
        }

        if ($targetKartonQty <= 0) {
            $targetKartonQty = 1;
        }

        try {
            $preview = $this->packagingService->getBomPreview($barangJadiId, $targetKartonQty);
            return response()->json(['success' => true, 'data' => $preview]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan dokumen pengemasan baru, menghitung HPP, dan memutasi stok.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pack_tgl'                 => 'required|date',
            'barang_jadi_id'           => 'required|exists:mst_barang,barang_id',
            'gudang_id'                => 'required|exists:mst_gudang,gudang_id',
            'bom_id'                   => 'nullable|exists:mst_bom_hdr,bom_id',
            'produksi_id'              => 'nullable|exists:dat_produksi_hdr,produksi_id',
            'target_karton_qty'        => 'required|numeric|min:0.01',
            'hasil_karton_qty'         => 'required|numeric|min:0.01',
            'isi_per_karton_pcs'       => 'nullable|numeric|min:1',
            'batch_no'                 => 'nullable|string|max:100',
            'expired_tgl'              => 'nullable|date',
            'total_biaya_tenaga_kerja' => 'nullable|numeric|min:0',
            'total_biaya_lain'         => 'nullable|numeric|min:0',
            'catatan_txt'              => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.barang_id'        => 'required|exists:mst_barang,barang_id',
            'items.*.kategori_bahan'   => 'required|string|max:30',
            'items.*.batch_no'         => 'nullable|string|max:100',
            'items.*.qty_standar'      => 'nullable|numeric|min:0',
            'items.*.qty_aktual'       => 'required|numeric|min:0',
            'items.*.satuan_cd'        => 'nullable|string|max:20',
            'items.*.harga_satuan'     => 'required|numeric|min:0',
            'items.*.catatan_txt'      => 'nullable|string|max:255',
        ], [
            'pack_tgl.required'        => 'Tanggal pengemasan wajib diisi.',
            'barang_jadi_id.required'  => 'Produk jadi target wajib dipilih.',
            'hasil_karton_qty.required'=> 'Jumlah karton hasil kemas wajib diisi.',
            'items.required'           => 'Minimal harus ada 1 komponen bahan kemasan/WIP yang dikonsumsi.',
        ]);

        try {
            $header = $this->packagingService->storePackaging($validated, auth()->user()?->name);

            return redirect()->route('produksi.packaging.show', $header->pack_id)
                ->with('success', "Proses pengemasan nomor {$header->pack_no} berhasil disimpan! HPP terbentuk: Rp " . number_format($header->hpp_per_karton, 0, ',', '.') . "/Karton.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pengemasan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan dokumen berita acara pengemasan & rincian HPP.
     */
    public function show(int $id): View
    {
        $header = DatPackagingHdr::with(['barangJadi.satuanDasar', 'gudang', 'details.barang.satuanDasar', 'bom'])->findOrFail($id);

        return view('produksi.packaging.show', compact('header'));
    }

    /**
     * Membatalkan (Void) Dokumen Pengemasan.
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:255',
        ], [
            'alasan_batal.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        try {
            $header = $this->packagingService->voidPackaging($id, $request->input('alasan_batal'), auth()->user()?->name);

            return redirect()->route('produksi.packaging.index')
                ->with('success', "Dokumen {$header->pack_no} berhasil dibatalkan dan seluruh mutasi stok telah dikembalikan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Khusus Buku Rekapitulasi Harga Pokok Produksi Pengemasan (HPP Packaging):
     * - Mode Harian: Analisis per hari dalam 1 bulan kalender (Beban Curah, Kemasan, Borongan & HPP/Karton)
     * - Mode Bulanan: Analisis tren per bulan dalam 1 tahun fiskal (Januari s/d Desember)
     */
    public function rekap(Request $request): View
    {
        $mode = $request->input('mode', 'harian'); // 'harian' atau 'bulanan'
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));
        $barangJadiId = $request->filled('barang_jadi_id') ? (int) $request->input('barang_jadi_id') : null;

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $yearsList = [2024, 2025, 2026, 2027];
        $monthName = $monthsList[$month] ?? 'Januari';

        $report = null;
        $yearlyReport = null;

        if ($mode === 'bulanan') {
            $yearlyReport = $this->packagingService->getYearlyReport($year, $barangJadiId);
        } else {
            $report = $this->packagingService->getMonthlyReport($year, $month, $barangJadiId);
        }

        $barangJadiList = MstBarang::active()
            ->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['FG', 'WIP']))
            ->orderBy('barang_nm')
            ->get(['barang_id', 'barang_cd', 'barang_nm']);

        return view('produksi.packaging.rekap', compact(
            'mode',
            'year',
            'month',
            'monthName',
            'monthsList',
            'yearsList',
            'report',
            'yearlyReport',
            'barangJadiList',
            'barangJadiId'
        ));
    }

    /**
     * Export data Rekapitulasi HPP Pengemasan ke file Excel (.xlsx).
     */
    public function exportRekapExcel(Request $request): StreamedResponse
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));
        $barangJadiId = $request->filled('barang_jadi_id') ? (int) $request->input('barang_jadi_id') : null;

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsList[$month] ?? 'Januari';

        $printedBy = auth()->user()?->username ?? 'Admin';
        $printedAt = Carbon::now()->format('d/m/Y H:i');

        $report = $this->packagingService->getMonthlyReport($year, $month, $barangJadiId);
        $exporter = new RekapHppPackagingExport($report, $year, $month, $monthName, $printedBy, $printedAt);
        return $exporter->download("Rekap_HPP_Packaging_{$monthName}_{$year}.xlsx");
    }

    /**
     * Cetak dokumen PDF resmi Rekapitulasi HPP Pengemasan (Legal Landscape).
     */
    public function exportRekapPdf(Request $request)
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));
        $barangJadiId = $request->filled('barang_jadi_id') ? (int) $request->input('barang_jadi_id') : null;

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsList[$month] ?? 'Januari';

        $report = $this->packagingService->getMonthlyReport($year, $month, $barangJadiId);
        $pdf = Pdf::loadView('produksi.pdf.rekap-hpp-packaging', compact('report', 'year', 'month', 'monthName'))
            ->setPaper('legal', 'landscape');
        return $pdf->stream("Rekap_HPP_Packaging_{$monthName}_{$year}.pdf");
    }
}
