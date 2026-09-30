@extends('layouts.app')

@section('title', 'PO Penjualan - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Pesan Order (PO) Penjualan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Kelola pesanan penjualan produk hasil produksi (barang jadi &amp; barang setengah jadi) ke pelanggan dan mitra distributor.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('penjualan.so.create') }}" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Buat PO Penjualan
        </a>
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL PENJUALAN --}}
@php
    $currentStatus = $status ?? '';
    $hasActiveFilter = !empty($filters['barang_cd']) || !empty($filters['barang_nm']) || !empty($filters['customer_cd']) || !empty($filters['customer_nm']);
@endphp
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- SEMUA PO PENJUALAN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    Semua Dokumen PO
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($statusCounts['all'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #475569;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total transaksi pesanan penjualan aktif
        </div>
    </div>

    {{-- MENUNGGU PENGIRIMAN / APPROVED --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Menunggu Pengiriman
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($statusCounts['approved'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            PO disetujui &bull; Siap dijadwalkan kirim
        </div>
    </div>

    {{-- DALAM PROSES / SEBAGIAN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706;">
                    Dalam Proses / Parsial
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($statusCounts['partial'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM4 17h1m4 0h6m4 0h1m-1-4V6a1 1 0 00-1-1H4a1 1 0 00-1 1v7m14 0h3l2 3v1a1 1 0 01-1 1h-1m-17 0H3a1 1 0 01-1-1v-1l2-3h12"/>
                </svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Sedang disiapkan / dikirim armada
        </div>
    </div>

    {{-- TOTAL OMZET PENJUALAN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Total Nilai Omzet
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                    Rp {{ number_format($statusCounts['total_omzet'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Akumulasi transaksi resmi disetujui
        </div>
    </div>
</div>

{{-- KARTU TABEL UTAMA DENGAN SEARCH & FILTER SESUAI STANDAR --}}
<div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    {{-- BARIS UTAMA FILTER & SEARCH --}}
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('penjualan.so.index') }}" method="GET" id="searchFilterForm" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; flex: 1;">
            {{-- Search: (Kode Pesanan / Supplier / Customer / Nama Barang) --}}
            <div style="position: relative; flex: 1; min-width: 260px; max-width: 380px;">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Search : (Kode Pesanan / Customer / Nama Barang)" 
                       class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%; box-sizing: border-box;">
            </div>
            
            <select name="status" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 175px; font-size: 0.85rem;" onchange="document.getElementById('searchFilterForm').submit()">
                <option value="">-- Semua Status --</option>
                <option value="APPROVED" {{ ($currentStatus == 'APPROVED') ? 'selected' : '' }}>Disetujui (Approved)</option>
                <option value="PROCESSING" {{ ($currentStatus == 'PROCESSING') ? 'selected' : '' }}>Diproses (Processing)</option>
                <option value="PARTIAL" {{ ($currentStatus == 'PARTIAL') ? 'selected' : '' }}>Sebagian Terkirim</option>
                <option value="COMPLETED" {{ ($currentStatus == 'COMPLETED') ? 'selected' : '' }}>Selesai (Completed)</option>
                <option value="DRAFT" {{ ($currentStatus == 'DRAFT') ? 'selected' : '' }}>Draft</option>
                <option value="CANCELLED" {{ ($currentStatus == 'CANCELLED') ? 'selected' : '' }}>Dibatalkan</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Cari</button>

            {{-- Tombol Toggle Filter Spesifik (Kode Barang, Nama Barang, Kode Customer, Nama Customer) --}}
            <button type="button" onclick="toggleFilterDrawer()" class="btn btn-sm" style="background: {{ $hasActiveFilter ? '#e0f2fe' : '#f1f5f9' }}; color: {{ $hasActiveFilter ? '#0284c7' : '#475569' }}; border: 1px solid {{ $hasActiveFilter ? '#bae6fd' : '#cbd5e1' }}; padding: 0.45rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>Filter Spesifik</span>
                @if($hasActiveFilter)
                    <span style="background: #0284c7; color: white; border-radius: 9999px; font-size: 0.65rem; padding: 1px 5px;">Aktif</span>
                @endif
            </button>

            @if(!empty($search) || !empty($currentStatus) || $hasActiveFilter)
                <a href="{{ route('penjualan.so.index') }}" class="btn btn-secondary btn-sm" title="Reset Semua Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif

            {{-- Hidden input sync untuk drawer jika submit dari form atas --}}
            <input type="hidden" name="barang_cd" id="sync_barang_cd" value="{{ $filters['barang_cd'] ?? '' }}">
            <input type="hidden" name="barang_nm" id="sync_barang_nm" value="{{ $filters['barang_nm'] ?? '' }}">
            <input type="hidden" name="customer_cd" id="sync_customer_cd" value="{{ $filters['customer_cd'] ?? '' }}">
            <input type="hidden" name="customer_nm" id="sync_customer_nm" value="{{ $filters['customer_nm'] ?? '' }}">
        </form>

        <span style="color: #64748b; font-size: 0.85rem;">
            Total PO: <strong style="color: #0f172a;">{{ $orders->total() }}</strong> dokumen
        </span>
    </div>

    {{-- FILTER DRAWER: 4 FILTER SESUAI STANDAR GAMBAR --}}
    {{-- (Kode Barang, Nama Barang, Kode Customer, Nama Customer) --}}
    <div id="filterDrawer" style="display: {{ $hasActiveFilter ? 'block' : 'none' }}; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem;">
        <div style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            Filter Spesifik Pesanan Penjualan
        </div>
        <form action="{{ route('penjualan.so.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            @if(!empty($search))
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            @if(!empty($currentStatus))
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif

            {{-- 1. Kode Barang --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem; display: block;">
                    Kode Barang
                </label>
                <input type="text" name="barang_cd" value="{{ $filters['barang_cd'] ?? '' }}" placeholder="Contoh: FG-KRP-001..." class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.65rem;">
            </div>

            {{-- 2. Nama Barang --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem; display: block;">
                    Nama Barang
                </label>
                <input type="text" name="barang_nm" value="{{ $filters['barang_nm'] ?? '' }}" placeholder="Contoh: Keripik Singkong..." class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.65rem;">
            </div>

            {{-- 3. Kode Customer --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem; display: block;">
                    Kode Customer
                </label>
                <input type="text" name="customer_cd" value="{{ $filters['customer_cd'] ?? '' }}" placeholder="Contoh: CUST-001..." class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.65rem;">
            </div>

            {{-- 4. Nama Customer --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem; display: block;">
                    Nama Customer
                </label>
                <input type="text" name="customer_nm" value="{{ $filters['customer_nm'] ?? '' }}" placeholder="Contoh: Indofood..." class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.65rem;">
            </div>

            {{-- Tombol Submit Filter Spesifik --}}
            <div style="display: flex; gap: 0.4rem;">
                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.4rem 0.85rem; font-size: 0.8rem;">
                    Terapkan Filter
                </button>
                <a href="{{ route('penjualan.so.index', ['search' => $search, 'status' => $currentStatus]) }}" class="btn btn-secondary btn-sm" style="padding: 0.4rem 0.65rem; font-size: 0.8rem;">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- TABEL INPUTAN / DAFTAR TRANSAKSI PO PENJUALAN --}}
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="min-width: 180px;">Kode Pesanan</th>
                    <th style="min-width: 110px;">Tanggal</th>
                    <th style="min-width: 180px;">Nama Customer</th>
                    <th style="min-width: 140px;">Kode Customer</th>
                    <th style="min-width: 170px;">Status &amp; Realisasi</th>
                    <th style="min-width: 130px; text-align: right;">Total Tagihan</th>
                    <th style="min-width: 240px; text-align: right;">Aksi &amp; Export Dokumen</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $index => $so)
                    @php
                        $itemCount = $so->details->count();
                        $totalQty = (float) $so->details->sum('pesan_qty');
                        $statusCd = $so->status_cd;
                        
                        $pct = match($statusCd) {
                            'COMPLETED' => 100,
                            'PARTIAL'   => 50,
                            'PROCESSING'=> 25,
                            'APPROVED'  => 10,
                            default     => 0
                        };
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                            {{ $orders->firstItem() + $index }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                {{-- Tombol expand preview item tanpa pindah halaman --}}
                                <button type="button" 
                                        onclick="toggleSoRow('row-items-{{ $so->so_id }}', this)" 
                                        title="Intip rincian produk yang dipesan"
                                        style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; padding: 0; flex-shrink: 0;">
                                    <svg class="chevron-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: transform 0.15s ease;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <div>
                                    <a href="{{ route('penjualan.so.show', $so->so_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                        {{ $so->so_no }}
                                    </a>
                                    <small style="display: block; font-size: 0.725rem; color: #64748b;">
                                        {{ $itemCount }} item produk &bull; {{ number_format($totalQty, 0) }} unit
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155; font-size: 0.85rem;">
                                {{ \Carbon\Carbon::parse($so->so_tgl)->format('d/m/Y') }}
                            </div>
                            @if ($so->tgl_kirim_estimasi)
                                <small style="display: block; font-size: 0.725rem; color: #0369a1;">
                                    Kirim: {{ \Carbon\Carbon::parse($so->tgl_kirim_estimasi)->format('d/m/Y') }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.875rem;">{{ $so->customer?->customer_nm ?? '-' }}</strong>
                            @if($so->customer_po_no)
                                <small style="display: block; font-size: 0.725rem; color: #64748b;">Ref PO: {{ $so->customer_po_no }}</small>
                            @endif
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.825rem; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #e0f2fe;">
                                {{ $so->customer?->customer_cd ?? '-' }}
                            </span>
                        </td>
                        <td>
                            {{-- BADGE STATUS & PROGRESS BAR --}}
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <div>
                                    @if ($statusCd == 'COMPLETED')
                                        <span class="badge badge-success">Selesai (100%)</span>
                                    @elseif ($statusCd == 'PARTIAL')
                                        <span class="badge badge-info">Sebagian Kirim (50%)</span>
                                    @elseif ($statusCd == 'PROCESSING')
                                        <span class="badge" style="background:#e0f2fe; color:#0369a1;">Diproses</span>
                                    @elseif ($statusCd == 'APPROVED')
                                        <span class="badge" style="background:#dbeafe; color:#1d4ed8;">Disetujui</span>
                                    @elseif ($statusCd == 'DRAFT')
                                        <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
                                    @else
                                        <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
                                    @endif
                                </div>

                                {{-- Progress Bar Ramping --}}
                                @php
                                    $barBg = '#e2e8f0';
                                    $barFill = '#0284c7';
                                    if ($statusCd == 'COMPLETED') {
                                        $barFill = '#10b981';
                                    } elseif ($statusCd == 'PARTIAL') {
                                        $barFill = '#f59e0b';
                                    } elseif ($statusCd == 'CANCELLED') {
                                        $barFill = '#ef4444';
                                    }
                                @endphp
                                <div style="width: 100%; max-width: 140px; height: 5px; background: {{ $barBg }}; border-radius: 9999px; overflow: hidden; margin-top: 2px;">
                                    <div style="width: {{ $pct }}%; height: 100%; background: {{ $barFill }}; border-radius: 9999px;"></div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                            Rp {{ number_format((float) $so->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem; align-items: center; flex-wrap: wrap; justify-content: flex-end;">
                                {{-- Tombol Detail --}}
                                <a href="{{ route('penjualan.so.show', $so->so_id) }}" class="btn btn-secondary btn-sm" title="Lihat Detail Dokumen PO">
                                    Detail
                                </a>

                                {{-- d. Export Faktur --}}
                                <a href="{{ route('penjualan.so.export-faktur', $so->so_id) }}" target="_blank" class="btn btn-sm" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 600;" title="Export Faktur Penjualan (PDF)">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    Faktur
                                </a>

                                {{-- e. Export Surat Jalan --}}
                                <a href="{{ route('penjualan.so.export-surat-jalan', $so->so_id) }}" target="_blank" class="btn btn-sm" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 600;" title="Export Surat Jalan Pengiriman (PDF)">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    Surat Jalan
                                </a>
                            </div>
                        </td>
                    </tr>

                    {{-- EXPANDABLE SUB-ROW: RINCIAN ITEM BARANG HASIL PRODUKSI --}}
                    <tr id="row-items-{{ $so->so_id }}" style="display: none; background: #f8fafc;">
                        <td colspan="8" style="padding: 0.75rem 1.25rem; border-bottom: 2px solid #e2e8f0;">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <strong style="font-size: 0.825rem; color: #334155;">
                                        Rincian Produk Pesanan ({{ $so->so_no }}):
                                    </strong>
                                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                                        <a href="{{ route('penjualan.so.export-faktur', $so->so_id) }}" target="_blank" style="font-size: 0.75rem; color: #0284c7; text-decoration: none; font-weight: 600;">
                                            📄 Export Faktur (PDF)
                                        </a>
                                        <a href="{{ route('penjualan.so.export-surat-jalan', $so->so_id) }}" target="_blank" style="font-size: 0.75rem; color: #16a34a; text-decoration: none; font-weight: 600;">
                                            🚚 Export Surat Jalan (PDF)
                                        </a>
                                        <a href="{{ route('penjualan.so.show', $so->so_id) }}" style="font-size: 0.75rem; color: #64748b; text-decoration: none;">
                                            Buka detail penuh &rarr;
                                        </a>
                                    </div>
                                </div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Jenis Barang</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Nama Barang</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Kode Barang</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Jumlah Pesanan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Satuan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">@Harga Satuan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Diskon %</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Potongan Harga</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: center;">PPN</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Total Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($so->details as $item)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 0.4rem 0.5rem; color: #475569;">
                                                    <span class="badge" style="background:#f1f5f9; color:#334155; font-size:0.7rem;">
                                                        {{ $item->barang?->jenisBarang?->jenis_barang_nm ?? ($item->barang?->jenis_barang_cd ?? 'FG') }}
                                                    </span>
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem;">
                                                    <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; font-family: monospace; color: #64748b;">
                                                    {{ $item->barang?->barang_cd }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600;">
                                                    {{ number_format((float) $item->pesan_qty, 0) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; color: #475569;">
                                                    {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; color: #475569; font-family: monospace;">
                                                    Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; color: {{ (float) $item->diskon_persen > 0 ? '#d97706' : '#94a3b8' }};">
                                                    {{ (float) $item->diskon_persen > 0 ? number_format($item->diskon_persen, 1) . '%' : '-' }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; color: {{ (float) $item->potongan_nominal > 0 ? '#dc2626' : '#94a3b8' }};">
                                                    {{ (float) $item->potongan_nominal > 0 ? 'Rp ' . number_format($item->potongan_nominal, 0, ',', '.') : '-' }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: center;">
                                                    @if($item->ppn_tipe === 'PPN_11')
                                                        <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:0.7rem; font-weight:700;">PPN 11%</span>
                                                    @else
                                                        <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:0.7rem;">Non PPN</span>
                                                    @endif
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: #0f172a; font-family: monospace;">
                                                    Rp {{ number_format((float) $item->subtotal_tagihan, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada dokumen PO Penjualan. Klik tombol <strong>"+ Buat PO Penjualan"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>

<script>
    function toggleSoRow(rowId, btn) {
        const row = document.getElementById(rowId);
        if (!row) return;

        const isHidden = (row.style.display === 'none' || row.style.display === '');
        row.style.display = isHidden ? 'table-row' : 'none';

        const icon = btn.querySelector('.chevron-icon');
        if (icon) {
            icon.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
        }
    }

    function toggleFilterDrawer() {
        const drawer = document.getElementById('filterDrawer');
        if (!drawer) return;
        const isHidden = (drawer.style.display === 'none' || drawer.style.display === '');
        drawer.style.display = isHidden ? 'block' : 'none';
    }
</script>
@endsection
