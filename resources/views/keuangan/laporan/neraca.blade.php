@extends('layouts.app')

@section('title', 'Neraca Posisi Keuangan')

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
                ⚖️ Neraca Posisi Keuangan (Balance Sheet)
            </h1>
        </div>
        <p style="font-size: 0.775rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Statement of Financial Position &bull; Posisi per Tanggal {{ \Carbon\Carbon::parse($asOfDate)->translatedFormat('d F Y') }}
        </p>
    </div>
</div>

<div class="laporan-container">
    {{-- Navigasi Tab --}}
    @include('keuangan.laporan.partials.nav-tabs')

    {{-- Filter Bar --}}
    @include('keuangan.laporan.partials.header-filter', [
        'tableExportId' => 'tableNeracaPrint',
        'exportFilename' => 'Neraca_Keuangan_PT_Mirasa_' . $asOfDate . '.csv'
    ])

    {{-- Banner Status Keseimbangan Neraca (Audit Trail Pill) --}}
    <div class="balance-status-banner {{ $report['is_balanced'] ? 'balanced' : 'unbalanced' }} no-print">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            @if($report['is_balanced'])
                <span>✓</span>
                <span>NERACA KEUANGAN SEIMBANG 100% (BALANCED) &bull; Total Aktiva sama dengan Total Pasiva</span>
            @else
                <span>⚠️</span>
                <span>TERDAPAT SELISIH AUDIT: Rp {{ number_format(abs($report['selisih']), 0, ',', '.') }} &bull; Periksa akun pembuka atau jurnal penyesuaian</span>
            @endif
        </div>
        <div>
            Total Aset: <strong>Rp {{ number_format($report['total_aktiva'], 0, ',', '.') }}</strong>
        </div>
    </div>

    {{-- Lembar Akuntansi Cetak --}}
    <div class="corporate-sheet">
        @include('keuangan.laporan.partials.sheet-header', [
            'reportTitle' => 'NERACA POSISI KEUANGAN',
            'periodSubtitle' => 'Per ' . \Carbon\Carbon::parse($asOfDate)->translatedFormat('d F Y')
        ])

        {{-- Format Skontro: Sisi Kiri (Aktiva / Aset) & Sisi Kanan (Pasiva / Kewajiban & Modal) --}}
        <div class="neraca-skontro-grid" id="tableNeracaPrint">
            
            {{-- KOLOM KIRI: AKTIVA (ASET) --}}
            <div class="neraca-column">
                <div class="neraca-col-header" style="display: flex; justify-content: space-between;">
                    <span>AKTIVA (ASET)</span>
                    <span>NOMINAL (RP)</span>
                </div>

                <table class="accounting-table">
                    <tbody>
                        {{-- 1. AKTIVA LANCAR --}}
                        <tr class="tr-section-title">
                            <td colspan="2">1. AKTIVA LANCAR</td>
                        </tr>
                        @php
                            $groupedLancar = collect($report['aktiva_lancar'])->groupBy('sub_kategori');
                        @endphp
                        @foreach($groupedLancar as $subCat => $items)
                            <tr style="background: #fafafa;">
                                <td colspan="2" style="font-weight: 700; color: #475569; font-size: 0.775rem; padding-left: 1.25rem;">
                                    &bull; {{ $subCat }}
                                </td>
                            </tr>
                            @foreach($items as $item)
                                <tr>
                                    <td style="padding-left: 2rem;">
                                        <a href="{{ route('keuangan.buku_besar.index', ['coa_id' => $item['coa_id'], 'start_date' => $dateInfo['start_date'], 'end_date' => $asOfDate]) }}" style="color: #0f172a; text-decoration: none;">
                                            <span class="coa-code-badge">{{ $item['kode_akun'] }}</span>
                                            <span>{{ $item['nama_akun'] }}</span>
                                        </a>
                                    </td>
                                    <td class="cell-num">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        <tr class="tr-subtotal">
                            <td>TOTAL AKTIVA LANCAR</td>
                            <td class="cell-num cell-bold">Rp {{ number_format($report['total_aktiva_lancar'], 0, ',', '.') }}</td>
                        </tr>

                        {{-- 2. AKTIVA TETAP --}}
                        <tr class="tr-section-title">
                            <td colspan="2">2. AKTIVA TETAP PABRIK</td>
                        </tr>
                        @forelse($report['aktiva_tetap'] as $at)
                            <tr>
                                <td style="padding-left: 1.5rem;">
                                    <a href="{{ route('keuangan.buku_besar.index', ['coa_id' => $at['coa_id'], 'start_date' => $dateInfo['start_date'], 'end_date' => $asOfDate]) }}" style="color: #0f172a; text-decoration: none;">
                                        <span class="coa-code-badge">{{ $at['kode_akun'] }}</span>
                                        <span>{{ $at['nama_akun'] }}</span>
                                    </a>
                                </td>
                                <td class="cell-num">Rp {{ number_format($at['nominal'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="padding-left: 1.5rem; color: #94a3b8; font-style: italic;">
                                    Tidak ada aset tetap tercatat.
                                </td>
                            </tr>
                        @endforelse
                        <tr class="tr-subtotal">
                            <td>TOTAL AKTIVA TETAP</td>
                            <td class="cell-num cell-bold">Rp {{ number_format($report['total_aktiva_tetap'], 0, ',', '.') }}</td>
                        </tr>

                        {{-- TOTAL AKTIVA (BOTTOM LINE) --}}
                        <tr class="tr-grand-total">
                            <td>TOTAL AKTIVA (ASET)</td>
                            <td class="cell-num">Rp {{ number_format($report['total_aktiva'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- KOLOM KANAN: PASIVA (KEWAJIBAN & EKUITAS) --}}
            <div class="neraca-column">
                <div class="neraca-col-header" style="display: flex; justify-content: space-between; background: #1e293b;">
                    <span>PASIVA (KEWAJIBAN &amp; MODAL)</span>
                    <span>NOMINAL (RP)</span>
                </div>

                <table class="accounting-table">
                    <tbody>
                        {{-- 1. KEWAJIBAN LANCAR / HUTANG --}}
                        <tr class="tr-section-title">
                            <td colspan="2">1. KEWAJIBAN JANGKA PENDEK (HUTANG)</td>
                        </tr>
                        @php
                            $groupedHutang = collect($report['kewajiban_lancar'])->groupBy('sub_kategori');
                        @endphp
                        @forelse($groupedHutang as $subCat => $items)
                            <tr style="background: #fafafa;">
                                <td colspan="2" style="font-weight: 700; color: #475569; font-size: 0.775rem; padding-left: 1.25rem;">
                                    &bull; {{ $subCat }}
                                </td>
                            </tr>
                            @foreach($items as $item)
                                <tr>
                                    <td style="padding-left: 2rem;">
                                        <a href="{{ route('keuangan.buku_besar.index', ['coa_id' => $item['coa_id'], 'start_date' => $dateInfo['start_date'], 'end_date' => $asOfDate]) }}" style="color: #0f172a; text-decoration: none;">
                                            <span class="coa-code-badge">{{ $item['kode_akun'] }}</span>
                                            <span>{{ $item['nama_akun'] }}</span>
                                        </a>
                                    </td>
                                    <td class="cell-num">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="2" style="padding-left: 1.5rem; color: #94a3b8; font-style: italic;">
                                    Tidak ada kewajiban hutang yang belum lunas.
                                </td>
                            </tr>
                        @endforelse
                        <tr class="tr-subtotal">
                            <td>TOTAL KEWAJIBAN LANCAR</td>
                            <td class="cell-num cell-bold">Rp {{ number_format($report['total_kewajiban'], 0, ',', '.') }}</td>
                        </tr>

                        {{-- 2. EKUITAS & MODAL --}}
                        <tr class="tr-section-title">
                            <td colspan="2">2. EKUITAS &amp; MODAL PEMILIK</td>
                        </tr>
                        @foreach($report['ekuitas_items'] as $ek)
                            <tr>
                                <td style="padding-left: 1.5rem;">
                                    <a href="{{ route('keuangan.buku_besar.index', ['coa_id' => $ek['coa_id'], 'start_date' => $dateInfo['start_date'], 'end_date' => $asOfDate]) }}" style="color: #0f172a; text-decoration: none;">
                                        <span class="coa-code-badge">{{ $ek['kode_akun'] }}</span>
                                        <span>{{ $ek['nama_akun'] }}</span>
                                    </a>
                                </td>
                                <td class="cell-num">Rp {{ number_format($ek['nominal'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="padding-left: 1.5rem; font-weight: 700; color: #0f172a;">
                                <span class="coa-code-badge" style="background: #ecfdf5; color: #047857;">LR</span>
                                <span>Laba Bersih Periode Berjalan</span>
                            </td>
                            <td class="cell-num cell-bold" style="color: {{ $report['laba_periode_berjalan'] >= 0 ? '#15803d' : '#b91c1c' }};">
                                Rp {{ number_format($report['laba_periode_berjalan'], 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="tr-subtotal">
                            <td>TOTAL EKUITAS / MODAL</td>
                            <td class="cell-num cell-bold">Rp {{ number_format($report['total_ekuitas'], 0, ',', '.') }}</td>
                        </tr>

                        {{-- TOTAL PASIVA (BOTTOM LINE) --}}
                        <tr class="tr-grand-total">
                            <td>TOTAL PASIVA (KEWAJIBAN + EKUITAS)</td>
                            <td class="cell-num">Rp {{ number_format($report['total_pasiva'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

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

