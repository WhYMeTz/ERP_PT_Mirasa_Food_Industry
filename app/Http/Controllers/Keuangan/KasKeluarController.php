<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Services\Keuangan\KasKeluarService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KasKeluarController extends Controller
{
    public function __construct(
        protected KasKeluarService $kasKeluarService
    ) {}

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

        $defaultKategori = $request->input('kategori', 'BIAYA_OPERASIONAL');
        $suggestedBkkNo  = $this->kasKeluarService->generateBkkNo();

        return view('keuangan.kas_keluar.create', compact(
            'kasBanks',
            'expenseCoas',
            'defaultKategori',
            'suggestedBkkNo'
        ));
    }

    /**
     * Simpan Transaksi Pengeluaran Kas Operasional & Otomatis Catat Jurnal
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bkk_no'               => 'nullable|string|max:100',
            'tgl_keluar'           => 'required|date',
            'kas_bank_id'          => 'required|exists:mst_kas_bank,kas_bank_id',
            'coa_id'               => 'nullable|exists:mst_coa,coa_id',
            'kategori_pengeluaran' => 'required|string|max:50',
            'penerima_nama'        => 'required|string|max:150',
            'metode_bayar'         => 'required|in:CASH,TRANSFER,GIRO,LAINNYA',
            'no_referensi_bank'    => 'nullable|string|max:100',
            'catatan'              => 'nullable|string|max:500',
            'items'                => 'required|array|min:1',
            'items.*.uraian'       => 'required|string|max:255',
            'items.*.nominal'      => 'required|numeric|min:1',
            'items.*.coa_id'       => 'nullable|exists:mst_coa,coa_id',
            'items.*.qty'          => 'nullable|numeric|min:0.01',
            'items.*.satuan'       => 'nullable|string|max:20',
        ], [
            'penerima_nama.required' => 'Nama penerima / pihak yang dibayar wajib diisi.',
            'kas_bank_id.required'   => 'Pilih akun kas atau rekening bank sumber dana.',
            'items.required'         => 'Minimal harus ada 1 rincian pengeluaran.',
            'items.*.uraian.required'=> 'Uraian pengeluaran wajib diisi.',
            'items.*.nominal.min'    => 'Nominal setiap rincian harus lebih dari Rp 0.',
        ]);

        try {
            $user = Auth::user();
            $bkk = $this->kasKeluarService->storeKasKeluar($request->all(), $user);

            return redirect()->route('keuangan.kas_keluar.show', $bkk->bkk_id)
                ->with('toast_success', "Bukti Kas Keluar {$bkk->bkk_no} berhasil dicatat! Saldo kas telah terpotong dan jurnal umum telah terbentuk otomatis.");
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

            return redirect()->route('keuangan.kas_keluar.show', $bkk->bkk_id)
                ->with('toast_success', "Voucher BKK {$bkk->bkk_no} berhasil DIBATALKAN (VOID). Saldo kas telah dikembalikan.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}

