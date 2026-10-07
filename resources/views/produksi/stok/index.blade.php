@extends('layouts.app')

@section('title', 'Stok Hasil Produksi (WIP & FG) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/stok/batch-detail-modal.css') }}">
@endpush

@section('content')
{{-- Header Halaman & Tombol Aksi Cepat --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <h1 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.5rem; margin: 0;">
            <span>Stok Hasil Produksi (WIP &amp; Barang Jadi)</span>
            <span class="badge" style="background: #ea580c18; color: #ea580c; border: 1px solid #ea580c33; font-size: 0.75rem; font-weight: 700;">Lantai Produksi &amp; Gudang Jadi</span>
        </h1>
        <p style="color: #64748b; font-size: 0.825rem; margin: 0.2rem 0 0 0;">
            Pantau saldo fisik on-hand olahan setengah jadi (WIP) di lantai produksi dan barang jadi kemasan (FG) siap jual.
        </p>
    </div>
    
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        {{-- View Switcher --}}
        <div style="display: inline-flex; background: #e2e8f0; padding: 0.2rem; border-radius: 8px; gap: 0.2rem;">
            <a href="{{ route('produksi.stok.index', array_merge(request()->except(['view', 'page']), ['view' => 'split'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $viewType === 'split' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'split' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'split' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 700; padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                ⚡ Split-Screen (Master-Detail)
            </a>
            <a href="{{ route('produksi.stok.index', array_merge(request()->except(['view', 'page']), ['view' => 'summary'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $viewType === 'summary' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'summary' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'summary' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 700; padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                📦 Tabel Ringkas (2-Level)
            </a>
            <a href="{{ route('produksi.stok.index', array_merge(request()->except(['view', 'page']), ['view' => 'batch'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $viewType === 'batch' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'batch' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'batch' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 700; padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                📋 Sheet per-Batch
            </a>
        </div>

        <a href="{{ route('produksi.create') }}" class="btn btn-primary btn-sm" style="background: #059669; border-color: #059669; padding: 0.4rem 0.85rem; font-weight: 600;">
            + Input Hasil Produksi
        </a>
        <a href="{{ route('produksi.index') }}" class="btn btn-secondary btn-sm" style="padding: 0.4rem 0.85rem; font-weight: 600;">
            Riwayat Output
        </a>
        <a href="{{ route('produksi.stok.ledger') }}" class="btn btn-secondary btn-sm" style="padding: 0.4rem 0.85rem; font-weight: 600;">
            📜 Kartu Stok WIP &amp; FG
        </a>
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL PRODUKSI --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
    {{-- 1. VALUASI HASIL PRODUKSI --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    Valuasi Stok Produksi (WIP &amp; FG)
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem; font-family: monospace;">
                    Rp {{ number_format($kpiMetrics['total_nilai'], 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef2f2; display: flex; align-items: center; justify-content: center; color: #ea580c; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total estimasi aset barang olahan &amp; jadi
        </div>
    </div>

    {{-- 2. TOTAL SKU OUTPUT --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Total Item Output (SKU)
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                    {{ $kpiMetrics['total_sku'] }} <span style="font-size: 0.8rem; font-weight: 600; color: #0284c7;">({{ $kpiMetrics['sku_tersedia'] }} Ada Stok)</span>
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            WIP setengah jadi &amp; kemasan Finished Goods
        </div>
    </div>

    {{-- 3. STOK MENIPIS / DI BAWAH BUFFER --}}
    <div style="background: {{ $kpiMetrics['sku_menipis'] > 0 ? '#fffbeb' : '#ffffff' }}; border-radius: 8px; border: 1px solid {{ $kpiMetrics['sku_menipis'] > 0 ? '#fde68a' : '#e2e8f0' }}; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#64748b' }};">
                    Stok Menipis / Kritis
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#0f172a' }}; margin-top: 0.25rem;">
                    {{ $kpiMetrics['sku_menipis'] }} <span style="font-size: 0.8rem; font-weight: 600; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#64748b' }};">Produk</span>
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ $kpiMetrics['sku_menipis'] > 0 ? '#fef3c7' : '#f1f5f9' }}; display: flex; align-items: center; justify-content: center; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#d97706' : '#64748b' }}; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#64748b' }}; margin-top: 0.4rem;">
            {{ $kpiMetrics['sku_menipis'] > 0 ? 'Perlu dijadwalkan masak / goreng tambahan' : 'Semua produk di atas batas buffer' }}
        </div>
    </div>

    {{-- 4. BATCH AKTIF --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Batch Aktif Siap Kirim
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                    {{ $kpiMetrics['batch_aktif'] }} <span style="font-size: 0.8rem; font-weight: 600; color: #059669;">Batch</span>
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Lot nomor batch hasil masak &amp; packing
        </div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- KONSEP 1: SPLIT-SCREEN MASTER-DETAIL (ANTI-SCROLL)      --}}
{{-- ======================================================== --}}
@if ($viewType === 'split')
<div style="display: flex; gap: 0.85rem; height: calc(100vh - 250px); min-height: 520px; box-sizing: border-box;">
    
    {{-- PANEL KIRI: DAFTAR BARANG (MASTER LIST - LEBAR 38%) --}}
    <div style="flex: 0 0 38%; display: flex; flex-direction: column; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        {{-- Search & Filter Bar Kiri --}}
        <div style="padding: 0.75rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <div style="position: relative; margin-bottom: 0.5rem;">
                <input type="text" id="masterSearchInput" placeholder="Ketik nama atau kode barang jadi..." 
                       style="width: 100%; padding: 0.45rem 2rem 0.45rem 0.75rem; font-size: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;"
                       oninput="filterMasterList()">
                <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem; pointer-events: none;">🔍</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                {{-- Quick Status Filter Chips --}}
                <div style="display: flex; gap: 0.25rem;">
                    <button type="button" class="btn-chip active" data-filter="all" onclick="setChipFilter('all', this)" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #0f172a; color: #ffffff; cursor: pointer; font-weight: 600;">
                        Semua
                    </button>
                    <button type="button" class="btn-chip" data-filter="ada" onclick="setChipFilter('ada', this)" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #ffffff; color: #059669; cursor: pointer; font-weight: 600;">
                        🟢 Ada Stok
                    </button>
                    <button type="button" class="btn-chip" data-filter="menipis" onclick="setChipFilter('menipis', this)" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #ffffff; color: #d97706; cursor: pointer; font-weight: 600;">
                        ⚠️ Menipis
                    </button>
                    <button type="button" class="btn-chip" data-filter="habis" onclick="setChipFilter('habis', this)" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #ffffff; color: #dc2626; cursor: pointer; font-weight: 600;">
                        🔴 Habis
                    </button>
                </div>

                {{-- Gudang Selector --}}
                <form action="{{ route('produksi.stok.index') }}" method="GET" style="margin: 0;">
                    <input type="hidden" name="view" value="split">
                    <select name="gudang_id" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px; max-width: 220px;" onchange="this.form.submit()">
                        <option value="">Semua Lokasi Pabrik</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ $gudangId == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->display_name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- Scrollable List of Items --}}
        <div id="masterListContainer" style="flex: 1; overflow-y: auto; padding: 0.4rem; display: flex; flex-direction: column; gap: 0.35rem;">
            @php $firstItemJson = null; @endphp
            @forelse ($summaryList as $index => $item)
                @php
                    $sisaQty = (float) $item->total_sisa_qty;
                    $minStok = (float) ($item->batas_minimum_qty ?? 0);
                    $isHabis = $sisaQty <= 0;
                    $isMenipis = !$isHabis && $minStok > 0 && $sisaQty <= $minStok;
                    $statusCat = $isHabis ? 'habis' : ($isMenipis ? 'menipis' : 'ada');

                    $itemData = [
                        'barang_id'          => $item->barang_id,
                        'barang_cd'          => $item->barang_cd,
                        'barang_nm'          => $item->barang_nm,
                        'jenis_nm'           => $item->jenisBarang?->jenis_barang_nm ?? 'Hasil Produksi',
                        'satuan_nm'          => $item->satuanDasar?->satuan_nm ?? 'Unit',
                        'sisa_qty'           => $sisaQty,
                        'min_stok'           => $minStok,
                        'qty_awal'           => (float) $item->total_qty_awal,
                        'qty_keluar'         => max(0, (float) $item->total_qty_awal - $sisaQty),
                        'nilai_total'        => (float) $item->total_sisa_nilai,
                        'active_batch_count' => (int) $item->active_batch_count,
                        'status'             => $statusCat,
                        'batches'            => $item->stokBatches->map(function($b) {
                            return [
                                'batch_no'     => $b->batch_no,
                                'grade_cd'     => $b->grade_cd ?? 'A',
                                'gudang_nm'    => $b->gudang?->gudang_nm ?? '-',
                                'sisa_qty'     => (float) $b->sisa_qty,
                                'qty_awal'     => (float) $b->qty_awal,
                                'harga_satuan' => (float) $b->harga_satuan,
                                'nilai'        => (float) $b->sisa_qty * (float) $b->harga_satuan,
                                'expired_tgl'  => $b->expired_tgl ? \Carbon\Carbon::parse($b->expired_tgl)->format('d/m/Y') : '-',
                                'tgl_terima'   => $b->created_at ? $b->created_at->format('d/m/Y') : '-',
                                'is_habis'     => (float) $b->sisa_qty <= 0,
                            ];
                        })->toArray()
                    ];

                    if ($index === 0) {
                        $firstItemJson = $itemData;
                    }
                @endphp

                <div class="master-item-card {{ $index === 0 ? 'selected' : '' }}" 
                     id="item-card-{{ $item->barang_id }}"
                     data-id="{{ $item->barang_id }}"
                     data-status="{{ $statusCat }}"
                     data-search="{{ strtolower($item->barang_cd . ' ' . $item->barang_nm . ' ' . ($item->jenisBarang?->jenis_barang_nm ?? '')) }}"
                     onclick="selectBarang({{ json_encode($itemData) }})"
                     style="padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid {{ $index === 0 ? '#ea580c' : '#e2e8f0' }}; background: {{ $index === 0 ? '#fff7ed' : '#ffffff' }}; cursor: pointer; transition: all 0.15s ease; border-left: 4px solid {{ $index === 0 ? '#ea580c' : ($isHabis ? '#cbd5e1' : ($isMenipis ? '#d97706' : '#059669')) }};">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.15rem;">
                        <span style="font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #475569;">
                            {{ $item->barang_cd }}
                        </span>
                        <div style="display: flex; gap: 0.25rem; align-items: center;">
                            @if ($isHabis)
                                <span style="background: #fee2e2; color: #991b1b; padding: 0.1rem 0.35rem; border-radius: 3px; font-size: 0.65rem; font-weight: 700;">HABIS</span>
                            @elseif ($isMenipis)
                                <span style="background: #fef3c7; color: #b45309; padding: 0.1rem 0.35rem; border-radius: 3px; font-size: 0.65rem; font-weight: 700;">⚠️ MENIPIS</span>
                            @else
                                <span style="background: #dcfce7; color: #166534; padding: 0.1rem 0.35rem; border-radius: 3px; font-size: 0.65rem; font-weight: 700;">🟢 AMAN</span>
                            @endif
                        </div>
                    </div>

                    <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $item->barang_nm }}
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem; font-size: 0.75rem;">
                        <span style="color: #64748b;">
                            {{ $item->jenisBarang?->jenis_barang_nm ?? 'Output' }}
                        </span>
                        <span style="font-weight: 800; color: {{ $isHabis ? '#94a3b8' : ($isMenipis ? '#d97706' : '#ea580c') }}; font-size: 0.85rem;">
                            {{ number_format($sisaQty, 2, ',', '.') }} {{ $item->satuanDasar?->satuan_nm }}
                        </span>
                    </div>
                </div>
            @empty
                <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                    Tidak ada barang hasil produksi ditemukan.
                </div>
            @endforelse
        </div>

        {{-- Footer Panel Kiri --}}
        <div style="padding: 0.4rem 0.75rem; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 0.725rem; color: #64748b; display: flex; justify-content: space-between; align-items: center;">
            <span id="itemCountLabel">Menampilkan {{ count($summaryList) }} item</span>
            <span>💡 Klik item untuk rincian batch output</span>
        </div>
    </div>

    {{-- PANEL KANAN: DETAIL BARANG & BATCH (LEBAR 62%) --}}
    <div id="detailPanel" style="flex: 1; display: flex; flex-direction: column; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        {{-- Detail Header --}}
        <div style="padding: 0.85rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span id="dtlCd" style="font-family: monospace; font-size: 0.8rem; font-weight: 800; background: #e2e8f0; padding: 0.15rem 0.45rem; border-radius: 4px; color: #1e293b;">
                        -
                    </span>
                    <span id="dtlJenis" style="background: #ffedd5; color: #c2410c; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                        -
                    </span>
                    <span id="dtlStatusBadge"></span>
                </div>
                <h2 id="dtlNm" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0.25rem 0 0 0;">
                    Pilih barang di sebelah kiri
                </h2>
            </div>

            <div style="display: flex; gap: 0.4rem;">
                <a id="dtlBtnLedger" href="#" class="btn btn-sm btn-secondary" style="font-weight: 600; padding: 0.35rem 0.75rem; font-size: 0.775rem;">
                    📜 Kartu Stok WIP/FG &rarr;
                </a>
            </div>
        </div>

        {{-- Detail Body --}}
        <div style="flex: 1; overflow-y: auto; padding: 1rem 1.25rem;">
            {{-- 4 Kotak Ringkasan Cepat --}}
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.6rem; margin-bottom: 1rem;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Sisa Fisik On-Hand</div>
                    <div id="dtlSisaQty" style="font-size: 1.1rem; font-weight: 800; color: #ea580c; margin-top: 0.1rem;">-</div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Buffer Minimum</div>
                    <div id="dtlMinStok" style="font-size: 1.1rem; font-weight: 700; color: #475569; margin-top: 0.1rem;">-</div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Hasil / Penjualan</div>
                    <div id="dtlAwalKeluar" style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-top: 0.25rem;">-</div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Nilai Stok Standar</div>
                    <div id="dtlNilaiTotal" style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-top: 0.1rem;">-</div>
                </div>
            </div>

            {{-- Bagian Tabel Batch Output --}}
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                <div style="background: #7c2d12; color: #ffffff; padding: 0.55rem 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 0.35rem;">
                        <span>Rincian Lot Batch Hasil Masak &amp; Packing</span>
                        <span id="dtlBatchCountBadge" style="background: #ea580c; padding: 0.1rem 0.4rem; border-radius: 10px; font-size: 0.7rem;">0 Batch</span>
                    </div>
                    <div style="font-size: 0.7rem; color: #fed7aa;">
                        📦 Prioritas FIFO untuk pengemasan atau pengiriman ke toko/agen
                    </div>
                </div>

                <div style="overflow-x: auto; max-height: 280px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                        <thead style="position: sticky; top: 0; background: #f1f5f9; z-index: 5;">
                            <tr style="border-bottom: 1px solid #cbd5e1; color: #475569; font-weight: 700;">
                                <th style="padding: 0.5rem 0.65rem; text-align: center; width: 40px;">No</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: left;">Nomor Batch Output</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: left;">Lokasi Penyimpanan</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: left;">Tgl Dihasilkan</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: left;">Kadaluarsa</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right; font-weight: 800;">Sisa Fisik</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right;">HPP Standar</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right;">Total Nilai</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="dtlBatchTbody">
                            {{-- Diisi secara dinamis oleh JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT INTERAKTIF MASTER-DETAIL --}}
<script>
    let activeFilter = 'all';

    function selectBarang(item) {
        if (!item) return;

        document.querySelectorAll('.master-item-card').forEach(el => {
            el.style.border = '1px solid #e2e8f0';
            el.style.background = '#ffffff';
        });

        const activeCard = document.getElementById('item-card-' + item.barang_id);
        if (activeCard) {
            activeCard.style.border = '1px solid #ea580c';
            activeCard.style.background = '#fff7ed';
        }

        document.getElementById('dtlCd').textContent = item.barang_cd;
        document.getElementById('dtlNm').textContent = item.barang_nm;
        document.getElementById('dtlJenis').textContent = item.jenis_nm;

        const badgeSpan = document.getElementById('dtlStatusBadge');
        if (item.status === 'habis') {
            badgeSpan.innerHTML = '<span style="background: #fee2e2; color: #991b1b; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700;">🔴 HABIS</span>';
        } else if (item.status === 'menipis') {
            badgeSpan.innerHTML = '<span style="background: #fef3c7; color: #b45309; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700;">⚠️ MENIPIS</span>';
        } else {
            badgeSpan.innerHTML = '<span style="background: #dcfce7; color: #166534; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700;">🟢 TERSEDIA</span>';
        }

        document.getElementById('dtlBtnLedger').href = `{{ route('produksi.stok.ledger') }}?barang_id=${item.barang_id}`;

        const sisaColor = item.status === 'habis' ? '#94a3b8' : (item.status === 'menipis' ? '#d97706' : '#ea580c');
        document.getElementById('dtlSisaQty').innerHTML = `<span style="color: ${sisaColor}">${item.sisa_qty.toLocaleString('id-ID', {minimumFractionDigits: 2})}</span> <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">${item.satuan_nm}</span>`;
        
        document.getElementById('dtlMinStok').textContent = item.min_stok > 0 
            ? `${item.min_stok.toLocaleString('id-ID')} ${item.satuan_nm}` 
            : 'Belum diatur';

        document.getElementById('dtlAwalKeluar').innerHTML = `Output: ${item.qty_awal.toLocaleString('id-ID')} <br> Keluar: <span style="color: #dc2626">${item.qty_keluar.toLocaleString('id-ID')}</span>`;

        document.getElementById('dtlNilaiTotal').textContent = 'Rp ' + item.nilai_total.toLocaleString('id-ID');

        const tbody = document.getElementById('dtlBatchTbody');
        const countBadge = document.getElementById('dtlBatchCountBadge');
        countBadge.textContent = `${item.batches ? item.batches.length : 0} Batch`;

        if (!item.batches || item.batches.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">Belum ada catatan lot hasil produksi untuk barang ini.</td></tr>`;
            return;
        }

        let html = '';
        item.batches.forEach((b, idx) => {
            const isFirst = (idx === 0 && !b.is_habis);
            const rowBg = b.is_habis ? '#fafafa' : (isFirst ? '#fff7ed' : '#ffffff');
            const textColor = b.is_habis ? '#94a3b8' : '#0f172a';

            html += `
                <tr style="border-bottom: 1px solid #f1f5f9; background: ${rowBg};">
                    <td style="padding: 0.45rem 0.65rem; text-align: center; color: #64748b;">
                        ${isFirst ? '<span title="Prioritas 1 FIFO">⭐</span>' : (idx + 1)}
                    </td>
                    <td style="padding: 0.45rem 0.65rem;">
                        <span class="batch-clickable" onclick="showBatchDetailModal('${b.batch_no}', ${item.barang_id})" title="Klik untuk menelusuri isi detail & riwayat batch" style="font-family: monospace; font-weight: 700; font-size: 0.775rem; background: ${b.is_habis ? '#f1f5f9' : '#fff7ed'}; color: ${b.is_habis ? '#94a3b8' : '#ea580c'}; padding: 0.15rem 0.45rem; border-radius: 4px; border: 1px solid ${b.is_habis ? '#e2e8f0' : '#fed7aa'};">
                            ${b.batch_no} 🔍
                        </span>
                    </td>
                    <td style="padding: 0.45rem 0.65rem; color: #334155;">${b.gudang_nm}</td>
                    <td style="padding: 0.45rem 0.65rem; color: #64748b;">${b.tgl_terima}</td>
                    <td style="padding: 0.45rem 0.65rem; color: #64748b;">${b.expired_tgl}</td>
                    <td style="padding: 0.45rem 0.65rem; text-align: right; font-weight: 800; color: ${b.is_habis ? '#94a3b8' : '#ea580c'};">
                        ${b.sisa_qty.toLocaleString('id-ID', {minimumFractionDigits: 2})} ${item.satuan_nm}
                    </td>
                    <td style="padding: 0.45rem 0.65rem; text-align: right; color: #475569;">
                        Rp ${b.harga_satuan.toLocaleString('id-ID')}
                    </td>
                    <td style="padding: 0.45rem 0.65rem; text-align: right; font-weight: 700; color: ${textColor};">
                        Rp ${b.nilai.toLocaleString('id-ID')}
                    </td>
                    <td style="padding: 0.45rem 0.65rem; text-align: center;">
                        ${b.is_habis 
                            ? '<span style="color: #94a3b8; font-size: 0.7rem; font-weight: 700;">HABIS</span>' 
                            : '<span style="background: #ea580c; color: #ffffff; padding: 0.1rem 0.4rem; border-radius: 3px; font-size: 0.675rem; font-weight: 700;">READY</span>'}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function filterMasterList() {
        const query = document.getElementById('masterSearchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.master-item-card');
        let visibleCount = 0;
        let firstVisible = null;

        cards.forEach(card => {
            const searchData = card.getAttribute('data-search') || '';
            const statusData = card.getAttribute('data-status') || '';

            const matchesSearch = !query || searchData.includes(query);
            const matchesChip = (activeFilter === 'all') || (statusData === activeFilter);

            if (matchesSearch && matchesChip) {
                card.style.display = 'block';
                visibleCount++;
                if (!firstVisible) firstVisible = card;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('itemCountLabel').textContent = `Menampilkan ${visibleCount} barang`;
    }

    function setChipFilter(filterType, btn) {
        activeFilter = filterType;
        document.querySelectorAll('.btn-chip').forEach(b => {
            b.style.background = '#ffffff';
            b.style.color = '#475569';
        });

        btn.style.background = '#0f172a';
        btn.style.color = '#ffffff';

        filterMasterList();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const firstItem = @json($firstItemJson);
        if (firstItem) {
            selectBarang(firstItem);
        }
    });
</script>

{{-- ======================================================== --}}
{{-- KONTEN MODE 2: TABEL RINGKASAN PER BARANG (2-LEVEL)     --}}
{{-- ======================================================== --}}
@elseif ($viewType === 'summary')
<div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('produksi.stok.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 860px; width: 100%;">
            <input type="hidden" name="view" value="summary">
            
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama atau kode produk..." class="form-control" style="padding: 0.45rem 0.75rem; max-width: 220px; font-size: 0.85rem;">

            <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 170px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Lokasi Pabrik --</option>
                @foreach ($gudangList as $gdg)
                    <option value="{{ $gdg->gudang_id }}" {{ $gudangId == $gdg->gudang_id ? 'selected' : '' }}>
                        {{ $gdg->display_name }}
                    </option>
                @endforeach
            </select>

            <select name="jenis_barang_id" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 160px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Output --</option>
                @foreach ($jenisBarangList as $jb)
                    <option value="{{ $jb->jenis_barang_id }}" {{ ($jenisBarangId == $jb->jenis_barang_id) ? 'selected' : '' }}>
                        {{ $jb->jenis_barang_nm }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 150px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="tersedia" {{ ($status == 'tersedia') ? 'selected' : '' }}>🟢 Tersedia</option>
                <option value="menipis" {{ ($status == 'menipis') ? 'selected' : '' }}>⚠️ Menipis</option>
                <option value="habis" {{ ($status == 'habis') ? 'selected' : '' }}>🔴 Habis</option>
                <option value="aman" {{ ($status == 'aman') ? 'selected' : '' }}>🛡️ Aman (> Min)</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($search) || !empty($gudangId) || !empty($jenisBarangId) || !empty($status))
                <a href="{{ route('produksi.stok.index', ['view' => 'summary']) }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <span style="color: #64748b; font-size: 0.85rem;">
                Total: <strong style="color: #0f172a;">{{ $summaryList->total() }}</strong> Produk Output
            </span>
            <div style="display: flex; gap: 0.35rem;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllBatches(true)" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                    Buka Semua Batch
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllBatches(false)" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                    Tutup Semua
                </button>
            </div>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 2px solid #cbd5e1;">
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.5rem; width: 35px; text-align: center;"></th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: left;">Kode</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: left;">Nama Produk &amp; Kategori</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: center;">Satuan</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Buffer Min</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Total Output</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Terjual / Keluar</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 800; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Sisa On-Hand</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Total Nilai</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: center;">Status</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: center;">Batch</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 1rem; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summaryList as $item)
                    @php
                        $sisaQty = (float) $item->total_sisa_qty;
                        $qtyAwal = (float) $item->total_qty_awal;
                        $qtyKeluar = max(0, $qtyAwal - $sisaQty);
                        $minStok = (float) ($item->batas_minimum_qty ?? 0);
                        $isHabis = $sisaQty <= 0;
                        $isMenipis = !$isHabis && $minStok > 0 && $sisaQty <= $minStok;
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#fff7ed'" onmouseout="this.style.background='transparent'" onclick="toggleBatchRow({{ $item->barang_id }})">
                        <td style="text-align: center; padding: 0.65rem 0.25rem;">
                            <span id="icon-chevron-{{ $item->barang_id }}" style="display: inline-block; font-size: 0.75rem; color: #64748b; transition: transform 0.2s;">▶</span>
                        </td>
                        <td style="padding: 0.65rem 0.75rem; font-family: monospace; font-weight: 700; color: #ea580c;">{{ $item->barang_cd }}</td>
                        <td style="padding: 0.65rem 0.75rem;">
                            <strong style="color: #0f172a;">{{ $item->barang_nm }}</strong>
                            <span style="display: block; font-size: 0.725rem; color: #64748b; font-weight: 500;">
                                {{ $item->jenisBarang?->jenis_barang_nm ?? 'Output' }}
                            </span>
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: center;">{{ $item->satuanDasar?->satuan_nm }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; color: #64748b;">{{ $minStok > 0 ? number_format($minStok, 0, ',', '.') : '-' }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right;">{{ number_format($qtyAwal, 2, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; color: #dc2626;">{{ number_format($qtyKeluar, 2, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 800; color: {{ $isHabis ? '#94a3b8' : ($isMenipis ? '#d97706' : '#ea580c') }};">
                            {{ number_format($sisaQty, 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 700; color: #0f172a;">Rp {{ number_format((float) $item->total_sisa_nilai, 0, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: center;">
                            @if ($isHabis)
                                <span style="background: #fee2e2; color: #991b1b; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.675rem; font-weight: 700;">HABIS</span>
                            @elseif ($isMenipis)
                                <span style="background: #fef3c7; color: #b45309; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.675rem; font-weight: 700;">⚠️ MENIPIS</span>
                            @else
                                <span style="background: #dcfce7; color: #166534; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.675rem; font-weight: 700;">🟢 AMAN</span>
                            @endif
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: center;">
                            <span style="background: #ffedd5; color: #c2410c; padding: 0.15rem 0.45rem; border-radius: 10px; font-size: 0.7rem; font-weight: 700;">
                                {{ $item->active_batch_count }} Batch
                            </span>
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: right;" onclick="event.stopPropagation();">
                            <a href="{{ route('produksi.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $gudangId]) }}" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.2rem 0.45rem;">
                                Kartu &rarr;
                            </a>
                        </td>
                    </tr>

                    {{-- Drawer Sub-tabel Batch --}}
                    <tr id="drawer-batch-{{ $item->barang_id }}" style="display: none; background: #f8fafc;">
                        <td colspan="12" style="padding: 0.75rem 1.25rem;">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.775rem;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid #cbd5e1;">
                                            <th style="background: #f1f5f9 !important; color: #334155 !important; font-weight: 700; padding: 0.45rem 0.65rem; text-align: left;">No. Batch Output</th>
                                            <th style="background: #f1f5f9 !important; color: #334155 !important; font-weight: 700; padding: 0.45rem 0.65rem; text-align: left;">Lokasi Penyimpanan</th>
                                            <th style="background: #f1f5f9 !important; color: #334155 !important; font-weight: 700; padding: 0.45rem 0.65rem; text-align: left;">Tgl Dihasilkan</th>
                                            <th style="background: #f1f5f9 !important; color: #334155 !important; font-weight: 700; padding: 0.45rem 0.65rem; text-align: right;">Sisa Qty</th>
                                            <th style="background: #f1f5f9 !important; color: #334155 !important; font-weight: 700; padding: 0.45rem 0.65rem; text-align: right;">Harga Satuan</th>
                                            <th style="background: #f1f5f9 !important; color: #334155 !important; font-weight: 700; padding: 0.45rem 0.65rem; text-align: right;">Total Nilai</th>
                                            <th style="background: #f1f5f9 !important; color: #334155 !important; font-weight: 700; padding: 0.45rem 0.65rem; text-align: center;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($item->stokBatches as $b)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 0.45rem 0.65rem;">
                                                    <span class="batch-clickable" onclick="showBatchDetailModal('{{ $b->batch_no }}', {{ $b->barang_id }})" title="Klik untuk menelusuri isi detail & riwayat batch" style="font-family: monospace; font-weight: 700; color: #ea580c; background: #fff7ed; padding: 2px 7px; border-radius: 4px; border: 1px solid #fed7aa; font-size: 0.775rem;">
                                                        {{ $b->batch_no }} 🔍
                                                    </span>
                                                </td>
                                                <td style="padding: 0.45rem 0.65rem;">{{ $b->gudang?->gudang_nm ?? '-' }}</td>
                                                <td style="padding: 0.45rem 0.65rem; color: #64748b;">{{ $b->created_at ? $b->created_at->format('d/m/Y') : '-' }}</td>
                                                <td style="padding: 0.45rem 0.65rem; text-align: right; font-weight: 800; color: {{ (float)$b->sisa_qty <= 0 ? '#94a3b8' : '#ea580c' }};">
                                                    {{ number_format((float)$b->sisa_qty, 2, ',', '.') }}
                                                </td>
                                                <td style="padding: 0.45rem 0.65rem; text-align: right;">Rp {{ number_format((float)$b->harga_satuan, 0, ',', '.') }}</td>
                                                <td style="padding: 0.45rem 0.65rem; text-align: right; font-weight: 700;">Rp {{ number_format((float)$b->sisa_qty * (float)$b->harga_satuan, 0, ',', '.') }}</td>
                                                <td style="padding: 0.45rem 0.65rem; text-align: center;">
                                                    <span style="font-size: 0.675rem; font-weight: 700; color: {{ (float)$b->sisa_qty <= 0 ? '#991b1b' : '#166534' }};">
                                                        {{ (float)$b->sisa_qty <= 0 ? 'HABIS' : 'READY' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" style="text-align: center; padding: 1rem; color: #94a3b8;">Belum ada riwayat batch untuk barang ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" style="text-align: center; padding: 2.5rem; color: #64748b;">Tidak ada data produk hasil produksi ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($summaryList->hasPages())
        <div style="padding: 0.75rem 1.25rem; border-top: 1px solid #e2e8f0; background: #ffffff;">
            {{ $summaryList->withQueryString()->links() }}
        </div>
    @endif
</div>

<script>
function toggleBatchRow(id) {
    const d = document.getElementById('drawer-batch-' + id);
    const c = document.getElementById('icon-chevron-' + id);
    if (!d) return;
    const isHidden = d.style.display === 'none' || d.style.display === '';
    d.style.display = isHidden ? 'table-row' : 'none';
    if (c) c.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
}
function toggleAllBatches(show) {
    document.querySelectorAll('[id^="drawer-batch-"]').forEach(el => el.style.display = show ? 'table-row' : 'none');
    document.querySelectorAll('[id^="icon-chevron-"]').forEach(el => el.style.transform = show ? 'rotate(90deg)' : 'rotate(0deg)');
}
</script>

{{-- ======================================================== --}}
{{-- KONTEN MODE 3: DETAIL SHEET PER-BATCH (FLAT FORMAT EXCEL)--}}
{{-- ======================================================== --}}
@else
<div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('produksi.stok.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 860px; width: 100%;">
            <input type="hidden" name="view" value="batch">
            
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nomor batch, kode atau nama..." class="form-control" style="padding: 0.45rem 0.75rem; max-width: 240px; font-size: 0.85rem;">

            <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 170px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Lokasi Pabrik --</option>
                @foreach ($gudangList as $gdg)
                    <option value="{{ $gdg->gudang_id }}" {{ $gudangId == $gdg->gudang_id ? 'selected' : '' }}>
                        {{ $gdg->display_name }}
                    </option>
                @endforeach
            </select>

            <select name="jenis_barang_id" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 160px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Output --</option>
                @foreach ($jenisBarangList as $jb)
                    <option value="{{ $jb->jenis_barang_id }}" {{ ($jenisBarangId == $jb->jenis_barang_id) ? 'selected' : '' }}>
                        {{ $jb->jenis_barang_nm }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 150px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="tersedia" {{ ($status == 'tersedia') ? 'selected' : '' }}>🟢 Tersedia</option>
                <option value="habis" {{ ($status == 'habis') ? 'selected' : '' }}>🔴 Habis</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($search) || !empty($gudangId) || !empty($jenisBarangId) || !empty($status))
                <a href="{{ route('produksi.stok.index', ['view' => 'batch']) }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif
        </form>

        <span style="color: #64748b; font-size: 0.85rem;">
            Total: <strong style="color: #0f172a;">{{ $stokList->total() }}</strong> Baris Lot Batch Output
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 2px solid #cbd5e1;">
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: left;">Kode Batch</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: left;">Kode Barang</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: left;">Nama Produk &amp; Lokasi</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Qty Output</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">HPP Standar</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Qty Terjual/Keluar</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 800; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Sisa On-Hand</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: right;">Total Nilai</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 0.75rem; text-align: center;">Status</th>
                    <th style="background: #f8fafc !important; color: #1e293b !important; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 1rem; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stokList as $b)
                    @php
                        $isHabis = (float) $b->sisa_qty <= 0;
                        $qtyAwal = (float) ($b->qty_awal > 0 ? $b->qty_awal : $b->sisa_qty);
                        $qtyKeluar = max(0, $qtyAwal - (float) $b->sisa_qty);
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $isHabis ? '#fafafa' : '#ffffff' }}; transition: background 0.15s;" onmouseover="this.style.background='#fff7ed'" onmouseout="this.style.background='{{ $isHabis ? '#fafafa' : '#ffffff' }}'">
                        <td style="padding: 0.65rem 0.75rem;">
                            <span class="batch-clickable" onclick="showBatchDetailModal('{{ $b->batch_no }}', {{ $b->barang_id }})" title="Klik untuk menelusuri isi detail & riwayat batch" style="font-family: monospace; font-weight: 700; color: #ea580c; background: #fff7ed; padding: 2px 7px; border-radius: 4px; border: 1px solid #fed7aa; font-size: 0.8rem;">
                                {{ $b->batch_no }} 🔍
                            </span>
                        </td>
                        <td style="padding: 0.65rem 0.75rem; font-family: monospace;">{{ $b->barang?->barang_cd }}</td>
                        <td style="padding: 0.65rem 0.75rem;">
                            <strong style="color: #0f172a;">{{ $b->barang?->barang_nm }}</strong>
                            <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.1rem;">
                                <span>{{ $b->gudang?->gudang_nm }}</span>
                                @if($b->barang?->jenisBarang)
                                    &bull; <span>{{ $b->barang->jenisBarang->jenis_barang_nm }}</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right;">{{ number_format($qtyAwal, 2, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right;">Rp {{ number_format((float)$b->harga_satuan, 0, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; color: #dc2626;">{{ number_format($qtyKeluar, 2, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 800; color: {{ $isHabis ? '#94a3b8' : '#ea580c' }};">
                            {{ number_format((float)$b->sisa_qty, 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 700; color: #0f172a;">
                            Rp {{ number_format((float)$b->sisa_qty * (float)$b->harga_satuan, 0, ',', '.') }}
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: center;">
                            @if ($isHabis)
                                <span style="background: #fee2e2; color: #991b1b; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.675rem; font-weight: 700;">HABIS</span>
                            @else
                                <span style="background: #ea580c; color: #ffffff; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.675rem; font-weight: 700;">READY</span>
                            @endif
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: center;">
                            @php
                                $batchMenuId = 'dropdown-prod-batch-' . $b->stok_id;
                            @endphp
                            <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, '{{ $batchMenuId }}')">
                                Aksi &#9660;
                            </button>
                            <div id="{{ $batchMenuId }}" class="action-dropdown-menu">
                                <button type="button" class="action-dropdown-item" onclick="showBatchDetailModal('{{ $b->batch_no }}', {{ $b->barang_id }})">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    <span>Dossier Batch</span>
                                </button>
                                <a href="{{ route('produksi.stok.ledger', ['barang_id' => $b->barang_id, 'gudang_id' => $b->gudang_id]) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Buku Kartu Stok</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" style="text-align: center; padding: 2.5rem; color: #64748b;">Tidak ada data batch hasil produksi ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($stokList->hasPages())
        <div style="padding: 0.75rem 1.25rem; border-top: 1px solid #e2e8f0; background: #ffffff;">
            {{ $stokList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endif

@include('gudang.stok.partials.modal-batch-detail')

@push('scripts')
    <script src="{{ asset('js/gudang/stok/batch-detail-modal.js') }}"></script>
@endpush

@endsection
