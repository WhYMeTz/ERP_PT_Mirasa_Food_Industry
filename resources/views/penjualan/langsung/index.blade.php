@extends('layouts.app')

@section('title', 'Penjualan Langsung - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/direct-sales-index.css') }}?v={{ filemtime(public_path('css/penjualan/direct-sales-index.css')) }}">
@endpush

@section('content')
{{-- 1. HEADER HALAMAN STANDAR ERP MIRASA --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Penjualan Langsung</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Kelola transaksi penjualan langsung gudang pabrik dan setoran sales tanpa PO pelanggan.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('penjualan.langsung.create') }}" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Penjualan Langsung
        </a>
    </div>
</div>

{{-- 2. 4 KARTU METRIK OPERASIONAL STANDAR --}}
<div class="ds-metrics-grid">
    {{-- SEMUA TRANSAKSI --}}
    <div class="ds-metric-card">
        <div class="ds-metric-header">
            <div>
                <span class="ds-metric-title">Semua Transaksi</span>
                <div class="ds-metric-val">
                    {{ number_format($metrics['total_count'] ?? 0) }}
                </div>
            </div>
            <div class="ds-metric-icon slate">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="ds-metric-subtext">
            Total transaksi penjualan non-PO
        </div>
    </div>

    {{-- OMZET HARI INI --}}
    <div class="ds-metric-card">
        <div class="ds-metric-header">
            <div>
                <span class="ds-metric-title" style="color: #059669;">Omzet Hari Ini</span>
                <div class="ds-metric-val">
                    Rp {{ number_format($metrics['today_omzet'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="ds-metric-icon emerald">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="ds-metric-subtext">
            <strong>{{ number_format($metrics['today_count'] ?? 0) }}</strong> transaksi hari ini
        </div>
    </div>

    {{-- OMZET BULAN INI --}}
    <div class="ds-metric-card">
        <div class="ds-metric-header">
            <div>
                <span class="ds-metric-title" style="color: #0284c7;">Omzet Bulan Ini</span>
                <div class="ds-metric-val">
                    Rp {{ number_format($metrics['month_omzet'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="ds-metric-icon blue">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="ds-metric-subtext">
            <strong>{{ number_format($metrics['month_count'] ?? 0) }}</strong> transaksi bulan ini
        </div>
    </div>

    {{-- TOTAL AKUMULASI OMZET --}}
    <div class="ds-metric-card">
        <div class="ds-metric-header">
            <div>
                <span class="ds-metric-title" style="color: #d97706;">Total Akumulasi Omzet</span>
                <div class="ds-metric-val">
                    Rp {{ number_format($metrics['total_omzet'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="ds-metric-icon amber">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <div class="ds-metric-subtext">
            Akumulasi transaksi penjualan tercatat
        </div>
    </div>
</div>

{{-- 3. KARTU TABEL UTAMA DENGAN SEARCH & FILTER TERPADU --}}
<div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    {{-- CARD HEADER DENGAN FORM FILTER SESUAI STANDAR --}}
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('penjualan.langsung.index') }}" method="GET" id="searchFilterForm" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; flex: 1;">
            {{-- Input Pencarian --}}
            <div style="position: relative; flex: 1; min-width: 250px; max-width: 380px;">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Search : (No. Transaksi / Dokumen / Pelanggan / Sales)" 
                       class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%; box-sizing: border-box;">
            </div>

            {{-- Filter Tanggal Mulai --}}
            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" 
                   class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; max-width: 155px;" title="Dari Tanggal">

            {{-- Filter Tanggal Akhir --}}
            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" 
                   class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; max-width: 155px;" title="Sampai Tanggal">

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Cari</button>

            @if (!empty($search) || !empty($startDate) || !empty($endDate))
                <a href="{{ route('penjualan.langsung.index') }}" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.65rem;" title="Reset Filter">Reset</a>
            @endif
        </form>

        <span style="color: #64748b; font-size: 0.85rem;">
            Total Transaksi: <strong style="color: #0f172a;">{{ $sales->total() }}</strong> data
        </span>
    </div>

    {{-- TABEL TRANSAKSI PENJUALAN LANGSUNG --}}
    <div style="overflow-x: auto;">
        <table class="table-sales">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th style="min-width: 150px;">No. Transaksi</th>
                    <th style="min-width: 150px;">Dokumen Terkait</th>
                    <th style="min-width: 95px;">Tanggal</th>
                    <th style="min-width: 185px;">Pelanggan / Pembeli</th>
                    <th style="min-width: 160px;">Item Produk</th>
                    <th style="min-width: 125px; text-align: right;">Total Tagihan</th>
                    <th style="min-width: 110px; text-align: center;">Metode Bayar</th>
                    <th style="min-width: 85px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $index => $sale)
                    @php
                        $menuId = 'actionMenuDirect_' . $sale->so_id;
                        $firstPengeluaran = $sale->pengeluaran ? $sale->pengeluaran->first() : null;
                        $firstFaktur = $sale->faktur;
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                            {{ $sales->firstItem() + $index }}
                        </td>
                        <td>
                            <div>
                                <a href="{{ route('penjualan.langsung.show', $sale->so_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.875rem;">
                                    {{ $sale->so_no }}
                                </a>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.775rem; line-height: 1.5; color: #334155;">
                                @if ($firstFaktur)
                                    <div>
                                        <span style="color: #64748b; font-size: 0.7rem;">Faktur:</span>
                                        <a href="{{ route('penjualan.faktur.export-pdf', $firstFaktur->faktur_id) }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none;" title="Cetak Faktur Penjualan (PDF)">
                                            {{ $firstFaktur->faktur_no }}
                                        </a>
                                    </div>
                                @elseif ($sale->faktur_no)
                                    <div><span style="color: #64748b; font-size: 0.7rem;">Faktur:</span> <strong style="color: #0f172a;">{{ $sale->faktur_no }}</strong></div>
                                @endif

                                @if ($firstPengeluaran)
                                    <div>
                                        <span style="color: #64748b; font-size: 0.7rem;">SJ:</span>
                                        <a href="{{ route('penjualan.pengeluaran.export-pdf', $firstPengeluaran->pengeluaran_id) }}" target="_blank" style="color: #0284c7; text-decoration: none;" title="Cetak Surat Jalan (PDF)">
                                            {{ $firstPengeluaran->pengeluaran_no }}
                                        </a>
                                    </div>
                                @elseif ($sale->surat_jalan_no)
                                    <div><span style="color: #64748b; font-size: 0.7rem;">SJ:</span> {{ $sale->surat_jalan_no }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155; font-size: 0.825rem;">
                                {{ \Carbon\Carbon::parse($sale->so_tgl)->format('d/m/Y') }}
                            </div>
                            <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 1px;">
                                {{ \Carbon\Carbon::parse($sale->created_dt)->format('H:i') }} WIB
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.85rem; margin-bottom: 2px;">
                                {{ $sale->customer->customer_nm ?? 'Pelanggan Umum' }}
                            </div>
                            @if (!empty($sale->supir_nm) && $sale->supir_nm !== '-')
                                <div style="font-size: 0.75rem; color: #475569;">
                                    Pembeli: <strong>{{ $sale->supir_nm }}</strong>
                                    @if(!empty($sale->nopol_kendaraan) && $sale->nopol_kendaraan !== 'AMBIL SENDIRI') ({{ $sale->nopol_kendaraan }}) @endif
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
                                    <div style="font-weight: 600; color: #0f172a;">{{ $firstItem->barang->barang_nm ?? "Barang #{$firstItem->barang_id}" }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                        {{ number_format($firstItem->qty, 0) }} {{ $firstItem->barang->satuanDasar?->satuan_nm ?? 'Karton' }}
                                        @if ($itemCount > 1)
                                            <span style="background: #f1f5f9; color: #475569; font-size: 0.68rem; padding: 1px 5px; border-radius: 4px; margin-left: 0.25rem;">+{{ $itemCount - 1 }} item lainnya</span>
                                        @endif
                                    </div>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right; vertical-align: middle;">
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem; white-space: nowrap; font-feature-settings: 'tnum';">
                                Rp {{ number_format($sale->total_tagihan, 0, ',', '.') }}
                            </div>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if (strtoupper($sale->top_txt) === 'TRANSFER')
                                <span style="background: #eff6ff; color: #1d4ed8; font-weight: 600; font-size: 0.75rem; padding: 2px 7px; border-radius: 4px; border: 1px solid #bfdbfe;">
                                    Transfer
                                </span>
                            @elseif (strtoupper($sale->top_txt) === 'QRIS')
                                <span style="background: #f5f3ff; color: #7c3aed; font-weight: 600; font-size: 0.75rem; padding: 2px 7px; border-radius: 4px; border: 1px solid #ddd6fe;">
                                    QRIS
                                </span>
                            @else
                                <span style="background: #ecfdf5; color: #047857; font-weight: 600; font-size: 0.75rem; padding: 2px 7px; border-radius: 4px; border: 1px solid #a7f3d0;">
                                    Tunai
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
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
                                    <span>Cetak Nota Penjualan</span>
                                </a>
                                @if ($firstPengeluaran || $firstFaktur)
                                    <div class="action-dropdown-divider"></div>
                                @endif
                                @if ($firstPengeluaran)
                                    <a href="{{ route('penjualan.pengeluaran.export-pdf', $firstPengeluaran->pengeluaran_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                        <span>Cetak Surat Jalan</span>
                                    </a>
                                @endif
                                @if ($firstFaktur)
                                    <a href="{{ route('penjualan.faktur.export-pdf', $firstFaktur->faktur_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Cetak Faktur Penjualan</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #64748b; font-size: 0.875rem;">
                            Tidak ada riwayat transaksi penjualan langsung yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($sales->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; background: #ffffff;">
            {{ $sales->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/direct-sales-index.js') }}?v={{ filemtime(public_path('js/penjualan/direct-sales-index.js')) }}"></script>
@endpush
