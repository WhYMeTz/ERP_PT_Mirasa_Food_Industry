<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\DatKasKeluarHdr;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Models\Keuangan\MstKategoriPengeluaran;
use App\Models\MasterData\MstKaryawan;
use App\Services\Keuangan\KasBankService;
use App\Services\Keuangan\KasKeluarService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KasKeluarController extends Controller
{
    public function __construct(
        protected KasKeluarService $kasKeluarService,
        protected KasBankService $kasBankService
    ) {}

    /**
     * Daftar Riwayat Pengeluaran Kas Operasional Pabrik (BKK Non-Tagihan)
     */
    public function index(Request $request): View
    {
        $filters = [
            'kas_bank_id'  => $request->input('kas_bank_id'),
            'kategori'     => $request->input('kategori'),
            'status_cd'    => $request->input('status_cd'),
            'metode_bayar' => $request->input('metode_bayar'),
            'search'       => $request->input('search'),
            'tgl_mulai'    => $request->input('tgl_mulai'),
            'tgl_selesai'  => $request->input('tgl_selesai'),
        ];

        $operasionalList = $this->kasKeluarService->getKasKeluarListPaginated($filters, 15);
        $kasBanks        = $this->kasBankService->getAllKasBank();

        $countOperasional = DatKasKeluarHdr::where('deleted_st', false)->count();
        $countDraft       = DatKasKeluarHdr::where('deleted_st', false)->where('status_cd', 'DRAFT')->count();
        $countPosted      = DatKasKeluarHdr::where('deleted_st', false)->where('status_cd', 'POSTED')->count();
        $countVoid        = DatKasKeluarHdr::where('deleted_st', false)->where('status_cd', 'VOID')->count();
        $totalOperasional = (float) DatKasKeluarHdr::where('deleted_st', false)
            ->whereIn('status_cd', ['DRAFT', 'POSTED'])
            ->sum('total_keluar');

        $kategoriMasterList = MstKategoriPengeluaran::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('nama_kategori')
            ->get();

        $kategoriList = [
            'BIAYA_OPERASIONAL'     => 'Biaya Operasional Umum',
            'LISTRIK_AIR'           => 'Listrik & Air',
            'BBM_TRANSPORT'         => 'BBM / Solar / Transport',
            'PEMELIHARAAN_MESIN'    => 'Pemeliharaan & Servis Mesin',
            'PERLENGKAPAN_ATK'      => 'Perlengkapan & ATK',
            'KONSUMSI_RUMAH_TANGGA' => 'Konsumsi & Rumah Tangga',
            'RISET_QC'              => 'Riset & Pengujian QC',
            'PENGIRIMAN_EKSPEDISI'  => 'Pengiriman & Ekspedisi',
            'GAJI_UPAH_HARIAN'      => 'Upah / Gaji Harian',
            'LAINNYA'               => 'Lainnya',
        ];

        return view('keuangan.kas_keluar.index', compact(
            'operasionalList',
            'kasBanks',
            'filters',
            'countOperasional',
            'countDraft',
            'countPosted',
            'countVoid',
            'totalOperasional',
            'kategoriMasterList',
            'kategoriList'
        ));
    }

    /**
     * Form Input Pengeluaran Kas / Biaya Operasional Baru (BKK Operasional)
     */
    public function create(Request $request): View
    {
        $kasBanks = MstKasBank::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('tipe_akun', 'asc')
            ->orderBy('nama_akun', 'asc')
            ->get();

        // Akun-akun beban operasional resmi perusahaan (6xxx dan penyusutan 5301)
        $expenseCoas = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->where(function ($q) {
                $q->where('kategori_akun', 'BEBAN')
                  ->orWhere('kode_akun', 'like', '6%')
                  ->orWhere('kode_akun', '5301');
            })
            ->orderBy('kode_akun', 'asc')
            ->get();

        // Daftar Karyawan Aktif untuk Pemohon Dana
        $karyawanList = MstKaryawan::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('karyawan_nm')
            ->get(['karyawan_id', 'karyawan_nm', 'departemen_cd', 'jabatan_nm']);

        // Daftar Kategori Pengeluaran Aktif dari Master Data dengan Relasi COA Default
        $kategoriMasterList = MstKategoriPengeluaran::with('coaDefault')
            ->where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('nama_kategori')
            ->get();

        $kategoriList = [
            'LISTRIK_AIR'           => 'Listrik & Air',
            'BBM_TRANSPORT'         => 'BBM / Solar',
            'PEMELIHARAAN_MESIN'    => 'Servis Mesin',
            'PERLENGKAPAN_ATK'      => 'ATK & Kardus',
            'KONSUMSI_RUMAH_TANGGA' => 'Makan / Dapur',
            'RISET_QC'              => 'Riset & Pengujian QC',
            'PENGIRIMAN_EKSPEDISI'  => 'Ekspedisi',
            'BIAYA_OPERASIONAL'     => 'Operasional Lainnya',
        ];

        $defaultKategori = $request->input('kategori', 'BIAYA_OPERASIONAL');
        $suggestedBkkNo  = $this->kasKeluarService->generateBkkNo();

        return view('keuangan.kas_keluar.create', compact(
            'kasBanks',
            'expenseCoas',
            'karyawanList',
            'kategoriMasterList',
            'kategoriList',
            'defaultKategori',
            'suggestedBkkNo'
        ));
    }

    /**
     * Simpan Transaksi Pengeluaran Kas Operasional (Tahap 1 Kasir)
     */
    public function store(Request $request): RedirectResponse
    {
        // Auto-detect tipe_penerima jika tidak dikirim eksplisit
        if (!$request->filled('tipe_penerima')) {
            $request->merge([
                'tipe_penerima' => $request->filled('pemohon_karyawan_id') ? 'KARYAWAN' : 'PIHAK_KETIGA',
            ]);
        }

        // Auto-fill nama penerima jika kasir memilih reimbursement/kasbon karyawan
        if ($request->input('tipe_penerima') === 'KARYAWAN' && $request->filled('pemohon_karyawan_id') && !$request->filled('penerima_nama')) {
            $karyawan = MstKaryawan::find($request->input('pemohon_karyawan_id'));
            if ($karyawan) {
                $request->merge(['penerima_nama' => $karyawan->karyawan_nm]);
            }
        }

        // Auto-sync kategori_id dan kategori_pengeluaran
        if ($request->filled('kategori_id') && !$request->filled('kategori_pengeluaran')) {
            $kat = MstKategoriPengeluaran::find($request->input('kategori_id'));
            if ($kat) {
                $request->merge(['kategori_pengeluaran' => $kat->kode_kategori]);
            }
        } elseif ($request->filled('kategori_pengeluaran') && !$request->filled('kategori_id')) {
            $kat = MstKategoriPengeluaran::where('kode_kategori', $request->input('kategori_pengeluaran'))->first();
            if ($kat) {
                $request->merge(['kategori_id' => $kat->kategori_id]);
            }
        }

        $request->validate([
            'bkk_no'               => 'nullable|string|max:100',
            'tgl_keluar'           => 'required|date',
            'kas_bank_id'          => 'required|exists:mst_kas_bank,kas_bank_id',
            'coa_id'               => 'nullable|exists:mst_coa,coa_id',
            'pemohon_karyawan_id'  => 'nullable|exists:mst_karyawan,karyawan_id',
            'departemen_pemohon'   => 'nullable|string|max:50',
            'tipe_penerima'        => 'required|in:KARYAWAN,PIHAK_KETIGA',
            'kategori_id'          => 'nullable|exists:mst_kategori_pengeluaran,kategori_id',
            'kategori_pengeluaran' => 'nullable|string|max:50',
            'penerima_nama'        => 'required|string|max:150',
            'metode_bayar'         => 'required|in:CASH,TRANSFER,GIRO,LAINNYA',
            'no_referensi_bank'    => 'nullable|string|max:100',
            'catatan'              => 'nullable|string|max:500',
            'items'                => 'required|array|min:1',
            'items.*.uraian'       => 'required|string|max:255',
            'items.*.nominal'      => 'nullable|numeric|min:0',
            'items.*.harga_satuan' => 'nullable|numeric|min:0',
            'items.*.coa_id'       => 'nullable|exists:mst_coa,coa_id',
            'items.*.qty'          => 'nullable|numeric|min:0.01',
            'items.*.satuan'       => 'nullable|string|max:20',
        ], [
            'penerima_nama.required' => 'Nama penerima / pihak yang dibayar wajib diisi.',
            'kas_bank_id.required'   => 'Pilih akun kas atau rekening bank sumber dana.',
            'items.required'         => 'Minimal harus ada 1 rincian pengeluaran.',
            'items.*.uraian.required'=> 'Uraian pengeluaran wajib diisi.',
            'tipe_penerima.required' => 'Pilih tipe penerima dana (Karyawan / Pihak Ketiga).',
        ]);

        try {
            $user = Auth::user();
            $bkk = $this->kasKeluarService->storeKasKeluar($request->all(), $user);

            return redirect()->route('keuangan.kas_keluar.show', $bkk->bkk_id)
                ->with('success', "Kas Keluar {$bkk->bkk_no} berhasil dicatat! Saldo kas/bank telah terpotong dan dokumen masuk ke antrean verifikasi Jurnal Umum Akuntansi.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat pengeluaran kas: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Detail Voucher Bukti Kas Keluar (BKK) Resmi & Siap Cetak
     */
    public function show(int $id): View
    {
        $bkk = $this->kasKeluarService->getById($id);

        return view('keuangan.kas_keluar.show', compact('bkk'));
    }

    /**
     * Batalkan / Void Transaksi Bukti Kas Keluar
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'alasan_void' => 'required|string|min:5|max:255',
        ], [
            'alasan_void.required' => 'Alasan pembatalan (void) wajib diisi.',
            'alasan_void.min'      => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        try {
            $user = Auth::user();
            $bkk = $this->kasKeluarService->voidKasKeluar($id, $request->input('alasan_void'), $user);

            $targetRoute = match ($request->input('redirect_to')) {
                'index'      => route('keuangan.kas_keluar.index'),
                'verifikasi' => route('keuangan.verifikasi_jurnal.index'),
                default      => route('keuangan.kas_keluar.show', $bkk->bkk_id),
            };

            return redirect($targetRoute)
                ->with('success', "Voucher BKK {$bkk->bkk_no} berhasil DIBATALKAN (VOID). Saldo kas telah dikembalikan.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}
