<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\MstCoa;
use App\Services\Keuangan\JurnalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurnalController extends Controller
{
    public function __construct(
        protected JurnalService $jurnalService
    ) {}

    /**
     * Halaman Utama Jurnal Umum (General Journal Ledger)
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'         => $request->input('search'),
            'tipe_transaksi' => $request->input('tipe_transaksi'),
            'start_date'     => $request->input('start_date'),
            'end_date'       => $request->input('end_date'),
            'coa_id'         => $request->input('coa_id'),
        ];

        $jurnals = $this->jurnalService->getAllPaginated($filters, 20);
        $metrics = $this->jurnalService->getSummaryMetrics($filters);
        $coaList = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('kode_akun', 'asc')
            ->get();

        $tipeTransaksiList = [
            'HUTANG_PEMBELIAN' => 'Hutang Pembelian (GRN)',
            'BAYAR_HUTANG'     => 'Pelunasan Hutang (BKK)',
            'KAS_MASUK'        => 'Kas Masuk (BKM)',
            'JURNAL_UMUM'      => 'Jurnal Penyesuaian / Umum',
        ];

        return view('keuangan.jurnal.index', compact(
            'jurnals',
            'metrics',
            'coaList',
            'tipeTransaksiList',
            'filters'
        ));
    }

    /**
     * Detail Jurnal (AJAX / JSON)
     */
    public function show(int $id, Request $request): View|JsonResponse
    {
        $jurnal = $this->jurnalService->getById($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'data'   => $jurnal,
            ]);
        }

        return view('keuangan.jurnal.show', compact('jurnal'));
    }
}
