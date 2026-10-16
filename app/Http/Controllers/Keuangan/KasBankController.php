<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstKasBank;
use App\Services\Keuangan\KasBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Exception;

class KasBankController extends Controller
{
    protected KasBankService $kasBankService;

    public function __construct(KasBankService $kasBankService)
    {
        $this->kasBankService = $kasBankService;
    }

    /**
     * Halaman Utama Saldo Akun Kas & Rekening Bank
     */
    public function index(): View
    {
        $kasBanks = $this->kasBankService->getAllKasBank();
        $totalSaldo = (float) $kasBanks->sum('saldo_berjalan');
        $totalKas = (float) $kasBanks->where('tipe_akun', 'KAS')->sum('saldo_berjalan');
        $totalBank = (float) $kasBanks->where('tipe_akun', 'BANK')->sum('saldo_berjalan');
        $totalAkun = $kasBanks->count();

        // Mutasi hari ini
        $mutasiHariIni = DatKasMutasi::where('deleted_st', false)
            ->whereDate('tgl_mutasi', now()->toDateString())
            ->count();

        return view('keuangan.kas_bank.index', compact(
            'kasBanks',
            'totalSaldo',
            'totalKas',
            'totalBank',
            'totalAkun',
            'mutasiHariIni'
        ));
    }

    /**
     * Tambah Akun Kas/Bank Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'kode_akun'      => 'required|string|max:50|unique:mst_kas_bank,kode_akun',
            'nama_akun'      => 'required|string|max:150',
            'tipe_akun'      => 'required|in:KAS,BANK',
            'saldo_awal'     => 'nullable|numeric|min:0',
            'nomor_rekening' => 'nullable|string|max:100',
            'atas_nama'      => 'nullable|string|max:150',
            'bank_nama'      => 'nullable|string|max:100',
            'cabang'         => 'nullable|string|max:100',
            'keterangan'     => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $kasBank = $this->kasBankService->createKasBank($request->all(), $user);

            return redirect()->route('keuangan.kas_bank.index')
                ->with('success', "Akun Kas/Bank '{$kasBank->nama_akun}' ({$kasBank->kode_akun}) berhasil ditambahkan.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan akun: ' . $e->getMessage());
        }
    }

    /**
     * Perbarui Data Akun Kas/Bank
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $kasBank = MstKasBank::where('deleted_st', false)->findOrFail($id);

        $request->validate([
            'nama_akun'      => 'required|string|max:150',
            'nomor_rekening' => 'nullable|string|max:100',
            'atas_nama'      => 'nullable|string|max:150',
            'bank_nama'      => 'nullable|string|max:100',
            'cabang'         => 'nullable|string|max:100',
            'keterangan'     => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $this->kasBankService->updateKasBank($kasBank, $request->all(), $user);

            return redirect()->route('keuangan.kas_bank.index')
                ->with('success', "Informasi rekening '{$kasBank->nama_akun}' berhasil diperbarui.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui rekening: ' . $e->getMessage());
        }
    }

    /**
     * Hapus / Nonaktifkan Akun Kas/Bank
     */
    public function destroy($id): RedirectResponse
    {
        $kasBank = MstKasBank::where('deleted_st', false)->findOrFail($id);

        try {
            $user = Auth::user();
            $namaAkun = $kasBank->nama_akun;
            $isSoftDeleted = $this->kasBankService->deleteKasBank($kasBank, $user);

            if ($isSoftDeleted) {
                return redirect()->route('keuangan.kas_bank.index')
                    ->with('success', "Akun '{$namaAkun}' berhasil dihapus dari sistem.");
            } else {
                return redirect()->route('keuangan.kas_bank.index')
                    ->with('info', "Akun '{$namaAkun}' memiliki riwayat transaksi/pembayaran, sehingga statusnya dinonaktifkan (diarsipkan) untuk menjaga integritas pembukuan.");
            }
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }

    /**
     * Transfer Dana Antar Kas & Bank
     */
    public function transfer(Request $request): RedirectResponse
    {
        $request->validate([
            'kas_bank_asal_id'   => 'required|integer|exists:mst_kas_bank,kas_bank_id',
            'kas_bank_tujuan_id' => 'required|integer|different:kas_bank_asal_id|exists:mst_kas_bank,kas_bank_id',
            'nominal'            => 'required|numeric|min:1',
            'tgl_mutasi'         => 'required|date',
            'keterangan'         => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $result = $this->kasBankService->transferDana($request->all(), $user);

            $nominalFormat = 'Rp ' . number_format($result['nominal'], 0, ',', '.');
            return redirect()->route('keuangan.kas_bank.index')
                ->with('success', "Transfer dana {$nominalFormat} dari [{$result['kas_asal']->nama_akun}] ke [{$result['kas_tujuan']->nama_akun}] berhasil dibukukan (Ref: {$result['ref_no']}).");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer dana gagal: ' . $e->getMessage());
        }
    }

    /**
     * Penyesuaian Saldo / Opname Fisik
     */
    public function penyesuaian(Request $request): RedirectResponse
    {
        $request->validate([
            'kas_bank_id'      => 'required|integer|exists:mst_kas_bank,kas_bank_id',
            'saldo_fisik_baru' => 'required|numeric|min:0',
            'tgl_mutasi'       => 'required|date',
            'keterangan'       => 'required|string|max:255',
        ]);

        try {
            $kasBank = MstKasBank::where('deleted_st', false)->findOrFail($request->kas_bank_id);
            $user = Auth::user();

            $mutasi = $this->kasBankService->penyesuaianSaldo(
                $kasBank,
                (float)$request->saldo_fisik_baru,
                $request->tgl_mutasi,
                $request->keterangan,
                $user
            );

            $selisihFormat = 'Rp ' . number_format($mutasi->nominal, 0, ',', '.');
            $simbol = ($mutasi->tipe_mutasi === 'MASUK') ? '+' : '-';
            return redirect()->route('keuangan.kas_bank.index')
                ->with('success', "Penyesuaian saldo akun [{$kasBank->nama_akun}] berhasil dibukukan dengan selisih {$simbol}{$selisihFormat} (Ref: {$mutasi->ref_no}).");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Penyesuaian saldo gagal: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Buku Mutasi Kas & Bank (Cash & Bank Ledger)
     */
    public function mutasi(Request $request): View
    {
        $filters = [
            'kas_bank_id'     => $request->input('kas_bank_id'),
            'tipe_mutasi'     => $request->input('tipe_mutasi'),
            'jenis_transaksi' => $request->input('jenis_transaksi'),
            'tgl_mulai'       => $request->input('tgl_mulai'),
            'tgl_selesai'     => $request->input('tgl_selesai'),
        ];

        $mutasiList = $this->kasBankService->getMutasiPaginated($filters, 25);
        $kasBanks = $this->kasBankService->getAllKasBank();

        // Hitung total masuk & keluar berdasarkan filter periode/akun saat ini
        $baseQuery = DatKasMutasi::where('deleted_st', false);
        if (!empty($filters['kas_bank_id'])) {
            $baseQuery->where('kas_bank_id', $filters['kas_bank_id']);
        }
        if (!empty($filters['tgl_mulai'])) {
            $baseQuery->where('tgl_mutasi', '>=', $filters['tgl_mulai']);
        }
        if (!empty($filters['tgl_selesai'])) {
            $baseQuery->where('tgl_mutasi', '<=', $filters['tgl_selesai']);
        }

        $totalMasuk = (float) (clone $baseQuery)->where('tipe_mutasi', 'MASUK')->sum('nominal');
        $totalKeluar = (float) (clone $baseQuery)->where('tipe_mutasi', 'KELUAR')->sum('nominal');
        $netFlow = $totalMasuk - $totalKeluar;

        return view('keuangan.kas_bank.mutasi', compact(
            'mutasiList',
            'kasBanks',
            'filters',
            'totalMasuk',
            'totalKeluar',
            'netFlow'
        ));
    }
}
