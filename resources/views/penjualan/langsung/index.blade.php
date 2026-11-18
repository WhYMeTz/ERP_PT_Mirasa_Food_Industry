@extends('layouts.app')

@section('title', 'Penjualan Langsung / Tunai - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/direct-sales-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Penjualan Langsung / Tunai</h1>
            <span class="ds-badge tunai">Kasir Pabrik &amp; Retail</span>
        </div>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Transaksi kasir cepat tanpa dokumen PO mitra: otomatis potong stok Gudang FG (FIFO), terbitkan faktur lunas, dan cetak nota kasir.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('penjualan.langsung.create') }}" class="btn btn-primary" style="background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; font-weight: 600; font-size: 0.875rem; border-radius: 6px; color: #fff; text-decoration: none;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Transaksi Kasir Baru
        </a>
    </div>
</div>

{{-- METRIK OPERASIONAL KASIR --}}
<div class="ds-metrics-grid">
    {{-- OMZET HARI INI --}}
    <div class="ds-metric-card">
        <div class="ds-metric-header">
            <div>
                <span class="ds-metric-title">Omzet Hari Ini</span>
                <div class="ds-metric-val" style="color: #059669;">
                    Rp {{ number_format($metrics['today_omzet'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="ds-metric-icon emerald">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="ds-metric-sub">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span><strong>{{ number_format($metrics['today_count'] ?? 0) }}</strong> transaksi hari ini</span>
        </div>
    </div>

    {{-- OMZET BULAN INI --}}
    <div class="ds-metric-card">
        <div class="ds-metric-header">
            <div>
                <span class="ds-metric-title">Omzet Bulan Ini</span>
                <div class="ds-metric-val" style="color: #2563eb;">
                    Rp {{ number_format($metrics['month_omzet'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="ds-metric-icon blue">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="ds-metric-sub">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span><strong>{{ number_format($metrics['month_count'] ?? 0) }}</strong> transaksi bulan ini</span>
        </div>
    </div>

    {{-- TOTAL AKUMULASI --}}
    <div class="ds-metric-card">
        <div class="ds-metric-header">
            <div>
                <span class="ds-metric-title">Total Akumulasi Omzet</span>
                <div class="ds-metric-val" style="color: #4f46e5;">
                    Rp {{ number_format($metrics['total_omzet'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="ds-metric-icon indigo">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>
        <div class="ds-metric-sub">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span><strong>{{ number_format($metrics['total_count'] ?? 0) }}</strong> transaksi tercatat</span>
        </div>
    </div>
</div>

{{-- FILTER CARD --}}
<div class="ds-filter-card">
    <form method="GET" action="{{ route('penjualan.langsung.index') }}" class="ds-filter-form">
        <div class="ds-filter-group" style="flex: 1; min-width: 220px;">
            <label class="ds-filter-label" for="search">Cari Transaksi / Nota / Pembeli</label>
            <input type="text" name="search" id="search" class="ds-input" placeholder="No. Transaksi, Faktur, Pembeli, Catatan..." value="{{ $search ?? '' }}">
        </div>

        <div class="ds-filter-group" style="width: 160px;">
            <label class="ds-filter-label" for="start_date">Dari Tanggal</label>
            <input type="date" name="start_date" id="start_date" class="ds-input" value="{{ $startDate ?? '' }}">
        </div>

        <div class="ds-filter-group" style="width: 160px;">
            <label class="ds-filter-label" for="end_date">Sampai Tanggal</label>
            <input type="date" name="end_date" id="end_date" class="ds-input" value="{{ $endDate ?? '' }}">
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" style="background: #0f172a; border-color: #0f172a; height: 36px; padding: 0 1rem; font-size: 0.8125rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; color: #fff; cursor: pointer;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filter
            </button>
            @if (!empty($search) || !empty($startDate) || !empty($endDate))
                <a href="{{ route('penjualan.langsung.index') }}" class="btn" style="background: #f1f5f9; color: #475569; height: 36px; padding: 0 0.85rem; font-size: 0.8125rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; text-decoration: none; border: 1px solid #cbd5e1;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- TABEL TRANSAKSI PENJUALAN LANGSUNG --}}
<div class="ds-table-card">
    <div class="ds-table-wrapper">
        <table class="ds-table">
            <thead>
                <tr>
                    <th style="width: 150px;">No. Transaksi</th>
                    <th style="width: 110px;">Tanggal</th>
                    <th>Pelanggan / Pembeli</th>
                    <th>Item Produk</th>
                    <th style="text-align: right; width: 140px;">Total Tagihan</th>
                    <th style="text-align: center; width: 110px;">Metode</th>
                    <th style="text-align: center; width: 110px;">Status</th>
                    <th style="text-align: center; width: 90px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    @php
                        $menuId = 'actionMenuDirect_' . $sale->so_id;
                        $firstPengeluaran = $sale->pengeluaran->first();
                        $firstFaktur = $sale->faktur->first();
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">{{ $sale->so_no }}</div>
                            @if ($firstFaktur)
                                <div style="font-size: 0.72rem; color: #64748b;">Faktur: {{ $firstFaktur->faktur_no }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155;">{{ \Carbon\Carbon::parse($sale->so_tgl)->format('d/m/Y') }}</div>
                            <div style="font-size: 0.7rem; color: #94a3b8;">{{ \Carbon\Carbon::parse($sale->created_dt)->format('H:i') }} WIB</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #0f172a;">
                                {{ $sale->customer->customer_nm ?? 'Pelanggan Umum' }}
                            </div>
                            @if (!empty($sale->supir_nm) && $sale->supir_nm !== '-')
                                <div style="font-size: 0.72rem; color: #475569;">
                                    Pembeli: <strong>{{ $sale->supir_nm }}</strong>
                                    @if(!empty($sale->nopol_kendaraan)) ({{ $sale->nopol_kendaraan }}) @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 0.8rem; color: #334155;">
                                @php
                                    $itemCount = $sale->details->count();
                                    $firstItem = $sale->details->first();
                                @endphp
                                @if ($firstItem)
                                    <span>{{ $firstItem->barang->barang_nm ?? "Barang #{$firstItem->barang_id}" }}</span>
                                    <span style="color: #64748b;">({{ number_format($firstItem->qty, 0) }} {{ $firstItem->barang->satuanDasar?->satuan_nm ?? 'Karton' }})</span>
                                    @if ($itemCount > 1)
                                        <span class="ds-badge" style="background: #f1f5f9; color: #475569; font-size: 0.68rem; margin-left: 0.25rem;">+{{ $itemCount - 1 }} item lainnya</span>
                                    @endif
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-feature-settings: 'tnum';">
                            Rp {{ number_format($sale->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            @if (strtoupper($sale->top_txt) === 'TRANSFER')
                                <span class="ds-badge transfer">Transfer</span>
                            @else
                                <span class="ds-badge tunai">Tunai</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="ds-badge selesai">
                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                Selesai
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')" title="Pilihan Aksi">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <a href="{{ route('penjualan.langsung.show', $sale->so_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Detail</span>
                                </a>
                                <a href="{{ route('penjualan.langsung.cetak-nota', $sale->so_id) }}" target="_blank" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak Nota Kasir</span>
                                </a>
                                @if ($firstPengeluaran)
                                    <div class="action-dropdown-divider"></div>
                                    <a href="{{ route('penjualan.pengeluaran.export-pdf', $firstPengeluaran->pengeluaran_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                        <span>Surat Jalan Gudang</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🧾</div>
                            <div style="font-weight: 600; color: #475569; font-size: 0.95rem;">Belum ada riwayat transaksi penjualan langsung.</div>
                            <div style="font-size: 0.8rem; margin-top: 0.25rem;">Klik tombol "+ Transaksi Kasir Baru" untuk memulai transaksi kasir cepat.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($sales->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0;">
            {{ $sales->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/direct-sales-index.js') }}"></script>
@endpush
