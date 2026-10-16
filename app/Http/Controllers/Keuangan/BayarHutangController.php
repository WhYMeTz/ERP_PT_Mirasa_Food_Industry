<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\DatBayarHutangHdr;
use App\Models\Keuangan\DatHutangHdr;
use App\Models\MasterData\MstSupplier;
use App\Services\Keuangan\KasBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Exception;

class BayarHutangController extends Controller
{
    protected KasBankService $kasBankService;

    public function __construct(KasBankService $kasBankService)
    {
        $this->kasBankService = $kasBankService;
    }

    /**
     * Riwayat Pembayaran Hutang / Bukti Kas Keluar (BKK)
     */
    public function index(Request $request): View
    {
        $filters = [
            'kas_bank_id'  => $request->input('kas_bank_id'),
            'metode_bayar' => $request->input('metode_bayar'),
            'search'       => $request->input('search'),
            'tgl_mulai'    => $request->input('tgl_mulai'),
            'tgl_selesai'  => $request->input('tgl_selesai'),
        ];

        $bayarList = $this->kasBankService->getBayarListPaginated($filters, 15);
        $kasBanks = $this->kasBankService->getAllKasBank();

        return view('keuangan.bayar.index', compact('bayarList', 'kasBanks', 'filters'));
    }

    /**
     * Form Pembayaran Tagihan (Bisa 1 Invoice atau Banyak Invoice dari Supplier yang sama)
     */
    public function create(Request $request): View
    {
        $hutangId = $request->input('hutang_id');
        $supplierId = $request->input('supplier_id');

        $selectedHutang = null;
        if ($hutangId) {
            $selectedHutang = DatHutangHdr::with('supplier')->where('deleted_st', false)->find($hutangId);
            if ($selectedHutang) {
                $supplierId = $selectedHutang->supplier_id;
            }
        }

        // Ambil semua tagihan yang belum lunas (agar chip filter supplier instan di frontend)
        $unpaidHutang = DatHutangHdr::with('supplier')
            ->where('deleted_st', false)
            ->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
            ->orderBy('tgl_jatuh_tempo', 'asc')
            ->get();

        $kasBanks = $this->kasBankService->getAllKasBank();
        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();

        $unpaidCounts = DatHutangHdr::where('deleted_st', false)
            ->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
            ->selectRaw('supplier_id, count(*) as count_tagihan, sum(sisa_hutang) as total_sisa, min(tgl_jatuh_tempo) as min_tempo')
            ->groupBy('supplier_id')
            ->get()
            ->keyBy('supplier_id');

        return view('keuangan.bayar.create', compact('selectedHutang', 'unpaidHutang', 'kasBanks', 'suppliers', 'supplierId', 'unpaidCounts'));
    }

    /**
     * Proses Eksekusi Pembayaran Tagihan
     */
    public function store(Request $request): RedirectResponse
    {
        // Filter hanya baris alokasi tagihan yang dipilih dan memiliki nominal > 0
        $rawAllocations = $request->input('allocations', []);
        $filteredAllocations = collect($rawAllocations)
            ->filter(function ($item) {
                return isset($item['nominal_bayar']) && (float)$item['nominal_bayar'] > 0;
            })
            ->values()
            ->all();

        $request->merge(['allocations' => $filteredAllocations]);

        $request->validate([
            'kas_bank_id'                 => 'required|exists:mst_kas_bank,kas_bank_id',
            'tgl_bayar'                   => 'required|date',
            'metode_bayar'                => 'required|string',
            'allocations'                 => 'required|array|min:1',
            'allocations.*.hutang_id'     => 'required|exists:dat_hutang_hdr,hutang_id',
            'allocations.*.nominal_bayar' => 'required|numeric|min:0.01',
        ], [
            'kas_bank_id.required'                 => 'Silakan pilih rekening/kas sumber pembayaran.',
            'allocations.required'                 => 'Pilih minimal 1 tagihan dengan mencentang atau mengisi nominal bayar.',
            'allocations.min'                      => 'Pilih minimal 1 tagihan dengan mencentang atau mengisi nominal bayar.',
            'allocations.*.nominal_bayar.min'      => 'Nominal pembayaran tagihan minimal Rp 1.',
        ]);

        try {
            $bkk = $this->kasBankService->bayarHutang($request->all(), Auth::user());

            return redirect()->route('keuangan.bayar.show', $bkk->bayar_id)
                ->with('success', "Pembayaran {$bkk->bayar_no} berhasil dicatat sebesar Rp " . number_format($bkk->total_bayar, 0, ',', '.') . ".");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Lembar Bukti Kas/Bank Keluar (BKK) Siap Cetak A4 / Thermal
     */
    public function show(int $id): View
    {
        $bkk = DatBayarHutangHdr::with([
            'supplier',
            'kasBank',
            'details.hutang.supplier',
        ])->where('deleted_st', false)->findOrFail($id);

        return view('keuangan.bayar.show', compact('bkk'));
    }

    /**
     * Batalkan / Void Transaksi Pembayaran BKK
     */
    public function void(int $id): RedirectResponse
    {
        try {
            $bkk = DatBayarHutangHdr::where('deleted_st', false)->findOrFail($id);
            $this->kasBankService->voidPembayaran($bkk, Auth::user());

            return redirect()->route('keuangan.bayar.index')
                ->with('success', "Pembayaran {$bkk->bayar_no} berhasil dibatalkan (VOID) dan saldo telah dikembalikan.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan pembayaran: ' . $e->getMessage());
        }
    }
}
