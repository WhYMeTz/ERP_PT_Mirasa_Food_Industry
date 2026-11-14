@extends('layouts.app')

@section('title', 'Modul Produksi & Manufaktur - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}?v={{ filemtime(public_path('css/produksi/produksi-index.css')) }}">
@endpush

@section('content')
<div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
            Hasil Barang Produksi Pabrik
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
            Pencatatan output fisik barang setengah jadi (WIP) hasil olahan &amp; penggorengan, histori pengerjaan shift, dan mutasi kartu stok gudang simpan.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('produksi.export-hasil', request()->all()) }}" 
           class="btn-export-excel" 
           title="Download data hasil produksi format Excel (.xlsx)">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export Excel</span>
        </a>

        <button type="button" 
                onclick="openModalImportHasil()" 
                class="btn-import-excel" 
                title="Import data hasil produksi dari berkas Excel">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <span>Import Excel</span>
        </button>

        @if(Auth::user()->canCreateProduksi())
            <a href="{{ route('produksi.rework.create') }}" 
               class="btn-rework-amber" 
               title="Formulir Sortir Ulang / Rework Produk">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Sortir Ulang / Rework</span>
            </a>

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

@php
    $pageTotalBeratKg = $produksiList->getCollection()->sum(function($p) {
        return $p->details->sum('qty_kg');
    });
    $pageTotalSisaStok = $produksiList->getCollection()->sum(function($p) {
        return $p->details->sum(fn($d) => (float) ($d->sisa_stok ?? $d->qty_kg));
    });
    $totalCount = $produksiList->total();
    $statLabel = 'Total Dokumen Produksi';
    $statSubtext = 'SPK / Proses Produksi Pabrik';
    $statUnit = 'Dokumen';
@endphp

    {{-- 3 KARTU METRIK EKSEKUTIF (SUBTOTAL & STOK PRODUKSI LANGSUNG TERBACA DI ATAS) --}}
    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL DOKUMEN / RINCIAN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">{{ $statLabel }}</span>
                    <div class="executive-stat-value">
                        {{ number_format($totalCount, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">{{ $statUnit }}</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #f1f5f9; color: #475569;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                {{ $statSubtext }}
            </div>
        </div>

        {{-- METRIK 2: TOTAL BERAT BERSIH --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #0284c7;">Total Berat Hasil</span>
                    <div class="executive-stat-value" style="color: #0284c7;">
                        {{ number_format($pageTotalBeratKg, 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Akumulasi berat bersih output WIP
            </div>
        </div>

        {{-- METRIK 3: SISA STOK FISIK --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #059669;">Sisa Stok Fisik</span>
                    <div class="executive-stat-value" style="color: #059669;">
                        {{ number_format($pageTotalSisaStok, 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #d1fae5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Stok fisik WIP belum dikemas
            </div>
        </div>
    </div>

    {{-- FILTER CARD ENTERPRISE (TERPUSAT & ERGONOMIS) --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('produksi.index') }}">
            <div class="filter-grid">
                <div>
                    <label class="filter-label">Gudang Simpan</label>
                    <select name="gudang_id" class="filter-input">
                        <option value="">-- Semua Gudang Simpan --</option>
                        @foreach($gudangList as $g)
                            <option value="{{ $g->gudang_id }}" {{ request('gudang_id') == $g->gudang_id ? 'selected' : '' }}>
                                {{ $g->gudang_nm }} ({{ $g->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label">Kode Barang</label>
                    <input type="text" name="barang_cd" class="filter-input" placeholder="Misal: BRG-001 / WIP..." value="{{ request('barang_cd') }}">
                </div>

                <div>
                    <label class="filter-label">Nama Barang</label>
                    <input type="text" name="barang_nm" class="filter-input" placeholder="Misal: Keripik / Berko..." value="{{ request('barang_nm') }}">
                </div>

                <div>
                    <label class="filter-label">Kode Batch</label>
                    <input type="text" name="batch_no" class="filter-input" placeholder="Misal: A0001 / 27 10..." value="{{ request('batch_no') }}">
                </div>

                <div>
                    <label class="filter-label">Tanggal Dari</label>
                    <input type="date" name="tgl_dari" class="filter-input" value="{{ request('tgl_dari') }}">
                </div>

                <div>
                    <label class="filter-label">Tanggal Sampai</label>
                    <input type="date" name="tgl_sampai" class="filter-input" value="{{ request('tgl_sampai') }}">
                </div>

                <div style="display: flex; gap: 0.4rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; flex: 1;">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('produksi.index') }}" class="btn btn-secondary" style="padding: 0.45rem 0.65rem; font-size: 0.825rem; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- WADAH TABEL UTAMA (SERAGAM DENGAN MODUL LAIN: CARD ENTERPRISE) --}}
    <div class="card" style="margin-bottom: 1.5rem; overflow: hidden;">
        {{-- HEADER TABEL DAFTAR DOKUMEN HASIL PRODUKSI --}}
        <div style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #0f172a;">Daftar Dokumen Hasil Produksi</h3>
                <span style="font-size: 0.75rem; color: #64748b; background: #f1f5f9; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600;">
                    {{ number_format($totalCount, 0, ',', '.') }} Dokumen
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                <span style="background: #f0f9ff; color: #0369a1; border: 1.5px solid #bae6fd; font-size: 0.825rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    <span>Total Berat Bersih Halaman:</span>
                    <strong style="font-size: 0.9rem; font-family: monospace;">{{ number_format($pageTotalBeratKg, 2, ',', '.') }} kg</strong>
                </span>
            </div>
        </div>
            {{-- VIEW 1: DAFTAR DOKUMEN PRODUKSI (STANDAR MANUFAKTUR ERP - 1 BARIS PER NOMOR PRODUKSI) --}}
            <div style="overflow-x: auto;">
                <table class="table-prod-main">
                    <thead>
                        <tr>
                            <th style="padding: 0.75rem 1rem; width: 45px; text-align: center;">No</th>
                            <th style="padding: 0.75rem 1rem; min-width: 180px;">Dokumen &amp; Tanggal</th>
                            <th style="padding: 0.75rem 1rem; min-width: 150px;">Gudang Simpan</th>
                            <th style="padding: 0.75rem 0.85rem; min-width: 480px;">
                                <div class="prod-item-header-grid">
                                    <span class="sub-col-title">Varian Produk (WIP)</span>
                                    <span class="sub-col-title">Output Fisik</span>
                                    <span class="sub-col-title">Sisa Gudang</span>
                                </div>
                            </th>
                            <th style="padding: 0.75rem 1rem; min-width: 120px; text-align: right;">Total Berat</th>
                            <th style="padding: 0.75rem 1rem; min-width: 130px; text-align: center;">Status Kalkulasi HPP</th>
                            <th style="padding: 0.75rem 1rem; width: 95px; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produksiList as $idx => $prod)
                            @php
                                $menuId = 'menu-prod-' . $prod->produksi_id;
                                $isPendingBatch = in_array(strtoupper(trim($prod->batch_wip_no ?? '')), ['BELUM TERBIT', 'PENDING', '-']) || empty($prod->batch_wip_no);
                                $isReworkDoc = str_contains(strtoupper($prod->lini_produksi ?? ''), 'SORTIR') || str_contains(strtoupper($prod->lini_produksi ?? ''), 'REWORK');
                                $liniClean = trim(str_ireplace('PRODUKSI', '', $prod->lini_produksi ?? ''));
                                $totalDocKg = (float) $prod->details->sum('qty_kg');
                                $hasHpp = (float) ($prod->hpp_per_kg ?? 0) > 0;
                            @endphp
                            <tr class="prod-main-row">
                                {{-- 1. NO --}}
                                <td style="padding: 0.75rem 1rem; text-align: center; color: #64748b; font-size: 0.85rem; vertical-align: top;">
                                    {{ $produksiList->firstItem() + $idx }}
                                </td>

                                {{-- 2. DOKUMEN & TANGGAL --}}
                                <td style="padding: 0.65rem 0.85rem; vertical-align: top;">
                                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                        <a href="{{ route('produksi.show', $prod->produksi_id) }}" 
                                           class="prod-doc-title"
                                           title="Klik untuk melihat detail SPK dokumen produksi ini">
                                            {{ $prod->produksi_no }}
                                        </a>
                                        @if($isReworkDoc)
                                            <span class="badge-rework-tag">REWORK</span>
                                        @endif
                                    </div>
                                    <div class="prod-doc-meta">
                                        <span>{{ \Carbon\Carbon::parse($prod->produksi_tgl)->format('d/m/Y') }}</span>
                                        @if($prod->shift_cd)
                                            <span>&bull;</span>
                                            <span>Shift {{ $prod->shift_cd }}</span>
                                        @endif
                                        @if($liniClean)
                                            <span>&bull;</span>
                                            <span>{{ $liniClean }}</span>
                                        @endif
                                    </div>
                                    @if($prod->po_ifl_no)
                                        <div class="prod-doc-po">
                                            PO: <span>{{ $prod->po_ifl_no }}</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- 3. GUDANG SIMPAN & BATCH UTAMA --}}
                                <td style="padding: 0.65rem 0.85rem; vertical-align: top;">
                                    <div class="prod-gdg-title">
                                        {{ $prod->gudang?->gudang_nm ?? '-' }}
                                    </div>
                                    <div class="prod-gdg-code">
                                        {{ $prod->gudang?->gudang_cd ?? '' }}
                                    </div>
                                    <div style="margin-top: 0.35rem;">
                                        @if($isPendingBatch)
                                            <span class="badge-batch-pending" style="font-size: 0.7rem; padding: 0.1rem 0.4rem;" title="Batch Buffer Stock IFM ditangguhkan">
                                                BATCH BELUM TERBIT
                                            </span>
                                        @else
                                            <span class="prod-batch-chip">
                                                Batch: {{ $prod->batch_wip_no }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($prod->exp_date)
                                        <div class="prod-exp-date">
                                            Exp: {{ \Carbon\Carbon::parse($prod->exp_date)->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 4. RINCIAN HASIL OLAHAN FISIK (FORMAT LIST BERSIH, TERSTRUKTUR & NYAMAN DIBACA) --}}
                                <td style="padding: 0.5rem 0.85rem; vertical-align: top;">
                                    <div class="prod-item-list">
                                        @forelse($prod->details as $item)
                                            @php
                                                $barang = $item->barang;
                                                $satuanNm = strtoupper(trim($item->satuan_cd ?: ($barang?->satuan?->satuan_nm ?? 'KG')));
                                                $qtyTampil = $item->qty_hasil > 0 ? (float) $item->qty_hasil : (float) $item->qty_kg;
                                                $qtyKg = (float) $item->qty_kg;
                                                $sisaStok = (float) ($item->sisa_stok ?? $item->qty_kg);
                                                $isDifferentUnit = ($satuanNm !== 'KG' && $satuanNm !== '') && abs($qtyTampil - $qtyKg) > 0.001;
                                            @endphp
                                            <div class="prod-item-row">
                                                {{-- KOLOM 1: NAMA BARANG & KODE (RATA KIRI) --}}
                                                <div class="prod-item-left">
                                                    <span class="prod-item-name">{{ $barang?->barang_nm ?? '-' }}</span>
                                                    @if($barang?->barang_cd)
                                                        <span class="prod-item-code">{{ $barang->barang_cd }}</span>
                                                    @endif
                                                    @if(!empty($item->batch_no) && $item->batch_no !== $prod->batch_wip_no)
                                                        <span class="prod-item-batch">B: {{ $item->batch_no }}</span>
                                                    @endif
                                                </div>

                                                {{-- KOLOM 2: OUTPUT FISIK (RATA KIRI) --}}
                                                <div class="prod-item-qty">
                                                    @if($isDifferentUnit)
                                                        <strong>{{ number_format($qtyTampil, 0, ',', '.') }}</strong>
                                                        <span class="unit-text">{{ $satuanNm }}</span>
                                                        <span class="kg-alt">({{ number_format($qtyKg, 2, ',', '.') }} kg)</span>
                                                    @else
                                                        <strong>{{ number_format($qtyKg, 2, ',', '.') }}</strong>
                                                        <span class="unit-text">kg</span>
                                                    @endif
                                                </div>

                                                {{-- KOLOM 3: SISA STOK GUDANG (RATA KIRI) --}}
                                                <div class="prod-item-stock">
                                                    @if($sisaStok > 0)
                                                        <span class="badge-stock-pill" title="Sisa persediaan fisik di gudang">
                                                            Sisa {{ number_format($sisaStok, $isDifferentUnit ? 0 : 1, ',', '.') }} {{ $isDifferentUnit ? $satuanNm : 'kg' }}
                                                        </span>
                                                    @else
                                                        <span class="badge-stock-empty">Habis</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div style="color: #94a3b8; font-style: italic; font-size: 0.8rem; padding: 0.25rem 0;">
                                                Tidak ada rincian output
                                            </div>
                                        @endforelse
                                    </div>
                                </td>

                                {{-- 5. TOTAL BERAT DOKUMEN --}}
                                <td style="padding: 0.65rem 0.85rem; text-align: right; vertical-align: top;">
                                    <div class="prod-total-kg">
                                        {{ number_format($totalDocKg, 2, ',', '.') }} <span class="prod-total-unit">kg</span>
                                    </div>
                                    <small class="prod-total-count">
                                        {{ $prod->details->count() }} jenis output
                                    </small>
                                </td>

                                {{-- 6. STATUS KALKULASI HPP --}}
                                <td style="padding: 0.65rem 0.85rem; text-align: center; vertical-align: top;">
                                    @if($hasHpp)
                                        <div class="badge-hpp-pill done">
                                            <span>Sudah Dihitung</span>
                                        </div>
                                    @else
                                        <div class="badge-hpp-pill pending">
                                            <span>Belum Dihitung</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- 7. TOMBOL AKSI TERPADU (1 KALI PER DOKUMEN) --}}
                                <td style="padding: 0.65rem 0.85rem; text-align: right; position: relative; vertical-align: top;">
                                    <button type="button" 
                                            class="btn-action-trigger" 
                                            onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                        <span>Aksi</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <div id="{{ $menuId }}" class="action-dropdown-menu">
                                        <a href="{{ route('produksi.show', $prod->produksi_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Detail Produksi</span>
                                        </a>

                                        @if(Auth::user()->canCreateProduksi() && (in_array($prod->batch_wip_no, ['BELUM TERBIT', 'PENDING']) || empty($prod->batch_wip_no)))
                                            <div class="action-dropdown-divider"></div>
                                            <button type="button" 
                                                    class="action-dropdown-item" 
                                                    style="color: #d97706; font-weight: 700;" 
                                                    onclick="openAssignBatchModal({{ $prod->produksi_id }}, '{{ $prod->produksi_no }}', {{ (int) $prod->qty_karton }})">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                <span>Beri Nomor Batch</span>
                                            </button>
                                        @endif

                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteProduksiModal({{ $prod->produksi_id }}, '{{ $prod->produksi_no }}', '{{ \Carbon\Carbon::parse($prod->produksi_tgl)->format('d/m/Y') }}', '{{ $prod->shift_cd }}', '{{ $prod->batch_wip_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus Produksi</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2.5rem; color: #64748b;">
                                    <div style="font-size: 0.95rem; font-weight: 700; color: #334155;">Belum Ada Data Transaksi Produksi</div>
                                    <div style="font-size: 0.8rem; margin-top: 0.25rem;">Gunakan tombol <strong>+ Catat Hasil Produksi</strong> di atas untuk menambahkan data.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINASI TABEL DOKUMEN --}}
            <div style="padding: 0.875rem 1.25rem; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="font-size: 0.8rem; color: #64748b;">
                    Menampilkan {{ $produksiList->firstItem() ?? 0 }} - {{ $produksiList->lastItem() ?? 0 }} dari total {{ $produksiList->total() }} dokumen produksi
                </div>
                <div>
                    {{ $produksiList->links() }}
                </div>
            </div>
    </div>

{{-- MODALS PARTIALS --}}
@include('produksi.partials.modal-import-hasil')
@include('produksi.partials.modal-delete-confirm')
@include('produksi.partials.modal-assign-batch')

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-index.js') }}?v={{ filemtime(public_path('js/produksi/produksi-index.js')) }}"></script>
@endpush
@endsection
