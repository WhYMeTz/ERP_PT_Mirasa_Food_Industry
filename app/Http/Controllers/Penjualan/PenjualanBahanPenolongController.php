<?php

namespace App\Http\Controllers\Penjualan;

use App\Exports\Penjualan\PenjualanBahanPenolongExport;
use App\Http\Controllers\Controller;
use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstGudang;
use App\Models\Penjualan\DatSoHdr;
use App\Services\Common\CodeGeneratorService;
use App\Services\Penjualan\PenjualanBahanPenolongService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjualanBahanPenolongController extends Controller
{
    public function __construct(
        protected PenjualanBahanPenolongService $service,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Tampilan Indeks Daftar Penjualan Bahan Penolong & Bumbu
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $sales = $this->service->getAllPaginated(
            perPage: 15,
            search: $search,
            startDate: $startDate,
            endDate: $endDate
        );

        $metrics = $this->service->getMetrics();

        return view('penjualan.bahan_penolong.index', compact('sales', 'metrics', 'search', 'startDate', 'endDate'));
    }

    /**
     * Export data Penjualan Bahan Penolong & Bumbu ke format Excel (.xlsx)
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $orders = $this->service->getAllForExport(
            search: $search,
            startDate: $startDate,
            endDate: $endDate
        );

        $printedBy = auth()->user()?->nama_lengkap ?? 'Administrator ERP';
        $printedAt = now()->translatedFormat('d F Y H:i');
        $filename = 'Rekap_Penjualan_Bahan_Penolong_' . date('Ymd_His') . '.xlsx';

        $export = new PenjualanBahanPenolongExport(
            orders: $orders,
            search: $search,
            startDate: $startDate,
            endDate: $endDate,
            printedBy: $printedBy,
            printedAt: $printedAt
        );

        return $export->download($filename);
    }

    /**
     * Form Transaksi Baru Penjualan Bahan Penolong, Kemasan & Bumbu
     */
    public function create(): View
    {
        $transaksiNo = $this->codeGenerator->generatePenjualanBahanPenolongNo();
        $defaultCashCustomer = $this->service->getOrCreateCashCustomer();
        $customers = MstCustomer::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('customer_nm')
            ->get();

        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_id')
            ->get();

        // Default ke Gudang MFI-CBG (3) atau MFI (1)
        $defaultGudangId = $gudangList->firstWhere('gudang_cd', 'MFI-CBG')?->gudang_id ?? ($gudangList->firstWhere('gudang_cd', 'MFI')?->gudang_id ?? 3);
        $items = $this->service->getReadyToSellItems($defaultGudangId);

        return view('penjualan.bahan_penolong.create', compact(
            'transaksiNo',
            'defaultCashCustomer',
            'customers',
            'items',
            'gudangList',
            'defaultGudangId'
        ));
    }

    /**
     * Simpan Transaksi Penjualan Bahan Penolong & Bumbu
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tgl_transaksi'    => 'required|date',
            'gudang_id'        => 'required|exists:mst_gudang,gudang_id',
            'customer_id'      => 'nullable|exists:mst_customer,customer_id',
            'nama_pembeli'     => 'nullable|string|max:150',
            'nopol_kendaraan'  => 'nullable|string|max:50',
            'metode_bayar'     => 'required|string|in:TUNAI,TRANSFER,TEMPO',
            'items'            => 'required|array|min:1',
            'items.*.barang_id'=> 'required|exists:mst_barang,barang_id',
            'items.*.qty'      => 'required|numeric|min:0.01',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.diskon_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.potongan_nominal' => 'nullable|numeric|min:0',
            'potongan_nominal' => 'nullable|numeric|min:0',
            'is_ppn'           => 'nullable|boolean',
            'catatan_txt'      => 'nullable|string|max:500',
        ], [
            'items.required'   => 'Minimal harus menambahkan 1 item barang yang dijual.',
            'items.min'        => 'Minimal harus menambahkan 1 item barang yang dijual.',
            'items.*.qty.min'  => 'Kuantitas barang minimal 0.01.',
        ]);

        try {
            $order = $this->service->createPenjualanBahanPenolong($validated, auth()->user()?->nama_lengkap ?? 'Kasir');

            return redirect()
                ->route('penjualan.bahan-penolong.show', $order->so_id)
                ->with('success', "Penjualan Bahan Penolong {$order->so_no} berhasil dicatat dan stok berhasil dipotong.");
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Detail Penjualan Bahan Penolong & Bumbu
     */
    public function show(int $id): View
    {
        $order = DatSoHdr::with([
            'customer',
            'details.barang.satuanDasar',
            'details.barang.jenisBarang',
            'pengeluaran.faktur',
            'pengeluaran.gudang',
            'faktur.details'
        ])->findOrFail($id);

        return view('penjualan.bahan_penolong.show', compact('order'));
    }

    /**
     * Cetak Nota Penjualan Resmi / Bukti Serah Terima
     */
    public function cetakNota(int $id): View
    {
        $order = DatSoHdr::with([
            'customer',
            'details.barang.satuanDasar',
            'details.barang.jenisBarang',
            'pengeluaran.gudang',
            'faktur'
        ])->findOrFail($id);

        return view('penjualan.bahan_penolong.nota', compact('order'));
    }
}
