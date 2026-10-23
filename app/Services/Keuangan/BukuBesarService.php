<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatJurnalDtl;
use App\Models\Keuangan\MstCoa;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class BukuBesarService
{
    /**
     * Dapatkan daftar akun COA untuk panel navigasi Buku Besar
     */
    public function getCoaAccountsList(?string $search = null, ?string $kategori = null, ?string $month = null): Collection
    {
        $query = MstCoa::where('deleted_st', false)
            ->where('active_st', true);

        if (!empty($search)) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('kode_akun', 'ILIKE', "%{$s}%")
                  ->orWhere('nama_akun', 'ILIKE', "%{$s}%")
                  ->orWhere('sub_kategori', 'ILIKE', "%{$s}%");
            });
        }

        if (!empty($kategori) && $kategori !== 'ALL') {
            $query->where('kategori_akun', $kategori);
        }

        $accounts = $query->orderBy('kode_akun', 'asc')->get();

        // Hitung total mutasi per akun untuk badge transaksi
        $monthDate = $month ? Carbon::parse($month . '-01') : now();
        $start = $monthDate->copy()->startOfMonth()->toDateString();
        $end = $monthDate->copy()->endOfMonth()->toDateString();

        $mutationCounts = DatJurnalDtl::where('deleted_st', false)
            ->whereHas('header', function ($hq) use ($start, $end) {
                $hq->where('deleted_st', false)
                   ->whereBetween('tgl_jurnal', [$start, $end]);
            })
            ->selectRaw('coa_id, COUNT(*) as total_count')
            ->groupBy('coa_id')
            ->pluck('total_count', 'coa_id')
            ->toArray();

        return $accounts->map(function ($acc) use ($mutationCounts) {
            $acc->mutasi_count = $mutationCounts[$acc->coa_id] ?? 0;
            return $acc;
        });
    }

    /**
     * Dapatkan detail buku besar per akun lengkap dengan running balance
     */
    public function getLedgerDetail(int $coaId, ?string $startDate = null, ?string $endDate = null): array
    {
        $coa = MstCoa::where('deleted_st', false)->findOrFail($coaId);

        $now = now();
        $start = $startDate ?: $now->copy()->startOfMonth()->toDateString();
        $end = $endDate ?: $now->copy()->endOfMonth()->toDateString();

        $isDebetNormal = ($coa->saldo_normal === 'DEBET');

        // 1. Hitung Saldo Awal Periode (Sebelum $start)
        $priorMutations = DatJurnalDtl::where('coa_id', $coaId)
            ->where('deleted_st', false)
            ->whereHas('header', function ($hq) use ($start) {
                $hq->where('deleted_st', false)
                   ->where('tgl_jurnal', '<', $start);
            })
            ->selectRaw('SUM(debet) as total_debet, SUM(kredit) as total_kredit')
            ->first();

        $priorDebet = (float)($priorMutations?->total_debet ?? 0);
        $priorKredit = (float)($priorMutations?->total_kredit ?? 0);

        $baseSaldoAwal = (float)($coa->saldo_awal ?? 0);
        if ($isDebetNormal) {
            $saldoAwalPeriode = $baseSaldoAwal + ($priorDebet - $priorKredit);
        } else {
            $saldoAwalPeriode = $baseSaldoAwal + ($priorKredit - $priorDebet);
        }

        // 2. Ambil baris mutasi dalam rentang [$start, $end]
        $mutations = DatJurnalDtl::with(['header'])
            ->join('dat_jurnal_hdr', 'dat_jurnal_dtl.jurnal_id', '=', 'dat_jurnal_hdr.jurnal_id')
            ->where('dat_jurnal_dtl.coa_id', $coaId)
            ->where('dat_jurnal_dtl.deleted_st', false)
            ->where('dat_jurnal_hdr.deleted_st', false)
            ->whereBetween('dat_jurnal_hdr.tgl_jurnal', [$start, $end])
            ->orderBy('dat_jurnal_hdr.tgl_jurnal', 'asc')
            ->orderBy('dat_jurnal_hdr.jurnal_id', 'asc')
            ->orderBy('dat_jurnal_dtl.jurnaldtl_id', 'asc')
            ->select('dat_jurnal_dtl.*')
            ->get();

        // 3. Hitung Running Balance baris demi baris
        $runningBalance = $saldoAwalPeriode;
        $totalDebetPeriode = 0;
        $totalKreditPeriode = 0;

        $ledgerLines = [];
        foreach ($mutations as $dtl) {
            $debet = (float)$dtl->debet;
            $kredit = (float)$dtl->kredit;

            $totalDebetPeriode += $debet;
            $totalKreditPeriode += $kredit;

            if ($isDebetNormal) {
                $runningBalance += ($debet - $kredit);
            } else {
                $runningBalance += ($kredit - $debet);
            }

            $hdr = $dtl->header;

            $ledgerLines[] = [
                'dtl_id'          => $dtl->jurnaldtl_id,
                'jurnal_id'       => $dtl->jurnal_id,
                'tgl_jurnal'      => $hdr?->tgl_jurnal,
                'jurnal_no'       => $hdr?->jurnal_no,
                'ref_no'          => $hdr?->ref_no,
                'ref_id'          => $hdr?->ref_id,
                'tipe_transaksi'  => $hdr?->tipe_transaksi,
                'keterangan'      => $dtl->keterangan ?: ($hdr?->keterangan ?? '-'),
                'debet'           => $debet,
                'kredit'          => $kredit,
                'saldo_berjalan'  => $runningBalance,
            ];
        }

        $saldoAkhirPeriode = $runningBalance;

        // 4. Hitung Grafik Saldo Harian
        $chartData = $this->calculateDailyChart($coa, $saldoAwalPeriode, $start, $end);

        return [
            'coa'                 => $coa,
            'start_date'          => $start,
            'end_date'            => $end,
            'saldo_awal_periode'  => $saldoAwalPeriode,
            'total_debet_periode' => $totalDebetPeriode,
            'total_kredit_periode'=> $totalKreditPeriode,
            'saldo_akhir_periode' => $saldoAkhirPeriode,
            'lines'               => $ledgerLines,
            'chart'               => $chartData,
        ];
    }

    /**
     * Hitung tren saldo harian dari start ke end
     */
    protected function calculateDailyChart(MstCoa $coa, float $initialSaldo, string $start, string $end): array
    {
        $isDebetNormal = ($coa->saldo_normal === 'DEBET');

        // Ambil pergerakan harian
        $dailySums = DatJurnalDtl::join('dat_jurnal_hdr', 'dat_jurnal_dtl.jurnal_id', '=', 'dat_jurnal_hdr.jurnal_id')
            ->where('dat_jurnal_dtl.coa_id', $coa->coa_id)
            ->where('dat_jurnal_dtl.deleted_st', false)
            ->where('dat_jurnal_hdr.deleted_st', false)
            ->whereBetween('dat_jurnal_hdr.tgl_jurnal', [$start, $end])
            ->selectRaw('dat_jurnal_hdr.tgl_jurnal as date_str, SUM(dat_jurnal_dtl.debet) as day_debet')
            ->groupBy('dat_jurnal_hdr.tgl_jurnal')
            ->pluck('day_debet', 'date_str')
            ->toArray();

        $dailyKreditSums = DatJurnalDtl::join('dat_jurnal_hdr', 'dat_jurnal_dtl.jurnal_id', '=', 'dat_jurnal_hdr.jurnal_id')
            ->where('dat_jurnal_dtl.coa_id', $coa->coa_id)
            ->where('dat_jurnal_dtl.deleted_st', false)
            ->where('dat_jurnal_hdr.deleted_st', false)
            ->whereBetween('dat_jurnal_hdr.tgl_jurnal', [$start, $end])
            ->selectRaw('dat_jurnal_hdr.tgl_jurnal as date_str, SUM(dat_jurnal_dtl.kredit) as day_kredit')
            ->groupBy('dat_jurnal_hdr.tgl_jurnal')
            ->pluck('day_kredit', 'date_str')
            ->toArray();

        $period = CarbonPeriod::create($start, $end);
        $labels = [];
        $values = [];
        $currSaldo = $initialSaldo;

        foreach ($period as $date) {
            $dStr = $date->toDateString();
            $dDebet = (float)($dailySums[$dStr] ?? 0);
            $dKredit = (float)($dailyKreditSums[$dStr] ?? 0);

            if ($isDebetNormal) {
                $currSaldo += ($dDebet - $dKredit);
            } else {
                $currSaldo += ($dKredit - $dDebet);
            }

            $labels[] = $date->format('d M');
            $values[] = round($currSaldo, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
