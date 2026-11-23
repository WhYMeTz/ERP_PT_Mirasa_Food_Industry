<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\DatKasKeluarHdr;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Services\Keuangan\KasKeluarService;
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
        protected KasKeluarService $kasKeluarService
    ) {}

    /**
     * Tampilkan antrean transaksi Kas Keluar (BKK) yang berstatus DRAFT dan belum diterbitkan Jurnal Umum.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'      => trim((string) $request->input('search')),
            'start_date'  => $request->input('start_date'),
            'end_date'    => $request->input('end_date'),
            'kas_bank_id' => $request->input('kas_bank_id'),
            'kategori'    => $request->input('kategori'),
        ];

        // Query dasar antrean pending: status DRAFT, belum ada jurnal, dan tidak dihapus
        $basePendingQuery = DatKasKeluarHdr::where('deleted_st', false)
            ->where('status_cd', 'DRAFT')
            ->whereNull('jurnal_id');

        // Hitung metrik ringkasan antrean kas keluar
        $summaryMetrics = [
            'total_dokumen' => (clone $basePendingQuery)->count(),
            'total_nominal' => (float) (clone $basePendingQuery)->sum('total_keluar'),
            'total_kas_bank' => (clone $basePendingQuery)->distinct('kas_bank_id')->count('kas_bank_id'),
        ];

        // Query dengan relasi dan filter pencarian
        $query = DatKasKeluarHdr::with(['kasBank', 'details', 'pemohon', 'kategoriPengeluaran.coaDefault'])
            ->where('deleted_st', false)
            ->where('status_cd', 'DRAFT')
            ->whereNull('jurnal_id');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
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
            $query->whereDate('tgl_keluar', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('tgl_keluar', '<=', $filters['end_date']);
        }

        if (!empty($filters['kas_bank_id'])) {
            $query->where('kas_bank_id', $filters['kas_bank_id']);
        }

        if (!empty($filters['kategori'])) {
            $query->where('kategori_pengeluaran', $filters['kategori']);
        }

        $bkkList = $query->orderBy('tgl_keluar', 'asc')
            ->orderBy('bkk_id', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Daftar Akun COA Beban (Kategori Beban atau Awalan 5xxx dan 6xxx)
        $expenseCoas = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->where(function ($q) {
                $q->where('kategori_akun', 'BEBAN')
                  ->orWhere('kode_akun', 'like', '5%')
                  ->orWhere('kode_akun', 'like', '6%');
            })
            ->orderBy('kode_akun', 'asc')
            ->get();

        // Daftar Kas & Bank aktif untuk filter
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
            'bkkList',
            'summaryMetrics',
            'expenseCoas',
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
     * Verifikasi Masal Bukti Kas Keluar (BKK) dan Terbitkan Jurnal Umum Sekaligus.
     */
    public function batchProses(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.bkk_id'      => 'required|integer|exists:dat_kas_keluar_hdr,bkk_id',
            'items.*.coa_id'      => 'required|integer|exists:mst_coa,coa_id',
            'items.*.tgl_jurnal'  => 'nullable|date',
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

            return redirect()->route('keuangan.verifikasi_jurnal.index')->with('success', $message);
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
}
