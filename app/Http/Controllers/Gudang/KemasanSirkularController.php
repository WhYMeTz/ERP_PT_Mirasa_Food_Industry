<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstGudang;
use App\Models\Penjualan\DatPengeluaranHdr;
use App\Services\Gudang\KemasanSirkularService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KemasanSirkularController extends Controller
{
    public function __construct(
        protected KemasanSirkularService $sirkularService
    ) {}

    /**
     * Menampilkan daftar penerimaan kemasan sirkular.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'      => $request->input('search'),
            'customer_id' => $request->input('customer_id'),
            'tgl_mulai'   => $request->input('tgl_mulai'),
            'tgl_selesai' => $request->input('tgl_selesai'),
        ];

        $list = $this->sirkularService->getAllPaginated($filters);
        $customerList = MstCustomer::where('deleted_st', false)->orderBy('customer_nm')->get();

        return view('gudang.kemasan_sirkular.index', compact('list', 'filters', 'customerList'));
    }

    /**
     * Menampilkan formulir penerimaan & sortir kemasan sirkular baru.
     */
    public function create(): View
    {
        $customers = MstCustomer::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('customer_nm')
            ->get();

        $gudangs = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        $pengeluaranList = $this->sirkularService->getAvailablePengeluaranList();

        // SKU Master Kemasan Sirkular
        $barangKartonBaru = MstBarang::where('barang_cd', 'KIF00G-BP1')->first();
        $barangKartonPuteran = MstBarang::where('barang_cd', 'KIF00G-PUT')->first();
        $barangKartonBmg = MstBarang::where('barang_cd', 'KIF00G-BMG')->first();
        $barangKartonReject = MstBarang::where('barang_cd', 'KMS-KTN-REJ')->first();

        $sirkularNo = $this->sirkularService->generateSirkularNo();

        return view('gudang.kemasan_sirkular.create', compact(
            'customers',
            'gudangs',
            'pengeluaranList',
            'barangKartonBaru',
            'barangKartonPuteran',
            'barangKartonBmg',
            'barangKartonReject',
            'sirkularNo'
        ));
    }

    /**
     * Menampilkan formulir penerimaan kemasan sirkular versi Mobile-First (seperti QC Mobile).
     */
    public function mobileCreate(): View
    {
        $customers = MstCustomer::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('customer_nm')
            ->get();

        $gudangs = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        $pengeluaranList = $this->sirkularService->getAvailablePengeluaranList();

        $barangKartonBaru = MstBarang::where('barang_cd', 'KIF00G-BP1')->first();
        $barangKartonPuteran = MstBarang::where('barang_cd', 'KIF00G-PUT')->first();
        $barangKartonBmg = MstBarang::where('barang_cd', 'KIF00G-BMG')->first();
        $barangKartonReject = MstBarang::where('barang_cd', 'KMS-KTN-REJ')->first();

        $sirkularNo = $this->sirkularService->generateSirkularNo();

        return view('gudang.kemasan_sirkular.mobile.create', compact(
            'customers',
            'gudangs',
            'pengeluaranList',
            'barangKartonBaru',
            'barangKartonPuteran',
            'barangKartonBmg',
            'barangKartonReject',
            'sirkularNo'
        ));
    }

    /**
     * Menyimpan data penerimaan & sortir kemasan sirkular baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sirkular_no'             => 'nullable|string|max:50',
            'sirkular_tgl'            => 'required|date',
            'gudang_id'               => 'required|integer|exists:mst_gudang,gudang_id',
            'customer_id'             => 'nullable|integer|exists:mst_customer,customer_id',
            'pengeluaran_id'          => 'nullable|integer|exists:dat_pengeluaran_hdr,pengeluaran_id',
            'surat_jalan_customer_no' => 'nullable|string|max:100',
            'supir_nm'                => 'nullable|string|max:100',
            'nopol_kendaraan'         => 'nullable|string|max:20',
            'ekspedisi_nm'            => 'nullable|string|max:100',
            'catatan_txt'             => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.barang_asal_id'  => 'required|integer|exists:mst_barang,barang_id',
            'items.*.barang_mulus_id' => 'nullable|integer|exists:mst_barang,barang_id',
            'items.*.barang_bmg_id'   => 'nullable|integer|exists:mst_barang,barang_id',
            'items.*.barang_rusak_id' => 'nullable|integer|exists:mst_barang,barang_id',
            'items.*.kirim_qty'       => 'nullable|numeric|min:0',
            'items.*.kembali_qty'     => 'required|numeric|min:0',
            'items.*.mulus_qty'       => 'required|numeric|min:0',
            'items.*.bmg_qty'         => 'nullable|numeric|min:0',
            'items.*.rusak_qty'       => 'required|numeric|min:0',
            'items.*.catatan_txt'     => 'nullable|string|max:255',
            'view'                    => 'nullable|string',
        ]);

        try {
            $header = $this->sirkularService->store($validated);

            if ($request->input('view') === 'mobile') {
                return redirect()
                    ->route('gudang.kemasan-sirkular.mobile')
                    ->with('success', "✅ Penerimaan Kemasan Sirkular [{$header->sirkular_no}] berhasil dicatat dan stok gudang telah masuk.");
            }

            return redirect()
                ->route('gudang.kemasan-sirkular.show', $header->kemasan_sirkular_id)
                ->with('success', "Penerimaan Kemasan Sirkular [{$header->sirkular_no}] berhasil dicatat dan stok gudang telah diperbarui.");
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan penerimaan kemasan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan rincian dokumen penerimaan kemasan sirkular.
     */
    public function show(int $id): View
    {
        $kemasan = $this->sirkularService->getById($id);
        return view('gudang.kemasan_sirkular.show', compact('kemasan'));
    }

    /**
     * Membatalkan (Void) dokumen penerimaan kemasan sirkular.
     */
    public function void(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:255',
        ]);

        try {
            $this->sirkularService->void($id, $request->input('reason'));

            return redirect()
                ->route('gudang.kemasan-sirkular.index')
                ->with('success', 'Dokumen penerimaan kemasan sirkular berhasil dibatalkan (VOID) dan mutasi stok telah di-rollback.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal membatalkan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint: Mengambil detail pengeluaran untuk auto-fill form.
     */
    public function ajaxPengeluaranDetail(int $id): JsonResponse
    {
        try {
            $pengeluaran = DatPengeluaranHdr::with(['customer', 'details.barang'])->findOrFail($id);

            // Cari estimasi boks terkirim (dari total quantity pengiriman)
            $totalBoxKirim = (float) $pengeluaran->total_qty;

            return response()->json([
                'success' => true,
                'data'    => [
                    'pengeluaran_id'  => $pengeluaran->pengeluaran_id,
                    'pengeluaran_no'  => $pengeluaran->pengeluaran_no,
                    'customer_id'     => $pengeluaran->customer_id,
                    'customer_nm'     => $pengeluaran->customer?->customer_nm,
                    'supir_nm'        => $pengeluaran->supir_nm,
                    'nopol_kendaraan' => $pengeluaran->nopol_kendaraan,
                    'ekspedisi_nm'    => $pengeluaran->ekspedisi_nm,
                    'total_box_kirim' => $totalBoxKirim,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}

