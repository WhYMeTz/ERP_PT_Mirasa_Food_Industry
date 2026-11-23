<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Services\Penjualan\LaporanPenjualanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LaporanPenjualanController extends Controller
{
    public function __construct(
        protected LaporanPenjualanService $laporanService
    ) {}

    /**
     * Dashboard Rekapitulasi & Analisis Penjualan (PO & Non-PO)
     */
    public function index(Request $request): View
    {
        $preset = $request->input('preset');
        if (!$preset) {
            $hasThisMonth = \App\Models\Penjualan\DatSoHdr::where('deleted_st', false)
                ->whereDate('so_tgl', '>=', now()->startOfMonth()->toDateString())
                ->whereDate('so_tgl', '<=', now()->endOfMonth()->toDateString())
                ->exists();
            $preset = $hasThisMonth ? 'this_month' : 'all';
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $channel = $request->input('channel', 'ALL'); // 'ALL', 'PO', 'DIRECT'
        $search = $request->input('search');
        $tab = $request->input('tab', 'ringkasan'); // 'ringkasan', 'pelanggan', 'transaksi'

        $dateInfo = $this->laporanService->resolveDateRange($preset, $startDate, $endDate);

        $metrics = $this->laporanService->getExecutiveMetrics(
            $dateInfo['start_date'],
            $dateInfo['end_date'],
            $channel !== 'ALL' ? $channel : null
        );

        $salesByProduct = $this->laporanService->getSalesByProduct(
            $dateInfo['start_date'],
            $dateInfo['end_date'],
            $channel !== 'ALL' ? $channel : null
        );

        $salesByCustomer = $this->laporanService->getSalesByCustomer(
            $dateInfo['start_date'],
            $dateInfo['end_date'],
            $channel !== 'ALL' ? $channel : null
        );

        $transactions = $this->laporanService->getTransactionLedger(
            $dateInfo['start_date'],
            $dateInfo['end_date'],
            $channel !== 'ALL' ? $channel : null,
            $search,
            15
        );

        return view('penjualan.laporan.index', compact(
            'dateInfo',
            'channel',
            'tab',
            'search',
            'metrics',
            'salesByProduct',
            'salesByCustomer',
            'transactions'
        ));
    }

    /**
     * Cetak Dokumen Rekapitulasi Eksekutif Penjualan (PDF)
     */
    public function exportPdf(Request $request): Response
    {
        $preset = $request->input('preset', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $channel = $request->input('channel', 'ALL');

        $dateInfo = $this->laporanService->resolveDateRange($preset, $startDate, $endDate);

        $metrics = $this->laporanService->getExecutiveMetrics(
            $dateInfo['start_date'],
            $dateInfo['end_date'],
            $channel !== 'ALL' ? $channel : null
        );

        $salesByProduct = $this->laporanService->getSalesByProduct(
            $dateInfo['start_date'],
            $dateInfo['end_date'],
            $channel !== 'ALL' ? $channel : null
        );

        $salesByCustomer = $this->laporanService->getSalesByCustomer(
            $dateInfo['start_date'],
            $dateInfo['end_date'],
            $channel !== 'ALL' ? $channel : null
        );

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('penjualan.laporan.pdf', compact(
            'dateInfo',
            'channel',
            'metrics',
            'salesByProduct',
            'salesByCustomer',
            'logoBase64'
        ))->setPaper('a4', 'portrait');

        $filename = 'Rekap_Penjualan_' . $dateInfo['start_date'] . '_' . $dateInfo['end_date'] . '.pdf';

        return $pdf->stream($filename);
    }
}
