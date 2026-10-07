@extends('layouts.app')

@section('title', 'Buku Rekapitulasi Harga Pokok Produksi (HPP) - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}">
@endpush

@section('content')
<div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.06em; display: flex; align-items: center; gap: 0.35rem;">
            <span>Produksi &amp; Manufaktur</span>
            <span>&bull;</span>
            <span>PT Mirasa Food Industry</span>
        </div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0 0 0; letter-spacing: -0.02em;">
            Buku Rekapitulasi Harga Pokok Produksi (HPP)
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
            Audit kalkulasi biaya bahan baku, energi CNG, upah operator, FOH, rendemen dan beban pokok per kilogram.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('produksi.index') }}" 
           class="btn btn-secondary" 
           style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
           onmouseover="this.style.background='#f8fafc'" 
           onmouseout="this.style.background='#ffffff'" 
           title="Beralih ke daftar rincian barang hasil produksi">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Hasil Barang Produksi</span>
        </a>

        @if(Auth::user()->canCreateProduksi())
            <a href="{{ route('produksi.create') }}" 
               class="btn btn-primary" 
               style="background: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Hasil Produksi</span>
            </a>
        @endif
    </div>
</div>

{{-- NOTIFIKASI SUKSES & ERROR --}}
@if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="produksi-tabs-nav" style="margin-bottom: 1.25rem;">
    <a href="{{ route('produksi.rekap', ['mode' => 'harian', 'tahun' => $year, 'bulan' => $month]) }}" 
       class="tab-nav-btn {{ $mode === 'harian' ? 'active' : '' }}" 
       style="text-decoration: none;">
        <span>Rekapitulasi Harian</span>
        <span class="tab-nav-badge">{{ strtoupper($monthName) }} {{ $year }}</span>
    </a>
    <a href="{{ route('produksi.rekap', ['mode' => 'bulanan', 'tahun' => $year]) }}" 
       class="tab-nav-btn {{ $mode === 'bulanan' ? 'active' : '' }}" 
       style="text-decoration: none;">
        <span>Rekapitulasi Bulanan (Tren 1 Tahun)</span>
        <span class="tab-nav-badge">TAHUN {{ $year }}</span>
    </a>
</div>

@if ($mode === 'harian')
    {{-- BARIS KONTROL BULAN/TAHUN & EXPORT REKAP HPP --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
        {{-- TITLE & PERIOD PICKER BAR --}}
        <div>
            <span style="font-weight: 700; font-size: 1.05rem; color: #0f172a; letter-spacing: -0.01em;">Rekapitulasi Produksi Harian</span>
            <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.5rem;">Audit kalkulasi biaya bahan, energi CNG, upah kerja, FOH, rendemen &amp; HPP/kg.</span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Periode Kalender Terpadu --}}
            <form action="{{ route('produksi.rekap') }}" method="GET" style="display: flex; gap: 0.35rem; align-items: center; background: #ffffff; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <input type="hidden" name="mode" value="harian">
                <select name="bulan" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @foreach ($monthsList as $num => $nm)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @foreach ($yearsList as $yr)
                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('produksi.export-rekap-pdf', ['tahun' => $year, 'bulan' => $month]) }}" 
               target="_blank" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
               onmouseover="this.style.background='#fef2f2'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Buka &amp; Cetak Dokumen Rekapitulasi HPP PDF Resmi">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Cetak Rekap (PDF)</span>
            </a>

            <a href="{{ route('produksi.export-rekap-excel', ['tahun' => $year, 'bulan' => $month]) }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
               onmouseover="this.style.background='#ecfdf5'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Download Rekapitulasi HPP format Excel (.xlsx)">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>

            <button type="button" 
                    onclick="openModalImportRekap()" 
                    class="btn btn-secondary" 
                    style="background: #ffffff; border: 1.5px solid #7c3aed; color: #7c3aed; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
                    onmouseover="this.style.background='#faf5ff'" 
                    onmouseout="this.style.background='#ffffff'" 
                    title="Import data spreadsheet harian dari file Excel">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import Excel</span>
            </button>

            <button type="button" 
                    onclick="openModalAdjustUtilitas()" 
                    class="btn btn-secondary" 
                    style="background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
                    onmouseover="this.style.background='#f0f9ff'" 
                    onmouseout="this.style.background='#ffffff'" 
                    title="Terapkan tarif standar beban listrik &amp; gas CNG ke seluruh catatan produksi harian">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Set Tarif Utilitas</span>
            </button>
        </div>
    </div>

    {{-- EXECUTIVE FINANCIAL METRIC STRIP (4 INDIKATOR KUNCI BULANAN) --}}
    @php
        $tot = $report['totals'];
        $rendemenLolos = $tot['rendemen_persen'] >= 33.0;
        $cardRendemenClass = $rendemenLolos ? 'card-success' : ($tot['rendemen_persen'] >= 30.0 ? 'card-warning' : 'card-neutral');
    @endphp

    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL BIAYA OPERASIONAL --}}
        <div class="executive-stat-card card-primary">
            <div>
                <div class="executive-stat-label">Total Biaya Operasional</div>
                <div class="executive-stat-value">
                    Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span>Akumulasi Bahan, Gas CNG, Upah &amp; FOH ({{ $report['count'] }} Hari)</span>
            </div>
        </div>

        {{-- METRIK 2: TOTAL OUTPUT WIP JADI --}}
        <div class="executive-stat-card card-neutral">
            <div>
                <div class="executive-stat-label">Total Output Barang Jadi (WIP)</div>
                <div class="executive-stat-value">
                    {{ number_format($tot['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span>Dari input {{ number_format($tot['singkong_qty'], 0, ',', '.') }} kg singkong mentah</span>
            </div>
        </div>

        {{-- METRIK 3: RENDEMEN RATA-RATA --}}
        <div class="executive-stat-card {{ $cardRendemenClass }}">
            <div>
                <div class="executive-stat-label">Rasio Rendemen Efektif</div>
                <div class="executive-stat-value" style="color: {{ $rendemenLolos ? '#059669' : ($tot['rendemen_persen'] >= 30.0 ? '#d97706' : '#dc2626') }};">
                    {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $rendemenLolos ? '#059669' : ($tot['rendemen_persen'] >= 30.0 ? '#d97706' : '#dc2626') }};"></span>
                <span>Standar Toleransi Pabrik: Min. 33.00%</span>
            </div>
        </div>

        {{-- METRIK 4: HPP RATA-RATA PER KG --}}
        <div class="executive-stat-card card-success">
            <div>
                <div class="executive-stat-label">Harga Pokok Produksi (HPP)</div>
                <div class="executive-stat-value" style="color: #0f172a; font-family: monospace;">
                    Rp {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="color: #059669; font-weight: 700;">Rata-rata Riil per Kg Produk Jadi</span>
            </div>
        </div>
    </div>

    {{-- DUAL-VIEW CONTAINER: RINGKASAN EKSEKUTIF (DEFAULT) ATAU SPREADSHEET LENGKAP --}}
    <div id="compact-view-container">
        @include('produksi.partials.table-rekap-compact')
    </div>

    <div id="spreadsheet-view-container" style="display: none;">
        @include('produksi.partials.table-rekap-spreadsheet')
    </div>

{{-- ═════════════════════════════════════════════════════════════
     TAMPILAN 2: REKAPITULASI BULANAN (TREN 12 BULAN DALAM 1 TAHUN)
     ═════════════════════════════════════════════════════════════ --}}
@else
    {{-- BARIS KONTROL TAHUN --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
        <div>
            <span style="font-weight: 700; font-size: 1.05rem; color: #0f172a; letter-spacing: -0.01em;">Konsolidasi Tren Produksi &amp; HPP Tahunan</span>
            <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.5rem;">Perbandingan akumulasi biaya, output WIP, dan rendemen antar bulan sepanjang tahun.</span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Tahun --}}
            <form action="{{ route('produksi.rekap') }}" method="GET" style="display: flex; gap: 0.35rem; align-items: center; background: #ffffff; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <input type="hidden" name="mode" value="bulanan">
                <select name="tahun" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @foreach ($yearsList as $yr)
                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </form>

            <button type="button" 
                    onclick="window.print()" 
                    class="btn btn-secondary" 
                    style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
                    onmouseover="this.style.background='#fef2f2'" 
                    onmouseout="this.style.background='#ffffff'" 
                    title="Cetak atau simpan PDF laporan konsolidasi tahunan">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Cetak Laporan (PDF)</span>
            </button>
        </div>
    </div>

    {{-- EXECUTIVE FINANCIAL METRIC STRIP (4 INDIKATOR KUNCI TAHUNAN) --}}
    @php
        $an = $yearlyReport['annual_totals'];
        $anRendemenLolos = $an['rendemen'] >= 33.0;
        $anCardRendemenClass = $anRendemenLolos ? 'card-success' : ($an['rendemen'] >= 30.0 ? 'card-warning' : 'card-neutral');
    @endphp

    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL BIAYA TAHUNAN --}}
        <div class="executive-stat-card card-primary">
            <div>
                <div class="executive-stat-label">Total Biaya Operasional (Tahun {{ $year }})</div>
                <div class="executive-stat-value">
                    Rp {{ number_format($an['total_biaya'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span>Total akumulasi seluruh biaya pabrik ({{ $an['total_work_days'] }} Hari Kerja)</span>
            </div>
        </div>

        {{-- METRIK 2: TOTAL OUTPUT WIP TAHUNAN --}}
        <div class="executive-stat-card card-neutral">
            <div>
                <div class="executive-stat-label">Total Output Barang Jadi (WIP)</div>
                <div class="executive-stat-value">
                    {{ number_format($an['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span>Dari input {{ number_format($an['singkong_qty'], 0, ',', '.') }} kg singkong mentah</span>
            </div>
        </div>

        {{-- METRIK 3: RENDEMEN RATA-RATA TAHUNAN --}}
        <div class="executive-stat-card {{ $anCardRendemenClass }}">
            <div>
                <div class="executive-stat-label">Rasio Rendemen Rata-Rata Tahunan</div>
                <div class="executive-stat-value" style="color: {{ $anRendemenLolos ? '#059669' : ($an['rendemen'] >= 30.0 ? '#d97706' : '#dc2626') }};">
                    {{ number_format($an['rendemen'], 2, ',', '.') }}%
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $anRendemenLolos ? '#059669' : ($an['rendemen'] >= 30.0 ? '#d97706' : '#dc2626') }};"></span>
                <span>Standar Target Pabrik: Min. 33.00%</span>
            </div>
        </div>

        {{-- METRIK 4: HPP RATA-RATA TAHUNAN PER KG --}}
        <div class="executive-stat-card card-success">
            <div>
                <div class="executive-stat-label">HPP Rata-Rata per Kg (Tahun {{ $year }})</div>
                <div class="executive-stat-value" style="color: #0f172a; font-family: monospace;">
                    Rp {{ number_format($an['hpp_per_kg'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="color: #059669; font-weight: 700;">Biaya Riil Tertimbang per Kg Setahun</span>
            </div>
        </div>
    </div>

    {{-- TABEL KONSOLIDASI 12 BULAN --}}
    @include('produksi.partials.table-rekap-bulanan')

@endif

{{-- MODALS PARTIALS --}}
@if($mode === 'harian')
    @include('produksi.partials.modal-import-rekap')
    @include('produksi.partials.modal-adjust-utilitas')
@endif
@include('produksi.partials.modal-delete-confirm')

@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-index.js') }}"></script>
@endpush
