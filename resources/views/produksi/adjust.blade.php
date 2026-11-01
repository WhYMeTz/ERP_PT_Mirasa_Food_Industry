@extends('layouts.app')

@section('title', 'Adjustment HPP - ' . $produksi->produksi_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-adjust.css') }}?v={{ filemtime(public_path('css/produksi/produksi-adjust.css')) }}">
@endpush

@section('content')
@php
    $isIfm = str_contains(strtoupper($produksi->lini_produksi ?? ''), 'IFM') || str_contains(strtoupper($produksi->lini_produksi ?? ''), 'IFL');
    $minyakQtyAwal = (float) ($produksi->minyak_sawit_qty + $produksi->minyak_kelapa_qty);
    $unitPrices = [
        'singkong_qty' => (float) $produksi->singkong_qty > 0 ? round((float) $produksi->singkong_nilai / (float) $produksi->singkong_qty, 2) : 4500,
        'minyak_qty' => $minyakQtyAwal > 0 ? round((float) $produksi->minyak_nilai / $minyakQtyAwal, 2) : 17500,
        'bumbu_qty' => (float) $produksi->bumbu_qty > 0 ? round((float) $produksi->bumbu_nilai / (float) $produksi->bumbu_qty, 2) : 35000,
        'karton_baru_qty' => (float) $produksi->karton_baru_qty > 0 ? round((float) $produksi->karton_baru_nilai / (float) $produksi->karton_baru_qty, 2) : 10000,
        'karton_bekas_qty' => (float) $produksi->karton_bekas_qty > 0 ? round((float) $produksi->karton_bekas_nilai / (float) $produksi->karton_bekas_qty, 2) : 5000,
        'plastik_hd_qty' => (float) $produksi->plastik_hd_qty > 0 ? round((float) $produksi->plastik_hd_nilai / (float) $produksi->plastik_hd_qty, 2) : 29400,
        'lakban_besar_qty' => (float) $produksi->lakban_besar_qty > 0 ? round((float) $produksi->lakban_besar_nilai / (float) $produksi->lakban_besar_qty, 2) : 15000,
        'lakban_kecil_qty' => (float) $produksi->lakban_kecil_qty > 0 ? round((float) $produksi->lakban_kecil_nilai / (float) $produksi->lakban_kecil_qty, 2) : 6140,
        'tali_rafia_qty' => (float) $produksi->tali_rafia_qty > 0 ? round((float) $produksi->tali_rafia_nilai / (float) $produksi->tali_rafia_qty, 2) : 10000,
        'tk_jumlah_org' => (int) $produksi->tk_jumlah_org > 0 ? round((float) $produksi->tk_total_nilai / (int) $produksi->tk_jumlah_org, 2) : 150000,
        'cng_mmbtu' => (float) $produksi->cng_mmbtu > 0 ? round((float) $produksi->cng_nilai / (float) $produksi->cng_mmbtu, 2) : 450000,
        'sarung_tangan_plastik_qty' => (float) $produksi->sarung_tangan_plastik_qty > 0 ? round((float) $produksi->sarung_tangan_plastik_nilai / (float) $produksi->sarung_tangan_plastik_qty, 2) : 25000,
        'sarung_tangan_kain_qty' => (float) $produksi->sarung_tangan_kain_qty > 0 ? round((float) $produksi->sarung_tangan_kain_nilai / (float) $produksi->sarung_tangan_kain_qty, 2) : 5000,
    ];
    $totalFohAwal = (float) (
        ($produksi->pemeliharaan_mesin_nilai ?? 0) +
        ($produksi->qc_pengawasan_nilai ?? 0) +
        ($produksi->penyusutan_mesin_nilai ?? 0) +
        ($produksi->sarung_tangan_plastik_nilai ?? 0) +
        ($produksi->sarung_tangan_kain_nilai ?? 0) +
        ($produksi->fotocopy_nilai ?? 0) +
        ($produksi->limbah_padat_nilai ?? 0) +
        ($produksi->limbah_kimia_nilai ?? 0) +
        ($produksi->listrik_air_telp_nilai ?? 0)
    );
@endphp
<div class="adjust-container">
    {{-- 1. BREADCRUMBS & TOP HEADER --}}
    <div class="adjust-header">
        <div>
            <a href="{{ route('produksi.show', $produksi->produksi_id) }}" class="btn-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Detail Lembar Produksi</span>
            </a>
            <div class="adjust-title-wrap">
                <h1 class="adjust-title">Adjustment HPP</h1>
                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700; border: 1px solid #bae6fd;">{{ $produksi->produksi_no }}</span>
                <span class="badge-version">v{{ $produksi->version_no ?? 1 }}</span>
            </div>
        </div>
        <div class="adjust-header-actions">
            <button type="button" onclick="resetAllAdjFields()" class="btn-cancel-doc" title="Reset seluruh 11 nilai input ke kondisi awal dokumen">
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

    {{-- 3. PARAMETER DOKUMEN PRODUKSI (KARTU PUTIH STANDAR ERP MIRASA) --}}
    <div class="card-enterprise">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Parameter Dokumen Produksi</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                @if ($produksi->shift_cd)
                    <span class="badge-shift-pill {{ $produksi->shift_cd === 'A' ? 'badge-shift-a' : 'badge-shift-b' }}">
                        Shift {{ $produksi->shift_cd }}
                    </span>
                @endif
            </div>
        </div>
        <div style="padding: 1rem 1.25rem;">
            <div class="adjust-param-grid">
                <div class="adjust-param-item">
                    <span class="param-label">Nomor Produksi</span>
                    <span class="param-value font-mono">{{ $produksi->produksi_no }}</span>
                </div>
                <div class="adjust-param-item">
                    <span class="param-label">Tanggal Produksi</span>
                    <span class="param-value">{{ \Carbon\Carbon::parse($produksi->produksi_tgl)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                </div>
                <div class="adjust-param-item">
                    <span class="param-label">Shift Kerja</span>
                    <span class="param-value">
                        @if ($produksi->shift_cd)
                            <span class="badge-shift-pill {{ $produksi->shift_cd === 'A' ? 'badge-shift-a' : 'badge-shift-b' }}" style="font-size: 0.775rem;">
                                Shift {{ $produksi->shift_cd }}
                            </span>
                        @else
                            <span style="color: #64748b;">Shift A</span>
                        @endif
                    </span>
                </div>
                <div class="adjust-param-item">
                    <span class="param-label">Lini Produksi Pabrik</span>
                    <span class="param-value" style="font-weight: 700; color: #0f172a;">{{ $produksi->lini_produksi ?: 'PRODUKSI MANUAL' }}</span>
                </div>
                <div class="adjust-param-item">
                    <span class="param-label">Total Output Barang Jadi (WIP)</span>
                    <span class="param-value" style="font-weight: 800; color: #0284c7;">{{ number_format((float) $produksi->total_wip_qty, 2, ',', '.') }} Kg</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. LEMBAR KERJA KOMPONEN BIAYA (FULL-WIDTH - BEBAS SCROLL KANAN-KIRI) --}}
    <form id="formAdjustHppWorksheet" action="{{ route('produksi.adjust-hpp', $produksi->produksi_id) }}" method="POST">
        @csrf

        {{-- KARTU KOREKSI KUANTITAS OUTPUT HASIL PRODUKSI (WIP) --}}
        <div class="adj-output-card">
            <div class="adj-output-header">
                <div class="adj-output-title">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    <span>Koreksi Hasil Timbangan WIP (Output Produksi)</span>
                </div>
                <div style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                    Koreksi timbangan fisik hasil jadi kerupuk mentah (WIP) yang mempengaruhi pembagi HPP/Kg &amp; Rendemen
                </div>
            </div>
            @if($produksi->outputs && $produksi->outputs->isNotEmpty())
                <div style="overflow-x: auto;">
                    <table class="adj-output-table">
                        <thead>
                            <tr>
                                <th style="width: 28%; text-align: left;">Varian Output WIP</th>
                                <th style="width: 16%; text-align: center;">Nomor Batch</th>
                                <th style="width: 16%; text-align: right;">Kuantitas Awal (Kg)</th>
                                <th style="width: 20%; text-align: right;">Kuantitas Koreksi (Kg)</th>
                                <th style="width: 14%; text-align: right;">Selisih Output</th>
                                <th style="width: 6%; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produksi->outputs as $out)
                                <tr class="adj-output-row">
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;">{{ $out->barang?->barang_nm ?? $out->kategori_output }}</div>
                                        <div style="font-size: 0.725rem; color: #64748b; font-family: ui-monospace, monospace;">{{ $out->barang?->barang_cd ?? 'WIP' }} &bull; {{ $out->kategori_output }}</div>
                                    </td>
                                    <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 700; color: #0284c7;">
                                        {{ $out->batch_no ?: '-' }}
                                    </td>
                                    <td style="text-align: right; font-weight: 600; color: #64748b; font-family: ui-monospace, monospace;">
                                        {{ number_format((float) $out->qty_kg, 2, ',', '.') }} Kg
                                    </td>
                                    <td>
                                        <div class="adj-qty-input-wrap" style="margin-left: auto;">
                                            <input type="number" step="0.01" min="0" 
                                                   name="output_items[{{ $out->output_id }}][qty_kg]" 
                                                   id="input_adj_output_{{ $out->output_id }}" 
                                                   data-out-id="{{ $out->output_id }}" 
                                                   value="{{ round((float) $out->qty_kg, 2) }}" 
                                                   class="adj-calc-output-qty adj-calc-qty-input"
                                                   title="Koreksi kuantitas timbangan {{ $out->barang?->barang_nm ?? $out->kategori_output }}">
                                            <span class="adj-qty-suffix">Kg</span>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <span id="badge_delta_output_{{ $out->output_id }}" class="adj-variance-badge adj-variance-zero">0 Kg</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" id="btn_reset_output_{{ $out->output_id }}" 
                                                onclick="resetAdjOutputField('{{ $out->output_id }}')" 
                                                class="btn-adj-reset" title="Kembalikan ke kuantitas awal" style="display: none;">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">Total Kuantitas Output WIP (Hasil Olahan)</div>
                            <div style="font-size: 0.775rem; color: #64748b;">Kuantitas timbangan curah bersih yang tersimpan di gudang</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 0.825rem; color: #64748b;">Awal: <strong>{{ number_format((float) $produksi->total_wip_qty, 2, ',', '.') }} Kg</strong></span>
                            <div class="adj-qty-input-wrap">
                                <input type="number" step="0.01" min="0" 
                                       name="total_wip_qty" 
                                       id="input_adj_total_wip_qty" 
                                       value="{{ round((float) $produksi->total_wip_qty, 2) }}" 
                                       class="adj-calc-output-qty adj-calc-qty-input">
                                <span class="adj-qty-suffix">Kg</span>
                            </div>
                            <span id="badge_delta_total_wip_qty" class="adj-variance-badge adj-variance-zero">0 Kg</span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="adj-output-summary-bar">
                <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; font-size: 0.825rem;">
                    <div>
                        <span style="color: #64748b;">Total Output Awal:</span>
                        <strong style="color: #0f172a; margin-left: 0.25rem;">{{ number_format((float) $produksi->total_wip_qty, 2, ',', '.') }} Kg</strong>
                    </div>
                    <div>
                        <span style="color: #64748b;">Estimasi Output Baru:</span>
                        <strong id="summaryTotalWipBaruText" style="color: #0284c7; margin-left: 0.25rem;">{{ number_format((float) $produksi->total_wip_qty, 2, ',', '.') }} Kg</strong>
                        <span id="summaryDeltaWipBadge" class="adj-variance-badge adj-variance-zero" style="margin-left: 0.35rem; font-size: 0.7rem;">0 Kg</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="adj-rendemen-pill">
                        <span style="color: #64748b;">Rendemen Singkong:</span>
                        <span style="color: #0f172a;">{{ number_format((float) $produksi->rendemen_persen, 2, ',', '.') }}%</span>
                        <span style="color: #94a3b8;">&rarr;</span>
                        <strong id="summaryRendemenBaruText" style="color: #059669;">{{ number_format((float) $produksi->rendemen_persen, 2, ',', '.') }}%</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU TABEL KOMPONEN BIAYA FULL WIDTH --}}
        <div class="adjust-table-card">
            <div class="adjust-table-card-header">
                <div>
                    <h2 class="adjust-card-title">Lembar Kerja Komponen Biaya Pabrik (11 Komponen)</h2>
                    <div class="adjust-card-subtitle">Ubah nilai kuantitas maupun nominal rupiah di bawah ini untuk melihat simulasi dampak selisih HPP secara langsung.</div>
                </div>

                {{-- NAVIGASI TAB KATEGORI BIAYA --}}
                <div class="adj-nav-tabs">
                    <button type="button" class="adj-nav-btn active" onclick="filterAdjCategory('all', this)">Semua Komponen (11)</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('BBL', this)">Bahan Baku (3)</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('KEMASAN', this)">Kemasan &amp; Packing (6)</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('ENERGI', this)">Tenaga Kerja &amp; Energi (2)</button>
                </div>
            </div>

            {{-- TABEL INPUT KOMPONEN BIAYA FULL WIDTH TANPA HORIZONTAL SCROLL --}}
            <table class="adj-full-table">
                <thead>
                    <tr>
                        <th style="width: 26%; text-align: left;">Komponen Biaya</th>
                        <th style="width: 22%; text-align: left;">Kuantitas / Satuan</th>
                        <th style="width: 14%; text-align: right;">Nilai Awal (Rp)</th>
                        <th style="width: 19%; text-align: right;">Nilai Koreksi (Rp)</th>
                        <th style="width: 14%; text-align: right;">Selisih Nilai</th>
                        <th style="width: 5%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- GRUP 1: BAHAN BAKU LANGSUNG (BBL) --}}
                    <tr class="adj-group-row" data-group-header="BBL">
                        <td colspan="6">
                            <div class="adj-group-header-flex">
                                <div class="adj-group-header-left">
                                    <span class="adj-group-num">1</span>
                                    <span class="adj-group-name">BIAYA BAHAN BAKU LANGSUNG (BBL)</span>
                                </div>
                                <span class="adj-group-badge">3 Komponen Biaya</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="BBL">
                        <td>
                            <div class="adj-item-title">Singkong Mentah Basah</div>
                            <div class="adj-item-code">BBL-01 &bull; Singkong Grade A/B</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['singkong']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Singkong">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['singkong']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('singkong', 'Singkong Mentah Basah', 'BBL-01', 'Kg')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_singkong" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_singkong">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('singkong')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            {{-- Hidden inputs mutasi stok batch gudang --}}
                            <input type="hidden" name="additional_batches[singkong][batch_no]" id="additional_batch_no_singkong" value="">
                            <input type="hidden" name="additional_batches[singkong][qty]" id="additional_batch_qty_singkong" value="0">
                            <input type="hidden" name="additional_batches[singkong][harga]" id="additional_batch_harga_singkong" value="0">
                            {{-- Compatibility backwards --}}
                            <input type="hidden" name="singkong_stok_action" id="singkong_stok_action" value="none">
                            <input type="hidden" name="additional_batch_no" id="additional_batch_no" value="">
                            <input type="hidden" name="additional_batch_qty" id="additional_batch_qty" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) $produksi->singkong_qty, 2, ',', '.') }} Kg &bull; @ Rp {{ number_format($unitPrices['singkong_qty'], 0, ',', '.') }}/Kg</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="0.01" min="0" name="singkong_qty" id="input_adj_singkong_qty" data-key="singkong_qty" value="{{ round((float) $produksi->singkong_qty, 2) }}" class="adj-calc-qty-input" title="Koreksi kuantitas timbangan singkong mentah">
                                    <span class="adj-qty-suffix">Kg</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_singkong_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Kg</span>
                                    <button type="button" id="btn_reset_qty_singkong_qty" onclick="resetAdjQtyField('singkong_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->singkong_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="singkong_nilai" id="input_adj_singkong_nilai" data-key="singkong_nilai" value="{{ round((float) $produksi->singkong_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_singkong_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_singkong_nilai" onclick="resetAdjField('singkong_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="BBL">
                        <td>
                            <div class="adj-item-title">Minyak Goreng</div>
                            <div class="adj-item-code">BBL-02 &bull; Sawit &amp; Minyak Kelapa</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['minyak']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Minyak Goreng">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['minyak']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('minyak', 'Minyak Goreng', 'BBL-02', 'Kg')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_minyak" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_minyak">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('minyak')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            <input type="hidden" name="additional_batches[minyak][batch_no]" id="additional_batch_no_minyak" value="">
                            <input type="hidden" name="additional_batches[minyak][qty]" id="additional_batch_qty_minyak" value="0">
                            <input type="hidden" name="additional_batches[minyak][harga]" id="additional_batch_harga_minyak" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) ($produksi->minyak_sawit_qty + $produksi->minyak_kelapa_qty), 2, ',', '.') }} Kg &bull; @ Rp {{ number_format($unitPrices['minyak_qty'], 0, ',', '.') }}/Kg</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="0.01" min="0" name="minyak_qty" id="input_adj_minyak_qty" data-key="minyak_qty" value="{{ round((float) ($produksi->minyak_sawit_qty + $produksi->minyak_kelapa_qty), 2) }}" class="adj-calc-qty-input" title="Koreksi kuantitas pemakaian minyak goreng">
                                    <span class="adj-qty-suffix">Kg</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_minyak_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Kg</span>
                                    <button type="button" id="btn_reset_qty_minyak_qty" onclick="resetAdjQtyField('minyak_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->minyak_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="minyak_nilai" id="input_adj_minyak_nilai" data-key="minyak_nilai" value="{{ round((float) $produksi->minyak_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_minyak_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_minyak_nilai" onclick="resetAdjField('minyak_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="BBL">
                        <td>
                            <div class="adj-item-title">Bumbu Masak &amp; Perenyah</div>
                            <div class="adj-item-code">BBL-03 &bull; Racik Bumbu &amp; Garam</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['bumbu']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Bumbu">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['bumbu']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('bumbu', 'Bumbu Masak & Perenyah', 'BBL-03', 'Kg')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_bumbu" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_bumbu">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('bumbu')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            <input type="hidden" name="additional_batches[bumbu][batch_no]" id="additional_batch_no_bumbu" value="">
                            <input type="hidden" name="additional_batches[bumbu][qty]" id="additional_batch_qty_bumbu" value="0">
                            <input type="hidden" name="additional_batches[bumbu][harga]" id="additional_batch_harga_bumbu" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) ($produksi->bumbu_qty ?? 0), 2, ',', '.') }} Kg &bull; @ Rp {{ number_format($unitPrices['bumbu_qty'], 0, ',', '.') }}/Kg</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="0.01" min="0" name="bumbu_qty" id="input_adj_bumbu_qty" data-key="bumbu_qty" value="{{ round((float) ($produksi->bumbu_qty ?? 0), 2) }}" class="adj-calc-qty-input" title="Koreksi kuantitas racik bumbu & garam">
                                    <span class="adj-qty-suffix">Kg</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_bumbu_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Kg</span>
                                    <button type="button" id="btn_reset_qty_bumbu_qty" onclick="resetAdjQtyField('bumbu_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->bumbu_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="bumbu_nilai" id="input_adj_bumbu_nilai" data-key="bumbu_nilai" value="{{ round((float) $produksi->bumbu_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_bumbu_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_bumbu_nilai" onclick="resetAdjField('bumbu_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-group-subtotal-row" data-group-subtotal="BBL">
                        <td colspan="2">
                            <span class="adj-subtotal-label">Subtotal Biaya Bahan Baku Langsung (BBL)</span>
                        </td>
                        <td class="adj-subtotal-awal">
                            Rp {{ number_format((float) ($produksi->singkong_nilai + $produksi->minyak_nilai + $produksi->bumbu_nilai), 0, ',', '.') }}
                        </td>
                        <td class="adj-subtotal-new">
                            <span id="subtotal_bbl_text">Rp {{ number_format((float) ($produksi->singkong_nilai + $produksi->minyak_nilai + $produksi->bumbu_nilai), 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="subtotal_bbl_delta" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>

                    {{-- GRUP 2: KEMASAN & PACKING (KMS) --}}
                    <tr class="adj-group-row" data-group-header="KEMASAN">
                        <td colspan="6">
                            <div class="adj-group-header-flex">
                                <div class="adj-group-header-left">
                                    <span class="adj-group-num">2</span>
                                    <span class="adj-group-name">BIAYA KEMASAN &amp; PACKING</span>
                                </div>
                                <span class="adj-group-badge">6 Komponen Biaya</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="KEMASAN">
                        <td>
                            <div class="adj-item-title">{{ $isIfm ? 'Karton Box Baru IFM' : 'Karton Box Baru' }}</div>
                            <div class="adj-item-code">KIF00G-BP1 &bull; {{ $isIfm ? 'Box Luar Baru 100% Harga Beli' : 'Box Cetak Baru' }}</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['karton_baru']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Karton Baru">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['karton_baru']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('karton_baru', '{{ $isIfm ? 'Karton Box Baru IFM' : 'Karton Box Baru' }}', 'KMS-01', 'Pcs')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_karton_baru" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_karton_baru">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('karton_baru')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            <input type="hidden" name="additional_batches[karton_baru][batch_no]" id="additional_batch_no_karton_baru" value="">
                            <input type="hidden" name="additional_batches[karton_baru][qty]" id="additional_batch_qty_karton_baru" value="0">
                            <input type="hidden" name="additional_batches[karton_baru][harga]" id="additional_batch_harga_karton_baru" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) ($produksi->karton_baru_qty ?? 0), 0, ',', '.') }} Pcs &bull; @ Rp {{ number_format($unitPrices['karton_baru_qty'], 0, ',', '.') }}/Pcs</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="1" min="0" name="karton_baru_qty" id="input_adj_karton_baru_qty" data-key="karton_baru_qty" value="{{ round((float) ($produksi->karton_baru_qty ?? 0)) }}" class="adj-calc-qty-input" title="Koreksi kuantitas karton baru">
                                    <span class="adj-qty-suffix">Pcs</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_karton_baru_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Pcs</span>
                                    <button type="button" id="btn_reset_qty_karton_baru_qty" onclick="resetAdjQtyField('karton_baru_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->karton_baru_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="karton_baru_nilai" id="input_adj_karton_baru_nilai" data-key="karton_baru_nilai" value="{{ round((float) $produksi->karton_baru_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_karton_baru_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_karton_baru_nilai" onclick="resetAdjField('karton_baru_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="KEMASAN">
                        <td>
                            <div class="adj-item-title">{{ $isIfm ? 'Karton Puteran IFM' : 'Karton Reject / Rusak (Ex-IFM)' }}</div>
                            <div class="adj-item-code">{{ $isIfm ? 'KIF00G-PUT • Tarif 1/5 Siklus Sirkular' : 'KMS-KTN-REJ • Beban Lunas di IFM (Rp 0)' }}</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['karton_bekas']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Karton Bekas">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['karton_bekas']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('karton_bekas', '{{ $isIfm ? 'Karton Puteran IFM' : 'Karton Bekas' }}', 'KMS-02', 'Pcs')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_karton_bekas" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_karton_bekas">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('karton_bekas')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            <input type="hidden" name="additional_batches[karton_bekas][batch_no]" id="additional_batch_no_karton_bekas" value="">
                            <input type="hidden" name="additional_batches[karton_bekas][qty]" id="additional_batch_qty_karton_bekas" value="0">
                            <input type="hidden" name="additional_batches[karton_bekas][harga]" id="additional_batch_harga_karton_bekas" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) ($produksi->karton_bekas_qty ?? 0), 0, ',', '.') }} Pcs &bull; @ Rp {{ number_format($unitPrices['karton_bekas_qty'], 0, ',', '.') }}/Pcs</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="1" min="0" name="karton_bekas_qty" id="input_adj_karton_bekas_qty" data-key="karton_bekas_qty" value="{{ round((float) ($produksi->karton_bekas_qty ?? 0)) }}" class="adj-calc-qty-input" title="Koreksi kuantitas karton bekas">
                                    <span class="adj-qty-suffix">Pcs</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_karton_bekas_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Pcs</span>
                                    <button type="button" id="btn_reset_qty_karton_bekas_qty" onclick="resetAdjQtyField('karton_bekas_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->karton_bekas_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="karton_bekas_nilai" id="input_adj_karton_bekas_nilai" data-key="karton_bekas_nilai" value="{{ round((float) $produksi->karton_bekas_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_karton_bekas_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_karton_bekas_nilai" onclick="resetAdjField('karton_bekas_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="KEMASAN">
                        <td>
                            <div class="adj-item-title">Plastik HD / Inner Bag</div>
                            <div class="adj-item-code">KMS-03 &bull; Plastik Pelindung Dalam</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['plastik_hd']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Plastik HD">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['plastik_hd']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('plastik_hd', 'Plastik HD / Inner Bag', 'KMS-03', 'Roll')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_plastik_hd" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_plastik_hd">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('plastik_hd')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            <input type="hidden" name="additional_batches[plastik_hd][batch_no]" id="additional_batch_no_plastik_hd" value="">
                            <input type="hidden" name="additional_batches[plastik_hd][qty]" id="additional_batch_qty_plastik_hd" value="0">
                            <input type="hidden" name="additional_batches[plastik_hd][harga]" id="additional_batch_harga_plastik_hd" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) ($produksi->plastik_hd_qty ?? 0), 2, ',', '.') }} Roll &bull; @ Rp {{ number_format($unitPrices['plastik_hd_qty'], 0, ',', '.') }}/Roll</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="0.01" min="0" name="plastik_hd_qty" id="input_adj_plastik_hd_qty" data-key="plastik_hd_qty" value="{{ round((float) ($produksi->plastik_hd_qty ?? 0), 2) }}" class="adj-calc-qty-input" title="Koreksi kuantitas plastik HD">
                                    <span class="adj-qty-suffix">Roll</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_plastik_hd_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Roll</span>
                                    <button type="button" id="btn_reset_qty_plastik_hd_qty" onclick="resetAdjQtyField('plastik_hd_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->plastik_hd_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="plastik_hd_nilai" id="input_adj_plastik_hd_nilai" data-key="plastik_hd_nilai" value="{{ round((float) $produksi->plastik_hd_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_plastik_hd_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_plastik_hd_nilai" onclick="resetAdjField('plastik_hd_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="KEMASAN">
                        <td>
                            <div class="adj-item-title">Lakban Besar</div>
                            <div class="adj-item-code">KMS-04 &bull; Lakban 2 Inch Box</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['lakban_besar']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Lakban Besar">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['lakban_besar']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('lakban_besar', 'Lakban Besar', 'KMS-04', 'Roll')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_lakban_besar" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_lakban_besar">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('lakban_besar')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            <input type="hidden" name="additional_batches[lakban_besar][batch_no]" id="additional_batch_no_lakban_besar" value="">
                            <input type="hidden" name="additional_batches[lakban_besar][qty]" id="additional_batch_qty_lakban_besar" value="0">
                            <input type="hidden" name="additional_batches[lakban_besar][harga]" id="additional_batch_harga_lakban_besar" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) ($produksi->lakban_besar_qty ?? 0), 2, ',', '.') }} Roll &bull; @ Rp {{ number_format($unitPrices['lakban_besar_qty'], 0, ',', '.') }}/Roll</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="0.01" min="0" name="lakban_besar_qty" id="input_adj_lakban_besar_qty" data-key="lakban_besar_qty" value="{{ round((float) ($produksi->lakban_besar_qty ?? 0), 2) }}" class="adj-calc-qty-input" title="Koreksi kuantitas lakban besar">
                                    <span class="adj-qty-suffix">Roll</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_lakban_besar_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Roll</span>
                                    <button type="button" id="btn_reset_qty_lakban_besar_qty" onclick="resetAdjQtyField('lakban_besar_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->lakban_besar_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="lakban_besar_nilai" id="input_adj_lakban_besar_nilai" data-key="lakban_besar_nilai" value="{{ round((float) $produksi->lakban_besar_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_lakban_besar_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_lakban_besar_nilai" onclick="resetAdjField('lakban_besar_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="KEMASAN">
                        <td>
                            <div class="adj-item-title">Lakban Kecil</div>
                            <div class="adj-item-code">KMS-05 &bull; Lakban Seal Plastik</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['lakban_kecil']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Lakban Kecil">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['lakban_kecil']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('lakban_kecil', 'Lakban Kecil', 'KMS-05', 'Roll')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_lakban_kecil" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_lakban_kecil">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('lakban_kecil')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            <input type="hidden" name="additional_batches[lakban_kecil][batch_no]" id="additional_batch_no_lakban_kecil" value="">
                            <input type="hidden" name="additional_batches[lakban_kecil][qty]" id="additional_batch_qty_lakban_kecil" value="0">
                            <input type="hidden" name="additional_batches[lakban_kecil][harga]" id="additional_batch_harga_lakban_kecil" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) ($produksi->lakban_kecil_qty ?? 0), 2, ',', '.') }} Roll &bull; @ Rp {{ number_format($unitPrices['lakban_kecil_qty'], 0, ',', '.') }}/Roll</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="0.01" min="0" name="lakban_kecil_qty" id="input_adj_lakban_kecil_qty" data-key="lakban_kecil_qty" value="{{ round((float) ($produksi->lakban_kecil_qty ?? 0), 2) }}" class="adj-calc-qty-input" title="Koreksi kuantitas lakban kecil">
                                    <span class="adj-qty-suffix">Roll</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_lakban_kecil_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Roll</span>
                                    <button type="button" id="btn_reset_qty_lakban_kecil_qty" onclick="resetAdjQtyField('lakban_kecil_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->lakban_kecil_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="lakban_kecil_nilai" id="input_adj_lakban_kecil_nilai" data-key="lakban_kecil_nilai" value="{{ round((float) $produksi->lakban_kecil_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_lakban_kecil_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_lakban_kecil_nilai" onclick="resetAdjField('lakban_kecil_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="KEMASAN">
                        <td>
                            <div class="adj-item-title">Tali Rafia / Strapping</div>
                            <div class="adj-item-code">KMS-06 &bull; Pengikat Pallet &amp; Box</div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.3rem;">
                                @if(!empty($produksi->bahan_baku_batch_map['tali_rafia']))
                                    <span class="adj-batch-pill" title="No. Batch Pemakaian Tali Rafia">
                                        Batch: {{ implode(', ', $produksi->bahan_baku_batch_map['tali_rafia']) }}
                                    </span>
                                @endif
                                <button type="button" class="btn-batch-modal-link" onclick="openModalAlokasiBatch('tali_rafia', 'Tali Rafia / Strapping', 'KMS-06', 'Roll')" title="Ambil tambahan bahan fisik dari batch gudang lain">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Ambil dari Batch Lain</span>
                                </button>
                            </div>
                            <div id="badgeActiveAdditionalBatch_tali_rafia" style="display: none; margin-top: 0.25rem;">
                                <span class="adj-batch-pill" style="background: #f0fdf4; border-color: #86efac; color: #15803d; font-size: 0.6875rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span id="textActiveAdditionalBatch_tali_rafia">Tambahan: ...</span>
                                    <button type="button" onclick="cancelAdditionalBatch('tali_rafia')" title="Batalkan pengambilan batch lain" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 0.85rem; line-height: 1; font-weight: bold;">&times;</button>
                                </span>
                            </div>
                            <input type="hidden" name="additional_batches[tali_rafia][batch_no]" id="additional_batch_no_tali_rafia" value="">
                            <input type="hidden" name="additional_batches[tali_rafia][qty]" id="additional_batch_qty_tali_rafia" value="0">
                            <input type="hidden" name="additional_batches[tali_rafia][harga]" id="additional_batch_harga_tali_rafia" value="0">
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) ($produksi->tali_rafia_qty ?? 0), 2, ',', '.') }} Roll &bull; @ Rp {{ number_format($unitPrices['tali_rafia_qty'], 0, ',', '.') }}/Roll</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="0.01" min="0" name="tali_rafia_qty" id="input_adj_tali_rafia_qty" data-key="tali_rafia_qty" value="{{ round((float) ($produksi->tali_rafia_qty ?? 0), 2) }}" class="adj-calc-qty-input" title="Koreksi kuantitas tali rafia">
                                    <span class="adj-qty-suffix">Roll</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_tali_rafia_qty" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Roll</span>
                                    <button type="button" id="btn_reset_qty_tali_rafia_qty" onclick="resetAdjQtyField('tali_rafia_qty')" class="btn-adj-reset" title="Kembalikan kuantitas ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->tali_rafia_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="tali_rafia_nilai" id="input_adj_tali_rafia_nilai" data-key="tali_rafia_nilai" value="{{ round((float) $produksi->tali_rafia_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_tali_rafia_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_tali_rafia_nilai" onclick="resetAdjField('tali_rafia_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-group-subtotal-row" data-group-subtotal="KEMASAN">
                        <td colspan="2">
                            <span class="adj-subtotal-label">Subtotal Biaya Kemasan &amp; Packing (KMS)</span>
                        </td>
                        <td class="adj-subtotal-awal">
                            Rp {{ number_format((float) ($produksi->karton_baru_nilai + $produksi->karton_bekas_nilai + $produksi->plastik_hd_nilai + $produksi->lakban_besar_nilai + $produksi->lakban_kecil_nilai + $produksi->tali_rafia_nilai), 0, ',', '.') }}
                        </td>
                        <td class="adj-subtotal-new">
                            <span id="subtotal_kms_text">Rp {{ number_format((float) ($produksi->karton_baru_nilai + $produksi->karton_bekas_nilai + $produksi->plastik_hd_nilai + $produksi->lakban_besar_nilai + $produksi->lakban_kecil_nilai + $produksi->tali_rafia_nilai), 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="subtotal_kms_delta" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>

                    {{-- GRUP 3: TENAGA KERJA & ENERGI (ENG) --}}
                    <tr class="adj-group-row" data-group-header="ENERGI">
                        <td colspan="6">
                            <div class="adj-group-header-flex">
                                <div class="adj-group-header-left">
                                    <span class="adj-group-num">3</span>
                                    <span class="adj-group-name">TENAGA KERJA LANGSUNG &amp; ENERGI</span>
                                </div>
                                <span class="adj-group-badge">3 Komponen Biaya</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="ENERGI">
                        <td>
                            <div class="adj-item-title">Upah Tenaga Kerja Langsung (BTKL)</div>
                            <div class="adj-item-code">BTK-01 &bull; Upah Operator Lini Produksi</div>
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ $produksi->tk_jumlah_org }} Orang &bull; @ Rp {{ number_format($unitPrices['tk_jumlah_org'], 0, ',', '.') }}/Org</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="1" min="0" name="tk_jumlah_org" id="input_adj_tk_jumlah_org" data-key="tk_jumlah_org" value="{{ $produksi->tk_jumlah_org }}" class="adj-calc-qty-input" title="Koreksi jumlah tenaga kerja hadir">
                                    <span class="adj-qty-suffix">Org</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_tk_jumlah_org" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 Org</span>
                                    <button type="button" id="btn_reset_qty_tk_jumlah_org" onclick="resetAdjQtyField('tk_jumlah_org')" class="btn-adj-reset" title="Kembalikan jumlah orang ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->tk_total_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="tk_total_nilai" id="input_adj_tk_total_nilai" data-key="tk_total_nilai" value="{{ round((float) $produksi->tk_total_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_tk_total_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_tk_total_nilai" onclick="resetAdjField('tk_total_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="ENERGI">
                        <td>
                            <div class="adj-item-title">Gas Alam Terkompresi (CNG)</div>
                            <div class="adj-item-code">NRG-01 &bull; Bahan Bakar Penggorengan</div>
                        </td>
                        <td>
                            <div class="adj-qty-cell">
                                <span class="adj-qty-initial">Awal: {{ number_format((float) $produksi->cng_mmbtu, 4, ',', '.') }} MMBTU &bull; @ Rp {{ number_format($unitPrices['cng_mmbtu'], 0, ',', '.') }}/MMBTU</span>
                                <div class="adj-qty-input-wrap">
                                    <input type="number" step="0.0001" min="0" name="cng_mmbtu" id="input_adj_cng_mmbtu" data-key="cng_mmbtu" value="{{ round((float) $produksi->cng_mmbtu, 4) }}" class="adj-calc-qty-input" title="Koreksi konsumsi gas CNG">
                                    <span class="adj-qty-suffix">MMBTU</span>
                                </div>
                                <div class="adj-qty-delta-row">
                                    <span id="badge_delta_qty_cng_mmbtu" class="adj-variance-badge adj-variance-zero" style="font-size: 0.7rem;">0 MMBTU</span>
                                    <button type="button" id="btn_reset_qty_cng_mmbtu" onclick="resetAdjQtyField('cng_mmbtu')" class="btn-adj-reset" title="Kembalikan konsumsi CNG ke awal" style="display: none;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->cng_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="cng_nilai" id="input_adj_cng_nilai" data-key="cng_nilai" value="{{ round((float) $produksi->cng_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_cng_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_cng_nilai" onclick="resetAdjField('cng_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    {{-- 3. Listrik & Air + Telp (Sementara di-hidden/dialokasikan via Utilitas Bulanan) --}}
                    <tr class="adj-item-row" data-group="ENERGI" data-hidden-initial="true" style="display: none;">
                        <td>
                            <div class="adj-item-title">Listrik PLN, Air &amp; Utilitas</div>
                            <div class="adj-item-code">NRG-02 &bull; Beban Daya &amp; Sanitasi (Dialokasikan via Utilitas Bulanan)</div>
                        </td>
                        <td class="adj-no-qty-cell">
                            <span class="adj-no-qty-pill">
                                <span class="adj-no-qty-dash">&mdash;</span>
                                <span class="adj-no-qty-text">Alokasi Biaya</span>
                            </span>
                        </td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->listrik_air_telp_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="listrik_air_telp_nilai" id="input_adj_listrik_air_telp_nilai" data-key="listrik_air_telp_nilai" value="{{ round((float) $produksi->listrik_air_telp_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_listrik_air_telp_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_listrik_air_telp_nilai" onclick="resetAdjField('listrik_air_telp_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-group-subtotal-row" data-group-subtotal="ENERGI">
                        <td colspan="2">
                            <span class="adj-subtotal-label">Subtotal Tenaga Kerja &amp; Energi (NRG)</span>
                        </td>
                        <td class="adj-subtotal-awal">
                            Rp {{ number_format((float) ($produksi->tk_total_nilai + $produksi->cng_nilai + $produksi->listrik_air_telp_nilai), 0, ',', '.') }}
                        </td>
                        <td class="adj-subtotal-new">
                            <span id="subtotal_eng_text">Rp {{ number_format((float) ($produksi->tk_total_nilai + $produksi->cng_nilai + $produksi->listrik_air_telp_nilai), 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="subtotal_eng_delta" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>

                    {{-- BIAYA OVERHEAD PABRIK (FOH) DISEMBUNYIKAN PENUH: Dialihkan ke Jurnal Keuangan / Accounting --}}
                    <input type="hidden" name="qc_pengawasan_nilai" id="input_nominal_qc_pengawasan_nilai" value="{{ round((float) ($produksi->qc_pengawasan_nilai ?? 0)) }}">
                    <input type="hidden" name="limbah_kimia_nilai" id="input_nominal_limbah_kimia_nilai" value="{{ round((float) ($produksi->limbah_kimia_nilai ?? 0)) }}">
                    <input type="hidden" name="fotocopy_nilai" id="input_nominal_fotocopy_nilai" value="{{ round((float) ($produksi->fotocopy_nilai ?? 0)) }}">
                    <input type="hidden" name="sarung_tangan_plastik_nilai" id="input_nominal_sarung_tangan_plastik_nilai" value="{{ round((float) ($produksi->sarung_tangan_plastik_nilai ?? 0)) }}">
                    <input type="hidden" name="sarung_tangan_kain_nilai" id="input_nominal_sarung_tangan_kain_nilai" value="{{ round((float) ($produksi->sarung_tangan_kain_nilai ?? 0)) }}">
                    <input type="hidden" name="sarung_tangan_plastik_qty" id="input_adj_sarung_tangan_plastik_qty" value="{{ (float) ($produksi->sarung_tangan_plastik_qty ?? 0) }}">
                    <input type="hidden" name="sarung_tangan_kain_qty" id="input_adj_sarung_tangan_kain_qty" value="{{ (float) ($produksi->sarung_tangan_kain_qty ?? 0) }}">
                    <input type="hidden" name="pemeliharaan_mesin_nilai" id="input_nominal_pemeliharaan_mesin_nilai" value="{{ round((float) ($produksi->pemeliharaan_mesin_nilai ?? 0)) }}">
                    <input type="hidden" name="penyusutan_mesin_nilai" id="input_nominal_penyusutan_mesin_nilai" value="{{ round((float) ($produksi->penyusutan_mesin_nilai ?? 0)) }}">
                    <input type="hidden" name="limbah_padat_nilai" id="input_nominal_limbah_padat_nilai" value="{{ round((float) ($produksi->limbah_padat_nilai ?? 0)) }}">
                </tbody>
                <tfoot>
                    <tr class="adj-tfoot-total-row">
                        <td colspan="2">
                            <div class="adj-tfoot-title">TOTAL BIAYA PRODUKSI PABRIK (11 KOMPONEN LANGSUNG)</div>
                            <div class="adj-tfoot-hpp-box">
                                <span>Estimasi HPP Baru:</span>
                                <strong id="simulasiHppBaruText" class="adj-tfoot-hpp-val">Rp {{ number_format((float) $produksi->hpp_per_kg, 2, ',', '.') }}</strong>
                                <span style="color: #64748b; font-size: 0.75rem;">/ Kg</span>
                                <span id="simulasiSelisihHppBadge" class="adj-variance-badge adj-variance-zero" style="margin-left: 0.5rem; font-size: 0.725rem;">Selisih: Rp 0,00 / kg</span>
                            </div>
                            <div style="margin-top: 0.35rem; font-size: 0.75rem; color: #64748b; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                                <span>Output WIP: <strong id="simulasiWipFooter" style="color: #0284c7;">{{ number_format((float) $produksi->total_wip_qty, 2, ',', '.') }} Kg</strong></span>
                                <span>&bull;</span>
                                <span>Rendemen: <strong id="simulasiRendemenFooter" style="color: #059669;">{{ number_format((float) $produksi->rendemen_persen, 2, ',', '.') }}%</strong></span>
                            </div>
                        </td>
                        <td class="adj-tfoot-current">
                            Rp {{ number_format((float) $produksi->total_biaya_produksi, 0, ',', '.') }}
                        </td>
                        <td class="adj-tfoot-revised">
                            <span id="simulasiTotalBiayaBaruText">Rp {{ number_format((float) $produksi->total_biaya_produksi, 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="simulasiNetSelisihBiayaText" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 5. OTORISASI & BERITA ACARA AKUNTANSI (KARTU BAWAH TABEL) --}}
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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.35rem;">
                        <label for="alasan_txt" class="adjust-form-label" style="margin-bottom: 0;">
                            Alasan Penyesuaian / Dasar Otorisasi <span style="color: #dc2626;">*</span>
                        </label>
                        <div class="adj-quick-templates">
                            <span class="adj-quick-label">Template Cepat:</span>
                            <button type="button" class="btn-quick-chip" onclick="setAdjTemplate('timbangan')">Koreksi Timbangan</button>
                            <button type="button" class="btn-quick-chip" onclick="setAdjTemplate('tambahan')">Tambahan Bahan/Batch</button>
                            <button type="button" class="btn-quick-chip" onclick="setAdjTemplate('faktur')">Faktur/Nota Riil</button>
                            <button type="button" class="btn-quick-chip" onclick="setAdjTemplate('wip')">Koreksi Output WIP</button>
                        </div>
                    </div>
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

    {{-- 7. RIWAYAT BERITA ACARA ADJUSTMENT SEBELUMNYA (AUDIT TRAIL) --}}
    <div class="adjust-audit-card">
        <div class="adjust-audit-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="adjust-audit-title">Riwayat Berita Acara Penyesuaian Dokumen Ini</h3>
            </div>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
                {{ $produksi->adjustmentLogs ? $produksi->adjustmentLogs->count() : 0 }} Catatan
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
                        <th style="width: 12%; text-align: right;">HPP / Kg Revisi</th>
                        <th style="width: 30%;">Alasan &amp; Rincian Perubahan</th>
                        <th style="width: 13%; text-align: center;">Otorisasi</th>
                    </tr>
                </thead>
            <tbody>
                @forelse($produksi->adjustmentLogs as $log)
                    <tr>
                        <td>
                            <span class="adjust-ba-badge">{{ $log->adj_no }}</span>
                        </td>
                        <td style="color: #475569; font-weight: 600; white-space: nowrap;">
                            <div>{{ \Carbon\Carbon::parse($log->adj_tgl)->format('d/m/Y') }}</div>
                            <div style="font-size: 0.725rem; color: #94a3b8; font-weight: 500;">{{ \Carbon\Carbon::parse($log->created_dt ?? $log->adj_tgl)->format('H:i') }} WIB</div>
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
                            <div class="audit-old-val">Rp {{ number_format((float) $log->hpp_sebelum, 2, ',', '.') }}</div>
                            <div class="audit-new-val audit-hpp-val">&rarr; Rp {{ number_format((float) $log->hpp_sesudah, 2, ',', '.') }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #0f172a; font-size: 0.825rem; line-height: 1.4;">
                                {{ $log->alasan_txt }}
                            </div>
                            @if(!empty($log->perubahan_field_json) && is_array($log->perubahan_field_json))
                                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.4rem;">
                                    @foreach($log->perubahan_field_json as $fKey => $fVal)
                                        @php
                                            $isStock = isset($fVal['type']) && $fVal['type'] === 'STOCK_DEDUCTION';
                                            $isQty = isset($fVal['type']) && $fVal['type'] === 'QUANTITY';
                                            $unit = $fVal['unit'] ?? '';
                                            $oldFormatted = $isQty 
                                                ? number_format((float) ($fVal['old'] ?? 0), 2, ',', '.') . ($unit ? ' ' . $unit : '')
                                                : 'Rp ' . number_format((float) ($fVal['old'] ?? 0), 0, ',', '.');
                                            $newFormatted = $isQty 
                                                ? number_format((float) ($fVal['new'] ?? 0), 2, ',', '.') . ($unit ? ' ' . $unit : '')
                                                : 'Rp ' . number_format((float) ($fVal['new'] ?? 0), 0, ',', '.');
                                        @endphp
                                        @if($isStock)
                                            <span class="audit-diff-chip" style="background: #f0fdf4; border-color: #86efac; color: #166534;">
                                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: -1px; margin-right: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                <strong>{{ $fVal['label'] ?? $fKey }}:</strong> {{ $fVal['new'] }}
                                            </span>
                                        @else
                                            <span class="audit-diff-chip {{ $isQty ? 'audit-diff-chip-qty' : '' }}">
                                                <strong>{{ $fVal['label'] ?? $fKey }}:</strong> {{ $oldFormatted }} &rarr; {{ $newFormatted }}
                                            </span>
                                        @endif
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
                            <div style="font-weight: 700; color: #64748b; font-size: 0.9rem;">Belum Ada Riwayat Penyesuaian HPP</div>
                            <div style="font-size: 0.775rem; margin-top: 0.25rem;">Dokumen ini masih mencatat nilai kalkulasi produksi harian orisinal (Versi 1).</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- MODAL DIALOG AMBIL BATCH GUDANG LAIN (UNIVERSAL MULTI-BARANG) --}}
@include('produksi.partials.modal-alokasi-batch-material')
@endsection

@push('scripts')
    @php
        $adjOutputItems = $produksi->outputs->map(function($o) {
            return [
                'output_id' => $o->output_id,
                'qty_kg'    => (float) $o->qty_kg,
                'barang_nm' => $o->barang?->barang_nm ?? $o->kategori_output,
            ];
        })->values()->all();
    @endphp
    <script>
        window.adjBatchesMap = {!! json_encode($availableBatchesMap ?? []) !!};
        window.adjBahanBakuBatchMap = {!! json_encode($produksi->bahan_baku_batch_map ?? []) !!};
        window.adjProdConfig = {
            totalWipQty: {{ (float) $produksi->total_wip_qty }},
            totalBiayaAwal: {{ (float) $produksi->total_biaya_produksi }},
            hppAwal: {{ (float) $produksi->hpp_per_kg }},
            singkongQtyAwal: {{ (float) ($produksi->singkong_qty ?? 0) }},
            minyakQtyAwal: {{ (float) ($produksi->minyak_sawit_qty + $produksi->minyak_kelapa_qty) }},
            bumbuQtyAwal: {{ (float) ($produksi->bumbu_qty ?? 0) }},
            kartonBaruQtyAwal: {{ (float) ($produksi->karton_baru_qty ?? 0) }},
            kartonBekasQtyAwal: {{ (float) ($produksi->karton_bekas_qty ?? 0) }},
            plastikHdQtyAwal: {{ (float) ($produksi->plastik_hd_qty ?? 0) }},
            lakbanBesarQtyAwal: {{ (float) ($produksi->lakban_besar_qty ?? 0) }},
            lakbanKecilQtyAwal: {{ (float) ($produksi->lakban_kecil_qty ?? 0) }},
            taliRafiaQtyAwal: {{ (float) ($produksi->tali_rafia_qty ?? 0) }},
            cngMmbtuAwal: {{ (float) ($produksi->cng_mmbtu ?? 0) }},
            tkJumlahOrgAwal: {{ (int) ($produksi->tk_jumlah_org ?? 0) }},
            sarungTanganPlastikQtyAwal: {{ (float) ($produksi->sarung_tangan_plastik_qty ?? 0) }},
            sarungTanganKainQtyAwal: {{ (float) ($produksi->sarung_tangan_kain_qty ?? 0) }},
            rendemenAwal: {{ (float) ($produksi->rendemen_persen ?? 0) }},
            outputItems: {!! json_encode($adjOutputItems) !!},
            unitPrices: {!! json_encode($unitPrices) !!},
            
            // 1. Bahan Baku Langsung
            singkong_nilai: {{ round((float) $produksi->singkong_nilai) }},
            minyak_nilai: {{ round((float) $produksi->minyak_nilai) }},
            bumbu_nilai: {{ round((float) $produksi->bumbu_nilai) }},

            // 2. Kemasan & Packing
            karton_baru_nilai: {{ round((float) $produksi->karton_baru_nilai) }},
            karton_bekas_nilai: {{ round((float) $produksi->karton_bekas_nilai) }},
            plastik_hd_nilai: {{ round((float) $produksi->plastik_hd_nilai) }},
            lakban_besar_nilai: {{ round((float) $produksi->lakban_besar_nilai) }},
            lakban_kecil_nilai: {{ round((float) $produksi->lakban_kecil_nilai) }},
            tali_rafia_nilai: {{ round((float) $produksi->tali_rafia_nilai) }},

            // 3. Tenaga Kerja & Energi
            tk_total_nilai: {{ round((float) $produksi->tk_total_nilai) }},
            cng_nilai: {{ round((float) $produksi->cng_nilai) }},
            listrik_air_telp_nilai: {{ round((float) $produksi->listrik_air_telp_nilai) }},

            // 4. Overhead Pabrik (FOH)
            pemeliharaan_mesin_nilai: {{ round((float) $produksi->pemeliharaan_mesin_nilai) }},
            qc_pengawasan_nilai: {{ round((float) $produksi->qc_pengawasan_nilai) }},
            penyusutan_mesin_nilai: {{ round((float) $produksi->penyusutan_mesin_nilai) }},
            sarung_tangan_plastik_nilai: {{ round((float) $produksi->sarung_tangan_plastik_nilai) }},
            sarung_tangan_kain_nilai: {{ round((float) $produksi->sarung_tangan_kain_nilai) }},
            fotocopy_nilai: {{ round((float) $produksi->fotocopy_nilai) }},
            limbah_padat_nilai: {{ round((float) $produksi->limbah_padat_nilai) }},
            limbah_kimia_nilai: {{ round((float) $produksi->limbah_kimia_nilai) }},
        };
    </script>
    <script src="{{ asset('js/produksi/produksi-adjust.js') }}?v={{ filemtime(public_path('js/produksi/produksi-adjust.js')) }}"></script>
@endpush
