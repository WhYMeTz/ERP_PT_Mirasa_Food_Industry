@extends('layouts.app')

@section('title', 'Pemakaian Bahan (Outbound) - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Pemakaian Bahan (Outbound)</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Buku catatan fisik pengeluaran bahan baku &amp; bahan penolong ke proses produksi, packing, seasoning, atau afkir ulang (FIFO per Batch).
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('gudang.pemakaian.export-rekap-pdf', request()->query()) }}" target="_blank" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#ffffff'" title="Buka & Cetak Rekap Barang Keluar PDF Resmi">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Cetak Rekap (PDF)</span>
        </a>
        <a href="{{ route('gudang.pemakaian.export-excel', request()->query()) }}" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" onmouseover="this.style.background='#ecfdf5'" onmouseout="this.style.background='#ffffff'" title="Download Rekap Barang Keluar format Excel (.xlsx)">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export Excel</span>
        </a>
        @if (Auth::user()?->canCreatePemakaian())
            <button type="button" onclick="document.getElementById('modal-import-pemakaian').style.display='flex'" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #7c3aed; color: #7c3aed; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" onmouseover="this.style.background='#faf5ff'" onmouseout="this.style.background='#ffffff'" title="Import data pemakaian dari file Excel">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import Excel</span>
            </button>
        @endif
        @if (Auth::user()?->canCreatePemakaian())
            <a href="{{ route('gudang.pemakaian.create') }}" class="btn btn-primary" style="background: #dc2626;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Pemakaian Bahan
            </a>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL (SERAGAM DENGAN PO, BARANG MASUK & LACAK STOK) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    {{-- 1. SEMUA DOKUMEN PENGELUARAN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    Semua Dokumen Keluar
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpiCounts['total'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #475569; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total bukti pengeluaran bahan tercatat
        </div>
    </div>

    {{-- 2. PENGELUARAN HARI INI --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Pengeluaran Hari Ini
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpiCounts['today'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Bongkar ke lantai produksi hari ini
        </div>
    </div>

    {{-- 3. TOTAL ITEM BAHAN KELUAR --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706;">
                    Total Item Bahan Keluar
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
            {{ number_format($ringkasan['singkong_qty'] ?? 0, 0, ',', '.') }} kg singkong &bull; {{ number_format(($ringkasan['bumbu_qty'] ?? 0) + ($ringkasan['minyak_qty'] ?? 0), 0, ',', '.') }} kg penolong
        </div>
    </div>

    {{-- 4. TOTAL BIAYA BAHAN (HPP) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #dc2626;">
                    Total Biaya Bahan (HPP)
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem; font-family: monospace;">
                    Rp {{ number_format($ringkasan['grand_total_nilai'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Akumulasi nilai seluruh bahan dikeluarkan
        </div>
    </div>
</div>

{{-- TAB FILTER KATEGORI BAHAN (ENTERPRISE SEGMENTED CONTROL INDUSTRIAL ERP) --}}
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
    <div style="display: inline-flex; align-items: center; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); flex-wrap: wrap; gap: 0.25rem;">
        
        {{-- Semua Bahan --}}
        <a href="{{ route('gudang.pemakaian.index', array_merge(request()->except('kategori', 'page'), ['kategori' => null])) }}" 
           style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s; {{ empty($kategori) ? 'background: #0f172a; color: #ffffff;' : 'background: transparent; color: #475569;' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Semua Bahan</span>
            <span style="font-size: 0.725rem; font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 9999px; {{ empty($kategori) ? 'background: rgba(255,255,255,0.2); color: #ffffff;' : 'background: #f1f5f9; color: #64748b;' }}">
                {{ number_format($ringkasan['total_item_count'] ?? 0) }}
            </span>
        </a>

        {{-- Bahan Baku (Singkong) --}}
        <a href="{{ route('gudang.pemakaian.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'BAHAN_BAKU'])) }}" 
           style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s; {{ $kategori === 'BAHAN_BAKU' ? 'background: #0f172a; color: #ffffff;' : 'background: transparent; color: #475569;' }}">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #d97706; display: inline-block;"></span>
            <span>Bahan Baku (Singkong)</span>
            <span style="font-size: 0.725rem; font-weight: 700; font-family: monospace; padding: 0.1rem 0.45rem; border-radius: 9999px; {{ $kategori === 'BAHAN_BAKU' ? 'background: rgba(255,255,255,0.2); color: #ffffff;' : 'background: #fef3c7; color: #92400e;' }}">
                {{ number_format($ringkasan['singkong_qty'] ?? 0, 0, ',', '.') }} kg
            </span>
        </a>

        {{-- Bahan Penolong (Bumbu & Minyak) --}}
        <a href="{{ route('gudang.pemakaian.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'BAHAN_PENOLONG'])) }}" 
           style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s; {{ $kategori === 'BAHAN_PENOLONG' ? 'background: #0f172a; color: #ffffff;' : 'background: transparent; color: #475569;' }}">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #059669; display: inline-block;"></span>
            <span>Bahan Penolong &amp; Bumbu</span>
            <span style="font-size: 0.725rem; font-weight: 700; font-family: monospace; padding: 0.1rem 0.45rem; border-radius: 9999px; {{ $kategori === 'BAHAN_PENOLONG' ? 'background: rgba(255,255,255,0.2); color: #ffffff;' : 'background: #ecfdf5; color: #047857;' }}">
                {{ number_format(($ringkasan['bumbu_qty'] ?? 0) + ($ringkasan['minyak_qty'] ?? 0), 0, ',', '.') }} kg
            </span>
        </a>

        {{-- Kemasan & Packaging --}}
        <a href="{{ route('gudang.pemakaian.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'KEMASAN'])) }}" 
           style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s; {{ $kategori === 'KEMASAN' ? 'background: #0f172a; color: #ffffff;' : 'background: transparent; color: #475569;' }}">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #6366f1; display: inline-block;"></span>
            <span>Kemasan &amp; Dus</span>
            <span style="font-size: 0.725rem; font-weight: 700; font-family: monospace; padding: 0.1rem 0.45rem; border-radius: 9999px; {{ $kategori === 'KEMASAN' ? 'background: rgba(255,255,255,0.2); color: #ffffff;' : 'background: #eef2ff; color: #4338ca;' }}">
                {{ number_format($ringkasan['kemasan_qty'] ?? 0, 0, ',', '.') }} unit
            </span>
        </a>
    </div>

    @if (!empty($kategori))
        <a href="{{ route('gudang.pemakaian.index', array_merge(request()->except('kategori', 'page'), ['kategori' => null])) }}" 
           style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.65rem; border-radius: 6px; background: #f1f5f9; border: 1px solid #e2e8f0;">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>Reset Kategori</span>
        </a>
    @endif
</div>

{{-- WADAH TABEL UTAMA (PERSIS FORMAT CARD PO & BARANG MASUK) --}}
<div class="card" style="border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden;">
    
    {{-- BARIS 1: PERSPEKTIF VIEW SWITCHER & TOTAL HASIL --}}
    <div style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        {{-- View Switcher Tab --}}
        <div style="display: inline-flex; background: #f1f5f9; padding: 0.2rem; border-radius: 6px; border: 1px solid #e2e8f0;">
            <a href="{{ route('gudang.pemakaian.index', array_merge(request()->query(), ['view' => 'item'])) }}" 
               style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; background: {{ $viewType === 'item' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'item' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'item' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Buku Rekap Pemakaian (Excel Grid)
            </a>
            <a href="{{ route('gudang.pemakaian.index', array_merge(request()->query(), ['view' => 'header'])) }}" 
               style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; background: {{ $viewType === 'header' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'header' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'header' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Daftar Dokumen Pengeluaran
            </a>
        </div>

        <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; color: #64748b;">
            <span>Total Hasil:</span>
            <strong style="color: #0f172a; font-family: monospace; font-size: 0.95rem;">{{ $dataList->total() }}</strong>
            <span>{{ $viewType === 'item' ? 'baris data' : 'dokumen' }}</span>
        </div>
    </div>

    {{-- BARIS 2: TOOLBAR FILTER LENGKAP & TERPADU (SELALU TERBUKA TANPA TOGGLE RAHASIA) --}}
    <div style="background: #f8fafc; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
        <form action="{{ route('gudang.pemakaian.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="view" value="{{ $viewType }}">
            <input type="hidden" name="kategori" value="{{ $kategori ?? '' }}">
            
            {{-- 1. Input Pencarian --}}
            <div style="flex: 1 1 220px; min-width: 180px;">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Cari No Dokumen, batch, SPK, bahan..." 
                       class="form-control" 
                       style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;">
            </div>

            {{-- 2. Dropdown Gudang --}}
            <div style="flex: 0 1 165px; min-width: 140px;">
                <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;" onchange="this.form.submit()">
                    <option value="">-- Semua Gudang --</option>
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                            {{ $gdg->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Dropdown Lini Tujuan --}}
            <div style="flex: 0 1 180px; min-width: 150px;">
                <select name="tujuan" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;" onchange="this.form.submit()">
                    <option value="">-- Semua Lini Tujuan --</option>
                    @foreach ($tujuanOptions as $opt)
                        <option value="{{ $opt }}" {{ ($tujuan === $opt) ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 4. Kapsul Rentang Tanggal Periode (Selalu Terbuka Rapi & Elegan) --}}
            <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: {{ (!empty($startDate) || !empty($endDate)) ? '#f0f9ff' : '#ffffff' }}; border: 1px solid {{ (!empty($startDate) || !empty($endDate)) ? '#0284c7' : '#cbd5e1' }}; border-radius: 6px; padding: 0.2rem 0.6rem; height: 35px; box-sizing: border-box;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: {{ (!empty($startDate) || !empty($endDate)) ? '#0284c7' : '#64748b' }}; flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" title="Dari Tanggal" 
                       style="border: none; background: transparent; font-size: 0.825rem; padding: 0; color: #0f172a; outline: none; width: 110px;">
                <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" title="Sampai Tanggal" 
                       style="border: none; background: transparent; font-size: 0.825rem; padding: 0; color: #0f172a; outline: none; width: 110px;">
            </div>

            {{-- 5. Tombol Aksi Filter & Reset --}}
            <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.45rem 0.95rem; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>Filter</span>
            </button>

            @if(!empty($search) || !empty($gudangId) || !empty($tujuan) || !empty($kategori) || !empty($startDate) || !empty($endDate))
                <a href="{{ route('gudang.pemakaian.index', ['view' => $viewType]) }}" class="btn btn-secondary btn-sm" title="Hapus Semua Filter" style="padding: 0.45rem 0.75rem; font-size: 0.825rem;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if ($viewType === 'item')
        {{-- VIEW 1: BUKU REKAP BAHAN KELUAR (EXCEL GRID COMPREHENSIVE YANG DISUKAI OPERATOR) --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 140px;">Tgl &amp; No. Dokumen</th>
                        <th style="min-width: 180px;">Gudang &amp; Tujuan / SPK</th>
                        <th style="min-width: 190px;">Bahan Keluar</th>
                        <th style="min-width: 130px;">No. Batch (FIFO)</th>
                        <th style="min-width: 110px; text-align: right;">Qty Keluar</th>
                        <th style="min-width: 120px; text-align: right;">Sisa Barang</th>
                        <th style="min-width: 130px; text-align: right;">Total Biaya (HPP)</th>
                        <th style="width: 100px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $row)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                {{ $dataList->firstItem() + $index }}
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #334155; font-size: 0.85rem;">
                                    {{ \Carbon\Carbon::parse($row->header?->pakai_tgl)->format('d/m/Y') }}
                                </div>
                                <a href="{{ route('gudang.pemakaian.show', $row->header?->pakai_id) }}" style="font-size: 0.85rem; font-weight: 700; color: #dc2626; text-decoration: none;">
                                    {{ $row->header?->pakai_no ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                                    {{ $row->header?->gudang?->gudang_nm ?? '-' }}
                                </div>
                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.725rem; margin-top: 0.2rem; display: inline-block;">
                                    {{ $row->keterangan_txt ?? ($row->header?->tujuan_pemakaian ?? '-') }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-size: 0.875rem;">
                                    {{ $row->barang?->barang_nm ?? '-' }}
                                </strong>
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem;">
                                    <span style="font-family: monospace; font-size: 0.75rem; color: #64748b;">
                                        {{ $row->barang?->barang_cd ?? '-' }}
                                    </span>
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; padding: 0.1rem 0.35rem;">
                                        {{ $row->barang?->jenisBarang?->jenis_barang_nm ?? ($row->barang?->jenisBarang?->jenis_barang_cd ?? '-') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 0.825rem; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; display: inline-block;">
                                    {{ $row->batch_no }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <span style="font-weight: 700; color: #dc2626; font-size: 0.95rem;">
                                    {{ number_format((float) $row->qty_keluar, 2, ',', '.') }}
                                </span>
                                <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.2rem;">
                                    {{ $row->barang?->satuanDasar?->satuan_nm ?? '-' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <span style="font-weight: 700; color: {{ ($row->sisa_gudang_qty ?? 0) > 0 ? '#059669' : '#dc2626' }}; font-size: 0.95rem;">
                                    {{ number_format((float) ($row->sisa_gudang_qty ?? 0), 2, ',', '.') }}
                                </span>
                                <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.2rem;">
                                    {{ $row->barang?->satuanDasar?->satuan_nm ?? '-' }}
                                </span>
                                @if(!empty($row->batch_no) && isset($row->sisa_batch_qty))
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.15rem;">
                                        Batch: {{ number_format((float) $row->sisa_batch_qty, 2, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                    Rp {{ number_format((float) $row->total_harga, 0, ',', '.') }}
                                </div>
                                <small style="color: #64748b; font-size: 0.725rem;">
                                    @ Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}
                                </small>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('gudang.pemakaian.show', $row->header?->pakai_id) }}" class="btn btn-secondary btn-sm" title="Lihat Dokumen Pengeluaran &amp; Cetak BPPB">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat transaksi pengeluaran barang. Klik tombol <strong>"+ Catat Barang Keluar"</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        {{-- VIEW 2: DAFTAR DOKUMEN PENGELUARAN (HEADER VIEW) --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 160px;">Nomor Pengeluaran</th>
                        <th style="min-width: 120px;">Tanggal Keluar</th>
                        <th style="min-width: 150px;">Gudang Asal</th>
                        <th style="min-width: 180px;">Tujuan Pemakaian / SPK</th>
                        <th style="min-width: 110px; text-align: center;">Total Item</th>
                        <th style="min-width: 160px;">Catatan</th>
                        <th style="width: 100px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $hdr)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                {{ $dataList->firstItem() + $index }}
                            </td>
                            <td>
                                <a href="{{ route('gudang.pemakaian.show', $hdr->pakai_id) }}" style="font-weight: 700; color: #dc2626; text-decoration: none; font-size: 0.9rem;">
                                    {{ $hdr->pakai_no }}
                                </a>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #334155; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($hdr->pakai_tgl)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #334155;">{{ $hdr->gudang?->gudang_nm ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700;">
                                    {{ $hdr->tujuan_pemakaian }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700;">
                                    {{ $hdr->details->count() }} Bahan
                                </span>
                            </td>
                            <td style="color: #64748b; font-size: 0.85rem;">{{ $hdr->catatan_txt ?? '-' }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('gudang.pemakaian.show', $hdr->pakai_id) }}" class="btn btn-secondary btn-sm" title="Lihat Dokumen Lengkap">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat dokumen pengeluaran barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

@if ($dataList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $dataList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection

{{-- ════════════════════════════════════════════════════════ --}}
{{--  MODAL IMPORT EXCEL PEMAKAIAN BAHAN                      --}}
{{-- ════════════════════════════════════════════════════════ --}}
<div id="modal-import-pemakaian" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.55); align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.2); width:100%; max-width:520px; overflow:hidden;">

        {{-- Header Modal --}}
        <div style="background:#134e5e; padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-weight:700; font-size:1rem; color:#fff;">Import Pemakaian Bahan dari Excel</div>
                <div style="font-size:0.8rem; color:#a5f3d8; margin-top:2px;">Upload file .xlsx sesuai template resmi ERP Mirasa</div>
            </div>
            <button type="button" onclick="document.getElementById('modal-import-pemakaian').style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer; font-size:1.4rem; line-height:1;">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.5rem;">

            @if(session('success'))
                <div style="background:#dcfce7; border:1px solid #86efac; color:#14532d; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.85rem;">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div style="background:#fef9c3; border:1px solid #fde68a; color:#713f12; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.85rem;">
                    ⚠️ {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background:#fee2e2; border:1px solid #fca5a5; color:#7f1d1d; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.85rem;">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:0.9rem 1rem; margin-bottom:1.25rem; font-size:0.83rem; color:#475569;">
                <div style="font-weight:700; color:#0f172a; margin-bottom:0.4rem;">📋 Cara Import:</div>
                <ol style="margin:0; padding-left:1.25rem; line-height:1.8;">
                    <li>Download template resmi terlebih dahulu</li>
                    <li>Isi data di sheet <strong>Barang Keluar</strong> mulai baris ke-7</li>
                    <li>Gunakan sheet <em>Ref. Kode Barang</em> & <em>Ref. Gudang</em> sebagai referensi</li>
                    <li>Baris dengan <strong>No. Dokumen sama</strong> dianggap satu dokumen pengeluaran</li>
                    <li>Batch & stok harus sudah tersedia di gudang yang dituju</li>
                    <li>Simpan file lalu upload di sini</li>
                </ol>
            </div>

            <a href="{{ route('gudang.pemakaian.download-template') }}" class="btn" style="display:flex; align-items:center; justify-content:center; gap:0.5rem; background:#f0fdf4; border:1.5px solid #059669; color:#059669; font-weight:700; padding:0.65rem 1rem; border-radius:8px; text-decoration:none; font-size:0.875rem; margin-bottom:1.25rem; width:100%;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Download Template Import (.xlsx)
            </a>

            <form method="POST" action="{{ route('gudang.pemakaian.import-excel') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">Pilih File Excel (.xlsx / .xls)</label>
                    <input type="file" name="import_file" id="import_file_pemakaian" accept=".xlsx,.xls"
                        style="display:block; width:100%; font-size:0.875rem; border:1.5px solid #cbd5e1; border-radius:8px; padding:0.5rem; background:#f8fafc; cursor:pointer;"
                        required>
                    @error('import_file')
                        <div style="color:#dc2626; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.77rem; color:#94a3b8; margin-top:0.3rem;">Maksimal 5 MB. Hanya format .xlsx dan .xls. Batch harus sudah ada di stok gudang.</div>
                </div>

                <div style="display:flex; gap:0.75rem;">
                    <button type="submit" id="btn-import-pakai" style="flex:1; background:#134e5e; color:#fff; border:none; border-radius:8px; padding:0.7rem 1rem; font-size:0.9rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Proses Import
                    </button>
                    <button type="button" onclick="document.getElementById('modal-import-pemakaian').style.display='none'" style="background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; border-radius:8px; padding:0.7rem 1rem; font-size:0.9rem; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    @if(session('warning') || session('error'))
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-import-pemakaian').style.display = 'flex';
        });
    @endif
    document.querySelector('#modal-import-pemakaian form')?.addEventListener('submit', function() {
        const btn = document.getElementById('btn-import-pakai');
        if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }
    });
</script>
