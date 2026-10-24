@extends('layouts.app')

@section('title', 'Pengeluaran Barang - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/pengeluaran/pengeluaran-index.css') }}">
@endpush

@section('content')
<div class="sj-index-container">

    {{-- HEADER & TOMBOL AKSI UTAMA --}}
    <div class="sj-header-row">
        <div>
            <h1 class="sj-page-title">Pengeluaran Barang</h1>
            <p class="sj-page-subtitle">
                Monitoring fisik barang keluar dari Gudang Barang Jadi (GDG-FG), alokasi batch FEFO, dan penerbitan Surat Jalan resmi.
            </p>
        </div>

        <div class="sj-header-buttons">
            <a href="{{ route('penjualan.pengeluaran.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 700;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Catat Pengeluaran Barang</span>
            </a>
            <a href="{{ route('penjualan.so.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Daftar PO Penjualan</span>
            </a>
        </div>
    </div>

    {{-- 4 KARTU METRIK OPERASIONAL --}}
    <div class="sj-metrics-grid">
        {{-- KARTU 1: TOTAL SURAT JALAN --}}
        <div class="sj-metric-card">
            <div class="sj-metric-header">
                <span class="sj-metric-title">Total Surat Jalan</span>
                <div class="sj-metric-icon blue">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="sj-metric-val">{{ number_format($metrics['total_dokumen'], 0, ',', '.') }}</div>
            <div class="sj-metric-subtext">Total dokumen pengeluaran diterbitkan</div>
        </div>

        {{-- KARTU 2: PENGIRIMAN HARI INI --}}
        <div class="sj-metric-card">
            <div class="sj-metric-header">
                <span class="sj-metric-title">Pengiriman Hari Ini</span>
                <div class="sj-metric-icon emerald">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="sj-metric-val">{{ number_format($metrics['kirim_hari_ini'], 0, ',', '.') }}</div>
            <div class="sj-metric-subtext">Truk diberangkatkan hari ini</div>
        </div>

        {{-- KARTU 3: TOTAL KARTON KELUAR --}}
        <div class="sj-metric-card">
            <div class="sj-metric-header">
                <span class="sj-metric-title">Total Fisik Keluar</span>
                <div class="sj-metric-icon amber">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="sj-metric-val">
                {{ number_format($metrics['total_qty_kirim'], 0, ',', '.') }}
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
            </div>
            <div class="sj-metric-subtext">Akumulasi produk keluar Gudang FG</div>
        </div>

        {{-- KARTU 4: DALAM PERJALANAN --}}
        <div class="sj-metric-card">
            <div class="sj-metric-header">
                <span class="sj-metric-title">Dalam Perjalanan</span>
                <div class="sj-metric-icon purple">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="sj-metric-val">{{ number_format($metrics['dalam_perjalanan'], 0, ',', '.') }}</div>
            <div class="sj-metric-subtext">Menuju customer / Siap verifikasi tiba</div>
        </div>
    </div>

    {{-- FILTER & PENCARIAN --}}
    <div class="sj-filter-card">
        <form method="GET" action="{{ route('penjualan.pengeluaran.index') }}" class="sj-filter-grid">
            <div class="sj-form-group">
                <label for="search" class="sj-form-label">Cari Pengeluaran / Customer / Truk</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                       placeholder="No. SJ, No. PO, Supir, Nopol, Nama Customer..." class="sj-form-input">
            </div>

            <div class="sj-form-group">
                <label for="status" class="sj-form-label">Status Kirim</label>
                <select name="status" id="status" class="sj-form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="TERKIRIM" {{ $status === 'TERKIRIM' ? 'selected' : '' }}>Terkirim (Di Jalan)</option>
                    <option value="SAMPAI_TUJUAN" {{ $status === 'SAMPAI_TUJUAN' ? 'selected' : '' }}>Sampai Tujuan</option>
                    <option value="BATAL" {{ $status === 'BATAL' ? 'selected' : '' }}>Dibatalkan (Void)</option>
                </select>
            </div>

            <div class="sj-form-group">
                <label for="date_from" class="sj-form-label">Tanggal Dari</label>
                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="sj-form-input">
            </div>

            <div class="sj-form-group">
                <label for="date_to" class="sj-form-label">Tanggal Sampai</label>
                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="sj-form-input">
            </div>

            <div style="display: flex; gap: 0.35rem;">
                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 0.95rem; font-size: 0.825rem; font-weight: 700;">
                    Filter
                </button>
                @if ($search || $status || $dateFrom || $dateTo)
                    <a href="{{ route('penjualan.pengeluaran.index') }}" class="btn btn-secondary" style="height: 38px; padding: 0 0.75rem; font-size: 0.825rem; display: inline-flex; align-items: center;">
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
                        <th style="width: 50px; text-align: center;">No.</th>
                        <th>Dokumen Surat Jalan</th>
                        <th>Referensi PO Penjualan</th>
                        <th>Mitra Customer</th>
                        <th>Armada &amp; Supir</th>
                        <th style="text-align: right;">Total Qty</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center; width: 100px;">Aksi</th>
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
                                    Tgl Berangkat: {{ \Carbon\Carbon::parse($sj->pengeluaran_tgl)->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- Referensi PO Penjualan --}}
                            <td>
                                @if ($order)
                                    <a href="{{ route('penjualan.so.show', $order->so_id) }}" style="font-weight: 600; color: #334155; text-decoration: none; font-size: 0.8125rem;">
                                        {{ $order->so_no }}
                                    </a>
                                    @if ($order->customer_po_no)
                                        <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">
                                            PO Ext: {{ $order->customer_po_no }}
                                        </div>
                                    @endif
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>

                            {{-- Mitra Customer --}}
                            <td>
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
                            </td>

                            {{-- Armada & Supir --}}
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">
                                    {{ $sj->supir_nm ?: 'Supir Belum Dicatat' }}
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;">
                                    {{ $sj->nopol_kendaraan ?: '-' }} {{ $sj->ekspedisi_nm ? '('.$sj->ekspedisi_nm.')' : '' }}
                                </div>
                            </td>

                            {{-- Total Kuantitas (Karton) --}}
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-feature-settings: 'tnum';">
                                {{ number_format($sj->total_qty, 0, ',', '.') }}
                                <span style="font-size: 0.75rem; font-weight: 500; color: #64748b;">Karton</span>
                            </td>

                            {{-- Status Pengiriman --}}
                            <td style="text-align: center;">
                                @if ($sj->status_cd === 'SAMPAI_TUJUAN')
                                    <span class="badge-status sampai">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Sampai Tujuan
                                    </span>
                                @elseif ($sj->status_cd === 'BATAL')
                                    <span class="badge-status batal">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Batal (Void)
                                    </span>
                                @else
                                    <span class="badge-status terkirim">
                                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Terkirim
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
                                        <span>Lihat Dokumen SJ</span>
                                    </a>

                                    {{-- Cetak PDF --}}
                                    <a href="{{ route('penjualan.pengeluaran.export-pdf', $sj->pengeluaran_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>Cetak Surat Jalan (PDF)</span>
                                    </a>

                                    <div class="action-dropdown-divider"></div>

                                    {{-- Tahap 3: Terbitkan / Lihat Penjualan --}}
                                    @if ($sj->faktur)
                                        <a href="{{ route('penjualan.faktur.show', $sj->faktur->faktur_id) }}" class="action-dropdown-item" style="color: #059669; font-weight: 700;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                            <span>Lihat Penjualan</span>
                                        </a>
                                    @elseif ($sj->status_cd !== 'BATAL')
                                        <a href="{{ route('penjualan.faktur.create', ['pengeluaran_id' => $sj->pengeluaran_id]) }}" class="action-dropdown-item" style="color: #059669; font-weight: 700;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>+ Catat Penjualan</span>
                                        </a>
                                    @endif

                                    @if ($sj->status_cd !== 'BATAL')
                                        <div class="action-dropdown-divider"></div>
                                        {{-- Batalkan Pengiriman (Void) --}}
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openVoidModal('{{ $sj->pengeluaran_id }}', '{{ $sj->pengeluaran_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Batalkan (Void SJ)</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="sj-empty-state">
                                    <svg class="sj-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <div style="font-weight: 700; font-size: 1rem; color: #334155;">Belum Ada Dokumen Surat Jalan</div>
                                    <p style="font-size: 0.8125rem; margin: 0.35rem 0 1rem 0;">
                                        Dokumen pengeluaran fisik barang jadi dari Gudang FG akan tercatat di halaman ini.
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

</div>

{{-- MODAL VOID / PEMBATALAN SURAT JALAN --}}
@include('penjualan.pengeluaran.partials.modal-void')

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/pengeluaran/pengeluaran-index.js') }}"></script>
@endpush
