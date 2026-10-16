@extends('layouts.app')

@section('title', 'Adjustment HPP - ' . $produksi->produksi_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-adjust.css') }}?v={{ filemtime(public_path('css/produksi/produksi-adjust.css')) }}">
@endpush

@section('content')
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
                @if($produksi->status_cd === 'POSTED')
                    <span class="badge badge-success">Posted</span>
                @else
                    <span class="badge" style="background: #f1f5f9; color: #475569;">{{ $produksi->status_cd }}</span>
                @endif
            </div>
        </div>
        <div class="adjust-header-actions">
            <button type="button" onclick="resetAllAdjFields()" class="btn-cancel-doc" title="Reset seluruh 20 nilai input ke kondisi awal dokumen">
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

        {{-- KARTU TABEL KOMPONEN BIAYA FULL WIDTH --}}
        <div class="adjust-table-card">
            <div class="adjust-table-card-header">
                <div>
                    <h2 class="adjust-card-title">Lembar Kerja Komponen Biaya Pabrik (20 Komponen)</h2>
                    <div class="adjust-card-subtitle">Ubah nilai nominal rupiah di bawah ini untuk melihat simulasi dampak selisih HPP secara langsung.</div>
                </div>

                {{-- NAVIGASI TAB KATEGORI BIAYA --}}
                <div class="adj-nav-tabs">
                    <button type="button" class="adj-nav-btn active" onclick="filterAdjCategory('all', this)">Semua Komponen (20)</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('BBL', this)">Bahan Baku (3)</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('KEMASAN', this)">Kemasan &amp; Packing (6)</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('ENERGI', this)">Tenaga Kerja &amp; Energi (3)</button>
                    <button type="button" class="adj-nav-btn" onclick="filterAdjCategory('FOH', this)">Overhead Pabrik / FOH (8)</button>
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
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->singkong_kebun_qty, 2, ',', '.') }} Kg Bahan Baku</td>
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
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->minyak_qty, 2, ',', '.') }} Kg / Ltr Terpakai</td>
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
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->bumbu_qty, 2, ',', '.') }} Kg Campuran</td>
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
                            <div class="adj-item-title">Karton Box Baru</div>
                            <div class="adj-item-code">KMS-01 &bull; Box Cetak Baru PT Mirasa</div>
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->karton_baru_qty, 0, ',', '.') }} Pcs Box</td>
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
                            <div class="adj-item-title">Karton Box Bekas</div>
                            <div class="adj-item-code">KMS-02 &bull; Box Bekas Layak Pakai</div>
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->karton_bekas_qty, 0, ',', '.') }} Pcs Box</td>
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
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->plastik_hd_qty, 2, ',', '.') }} Roll / Kg</td>
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
                            <div class="adj-item-title">Lakban Coklat Besar</div>
                            <div class="adj-item-code">KMS-04 &bull; Lakban 2 Inch Box</div>
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->lakban_besar_qty, 2, ',', '.') }} Roll Terpakai</td>
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
                            <div class="adj-item-title">Lakban Bening Kecil</div>
                            <div class="adj-item-code">KMS-05 &bull; Lakban Seal Plastik</div>
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->lakban_kecil_qty, 2, ',', '.') }} Roll Terpakai</td>
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
                        </td>
                        <td class="adj-item-spec">{{ number_format((float) $produksi->tali_rafia_qty, 2, ',', '.') }} Roll Pemakaian</td>
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
                        <td class="adj-item-spec">{{ $produksi->tk_orang_qty ?? 0 }} Orang Tenaga Kerja</td>
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
                        <td class="adj-item-spec">{{ number_format((float) $produksi->cng_qty, 2, ',', '.') }} MMBTU / Satuan</td>
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
                    <tr class="adj-item-row" data-group="ENERGI">
                        <td>
                            <div class="adj-item-title">Listrik PLN, Air &amp; Utilitas</div>
                            <div class="adj-item-code">NRG-02 &bull; Beban Daya &amp; Sanitasi</div>
                        </td>
                        <td class="adj-item-spec">Alokasi Harian Shift</td>
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

                    {{-- GRUP 4: OVERHEAD PABRIK (FOH) --}}
                    <tr class="adj-group-row" data-group-header="FOH">
                        <td colspan="6">
                            <div class="adj-group-header-flex">
                                <div class="adj-group-header-left">
                                    <span class="adj-group-num">4</span>
                                    <span class="adj-group-name">BIAYA OVERHEAD PABRIK (FOH / BOP)</span>
                                </div>
                                <span class="adj-group-badge">8 Komponen Biaya</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="FOH">
                        <td>
                            <div class="adj-item-title">Pemeliharaan Mesin &amp; Genset</div>
                            <div class="adj-item-code">FOH-01 &bull; Servis, Oli &amp; Suku Cadang</div>
                        </td>
                        <td class="adj-item-spec">Alokasi Harian Mesin</td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->pemeliharaan_mesin_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="pemeliharaan_mesin_nilai" id="input_adj_pemeliharaan_mesin_nilai" data-key="pemeliharaan_mesin_nilai" value="{{ round((float) $produksi->pemeliharaan_mesin_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_pemeliharaan_mesin_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_pemeliharaan_mesin_nilai" onclick="resetAdjField('pemeliharaan_mesin_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="FOH">
                        <td>
                            <div class="adj-item-title">Pengawasan Mutu (QC)</div>
                            <div class="adj-item-code">FOH-02 &bull; Uji Lab &amp; Reagen Kimia</div>
                        </td>
                        <td class="adj-item-spec">Inspeksi Mutu Produksi</td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->qc_pengawasan_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="qc_pengawasan_nilai" id="input_adj_qc_pengawasan_nilai" data-key="qc_pengawasan_nilai" value="{{ round((float) $produksi->qc_pengawasan_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_qc_pengawasan_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_qc_pengawasan_nilai" onclick="resetAdjField('qc_pengawasan_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="FOH">
                        <td>
                            <div class="adj-item-title">Penyusutan Mesin Pabrik</div>
                            <div class="adj-item-code">FOH-03 &bull; Amortisasi Aset Tetap</div>
                        </td>
                        <td class="adj-item-spec">Alokasi HPP Harian</td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->penyusutan_mesin_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="penyusutan_mesin_nilai" id="input_adj_penyusutan_mesin_nilai" data-key="penyusutan_mesin_nilai" value="{{ round((float) $produksi->penyusutan_mesin_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_penyusutan_mesin_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_penyusutan_mesin_nilai" onclick="resetAdjField('penyusutan_mesin_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="FOH">
                        <td>
                            <div class="adj-item-title">Sarung Tangan Plastik</div>
                            <div class="adj-item-code">FOH-04 &bull; APD Higienitas Makanan</div>
                        </td>
                        <td class="adj-item-spec">APD Harian Operator</td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->sarung_tangan_plastik_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="sarung_tangan_plastik_nilai" id="input_adj_sarung_tangan_plastik_nilai" data-key="sarung_tangan_plastik_nilai" value="{{ round((float) $produksi->sarung_tangan_plastik_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_sarung_tangan_plastik_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_sarung_tangan_plastik_nilai" onclick="resetAdjField('sarung_tangan_plastik_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="FOH">
                        <td>
                            <div class="adj-item-title">Sarung Tangan Kain</div>
                            <div class="adj-item-code">FOH-05 &bull; APD Panas &amp; Keselamatan</div>
                        </td>
                        <td class="adj-item-spec">APD Pekerja Goreng</td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->sarung_tangan_kain_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="sarung_tangan_kain_nilai" id="input_adj_sarung_tangan_kain_nilai" data-key="sarung_tangan_kain_nilai" value="{{ round((float) $produksi->sarung_tangan_kain_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_sarung_tangan_kain_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_sarung_tangan_kain_nilai" onclick="resetAdjField('sarung_tangan_kain_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="FOH">
                        <td>
                            <div class="adj-item-title">Dokumen &amp; ATK Produksi</div>
                            <div class="adj-item-code">FOH-06 &bull; Fotocopy &amp; Form Kontrol</div>
                        </td>
                        <td class="adj-item-spec">Administrasi Shift Pabrik</td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->fotocopy_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="fotocopy_nilai" id="input_adj_fotocopy_nilai" data-key="fotocopy_nilai" value="{{ round((float) $produksi->fotocopy_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_fotocopy_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_fotocopy_nilai" onclick="resetAdjField('fotocopy_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="FOH">
                        <td>
                            <div class="adj-item-title">Pengolahan Limbah Padat</div>
                            <div class="adj-item-code">FOH-07 &bull; Kulit Singkong &amp; Abu</div>
                        </td>
                        <td class="adj-item-spec">Rit Pengangkutan Harian</td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->limbah_padat_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="limbah_padat_nilai" id="input_adj_limbah_padat_nilai" data-key="limbah_padat_nilai" value="{{ round((float) $produksi->limbah_padat_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_limbah_padat_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_limbah_padat_nilai" onclick="resetAdjField('limbah_padat_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-item-row" data-group="FOH">
                        <td>
                            <div class="adj-item-title">Pengolahan Limbah Kimia</div>
                            <div class="adj-item-code">FOH-08 &bull; IPAL &amp; Bahan Kimia</div>
                        </td>
                        <td class="adj-item-spec">Pengolahan Air Limbah</td>
                        <td class="adj-item-current">Rp {{ number_format(round((float) $produksi->limbah_kimia_nilai), 0, ',', '.') }}</td>
                        <td>
                            <div class="adj-input-wrap">
                                <span class="adj-input-prefix">Rp</span>
                                <input type="number" step="1" min="0" name="limbah_kimia_nilai" id="input_adj_limbah_kimia_nilai" data-key="limbah_kimia_nilai" value="{{ round((float) $produksi->limbah_kimia_nilai) }}" class="adj-calc-input">
                            </div>
                        </td>
                        <td><span id="badge_delta_limbah_kimia_nilai" class="adj-variance-badge adj-variance-zero">Rp 0</span></td>
                        <td style="text-align: center;">
                            <button type="button" id="btn_reset_limbah_kimia_nilai" onclick="resetAdjField('limbah_kimia_nilai')" class="btn-adj-reset" title="Kembalikan ke nilai awal" style="display: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="adj-group-subtotal-row" data-group-subtotal="FOH">
                        <td colspan="2">
                            <span class="adj-subtotal-label">Subtotal Biaya Overhead Pabrik (FOH / BOP)</span>
                        </td>
                        <td class="adj-subtotal-awal">
                            Rp {{ number_format((float) ($produksi->pemeliharaan_mesin_nilai + $produksi->qc_pengawasan_nilai + $produksi->penyusutan_mesin_nilai + $produksi->sarung_tangan_plastik_nilai + $produksi->sarung_tangan_kain_nilai + $produksi->fotocopy_nilai + $produksi->limbah_padat_nilai + $produksi->limbah_kimia_nilai), 0, ',', '.') }}
                        </td>
                        <td class="adj-subtotal-new">
                            <span id="subtotal_foh_text">Rp {{ number_format((float) ($produksi->pemeliharaan_mesin_nilai + $produksi->qc_pengawasan_nilai + $produksi->penyusutan_mesin_nilai + $produksi->sarung_tangan_plastik_nilai + $produksi->sarung_tangan_kain_nilai + $produksi->fotocopy_nilai + $produksi->limbah_padat_nilai + $produksi->limbah_kimia_nilai), 0, ',', '.') }}</span>
                        </td>
                        <td style="text-align: right;">
                            <span id="subtotal_foh_delta" class="adj-variance-badge adj-variance-zero">Rp 0</span>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="adj-tfoot-total-row">
                        <td colspan="2">
                            <div class="adj-tfoot-title">TOTAL BIAYA PRODUKSI PABRIK (20 KOMPONEN)</div>
                            <div class="adj-tfoot-hpp-box">
                                <span>Estimasi HPP Baru:</span>
                                <strong id="simulasiHppBaruText" class="adj-tfoot-hpp-val">Rp {{ number_format((float) $produksi->hpp_per_kg, 2, ',', '.') }}</strong>
                                <span style="color: #64748b; font-size: 0.75rem;">/ Kg</span>
                                <span id="simulasiSelisihHppBadge" class="adj-variance-badge adj-variance-zero" style="margin-left: 0.5rem; font-size: 0.725rem;">Selisih: Rp 0,00 / kg</span>
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
                                        <span class="audit-diff-chip">
                                            <strong>{{ $fVal['label'] ?? $fKey }}:</strong> Rp {{ number_format((float) ($fVal['old'] ?? 0), 0, ',', '.') }} &rarr; Rp {{ number_format((float) ($fVal['new'] ?? 0), 0, ',', '.') }}
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
@endsection

@push('scripts')
    <script>
        window.adjProdConfig = {
            totalWipQty: {{ (float) $produksi->total_wip_qty }},
            totalBiayaAwal: {{ (float) $produksi->total_biaya_produksi }},
            hppAwal: {{ (float) $produksi->hpp_per_kg }},
            
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
