@extends('layouts.app')

@section('title', 'Rekapitulasi Retur Penjualan (Customer Returns) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/retur/retur-index.css') }}">
@endpush

@section('content')

{{-- TOP HEADER COMMAND --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
            Retur Penjualan (Customer Returns)
        </h1>
        <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">
            Monitoring rekapitulasi penerimaan barang rusak/cacat dan penyesuaian piutang customer PT Mirasa.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('penjualan.so.index', ['status' => 'DELIVERED']) }}" class="btn btn-primary" style="font-size: 0.825rem; padding: 0.45rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Catat Retur Baru</span>
        </a>
        <a href="{{ route('penjualan.so.index') }}" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.45rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Daftar Seluruh PO Penjualan</span>
        </a>
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL RETUR --}}
<div class="retur-metrics-grid">
    {{-- KARTU 1: TOTAL TRANSAKSI RETUR --}}
    <div class="retur-metric-card">
        <div class="retur-metric-header">
            <span class="retur-metric-title">Total Berita Acara</span>
            <div class="retur-metric-icon slate">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="retur-metric-val">{{ number_format($kpi['total_trx'], 0) }}</div>
        <div class="retur-metric-subtext">Dokumen klaim retur pelanggan</div>
    </div>

    {{-- KARTU 2: TOTAL KUANTITAS DIRETUR --}}
    <div class="retur-metric-card">
        <div class="retur-metric-header">
            <span class="retur-metric-title">Total Barang Diretur</span>
            <div class="retur-metric-icon blue">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div class="retur-metric-val">{{ number_format($kpi['total_qty'], 0) }} <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">Karton</span></div>
        <div class="retur-metric-subtext">Masuk kembali ke stok gudang</div>
    </div>

    {{-- KARTU 3: TOTAL NILAI RUPIAH RETUR --}}
    <div class="retur-metric-card">
        <div class="retur-metric-header">
            <span class="retur-metric-title">Total Nilai Retur</span>
            <div class="retur-metric-icon emerald">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="retur-metric-val" style="color: #059669; font-size: 1.3rem;">Rp {{ number_format($kpi['total_nilai'], 0, ',', '.') }}</div>
        <div class="retur-metric-subtext">Akumulasi komersial terpotong</div>
    </div>

    {{-- KARTU 4: RETUR BULAN INI --}}
    <div class="retur-metric-card">
        <div class="retur-metric-header">
            <span class="retur-metric-title">Retur Bulan Ini</span>
            <div class="retur-metric-icon blue">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="retur-metric-val">{{ number_format($kpi['qty_bulan_ini'], 0) }} <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">Karton</span></div>
        <div class="retur-metric-subtext">Periode: {{ date('F Y') }}</div>
    </div>
</div>

{{-- FILTER PENCARIAN & PARAMETER --}}
<div class="retur-filter-card">
    <form action="{{ route('penjualan.retur.index') }}" method="GET" class="retur-filter-grid">
        {{-- Cari Teks --}}
        <div>
            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">Pencarian Umum</label>
            <div style="position: relative;">
                <input type="text" name="search" value="{{ $search }}" placeholder="No. Retur / No. SO / Nama Customer / Catatan..." class="form-control" style="width: 100%; padding-left: 2rem; font-size: 0.825rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        {{-- Filter Customer --}}
        <div>
            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">Mitra Customer</label>
            <select name="customer_id" class="form-control" style="width: 100%; font-size: 0.825rem; background: #ffffff;">
                <option value="">-- Semua Customer --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->customer_id }}" {{ ($filters['customer_id'] ?? '') == $c->customer_id ? 'selected' : '' }}>
                        {{ $c->customer_nm }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter Tindakan --}}
        <div>
            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">Tindakan Komersial</label>
            <select name="tindakan_cd" class="form-control" style="width: 100%; font-size: 0.825rem; background: #ffffff;">
                <option value="">-- Semua Tindakan --</option>
                <option value="POTONG_TAGIHAN" {{ ($filters['tindakan_cd'] ?? '') === 'POTONG_TAGIHAN' ? 'selected' : '' }}>Potong Tagihan (Credit Note)</option>
                <option value="TUKAR_GULING" {{ ($filters['tindakan_cd'] ?? '') === 'TUKAR_GULING' ? 'selected' : '' }}>Tukar Guling Fisik</option>
            </select>
        </div>

        {{-- Filter Status --}}
        <div>
            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">Status</label>
            <select name="status_cd" class="form-control" style="width: 100%; font-size: 0.825rem; background: #ffffff;">
                <option value="">-- Semua Status --</option>
                <option value="CONFIRMED" {{ ($filters['status_cd'] ?? '') === 'CONFIRMED' ? 'selected' : '' }}>Terkonfirmasi</option>
                <option value="VOID" {{ ($filters['status_cd'] ?? '') === 'VOID' ? 'selected' : '' }}>Dibatalkan (Void)</option>
            </select>
        </div>

        {{-- Action Buttons --}}
        <div style="display: flex; gap: 0.35rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.45rem 0.85rem; font-size: 0.825rem;">
                Filter
            </button>
            <a href="{{ route('penjualan.retur.index') }}" class="btn btn-secondary" style="padding: 0.45rem 0.65rem; font-size: 0.825rem;" title="Reset Filter">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- TABEL DATA RETUR PENJUALAN --}}
<div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div style="overflow-x: auto;">
        <table class="table-retur">
            <thead>
                <tr>
                    <th style="width: 35px; text-align: center;">No</th>
                    <th style="min-width: 140px;">No. Retur &amp; Tgl</th>
                    <th style="min-width: 140px;">Dokumen SO / Faktur</th>
                    <th style="min-width: 180px;">Mitra Customer</th>
                    <th style="min-width: 150px;">Gudang &amp; Batch Masuk</th>
                    <th style="width: 110px;">Tindakan</th>
                    <th style="min-width: 200px;">Rincian Barang Diretur</th>
                    <th style="width: 85px; text-align: right;">Total Qty</th>
                    <th style="width: 125px; text-align: right;">Total Nilai</th>
                    <th style="width: 100px; text-align: center;">Status</th>
                    <th style="width: 75px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $idx => $item)
                    @php
                        $menuId = 'smartActionRetur_' . $item->retur_id;
                    @endphp
                    <tr style="background: {{ $item->status_cd === 'VOID' ? '#fff1f2' : '#ffffff' }};">
                        <td style="text-align: center; color: #64748b;">
                            {{ $returns->firstItem() + $idx }}
                        </td>
                        <td>
                            <strong style="font-family: monospace; color: #0284c7; font-size: 0.875rem;">{{ $item->retur_no }}</strong>
                            <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                {{ $item->retur_tgl ? $item->retur_tgl->format('d/m/Y') : '-' }}
                            </div>
                            @if($item->alasan_txt)
                                <div style="font-size: 0.725rem; color: #475569; margin-top: 3px; font-style: italic;">
                                    "{{ Str::limit($item->alasan_txt, 40) }}"
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('penjualan.so.show', $item->so_id) }}" style="font-family: monospace; font-weight: 700; color: #0284c7; text-decoration: none;">
                                {{ $item->order?->so_no ?? '-' }}
                            </a>
                            <div style="font-size: 0.725rem; font-family: monospace; color: #64748b; margin-top: 2px;">
                                {{ $item->order?->faktur_no ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.85rem;">{{ $item->customer?->customer_nm ?? '-' }}</strong>
                            <span style="display: block; font-size: 0.725rem; color: #64748b;">{{ $item->customer?->customer_cd ?? '-' }}</span>
                        </td>
                        <td>
                            <strong style="color: #334155;">{{ $item->gudang?->gudang_nm ?? '-' }}</strong>
                            <div style="font-size: 0.725rem; font-family: monospace; color: #64748b; margin-top: 2px;">
                                Batch: RET-{{ $item->order?->so_no }}
                            </div>
                        </td>
                        <td>
                            @if($item->tindakan_cd === 'POTONG_TAGIHAN')
                                <span class="badge-tindakan-potong">Potong Tagihan</span>
                            @else
                                <span class="badge-tindakan-tukar">Tukar Guling</span>
                            @endif
                        </td>
                        <td>
                            @foreach($item->details as $dtl)
                                <div style="margin-bottom: 2px; line-height: 1.3;">
                                    &bull; <strong>{{ $dtl->barang?->barang_nm ?? '-' }}</strong>: 
                                    {{ number_format((float) $dtl->qty_retur, 0) }} unit
                                    <span style="font-size: 0.725rem; color: #64748b;">({{ $dtl->kondisi_label }})</span>
                                </div>
                            @endforeach
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: 700; font-size: 0.85rem;">
                            {{ number_format((float) $item->total_qty, 0) }}
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                            Rp {{ number_format((float) $item->total_nilai, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            {!! $item->status_badge !!}
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <a href="{{ route('penjualan.retur.show', $item->retur_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Detail di SO</span>
                                </a>

                                <a href="{{ route('penjualan.retur.export-pdf', $item->retur_id) }}" target="_blank" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak Berita Acara (PDF)</span>
                                </a>

                                @if($item->status_cd === 'CONFIRMED')
                                    <button type="button" class="action-dropdown-item danger-item" onclick="openVoidModal({{ $item->retur_id }}, '{{ $item->retur_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batalkan (Void)</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4 4m-4-4l4-4m12 11a4 4 0 004-4v-1a4 4 0 00-4-4h-4"/></svg>
                                <span style="font-weight: 600; color: #64748b;">Belum Ada Dokumen Retur Penjualan</span>
                                <span style="font-size: 0.775rem;">Semua barang terkirim ke customer aman dan belum ada klaim pengembalian.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($returns->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; background: #ffffff;">
            {{ $returns->links() }}
        </div>
    @endif
</div>

{{-- MODAL VOID RETUR --}}
@include('penjualan.retur.partials.modal-void-retur')

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/retur/retur-index.js') }}"></script>
@endpush
