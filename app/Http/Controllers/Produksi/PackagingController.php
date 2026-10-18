<?php

namespace App\Http\Controllers\Produksi;

use App\Exports\Produksi\RekapHppPackagingExport;
use App\Http\Controllers\Controller;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\Produksi\DatPackagingHdr;
use App\Models\Produksi\DatProduksiHdr;
use App\Services\Produksi\PackagingService;
use App\Services\Produksi\TarifKemasanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PackagingController extends Controller
{
    public function __construct(
        protected PackagingService $packagingService,
        protected \App\Services\Produksi\LaporanKemasOperatorService $operatorService,
        protected TarifKemasanService $tarifKemasanService
    ) {}

    /**
     * Halaman index daftar riwayat Pengemasan Barang Jadi (Packaging & HPP).
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'         => $request->input('search'),
            'pack_tgl'       => $request->input('pack_tgl'),
            'barang_jadi_id' => $request->input('barang_jadi_id'),
            'status_cd'      => $request->input('status_cd'),
        ];

        $dataList = $this->packagingService->getAllPaginated(15, $filters);
        $metrics = $this->packagingService->getMetrics($filters);
        $unprocessedCount = \App\Models\Produksi\DatLaporanKemasOperator::where('deleted_st', false)
            ->where('status_cd', 'SUBMITTED')
            ->count();

        // Ambil daftar barang jadi aktif untuk filter (Khusus FG)
        $barangJadiList = MstBarang::active()
            ->whereHas('jenisBarang', fn($q) => $q->where('jenis_barang_cd', 'FG'))
            ->orderBy('barang_nm')
            ->get(['barang_id', 'barang_cd', 'barang_nm']);

        return view('produksi.packaging.index', compact('dataList', 'metrics', 'filters', 'barangJadiList', 'unprocessedCount'));
    }

    /**
     * Halaman web office: Riwayat Hasil Catatan dari Operator Packaging Lapangan
     */
    public function catatanOperator(Request $request): View
    {
        $filters = [
            'search'         => $request->input('search'),
            'tgl_kemas'      => $request->input('tgl_kemas'),
            'shift'          => $request->input('shift'),
            'status_cd'      => $request->input('status_cd'),
            'barang_jadi_id' => $request->input('barang_jadi_id'),
        ];

        $dataList = $this->operatorService->getLaporanListPaginated($filters, 15);
        $metrics = $this->operatorService->getMetrics($filters);
        $unprocessedCount = $metrics['menunggu_office'] ?? 0;

        $barangJadiList = MstBarang::active()
            ->whereHas('jenisBarang', fn($q) => $q->where('jenis_barang_cd', 'FG'))
            ->orderBy('barang_nm')
            ->get(['barang_id', 'barang_cd', 'barang_nm']);

        return view('produksi.packaging.catatan_operator.index', compact('dataList', 'metrics', 'filters', 'barangJadiList', 'unprocessedCount'));
    }

    /**
     * Detail Lembar Bukti Laporan Catatan Operator Lapangan (Web Office)
     */
    public function showCatatanOperator(int $id): View
    {
        $laporan = $this->operatorService->getById($id);

        return view('produksi.packaging.catatan_operator.show', compact('laporan'));
    }

    /**
     * Form tambah pencatatan pengemasan baru (Smart Backflush BOM - Satu Pintu Input).
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        $gudangList = $user ? $user->getAllowedGudangList() : MstGudang::active()->get();

        $selectedLaporan = null;
        if ($request->has('laporan_id')) {
            try {
                $selectedLaporan = $this->operatorService->getById((int) $request->input('laporan_id'));
            } catch (Exception) {
                $selectedLaporan = null;
            }
        }

        // Ambil daftar barang jadi untuk dropdown target kemas (Khusus Finished Goods / FG, bukan WIP/Curah)
        $barangJadiList = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->active()
            ->whereHas('jenisBarang', fn($q) => $q->where('jenis_barang_cd', 'FG'))
            ->orderBy('barang_nm')
            ->get();

        // Ambil peta standar tarif kemasan dari Master Data Standar Harga Kemasan & Packaging
        $tarifMap = $this->tarifKemasanService->getTarifMap();
        $tarifLakbanDefault = (float) ($tarifMap['LAKBAN_GRAM'] ?? 36.70);

        // Ambil daftar barang kemasan, bumbu, penolong & curah untuk komponen manual / tambahan
        $kemasanBarangList = MstBarang::active()
            ->with(['satuanDasar', 'jenisBarang'])
            ->where(function ($q) {
                $q->whereHas('jenisBarang', fn($j) => $j->whereIn('jenis_barang_cd', ['PACK', 'BP', 'BUMBU', 'WIP', 'SUPP']))
                  ->orWhere('barang_nm', 'ILIKE', '%KARTON%')
                  ->orWhere('barang_nm', 'ILIKE', '%ROLL%')
                  ->orWhere('barang_nm', 'ILIKE', '%LAKBAN%')
                  ->orWhere('barang_nm', 'ILIKE', '%PLASTIK%')
                  ->orWhere('barang_nm', 'ILIKE', '%RAFIA%')
                  ->orWhere('barang_nm', 'ILIKE', '%SARUNG TANGAN%')
                  ->orWhere('barang_nm', 'ILIKE', '%BERKO%')
                  ->orWhere('barang_nm', 'ILIKE', '%EXP%')
                  ->orWhere('barang_cd', 'ILIKE', '%EXP%')
                  ->orWhere('barang_nm', 'ILIKE', '%KADALUARSA%')
                  ->orWhere('barang_nm', 'ILIKE', '%LABEL%')
                  ->orWhere('barang_nm', 'ILIKE', '%STIKER%')
                  ->orWhere('barang_nm', 'ILIKE', '%TALI%')
                  ->orWhere('barang_nm', 'ILIKE', '%BAL%');
            })
            ->orderBy('barang_nm')
            ->get()
            ->map(function ($b) use ($tarifMap) {
                $cd = strtoupper($b->jenisBarang->jenis_barang_cd ?? '');
                $nm = strtoupper($b->barang_nm ?? '');

                if ($cd === 'WIP' || str_contains($nm, 'BERKO') || str_contains($nm, 'CURAH')) {
                    $kategori = 'WIP';
                    $kategoriLabel = 'Keripik Curah (WIP)';
                } elseif (str_contains($nm, 'BUMBU') || $cd === 'BUMBU') {
                    $kategori = 'BUMBU';
                    $kategoriLabel = 'Bumbu & Perasa';
                } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'ROLL') || str_contains($nm, 'LAKBAN') || str_contains($nm, 'PLASTIK') || $cd === 'PACK') {
                    $kategori = 'KEMASAN';
                    $kategoriLabel = 'Bahan Kemasan';
                } else {
                    $kategori = 'LAIN';
                    $kategoriLabel = 'Bahan Penolong / Lainnya';
                }

                // Prioritaskan harga acuan standar dari Master Standar Harga Kemasan & Packaging
                $hargaStandar = (float) ($b->harga_beli_standar ?? 0);
                if (str_contains($nm, 'KARTON') && (str_contains($nm, 'MAKSI') || str_contains($nm, 'MX') || str_contains($nm, 'MK'))) {
                    $hargaStandar = (float) ($tarifMap['KARTON_MX'] ?? $hargaStandar);
                } elseif (str_contains($nm, 'KARTON')) {
                    $hargaStandar = (float) ($tarifMap['KARTON_XX'] ?? $hargaStandar);
                } elseif (str_contains($nm, 'BUMBU') || str_contains($nm, 'BALADO')) {
                    $hargaStandar = (float) ($tarifMap['BUMBU_BALADO'] ?? $hargaStandar);
                } elseif (str_contains($nm, 'ROLL') && (str_contains($nm, 'MAKSI') || str_contains($nm, 'MX') || str_contains($nm, 'MK'))) {
                    $hargaStandar = (float) ($tarifMap['ROLL_MX'] ?? $hargaStandar);
                } elseif (str_contains($nm, 'ROLL') || str_contains($nm, 'PLASTIK')) {
                    $hargaStandar = (float) ($tarifMap['ROLL_XX'] ?? $hargaStandar);
                } elseif (str_contains($nm, 'LAKBAN')) {
                    $hargaStandar = (float) ($tarifMap['LAKBAN_ROLL'] ?? $hargaStandar);
                } elseif (str_contains($nm, 'EXP') || str_contains($nm, 'KERTAS')) {
                    $hargaStandar = (float) ($tarifMap['KERTAS_EXP'] ?? $hargaStandar);
                } elseif (str_contains($nm, 'BERKO')) {
                    $hargaStandar = (float) ($tarifMap['WIP_BERKO'] ?? $hargaStandar);
                } elseif ($kategori === 'WIP') {
                    $hargaStandar = (float) ($tarifMap['WIP_CURAH'] ?? $hargaStandar);
                }

                return [
                    'barang_id'      => $b->barang_id,
                    'barang_cd'      => $b->barang_cd,
                    'barang_nm'      => $b->barang_nm,
                    'satuan_cd'      => $b->satuanDasar?->satuan_cd ?? 'PCS',
                    'satuan_nm'      => $b->satuanDasar?->satuan_nm ?? 'Pcs',
                    'harga_standar'  => $hargaStandar,
                    'kategori'       => $kategori,
                    'kategori_label' => $kategoriLabel,
                ];
            });

        // Ambil daftar dokumen HPP Produksi (WIP) aktif untuk opsi sinkronisasi HPP curah - Prioritas FIFO
        $produksiList = DatProduksiHdr::with(['details.barang.satuanDasar'])
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'VOID')
            ->where('produksi_tgl', '>=', now()->subDays(30)->toDateString())
            ->orderBy('produksi_tgl', 'asc')
            ->orderBy('produksi_id', 'asc')
            ->get();

        return view('produksi.packaging.create', compact('gudangList', 'barangJadiList', 'kemasanBarangList', 'produksiList', 'selectedLaporan', 'tarifLakbanDefault'));
    }

    /**
     * AJAX endpoint untuk mengambil detail tiket produksi (PRD) & dokumen kemasan gudang terkait.
     */
    public function ajaxProduksiDetails(Request $request): JsonResponse
    {
        $produksiId = (int) $request->input('produksi_id');
        if (!$produksiId) {
            return response()->json(['success' => false, 'message' => 'Tiket produksi wajib dipilih.'], 400);
        }

        $produksi = \App\Models\Produksi\DatProduksiHdr::with([
            'details.barang.satuanDasar',
            'pengeluaranKemasan.details.barang.satuanDasar'
        ])
            ->where('deleted_st', false)
            ->find($produksiId);

        if (!$produksi) {
            return response()->json(['success' => false, 'message' => 'Data tiket produksi tidak ditemukan.'], 404);
        }

        $barangIdReq = (int) $request->input('barang_id');

        // Cari master barang WIP (prioritaskan barang_id dari request, atau rincian detail pertama, atau KS BERKO)
        $wipBarang = null;
        $wipQty = 0;
        $wipHpp = (float) $produksi->hpp_per_kg;
        $batchTgl = $produksi->batch_wip_no 
            ?: ($produksi->produksi_tgl ? $produksi->produksi_tgl->format('d m Y') : date('d m Y'));

        if ($barangIdReq > 0) {
            $matchedDtl = $produksi->details->where('barang_id', $barangIdReq)->first();
            if ($matchedDtl) {
                $wipBarang = $matchedDtl->barang;
                $wipQty = (float) $matchedDtl->qty_kg;
                if ($matchedDtl->hpp_satuan > 0) $wipHpp = (float) $matchedDtl->hpp_satuan;
                if (!empty($matchedDtl->batch_no)) $batchTgl = $matchedDtl->batch_no;
            }
        }

        if (!$wipBarang && $produksi->details->isNotEmpty()) {
            $firstDtl = $produksi->details->first(fn($d) => (float)$d->qty_kg > 0) ?: $produksi->details->first();
            if ($firstDtl && $firstDtl->barang) {
                $wipBarang = $firstDtl->barang;
                $wipQty = (float) $firstDtl->qty_kg;
                if ($firstDtl->hpp_satuan > 0) $wipHpp = (float) $firstDtl->hpp_satuan;
                if (!empty($firstDtl->batch_no)) $batchTgl = $firstDtl->batch_no;
            }
        }

        if (!$wipBarang) {
            $wipBarang = MstBarang::with('satuanDasar')
                ->where('barang_nm', 'ILIKE', '%KS BERKO%')
                ->orWhere('barang_cd', 'WIP-BRK')
                ->first();
            $wipQty = (float) ($produksi->total_berko_qty > 0 ? $produksi->total_berko_qty : $produksi->berko_qty);
        }

        // Cari dokumen pemakaian gudang yang ditautkan ke produksi ini
        $linkedPakai = $produksi->pengeluaranKemasan()
            ->where('deleted_st', false)
            ->latest('pakai_id')
            ->first();

        $expDateStr = $produksi->exp_date 
            ? $produksi->exp_date->format('Y-m-d') 
            : ($produksi->produksi_tgl ? $produksi->produksi_tgl->copy()->addMonths(6)->format('Y-m-d') : date('Y-m-d', strtotime('+6 months')));

        return response()->json([
            'success' => true,
            'data'    => [
                'produksi_id'      => $produksi->produksi_id,
                'produksi_no'      => $produksi->produksi_no,
                'produksi_tgl'     => $produksi->produksi_tgl?->format('Y-m-d'),
                'batch_wip_no'     => $batchTgl,
                'exp_date'         => $expDateStr,
                'total_berko_qty'  => $wipQty,
                'hpp_per_kg'       => $wipHpp,
                'berko_barang'     => $wipBarang ? [
                    'barang_id'  => $wipBarang->barang_id,
                    'barang_cd'  => $wipBarang->barang_cd,
                    'barang_nm'  => $wipBarang->barang_nm,
                    'satuan_cd'  => $wipBarang->satuanDasar?->satuan_cd ?? 'KG',
                ] : null,
                'linked_pakai_id'  => $linkedPakai?->pakai_id,
                'linked_pakai_no'  => $linkedPakai?->pakai_no,
            ]
        ]);
    }

    /**
     * AJAX endpoint untuk menarik rincian barang keluar dari dokumen pemakaian gudang (PKI/OUT).
     */
    public function ajaxPakaiDetails(Request $request): JsonResponse
    {
        $pakaiId = (int) $request->input('pakai_id');
        if (!$pakaiId) {
            return response()->json(['success' => false, 'message' => 'Dokumen gudang wajib dipilih.'], 400);
        }

        $pakai = \App\Models\Gudang\DatPakaiHdr::with(['details.barang.satuanDasar', 'details.barang.jenisBarang'])
            ->where('deleted_st', false)
            ->find($pakaiId);

        if (!$pakai) {
            return response()->json(['success' => false, 'message' => 'Data dokumen pemakaian gudang tidak ditemukan.'], 404);
        }

        $items = [];
        foreach ($pakai->details as $dtl) {
            $b = $dtl->barang;
            if (!$b) continue;

            $jenisCd = strtoupper($b->jenisBarang?->jenis_barang_cd ?? '');
            $kategori = 'KEMASAN';
            if (in_array($jenisCd, ['BUMBU', 'BMB'])) {
                $kategori = 'BUMBU';
            } elseif (in_array($jenisCd, ['WIP', 'SETENGAH_JADI'])) {
                $kategori = 'WIP';
            }

            $items[] = [
                'barang_id'        => $b->barang_id,
                'barang_cd'        => $b->barang_cd,
                'barang_nm'        => $b->barang_nm,
                'satuan_cd'        => $b->satuanDasar?->satuan_cd ?? 'PCS',
                'kategori_bahan'   => $kategori,
                'is_wip'           => ($kategori === 'WIP'),
                'from_gudang'      => true,
                'qty_standar'      => (float) $dtl->qty_keluar,
                'qty_aktual'       => (float) $dtl->qty_keluar,
                'harga_satuan'     => (float) $dtl->harga_satuan,
                'subtotal_nilai'   => (float) ($dtl->total_harga > 0 ? $dtl->total_harga : ($dtl->qty_keluar * $dtl->harga_satuan)),
                'batch_no'         => $dtl->batch_no ?? '',
                'stok_tersedia'    => (float) $dtl->qty_keluar,
                'cukup_stok'       => true,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'pakai_id'   => $pakai->pakai_id,
                'pakai_no'   => $pakai->pakai_no,
                'pakai_tgl'  => $pakai->pakai_tgl?->format('Y-m-d'),
                'items'      => $items,
            ]
        ]);
    }

    /**
     * AJAX endpoint untuk mengambil pratinjau resep BOM, tarikan stok WIP & estimasi HPP.
     */
    public function ajaxBomPreview(Request $request): JsonResponse
    {
        $barangJadiId = (int) $request->input('barang_jadi_id');
        $targetKartonQty = (float) $request->input('target_karton_qty', 100);
        $produksiId = (int) $request->input('produksi_id', 0);

        if (!$barangJadiId) {
            return response()->json(['success' => false, 'message' => 'Barang jadi target wajib dipilih.'], 400);
        }

        if ($targetKartonQty <= 0) {
            $targetKartonQty = 1;
        }

        try {
            $preview = $this->packagingService->getBomPreview($barangJadiId, $targetKartonQty, $produksiId);
            return response()->json(['success' => true, 'data' => $preview]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan dokumen pengemasan baru, menghitung HPP, dan memutasi stok.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pack_tgl'                 => 'required|date',
            'barang_jadi_id'           => 'required|exists:mst_barang,barang_id',
            'gudang_id'                => 'required|exists:mst_gudang,gudang_id',
            'bom_id'                   => 'nullable|exists:mst_bom_hdr,bom_id',
            'produksi_id'              => 'nullable|exists:dat_produksi_hdr,produksi_id',
            'pakai_id'                 => 'nullable|exists:dat_pakai_hdr,pakai_id',
            'target_karton_qty'        => 'nullable|numeric|min:0.01',
            'hasil_karton_qty'         => 'required|numeric|min:0.01',
            'isi_per_karton_pcs'       => 'nullable|numeric|min:1',
            'gramatur_gr'              => 'nullable|numeric|min:0',
            'batch_no'                 => 'nullable|string|max:100',
            'expired_tgl'              => 'nullable|date',
            'lakban_atas_gr'           => 'nullable|numeric|min:0',
            'lakban_bawah_gr'          => 'nullable|numeric|min:0',
            'lakban_jumlah_gr'         => 'nullable|numeric|min:0',
            'lakban_nilai'             => 'nullable|numeric|min:0',
            'total_biaya_tenaga_kerja' => 'nullable|numeric|min:0',
            'total_biaya_lain'         => 'nullable|numeric|min:0',
            'catatan_txt'              => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.barang_id'        => 'required|exists:mst_barang,barang_id',
            'items.*.kategori_bahan'   => 'required|string|max:30',
            'items.*.batch_no'         => 'nullable|string|max:100',
            'items.*.qty_standar'      => 'nullable|numeric|min:0',
            'items.*.qty_aktual'       => 'required|numeric|min:0',
            'items.*.satuan_cd'        => 'nullable|string|max:20',
            'items.*.harga_satuan'     => 'required|numeric|min:0',
            'items.*.catatan_txt'      => 'nullable|string|max:255',
        ], [
            'pack_tgl.required'        => 'Tanggal pengemasan wajib diisi.',
            'barang_jadi_id.required'  => 'Produk jadi target wajib dipilih.',
            'hasil_karton_qty.required'=> 'Jumlah karton hasil kemas wajib diisi.',
            'items.required'           => 'Minimal harus ada 1 komponen bahan kemasan/WIP yang dikonsumsi.',
        ]);

        $validated['target_karton_qty'] = $validated['target_karton_qty'] ?? $validated['hasil_karton_qty'];

        try {
            $header = $this->packagingService->storePackaging($validated, auth()->user()?->name);

            // Tautkan status laporan operator jika ditarik dari operator lapangan
            if ($request->filled('laporan_operator_id')) {
                app(\App\Services\Produksi\LaporanKemasOperatorService::class)->markAsProcessed(
                    (int) $request->input('laporan_operator_id'),
                    $header->pack_id,
                    auth()->user()
                );
            }

            return redirect()->route('produksi.packaging.show', $header->pack_id)
                ->with('success', "Proses pengemasan nomor {$header->pack_no} berhasil disimpan! HPP terbentuk: Rp " . number_format($header->hpp_per_karton, 0, ',', '.') . "/Karton.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pengemasan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan dokumen berita acara pengemasan & rincian HPP.
     */
    public function show(int $id): View
    {
        $header = DatPackagingHdr::with([
            'barangJadi.satuanDasar', 
            'gudang', 
            'details.barang.satuanDasar', 
            'bom',
            'produksi',
            'pemakaian.details.barang'
        ])->findOrFail($id);

        return view('produksi.packaging.show', compact('header'));
    }

    /**
     * Membatalkan (Void) Dokumen Pengemasan.
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:255',
        ], [
            'alasan_batal.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        try {
            $header = $this->packagingService->voidPackaging($id, $request->input('alasan_batal'), auth()->user()?->name);

            return redirect()->route('produksi.packaging.index')
                ->with('success', "Dokumen {$header->pack_no} berhasil dibatalkan dan seluruh mutasi stok telah dikembalikan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Khusus Buku Rekapitulasi Harga Pokok Produksi Pengemasan (HPP Packaging):
     * - Mode Harian: Analisis per hari dalam 1 bulan kalender (Beban Curah, Kemasan, Borongan & HPP/Karton)
     * - Mode Bulanan: Analisis tren per bulan dalam 1 tahun fiskal (Januari s/d Desember)
     */
    public function rekap(Request $request): View
    {
        $mode = $request->input('mode') === 'bulanan' ? 'bulanan' : 'harian';
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));
        $barangJadiId = $request->filled('barang_jadi_id') ? (int) $request->input('barang_jadi_id') : null;

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $yearsList = [2024, 2025, 2026, 2027];
        $monthName = $monthsList[$month] ?? 'Januari';

        $report = null;
        $yearlyReport = null;

        if ($mode === 'bulanan') {
            $yearlyReport = $this->packagingService->getYearlyReport($year, $barangJadiId);
        } else {
            $report = $this->packagingService->getMonthlyReport($year, $month, $barangJadiId);
        }

        $barangJadiList = MstBarang::active()
            ->whereHas('jenisBarang', fn($q) => $q->whereIn('jenis_barang_cd', ['FG', 'WIP']))
            ->orderBy('barang_nm')
            ->get(['barang_id', 'barang_cd', 'barang_nm']);

        return view('produksi.packaging.rekap', compact(
            'mode',
            'year',
            'month',
            'monthName',
            'monthsList',
            'yearsList',
            'report',
            'yearlyReport',
            'barangJadiList',
            'barangJadiId'
        ));
    }

    /**
     * Export data Rekapitulasi HPP Pengemasan ke file Excel (.xlsx).
     */
    public function exportRekapExcel(Request $request): StreamedResponse
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));
        $barangJadiId = $request->filled('barang_jadi_id') ? (int) $request->input('barang_jadi_id') : null;

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsList[$month] ?? 'Januari';

        $printedBy = auth()->user()?->username ?? 'Admin';
        $printedAt = Carbon::now()->format('d/m/Y H:i');

        $report = $this->packagingService->getMonthlyReport($year, $month, $barangJadiId);
        $exporter = new RekapHppPackagingExport($report, $year, $month, $monthName, $printedBy, $printedAt);
        return $exporter->download("Rekap_HPP_Packaging_{$monthName}_{$year}.xlsx");
    }

    /**
     * Cetak dokumen PDF resmi Rekapitulasi HPP Pengemasan (Legal Landscape).
     */
    public function exportRekapPdf(Request $request)
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));
        $barangJadiId = $request->filled('barang_jadi_id') ? (int) $request->input('barang_jadi_id') : null;

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsList[$month] ?? 'Januari';

        $report = $this->packagingService->getMonthlyReport($year, $month, $barangJadiId);
        $pdf = Pdf::loadView('produksi.pdf.rekap-hpp-packaging', compact('report', 'year', 'month', 'monthName'))
            ->setPaper('legal', 'landscape');
        return $pdf->stream("Rekap_HPP_Packaging_{$monthName}_{$year}.pdf");
    }
}
