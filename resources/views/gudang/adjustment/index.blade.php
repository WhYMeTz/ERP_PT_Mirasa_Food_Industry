@extends('layouts.app')

@section('title', 'Adjustment')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-index.css') }}">
@endpush

@section('content')
<div class="adj-container">

    {{-- 1. Header Halaman --}}
    <div class="adj-header-wrap">
        <div class="adj-title-area">
            <h1>
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Adjustment
            </h1>
            <p class="adj-subtitle">
                Pencatatan koreksi selisih stok gudang, penyusutan drum minyak, susut alami bahan baku, dan rekonsiliasi opname.
            </p>
        </div>
        <div class="adj-header-actions">
            <a href="{{ route('gudang.stok.rekap') }}" class="btn-adj-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Rekap Stok Komoditas
            </a>
            <a href="{{ route('gudang.adjustment.create') }}" class="btn-adj-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Buat Adjustment Baru
            </a>
        </div>
    </div>

    {{-- Alert Flash Message --}}
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- 2. KPI Metrics Grid --}}
    <div class="adj-metrics-grid">
        <div class="adj-metric-card blue">
            <span class="adj-metric-label">Total Dokumen Adjustment</span>
            <span class="adj-metric-val">{{ number_format($metrics['total_dokumen']) }}</span>
            <span class="adj-metric-sub">{{ number_format($metrics['total_item']) }} baris item disesuaikan</span>
        </div>
        <div class="adj-metric-card red">
            <span class="adj-metric-label">Total Nilai Defisit (Penyusutan)</span>
            <span class="adj-metric-val" style="color: #b91c1c;">Rp {{ number_format($metrics['total_defisit_nilai'], 0, ',', '.') }}</span>
            <span class="adj-metric-sub">Penyusutan drum, susut alami &amp; kerusakan</span>
        </div>
        <div class="adj-metric-card emerald">
            <span class="adj-metric-label">Total Nilai Surplus (Kelebihan)</span>
            <span class="adj-metric-val" style="color: #15803d;">Rp {{ number_format($metrics['total_surplus_nilai'], 0, ',', '.') }}</span>
            <span class="adj-metric-sub">Kelebihan fisik / penemuan stok</span>
        </div>
        <div class="adj-metric-card indigo">
            <span class="adj-metric-label">Net Dampak Persediaan (Rp)</span>
            <span class="adj-metric-val" style="color: {{ $metrics['net_selisih_nilai'] < 0 ? '#b91c1c' : ($metrics['net_selisih_nilai'] > 0 ? '#15803d' : '#0f172a') }};">
                {{ $metrics['net_selisih_nilai'] < 0 ? '-' : ($metrics['net_selisih_nilai'] > 0 ? '+' : '') }}Rp {{ number_format(abs($metrics['net_selisih_nilai']), 0, ',', '.') }}
            </span>
            <span class="adj-metric-sub">Dampak buku nilai aset bersih</span>
        </div>
    </div>

    {{-- 3. Filter Box Sesuai Blueprint --}}
    <div class="adj-filter-card">
        <form method="GET" action="{{ route('gudang.adjustment.index') }}">
            <div class="adj-filter-grid">
                {{-- Search Teks (Nama Barang / Kode Barang) --}}
                <div class="adj-form-group" style="grid-column: span 2;">
                    <label for="search">Pencarian (Nama / Kode Barang / No. Dokumen)</label>
                    <input type="text" name="search" id="search" class="adj-form-control" 
                           placeholder="Ketik nama atau kode barang..." 
                           value="{{ $filters['search'] ?? '' }}">
                </div>

                {{-- Filter Gudang --}}
                <div class="adj-form-group">
                    <label for="gudang_id">Gudang</label>
                    <select name="gudang_id" id="gudang_id" class="adj-form-control">
                        <option value="">Semua Gudang</option>
                        @foreach($gudangList as $g)
                            <option value="{{ $g->gudang_id }}" {{ ($filters['gudang_id'] ?? '') == $g->gudang_id ? 'selected' : '' }}>
                                {{ $g->gudang_nm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tanggal Mulai --}}
                <div class="adj-form-group">
                    <label for="tgl_mulai">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="adj-form-control" 
                           value="{{ $filters['tgl_mulai'] ?? '' }}">
                </div>

                {{-- Filter Tanggal Selesai --}}
                <div class="adj-form-group">
                    <label for="tgl_selesai">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="adj-form-control" 
                           value="{{ $filters['tgl_selesai'] ?? '' }}">
                </div>

                {{-- Filter Tipe Selisih --}}
                <div class="adj-form-group">
                    <label for="tipe_selisih">Status Selisih</label>
                    <select name="tipe_selisih" id="tipe_selisih" class="adj-form-control">
                        <option value="">Semua Status</option>
                        <option value="DEFISIT" {{ ($filters['tipe_selisih'] ?? '') === 'DEFISIT' ? 'selected' : '' }}>Defisit / Susut (-)</option>
                        <option value="SURPLUS" {{ ($filters['tipe_selisih'] ?? '') === 'SURPLUS' ? 'selected' : '' }}>Surplus / Lebih (+)</option>
                        <option value="MATCH" {{ ($filters['tipe_selisih'] ?? '') === 'MATCH' ? 'selected' : '' }}>Cocok / Imbang (0)</option>
                    </select>
                </div>

                {{-- Tombol Filter --}}
                <div class="adj-form-group">
                    <label>&nbsp;</label>
                    <div class="adj-filter-buttons">
                        <button type="submit" class="btn-adj-primary" style="flex: 1; justify-content: center;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Terapkan
                        </button>
                        <a href="{{ route('gudang.adjustment.index') }}" class="btn-adj-secondary" title="Reset Filter">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- 4. Tabel Adjustment (Desain Kompak Tanpa Scroll Horizontal) --}}
    <div class="adj-table-card">
        <div class="adj-table-responsive">
            <table class="adj-table-compact">
                <thead>
                    <tr>
                        <th style="width: 15%;">Tanggal &amp; Dokumen</th>
                        <th style="width: 22%;">Barang &amp; Batch</th>
                        <th style="width: 11%; text-align: right;">@Harga Satuan</th>
                        <th style="width: 22%;">Komparasi Stok (Sistem vs Fisik)</th>
                        <th style="width: 14%; text-align: right;">Koreksi Selisih</th>
                        <th style="width: 11%;">Alasan / Catatan</th>
                        <th style="width: 5%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($details as $row)
                        @php
                            $hdr = $row->header;
                            $barang = $row->barang;
                            $selisihQty = (float) $row->selisih_qty;
                            $selisihNilai = (float) $row->total_selisih_nilai;
                            $satuan = $barang->satuanDasar->satuan_cd ?? 'KG';
                        @endphp
                        <tr>
                            {{-- 1. Tanggal & Dokumen --}}
                            <td>
                                <div class="cell-doc-info">
                                    <span style="font-weight: 600; color: #1e293b; font-size: 0.8rem;">
                                        {{ $hdr->adj_tgl ? $hdr->adj_tgl->format('d/m/Y') : '-' }}
                                    </span>
                                    <a href="{{ route('gudang.adjustment.show', $hdr->adj_id) }}" class="cell-doc-no" title="Lihat Berita Acara">
                                        {{ $hdr->adj_no }}
                                    </a>
                                    <span class="cell-badge-gudang">
                                        {{ $hdr->gudang->gudang_nm ?? '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- 2. Barang & Batch --}}
                            <td>
                                <div class="cell-barang-info">
                                    <span class="cell-barang-nm">
                                        {{ $barang->barang_nm ?? '-' }}
                                    </span>
                                    <span class="cell-barang-meta">
                                        {{ $barang->barang_cd ?? '-' }} 
                                        @if($row->batch_no)
                                            • Batch: <strong>{{ $row->batch_no }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </td>

                            {{-- 3. @Harga Satuan --}}
                            <td style="text-align: right;">
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <span style="font-family: 'Consolas', monospace; font-weight: 600; color: #334155;">
                                        Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}
                                    </span>
                                    <span style="font-size: 0.675rem; color: #94a3b8;">
                                        / {{ $satuan }}
                                    </span>
                                </div>
                            </td>

                            {{-- 4. Komparasi Stok (Sistem vs Fisik) --}}
                            <td>
                                <div class="cell-stock-compare">
                                    {{-- Data Sistem (Sebelum Opname) --}}
                                    <div class="stock-line">
                                        <span class="stock-line-label">Sistem (Awal):</span>
                                        <span class="stock-line-val" style="color: #475569;">
                                            {{ number_format((float) $row->stok_sistem_qty, 2, ',', '.') }} {{ $satuan }}
                                            <span style="font-size: 0.7rem; color: #94a3b8;">(Rp {{ number_format((float) $row->total_sistem_nilai, 0, ',', '.') }})</span>
                                        </span>
                                    </div>
                                    {{-- Data Fisik Gudang (Hasil Riil) --}}
                                    <div class="stock-line fisik">
                                        <span class="stock-line-label">Fisik (Gudang):</span>
                                        <span class="stock-line-val">
                                            {{ number_format((float) $row->stok_fisik_qty, 2, ',', '.') }} {{ $satuan }}
                                            <span style="font-size: 0.7rem; opacity: 0.85;">(Rp {{ number_format((float) $row->total_fisik_nilai, 0, ',', '.') }})</span>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- 5. Koreksi Selisih (Qty & Rp) --}}
                            <td style="text-align: right;">
                                <div class="cell-selisih-wrap">
                                    {{-- Kuantitas Selisih --}}
                                    @if($selisihQty < 0)
                                        <span class="badge-adj-defisit">{{ number_format($selisihQty, 2, ',', '.') }} {{ $satuan }}</span>
                                    @elseif($selisihQty > 0)
                                        <span class="badge-adj-surplus">+{{ number_format($selisihQty, 2, ',', '.') }} {{ $satuan }}</span>
                                    @else
                                        <span class="badge-adj-match">0,00 {{ $satuan }}</span>
                                    @endif

                                    {{-- Total Selisih Nilai (Rp) --}}
                                    <span class="val-selisih-rp" style="color: {{ $selisihNilai < 0 ? '#b91c1c' : ($selisihNilai > 0 ? '#15803d' : '#64748b') }};">
                                        {{ $selisihNilai < 0 ? '-' : ($selisihNilai > 0 ? '+' : '') }}Rp {{ number_format(abs($selisihNilai), 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>

                            {{-- 6. Kategori & Alasan --}}
                            <td>
                                <div>
                                    <span class="cell-kategori-tag">
                                        {{ str_replace('_', ' ', $hdr->kategori_adj) }}
                                    </span>
                                    <div class="cell-alasan-text" title="{{ $row->alasan_txt ?: ($hdr->catatan_txt ?: '-') }}">
                                        {{ $row->alasan_txt ?: ($hdr->catatan_txt ?: '-') }}
                                    </div>
                                </div>
                            </td>

                            {{-- 7. Aksi (Smart Action Trigger) --}}
                            <td style="text-align: center;">
                                <div style="display: inline-block; position: relative;">
                                    <button type="button" class="btn-action-trigger" 
                                            onclick="toggleSmartActionDropdown(this, event, 'menuAdj_{{ $row->adjdtl_id }}')">
                                        <span>Aksi</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <div id="menuAdj_{{ $row->adjdtl_id }}" class="action-dropdown-menu">
                                        <a href="{{ route('gudang.adjustment.show', $hdr->adj_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Lihat Dokumen
                                        </a>

                                        @if($hdr->status_cd !== 'VOID')
                                            <button type="button" class="action-dropdown-item danger" 
                                                    onclick="openVoidModal({{ $hdr->adj_id }}, '{{ $hdr->adj_no }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                Batalkan (Void)
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="adj-empty-state">
                                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1; margin-bottom: 0.5rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p style="font-weight: 600; margin: 0; color: #475569;">Belum Ada Data Adjustment</p>
                                <p style="font-size: 0.8rem; margin: 0.25rem 0 0 0; color: #94a3b8;">Klik tombol "+ Buat Adjustment Baru" untuk mencatat opname stok atau penyusutan barang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($details->hasPages())
            <div class="adj-pagination-wrap">
                <span style="font-size: 0.8rem; color: #64748b;">
                    Menampilkan {{ $details->firstItem() }} - {{ $details->lastItem() }} dari {{ $details->total() }} baris
                </span>
                <div>
                    {{ $details->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- Include Modal Konfirmasi Void (Partials) --}}
@include('gudang.adjustment.partials.modal-void')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/adjustment/adjustment-index.js') }}"></script>
@endpush
