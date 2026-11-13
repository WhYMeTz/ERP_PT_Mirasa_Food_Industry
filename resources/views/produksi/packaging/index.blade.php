@extends('layouts.app')

@section('title', 'Modul Pengemasan & HPP Barang Jadi (Packaging) - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-index.css') }}">
@endpush

@section('content')
<div class="packaging-index-container">
    {{-- Header Halaman (Identik dengan Hasil Barang Produksi & Buku Rekapitulasi) --}}
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                Riwayat Pengemasan Barang Jadi (FG)
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
                Pencatatan konversi keripik curah (WIP) ke produk kemasan (FG), histori pengerjaan, dan mutasi stok gudang barang jadi.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Mode Input Mobile / Layar Sentuh: Cepat untuk Operator di Lantai Kemas --}}
            <a href="{{ route('produksi.packaging.mobile') }}" 
               class="btn-header-mobile" 
               title="Buka formulir input cepat khusus smartphone / tablet di lantai kemas">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Mode Input HP / Tablet</span>
            </a>

            @if(Auth::user()->canCreateProduksi())
                <a href="{{ route('produksi.packaging.create') }}" 
                   class="btn-header-create" 
                   title="Buka formulir input lengkap pengemasan Finished Goods desktop">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Catat Pengemasan Baru</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Notifikasi Sukses & Error --}}
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

    {{-- 4 KARTU METRIK EKSEKUTIF (SERAGAM 100% DENGAN REKAP PRODUKSI & MODUL LAIN) --}}
    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL DOKUMEN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Total Dokumen
                    </span>
                    <div class="executive-stat-value">
                        {{ number_format($metrics['total_dokumen'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Siklus pengemasan tercatat
            </div>
        </div>

        {{-- METRIK 2: HASIL KEMAS FINISH GOOD --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Hasil Kemas (FG)
                    </span>
                    <div class="executive-stat-value">
                        {{ number_format($metrics['total_karton'], 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #ecfdf5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Siap jual di gudang barang jadi
            </div>
        </div>

        {{-- METRIK 3: TOTAL CURAH (WIP) TERPAKAI --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Curah WIP Terpakai
                    </span>
                    <div class="executive-stat-value">
                        {{ number_format($metrics['total_wip_kg'] ?? 0, 1, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Kg</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #fef3c7; color: #d97706;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Keripik curah terkonversi
            </div>
        </div>

        {{-- METRIK 4: TOTAL HASIL ECERAN (PCS) --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #4f46e5;">
                        Hasil Kemas (Eceran)
                    </span>
                    <div class="executive-stat-value" style="color: #4f46e5;">
                        {{ number_format($metrics['total_pcs'] ?? 0, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Pcs</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0e7ff; color: #4f46e5;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Akumulasi unit pack kecil
            </div>
        </div>
    </div>

    {{-- Filter Toolbar (Sesuai Standar Produksi Index) --}}
    <div class="card" style="margin-bottom: 1.25rem; padding: 1.15rem; background: #ffffff;">
        <form method="GET" action="{{ route('produksi.packaging.index') }}">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; align-items: end;">
                <div>
                    <label class="filter-label" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Pencarian Dokumen / Batch</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                           class="form-control" style="font-size: 0.825rem; height: 36px;" placeholder="No PCK, Batch, atau nama barang...">
                </div>

                <div>
                    <label class="filter-label" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Tanggal Pengemasan</label>
                    <input type="date" name="pack_tgl" value="{{ $filters['pack_tgl'] ?? '' }}" 
                           class="form-control" style="font-size: 0.825rem; height: 36px;">
                </div>

                <div>
                    <label class="filter-label" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Target Produk Jadi (FG)</label>
                    <select name="barang_jadi_id" class="form-control" style="font-size: 0.825rem; height: 36px;">
                        <option value="">-- Semua Produk Jadi --</option>
                        @foreach($barangJadiList as $b)
                            <option value="{{ $b->barang_id }}" {{ ($filters['barang_jadi_id'] ?? '') == $b->barang_id ? 'selected' : '' }}>
                                {{ $b->barang_nm }} ({{ $b->barang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Status Dokumen</label>
                    <select name="status_cd" class="form-control" style="font-size: 0.825rem; height: 36px;">
                        <option value="">-- Semua Status --</option>
                        <option value="POSTED" {{ ($filters['status_cd'] ?? '') === 'POSTED' ? 'selected' : '' }}>POSTED</option>
                        <option value="VOID" {{ ($filters['status_cd'] ?? '') === 'VOID' ? 'selected' : '' }}>VOID (Batal)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.4rem;">
                    <button type="submit" class="btn btn-primary" style="height: 36px; padding: 0 1rem; font-size: 0.825rem; flex: 1;">
                        Filter
                    </button>
                    <a href="{{ route('produksi.packaging.index') }}" class="btn btn-secondary" style="height: 36px; padding: 0 0.85rem; font-size: 0.825rem; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;" title="Reset Filter">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABEL DATA HASIL PENGEMASAN (IDENTIK DENGAN TABEL HASIL PRODUKSI) --}}
    <div class="card" style="margin-bottom: 1.5rem; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 8px;">
        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; margin: 0; font-size: 0.85rem;">
                <thead style="background: #f8fafc; color: #475569; font-weight: 700; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 0.75rem 1rem; width: 145px; text-align: left;">NO DOKUMEN</th>
                        <th style="padding: 0.75rem 0.85rem; width: 105px; text-align: left;">TANGGAL</th>
                        <th style="padding: 0.75rem 0.85rem; width: 120px; text-align: left;">NO BATCH</th>
                        <th style="padding: 0.75rem 0.85rem; min-width: 140px; text-align: left;">GUDANG SIMPAN</th>
                        <th style="padding: 0.75rem 1rem; text-align: left;">PRODUK JADI (FG)</th>
                        <th style="padding: 0.75rem 1rem; text-align: left;">WIP &amp; BAHAN TERPAKAI</th>
                        <th style="padding: 0.75rem 1rem; text-align: left;">HASIL KEMAS</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; width: 135px;">DICATAT OLEH</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; width: 135px;">STATUS HPP</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; width: 85px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataList as $index => $row)
                        @php
                            $menuId = 'actionMenu_' . $row->pack_id;
                            $isEven = ($index % 2 === 1);
                            $rowBg = $isEven ? '#f1f5f9' : '#ffffff';
                            $wipDetail = $row->details->firstWhere(fn($d) => strtoupper($d->kategori_bahan) === 'WIP');
                            $wipQty = $wipDetail ? (float) $wipDetail->qty_aktual : 0;
                            $wipNm = $wipDetail?->barang?->barang_nm ?? 'Keripik Curah (WIP)';
                            $isFinal = $row->isHppFinal();
                            $canCalc = $row->canCalculateHpp();
                        @endphp
                        <tr class="prod-main-row" style="background: {{ $rowBg }}; border-bottom: 1px solid #e2e8f0; transition: background 0.15s ease;">
                            {{-- No Dokumen --}}
                            <td style="padding: 0.75rem 1rem; vertical-align: top;">
                                <a href="{{ route('produksi.packaging.show', $row->pack_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>{{ $row->pack_no }}</span>
                                </a>
                            </td>

                            {{-- Tanggal --}}
                            <td style="padding: 0.75rem 0.85rem; color: #334155; vertical-align: top; white-space: nowrap;">
                                <div style="font-weight: 600;">
                                    {{ date('d/m/Y', strtotime($row->pack_tgl)) }}
                                </div>
                            </td>

                            {{-- No Batch / Lot --}}
                            <td style="padding: 0.75rem 0.85rem; vertical-align: top;">
                                @if($row->batch_no)
                                    <span style="display: inline-block; font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #0369a1; background: #f0f9ff; border: 1px solid #bae6fd; padding: 2px 7px; border-radius: 4px;">
                                        {{ $row->batch_no }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
                                @endif
                            </td>

                            {{-- Gudang Simpan --}}
                            <td style="padding: 0.75rem 0.85rem; vertical-align: top;">
                                <div style="font-weight: 600; color: #0f172a; font-size: 0.8125rem;">
                                    {{ $row->gudang?->gudang_nm ?? 'Gudang FG' }}
                                </div>
                                @if($row->gudang?->gudang_cd)
                                    <div style="font-size: 0.6875rem; color: #64748b; margin-top: 1px;">
                                        Kode: {{ $row->gudang->gudang_cd }}
                                    </div>
                                @endif
                            </td>

                            {{-- Target Produk Jadi --}}
                            <td style="padding: 0.75rem 1rem; vertical-align: top;">
                                <div style="font-weight: 700; color: #0f172a;">
                                    {{ $row->barangJadi?->barang_nm ?? '-' }}
                                </div>
                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                    Kode: {{ $row->barangJadi?->barang_cd ?? '-' }}
                                    @if($row->isi_per_karton_pcs > 1)
                                        &bull; {{ number_format($row->isi_per_karton_pcs, 0) }} Pcs/Ktn
                                    @endif
                                </div>
                            </td>

                            {{-- WIP & Bahan Terpakai --}}
                            <td style="padding: 0.75rem 1rem; vertical-align: top;">
                                <div style="font-size: 0.8rem; font-weight: 600; color: #0f172a;">
                                    {{ $wipNm }} ({{ number_format($wipQty, 1, ',', '.') }} kg)
                                </div>
                                @if($row->produksi)
                                    <div style="margin-top: 0.25rem;">
                                        <a href="{{ route('produksi.show', $row->produksi->produksi_id) }}" style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.65rem; font-weight: 700; color: #15803d; background: #dcfce7; padding: 0.1rem 0.4rem; border-radius: 4px; text-decoration: none; border: 1px solid #86efac;" title="Lihat Dokumen Hasil Masak Dapur">
                                            <span>SPK #{{ $row->produksi->produksi_no }}</span>
                                            @if($row->produksi->shift)
                                                <span>&bull; Shift {{ $row->produksi->shift }}</span>
                                            @endif
                                        </a>
                                    </div>
                                @else
                                    <span style="font-size: 0.7rem; color: #94a3b8;">Konsumsi Bebas / Gudang</span>
                                @endif
                            </td>

                            {{-- Hasil Kemas --}}
                            <td style="padding: 0.75rem 1rem; text-align: left; vertical-align: top;">
                                <div style="font-weight: 800; color: #059669; font-size: 0.9rem;">
                                    {{ number_format($row->hasil_karton_qty, 0, ',', '.') }} <span style="font-size: 0.725rem; font-weight: 600; color: #64748b;">Ktn</span>
                                </div>
                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                    {{ number_format($row->hasil_pcs_qty, 0, ',', '.') }} Pcs
                                </div>
                            </td>

                            {{-- Otorisasi (Dicatat Oleh & Jam) --}}
                            <td style="padding: 0.75rem 1rem; vertical-align: top;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem; display: flex; align-items: center; gap: 0.35rem;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>{{ $row->created_by ?? 'System' }}</span>
                                </div>
                                <div style="font-size: 0.6875rem; color: #64748b; margin-top: 2px;">
                                    {{ $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-' }}
                                </div>
                            </td>

                            {{-- Status HPP --}}
                            <td style="padding: 0.75rem 1rem; text-align: center; vertical-align: top;">
                                @if($row->status_cd === 'VOID')
                                    <span style="font-size: 0.7rem; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.2rem 0.55rem; border-radius: 9999px; font-weight: 700;">
                                        VOID
                                    </span>
                                @elseif($isFinal)
                                    <span style="font-size: 0.7rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.2rem 0.55rem; border-radius: 9999px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>HPP FINAL</span>
                                    </span>
                                    <div style="font-size: 0.65rem; color: #059669; font-weight: 600; margin-top: 3px;">
                                        Tervalidasi
                                    </div>
                                @elseif(!$canCalc && $row->produksi_id)
                                    <span style="font-size: 0.7rem; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 0.2rem 0.55rem; border-radius: 9999px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;" title="HPP Produksi Asal belum difinalkan">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Menunggu HPP Produksi</span>
                                    </span>
                                    <div style="font-size: 0.65rem; color: #b45309; margin-top: 2px;">
                                        {{ $row->produksi?->produksi_no }}
                                    </div>
                                @else
                                    <span style="font-size: 0.7rem; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.2rem 0.55rem; border-radius: 9999px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <span>Siap Dihitung</span>
                                    </span>
                                    <div style="font-size: 0.65rem; color: #1e40af; margin-top: 2px;">
                                        Siap Ditarik
                                    </div>
                                @endif
                            </td>

                            {{-- Aksi Dropdown --}}
                            <td style="padding: 0.75rem 1rem; text-align: center; vertical-align: top; position: relative;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('produksi.packaging.show', $row->pack_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Lihat Bukti Hasil Kemas</span>
                                    </a>

                                    @if($row->status_cd === 'POSTED')
                                        @if(!$isFinal && (Auth::user()->canCreateProduksi() || Auth::user()->canCreatePackaging()))
                                            <div class="action-dropdown-divider"></div>
                                            <button type="button" class="action-dropdown-item danger-item" 
                                                    onclick="openVoidPackagingModal({{ $row->pack_id }}, '{{ $row->pack_no }}', '{{ addslashes($row->barangJadi?->barang_nm ?? '') }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Batalkan (Void)</span>
                                            </button>
                                        @elseif($isFinal)
                                            <div class="action-dropdown-divider"></div>
                                            <div style="padding: 0.35rem 0.75rem; font-size: 0.7rem; color: #64748b; background: #f8fafc; display: flex; align-items: center; gap: 5px;">
                                                <svg width="12" height="12" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span>Terkunci (HPP Final)</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <span style="font-weight: 600; font-size: 0.95rem; color: #64748b;">Belum ada dokumen pengemasan yang tercatat</span>
                                    <span style="font-size: 0.8rem; color: #94a3b8;">Silakan klik tombol <strong>+ Catat Pengemasan Baru</strong> untuk mencatat proses kemas keripik curah ke barang jadi.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($dataList->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                {{ $dataList->appends($filters)->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL VOID DANGER --}}
@include('produksi.packaging.partials.modal-void')

{{-- MODAL FINALISASI HPP & PERINGATAN HPP PRODUKSI --}}
@include('produksi.packaging.partials.modal-finalize-hpp')
@include('produksi.packaging.partials.modal-warning-prod-hpp')
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/packaging/packaging-index.js') }}"></script>
@endpush
