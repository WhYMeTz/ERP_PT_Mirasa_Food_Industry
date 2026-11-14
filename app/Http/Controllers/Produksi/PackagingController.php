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
use Illuminate\Support\Facades\Auth;
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

        // Ambil daftar barang jadi aktif untuk filter (Khusus FG)
        $barangJadiList = MstBarang::active()
            ->whereHas('jenisBarang', fn($q) => $q->where('jenis_barang_cd', 'FG'))
            ->orderBy('barang_nm')
            ->get(['barang_id', 'barang_cd', 'barang_nm']);

        return view('produksi.packaging.index', compact('dataList', 'metrics', 'filters', 'barangJadiList'));
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

        // Ambil standar tarif lakban per gram langsung dari Master Barang (LKB-GR01) atau fallback acuan pabrik
        $lakbanGramBarang = MstBarang::where('barang_cd', 'LKB-GR01')->first();
        $tarifLakbanDefault = $lakbanGramBarang && (float)$lakbanGramBarang->harga_beli_standar > 0 
            ? (float)$lakbanGramBarang->harga_beli_standar 
            : 36.70;

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
            ->map(function ($b) {
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

                // Single Source of Truth: Prioritaskan harga beli standar dari Master Katalog Barang
                $hargaStandar = (float) ($b->harga_beli_standar ?? 0);

                // Safety Fallback Guard: Jika harga di master barang masih 0/kosong, gunakan acuan benchmark pabrik
                if ($hargaStandar <= 0) {
                    if (str_contains($nm, 'KARTON') && (str_contains($nm, 'MAKSI') || str_contains($nm, 'MX') || str_contains($nm, 'MK'))) {
                        $hargaStandar = 1322.60;
                    } elseif (str_contains($nm, 'KARTON')) {
                        $hargaStandar = 1338.90;
                    } elseif (str_contains($nm, 'BUMBU') || str_contains($nm, 'BALADO')) {
                        $hargaStandar = 38499.00;
                    } elseif (str_contains($nm, 'ROLL') && (str_contains($nm, 'MAKSI') || str_contains($nm, 'MX') || str_contains($nm, 'MK'))) {
                        $hargaStandar = 364427.00;
                    } elseif (str_contains($nm, 'ROLL') || str_contains($nm, 'PLASTIK')) {
                        $hargaStandar = 364830.00;
                    } elseif (str_contains($nm, 'LAKBAN')) {
                        $satuan = strtoupper($b->satuanDasar?->satuan_cd ?? '');
                        $hargaStandar = in_array($satuan, ['GR', 'GRAM', 'G']) ? 36.70 : 45880.00;
                    } elseif (str_contains($nm, 'EXP') || str_contains($nm, 'KERTAS')) {
                        $hargaStandar = 250.00;
                    } elseif (str_contains($nm, 'BERKO')) {
                        $hargaStandar = 10000.00;
                    } elseif ($kategori === 'WIP') {
                        $hargaStandar = 25500.00;
                    }
                }

                // POIN 28: Rule Amortisasi HPP Karton Khusus IFM (Dibagi 5) & Karton Manual (Rp 0)
                if ($b->barang_cd === 'KMS-KTN-REJ' || str_contains($nm, 'REJECT') || str_contains($nm, 'MANUAL')) {
                    $hargaStandar = 0.00; // Manual karton tidak masuk hitungan HPP
                } elseif ($b->barang_cd === 'KIF00G-BP1' || (str_contains($nm, 'IFM') && str_contains($nm, 'KARTON'))) {
                    if ($hargaStandar > 2000) {
                        $hargaStandar = round($hargaStandar / 5, 2); // Khusus IFM langsung dibagi 5 = Rp 1.748,20
                    }
                } elseif ($b->barang_cd === 'KIF00G-PUT') {
                    $hargaStandar = 1748.20;
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
            ->where('produksi_tgl', '>=', now()->subDays(60)->toDateString())
            ->orderBy('produksi_tgl', 'asc')
            ->orderBy('produksi_id', 'asc')
            ->get();

        // Hitung pemakaian dan sisa kuantitas WIP per produksi & barang_id agar stok yang habis terkunci otomatis
        foreach ($produksiList as $p) {
            $totalPackedWip = \App\Models\Produksi\DatPackagingDtl::whereHas('header', function ($q) use ($p) {
                $q->where('deleted_st', false)
                  ->where('status_cd', '!=', 'VOID')
                  ->where('produksi_id', $p->produksi_id);
            })->where('kategori_bahan', 'WIP')
              ->selectRaw('barang_id, SUM(qty_aktual) as total_pakai')
              ->groupBy('barang_id')
              ->pluck('total_pakai', 'barang_id')
              ->toArray();

            foreach ($p->details as $dtl) {
                $used = (float) ($totalPackedWip[$dtl->barang_id] ?? 0);
                $initial = (float) ($dtl->qty_kg > 0 ? $dtl->qty_kg : $dtl->qty_hasil);
                $dtl->sisa_wip_kg = max(0, round($initial - $used, 2));
                $dtl->is_wip_habis = ($dtl->sisa_wip_kg <= 0.05);
            }
        }

        $tarifs = $this->tarifKemasanService->getAllTarif();

        // 1. Varian Output WIP Hasil Masak Dapur
        $wipList = \App\Models\Produksi\DatProduksiDtl::with(['produksi', 'barang.satuanDasar'])
            ->where('deleted_st', false)
            ->whereHas('produksi', function ($q) {
                $q->where('deleted_st', false)
                  ->where('status_cd', '!=', 'VOID');
            })
            ->where('qty_kg', '>', 0)
            ->orderByDesc('output_id')
            ->get();

        // 2. Karyawan (Operator & Leader Kemas)
        $karyawanList = \App\Models\MasterData\MstKaryawan::where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('karyawan_nm')
            ->get();

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
            ->whereHas('jenisBarang', fn($q) => $q->whereNotIn('jenis_barang_cd', ['FG', 'WIP', 'BB', 'RAW']))
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

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

        $lakbanList = MstBarang::with('satuanDasar')
            ->where(function ($q) {
                $q->where('barang_nm', 'ILIKE', '%lakban%')
                  ->orWhere('barang_nm', 'ILIKE', '%selotip%')
                  ->orWhere('barang_nm', 'ILIKE', '%tape%')
                  ->orWhere('barang_cd', 'ILIKE', 'LKB%');
            })
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->orderBy('barang_nm')
            ->get();

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

        $kartonPuteran = MstBarang::where('barang_cd', 'KIF00G-PUT')->first();
        $kartonBaru = MstBarang::where('barang_cd', 'KIF00G-BP1')->first();
        $kartonReject = MstBarang::where('barang_cd', 'KMS-KTN-REJ')->first();
        $stokKartonPuteran = $kartonPuteran ? (float) \App\Models\Gudang\DatStokBatch::where('barang_id', $kartonPuteran->barang_id)->where('deleted_st', false)->sum('sisa_qty') : 0;
        $stokKartonBaru = $kartonBaru ? (float) \App\Models\Gudang\DatStokBatch::where('barang_id', $kartonBaru->barang_id)->where('deleted_st', false)->sum('sisa_qty') : 0;
        $stokKartonReject = $kartonReject ? (float) \App\Models\Gudang\DatStokBatch::where('barang_id', $kartonReject->barang_id)->where('deleted_st', false)->sum('sisa_qty') : 0;

        // Peta Informasi Batch Aktif (FIFO) & Sisa Stok Fisik Seluruh Kemasan
        $allPkgBarangIds = collect()
            ->merge($bumbuList->pluck('barang_id'))
            ->merge($plastikList->pluck('barang_id'))
            ->merge($kartonList->pluck('barang_id'))
            ->merge($lakbanList->pluck('barang_id'))
            ->merge($expiredList->pluck('barang_id'))
            ->merge($kemasanLainList->pluck('barang_id'))
            ->when($kartonPuteran, fn($c) => $c->push($kartonPuteran->barang_id))
            ->when($kartonBaru, fn($c) => $c->push($kartonBaru->barang_id))
            ->unique()
            ->filter();

        $activeBatches = \App\Models\Gudang\DatStokBatch::whereIn('barang_id', $allPkgBarangIds)
            ->where('deleted_st', false)
            ->where('sisa_qty', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get(['barang_id', 'batch_no', 'sisa_qty'])
            ->groupBy('barang_id');

        $batchInfoMap = [];
        foreach ($activeBatches as $bId => $batchGroup) {
            $firstBatch = $batchGroup->first();
            $batchInfoMap[$bId] = [
                'batch_no' => $firstBatch?->batch_no ?? '-',
                'sisa_qty' => (float) $batchGroup->sum('sisa_qty'),
            ];
        }

        $initialPackTgl = old('pack_tgl', $selectedLaporan?->laporan_tgl?->format('Y-m-d') ?? date('Y-m-d'));
        $initialLabor = app(\App\Services\Produksi\ProduksiService::class)->getLaborDataFromKaryawan($initialPackTgl, 'Langsung Packing');

        return view('produksi.packaging.create', compact(
            'gudangList', 
            'barangJadiList', 
            'kemasanBarangList', 
            'produksiList', 
            'wipList',
            'selectedLaporan', 
            'initialLabor',
            'tarifLakbanDefault', 
            'tarifs',
            'karyawanList',
            'bumbuList',
            'plastikList',
            'kartonList',
            'lakbanList',
            'expiredList',
            'kemasanLainList',
            'kartonPuteran',
            'kartonBaru',
            'kartonReject',
            'stokKartonPuteran',
            'stokKartonBaru',
            'stokKartonReject',
            'batchInfoMap'
        ));
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

        if ($pakai->status_st !== 'POSTED') {
            return response()->json([
                'success' => false,
                'message' => "Dokumen BPPB [{$pakai->pakai_no}] masih berstatus DRAFT. Hanya dokumen yang sudah POSTED oleh Gudang yang dapat ditarik."
            ], 422);
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
        // 1. Normalisasi tanggal kemas
        $packTgl = $request->input('pack_tgl') ?: $request->input('tgl_kemas', date('Y-m-d'));
        $request->merge(['pack_tgl' => $packTgl]);

        // 2. Normalisasi Gudang FG default jika kosong
        if (!$request->filled('gudang_id')) {
            $defaultGudang = \App\Models\MasterData\MstGudang::where('tipe_gudang_cd', 'FG')->active()->first()
                ?: \App\Models\MasterData\MstGudang::active()->first();
            if ($defaultGudang) {
                $request->merge(['gudang_id' => $defaultGudang->gudang_id]);
            }
        }

        // 3. Auto-Assemble Items dari Form Fisik Terstruktur (Tanpa Beban Nominal Rp untuk User)
        if (!$request->has('items') || empty($request->input('items'))) {
            $items = [];

            // A. Curah WIP
            $wipId = $request->input('wip_barang_id');
            $outputId = $request->input('output_id');
            $prodId = $request->input('produksi_id');
            $batchNo = $request->input('batch_no');

            if ($outputId) {
                $output = \App\Models\Produksi\DatProduksiDtl::find($outputId);
                if ($output) {
                    $wipId = $wipId ?: $output->barang_id;
                    $prodId = $prodId ?: $output->produksi_id;
                    $batchNo = $batchNo ?: $output->batch_no;
                }
            }

            $wipQty = (float) $request->input('wip_qty_kg', 0);
            if ($wipId && $wipQty > 0) {
                $wipBarang = MstBarang::find($wipId);
                $wipHpp = 0;
                if (!empty($output) && (float) $output->hpp_satuan > 0) {
                    $wipHpp = (float) $output->hpp_satuan;
                } elseif ($prodId) {
                    $prodHdr = \App\Models\Produksi\DatProduksiHdr::find($prodId);
                    $wipHpp = (float) ($prodHdr?->hpp_per_kg ?? 0);
                }
                
                // Fallback: cek kartu stok batch gudang (DatStokBatch) berdasarkan batch_no
                if ($wipHpp <= 0 && $wipId && $batchNo) {
                    $stkBatch = \App\Models\Gudang\DatStokBatch::where('barang_id', $wipId)
                        ->where('batch_no', $batchNo)
                        ->where('harga_satuan', '>', 0)
                        ->first();
                    if ($stkBatch) {
                        $wipHpp = (float) $stkBatch->harga_satuan;
                    }
                }

                if ($wipHpp <= 0) {
                    $wipHpp = (float) ($wipBarang?->harga_beli_standar ?? 25500);
                }

                $items[] = [
                    'barang_id'      => (int) $wipId,
                    'kategori_bahan' => 'WIP',
                    'batch_no'       => $batchNo,
                    'qty_standar'    => $wipQty,
                    'qty_aktual'     => $wipQty,
                    'satuan_cd'      => 'KG',
                    'harga_satuan'   => $wipHpp,
                ];
            }

            // B. Bumbu Tabur
            $bumbuId = $request->input('bumbu_barang_id');
            $bumbuQty = (float) $request->input('bumbu_qty_kg', 0);
            $bumbuSatuan = strtoupper($request->input('bumbu_satuan', 'KG'));
            if ($bumbuId && $bumbuQty > 0) {
                $bumbuBarang = MstBarang::find($bumbuId);
                // Normalisasi kuantitas ke KG jika operator memilih inputan Gram
                $bumbuQtyKg = in_array($bumbuSatuan, ['GR', 'GRAM', 'G']) ? round($bumbuQty / 1000, 4) : $bumbuQty;
                $items[] = [
                    'barang_id'      => (int) $bumbuId,
                    'kategori_bahan' => 'BUMBU',
                    'batch_no'       => null,
                    'qty_standar'    => $bumbuQtyKg,
                    'qty_aktual'     => $bumbuQtyKg,
                    'satuan_cd'      => in_array($bumbuSatuan, ['GR', 'GRAM', 'G']) ? 'GRAM' : 'KG',
                    'harga_satuan'   => (float) ($bumbuBarang?->harga_beli_standar ?? 38499),
                ];
            }

            // C. Plastik Kemasan
            $plastikId = $request->input('plastik_barang_id');
            $plastikQty = (float) $request->input('plastik_qty', 0);
            $plastikSatuan = strtoupper($request->input('plastik_satuan', ''));
            if ($plastikId && $plastikQty > 0) {
                $plastikBarang = MstBarang::find($plastikId);
                $satuanPls = $plastikSatuan ?: ($plastikBarang?->satuanDasar?->satuan_cd ?? 'PCS');
                $items[] = [
                    'barang_id'      => (int) $plastikId,
                    'kategori_bahan' => 'KEMASAN',
                    'batch_no'       => null,
                    'qty_standar'    => $plastikQty,
                    'qty_aktual'     => $plastikQty,
                    'satuan_cd'      => $satuanPls,
                    'harga_satuan'   => (float) ($plastikBarang?->harga_beli_standar ?? 364830),
                ];
            }

            // D. Karton Box: Puteran & Baru
            $puteranQty = (float) $request->input('karton_puteran_qty', 0);
            $kartonPuteranBarangId = (int) ($request->input('karton_puteran_barang_id') ?: 82);
            $puteranSatuan = strtoupper($request->input('karton_puteran_satuan', 'LEMBAR'));
            if ($puteranQty > 0) {
                $items[] = [
                    'barang_id'      => $kartonPuteranBarangId,
                    'kategori_bahan' => 'KEMASAN',
                    'batch_no'       => null,
                    'qty_standar'    => $puteranQty,
                    'qty_aktual'     => $puteranQty,
                    'satuan_cd'      => $puteranSatuan,
                    'harga_satuan'   => 1748.20,
                ];
            }

            $baruQty = (float) $request->input('karton_baru_qty', 0);
            $kartonBaruBarangId = (int) ($request->input('karton_baru_barang_id') ?: 29);
            $baruSatuan = strtoupper($request->input('karton_baru_satuan', 'LEMBAR'));
            if ($baruQty > 0) {
                $kartonBaruObj = MstBarang::find($kartonBaruBarangId);
                $hBeli = (float) ($kartonBaruObj?->harga_beli_standar ?? 8741);
                $items[] = [
                    'barang_id'      => $kartonBaruBarangId,
                    'kategori_bahan' => 'KEMASAN',
                    'batch_no'       => null,
                    'qty_standar'    => $baruQty,
                    'qty_aktual'     => $baruQty,
                    'satuan_cd'      => $baruSatuan,
                    'harga_satuan'   => round($hBeli / 5, 2),
                ];
            }

            // Fallback jika karton diinput single
            if ($puteranQty <= 0 && $baruQty <= 0) {
                $kId = $request->input('karton_barang_id');
                $kQty = (float) ($request->input('karton_qty') ?: $request->input('hasil_karton_qty', 0));
                if ($kId && $kQty > 0) {
                    $kBarang = MstBarang::find($kId);
                    $items[] = [
                        'barang_id'      => (int) $kId,
                        'kategori_bahan' => 'KEMASAN',
                        'batch_no'       => null,
                        'qty_standar'    => $kQty,
                        'qty_aktual'     => $kQty,
                        'satuan_cd'      => 'LEMBAR',
                        'harga_satuan'   => (float) ($kBarang?->harga_beli_standar ?? 1748.20),
                    ];
                }
            }

            // E. Lakban Segel Dus
            $lakbanId = $request->input('lakban_barang_id');
            $lakbanQty = (float) ($request->input('lakban_qty') ?: $request->input('lakban_roll_qty', 0));
            $lakbanSatuan = strtoupper($request->input('lakban_satuan', ''));
            if ($lakbanId && $lakbanQty > 0) {
                $lakbanBarang = MstBarang::find($lakbanId);
                $satuan = $lakbanSatuan ?: strtoupper($lakbanBarang?->satuanDasar?->satuan_cd ?? 'ROLL');
                $hLakban = (float) ($lakbanBarang?->harga_beli_standar ?? (in_array($satuan, ['GR', 'GRAM', 'G']) ? 36.70 : 45880));
                $items[] = [
                    'barang_id'      => (int) $lakbanId,
                    'kategori_bahan' => 'KEMASAN',
                    'batch_no'       => null,
                    'qty_standar'    => $lakbanQty,
                    'qty_aktual'     => $lakbanQty,
                    'satuan_cd'      => $satuan,
                    'harga_satuan'   => $hLakban,
                ];
            }

            // F. Expired Label
            $expId = $request->input('expired_barang_id');
            $expQty = (float) $request->input('expired_qty', 0);
            $expSatuan = strtoupper($request->input('expired_satuan', ''));
            if ($expId && $expQty > 0) {
                $expBarang = MstBarang::find($expId);
                $items[] = [
                    'barang_id'      => (int) $expId,
                    'kategori_bahan' => 'KEMASAN',
                    'batch_no'       => null,
                    'qty_standar'    => $expQty,
                    'qty_aktual'     => $expQty,
                    'satuan_cd'      => $expSatuan ?: ($expBarang?->satuanDasar?->satuan_cd ?? 'LEMBAR'),
                    'harga_satuan'   => (float) ($expBarang?->harga_beli_standar ?? 250),
                ];
            }

            // G. Kemasan Tambahan Dinamis
            $kemasanTambahan = $request->input('kemasan_tambahan', []);
            if (is_array($kemasanTambahan)) {
                foreach ($kemasanTambahan as $kt) {
                    $ktId = !empty($kt['barang_id']) ? (int) $kt['barang_id'] : null;
                    $ktQty = !empty($kt['qty']) ? (float) $kt['qty'] : 0;
                    $ktSatuan = !empty($kt['satuan']) ? strtoupper($kt['satuan']) : null;
                    if ($ktId && $ktQty > 0) {
                        $ktBarang = MstBarang::find($ktId);
                        $items[] = [
                            'barang_id'      => $ktId,
                            'kategori_bahan' => 'KEMASAN',
                            'batch_no'       => null,
                            'qty_standar'    => $ktQty,
                            'qty_aktual'     => $ktQty,
                            'satuan_cd'      => $ktSatuan ?: ($ktBarang?->satuanDasar?->satuan_cd ?? 'PCS'),
                            'harga_satuan'   => (float) ($ktBarang?->harga_beli_standar ?? 0),
                        ];
                    }
                }
            }

            $request->merge(['items' => $items]);
        }

        // 4. Validasi Data
        $validated = $request->validate([
            'pack_tgl'                 => 'required|date',
            'shift'                    => 'nullable|string|in:A,B,C',
            'operator_nama'            => 'nullable|string|max:150',
            'karyawan_id'              => 'nullable|exists:mst_karyawan,karyawan_id',
            'jumlah_personil'          => 'nullable|integer|min:1',
            'jumlah_mesin'             => 'nullable|integer|min:1',
            'barang_jadi_id'           => 'required|exists:mst_barang,barang_id',
            'gudang_id'                => 'required|exists:mst_gudang,gudang_id',
            'bom_id'                   => 'nullable|exists:mst_bom_hdr,bom_id',
            'produksi_id'              => 'nullable|exists:dat_produksi_hdr,produksi_id',
            'pakai_id'                 => 'nullable|exists:dat_pakai_hdr,pakai_id',
            'target_karton_qty'        => 'nullable|numeric|min:0.01',
            'hasil_karton_qty'         => 'required|numeric|min:0.01',
            'hasil_pack_qty'           => 'nullable|numeric|min:0',
            'satuan_hasil'             => 'nullable|string|max:20',
            'isi_per_karton_pcs'       => 'nullable|numeric|min:1',
            'gramatur_gr'              => 'nullable|numeric|min:0',
            'batch_no'                 => 'nullable|string|max:100',
            'expired_tgl'              => 'nullable|date',
            'lakban_atas_gr'           => 'nullable|numeric|min:0',
            'lakban_bawah_gr'          => 'nullable|numeric|min:0',
            'lakban_jumlah_gr'         => 'nullable|numeric|min:0',
            'lakban_nilai'             => 'nullable|numeric|min:0',
            'karton_rusak_lembar'      => 'nullable|numeric|min:0',
            'plastik_rusak_pcs'        => 'nullable|numeric|min:0',
            'lakban_rusak_m'           => 'nullable|numeric|min:0',
            'total_biaya_tenaga_kerja' => 'nullable|numeric|min:0',
            'total_biaya_lain'         => 'nullable|numeric|min:0',
            'catatan_txt'              => 'nullable|string|max:1000',
            'catatan'                  => 'nullable|string|max:1000',
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
        if (empty($validated['catatan_txt']) && !empty($validated['catatan'])) {
            $validated['catatan_txt'] = $validated['catatan'];
        }

        $kartonRusak = (float) $request->input('karton_rusak_lembar', 0);
        $plastikRusak = (float) $request->input('plastik_rusak_pcs', 0);
        $lakbanRusak = (float) $request->input('lakban_rusak_m', 0);

        if ($kartonRusak > 0 || $plastikRusak > 0 || $lakbanRusak > 0) {
            $scrapParts = [];
            if ($kartonRusak > 0) $scrapParts[] = "Dus Afkir: {$kartonRusak} lbr";
            if ($plastikRusak > 0) $scrapParts[] = "Plastik Reject: {$plastikRusak} pcs";
            if ($lakbanRusak > 0) $scrapParts[] = "Lakban Kusut: {$lakbanRusak} m";
            $scrapNote = "[Scrap Kemasan IFM]: " . implode(', ', $scrapParts);

            if (!empty($validated['catatan_txt'])) {
                if (!str_contains($validated['catatan_txt'], 'Dus Afkir') && !str_contains($validated['catatan_txt'], 'Dus afkir')) {
                    $validated['catatan_txt'] .= " | " . $scrapNote;
                }
            } else {
                $validated['catatan_txt'] = $scrapNote;
            }
        }

        try {
            $header = $this->packagingService->storePackaging($validated, auth()->user()?->name);

            // Tautkan status laporan operator jika ditarik dari operator lapangan
            if ($request->filled('laporan_operator_id')) {
                \App\Models\Produksi\DatLaporanKemasOperator::where('laporan_id', (int) $request->input('laporan_operator_id'))
                    ->update([
                        'karton_rusak_lembar' => $kartonRusak,
                        'plastik_rusak_pcs'   => $plastikRusak,
                        'lakban_rusak_m'      => $lakbanRusak,
                    ]);

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
     * Menampilkan Surat Bukti Hasil Pengemasan & Serah Terima Fisik Gudang Barang Jadi (FG)
     * Bebas dari komponen biaya & nominal HPP untuk operasional gudang dan lantai kemas.
     */
    public function show(int $id): View
    {
        $header = DatPackagingHdr::with([
            'barangJadi.satuanDasar', 
            'gudang', 
            'details.barang.satuanDasar', 
            'bom',
            'produksi',
            'pemakaian.details.barang',
            'laporanKemasOperator',
        ])->findOrFail($id);

        // Ambil seluruh dokumen pengemasan pada tanggal yang sama untuk Tab Switcher Split View
        $sameDatePackaging = DatPackagingHdr::with(['barangJadi'])
            ->whereDate('pack_tgl', $header->pack_tgl)
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'VOID')
            ->orderBy('pack_id', 'asc')
            ->get();

        return view('produksi.packaging.show', compact('header', 'sameDatePackaging'));
    }

    /**
     * Menampilkan Lembar Bukti Hasil Pengemasan & Harga Pokok (HPP Kemas)
     * Khusus untuk bagian Finance / Akuntansi Biaya Manufaktur.
     */
    public function showHpp(int $id): View
    {
        $header = DatPackagingHdr::with([
            'barangJadi.satuanDasar', 
            'gudang', 
            'details.barang.satuanDasar', 
            'bom',
            'produksi',
            'pemakaian.details.barang',
            'adjustmentLogs',
            'laporanKemasOperator',
        ])->findOrFail($id);

        // Ambil seluruh dokumen pengemasan pada tanggal yang sama untuk Tab Switcher Split View
        $sameDatePackaging = DatPackagingHdr::with(['barangJadi'])
            ->whereDate('pack_tgl', $header->pack_tgl)
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'VOID')
            ->orderBy('pack_id', 'asc')
            ->get();

        return view('produksi.packaging.show-hpp', compact('header', 'sameDatePackaging'));
    }

    /**
     * Halaman Khusus Penyesuaian / Koreksi HPP Pengemasan Barang Jadi (Worksheet Akuntansi Biaya Kemas).
     */
    public function adjustPage(int $id): View
    {
        $header = DatPackagingHdr::with([
            'barangJadi.satuanDasar', 
            'gudang', 
            'details.barang.satuanDasar', 
            'bom',
            'produksi',
            'adjustmentLogs'
        ])->findOrFail($id);

        if ($header->isHppFinal()) {
            return redirect()->route('produksi.packaging.rekap')
                ->with('error', "Dokumen {$header->pack_no} telah berstatus HPP FINAL dan terkunci. Penyesuaian HPP hanya dapat dilakukan sebelum status final.");
        }

        // Ambil seluruh dokumen pengemasan pada tanggal yang sama untuk Tab Switcher Split View
        $sameDatePackaging = DatPackagingHdr::with(['barangJadi'])
            ->whereDate('pack_tgl', $header->pack_tgl)
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'VOID')
            ->orderBy('pack_id', 'asc')
            ->get();

        return view('produksi.packaging.adjust', compact('header', 'sameDatePackaging'));
    }

    /**
     * Penyesuaian / Koreksi HPP Pengemasan Dokumen Tertentu dengan Audit Trail Akuntansi.
     */
    public function adjustHpp(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'alasan_txt'                  => 'required|string|min:5',
            'no_referensi_txt'            => 'nullable|string|max:100',
            'total_biaya_tenaga_kerja'    => 'nullable|numeric|min:0',
            'total_biaya_lain'            => 'nullable|numeric|min:0',
            'lakban_nilai'                => 'nullable|numeric|min:0',
            'items'                       => 'nullable|array',
            'items.*.subtotal_nilai'      => 'nullable|numeric|min:0',
            'items.*.harga_satuan'        => 'nullable|numeric|min:0',
        ], [
            'alasan_txt.required'         => 'Alasan penyesuaian wajib diisi minimal 5 karakter untuk audit akuntansi.',
            'alasan_txt.min'              => 'Alasan penyesuaian wajib diisi minimal 5 karakter untuk audit akuntansi.',
        ]);

        try {
            $targetHeader = DatPackagingHdr::findOrFail($id);
            if ($targetHeader->isHppFinal()) {
                return redirect()->route('produksi.packaging.rekap')
                    ->with('error', "Dokumen {$targetHeader->pack_no} telah berstatus HPP FINAL dan terkunci. Penyesuaian HPP tidak dapat diproses.");
            }

            $user = Auth::user()?->name ?? (Auth::user()?->username ?? 'SYSTEM');
            $log = $this->packagingService->adjustPackagingHpp($id, $request->all(), $user);

            $msg = "✅ Berhasil melakukan adjustment HPP Pengemasan (No. BA: {$log->adj_no})! ";
            $msg .= "HPP baru: Rp " . number_format($log->hpp_karton_sesudah, 0, ',', '.') . "/Karton (Rp " . number_format($log->hpp_pcs_sesudah, 0, ',', '.') . "/Pcs). Persediaan FG di gudang telah disinkronkan.";

            return redirect()->route('produksi.packaging.show', $id)->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal melakukan adjustment HPP: ' . $e->getMessage());
        }
    }

    /**
     * Menarik data biaya WIP riil dan memfinalkan HPP Pengemasan Barang Jadi (FG).
     */
    public function finalizeHpp(Request $request, int $id): RedirectResponse
    {
        try {
            $user = Auth::user()?->name ?? 'System';
            $header = $this->packagingService->finalizeHppPackaging($id, $user);

            $msg = "✅ Dokumen {$header->pack_no} berhasil ditarik & difinalkan! ";
            $msg .= "HPP FG terbentuk: Rp " . number_format($header->hpp_per_karton, 0, ',', '.') . "/Karton (Rp " . number_format($header->hpp_per_pcs, 0, ',', '.') . "/Pcs). Nilai modal persediaan FG telah disinkronkan ke gudang.";

            return redirect()->back()->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
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

        $tarifs = $this->tarifKemasanService->getAllTarif();

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
            'barangJadiId',
            'tarifs'
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

    /**
     * Halaman Formulir Kalkulasi Lembar HPP Pengemasan (Tahap 2 Costing).
     */
    public function createHpp(Request $request): View
    {
        $selectedPackId = $request->filled('pack_id') ? (int) $request->input('pack_id') : null;
        $packagingList = $this->packagingService->getPackagingListForHpp($selectedPackId);

        return view('produksi.packaging.hpp.create', compact('packagingList', 'selectedPackId'));
    }

    /**
     * AJAX endpoint mengambil data rincian catatan hasil pengemasan untuk Lembar HPP.
     */
    public function getPackagingDataForHpp(int $id): JsonResponse
    {
        try {
            $data = $this->packagingService->getPackagingDataForHpp($id);
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memuat rincian dokumen pengemasan: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Simpan & finalkan lembar kalkulasi HPP Pengemasan Barang Jadi (FG).
     */
    public function storeHpp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pack_id'                  => 'required|exists:dat_packaging_hdr,pack_id',
            'total_biaya_wip'          => 'required|numeric|min:0',
            'total_biaya_kemasan'      => 'required|numeric|min:0',
            'jumlah_personil'          => 'required|integer|min:0',
            'total_biaya_tenaga_kerja' => 'required|numeric|min:0',
            'total_biaya_lain'         => 'nullable|numeric|min:0',
            'wip_items'                => 'nullable|array',
            'kemasan_items'            => 'nullable|array',
        ], [
            'pack_id.required'         => 'Dokumen pengemasan fisik wajib dipilih.',
            'total_biaya_tenaga_kerja' => 'Total biaya tenaga kerja kemas wajib diisi.',
        ]);

        try {
            $user = auth()->user()?->name ?? 'System';
            $header = $this->packagingService->storePackagingHppCalculation((int) $validated['pack_id'], $request->all(), $user);

            $msg = "✅ Lembar HPP Pengemasan [{$header->pack_no}] berhasil dikalkulasi & difinalkan! ";
            $msg .= "HPP Terbentuk: Rp " . number_format($header->hpp_per_karton, 0, ',', '.') . "/Karton (Rp " . number_format($header->hpp_per_pcs, 0, ',', '.') . "/Pcs). Persediaan FG di gudang telah disinkronkan.";

            return redirect()->route('produksi.packaging.rekap')->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memfinalkan HPP Pengemasan: ' . $e->getMessage());
        }
    }
}
