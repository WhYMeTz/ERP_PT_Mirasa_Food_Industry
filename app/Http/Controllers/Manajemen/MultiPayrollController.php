<?php
// app/Http/Controllers/Manajemen/MultiPayrollController.php
namespace App\Http\Controllers\Manajemen;

use App\Exports\Payroll\MultiPayrollExport;
use App\Exports\Payroll\MultiPayrollBankSheet;
use App\Exports\Payroll\MultiPayrollSheet;
use App\Http\Controllers\Controller;
use App\Models\Manajemen\TrxMultiPayroll;
use App\Models\MasterData\MstKaryawan;
use App\Services\Manajemen\MultiPayrollBca;
use App\Services\Manajemen\MultiPayrollService;
use App\Services\Pajak\Bp21Penyusun;
use App\Services\Pajak\Bp21Xml;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

class MultiPayrollController extends Controller
{
    /** Nama sheet di Excel menurut kode bagian, sama dengan Excel Multi Payroll lama. */
    private const NAMA_SHEET = ['AC' => 'AC', 'GO' => 'BATCH-FRYER', 'PT' => 'POT', 'UM' => 'UMUM', 'BU' => 'PACKING', 'MKR' => 'MAKARONI'];

    public function index(Request $request, MultiPayrollService $svc)
    {
        $periode = $this->periode($request);
        $bagian  = $this->bagian($request);
        $siap    = $svc->periodeSiap($periode);

        $data = $siap
            ? $svc->untukBagian($periode, $bagian)
            : ['layak' => collect(), 'dilewati' => collect(), 'total' => 0];

        $cfg   = config('multipayroll.bagian')[$bagian];
        $isian = [
            'header_id'        => TrxMultiPayroll::nomorBerikutnya(),
            'tanggal_transfer' => Carbon::createFromFormat('!Y-m', $periode)->addMonth()->startOfMonth()->format('Y-m-d'),
            'remarks1'         => $cfg['remarks1'],
            'remarks2'         => $this->namaPeriode($periode),
        ];

        // Pratinjau ID transaksi sama dengan yang nanti ditulis ke file
        $p = $this->param($periode, $bagian, $isian);
        $data['layak'] = $data['layak']->map(function ($r, $i) use ($p) {
            $r['id_transaksi'] = MultiPayrollBca::idTransaksi($p['tahun'], $p['tanggal'], $p['bulan'], $p['jam'], $p['kode'], $i + 1);
            $r['nama_file']    = MultiPayrollBca::bersihNama($r['nama'], $p['maks_nama']);

            return $r;
        });

        return view('manajemen.payroll.multi', [
            'periode'    => $periode,
            'namaPeriode'=> $this->namaPeriode($periode),
            'bagian'     => $bagian,
            'daftarBagian' => array_keys(config('multipayroll.bagian')),
            'siap'       => $siap,
            'ringkasan'  => $siap ? $svc->ringkasan($periode) : [],
            'data'       => $data,
            'isian'      => $isian,
            'config'     => config('multipayroll'),
            'riwayat'    => TrxMultiPayroll::where('periode', $periode)->orderByDesc('id')->limit(20)->get(),
        ]);
    }

    /**
     * Excel pengecek: satu sheet per bagian (kolom sama dengan Excel lama Anda) + sheet "Dilewati".
     * Hanya karyawan yang layak; Transaction ID tanpa angka 0 di depan, seperti di Excel lama.
     */
    public function excel(Request $request, MultiPayrollService $svc)
    {
        $periode = $this->periode($request);

        if (! $svc->periodeSiap($periode)) {
            return redirect()->route('manajemen.payroll.multi.index', ['periode' => $periode])
                ->with('error', 'Periode ini belum Final. Finalkan payroll dulu.');
        }

        $semua  = $svc->semua($periode);
        $sheets = [];

        foreach (config('multipayroll.bagian') as $b => $cfg) {
            $layak = $semua->where('bagian', $b)->whereNull('alasan')->values();
            if ($layak->isEmpty()) {
                continue;
            }

            $isian = ['header_id' => 0, 'tanggal_transfer' => now()->format('Y-m-d'), 'remarks1' => $cfg['remarks1'], 'remarks2' => $this->namaPeriode($periode)];
            $p     = $this->param($periode, $b, $isian);

            $baris = $layak->map(fn ($r, $i) => [
                $i + 1,
                ltrim(MultiPayrollBca::idTransaksi($p['tahun'], $p['tanggal'], $p['bulan'], $p['jam'], $p['kode'], $i + 1), '0'),
                config('multipayroll.kode_bank'),
                '',
                $r['rekening'],
                MultiPayrollBca::bersihNama($r['nama'], $p['maks_nama']),
                (float) $r['jumlah'],
                '', $isian['remarks2'], '', '', '', '',
            ])->all();

            $sheets[] = new MultiPayrollBankSheet(self::NAMA_SHEET[$cfg['kode']] ?? $cfg['remarks1'], $baris);
        }

        $lewat = $semua->filter(fn ($r) => $r['alasan'] !== null)->map(fn ($r) => [
            $r['bagian'] ?? '(tidak dikenali)', $r['nik'], $r['nama'], (float) $r['jumlah'], $r['alasan'],
        ])->values()->all();

        if ($lewat) {
            $sheets[] = new MultiPayrollSheet('Dilewati', ['Bagian', 'NIK', 'Nama', 'Gaji Bersih', 'Alasan'], $lewat, ['D' => '#,##0']);
        }

        if (! $sheets) {
            return redirect()->route('manajemen.payroll.multi.index', ['periode' => $periode])->with('error', 'Tidak ada data untuk diekspor.');
        }

        return Excel::download(new MultiPayrollExport($sheets), "Multi-Payroll-{$periode}.xlsx");
    }

    public function unduh(Request $request, MultiPayrollService $svc)
    {
        $v = $request->validate([
            'periode'          => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'bagian'           => ['required', 'string'],
            'header_id'        => ['required', 'integer', 'min:1', 'max:99999999'],
            'tanggal_transfer' => ['required', 'date_format:Y-m-d'],
            'remarks1'         => ['required', 'string', 'max:50'],
            'remarks2'         => ['required', 'string', 'max:50'],
        ]);

        $balik = ['periode' => $v['periode'], 'bagian' => $v['bagian']];
        $cfg   = config('multipayroll.bagian')[strtoupper($v['bagian'])] ?? null;
        if (! $cfg) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)->with('error', 'Bagian tidak dikenali.');
        }
        $bagian = strtoupper($v['bagian']);

        if (! $svc->periodeSiap($v['periode'])) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)
                ->with('error', 'Periode ini belum Final. Finalkan payroll dulu.');
        }
        if (TrxMultiPayroll::where('header_id', $v['header_id'])->exists()) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)
                ->with('error', "Nomor header {$v['header_id']} sudah pernah dipakai. Pakai nomor lain.");
        }

        $data = $svc->untukBagian($v['periode'], $bagian);

        // Hanya karyawan yang dicentang di pratinjau yang ikut file (yang ditahan dilewati)
        $ikut = array_map('strval', (array) $request->input('ikut', []));
        $data['layak'] = $data['layak']->filter(fn ($r) => in_array((string) $r['id_absen'], $ikut, true))->values();
        $data['total'] = (int) $data['layak']->sum('jumlah');

        if ($data['layak']->isEmpty()) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)
                ->with('error', 'Tidak ada karyawan yang dipilih untuk ditransfer.');
        }

        $p = $this->param($v['periode'], $bagian, $v);

        try {
            $txt = MultiPayrollBca::buat($p, $data['layak']->map(fn ($r) => [
                'rekening' => $r['rekening'], 'nama' => $r['nama'], 'jumlah' => $r['jumlah'],
            ])->all());
        } catch (InvalidArgumentException $e) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)->with('error', $e->getMessage());
        }

        $namaFile = $this->namaFile($v['header_id'], $v['remarks2'], $v['remarks1']);

        try {
            TrxMultiPayroll::create([
                'header_id'        => $v['header_id'],
                'periode'          => $v['periode'],
                'bagian'           => $bagian,
                'kode'             => $cfg['kode'],
                'tanggal_transfer' => $v['tanggal_transfer'],
                'remarks1'         => $v['remarks1'],
                'remarks2'         => $v['remarks2'],
                'jumlah_baris'     => $data['layak']->count(),
                'total'            => $data['total'],
                'nama_file'        => $namaFile,
                'dibuat_oleh'      => $request->user()?->name,
            ]);
        } catch (QueryException $e) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)
                ->with('error', 'Nomor header sudah dipakai. Muat ulang halaman lalu coba lagi.');
        }

        return response()->streamDownload(function () use ($txt) {
            echo $txt;
        }, $namaFile, ['Content-Type' => 'text/plain; charset=US-ASCII']);
    }

    /**
     * Berkas XML BP21 (PPh 21) untuk satu bagian: penghasilan = gaji bersih yang ditransfer,
     * tarif = TER menurut status PTKP. Karyawan yang ikut sama dengan yang layak di file Multi Payroll.
     */
    public function xml(Request $request, MultiPayrollService $svc, Bp21Penyusun $penyusun)
    {
        $v = $request->validate([
            'periode'            => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'bagian'             => ['required', 'string'],
            'nomor_dokumen'      => ['required', 'string', 'max:50'],
            'tanggal_dokumen'    => ['required', 'date_format:Y-m-d'],
            'tanggal_pemotongan' => ['required', 'date_format:Y-m-d'],
            'pilih'              => ['nullable', 'boolean'],
            'ikut'               => ['nullable', 'array'],
            'ikut.*'             => ['string', 'max:50'],
        ]);

        $balik  = ['periode' => $v['periode'], 'bagian' => $v['bagian']];
        $bagian = strtoupper($v['bagian']);
        $cfg    = config('multipayroll.bagian')[$bagian] ?? null;

        if (! $cfg) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)->with('error', 'Bagian tidak dikenali.');
        }
        if (! $svc->periodeSiap($v['periode'])) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)->with('error', 'Periode ini belum Final. Finalkan payroll dulu.');
        }
        if (substr($v['periode'], 5, 2) === '12') {
            return redirect()->route('manajemen.payroll.multi.index', $balik)
                ->with('error', 'Masa pajak Desember memakai perhitungan tahunan, bukan tarif TER. Bagian ini belum didukung.');
        }

        $layak = $svc->untukBagian($v['periode'], $bagian)['layak'];
        if ($layak->isEmpty()) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)->with('error', 'Tidak ada karyawan layak pada bagian ini.');
        }

        // Hanya karyawan yang dicentang di pratinjau (sama seperti file TXT) yang masuk XML
        if ($request->boolean('pilih')) {
            $ikut  = array_map('strval', (array) $request->input('ikut', []));
            $layak = $layak->filter(fn ($r) => in_array((string) $r['id_absen'], $ikut, true))->values();

            if ($layak->isEmpty()) {
                return redirect()->route('manajemen.payroll.multi.index', $balik)
                    ->with('error', 'Tidak ada karyawan yang dicentang untuk XML BP21.');
            }
        }

        $dataPajak = MstKaryawan::whereIn('nik', $layak->pluck('nik')->filter()->unique()->all())->get()
            ->mapWithKeys(fn ($k) => [(string) $k->nik => ['npwp_nik' => $k->npwp_nik, 'status_ptkp' => $k->status_ptkp]])
            ->all();

        $hasil = $penyusun->susun($v['periode'], $layak, $dataPajak, [
            'nomor_dokumen'      => $v['nomor_dokumen'],
            'tanggal_dokumen'    => $v['tanggal_dokumen'],
            'tanggal_pemotongan' => $v['tanggal_pemotongan'],
            'kode_objek'         => config('pajak.kode_objek'),
            'fasilitas'          => config('pajak.fasilitas'),
            'dokumen'            => config('pajak.dokumen'),
        ]);

        if ($hasil['masalah']) {
            $daftar = implode('; ', array_slice($hasil['masalah'], 0, 8)) . (count($hasil['masalah']) > 8 ? '; dan ' . (count($hasil['masalah']) - 8) . ' lainnya' : '');

            return redirect()->route('manajemen.payroll.multi.index', $balik)
                ->with('error', count($hasil['masalah']) . " karyawan belum lengkap data pajaknya, XML tidak dibuat. {$daftar}.");
        }

        try {
            $xml = (new Bp21Xml)->buat(['tin' => config('pajak.npwp_pemotong')], $hasil['baris']);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('manajemen.payroll.multi.index', $balik)->with('error', $e->getMessage());
        }

        $bersih = fn (string $t) => trim(preg_replace('/[^A-Za-z0-9]+/', '-', $t), '-');

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, 'BP21-' . $v['periode'] . '-' . $bersih($cfg['remarks1']) . '.xml', ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    // ---------------------------------------------------------------------------------------------

    /** Parameter untuk MultiPayrollBca::buat. tanggal/bulan ID = akhir periode gaji; jam = jam saat dibuat. */
    private function param(string $periode, string $bagian, array $isian): array
    {
        $akhir = Carbon::createFromFormat('!Y-m', $periode)->endOfMonth();

        return [
            'header_id'        => (int) $isian['header_id'],
            'tanggal_transfer' => $isian['tanggal_transfer'],
            'corporate_id'     => config('multipayroll.corporate_id'),
            'company_code'     => config('multipayroll.company_code'),
            'rekening_sumber'  => config('multipayroll.rekening_sumber'),
            'kode_bank'        => config('multipayroll.kode_bank'),
            'remarks1'         => $isian['remarks1'],
            'remarks2'         => $isian['remarks2'],
            'kode'             => config('multipayroll.bagian')[$bagian]['kode'],
            'tahun'            => (int) $akhir->format('Y'),
            'tanggal'          => (int) $akhir->format('d'),
            'bulan'            => (int) $akhir->format('m'),
            'jam'              => (int) now()->format('H'),
            'maks_nama'        => (int) config('multipayroll.maks_nama', 70),
        ];
    }

    /** Contoh: 73.MAT-AGUSTUS-2026-AC.txt */
    private function namaFile(int $headerId, string $remarks2, string $remarks1): string
    {
        $bersih = fn (string $t) => trim(preg_replace('/[^A-Za-z0-9]+/', '-', $t), '-');

        return sprintf('%d.MAT-%s-%s.txt', $headerId, $bersih($remarks2), $bersih($remarks1));
    }

    private function namaPeriode(string $periode): string
    {
        return mb_strtoupper(Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y'));
    }

    private function bagian(Request $request): string
    {
        $b = strtoupper(trim((string) $request->input('bagian')));
        $daftar = array_keys(config('multipayroll.bagian'));

        return in_array($b, $daftar, true) ? $b : $daftar[0];
    }

    private function periode(Request $request): string
    {
        $p = (string) $request->input('periode');

        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $p) ? $p : now()->format('Y-m');
    }
}
