<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\DatHutangHdr;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Services\Keuangan\HutangService;
use App\Services\Keuangan\KasBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class HutangController extends Controller
{
    protected HutangService $hutangService;
    protected KasBankService $kasBankService;

    public function __construct(HutangService $hutangService, KasBankService $kasBankService)
    {
        $this->hutangService = $hutangService;
        $this->kasBankService = $kasBankService;
    }

    /**
     * Halaman Indeks Daftar Hutang Usaha & Tagihan Pembelian (AP)
     */
    public function index(Request $request): View
    {
        $filters = [
            'status_pembayaran' => $request->input('status_pembayaran'),
            'supplier_id'       => $request->input('supplier_id'),
            'tipe_hutang'       => $request->input('tipe_hutang'),
            'search'            => $request->input('search'),
            'tgl_mulai'         => $request->input('tgl_mulai'),
            'tgl_selesai'       => $request->input('tgl_selesai'),
        ];

        $hutangList = $this->hutangService->getAllPaginated($filters, 15);
        $metrics = $this->hutangService->getSummaryMetrics();
        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();
        $kasBanks = $this->kasBankService->getAllKasBank();

        return view('keuangan.hutang.index', compact('hutangList', 'metrics', 'suppliers', 'kasBanks', 'filters'));
    }

    /**
     * Form Catat Tagihan Pembelian Baru (Tarik dari GRN)
     */
    public function create(Request $request): View
    {
        $unbilledSupplierSummary = $this->hutangService->getUnbilledSupplierSummary();
        $suppliers = MstSupplier::where('deleted_st', false)
            ->where('active_st', true)
            ->get()
            ->sortByDesc(function ($s) use ($unbilledSupplierSummary) {
                return $unbilledSupplierSummary[$s->supplier_id] ?? 0;
            })
            ->values();

        $gudangs = Auth::user()->getAllowedGudangList();
        $selectedSupplierId = $request->input('supplier_id');

        $unbilledGrn = [];
        if ($selectedSupplierId) {
            $unbilledGrn = $this->hutangService->getUnbilledGrnList((int) $selectedSupplierId);
        }

        return view('keuangan.hutang.create', compact('suppliers', 'gudangs', 'selectedSupplierId', 'unbilledGrn', 'unbilledSupplierSummary'));
    }

    /**
     * Simpan Tagihan Pembelian dari GRN
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalisasi format desimal jika ada koma yang terkirim dari browser
        $input = $request->all();
        if (isset($input['biaya_ongkir'])) {
            $input['biaya_ongkir'] = str_replace(',', '.', (string)$input['biaya_ongkir']);
        }
        if (isset($input['total_potongan_retur'])) {
            $input['total_potongan_retur'] = str_replace(',', '.', (string)$input['total_potongan_retur']);
        }
        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as $idx => $item) {
                if (isset($item['harga_satuan'])) {
                    $input['items'][$idx]['harga_satuan'] = str_replace(',', '.', (string)$item['harga_satuan']);
                }
                if (isset($item['qty'])) {
                    $input['items'][$idx]['qty'] = str_replace(',', '.', (string)$item['qty']);
                }
                if (isset($item['diskon_persen'])) {
                    $input['items'][$idx]['diskon_persen'] = str_replace(',', '.', (string)$item['diskon_persen']);
                }
                if (isset($item['diskon_nominal'])) {
                    $input['items'][$idx]['diskon_nominal'] = str_replace(',', '.', (string)$item['diskon_nominal']);
                }
                if (isset($item['potongan_nominal'])) {
                    $input['items'][$idx]['potongan_nominal'] = str_replace(',', '.', (string)$item['potongan_nominal']);
                }
                if (isset($item['ppn_nominal'])) {
                    $input['items'][$idx]['ppn_nominal'] = str_replace(',', '.', (string)$item['ppn_nominal']);
                }
            }
        }
        $request->merge($input);

        $request->validate([
            'supplier_id'          => 'required|exists:mst_supplier,supplier_id',
            'tgl_tagihan'          => 'required|date',
            'tgl_jatuh_tempo'      => 'required|date|after_or_equal:tgl_tagihan',
            'biaya_ongkir'         => 'nullable|numeric|min:0',
            'total_potongan_retur' => 'nullable|numeric|min:0',
            'items'                => 'required|array|min:1',
            'items.*.barang_id'    => 'nullable|exists:mst_barang,barang_id',
            'items.*.qty'          => 'required|numeric|min:0.0001',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ], [
            'supplier_id.required'           => 'Silakan pilih rekanan supplier.',
            'items.required'                 => 'Minimal harus ada 1 item barang yang ditagihkan.',
            'tgl_jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal tagihan.',
        ]);

        try {
            $hdr = $this->hutangService->createFromGrn($request->all(), Auth::user());

            return redirect()->route('keuangan.hutang.show', $hdr->hutang_id)
                ->with('success', "Tagihan Hutang Pembelian {$hdr->hutang_no} berhasil dicatat sebesar Rp " . number_format($hdr->total_tagihan, 0, ',', '.') . ".");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat tagihan hutang: ' . $e->getMessage());
        }
    }

    /**
     * Form Khusus Tagihan Gas Utilitas Bulanan (Pasca-Bayar Konsinyasi)
     */
    public function createGas(Request $request): View
    {
        $periode = $request->input('periode', date('Y-m'));
        $gasSummary = $this->hutangService->getGasUsageSummary($periode);
        
        // Cari supplier gas (misal PGN, Samator, Pertamina, atau semua supplier)
        $suppliers = MstSupplier::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('supplier_nm')
            ->get();

        return view('keuangan.hutang.create-gas', compact('periode', 'gasSummary', 'suppliers'));
    }

    /**
     * Simpan Tagihan Gas Utilitas Bulanan
     */
    public function storeGas(Request $request): RedirectResponse
    {
        $request->validate([
            'periode_bulan'    => 'required|string',
            'tgl_tagihan'      => 'required|date',
            'tgl_jatuh_tempo'  => 'required|date',
        ]);

        try {
            $hdr = $this->hutangService->createTagihanGas($request->all(), Auth::user());

            return redirect()->route('keuangan.hutang.show', $hdr->hutang_id)
                ->with('success', "Tagihan Konsinyasi Gas {$hdr->hutang_no} Periode {$hdr->periode_bulan} berhasil diterbitkan sebesar Rp " . number_format($hdr->total_tagihan, 0, ',', '.') . ".");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat tagihan gas: ' . $e->getMessage());
        }
    }

    /**
     * Detail Lembar Tagihan Hutang & Riwayat Pelunasan BKK
     */
    public function show(Request $request, int $id): View|JsonResponse
    {
        $hutang = DatHutangHdr::with([
            'supplier.jenisSupplier',
            'gudang',
            'details.barang.satuanDasar',
            'details.terima',
            'bayarDetails.header.kasBank',
        ])->where('deleted_st', false)->findOrFail($id);

        $jurnal = \App\Models\Keuangan\DatJurnalHdr::with(['details.coa'])
            ->where('ref_no', $hutang->hutang_no)
            ->where('deleted_st', false)
            ->first();

        // Dukungan AJAX / Pop-up Modal Detail Tagihan (Tanpa Pindah Halaman)
        if ($request->ajax() || $request->wantsJson() || $request->input('format') === 'json') {
            return response()->json([
                'success' => true,
                'data'    => [
                    'hutang_id'           => $hutang->hutang_id,
                    'hutang_no'           => $hutang->hutang_no,
                    'supplier_nm'         => $hutang->supplier?->supplier_nm ?: 'Operasional Pabrik',
                    'supplier_alamat'     => $hutang->supplier?->alamat_txt ?: '-',
                    'supplier_telp'       => $hutang->supplier?->telepon_txt ?: '-',
                    'no_faktur_supplier'  => $hutang->no_faktur_supplier ?: '— (Tanpa No Faktur)',
                    'po_nomor'            => $hutang->po_nomor ?: '-',
                    'gudang_nm'           => $hutang->gudang?->gudang_nm ?: 'Gudang Utama',
                    'tgl_tagihan'         => $hutang->tgl_tagihan ? Carbon::parse($hutang->tgl_tagihan)->format('d M Y') : '-',
                    'tgl_jatuh_tempo'     => $hutang->tgl_jatuh_tempo ? Carbon::parse($hutang->tgl_jatuh_tempo)->format('d M Y') : '-',
                    'status_pembayaran'   => $hutang->status_pembayaran,
                    'is_overdue'          => $hutang->isOverdue(),
                    'subtotal'            => (float)$hutang->subtotal,
                    'biaya_ongkir'        => (float)$hutang->biaya_ongkir,
                    'total_ppn'           => (float)$hutang->total_ppn,
                    'total_potongan_retur'=> (float)$hutang->total_potongan_retur,
                    'total_tagihan'       => (float)$hutang->total_tagihan,
                    'total_dibayar'       => (float)$hutang->total_dibayar,
                    'sisa_hutang'         => (float)$hutang->sisa_hutang,
                    'catatan'             => $hutang->catatan ?: '-',
                    'created_by'          => $hutang->created_by ?: 'SYSTEM',
                    'full_url'            => route('keuangan.hutang.show', $hutang->hutang_id),
                    'items'               => $hutang->details->map(function ($d) {
                        return [
                            'barang_kode'      => $d->barang?->barang_kd ?: '-',
                            'barang_nama'      => $d->barang?->barang_nm ?: ($d->keterangan_item ?: 'Item Tagihan'),
                            'qty'              => (float)$d->qty,
                            'satuan'           => $d->barang?->satuanDasar?->satuan_nm ?: ($d->satuan_nm ?: 'Unit'),
                            'harga_satuan'     => (float)$d->harga_satuan,
                            'diskon_persen'    => (float)$d->diskon_persen,
                            'potongan_nominal' => (float)$d->potongan_nominal,
                            'subtotal'         => (float)$d->subtotal,
                            'penerimaan_no'    => $d->terima?->penerimaan_no ?: '-',
                        ];
                    }),
                    'jurnal'              => $jurnal ? [
                        'jurnal_id'       => $jurnal->jurnal_id,
                        'jurnal_no'       => $jurnal->jurnal_no,
                        'tgl_jurnal'      => $jurnal->tgl_jurnal ? Carbon::parse($jurnal->tgl_jurnal)->format('d M Y') : '-',
                        'tipe_transaksi'  => $jurnal->tipe_transaksi,
                        'total_debet'     => (float)$jurnal->total_debet,
                        'total_kredit'    => (float)$jurnal->total_kredit,
                        'is_balanced'     => (bool)$jurnal->is_balanced,
                        'keterangan'      => $jurnal->keterangan,
                        'created_by'      => $jurnal->created_by ?: 'SYSTEM',
                        'details'         => $jurnal->details->map(function ($jd) {
                            return [
                                'kode_akun'  => $jd->coa?->kode_akun ?: '-',
                                'nama_akun'  => $jd->coa?->nama_akun ?: '-',
                                'debet'      => (float)$jd->debet,
                                'kredit'     => (float)$jd->kredit,
                                'keterangan' => $jd->keterangan,
                            ];
                        })
                    ] : null,
                    'pembayaran'          => $hutang->bayarDetails->map(function ($b) {
                        return [
                            'bkk_no'     => $b->header?->bayar_no ?: '-',
                            'tgl_bayar'  => $b->header?->tgl_bayar ? Carbon::parse($b->header->tgl_bayar)->format('d M Y') : '-',
                            'kas_bank'   => $b->header?->kasBank?->nama_bank ?: 'Kas / Bank',
                            'nominal'    => (float)$b->nominal_dibayar,
                        ];
                    }),
                ],
            ]);
        }

        $kasBanks = $this->kasBankService->getAllKasBank();

        return view('keuangan.hutang.show', compact('hutang', 'kasBanks', 'jurnal'));
    }

    /**
     * Perbarui No. Faktur Supplier dan Tanggal Jatuh Tempo oleh tim Keuangan
     */
    public function updateTempo(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'tgl_jatuh_tempo'    => 'required|date',
            'no_faktur_supplier' => 'nullable|string|max:100',
            'catatan'            => 'nullable|string|max:255',
        ]);

        try {
            $this->hutangService->updateTempoDanFaktur($id, $request->all(), Auth::user());

            return redirect()->route('keuangan.hutang.show', $id)
                ->with('success', "Informasi Faktur & Tanggal Jatuh Tempo berhasil diperbarui.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Hapus / Batalkan Tagihan
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $hutang = DatHutangHdr::where('deleted_st', false)->findOrFail($id);
            $this->hutangService->destroy($hutang, Auth::user());

            return redirect()->route('keuangan.hutang.index')
                ->with('success', "Tagihan Hutang {$hutang->hutang_no} berhasil dibatalkan.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan tagihan: ' . $e->getMessage());
        }
    }

    /**
     * AJAX API: Ambil daftar GRN belum ditagih berdasarkan supplier
     */
    public function ajaxUnbilledGrn(Request $request): JsonResponse
    {
        $supplierId = $request->input('supplier_id');
        $grnList = $this->hutangService->getUnbilledGrnList($supplierId ? (int) $supplierId : null);
        $supplier = $supplierId ? MstSupplier::find((int) $supplierId) : null;

        return response()->json([
            'status'   => 'success',
            'data'     => $grnList,
            'supplier' => $supplier ? [
                'id'        => $supplier->supplier_id,
                'name'      => $supplier->supplier_nm,
                'code'      => $supplier->supplier_cd,
                'top_hari'  => (int) ($supplier->top_hari ?? 14),
                'kontak_no' => $supplier->kontak_no,
                'alamat'    => $supplier->alamat_txt,
            ] : null,
        ]);
    }

    /**
     * AJAX API: Ambil rekap pemakaian gas bulanan
     */
    public function ajaxGasSummary(Request $request): JsonResponse
    {
        $periode = $request->input('periode', date('Y-m'));
        $summary = $this->hutangService->getGasUsageSummary($periode);

        return response()->json([
            'status' => 'success',
            'data'   => $summary,
        ]);
    }
}
