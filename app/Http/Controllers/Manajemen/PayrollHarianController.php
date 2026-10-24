<?php

namespace App\Http\Controllers\Manajemen;

use App\Exports\Payroll\PayrollHarianExport;
use App\Http\Controllers\Controller;
use App\Services\Manajemen\PayrollHarianService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PayrollHarianController extends Controller
{
    public function index(Request $request, PayrollHarianService $svc)
    {
        return view('manajemen.payroll_harian.index', $svc->laporan($this->periode($request)));
    }

    public function rincian(Request $request, PayrollHarianService $svc)
    {
        $d = $request->validate([
            'tanggal'  => 'required|date_format:Y-m-d',
            'kelompok' => 'nullable|string|max:50',
        ]);

        return view('manajemen.payroll_harian.rincian', $svc->rincian($d['tanggal'], $d['kelompok'] ?? null));
    }

    public function export(Request $request, PayrollHarianService $svc)
    {
        $periode = $this->periode($request);

        return Excel::download(new PayrollHarianExport($svc->laporan($periode)), "payroll-harian-{$periode}.xlsx");
    }

    private function periode(Request $request): string
    {
        $p = (string) $request->input('periode');

        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $p) ? $p : now()->format('Y-m');
    }
}