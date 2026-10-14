<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
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
     * Halaman Saldo Akun Kas & Rekening Bank
     */
    public function index(): View
    {
        $kasBanks = $this->kasBankService->getAllKasBank();
        $totalSaldo = $kasBanks->sum('saldo_berjalan');
        $totalKas = $kasBanks->where('tipe_akun', 'KAS')->sum('saldo_berjalan');
        $totalBank = $kasBanks->where('tipe_akun', 'BANK')->sum('saldo_berjalan');

        return view('keuangan.kas_bank.index', compact('kasBanks', 'totalSaldo', 'totalKas', 'totalBank'));
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
        ]);

        try {
            $saldoAwal = (float)($request->input('saldo_awal', 0));
            $user = Auth::user();

            $kasBank = MstKasBank::create([
                'kode_akun'      => strtoupper(trim($request->kode_akun)),
                'nama_akun'      => trim($request->nama_akun),
                'tipe_akun'      => $request->tipe_akun,
                'nomor_rekening' => $request->nomor_rekening,
                'atas_nama'      => $request->atas_nama,
                'bank_nama'      => $request->bank_nama ? strtoupper(trim($request->bank_nama)) : null,
                'cabang'         => $request->cabang,
                'saldo_awal'     => $saldoAwal,
                'saldo_berjalan' => $saldoAwal,
                'keterangan'     => $request->keterangan,
                'created_by'     => $user->name ?? 'SYSTEM',
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            return redirect()->route('keuangan.kas_bank.index')
                ->with('success', "Akun Kas/Bank {$kasBank->nama_akun} berhasil ditambahkan.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan akun: ' . $e->getMessage());
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

        return view('keuangan.kas_bank.mutasi', compact('mutasiList', 'kasBanks', 'filters'));
    }
}
