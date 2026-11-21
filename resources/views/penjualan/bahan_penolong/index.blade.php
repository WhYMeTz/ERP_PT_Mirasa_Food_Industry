@extends('layouts.app')

@section('title', 'Penjualan Bahan Penolong & Bumbu - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/bahan_penolong/bp-index.css') }}?v={{ filemtime(public_path('css/penjualan/bahan_penolong/bp-index.css')) }}">
@endpush

@section('content')
<div class="bp-container">
    {{-- HEADER ROW --}}
    <div class="bp-header-row">
        <div>
            <h1 class="bp-title">Penjualan Bahan Penolong &amp; Bumbu</h1>
            <p class="bp-subtitle">
                Pencatatan transaksi penjualan karton dus, plastik kemasan, bumbu eceran, serta persediaan non-produk jadi.
            </p>
        </div>
        <div>
            <a href="{{ route('penjualan.bahan-penolong.create') }}" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 700; font-size: 0.875rem; padding: 0.55rem 1.15rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                + Transaksi Penjualan Baru
            </a>
        </div>
    </div>

    @if (session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; color: #166534; font-size: 0.85rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- METRIC CARDS --}}
    <div class="bp-kpi-grid">
        <div class="bp-kpi-card">
            <div class="bp-kpi-label">Omzet Hari Ini</div>
            <div class="bp-kpi-val highlight">Rp {{ number_format($metrics['today_omzet'], 0, ',', '.') }}</div>
            <div class="bp-kpi-sub">{{ $metrics['today_count'] }} Transaksi Hari Ini</div>
        </div>
        <div class="bp-kpi-card">
            <div class="bp-kpi-label">Omzet Bulan Ini</div>
            <div class="bp-kpi-val amber">Rp {{ number_format($metrics['month_omzet'], 0, ',', '.') }}</div>
            <div class="bp-kpi-sub">{{ $metrics['month_count'] }} Transaksi Bulan Ini</div>
        </div>
        <div class="bp-kpi-card">
            <div class="bp-kpi-label">Total Omzet Non-Core</div>
            <div class="bp-kpi-val">Rp {{ number_format($metrics['total_omzet'], 0, ',', '.') }}</div>
            <div class="bp-kpi-sub">Akumulasi Penjualan Bahan Penolong</div>
        </div>
        <div class="bp-kpi-card">
            <div class="bp-kpi-label">Total Transaksi</div>
            <div class="bp-kpi-val">{{ number_format($metrics['total_count'], 0) }}</div>
            <div class="bp-kpi-sub">Seluruh Transaksi Tercatat</div>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="bp-filter-card">
        <form method="GET" action="{{ route('penjualan.bahan-penolong.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Cari No. Transaksi, Pembeli, atau Nama Bahan/Bumbu..." value="{{ $search }}" style="font-size: 0.85rem;">
            </div>
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" style="font-size: 0.85rem; width: 145px;">
                <span style="color: #94a3b8; font-size: 0.8rem;">s/d</span>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" style="font-size: 0.85rem; width: 145px;">
            </div>
            <div style="display: flex; gap: 0.4rem;">
                <button type="submit" class="btn btn-secondary" style="font-size: 0.85rem; font-weight: 600;">Filter</button>
                @if ($search || $startDate || $endDate)
                    <a href="{{ route('penjualan.bahan-penolong.index') }}" class="btn btn-light" style="font-size: 0.85rem; color: #64748b; border: 1px solid #cbd5e1;">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- DATA TABLE CARD --}}
    <div class="bp-table-card">
        <div style="overflow-x: auto;">
            <table class="bp-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 140px;">No. Transaksi</th>
                        <th style="width: 110px;">Tanggal</th>
                        <th style="min-width: 160px;">Pembeli / Customer</th>
                        <th style="min-width: 200px;">Rincian Bahan / Bumbu</th>
                        <th style="width: 100px; text-align: right;">Total Qty</th>
                        <th style="width: 130px; text-align: right;">Total Bayar</th>
                        <th style="width: 110px; text-align: center;">Status</th>
                        <th style="width: 100px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $idx => $row)
                        @php
                            $menuId = 'actionMenu_' . $row->so_id;
                            $firstDetail = $row->details->first();
                            $detailCount = $row->details->count();
                            $totQty = $row->details->sum('pesan_qty');
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b;">
                                {{ $sales->firstItem() + $idx }}
                            </td>
                            <td>
                                <a href="{{ route('penjualan.bahan-penolong.show', $row->so_id) }}" style="font-weight: 700; font-family: monospace; color: #0f172a; text-decoration: none;">
                                    {{ $row->so_no }}
                                </a>
                                @if ($row->faktur_no)
                                    <div style="font-size: 0.7rem; color: #64748b; font-family: monospace;">Faktur: {{ $row->faktur_no }}</div>
                                @endif
                            </td>
                            <td style="color: #475569; white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($row->so_tgl)->translatedFormat('d M Y') }}
                            </td>
                            <td>
                                <strong style="color: #0f172a; display: block;">{{ $row->supir_nm ?: ($row->customer?->customer_nm ?? 'Pelanggan Umum') }}</strong>
                                <span style="font-size: 0.725rem; color: #64748b;">Metode: {{ $row->top_txt ?: 'TUNAI' }}</span>
                            </td>
                            <td>
                                @if ($firstDetail)
                                    <div style="font-weight: 600; color: #1e293b;">
                                        {{ $firstDetail->barang?->barang_nm ?? 'Item Bahan Penolong' }}
                                        @if ($detailCount > 1)
                                            <span style="font-size: 0.725rem; color: #0284c7; font-weight: 700;">+{{ $detailCount - 1 }} item lainnya</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.7rem; color: #64748b;">
                                        {{ number_format((float) $firstDetail->pesan_qty, 2) }} {{ $firstDetail->satuan }} &bull; Rp {{ number_format((float) $firstDetail->harga_satuan, 0, ',', '.') }}
                                    </div>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #0f172a;">
                                {{ number_format($totQty, 2) }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 800; color: #059669; white-space: nowrap;">
                                Rp {{ number_format((float) $row->total_tagihan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 0.725rem; font-weight: 700; padding: 3px 8px; border-radius: 4px;">
                                    Lunas &amp; Selesai
                                </span>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('penjualan.bahan-penolong.show', $row->so_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Detail</span>
                                    </a>
                                    <a href="{{ route('penjualan.bahan-penolong.cetak-nota', $row->so_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>Cetak Nota / Struk</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                Belum ada data transaksi penjualan bahan penolong &amp; bumbu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/bahan_penolong/bp-index.js') }}?v={{ filemtime(public_path('js/penjualan/bahan_penolong/bp-index.js')) }}"></script>
@endpush
