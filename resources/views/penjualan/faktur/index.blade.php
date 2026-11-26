@extends('layouts.app')

@section('title', 'Penjualan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/faktur/faktur-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penjualan/faktur/faktur-form.css') }}">
@endpush

@section('content')
<div class="fk-index-container">

    {{-- HEADER HALAMAN FAKTUR --}}
    <div class="fk-header-row">
        <div>
            <h1 class="fk-page-title">Faktur Penjualan &amp; Piutang Usaha</h1>
            <p class="fk-page-subtitle">
                Monitoring status tagihan piutang customer (AR), pencatatan pembayaran pelunasan, dan rekonsiliasi faktur penjualan.
            </p>
        </div>
    </div>

    {{-- 4 KARTU METRIK KEUANGAN & PIUTANG --}}
    <div class="fk-metrics-grid">
        {{-- KARTU 1: TOTAL FAKTUR --}}
        <div class="fk-metric-card">
            <div class="fk-metric-header">
                <span class="fk-metric-title">Total Dokumen Faktur</span>
                <div class="fk-metric-icon blue">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="fk-metric-val">{{ number_format($metrics['total_dokumen'], 0, ',', '.') }}</div>
            <div class="fk-metric-subtext">Akumulasi faktur komersial diterbitkan</div>
        </div>

        {{-- KARTU 2: TOTAL OMZET PENJUALAN --}}
        <div class="fk-metric-card">
            <div class="fk-metric-header">
                <span class="fk-metric-title">Total Tagihan (Omzet)</span>
                <div class="fk-metric-icon emerald">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="fk-metric-val">Rp {{ number_format($metrics['total_omzet'], 0, ',', '.') }}</div>
            <div class="fk-metric-subtext">Nilai bruto piutang penjualan</div>
        </div>

        {{-- KARTU 3: SISA PIUTANG AKTIF --}}
        <div class="fk-metric-card">
            <div class="fk-metric-header">
                <span class="fk-metric-title">Sisa Piutang Berjalan</span>
                <div class="fk-metric-icon amber">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="fk-metric-val" style="color: #b45309;">Rp {{ number_format($metrics['total_piutang'], 0, ',', '.') }}</div>
            <div class="fk-metric-subtext">Tagihan yang belum dilunasi customer</div>
        </div>

        {{-- KARTU 4: FAKTUR BELUM LUNAS --}}
        <div class="fk-metric-card">
            <div class="fk-metric-header">
                <span class="fk-metric-title">Menunggu Pelunasan</span>
                <div class="fk-metric-icon rose">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="fk-metric-val">{{ number_format($metrics['count_belum_lunas'], 0, ',', '.') }}</div>
            <div class="fk-metric-subtext">{{ number_format($metrics['count_lunas'], 0, ',', '.') }} Faktur telah lunas</div>
        </div>
    </div>

    {{-- FILTER & SEARCH CARD --}}
    <div class="fk-filter-card">
        <form method="GET" action="{{ route('penjualan.faktur.index') }}" class="fk-filter-form">
            {{-- Search Box --}}
            <div class="fk-form-group" style="flex: 2; min-width: 220px;">
                <label class="fk-form-label" for="search">Pencarian Cepat</label>
                <input type="text" id="search" name="search" class="fk-input" 
                       placeholder="No Faktur / No PO Customer / Customer / No SJ..." 
                       value="{{ $search }}">
            </div>

            {{-- Filter Status Pembayaran --}}
            <div class="fk-form-group" style="flex: 1; min-width: 160px;">
                <label class="fk-form-label" for="status">Status Pembayaran</label>
                <select id="status" name="status" class="fk-select">
                    <option value="">Semua Status</option>
                    <option value="BELUM_LUNAS" {{ $status === 'BELUM_LUNAS' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="SEBAGIAN" {{ $status === 'SEBAGIAN' ? 'selected' : '' }}>Sebagian</option>
                    <option value="LUNAS" {{ $status === 'LUNAS' ? 'selected' : '' }}>Lunas</option>
                    <option value="TAK_TERTAGIH" {{ $status === 'TAK_TERTAGIH' ? 'selected' : '' }}>Piutang Tak Tertagih</option>
                    <option value="BATAL" {{ $status === 'BATAL' ? 'selected' : '' }}>Dibatalkan (Void)</option>
                </select>
            </div>

            {{-- Filter Tanggal --}}
            <div class="fk-form-group" style="width: 140px;">
                <label class="fk-form-label" for="date_from">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" class="fk-input" value="{{ $dateFrom }}">
            </div>
            <div class="fk-form-group" style="width: 140px;">
                <label class="fk-form-label" for="date_to">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" class="fk-input" value="{{ $dateTo }}">
            </div>

            {{-- Submit Buttons --}}
            <div style="display: flex; gap: 0.4rem;">
                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 0.95rem; font-size: 0.825rem; font-weight: 700; background: #059669; border-color: #047857;">
                    Filter
                </button>
                @if ($search || $status || $dateFrom || $dateTo)
                    <a href="{{ route('penjualan.faktur.index') }}" class="btn btn-secondary" style="height: 38px; padding: 0 0.75rem; font-size: 0.825rem; display: inline-flex; align-items: center;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL DATA FAKTUR PENJUALAN --}}
    <div class="fk-table-card">
        <div class="fk-table-responsive">
            <table class="fk-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No.</th>
                        <th>Dokumen Faktur</th>
                        <th>Mitra Customer</th>
                        <th>Referensi (SJ &amp; SO)</th>
                        <th style="text-align: right;">Total Tagihan</th>
                        <th style="text-align: right;">Sisa Piutang</th>
                        <th style="text-align: center;">Status &amp; Tempo</th>
                        <th style="text-align: center; width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fakturList as $idx => $faktur)
                        @php
                            $order = $faktur->order;
                            $sj = $faktur->pengeluaran;
                            $customer = $faktur->customer;
                            $menuId = 'actionMenuFk_' . $faktur->faktur_id;
                            
                            $todayCarbon = \Carbon\Carbon::today();
                            $tglTempo = $faktur->tgl_jatuh_tempo ? \Carbon\Carbon::parse($faktur->tgl_jatuh_tempo) : null;
                            $diffDays = $tglTempo ? $todayCarbon->diffInDays($tglTempo, false) : 0;
                        @endphp
                        <tr>
                            {{-- 1. No. Urut --}}
                            <td style="text-align: center; color: #94a3b8; font-weight: 600; font-size: 0.775rem;">
                                {{ $fakturList->firstItem() + $idx }}
                            </td>

                            {{-- 2. Dokumen Faktur & Tgl Terbit --}}
                            <td>
                                <a href="{{ route('penjualan.faktur.show', $faktur->faktur_id) }}" class="fk-link-faktur">
                                    {{ $faktur->faktur_no }}
                                </a>
                                <div class="fk-subtext">
                                    {{ \Carbon\Carbon::parse($faktur->faktur_tgl)->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- 3. Mitra Customer & TOP --}}
                            <td>
                                <div class="fk-customer-name">
                                    {{ $customer?->customer_nm ?: 'Pelanggan Umum' }}
                                </div>
                                <div class="fk-subtext">
                                    <span>TOP: {{ $faktur->top_txt ?: '-' }}</span>
                                </div>
                            </td>

                            {{-- 4. Referensi Surat Jalan & SO/PO --}}
                            <td>
                                @if ($sj)
                                    <a href="{{ route('penjualan.pengeluaran.show', $sj->pengeluaran_id) }}" class="fk-link-ref">
                                        {{ $sj->pengeluaran_no }}
                                    </a>
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                                <div class="fk-subtext">
                                    @if ($order)
                                        <a href="{{ route('penjualan.so.show', $order->so_id) }}" class="fk-link-sub">SO: {{ $order->so_no }}</a>
                                    @endif
                                    @if ($faktur->customer_po_no)
                                        <span style="color: #cbd5e1;">&bull;</span>
                                        <span>PO: {{ $faktur->customer_po_no }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- 5. Total Tagihan (Omzet) --}}
                            <td style="text-align: right;">
                                <div class="fk-amount-main">
                                    Rp {{ number_format($faktur->total_tagihan, 0, ',', '.') }}
                                </div>
                                <div class="fk-subtext">
                                    @if ($faktur->ppn_nominal > 0)
                                        <span class="tag-tax ppn">PPN 11%</span>
                                    @else
                                        <span class="tag-tax non">Non-PPN</span>
                                    @endif
                                </div>
                            </td>

                            {{-- 6. Sisa Piutang --}}
                            <td style="text-align: right;">
                                @if ($faktur->status_pembayaran === 'LUNAS')
                                    <div class="fk-amount-sisa lunas">Rp 0</div>
                                    <div class="fk-subtext emerald">Lunas</div>
                                @elseif ($faktur->status_pembayaran === 'TAK_TERTAGIH')
                                    <div class="fk-amount-sisa macet">
                                        Rp {{ number_format($faktur->sisa_piutang, 0, ',', '.') }}
                                    </div>
                                    <div class="fk-subtext danger" style="font-weight: 700;">Tak Tertagih</div>
                                @elseif ($faktur->status_pembayaran === 'SEBAGIAN')
                                    <div class="fk-amount-sisa sebagian">
                                        Rp {{ number_format($faktur->sisa_piutang, 0, ',', '.') }}
                                    </div>
                                    <div class="fk-subtext emerald">
                                        Dibayar: Rp {{ number_format($faktur->total_dibayar, 0, ',', '.') }}
                                    </div>
                                @elseif ($diffDays < 0 && $faktur->status_pembayaran === 'BELUM_LUNAS')
                                    <div class="fk-amount-sisa overdue">
                                        Rp {{ number_format($faktur->sisa_piutang, 0, ',', '.') }}
                                    </div>
                                    <div class="fk-subtext danger">Tertunggak</div>
                                @else
                                    <div class="fk-amount-sisa">
                                        Rp {{ number_format($faktur->sisa_piutang, 0, ',', '.') }}
                                    </div>
                                    <div class="fk-subtext">Belum Bayar</div>
                                @endif
                            </td>

                            {{-- 7. Status & Jatuh Tempo --}}
                            <td style="text-align: center;">
                                @if ($faktur->status_pembayaran === 'LUNAS')
                                    <span class="badge-status lunas">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Lunas
                                    </span>
                                    <div class="fk-subtext emerald">Lengkap</div>
                                @elseif ($faktur->status_pembayaran === 'TAK_TERTAGIH')
                                    <span class="badge-status macet">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Piutang Tak Tertagih
                                    </span>
                                    <div class="fk-subtext danger" title="{{ $faktur->alasan_macet }}" style="max-width: 130px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0 auto;">
                                        {{ $faktur->alasan_macet ?: 'Tak Tertagih' }}
                                    </div>
                                @elseif ($faktur->status_pembayaran === 'BATAL')
                                    <span class="badge-status batal">
                                        Batal (Void)
                                    </span>
                                @elseif ($faktur->status_pembayaran === 'SEBAGIAN')
                                    <span class="badge-status sebagian">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Sebagian
                                    </span>
                                    @if ($tglTempo)
                                        <div class="fk-subtext">Tempo: {{ $tglTempo->translatedFormat('d M Y') }}</div>
                                    @endif
                                @else
                                    {{-- BELUM LUNAS --}}
                                    @if ($tglTempo)
                                        @if ($diffDays < 0)
                                            <span class="badge-status overdue">
                                                Lewat {{ abs($diffDays) }} Hari
                                            </span>
                                            <div class="fk-subtext danger">Tempo: {{ $tglTempo->translatedFormat('d M Y') }}</div>
                                        @elseif ($diffDays <= 3)
                                            <span class="badge-status tempo-dekat">
                                                Tempo H-{{ $diffDays > 0 ? $diffDays : '0' }}
                                            </span>
                                            <div class="fk-subtext amber">Tempo: {{ $tglTempo->translatedFormat('d M Y') }}</div>
                                        @else
                                            <span class="badge-status belum-lunas">
                                                Belum Lunas
                                            </span>
                                            <div class="fk-subtext">Tempo: {{ $tglTempo->translatedFormat('d M Y') }}</div>
                                        @endif
                                    @else
                                        <span class="badge-status belum-lunas">
                                            Belum Lunas
                                        </span>
                                        <div class="fk-subtext">—</div>
                                    @endif
                                @endif
                            </td>

                            {{-- 8. Tombol Aksi (Smart Action Dropdown Standard Wajib) --}}
                            <td style="text-align: center;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    {{-- Lihat Detail --}}
                                    <a href="{{ route('penjualan.faktur.show', $faktur->faktur_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Faktur</span>
                                    </a>

                                    {{-- Cetak Faktur PDF --}}
                                    <a href="{{ route('penjualan.faktur.export-pdf', $faktur->faktur_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>Cetak Faktur (PDF)</span>
                                    </a>

                                    @if ($sj)
                                        <div class="action-dropdown-divider"></div>
                                        <a href="{{ route('penjualan.pengeluaran.show', $sj->pengeluaran_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            <span>Lihat Surat Jalan</span>
                                        </a>
                                    @endif

                                    @if ($faktur->order)
                                        <a href="{{ route('penjualan.so.show', $faktur->order->so_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Lihat PO Penjualan</span>
                                        </a>

                                        @if ($faktur->sisa_piutang > 0 && !in_array($faktur->status_pembayaran, ['BATAL', 'TAK_TERTAGIH']))
                                            <button type="button" 
                                                    class="action-dropdown-item" 
                                                    style="color: #0284c7; font-weight: 600;"
                                                    onclick="openFakturPaymentModal('{{ $faktur->faktur_id }}', '{{ $faktur->faktur_no }}', '{{ addslashes($customer?->customer_nm ?? 'Pelanggan Umum') }}', {{ (float) $faktur->total_tagihan }}, {{ (float) $faktur->sisa_piutang }})">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                <span>Catat Pembayaran (BKM)</span>
                                            </button>

                                            {{-- Opsi Tandai Tak Tertagih --}}
                                            <button type="button" 
                                                    class="action-dropdown-item" 
                                                    style="color: #be123c;"
                                                    onclick="openMacetModal('{{ $faktur->faktur_id }}', '{{ $faktur->faktur_no }}', '{{ addslashes($customer?->customer_nm ?? 'Pelanggan Umum') }}', {{ (float) $faktur->sisa_piutang }})">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Tandai Piutang Tak Tertagih</span>
                                            </button>
                                        @endif

                                        {{-- Jika status Tak Tertagih, beri opsi pulihkan --}}
                                        @if ($faktur->status_pembayaran === 'TAK_TERTAGIH')
                                            <form method="POST" action="{{ route('penjualan.faktur.restore-macet', $faktur->faktur_id) }}" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan Faktur {{ $faktur->faktur_no }} kembali ke daftar tagihan aktif?')" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="action-dropdown-item" style="color: #059669; width: 100%; text-align: left; background: none; border: none; cursor: pointer;">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    <span>Pulihkan ke Tagihan Aktif</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    {{-- Void / Pembatalan --}}
                                    @if ($faktur->status_pembayaran !== 'BATAL' && $faktur->total_dibayar <= 0)
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openVoidModal('{{ $faktur->faktur_id }}', '{{ $faktur->faktur_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Batalkan (Void)</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
                                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 0.75rem auto; display: block; color: #cbd5e1;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span style="font-weight: 600; font-size: 0.95rem; color: #334155;">Belum ada dokumen Penjualan.</span>
                                <p style="font-size: 0.8125rem; color: #94a3b8; margin: 0.35rem 0 0 0;">
                                    Faktur dibuat setelah Surat Jalan pengiriman fisik diterbitkan oleh bagian Gudang FG.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($fakturList->hasPages())
            <div class="fk-pagination-wrapper">
                <div style="font-size: 0.775rem; color: #64748b;">
                    Menampilkan {{ $fakturList->firstItem() }} - {{ $fakturList->lastItem() }} dari {{ $fakturList->total() }} faktur
                </div>
                <div>
                    {{ $fakturList->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- MODAL VOID & PEMBAYARAN FAKTUR & PIUTANG MACET --}}
@include('penjualan.faktur.partials.modal-void')
@include('penjualan.faktur.partials.modal-record-payment')
@include('penjualan.faktur.partials.modal-mark-macet')

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/faktur/faktur-index.js') }}"></script>
@endpush
