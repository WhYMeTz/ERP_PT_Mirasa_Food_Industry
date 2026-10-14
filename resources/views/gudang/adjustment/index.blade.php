@extends('layouts.app')

@section('title', 'Penyesuaian Stok (Stock Adjustment) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-index.css') }}">
@endpush

@section('content')
{{-- 1. HEADER HALAMAN STANDAR MIRASA --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Penyesuaian Stok (Stock Adjustment)</h1>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; flex-shrink: 0;">
        <a href="{{ route('gudang.stok.rekap') }}" class="btn-adj-secondary" title="Buka Rekapitulasi Saldo Stok Komoditas">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Rekap Stok Komoditas</span>
        </a>
        <a href="{{ route('gudang.adjustment.create') }}" class="btn-adj-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Catat Penyesuaian Stok</span>
        </a>
    </div>
</div>

{{-- ALERT FLASH MESSAGES --}}
@if(session('success'))
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- 2. 4 KARTU METRIK OPERASIONAL (SERAGAM DENGAN PO & BARANG MASUK) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
    {{-- 1. TOTAL DOKUMEN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    Total Dokumen Adjustment
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($metrics['total_dokumen']) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #475569; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            {{ number_format($metrics['total_item']) }} baris komoditas disesuaikan
        </div>
    </div>

    {{-- 2. TOTAL DEFISIT --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #dc2626;">
                    Total Nilai Defisit (Susut)
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: #dc2626; margin-top: 0.25rem; font-family: monospace;">
                    -Rp {{ number_format($metrics['total_defisit_nilai'], 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Penyusutan drum, susut alami &amp; rusak
        </div>
    </div>

    {{-- 3. TOTAL SURPLUS --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Total Nilai Surplus (Lebih)
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: #059669; margin-top: 0.25rem; font-family: monospace;">
                    +Rp {{ number_format($metrics['total_surplus_nilai'], 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Kelebihan fisik &amp; temuan opname
        </div>
    </div>

    {{-- 4. NET DAMPAK BUKU --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #4f46e5;">
                    Net Dampak Persediaan
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: {{ $metrics['net_selisih_nilai'] < 0 ? '#dc2626' : ($metrics['net_selisih_nilai'] > 0 ? '#059669' : '#0f172a') }}; margin-top: 0.25rem; font-family: monospace;">
                    {{ $metrics['net_selisih_nilai'] < 0 ? '-' : ($metrics['net_selisih_nilai'] > 0 ? '+' : '') }}Rp {{ number_format(abs($metrics['net_selisih_nilai']), 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0e7ff; display: flex; align-items: center; justify-content: center; color: #4f46e5; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Dampak nilai buku persediaan bersih
        </div>
    </div>
</div>

{{-- 3. WADAH TABEL UTAMA (PERSIS FORMAT CARD PO & PEMAKAIAN) --}}
<div class="card" style="border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden;">
    
    {{-- BARIS 1: PERSPEKTIF VIEW SWITCHER & TOTAL HASIL --}}
    <div style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        {{-- View Switcher Tab --}}
        <div style="display: inline-flex; background: #f1f5f9; padding: 0.2rem; border-radius: 6px; border: 1px solid #e2e8f0;">
            <a href="{{ route('gudang.adjustment.index', array_merge(request()->query(), ['view' => 'item'])) }}" 
               title="Tampilan rincian fisik per komoditas & batch barang"
               style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; background: {{ $viewType === 'item' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'item' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'item' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Rincian per Bahan (Buku Rekap)</span>
            </a>
            <a href="{{ route('gudang.adjustment.index', array_merge(request()->query(), ['view' => 'header'])) }}" 
               title="Tampilan ringkasan 1 baris per berkas nomor berita acara"
               style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; background: {{ $viewType === 'header' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'header' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'header' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ringkasan per Berita Acara</span>
            </a>
        </div>

        <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; color: #64748b;">
            <span>Total Hasil:</span>
            <strong style="color: #0f172a; font-family: monospace; font-size: 0.95rem;">{{ $dataList->total() }}</strong>
            <span>{{ $viewType === 'item' ? 'baris rincian bahan' : 'dokumen berita acara' }}</span>
        </div>
    </div>

    {{-- BARIS 2: TOOLBAR FILTER LENGKAP & TERPADU --}}
    <div style="background: #f8fafc; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
        <form action="{{ route('gudang.adjustment.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="view" value="{{ $viewType }}">
            
            {{-- 1. Input Pencarian --}}
            <div style="flex: 1 1 240px; min-width: 200px;">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Cari Nama Barang, Kode Barang, No Dokumen..." 
                       class="form-control" 
                       style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;">
            </div>

            {{-- 2. Dropdown Gudang --}}
            <div style="flex: 0 1 170px; min-width: 140px;">
                <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;" onchange="this.form.submit()">
                    <option value="">-- Semua Gudang --</option>
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ (($filters['gudang_id'] ?? '') == $gdg->gudang_id) ? 'selected' : '' }}>
                            {{ $gdg->display_name ?? $gdg->gudang_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Dropdown Status Selisih --}}
            <div style="flex: 0 1 175px; min-width: 140px;">
                <select name="tipe_selisih" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;" onchange="this.form.submit()">
                    <option value="">-- Semua Status Selisih --</option>
                    <option value="DEFISIT" {{ (($filters['tipe_selisih'] ?? '') === 'DEFISIT') ? 'selected' : '' }}>Defisit / Susut (-)</option>
                    <option value="SURPLUS" {{ (($filters['tipe_selisih'] ?? '') === 'SURPLUS') ? 'selected' : '' }}>Surplus / Lebih (+)</option>
                    <option value="MATCH" {{ (($filters['tipe_selisih'] ?? '') === 'MATCH') ? 'selected' : '' }}>Cocok / Imbang (0)</option>
                </select>
            </div>

            {{-- 4. Filter Tanggal Tunggal (Sesuai Spesifikasi Dokumen) --}}
            <div style="flex: 0 1 160px; min-width: 140px;">
                <input type="date" name="adj_tgl" value="{{ $filters['adj_tgl'] ?? '' }}" 
                       class="form-control" 
                       title="Filter Tanggal Penyesuaian" 
                       style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;"
                       onchange="this.form.submit()">
            </div>

            {{-- 5. Tombol Aksi Filter & Reset --}}
            <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.45rem 0.95rem; font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; background: #059669; border-color: #059669;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>Filter</span>
            </button>

            @if(!empty($filters['search']) || !empty($filters['gudang_id']) || !empty($filters['tipe_selisih']) || !empty($filters['adj_tgl']) || !empty($filters['tgl_mulai']) || !empty($filters['tgl_selesai']))
                <a href="{{ route('gudang.adjustment.index', ['view' => $viewType]) }}" class="btn btn-secondary btn-sm" title="Hapus Semua Filter" style="padding: 0.45rem 0.75rem; font-size: 0.825rem;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if ($viewType === 'item')
        {{-- VIEW 1: BUKU REKAP PENYESUAIAN (KOLOM TUNGGAL TANPA TUMPUKAN TEKS) --}}
        <div style="overflow-x: auto;">
            <table class="adj-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center; padding: 0.75rem 0.5rem;">No</th>
                        <th style="width: 140px; min-width: 130px; padding: 0.75rem 0.75rem;">Tgl &amp; No. Dokumen</th>
                        <th style="width: 160px; min-width: 145px; padding: 0.75rem 0.75rem;">Gudang Simpan</th>
                        <th style="width: 210px; min-width: 190px; padding: 0.75rem 0.75rem;">Komoditas &amp; Batch</th>
                        <th style="width: 130px; min-width: 120px; text-align: right; padding: 0.75rem 0.75rem; white-space: nowrap;">Persediaan Awal</th>
                        <th style="width: 130px; min-width: 120px; text-align: right; padding: 0.75rem 0.75rem; white-space: nowrap;">Persediaan Gudang</th>
                        <th style="width: 130px; min-width: 120px; text-align: right; padding: 0.75rem 0.75rem; white-space: nowrap;">Selisih Persediaan</th>
                        <th style="width: 135px; min-width: 125px; text-align: right; padding: 0.75rem 0.75rem; white-space: nowrap;">Total Selisih (Rp)</th>
                        <th style="min-width: 180px; padding: 0.75rem 0.75rem;">Alasan / Catatan</th>
                        <th style="width: 90px; min-width: 85px; text-align: center; padding: 0.75rem 0.75rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $row)
                        @php
                            $hdr = $row->header;
                            $barang = $row->barang;
                            $stokSistem = (float) $row->stok_sistem_qty;
                            $stokFisik = (float) $row->stok_fisik_qty;
                            $hargaSatuan = (float) $row->harga_satuan;
                            $totalSistemNilai = (float) ($row->total_sistem_nilai ?: ($stokSistem * $hargaSatuan));
                            $totalFisikNilai = (float) ($row->total_fisik_nilai ?: ($stokFisik * $hargaSatuan));
                            $selisihQty = (float) $row->selisih_qty;
                            $selisihNilai = (float) $row->total_selisih_nilai;
                            $satuan = $barang->satuanDasar->satuan_cd ?? 'KG';
                        @endphp
                        <tr>
                            {{-- 1. No Urut --}}
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem; font-weight: 600;">
                                {{ $dataList->firstItem() + $index }}
                            </td>

                            {{-- 2. Tgl & No Dokumen (a. Tanggal) --}}
                            <td style="padding: 0.65rem 0.75rem; white-space: nowrap;">
                                <div style="font-weight: 600; color: #64748b; font-size: 0.775rem; margin-bottom: 0.15rem;">
                                    {{ $hdr && $hdr->adj_tgl ? $hdr->adj_tgl->format('d/m/Y') : '-' }}
                                </div>
                                <a href="{{ route('gudang.adjustment.show', $hdr->adj_id ?? 0) }}" style="font-size: 0.85rem; font-weight: 700; color: #0284c7; text-decoration: none; white-space: nowrap; display: inline-block;">
                                    {{ $hdr->adj_no ?? '-' }}
                                </a>
                            </td>

                            {{-- 3. Gudang Simpan --}}
                            <td style="padding: 0.65rem 0.75rem;">
                                <span style="font-size: 0.825rem; color: #334155; font-weight: 600; display: block; line-height: 1.35;">
                                    {{ $hdr->gudang->gudang_nm ?? '-' }}
                                </span>
                            </td>

                            {{-- 4. Komoditas & Batch (b. Kode Barang, c. Nama Barang) --}}
                            <td style="padding: 0.65rem 0.75rem;">
                                <strong style="color: #0f172a; font-size: 0.875rem; display: block; line-height: 1.25;">
                                    {{ $barang->barang_nm ?? '-' }}
                                </strong>
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem; flex-wrap: wrap;">
                                    <span style="font-family: monospace; font-size: 0.75rem; color: #64748b;">
                                        {{ $barang->barang_cd ?? '-' }}
                                    </span>
                                    @if($row->batch_no)
                                        <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-family: monospace; font-size: 0.725rem; padding: 0.1rem 0.35rem; font-weight: 700; white-space: nowrap;">
                                            Batch: {{ $row->batch_no }}
                                        </span>
                                    @else
                                        <span style="font-size: 0.725rem; color: #94a3b8; font-style: italic;">[Akumulatif]</span>
                                    @endif
                                </div>
                            </td>

                            {{-- 5. Persediaan Awal (d. Persediaan Awal Qty, f. Total Persediaan Awal Rp) --}}
                            <td style="text-align: right; padding: 0.65rem 0.75rem;">
                                <div style="font-family: monospace; font-weight: 700; color: #334155; font-size: 0.875rem;">
                                    {{ number_format($stokSistem, 2, ',', '.') }} <span style="font-size: 0.75rem; color: #64748b;">{{ $satuan }}</span>
                                </div>
                                <div style="font-family: monospace; font-size: 0.725rem; color: #64748b; margin-top: 0.15rem;" title="Total Persediaan Awal (Rp)">
                                    Rp {{ number_format($totalSistemNilai, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- 6. Persediaan Gudang (g. Persediaan Gudang Qty, i. Total Persediaan Gudang Rp) --}}
                            <td style="text-align: right; padding: 0.65rem 0.75rem;">
                                <div style="font-family: monospace; font-weight: 700; color: #064e3b; font-size: 0.875rem;">
                                    {{ number_format($stokFisik, 2, ',', '.') }} <span style="font-size: 0.75rem; color: #64748b;">{{ $satuan }}</span>
                                </div>
                                <div style="font-family: monospace; font-size: 0.725rem; color: #059669; font-weight: 600; margin-top: 0.15rem;" title="Total Persediaan Gudang (Rp)">
                                    Rp {{ number_format($totalFisikNilai, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- 7. Selisih Persediaan (j. Selisih Qty, e/h/k. @Harga Satuan) --}}
                            <td style="text-align: right; padding: 0.65rem 0.75rem;">
                                @if($selisihQty < 0)
                                    <span style="font-family: monospace; font-size: 0.825rem; font-weight: 700; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-block;">
                                        {{ number_format($selisihQty, 2, ',', '.') }} {{ $satuan }}
                                    </span>
                                @elseif($selisihQty > 0)
                                    <span style="font-family: monospace; font-size: 0.825rem; font-weight: 700; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-block;">
                                        +{{ number_format($selisihQty, 2, ',', '.') }} {{ $satuan }}
                                    </span>
                                @else
                                    <span style="font-family: monospace; font-size: 0.825rem; font-weight: 600; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-block;">
                                        0,00 {{ $satuan }}
                                    </span>
                                @endif
                                <div style="font-family: monospace; font-size: 0.725rem; color: #64748b; margin-top: 0.2rem;" title="Harga Satuan (HPP)">
                                    @if($hargaSatuan > 0)
                                        @Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>

                            {{-- 8. Total Selisih (l. Total Selisih Nilai Rp) --}}
                            <td style="text-align: right; padding: 0.65rem 0.75rem;">
                                <div style="font-family: monospace; font-weight: 800; font-size: 0.875rem; color: {{ $selisihNilai < 0 ? '#dc2626' : ($selisihNilai > 0 ? '#16a34a' : '#64748b') }};">
                                    {{ $selisihNilai < 0 ? '-' : ($selisihNilai > 0 ? '+' : '') }}Rp {{ number_format(abs($selisihNilai), 0, ',', '.') }}
                                </div>
                                <span style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.03em; text-transform: uppercase; color: {{ $selisihNilai < 0 ? '#dc2626' : ($selisihNilai > 0 ? '#16a34a' : '#64748b') }};">
                                    {{ $selisihNilai < 0 ? 'Defisit' : ($selisihNilai > 0 ? 'Surplus' : 'Imbang') }}
                                </span>
                            </td>

                            {{-- 9. Alasan / Catatan (SEBELUM AKSI) --}}
                            <td style="padding: 0.65rem 0.75rem;">
                                @if($row->alasan_txt || ($hdr && $hdr->catatan_txt))
                                    <span style="font-size: 0.8rem; color: #334155; line-height: 1.35; display: block;">
                                        {{ $row->alasan_txt ?: $hdr->catatan_txt }}
                                    </span>
                                @else
                                    <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">-</span>
                                @endif
                            </td>

                            {{-- 10. Aksi Dropdown --}}
                            <td style="text-align: center; padding: 0.65rem 0.75rem; position: relative;">
                                <button type="button" class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, 'menuAdj_{{ $row->adjdtl_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="menuAdj_{{ $row->adjdtl_id }}" class="action-dropdown-menu">
                                    <a href="{{ route('gudang.adjustment.show', $hdr->adj_id ?? 0) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Dokumen</span>
                                    </a>

                                    @if($hdr && $hdr->status_cd !== 'VOID')
                                        <button type="button" class="action-dropdown-item danger-item" 
                                                onclick="openVoidModal({{ $hdr->adj_id }}, '{{ $hdr->adj_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            <span>Batalkan (Void)</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat penyesuaian stok. Klik tombol <strong>"+ Catat Penyesuaian Stok"</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    {{-- VIEW 2: DAFTAR DOKUMEN BERITA ACARA (HEADER VIEW PERSIS DOKUMEN GRN & PO) --}}
    @else
        <div style="overflow-x: auto;">
            <table class="adj-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center; padding: 0.75rem 0.5rem;">No</th>
                        <th style="width: 180px; min-width: 165px; padding: 0.75rem 0.75rem;">Nomor Berita Acara</th>
                        <th style="width: 105px; min-width: 95px; text-align: center; padding: 0.75rem 0.75rem;">Tanggal</th>
                        <th style="width: 175px; min-width: 160px; padding: 0.75rem 0.75rem;">Gudang Simpan</th>
                        <th style="width: 95px; min-width: 90px; text-align: center; padding: 0.75rem 0.75rem;">Total Item</th>
                        <th style="width: 125px; min-width: 115px; text-align: right; padding: 0.75rem 0.75rem; white-space: nowrap;">Net Selisih Qty</th>
                        <th style="width: 145px; min-width: 135px; text-align: right; padding: 0.75rem 0.75rem; white-space: nowrap;">Net Dampak Nilai</th>
                        <th style="width: 95px; min-width: 90px; text-align: center; padding: 0.75rem 0.75rem;">Status</th>
                        <th style="min-width: 220px; padding: 0.75rem 0.75rem;">Catatan Berita Acara</th>
                        <th style="width: 90px; min-width: 85px; text-align: center; padding: 0.75rem 0.75rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $hdr)
                        @php
                            $netQty = (float) $hdr->total_selisih_qty;
                            $netNilai = (float) $hdr->total_selisih_nilai;
                        @endphp
                        <tr>
                            {{-- 1. No Urut --}}
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem; font-weight: 600;">
                                {{ $dataList->firstItem() + $index }}
                            </td>

                            {{-- 2. No Berita Acara --}}
                            <td style="padding: 0.65rem 0.75rem; white-space: nowrap;">
                                <a href="{{ route('gudang.adjustment.show', $hdr->adj_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.9rem; white-space: nowrap; display: inline-block;">
                                    {{ $hdr->adj_no }}
                                </a>
                            </td>

                            {{-- 3. Tanggal --}}
                            <td style="text-align: center; padding: 0.65rem 0.75rem; white-space: nowrap;">
                                <span style="font-size: 0.85rem; color: #334155; font-weight: 600;">
                                    {{ $hdr->adj_tgl ? $hdr->adj_tgl->format('d/m/Y') : '-' }}
                                </span>
                            </td>

                            {{-- 4. Gudang Simpan --}}
                            <td style="padding: 0.65rem 0.75rem;">
                                <span style="font-size: 0.85rem; color: #334155; font-weight: 600; display: block; line-height: 1.35;">{{ $hdr->gudang->gudang_nm ?? '-' }}</span>
                            </td>

                            {{-- 5. Total Item --}}
                            <td style="text-align: center; padding: 0.65rem 0.75rem;">
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700;">
                                    {{ $hdr->total_item }} Bahan
                                </span>
                            </td>

                            {{-- 6. Net Selisih Qty --}}
                            <td style="text-align: right; padding: 0.65rem 0.75rem;">
                                @if($netQty < 0)
                                    <span style="font-family: monospace; font-size: 0.825rem; font-weight: 700; color: #dc2626;">
                                        {{ number_format($netQty, 2, ',', '.') }}
                                    </span>
                                @elseif($netQty > 0)
                                    <span style="font-family: monospace; font-size: 0.825rem; font-weight: 700; color: #16a34a;">
                                        +{{ number_format($netQty, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span style="font-family: monospace; font-size: 0.825rem; color: #64748b;">0,00</span>
                                @endif
                            </td>

                            {{-- 7. Net Dampak Nilai (Rp) --}}
                            <td style="text-align: right; padding: 0.65rem 0.75rem;">
                                <strong style="font-size: 0.9rem; font-family: monospace; color: {{ $netNilai < 0 ? '#dc2626' : ($netNilai > 0 ? '#16a34a' : '#0f172a') }};">
                                    {{ $netNilai < 0 ? '-' : ($netNilai > 0 ? '+' : '') }}Rp {{ number_format(abs($netNilai), 0, ',', '.') }}
                                </strong>
                            </td>

                            {{-- 8. Status Dokumen --}}
                            <td style="text-align: center; padding: 0.65rem 0.75rem;">
                                @if($hdr->status_cd === 'VOID')
                                    <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-size: 0.7rem; font-weight: 800;">VOID</span>
                                @else
                                    <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 0.7rem; font-weight: 800;">POSTED</span>
                                @endif
                            </td>

                            {{-- 9. Catatan Berita Acara (SEBELUM AKSI) --}}
                            <td style="padding: 0.65rem 0.75rem;">
                                @if($hdr->catatan_txt)
                                    <span style="font-size: 0.8rem; color: #475569; line-height: 1.35; display: block;">
                                        {{ $hdr->catatan_txt }}
                                    </span>
                                @else
                                    <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">-</span>
                                @endif
                            </td>

                            {{-- 10. Aksi Dropdown --}}
                            <td style="text-align: center; padding: 0.65rem 0.75rem; position: relative;">
                                <button type="button" class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, 'menuHdr_{{ $hdr->adj_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="menuHdr_{{ $hdr->adj_id }}" class="action-dropdown-menu">
                                    <a href="{{ route('gudang.adjustment.show', $hdr->adj_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Berita Acara</span>
                                    </a>

                                    @if($hdr->status_cd !== 'VOID')
                                        <button type="button" class="action-dropdown-item danger-item" 
                                                onclick="openVoidModal({{ $hdr->adj_id }}, '{{ $hdr->adj_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            <span>Batalkan (Void)</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat Berita Acara penyesuaian stok. Klik tombol <strong>"+ Catat Penyesuaian Stok"</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- FOOTER & PAGINATION --}}
    @if ($dataList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $dataList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- INCLUDE MODAL KONFIRMASI VOID --}}
@include('gudang.adjustment.partials.modal-void')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/adjustment/adjustment-index.js') }}"></script>
@endpush
