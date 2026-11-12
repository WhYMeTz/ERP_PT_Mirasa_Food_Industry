<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatJurnalDtl;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanKeuanganService
{
    /**
     * Hitung mutasi debet & kredit per akun dalam rentang tanggal
     */
    protected function getAccountPeriodMutations(string $startDate, string $endDate): Collection
    {
        return DatJurnalDtl::join('dat_jurnal_hdr', 'dat_jurnal_dtl.jurnal_id', '=', 'dat_jurnal_hdr.jurnal_id')
            ->where('dat_jurnal_dtl.deleted_st', false)
            ->where('dat_jurnal_hdr.deleted_st', false)
            ->whereBetween('dat_jurnal_hdr.tgl_jurnal', [$startDate, $endDate])
            ->selectRaw('dat_jurnal_dtl.coa_id, SUM(dat_jurnal_dtl.debet) as total_debet, SUM(dat_jurnal_dtl.kredit) as total_kredit')
            ->groupBy('dat_jurnal_dtl.coa_id')
            ->get()
            ->keyBy('coa_id');
    }

    /**
     * Hitung saldo kumulatif akun sampai tanggal tertentu ($asOfDate)
     */
    protected function getAccountBalancesAsOf(string $asOfDate): Collection
    {
        $allCoa = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->get();

        $mutations = DatJurnalDtl::join('dat_jurnal_hdr', 'dat_jurnal_dtl.jurnal_id', '=', 'dat_jurnal_hdr.jurnal_id')
            ->where('dat_jurnal_dtl.deleted_st', false)
            ->where('dat_jurnal_hdr.deleted_st', false)
            ->where('dat_jurnal_hdr.tgl_jurnal', '<=', $asOfDate)
            ->selectRaw('dat_jurnal_dtl.coa_id, SUM(dat_jurnal_dtl.debet) as sum_debet, SUM(dat_jurnal_dtl.kredit) as sum_kredit')
            ->groupBy('dat_jurnal_dtl.coa_id')
            ->get()
            ->keyBy('coa_id');

        return $allCoa->mapWithKeys(function ($coa) use ($mutations) {
            $mut = $mutations->get($coa->coa_id);
            $debet = (float)($mut?->sum_debet ?? 0);
            $kredit = (float)($mut?->sum_kredit ?? 0);
            $saldoAwal = (float)($coa->saldo_awal ?? 0);

            if ($coa->saldo_normal === 'DEBET') {
                $balance = $saldoAwal + ($debet - $kredit);
            } else {
                $balance = $saldoAwal + ($kredit - $debet);
            }

            return [$coa->coa_id => [
                'coa'     => $coa,
                'balance' => $balance,
                'debet'   => $debet,
                'kredit'  => $kredit,
            ]];
        });
    }

    /**
     * 1. LAPORAN LABA RUGI KOMPREHENSIF (INCOME STATEMENT)
     */
    public function getLabaRugi(string $startDate, string $endDate): array
    {
        $mutations = $this->getAccountPeriodMutations($startDate, $endDate);
        $accounts = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->whereIn('kategori_akun', ['PENDAPATAN', 'HPP', 'BEBAN'])
            ->orderBy('kode_akun', 'asc')
            ->get();

        $pendapatanOperasional = [];
        $totalPendapatanOps = 0;

        $hppItems = [];
        $totalHpp = 0;

        $bebanOperasional = [];
        $totalBebanOps = 0;

        $pendapatanNonOps = [];
        $totalPendapatanNonOps = 0;

        $bebanNonOps = [];
        $totalBebanNonOps = 0;

        foreach ($accounts as $acc) {
            $mut = $mutations->get($acc->coa_id);
            $debet = (float)($mut?->total_debet ?? 0);
            $kredit = (float)($mut?->total_kredit ?? 0);

            // Lewati akun jika tidak ada mutasi di periode ini dan saldo awal 0
            if ($debet == 0 && $kredit == 0) {
                continue;
            }

            $kode = (string)$acc->kode_akun;
            $nama = $acc->nama_akun;

            // A. PENDAPATAN (4xxx)
            if ($acc->kategori_akun === 'PENDAPATAN' || str_starts_with($kode, '4')) {
                if ($kode === '4200' || str_contains(strtolower($nama), 'lain-lain') || str_contains(strtolower($nama), 'non operasional')) {
                    $nominal = ($acc->saldo_normal === 'KREDIT') ? ($kredit - $debet) : ($debet - $kredit);
                    $pendapatanNonOps[] = [
                        'coa_id'    => $acc->coa_id,
                        'kode_akun' => $kode,
                        'nama_akun' => $nama,
                        'nominal'   => $nominal,
                    ];
                    $totalPendapatanNonOps += $nominal;
                } else {
                    // Pendapatan usaha utama (retur bernilai kontra / mengurangi)
                    $nominal = ($acc->saldo_normal === 'KREDIT') ? ($kredit - $debet) : -($debet - $kredit);
                    $pendapatanOperasional[] = [
                        'coa_id'    => $acc->coa_id,
                        'kode_akun' => $kode,
                        'nama_akun' => $nama,
                        'nominal'   => $nominal,
                    ];
                    $totalPendapatanOps += $nominal;
                }
            }
            // B. HARGA POKOK PENJUALAN / HPP (5xxx)
            elseif ($acc->kategori_akun === 'HPP' || str_starts_with($kode, '5')) {
                $nominal = ($acc->saldo_normal === 'DEBET') ? ($debet - $kredit) : ($kredit - $debet);
                $hppItems[] = [
                    'coa_id'    => $acc->coa_id,
                    'kode_akun' => $kode,
                    'nama_akun' => $nama,
                    'nominal'   => $nominal,
                ];
                $totalHpp += $nominal;
            }
            // C. BEBAN OPERASIONAL & NON-OPS (6xxx)
            elseif ($acc->kategori_akun === 'BEBAN' || str_starts_with($kode, '6')) {
                $nominal = ($acc->saldo_normal === 'DEBET') ? ($debet - $kredit) : ($kredit - $debet);

                if (in_array($kode, ['6109', '6110']) || str_contains(strtolower($nama), 'bank') || str_contains(strtolower($nama), 'lain-lain')) {
                    $bebanNonOps[] = [
                        'coa_id'    => $acc->coa_id,
                        'kode_akun' => $kode,
                        'nama_akun' => $nama,
                        'nominal'   => $nominal,
                    ];
                    $totalBebanNonOps += $nominal;
                } else {
                    $bebanOperasional[] = [
                        'coa_id'    => $acc->coa_id,
                        'kode_akun' => $kode,
                        'nama_akun' => $nama,
                        'nominal'   => $nominal,
                    ];
                    $totalBebanOps += $nominal;
                }
            }
        }

        // Kalkulasi Keuangan
        $labaKotor = $totalPendapatanOps - $totalHpp;
        $labaOperasional = $labaKotor - $totalBebanOps;
        $netNonOperasional = $totalPendapatanNonOps - $totalBebanNonOps;
        $labaBersih = $labaOperasional + $netNonOperasional;

        // Persentase Margin terhadap Pendapatan Operasional
        $grossMarginPct = ($totalPendapatanOps > 0) ? round(($labaKotor / $totalPendapatanOps) * 100, 2) : 0;
        $operatingMarginPct = ($totalPendapatanOps > 0) ? round(($labaOperasional / $totalPendapatanOps) * 100, 2) : 0;
        $netMarginPct = ($totalPendapatanOps > 0) ? round(($labaBersih / $totalPendapatanOps) * 100, 2) : 0;

        return [
            'start_date'               => $startDate,
            'end_date'                 => $endDate,
            'pendapatan_operasional'   => $pendapatanOperasional,
            'total_pendapatan_ops'     => $totalPendapatanOps,
            'hpp_items'                => $hppItems,
            'total_hpp'                => $totalHpp,
            'laba_kotor'               => $labaKotor,
            'beban_operasional'        => $bebanOperasional,
            'total_beban_ops'          => $totalBebanOps,
            'laba_operasional'         => $labaOperasional,
            'pendapatan_non_ops'       => $pendapatanNonOps,
            'total_pendapatan_non_ops' => $totalPendapatanNonOps,
            'beban_non_ops'            => $bebanNonOps,
            'total_beban_non_ops'      => $totalBebanNonOps,
            'net_non_operasional'      => $netNonOperasional,
            'laba_bersih'              => $labaBersih,
            'gross_margin_pct'         => $grossMarginPct,
            'operating_margin_pct'     => $operatingMarginPct,
            'net_margin_pct'           => $netMarginPct,
        ];
    }

    /**
     * 2. LAPORAN NERACA KEUANGAN (BALANCE SHEET)
     */
    public function getNeraca(string $asOfDate): array
    {
        $accountBalances = $this->getAccountBalancesAsOf($asOfDate);

        // Grouping Akun Aktiva & Pasiva
        $aktivaLancar = [];
        $totalAktivaLancar = 0;

        $aktivaTetap = [];
        $totalAktivaTetap = 0;

        $kewajibanLancar = [];
        $totalKewajibanLancar = 0;

        $ekuitasItems = [];
        $totalEkuitasTercatat = 0;

        // Ambil data Laba Bersih kumulatif s.d. tanggal cut-off
        $earliestDate = '2000-01-01';
        $labaRugiKumulatif = $this->getLabaRugi($earliestDate, $asOfDate);
        $labaPeriodeBerjalan = (float)$labaRugiKumulatif['laba_bersih'];

        foreach ($accountBalances as $coaId => $item) {
            $coa = $item['coa'];
            $balance = (float)$item['balance'];
            $kode = (string)$coa->kode_akun;
            $nama = $coa->nama_akun;
            $kategori = $coa->kategori_akun;
            $subKategori = $coa->sub_kategori ?? '';

            // Lewati akun yang saldonya 0
            if (abs($balance) < 0.01) {
                continue;
            }

            // 1. AKTIVA (Kode 1xxx)
            if ($kategori === 'AKTIVA' || str_starts_with($kode, '1')) {
                // Aktiva Tetap: 12xx (Tanah, Mesin)
                if (str_starts_with($kode, '12')) {
                    $aktivaTetap[] = [
                        'coa_id'       => $coaId,
                        'kode_akun'    => $kode,
                        'nama_akun'    => $nama,
                        'sub_kategori' => $subKategori ?: 'Aset Tetap Pabrik',
                        'nominal'      => $balance,
                    ];
                    $totalAktivaTetap += $balance;
                } else {
                    // Aktiva Lancar: Kas, Bank, Piutang, Pajak Dibayar Dimuka, Persediaan
                    $subLabel = $subKategori;
                    if (!$subLabel) {
                        if ($coa->is_kas_bank || str_starts_with($kode, '110')) {
                            $subLabel = 'Kas & Setara Kas';
                        } elseif (str_starts_with($kode, '112')) {
                            $subLabel = 'Piutang Usaha';
                        } elseif (str_starts_with($kode, '113')) {
                            $subLabel = 'Pajak Dibayar Dimuka';
                        } elseif (str_starts_with($kode, '115')) {
                            $subLabel = 'Persediaan Barang & Bahan';
                        } else {
                            $subLabel = 'Aset Lancar Lainnya';
                        }
                    }

                    $aktivaLancar[] = [
                        'coa_id'       => $coaId,
                        'kode_akun'    => $kode,
                        'nama_akun'    => $nama,
                        'sub_kategori' => $subLabel,
                        'nominal'      => $balance,
                    ];
                    $totalAktivaLancar += $balance;
                }
            }
            // 2. KEWAJIBAN / HUTANG (Kode 2xxx)
            elseif (in_array($kategori, ['PASIVA', 'KEWAJIBAN', 'HUTANG']) || str_starts_with($kode, '2')) {
                $subLabel = $subKategori;
                if (!$subLabel) {
                    if ($kode === '2100') {
                        $subLabel = 'Hutang Usaha (Supplier)';
                    } elseif ($kode === '2104') {
                        $subLabel = 'Uang Muka Pembelian';
                    } elseif (in_array($kode, ['2103', '2105', '2106', '2201'])) {
                        $subLabel = 'Hutang Pajak';
                    } else {
                        $subLabel = 'Kewajiban Jangka Pendek';
                    }
                }

                $kewajibanLancar[] = [
                    'coa_id'       => $coaId,
                    'kode_akun'    => $kode,
                    'nama_akun'    => $nama,
                    'sub_kategori' => $subLabel,
                    'nominal'      => $balance,
                ];
                $totalKewajibanLancar += $balance;
            }
            // 3. EKUITAS / MODAL (Kode 3xxx)
            elseif (in_array($kategori, ['MODAL', 'EKUITAS']) || str_starts_with($kode, '3')) {
                // Prive bersifat kontra ekuitas (mengurangi modal)
                $nominal = ($kode === '3110' || str_contains(strtolower($nama), 'prive')) ? -$balance : $balance;

                $ekuitasItems[] = [
                    'coa_id'       => $coaId,
                    'kode_akun'    => $kode,
                    'nama_akun'    => $nama,
                    'sub_kategori' => $subKategori ?: 'Ekuitas Pemilik',
                    'nominal'      => $nominal,
                ];
                $totalEkuitasTercatat += $nominal;
            }
        }

        // Total Aktiva & Total Pasiva
        $totalAktiva = $totalAktivaLancar + $totalAktivaTetap;

        // Total Ekuitas Akhir = Ekuitas Tercatat + Laba Bersih Kumulatif Berjalan
        $totalEkuitasAkhir = $totalEkuitasTercatat + $labaPeriodeBerjalan;
        $totalPasiva = $totalKewajibanLancar + $totalEkuitasAkhir;

        $selisih = round($totalAktiva - $totalPasiva, 2);
        $isBalanced = (abs($selisih) < 1.0);

        return [
            'as_of_date'            => $asOfDate,
            'aktiva_lancar'         => $aktivaLancar,
            'total_aktiva_lancar'   => $totalAktivaLancar,
            'aktiva_tetap'          => $aktivaTetap,
            'total_aktiva_tetap'    => $totalAktivaTetap,
            'total_aktiva'          => $totalAktiva,
            'kewajiban_lancar'      => $kewajibanLancar,
            'total_kewajiban'       => $totalKewajibanLancar,
            'ekuitas_items'         => $ekuitasItems,
            'laba_periode_berjalan' => $labaPeriodeBerjalan,
            'total_ekuitas'         => $totalEkuitasAkhir,
            'total_pasiva'          => $totalPasiva,
            'selisih'               => $selisih,
            'is_balanced'           => $isBalanced,
        ];
    }

    /**
     * 3. LAPORAN NERACA SALDO (TRIAL BALANCE)
     */
    public function getNeracaSaldo(string $startDate, string $endDate): array
    {
        $allCoa = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('kode_akun', 'asc')
            ->get();

        // Mutasi sebelum startDate (untuk Saldo Awal)
        $priorMutations = DatJurnalDtl::join('dat_jurnal_hdr', 'dat_jurnal_dtl.jurnal_id', '=', 'dat_jurnal_hdr.jurnal_id')
            ->where('dat_jurnal_dtl.deleted_st', false)
            ->where('dat_jurnal_hdr.deleted_st', false)
            ->where('dat_jurnal_hdr.tgl_jurnal', '<', $startDate)
            ->selectRaw('dat_jurnal_dtl.coa_id, SUM(dat_jurnal_dtl.debet) as sum_debet, SUM(dat_jurnal_dtl.kredit) as sum_kredit')
            ->groupBy('dat_jurnal_dtl.coa_id')
            ->get()
            ->keyBy('coa_id');

        // Mutasi periode [$startDate, $endDate]
        $periodMutations = $this->getAccountPeriodMutations($startDate, $endDate);

        $rows = [];
        $sumAwalDebet = 0;
        $sumAwalKredit = 0;
        $sumMutasiDebet = 0;
        $sumMutasiKredit = 0;
        $sumAkhirDebet = 0;
        $sumAkhirKredit = 0;

        foreach ($allCoa as $coa) {
            $isDebetNormal = ($coa->saldo_normal === 'DEBET');
            $baseAwal = (float)($coa->saldo_awal ?? 0);

            // 1. Saldo Awal
            $pMut = $priorMutations->get($coa->coa_id);
            $pDebet = (float)($pMut?->sum_debet ?? 0);
            $pKredit = (float)($pMut?->sum_kredit ?? 0);

            if ($isDebetNormal) {
                $saldoAwalNet = $baseAwal + ($pDebet - $pKredit);
                $awalDebet = max(0, $saldoAwalNet);
                $awalKredit = ($saldoAwalNet < 0) ? abs($saldoAwalNet) : 0;
            } else {
                $saldoAwalNet = $baseAwal + ($pKredit - $pDebet);
                $awalKredit = max(0, $saldoAwalNet);
                $awalDebet = ($saldoAwalNet < 0) ? abs($saldoAwalNet) : 0;
            }

            // 2. Mutasi Periode
            $curMut = $periodMutations->get($coa->coa_id);
            $mutDebet = (float)($curMut?->total_debet ?? 0);
            $mutKredit = (float)($curMut?->total_kredit ?? 0);

            // 3. Saldo Akhir
            if ($isDebetNormal) {
                $saldoAkhirNet = $saldoAwalNet + ($mutDebet - $mutKredit);
                $akhirDebet = max(0, $saldoAkhirNet);
                $akhirKredit = ($saldoAkhirNet < 0) ? abs($saldoAkhirNet) : 0;
            } else {
                $saldoAkhirNet = $saldoAwalNet + ($mutKredit - $mutDebet);
                $akhirKredit = max(0, $saldoAkhirNet);
                $akhirDebet = ($saldoAkhirNet < 0) ? abs($saldoAkhirNet) : 0;
            }

            // Lewati akun yang sama sekali tidak ada saldo maupun mutasi
            if ($awalDebet == 0 && $awalKredit == 0 && $mutDebet == 0 && $mutKredit == 0 && $akhirDebet == 0 && $akhirKredit == 0) {
                continue;
            }

            $sumAwalDebet += $awalDebet;
            $sumAwalKredit += $awalKredit;
            $sumMutasiDebet += $mutDebet;
            $sumMutasiKredit += $mutKredit;
            $sumAkhirDebet += $akhirDebet;
            $sumAkhirKredit += $akhirKredit;

            $rows[] = [
                'coa_id'        => $coa->coa_id,
                'kode_akun'     => $coa->kode_akun,
                'nama_akun'     => $coa->nama_akun,
                'kategori_akun' => $coa->kategori_akun,
                'saldo_normal'  => $coa->saldo_normal,
                'awal_debet'    => $awalDebet,
                'awal_kredit'   => $awalKredit,
                'mutasi_debet'  => $mutDebet,
                'mutasi_kredit' => $mutKredit,
                'akhir_debet'   => $akhirDebet,
                'akhir_kredit'  => $akhirKredit,
            ];
        }

        $isAwalBalanced = (round(abs($sumAwalDebet - $sumAwalKredit), 2) < 1.0);
        $isMutasiBalanced = (round(abs($sumMutasiDebet - $sumMutasiKredit), 2) < 1.0);
        $isAkhirBalanced = (round(abs($sumAkhirDebet - $sumAkhirKredit), 2) < 1.0);

        return [
            'start_date'         => $startDate,
            'end_date'           => $endDate,
            'rows'               => $rows,
            'sum_awal_debet'     => $sumAwalDebet,
            'sum_awal_kredit'    => $sumAwalKredit,
            'sum_mutasi_debet'   => $sumMutasiDebet,
            'sum_mutasi_kredit'  => $sumMutasiKredit,
            'sum_akhir_debet'    => $sumAkhirDebet,
            'sum_akhir_kredit'   => $sumAkhirKredit,
            'is_awal_balanced'   => $isAwalBalanced,
            'is_mutasi_balanced' => $isMutasiBalanced,
            'is_akhir_balanced'  => $isAkhirBalanced,
        ];
    }

    /**
     * 4. LAPORAN ARUS KAS (CASH FLOW STATEMENT - METODE LANGSUNG)
     */
    public function getArusKas(string $startDate, string $endDate): array
    {
        // 1. Saldo Awal Kas & Bank (Sebelum $startDate)
        $kasBanks = MstKasBank::where('deleted_st', false)
            ->where('active_st', true)
            ->get();

        $kasBankIds = $kasBanks->pluck('kas_bank_id')->toArray();

        $priorMutasi = DatKasMutasi::whereIn('kas_bank_id', $kasBankIds)
            ->where('deleted_st', false)
            ->where('tgl_mutasi', '<', $startDate)
            ->selectRaw('kas_bank_id, 
                SUM(CASE WHEN tipe_mutasi = \'MASUK\' THEN nominal ELSE 0 END) as sum_masuk,
                SUM(CASE WHEN tipe_mutasi = \'KELUAR\' THEN nominal ELSE 0 END) as sum_keluar')
            ->groupBy('kas_bank_id')
            ->get()
            ->keyBy('kas_bank_id');

        $saldoAwalKasTotal = 0;
        $rekeningBreakdown = [];

        foreach ($kasBanks as $kb) {
            $p = $priorMutasi->get($kb->kas_bank_id);
            $masuk = (float)($p?->sum_masuk ?? 0);
            $keluar = (float)($p?->sum_keluar ?? 0);
            $saldoAwalKb = (float)($kb->saldo_awal ?? 0) + ($masuk - $keluar);
            $saldoAwalKasTotal += $saldoAwalKb;

            $rekeningBreakdown[$kb->kas_bank_id] = [
                'kas_bank'    => $kb,
                'saldo_awal'  => $saldoAwalKb,
                'masuk'       => 0,
                'keluar'      => 0,
                'saldo_akhir' => $saldoAwalKb,
            ];
        }

        // 2. Mutasi Periode Arus Kas Berjalan
        $periodMutasi = DatKasMutasi::whereIn('kas_bank_id', $kasBankIds)
            ->where('deleted_st', false)
            ->whereBetween('tgl_mutasi', [$startDate, $endDate])
            ->orderBy('tgl_mutasi', 'asc')
            ->get();

        $arusOperasiMasuk = [];
        $totalOperasiMasuk = 0;

        $arusOperasiKeluar = [];
        $totalOperasiKeluar = 0;

        $arusInvestasiKeluar = [];
        $totalInvestasiKeluar = 0;

        $arusPendanaanMasuk = [];
        $totalPendanaanMasuk = 0;

        foreach ($periodMutasi as $m) {
            $nom = (float)$m->nominal;
            $jenis = strtoupper((string)$m->jenis_transaksi);
            $ket = $m->keterangan ?: "Transaksi {$m->ref_no}";

            // Update breakdown rekening
            if (isset($rekeningBreakdown[$m->kas_bank_id])) {
                if ($m->tipe_mutasi === 'MASUK') {
                    $rekeningBreakdown[$m->kas_bank_id]['masuk'] += $nom;
                    $rekeningBreakdown[$m->kas_bank_id]['saldo_akhir'] += $nom;
                } else {
                    $rekeningBreakdown[$m->kas_bank_id]['keluar'] += $nom;
                    $rekeningBreakdown[$m->kas_bank_id]['saldo_akhir'] -= $nom;
                }
            }

            if ($m->tipe_mutasi === 'MASUK') {
                if (in_array($jenis, ['SETORAN_MODAL', 'HUTANG_BANK'])) {
                    $arusPendanaanMasuk[] = ['keterangan' => $ket, 'nominal' => $nom];
                    $totalPendanaanMasuk += $nom;
                } else {
                    $arusOperasiMasuk[] = ['keterangan' => $ket, 'nominal' => $nom];
                    $totalOperasiMasuk += $nom;
                }
            } else {
                if (in_array($jenis, ['BELI_ASET', 'INVESTASI'])) {
                    $arusInvestasiKeluar[] = ['keterangan' => $ket, 'nominal' => $nom];
                    $totalInvestasiKeluar += $nom;
                } else {
                    $arusOperasiKeluar[] = ['keterangan' => $ket, 'nominal' => $nom];
                    $totalOperasiKeluar += $nom;
                }
            }
        }

        $netOperasi = $totalOperasiMasuk - $totalOperasiKeluar;
        $netInvestasi = -$totalInvestasiKeluar;
        $netPendanaan = $totalPendanaanMasuk;

        $kenaikanBersihKas = $netOperasi + $netInvestasi + $netPendanaan;
        $saldoAkhirKasTotal = $saldoAwalKasTotal + $kenaikanBersihKas;

        return [
            'start_date'             => $startDate,
            'end_date'               => $endDate,
            'saldo_awal_total'       => $saldoAwalKasTotal,
            'arus_operasi_masuk'     => $arusOperasiMasuk,
            'total_operasi_masuk'    => $totalOperasiMasuk,
            'arus_operasi_keluar'    => $arusOperasiKeluar,
            'total_operasi_keluar'   => $totalOperasiKeluar,
            'net_operasi'            => $netOperasi,
            'arus_investasi_keluar'  => $arusInvestasiKeluar,
            'total_investasi_keluar' => $totalInvestasiKeluar,
            'net_investasi'          => $netInvestasi,
            'arus_pendanaan_masuk'   => $arusPendanaanMasuk,
            'total_pendanaan_masuk'  => $totalPendanaanMasuk,
            'net_pendanaan'          => $netPendanaan,
            'kenaikan_bersih_kas'    => $kenaikanBersihKas,
            'saldo_akhir_total'      => $saldoAkhirKasTotal,
            'rekening_breakdown'     => array_values($rekeningBreakdown),
        ];
    }

    /**
     * 5. RINGKASAN EKSEKUTIF KEUANGAN (EXECUTIVE KPI SUMMARY)
     */
    public function getExecutiveSummary(string $startDate, string $endDate): array
    {
        $labaRugi = $this->getLabaRugi($startDate, $endDate);
        $neraca = $this->getNeraca($endDate);
        $arusKas = $this->getArusKas($startDate, $endDate);

        // Hitung Current Ratio = Total Aktiva Lancar / Total Kewajiban Lancar
        $currentRatio = ($neraca['total_kewajiban'] > 0)
            ? round($neraca['total_aktiva_lancar'] / $neraca['total_kewajiban'], 2)
            : ($neraca['total_aktiva_lancar'] > 0 ? 99.9 : 1.0);

        return [
            'period_label'        => Carbon::parse($startDate)->format('d M Y') . ' — ' . Carbon::parse($endDate)->format('d M Y'),
            'total_pendapatan'    => $labaRugi['total_pendapatan_ops'],
            'total_hpp'           => $labaRugi['total_hpp'],
            'laba_kotor'          => $labaRugi['laba_kotor'],
            'total_beban'         => $labaRugi['total_beban_ops'] + $labaRugi['total_beban_non_ops'],
            'laba_bersih'         => $labaRugi['laba_bersih'],
            'gross_margin_pct'    => $labaRugi['gross_margin_pct'],
            'net_margin_pct'      => $labaRugi['net_margin_pct'],
            'total_aktiva'        => $neraca['total_aktiva'],
            'total_kewajiban'     => $neraca['total_kewajiban'],
            'total_ekuitas'       => $neraca['total_ekuitas'],
            'is_neraca_balanced'  => $neraca['is_balanced'],
            'saldo_kas_akhir'     => $arusKas['saldo_akhir_total'],
            'current_ratio'       => $currentRatio,
        ];
    }
}
