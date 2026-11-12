@extends('layouts.app')

@section('title', 'Neraca Saldo (Trial Balance)')

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
                📑 Neraca Saldo (Trial Balance)
            </h1>
        </div>
        <p style="font-size: 0.775rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Verifikasi Keseimbangan Akuntansi Seluruh Buku Besar Akun &bull; Periode {{ $dateInfo['label'] }}
        </p>
    </div>
</div>

<div class="laporan-container">
    {{-- Navigasi Tab --}}
    @include('keuangan.laporan.partials.nav-tabs')

    {{-- Filter Bar --}}
    @include('keuangan.laporan.partials.header-filter', [
        'tableExportId' => 'tableNeracaSaldo',
        'exportFilename' => 'Trial_Balance_PT_Mirasa_' . $dateInfo['start_date'] . '_' . $dateInfo['end_date'] . '.csv'
    ])

    {{-- Banner Status Keseimbangan Trial Balance --}}
    <div class="balance-status-banner {{ $report['is_akhir_balanced'] ? 'balanced' : 'unbalanced' }} no-print">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            @if($report['is_akhir_balanced'])
                <span>✓</span>
                <span>TRIAL BALANCE SEIMBANG 100% &bull; Total Debet Akhir sama persis dengan Total Kredit Akhir</span>
            @else
                <span>⚠️</span>
                <span>PERINGATAN AUDIT: Saldo akhir debet dan kredit tidak seimbang.</span>
            @endif
        </div>
        <div>
            Total Mutasi Periode: <strong>Rp {{ number_format($report['sum_mutasi_debet'], 0, ',', '.') }}</strong>
        </div>
    </div>

    {{-- Lembar Akuntansi Cetak --}}
    <div class="corporate-sheet">
        @include('keuangan.laporan.partials.sheet-header', [
            'reportTitle' => 'NERACA SALDO (TRIAL BALANCE)'
        ])

        <div style="overflow-x: auto;">
            <table class="accounting-table" id="tableNeracaSaldo">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 80px; text-align: center;">Kode</th>
                        <th rowspan="2">Nama Akun COA</th>
                        <th rowspan="2" style="width: 75px; text-align: center;">Posisi</th>
                        <th colspan="2" style="text-align: center; border-left: 1px solid #cbd5e1;">Saldo Awal</th>
                        <th colspan="2" style="text-align: center; border-left: 1px solid #cbd5e1;">Mutasi Periode</th>
                        <th colspan="2" style="text-align: center; border-left: 1px solid #cbd5e1;">Saldo Akhir</th>
                    </tr>
                    <tr>
                        <th style="width: 100px; text-align: right; border-left: 1px solid #cbd5e1;">Debet</th>
                        <th style="width: 100px; text-align: right;">Kredit</th>
                        <th style="width: 100px; text-align: right; border-left: 1px solid #cbd5e1;">Debet</th>
                        <th style="width: 100px; text-align: right;">Kredit</th>
                        <th style="width: 100px; text-align: right; border-left: 1px solid #cbd5e1;">Debet</th>
                        <th style="width: 100px; text-align: right;">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $r)
                        <tr>
                            <td style="text-align: center;">
                                <span class="coa-code-badge" style="margin-right: 0;">{{ $r['kode_akun'] }}</span>
                            </td>
                            <td>
                                <a href="{{ route('keuangan.buku_besar.index', ['coa_id' => $r['coa_id'], 'start_date' => $dateInfo['start_date'], 'end_date' => $dateInfo['end_date']]) }}" style="color: #0f172a; text-decoration: none; font-weight: 600;">
                                    {{ $r['nama_akun'] }}
                                </a>
                            </td>
                            <td style="text-align: center; font-size: 0.72rem; color: #64748b; font-weight: 700;">
                                {{ $r['saldo_normal'] === 'DEBET' ? 'D' : 'K' }}
                            </td>

                            {{-- Saldo Awal --}}
                            <td class="cell-num" style="border-left: 1px solid #f1f5f9; color: {{ $r['awal_debet'] > 0 ? '#0f172a' : '#cbd5e1' }};">
                                {{ $r['awal_debet'] > 0 ? number_format($r['awal_debet'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="cell-num" style="color: {{ $r['awal_kredit'] > 0 ? '#0f172a' : '#cbd5e1' }};">
                                {{ $r['awal_kredit'] > 0 ? number_format($r['awal_kredit'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Mutasi --}}
                            <td class="cell-num" style="border-left: 1px solid #f1f5f9; color: {{ $r['mutasi_debet'] > 0 ? '#0369a1' : '#cbd5e1' }};">
                                {{ $r['mutasi_debet'] > 0 ? number_format($r['mutasi_debet'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="cell-num" style="color: {{ $r['mutasi_kredit'] > 0 ? '#0369a1' : '#cbd5e1' }};">
                                {{ $r['mutasi_kredit'] > 0 ? number_format($r['mutasi_kredit'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Saldo Akhir --}}
                            <td class="cell-num cell-bold" style="border-left: 1px solid #f1f5f9; color: {{ $r['akhir_debet'] > 0 ? '#0f172a' : '#cbd5e1' }};">
                                {{ $r['akhir_debet'] > 0 ? number_format($r['akhir_debet'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="cell-num cell-bold" style="color: {{ $r['akhir_kredit'] > 0 ? '#0f172a' : '#cbd5e1' }};">
                                {{ $r['akhir_kredit'] > 0 ? number_format($r['akhir_kredit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                Tidak ada akun COA dengan aktivitas saldo pada periode ini.
                            </td>
                        </tr>
                    @endforelse

                    {{-- Baris Total Keseimbangan --}}
                    <tr class="tr-grand-total">
                        <td colspan="3" style="text-align: right;">TOTAL KESEIMBANGAN:</td>
                        <td class="cell-num cell-bold" style="border-left: 1px solid #a7f3d0;">
                            Rp {{ number_format($report['sum_awal_debet'], 0, ',', '.') }}
                        </td>
                        <td class="cell-num cell-bold">
                            Rp {{ number_format($report['sum_awal_kredit'], 0, ',', '.') }}
                        </td>
                        <td class="cell-num cell-bold" style="border-left: 1px solid #a7f3d0;">
                            Rp {{ number_format($report['sum_mutasi_debet'], 0, ',', '.') }}
                        </td>
                        <td class="cell-num cell-bold">
                            Rp {{ number_format($report['sum_mutasi_kredit'], 0, ',', '.') }}
                        </td>
                        <td class="cell-num cell-bold" style="border-left: 1px solid #a7f3d0;">
                            Rp {{ number_format($report['sum_akhir_debet'], 0, ',', '.') }}
                        </td>
                        <td class="cell-num cell-bold">
                            Rp {{ number_format($report['sum_akhir_kredit'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

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

