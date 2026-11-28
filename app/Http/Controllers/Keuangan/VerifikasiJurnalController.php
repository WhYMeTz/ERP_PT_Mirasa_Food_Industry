<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\DatKasKeluarHdr;
use App\Models\Keuangan\DatKasMasukHdr;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Services\Keuangan\KasKeluarService;
use App\Services\Keuangan\KasMasukService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiJurnalController extends Controller
{
    public function __construct(
        protected KasKeluarService $kasKeluarService,
        protected KasMasukService $kasMasukService
    ) {}

    /**
     * Tampilkan antrean transaksi Kas Keluar (BKK) dan Kas Masuk (BKM) yang berstatus DRAFT dan belum diterbitkan Jurnal Umum.
     * Dilengkapi 2 Tab Segmented: BKK (Kas Keluar) dan BKM (Kas Masuk).
     */
    public function index(Request $request): View
    {
        $activeTab = in_array($request->input('tab'), ['bkk', 'bkm']) ? $request->input('tab') : 'bkk';

        $filters = [
            'tab'         => $activeTab,
            'search'      => trim((string) $request->input('search')),
            'start_date'  => $request->input('start_date'),
            'end_date'    => $request->input('end_date'),
            'kas_bank_id' => $request->input('kas_bank_id'),
            'kategori'    => $request->input('kategori'),
        ];

        // 1. Hitung metrik ringkasan antrean Kas Keluar (BKK)
        $basePendingBkkQuery = DatKasKeluarHdr::where('deleted_st', false)
            ->where('status_cd', 'DRAFT')
            ->whereNull('jurnal_id');

        $summaryMetricsBkk = [
            'total_dokumen'  => (clone $basePendingBkkQuery)->count(),
            'total_nominal'  => (float) (clone $basePendingBkkQuery)->sum('total_keluar'),
            'total_kas_bank' => (clone $basePendingBkkQuery)->distinct('kas_bank_id')->count('kas_bank_id'),
        ];

        // 2. Hitung metrik ringkasan antrean Kas Masuk (BKM)
        $basePendingBkmQuery = DatKasMasukHdr::where('deleted_st', false)
            ->where('status_cd', 'DRAFT')
            ->whereNull('jurnal_id');

        $summaryMetricsBkm = [
            'total_dokumen'  => (clone $basePendingBkmQuery)->count(),
            'total_nominal'  => (float) (clone $basePendingBkmQuery)->sum('total_terima'),
            'total_kas_bank' => (clone $basePendingBkmQuery)->distinct('kas_bank_id')->count('kas_bank_id'),
        ];

        // Metrik aktif untuk view umum
        $summaryMetrics = ($activeTab === 'bkm') ? $summaryMetricsBkm : $summaryMetricsBkk;

        // 3. Query data BKK dengan filter pencarian
        $queryBkk = DatKasKeluarHdr::with(['kasBank', 'details', 'pemohon', 'kategoriPengeluaran.coaDefault'])
            ->where('deleted_st', false)
            ->where('status_cd', 'DRAFT')
            ->whereNull('jurnal_id');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $queryBkk->where(function ($q) use ($search) {
                $q->where('bkk_no', 'like', "%{$search}%")
                  ->orWhere('penerima_nama', 'like', "%{$search}%")
                  ->orWhere('departemen_pemohon', 'like', "%{$search}%")
                  ->orWhereHas('pemohon', function ($qp) use ($search) {
                      $qp->where('karyawan_nm', 'like', "%{$search}%");
                  })
                  ->orWhereHas('details', function ($qd) use ($search) {
                      $qd->where('uraian', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['start_date'])) {
            $queryBkk->whereDate('tgl_keluar', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $queryBkk->whereDate('tgl_keluar', '<=', $filters['end_date']);
        }

        if (!empty($filters['kas_bank_id'])) {
            $queryBkk->where('kas_bank_id', $filters['kas_bank_id']);
        }

        if (!empty($filters['kategori'])) {
            $queryBkk->where('kategori_pengeluaran', $filters['kategori']);
        }

        $bkkList = $queryBkk->orderBy('tgl_keluar', 'asc')
            ->orderBy('bkk_id', 'asc')
            ->paginate(20, ['*'], 'page_bkk')
            ->withQueryString();

        // 4. Query data BKM dengan filter pencarian
        $queryBkm = DatKasMasukHdr::with(['kasBank', 'details', 'customer'])
            ->where('deleted_st', false)
            ->where('status_cd', 'DRAFT')
            ->whereNull('jurnal_id');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $queryBkm->where(function ($q) use ($search) {
                $q->where('bkm_no', 'like', "%{$search}%")
                  ->orWhere('penyetor_nama', 'like', "%{$search}%")
                  ->orWhere('penerima_kasir', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($qc) use ($search) {
                      $qc->where('customer_nm', 'like', "%{$search}%");
                  })
                  ->orWhereHas('details', function ($qd) use ($search) {
                      $qd->where('uraian', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['start_date'])) {
            $queryBkm->whereDate('tgl_terima', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $queryBkm->whereDate('tgl_terima', '<=', $filters['end_date']);
        }

        if (!empty($filters['kas_bank_id'])) {
            $queryBkm->where('kas_bank_id', $filters['kas_bank_id']);
        }

        $bkmList = $queryBkm->orderBy('tgl_terima', 'asc')
            ->orderBy('bkm_id', 'asc')
            ->paginate(20, ['*'], 'page_bkm')
            ->withQueryString();

        // 5. Daftar Akun COA Beban (untuk Tab BKK: Kategori Beban atau Awalan 5xxx dan 6xxx)
        $expenseCoas = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->where(function ($q) {
                $q->where('kategori_akun', 'BEBAN')
                  ->orWhere('kode_akun', 'like', '5%')
                  ->orWhere('kode_akun', 'like', '6%');
            })
            ->orderBy('kode_akun', 'asc')
            ->get();

        // 6. Daftar Akun COA Kredit (untuk Tab BKM: Pendapatan 4xxx, Modal 3xxx, Kewajiban 2xxx, Pendapatan Lain 7xxx/8xxx)
        $creditCoas = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->where(function ($q) {
                $q->whereIn('kategori_akun', ['PENDAPATAN', 'MODAL', 'PASIVA'])
                  ->orWhere('kode_akun', 'like', '2%')
                  ->orWhere('kode_akun', 'like', '3%')
                  ->orWhere('kode_akun', 'like', '4%')
                  ->orWhere('kode_akun', 'like', '7%')
                  ->orWhere('kode_akun', 'like', '8%')
                  ->orWhere('kode_akun', 'like', '6114%');
            })
            ->orderBy('kode_akun', 'asc')
            ->get();

        // 7. Daftar Kas & Bank aktif untuk filter
        $kasBanks = MstKasBank::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('nama_akun', 'asc')
            ->get();

        $kategoriList = [
            'BIAYA_OPERASIONAL'     => 'Biaya Operasional Umum',
            'LISTRIK_AIR'           => 'Listrik & Air',
            'BBM_TRANSPORT'         => 'BBM / Solar / Transport',
            'PEMELIHARAAN_MESIN'    => 'Pemeliharaan & Servis Mesin',
            'PERLENGKAPAN_ATK'      => 'Perlengkapan & ATK',
            'KONSUMSI_RUMAH_TANGGA' => 'Konsumsi & Rumah Tangga',
            'GAJI_UPAH_HARIAN'      => 'Upah / Gaji Harian',
            'LAINNYA'               => 'Lainnya',
        ];

        return view('keuangan.verifikasi_jurnal.index', compact(
            'activeTab',
            'bkkList',
            'bkmList',
            'summaryMetrics',
            'summaryMetricsBkk',
            'summaryMetricsBkm',
            'expenseCoas',
            'creditCoas',
            'kasBanks',
            'kategoriList',
            'filters'
        ));
    }

    /**
     * Verifikasi BKK Operasional dari Kasir dan Terbitkan Jurnal Umum oleh Bagian Akuntansi.
     */
    public function verifikasiBkk(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'coa_id'     => 'required|exists:mst_coa,coa_id',
            'tgl_jurnal' => 'nullable|date',
        ], [
            'coa_id.required' => 'Pilih Pos Akun Beban (COA Debit) terlebih dahulu untuk menerbitkan Jurnal Umum.',
            'coa_id.exists'   => 'Akun COA beban yang dipilih tidak valid di sistem.',
            'tgl_jurnal.date' => 'Format tanggal jurnal tidak valid.',
        ]);

        try {
            $coaId = (int) $request->input('coa_id');
            $tglJurnal = $request->input('tgl_jurnal');

            $bkk = $this->kasKeluarService->verifikasiDanTerbitkanJurnal(
                $id,
                $coaId,
                $tglJurnal,
                Auth::user()
            );

            $message = "Pengeluaran Kas {$bkk->bkk_no} berhasil diverifikasi dan Jurnal Umum telah diterbitkan!";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'bkk_no'  => $bkk->bkk_no,
                    'bkk_id'  => $bkk->bkk_id,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memverifikasi dan menerbitkan jurnal BKK: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal memverifikasi dan menerbitkan jurnal BKK: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi BKM Penerimaan Kas dari Kasir dan Terbitkan Jurnal Umum oleh Bagian Akuntansi.
     */
    public function verifikasiBkm(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'coa_id'            => 'required|exists:mst_coa,coa_id',
            'tgl_jurnal'        => 'nullable|date',
            'keterangan_jurnal' => 'nullable|string|max:500',
        ], [
            'coa_id.required'   => 'Pilih Pos Akun Kredit (COA Kredit) terlebih dahulu untuk menerbitkan Jurnal Umum.',
            'coa_id.exists'     => 'Akun COA kredit yang dipilih tidak valid di sistem.',
            'tgl_jurnal.date'   => 'Format tanggal jurnal tidak valid.',
        ]);

        try {
            $coaId = (int) $request->input('coa_id');
            $tglJurnal = $request->input('tgl_jurnal');
            $keteranganJurnal = $request->input('keterangan_jurnal');

            $bkm = $this->kasMasukService->verifikasiDanTerbitkanJurnal(
                $id,
                $coaId,
                $tglJurnal,
                Auth::user(),
                $keteranganJurnal
            );

            $message = "Penerimaan Kas {$bkm->bkm_no} berhasil diverifikasi dan Jurnal Umum telah diterbitkan!";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'bkm_no'  => $bkm->bkm_no,
                    'bkm_id'  => $bkm->bkm_id,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memverifikasi dan menerbitkan jurnal BKM: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal memverifikasi dan menerbitkan jurnal BKM: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi Masal Bukti Kas Keluar (BKK) dan Terbitkan Jurnal Umum Sekaligus.
     */
    public function batchProses(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.bkk_id'     => 'required|integer|exists:dat_kas_keluar_hdr,bkk_id',
            'items.*.coa_id'     => 'required|integer|exists:mst_coa,coa_id',
            'items.*.tgl_jurnal' => 'nullable|date',
        ], [
            'items.required'          => 'Pilih minimal satu transaksi Kas Keluar (BKK) untuk diverifikasi.',
            'items.min'               => 'Pilih minimal satu transaksi Kas Keluar (BKK) untuk diverifikasi.',
            'items.*.bkk_id.required' => 'ID Bukti Kas Keluar tidak valid.',
            'items.*.coa_id.required' => 'Pos Akun Beban (COA Debit) wajib ditentukan untuk seluruh BKK yang dipilih.',
            'items.*.coa_id.exists'   => 'Akun COA beban yang dipilih tidak valid di sistem.',
            'items.*.tgl_jurnal.date' => 'Format tanggal jurnal tidak valid.',
        ]);

        $items = $validated['items'];
        $user = Auth::user();
        $processedCount = 0;

        try {
            DB::transaction(function () use ($items, $user, &$processedCount) {
                foreach ($items as $item) {
                    $bkkId = (int) $item['bkk_id'];
                    $coaId = (int) $item['coa_id'];
                    $tglJurnal = !empty($item['tgl_jurnal']) ? $item['tgl_jurnal'] : null;

                    $this->kasKeluarService->verifikasiDanTerbitkanJurnal(
                        $bkkId,
                        $coaId,
                        $tglJurnal,
                        $user
                    );
                    $processedCount++;
                }
            });

            $message = "Berhasil memverifikasi {$processedCount} transaksi Kas Keluar dan menerbitkan Jurnal Umum!";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count'   => $processedCount,
                ]);
            }

            return redirect()->route('keuangan.verifikasi_jurnal.index', ['tab' => 'bkk'])->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses verifikasi masal: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal memproses verifikasi masal: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi Masal Bukti Kas Masuk (BKM) dan Terbitkan Jurnal Umum Sekaligus.
     */
    public function batchProsesBkm(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.bkm_id'            => 'required|integer|exists:dat_kas_masuk_hdr,bkm_id',
            'items.*.coa_id'            => 'required|integer|exists:mst_coa,coa_id',
            'items.*.tgl_jurnal'        => 'nullable|date',
            'items.*.keterangan_jurnal' => 'nullable|string|max:500',
        ], [
            'items.required'          => 'Pilih minimal satu transaksi Kas Masuk (BKM) untuk diverifikasi.',
            'items.min'               => 'Pilih minimal satu transaksi Kas Masuk (BKM) untuk diverifikasi.',
            'items.*.bkm_id.required' => 'ID Bukti Kas Masuk tidak valid.',
            'items.*.coa_id.required' => 'Pos Akun Kredit (COA Kredit) wajib ditentukan untuk seluruh BKM yang dipilih.',
            'items.*.coa_id.exists'   => 'Akun COA kredit yang dipilih tidak valid di sistem.',
            'items.*.tgl_jurnal.date' => 'Format tanggal jurnal tidak valid.',
        ]);

        $items = $validated['items'];
        $user = Auth::user();
        $processedCount = 0;

        try {
            DB::transaction(function () use ($items, $user, &$processedCount) {
                foreach ($items as $item) {
                    $bkmId = (int) $item['bkm_id'];
                    $coaId = (int) $item['coa_id'];
                    $tglJurnal = !empty($item['tgl_jurnal']) ? $item['tgl_jurnal'] : null;
                    $keteranganJurnal = !empty($item['keterangan_jurnal']) ? $item['keterangan_jurnal'] : null;

                    $this->kasMasukService->verifikasiDanTerbitkanJurnal(
                        $bkmId,
                        $coaId,
                        $tglJurnal,
                        $user,
                        $keteranganJurnal
                    );
                    $processedCount++;
                }
            });

            $message = "Berhasil memverifikasi {$processedCount} transaksi Kas Masuk dan menerbitkan Jurnal Umum!";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count'   => $processedCount,
                ]);
            }

            return redirect()->route('keuangan.verifikasi_jurnal.index', ['tab' => 'bkm'])->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses verifikasi masal BKM: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal memproses verifikasi masal BKM: ' . $e->getMessage());
        }
    }
}
