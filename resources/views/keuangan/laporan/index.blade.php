@extends('layouts.app')

@section('title', 'Laporan Keuangan - Executive Hub')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/laporan/laporan.css') }}">
@endpush

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
    <div>
        <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <span>📊</span> <span>Laporan Keuangan Komprehensif</span>
        </h1>
        <p style="font-size: 0.775rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Pusat laporan kinerja akuntansi, laba rugi, neraca posisi keuangan, dan likuiditas kas PT Mirasa Food Industry
        </p>
    </div>
</div>

<div class="laporan-container">
    {{-- Navigasi Tab --}}
    @include('keuangan.laporan.partials.nav-tabs')

    {{-- Filter Bar --}}
    @include('keuangan.laporan.partials.header-filter')

    {{-- Kartu Metrik Eksekutif (Financial KPIs) --}}
    <div class="executive-metric-grid">
        <div class="metric-card">
            <div class="metric-card-label">Pendapatan Usaha</div>
            <div class="metric-card-val val-blue">
                Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}
            </div>
            <div class="metric-card-sub">
                Gross Margin: <strong>{{ $summary['gross_margin_pct'] }}%</strong>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-label">Harga Pokok Penjualan (HPP)</div>
            <div class="metric-card-val">
                Rp {{ number_format($summary['total_hpp'], 0, ',', '.') }}
            </div>
            <div class="metric-card-sub">
                Bahan baku &amp; konversi pabrik
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-label">Laba Bersih Periode</div>
            <div class="metric-card-val {{ $summary['laba_bersih'] >= 0 ? 'val-green' : 'val-red' }}">
                Rp {{ number_format($summary['laba_bersih'], 0, ',', '.') }}
            </div>
            <div class="metric-card-sub">
                Net Margin: <strong>{{ $summary['net_margin_pct'] }}%</strong>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-label">Saldo Kas &amp; Bank</div>
            <div class="metric-card-val val-green">
                Rp {{ number_format($summary['saldo_kas_akhir'], 0, ',', '.') }}
            </div>
            <div class="metric-card-sub">
                Likuiditas kas operasional &amp; bank
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-label">Total Aset (Aktiva)</div>
            <div class="metric-card-val">
                Rp {{ number_format($summary['total_aktiva'], 0, ',', '.') }}
            </div>
            <div class="metric-card-sub">
                Kewajiban: Rp {{ number_format($summary['total_kewajiban'], 0, ',', '.') }}
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-label">Keseimbangan Neraca</div>
            <div class="metric-card-val {{ $summary['is_neraca_balanced'] ? 'val-green' : 'val-red' }}" style="font-size: 1.05rem;">
                {{ $summary['is_neraca_balanced'] ? '✓ SEIMBANG (0 Diff)' : '⚠️ SELISIH' }}
            </div>
            <div class="metric-card-sub">
                Current Ratio: <strong>{{ $summary['current_ratio'] }}x</strong>
            </div>
        </div>
    </div>

    {{-- Grid 4 Panel Laporan Utama --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap: 1.25rem;">
        
        {{-- PANEL 1: IKHTISAR LABA RUGI --}}
        <div class="corporate-sheet" style="padding: 1.25rem; margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.65rem; border-bottom: 1.5px solid #e2e8f0;">
                <div>
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                        <span>📈</span> <span>Laporan Laba Rugi</span>
                    </h3>
                    <span style="font-size: 0.725rem; color: #64748b;">Kinerja pendapatan dan beban periode berjalan</span>
                </div>
                <a href="{{ route('keuangan.laporan.laba_rugi', ['preset' => $dateInfo['preset']]) }}" class="btn-filter-action btn-filter-secondary">
                    <span>Lihat Rincian &rarr;</span>
                </a>
            </div>

            <table class="accounting-table">
                <tbody>
                    <tr>
                        <td style="font-weight: 600;">Total Pendapatan Operasional</td>
                        <td class="cell-num cell-bold" style="color: #0369a1;">Rp {{ number_format($labaRugi['total_pendapatan_ops'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem; color: #64748b;">(-) Harga Pokok Penjualan (HPP)</td>
                        <td class="cell-num" style="color: #b91c1c;">(Rp {{ number_format($labaRugi['total_hpp'], 0, ',', '.') }})</td>
                    </tr>
                    <tr class="tr-subtotal">
                        <td>Laba Kotor (Gross Profit)</td>
                        <td class="cell-num cell-bold">Rp {{ number_format($labaRugi['laba_kotor'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem; color: #64748b;">(-) Total Beban Operasional</td>
                        <td class="cell-num" style="color: #b91c1c;">(Rp {{ number_format($labaRugi['total_beban_ops'], 0, ',', '.') }})</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Laba Bersih Operasional</td>
                        <td class="cell-num cell-bold">Rp {{ number_format($labaRugi['laba_operasional'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem; color: #64748b;">(+/-) Net Pendapatan/Beban Non-Operasional</td>
                        <td class="cell-num">Rp {{ number_format($labaRugi['net_non_operasional'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="tr-grand-total {{ $labaRugi['laba_bersih'] >= 0 ? '' : 'danger' }}">
                        <td>LABA BERSIH PERIODE BERJALAN</td>
                        <td class="cell-num">Rp {{ number_format($labaRugi['laba_bersih'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- PANEL 2: IKHTISAR NERACA KEUANGAN --}}
        <div class="corporate-sheet" style="padding: 1.25rem; margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.65rem; border-bottom: 1.5px solid #e2e8f0;">
                <div>
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                        <span>⚖️</span> <span>Neraca Posisi Keuangan</span>
                    </h3>
                    <span style="font-size: 0.725rem; color: #64748b;">Posisi aset, hutang kewajiban dan ekuitas modal</span>
                </div>
                <a href="{{ route('keuangan.laporan.neraca', ['preset' => $dateInfo['preset']]) }}" class="btn-filter-action btn-filter-secondary">
                    <span>Lihat Rincian &rarr;</span>
                </a>
            </div>

            <table class="accounting-table">
                <tbody>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a;">Total Aset Lancar (Kas, Piutang, Persediaan)</td>
                        <td class="cell-num cell-bold">Rp {{ number_format($neraca['total_aktiva_lancar'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a;">Total Aset Tetap Pabrik (Tanah, Mesin)</td>
                        <td class="cell-num cell-bold">Rp {{ number_format($neraca['total_aktiva_tetap'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="tr-subtotal" style="background: #f8fafc;">
                        <td style="font-weight: 900; color: #0369a1;">TOTAL AKTIVA (ASET)</td>
                        <td class="cell-num cell-bold" style="color: #0369a1;">Rp {{ number_format($neraca['total_aktiva'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-top: 0.85rem; font-weight: 700;">Total Kewajiban (Hutang Usaha &amp; Pajak)</td>
                        <td class="cell-num cell-bold" style="padding-top: 0.85rem;">Rp {{ number_format($neraca['total_kewajiban'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">Total Ekuitas (Modal &amp; Laba Berjalan)</td>
                        <td class="cell-num cell-bold">Rp {{ number_format($neraca['total_ekuitas'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="tr-grand-total">
                        <td>TOTAL PASIVA (KEWAJIBAN + EKUITAS)</td>
                        <td class="cell-num">Rp {{ number_format($neraca['total_pasiva'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- PANEL 3: IKHTISAR ARUS KAS --}}
        <div class="corporate-sheet" style="padding: 1.25rem; margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.65rem; border-bottom: 1.5px solid #e2e8f0;">
                <div>
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                        <span>💵</span> <span>Laporan Arus Kas</span>
                    </h3>
                    <span style="font-size: 0.725rem; color: #64748b;">Pergerakan aliran kas operasional, investasi &amp; pendanaan</span>
                </div>
                <a href="{{ route('keuangan.laporan.arus_kas', ['preset' => $dateInfo['preset']]) }}" class="btn-filter-action btn-filter-secondary">
                    <span>Lihat Rincian &rarr;</span>
                </a>
            </div>

            <table class="accounting-table">
                <tbody>
                    <tr>
                        <td style="font-weight: 600;">Saldo Kas &amp; Bank Awal Periode</td>
                        <td class="cell-num cell-bold">Rp {{ number_format($arusKas['saldo_awal_total'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem; color: #475569;">Arus Kas dari Aktivitas Operasi</td>
                        <td class="cell-num {{ $arusKas['net_operasi'] >= 0 ? 'val-green' : 'val-red' }}">
                            {{ $arusKas['net_operasi'] >= 0 ? 'Rp ' . number_format($arusKas['net_operasi'], 0, ',', '.') : '(Rp ' . number_format(abs($arusKas['net_operasi']), 0, ',', '.') . ')' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem; color: #475569;">Arus Kas dari Aktivitas Investasi</td>
                        <td class="cell-num">
                            {{ $arusKas['net_investasi'] == 0 ? '-' : 'Rp ' . number_format($arusKas['net_investasi'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem; color: #475569;">Arus Kas dari Aktivitas Pendanaan</td>
                        <td class="cell-num">
                            {{ $arusKas['net_pendanaan'] == 0 ? '-' : 'Rp ' . number_format($arusKas['net_pendanaan'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="tr-subtotal">
                        <td>Kenaikan / (Penurunan) Kas Bersih</td>
                        <td class="cell-num cell-bold {{ $arusKas['kenaikan_bersih_kas'] >= 0 ? 'val-green' : 'val-red' }}">
                            {{ $arusKas['kenaikan_bersih_kas'] >= 0 ? '+ Rp ' . number_format($arusKas['kenaikan_bersih_kas'], 0, ',', '.') : '- Rp ' . number_format(abs($arusKas['kenaikan_bersih_kas']), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="tr-grand-total">
                        <td>SALDO KAS &amp; BANK AKHIR PERIODE</td>
                        <td class="cell-num">Rp {{ number_format($arusKas['saldo_akhir_total'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- PANEL 4: IKHTISAR REKENING KAS & BANK FISIK --}}
        <div class="corporate-sheet" style="padding: 1.25rem; margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.65rem; border-bottom: 1.5px solid #e2e8f0;">
                <div>
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🏦</span> <span>Rekap Rekening Kas &amp; Bank</span>
                    </h3>
                    <span style="font-size: 0.725rem; color: #64748b;">Rincian saldo kas tunai dan perbankan operasional</span>
                </div>
                <a href="{{ route('keuangan.laporan.neraca_saldo', ['preset' => $dateInfo['preset']]) }}" class="btn-filter-action btn-filter-secondary">
                    <span>Trial Balance &rarr;</span>
                </a>
            </div>

            <table class="accounting-table">
                <thead>
                    <tr>
                        <th>Rekening / Sumber Dana</th>
                        <th style="text-align: right;">Saldo Awal</th>
                        <th style="text-align: right;">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($arusKas['rekening_breakdown'] as $rb)
                        <tr>
                            <td>
                                <span class="coa-code-badge">{{ $rb['kas_bank']->kode_akun }}</span>
                                <strong style="color: #0f172a;">{{ $rb['kas_bank']->nama_akun }}</strong>
                            </td>
                            <td class="cell-num">Rp {{ number_format($rb['saldo_awal'], 0, ',', '.') }}</td>
                            <td class="cell-num cell-bold" style="color: #059669;">Rp {{ number_format($rb['saldo_akhir'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="tr-grand-total">
                        <td>TOTAL LIKUIDITAS KAS &amp; BANK</td>
                        <td class="cell-num">Rp {{ number_format($arusKas['saldo_awal_total'], 0, ',', '.') }}</td>
                        <td class="cell-num">Rp {{ number_format($arusKas['saldo_akhir_total'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/laporan/laporan.js') }}"></script>
@endpush

