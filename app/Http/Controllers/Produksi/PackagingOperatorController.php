<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstKaryawan;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\Produksi\DatProduksiDtl;
use App\Services\Produksi\LaporanKemasOperatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Exception;

class PackagingOperatorController extends Controller
{
    protected LaporanKemasOperatorService $operatorService;

    public function __construct(LaporanKemasOperatorService $operatorService)
    {
        $this->operatorService = $operatorService;
    }

    /**
     * Form Input Pengemasan Lapangan (Mobile-First untuk Operator HP)
     */
    public function create(): View
    {
        // 1. Barang Jadi (FG) - Filter ketat HANYA barang Finished Goods (FG)
        $barangJadiList = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->whereHas('jenisBarang', function ($q) {
                $q->where('jenis_barang_cd', 'FG');
            })
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        // 2. Varian Output WIP Hasil Masak Dapur (Option A)
        $wipList = DatProduksiDtl::with(['produksi', 'barang.satuanDasar'])
            ->where('deleted_st', false)
            ->whereHas('produksi', function ($q) {
                $q->where('deleted_st', false)
                  ->where('status_cd', '!=', 'VOID');
            })
            ->where('qty_kg', '>', 0)
            ->orderByDesc('output_id')
            ->get();

        // Lembar Masak WIP Curah - Diurutkan FIFO dengan eager loading detail output
        $produksiList = DatProduksiHdr::with(['details.barang.satuanDasar'])
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'VOID')
            ->orderBy('produksi_tgl', 'asc')
            ->orderBy('produksi_id', 'asc')
            ->get();

        // 3. Karyawan (Operator)
        $karyawanList = MstKaryawan::where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('karyawan_nm')
            ->get();

        // 4. Bumbu Tabur (Seasoning) - Khusus bahan penolong / bumbu tabur (Bukan FG/WIP)
        $bumbuList = MstBarang::where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%bumbu%')
                  ->orWhere('barang_nm', 'ILIKE', '%seasoning%')
                  ->orWhere('barang_nm', 'ILIKE', '%perasa%')
                  ->orWhere('barang_cd', 'ILIKE', 'BBL%')
                  ->orWhere('barang_cd', 'ILIKE', 'BBQ%')
                  ->orWhere('barang_cd', 'ILIKE', 'BCL%')
                  ->orWhere('barang_cd', 'ILIKE', 'BCS%')
                  ->orWhere('barang_cd', 'ILIKE', 'BSS%');
            })
            ->whereHas('jenisBarang', function ($q) {
                $q->whereNotIn('jenis_barang_cd', ['FG', 'WIP', 'BB', 'RAW']);
            })
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        // 5. Plastik Kemasan (Roll & Pouch)
        $plastikList = MstBarang::where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%plastik%')
                  ->orWhere('barang_nm', 'ILIKE', '%roll%')
                  ->orWhere('barang_nm', 'ILIKE', '%kemas%')
                  ->orWhere('barang_nm', 'ILIKE', '%pouch%');
            })
            ->whereHas('jenisBarang', fn($q) => $q->whereNotIn('jenis_barang_cd', ['FG', 'WIP', 'BB', 'RAW']))
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        // 6. Karton Box (Dus)
        $kartonList = MstBarang::where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%karton%')
                  ->orWhere('barang_nm', 'ILIKE', '%dus%')
                  ->orWhere('barang_nm', 'ILIKE', '%box%');
            })
            ->whereHas('jenisBarang', fn($q) => $q->whereNotIn('jenis_barang_cd', ['FG', 'WIP', 'BB', 'RAW']))
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        // 7. Lakban (Selotip / Tape - Filter Khusus Lakban Pabrik)
        $lakbanList = MstBarang::with('satuanDasar')
            ->where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%lakban%')
                  ->orWhere('barang_cd', 'ILIKE', '%lkb%')
                  ->orWhere('barang_nm', 'ILIKE', '%tape%')
                  ->orWhere('barang_nm', 'ILIKE', '%selotip%');
            })
            ->whereHas('jenisBarang', fn($q) => $q->whereNotIn('jenis_barang_cd', ['FG', 'WIP', 'BB', 'RAW']))
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        // 8. Plastik / Kertas Expired (Label Batch Kadaluarsa)
        $expiredList = MstBarang::with('satuanDasar')
            ->where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%exp%')
                  ->orWhere('barang_nm', 'ILIKE', '%kadaluarsa%')
                  ->orWhere('barang_cd', 'ILIKE', '%exp%');
            })
            ->where('barang_nm', 'NOT ILIKE', '%export%')
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        // 9. Kemasan Lainnya / Tambahan (Plastik Bal, Tali Rafia, Stiker, Sarung Tangan, dll)
        $kemasanLainList = MstBarang::with('satuanDasar')
            ->where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%bal%')
                  ->orWhere('barang_nm', 'ILIKE', '%rafia%')
                  ->orWhere('barang_nm', 'ILIKE', '%tali%')
                  ->orWhere('barang_nm', 'ILIKE', '%label%')
                  ->orWhere('barang_nm', 'ILIKE', '%stiker%')
                  ->orWhere('barang_nm', 'ILIKE', '%sarung tangan%')
                  ->orWhere('barang_nm', 'ILIKE', '%plastik%')
                  ->orWhere('barang_nm', 'ILIKE', '%klip%');
            })
            ->where('barang_nm', 'NOT ILIKE', '%bumbu%')
            ->where('barang_nm', 'NOT ILIKE', '%lakban%')
            ->where('barang_nm', 'NOT ILIKE', '%karton%')
            ->where('barang_nm', 'NOT ILIKE', '%keripik%')
            ->whereHas('jenisBarang', fn($q) => $q->whereNotIn('jenis_barang_cd', ['FG', 'WIP', 'BB', 'RAW']))
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        return view('produksi.packaging_operator.create', compact(
            'barangJadiList',
            'wipList',
            'produksiList',
            'karyawanList',
            'bumbuList',
            'plastikList',
            'kartonList',
            'lakbanList',
            'expiredList',
            'kemasanLainList'
        ));
    }

    /**
     * Simpan Laporan Pengemasan dari HP Operator
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tgl_kemas'                    => 'required|date',
            'shift'                        => 'required|string',
            'operator_nama'                => 'required|string|max:150',
            'output_id'                    => 'nullable|exists:dat_produksi_dtl,output_id',
            'wip_barang_id'                => 'nullable|exists:mst_barang,barang_id',
            'produksi_id'                  => 'nullable|exists:dat_produksi_hdr,produksi_id',
            'barang_jadi_id'               => 'required|exists:mst_barang,barang_id',
            'hasil_karton_qty'             => 'required|numeric|min:0.0001',
            'lakban_barang_id'             => 'nullable|exists:mst_barang,barang_id',
            'lakban_qty'                   => 'nullable|numeric|min:0',
            'expired_barang_id'            => 'nullable|exists:mst_barang,barang_id',
            'expired_qty'                  => 'nullable|numeric|min:0',
            'kemasan_lain_barang_id'       => 'nullable|exists:mst_barang,barang_id',
            'kemasan_lain_qty'             => 'nullable|numeric|min:0',
            'kemasan_tambahan'             => 'nullable|array',
            'kemasan_tambahan.*.barang_id' => 'nullable|exists:mst_barang,barang_id',
            'kemasan_tambahan.*.qty'       => 'nullable|numeric|min:0',
        ], [
            'tgl_kemas.required'        => 'Tanggal kemas wajib diisi.',
            'operator_nama.required'    => 'Nama operator/leader wajib diisi.',
            'barang_jadi_id.required'   => 'Silakan pilih produk barang jadi yang dikemas.',
            'hasil_karton_qty.required' => 'Jumlah karton/kemasan hasil jadi wajib diisi.',
            'hasil_karton_qty.min'      => 'Jumlah hasil kemas minimal > 0.',
        ]);

        try {
            $laporan = $this->operatorService->storeLaporan($request->all(), Auth::user());

            return redirect()->route('produksi.packaging.mobile.show', $laporan->laporan_id)
                ->with('success', "Laporan Pengemasan {$laporan->laporan_no} berhasil dikirim ke Admin Office!");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengirim laporan: ' . $e->getMessage());
        }
    }

    /**
     * Riwayat Laporan Pengemasan Lapangan di HP Operator
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'         => $request->input('search'),
            'tgl_kemas'      => $request->input('tgl_kemas', now()->toDateString()),
            'shift'          => $request->input('shift'),
            'status_cd'      => $request->input('status_cd'),
            'barang_jadi_id' => $request->input('barang_jadi_id'),
        ];

        $laporanList = $this->operatorService->getLaporanListPaginated($filters, 15);

        return view('produksi.packaging_operator.index', compact('laporanList', 'filters'));
    }

    /**
     * Detail Lembar Bukti Laporan Operator
     */
    public function show(int $id): View
    {
        $laporan = $this->operatorService->getById($id);

        return view('produksi.packaging_operator.show', compact('laporan'));
    }

    /**
     * API JSON: Daftar Laporan yang Belum Ditarik Office (untuk Picker di form HPP FG)
     */
    public function ajaxUnprocessed(): JsonResponse
    {
        $list = $this->operatorService->getUnprocessedLaporanList();

        return response()->json([
            'status' => 'success',
            'count'  => $list->count(),
            'data'   => $list,
        ]);
    }
}
