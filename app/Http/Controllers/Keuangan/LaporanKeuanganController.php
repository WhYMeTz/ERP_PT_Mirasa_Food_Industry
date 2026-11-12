<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Services\Keuangan\LaporanKeuanganService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanKeuanganController extends Controller
{
    protected LaporanKeuanganService $laporanService;

    public function __construct(LaporanKeuanganService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    /**
     * Helper resolving date range from presets or request
     */
    protected function resolveDates(Request $request): array
    {
        $preset = $request->input('preset', 'this_month');
        $now = now();

        switch ($preset) {
            case 'today':
                $start = $now->toDateString();
                $end = $now->toDateString();
                $label = 'Hari Ini (' . $now->format('d M Y') . ')';
                break;
            case 'last_month':
                $last = $now->copy()->subMonth();
                $start = $last->startOfMonth()->toDateString();
                $end = $last->endOfMonth()->toDateString();
                $label = 'Bulan Lalu (' . $last->translatedFormat('F Y') . ')';
                break;
            case 'this_quarter':
                $start = $now->copy()->firstOfQuarter()->toDateString();
                $end = $now->copy()->lastOfQuarter()->toDateString();
                $label = 'Kuartal Ini (Q' . $now->quarter . ' ' . $now->year . ')';
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear()->toDateString();
                $end = $now->copy()->endOfYear()->toDateString();
                $label = 'Tahun Ini (' . $now->year . ')';
                break;
            case 'custom':
                $start = $request->input('start_date') ?: $now->copy()->startOfMonth()->toDateString();
                $end = $request->input('end_date') ?: $now->copy()->endOfMonth()->toDateString();
                $label = Carbon::parse($start)->format('d/m/Y') . ' - ' . Carbon::parse($end)->format('d/m/Y');
                break;
            case 'this_month':
            default:
                $preset = 'this_month';
                $start = $now->copy()->startOfMonth()->toDateString();
                $end = $now->copy()->endOfMonth()->toDateString();
                $label = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
                break;
        }

        return [
            'preset'     => $preset,
            'start_date' => $start,
            'end_date'   => $end,
            'label'      => $label,
        ];
    }

    /**
     * Hub Utama Laporan Keuangan (Executive Financial Dashboard)
     */
    public function index(Request $request): View
    {
        $dateInfo = $this->resolveDates($request);
        $summary = $this->laporanService->getExecutiveSummary($dateInfo['start_date'], $dateInfo['end_date']);
        $labaRugi = $this->laporanService->getLabaRugi($dateInfo['start_date'], $dateInfo['end_date']);
        $neraca = $this->laporanService->getNeraca($dateInfo['end_date']);
        $arusKas = $this->laporanService->getArusKas($dateInfo['start_date'], $dateInfo['end_date']);

        return view('keuangan.laporan.index', compact('dateInfo', 'summary', 'labaRugi', 'neraca', 'arusKas'));
    }

    /**
     * 1. Laporan Laba Rugi Komprehensif
     */
    public function labaRugi(Request $request): View|JsonResponse
    {
        $dateInfo = $this->resolveDates($request);
        $report = $this->laporanService->getLabaRugi($dateInfo['start_date'], $dateInfo['end_date']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $report,
            ]);
        }

        $isPrint = $request->boolean('print');
        return view('keuangan.laporan.laba-rugi', compact('dateInfo', 'report', 'isPrint'));
    }

    /**
     * 2. Laporan Neraca Keuangan (Balance Sheet)
     */
    public function neraca(Request $request): View|JsonResponse
    {
        $dateInfo = $this->resolveDates($request);
        $asOfDate = $request->input('as_of_date', $dateInfo['end_date']);
        $report = $this->laporanService->getNeraca($asOfDate);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $report,
            ]);
        }

        $isPrint = $request->boolean('print');
        return view('keuangan.laporan.neraca', compact('dateInfo', 'asOfDate', 'report', 'isPrint'));
    }

    /**
     * 3. Laporan Neraca Saldo (Trial Balance)
     */
    public function neracaSaldo(Request $request): View|JsonResponse
    {
        $dateInfo = $this->resolveDates($request);
        $report = $this->laporanService->getNeracaSaldo($dateInfo['start_date'], $dateInfo['end_date']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $report,
            ]);
        }

        $isPrint = $request->boolean('print');
        return view('keuangan.laporan.neraca-saldo', compact('dateInfo', 'report', 'isPrint'));
    }

    /**
     * 4. Laporan Arus Kas (Cash Flow Statement)
     */
    public function arusKas(Request $request): View|JsonResponse
    {
        $dateInfo = $this->resolveDates($request);
        $report = $this->laporanService->getArusKas($dateInfo['start_date'], $dateInfo['end_date']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $report,
            ]);
        }

        $isPrint = $request->boolean('print');
        return view('keuangan.laporan.arus-kas', compact('dateInfo', 'report', 'isPrint'));
    }
}

