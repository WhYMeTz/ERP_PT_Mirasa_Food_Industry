<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Services\Manajemen\PayrollService;
use App\Exports\Payroll\PayrollExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Payroll\PayrollTemplateExport;
use App\Imports\Manajemen\PayrollKomponenImport;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request, PayrollService $svc)
    {
        $periode = $this->periode($request);

        return view('manajemen.payroll.index', $svc->daftar($periode, $request->query('q')) + ['periode' => $periode]);
    }

    public function simpan(Request $request, PayrollService $svc)
    {
        $data = $request->validate([
            'periode'  => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'id_absen' => ['required', 'string', 'max:20'],
            'catatan'  => ['nullable', 'string', 'max:255'],
        ] + collect(PayrollService::INPUT)->mapWithKeys(fn ($f) => [$f => ['nullable', 'string', 'max:20']])->all());

        // isian berformat "97.300" -> 97300 (rupiah bulat)
        $komponen = [];
        foreach (PayrollService::INPUT as $f) {
            $komponen[$f] = (float) preg_replace('/\D/', '', (string) ($data[$f] ?? ''));
        }

        try {
            $svc->simpanKomponen($data['periode'], $data['id_absen'], $komponen, $data['catatan'] ?? null);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Komponen payroll berhasil disimpan.');
    }

    public function finalkan(Request $request, PayrollService $svc)
    {
        $periode = $this->periode($request);
        try {
            $n = $svc->finalkan($periode, $request->user()?->name);
        } catch (\DomainException $e) {
            return redirect()->route('manajemen.payroll.index', ['periode' => $periode])->with('error', $e->getMessage());
        }

        return redirect()->route('manajemen.payroll.index', ['periode' => $periode])
            ->with('success', "Payroll {$periode} difinalkan untuk {$n} karyawan. Angka sudah dikunci.");
    }

    public function bukaKunci(Request $request, PayrollService $svc)
    {
        return $this->aksiStatus($request, fn ($p) => $svc->bukaKunci($p), 'Kunci payroll dibuka. Periode kembali ke Draft.');
    }

    public function dibayar(Request $request, PayrollService $svc)
    {
        return $this->aksiStatus($request, fn ($p) => $svc->tandaiDibayar($p), 'Payroll ditandai sudah dibayar.');
    }

        public function export(Request $request, PayrollService $svc)
    {
        $periode = $this->periode($request);
        $rows = $svc->daftar($periode)['rows'];

        return Excel::download(new PayrollExport($rows), "payroll-{$periode}.xlsx");
    }

        public function template(Request $request, PayrollService $svc)
    {
        $periode = $this->periode($request);

        return Excel::download(new PayrollTemplateExport($svc->daftar($periode)['rows']), "template-komponen-payroll-{$periode}.xlsx");
    }

    public function import(Request $request, PayrollService $svc)
    {
        $request->validate([
            'periode' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'file'    => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);
        $periode = $this->periode($request);

        try {
            $baris = Excel::toCollection(new PayrollKomponenImport, $request->file('file'))->first() ?? collect();
            $hasil = $svc->importKomponen($periode, $baris);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($hasil['errors']) {
            return back()
                ->with('error', 'Import dibatalkan, tidak ada data yang berubah. Perbaiki baris berikut lalu coba lagi.')
                ->with('import_errors', array_slice($hasil['errors'], 0, 50));
        }

        return back()->with('success', "Import selesai: komponen {$hasil['berhasil']} karyawan diperbarui"
            . ($hasil['kosong'] ? ", {$hasil['kosong']} baris kosong dilewati" : '') . '.');
    }

    public function slip(Request $request, PayrollService $svc)
    {
        $periode = $this->periode($request);
        $row = $svc->baris($periode, (string) $request->query('id_absen'));
        abort_if(!$row, 404, 'Data payroll karyawan tidak ditemukan pada periode ini.');

        return view('manajemen.payroll.slip', ['rows' => collect([$row]), 'periode' => $periode]);
    }

    public function slipSemua(Request $request, PayrollService $svc)
    {
        $periode = $this->periode($request);
        $rows = $svc->daftar($periode)['rows']->where('siap', true)->values();
        abort_if($rows->isEmpty(), 404, 'Belum ada slip yang bisa dicetak pada periode ini.');

        return view('manajemen.payroll.slip', ['rows' => $rows, 'periode' => $periode]);
    }

    private function aksiStatus(Request $request, callable $aksi, string $pesan)
    {
        $periode = $this->periode($request);
        try {
            $aksi($periode);
        } catch (\DomainException $e) {
            return redirect()->route('manajemen.payroll.index', ['periode' => $periode])->with('error', $e->getMessage());
        }

        return redirect()->route('manajemen.payroll.index', ['periode' => $periode])->with('success', $pesan);
    }

    private function periode(Request $request): string
    {
        $p = (string) $request->input('periode');

        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $p) ? $p : now()->format('Y-m');
    }
}