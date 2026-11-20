<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstKaryawan;
use App\Services\Common\CodeGeneratorService;
use App\Services\Penjualan\DirectSalesService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DirectSalesController extends Controller
{
    public function __construct(
        protected DirectSalesService $directSalesService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Halaman Indeks Rekap Penjualan Langsung / Tunai (Kasir Toko Pabrik)
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $sales = $this->directSalesService->getAllPaginated(
            perPage: 15,
            search: $search,
            startDate: $startDate,
            endDate: $endDate
        );

        $metrics = $this->directSalesService->getMetrics();

        return view('penjualan.langsung.index', compact('sales', 'metrics', 'search', 'startDate', 'endDate'));
    }

    /**
     * Form Layar Checkout Penjualan Langsung Pabrik (Umum & Sales Keliling)
     */
    public function create(): View
    {
        $directSaleNo = $this->codeGenerator->generateDirectSaleNo();
        $defaultCashCustomer = $this->directSalesService->getOrCreateCashCustomer();
        $customers = MstCustomer::active()->orderBy('customer_nm')->get();
        $products = $this->directSalesService->getReadyToSellProducts();
        $karyawans = MstKaryawan::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('karyawan_nm')
            ->get(['karyawan_id', 'karyawan_nm', 'nik', 'jabatan_nm']);

        return view('penjualan.langsung.create', compact(
            'directSaleNo',
            'defaultCashCustomer',
            'customers',
            'products',
            'karyawans'
        ));
    }

    /**
     * Simpan & Selesaikan Transaksi Penjualan Langsung (1-Klik Selesai)
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'kategori_penjualan' => 'required|string|in:UMUM,SALES',
            'tgl_transaksi'      => 'required|date',
            'customer_id'        => 'nullable|integer|exists:mst_customer,customer_id',
            'customer_id_sales'  => 'nullable|integer|exists:mst_customer,customer_id',
            'nama_pembeli'       => 'nullable|string|max:100',
            'kontak_pembeli'     => 'nullable|string|max:50',
            'sales_nm'           => 'nullable|string|max:100',
            'rute_wilayah'       => 'nullable|string|max:150',
            'armada_sales'       => 'nullable|string|max:50',
            'toko_kanvas_nm'     => 'nullable|string|max:150',
            'nota_manual_no'     => 'nullable|string|max:100',
            'nopol_kendaraan'    => 'nullable|string|max:50',
            'metode_bayar'       => 'required|string|in:TUNAI,TRANSFER',
            'uang_diterima'      => 'nullable|numeric|min:0',
            'potongan_nominal'   => 'nullable|numeric|min:0',
            'is_ppn'             => 'nullable',
            'catatan_txt'        => 'nullable|string|max:500',

            'items'                    => 'required|array|min:1',
            'items.*.barang_id'        => 'required|integer|exists:mst_barang,barang_id',
            'items.*.qty'              => 'required|numeric|min:0.0001',
            'items.*.harga_satuan'     => 'required|numeric|min:0',
            'items.*.diskon_persen'    => 'nullable|numeric|min:0|max:100',
            'items.*.potongan_nominal' => 'nullable|numeric|min:0',
            'items.*.catatan_txt'      => 'nullable|string|max:255',
        ]);

        try {
            $userName = Auth::user()?->nama_lengkap ?? (Auth::user()?->name ?? 'Admin Penjualan');
            $order = $this->directSalesService->createDirectSale($request->all(), $userName);

            return redirect()
                ->route('penjualan.langsung.show', $order->so_id)
                ->with('success', "Transaksi Penjualan Langsung {$order->so_no} berhasil disimpan & diselesaikan!");
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses penjualan langsung: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Detail Rincian Dokumen Penjualan Langsung
     */
    public function show(int $id): View
    {
        $order = $this->directSalesService->getById($id);
        return view('penjualan.langsung.show', compact('order'));
    }

    /**
     * Format Cetak Struk / Nota Penjualan Kasir (Ukuran Nota Kasir / Struk Penjualan)
     */
    public function cetakNota(int $id): View
    {
        $order = $this->directSalesService->getById($id);
        return view('penjualan.langsung.nota', compact('order'));
    }
}
