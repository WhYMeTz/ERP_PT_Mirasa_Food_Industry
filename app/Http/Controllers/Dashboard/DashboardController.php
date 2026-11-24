<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $bolehBeli  = $user->canAccessTerima() || $user->canAccessPo() || $user->canAccessHutang();
        $bolehJual  = $user->canAccessSo();
        $bolehProd  = $user->canAccessProduksi();

        if (!$bolehBeli && !$bolehJual && !$bolehProd) {
            return redirect()->route('gudang.stok.index');
        }

        // Periode: default 1 Januari tahun berjalan s/d hari ini
        $today = Carbon::today();
        $parse = function (?string $v, Carbon $default) {
            try { return $v ? Carbon::createFromFormat('Y-m-d', $v)->startOfDay() : $default; }
            catch (\Throwable $e) { return $default; }
        };
        $awal  = $parse($request->input('dari'),   $today->copy()->startOfYear());
        $akhir = $parse($request->input('sampai'), $today->copy());
        if ($akhir->lt($awal)) { [$awal, $akhir] = [$akhir, $awal]; }
        if ($awal->diffInDays($akhir) > 366 * 5) { $awal = $akhir->copy()->subYears(5); }

        $pabrik = strtoupper((string) $request->input('pabrik', ''));
        if (!in_array($pabrik, ['BARAT', 'TIMUR'], true)) {
            $pabrik = '';
        }

        // Rentang pendek (<= 62 hari) ditampilkan harian, selebihnya bulanan
        $harian = $awal->diffInDays($akhir) <= 62;
        $fmtSql = $harian ? 'YYYY-MM-DD' : 'YYYY-MM';

        $keys = [];
        $labels = [];
        if ($harian) {
            for ($d = $awal->copy(); $d->lte($akhir); $d->addDay()) {
                $keys[]   = $d->format('Y-m-d');
                $labels[] = $d->translatedFormat('d M');
            }
        } else {
            for ($d = $awal->copy()->startOfMonth(); $d->lte($akhir); $d->addMonth()) {
                $keys[]   = $d->format('Y-m');
                $labels[] = $d->translatedFormat('M Y');
            }
        }
        $isi = fn(array $rows, string $col = 'total') =>
            array_map(fn($k) => round((float) ($rows[$k][$col] ?? 0), 2), $keys);

        $data = ['labels' => $labels];
        $kartu = [];

        // ---------- PEMBELIAN (barang diterima / GRN) ----------
        if ($bolehBeli) {
            $rows = DB::table('dat_terima_hdr')
                ->where('deleted_st', false)
                ->whereNotIn(DB::raw('UPPER(COALESCE(status_cd, \'\'))'), ['VOID', 'CANCELLED', 'BATAL'])
                ->whereDate('terima_tgl', '>=', $awal->toDateString())
                ->whereDate('terima_tgl', '<=', $akhir->toDateString())
                ->selectRaw("to_char(terima_tgl, '{$fmtSql}') as ym, SUM(total_tagihan) as total, COUNT(*) as dok")
                ->groupBy('ym')->get()->keyBy('ym')->map(fn($r) => (array) $r)->all();

            $data['pembelian'] = ['total' => $isi($rows), 'dok' => $isi($rows, 'dok')];
            $kartu['pembelian'] = [
                'nilai' => array_sum($data['pembelian']['total']),
                'dok'   => (int) array_sum($data['pembelian']['dok']),
            ];
        }

        // ---------- PENJUALAN (omzet; aturan sama dengan Laporan Penjualan) ----------
        if ($bolehJual) {
            $rows = DB::table('dat_so_hdr')
                ->where('deleted_st', false)
                ->where('status_cd', '!=', 'CANCELLED')
                ->whereDate('so_tgl', '>=', $awal->toDateString())
                ->whereDate('so_tgl', '<=', $akhir->toDateString())
                ->selectRaw("to_char(so_tgl, '{$fmtSql}') as ym,
                    SUM(CASE WHEN (customer_po_no LIKE 'PENJUALAN LANGSUNG%' OR so_no LIKE 'DIR-%') THEN 0 ELSE total_tagihan END) as po,
                    SUM(CASE WHEN (customer_po_no LIKE 'PENJUALAN LANGSUNG%' OR so_no LIKE 'DIR-%') THEN total_tagihan ELSE 0 END) as langsung,
                    COUNT(*) as dok")
                ->groupBy('ym')->get()->keyBy('ym')->map(fn($r) => (array) $r)->all();

            $data['penjualan'] = [
                'po'       => $isi($rows, 'po'),
                'langsung' => $isi($rows, 'langsung'),
            ];
            $kartu['penjualan'] = [
                'nilai' => array_sum($data['penjualan']['po']) + array_sum($data['penjualan']['langsung']),
                'dok'   => (int) array_sum($isi($rows, 'dok')),
            ];
        }

        // ---------- HASIL PRODUKSI & KURVA HPP (WIP) ----------
        if ($bolehProd) {
            $q = DB::table('dat_produksi_hdr')
                ->where('deleted_st', false)
                ->whereDate('produksi_tgl', '>=', $awal->toDateString())
                ->whereDate('produksi_tgl', '<=', $akhir->toDateString());
            if ($pabrik !== '') {
                $pabrik === 'BARAT'
                    ? $q->where(fn($w) => $w->where('lokasi_pabrik', 'BARAT')->orWhereNull('lokasi_pabrik'))
                    : $q->where('lokasi_pabrik', 'TIMUR');
            }

            $rows = $q->selectRaw("to_char(produksi_tgl, '{$fmtSql}') as ym,
                    SUM(total_wip_qty) as kg,
                    COUNT(*) as dok,
                    SUM(CASE WHEN hpp_per_kg > 0 THEN hpp_per_kg * total_wip_qty ELSE 0 END) as hpp_x_kg,
                    SUM(CASE WHEN hpp_per_kg > 0 THEN total_wip_qty ELSE 0 END) as kg_hpp,
                    MIN(CASE WHEN hpp_per_kg > 0 THEN hpp_per_kg END) as hpp_min,
                    MAX(CASE WHEN hpp_per_kg > 0 THEN hpp_per_kg END) as hpp_max")
                ->groupBy('ym')->get()->keyBy('ym')->map(fn($r) => (array) $r)->all();

            // HPP rata-rata tertimbang kg; bulan tanpa HPP -> null (garis putus, bukan 0)
            $hppAvg = $hppMin = $hppMax = [];
            foreach ($keys as $k) {
                $kgHpp = (float) ($rows[$k]['kg_hpp'] ?? 0);
                $hppAvg[] = $kgHpp > 0 ? round($rows[$k]['hpp_x_kg'] / $kgHpp, 2) : null;
                $hppMin[] = isset($rows[$k]['hpp_min']) ? round((float) $rows[$k]['hpp_min'], 2) : null;
                $hppMax[] = isset($rows[$k]['hpp_max']) ? round((float) $rows[$k]['hpp_max'], 2) : null;
            }

            $data['produksi'] = ['kg' => $isi($rows, 'kg')];
            $data['hpp']      = ['avg' => $hppAvg, 'min' => $hppMin, 'max' => $hppMax];

            $hppValid = array_values(array_filter($hppAvg, fn($v) => $v !== null));
            $kartu['produksi'] = [
                'kg'  => array_sum($data['produksi']['kg']),
                'dok' => (int) array_sum($isi($rows, 'dok')),
            ];
            $kartu['hpp'] = [
                'terakhir' => $hppValid ? end($hppValid) : null,
                'rata'     => $hppValid ? round(array_sum($hppValid) / count($hppValid), 2) : null,
            ];
        }

        return view('dashboard.index', [
            'data'       => $data,
            'kartu'      => $kartu,
            'dari'       => $awal->toDateString(),
            'sampai'     => $akhir->toDateString(),
            'harian'     => $harian,
            'pabrik'     => $pabrik,
            'periode'    => $awal->translatedFormat('d M Y') . ' – ' . $akhir->translatedFormat('d M Y'),
            'bolehBeli'  => $bolehBeli,
            'bolehJual'  => $bolehJual,
            'bolehProd'  => $bolehProd,
        ]);
    }
}
