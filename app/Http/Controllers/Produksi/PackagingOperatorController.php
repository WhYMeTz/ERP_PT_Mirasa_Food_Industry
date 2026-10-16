<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstKaryawan;
use App\Models\Produksi\DatProduksiHdr;
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
        // 1. Barang Jadi (FG)
        $barangJadiList = MstBarang::with('satuanDasar')
            ->whereHas('jenisBarang', function ($q) {
                $q->where('jenis_barang_cd', 'FG')
                  ->orWhere('jenis_barang_nm', 'ILIKE', '%jadi%');
            })
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        // 2. Lembar Masak WIP Curah (14 hari terakhir)
        $produksiList = DatProduksiHdr::where('deleted_st', false)
            ->where('produksi_tgl', '>=', now()->subDays(14)->toDateString())
            ->orderByDesc('produksi_tgl')
            ->orderByDesc('produksi_id')
            ->get();

        // 3. Karyawan (Operator)
        $karyawanList = MstKaryawan::where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('karyawan_nm')
            ->get();

        // 4. Bumbu Tabur (Seasoning)
        $bumbuList = MstBarang::where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%bumbu%')
                  ->orWhere('barang_nm', 'ILIKE', '%balado%')
                  ->orWhere('barang_nm', 'ILIKE', '%seasoning%')
                  ->orWhere('barang_nm', 'ILIKE', '%perasa%');
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
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        // 7. Kemasan Lainnya (Lakban, Bal, Label)
        $kemasanLainList = MstBarang::where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%lakban%')
                  ->orWhere('barang_nm', 'ILIKE', '%bal%')
                  ->orWhere('barang_nm', 'ILIKE', '%expired%')
                  ->orWhere('barang_nm', 'ILIKE', '%label%');
            })
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

        return view('produksi.packaging_operator.create', compact(
            'barangJadiList',
            'produksiList',
            'karyawanList',
            'bumbuList',
            'plastikList',
            'kartonList',
            'kemasanLainList'
        ));
    }

    /**
     * Simpan Laporan Pengemasan dari HP Operator
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tgl_kemas'        => 'required|date',
            'shift'            => 'required|string',
            'operator_nama'    => 'required|string|max:150',
            'barang_jadi_id'   => 'required|exists:mst_barang,barang_id',
            'hasil_karton_qty' => 'required|numeric|min:0.0001',
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
