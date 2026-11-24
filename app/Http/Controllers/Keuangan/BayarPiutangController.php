<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\DatBayarPiutangHdr;
use App\Models\MasterData\MstCustomer;
use App\Models\Penjualan\DatFakturHdr;
use App\Services\Keuangan\BayarPiutangService;
use App\Services\Keuangan\KasBankService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BayarPiutangController extends Controller
{
    protected BayarPiutangService $bayarPiutangService;
    protected KasBankService $kasBankService;

    public function __construct(
        BayarPiutangService $bayarPiutangService,
        KasBankService $kasBankService
    ) {
        $this->bayarPiutangService = $bayarPiutangService;
        $this->kasBankService = $kasBankService;
    }

    /**
     * Riwayat Pelunasan Piutang Usaha (Bukti Kas Masuk - BKM AR)
     */
    public function index(Request $request): View
    {
        $filters = [
            'customer_id'       => $request->input('customer_id'),
            'kas_bank_id'       => $request->input('kas_bank_id'),
            'metode_bayar'      => $request->input('metode_bayar'),
            'status_cd'         => $request->input('status_cd'),
            'search'            => $request->input('search'),
            'tgl_mulai'         => $request->input('tgl_mulai'),
            'tgl_selesai'       => $request->input('tgl_selesai'),
        ];

        $bayarList = $this->bayarPiutangService->getBayarPiutangListPaginated($filters, 15);
        $kasBanks = $this->kasBankService->getAllKasBank();
        $customers = MstCustomer::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('customer_nm')
            ->get();

        $countBkm = DatBayarPiutangHdr::where('deleted_st', false)->count();
        $countVoid = DatBayarPiutangHdr::where('deleted_st', false)
            ->where('status_cd', 'VOID')
            ->count();
        $countSebagian = DatBayarPiutangHdr::where('deleted_st', false)
            ->where('status_cd', 'POSTED')
            ->whereHas('details', function ($q) {
                $q->where('sisa_piutang_sesudah', '>', 0);
            })
            ->count();
        $countLunas = DatBayarPiutangHdr::where('deleted_st', false)
            ->where('status_cd', 'POSTED')
            ->whereDoesntHave('details', function ($q) {
                $q->where('sisa_piutang_sesudah', '>', 0);
            })
            ->count();

        $countCustomerAktif = MstCustomer::where('deleted_st', false)
            ->where('active_st', true)
            ->count();

        $totalDiterima = (float) DatBayarPiutangHdr::where('deleted_st', false)
            ->where('status_cd', 'POSTED')
            ->sum('total_bayar');

        $totalSisaPiutang = (float) DatFakturHdr::where('deleted_st', false)
            ->where('active_st', true)
            ->where('status_pembayaran', '!=', 'LUNAS')
            ->sum('sisa_piutang');

        return view('keuangan.bayar_piutang.index', compact(
            'bayarList',
            'kasBanks',
            'customers',
            'filters',
            'countBkm',
            'countLunas',
            'countSebagian',
            'countVoid',
            'countCustomerAktif',
            'totalDiterima',
            'totalSisaPiutang'
        ));
    }

    /**
     * Form Pelunasan Piutang (BKM AR)
     */
    public function create(Request $request): View
    {
        $customerId = $request->input('customer_id');
        $fakturId = $request->input('faktur_id');

        $selectedFaktur = null;
        if ($fakturId) {
            $selectedFaktur = DatFakturHdr::with('customer')
                ->where('deleted_st', false)
                ->find($fakturId);

            if ($selectedFaktur && empty($customerId)) {
                $customerId = $selectedFaktur->customer_id;
            }
        }

        $customers = MstCustomer::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('customer_nm')
            ->get();

        $kasBanks = $this->kasBankService->getAllKasBank();

        // Rekap ringkas jumlah faktur dan total sisa piutang per customer
        $unpaidCounts = DatFakturHdr::where('deleted_st', false)
            ->where('active_st', true)
            ->where('status_pembayaran', '!=', 'LUNAS')
            ->where('sisa_piutang', '>', 0)
            ->selectRaw('customer_id, count(*) as count_faktur, sum(sisa_piutang) as total_sisa, min(tgl_jatuh_tempo) as min_tempo')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        $customerListData = $customers->map(function ($c) use ($unpaidCounts) {
            return [
                'customer_id'  => $c->customer_id,
                'customer_nm'  => $c->customer_nm,
                'customer_cd'  => $c->customer_cd ?? 'CUST',
                'count_faktur' => $unpaidCounts[$c->customer_id]->count_faktur ?? 0,
                'total_sisa'   => (float) ($unpaidCounts[$c->customer_id]->total_sisa ?? 0),
            ];
        })->values();

        return view('keuangan.bayar_piutang.create', compact(
            'customers',
            'kasBanks',
            'customerId',
            'selectedFaktur',
            'unpaidCounts',
            'customerListData'
        ));
    }

    /**
     * Endpoint API JSON untuk memuat faktur belum lunas milik customer terpilih
     */
    public function getFakturCustomer(int $customerId): JsonResponse
    {
        $fakturs = $this->bayarPiutangService->getUnpaidFakturCustomer($customerId);

        $payload = $fakturs->map(function ($f) {
            $isOverdue = false;
            $hariJatuhTempo = null;
            if ($f->tgl_jatuh_tempo) {
                $today = now()->startOfDay();
                $tempo = $f->tgl_jatuh_tempo->startOfDay();
                $isOverdue = $tempo->lt($today);
                $hariJatuhTempo = (int) $today->diffInDays($tempo, false);
            }

            return [
                'faktur_id'         => $f->faktur_id,
                'faktur_no'         => $f->faktur_no,
                'faktur_tgl'        => $f->faktur_tgl ? $f->faktur_tgl->format('d/m/Y') : '-',
                'faktur_tgl_raw'    => $f->faktur_tgl ? $f->faktur_tgl->toDateString() : '',
                'tgl_jatuh_tempo'   => $f->tgl_jatuh_tempo ? $f->tgl_jatuh_tempo->format('d/m/Y') : '-',
                'customer_po_no'    => $f->customer_po_no ?? '-',
                'top_txt'           => $f->top_txt ?? '-',
                'total_tagihan'     => (float) $f->total_tagihan,
                'total_dibayar'     => (float) $f->total_dibayar,
                'sisa_piutang'      => (float) $f->sisa_piutang,
                'status_pembayaran' => $f->status_pembayaran,
                'is_overdue'        => $isOverdue,
                'hari_jatuh_tempo'  => $hariJatuhTempo,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $payload,
        ]);
    }

    /**
     * Simpan Pelunasan Piutang
     */
    public function store(Request $request): RedirectResponse
    {
        $rawAllocations = $request->input('allocations', []);
        $filteredAllocations = collect($rawAllocations)
            ->filter(function ($item) {
                return isset($item['nominal_bayar']) && (float) $item['nominal_bayar'] > 0;
            })
            ->values()
            ->all();

        $request->merge(['allocations' => $filteredAllocations]);

        $request->validate([
            'customer_id'                 => 'required|exists:mst_customer,customer_id',
            'kas_bank_id'                 => 'required|exists:mst_kas_bank,kas_bank_id',
            'tgl_bayar'                   => 'required|date',
            'metode_bayar'                => 'required|string',
            'diterima_dari'               => 'required|string|max:150',
            'no_referensi_bank'           => 'nullable|string|max:100',
            'catatan'                     => 'nullable|string',
            'allocations'                 => 'required|array|min:1',
            'allocations.*.faktur_id'     => 'required|exists:dat_faktur_hdr,faktur_id',
            'allocations.*.nominal_bayar' => 'required|numeric|min:0.01',
        ], [
            'customer_id.required'                 => 'Silakan pilih pelanggan / customer terlebih dahulu.',
            'kas_bank_id.required'                 => 'Silakan pilih rekening atau kas penerima.',
            'tgl_bayar.required'                   => 'Tanggal penerimaan pembayaran wajib diisi.',
            'diterima_dari.required'               => 'Nama penyetor / diterima dari wajib diisi.',
            'allocations.required'                 => 'Pilih minimal satu faktur penjualan dengan mencentang atau mengisi nominal bayar.',
            'allocations.min'                      => 'Pilih minimal satu faktur penjualan dengan mencentang atau mengisi nominal bayar.',
            'allocations.*.nominal_bayar.min'      => 'Nominal alokasi pembayaran minimal Rp 1.',
        ]);

        try {
            $bkm = $this->bayarPiutangService->simpanPelunasanPiutang($request->all(), Auth::user());

            return redirect()->route('keuangan.bayar_piutang.show', $bkm->bayar_id)
                ->with('success', "Bukti Kas Masuk {$bkm->bkm_no} berhasil dicatat sebesar Rp " . number_format($bkm->total_bayar, 0, ',', '.') . ".");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses pelunasan piutang: ' . $e->getMessage());
        }
    }

    /**
     * Slip Bukti Kas Masuk (Kuitansi Penerimaan Kas Piutang)
     */
    public function show(int $id): View
    {
        $bkm = DatBayarPiutangHdr::with([
            'customer',
            'kasBank',
            'details.faktur',
            'jurnal.details.coa',
        ])->where('deleted_st', false)->findOrFail($id);

        return view('keuangan.bayar_piutang.show', compact('bkm'));
    }

    /**
     * Batalkan / Void Transaksi BKM
     */
    public function void(int $id, Request $request): RedirectResponse
    {
        $alasan = $request->input('alasan', '');

        try {
            $bkm = $this->bayarPiutangService->batalkanPelunasanPiutang($id, $alasan, Auth::user());

            return redirect()->route('keuangan.bayar_piutang.index')
                ->with('success', "Transaksi {$bkm->bkm_no} berhasil dibatalkan (VOID). Saldo kas dan sisa piutang faktur telah dikembalikan.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan pembayaran: ' . $e->getMessage());
        }
    }
}
