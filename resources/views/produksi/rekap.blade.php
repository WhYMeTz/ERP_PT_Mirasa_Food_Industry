@extends('layouts.app')

@section('title', 'Buku Rekapitulasi Harga Pokok Produksi (HPP) - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}?v={{ filemtime(public_path('css/produksi/produksi-index.css')) }}">
@endpush

@section('content')
<div class="hpp-hub-header">
    <div>
        <h1 class="hpp-hub-title">
            Buku Rekapitulasi HPP Pabrik
        </h1>
        <p class="hpp-hub-subtitle">
            Pusat audit biaya manufaktur olahan dapur (WIP curah) dan pengemasan produk jadi (FG).
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('produksi.export-rekap-pdf', ['tahun' => $year, 'bulan' => $month]) }}" 
           target="_blank" 
           class="btn btn-secondary" 
           style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 0.95rem; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out; text-decoration: none;" 
           onmouseover="this.style.background='#fef2f2'" 
           onmouseout="this.style.background='#ffffff'" 
           title="Buka &amp; Cetak Dokumen Rekapitulasi HPP PDF Resmi">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Cetak Rekap (PDF)</span>
        </a>

        <a href="{{ route('produksi.export-rekap-excel', ['tahun' => $year, 'bulan' => $month]) }}" 
           class="btn btn-secondary" 
           style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 0.95rem; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out; text-decoration: none;" 
           onmouseover="this.style.background='#ecfdf5'" 
           onmouseout="this.style.background='#ffffff'" 
           title="Download Rekapitulasi HPP format Excel (.xlsx)">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export Excel</span>
        </a>

        @if(Auth::user()->canCreateProduksi())
            <button type="button" 
                    onclick="openModalImportRekap()" 
                    class="btn btn-secondary" 
                    style="background: #ffffff; border: 1.5px solid #7c3aed; color: #7c3aed; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 0.95rem; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out; cursor: pointer;" 
                    onmouseover="this.style.background='#faf5ff'" 
                    onmouseout="this.style.background='#ffffff'" 
                    title="Import data spreadsheet harian dari file Excel">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import Excel</span>
            </button>

            <a href="{{ route('produksi.adjust-utilitas', ['tahun' => $year, 'bulan' => $month]) }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 0.95rem; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out; text-decoration: none;" 
               onmouseover="this.style.background='#f0f9ff'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Buka Halaman Rekonsiliasi Alokasi Tagihan Listrik PLN &amp; Gas CNG Bulanan ke HPP">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Rekonsiliasi Utilitas</span>
            </a>

            <a href="{{ route('produksi.hpp.create') }}" 
               class="btn btn-primary" 
               style="background: #059669; border-color: #059669; font-weight: 700; padding: 0.5rem 1.15rem; font-size: 0.85rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(5,150,105,0.2);">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Kalkulasi Lembar HPP</span>
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

{{-- ═════════════════════════════════════════════════════════════
     MASTER SEGMENTED TABS: DUA PILAR BIAYA PABRIK (DAPUR & KEMAS)
     ═════════════════════════════════════════════════════════════ --}}
<div class="hpp-master-nav">
    <a href="{{ route('produksi.rekap', ['mode' => $mode, 'tahun' => $year, 'bulan' => $month]) }}" class="hpp-master-tab active">
        <div class="hpp-master-tab-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <div class="hpp-master-tab-content">
            <span class="hpp-master-tab-title">1. HPP Olahan Dapur</span>
            <span class="hpp-master-tab-desc">WIP Masak Curah • Singkong, Gas CNG, Upah Regu &amp; Rendemen</span>
        </div>
        <span class="hpp-master-tab-tag">WIP Curah (Rp/Kg)</span>
    </a>

    <a href="{{ route('produksi.packaging.rekap', ['mode' => $mode, 'tahun' => $year, 'bulan' => $month]) }}" class="hpp-master-tab">
        <div class="hpp-master-tab-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
        </div>
        <div class="hpp-master-tab-content">
            <span class="hpp-master-tab-title">2. HPP Pengemasan</span>
            <span class="hpp-master-tab-desc">Barang Jadi (FG) • Curah ke Karton, Dus, Plastik &amp; Borongan</span>
        </div>
        <span class="hpp-master-tab-tag">Produk Jadi (Karton/Pcs)</span>
    </a>
</div>

{{-- ═════════════════════════════════════════════════════════════
     SUB-TOOLBAR: MODE VIEW (HARIAN VS TREN BULANAN) + FILTER KALENDER
     ═════════════════════════════════════════════════════════════ --}}
<div class="hpp-sub-toolbar">
    <div class="hpp-view-segmented">
        <a href="{{ route('produksi.rekap', ['mode' => 'harian', 'tahun' => $year, 'bulan' => $month]) }}" 
           class="hpp-view-btn {{ $mode === 'harian' ? 'active' : '' }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Rekapitulasi Harian</span>
            <span style="font-size: 0.6875rem; opacity: 0.85; margin-left: 0.2rem;">({{ strtoupper($monthName) }} {{ $year }})</span>
        </a>
        <a href="{{ route('produksi.rekap', ['mode' => 'bulanan', 'tahun' => $year]) }}" 
           class="hpp-view-btn {{ $mode === 'bulanan' ? 'active' : '' }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Tren Bulanan (1 Tahun)</span>
            <span style="font-size: 0.6875rem; opacity: 0.85; margin-left: 0.2rem;">(TAHUN {{ $year }})</span>
        </a>
    </div>

    <div>
        <form action="{{ route('produksi.rekap') }}" method="GET" class="hpp-filter-form">
            <input type="hidden" name="mode" value="{{ $mode }}">
            @if($mode === 'harian')
                <select name="bulan" class="hpp-select-control" onchange="this.form.submit()">
                    @foreach ($monthsList as $num => $nm)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            @endif
            <select name="tahun" class="hpp-select-control" onchange="this.form.submit()">
                @foreach ($yearsList as $yr)
                    <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

@if ($mode === 'harian')
    {{-- 4 KARTU METRIK OPERASIONAL & HPP (SERAGAM DENGAN MODUL LAIN) --}}
    @php
        $tot = $report['totals'];
        $rendemenLolos = $tot['rendemen_persen'] >= 33.0;
        $rendemenWarning = $tot['rendemen_persen'] >= 30.0;
        $rendemenColor = $rendemenLolos ? '#059669' : ($rendemenWarning ? '#d97706' : '#dc2626');
        $rendemenBg = $rendemenLolos ? '#d1fae5' : ($rendemenWarning ? '#fef3c7' : '#fee2e2');
        $finalizedCount = $report['finalized_count'] ?? 0;
        $pendingCount = $report['pending_count'] ?? 0;
        $totalWorkDays = $report['count'] ?? 0;
    @endphp

    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL BIAYA OPERASIONAL --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Total Biaya Operasional
                    </span>
                    <div class="executive-stat-value">
                        @if($finalizedCount > 0)
                            Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                        @else
                            <span style="color: #64748b; font-size: 1.25rem;">Rp 0</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($finalizedCount > 0 && $pendingCount > 0)
                    <span style="color: #d97706; font-weight: 600;">Akumulasi {{ $finalizedCount }} Hari Final ({{ $pendingCount }} Hari Belum Final)</span>
                @elseif($finalizedCount > 0)
                    Akumulasi Bahan, Gas CNG, Upah &amp; FOH ({{ $finalizedCount }} Hari)
                @else
                    <span style="color: #b45309; font-weight: 600;">Menunggu finalisasi HPP (0 dari {{ $totalWorkDays }} Hari)</span>
                @endif
            </div>
        </div>

        {{-- METRIK 2: TOTAL OUTPUT WIP JADI --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Total Output Barang Jadi (WIP)
                    </span>
                    <div class="executive-stat-value">
                        @if($finalizedCount > 0)
                            {{ number_format($tot['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                        @else
                            <span style="color: #64748b; font-size: 1.25rem;">0,00 kg</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #f1f5f9; color: #475569;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($finalizedCount > 0 && $pendingCount > 0)
                    <span style="color: #d97706; font-weight: 600;">Dari input {{ number_format($tot['singkong_qty'], 0, ',', '.') }} kg singkong ({{ $finalizedCount }} Hari Final)</span>
                @elseif($finalizedCount > 0)
                    Dari input {{ number_format($tot['singkong_qty'], 0, ',', '.') }} kg singkong mentah
                @else
                    <span style="color: #b45309; font-weight: 600;">Menunggu penarikan lembar HPP ({{ $totalWorkDays }} Dokumen Fisik)</span>
                @endif
            </div>
        </div>

        {{-- METRIK 3: RENDEMEN RATA-RATA --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: {{ $finalizedCount > 0 ? $rendemenColor : '#64748b' }};">
                        Rasio Rendemen Efektif
                    </span>
                    <div class="executive-stat-value" style="color: {{ $finalizedCount > 0 ? $rendemenColor : '#64748b' }};">
                        @if($finalizedCount > 0)
                            {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                        @else
                            <span style="color: #64748b; font-size: 1.25rem;">0,00%</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: {{ $finalizedCount > 0 ? $rendemenBg : '#f1f5f9' }}; color: {{ $finalizedCount > 0 ? $rendemenColor : '#64748b' }};">
                    @if($finalizedCount > 0)
                        @if($rendemenLolos)
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($rendemenWarning)
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        @else
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                        @endif
                    @else
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($finalizedCount > 0)
                    <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: {{ $rendemenColor }};"></span>
                    Standar Toleransi Pabrik: Min. 33.00%
                @else
                    <span style="color: #b45309; font-weight: 600;">Belum dihitung (menunggu HPP)</span>
                @endif
            </div>
        </div>

        {{-- METRIK 4: HPP RATA-RATA PER KG --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #059669;">
                        Harga Pokok Produksi (HPP)
                    </span>
                    <div class="executive-stat-value" style="font-family: monospace;">
                        @if($finalizedCount > 0)
                            Rp {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}
                        @else
                            <span style="color: #64748b; font-size: 1.15rem; font-weight: 700;">Belum Ditetapkan</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #d1fae5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($finalizedCount > 0)
                    <span style="color: #059669; font-weight: 700;">Rata-rata Riil per Kg ({{ $finalizedCount }} Hari Final)</span>
                @else
                    <span style="color: #b45309; font-weight: 600;">Menunggu penarikan &amp; penetapan HPP</span>
                @endif
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
@elseif ($mode === 'bulanan' && $yearlyReport)
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
        <div style="display: flex; flex-direction: column; min-width: 0; flex: 1;">
            <span style="font-weight: 800; font-size: 1.15rem; color: #0f172a; letter-spacing: -0.01em; white-space: nowrap;">Konsolidasi Tren Produksi &amp; HPP Tahunan</span>
            <span style="font-size: 0.825rem; color: #64748b; line-height: 1.4; white-space: nowrap; margin-top: 0.15rem;">Perbandingan akumulasi biaya, output WIP, dan rendemen antar bulan sepanjang tahun.</span>
        </div>

        <div style="display: flex; gap: 0.35rem; align-items: center; flex-shrink: 0; flex-wrap: nowrap;">
            {{-- Filter Tahun --}}
            <form action="{{ route('produksi.rekap') }}" method="GET" style="display: flex; gap: 0.2rem; align-items: center; background: #ffffff; padding: 0.15rem 0.35rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02); margin: 0;">
                <input type="hidden" name="mode" value="bulanan">
                <select name="tahun" class="form-control" style="font-size: 0.8125rem; padding: 0.15rem 0.5rem 0.15rem 0.35rem; height: 30px; border: none; font-weight: 700; color: #1e293b; min-width: 95px; cursor: pointer; background: transparent;" onchange="this.form.submit()">
                    @foreach ($yearsList as $yr)
                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </form>

            <button type="button" 
                    onclick="window.print()" 
                    class="btn btn-secondary" 
                    style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.8125rem; font-weight: 700; padding: 0.42rem 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out; white-space: nowrap;" 
                    onmouseover="this.style.background='#fef2f2'" 
                    onmouseout="this.style.background='#ffffff'" 
                    title="Cetak atau simpan PDF laporan konsolidasi tahunan">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Cetak Laporan (PDF)</span>
            </button>
        </div>
    </div>

    {{-- 4 KARTU METRIK OPERASIONAL & HPP TAHUNAN (SERAGAM DENGAN MODUL LAIN) --}}
    @php
        $an = $yearlyReport['annual_totals'] ?? [];
        $anFinalizedDays = $an['finalized_days'] ?? 0;
        $anWorkDays = $an['total_work_days'] ?? 0;
        $anRendemen = (float) ($an['rendemen'] ?? 0);
        $anRendemenLolos = $anRendemen >= 33.0;
        $anRendemenWarning = $anRendemen >= 30.0;
        $anRendemenColor = $anRendemenLolos ? '#059669' : ($anRendemenWarning ? '#d97706' : '#dc2626');
        $anRendemenBg = $anRendemenLolos ? '#d1fae5' : ($anRendemenWarning ? '#fef3c7' : '#fee2e2');
    @endphp

    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL BIAYA TAHUNAN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Total Biaya Operasional (Tahun {{ $year }})
                    </span>
                    <div class="executive-stat-value">
                        @if($anFinalizedDays > 0)
                            Rp {{ number_format($an['total_biaya'], 0, ',', '.') }}
                        @else
                            <span style="color: #64748b; font-size: 1.25rem;">Rp 0</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($anFinalizedDays > 0 && $anFinalizedDays < $anWorkDays)
                    <span style="color: #d97706; font-weight: 600;">Akumulasi {{ $anFinalizedDays }} dari {{ $anWorkDays }} Hari Kerja Final</span>
                @elseif($anFinalizedDays > 0)
                    Total akumulasi seluruh biaya pabrik ({{ $anFinalizedDays }} Hari Kerja)
                @else
                    <span style="color: #b45309; font-weight: 600;">Menunggu finalisasi HPP (0 dari {{ $anWorkDays }} Hari)</span>
                @endif
            </div>
        </div>

        {{-- METRIK 2: TOTAL OUTPUT WIP TAHUNAN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Total Output Barang Jadi (WIP)
                    </span>
                    <div class="executive-stat-value">
                        @if($anFinalizedDays > 0)
                            {{ number_format($an['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                        @else
                            <span style="color: #64748b; font-size: 1.25rem;">0,00 kg</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #f1f5f9; color: #475569;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($anFinalizedDays > 0)
                    Dari input {{ number_format($an['singkong_qty'], 0, ',', '.') }} kg singkong mentah
                @else
                    <span style="color: #b45309; font-weight: 600;">Menunggu penarikan lembar HPP ({{ $anWorkDays }} Hari)</span>
                @endif
            </div>
        </div>

        {{-- METRIK 3: RENDEMEN RATA-RATA TAHUNAN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: {{ $anFinalizedDays > 0 ? $anRendemenColor : '#64748b' }};">
                        Rasio Rendemen Rata-Rata Tahunan
                    </span>
                    <div class="executive-stat-value" style="color: {{ $anFinalizedDays > 0 ? $anRendemenColor : '#64748b' }};">
                        @if($anFinalizedDays > 0)
                            {{ number_format($an['rendemen'], 2, ',', '.') }}%
                        @else
                            <span style="color: #64748b; font-size: 1.25rem;">0,00%</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: {{ $anFinalizedDays > 0 ? $anRendemenBg : '#f1f5f9' }}; color: {{ $anFinalizedDays > 0 ? $anRendemenColor : '#64748b' }};">
                    @if($anFinalizedDays > 0)
                        @if($anRendemenLolos)
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($anRendemenWarning)
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        @else
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                        @endif
                    @else
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($anFinalizedDays > 0)
                    <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: {{ $anRendemenColor }};"></span>
                    Standar Target Pabrik: Min. 33.00%
                @else
                    <span style="color: #b45309; font-weight: 600;">Belum dihitung (menunggu HPP)</span>
                @endif
            </div>
        </div>

        {{-- METRIK 4: HPP RATA-RATA TAHUNAN PER KG --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #059669;">
                        HPP Rata-Rata per Kg (Tahun {{ $year }})
                    </span>
                    <div class="executive-stat-value" style="font-family: monospace;">
                        @if($anFinalizedDays > 0)
                            Rp {{ number_format($an['hpp_per_kg'], 0, ',', '.') }}
                        @else
                            <span style="color: #64748b; font-size: 1.15rem; font-weight: 700;">Belum Ditetapkan</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #d1fae5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($anFinalizedDays > 0)
                    <span style="color: #059669; font-weight: 700;">Biaya Riil Tertimbang per Kg Setahun</span>
                @else
                    <span style="color: #b45309; font-weight: 600;">Menunggu penarikan &amp; penetapan HPP</span>
                @endif
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
    <script src="{{ asset('js/produksi/produksi-index.js') }}?v={{ filemtime(public_path('js/produksi/produksi-index.js')) }}"></script>
@endpush
