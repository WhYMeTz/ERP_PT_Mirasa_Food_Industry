<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstGudang;
use App\Models\Penjualan\DatSoHdr;
use App\Models\Penjualan\DatSoReturDtl;
use App\Models\Penjualan\DatSoReturHdr;
use App\Services\Penjualan\SoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SoReturController extends Controller
{
    public function __construct(
        protected SoService $soService
    ) {}

    /**
     * Halaman indeks daftar seluruh dokumen Retur Penjualan (Customer Returns).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $filters = [
            'customer_id' => $request->query('customer_id'),
            'gudang_id'   => $request->query('gudang_id'),
            'tindakan_cd' => $request->query('tindakan_cd'),
            'status_cd'   => $request->query('status_cd'),
            'date_from'   => $request->query('date_from'),
            'date_to'     => $request->query('date_to'),
        ];

        $returns = $this->soService->getReturnsPaginated(15, $search, $filters);
        $kpi = $this->soService->getReturnKpi();

        $customers = MstCustomer::active()->orderBy('customer_nm')->get(['customer_id', 'customer_cd', 'customer_nm']);
        $gudangs = MstGudang::active()->orderBy('gudang_nm')->get(['gudang_id', 'gudang_cd', 'gudang_nm']);

        // Daftar pesanan (SO) yang telah terkirim (siap dicatat retur)
        $eligibleSos = DatSoHdr::with('customer')
            ->whereIn('status_cd', ['COMPLETED', 'PARTIAL'])
            ->where('deleted_st', false)
            ->orderByDesc('so_tgl')
            ->get(['so_id', 'so_no', 'so_tgl', 'customer_id', 'status_cd', 'total_tagihan']);

        return view('penjualan.retur.index', compact('returns', 'kpi', 'customers', 'gudangs', 'search', 'filters', 'eligibleSos'));
    }

    /**
     * Halaman form pencatatan retur penjualan lengkap (Dedicated Page, Non-Popup).
     */
    public function create(Request $request): View
    {
        $soId = $request->query('so_id');
        $selectedSo = null;

        if ($soId) {
            $selectedSo = DatSoHdr::with([
                'customer',
                'details.barang.satuanDasar',
                'details.barang.jenisBarang',
                'pengeluaran.gudang',
                'faktur'
            ])
            ->where('deleted_st', false)
            ->whereIn('status_cd', ['COMPLETED', 'PARTIAL'])
            ->find($soId);
        }

        // Daftar pesanan (SO) yang telah terkirim (siap dicatat retur)
        $eligibleSos = DatSoHdr::with('customer')
            ->whereIn('status_cd', ['COMPLETED', 'PARTIAL'])
            ->where('deleted_st', false)
            ->orderByDesc('so_tgl')
            ->get(['so_id', 'so_no', 'so_tgl', 'customer_id', 'status_cd', 'total_tagihan', 'faktur_no', 'surat_jalan_no']);

        $gudangs = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_id')
            ->get();

        $defaultGudangId = $gudangs->firstWhere('gudang_cd', 'GDG-FG')?->gudang_id ?? ($gudangs->firstWhere('gudang_cd', 'MFI')?->gudang_id ?? 6);

        // Hitung kuantitas maksimal retur per detail jika ada SO terpilih
        $itemsData = [];
        if ($selectedSo) {
            $itemsData = $selectedSo->details->map(function ($dtl) {
                $alreadyRetur = (float) DatSoReturDtl::where('sodtl_id', $dtl->sodtl_id)
                    ->whereHas('header', fn($q) => $q->where('status_cd', 'CONFIRMED')->where('deleted_st', false))
                    ->sum('qty_retur');

                $maxReturnable = max(0, (float) $dtl->kirim_qty - $alreadyRetur);
                $effectivePrice = (float) $dtl->pesan_qty > 0 
                    ? ((float) $dtl->subtotal_tagihan / (float) $dtl->pesan_qty) 
                    : (float) $dtl->harga_satuan;

                return [
                    'sodtl_id'          => $dtl->sodtl_id,
                    'barang_id'         => $dtl->barang_id,
                    'barang_cd'         => $dtl->barang?->barang_cd ?? '-',
                    'barang_nm'         => $dtl->barang?->barang_nm ?? '-',
                    'satuan_nm'         => $dtl->satuan ?: ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton'),
                    'kirim_qty'         => (float) $dtl->kirim_qty,
                    'already_retur_qty' => $alreadyRetur,
                    'max_returnable'    => $maxReturnable,
                    'effective_price'   => $effectivePrice,
                    'formatted_price'   => 'Rp ' . number_format($effectivePrice, 0, ',', '.'),
                ];
            });
        }

        return view('penjualan.retur.create', compact('selectedSo', 'eligibleSos', 'gudangs', 'defaultGudangId', 'itemsData'));
    }

    /**
     * Menyimpan formulir retur penjualan.
     */
    public function store(Request $request): RedirectResponse
    {
        $soId = (int) $request->input('so_id');
        $validated = $request->validate([
            'so_id'                        => 'required|exists:dat_so_hdr,so_id',
            'retur_tgl'                    => 'required|date',
            'gudang_id'                    => 'required|exists:mst_gudang,gudang_id',
            'tindakan_cd'                  => 'required|in:POTONG_TAGIHAN,TUKAR_GULING',
            'alasan_txt'                   => 'nullable|string|max:500',
            'items'                        => 'required|array|min:1',
            'items.*.sodtl_id'             => 'required|integer',
            'items.*.qty_retur'            => 'required|numeric|min:0',
            'items.*.perlakuan_cd'         => 'required|string|in:MASUK_PERSEDIAAN,DIMUSNAHKAN',
            'items.*.kondisi_cd'           => 'nullable|string|max:50',
            'items.*.catatan_txt'          => 'nullable|string|max:255',
        ], [
            'so_id.required'       => 'Pilih dokumen pesanan (SO) yang akan diretur.',
            'retur_tgl.required'   => 'Tanggal retur wajib diisi.',
            'gudang_id.required'   => 'Pilih gudang tujuan barang masuk.',
            'tindakan_cd.required' => 'Pilih tindakan komersial tagihan.',
            'items.required'       => 'Minimal harus ada 1 item barang yang diretur.',
        ]);

        try {
            $retur = $this->soService->recordSalesReturn($soId, $validated, auth()->user());

            $actionMsg = $retur->tindakan_cd === 'POTONG_TAGIHAN'
                ? "Sisa tagihan SO otomatis dikurangi Rp " . number_format((float) $retur->total_nilai, 0, ',', '.')
                : "Barang dicatat untuk ditukar guling secara fisik";

            // Jika ada barang yang dimusnahkan, arahkan langsung ke halaman Berita Acara Pemusnahan!
            if ($retur->total_qty_dimusnahkan > 0) {
                return redirect()
                    ->route('penjualan.retur.berita-acara-pemusnahan', $retur->retur_id)
                    ->with('toast_success', "Retur Penjualan {$retur->retur_no} berhasil dicatat! {$actionMsg}. Terdapat {$retur->total_qty_dimusnahkan} unit barang dimusnahkan — silakan cetak Berita Acara Pemusnahan berikut.");
            }

            return redirect()
                ->route('penjualan.so.show', $soId)
                ->with('toast_success', "Retur Penjualan {$retur->retur_no} ({$retur->total_qty} unit) berhasil dicatat dan masuk kembali ke persediaan gudang! {$actionMsg}.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses retur penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan Berita Acara Pemusnahan Produk Jadi (Scrap Report) berbasis Web & Siap Cetak (A4).
     */
    public function beritaAcaraPemusnahanWeb(int $id): View
    {
        $retur = DatSoReturHdr::with(['order.customer', 'customer', 'gudang', 'details.barang.satuanDasar'])->findOrFail($id);

        return view('penjualan.retur.berita_acara_pemusnahan', compact('retur'));
    }

    /**
     * Mengambil data rincian barang SO terkirim untuk formulir modal retur (AJAX).
     */
    public function getSoData(int $id): JsonResponse
    {
        $so = DatSoHdr::with(['customer', 'details.barang.satuanDasar'])
            ->where('deleted_st', false)
            ->findOrFail($id);

        if (!in_array($so->status_cd, ['COMPLETED', 'PARTIAL'])) {
            return response()->json([
                'status'  => 'error',
                'message' => "Dokumen SO {$so->so_no} berstatus {$so->status_cd}. Hanya pesanan yang sudah terkirim (COMPLETED / PARTIAL) yang dapat diretur.",
            ], 422);
        }

        $items = $so->details->map(function ($dtl) {
            $alreadyRetur = (float) DatSoReturDtl::where('sodtl_id', $dtl->sodtl_id)
                ->whereHas('header', fn($q) => $q->where('status_cd', 'CONFIRMED')->where('deleted_st', false))
                ->sum('qty_retur');

            $maxReturnable = max(0, (float) $dtl->kirim_qty - $alreadyRetur);
            $effectivePrice = (float) $dtl->pesan_qty > 0 
                ? ((float) $dtl->subtotal_tagihan / (float) $dtl->pesan_qty) 
                : (float) $dtl->harga_satuan;

            return [
                'sodtl_id'          => $dtl->sodtl_id,
                'barang_id'         => $dtl->barang_id,
                'barang_cd'         => $dtl->barang?->barang_cd ?? '-',
                'barang_nm'         => $dtl->barang?->barang_nm ?? '-',
                'satuan_nm'         => $dtl->barang?->satuanDasar?->satuan_nm ?? 'Unit',
                'kirim_qty'         => (float) $dtl->kirim_qty,
                'already_retur_qty' => $alreadyRetur,
                'max_returnable'    => $maxReturnable,
                'effective_price'   => $effectivePrice,
                'formatted_price'   => 'Rp ' . number_format($effectivePrice, 0, ',', '.'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'so_id'            => $so->so_id,
                'so_no'            => $so->so_no,
                'customer_nm'      => $so->customer?->customer_nm ?? '-',
                'so_tgl_formatted' => $so->so_tgl ? $so->so_tgl->format('d/m/Y') : '-',
                'items'            => $items,
            ],
        ]);
    }

    /**
     * Tampilan detail dokumen retur penjualan (diarahkan ke detail SO bersangkutan).
     */
    public function show(int $id): RedirectResponse
    {
        $retur = DatSoReturHdr::findOrFail($id);
        return redirect()->to(route('penjualan.so.show', $retur->so_id) . '#retur-penjualan');
    }

    /**
     * Membatalkan dokumen retur penjualan (Void) dan menyesuaikan stok kembali.
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Alasan pembatalan retur wajib diisi.',
        ]);

        try {
            $retur = $this->soService->voidSalesReturn($id, $request->input('reason'), auth()->user()?->nama_lengkap);

            return back()->with('toast_success', "Dokumen Retur {$retur->retur_no} berhasil dibatalkan (VOID) dan stok fisik telah disesuaikan kembali.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
        }
    }

    /**
     * Export Berita Acara Retur Penjualan resmi ke format PDF.
     */
    public function exportPdf(int $id): Response
    {
         $retur = DatSoReturHdr::with(['order', 'customer', 'gudang', 'details.barang'])->findOrFail($id);

         $logoPath = public_path('images/logo.png');
         $logoBase64 = null;
         if (file_exists($logoPath)) {
             $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
         }

         $pdf = Pdf::loadView('penjualan.retur.pdf_berita_acara', [
             'retur'      => $retur,
             'logoBase64' => $logoBase64,
             'printedAt'  => now()->translatedFormat('d F Y H:i'),
             'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
         ]);

         $pdf->setPaper('a4', 'portrait');

         $fileName = 'BERITA_ACARA_RETUR_' . preg_replace('/[^A-Za-z0-9\-]/', '', $retur->retur_no) . '.pdf';
         return $pdf->stream($fileName);
     }

    /**
     * Export Berita Acara Pemusnahan Produk Jadi (Scrap Report) ke format PDF.
     */
    public function exportPemusnahanPdf(int $id): Response
    {
        $retur = DatSoReturHdr::with(['order', 'customer', 'gudang', 'details.barang'])->findOrFail($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('penjualan.retur.pdf_pemusnahan', [
            'retur'      => $retur,
            'logoBase64' => $logoBase64,
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
            'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $fileName = 'BAP_PEMUSNAHAN_' . preg_replace('/[^A-Za-z0-9\-]/', '', $retur->retur_no) . '.pdf';
        return $pdf->stream($fileName);
    }
}
