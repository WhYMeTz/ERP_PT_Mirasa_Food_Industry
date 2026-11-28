@extends('layouts.app')

@section('title', 'Lembar Rincian HPP Terpadu - SPK #' . $produksi->produksi_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/hpp/hpp-terpadu.css') }}?v={{ filemtime(public_path('css/produksi/hpp/hpp-terpadu.css')) }}">
@endpush

@section('content')
@php
    $packagingBranches = $packagingBranches ?? ($packaging_branches ?? []);
    $tglCarbon = \Carbon\Carbon::parse($produksi->produksi_tgl)->locale('id');
    $shiftUpper = strtoupper($produksi->shift_cd ?? 'A');
    $isShiftB = str_contains($shiftUpper, 'B') && !str_contains($shiftUpper, 'A');
    $liniUpper = strtoupper($produksi->lini_produksi ?? 'REGULER');
    $isIfm = str_contains($liniUpper, 'IFM') || str_contains($liniUpper, 'INDOFOOD');
    $pabrikCd = strtoupper(trim((string)$produksi->lokasi_pabrik));
    $isBmb = in_array($pabrikCd, ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);
    $pabrikNm = $isBmb ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry';
@endphp

<div class="terpadu-container">

    {{-- KOP SURAT RESMI CETAK (PRINT ONLY) --}}
    <div class="print-only" style="margin-bottom: 1.25rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
                <p style="font-size: 0.72rem; color: #475569; margin: 0.15rem 0 0 0;">
                    Pabrik Pengolahan Keripik Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
                </p>
            </div>
            <div style="text-align: right;">
                <strong style="font-size: 0.95rem; color: #059669; display: block; text-transform: uppercase;">Lembar Rincian HPP Terpadu (Produksi &amp; Pengemasan)</strong>
                <span style="font-family: monospace; font-size: 0.85rem; font-weight: 700;">No. SPK: {{ $produksi->produksi_no }}</span>
            </div>
        </div>
    </div>

    {{-- BARIS NAVIGASI & AKSI --}}
    <div class="terpadu-nav-row no-print">
        <div>
            <a href="{{ route('produksi.rekap', ['tahun' => $tglCarbon->year, 'bulan' => $tglCarbon->month]) }}" class="btn-terpadu-back">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Buku Rekap Bulanan</span>
            </a>
        </div>
        <div class="terpadu-actions-group">
            <a href="{{ route('produksi.hpp.show', $produksi->produksi_id) }}" class="btn-terpadu-print" style="text-decoration: none;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Lembar HPP Produksi</span>
            </a>
            <button type="button" onclick="window.print()" class="btn-terpadu-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar HPP Terpadu</span>
            </button>
        </div>
    </div>

    {{-- IDENTITY & METADATA BANNER --}}
    <div class="terpadu-identity-card">
        <div class="terpadu-identity-main">
            <div>
                <h1 class="terpadu-spk-title" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <span>SPK #{{ $produksi->produksi_no }}</span>
                    @if($isBmb)
                        <span style="display: inline-block; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 4px; background: #faf5ff; color: #7e22ce; border: 1px solid #d8b4fe;">CV Bahtera Mandiri Bersama (BMB)</span>
                    @else
                        <span style="display: inline-block; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 4px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">PT Mirasa Food Industry (MFI)</span>
                    @endif
                    <span class="terpadu-badge {{ $isShiftB ? 'badge-shift-b' : 'badge-shift-a' }}">Shift {{ $produksi->shift_cd }}</span>
                    <span class="terpadu-badge {{ $isIfm ? 'badge-lini-ifm' : 'badge-lini-manual' }}">{{ $produksi->lini_produksi }}</span>
                </h1>
                <div class="terpadu-meta-row">
                    <span class="terpadu-meta-item">
                        <strong>Tanggal Produksi:</strong> {{ $tglCarbon->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="terpadu-meta-divider">&bull;</span>
                    <span class="terpadu-meta-item">
                        <strong>Gudang Asal:</strong> {{ $produksi->gudang?->gudang_nm ?? ($isBmb ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry') }}
                    </span>
                    <span class="terpadu-meta-divider">&bull;</span>
                    <span class="terpadu-meta-item">
                        <strong>Nomor Batch WIP:</strong> <code style="font-size: 0.8rem; font-weight: 700; color: #0284c7;">{{ $produksi->batch_wip_no ?: 'BELUM TERBIT' }}</code>
                    </span>
                    @if($produksi->operator_nama)
                        <span class="terpadu-meta-divider">&bull;</span>
                        <span class="terpadu-meta-item">
                            <strong>Operator:</strong> {{ $produksi->operator_nama }}
                        </span>
                    @endif
                </div>
            </div>

            <div style="text-align: right;">
                <span style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.25rem;">Status Pengemasan</span>
                @if(count($packagingBranches) > 0)
                    <span class="status-pill success">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Terkemas ({{ count($packagingBranches) }} Dokumen)</span>
                    </span>
                @else
                    <span class="status-pill warning">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
                        <span>Belum Dikemas</span>
                    </span>
                @endif
            </div>
        </div>

        {{-- LINE SWITCHER: Muncul Otomatis Jika Ada >1 SPK / Lini di Tanggal yang Sama --}}
        @if(count($sibling_spks ?? []) > 1)
            <div class="terpadu-switcher-bar no-print">
                <div class="terpadu-switcher-header">
                    <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Lini &amp; SPK Lain Hari Ini ({{ $tglCarbon->format('d/m/Y') }}):</span>
                    </div>
                    <span style="font-size: 0.7rem; color: #64748b; background: #f8fafc; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                        Klik kartu untuk beralih lembar HPP terpadu
                    </span>
                </div>
                <div class="terpadu-switcher-grid">
                    @foreach($sibling_spks as $sib)
                        @php
                            $isSibBmb = ($sib['lokasi_pabrik'] ?? '') === 'BMB';
                        @endphp
                        @if($sib['is_current'])
                            <div class="terpadu-switch-card active {{ $isSibBmb ? 'card-bmb' : 'card-mfi' }}">
                                <div class="switch-card-top">
                                    <div class="switch-card-title">
                                        <span class="switch-card-badge {{ $isSibBmb ? 'badge-bmb' : 'badge-mfi' }}">{{ $sib['lokasi_pabrik'] }}</span>
                                        <strong>{{ $sib['line_title'] ?? ($sib['lini_produksi'] . ' (Shift ' . $sib['shift_cd'] . ')') }}</strong>
                                    </div>
                                    <span class="switch-card-active-label">Sedang Dilihat</span>
                                </div>
                                <div class="switch-card-meta">
                                    <span class="switch-card-spk">#{{ $sib['produksi_no'] }}</span>
                                    <span class="meta-sep">&bull;</span>
                                    <span>{{ number_format($sib['total_wip_qty'] ?? 0, 0, ',', '.') }} kg WIP</span>
                                    <span class="meta-sep">&bull;</span>
                                    <span class="switch-card-hpp">
                                        Rp {{ number_format($sib['hpp_per_kg'], 0, ',', '.') }}/kg
                                        @if(!($sib['is_hpp_final'] ?? false))
                                            <span style="font-size: 0.72rem; color: #b45309; font-weight: 600;">(Estimasi)</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('produksi.hpp.terpadu', $sib['produksi_id']) }}" class="terpadu-switch-card {{ $isSibBmb ? 'card-bmb' : 'card-mfi' }}" title="Klik untuk beralih ke {{ $sib['produksi_no'] }}">
                                <div class="switch-card-top">
                                    <div class="switch-card-title">
                                        <span class="switch-card-badge {{ $isSibBmb ? 'badge-bmb' : 'badge-mfi' }}">{{ $sib['lokasi_pabrik'] }}</span>
                                        <strong>{{ $sib['line_title'] ?? ($sib['lini_produksi'] . ' (Shift ' . $sib['shift_cd'] . ')') }}</strong>
                                    </div>
                                    <span class="switch-card-hint">Beralih ke Dokumen Ini &rarr;</span>
                                </div>
                                <div class="switch-card-meta">
                                    <span class="switch-card-spk">#{{ $sib['produksi_no'] }}</span>
                                    <span class="meta-sep">&bull;</span>
                                    <span>{{ number_format($sib['total_wip_qty'] ?? 0, 0, ',', '.') }} kg WIP</span>
                                    <span class="meta-sep">&bull;</span>
                                    <span class="switch-card-hpp">
                                        Rp {{ number_format($sib['hpp_per_kg'], 0, ',', '.') }}/kg
                                        @if(!($sib['is_hpp_final'] ?? false))
                                            <span style="font-size: 0.72rem; color: #b45309; font-weight: 600;">(Estimasi)</span>
                                        @endif
                                    </span>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- EXECUTIVE METRICS RIBBON (Single unified horizontal bar) --}}
    <div class="terpadu-executive-ribbon">
        <div class="terpadu-metric-cell">
            <span class="terpadu-metric-caption">1. Biaya Produksi</span>
            <span class="terpadu-metric-value">Rp {{ number_format($total_dapur_nilai, 0, ',', '.') }}</span>
            <span class="terpadu-metric-sub">Output: <strong>{{ number_format($total_wip_kg_produced, 2, ',', '.') }} Kg</strong> Curah</span>
        </div>

        <div class="terpadu-metric-cell">
            <span class="terpadu-metric-caption">2. Biaya Kemas Barang Jadi</span>
            <span class="terpadu-metric-value">Rp {{ number_format($total_biaya_kemas_kumulatif, 0, ',', '.') }}</span>
            <span class="terpadu-metric-sub">Realisasi: <strong>{{ number_format($total_hasil_karton_kumulatif, 0, ',', '.') }} Karton</strong> ({{ number_format($total_hasil_pcs_kumulatif, 0, ',', '.') }} Pcs)</span>
        </div>

        <div class="terpadu-metric-cell highlight-pabrik">
            <span class="terpadu-metric-caption">Grand Total Biaya Pabrik</span>
            <span class="terpadu-metric-value" style="color: #059669;">Rp {{ number_format($grand_total_biaya_pabrik, 0, ',', '.') }}</span>
            <span class="terpadu-metric-sub">Total biaya produksi &amp; pengemasan</span>
        </div>

        @php
            $isKupasTerpadu = ($singkong_tipe ?? '') === 'KUPAS';
            $activeRendemenTerpadu = $isKupasTerpadu ? ($rendemen_kupas_persen ?? 0) : $rendemen_persen;
            $rendemenColorTerpadu = $isKupasTerpadu 
                ? ($activeRendemenTerpadu >= 44 ? '#059669' : ($activeRendemenTerpadu >= 40 ? '#d97706' : '#dc2626'))
                : ($activeRendemenTerpadu >= 33 ? '#059669' : ($activeRendemenTerpadu >= 30 ? '#d97706' : '#dc2626'));
        @endphp
        <div class="terpadu-metric-cell">
            <span class="terpadu-metric-caption">Rasio Rendemen</span>
            <span class="terpadu-metric-value" style="color: {{ $rendemenColorTerpadu }};">
                {{ number_format($activeRendemenTerpadu, 2, ',', '.') }}%
                <small style="font-size: 0.7rem; font-weight: 600; color: #64748b;">({{ $isKupasTerpadu ? 'Kupas' : 'Kulit' }})</small>
            </span>
            <span class="terpadu-metric-sub">
                Target: {{ $isKupasTerpadu ? '44% - 45% (Setara Kulitan: ' . number_format($rendemen_persen, 2, ',', '.') . '%)' : '≥ 33.0%' }}
            </span>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- TAHAP 1: OLAHAN DAPUR & PEMAKAIAN BAHAN BAKU MENTAH               --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="terpadu-sheet">
        <div class="terpadu-sheet-header">
            <div class="terpadu-sheet-title">
                <span class="terpadu-sheet-badge badge-phase-1">Tahap 1</span>
                <span>Biaya Produksi</span>
            </div>
            @if($produksi->pemakaianBahan)
                <span style="font-size: 0.75rem; color: #475569; font-weight: 600;">
                    BPPB Gudang: <strong style="font-family: monospace; color: #0284c7;">#{{ $produksi->pemakaianBahan->pakai_no }}</strong>
                </span>
            @endif
        </div>

        <div class="terpadu-sheet-body">
            {{-- Tabel BPPB --}}
            <table class="terpadu-grid-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Nama Bahan Baku Mentah</th>
                        <th style="width: 15%;">Kategori</th>
                        <th style="width: 15%;">Batch Gudang</th>
                        <th style="width: 12%;" class="text-right">Qty Keluar</th>
                        <th style="width: 11%;" class="text-right">Harga Satuan</th>
                        <th style="width: 12%;" class="text-right">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bahan_baku_items as $bItem)
                        <tr>
                            <td>
                                <strong style="color: #0f172a;">{{ $bItem['barang_nm'] }}</strong>
                                <span style="font-size: 0.7rem; color: #64748b; font-family: monospace; display: block;">{{ $bItem['barang_cd'] }}</span>
                            </td>
                            <td>
                                <span style="font-size: 0.72rem; font-weight: 600; color: #475569;">{{ $bItem['kategori'] }}</span>
                            </td>
                            <td>
                                <span style="font-size: 0.72rem; font-family: monospace; color: #64748b;">{{ $bItem['batch_no'] }}</span>
                            </td>
                            <td class="text-right num-val">
                                {{ number_format($bItem['qty'], 2, ',', '.') }} {{ $bItem['satuan'] }}
                            </td>
                            <td class="text-right num-val" style="color: #64748b;">
                                Rp {{ number_format($bItem['harga_satuan'], 2, ',', '.') }}
                            </td>
                            <td class="text-right num-val" style="font-weight: 700; color: #0f172a;">
                                Rp {{ number_format($bItem['subtotal'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 1rem; color: #94a3b8;">Tidak ada data rincian pemakaian bahan baku BPPB.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right" style="color: #475569;">Total Biaya Bahan Baku Mentah:</td>
                        <td class="text-right num-val" style="color: #0f172a; font-size: 0.9rem;">
                            Rp {{ number_format($total_bahan_nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            {{-- Ribbon Biaya Operasional Masak (Gas CNG & Upah Regu) --}}
            <div class="terpadu-utilities-bar">
                <div class="util-item">
                    <span class="util-label">Gas CNG</span>
                    <span class="util-value">Rp {{ number_format($cng_nilai, 0, ',', '.') }}</span>
                    <span class="util-sub">Berdasarkan pemakaian m³</span>
                </div>
                <div class="util-item">
                    <span class="util-label">Biaya Tenaga Kerja</span>
                    <span class="util-value">Rp {{ number_format($upah_dapur_nilai, 0, ',', '.') }}</span>
                    <span class="util-sub">Tarif regu shift</span>
                </div>
                <div class="util-item" style="border-left: 2px solid #0284c7; padding-left: 0.75rem;">
                    <span class="util-label" style="color: #0284c7;">Total Biaya Produksi</span>
                    <span class="util-value" style="color: #0284c7;">Rp {{ number_format($total_dapur_nilai, 0, ',', '.') }}</span>
                    <span class="util-sub">Bahan mentah + Gas CNG + Upah</span>
                </div>
            </div>

            {{-- Hasil Timbangan Curah Masak (WIP Outputs) --}}
            <div class="terpadu-sub-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                <span>Hasil Timbangan Produk (WIP Output)</span>
            </div>

            <table class="terpadu-grid-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Varian Output Curah</th>
                        <th style="width: 15%;">Batch WIP</th>
                        <th style="width: 12%;" class="text-right">Timbangan Qty</th>
                        <th style="width: 10%;" class="text-right">Rendemen</th>
                        <th style="width: 13%;" class="text-right">HPP WIP / Kg</th>
                        <th style="width: 13%;" class="text-right">Total Nilai Curah</th>
                        <th style="width: 12%;" class="text-center">Pengemasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outputs_list as $outItem)
                        <tr>
                            <td>
                                <strong style="color: #0f172a;">{{ $outItem['barang_nm'] }}</strong>
                                <span style="font-size: 0.7rem; color: #64748b; display: block; text-transform: capitalize;">Kategori: {{ $outItem['kategori_output'] }}</span>
                            </td>
                            <td>
                                <code style="font-size: 0.75rem; font-weight: 700; color: #0284c7;">{{ $outItem['batch_no'] }}</code>
                            </td>
                            <td class="text-right num-val" style="font-size: 0.875rem;">
                                {{ number_format($outItem['qty_kg'], 2, ',', '.') }} kg
                            </td>
                            <td class="text-right num-val">
                                {{ number_format($rendemen_persen, 2, ',', '.') }}%
                            </td>
                            <td class="text-right num-val" style="color: #059669; font-weight: 700;">
                                Rp {{ number_format($outItem['hpp_per_kg'], 2, ',', '.') }}
                            </td>
                            <td class="text-right num-val" style="font-weight: 700;">
                                Rp {{ number_format($outItem['subtotal_nilai'], 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($outItem['is_fully_packed'])
                                    <span class="status-pill success" title="Seluruh timbangan curah sudah diserap kemasan">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Terserap 100%</span>
                                    </span>
                                @elseif($outItem['consumed_kg'] > 0)
                                    <span class="status-pill info" title="Sebagian curah telah dikemas">
                                        <span>Dikemas {{ number_format($outItem['consumed_kg'], 1, ',', '.') }} kg</span>
                                    </span>
                                @else
                                    <span class="status-pill warning" title="Curah belum diproses kemas">
                                        <span>Belum Dikemas</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- TAHAP 2: REALISASI PENGEMASAN BARANG JADI (FINISH GOODS / FG)     --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="terpadu-sheet">
        <div class="terpadu-sheet-header">
            <div class="terpadu-sheet-title">
                <span class="terpadu-sheet-badge badge-phase-2">Tahap 2</span>
                <span>Realisasi Pengemasan Barang Jadi (Finish Goods / FG)</span>
            </div>
            @if(count($packagingBranches) > 0)
                <span style="font-size: 0.75rem; color: #059669; font-weight: 700;">
                    {{ count($packagingBranches) }} Dokumen Kemas Terhubung
                </span>
            @endif
        </div>

        <div class="terpadu-sheet-body">
            @if(count($packagingBranches) > 0)
                {{-- Master Data Table of Packagings --}}
                <table class="terpadu-grid-table">
                    <thead>
                        <tr>
                            <th style="width: 14%;">No. Kemas &amp; Tgl</th>
                            <th style="width: 18%;">Produk Barang Jadi (FG)</th>
                            <th style="width: 11%;">Batch &amp; Exp</th>
                            <th style="width: 10%;" class="text-right">WIP Diserap</th>
                            <th style="width: 9%;" class="text-right">Bahan Kemas</th>
                            <th style="width: 8%;" class="text-right">Upah Kemas</th>
                            <th style="width: 9%;" class="text-right">Total Kemas</th>
                            <th style="width: 9%;" class="text-right">Hasil Jadi</th>
                            <th style="width: 8%;" class="text-right">HPP / Karton</th>
                            <th style="width: 8%;" class="text-right">HPP / Pcs</th>
                            <th style="width: 6%;" class="text-center no-print">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packagingBranches as $idx => $branch)
                            @php
                                $tglKemas = \Carbon\Carbon::parse($branch['pack_tgl']);
                                $detailRowId = 'detail-pack-' . $branch['pack_id'];
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('produksi.packaging.show', $branch['pack_id']) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;" title="Buka Detail Kemas">
                                        {{ $branch['pack_no'] }}
                                    </a>
                                    <span style="font-size: 0.7rem; color: #64748b; display: block;">{{ $tglKemas->format('d/m/Y') }} (Shift {{ $branch['shift'] }})</span>
                                </td>
                                <td>
                                    <strong style="color: #0f172a;">{{ $branch['barang_jadi_nm'] }}</strong>
                                    <span style="font-size: 0.7rem; color: #64748b; font-family: monospace; display: block;">{{ $branch['barang_jadi_cd'] }}</span>
                                </td>
                                <td>
                                    <code style="font-size: 0.75rem; font-weight: 700; color: #475569;">{{ $branch['batch_no'] }}</code>
                                    @if($branch['expired_tgl'])
                                        <span style="font-size: 0.685rem; color: #64748b; display: block;">Exp: {{ date('d/m/y', strtotime($branch['expired_tgl'])) }}</span>
                                    @endif
                                </td>
                                <td class="text-right num-val">
                                    Rp {{ number_format($branch['total_biaya_wip'], 0, ',', '.') }}
                                </td>
                                <td class="text-right num-val" style="color: #64748b;">
                                    Rp {{ number_format($branch['total_biaya_kemasan'], 0, ',', '.') }}
                                </td>
                                <td class="text-right num-val" style="color: #64748b;">
                                    Rp {{ number_format($branch['total_biaya_tenaga_kerja'], 0, ',', '.') }}
                                </td>
                                <td class="text-right num-val" style="font-weight: 700; color: #0f172a;">
                                    Rp {{ number_format($branch['total_biaya_produksi'], 0, ',', '.') }}
                                </td>
                                <td class="text-right num-val">
                                    <strong style="color: #059669;">{{ number_format($branch['hasil_karton_qty'], 0, ',', '.') }}</strong> ktn
                                    <span style="font-size: 0.7rem; color: #64748b; display: block;">{{ number_format($branch['hasil_pcs_qty'], 0, ',', '.') }} pcs</span>
                                </td>
                                <td class="text-right num-val" style="font-weight: 800; color: #0284c7;">
                                    Rp {{ number_format($branch['hpp_per_karton'], 0, ',', '.') }}
                                </td>
                                <td class="text-right num-val" style="font-weight: 800; color: #059669;">
                                    Rp {{ number_format($branch['hpp_per_pcs'], 2, ',', '.') }}
                                </td>
                                <td class="text-center no-print">
                                    <button type="button" class="btn-toggle-drilldown" onclick="togglePackagingDrilldown('{{ $detailRowId }}', this)">
                                        <span>Rincian</span>
                                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </td>
                            </tr>

                            {{-- EXPANDABLE DRILLDOWN SUB-ROW --}}
                            <tr id="{{ $detailRowId }}" class="packaging-detail-row" style="display: none;">
                                <td colspan="11" style="padding: 0;">
                                    <div class="packaging-detail-container">
                                        {{-- Sub-Table 1: Curah WIP yang Diserap --}}
                                        <div class="detail-table-card">
                                            <div class="detail-table-card-header">
                                                <span>Curah WIP yang Diserap</span>
                                                <span>Subtotal: Rp {{ number_format($branch['total_biaya_wip'], 0, ',', '.') }}</span>
                                            </div>
                                            <table class="terpadu-grid-table" style="font-size: 0.775rem;">
                                                <thead>
                                                    <tr>
                                                        <th>Nama Barang Curah</th>
                                                        <th class="text-right">Qty Ambil</th>
                                                        <th class="text-right">Harga WIP/Kg</th>
                                                        <th class="text-right">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($branch['wip_taken'] as $wItem)
                                                        <tr>
                                                            <td>{{ $wItem['barang_nm'] }}</td>
                                                            <td class="text-right num-val">{{ number_format($wItem['qty'], 2, ',', '.') }} {{ $wItem['satuan'] }}</td>
                                                            <td class="text-right num-val">Rp {{ number_format($wItem['harga_satuan'], 2, ',', '.') }}</td>
                                                            <td class="text-right num-val" style="font-weight: 700;">Rp {{ number_format($wItem['subtotal_nilai'], 0, ',', '.') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center" style="color: #94a3b8;">Tidak ada data penyerapan WIP.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Sub-Table 2: Bahan Kemas Penolong --}}
                                        <div class="detail-table-card">
                                            <div class="detail-table-card-header">
                                                <span>Bahan Kemas Penolong (Roll Plastik, Karton, Lakban)</span>
                                                <span>Subtotal: Rp {{ number_format($branch['total_biaya_kemasan'], 0, ',', '.') }}</span>
                                            </div>
                                            <table class="terpadu-grid-table" style="font-size: 0.775rem;">
                                                <thead>
                                                    <tr>
                                                        <th>Material Kemas</th>
                                                        <th class="text-right">Qty Pakai</th>
                                                        <th class="text-right">Harga Satuan</th>
                                                        <th class="text-right">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($branch['kemasan_materials'] as $kItem)
                                                        <tr>
                                                            <td>{{ $kItem['barang_nm'] }}</td>
                                                            <td class="text-right num-val">{{ number_format($kItem['qty'], 2, ',', '.') }} {{ $kItem['satuan'] }}</td>
                                                            <td class="text-right num-val">Rp {{ number_format($kItem['harga_satuan'], 2, ',', '.') }}</td>
                                                            <td class="text-right num-val" style="font-weight: 700;">Rp {{ number_format($kItem['subtotal_nilai'], 0, ',', '.') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center" style="color: #94a3b8;">Tidak ada data bahan kemasan penolong.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-right" style="color: #475569;">Total Akumulasi Biaya Pengemasan:</td>
                            <td class="text-right num-val" style="font-size: 0.875rem; color: #0f172a;">
                                Rp {{ number_format($total_biaya_kemas_kumulatif, 0, ',', '.') }}
                            </td>
                            <td class="text-right num-val" style="font-size: 0.875rem; color: #059669;">
                                {{ number_format($total_hasil_karton_kumulatif, 0, ',', '.') }} ktn
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            @else
                {{-- Empty State --}}
                <div style="text-align: center; padding: 2.5rem 1.5rem; background: #fafafa; border: 1px dashed #cbd5e1; border-radius: 6px;">
                    <div style="width: 44px; height: 44px; margin: 0 auto 0.75rem; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0 0 0.35rem;">Belum Ada Realisasi Pengemasan</h3>
                    <p style="font-size: 0.775rem; color: #64748b; margin: 0 0 1rem; max-width: 480px; margin-inline: auto;">
                        Hasil produksi SPK ini belum dibuatkan lembar pengemasan barang jadi (Finish Goods). Seluruh timbangan curah saat ini tersimpan di Gudang WIP.
                    </p>
                    <a href="{{ route('produksi.packaging.create') }}" class="btn-kemas-quick">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Buat Lembar Pengemasan</span>
                    </a>
                </div>
            @endif

            {{-- NOTICE / STATUS SISA CURAH TRANSIT --}}
            @php
                $unpackedOutputs = collect($outputs_list)->filter(fn($x) => !$x['is_fully_packed'] && $x['remaining_kg'] > 0);
            @endphp

            @if($unpackedOutputs->count() > 0)
                @foreach($unpackedOutputs as $unp)
                    @php
                        $estVal = $unp['remaining_kg'] * $unp['hpp_per_kg'];
                    @endphp
                    <div class="terpadu-transit-strip">
                        <div class="terpadu-transit-info">
                            <div class="terpadu-transit-icon">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
                            </div>
                            <div>
                                <strong style="font-size: 0.8125rem; color: #92400e; display: block;">
                                    Sisa Curah WIP Belum Dikemas: {{ $unp['barang_nm'] }}
                                </strong>
                                <span style="font-size: 0.735rem; color: #78350f;">
                                    Tersisa <strong>{{ number_format($unp['remaining_kg'], 2, ',', '.') }} kg</strong> (Nilai: <strong>Rp {{ number_format($estVal, 0, ',', '.') }}</strong>) tersimpan di Gudang WIP (Batch: {{ $unp['batch_no'] }}).
                                </span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('produksi.packaging.create') }}" class="btn-kemas-quick no-print">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Kemas Sisa Curah Ini</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- LEMBAR TANDA TANGAN CETAK (PRINT ONLY) --}}
    <div class="print-only" style="margin-top: 2rem; break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 8.5pt;">
            <tr>
                <td style="width: 33.33%; padding-bottom: 50pt;">
                    Dibuat Oleh,<br><strong>Admin Produksi</strong>
                </td>
                <td style="width: 33.33%; padding-bottom: 50pt;">
                    Diperiksa Oleh,<br><strong>Bagian Akuntansi &amp; HPP</strong>
                </td>
                <td style="width: 33.33%; padding-bottom: 50pt;">
                    Disetujui Oleh,<br><strong>Manajer Operasional</strong>
                </td>
            </tr>
            <tr>
                <td>( ......................................... )</td>
                <td>( ......................................... )</td>
                <td>( ......................................... )</td>
            </tr>
        </table>
    </div>

</div>

@push('scripts')
<script>
    function togglePackagingDrilldown(rowId, btn) {
        const row = document.getElementById(rowId);
        if (!row) return;

        if (row.style.display === 'none' || row.style.display === '') {
            row.style.display = 'table-row';
            btn.style.background = '#0284c7';
            btn.style.color = '#ffffff';
            btn.style.borderColor = '#0284c7';
        } else {
            row.style.display = 'none';
            btn.style.background = '';
            btn.style.color = '';
            btn.style.borderColor = '';
        }
    }
</script>
@endpush
@endsection
