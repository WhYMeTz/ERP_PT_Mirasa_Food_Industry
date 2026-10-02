<?php

namespace App\Http\Controllers\Produksi;

use App\Exports\Produksi\HasilProduksiExport;
use App\Exports\Produksi\HasilProduksiTemplate;
use App\Exports\Produksi\RekapHppExport;
use App\Http\Controllers\Controller;
use App\Imports\Produksi\HasilProduksiImport;
use App\Models\Gudang\DatPakaiHdr;
use App\Models\MasterData\MstGudang;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use App\Services\Produksi\ProduksiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProduksiController extends Controller
{
    public function __construct(
        protected ProduksiService $produksiService,
        protected CodeGeneratorService $codeGenerator,
        protected StokService $stokService
    ) {}

    /**
     * Halaman Utama Modul Produksi (Dual-Tab):
     * Tab 1: 📦 Hasil Barang Produksi & Sisa Stok WIP (Project Brief Poin 9)
     * Tab 2: 📊 Buku Rekap Pembaca HPP & Rendemen (Project Brief Poin 13)
     */
    public function index(Request $request): View
    {
        $activeTab = $request->input('tab', 'hasil');

        // Master Gudang untuk filter dropdown
        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        // ── DATA TAB 1: Hasil Barang Produksi (Point 9) ──
        $filters = [
            'gudang_id'  => $request->input('gudang_id'),
            'search'     => $request->input('search'),
            'batch_no'   => $request->input('batch_no'),
            'kategori'   => $request->input('kategori'),
            'tgl_dari'   => $request->input('tgl_dari'),
            'tgl_sampai' => $request->input('tgl_sampai'),
        ];
        $hasilItems = $this->produksiService->getHasilProduksiList($filters, 20);

        // ── DATA TAB 2: Rekap HPP Harian (Point 13) ──
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));
        $report = $this->produksiService->getMonthlyReport($year, $month);

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $yearsList = [2024, 2025, 2026, 2027];
        $monthName = $monthsList[$month] ?? 'Januari';

        return view('produksi.index', compact(
            'activeTab',
            'gudangList',
            'filters',
            'hasilItems',
            'report',
            'year',
            'month',
            'monthName',
            'monthsList',
            'yearsList'
        ));
    }

    /**
     * Formulir Input Hasil Produksi Harian Cepat & Cerdas.
     */
    public function create(Request $request): View
    {
        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        // Ambil daftar pemakaian bahan yang belum pernah dipakai atau daftar terbaru
        $pakaiList = DatPakaiHdr::with('details.barang')
            ->where('deleted_st', false)
            ->orderBy('pakai_tgl', 'desc')
            ->limit(30)
            ->get();

        return view('produksi.create', compact('gudangList', 'pakaiList'));
    }

    /**
     * Endpoint AJAX: Ekstrak data bahan dari Dokumen Pemakaian Bahan Gudang.
     */
    public function getPakaiData(int $pakaiId): JsonResponse
    {
        try {
            $summary = $this->produksiService->extractPakaiSummary($pakaiId);
            return response()->json([
                'status' => 'success',
                'data'   => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memuat rincian pemakaian bahan: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Endpoint AJAX: Cek nomor urut karton awal yang disarankan berdasarkan tanggal & shift.
     */
    public function getNextKarton(Request $request): JsonResponse
    {
        try {
            $tgl = $request->input('tgl', date('Y-m-d'));
            $shift = $request->input('shift', 'A');
            $data = $this->produksiService->getNextKartonAwal($tgl, $shift);

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memeriksa nomor karton: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Simpan data hasil produksi harian & suntik stok fisik WIP jika POSTED.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'produksi_tgl'                => 'required|date',
            'gudang_id'                   => 'required|exists:mst_gudang,gudang_id',
            'lini_produksi'               => 'required|string|max:100',
            'shift_cd'                    => 'nullable|string|in:A,B,a,b',
            'jam_produksi'                => 'nullable|string|max:10',
            'varietas_singkong'           => 'nullable|string|max:100',
            'qty_karton'                  => 'nullable|integer|min:0',
            'no_karton_awal'              => 'nullable|integer|min:1',
            'no_karton_akhir'             => 'nullable|integer|min:1',
            'batch_wip_no'                => 'nullable|string|max:100',
            'pakai_id'                    => 'nullable|exists:dat_pakai_hdr,pakai_id',
            'status_cd'                   => 'required|in:DRAFT,POSTED',

            // Bahan
            'singkong_qty'                => 'nullable|numeric|min:0',
            'singkong_nilai'              => 'nullable|numeric|min:0',
            'minyak_sawit_qty'            => 'nullable|numeric|min:0',
            'minyak_kelapa_qty'           => 'nullable|numeric|min:0',
            'minyak_nilai'                => 'nullable|numeric|min:0',
            'bumbu_nilai'                 => 'nullable|numeric|min:0',
            'karton_baru_nilai'           => 'nullable|numeric|min:0',
            'karton_bekas_nilai'          => 'nullable|numeric|min:0',
            'plastik_hd_nilai'            => 'nullable|numeric|min:0',
            'lakban_besar_nilai'          => 'nullable|numeric|min:0',
            'lakban_kecil_nilai'          => 'nullable|numeric|min:0',
            'tali_rafia_nilai'            => 'nullable|numeric|min:0',

            // Energi
            'cng_mmbtu'                   => 'nullable|numeric|min:0',
            'cng_tarif'                   => 'nullable|numeric|min:0',
            'cng_nilai'                   => 'nullable|numeric|min:0',

            // Tenaga Kerja
            'tk_langsung_org'             => 'nullable|integer|min:0',
            'tk_tidak_langsung_org'       => 'nullable|integer|min:0',
            'tk_training_org'             => 'nullable|integer|min:0',
            'tk_tarif_per_org'            => 'nullable|numeric|min:0',
            'tk_total_nilai'              => 'nullable|numeric|min:0',

            // Overhead
            'fotocopy_nilai'              => 'nullable|numeric|min:0',
            'sarung_tangan_plastik_nilai' => 'nullable|numeric|min:0',
            'sarung_tangan_kain_nilai'    => 'nullable|numeric|min:0',
            'qc_pengawasan_nilai'         => 'nullable|numeric|min:0',
            'listrik_air_telp_nilai'      => 'nullable|numeric|min:0',
            'pemeliharaan_mesin_nilai'    => 'nullable|numeric|min:0',
            'penyusutan_mesin_nilai'      => 'nullable|numeric|min:0',
            'limbah_padat_nilai'          => 'nullable|numeric|min:0',
            'limbah_kimia_nilai'          => 'nullable|numeric|min:0',

            // Hasil Output WIP
            'asin_barco_qty'              => 'nullable|numeric|min:0',
            'asin_sawit_qty'              => 'nullable|numeric|min:0',
            'no_salt_qty'                 => 'nullable|numeric|min:0',
            'balo_gelombang_qty'          => 'nullable|numeric|min:0',
            'berko_qty'                   => 'nullable|numeric|min:0',
            'berko_me_qty'                => 'nullable|numeric|min:0',
            'catatan_txt'                 => 'nullable|string',
        ]);

        try {
            $produksi = $this->produksiService->store($validated);

            $msg = "✅ Hasil Produksi {$produksi->produksi_no} tanggal " . Carbon::parse($produksi->produksi_tgl)->format('d/m/Y') . " berhasil disimpan! ";
            $msg .= "Rendemen: " . number_format($produksi->rendemen_persen, 2, ',', '.') . "% | HPP: Rp " . number_format($produksi->hpp_per_kg, 2, ',', '.') . " / kg.";

            $tglObj = Carbon::parse($produksi->produksi_tgl);
            return redirect()->route('produksi.index', ['tahun' => $tglObj->year, 'bulan' => $tglObj->month])
                ->with('success', $msg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan hasil produksi: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail lembar produksi harian, HPP riil, dan rincian karton.
     */
    public function show(int $id): View
    {
        $produksi = $this->produksiService->getById($id);
        return view('produksi.show', compact('produksi'));
    }

    /**
     * Tampilan cetak label stiker karton fisik (format label sticker box).
     */
    public function cetakStiker(Request $request, int $id): View
    {
        $produksi = $this->produksiService->getById($id);
        $noKarton = $request->input('karton_no'); // Opsional: cetak nomor karton tertentu
        return view('produksi.cetak-stiker', compact('produksi', 'noKarton'));
    }

    /**
     * Hapus lembar produksi harian.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->produksiService->delete($id);
            return back()->with('success', 'Catatan produksi harian berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel Daftar Hasil Barang Produksi (.xlsx) (Point 9.B.4)
     */
    public function exportHasilProduksi(Request $request): StreamedResponse
    {
        $filters = [
            'gudang_id'  => $request->input('gudang_id'),
            'search'     => $request->input('search'),
            'batch_no'   => $request->input('batch_no'),
            'kategori'   => $request->input('kategori'),
            'tgl_dari'   => $request->input('tgl_dari'),
            'tgl_sampai' => $request->input('tgl_sampai'),
        ];

        $items = $this->produksiService->getAllHasilProduksi($filters);

        $gudangNm = null;
        if (!empty($filters['gudang_id'])) {
            $gudang = MstGudang::find($filters['gudang_id']);
            $gudangNm = $gudang?->gudang_nm;
        }

        $printedBy = Auth::user()->username ?? 'Admin';
        $printedAt = Carbon::now()->format('d/m/Y H:i');

        $exporter = new HasilProduksiExport(
            $items,
            $gudangNm,
            $filters['search'],
            $filters['batch_no'],
            $printedBy,
            $printedAt
        );

        return $exporter->download('Laporan_Hasil_Produksi_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Download Template Resmi Import Excel Hasil Produksi (.xlsx) (Point 9.B.3)
     */
    public function downloadHasilTemplate(): StreamedResponse
    {
        $template = new HasilProduksiTemplate();
        return $template->download();
    }

    /**
     * Proses Upload File Excel Hasil Produksi (.xlsx) (Point 9.B.3)
     */
    public function importHasilProduksi(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'import_file.required' => 'Pilih file Excel yang akan diimpor.',
            'import_file.mimes'    => 'Format file harus berupa .xlsx atau .xls.',
            'import_file.max'      => 'Ukuran file tidak boleh melebihi 5 MB.',
        ]);

        try {
            $importer = new HasilProduksiImport($this->codeGenerator, $this->stokService);
            $importer->import($request->file('import_file'));

            $msg = "✅ Berhasil mengimpor {$importer->successCount} item hasil produksi!";
            if ($importer->errorCount > 0) {
                $msg .= " Catatan: terdapat {$importer->errorCount} grup data yang gagal atau dilewati.";
            }

            return redirect()->route('produksi.index', ['tab' => 'hasil'])
                ->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->route('produksi.index', ['tab' => 'hasil'])
                ->with('error', 'Gagal memproses import Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel Buku Rekap HPP Bulanan (.xlsx) (Point 13.c)
     */
    public function exportRekapExcel(Request $request): StreamedResponse
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));

        $report = $this->produksiService->getMonthlyReport($year, $month);

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsList[$month] ?? 'Januari';

        $printedBy = Auth::user()->username ?? 'Admin';
        $printedAt = Carbon::now()->format('d/m/Y H:i');

        $exporter = new RekapHppExport($report, $year, $month, $monthName, $printedBy, $printedAt);
        return $exporter->download("Rekap_HPP_{$monthName}_{$year}.xlsx");
    }

    /**
     * Export PDF Buku Rekap HPP Bulanan (.pdf) (Point 13.d)
     */
    public function exportRekapPdf(Request $request)
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));

        $report = $this->produksiService->getMonthlyReport($year, $month);

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsList[$month] ?? 'Januari';

        $pdf = Pdf::loadView('produksi.pdf.rekap-hpp', compact('report', 'year', 'month', 'monthName'))
            ->setPaper('legal', 'landscape');

        return $pdf->download("Rekap_HPP_{$monthName}_{$year}.pdf");
    }
}
