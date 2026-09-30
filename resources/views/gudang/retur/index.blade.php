@extends('layouts.app')

@section('title', 'Retur Pembelian (Pengembalian Barang ke Supplier) - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Retur Pembelian (Outbound Retur)</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Buku catatan fisik pengembalian bahan baku &amp; bahan penolong cacat mutu / reject ke pihak supplier (Pemotongan Stok &amp; Penyesuaian PO/Tagihan).
        </p>
    </div>
    <div>
        @if (Auth::user()?->canCreateRetur())
            <a href="{{ route('gudang.retur.create') }}" class="btn btn-primary" style="background: #dc2626;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Retur Pembelian
            </a>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL (SERAGAM DENGAN PO, BARANG MASUK & PEMAKAIAN) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    {{-- 1. SEMUA DOKUMEN RETUR --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    Semua Dokumen Retur
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpis['total'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #475569; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total surat jalan pengembalian fisik
        </div>
    </div>

    {{-- 2. RETUR HARI INI --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Retur Hari Ini
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpis['today'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Barang ditarik ke supplier hari ini
        </div>
    </div>

    {{-- 3. TOTAL ITEM BAHAN DIRETUR --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706;">
                    Total Item Diretur
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($ringkasan['total_item_count'] ?? 0) }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Rincian</span>
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            {{ number_format($ringkasan['singkong_qty'] ?? 0, 0, ',', '.') }} kg singkong &bull; {{ number_format($ringkasan['bumbu_minyak_qty'] ?? 0, 0, ',', '.') }} kg penolong
        </div>
    </div>

    {{-- 4. TOTAL NILAI RETUR (RP) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #dc2626;">
                    Total Nilai Retur
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem; font-family: monospace;">
                    Rp {{ number_format($ringkasan['grand_total_nilai'] ?? ($kpis['total_nominal'] ?? 0), 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            {{ $kpis['replace'] }} minta kirim ulang &bull; {{ $kpis['credit_note'] }} potong tagihan
        </div>
    </div>
</div>

{{-- TAB FILTER KATEGORI BAHAN (ENTERPRISE SEGMENTED CONTROL INDUSTRIAL ERP) --}}
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
    <div style="display: inline-flex; align-items: center; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); flex-wrap: wrap; gap: 0.25rem;">
        
        {{-- Semua Bahan --}}
        <a href="{{ route('gudang.retur.index', array_merge(request()->except('kategori', 'page'), ['kategori' => null])) }}" 
           style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s; {{ empty($kategori) ? 'background: #0f172a; color: #ffffff;' : 'background: transparent; color: #475569;' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Semua Bahan</span>
            <span style="font-size: 0.725rem; font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 9999px; {{ empty($kategori) ? 'background: rgba(255,255,255,0.2); color: #ffffff;' : 'background: #f1f5f9; color: #64748b;' }}">
                {{ number_format($ringkasan['total_item_count'] ?? 0) }}
            </span>
        </a>

        {{-- Bahan Baku (Singkong) --}}
        <a href="{{ route('gudang.retur.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'BAHAN_BAKU'])) }}" 
           style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s; {{ $kategori === 'BAHAN_BAKU' ? 'background: #0f172a; color: #ffffff;' : 'background: transparent; color: #475569;' }}">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #d97706; display: inline-block;"></span>
            <span>Bahan Baku (Singkong)</span>
            <span style="font-size: 0.725rem; font-weight: 700; font-family: monospace; padding: 0.1rem 0.45rem; border-radius: 9999px; {{ $kategori === 'BAHAN_BAKU' ? 'background: rgba(255,255,255,0.2); color: #ffffff;' : 'background: #fef3c7; color: #92400e;' }}">
                {{ number_format($ringkasan['singkong_qty'] ?? 0, 0, ',', '.') }} kg
            </span>
        </a>

        {{-- Bahan Penolong (Bumbu & Minyak) --}}
        <a href="{{ route('gudang.retur.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'BAHAN_PENOLONG'])) }}" 
           style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s; {{ $kategori === 'BAHAN_PENOLONG' ? 'background: #0f172a; color: #ffffff;' : 'background: transparent; color: #475569;' }}">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #059669; display: inline-block;"></span>
            <span>Bahan Penolong &amp; Bumbu</span>
            <span style="font-size: 0.725rem; font-weight: 700; font-family: monospace; padding: 0.1rem 0.45rem; border-radius: 9999px; {{ $kategori === 'BAHAN_PENOLONG' ? 'background: rgba(255,255,255,0.2); color: #ffffff;' : 'background: #ecfdf5; color: #047857;' }}">
                {{ number_format($ringkasan['bumbu_minyak_qty'] ?? 0, 0, ',', '.') }} kg
            </span>
        </a>

        {{-- Kemasan & Packaging --}}
        <a href="{{ route('gudang.retur.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'KEMASAN'])) }}" 
           style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s; {{ $kategori === 'KEMASAN' ? 'background: #0f172a; color: #ffffff;' : 'background: transparent; color: #475569;' }}">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #6366f1; display: inline-block;"></span>
            <span>Kemasan &amp; Dus</span>
            <span style="font-size: 0.725rem; font-weight: 700; font-family: monospace; padding: 0.1rem 0.45rem; border-radius: 9999px; {{ $kategori === 'KEMASAN' ? 'background: rgba(255,255,255,0.2); color: #ffffff;' : 'background: #eef2ff; color: #4338ca;' }}">
                {{ number_format($ringkasan['kemasan_qty'] ?? 0, 0, ',', '.') }} unit
            </span>
        </a>
    </div>

    @if (!empty($kategori))
        <a href="{{ route('gudang.retur.index', array_merge(request()->except('kategori', 'page'), ['kategori' => null])) }}" 
           style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.65rem; border-radius: 6px; background: #f1f5f9; border: 1px solid #e2e8f0;">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>Reset Kategori</span>
        </a>
    @endif
</div>

{{-- WADAH TABEL UTAMA (PERSIS FORMAT CARD PO, BARANG MASUK & PEMAKAIAN) --}}
<div class="card" style="border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; padding: 0;">
    
    {{-- BARIS 1: PERSPEKTIF VIEW SWITCHER & TOTAL HASIL --}}
    <div style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        {{-- View Switcher Tab --}}
        <div style="display: inline-flex; background: #f1f5f9; padding: 0.2rem; border-radius: 6px; border: 1px solid #e2e8f0;">
            <a href="{{ route('gudang.retur.index', array_merge(request()->query(), ['view' => 'item'])) }}" 
               style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; background: {{ $viewType === 'item' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'item' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'item' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Buku Rekap Retur (Excel Grid)
            </a>
            <a href="{{ route('gudang.retur.index', array_merge(request()->query(), ['view' => 'header'])) }}" 
               style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; background: {{ $viewType === 'header' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'header' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'header' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Daftar Dokumen Surat Jalan Retur
            </a>
        </div>

        <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; color: #64748b;">
            <span>Total Hasil:</span>
            <strong style="color: #0f172a; font-family: monospace; font-size: 0.95rem;">{{ $dataList->total() }}</strong>
            <span>{{ $viewType === 'item' ? 'baris data item' : 'dokumen' }}</span>
        </div>
    </div>

    {{-- BARIS 2: TOOLBAR FILTER LENGKAP & TERPADU --}}
    <div style="background: #f8fafc; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
        <form action="{{ route('gudang.retur.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="view" value="{{ $viewType }}">
            <input type="hidden" name="kategori" value="{{ $kategori ?? '' }}">
            
            {{-- 1. Input Pencarian --}}
            <div style="flex: 1 1 200px; min-width: 170px;">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Cari No Retur, Batch, Supplier, Cacat..." 
                       class="form-control" 
                       style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;">
            </div>

            {{-- 2. Dropdown Filter Barang --}}
            <div style="flex: 1 1 180px; min-width: 160px;">
                <select name="barang_id" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;">
                    <option value="">-- Semua Barang --</option>
                    @foreach ($barangList as $b)
                        <option value="{{ $b->barang_id }}" {{ (string) $barangId === (string) $b->barang_id ? 'selected' : '' }}>
                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Dropdown Filter Supplier --}}
            <div style="flex: 1 1 180px; min-width: 150px;">
                <select name="supplier_id" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;">
                    <option value="">-- Semua Supplier --</option>
                    @foreach ($suppliers as $sup)
                        <option value="{{ $sup->supplier_id }}" {{ (string) $supplierId === (string) $sup->supplier_id ? 'selected' : '' }}>
                            {{ $sup->supplier_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 4. Dropdown Tindakan Retur --}}
            <div style="flex: 1 1 160px; min-width: 140px;">
                <select name="tindakan" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;">
                    <option value="">-- Semua Tindakan --</option>
                    <option value="REPLACE" {{ $tindakan === 'REPLACE' ? 'selected' : '' }}>🔄 Minta Kirim Ulang</option>
                    <option value="CREDIT_NOTE" {{ $tindakan === 'CREDIT_NOTE' ? 'selected' : '' }}>💰 Potong Tagihan</option>
                </select>
            </div>

            {{-- 5. Dropdown Gudang --}}
            @if ($gudangList->count() > 1)
                <div style="flex: 1 1 150px; min-width: 130px;">
                    <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;">
                        <option value="">-- Semua Gudang --</option>
                        @foreach ($gudangList as $gd)
                            <option value="{{ $gd->gudang_id }}" {{ (string) $gudangId === (string) $gd->gudang_id ? 'selected' : '' }}>
                                {{ $gd->gudang_nm }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- 6. Filter Tanggal Dari - Sampai --}}
            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: nowrap;">
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" 
                       class="form-control" title="Tanggal Awal" 
                       style="padding: 0.45rem 0.6rem; font-size: 0.85rem; width: 135px;">
                <span style="color: #94a3b8; font-size: 0.8rem;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" 
                       class="form-control" title="Tanggal Akhir" 
                       style="padding: 0.45rem 0.6rem; font-size: 0.85rem; width: 135px;">
            </div>

            {{-- 7. Tombol Terapkan & Reset --}}
            <div style="display: flex; gap: 0.35rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; height: 36px; display: inline-flex; align-items: center; gap: 0.35rem; background: #0f172a;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Filter
                </button>
                @if ($search || $supplierId || $barangId || $tindakan || $gudangId || $startDate || $endDate || $kategori)
                    <a href="{{ route('gudang.retur.index', ['view' => $viewType]) }}" class="btn btn-secondary" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; height: 36px; display: inline-flex; align-items: center;" title="Reset Semua Filter">
                        ✕ Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- KONTEN TABEL --}}
    @if ($viewType === 'item')
        {{-- MODE 1: BUKU REKAP RETUR PER ITEM (EXCEL GRID) --}}
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 140px;">Tgl &amp; No Retur</th>
                        <th style="min-width: 150px;">Supplier Mitra</th>
                        <th style="width: 140px;">Nomor Batch</th>
                        <th style="width: 110px;">Kode Barang</th>
                        <th style="min-width: 180px;">Nama Barang</th>
                        <th style="width: 110px; text-align: right;">Qty Retur</th>
                        <th style="width: 70px;">Satuan</th>
                        <th style="width: 110px; text-align: right;">Harga Satuan</th>
                        <th style="width: 130px; text-align: right;">Total Nilai (Rp)</th>
                        <th style="width: 130px; text-align: center;">Tindakan</th>
                        <th style="min-width: 180px;">Alasan Cacat / Reject</th>
                        <th style="width: 60px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $item)
                        @php
                            $hdr = $item->header;
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b; font-size: 0.8rem;">
                                {{ $dataList->firstItem() + $index }}
                            </td>
                            <td>
                                <a href="{{ route('gudang.retur.show', $item->retur_id) }}" style="font-weight: 700; color: #dc2626; text-decoration: none; font-size: 0.85rem;">
                                    {{ $hdr?->retur_no ?? '-' }}
                                </a>
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    📅 {{ $hdr?->retur_tgl ? $hdr->retur_tgl->format('d/m/Y') : '-' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                                    {{ $hdr?->supplier?->supplier_nm ?? '-' }}
                                </div>
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    {{ $hdr?->gudang?->gudang_nm ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #0f172a; font-family: monospace; font-weight: 700; font-size: 0.775rem;">
                                    {{ $item->batch_no }}
                                </span>
                            </td>
                            <td style="font-family: monospace; font-size: 0.8rem; color: #64748b;">
                                {{ $item->barang?->barang_cd ?? '-' }}
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-size: 0.875rem;">
                                    {{ $item->barang?->barang_nm ?? '-' }}
                                </strong>
                                @if ($item->barang?->jenisBarang)
                                    <div style="font-size: 0.7rem; color: #64748b;">
                                        {{ $item->barang->jenisBarang->jenis_barang_nm }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 800; font-size: 0.925rem; color: #dc2626; font-family: monospace;">
                                {{ number_format((float) $item->retur_qty, 2, ',', '.') }}
                            </td>
                            <td style="font-size: 0.8rem; color: #475569;">
                                {{ $item->barang?->satuanDasar?->satuan_nm ?? '-' }}
                            </td>
                            <td style="text-align: right; color: #475569; font-size: 0.825rem; font-family: monospace;">
                                Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;">
                                Rp {{ number_format((float) $item->subtotal_nominal, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if ($hdr?->tindakan_cd === 'REPLACE')
                                    <span class="badge" style="background: #d1fae5; color: #047857; font-weight: 700; font-size: 0.725rem;">
                                        🔄 Kirim Ulang
                                    </span>
                                @else
                                    <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700; font-size: 0.725rem;">
                                        💰 Potong Nota
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($item->alasan_reject)
                                    <div style="color: #b91c1c; font-weight: 600; font-size: 0.775rem;">
                                        ⚠️ {{ $item->alasan_reject }}
                                    </div>
                                @endif
                                @if ($item->catatan_txt)
                                    <div style="font-size: 0.7rem; color: #64748b;">
                                        {{ $item->catatan_txt }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('gudang.retur.show', $item->retur_id) }}" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem;" title="Lihat Surat Jalan Retur">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦🔄</div>
                                <div style="font-weight: 600; color: #64748b; font-size: 0.95rem;">Tidak ada item retur yang sesuai dengan filter</div>
                                <p style="font-size: 0.8rem; margin-top: 0.25rem;">
                                    Silakan ubah kata kunci pencarian, filter barang, atau reset pilihan tanggal.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        {{-- MODE 2: DAFTAR DOKUMEN SURAT JALAN RETUR (HEADER VIEW) --}}
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="width: 140px;">No Retur &amp; Tgl</th>
                        <th style="min-width: 170px;">Supplier Mitra</th>
                        <th style="width: 130px;">Gudang Asal</th>
                        <th style="width: 150px;">Referensi Dokumen</th>
                        <th style="min-width: 250px;">Item Barang &amp; Batch</th>
                        <th style="width: 140px; text-align: center;">Tindakan Retur</th>
                        <th style="width: 130px; text-align: right;">Total Nilai</th>
                        <th style="width: 70px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $retur)
                        <tr>
                            <td>
                                <a href="{{ route('gudang.retur.show', $retur->retur_id) }}" style="font-weight: 700; color: #dc2626; text-decoration: none; font-size: 0.85rem;">
                                    {{ $retur->retur_no }}
                                </a>
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                    📅 {{ $retur->retur_tgl ? $retur->retur_tgl->format('d/m/Y') : '-' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                                    {{ $retur->supplier?->supplier_nm ?? '-' }}
                                </div>
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    {{ $retur->supplier?->supplier_cd ?? '' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 600;">
                                    🏬 {{ $retur->gudang?->gudang_nm ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if ($retur->po)
                                    <a href="{{ route('gudang.po.show', $retur->po_id) }}" style="font-size: 0.8rem; font-weight: 600; color: #0284c7; text-decoration: none;">
                                        PO: {{ $retur->po->po_no }}
                                    </a>
                                @else
                                    <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 0.7rem;">Non-PO (Bebas)</span>
                                @endif
                                @if ($retur->suratjalan_supplier_no)
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                        SJ: {{ $retur->suratjalan_supplier_no }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    @foreach ($retur->details as $dtl)
                                        <div style="font-size: 0.8rem; background: #f8fafc; border-left: 3px solid #ef4444; padding: 3px 6px; border-radius: 4px;">
                                            <strong>{{ $dtl->barang?->barang_nm ?? '-' }}</strong>: 
                                            <span style="color: #b91c1c; font-weight: 700;">{{ number_format((float) $dtl->retur_qty, 2) }} {{ $dtl->barang?->satuanDasar?->satuan_nm }}</span>
                                            <span style="font-size: 0.725rem; color: #64748b;"> (Batch: {{ $dtl->batch_no }})</span>
                                            @if ($dtl->alasan_reject)
                                                <div style="font-size: 0.7rem; color: #dc2626; font-style: italic;">
                                                    ⚠️ {{ $dtl->alasan_reject }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if ($retur->tindakan_cd === 'REPLACE')
                                    <span class="badge" style="background: #d1fae5; color: #047857; font-weight: 700;">
                                        🔄 Ganti Barang Baru
                                    </span>
                                @else
                                    <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700;">
                                        💰 Potong Tagihan
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 700; font-size: 0.85rem; color: #0f172a; font-family: monospace;">
                                Rp {{ number_format((float) $retur->total_nominal, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('gudang.retur.show', $retur->retur_id) }}" class="btn btn-secondary btn-sm" style="padding: 0.3rem 0.6rem;" title="Lihat Surat Jalan Retur">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦🔄</div>
                                <div style="font-weight: 600; color: #64748b; font-size: 0.95rem;">Belum ada dokumen retur pembelian yang tercatat</div>
                                <p style="font-size: 0.8rem; margin-top: 0.25rem;">
                                    Barang cacat mutu yang dikembalikan ke supplier akan tercatat rapi di sini beserta nomor surat jalan dan pemotongan stok otomatis.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- PAGINASI --}}
    @if ($dataList->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; background: #ffffff;">
            {{ $dataList->links() }}
        </div>
    @endif
</div>
@endsection
