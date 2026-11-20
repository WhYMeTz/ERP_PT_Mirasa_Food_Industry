@extends('layouts.app')

@section('title', 'Laporan Laba Rugi Komprehensif')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/laporan/laporan.css') }}">
@endpush

@section('content')
<div class="content-header no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('keuangan.laporan.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.825rem; font-weight: 600;">
                &larr; Hub Laporan
            </a>
            <span style="color: #cbd5e1;">/</span>
            <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                📈 Laporan Laba Rugi Komprehensif
            </h1>
        </div>
        <p style="font-size: 0.775rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Statement of Profit and Loss &bull; Periode {{ $dateInfo['label'] }}
        </p>
    </div>
</div>

<div class="laporan-container">
    {{-- Navigasi Tab --}}
    @include('keuangan.laporan.partials.nav-tabs')

    {{-- Filter Bar --}}
    @include('keuangan.laporan.partials.header-filter', [
        'tableExportId' => 'tableLabaRugi',
        'exportFilename' => 'Laba_Rugi_PT_Mirasa_' . $dateInfo['start_date'] . '_' . $dateInfo['end_date'] . '.csv'
    ])

    {{-- Lembar Akuntansi Cetak --}}
    <div class="corporate-sheet">
        @include('keuangan.laporan.partials.sheet-header', [
            'reportTitle' => 'LAPORAN LABA RUGI KOMPREHENSIF'
        ])

        <table class="accounting-table" id="tableLabaRugi">
            <thead>
                <tr>
                    <th style="width: 55%;">Uraian Akun / Pos Finansial</th>
                    <th style="width: 15%; text-align: center;">Kode Akun</th>
                    <th style="width: 15%; text-align: right;">Rasio (%)</th>
                    <th style="width: 15%; text-align: right;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                {{-- A. PENDAPATAN OPERASIONAL --}}
                <tr class="tr-section-title">
                    <td colspan="4">A. PENDAPATAN OPERASIONAL / PENJUALAN</td>
                </tr>
                @forelse($report['pendapatan_operasional'] as $p)
                    @php
                        $pct = ($report['total_pendapatan_ops'] > 0) ? round(($p['nominal'] / $report['total_pendapatan_ops']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td style="padding-left: 1.75rem;">
                            <a href="{{ route('keuangan.buku_besar.index', ['coa_id' => $p['coa_id'], 'start_date' => $dateInfo['start_date'], 'end_date' => $dateInfo['end_date']]) }}" style="color: #0f172a; text-decoration: none; font-weight: 600;">
                                {{ $p['nama_akun'] }}
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <span class="coa-code-badge">{{ $p['kode_akun'] }}</span>
                        </td>
                        <td class="cell-num" style="color: #64748b;">{{ $pct }}%</td>
                        <td class="cell-num cell-bold" style="color: #0369a1;">
                            Rp {{ number_format($p['nominal'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding-left: 1.75rem; color: #94a3b8; font-style: italic;">
                            Belum ada transaksi pendapatan pada periode ini.
                        </td>
                    </tr>
                @endforelse
                <tr class="tr-subtotal">
                    <td colspan="2">TOTAL PENDAPATAN OPERASIONAL</td>
                    <td class="cell-num">100.0%</td>
                    <td class="cell-num cell-bold" style="color: #0369a1;">Rp {{ number_format($report['total_pendapatan_ops'], 0, ',', '.') }}</td>
                </tr>

                {{-- B. HARGA POKOK PENJUALAN (HPP) --}}
                <tr class="tr-section-title">
                    <td colspan="4">B. HARGA POKOK PENJUALAN (HPP)</td>
                </tr>
                @forelse($report['hpp_items'] as $h)
                    @php
                        $pct = ($report['total_pendapatan_ops'] > 0) ? round(($h['nominal'] / $report['total_pendapatan_ops']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td style="padding-left: 1.75rem;">
                            <a href="{{ route('keuangan.buku_besar.index', ['coa_id' => $h['coa_id'], 'start_date' => $dateInfo['start_date'], 'end_date' => $dateInfo['end_date']]) }}" style="color: #0f172a; text-decoration: none; font-weight: 600;">
                                {{ $h['nama_akun'] }}
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <span class="coa-code-badge">{{ $h['kode_akun'] }}</span>
                        </td>
                        <td class="cell-num" style="color: #64748b;">{{ $pct }}%</td>
                        <td class="cell-num cell-bold" style="color: #b91c1c;">
                            (Rp {{ number_format($h['nominal'], 0, ',', '.') }})
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding-left: 1.75rem; color: #94a3b8; font-style: italic;">
                            Belum ada pemakaian bahan baku/HPP yang tercatat pada periode ini.
                        </td>
                    </tr>
                @endforelse
                <tr class="tr-subtotal">
                    <td colspan="2">TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                    <td class="cell-num">-</td>
                    <td class="cell-num cell-bold" style="color: #b91c1c;">(Rp {{ number_format($report['total_hpp'], 0, ',', '.') }})</td>
                </tr>

                {{-- LABA KOTOR --}}
                <tr class="tr-subtotal" style="background: #f8fafc; font-size: 0.875rem;">
                    <td colspan="2" style="font-weight: 900; color: #0f172a;">LABA KOTOR (GROSS PROFIT)</td>
                    <td class="cell-num cell-bold">{{ $report['gross_margin_pct'] }}%</td>
                    <td class="cell-num cell-bold" style="color: {{ $report['laba_kotor'] >= 0 ? '#15803d' : '#b91c1c' }};">
                        Rp {{ number_format($report['laba_kotor'], 0, ',', '.') }}
                    </td>
                </tr>

                {{-- C. BEBAN OPERASIONAL --}}
                <tr class="tr-section-title">
                    <td colspan="4">C. BEBAN OPERASIONAL &amp; USAHA</td>
                </tr>
                @forelse($report['beban_operasional'] as $b)
                    @php
                        $pct = ($report['total_pendapatan_ops'] > 0) ? round(($b['nominal'] / $report['total_pendapatan_ops']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td style="padding-left: 1.75rem;">
                            <a href="{{ route('keuangan.buku_besar.index', ['coa_id' => $b['coa_id'], 'start_date' => $dateInfo['start_date'], 'end_date' => $dateInfo['end_date']]) }}" style="color: #0f172a; text-decoration: none; font-weight: 600;">
                                {{ $b['nama_akun'] }}
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <span class="coa-code-badge">{{ $b['kode_akun'] }}</span>
                        </td>
                        <td class="cell-num" style="color: #64748b;">{{ $pct }}%</td>
                        <td class="cell-num">
                            (Rp {{ number_format($b['nominal'], 0, ',', '.') }})
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding-left: 1.75rem; color: #94a3b8; font-style: italic;">
                            Belum ada beban operasional tercatat pada periode ini.
                        </td>
                    </tr>
                @endforelse
                <tr class="tr-subtotal">
                    <td colspan="2">TOTAL BEBAN OPERASIONAL</td>
                    <td class="cell-num">-</td>
                    <td class="cell-num cell-bold" style="color: #b91c1c;">(Rp {{ number_format($report['total_beban_ops'], 0, ',', '.') }})</td>
                </tr>

                {{-- LABA OPERASIONAL --}}
                <tr class="tr-subtotal" style="background: #f8fafc; font-size: 0.85rem;">
                    <td colspan="2" style="font-weight: 800; color: #0f172a;">LABA BERSIH OPERASIONAL</td>
                    <td class="cell-num cell-bold">{{ $report['operating_margin_pct'] }}%</td>
                    <td class="cell-num cell-bold" style="color: {{ $report['laba_operasional'] >= 0 ? '#15803d' : '#b91c1c' }};">
                        Rp {{ number_format($report['laba_operasional'], 0, ',', '.') }}
                    </td>
                </tr>

                {{-- D. NON OPERASIONAL --}}
                @if(count($report['pendapatan_non_ops']) > 0 || count($report['beban_non_ops']) > 0)
                    <tr class="tr-section-title">
                        <td colspan="4">D. PENDAPATAN &amp; BEBAN LAIN-LAIN (NON OPERASIONAL)</td>
                    </tr>
                    @foreach($report['pendapatan_non_ops'] as $pno)
                        <tr>
                            <td style="padding-left: 1.75rem;">{{ $pno['nama_akun'] }}</td>
                            <td style="text-align: center;"><span class="coa-code-badge">{{ $pno['kode_akun'] }}</span></td>
                            <td class="cell-num">-</td>
                            <td class="cell-num" style="color: #15803d;">+ Rp {{ number_format($pno['nominal'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    @foreach($report['beban_non_ops'] as $bno)
                        <tr>
                            <td style="padding-left: 1.75rem;">{{ $bno['nama_akun'] }}</td>
                            <td style="text-align: center;"><span class="coa-code-badge">{{ $bno['kode_akun'] }}</span></td>
                            <td class="cell-num">-</td>
                            <td class="cell-num" style="color: #b91c1c;">(Rp {{ number_format($bno['nominal'], 0, ',', '.') }})</td>
                        </tr>
                    @endforeach
                    <tr class="tr-subtotal">
                        <td colspan="2">NET PENDAPATAN / (BEBAN) NON-OPERASIONAL</td>
                        <td class="cell-num">-</td>
                        <td class="cell-num cell-bold">Rp {{ number_format($report['net_non_operasional'], 0, ',', '.') }}</td>
                    </tr>
                @endif

                {{-- LABA BERSIH FINAL --}}
                <tr class="tr-grand-total {{ $report['laba_bersih'] >= 0 ? '' : 'danger' }}">
                    <td colspan="2">LABA BERSIH PERIODE BERJALAN (NET PROFIT)</td>
                    <td class="cell-num cell-bold">{{ $report['net_margin_pct'] }}%</td>
                    <td class="cell-num">Rp {{ number_format($report['laba_bersih'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Tanda Tangan Resmi Akuntansi --}}
        <div style="margin-top: 3rem; display: flex; justify-content: space-between; text-align: center; font-size: 0.8125rem;">
            <div style="width: 200px;">
                <div style="color: #64748b; margin-bottom: 3.5rem;">Dibuat Oleh:</div>
                <div style="font-weight: 800; border-top: 1px solid #94a3b8; padding-top: 0.25rem;">Staff Akuntansi</div>
            </div>
            <div style="width: 200px;">
                <div style="color: #64748b; margin-bottom: 3.5rem;">Diperiksa Oleh:</div>
                <div style="font-weight: 800; border-top: 1px solid #94a3b8; padding-top: 0.25rem;">Manajer Keuangan</div>
            </div>
            <div style="width: 200px;">
                <div style="color: #64748b; margin-bottom: 3.5rem;">Disetujui Oleh:</div>
                <div style="font-weight: 800; border-top: 1px solid #94a3b8; padding-top: 0.25rem;">Direktur Utama</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/laporan/laporan.js') }}"></script>
@endpush

