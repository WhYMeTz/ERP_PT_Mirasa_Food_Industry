@extends('layouts.app')

@section('title', 'Rekapitulasi & Laporan Penjualan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/laporan/laporan-index.css') }}">
@endpush

@section('content')
<div class="laporan-sales-container">

    {{-- HEADER & TOMBOL AKSI UTAMA --}}
    <div class="laporan-header-row">
        <div>
            <h1 class="laporan-title">Rekapitulasi &amp; Laporan Penjualan</h1>
            <p class="laporan-subtitle">
                Konsolidasi pesanan PO langganan, penjualan langsung pabrik, analisis produk terlaris &amp; piutang &bull; Periode: <strong>{{ $dateInfo['label'] }}</strong>
            </p>
        </div>

        <div class="laporan-header-actions">
            <a href="{{ route('penjualan.laporan.export-pdf', request()->query()) }}" target="_blank" class="btn btn-outline-danger" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8125rem; font-weight: 700; padding: 0.45rem 0.9rem; border-radius: 6px; border: 1.5px solid #fca5a5; color: #dc2626; background: #ffffff; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Rekap (PDF)</span>
            </a>
            <a href="{{ route('penjualan.so.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8125rem; font-weight: 600; padding: 0.45rem 0.9rem; border-radius: 6px; border: 1px solid #cbd5e1; color: #475569; background: #ffffff; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Daftar PO Penjualan</span>
            </a>
            <a href="{{ route('penjualan.faktur.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8125rem; font-weight: 600; padding: 0.45rem 0.9rem; border-radius: 6px; border: 1px solid #cbd5e1; color: #475569; background: #ffffff; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                <span>Daftar Faktur</span>
            </a>
        </div>
    </div>

    {{-- 4 KARTU METRIK OPERASIONAL --}}
    <div class="laporan-metrics-grid">
        {{-- METRIK 1: TOTAL OMZET PENJUALAN --}}
        <div class="laporan-metric-card">
            <div class="laporan-metric-header">
                <span class="laporan-metric-title">Total Nilai Omzet</span>
                <div class="laporan-metric-icon emerald">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="laporan-metric-val">Rp {{ number_format($metrics['total_omzet'], 0, ',', '.') }}</div>
            <div class="laporan-metric-subtext">
                <span>{{ number_format($metrics['total_transaksi']) }} transaksi penjualan selesai</span>
            </div>
        </div>

        {{-- METRIK 2: PO LANGGANAN --}}
        <div class="laporan-metric-card">
            <div class="laporan-metric-header">
                <span class="laporan-metric-title">PO Langganan (B2B)</span>
                <div class="laporan-metric-icon blue">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="laporan-metric-val">Rp {{ number_format($metrics['po_omzet'], 0, ',', '.') }}</div>
            <div class="laporan-metric-subtext">
                <span>{{ number_format($metrics['po_count']) }} pesanan PO</span>
                <span style="font-weight: 700; color: #0284c7;">{{ $metrics['po_pct'] }}% Kontribusi</span>
            </div>
        </div>

        {{-- METRIK 3: PENJUALAN LANGSUNG --}}
        <div class="laporan-metric-card">
            <div class="laporan-metric-header">
                <span class="laporan-metric-title">Penjualan Langsung / Tunai</span>
                <div class="laporan-metric-icon emerald">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="laporan-metric-val">Rp {{ number_format($metrics['direct_omzet'], 0, ',', '.') }}</div>
            <div class="laporan-metric-subtext">
                <span>{{ number_format($metrics['direct_count']) }} transaksi toko/sales</span>
                <span style="font-weight: 700; color: #059669;">{{ $metrics['direct_pct'] }}% Kontribusi</span>
            </div>
        </div>

        {{-- METRIK 4: VOLUME & PIUTANG --}}
        <div class="laporan-metric-card">
            <div class="laporan-metric-header">
                <span class="laporan-metric-title">Volume Terjual &amp; Piutang</span>
                <div class="laporan-metric-icon amber">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="laporan-metric-val">{{ number_format($metrics['total_qty'], 0) }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Unit</span></div>
            <div class="laporan-metric-subtext">
                <span>Sisa Piutang Berjalan:</span>
                <span style="font-weight: 700; color: {{ $metrics['total_piutang'] > 0 ? '#b45309' : '#059669' }};">
                    Rp {{ number_format($metrics['total_piutang'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- FILTER FORM LENGKAP --}}
    <div class="laporan-filter-card">
        <form method="GET" action="{{ route('penjualan.laporan.index') }}" class="laporan-filter-form">
            <input type="hidden" name="tab" value="{{ $tab }}">

            {{-- Preset Periode --}}
            <div class="laporan-form-group" style="width: 200px;">
                <label class="laporan-form-label" for="filterPreset">Periode Waktu</label>
                <select id="filterPreset" name="preset" class="laporan-select">
                    <option value="all" {{ $dateInfo['preset'] === 'all' ? 'selected' : '' }}>Semua Periode Transaksi</option>
                    <option value="this_month" {{ $dateInfo['preset'] === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="last_month" {{ $dateInfo['preset'] === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                    <option value="this_quarter" {{ $dateInfo['preset'] === 'this_quarter' ? 'selected' : '' }}>Kuartal Ini</option>
                    <option value="this_year" {{ $dateInfo['preset'] === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="today" {{ $dateInfo['preset'] === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ $dateInfo['preset'] === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="this_week" {{ $dateInfo['preset'] === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="custom" {{ $dateInfo['preset'] === 'custom' ? 'selected' : '' }}>Kustom Rentang Tanggal</option>
                </select>
            </div>

            {{-- Custom Date Wrap --}}
            <div id="customDateWrap" style="display: {{ $dateInfo['preset'] === 'custom' ? 'flex' : 'none' }}; gap: 0.5rem;">
                <div class="laporan-form-group" style="width: 145px;">
                    <label class="laporan-form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $dateInfo['start_date'] }}" class="laporan-input">
                </div>
                <div class="laporan-form-group" style="width: 145px;">
                    <label class="laporan-form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $dateInfo['end_date'] }}" class="laporan-input">
                </div>
            </div>

            {{-- Saluran Penjualan --}}
            <div class="laporan-form-group" style="width: 220px;">
                <label class="laporan-form-label" for="filterChannel">Saluran Penjualan</label>
                <select id="filterChannel" name="channel" class="laporan-select">
                    <option value="ALL" {{ $channel === 'ALL' ? 'selected' : '' }}>Semua Saluran (PO + Non-PO)</option>
                    <option value="PO" {{ $channel === 'PO' ? 'selected' : '' }}>Hanya PO Langganan (B2B)</option>
                    <option value="DIRECT" {{ $channel === 'DIRECT' ? 'selected' : '' }}>Hanya Penjualan Langsung Pabrik</option>
                </select>
            </div>

            {{-- Pencarian Kata Kunci --}}
            <div class="laporan-form-group" style="flex: 2; min-width: 220px;">
                <label class="laporan-form-label" for="search">Pencarian Cepat</label>
                <input type="text" id="search" name="search" value="{{ $search }}" placeholder="No SO, No Faktur, Pelanggan..." class="laporan-input">
            </div>

            {{-- Tombol Submit & Reset --}}
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 1rem; font-size: 0.8125rem; font-weight: 700; background: #0284c7; border: 1.5px solid #0284c7; color: #ffffff; border-radius: 6px; cursor: pointer;">
                    Filter Data
                </button>
                <a href="{{ route('penjualan.laporan.index') }}" class="btn btn-secondary" style="height: 38px; display: inline-flex; align-items: center; padding: 0 0.85rem; font-size: 0.8125rem; font-weight: 600; background: #f8fafc; border: 1.5px solid #cbd5e1; color: #475569; border-radius: 6px; text-decoration: none;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TAB NAVIGASI LAPORAN --}}
    <div class="laporan-tabs-bar">
        <a href="javascript:void(0)" onclick="switchLaporanTab('ringkasan')" class="laporan-tab-item {{ in_array($tab, ['ringkasan', 'produk']) ? 'active' : '' }}">
            <span>Rekapitulasi Produk Terjual ({{ $salesByProduct->count() }})</span>
        </a>
        <a href="javascript:void(0)" onclick="switchLaporanTab('pelanggan')" class="laporan-tab-item {{ $tab === 'pelanggan' ? 'active' : '' }}">
            <span>Rekapitulasi Pelanggan / Customer ({{ $salesByCustomer->count() }})</span>
        </a>
        <a href="javascript:void(0)" onclick="switchLaporanTab('transaksi')" class="laporan-tab-item {{ $tab === 'transaksi' ? 'active' : '' }}">
            <span>Buku Transaksi Lengkap ({{ $transactions->total() }})</span>
        </a>
    </div>

    {{-- KONTEN TAB 1: REKAPITULASI PRODUK TERJUAL --}}
    @if (in_array($tab, ['ringkasan', 'produk']))
        <div class="laporan-table-card">
            <div class="laporan-table-header">
                <div>
                    <h3 class="laporan-table-title">Daftar Penjualan Berdasarkan Varian Produk</h3>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Diurutkan berdasarkan total omzet tertinggi untuk periode {{ $dateInfo['label'] }}</div>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table class="laporan-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th style="min-width: 130px;">Kode Produk</th>
                            <th style="min-width: 240px;">Nama Produk Barang Jadi</th>
                            <th style="min-width: 110px;">Kategori</th>
                            <th style="min-width: 110px; text-align: right;">Qty Terjual</th>
                            <th style="min-width: 125px; text-align: right;">Rata-rata Harga</th>
                            <th style="min-width: 145px; text-align: right;">Total Nilai Omzet</th>
                            <th style="min-width: 110px; text-align: right;">Pangsa Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesByProduct as $idx => $item)
                            <tr>
                                <td style="text-align: center; color: #64748b; font-size: 0.85rem;">{{ $idx + 1 }}</td>
                                <td style="font-weight: 600; color: #475569; font-size: 0.825rem;">{{ $item->barang_cd }}</td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">{{ $item->barang_nm }}</div>
                                    <div style="font-size: 0.725rem; color: #64748b;">Diproses dalam {{ $item->frekuensi_pesanan }} transaksi</div>
                                </td>
                                <td>
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.725rem; padding: 2px 7px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                        {{ $item->jenis_barang_nm }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                    {{ number_format((float) $item->total_qty, 0) }} <span style="font-size: 0.75rem; font-weight: 500; color: #64748b;">{{ $item->satuan_nm }}</span>
                                </td>
                                <td style="text-align: right; color: #334155; font-size: 0.825rem;">
                                    Rp {{ number_format((float) $item->avg_harga, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #0284c7; font-size: 0.875rem;">
                                    Rp {{ number_format((float) $item->total_omzet, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-size: 0.825rem; color: #0f172a;">
                                    {{ $item->kontribusi_pct }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                    Tidak ada data transaksi penjualan pada periode ini. Gunakan filter periode <strong>"Semua Periode Transaksi"</strong> atau <strong>"Bulan Lalu"</strong> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($salesByProduct->count() > 0)
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #e2e8f0;">
                                <td colspan="4" style="text-align: right; padding: 0.75rem 1rem;">TOTAL:</td>
                                <td style="text-align: right; padding: 0.75rem 1rem;">{{ number_format($salesByProduct->sum('total_qty'), 0) }} Unit</td>
                                <td></td>
                                <td style="text-align: right; padding: 0.75rem 1rem; color: #0284c7;">Rp {{ number_format($salesByProduct->sum('total_omzet'), 0, ',', '.') }}</td>
                                <td style="text-align: right; padding: 0.75rem 1rem;">100.0%</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 2: REKAPITULASI PELANGGAN --}}
    @if ($tab === 'pelanggan')
        <div class="laporan-table-card">
            <div class="laporan-table-header">
                <div>
                    <h3 class="laporan-table-title">Daftar Kontribusi Pembelian Pelanggan (Customer)</h3>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Diurutkan berdasarkan nilai transaksi tertinggi</div>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table class="laporan-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th style="min-width: 130px;">Kode Customer</th>
                            <th style="min-width: 240px;">Nama Pelanggan</th>
                            <th style="min-width: 110px;">Klasifikasi</th>
                            <th style="min-width: 100px; text-align: center;">Jumlah Transaksi</th>
                            <th style="min-width: 155px; text-align: right;">Total Nilai Pembelian</th>
                            <th style="min-width: 145px; text-align: right;">Sisa Piutang Berjalan</th>
                            <th style="min-width: 110px; text-align: right;">Pangsa Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesByCustomer as $idx => $cust)
                            <tr>
                                <td style="text-align: center; color: #64748b; font-size: 0.85rem;">{{ $idx + 1 }}</td>
                                <td style="font-weight: 600; color: #475569; font-size: 0.825rem;">{{ $cust->customer_cd }}</td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">{{ $cust->customer_nm }}</div>
                                </td>
                                <td>
                                    <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.725rem; padding: 2px 7px; border-radius: 4px; border: 1px solid #bfdbfe;">
                                        {{ $cust->klasifikasi }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 600; color: #334155;">
                                    {{ $cust->total_transaksi }} transaksi
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #0f172a; font-size: 0.875rem;">
                                    Rp {{ number_format((float) $cust->total_omzet, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-size: 0.825rem; color: {{ (float) $cust->total_sisa_piutang > 0 ? '#b91c1c' : '#059669' }};">
                                    {{ (float) $cust->total_sisa_piutang > 0 ? 'Rp ' . number_format((float) $cust->total_sisa_piutang, 0, ',', '.') : '✓ Lunas' }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-size: 0.825rem; color: #0f172a;">
                                    {{ $cust->kontribusi_pct }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                    Tidak ada data transaksi pelanggan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- KONTEN TAB 3: BUKU TRANSAKSI GABUNGAN --}}
    @if ($tab === 'transaksi')
        <div class="laporan-table-card">
            <div class="laporan-table-header">
                <div>
                    <h3 class="laporan-table-title">Daftar Seluruh Transaksi Penjualan Riil</h3>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Menampilkan pesanan PO langganan dan penjualan langsung pabrik</div>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table class="laporan-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th style="min-width: 140px;">No Transaksi</th>
                            <th style="min-width: 120px;">Saluran</th>
                            <th style="min-width: 95px;">Tanggal</th>
                            <th style="min-width: 200px;">Pelanggan</th>
                            <th style="min-width: 140px;">Dokumen Terkait</th>
                            <th style="min-width: 130px; text-align: right;">Total Nilai</th>
                            <th style="min-width: 125px; text-align: center;">Status Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $idx => $trx)
                            @php
                                $isDirect = ($trx->customer_po_no === 'PENJUALAN LANGSUNG' || str_starts_with($trx->so_no, 'DIR-'));
                                $fakturItem = $trx->faktur;
                            @endphp
                            <tr>
                                <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                    {{ $transactions->firstItem() + $idx }}
                                </td>
                                <td>
                                    <a href="{{ $isDirect ? route('penjualan.langsung.show', $trx->so_id) : route('penjualan.so.show', $trx->so_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.875rem;">
                                        {{ $trx->so_no }}
                                    </a>
                                </td>
                                <td>
                                    @if ($isDirect)
                                        <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 700; font-size: 0.725rem; padding: 2px 7px; border-radius: 4px;">
                                            Langsung Pabrik
                                        </span>
                                    @else
                                        <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 700; font-size: 0.725rem; padding: 2px 7px; border-radius: 4px;">
                                            PO Langganan
                                        </span>
                                    @endif
                                </td>
                                <td style="font-size: 0.825rem; color: #334155; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($trx->so_tgl)->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.85rem;">{{ $trx->customer?->customer_nm ?? '-' }}</div>
                                    @if($trx->customer?->customer_cd)
                                        <div style="font-size: 0.725rem; color: #64748b;">{{ $trx->customer->customer_cd }}</div>
                                    @endif
                                </td>
                                <td style="font-size: 0.75rem; color: #475569;">
                                    @if($fakturItem)
                                        <div><span style="color:#64748b;">Faktur:</span> <strong>{{ $fakturItem->faktur_no }}</strong></div>
                                    @elseif($trx->faktur_no)
                                        <div><span style="color:#64748b;">Faktur:</span> <strong>{{ $trx->faktur_no }}</strong></div>
                                    @endif
                                    @if($trx->surat_jalan_no)
                                        <div><span style="color:#64748b;">SJ:</span> {{ $trx->surat_jalan_no }}</div>
                                    @endif
                                    @if(!$fakturItem && !$trx->faktur_no && !$trx->surat_jalan_no)
                                        <span style="color:#94a3b8; font-style:italic;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #0f172a; font-size: 0.875rem;">
                                    Rp {{ number_format((float) $trx->total_tagihan, 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    @if ($isDirect || ($fakturItem && $fakturItem->status_pembayaran === 'LUNAS'))
                                        <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 700; font-size: 0.725rem; padding: 2px 7px; border-radius: 4px;">
                                            ✓ Lunas
                                        </span>
                                    @elseif ($fakturItem && (float)$fakturItem->sisa_piutang > 0)
                                        <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; font-size: 0.725rem; padding: 2px 7px; border-radius: 4px;">
                                            Piutang (Tempo)
                                        </span>
                                    @else
                                        <span class="badge" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-weight: 500; font-size: 0.725rem; padding: 2px 7px; border-radius: 4px;">
                                            Belum Ditagih
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                    Tidak ada transaksi yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/laporan/laporan-index.js') }}"></script>
@endpush
