@extends('layouts.app')

@section('title', 'Penjualan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/faktur/faktur-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penjualan/faktur/faktur-form.css') }}">
@endpush

@section('content')
<div class="fk-index-container">

    {{-- HEADER & TOMBOL AKSI UTAMA --}}
    <div class="fk-header-row">
        <div>
            <h1 class="fk-page-title">Penjualan</h1>
            <p class="fk-page-subtitle">
                Penerbitan tagihan komersial resmi dari Surat Jalan, pengakuan piutang usaha (AR), dan monitoring pelunasan customer.
            </p>
        </div>

        <div class="fk-header-buttons">
            <a href="{{ route('penjualan.faktur.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 700; background: #059669; border-color: #047857;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Catat Penjualan</span>
            </a>
            <a href="{{ route('penjualan.pengeluaran.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Daftar Surat Jalan</span>
            </a>
            <a href="{{ route('penjualan.so.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Daftar PO Penjualan</span>
            </a>
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
                        <th style="width: 50px; text-align: center;">No.</th>
                        <th>Dokumen Faktur</th>
                        <th>Referensi Pengiriman &amp; PO</th>
                        <th>Mitra Customer</th>
                        <th style="text-align: right;">Total Tagihan</th>
                        <th style="text-align: right;">Sisa Piutang</th>
                        <th style="text-align: center;">Status Bayar</th>
                        <th style="text-align: center; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fakturList as $idx => $faktur)
                        @php
                            $order = $faktur->order;
                            $sj = $faktur->pengeluaran;
                            $customer = $faktur->customer;
                            $menuId = 'actionMenuFk_' . $faktur->faktur_id;
                            $klasifikasi = $customer?->klasifikasi ?? 'MANUAL';
                        @endphp
                        <tr>
                            {{-- No. Urut --}}
                            <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                                {{ $fakturList->firstItem() + $idx }}
                            </td>

                            {{-- Dokumen Faktur --}}
                            <td>
                                <a href="{{ route('penjualan.faktur.show', $faktur->faktur_id) }}" style="font-weight: 700; color: #059669; text-decoration: none; font-family: monospace; font-size: 0.875rem;">
                                    {{ $faktur->faktur_no }}
                                </a>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                    Tgl: {{ \Carbon\Carbon::parse($faktur->faktur_tgl)->translatedFormat('d M Y') }}
                                    @if ($faktur->tgl_jatuh_tempo)
                                        &bull; <span style="color: #b45309;">Tempo: {{ \Carbon\Carbon::parse($faktur->tgl_jatuh_tempo)->translatedFormat('d M Y') }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Referensi Pengiriman & PO --}}
                            <td>
                                @if ($sj)
                                    <a href="{{ route('penjualan.pengeluaran.show', $sj->pengeluaran_id) }}" style="font-weight: 600; color: #0284c7; text-decoration: none; font-family: monospace; font-size: 0.8125rem;">
                                        {{ $sj->pengeluaran_no }}
                                    </a>
                                @endif
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.1rem;">
                                    @if ($order)
                                        SO: <a href="{{ route('penjualan.so.show', $order->so_id) }}" style="color: #475569; text-decoration: none;">{{ $order->so_no }}</a>
                                    @endif
                                    @if ($faktur->customer_po_no)
                                        &bull; PO Ext: {{ $faktur->customer_po_no }}
                                    @endif
                                </div>
                            </td>

                            {{-- Mitra Customer --}}
                            <td>
                                <div style="font-weight: 700; color: #1e293b;">
                                    {{ $customer?->customer_nm ?: 'Pelanggan Umum' }}
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem;">
                                    <span class="badge-klasifikasi {{ in_array($klasifikasi, ['IDM', 'MIDI_ALFA', 'IDM_ALFA']) ? 'modern' : strtolower($klasifikasi) }}">
                                        {{ $klasifikasi === 'MIDI_ALFA' ? 'Midi/Alfa' : $klasifikasi }}
                                    </span>
                                    <span style="font-size: 0.725rem; color: #64748b;">
                                        TOP: {{ $faktur->top_txt ?: '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Total Tagihan --}}
                            <td style="text-align: right;">
                                <div style="font-weight: 800; font-family: monospace; color: #0f172a; font-size: 0.875rem;">
                                    Rp {{ number_format($faktur->total_tagihan, 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.7rem; color: #64748b;">
                                    DPP: Rp {{ number_format($faktur->dpp_nominal, 0, ',', '.') }}
                                    @if ($faktur->ppn_nominal > 0)
                                        | PPN: Rp {{ number_format($faktur->ppn_nominal, 0, ',', '.') }}
                                    @endif
                                </div>
                            </td>

                            {{-- Sisa Piutang --}}
                            <td style="text-align: right;">
                                <div style="font-weight: 800; font-family: monospace; color: {{ $faktur->sisa_piutang > 0 ? '#b45309' : '#059669' }}; font-size: 0.875rem;">
                                    Rp {{ number_format($faktur->sisa_piutang, 0, ',', '.') }}
                                </div>
                                @if ($faktur->total_dibayar > 0)
                                    <div style="font-size: 0.7rem; color: #059669;">
                                        Dibayar: Rp {{ number_format($faktur->total_dibayar, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Status Bayar --}}
                            <td style="text-align: center;">
                                @if ($faktur->status_pembayaran === 'LUNAS')
                                    <span class="badge-status lunas">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Lunas
                                    </span>
                                @elseif ($faktur->status_pembayaran === 'SEBAGIAN')
                                    <span class="badge-status sebagian">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Sebagian
                                    </span>
                                @elseif ($faktur->status_pembayaran === 'BATAL')
                                    <span class="badge-status batal">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Batal (Void)
                                    </span>
                                @else
                                    <span class="badge-status belum-lunas">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Belum Lunas
                                    </span>
                                @endif
                            </td>

                            {{-- Tombol Aksi (Smart Action Dropdown Standard Wajib) --}}
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

{{-- MODAL VOID FAKTUR --}}
@include('penjualan.faktur.partials.modal-void')

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/faktur/faktur-index.js') }}"></script>
@endpush
