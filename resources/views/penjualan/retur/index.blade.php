@extends('layouts.app')

@section('title', 'Rekapitulasi Retur Penjualan (Customer Returns) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/retur/retur-index.css') }}">
@endpush

@section('content')

{{-- TOP HEADER COMMAND --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
            Retur Penjualan (Customer Returns)
        </h1>
        <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">
            Monitoring rekapitulasi penerimaan barang rusak/cacat dan penyesuaian piutang customer PT Mirasa.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <a href="{{ route('penjualan.retur.export-excel', request()->query()) }}" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.825rem; font-weight: 700; padding: 0.45rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: all 0.15s ease-in-out;" onmouseover="this.style.background='#ecfdf5'" onmouseout="this.style.background='#ffffff'" title="Export Rekap Retur Penjualan ke file Excel (.xlsx) mengikuti filter yang aktif">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export Excel</span>
        </a>
        <a href="{{ route('penjualan.retur.create') }}" class="btn btn-primary" style="font-size: 0.825rem; padding: 0.45rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer; text-decoration: none;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Catat Retur Baru</span>
        </a>
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL RETUR --}}
<div class="retur-metrics-grid">
    {{-- KARTU 1: TOTAL TRANSAKSI RETUR --}}
    <div class="retur-metric-card">
        <div class="retur-metric-header">
            <span class="retur-metric-title">Total Berita Acara</span>
            <div class="retur-metric-icon slate">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="retur-metric-val">{{ number_format($kpi['total_trx'], 0) }}</div>
        <div class="retur-metric-subtext">Dokumen klaim retur pelanggan</div>
    </div>

    {{-- KARTU 2: TOTAL KUANTITAS DIRETUR --}}
    <div class="retur-metric-card">
        <div class="retur-metric-header">
            <span class="retur-metric-title">Total Barang Diretur</span>
            <div class="retur-metric-icon blue">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div class="retur-metric-val">{{ number_format($kpi['total_qty'], 0) }} <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">Karton</span></div>
        <div class="retur-metric-subtext">Masuk kembali ke stok gudang</div>
    </div>

    {{-- KARTU 3: TOTAL NILAI RUPIAH RETUR --}}
    <div class="retur-metric-card">
        <div class="retur-metric-header">
            <span class="retur-metric-title">Total Nilai Retur</span>
            <div class="retur-metric-icon emerald">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="retur-metric-val" style="color: #059669; font-size: 1.3rem;">Rp {{ number_format($kpi['total_nilai'], 0, ',', '.') }}</div>
        <div class="retur-metric-subtext">Akumulasi komersial terpotong</div>
    </div>

    {{-- KARTU 4: RETUR BULAN INI --}}
    <div class="retur-metric-card">
        <div class="retur-metric-header">
            <span class="retur-metric-title">Retur Bulan Ini</span>
            <div class="retur-metric-icon blue">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="retur-metric-val">{{ number_format($kpi['qty_bulan_ini'], 0) }} <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">Karton</span></div>
        <div class="retur-metric-subtext">Periode: {{ date('F Y') }}</div>
    </div>
</div>

{{-- KARTU TABEL UTAMA DENGAN SEARCH & FILTER SESUAI STANDAR --}}
<div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    {{-- BARIS UTAMA FILTER & SEARCH --}}
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('penjualan.retur.index') }}" method="GET" id="searchFilterForm" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; flex: 1;">
            {{-- Search: (No. Retur / No. SO / Customer / Nama Barang) --}}
            <div style="position: relative; flex: 1; min-width: 260px; max-width: 380px;">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Search : (No. Retur / No. SO / Customer / Barang)" 
                       class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%; box-sizing: border-box;">
            </div>
            
            {{-- Dropdown Status --}}
            <select name="status_cd" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 175px; font-size: 0.85rem;" onchange="document.getElementById('searchFilterForm').submit()">
                <option value="">-- Semua Status --</option>
                <option value="CONFIRMED" {{ (($filters['status_cd'] ?? '') === 'CONFIRMED') ? 'selected' : '' }}>Terkonfirmasi</option>
                <option value="VOID" {{ (($filters['status_cd'] ?? '') === 'VOID') ? 'selected' : '' }}>Dibatalkan (Void)</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Cari</button>

            {{-- Tombol Toggle Filter Spesifik (Tanggal, Customer, Tindakan) --}}
            <button type="button" onclick="toggleFilterDrawer()" class="btn btn-sm" style="background: {{ $hasActiveFilter ? '#e0f2fe' : '#f1f5f9' }}; color: {{ $hasActiveFilter ? '#0284c7' : '#475569' }}; border: 1px solid {{ $hasActiveFilter ? '#bae6fd' : '#cbd5e1' }}; padding: 0.45rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>Filter Spesifik</span>
                @if($hasActiveFilter)
                    <span style="background: #0284c7; color: white; border-radius: 9999px; font-size: 0.65rem; padding: 1px 5px;">Aktif</span>
                @endif
            </button>

            @if(!empty($search) || !empty($filters['status_cd']) || $hasActiveFilter)
                <a href="{{ route('penjualan.retur.index') }}" class="btn btn-secondary btn-sm" title="Reset Semua Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif

            {{-- Hidden input sync untuk drawer jika submit dari form atas --}}
            <input type="hidden" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            <input type="hidden" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            <input type="hidden" name="customer_id" value="{{ $filters['customer_id'] ?? '' }}">
            <input type="hidden" name="tindakan_cd" value="{{ $filters['tindakan_cd'] ?? '' }}">
        </form>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="{{ route('penjualan.retur.export-excel', request()->query()) }}" class="btn btn-secondary btn-sm" style="background: #ffffff; border: 1.5px solid #059669; color: #047857; font-weight: 600; padding: 0.45rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none;" title="Download Data Sesuai Filter ke Format Excel (.xlsx)">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>
            <span style="color: #64748b; font-size: 0.85rem; white-space: nowrap;">
                Total Retur: <strong style="color: #0f172a;">{{ $returns->total() }}</strong> dokumen
            </span>
        </div>
    </div>

    {{-- FILTER DRAWER: SPESIFIK TANGGAL, CUSTOMER, & TINDAKAN --}}
    <div id="filterDrawer" style="display: {{ $hasActiveFilter ? 'block' : 'none' }}; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem;">
        <div style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            Filter Spesifik Retur Penjualan
        </div>
        <form action="{{ route('penjualan.retur.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: end;">
            @if(!empty($search))
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            @if(!empty($filters['status_cd']))
                <input type="hidden" name="status_cd" value="{{ $filters['status_cd'] }}">
            @endif

            {{-- 1. Dari Tanggal Retur --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem; display: block;">
                    Dari Tanggal Retur
                </label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.65rem;">
            </div>

            {{-- 2. Sampai Tanggal Retur --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem; display: block;">
                    Sampai Tanggal Retur
                </label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.65rem;">
            </div>

            {{-- 3. Mitra Customer --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem; display: block;">
                    Mitra Customer
                </label>
                <select name="customer_id" class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.65rem; background: #ffffff;">
                    <option value="">-- Semua Customer --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->customer_id }}" {{ ($filters['customer_id'] ?? '') == $c->customer_id ? 'selected' : '' }}>
                            {{ $c->customer_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 4. Tindakan Komersial --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem; display: block;">
                    Tindakan Komersial
                </label>
                <select name="tindakan_cd" class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.65rem; background: #ffffff;">
                    <option value="">-- Semua Tindakan --</option>
                    <option value="POTONG_TAGIHAN" {{ ($filters['tindakan_cd'] ?? '') === 'POTONG_TAGIHAN' ? 'selected' : '' }}>Potong Tagihan (Credit Note)</option>
                    <option value="TUKAR_GULING" {{ ($filters['tindakan_cd'] ?? '') === 'TUKAR_GULING' ? 'selected' : '' }}>Tukar Guling Fisik</option>
                </select>
            </div>

            {{-- Tombol Submit Filter Spesifik --}}
            <div style="display: flex; gap: 0.4rem;">
                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.4rem 0.85rem; font-size: 0.8rem;">
                    Terapkan Filter
                </button>
                <a href="{{ route('penjualan.retur.index', ['search' => $search, 'status_cd' => $filters['status_cd'] ?? '']) }}" class="btn btn-secondary btn-sm" style="padding: 0.4rem 0.65rem; font-size: 0.8rem;">
                    Batal
                </a>
            </div>
        </form>
    </div>
    <div style="overflow-x: auto;">
        <table class="table-retur">
            <thead>
                <tr>
                    <th style="width: 35px; text-align: center;">No</th>
                    <th style="min-width: 135px;">No. Retur &amp; Tgl</th>
                    <th style="min-width: 135px;">Dokumen SO / Faktur</th>
                    <th style="min-width: 160px;">Mitra Customer</th>
                    <th style="width: 105px;">Tindakan</th>
                    <th style="min-width: 180px;">Rincian Barang</th>
                    <th style="min-width: 160px;">Disposisi Fisik Barang</th>
                    <th style="width: 80px; text-align: right;">Total Qty</th>
                    <th style="width: 120px; text-align: right;">Total Nilai</th>
                    <th style="width: 95px; text-align: center;">Status</th>
                    <th style="width: 75px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $idx => $item)
                    @php
                        $menuId = 'smartActionRetur_' . $item->retur_id;
                        $qtyMasuk = (float) $item->total_qty_masuk_persediaan;
                        $qtyMusnah = (float) $item->total_qty_dimusnahkan;
                        if ($qtyMasuk == 0 && $qtyMusnah == 0) {
                            $qtyMasuk = (float) $item->total_qty; // fallback data lama
                        }
                    @endphp
                    <tr class="{{ $item->status_cd === 'VOID' ? 'tr-void' : '' }}">
                        <td style="text-align: center; color: #64748b; font-size: 0.8rem;">
                            {{ $returns->firstItem() + $idx }}
                        </td>
                        <td>
                            <a href="{{ route('penjualan.so.show', $item->so_id) }}" class="retur-code-link">
                                {{ $item->retur_no }}
                            </a>
                            <span class="retur-date-sub">
                                {{ $item->retur_tgl ? $item->retur_tgl->format('d/m/Y') : '-' }}
                            </span>
                            @if($item->alasan_txt)
                                <div class="retur-note-tag" title="{{ $item->alasan_txt }}">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                    <span>{{ Str::limit($item->alasan_txt, 30) }}</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('penjualan.so.show', $item->so_id) }}" class="doc-link-so">
                                {{ $item->order?->so_no ?? '-' }}
                            </a>
                            <span class="doc-sub-faktur">
                                Faktur: {{ $item->order?->faktur_no ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="customer-nm">{{ $item->customer?->customer_nm ?? '-' }}</div>
                            <div class="customer-cd">{{ $item->customer?->customer_cd ?? '-' }}</div>
                        </td>
                        <td>
                            @if($item->tindakan_cd === 'POTONG_TAGIHAN')
                                <span class="badge-tindakan-potong" title="Pengurangan saldo piutang customer / nota kredit">Potong Tagihan</span>
                            @else
                                <span class="badge-tindakan-tukar" title="Tukar guling fisik barang">Tukar Guling</span>
                            @endif
                        </td>
                        <td>
                            <div class="retur-item-list">
                                @foreach($item->details as $dtl)
                                    <div class="retur-item-row">
                                        <span class="retur-item-name">{{ $dtl->barang?->barang_nm ?? '-' }}</span>
                                        <span class="retur-item-qty">{{ number_format((float) $dtl->qty_retur, 0) }} unit</span>
                                        <span class="retur-item-cond">({{ $dtl->kondisi_label }})</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div class="disposisi-tags-wrapper">
                                @if($qtyMasuk > 0)
                                    <span class="disposisi-tag disposisi-tag-kw2" title="Masuk ke persediaan sortir untuk diolah kembali jadi KW 2">
                                        <svg class="disp-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        <span>Sortir KW 2: <strong>{{ number_format($qtyMasuk, 0) }}</strong></span>
                                    </span>
                                @endif
                                @if($qtyMusnah > 0)
                                    <span class="disposisi-tag disposisi-tag-musnah" title="Barang cacat/rusak langsung dimusnahkan resmi di pabrik">
                                        <svg class="disp-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Dimusnahkan: <strong>{{ number_format($qtyMusnah, 0) }}</strong></span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right;" class="col-tabular">
                            {{ number_format((float) $item->total_qty, 0) }}
                        </td>
                        <td style="text-align: right;" class="col-tabular">
                            Rp {{ number_format((float) $item->total_nilai, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            {!! $item->status_badge !!}
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <a href="{{ route('penjualan.retur.show', $item->retur_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Detail SO</span>
                                </a>

                                <a href="{{ route('penjualan.retur.export-pdf', $item->retur_id) }}" target="_blank" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak BA Penerimaan Retur (PDF)</span>
                                </a>

                                @if($qtyMusnah > 0)
                                    <a href="{{ route('penjualan.retur.berita-acara-pemusnahan', $item->retur_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>BAP Pemusnahan (Web &amp; Cetak)</span>
                                    </a>
                                @endif

                                @if($item->status_cd === 'CONFIRMED')
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item danger-item" onclick="openVoidModal({{ $item->retur_id }}, '{{ $item->retur_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batalkan (Void)</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4 4m-4-4l4-4m12 11a4 4 0 004-4v-1a4 4 0 00-4-4h-4"/></svg>
                                <span style="font-weight: 600; color: #64748b;">Belum Ada Dokumen Retur Penjualan</span>
                                <span style="font-size: 0.775rem;">Semua barang terkirim ke customer aman dan belum ada klaim pengembalian.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($returns->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; background: #ffffff;">
            {{ $returns->links() }}
        </div>
    @endif
</div>

{{-- MODAL CREATE RETUR & MODAL VOID RETUR --}}
@include('penjualan.retur.partials.modal-create-retur')
@include('penjualan.retur.partials.modal-void-retur')

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/retur/retur-index.js') }}"></script>
    <script>
        function toggleFilterDrawer() {
            const drawer = document.getElementById('filterDrawer');
            if (!drawer) return;
            const isHidden = (drawer.style.display === 'none' || drawer.style.display === '');
            drawer.style.display = isHidden ? 'block' : 'none';
        }
    </script>
@endpush
