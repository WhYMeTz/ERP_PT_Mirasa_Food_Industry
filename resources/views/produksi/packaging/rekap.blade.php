@extends('layouts.app')

@section('title', 'Buku Rekapitulasi HPP Pengemasan (Packaging) - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/master/tarif_kemasan/tarif-kemasan-modal.css') }}">
@endpush

@section('content')
<div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
            Buku Rekapitulasi HPP Pengemasan (Packaging)
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
            Audit kalkulasi biaya bahan kemasan, WIP curah, upah borongan packing, HPP per karton dan per pcs.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('produksi.packaging.index') }}" 
           class="btn btn-secondary" 
           style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
           onmouseover="this.style.background='#f8fafc'" 
           onmouseout="this.style.background='#ffffff'" 
           title="Beralih ke daftar riwayat lembar kerja pengemasan">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Riwayat Pengemasan (FG)</span>
        </a>

        @if(Auth::user()->canManageTarifKemasan())
            <button type="button" 
                    onclick="openQuickUpdateModal()" 
                    class="btn btn-secondary" 
                    style="background: #f0fdf4; border: 1.5px solid #86efac; color: #166534; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer; transition: all 0.15s ease-in-out;" 
                    onmouseover="this.style.background='#dcfce7'" 
                    onmouseout="this.style.background='#f0fdf4'" 
                    title="Konfigurasi cepat standar harga acuan kemasan (Format Excel) tanpa meninggalkan rekapitulasi">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Standar Tarif Kemasan</span>
            </button>
        @endif

        @if(Auth::user()->canCreateProduksi())
            <a href="{{ route('produksi.packaging.create') }}" 
               class="btn btn-primary" 
               style="background: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Pengemasan Baru</span>
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

{{-- NAVIGASI TABS MODE (HARIAN VS BULANAN TREN) --}}
<div class="produksi-tabs-nav" style="margin-bottom: 1.25rem;">
    <a href="{{ route('produksi.packaging.rekap', ['mode' => 'harian', 'tahun' => $year, 'bulan' => $month, 'barang_jadi_id' => $barangJadiId ?? null]) }}" 
       class="tab-nav-btn {{ $mode === 'harian' ? 'active' : '' }}" 
       style="text-decoration: none;">
        <span>Rekapitulasi Harian</span>
        <span class="tab-nav-badge">{{ strtoupper($monthName) }} {{ $year }}</span>
    </a>
    <a href="{{ route('produksi.packaging.rekap', ['mode' => 'bulanan', 'tahun' => $year, 'barang_jadi_id' => $barangJadiId ?? null]) }}" 
       class="tab-nav-btn {{ $mode === 'bulanan' ? 'active' : '' }}" 
       style="text-decoration: none;">
        <span>Rekapitulasi Bulanan (Tren 1 Tahun)</span>
        <span class="tab-nav-badge">TAHUN {{ $year }}</span>
    </a>
</div>

{{-- ═════════════════════════════════════════════════════════════
     TAMPILAN 1: REKAPITULASI HARIAN (DALAM 1 BULAN KALENDER)
     ═════════════════════════════════════════════════════════════ --}}
@if ($mode === 'harian')
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
        <div style="display: flex; flex-direction: column; min-width: 0; flex: 1;">
            <span style="font-weight: 800; font-size: 1.15rem; color: #0f172a; letter-spacing: -0.01em; white-space: nowrap;">
                Rekapitulasi Pengemasan &amp; HPP Harian
            </span>
            <span style="font-size: 0.825rem; color: #64748b; line-height: 1.4; white-space: nowrap; margin-top: 0.15rem;">
                Audit biaya curah, bahan kemas dus &amp; plastik, upah borongan packing, dan beban pokok per karton &amp; pcs.
            </span>
        </div>

        <div style="display: flex; gap: 0.35rem; align-items: center; flex-shrink: 0; flex-wrap: nowrap;">
            {{-- Filter Periode Kalender Terpadu --}}
            <form action="{{ route('produksi.packaging.rekap') }}" method="GET" style="display: flex; gap: 0.2rem; align-items: center; background: #ffffff; padding: 0.15rem 0.35rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02); margin: 0;">
                <input type="hidden" name="mode" value="harian">
                
                <select name="barang_jadi_id" class="form-control" style="font-size: 0.8125rem; padding: 0.15rem 0.35rem; height: 30px; border: none; font-weight: 700; color: #1e293b; min-width: 150px; cursor: pointer; background: transparent;" onchange="this.form.submit()">
                    <option value="">-- Semua Produk Jadi --</option>
                    @foreach ($barangJadiList as $b)
                        <option value="{{ $b->barang_id }}" {{ $b->barang_id == $barangJadiId ? 'selected' : '' }}>
                            {{ $b->barang_nm }}
                        </option>
                    @endforeach
                </select>

                <select name="bulan" class="form-control" style="font-size: 0.8125rem; padding: 0.15rem 0.5rem 0.15rem 0.35rem; height: 30px; border: none; font-weight: 700; color: #1e293b; min-width: 110px; cursor: pointer; background: transparent;" onchange="this.form.submit()">
                    @foreach ($monthsList as $num => $nm)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>

                <select name="tahun" class="form-control" style="font-size: 0.8125rem; padding: 0.15rem 0.35rem; height: 30px; border: none; font-weight: 700; color: #1e293b; min-width: 68px; cursor: pointer; background: transparent;" onchange="this.form.submit()">
                    @foreach ($yearsList as $yr)
                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('produksi.packaging.export-rekap-pdf', ['tahun' => $year, 'bulan' => $month, 'barang_jadi_id' => $barangJadiId ?? null]) }}" 
               target="_blank" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.8125rem; font-weight: 700; padding: 0.42rem 0.65rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.3rem; transition: all 0.15s ease-in-out; white-space: nowrap;" 
               onmouseover="this.style.background='#fef2f2'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Buka &amp; Cetak Dokumen Rekapitulasi HPP Packaging PDF Resmi">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Cetak Rekap (PDF)</span>
            </a>

            <a href="{{ route('produksi.packaging.export-rekap-excel', ['tahun' => $year, 'bulan' => $month, 'barang_jadi_id' => $barangJadiId ?? null]) }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.8125rem; font-weight: 700; padding: 0.42rem 0.65rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.3rem; transition: all 0.15s ease-in-out; white-space: nowrap;" 
               onmouseover="this.style.background='#ecfdf5'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Download Rekapitulasi HPP Packaging format Excel (.xlsx)">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>
        </div>
    </div>

    {{-- 4 KARTU METRIK EKSEKUTIF PACKAGING --}}
    @php
        $tot = $report['totals'];
    @endphp
    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL BIAYA PENGEMASAN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Total Biaya Pengemasan
                    </span>
                    <div class="executive-stat-value">
                        Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                WIP + Kemasan + Upah Kemas ({{ $report['count'] }} Hari)
            </div>
        </div>

        {{-- METRIK 2: HASIL KEMAS FINISH GOOD --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Hasil Kemas (Finish Good)
                    </span>
                    <div class="executive-stat-value">
                        {{ number_format($tot['total_karton'], 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #ecfdf5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Setara {{ number_format($tot['total_pcs'], 0, ',', '.') }} Pcs siap didistribusikan
            </div>
        </div>

        {{-- METRIK 3: BIAYA KEMASAN PER KARTON --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #d97706;">
                        Beban Kemas / Karton
                    </span>
                    <div class="executive-stat-value" style="color: #d97706;">
                        Rp {{ number_format($tot['biaya_kemas_per_karton'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #fef3c7; color: #d97706;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Rata-rata Dus Box, Plastik &amp; Lakban
            </div>
        </div>

        {{-- METRIK 4: HPP RIIL PER KARTON & PCS --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #059669;">
                        HPP Riil Barang Jadi (FG)
                    </span>
                    <div class="executive-stat-value" style="font-family: monospace;">
                        Rp {{ number_format($tot['rata_hpp_karton'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #d1fae5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="color: #059669; font-weight: 700;">Setara Rp {{ number_format($tot['rata_hpp_pcs'], 0, ',', '.') }} / Pcs</span>
            </div>
        </div>
    </div>

    {{-- DUAL-VIEW CONTAINER UNTUK PACKAGING --}}
    <div id="compact-view-container">
        @include('produksi.partials.table-rekap-packaging-compact')
    </div>

    <div id="spreadsheet-view-container" style="display: none;">
        @include('produksi.partials.table-rekap-packaging-spreadsheet')
    </div>

{{-- ═════════════════════════════════════════════════════════════
     TAMPILAN 2: REKAPITULASI BULANAN (TREN 12 BULAN DALAM 1 TAHUN)
     ═════════════════════════════════════════════════════════════ --}}
@else
    {{-- BARIS KONTROL TAHUN --}}
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
        <div style="display: flex; flex-direction: column; min-width: 0; flex: 1;">
            <span style="font-weight: 800; font-size: 1.15rem; color: #0f172a; letter-spacing: -0.01em; white-space: nowrap;">
                Konsolidasi Tren Pengemasan &amp; HPP Tahunan
            </span>
            <span style="font-size: 0.825rem; color: #64748b; line-height: 1.4; white-space: nowrap; margin-top: 0.15rem;">
                Perbandingan akumulasi biaya, output, dan tren beban HPP antar bulan sepanjang tahun.
            </span>
        </div>

        <div style="display: flex; gap: 0.35rem; align-items: center; flex-shrink: 0; flex-wrap: nowrap;">
            {{-- Filter Tahun --}}
            <form action="{{ route('produksi.packaging.rekap') }}" method="GET" style="display: flex; gap: 0.2rem; align-items: center; background: #ffffff; padding: 0.15rem 0.35rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02); margin: 0;">
                <input type="hidden" name="mode" value="bulanan">
                
                <select name="barang_jadi_id" class="form-control" style="font-size: 0.8125rem; padding: 0.15rem 0.35rem; height: 30px; border: none; font-weight: 700; color: #1e293b; min-width: 150px; cursor: pointer; background: transparent;" onchange="this.form.submit()">
                    <option value="">-- Semua Produk FG --</option>
                    @foreach ($barangJadiList as $b)
                        <option value="{{ $b->barang_id }}" {{ $b->barang_id == $barangJadiId ? 'selected' : '' }}>
                            {{ $b->barang_nm }}
                        </option>
                    @endforeach
                </select>

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

    {{-- 4 KARTU METRIK TAHUNAN PACKAGING --}}
    @php
        $anPck = $yearlyReport['totals'];
    @endphp
    <div class="executive-stat-grid">
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">Total Biaya Kemas (Tahun {{ $year }})</span>
                    <div class="executive-stat-value">Rp {{ number_format($anPck['annual_biaya'], 0, ',', '.') }}</div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">Akumulasi Biaya Kemas 1 Tahun Fiskal</div>
        </div>

        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">Total Output Finish Good</span>
                    <div class="executive-stat-value">{{ number_format($anPck['annual_karton'], 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span></div>
                </div>
                <div class="executive-stat-icon-box" style="background: #ecfdf5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">Setara {{ number_format($anPck['annual_pcs'], 0, ',', '.') }} Pcs terkemas</div>
        </div>

        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #d97706;">Total Biaya Kemasan Box &amp; Plastik</span>
                    <div class="executive-stat-value" style="color: #d97706;">Rp {{ number_format($anPck['annual_kemasan'], 0, ',', '.') }}</div>
                </div>
                <div class="executive-stat-icon-box" style="background: #fef3c7; color: #d97706;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">Bahan Pembungkus &amp; Packing Setahun</div>
        </div>

        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #059669;">Rata-Rata HPP per Karton</span>
                    <div class="executive-stat-value" style="font-family: monospace;">Rp {{ number_format($anPck['annual_hpp_karton'], 0, ',', '.') }}</div>
                </div>
                <div class="executive-stat-icon-box" style="background: #d1fae5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext"><span style="color: #059669; font-weight: 700;">Setara Rp {{ number_format($anPck['annual_hpp_pcs'], 0, ',', '.') }} / Pcs</span></div>
        </div>
    </div>

    {{-- TABEL KONSOLIDASI 12 BULAN PACKAGING --}}
    @include('produksi.partials.table-rekap-packaging-bulanan')

@endif

{{-- MODAL VOID DANGER --}}
@include('produksi.packaging.partials.modal-void')

{{-- MODAL PERBARUI CEPAT STANDAR HARGA KEMASAN (EXCEL SYNC) --}}
@include('master_data.tarif_kemasan.partials.modal-quick-update')

@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-index.js') }}"></script>
    <script src="{{ asset('js/produksi/packaging/packaging-index.js') }}"></script>
@endpush
