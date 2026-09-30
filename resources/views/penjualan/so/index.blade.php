@extends('layouts.app')

@section('title', 'PO Penjualan (Sales Order) - ERP PT Mirasa')

@section('content')
<div class="page-container">
    {{-- Top Action & Header Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700; font-size: 0.75rem;">Modul Penjualan</span>
                <span style="color: #94a3b8; font-size: 0.8rem;">•</span>
                <span style="color: #64748b; font-size: 0.8rem; font-weight: 600;">Standar Format ERP</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                Pesan Order (PO) Penjualan
            </h1>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.2rem 0 0 0;">
                Kelola pesanan penjualan ke customer, kalkulasi otomatis diskon &amp; PPN 11%, serta cetak Faktur &amp; Surat Jalan.
            </p>
        </div>

        <div style="display: flex; gap: 0.65rem; align-items: center;">
            <a href="{{ route('penjualan.so.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.15rem; font-weight: 700; border-radius: 8px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat PO Penjualan</span>
            </a>
        </div>
    </div>

    {{-- KPI Metric Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="width: 44px; height: 44px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Total PO Penjualan</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a;">{{ number_format($kpi['total_so']) }} <span style="font-size: 0.8rem; font-weight: 500; color: #94a3b8;">dokumen</span></div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="width: 44px; height: 44px; border-radius: 8px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">SO Bulan Ini</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a;">{{ number_format($kpi['so_bulan_ini']) }} <span style="font-size: 0.8rem; font-weight: 500; color: #94a3b8;">pesanan</span></div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="width: 44px; height: 44px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Perlu Dikirim</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a;">{{ number_format($kpi['so_pending']) }} <span style="font-size: 0.8rem; font-weight: 500; color: #94a3b8;">pesanan</span></div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="width: 44px; height: 44px; border-radius: 8px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Total Nilai Omzet</div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a;">Rp {{ number_format($kpi['total_omzet'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="card" style="margin-bottom: 1.5rem; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div style="padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="16" height="16" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span style="font-weight: 700; font-size: 0.875rem; color: #1e293b;">Pencarian &amp; Multi-Filter Pesanan Penjualan</span>
            </div>
            @if(!empty($search) || !empty($status) || array_filter($filters))
                <a href="{{ route('penjualan.so.index') }}" style="font-size: 0.8rem; color: #ef4444; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Reset Filter</span>
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('penjualan.so.index') }}" style="padding: 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                {{-- Search Utama --}}
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                        Pencarian Utama (Kode PO / Customer / Barang)
                    </label>
                    <div style="position: relative;">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Ketik nomor SO, nama customer, atau nama barang..." style="padding-left: 2.25rem; font-size: 0.85rem;">
                        <div style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Filter Customer --}}
                <div>
                    <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                        Pilih Customer
                    </label>
                    <select name="customer_id" class="form-control" style="font-size: 0.85rem;">
                        <option value="">-- Semua Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->customer_id }}" {{ ($filters['customer_id'] ?? '') == $c->customer_id ? 'selected' : '' }}>
                                [{{ $c->customer_cd }}] {{ $c->customer_nm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Produk Hasil Produksi (FG / WIP) --}}
                <div>
                    <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                        Pilih Produk (FG / WIP)
                    </label>
                    <select name="barang_id" class="form-control" style="font-size: 0.85rem;">
                        <option value="">-- Semua Produk Hasil Produksi --</option>
                        @foreach($barangs as $b)
                            <option value="{{ $b->barang_id }}" {{ ($filters['barang_id'] ?? '') == $b->barang_id ? 'selected' : '' }}>
                                [{{ $b->jenisBarang?->jenis_barang_cd ?? 'FG' }}] {{ $b->barang_nm }} ({{ $b->barang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status --}}
                <div>
                    <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                        Status Pesanan
                    </label>
                    <select name="status" class="form-control" style="font-size: 0.85rem;">
                        <option value="">-- Semua Status --</option>
                        <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>Disetujui (Approved)</option>
                        <option value="PROCESSING" {{ $status === 'PROCESSING' ? 'selected' : '' }}>Dalam Proses (Processing)</option>
                        <option value="PARTIAL" {{ $status === 'PARTIAL' ? 'selected' : '' }}>Terkirim Sebagian</option>
                        <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>Selesai Terkirim</option>
                        <option value="CANCELLED" {{ $status === 'CANCELLED' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                {{-- Tanggal Mulai --}}
                <div>
                    <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                        Dari Tanggal
                    </label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control" style="font-size: 0.85rem;">
                </div>

                {{-- Tanggal Sampai --}}
                <div>
                    <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                        Sampai Tanggal
                    </label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control" style="font-size: 0.85rem;">
                </div>

                {{-- Tombol Filter --}}
                <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.55rem; font-weight: 700; font-size: 0.85rem; border-radius: 6px;">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Data Table Card --}}
    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="overflow-x: auto;">
            <table class="table" style="margin: 0; width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="background: #0f172a; color: #f8fafc; text-align: left;">
                        <th style="padding: 0.75rem 1rem; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em;">Tanggal</th>
                        <th style="padding: 0.75rem 1rem; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em;">Kode Pesanan</th>
                        <th style="padding: 0.75rem 1rem; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em;">Customer</th>
                        <th style="padding: 0.75rem 1rem; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em;">Rincian Barang</th>
                        <th style="padding: 0.75rem 1rem; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; text-align: right;">Total Tagihan</th>
                        <th style="padding: 0.75rem 1rem; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; text-align: center;">Status</th>
                        <th style="padding: 0.75rem 1rem; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; text-align: center;">Aksi &amp; Cetak</th>
                    </tr>
                </thead>
                <tbody style="divide-y: 1px solid #f1f5f9;">
                    @forelse($orders as $so)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            {{-- Tanggal --}}
                            <td style="padding: 0.85rem 1rem; vertical-align: top; white-space: nowrap;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $so->so_tgl?->format('d/m/Y') }}</div>
                                @if($so->tgl_kirim_estimasi)
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                        Kirim: {{ $so->tgl_kirim_estimasi->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Kode SO & PO Customer --}}
                            <td style="padding: 0.85rem 1rem; vertical-align: top; white-space: nowrap;">
                                <a href="{{ route('penjualan.so.show', $so->so_id) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                    {{ $so->so_no }}
                                </a>
                                @if($so->customer_po_no)
                                    <div style="font-size: 0.725rem; color: #475569; margin-top: 2px;">
                                        PO Cust: <strong>{{ $so->customer_po_no }}</strong>
                                    </div>
                                @endif
                                <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 2px;">
                                    Faktur: {{ $so->faktur_no ?? '-' }}
                                </div>
                            </td>

                            {{-- Customer --}}
                            <td style="padding: 0.85rem 1rem; vertical-align: top;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $so->customer?->customer_nm ?? '-' }}</div>
                                <div style="display: flex; gap: 0.35rem; align-items: center; margin-top: 2px;">
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.675rem; font-weight: 700;">{{ $so->customer?->customer_cd ?? '-' }}</span>
                                    @if($so->customer?->kontak_no)
                                        <span style="font-size: 0.725rem; color: #64748b;">{{ $so->customer->kontak_no }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Items Summary --}}
                            <td style="padding: 0.85rem 1rem; vertical-align: top;">
                                <div style="font-size: 0.8rem; color: #334155;">
                                    @foreach($so->details->take(2) as $dtl)
                                        <div style="margin-bottom: 2px;">
                                            <strong style="color: #0f172a;">{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                                            <span style="color: #64748b;">({{ number_format($dtl->pesan_qty, 0, ',', '.') }} {{ $dtl->barang?->satuanDasar?->satuan_nm ?? 'Unit' }})</span>
                                        </div>
                                    @endforeach
                                    @if($so->details->count() > 2)
                                        <span style="font-size: 0.725rem; color: #0284c7; font-weight: 600;">+ {{ $so->details->count() - 2 }} barang lainnya</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Total Tagihan --}}
                            <td style="padding: 0.85rem 1rem; vertical-align: top; text-align: right; white-space: nowrap;">
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                                    Rp {{ number_format($so->total_tagihan, 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                    @if($so->ppn_tipe === 'PPN_11')
                                        <span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.65rem;">PPN 11%</span>
                                    @elseif($so->ppn_tipe === 'MIXED')
                                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.65rem;">PPN Campuran</span>
                                    @else
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.65rem;">Non PPN</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td style="padding: 0.85rem 1rem; vertical-align: top; text-align: center; white-space: nowrap;">
                                {!! $so->status_badge !!}
                            </td>

                            {{-- Aksi & Export PDF --}}
                            <td style="padding: 0.85rem 1rem; vertical-align: top; text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                    {{-- Detail --}}
                                    <a href="{{ route('penjualan.so.show', $so->so_id) }}" class="btn btn-sm btn-outline-secondary" title="Lihat Detail Pesanan" style="padding: 0.35rem 0.6rem; border-radius: 6px;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    {{-- Cetak Faktur PDF --}}
                                    <a href="{{ route('penjualan.so.export-faktur', $so->so_id) }}" target="_blank" class="btn btn-sm btn-primary" title="Export Faktur Penjualan (PDF)" style="padding: 0.35rem 0.65rem; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span>Faktur</span>
                                    </a>

                                    {{-- Cetak Surat Jalan PDF --}}
                                    <a href="{{ route('penjualan.so.export-surat-jalan', $so->so_id) }}" target="_blank" class="btn btn-sm btn-success" title="Export Surat Jalan (PDF)" style="padding: 0.35rem 0.65rem; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                        <span>Surat Jalan</span>
                                    </a>

                                    {{-- Edit --}}
                                    @if(!in_array($so->status_cd, ['COMPLETED', 'CANCELLED']))
                                        <a href="{{ route('penjualan.so.edit', $so->so_id) }}" class="btn btn-sm btn-outline-primary" title="Edit Pesanan" style="padding: 0.35rem 0.6rem; border-radius: 6px;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 3rem 1rem; text-align: center;">
                                <div style="max-width: 360px; margin: 0 auto; color: #64748b;">
                                    <svg width="48" height="48" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24" style="margin: 0 auto 0.75rem auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 1rem; margin-bottom: 0.25rem;">Belum Ada Dokumen PO Penjualan</div>
                                    <p style="font-size: 0.825rem; margin: 0 0 1rem 0;">Mulai catat transaksi pesanan penjualan customer untuk mengaktifkan pemrosesan faktur &amp; surat jalan.</p>
                                    <a href="{{ route('penjualan.so.create') }}" class="btn btn-primary" style="font-size: 0.825rem; font-weight: 700; border-radius: 6px;">
                                        + Buat PO Penjualan Baru
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div style="padding: 1rem; border-top: 1px solid #f1f5f9; background: #ffffff;">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
