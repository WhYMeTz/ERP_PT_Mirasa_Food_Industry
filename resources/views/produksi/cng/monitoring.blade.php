@extends('layouts.app')

@section('title', 'Monitoring & Rekonsiliasi Gas CNG - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}?v={{ filemtime(public_path('css/produksi/produksi-index.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/cng/cng-monitoring.css') }}?v={{ filemtime(public_path('css/produksi/cng/cng-monitoring.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-adjust-cng-harian.css') }}?v={{ filemtime(public_path('css/produksi/produksi-adjust-cng-harian.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-adjust-utilitas.css') }}?v={{ filemtime(public_path('css/produksi/produksi-adjust-utilitas.css')) }}">
@endpush

@section('content')
<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('produksi.rekap', ['tahun' => $year, 'bulan' => $month]) }}" 
       style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.45rem; font-weight: 600; transition: color 0.15s ease-in-out;"
       onmouseover="this.style.color='#0f172a'" 
       onmouseout="this.style.color='#64748b'"
       title="Kembali ke Buku Rekapitulasi HPP">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Buku Rekapitulasi HPP</span>
    </a>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="26" height="26" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                <span>Monitoring &amp; Rekonsiliasi Penggunaan Gas CNG</span>
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
                Pusat pemantauan konsumsi energi gas harian dari HPP, analisis efisiensi boiler/fryer, dan rekonsiliasi faktur tagihan vendor.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Periode Kalender Terpadu --}}
            <form action="{{ route('produksi.cng-monitoring') }}" method="GET" style="display: flex; gap: 0.35rem; align-items: center; background: #ffffff; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <select name="bulan" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @foreach ($monthsList as $num => $nm)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @for ($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>
</div>

{{-- NOTIFIKASI SUKSES & ERROR --}}
@if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- KARTU RINGKASAN EKSEKUTIF (EXECUTIVE METRIC CARDS) --}}
<div class="cng-stat-grid">
    {{-- CARD 1: TOTAL KONSUMSI GAS --}}
    <div class="cng-stat-card card-primary">
        <div class="cng-stat-header">
            <div>
                <span class="cng-stat-title" style="color: #b45309;">1. Total Konsumsi Gas</span>
                <span style="font-size: 0.7rem; color: #94a3b8;">Akumulasi {{ $workDaysCount }} Hari Kerja</span>
            </div>
            <div class="cng-stat-icon" style="background: #fef3c7; color: #d97706;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
            </div>
        </div>
        <div class="cng-stat-value" style="color: #b45309;">
            {{ number_format($totalMmbtu, 4, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 700;">MMBTU</span>
        </div>
        <div class="cng-stat-subvalue">
            <span>Rata-rata: <strong>{{ number_format($avgMmbtuPerDay, 2, ',', '.') }} MMBTU/hari</strong></span>
        </div>
    </div>

    {{-- CARD 2: TOTAL BEBAN BIAYA GAS --}}
    <div class="cng-stat-card card-info">
        <div class="cng-stat-header">
            <div>
                <span class="cng-stat-title" style="color: #0369a1;">2. Total Beban Biaya Gas</span>
                <span style="font-size: 0.7rem; color: #94a3b8;">Kapitalisasi HPP Produksi</span>
            </div>
            <div class="cng-stat-icon" style="background: #e0f2fe; color: #0284c7;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="cng-stat-value" style="color: #0369a1;">
            Rp {{ number_format($totalNilaiRp, 0, ',', '.') }}
        </div>
        <div class="cng-stat-subvalue">
            <span>Rata-rata: <strong>Rp {{ number_format($avgNilaiPerDay, 0, ',', '.') }}/hari</strong></span>
        </div>
    </div>

    {{-- CARD 3: TOTAL OUTPUT WIP --}}
    <div class="cng-stat-card card-success">
        <div class="cng-stat-header">
            <div>
                <span class="cng-stat-title" style="color: #047857;">3. Total Output Keripik (WIP)</span>
                <span style="font-size: 0.7rem; color: #94a3b8;">Hasil Penggorengan</span>
            </div>
            <div class="cng-stat-icon" style="background: #d1fae5; color: #059669;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div class="cng-stat-value" style="color: #047857;">
            {{ number_format($totalWipKg, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 700;">kg</span>
        </div>
        <div class="cng-stat-subvalue">
            <span>Keripik Lolos Rilis &amp; Sampingan</span>
        </div>
    </div>

    {{-- CARD 4: RASIO EFISIENSI ENERGI --}}
    <div class="cng-stat-card card-neutral">
        <div class="cng-stat-header">
            <div>
                <span class="cng-stat-title" style="color: #475569;">4. Rasio Efisiensi Boiler</span>
                <span style="font-size: 0.7rem; color: #94a3b8;">Konsumsi per Satuan Hasil</span>
            </div>
            <div class="cng-stat-icon" style="background: #f1f5f9; color: #475569;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div class="cng-stat-value" style="color: #0f172a;">
            {{ number_format($ratioMmbtuPerTonWip, 2, ',', '.') }} <span style="font-size: 0.8rem; font-weight: 700; color: #64748b;">MMBTU / Ton</span>
        </div>
        <div class="cng-stat-subvalue">
            @php $biayaPerKgWip = $totalWipKg > 0 ? ($totalNilaiRp / $totalWipKg) : 0; @endphp
            <span>Biaya Gas: <strong>Rp {{ number_format($biayaPerKgWip, 0, ',', '.') }} / kg keripik</strong></span>
        </div>
    </div>
</div>

{{-- SEGMENTED TAB SWITCHER TERPADU (3-IN-1 HUB) --}}
<div class="cng-tab-switcher-wrap no-print">
    <button type="button" 
            class="cng-tab-btn tab-amber {{ $activeTab === 'rangkuman' ? 'active' : '' }}" 
            data-tab="rangkuman" 
            onclick="switchCngTab('rangkuman')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>1. Rangkuman Pemakaian Harian (Read-Only)</span>
    </button>

    <button type="button" 
            class="cng-tab-btn tab-amber {{ $activeTab === 'harian' ? 'active' : '' }}" 
            data-tab="harian" 
            onclick="switchCngTab('harian')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        <span>2. Penyesuaian Gas Harian &amp; Balancing</span>
    </button>

    <button type="button" 
            class="cng-tab-btn tab-blue {{ $activeTab === 'bulanan' ? 'active' : '' }}" 
            data-tab="bulanan" 
            onclick="switchCngTab('bulanan')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>3. Rekonsiliasi Faktur Total Bulanan</span>
    </button>
</div>

{{-- TAB PANE 1: RANGKUMAN HARIAN (READ-ONLY CLEAN TABLE) --}}
<div class="cng-tab-pane {{ $activeTab === 'rangkuman' ? 'active' : '' }}" id="pane_rangkuman">
    @include('produksi.cng.partials.tab-rangkuman')
</div>

{{-- TAB PANE 2: PENYESUAIAN HARIAN & BALANCING --}}
<div class="cng-tab-pane {{ $activeTab === 'harian' ? 'active' : '' }}" id="pane_harian">
    @include('produksi.cng.partials.tab-adjust-harian')
</div>

{{-- TAB PANE 3: REKONSILIASI BULANAN FAKTUR TOTAL --}}
<div class="cng-tab-pane {{ $activeTab === 'bulanan' ? 'active' : '' }}" id="pane_bulanan">
    @include('produksi.cng.partials.tab-adjust-bulanan')
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/cng/cng-monitoring.js') }}?v={{ filemtime(public_path('js/produksi/cng/cng-monitoring.js')) }}"></script>
    <script src="{{ asset('js/produksi/produksi-adjust-cng-harian.js') }}?v={{ filemtime(public_path('js/produksi/produksi-adjust-cng-harian.js')) }}"></script>
@endpush
