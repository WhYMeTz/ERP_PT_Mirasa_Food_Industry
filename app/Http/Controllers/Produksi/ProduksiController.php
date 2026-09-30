<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\Gudang\DatPakaiHdr;
use App\Models\MasterData\MstGudang;
use App\Services\Produksi\ProduksiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProduksiController extends Controller
{
    public function __construct(
        protected ProduksiService $produksiService
    ) {}

    /**
     * Halaman Buku Rekap HPP Harian & Rendemen (Format Excel Asli PT Mirasa).
     */
    public function index(Request $request): View
    {
        $year = (int) $request->input('tahun', 2026);
        $month = (int) $request->input('bulan', 1);

        $report = $this->produksiService->getMonthlyReport($year, $month);

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearsList = [2025, 2026, 2027];
        $monthName = $monthsList[$month] ?? 'Januari';

        return view('produksi.index', compact('report', 'year', 'month', 'monthName', 'monthsList', 'yearsList'));
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
     * Simpan data hasil produksi harian & suntik stok fisik WIP jika POSTED.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'produksi_tgl'                => 'required|date',
            'gudang_id'                   => 'required|exists:mst_gudang,gudang_id',
            'lini_produksi'               => 'required|string|max:100',
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
}
