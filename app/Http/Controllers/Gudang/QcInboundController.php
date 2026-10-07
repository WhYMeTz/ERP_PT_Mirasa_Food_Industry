<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatQcInboundHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Services\Gudang\QcInboundService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QcInboundController extends Controller
{
    public function __construct(
        protected QcInboundService $qcService
    ) {}

    /**
     * Menampilkan daftar tiket inspeksi QC bahan masuk
     * Otomatis memisahkan Tampilan Lapangan Mobile vs Web Admin Gudang Desktop
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $allowedGudangIds = !$user->isSuperAdmin() ? $user->getAllowedGudangIds() : [];

        $filters = [
            'search'             => $request->input('search'),
            'kategori_barang'    => $request->input('kategori_barang'),
            'status_qc'          => $request->input('status_qc'),
            'status_uji_goreng'  => $request->input('status_uji_goreng'),
            'tahap_uji'          => $request->input('tahap_uji'),
            'supplier_id'        => $request->input('supplier_id'),
            'tgl_mulai'          => $request->input('tgl_mulai'),
            'tgl_selesai'        => $request->input('tgl_selesai'),
            'allowed_gudang_ids' => $allowedGudangIds,
        ];

        $inspeksiList = $this->qcService->getAllPaginated($filters, 15);
        $suppliers = MstSupplier::where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();

        $isMobileReq = $request->input('view') === 'mobile';

        // Jika mode mobile smartphone
        if ($isMobileReq) {
            return view('gudang.qc.index-mobile', compact('inspeksiList', 'suppliers', 'filters'));
        }

        // Metrik Statistik Operasional Gudang & QC Inbound (Khusus Web Admin Desktop, difilter hak akses perusahaan)
        $baseMetrics = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)
            ->when(!empty($allowedGudangIds), fn($q) => $q->whereIn('gudang_id', $allowedGudangIds))
            ->where(function ($q) {
                $q->whereNull('parent_qc_id')
                  ->orWhereDoesntHave('parentQc');
            });

        $countSiap = (clone $baseMetrics)->where('status_qc', 'SIAP_GUDANG')->count();
        $countMenungguUji2 = (clone $baseMetrics)->where('kategori_barang', 'SINGKONG')
            ->where('tahap_uji', 'PENGUJIAN_1')
            ->where('status_qc', 'SIAP_GUDANG')
            ->doesntHave('pengujian2List')->count();
        $countSelesai = (clone $baseMetrics)->where(function ($q) {
            $q->where('status_qc', 'DITERIMA_GUDANG')
              ->orWhereHas('pengujian2List', fn($pq) => $pq->where('status_qc', 'DITERIMA_GUDANG'));
        })->count();
        $countParsial = (clone $baseMetrics)->where(function ($q) {
            $q->whereIn('status_qc', ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL'])
              ->orWhere(function ($sq) {
                  $sq->where('status_qc', '!=', 'DITOLAK_TOTAL')
                     ->whereHas('details', fn($dq) => $dq->where('qty_reject', '>', 0));
              })
              ->orWhereHas('pengujian2List', function ($pq) {
                  $pq->whereIn('status_qc', ['DITOLAK_TOTAL', 'DITERIMA_PARSIAL', 'DITOLAK_PARSIAL'])
                     ->orWhereHas('details', fn($dq) => $dq->where('qty_reject', '>', 0));
              })
              ->orWhere(function ($sq) {
                  $sq->where('status_qc', 'DITOLAK_TOTAL')
                     ->whereHas('pengujian2List', fn($pq) => $pq->whereIn('status_qc', ['DITERIMA_GUDANG', 'SIAP_GUDANG']));
              });
        })->count();
        $countReject = (clone $baseMetrics)->where('status_qc', 'DITOLAK_TOTAL')
            ->where(function ($sq) {
                $sq->doesntHave('pengujian2List')
                   ->orWhereDoesntHave('pengujian2List', fn($pq) => $pq->whereIn('status_qc', ['DITERIMA_GUDANG', 'SIAP_GUDANG']));
            })->count();

        return view('gudang.qc.index', compact('inspeksiList', 'suppliers', 'filters', 'countSiap', 'countMenungguUji2', 'countSelesai', 'countParsial', 'countReject'));
    }

    /**
     * Halaman Antrean Tiket QC Inbound Khusus Admin Gudang (Dialihkan terpadu ke Riwayat QC Inbound)
     */
    public function gudangAntrean(Request $request): RedirectResponse
    {
        return redirect()->route('qc.inbound.index', array_merge(['status_qc' => 'SIAP_GUDANG'], $request->all()));
    }

    /**
     * Cetak Lembar Checklist Mutu HACCP Resmi (A4) - Dialihkan langsung ke Direct Print pada Indeks QC
     */
    public function gudangHaccpCetak(int $id): RedirectResponse
    {
        return redirect()->route('qc.inbound.index', ['direct_print' => $id]);
    }

    /**
     * Formulir Uji QC Masuk (Mobile-First / Google Form Style)
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $suppliers = MstSupplier::with('jenisSupplier')->where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();
        // Hanya tampilkan entitas perusahaan/gudang yang diizinkan untuk akun pengguna ini
        $gudangs = $user->getAllowedGudangList();
        
        // Ambil seluruh barang yang dibeli (Bahan Baku, Bahan Penolong, Plastik, Karton, dll; kecuali FG & WIP)
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->bahanProduksi()
            ->where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('barang_nm')
            ->get();

        // PO aktif yang siap diterima (APPROVED atau PARTIAL) yang sesuai dengan akses perusahaan akun
        $allowedGudangIds = $user->getAllowedGudangIds();
        $pos = DatPoHdr::with(['supplier', 'details.barang'])
            ->where('deleted_st', false)
            ->whereIn('status_cd', ['APPROVED', 'PARTIAL'])
            ->when(!$user->isSuperAdmin(), function($q) use ($allowedGudangIds) {
                $q->whereIn('gudang_id', $allowedGudangIds);
            })
            ->orderBy('po_tgl', 'desc')
            ->get();

        $selectedPoId = $request->input('po_id');
        $selectedPo = $selectedPoId ? $pos->firstWhere('po_id', $selectedPoId) : null;

        // Mendukung Pengujian II Singkong: Ambil data QC asal jika diteruskan via parent_qc_id
        $parentQc = null;
        if ($request->filled('parent_qc_id')) {
            $parentQc = \App\Models\Gudang\DatQcInboundHdr::with([
                'supplier',
                'gudang',
                'po',
                'details.barang',
                'terima.details'
            ])->where('deleted_st', false)->find($request->input('parent_qc_id'));
        }

        // Ambil daftar kedatangan Pengujian 1 yang belum selesai Pengujian 2 (maksimal 3 hari terakhir)
        $testedParentIds = DatQcInboundHdr::where('deleted_st', false)
            ->where('tahap_uji', 'PENGUJIAN_2')
            ->pluck('parent_qc_id')
            ->filter()
            ->unique()
            ->toArray();

        $pendingPengujian1 = DatQcInboundHdr::with(['supplier', 'po', 'details.barang'])
            ->where('deleted_st', false)
            ->where('kategori_barang', 'SINGKONG')
            ->where('tahap_uji', 'PENGUJIAN_1')
            ->whereIn('status_qc', ['SIAP_GUDANG', 'DITERIMA_GUDANG'])
            ->whereNotIn('qc_id', $testedParentIds)
            ->where('created_at', '>=', now()->subDays(3))
            ->when(!empty($allowedGudangIds), fn($q) => $q->whereIn('gudang_id', $allowedGudangIds))
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $initialKomoditas = $request->input('kategori_barang');
        if (!$initialKomoditas && $selectedPo) {
            $firstItemNm = strtoupper($selectedPo->details->first()?->barang?->barang_nm ?? '');
            if (str_contains($firstItemNm, 'MINYAK')) {
                $initialKomoditas = 'MINYAK';
            } elseif (str_contains($firstItemNm, 'PLASTIK') || str_contains($firstItemNm, 'KEMASAN') || str_contains($firstItemNm, 'OPP') || str_contains($firstItemNm, 'PP')) {
                $initialKomoditas = 'PLASTIK';
            } elseif (str_contains($firstItemNm, 'KARTON') || str_contains($firstItemNm, 'DUS') || str_contains($firstItemNm, 'BOX')) {
                $initialKomoditas = 'KARTON';
            } elseif (str_contains($firstItemNm, 'MSG') || str_contains($firstItemNm, 'MONOSODIUM')) {
                $initialKomoditas = 'MSG';
            } elseif (str_contains($firstItemNm, 'GARAM') || str_contains($firstItemNm, 'SALT')) {
                $initialKomoditas = 'GARAM';
            } elseif (str_contains($firstItemNm, 'PERENYAH') || str_contains($firstItemNm, 'BUMBU')) {
                $initialKomoditas = 'PERENYAH';
            } else {
                $initialKomoditas = 'SINGKONG';
            }
        }
        $initialKomoditas = $initialKomoditas ?: 'SINGKONG';

        $defaultTahap = $request->input('tahap') == 2 || $parentQc ? 'PENGUJIAN_2' : 'PENGUJIAN_1';

        // Hitung stok riil Singkong di gudang untuk Grade A dan Grade B
        $stokSingkongA = (float) DatStokBatch::where('deleted_st', false)
            ->where('sisa_qty', '>', 0)
            ->when(!empty($allowedGudangIds), fn($q) => $q->whereIn('gudang_id', $allowedGudangIds))
            ->whereHas('barang', fn($b) => $b->where('barang_nm', 'like', '%SINGKONG%')->orWhere('barang_cd', 'like', '%SK%'))
            ->where(function($q) {
                $q->whereIn('batch_no', function($sub) {
                    $sub->select('batch_no')->from('dat_terima_dtl')->where('deleted_st', false)->where('grade_cd', 'A');
                })->orWhereNotIn('batch_no', function($sub) {
                    $sub->select('batch_no')->from('dat_terima_dtl')->where('deleted_st', false)->where('grade_cd', 'B');
                });
            })
            ->sum('sisa_qty');

        $stokSingkongB = (float) DatStokBatch::where('deleted_st', false)
            ->where('sisa_qty', '>', 0)
            ->when(!empty($allowedGudangIds), fn($q) => $q->whereIn('gudang_id', $allowedGudangIds))
            ->whereHas('barang', fn($b) => $b->where('barang_nm', 'like', '%SINGKONG%')->orWhere('barang_cd', 'like', '%SK%'))
            ->whereIn('batch_no', function($sub) {
                $sub->select('batch_no')->from('dat_terima_dtl')->where('deleted_st', false)->where('grade_cd', 'B');
            })
            ->sum('sisa_qty');

        return view('gudang.qc.create', compact(
            'suppliers',
            'gudangs',
            'barangs',
            'pos',
            'selectedPo',
            'parentQc',
            'pendingPengujian1',
            'defaultTahap',
            'initialKomoditas',
            'stokSingkongA',
            'stokSingkongB'
        ));
    }

    /**
     * Menampilkan formulir input QC Pengujian II (Lanjutan Kedatangan Truk)
     * Dialihkan langsung ke form QC terpadu dengan tahap=2 (tidak ada batch gudang)
     */
    public function createPengujian2(Request $request): RedirectResponse
    {
        return redirect()->route('qc.inbound.create', array_merge($request->query(), ['tahap' => 2]));
    }

    /**
     * Menyimpan hasil uji QC Pengujian II (Lantai Produksi)
     */
    public function storePengujian2(Request $request): RedirectResponse
    {
        $request->validate([
            'gudang_id'          => 'required|exists:mst_gudang,gudang_id',
            'fryer_rasa'         => 'required|in:TIDAK_PAHIT,PAHIT',
            'jumlah_sample_kg'   => 'nullable|numeric|min:0.1',
        ], [
            'gudang_id.required'  => 'Silakan tentukan entitas perusahaan/gudang.',
            'fryer_rasa.required' => 'Hasil uji rasa singkong wajib ditentukan.',
        ]);

        try {
            $user = Auth::user();
            $qc = $this->qcService->storePengujian2($request->all(), $user);

            $redirectParams = ['inbound' => $qc->qc_id];
            if ($request->input('view') === 'mobile' || ($user?->isQc() && !$user?->isSuperAdmin() && !$user?->isGudang() && $request->input('view') !== 'desktop')) {
                $redirectParams['view'] = 'mobile';
            }

            $pesan = ($qc->status_qc === 'DITOLAK_TOTAL')
                ? "⚠️ PENGUJIAN II: Rasa PAHIT terdeteksi! Tiket {$qc->qc_no} berstatus TOLAK TOTAL (Hentikan Batch Produksi) dan telah diteruskan ke Admin Gudang."
                : "✅ PENGUJIAN II: Tiket {$qc->qc_no} berhasil dicatat (Rasa Gurih / Lolos). Produksi batch dapat dilanjutkan.";

            return redirect()->route('qc.inbound.show', $redirectParams)->with('success', $pesan);
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat Pengujian II: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan hasil uji inspeksi QC
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id'               => 'required|exists:mst_supplier,supplier_id',
            'gudang_id'                 => 'required|exists:mst_gudang,gudang_id',
            'items'                     => 'required|array|min:1',
            'items.*.barang_id'         => 'required|exists:mst_barang,barang_id',
            'items.*.qty_timbang_gross' => 'nullable|numeric|min:0',
            'items.*.kadar_air_persen'  => 'nullable|numeric|min:0|max:100',
            'items.*.refraksi_persen'   => 'nullable|numeric|min:0|max:100',
            'items.*.qty_reject'        => 'nullable|numeric|min:0',
        ], [
            'supplier_id.required'       => 'Silakan pilih mitra supplier pengirim.',
            'gudang_id.required'         => 'Silakan tentukan gudang bongkar muat.',
            'items.required'             => 'Minimal harus ada 1 komoditas yang diuji.',
            'items.*.barang_id.required' => 'Komoditas barang harus dipilih.',
        ]);

        try {
            $user = Auth::user();
            $qc = $this->qcService->store($request->all(), $user);

            $redirectParams = ['inbound' => $qc->qc_id];
            if ($request->input('view') === 'mobile' || ($user?->isQc() && !$user?->isSuperAdmin() && !$user?->isGudang() && $request->input('view') !== 'desktop')) {
                $redirectParams['view'] = 'mobile';
            }

            return redirect()->route('qc.inbound.show', $redirectParams)
                ->with('success', "Inspeksi QC {$qc->qc_no} berhasil dicatat & diteruskan ke antrean Gudang.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat inspeksi QC: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan dokumen lembar hasil uji QC
     * Desktop: Dokumen Lembar Cetak HACCP Resmi PT Mirasa
     * Mobile: Kartu Ringkasan Mobile untuk Lapangan
     */
    public function show(Request $request, int $id): View
    {
        $qc = \App\Models\Gudang\DatQcInboundHdr::with([
            'supplier',
            'gudang',
            'po',
            'details.barang.satuanDasar',
            'details.poDetail',
            'terima',
            'pengujian2List',
        ])->where('deleted_st', false)->findOrFail($id);

        $user = Auth::user();
        $isMobileReq = $request->input('view') === 'mobile' || ($user?->isQc() && !$user?->isSuperAdmin() && !$user?->isGudang() && $request->input('view') !== 'desktop');

        // Jika mode mobile smartphone
        if ($isMobileReq) {
            return view('gudang.qc.show-mobile', compact('qc'));
        }

        // Web Admin Desktop: Lembar Dokumen HACCP Format Cetak Resmi
        return view('gudang.qc.show', compact('qc'));
    }

    /**
     * Berita Acara Penolakan Bahan Baku (Pengujian 1 Ditolak Total di Gerbang)
     * Ditampilkan untuk dicetak oleh Admin Gudang atau QC melalui Web Desktop
     */
    public function beritaAcara(int $id): View
    {
        $qc = \App\Models\Gudang\DatQcInboundHdr::with([
            'supplier',
            'gudang',
            'po',
            'details.barang.satuanDasar',
        ])->where('deleted_st', false)->findOrFail($id);

        return view('gudang.qc.berita_acara', compact('qc'));
    }

    /**
     * Formulir Koreksi / Edit Tiket QC Inbound
     * Mendukung tampilan mobile edit biasa untuk smartphone QC dan formulir HACCP desktop
     */
    public function edit(Request $request, int $id): View|RedirectResponse
    {
        $qc = \App\Models\Gudang\DatQcInboundHdr::with([
            'supplier',
            'gudang',
            'po',
            'details.barang.satuanDasar',
            'details.poDetail',
            'terima',
            'parentQc.details',
            'parentQc.supplier',
        ])->where('deleted_st', false)->findOrFail($id);

        $user = Auth::user();
        if ($qc->terima && !$user->isSuperAdmin() && !$user->isGudang()) {
            return redirect()->route('qc.inbound.show', $id)
                ->with('error', "Tiket QC #{$qc->qc_no} sudah diproses ke Penerimaan Barang (GRN #{$qc->terima->terima_no}). Hanya Admin Gudang atau Super Administrator yang berhak mengedit tiket yang sudah ditarik ke gudang.");
        }

        $suppliers = MstSupplier::with('jenisSupplier')->where('deleted_st', false)->where('active_st', true)->orderBy('supplier_nm')->get();
        $gudangs = $user->getAllowedGudangList();
        if ($qc->gudang && !$gudangs->contains('gudang_id', $qc->gudang_id)) {
            $gudangs->push($qc->gudang);
        }
        
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->bahanProduksi()
            ->where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('barang_nm')
            ->get();

        $allowedGudangIds = $user->getAllowedGudangIds();
        $pos = DatPoHdr::with(['supplier', 'details.barang'])
            ->where('deleted_st', false)
            ->where(function($q) use ($qc) {
                $q->whereIn('status_cd', ['APPROVED', 'PARTIAL']);
                if ($qc->po_id) {
                    $q->orWhere('po_id', $qc->po_id);
                }
            })
            ->when(!$user->isSuperAdmin(), function($q) use ($allowedGudangIds, $qc) {
                $q->where(function($sub) use ($allowedGudangIds, $qc) {
                    $sub->whereIn('gudang_id', $allowedGudangIds);
                    if ($qc->po_id) {
                        $sub->orWhere('po_id', $qc->po_id);
                    }
                });
            })
            ->orderBy('po_tgl', 'desc')
            ->get();

        $isMobileReq = $request->input('view') === 'mobile' || ($user?->isQc() && !$user?->isSuperAdmin() && !$user?->isGudang() && $request->input('view') !== 'desktop');

        // Jika mode mobile smartphone
        if ($isMobileReq) {
            return view('gudang.qc.edit-mobile', compact('qc', 'suppliers', 'gudangs', 'barangs', 'pos'));
        }

        // Web Admin Desktop: Formulir Dokumen HACCP Interaktif (Semua Kolom Bisa Diedit)
        return view('gudang.qc.edit', compact('qc', 'suppliers', 'gudangs', 'barangs', 'pos'));
    }

    /**
     * Memperbarui data tiket inspeksi QC (Koreksi)
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'supplier_id'               => 'required|exists:mst_supplier,supplier_id',
            'gudang_id'                 => 'required|exists:mst_gudang,gudang_id',
            'items'                     => 'required|array|min:1',
            'items.*.barang_id'         => 'required|exists:mst_barang,barang_id',
            'items.*.qty_timbang_gross' => 'nullable|numeric|min:0',
            'items.*.kadar_air_persen'  => 'nullable|numeric|min:0|max:100',
            'items.*.refraksi_persen'   => 'nullable|numeric|min:0|max:100',
            'items.*.qty_reject'        => 'nullable|numeric|min:0',
        ], [
            'supplier_id.required'       => 'Silakan pilih mitra supplier pengirim.',
            'gudang_id.required'         => 'Silakan tentukan gudang bongkar muat.',
            'items.required'             => 'Minimal harus ada 1 komoditas yang diuji.',
            'items.*.barang_id.required' => 'Komoditas barang harus dipilih.',
        ]);

        try {
            $qc = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)->findOrFail($id);
            $user = Auth::user();
            $updatedQc = $this->qcService->update($qc, $request->all(), $user);

            $msg = "Tiket QC {$updatedQc->qc_no} berhasil diperbarui.";
            if (($user->isSuperAdmin() || $user->isGudang()) && $updatedQc->terima) {
                $msg .= " Sinkronisasi otomatis ke Penerimaan Barang (#{$updatedQc->terima->terima_no}) & Batch Stok selesai tanpa unpost.";
            }

            // Jika user klik tombol "Simpan & Langsung Cetak A4"
            if ($request->input('and_print') == '1') {
                return redirect()->route('qc.inbound.index', ['direct_print' => $updatedQc->qc_id])
                    ->with('success', $msg);
            }

            if ($request->input('view') === 'mobile' || ($user?->isQc() && !$user?->isSuperAdmin() && !$user?->isGudang())) {
                return redirect()->route('qc.inbound.show', ['inbound' => $updatedQc->qc_id, 'view' => 'mobile'])
                    ->with('success', $msg);
            }

            // Web Admin Desktop: Jika user memilih "Simpan & Kembali ke Riwayat"
            if ($request->input('action') === 'save_and_close') {
                return redirect()->route('qc.inbound.index')
                    ->with('success', $msg);
            }

            // Default Web Admin Desktop: Tetap di halaman edit agar user bisa langsung meninjau data yang baru saja diperbarui
            return redirect()->route('qc.inbound.edit', $updatedQc->qc_id)
                ->with('success', "Perubahan data Dokumen QC {$updatedQc->qc_no} berhasil disimpan dan diperbarui.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui tiket QC: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus tiket QC
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $qc = \App\Models\Gudang\DatQcInboundHdr::where('deleted_st', false)->findOrFail($id);
            $user = Auth::user();
            $this->qcService->destroy($qc, $user);

            $isMobile = request('view') === 'mobile';
            $redirectParams = $isMobile ? ['view' => 'mobile'] : [];

            return redirect()->route('qc.inbound.index', $redirectParams)
                ->with('success', "Tiket QC #{$qc->qc_no} berhasil dibatalkan / dihapus.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus tiket QC: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui hasil uji goreng (Pengujian II) susulan
     */
    public function updateUjiGoreng(Request $request, int $id): RedirectResponse
    {
        try {
            $user = Auth::user();
            $this->qcService->updateUjiGoreng($id, $request->all(), $user);

            return redirect()->route('qc.inbound.show', $id)
                ->with('success', 'Hasil uji goreng lab (Pengujian II) berhasil disimpan & diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui hasil uji goreng: ' . $e->getMessage());
        }
    }

    /**
     * API AJAX untuk Admin Gudang: Mengambil daftar tiket QC status SIAP_GUDANG
     */
    public function getSiapGudang(): JsonResponse
    {
        $tickets = $this->qcService->getSiapGudangTickets();
        return response()->json([
            'status'  => 'success',
            'tickets' => $tickets,
        ]);
    }

    /**
     * API AJAX untuk Admin Gudang: Mengambil detail item tiket QC untuk ditarik ke form terima
     */
    public function getTicketData(int $id): JsonResponse
    {
        try {
            $data = $this->qcService->getTicketData($id);
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tiket QC tidak ditemukan: ' . $e->getMessage(),
            ], 404);
        }
    }
}
