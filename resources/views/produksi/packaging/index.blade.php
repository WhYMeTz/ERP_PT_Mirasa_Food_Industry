@extends('layouts.app')

@section('title', 'Modul Pengemasan & HPP Barang Jadi (Packaging) - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-index.css') }}">
@endpush

@section('content')
<div class="packaging-index-container">
    {{-- Header Halaman (Identik dengan Hasil Barang Produksi & Buku Rekapitulasi) --}}
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                Riwayat Pengemasan Barang Jadi (FG)
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
                Pencatatan konversi keripik curah (WIP) ke produk kemasan (FG), histori pengerjaan, dan mutasi stok gudang barang jadi.
            </p>
        </div>

            <a href="{{ route('produksi.packaging.catatan-operator') }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #6366f1; color: #4f46e5; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s ease-in-out;"
               onmouseover="this.style.background='#eef2ff'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Buka daftar tiket catatan laporan pengemasan dari HP operator lapangan">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Catatan Operator</span>
                @if(!empty($unprocessedCount) && $unprocessedCount > 0)
                    <span style="background: #ef4444; color: #ffffff; font-size: 0.675rem; font-weight: 800; padding: 1px 6px; border-radius: 999px;">
                        {{ $unprocessedCount }} baru
                    </span>
                @endif
            </a>

            <a href="{{ route('produksi.packaging.rekap') }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;"
               onmouseover="this.style.background='#f0f9ff'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Buka buku besar rekapitulasi HPP pengemasan harian &amp; bulanan">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Buku Rekapitulasi HPP Kemas</span>
            </a>

            @if(Auth::user()->canCreateProduksi())
                <a href="{{ route('produksi.packaging.create') }}" 
                   class="btn btn-primary" 
                   style="background: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Catat Pengemasan Baru</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Notifikasi Sukses & Error --}}
    @if(session('success'))
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- 4 KARTU METRIK EKSEKUTIF (SERAGAM 100% DENGAN REKAP PRODUKSI & MODUL LAIN) --}}
    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL DOKUMEN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Total Dokumen
                    </span>
                    <div class="executive-stat-value">
                        {{ number_format($metrics['total_dokumen'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Siklus pengemasan tercatat
            </div>
        </div>

        {{-- METRIK 2: HASIL KEMAS FINISH GOOD --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Hasil Kemas (FG)
                    </span>
                    <div class="executive-stat-value">
                        {{ number_format($metrics['total_karton'], 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #ecfdf5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Siap jual di gudang barang jadi
            </div>
        </div>

        {{-- METRIK 3: TOTAL BIAYA KEMAS --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #64748b;">
                        Total Biaya Kemas
                    </span>
                    <div class="executive-stat-value">
                        Rp {{ number_format($metrics['total_biaya_produksi'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #fef3c7; color: #d97706;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                WIP + kemasan + bumbu + upah
            </div>
        </div>

        {{-- METRIK 4: RATA-RATA HPP/KARTON --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #059669;">
                        Rata-Rata HPP/Karton
                    </span>
                    <div class="executive-stat-value" style="color: #059669;">
                        Rp {{ number_format($metrics['rata_hpp_karton'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #d1fae5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Beban modal pokok jadi
            </div>
        </div>
    </div>

    {{-- Filter Toolbar (Sesuai Standar Produksi Index) --}}
    <div class="card" style="margin-bottom: 1.25rem; padding: 1.15rem; background: #ffffff;">
        <form method="GET" action="{{ route('produksi.packaging.index') }}">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; align-items: end;">
                <div>
                    <label class="filter-label" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Pencarian Dokumen / Batch</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                           class="form-control" style="font-size: 0.825rem; height: 36px;" placeholder="No PCK, Lot, atau nama barang...">
                </div>

                <div>
                    <label class="filter-label" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Tanggal Pengemasan</label>
                    <input type="date" name="pack_tgl" value="{{ $filters['pack_tgl'] ?? '' }}" 
                           class="form-control" style="font-size: 0.825rem; height: 36px;">
                </div>

                <div>
                    <label class="filter-label" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Target Produk Jadi (FG)</label>
                    <select name="barang_jadi_id" class="form-control" style="font-size: 0.825rem; height: 36px;">
                        <option value="">-- Semua Produk Jadi --</option>
                        @foreach($barangJadiList as $b)
                            <option value="{{ $b->barang_id }}" {{ ($filters['barang_jadi_id'] ?? '') == $b->barang_id ? 'selected' : '' }}>
                                {{ $b->barang_nm }} ({{ $b->barang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Status Dokumen</label>
                    <select name="status_cd" class="form-control" style="font-size: 0.825rem; height: 36px;">
                        <option value="">-- Semua Status --</option>
                        <option value="POSTED" {{ ($filters['status_cd'] ?? '') === 'POSTED' ? 'selected' : '' }}>POSTED</option>
                        <option value="VOID" {{ ($filters['status_cd'] ?? '') === 'VOID' ? 'selected' : '' }}>VOID (Batal)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.4rem;">
                    <button type="submit" class="btn btn-primary" style="height: 36px; padding: 0 1rem; font-size: 0.825rem; flex: 1;">
                        Filter
                    </button>
                    <a href="{{ route('produksi.packaging.index') }}" class="btn btn-secondary" style="height: 36px; padding: 0 0.85rem; font-size: 0.825rem; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;" title="Reset Filter">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABEL DATA HASIL PENGEMASAN (IDENTIK DENGAN TABEL HASIL PRODUKSI) --}}
    <div class="card" style="margin-bottom: 1.5rem; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; margin: 0; font-size: 0.85rem;">
                <thead style="background: #f8fafc; color: #334155; font-weight: 700; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 0.75rem 1rem; width: 140px; text-align: left;">NO DOKUMEN</th>
                        <th style="padding: 0.75rem 1rem; width: 105px; text-align: left;">TANGGAL</th>
                        <th style="padding: 0.75rem 1rem; text-align: left;">TARGET PRODUK JADI (FG)</th>
                        <th style="padding: 0.75rem 1rem; text-align: left;">GUDANG FG</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">HASIL KEMAS</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">TOTAL BIAYA (RP)</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">HPP / KARTON</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; width: 95px;">STATUS</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; width: 85px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataList as $row)
                        @php
                            $menuId = 'actionMenu_' . $row->pack_id;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.75rem 1rem;">
                                <a href="{{ route('produksi.packaging.show', $row->pack_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;">
                                    {{ $row->pack_no }}
                                </a>
                                @if($row->batch_no)
                                    <div style="font-size: 0.6875rem; color: #64748b; font-family: monospace;">
                                        {{ $row->batch_no }}
                                    </div>
                                @endif
                                @if($row->produksi)
                                    <div style="margin-top: 0.2rem;">
                                        <a href="{{ route('produksi.show', $row->produksi->produksi_id) }}" style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.65rem; font-weight: 700; color: #15803d; background: #dcfce7; padding: 0.1rem 0.35rem; border-radius: 3px; text-decoration: none; border: 1px solid #86efac;" title="Lihat Dokumen HPP Produksi WIP">
                                            <span>WIP: {{ $row->produksi->produksi_no }}</span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; color: #334155;">
                                {{ date('d/m/Y', strtotime($row->pack_tgl)) }}
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <div style="font-weight: 700; color: #0f172a;">
                                    {{ $row->barangJadi?->barang_nm ?? '-' }}
                                </div>
                                <div style="font-size: 0.7rem; color: #64748b;">
                                    Kode: {{ $row->barangJadi?->barang_cd ?? '-' }}
                                    @if($row->isi_per_karton_pcs > 1)
                                        &bull; {{ number_format($row->isi_per_karton_pcs, 0) }} Pcs/Ktn
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 0.75rem 1rem; color: #334155;">
                                {{ $row->gudang?->gudang_nm ?? '-' }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                <div style="font-weight: 800; color: #059669;">
                                    {{ number_format($row->hasil_karton_qty, 0, ',', '.') }} <span style="font-size: 0.725rem; font-weight: 600; color: #64748b;">Ktn</span>
                                </div>
                                <div style="font-size: 0.7rem; color: #64748b;">
                                    ({{ number_format($row->hasil_pcs_qty, 0, ',', '.') }} Pcs)
                                </div>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                                Rp {{ number_format($row->total_biaya_produksi, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                <div style="font-weight: 900; color: #059669; font-size: 0.9rem;">
                                    Rp {{ number_format($row->hpp_per_karton, 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.6875rem; color: #64748b;">
                                    Rp {{ number_format($row->hpp_per_pcs, 0, ',', '.') }}/pcs
                                </div>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                @if($row->status_cd === 'POSTED')
                                    <span style="font-size: 0.725rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.2rem 0.55rem; border-radius: 9999px; font-weight: 700;">
                                        POSTED
                                    </span>
                                @else
                                    <span style="font-size: 0.725rem; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.2rem 0.55rem; border-radius: 9999px; font-weight: 700;">
                                        VOID
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('produksi.packaging.show', $row->pack_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Berita Acara</span>
                                    </a>

                                    @if($row->status_cd === 'POSTED')
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" class="action-dropdown-item danger-item" 
                                                onclick="openVoidPackagingModal({{ $row->pack_id }}, '{{ $row->pack_no }}', '{{ addslashes($row->barangJadi?->barang_nm ?? '') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Batalkan (Void)</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <span style="font-weight: 600; font-size: 0.95rem; color: #64748b;">Belum ada dokumen pengemasan yang tercatat</span>
                                    <span style="font-size: 0.8rem; color: #94a3b8;">Silakan klik tombol <strong>+ Catat Pengemasan Baru</strong> untuk mencatat proses kemas keripik curah ke barang jadi.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($dataList->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                {{ $dataList->appends($filters)->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL VOID DANGER --}}
@include('produksi.packaging.partials.modal-void')
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/packaging/packaging-index.js') }}"></script>
@endpush
