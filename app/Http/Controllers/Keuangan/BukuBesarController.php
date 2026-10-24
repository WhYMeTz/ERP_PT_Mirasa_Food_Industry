<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Services\Keuangan\BukuBesarService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BukuBesarController extends Controller
{
    protected BukuBesarService $bukuBesarService;

    public function __construct(BukuBesarService $bukuBesarService)
    {
        $this->bukuBesarService = $bukuBesarService;
    }

    /**
     * Tampilan Utama Buku Besar Interaktif (Split Layout: List Akun & Detail Buku Besar)
     */
    public function index(Request $request): View|JsonResponse
    {
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $month = $request->input('month', now()->format('Y-m'));

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (empty($startDate) || empty($endDate)) {
            $monthDate = Carbon::parse($month . '-01');
            $startDate = $monthDate->copy()->startOfMonth()->toDateString();
            $endDate = $monthDate->copy()->endOfMonth()->toDateString();
        }

        // Ambil daftar akun COA
        $accounts = $this->bukuBesarService->getCoaAccountsList($search, $kategori, $month);

        // Tentukan akun aktif terpilih
        $selectedCoaId = $request->input('coa_id');
        if (empty($selectedCoaId) && $accounts->isNotEmpty()) {
            // Prioritas akun yang memiliki mutasi terbanyak di bulan ini (misal 1150 atau 2100)
            $activeWithMutasi = $accounts->firstWhere('mutasi_count', '>', 0);
            $selectedCoaId = $activeWithMutasi ? $activeWithMutasi->coa_id : $accounts->first()->coa_id;
        }

        $ledgerData = null;
        if ($selectedCoaId) {
            $ledgerData = $this->bukuBesarService->getLedgerDetail((int)$selectedCoaId, $startDate, $endDate);
        }

        // Jika dipanggil via AJAX untuk ganti akun / filter tanggal
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'ledger_data' => $ledgerData,
            ]);
        }

        return view('keuangan.buku-besar.index', compact(
            'accounts',
            'selectedCoaId',
            'ledgerData',
            'startDate',
            'endDate',
            'month',
            'search',
            'kategori'
        ));
    }
}
