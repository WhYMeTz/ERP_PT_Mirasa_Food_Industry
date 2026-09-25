@extends('layouts.app')

@section('title', 'Monitoring & Lacak Stok Gudang - ERP PT Mirasa')

@section('content')
{{-- Header Halaman --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
            <span>Monitoring & Lacak Stok Gudang</span>
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700;">Inventory Engine</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Ringkasan saldo fisik per barang, ambang batas minimum (safety stock), serta pelacakan sub-batch sistem FIFO/FEFO.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('gudang.terima.create') }}" class="btn btn-primary" style="background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Terima Barang Masuk
        </a>
        <a href="{{ route('gudang.pemakaian.create') }}" class="btn btn-primary" style="background: #dc2626; border-color: #dc2626; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
            - Catat Barang Keluar
        </a>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- Card 1: Total Nilai Persediaan --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 1rem;">
        <div style="background: #ecfdf5; color: #059669; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
            💰
        </div>
        <div style="overflow: hidden;">
            <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Total Nilai Persediaan
            </div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-top: 0.15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Rp {{ number_format($kpiMetrics['total_nilai'], 2, ',', '.') }}">
                Rp {{ number_format($kpiMetrics['total_nilai'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #059669; font-weight: 600; margin-top: 0.15rem;">
                Valuasi fisik aktif di gudang
            </div>
        </div>
    </div>

    {{-- Card 2: Total SKU Terdaftar --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 1rem;">
        <div style="background: #eff6ff; color: #2563eb; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
            📦
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Item Barang (SKU)
            </div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-top: 0.15rem;">
                {{ $kpiMetrics['total_sku'] }} <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">Item</span>
            </div>
            <div style="font-size: 0.75rem; color: #2563eb; font-weight: 600; margin-top: 0.15rem;">
                {{ $kpiMetrics['sku_tersedia'] }} Tersedia • {{ $kpiMetrics['sku_habis'] }} Kosong
            </div>
        </div>
    </div>

    {{-- Card 3: Stok Menipis / Kritis --}}
    <div style="background: #ffffff; border: 1px solid {{ $kpiMetrics['sku_menipis'] > 0 ? '#fde68a' : '#e2e8f0' }}; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 1rem; background-color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#fffbeb' : '#ffffff' }};">
        <div style="background: {{ $kpiMetrics['sku_menipis'] > 0 ? '#fef3c7' : '#f1f5f9' }}; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#d97706' : '#64748b' }}; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
            ⚠️
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#64748b' }};">
                Stok Menipis / Kritis
            </div>
            <div style="font-size: 1.25rem; font-weight: 800; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#0f172a' }}; margin-top: 0.15rem;">
                {{ $kpiMetrics['sku_menipis'] }} <span style="font-size: 0.85rem; font-weight: 500;">Item</span>
            </div>
            <div style="font-size: 0.75rem; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#64748b' }}; font-weight: 600; margin-top: 0.15rem;">
                ≤ Batas safety stock minimum
            </div>
        </div>
    </div>

    {{-- Card 4: Total Batch Aktif FIFO --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 1rem;">
        <div style="background: #f5f3ff; color: #7c3aed; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
            🏷️
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Batch Aktif (FIFO)
            </div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-top: 0.15rem;">
                {{ $kpiMetrics['batch_aktif'] }} <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">Batch</span>
            </div>
            <div style="font-size: 0.75rem; color: #7c3aed; font-weight: 600; margin-top: 0.15rem;">
                Tersedia untuk pengeluaran bahan
            </div>
        </div>
    </div>
</div>

{{-- Main Card --}}
<div class="card" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
    {{-- Card Header: View Switcher, Filter Tabs & Form --}}
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            {{-- Mode Tampilan Switcher --}}
            <div style="display: inline-flex; background: #e2e8f0; padding: 0.25rem; border-radius: 8px; gap: 0.25rem;">
                <a href="{{ route('gudang.stok.index', array_merge(request()->except(['view', 'page']), ['view' => 'summary'])) }}" 
                   class="btn btn-sm" 
                   style="background: {{ $viewType === 'summary' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'summary' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'summary' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 700; padding: 0.4rem 0.85rem; font-size: 0.85rem;">
                    📦 Ringkasan per Barang (2-Level)
                </a>
                <a href="{{ route('gudang.stok.index', array_merge(request()->except(['view', 'page']), ['view' => 'batch'])) }}" 
                   class="btn btn-sm" 
                   style="background: {{ $viewType === 'batch' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'batch' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'batch' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 700; padding: 0.4rem 0.85rem; font-size: 0.85rem;">
                    📋 Detail Sheet per-Batch
                </a>
            </div>

            {{-- Status Filter Chips --}}
            <div style="display: inline-flex; background: #f1f5f9; padding: 0.2rem; border-radius: 6px; gap: 0.2rem;">
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('status'), ['status' => ''])) }}" 
                   style="padding: 0.3rem 0.65rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none; color: {{ empty($status) ? '#0f172a' : '#64748b' }}; background: {{ empty($status) ? '#ffffff' : 'transparent' }};">
                    Semua
                </a>
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('status'), ['status' => 'tersedia'])) }}" 
                   style="padding: 0.3rem 0.65rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none; color: {{ $status === 'tersedia' ? '#059669' : '#64748b' }}; background: {{ $status === 'tersedia' ? '#ffffff' : 'transparent' }};">
                    🟢 Tersedia
                </a>
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('status'), ['status' => 'menipis'])) }}" 
                   style="padding: 0.3rem 0.65rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none; color: {{ $status === 'menipis' ? '#d97706' : '#64748b' }}; background: {{ $status === 'menipis' ? '#ffffff' : 'transparent' }};">
                    ⚠️ Menipis
                </a>
                <a href="{{ route('gudang.stok.index', array_merge(request()->except('status'), ['status' => 'habis'])) }}" 
                   style="padding: 0.3rem 0.65rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none; color: {{ $status === 'habis' ? '#dc2626' : '#64748b' }}; background: {{ $status === 'habis' ? '#ffffff' : 'transparent' }};">
                    🔴 Habis
                </a>
            </div>
        </div>

        {{-- Search & Gudang Filters --}}
        <form action="{{ route('gudang.stok.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="view" value="{{ $viewType }}">
            <input type="hidden" name="status" value="{{ $status ?? '' }}">
            
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode, nama, jenis..." class="form-control" style="padding: 0.45rem 0.75rem; width: 220px; font-size: 0.85rem; border-radius: 6px;">
            
            <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; width: 190px; font-size: 0.85rem; border-radius: 6px;" onchange="this.form.submit()">
                <option value="">-- Semua Gudang --</option>
                @foreach ($gudangList as $gdg)
                    <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                        {{ $gdg->gudang_nm }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem; font-weight: 600;">Filter</button>
            @if(!empty($search) || !empty($gudangId) || !empty($status))
                <a href="{{ route('gudang.stok.index', ['view' => $viewType]) }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.75rem;">&times;</a>
            @endif
        </form>
    </div>

    {{-- KONTEN MODE 1: RINGKASAN PER BARANG (2-LEVEL DRILLDOWN) --}}
    @if ($viewType === 'summary')
        <div style="padding: 0.75rem 1.25rem; background: #f1f5f9; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; color: #475569;">
            <div>
                Menampilkan <strong>{{ $summaryList->total() }}</strong> barang terdaftar. Klik baris atau tombol <span style="background: #e2e8f0; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 700;">[🔽 X Batch]</span> untuk melihat detail nomor batch FIFO & tanggal expired.
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllBatches(true)" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                    Buka Semua Batch
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllBatches(false)" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                    Tutup Semua Batch
                </button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="background: #0f394c; color: #ffffff;">
                        <th style="padding: 0.75rem 0.5rem 0.75rem 1rem; width: 40px; text-align: center;"></th>
                        <th style="padding: 0.75rem 0.75rem; text-align: left; color: #e2e8f0; font-weight: 600; width: 120px;">Kode Barang</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: left; color: #e2e8f0; font-weight: 600;">Nama Barang & Kategori</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: center; color: #e2e8f0; font-weight: 600; width: 90px;">Satuan</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: right; color: #e2e8f0; font-weight: 600; width: 100px;">Min. Stok</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: right; color: #e2e8f0; font-weight: 600; width: 110px;">Total Masuk</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: right; color: #e2e8f0; font-weight: 600; width: 110px;">Total Keluar</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: right; color: #e2e8f0; font-weight: 700; width: 130px;">Sisa Fisik</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: right; color: #e2e8f0; font-weight: 600; width: 140px;">Estimasi Nilai</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: center; color: #e2e8f0; font-weight: 600; width: 100px;">Status</th>
                        <th style="padding: 0.75rem 0.75rem; text-align: center; color: #e2e8f0; font-weight: 600; width: 120px;">Batch FIFO</th>
                        <th style="padding: 0.75rem 1rem 0.75rem 0.75rem; text-align: right; color: #e2e8f0; font-weight: 600; width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summaryList as $item)
                        @php
                            $sisaQty = (float) $item->total_sisa_qty;
                            $qtyAwal = (float) $item->total_qty_awal;
                            $qtyKeluar = max(0, $qtyAwal - $sisaQty);
                            $minStok = (float) ($item->batas_minimum_qty ?? 0);
                            $nilaiTotal = (float) $item->total_sisa_nilai;
                            $activeBatchCount = (int) $item->active_batch_count;
                            $totalBatchCount = (int) $item->total_batch_count;

                            $isHabis = $sisaQty <= 0;
                            $isMenipis = !$isHabis && $minStok > 0 && $sisaQty <= $minStok;
                            $isAman = !$isHabis && !$isMenipis;

                            $rowBg = $isHabis ? '#fafafa' : ($isMenipis ? '#fffdf7' : '#ffffff');
                        @endphp
                        {{-- Level 1 Row: Ringkasan Barang --}}
                        <tr id="parent-row-{{ $item->barang_id }}" 
                            style="border-bottom: 1px solid #f1f5f9; background: {{ $rowBg }}; cursor: pointer; transition: background 0.15s ease;"
                            onmouseover="this.style.background='{{ $isMenipis ? '#fef9c3' : '#f8fafc' }}'"
                            onmouseout="this.style.background='{{ $rowBg }}'"
                            onclick="toggleBatchRow({{ $item->barang_id }})">
                            
                            {{-- Accordion Chevron Icon --}}
                            <td style="text-align: center; padding: 0.85rem 0.25rem 0.85rem 1rem;">
                                <span id="icon-chevron-{{ $item->barang_id }}" style="display: inline-block; transition: transform 0.2s ease; color: #64748b; font-size: 0.8rem; font-weight: 700;">
                                    ▶
                                </span>
                            </td>

                            {{-- Kode Barang --}}
                            <td style="padding: 0.85rem 0.75rem; font-family: monospace; font-size: 0.85rem; font-weight: 700; color: #1e293b;">
                                {{ $item->barang_cd }}
                            </td>

                            {{-- Nama Barang & Jenis --}}
                            <td style="padding: 0.85rem 0.75rem;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                                    {{ $item->barang_nm }}
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                    <span style="background: #f1f5f9; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                        {{ $item->jenisBarang?->jenis_barang_nm ?? 'Umum' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Satuan Dasar --}}
                            <td style="padding: 0.85rem 0.75rem; text-align: center; font-weight: 600; color: #475569; font-size: 0.8rem;">
                                {{ $item->satuanDasar?->satuan_nm ?? '-' }}
                            </td>

                            {{-- Safety Stock Minimum --}}
                            <td style="padding: 0.85rem 0.75rem; text-align: right; color: #64748b; font-size: 0.85rem;">
                                {{ $minStok > 0 ? number_format($minStok, 0, ',', '.') : '-' }}
                            </td>

                            {{-- Total Masuk --}}
                            <td style="padding: 0.85rem 0.75rem; text-align: right; color: #334155; font-size: 0.85rem;">
                                {{ number_format($qtyAwal, 2, ',', '.') }}
                            </td>

                            {{-- Total Keluar --}}
                            <td style="padding: 0.85rem 0.75rem; text-align: right; color: #dc2626; font-size: 0.85rem; font-weight: 600;">
                                {{ number_format($qtyKeluar, 2, ',', '.') }}
                            </td>

                            {{-- Sisa Fisik On-Hand --}}
                            <td style="padding: 0.85rem 0.75rem; text-align: right; font-size: 0.95rem; font-weight: 800; color: {{ $isHabis ? '#94a3b8' : ($isMenipis ? '#d97706' : '#059669') }};">
                                {{ number_format($sisaQty, 2, ',', '.') }}
                            </td>

                            {{-- Nilai Persediaan --}}
                            <td style="padding: 0.85rem 0.75rem; text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                Rp {{ number_format($nilaiTotal, 0, ',', '.') }}
                            </td>

                            {{-- Status Badge --}}
                            <td style="padding: 0.85rem 0.75rem; text-align: center;">
                                @if ($isHabis)
                                    <span style="background: #fee2e2; color: #991b1b; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700; text-transform: uppercase;">
                                        Habis
                                    </span>
                                @elseif ($isMenipis)
                                    <span style="background: #fef3c7; color: #92400e; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700; text-transform: uppercase; border: 1px solid #fde68a;">
                                        ⚠️ Menipis
                                    </span>
                                @else
                                    <span style="background: #dcfce7; color: #166534; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700; text-transform: uppercase;">
                                        🟢 Aman
                                    </span>
                                @endif
                            </td>

                            {{-- Batch FIFO Pill / Button --}}
                            <td style="padding: 0.85rem 0.75rem; text-align: center;">
                                @if ($activeBatchCount > 0)
                                    <span style="background: #e0e7ff; color: #3730a3; padding: 0.2rem 0.55rem; border-radius: 12px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <span>🏷️ {{ $activeBatchCount }} Batch</span>
                                    </span>
                                @else
                                    <span style="color: #94a3b8; font-size: 0.75rem;">0 Batch</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td style="padding: 0.85rem 1rem 0.85rem 0.75rem; text-align: right;" onclick="event.stopPropagation();">
                                <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $gudangId]) }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" title="Lihat Kartu Riwayat Mutasi">
                                    Kartu &rarr;
                                </a>
                            </td>
                        </tr>

                        {{-- Level 2 Drawer Row: Sub-Tabel Batch FIFO --}}
                        <tr id="drawer-batch-{{ $item->barang_id }}" style="display: none; background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                            <td colspan="12" style="padding: 1rem 1.25rem 1.25rem 2.5rem;">
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                    <div style="background: #f1f5f9; padding: 0.6rem 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 0.4rem;">
                                            <span>Rincian Batch FIFO untuk:</span>
                                            <span style="color: #0f172a; font-family: monospace;">{{ $item->barang_cd }} - {{ $item->barang_nm }}</span>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b;">
                                            Urutan prioritas pengeluaran bahan: <em>First-Expired / First-In (FEFO/FIFO)</em>
                                        </div>
                                    </div>

                                    @if ($item->stokBatches->count() > 0)
                                        <div style="overflow-x: auto;">
                                            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                                <thead>
                                                    <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                                                        <th style="padding: 0.5rem 0.75rem; text-align: left;">No. Batch</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: left;">Lokasi Gudang</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: left;">Tgl Masuk / Terima</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: left;">Tgl Kadaluarsa (Expired)</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: right;">Qty Awal</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: right;">Qty Keluar</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: right; font-weight: 700;">Sisa Fisik</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: right;">Harga Satuan Beli</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: right; font-weight: 700;">Nilai Batch</th>
                                                        <th style="padding: 0.5rem 0.75rem; text-align: center;">Status Batch</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($item->stokBatches as $batch)
                                                        @php
                                                            $bSisa = (float) $batch->sisa_qty;
                                                            $bAwal = (float) ($batch->qty_awal > 0 ? $batch->qty_awal : $bSisa);
                                                            $bKeluar = max(0, $bAwal - $bSisa);
                                                            $bHarga = (float) $batch->harga_satuan;
                                                            $bNilai = $bSisa * $bHarga;
                                                            $bHabis = $bSisa <= 0;
                                                        @endphp
                                                        <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $bHabis ? '#fafafa' : '#ffffff' }};">
                                                            <td style="padding: 0.5rem 0.75rem;">
                                                                <span style="font-family: monospace; font-size: 0.8rem; font-weight: 700; background: {{ $bHabis ? '#f1f5f9' : '#ecfdf5' }}; color: {{ $bHabis ? '#64748b' : '#065f46' }}; border: 1px solid {{ $bHabis ? '#e2e8f0' : '#a7f3d0' }}; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                                                    {{ $batch->batch_no }}
                                                                </span>
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem; color: #334155;">
                                                                {{ $batch->gudang?->gudang_nm ?? '-' }}
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem; color: #64748b;">
                                                                {{ $batch->created_at ? $batch->created_at->format('d/m/Y H:i') : '-' }}
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem;">
                                                                @if ($batch->expired_tgl)
                                                                    @php
                                                                        $expDate = \Carbon\Carbon::parse($batch->expired_tgl);
                                                                        $isExpired = $expDate->isPast();
                                                                        $isNearExp = !$isExpired && $expDate->diffInDays(now()) <= 30;
                                                                    @endphp
                                                                    <span style="font-weight: 600; color: {{ $isExpired ? '#dc2626' : ($isNearExp ? '#d97706' : '#334155') }};">
                                                                        {{ $expDate->format('d/m/Y') }}
                                                                    </span>
                                                                    @if ($isExpired)
                                                                        <span style="background: #fee2e2; color: #991b1b; padding: 0.1rem 0.35rem; border-radius: 3px; font-size: 0.675rem; font-weight: 700;">EXPIRED</span>
                                                                    @elseif ($isNearExp)
                                                                        <span style="background: #fef3c7; color: #92400e; padding: 0.1rem 0.35rem; border-radius: 3px; font-size: 0.675rem; font-weight: 700;">SEGERA EXP</span>
                                                                    @endif
                                                                @else
                                                                    <span style="color: #94a3b8;">-</span>
                                                                @endif
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem; text-align: right; color: #475569;">
                                                                {{ number_format($bAwal, 2, ',', '.') }}
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem; text-align: right; color: #dc2626; font-weight: 600;">
                                                                {{ number_format($bKeluar, 2, ',', '.') }}
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem; text-align: right; font-weight: 800; font-size: 0.85rem; color: {{ $bHabis ? '#94a3b8' : '#059669' }};">
                                                                {{ number_format($bSisa, 2, ',', '.') }} {{ $item->satuanDasar?->satuan_nm }}
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem; text-align: right; color: #334155;">
                                                                Rp {{ number_format($bHarga, 2, ',', '.') }}
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem; text-align: right; font-weight: 700; color: #0f172a;">
                                                                Rp {{ number_format($bNilai, 2, ',', '.') }}
                                                            </td>
                                                            <td style="padding: 0.5rem 0.75rem; text-align: center;">
                                                                @if ($bHabis)
                                                                    <span style="color: #94a3b8; font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Habis</span>
                                                                @else
                                                                    <span style="background: #059669; color: #ffffff; padding: 0.15rem 0.45rem; border-radius: 3px; font-size: 0.7rem; font-weight: 700;">TERSEDIA</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div style="padding: 1.5rem; text-align: center; color: #94a3b8; font-size: 0.825rem;">
                                            Belum ada catatan nomor batch untuk barang ini di gudang. Silakan lakukan transaksi <strong>Terima Barang Masuk</strong>.
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Tidak ada data barang yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($summaryList->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
                {{ $summaryList->withQueryString()->links() }}
            </div>
        @endif

    {{-- KONTEN MODE 2: DETAIL SHEET PER-BATCH (FLAT FORMAT EXCEL) --}}
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="background: #0f394c; color: #ffffff;">
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Kode Batch</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Kode Barang</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Nama Barang & Gudang</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Qty Awal</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Harga Satuan</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Qty Keluar</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 700; text-align: right;">Sisa Qty</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 700; text-align: right;">Sisa Nilai</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: center;">Status</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stokList as $item)
                        @php
                            $isHabis = (float) $item->sisa_qty <= 0;
                            $qtyAwal = (float) ($item->qty_awal > 0 ? $item->qty_awal : $item->sisa_qty);
                            $qtyKeluar = max(0, $qtyAwal - (float) $item->sisa_qty);
                            $hargaSatuan = (float) $item->harga_satuan;
                            $sisaNilai = (float) $item->sisa_qty * $hargaSatuan;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $isHabis ? '#fafafa' : '#ffffff' }};">
                            <td style="padding: 0.75rem 1rem;">
                                <span style="font-family: monospace; font-size: 0.85rem; background: {{ $isHabis ? '#fee2e2' : '#f0fdf4' }}; color: {{ $isHabis ? '#991b1b' : '#166534' }}; border: 1px solid {{ $isHabis ? '#fecaca' : '#bbf7d0' }}; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700;">
                                    {{ $item->batch_no }}
                                </span>
                                @if ($item->expired_tgl)
                                    <span style="display: block; font-size: 0.725rem; color: #64748b; margin-top: 0.2rem;">
                                        Exp: {{ \Carbon\Carbon::parse($item->expired_tgl)->format('d/m/Y') }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; font-family: monospace; font-size: 0.85rem; font-weight: 600; color: #475569;">
                                {{ $item->barang?->barang_cd ?? '-' }}
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <strong style="color: #0f172a;">{{ $item->barang?->barang_nm ?? '-' }}</strong>
                                <span style="display: block; font-size: 0.75rem; color: #64748b;">
                                    {{ $item->gudang?->gudang_nm }} • {{ $item->barang?->satuanDasar?->satuan_nm }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #334155;">
                                {{ number_format($qtyAwal, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #334155; font-size: 0.875rem;">
                                Rp {{ number_format($hargaSatuan, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #dc2626;">
                                {{ number_format($qtyKeluar, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; font-size: 0.95rem; color: {{ $isHabis ? '#94a3b8' : '#059669' }};">
                                {{ number_format((float) $item->sisa_qty, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                                Rp {{ number_format($sisaNilai, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                @if ($isHabis)
                                    <span style="background: #dc2626; color: #ffffff; padding: 0.25rem 0.65rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                        Habis
                                    </span>
                                @else
                                    <span style="background: #059669; color: #ffffff; padding: 0.25rem 0.65rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                        Tersedia
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $item->gudang_id]) }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" title="Lihat Kartu Riwayat Mutasi">
                                    Kartu Stok &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Tidak ada data batch stok yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stokList->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
                {{ $stokList->withQueryString()->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Interactive Accordion Script --}}
<script>
function toggleBatchRow(barangId) {
    const drawer = document.getElementById('drawer-batch-' + barangId);
    const chevron = document.getElementById('icon-chevron-' + barangId);
    
    if (!drawer) return;

    if (drawer.style.display === 'none' || drawer.style.display === '') {
        drawer.style.display = 'table-row';
        if (chevron) {
            chevron.style.transform = 'rotate(90deg)';
            chevron.style.color = '#0284c7';
        }
    } else {
        drawer.style.display = 'none';
        if (chevron) {
            chevron.style.transform = 'rotate(0deg)';
            chevron.style.color = '#64748b';
        }
    }
}

function toggleAllBatches(expand) {
    const drawers = document.querySelectorAll('[id^="drawer-batch-"]');
    const chevrons = document.querySelectorAll('[id^="icon-chevron-"]');
    
    drawers.forEach(drawer => {
        drawer.style.display = expand ? 'table-row' : 'none';
    });

    chevrons.forEach(chevron => {
        chevron.style.transform = expand ? 'rotate(90deg)' : 'rotate(0deg)';
        chevron.style.color = expand ? '#0284c7' : '#64748b';
    });
}
</script>
@endsection
