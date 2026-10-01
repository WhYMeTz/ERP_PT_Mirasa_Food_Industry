@extends('layouts.app')

@section('title', 'Penerimaan Barang & Bahan Baku (GRN) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/terima/terima-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Penerimaan Barang &amp; Bahan Baku (GRN)</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Buku catatan fisik bongkar muat bahan baku masuk ke gudang, nomor batch, dan tanggal kadaluarsa (Inbound FIFO).
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('gudang.terima.export-rekap-pdf', request()->query()) }}" target="_blank" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#ffffff'" title="Buka & Cetak Laporan Rekapitulasi Dokumen PDF Resmi">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Cetak Rekap (PDF)</span>
        </a>
        <a href="{{ route('gudang.terima.export-excel', request()->query()) }}" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" onmouseover="this.style.background='#ecfdf5'" onmouseout="this.style.background='#ffffff'" title="Download Rekap Barang Masuk format Excel (.xlsx)">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export Excel</span>
        </a>
        @if (Auth::user()?->canCreateTerima())
            <button type="button" onclick="document.getElementById('modal-import-excel').style.display='flex'" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #7c3aed; color: #7c3aed; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" onmouseover="this.style.background='#faf5ff'" onmouseout="this.style.background='#ffffff'" title="Import data GRN dari file Excel">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import Excel</span>
            </button>
        @endif
        @if (Auth::user()?->canCreateTerima())
            <a href="{{ route('gudang.terima.create') }}" class="btn btn-primary" style="background: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Penerimaan Barang
            </a>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL (INFORMATIF & SERAGAM DENGAN PO) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- SEMUA PENERIMAAN (GRN) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    Semua Dokumen GRN
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpiCounts['total'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #475569;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total fisik bongkar muat tercatat
        </div>
    </div>

    {{-- PENERIMAAN HARI INI --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Penerimaan Hari Ini
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpiCounts['today'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Armada / truk tiba hari ini
        </div>
    </div>

    {{-- DARI DOKUMEN PO --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Penerimaan via PO
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpiCounts['po'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Realisasi pesanan resmi pabrik
        </div>
    </div>

    {{-- NON-PO / LANGSUNG --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706;">
                    Penerimaan Non-PO
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpiCounts['direct'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Pembelian langsung / darurat
        </div>
    </div>
</div>

{{-- WADAH TABEL UTAMA (PERSIS FORMAT CARD PO) --}}
<div class="card">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        {{-- Form Filter & Pencarian Fleksibel --}}
        <form action="{{ route('gudang.terima.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 650px; width: 100%;">
            <input type="hidden" name="view" value="{{ $viewType }}">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari No GRN, SJ, supplier, batch, atau bahan..." class="form-control" style="padding: 0.45rem 0.75rem; max-width: 300px; font-size: 0.85rem;">
            
            <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 180px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Gudang --</option>
                @foreach ($gudangList as $gdg)
                    <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                        {{ $gdg->display_name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($search) || !empty($gudangId))
                <a href="{{ route('gudang.terima.index', ['view' => $viewType]) }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            {{-- Switcher Bersih & Halus (Tanpa Emoji) --}}
            <div style="display: inline-flex; background: #f1f5f9; padding: 0.2rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                <a href="{{ route('gudang.terima.index', array_merge(request()->query(), ['view' => 'item'])) }}" 
                   style="text-decoration: none; padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background: {{ $viewType === 'item' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'item' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'item' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                    Buku Rekap Bahan (Excel Grid)
                </a>
                <a href="{{ route('gudang.terima.index', array_merge(request()->query(), ['view' => 'header'])) }}" 
                   style="text-decoration: none; padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background: {{ $viewType === 'header' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'header' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'header' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                    Daftar Dokumen GRN
                </a>
            </div>

            <span style="color: #64748b; font-size: 0.85rem;">
                Total: <strong style="color: #0f172a;">{{ $dataList->total() }}</strong> {{ $viewType === 'item' ? 'baris data' : 'dokumen' }}
            </span>
        </div>
    </div>

    @if ($viewType === 'item')
        {{-- VIEW 1: BUKU REKAP BAHAN MASUK (EXCEL GRID COMPREHENSIVE YANG DISUKAI OPERATOR) --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 140px;">Tgl &amp; No. GRN</th>
                        <th style="min-width: 170px;">Supplier &amp; PO</th>
                        <th style="min-width: 190px;">Bahan Masuk</th>
                        <th style="min-width: 140px;">No. Batch &amp; Exp</th>
                        <th style="min-width: 110px; text-align: right;">Qty Masuk</th>
                        <th style="min-width: 130px;">Gudang Simpan</th>
                        <th style="min-width: 130px; text-align: right;">Total Nilai</th>
                        <th style="width: 100px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $row)
                        @php
                            $subtotal = (float) ($row->subtotal_netto ?: ((float) $row->terima_qty * (float) $row->harga_nominal));
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                {{ $dataList->firstItem() + $index }}
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #334155; font-size: 0.85rem;">
                                    {{ \Carbon\Carbon::parse($row->header?->terima_tgl)->format('d/m/Y') }}
                                </div>
                                <a href="{{ route('gudang.terima.show', $row->header?->terima_id) }}" style="font-size: 0.85rem; font-weight: 700; color: #0284c7; text-decoration: none;">
                                    {{ $row->header?->terima_no ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                                    {{ $row->header?->supplier?->supplier_nm ?? '-' }}
                                </div>
                                @if ($row->header?->po)
                                    <small style="color: #0369a1; font-weight: 600; font-size: 0.725rem;">
                                        PO: {{ $row->header->po->po_no }}
                                    </small>
                                @else
                                    <small style="color: #94a3b8; font-style: italic; font-size: 0.725rem;">
                                        Non-PO
                                    </small>
                                @endif
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
                                <span style="font-family: monospace; font-size: 0.825rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; display: inline-block;">
                                    {{ $row->batch_no }}
                                </span>
                                @if ($row->expired_tgl)
                                    <small style="display: block; font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">
                                        Exp: {{ \Carbon\Carbon::parse($row->expired_tgl)->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <span style="font-weight: 700; color: #059669; font-size: 0.95rem;">
                                    {{ number_format((float) $row->terima_qty, 2, ',', '.') }}
                                </span>
                                <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.2rem;">
                                    {{ $row->barang?->satuanDasar?->satuan_nm ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #334155;">
                                    {{ $row->header?->gudang?->gudang_nm ?? '-' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                </div>
                                <small style="color: #64748b; font-size: 0.725rem;">
                                    @ Rp {{ number_format((float) ($row->harga_netto ?: $row->harga_nominal), 0, ',', '.') }}
                                    @if((float) ($row->diskon_persen ?? 0) > 0)
                                        <span style="color: #d97706; font-weight: 600;">(Disc {{ number_format((float)$row->diskon_persen, 1) }}%)</span>
                                    @endif
                                </small>
                            </td>
                            <td style="text-align: right; position: relative;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, 'dropdown-item-{{ $row->terimadtl_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="dropdown-item-{{ $row->terimadtl_id }}" class="action-dropdown-menu">
                                    <a href="{{ route('gudang.terima.show', $row->header?->terima_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Surat Jalan / GRN</span>
                                    </a>
                                    <a href="{{ route('gudang.terima.export-pdf', $row->header?->terima_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span>Cetak PDF (GRN)</span>
                                    </a>
                                    @if ($row->header?->po_id)
                                        <a href="{{ route('gudang.po.show', $row->header->po_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Lihat PO Terkait</span>
                                        </a>
                                    @endif
                                    @if ($row->header?->gudang_id)
                                        <a href="{{ route('gudang.stok.index', ['gudang_id' => $row->header->gudang_id, 'search' => $row->batch_no]) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span>Cek Stok Batch di Gudang</span>
                                        </a>
                                    @endif
                                    @if (Auth::user()?->isSuperAdmin() || Auth::user()?->hasPermission('terima_create'))
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteTerimaModal({{ $row->header?->terima_id }}, '{{ $row->header?->terima_no }}', '{{ addslashes($row->header?->supplier?->supplier_nm ?? '') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Batalkan Dokumen</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat penerimaan barang fisik. Klik tombol <strong>"+ Catat Penerimaan Barang"</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        {{-- VIEW 2: DAFTAR DOKUMEN GRN (HEADER VIEW) --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 160px;">Nomor Penerimaan</th>
                        <th style="min-width: 120px;">Tanggal Masuk</th>
                        <th style="min-width: 130px;">Ref. PO</th>
                        <th style="min-width: 180px;">Supplier Pengirim</th>
                        <th style="min-width: 140px;">Gudang Simpan</th>
                        <th style="min-width: 110px; text-align: center;">Total Item</th>
                        <th style="min-width: 130px; text-align: right;">Total Tagihan</th>
                        <th style="width: 100px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $item)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                {{ $dataList->firstItem() + $index }}
                            </td>
                            <td>
                                <a href="{{ route('gudang.terima.show', $item->terima_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                    {{ $item->terima_no }}
                                </a>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #334155; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($item->terima_tgl)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                @if ($item->po)
                                    <a href="{{ route('gudang.po.show', $item->po_id) }}" style="color: #0369a1; text-decoration: none; font-weight: 600; font-size: 0.85rem;">
                                        {{ $item->po->po_no }}
                                    </a>
                                @else
                                    <span style="color: #94a3b8; font-style: italic; font-size: 0.85rem;">Non-PO</span>
                                @endif
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-size: 0.875rem;">{{ $item->supplier?->supplier_nm ?? '-' }}</strong>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #334155;">{{ $item->gudang?->gudang_nm ?? '-' }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 700;">
                                    {{ $item->details->count() }} Bahan
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <strong style="color: #0f172a; font-size: 0.9rem; font-family: monospace;">
                                    Rp {{ number_format((float) ($item->total_tagihan ?: $item->total_nominal), 0, ',', '.') }}
                                </strong>
                                @if($item->ppn_tipe === 'PPN_11')
                                    <small style="display: block; font-size: 0.7rem; color: #0284c7; font-weight: 600;">+ PPN 11%</small>
                                @endif
                            </td>
                            <td style="text-align: right; position: relative;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, 'dropdown-hdr-{{ $item->terima_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="dropdown-hdr-{{ $item->terima_id }}" class="action-dropdown-menu">
                                    <a href="{{ route('gudang.terima.show', $item->terima_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Detail Dokumen</span>
                                    </a>
                                    <a href="{{ route('gudang.terima.export-pdf', $item->terima_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span>Cetak PDF (GRN)</span>
                                    </a>
                                    @if ($item->po_id)
                                        <a href="{{ route('gudang.po.show', $item->po_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Lihat PO Terkait</span>
                                        </a>
                                    @endif
                                    @if ($item->gudang_id)
                                        <a href="{{ route('gudang.stok.index', ['gudang_id' => $item->gudang_id]) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span>Lihat Stok di Gudang Ini</span>
                                        </a>
                                    @endif
                                    @if (Auth::user()?->isSuperAdmin() || Auth::user()?->hasPermission('terima_create'))
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteTerimaModal({{ $item->terima_id }}, '{{ $item->terima_no }}', '{{ addslashes($item->supplier?->supplier_nm ?? '') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Batalkan Dokumen</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat dokumen penerimaan barang.
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

@include('gudang.terima.partials.modal-import-excel')
@include('gudang.terima.partials.modal-delete-confirm')

@push('scripts')
<script src="{{ asset('js/gudang/terima/terima-index.js') }}"></script>
@endpush
