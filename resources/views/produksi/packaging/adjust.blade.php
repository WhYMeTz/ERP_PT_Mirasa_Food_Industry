@extends('layouts.app')

@section('title', 'Adjustment HPP - ' . $header->pack_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-adjust.css') }}?v={{ filemtime(public_path('css/produksi/packaging/packaging-adjust.css')) }}">
@endpush

@section('content')
@php
    $wipItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) === 'WIP');
    $kemasanItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) !== 'WIP');
    $countWip = $wipItems->count();
    $countKemasan = $kemasanItems->count();
    $totalKomponen = $countWip + $countKemasan + 2; // +1 Tenaga Kerja Kemas + 1 Overhead
@endphp

<div class="adjust-container">
    {{-- 1. BREADCRUMBS & TOP HEADER --}}
    <div class="adjust-header">
        <div>
            <a href="{{ route('produksi.packaging.show', $header->pack_id) }}" class="btn-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Detail Lembar Pengemasan</span>
            </a>
            <div class="adjust-title-wrap">
                <h1 class="adjust-title">Adjustment HPP</h1>
                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700; border: 1px solid #bae6fd;">{{ $header->pack_no }}</span>
                <span class="badge-version">v{{ $header->version_no ?? 1 }}</span>
                @if($header->status_cd === 'POSTED')
                    <span class="badge badge-success">Posted</span>
                @else
                    <span class="badge" style="background: #f1f5f9; color: #475569;">{{ $header->status_cd }}</span>
                @endif
            </div>
        </div>
        <div class="adjust-header-actions">
            <button type="button" onclick="resetAllAdjFields()" class="btn-cancel-doc" title="Reset seluruh nilai input ke kondisi awal dokumen">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reset Nilai ke Awal</span>
            </button>
        </div>
    </div>

    {{-- 2. NOTIFIKASI ERROR / SUKSES --}}
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
            <strong>Gagal:</strong> {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.25rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- TAB SWITCHER SPLIT DOKUMEN PENGEMASAN (JIKA PADA TANGGAL INI TERDAPAT LEBIH DARI 1 DOKUMEN KEMAS) --}}
    @if(isset($sameDatePackaging) && $sameDatePackaging->count() > 1)
        <div class="card-enterprise no-print" style="margin-bottom: 1.25rem; padding: 0.9rem 1.15rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                <div style="font-size: 0.875rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Dokumen Pengemasan Hari Ini ({{ \Carbon\Carbon::parse($header->pack_tgl)->format('d/m/Y') }}):</span>
                </div>
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; background: #f8fafc; padding: 0.2rem 0.55rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                    <span>Tersedia {{ $sameDatePackaging->count() }} Dokumen Produk &bull; Klik Tab untuk Beralih Koreksi</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.75rem;">
                @foreach($sameDatePackaging as $pItem)
                    @php
                        $isActive = $pItem->pack_id === $header->pack_id;
                    @endphp
                    <a href="{{ route('produksi.packaging.adjust', $pItem->pack_id) }}" 
                       style="display: flex; flex-direction: column; gap: 0.35rem; padding: 0.75rem 1rem; border-radius: 8px; border: 1.5px solid {{ $isActive ? '#0284c7' : '#e2e8f0' }}; background: {{ $isActive ? '#f0f9ff' : '#ffffff' }}; text-decoration: none; transition: all 0.15s ease-in-out; box-shadow: {{ $isActive ? '0 2px 6px rgba(2, 132, 199, 0.12)' : 'none' }};">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $isActive ? '#0284c7' : '#94a3b8' }}; flex-shrink: 0;"></span>
                                <span style="font-size: 0.875rem; font-weight: 800; color: {{ $isActive ? '#0369a1' : '#0f172a' }};">
                                    {{ $pItem->barangJadi?->barang_nm ?? '-' }}
                                </span>
                            </div>
                            @if($isActive)
                                <span style="font-size: 0.6875rem; font-weight: 700; color: #0369a1; background: #e0f2fe; padding: 0.15rem 0.45rem; border-radius: 9999px; border: 1px solid #bae6fd;">
                                    Sedang Ditampilkan
                                </span>
                            @else
                                <span style="font-size: 0.72rem; color: #64748b; font-weight: 600;">
                                    Beralih ke Dokumen Ini &rarr;
                                </span>
                            @endif
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                            <span style="font-family: monospace; font-weight: 700; color: #0284c7;">#{{ $pItem->pack_no }}</span>
                            <span>&bull;</span>
                            <span style="font-weight: 700; color: #059669;">{{ number_format($pItem->hasil_karton_qty, 0, ',', '.') }} Ktn ({{ number_format($pItem->hasil_pcs_qty, 0, ',', '.') }} Pcs)</span>
                            <span>&bull;</span>
                            <span style="font-weight: 600; color: #0f172a;">HPP Rp {{ number_format($pItem->hpp_per_karton, 0, ',', '.') }}/Ktn</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 3. PARAMETER DOKUMEN PENGEMASAN (KARTU STANDAR ERP MIRASA IDENTIK DENGAN PRODUKSI) --}}
    <div class="card-enterprise">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Parameter Dokumen Pengemasan &amp; Persediaan FG</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700; border: 1px solid #bae6fd; font-size: 0.775rem;">
                    Batch FG: {{ $header->batch_no ?: '-' }}
                </span>
            </div>
        </div>
        <div style="padding: 1rem 1.25rem;">
            <div class="adjust-param-grid">
                <div class="adjust-param-item">
                    <span class="param-label">Nomor Pengemasan</span>
                    <span class="param-value font-mono">{{ $header->pack_no }}</span>
                </div>
                <div class="adjust-param-item">
                    <span class="param-label">Tanggal Pengemasan</span>
                    <span class="param-value">{{ \Carbon\Carbon::parse($header->pack_tgl)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                </div>
                <div class="adjust-param-item">
                    <span class="param-label">Target Produk Jadi (FG)</span>
                    <span class="param-value" style="font-weight: 700; color: #0f172a;">{{ $header->barangJadi?->barang_nm ?? '-' }}</span>
                </div>
                <div class="adjust-param-item">
                    <span class="param-label">Gudang Penyimpanan FG</span>
                    <span class="param-value">{{ $header->gudang?->gudang_nm ?? 'Gudang Hasil Produksi (FG)' }}</span>
                </div>
                <div class="adjust-param-item">
                    <span class="param-label">Total Hasil Kemas Siap Jual</span>
                    <span class="param-value" style="font-weight: 800; color: #0284c7;">
                        {{ number_format((float) $header->hasil_karton_qty, 0, ',', '.') }} Karton
                        <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">({{ number_format((float) $header->hasil_pcs_qty, 0, ',', '.') }} Pcs &bull; {{ number_format((float) $header->isi_per_karton_pcs, 0) }} pcs/ktn)</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. LEMBAR KERJA KOMPONEN BIAYA (FULL-WIDTH - BEBAS SCROLL KANAN-KIRI) --}}
    <form id="formAdjustHppWorksheet" action="{{ route('produksi.packaging.adjust-hpp', $header->pack_id) }}" method="POST">
        @csrf

        {{-- KARTU TABEL KOMPONEN BIAYA FULL WIDTH --}}
        <div class="adjust-table-card">
            <div class="adjust-table-card-header">
                <div>
                    <h2 class="adjust-card-title">Lembar Kerja Komponen Biaya Pengemasan ({{ $totalKomponen }} Komponen)</h2>
                    <div class="adjust-card-subtitle">Ubah nilai nominal rupiah di bawah ini untuk melihat simulasi dampak selisih HPP secara langsung.</div>
                </div>

                {{-- NAVIGASI TAB KATEGORI BIAYA --}}
                <div class="adj-nav-tabs">
                    <button type="button" class="adj-nav-btn active" onclick="filterAdjCategory('all', this)">Semua Komponen ({{ $totalKomponen }})</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('WIP', this)">Bahan Curah WIP ({{ $countWip }})</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('KEMASAN', this)">Kemasan &amp; Dus ({{ $countKemasan }})</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('LABOR', this)">Tenaga Kerja Kemas (1)</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('OVERHEAD', this)">Overhead &amp; Lainnya (1)</button>
                </div>
            </div>

            {{-- TABEL INPUT KOMPONEN BIAYA FULL WIDTH TANPA HORIZONTAL SCROLL --}}
            <table class="adj-full-table">
                <thead>
                    <tr>
                        <th style="width: 28%; text-align: left;">Komponen Biaya</th>
                        <th style="width: 18%; text-align: left;">Spesifikasi / Satuan</th>
                        <th style="width: 16%; text-align: right;">Nilai Awal (Rp)</th>
                        <th style="width: 20%; text-align: right;">Nilai Koreksi (Rp)</th>
                        <th style="width: 13%; text-align: right;">Selisih (Variance)</th>
                        <th style="width: 5%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- GRUP 1: BAHAN CURAH LANGSUNG (WIP) --}}
                    <tr class="adj-group-row" data-group-header="WIP">
                        <td colspan="6">
                            <div class="adj-group-header-flex">
                                <div class="adj-group-header-left">
                                    <span class="adj-group-num">1</span>
                                    <span class="adj-group-name">BIAYA BAHAN CURAH LANGSUNG (WIP)</span>
                                </div>
                                <span class="adj-group-badge">{{ $countWip }} Komponen Biaya</span>
                            </div>
                        </td>
                    </tr>
                    @foreach($wipItems as $wip)
                        @php
                            $subAwalWip = round((float) $wip->subtotal_nilai);
                        @endphp
                        <tr class="adj-item-row" data-group="WIP">
                            <td>
                                <div class="adj-item-title">{{ $wip->barang?->barang_nm ?? 'Bahan Keripik Curah' }}</div>
                                <div class="adj-item-code">
                                    <span>{{ $wip->barang?->barang_cd ?? 'WIP' }}</span>
                                    @if($wip->batch_no)
                                        &bull; <span>Lot: {{ $wip->batch_no }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="adj-item-spec">{{ number_format((float) $wip->qty_aktual, 2, ',', '.') }} {{ $wip->satuan_cd }} Terpakai</td>
                            <td class="adj-item-current">Rp {{ number_format($subAwalWip, 0, ',', '.') }}</td>
                            <td>
                                <div class="adj-input-wrap">
                                    <span class="adj-input-prefix">Rp</span>
                                    <input type="number" step="1" min="0" 
                                           name="items[{{ $wip->packdtl_id }}][subtotal_nilai]" 
                                           id="input_adj_item_{{ $wip->packdtl_id }}" 
                                           data-key="item_{{ $wip->packdtl_id }}" 
                                           data-group="WIP" 
                                           data-qty="{{ (float) $wip->qty_aktual }}" 
                                           value="{{ $subAwalWip }}" 
                                           class="adj-calc-input">
                                </div>
                            </td>
                            <td><span id="badge_delta_item_{{ $wip->packdtl_id }}" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                            <td style="text-align: center;">
                                <button type="button" id="btn_reset_item_{{ $wip->packdtl_id }}" 
                                        onclick="resetAdjField('item_{{ $wip->packdtl_id }}')" 
                                        class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="adj-group-subtotal-row" data-group-subtotal="WIP">
                        <td colspan="2">
                            <span class="adj-subtotal-label">Subtotal Biaya Bahan Curah (WIP)</span>
                        </td>
                        <td class="adj-subtotal-awal">
                            Rp {{ number_format((float) $header->total_biaya_wip, 0, ',', '.') }}
                        </td>
                        <td class="adj-subtotal-new">
                            <span id="subtotal_wip_text">Rp {{ number_format((float) $header->total_biaya_wip, 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="subtotal_wip_delta" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>

                    {{-- GRUP 2: KEMASAN & MATERIAL PACKING --}}
                    <tr class="adj-group-row" data-group-header="KEMASAN">
                        <td colspan="6">
                            <div class="adj-group-header-flex">
                                <div class="adj-group-header-left">
                                    <span class="adj-group-num">2</span>
                                    <span class="adj-group-name">BIAYA KEMASAN &amp; MATERIAL PACKING</span>
                                </div>
                                <span class="adj-group-badge">{{ $countKemasan }} Komponen Biaya</span>
                            </div>
                        </td>
                    </tr>
                    @foreach($kemasanItems as $kem)
                        @php
                            $subAwalK = round((float) $kem->subtotal_nilai);
                        @endphp
                        <tr class="adj-item-row" data-group="KEMASAN">
                            <td>
                                <div class="adj-item-title">{{ $kem->barang?->barang_nm ?? 'Material Kemasan' }}</div>
                                <div class="adj-item-code">
                                    <span>{{ $kem->barang?->barang_cd ?? '-' }}</span>
                                    &bull; <span>{{ $kem->kategori_bahan }}</span>
                                    @if($kem->batch_no)
                                        &bull; <span>({{ $kem->batch_no }})</span>
                                    @endif
                                </div>
                            </td>
                            <td class="adj-item-spec">{{ number_format((float) $kem->qty_aktual, 2, ',', '.') }} {{ $kem->satuan_cd }} Terpakai</td>
                            <td class="adj-item-current">Rp {{ number_format($subAwalK, 0, ',', '.') }}</td>
                            <td>
                                <div class="adj-input-wrap">
                                    <span class="adj-input-prefix">Rp</span>
                                    <input type="number" step="1" min="0" 
                                           name="items[{{ $kem->packdtl_id }}][subtotal_nilai]" 
                                           id="input_adj_item_{{ $kem->packdtl_id }}" 
                                           data-key="item_{{ $kem->packdtl_id }}" 
                                           data-group="KEMASAN" 
                                           data-qty="{{ (float) $kem->qty_aktual }}" 
                                           value="{{ $subAwalK }}" 
                                           class="adj-calc-input">
                                </div>
                            </td>
                            <td><span id="badge_delta_item_{{ $kem->packdtl_id }}" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                            <td style="text-align: center;">
                                <button type="button" id="btn_reset_item_{{ $kem->packdtl_id }}" 
                                        onclick="resetAdjField('item_{{ $kem->packdtl_id }}')" 
                                        class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="adj-group-subtotal-row" data-group-subtotal="KEMASAN">
                        <td colspan="2">
                            <span class="adj-subtotal-label">Subtotal Biaya Kemasan &amp; Dus</span>
                        </td>
                        <td class="adj-subtotal-awal">
                            Rp {{ number_format((float) $header->total_biaya_kemasan, 0, ',', '.') }}
                        </td>
                        <td class="adj-subtotal-new">
                            <span id="subtotal_kemasan_text">Rp {{ number_format((float) $header->total_biaya_kemasan, 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="subtotal_kemasan_delta" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>

                    {{-- GRUP 3: TENAGA KERJA LANGSUNG (BTKL KEMAS) --}}
                    <tr class="adj-group-row" data-group-header="LABOR">
                        <td colspan="6">
                            <div class="adj-group-header-flex">
                                <div class="adj-group-header-left">
                                    <span class="adj-group-num">3</span>
                                    <span class="adj-group-name">BIAYA TENAGA KERJA LANGSUNG (BTKL KEMAS)</span>
                                </div>
                                <span class="adj-group-badge">1 Komponen Biaya</span>
                            </div>
                        </td>
                    </tr>
                    @php
                        $subAwalLabor = round((float) $header->total_biaya_tenaga_kerja);
                    @endphp
                    <tr class="adj-item-row" data-group="LABOR">
                        <td>
                            <div class="adj-item-title">Upah Tenaga Kerja Kemas (BTKL)</div>
                            <div class="adj-item-code">BTKL &bull; Meja Packing Sachet &amp; Susun Karton Dus</div>
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $header->hasil_karton_qty, 0, ',', '.') }} Karton Borongan</td>
                        <td class="adj-item-current">Rp {{ number_format($subAwalLabor, 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" 
                                       name="total_biaya_tenaga_kerja" 
                                       id="input_adj_total_biaya_tenaga_kerja" 
                                       data-key="total_biaya_tenaga_kerja" 
                                       data-group="LABOR" 
                                       data-qty="{{ (float) $header->hasil_karton_qty }}" 
                                       value="{{ $subAwalLabor }}" 
                                       class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_total_biaya_tenaga_kerja" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_total_biaya_tenaga_kerja" 
                                    onclick="resetAdjField('total_biaya_tenaga_kerja')" 
                                    class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-group-subtotal-row" data-group-subtotal="LABOR">
                        <td colspan="2">
                            <span class="adj-subtotal-label">Subtotal Upah Tenaga Kerja Kemas (BTKL)</span>
                        </td>
                        <td class="adj-subtotal-awal">
                            Rp {{ number_format((float) $header->total_biaya_tenaga_kerja, 0, ',', '.') }}
                        </td>
                        <td class="adj-subtotal-new">
                            <span id="subtotal_labor_text">Rp {{ number_format((float) $header->total_biaya_tenaga_kerja, 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="subtotal_labor_delta" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>

                    {{-- GRUP 4: BIAYA OVERHEAD PABRIK & LAINNYA (FOH) --}}
                    <tr class="adj-group-row" data-group-header="OVERHEAD">
                        <td colspan="6">
                            <div class="adj-group-header-flex">
                                <div class="adj-group-header-left">
                                    <span class="adj-group-num">4</span>
                                    <span class="adj-group-name">BIAYA OVERHEAD PABRIK &amp; UTILITAS (FOH)</span>
                                </div>
                                <span class="adj-group-badge">1 Komponen Biaya</span>
                            </div>
                        </td>
                    </tr>
                    @php
                        $subAwalLain = round((float) $header->total_biaya_lain);
                    @endphp
                    <tr class="adj-item-row" data-group="OVERHEAD">
                        <td>
                            <div class="adj-item-title">Biaya Lain-Lain &amp; Overhead Pengemasan</div>
                            <div class="adj-item-code">FOH &bull; Listrik Sealer, Pemeliharaan &amp; Utilitas Kemas</div>
                        </td>
                        <td class="adj-item-spec">Alokasi Biaya Tidak Langsung</td>
                        <td class="adj-item-current">Rp {{ number_format($subAwalLain, 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" 
                                       name="total_biaya_lain" 
                                       id="input_adj_total_biaya_lain" 
                                       data-key="total_biaya_lain" 
                                       data-group="OVERHEAD" 
                                       value="{{ $subAwalLain }}" 
                                       class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_total_biaya_lain" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_total_biaya_lain" 
                                    onclick="resetAdjField('total_biaya_lain')" 
                                    class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-group-subtotal-row" data-group-subtotal="OVERHEAD">
                        <td colspan="2">
                            <span class="adj-subtotal-label">Subtotal Biaya Overhead / Lain-Lain</span>
                        </td>
                        <td class="adj-subtotal-awal">
                            Rp {{ number_format((float) $header->total_biaya_lain, 0, ',', '.') }}
                        </td>
                        <td class="adj-subtotal-new">
                            <span id="subtotal_overhead_text">Rp {{ number_format((float) $header->total_biaya_lain, 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="subtotal_overhead_delta" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr class="adj-tfoot-total-row">
                        <td colspan="2">
                            <div class="adj-tfoot-title">TOTAL BIAYA PENGEMASAN PABRIK ({{ $totalKomponen }} KOMPONEN)</div>
                            <div class="adj-tfoot-hpp-box">
                                <span>Estimasi HPP/Karton Baru:</span>
                                <strong id="simulasiHppKartonBaruText" class="adj-tfoot-hpp-val">Rp {{ number_format((float) $header->hpp_per_karton, 0, ',', '.') }}</strong>
                                <span style="color: #64748b; font-size: 0.75rem;">/ Karton</span>
                                <span id="simulasiSelisihHppKartonBadge" class="adj-variance-badge adj-variance-zero" style="margin-left: 0.5rem; font-size: 0.725rem;">Selisih: Rp 0 / ktn</span>
                            </div>
                            <div class="adj-tfoot-hpp-box" style="margin-top: 0.2rem;">
                                <span>Estimasi HPP/Pcs Baru:</span>
                                <strong id="simulasiHppPcsBaruText" class="adj-tfoot-hpp-val" style="color: #0284c7;">Rp {{ number_format((float) $header->hpp_per_pcs, 0, ',', '.') }}</strong>
                                <span style="color: #64748b; font-size: 0.75rem;">/ Pcs</span>
                                <span id="simulasiSelisihHppPcsBadge" class="adj-variance-badge adj-variance-zero" style="margin-left: 0.5rem; font-size: 0.725rem;">Selisih: Rp 0 / pcs</span>
                            </div>
                        </td>
                        <td class="adj-tfoot-current">
                            Rp {{ number_format((float) $header->total_biaya_produksi, 0, ',', '.') }}
                        </td>
                        <td class="adj-tfoot-revised">
                            <span id="simulasiTotalBiayaBaruText">Rp {{ number_format((float) $header->total_biaya_produksi, 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="simulasiNetSelisihBiayaText" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 5. OTORISASI & BERITA ACARA AKUNTANSI (KARTU BAWAH TABEL IDENTIK DENGAN PRODUKSI) --}}
        <div class="adjust-auth-card">
            <div class="adjust-auth-header">
                <div class="adjust-auth-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Otorisasi &amp; Berita Acara Akuntansi</span>
                </div>
                <span style="font-size: 0.775rem; color: #64748b;">Standar Audit Trail Biaya Manufaktur PT Mirasa Food Industry</span>
            </div>

            <div class="adjust-auth-grid">
                {{-- KOLOM 1: NOMOR REFERENSI --}}
                <div class="form-group">
                    <label for="no_referensi_txt" class="adjust-form-label">
                        No. Referensi Faktur / Memo Fisik <span style="font-weight: normal; color: #94a3b8;">(Opsional)</span>
                    </label>
                    <input type="text" name="no_referensi_txt" id="no_referensi_txt" class="adjust-form-input" placeholder="Contoh: MEMO-ACC-202610-004 / INV-10928" value="{{ old('no_referensi_txt') }}">
                    <div class="adjust-form-hint">Digunakan untuk mereferensikan dokumen tagihan fisik revisi atau memo pimpinan.</div>
                </div>

                {{-- KOLOM 2: ALASAN PENYESUAIAN --}}
                <div class="form-group">
                    <label for="alasan_txt" class="adjust-form-label">
                        Alasan Penyesuaian / Dasar Otorisasi <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="alasan_txt" id="alasan_txt" rows="3" required class="adjust-form-textarea" placeholder="Tuliskan alasan penyesuaian biaya secara lengkap dan objektif (wajib minimal 5 karakter untuk audit akuntansi)...">{{ old('alasan_txt') }}</textarea>
                    <div class="adjust-form-hint">Wajib diisi sesuai Standar Operasional Prosedur (SOP) Pengendalian Mutu &amp; Akuntansi Biaya.</div>
                </div>
            </div>

            {{-- SUBMIT FOOTER BAR --}}
            <div class="adjust-submit-bar">
                <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                    <div style="font-size: 0.825rem; color: #475569; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Perubahan akan otomatis menyelaraskan kartu stok batch gudang simpan &amp; mencatat Berita Acara resmi.</span>
                    </div>
                    <span id="adjCountBadge" class="adj-status-chip">0 Komponen Diubah</span>
                </div>

                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <button type="submit" id="btnSubmitAdjust" class="btn-submit-adjust">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Terapkan Berita Acara HPP</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- 6. RIWAYAT BERITA ACARA ADJUSTMENT SEBELUMNYA (AUDIT TRAIL) --}}
    <div class="adjust-audit-card">
        <div class="adjust-audit-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="adjust-audit-title">Riwayat Berita Acara Penyesuaian Dokumen Ini</h3>
            </div>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
                {{ $header->adjustmentLogs ? $header->adjustmentLogs->count() : 0 }} Catatan
            </span>
        </div>

        <div class="adjust-audit-table-wrap">
            <table class="adjust-audit-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">No. Berita Acara</th>
                        <th style="width: 10%;">Tanggal &amp; Waktu</th>
                        <th style="width: 12%; text-align: right;">Total Biaya Revisi</th>
                        <th style="width: 9%; text-align: right;">Selisih Biaya</th>
                        <th style="width: 12%; text-align: right;">HPP / Karton Revisi</th>
                        <th style="width: 30%;">Alasan &amp; Rincian Perubahan</th>
                        <th style="width: 13%; text-align: center;">Otorisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($header->adjustmentLogs as $log)
                        <tr>
                            <td>
                                <span class="adjust-ba-badge">{{ $log->adj_no }}</span>
                            </td>
                            <td style="color: #475569; font-weight: 600; white-space: nowrap;">
                                <div>{{ \Carbon\Carbon::parse($log->adj_tgl)->format('d/m/Y') }}</div>
                                <div style="font-size: 0.725rem; color: #94a3b8; font-weight: 500;">
                                    {{ \Carbon\Carbon::parse($log->created_at ?? $log->adj_tgl)->format('H:i') }} WIB
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div class="audit-old-val">Rp {{ number_format((float) $log->total_biaya_sebelum, 0, ',', '.') }}</div>
                                <div class="audit-new-val">&rarr; Rp {{ number_format((float) $log->total_biaya_sesudah, 0, ',', '.') }}</div>
                            </td>
                            <td style="text-align: right;">
                                @if((float) $log->selisih_biaya > 0)
                                    <span class="adj-variance-badge adj-variance-up">+Rp {{ number_format((float) $log->selisih_biaya, 0, ',', '.') }}</span>
                                @elseif((float) $log->selisih_biaya < 0)
                                    <span class="adj-variance-badge adj-variance-down">-Rp {{ number_format(abs((float) $log->selisih_biaya), 0, ',', '.') }}</span>
                                @else
                                    <span class="adj-variance-badge adj-variance-zero">Rp 0</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div class="audit-old-val">Rp {{ number_format((float) $log->hpp_karton_sebelum, 0, ',', '.') }}</div>
                                <div class="audit-new-val audit-hpp-val">&rarr; Rp {{ number_format((float) $log->hpp_karton_sesudah, 0, ',', '.') }}</div>
                                <div style="font-size: 0.7rem; color: #64748b;">(Rp {{ number_format((float)$log->hpp_pcs_sesudah, 0, ',', '.') }}/pcs)</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a; font-size: 0.825rem; line-height: 1.4;">
                                    {{ $log->alasan_txt }}
                                </div>
                                @if(!empty($log->perubahan_field_json) && is_array($log->perubahan_field_json))
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.4rem;">
                                        @foreach($log->perubahan_field_json as $fKey => $fVal)
                                            <span class="audit-diff-chip">
                                                <strong>{{ $fVal['label'] ?? $fKey }}:</strong> Rp {{ number_format((float) ($fVal['old_subtotal'] ?? 0), 0, ',', '.') }} &rarr; Rp {{ number_format((float) ($fVal['new_subtotal'] ?? 0), 0, ',', '.') }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="audit-user-badge">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>{{ $log->created_by ?? 'System' }}</span>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8;">
                                <div style="font-weight: 700; color: #64748b; font-size: 0.9rem;">Belum Ada Riwayat Penyesuaian HPP Kemas</div>
                                <div style="font-size: 0.775rem; margin-top: 0.25rem;">Dokumen ini masih mencatat nilai kalkulasi pengemasan orisinal (Versi 1).</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.adjPackConfig = {
            hasilKartonQty: {{ (float) $header->hasil_karton_qty }},
            hasilPcsQty: {{ (float) $header->hasil_pcs_qty }},
            totalBiayaAwal: {{ (float) $header->total_biaya_produksi }},
            hppKartonAwal: {{ (float) $header->hpp_per_karton }},
            hppPcsAwal: {{ (float) $header->hpp_per_pcs }},

            // Subtotal kelompok awal
            totalWipAwal: {{ (float) $header->total_biaya_wip }},
            totalKemasanAwal: {{ (float) $header->total_biaya_kemasan }},
            totalLaborAwal: {{ (float) $header->total_biaya_tenaga_kerja }},
            totalOverheadAwal: {{ (float) $header->total_biaya_lain }},

            // 1. Detail WIP & Kemasan
            @foreach($header->details as $dtl)
                item_{{ $dtl->packdtl_id }}: {{ round((float) $dtl->subtotal_nilai) }},
            @endforeach

            // 2. Upah & Overhead
            total_biaya_tenaga_kerja: {{ round((float) $header->total_biaya_tenaga_kerja) }},
            total_biaya_lain: {{ round((float) $header->total_biaya_lain) }},
        };
    </script>
    <script src="{{ asset('js/produksi/packaging/packaging-adjust.js') }}?v={{ filemtime(public_path('js/produksi/packaging/packaging-adjust.js')) }}"></script>
@endpush
