@extends('layouts.app')

@section('title', 'Pengeluaran Barang - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/pengeluaran/pengeluaran-index.css') }}?v={{ filemtime(public_path('css/penjualan/pengeluaran/pengeluaran-index.css')) }}">
@endpush

@section('content')
<div class="sj-index-container">

    {{-- HEADER & TOMBOL AKSI UTAMA --}}
    <div class="sj-header-row">
        <div>
            <h1 class="sj-page-title">Pengeluaran Barang</h1>
            <p class="sj-page-subtitle">
                Monitoring fisik pengeluaran barang, alokasi batch FEFO, dan penerbitan Surat Jalan resmi.
            </p>
        </div>

        <div class="sj-header-buttons">
            <a href="{{ route('penjualan.pengeluaran.create-sample') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.8125rem; font-weight: 600; padding: 0.45rem 0.95rem; border-radius: 6px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Pengeluaran Sampel</span>
            </a>
        </div>
    </div>

    {{-- 5 KARTU METRIK OPERASIONAL --}}
    <div class="sj-metrics-grid">
        {{-- KARTU 1: ANTREAN PENGELUARAN (FG) --}}
        <div class="sj-metric-card clickable {{ ($metrics['ready_orders_count'] ?? 0) > 0 ? 'queue-highlight' : '' }}" onclick="switchSjTab('antrean')" title="Klik untuk membuka Antrean Pengeluaran">
            <div class="sj-metric-header">
                <span class="sj-metric-title">Antrean Pengeluaran</span>
                <div class="sj-metric-icon amber" style="background: #fef3c7; color: #d97706;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <div class="sj-metric-val" style="color: {{ ($metrics['ready_orders_count'] ?? 0) > 0 ? '#b45309' : '#0f172a' }};">
                {{ number_format($metrics['ready_orders_count'] ?? 0, 0, ',', '.') }}
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">PO</span>
            </div>
            <div class="sj-metric-subtext">Menunggu pengeluaran fisik &bull; Klik buka tab</div>
        </div>

        {{-- KARTU 2: PENGIRIMAN HARI INI --}}
        <div class="sj-metric-card">
            <div class="sj-metric-header">
                <span class="sj-metric-title">Pengiriman Hari Ini</span>
                <div class="sj-metric-icon emerald">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="sj-metric-val">
                {{ number_format($metrics['kirim_hari_ini'] ?? 0, 0, ',', '.') }}
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">SJ</span>
            </div>
            <div class="sj-metric-subtext">Truk diberangkatkan hari ini</div>
        </div>

        {{-- KARTU 3: FISIK KELUAR HARI INI --}}
        <div class="sj-metric-card">
            <div class="sj-metric-header">
                <span class="sj-metric-title">Fisik Keluar Hari Ini</span>
                <div class="sj-metric-icon blue" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="sj-metric-val">
                {{ number_format($metrics['qty_hari_ini'] ?? 0, 0, ',', '.') }}
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
            </div>
            <div class="sj-metric-subtext">Volume produk keluar hari ini</div>
        </div>

        {{-- KARTU 4: TOTAL PENGELUARAN BULAN INI --}}
        <div class="sj-metric-card">
            <div class="sj-metric-header">
                <span class="sj-metric-title">Total Keluar Bulan Ini</span>
                <div class="sj-metric-icon purple" style="background: #faf5ff; color: #7c3aed;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="sj-metric-val">
                {{ number_format($metrics['qty_bulan_ini'] ?? 0, 0, ',', '.') }}
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
            </div>
            <div class="sj-metric-subtext">{{ $metrics['bulan_nama'] ?? 'Bulan Ini' }} &bull; {{ number_format($metrics['sj_bulan_ini'] ?? 0, 0, ',', '.') }} Surat Jalan</div>
        </div>
    </div>

    {{-- NAVIGASI TAB UTAMA (ENTERPRISE ERP WORKLIST) --}}
    <div class="sj-tabs-wrapper">
        <div class="sj-tabs-group">
            <button type="button" 
                    class="sj-tab-item {{ ($tab ?? 'antrean') === 'antrean' ? 'active' : '' }}" 
                    onclick="switchSjTab('antrean')" 
                    id="tabBtnAntrean">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Antrean Pengeluaran</span>
                @if(isset($readyOrders) && $readyOrders->isNotEmpty())
                    <span class="sj-tab-badge amber">{{ $readyOrders->count() }}</span>
                @else
                    <span class="sj-tab-badge count">0</span>
                @endif
            </button>

            <button type="button" 
                    class="sj-tab-item {{ ($tab ?? 'antrean') === 'sj' ? 'active' : '' }}" 
                    onclick="switchSjTab('sj')" 
                    id="tabBtnSj">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Riwayat Pengeluaran</span>
                <span class="sj-tab-badge count">{{ number_format($suratJalanList->total(), 0, ',', '.') }}</span>
            </button>
        </div>
    </div>

    {{-- ==============================================================
         TAB PANE 1: DAFTAR DOKUMEN SURAT JALAN
         ============================================================== --}}
    <div id="paneSj" class="sj-tab-pane {{ ($tab ?? 'antrean') === 'sj' ? 'active' : '' }}" style="{{ ($tab ?? 'antrean') === 'sj' ? '' : 'display: none;' }}">

    {{-- FILTER & PENCARIAN --}}
    <div class="sj-filter-card">
        <form method="GET" action="{{ route('penjualan.pengeluaran.index') }}" class="sj-filter-grid">
            <input type="hidden" name="tab" value="sj">
            <div class="sj-form-group">
                <label for="search" class="sj-form-label">Cari Pengeluaran / Customer / Truk</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                       placeholder="No. SJ, No. PO, Supir, Nopol, Nama Customer..." class="sj-form-input">
            </div>

            <div class="sj-form-group">
                <label for="status" class="sj-form-label">Status Dokumen</label>
                <select name="status" id="status" class="sj-form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="DIKELUARKAN" {{ $status === 'DIKELUARKAN' ? 'selected' : '' }}>Dikeluarkan</option>
                    <option value="BATAL" {{ $status === 'BATAL' ? 'selected' : '' }}>Dibatalkan (Void)</option>
                </select>
            </div>

            <div class="sj-form-group">
                <label for="date" class="sj-form-label">Tanggal Pengeluaran</label>
                <input type="date" name="date" id="date" value="{{ $date ?? '' }}" class="sj-form-input">
            </div>

            <div style="display: flex; gap: 0.35rem;">
                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 0.95rem; font-size: 0.825rem; font-weight: 700;">
                    Filter
                </button>
                @if ($search || $status || !empty($date))
                    <a href="{{ route('penjualan.pengeluaran.index', ['tab' => 'sj']) }}" class="btn btn-secondary" style="height: 38px; padding: 0 0.75rem; font-size: 0.825rem; display: inline-flex; align-items: center;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL DATA SURAT JALAN --}}
    <div class="sj-table-card">
        <div class="sj-table-responsive">
            <table class="sj-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No.</th>
                        <th style="width: 155px;">Dokumen Surat Jalan</th>
                        <th style="width: 140px;">Referensi PO Penjualan</th>
                        <th style="min-width: 180px;">Mitra Customer</th>
                        <th style="min-width: 220px;">Rincian Barang Dikeluarkan</th>
                        <th style="min-width: 160px;">Armada &amp; Supir</th>
                        <th style="text-align: right; width: 110px;">Total Qty</th>
                        <th style="text-align: center; width: 130px;">Status</th>
                        <th style="text-align: center; width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suratJalanList as $idx => $sj)
                        @php
                            $order = $sj->order;
                            $customer = $sj->customer;
                            $menuId = 'actionMenuSj_' . $sj->pengeluaran_id;
                            $klasifikasi = $customer?->klasifikasi ?? 'MANUAL';
                        @endphp
                        <tr>
                            {{-- No. Urut --}}
                            <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                                {{ $suratJalanList->firstItem() + $idx }}
                            </td>

                            {{-- Dokumen Surat Jalan --}}
                            <td>
                                <a href="{{ route('penjualan.pengeluaran.show', $sj->pengeluaran_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-family: monospace; font-size: 0.875rem;">
                                    {{ $sj->pengeluaran_no }}
                                </a>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                    {{ $sj->isSample() ? 'Tgl Keluar: ' : 'Tgl Berangkat: ' }}{{ \Carbon\Carbon::parse($sj->pengeluaran_tgl)->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- Referensi PO Penjualan --}}
                            <td>
                                @if ($sj->isSample())
                                    <span class="badge" style="background: #faf5ff; color: #7c3aed; border: 1px solid #e9d5ff; font-weight: 700; font-size: 0.725rem; padding: 2px 7px; border-radius: 4px;">
                                        SAMPEL / PROMOSI
                                    </span>
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                        {{ $sj->keperluan_txt ?: 'Pengeluaran Non-PO' }}
                                    </div>
                                @elseif ($order)
                                    @if(Auth::user()?->canAccessSo() ?? false)
                                        <a href="{{ route('penjualan.so.show', $order->so_id) }}" style="font-weight: 600; color: #334155; text-decoration: none; font-size: 0.8125rem;">
                                            {{ $order->so_no }}
                                        </a>
                                    @else
                                        <span style="font-weight: 600; color: #334155; font-size: 0.8125rem;">
                                            {{ $order->so_no }}
                                        </span>
                                    @endif
                                    @if ($order->customer_po_no)
                                        <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">
                                            PO Ext: {{ $order->customer_po_no }}
                                        </div>
                                    @endif
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>

                            {{-- Mitra Customer / Penerima --}}
                            <td>
                                @if ($sj->isSample())
                                    <div style="font-weight: 700; color: #1e293b;">
                                        {{ $sj->penerima_nm ?: ($customer?->customer_nm ?? 'Penerima Sampel') }}
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem;">
                                        <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.675rem; font-weight: 700; padding: 1px 5px; border-radius: 4px;">NON-KOMERSIAL</span>
                                        @if($sj->otorisasi_oleh)
                                            <span style="font-size: 0.725rem; color: #64748b;">
                                                Izin: {{ $sj->otorisasi_oleh }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div style="font-weight: 700; color: #1e293b;">
                                        {{ $customer?->customer_nm ?? 'Customer Non-Aktif' }}
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem;">
                                        @if ($klasifikasi === 'IFM')
                                            <span class="badge-klasifikasi ifm">IFM</span>
                                        @elseif (in_array($klasifikasi, ['MIDI_ALFA', 'IDM', 'IDM_ALFA']))
                                            <span class="badge-klasifikasi modern">{{ $klasifikasi === 'MIDI_ALFA' ? 'MIDI/ALFA' : ($klasifikasi === 'IDM' ? 'IDM' : 'MODERN') }}</span>
                                        @else
                                            <span class="badge-klasifikasi manual">MANUAL</span>
                                        @endif
                                        <span style="font-size: 0.725rem; color: #64748b;">
                                            {{ $customer?->wilayah ?: ($customer?->alamat_txt ? Str::limit($customer->alamat_txt, 25) : '-') }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- Rincian Barang Dikeluarkan --}}
                            <td>
                                <div style="font-size: 0.775rem; color: #334155; line-height: 1.45;">
                                    @forelse($sj->details->take(2) as $dtl)
                                        <div style="margin-bottom: 0.25rem;">
                                            <span style="font-weight: 700; color: #0f172a;">{{ $dtl->barang?->barang_nm ?? '-' }}</span>
                                            <span style="font-weight: 600; color: #0284c7;">({{ number_format($dtl->kirim_qty, 0, ',', '.') }} ctn)</span>
                                            @if($dtl->batch_no)
                                                <div style="font-size: 0.7rem; color: #64748b; font-family: monospace;">
                                                    Batch: {{ $dtl->batch_no }}
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <span style="color: #94a3b8; font-style: italic; font-size: 0.75rem;">Tanpa rincian barang</span>
                                    @endforelse

                                    @if($sj->details->count() > 2)
                                        <div style="color: #64748b; font-size: 0.725rem; font-style: italic; margin-top: 0.15rem;">
                                            +{{ $sj->details->count() - 2 }} produk lainnya...
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Armada & Supir / Pengeluaran Internal --}}
                            <td>
                                @if ($sj->isSample())
                                    <div style="font-weight: 600; color: #475569; font-size: 0.8rem;">
                                        Internal (Tanpa Ekspedisi)
                                    </div>
                                    <div style="font-size: 0.725rem; color: #64748b;">
                                        Oleh: {{ $sj->created_by ?? 'Gudang' }}
                                    </div>
                                @else
                                    <div style="font-weight: 600; color: #1e293b;">
                                        {{ $sj->supir_nm ?: 'Supir Belum Dicatat' }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;">
                                        {{ $sj->nopol_kendaraan ?: '-' }} {{ $sj->ekspedisi_nm ? '('.$sj->ekspedisi_nm.')' : '' }}
                                    </div>
                                @endif
                            </td>

                            {{-- Total Kuantitas (Karton) --}}
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-feature-settings: 'tnum';">
                                {{ number_format($sj->total_qty, 0, ',', '.') }}
                                <span style="font-size: 0.75rem; font-weight: 500; color: #64748b;">Karton</span>
                            </td>

                            {{-- Status Dokumen Pengeluaran --}}
                            <td style="text-align: center;">
                                @if ($sj->status_cd === 'BATAL')
                                    <span class="badge-status batal">
                                        <svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Batal (Void)
                                    </span>
                                @else
                                    <span class="badge-status dikeluarkan">
                                        <svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Dikeluarkan
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi (SMART ACTION DROPDOWN) --}}
                            <td style="text-align: center; position: relative;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    {{-- Lihat Detail --}}
                                    <a href="{{ route('penjualan.pengeluaran.show', $sj->pengeluaran_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>{{ $sj->isSample() ? 'Lihat Detail Sampel' : 'Lihat Dokumen SJ' }}</span>
                                    </a>

                                    {{-- Cetak PDF Surat Jalan / Bukti Sampel --}}
                                    <a href="{{ route('penjualan.pengeluaran.export-pdf', $sj->pengeluaran_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>{{ $sj->isSample() ? 'Cetak Bukti Sampel (PDF)' : 'Cetak Surat Jalan (PDF)' }}</span>
                                    </a>

                                    {{-- Cetak & Lihat Faktur Penjualan (Khusus Keuangan / Sales / Superadmin) --}}
                                    @if ($sj->faktur && $sj->status_cd !== 'BATAL' && (Auth::user()?->canAccessFaktur() ?? false))
                                        <a href="{{ route('penjualan.faktur.export-pdf', $sj->faktur->faktur_id) }}" target="_blank" class="action-dropdown-item" style="color: #059669; font-weight: 600;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Cetak Faktur Penjualan (PDF)</span>
                                        </a>
                                        <div class="action-dropdown-divider"></div>
                                        <a href="{{ route('penjualan.faktur.show', $sj->faktur->faktur_id) }}" class="action-dropdown-item" style="color: #0f766e;" title="Faktur Penjualan: {{ $sj->faktur->faktur_no }}">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Faktur (Office)</span>
                                        </a>
                                    @endif

                                    {{-- Batalkan Pengiriman (Void SJ - Khusus Supervisor / Berwenang) --}}
                                    @if ($sj->status_cd !== 'BATAL' && (Auth::user()?->canVoidPengeluaran() ?? false))
                                        <div class="action-dropdown-divider"></div>
                                        @if ($sj->faktur && (float) $sj->faktur->total_dibayar > 0)
                                            <div class="action-dropdown-item" style="color: #94a3b8; font-size: 0.75rem; cursor: not-allowed;" title="SJ terkunci karena Faktur Penjualan sudah ada pembayaran piutang.">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span>Terkunci (Ada Bayar)</span>
                                            </div>
                                        @else
                                            <button type="button" class="action-dropdown-item danger-item" onclick="openVoidModal('{{ $sj->pengeluaran_id }}', '{{ $sj->pengeluaran_no }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Batalkan (Void SJ)</span>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="sj-empty-state">
                                    <svg class="sj-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <div style="font-weight: 700; font-size: 1rem; color: #334155;">Belum Ada Dokumen Surat Jalan</div>
                                    <p style="font-size: 0.8125rem; margin: 0.35rem 0 1rem 0;">
                                        Dokumen pengeluaran fisik barang akan tercatat di halaman ini.
                                    </p>
                                    <a href="{{ route('penjualan.pengeluaran.create') }}" class="btn btn-primary" style="font-size: 0.825rem; font-weight: 700;">
                                        + Buat Surat Jalan Baru
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($suratJalanList->hasPages())
            <div style="padding: 0.85rem 1.25rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                {{ $suratJalanList->links() }}
            </div>
        @endif
    </div>
    </div>{{-- /#paneSj --}}

    {{-- ==============================================================
         TAB PANE 2: ANTREAN PO SIAP KIRIM (STANDARD ERP WORKLIST)
         ============================================================== --}}
    <div id="paneAntrean" class="sj-tab-pane {{ ($tab ?? 'antrean') === 'antrean' ? 'active' : '' }}" style="{{ ($tab ?? 'antrean') === 'antrean' ? '' : 'display: none;' }}">
        <div class="sj-table-card">
            <div class="sj-antrean-header">
                <div>
                    <h2 class="sj-antrean-title">Antrean PO Penjualan Menunggu Pengeluaran Fisik</h2>
                    <p class="sj-antrean-desc">
                        Daftar pesanan penjualan resmi yang siap dialokasikan batch FEFO untuk pengeluaran barang dan pengiriman.
                    </p>
                </div>
                <div>
                    <span class="sj-tab-badge amber" style="font-size: 0.775rem; padding: 4px 10px;">
                        {{ $readyOrders->count() }} PO Menunggu
                    </span>
                </div>
            </div>

            <div class="sj-table-responsive">
                <table class="sj-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">No. PO Penjualan</th>
                            <th style="width: 100px;">Tanggal PO</th>
                            <th style="min-width: 170px;">Mitra Customer &amp; Klasifikasi</th>
                            <th style="min-width: 220px;">Rincian Produk Dipesan</th>
                            <th style="min-width: 185px; text-align: left;">Kesiapan Stok</th>
                            <th style="text-align: right; width: 110px;">Total Pesan</th>
                            <th style="width: 115px;">Jadwal Kirim</th>
                            <th style="width: 140px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($readyOrders as $ro)
                            @php
                                $klasifikasi = $ro->customer?->klasifikasi ?? 'MANUAL';
                                $badgeStyle = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                                if ($klasifikasi === 'IFM') {
                                    $badgeStyle = 'background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;';
                                } elseif ($klasifikasi === 'IDM') {
                                    $badgeStyle = 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;';
                                } elseif (in_array($klasifikasi, ['MIDI_ALFA', 'MIDI', 'ALFA'])) {
                                    $badgeStyle = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;';
                                }
                                $totalKarton = (float) $ro->details->sum('pesan_qty');
                                $kirimQty = (float) $ro->details->sum('kirim_qty');
                                $sisaKarton = max(0, $totalKarton - $kirimQty);
                                $stockSt = $ro->stock_status ?? 'EMPTY';
                            @endphp
                            <tr>
                                <td>
                                    <span style="font-weight: 700; color: #0284c7; font-family: monospace; font-size: 0.85rem;">
                                        {{ $ro->so_no }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($ro->so_tgl)->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #1e293b; margin-bottom: 0.2rem;">
                                        {{ $ro->customer?->customer_nm ?? '-' }}
                                    </div>
                                    <span style="display: inline-block; font-size: 0.675rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; {{ $badgeStyle }}">
                                        {{ $klasifikasi }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.775rem; color: #475569; line-height: 1.55;">
                                        @if(!empty($ro->stock_item_summary))
                                            @foreach(array_slice($ro->stock_item_summary, 0, 3) as $itemSum)
                                                <div style="margin-bottom: 2px;">
                                                    <span style="color: #0f172a; font-weight: 600;">{{ $itemSum['barang_nm'] }}</span>: 
                                                    <span>{{ number_format($itemSum['sisa'], 0) }} ctn</span>
                                                    @if($itemSum['stok'] >= $itemSum['sisa'] && $itemSum['stok'] > 0)
                                                        <span style="color: #166534; font-size: 0.725rem; font-weight: 600;">(Stok: {{ number_format($itemSum['stok'], 0) }})</span>
                                                    @elseif($itemSum['stok'] > 0)
                                                        <span style="color: #b45309; font-size: 0.725rem; font-weight: 600;">(Stok: {{ number_format($itemSum['stok'], 0) }}, Kurang {{ number_format($itemSum['kurang'], 0) }})</span>
                                                    @else
                                                        <span style="color: #dc2626; font-size: 0.725rem; font-weight: 600;">(Stok: 0)</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if(count($ro->stock_item_summary) > 3)
                                                <div style="color: #64748b; font-size: 0.725rem; font-style: italic;">
                                                    +{{ count($ro->stock_item_summary) - 3 }} item lainnya...
                                                </div>
                                            @endif
                                        @else
                                            @foreach($ro->details->take(3) as $dtl)
                                                @php $sisaItem = max(0, (float)$dtl->pesan_qty - (float)$dtl->kirim_qty); @endphp
                                                <div>&bull; {{ $dtl->barang?->barang_nm ?? '-' }} <span style="font-weight: 600; color: #0f172a;">({{ number_format($sisaItem, 0, ',', '.') }} ctn)</span></div>
                                            @endforeach
                                        @endif
                                    </div>
                                </td>

                                {{-- Kesiapan Stok: Badge Enterprise Rapi & Informatif --}}
                                <td style="white-space: nowrap; vertical-align: middle;">
                                    @if($stockSt === 'READY')
                                        <div style="display: flex; flex-direction: column; gap: 3px; align-items: flex-start; white-space: nowrap;">
                                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; font-size: 0.725rem; font-weight: 700; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 4px; white-space: nowrap;">
                                                <svg width="7" height="7" fill="#059669" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                Siap Kirim
                                            </span>
                                            <div style="font-size: 0.75rem; color: #166534; font-weight: 600; white-space: nowrap;">
                                                Stok Lengkap: {{ number_format($ro->total_ready_stok ?? $totalKarton, 0) }} / {{ number_format($totalKarton, 0) }} ctn
                                            </div>
                                        </div>
                                    @elseif($stockSt === 'PARTIAL')
                                        <div style="display: flex; flex-direction: column; gap: 3px; align-items: flex-start; white-space: nowrap;">
                                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; font-size: 0.725rem; font-weight: 700; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; border-radius: 4px; white-space: nowrap;">
                                                <svg width="7" height="7" fill="#d97706" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                Stok Sebagian
                                            </span>
                                            <div style="font-size: 0.75rem; color: #334155; white-space: nowrap;">
                                                <span style="color: #b45309; font-weight: 600;">Ada {{ number_format($ro->total_ready_stok ?? 0, 0) }} ctn</span>
                                                <span style="color: #94a3b8; margin: 0 3px;">&bull;</span>
                                                <span style="color: #dc2626; font-weight: 600;">Kurang {{ number_format($ro->total_kurang_stok ?? 0, 0) }} ctn</span>
                                            </div>
                                        </div>
                                    @else
                                        <div style="display: flex; flex-direction: column; gap: 3px; align-items: flex-start; white-space: nowrap;">
                                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; font-size: 0.725rem; font-weight: 700; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 4px; white-space: nowrap;">
                                                <svg width="7" height="7" fill="#dc2626" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                Menunggu Produksi
                                            </span>
                                            <div style="font-size: 0.75rem; color: #64748b; white-space: nowrap;">
                                                Stok Gudang: <strong style="color: #dc2626;">0 ctn</strong>
                                                <span style="color: #94a3b8; margin: 0 3px;">&bull;</span>
                                                <span>Kurang {{ number_format($totalKarton, 0) }} ctn</span>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <td style="text-align: right;">
                                    <span style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                        {{ number_format($totalKarton, 0, ',', '.') }}
                                    </span>
                                    <span style="font-size: 0.725rem; color: #64748b;">Karton</span>
                                    @if ($ro->status_cd === 'PARTIAL' || ($kirimQty > 0 && $sisaKarton > 0))
                                        <div style="margin-top: 0.2rem;">
                                            <span style="display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 0.675rem; font-weight: 700; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                                Sisa: {{ number_format($sisaKarton, 0, ',', '.') }} ctn
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($ro->tgl_kirim_estimasi)
                                        @php
                                            $estDate = \Carbon\Carbon::parse($ro->tgl_kirim_estimasi)->startOfDay();
                                            $today = \Carbon\Carbon::today();
                                            $isOverdue = $estDate->lt($today);
                                            $isToday = $estDate->equalTo($today);
                                        @endphp
                                        <div style="font-size: 0.8rem; font-weight: 600; color: #0f172a;">
                                            {{ $estDate->format('d/m/Y') }}
                                        </div>
                                        @if($isOverdue)
                                            <span style="display: inline-block; font-size: 0.675rem; font-weight: 700; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; padding: 1px 5px; border-radius: 4px; margin-top: 0.15rem;">
                                                Lewat Jadwal
                                            </span>
                                        @elseif($isToday)
                                            <span style="display: inline-block; font-size: 0.675rem; font-weight: 700; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 1px 5px; border-radius: 4px; margin-top: 0.15rem;">
                                                Kirim Hari Ini
                                            </span>
                                        @endif
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if($stockSt === 'READY')
                                        <a href="{{ route('penjualan.pengeluaran.create', ['so_id' => $ro->so_id]) }}" 
                                           class="btn btn-primary btn-sm" 
                                           style="font-size: 0.8125rem; font-weight: 700; padding: 0.4rem 0.9rem; border-radius: 4px; background: #059669; border: 1px solid #047857; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"
                                           title="Keluarkan fisik barang dan terbitkan Surat Jalan">
                                            Keluarkan Barang
                                        </a>
                                    @elseif($stockSt === 'PARTIAL')
                                        <a href="{{ route('penjualan.pengeluaran.create', ['so_id' => $ro->so_id]) }}" 
                                           class="btn btn-warning btn-sm" 
                                           style="font-size: 0.8125rem; font-weight: 700; padding: 0.4rem 0.9rem; border-radius: 4px; background: #d97706; border: 1px solid #b45309; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"
                                           title="Keluarkan sebagian barang yang sudah tersedia di gudang">
                                            Kirim Sebagian
                                        </a>
                                    @else
                                        <button type="button" 
                                                disabled 
                                                class="btn btn-sm" 
                                                style="font-size: 0.8125rem; font-weight: 600; padding: 0.4rem 0.85rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #f8fafc; color: #94a3b8; cursor: not-allowed; display: inline-flex; align-items: center; justify-content: center;"
                                                title="Stok fisik belum tersedia di gudang (Menunggu Produksi)">
                                            Keluarkan Barang
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                        <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span style="font-weight: 600; color: #475569;">Tidak ada PO dalam antrean siap kirim</span>
                                        <span style="font-size: 0.775rem; color: #94a3b8;">Semua pesanan penjualan yang disetujui telah diproses pengeluarannya.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

{{-- MODAL VOID / PEMBATALAN SURAT JALAN (Hanya untuk Pengguna Berwenang) --}}
@if (Auth::user()?->canVoidPengeluaran() ?? false)
    @include('penjualan.pengeluaran.partials.modal-void')
@endif

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/pengeluaran/pengeluaran-index.js') }}"></script>
@endpush
