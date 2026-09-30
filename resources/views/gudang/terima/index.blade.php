@extends('layouts.app')

@section('title', 'Barang Masuk & Penerimaan Fisik (Goods Receipt) - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Barang Masuk &amp; Penerimaan Fisik (GRN)</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Buku catatan fisik bongkar muat bahan baku masuk ke gudang, nomor batch, dan tanggal kadaluarsa (Inbound FIFO).
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('gudang.terima.export-rekap-pdf', request()->query()) }}" target="_blank" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.35rem;" title="Download Laporan Rekapitulasi Barang Masuk (PDF)">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export Rekap (PDF)
        </a>
        @if (Auth::user()?->canCreateTerima())
            <a href="{{ route('gudang.terima.create') }}" class="btn btn-primary" style="background: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Barang Masuk
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
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                    <a href="{{ route('gudang.terima.show', $row->header?->terima_id) }}" class="btn btn-secondary btn-sm" title="Lihat Dokumen Lengkap">
                                        Detail
                                    </a>
                                    <a href="{{ route('gudang.terima.export-pdf', $row->header?->terima_id) }}" target="_blank" class="btn btn-sm" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600;" title="Export PDF Bukti Terima (GRN)">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat penerimaan barang fisik. Klik tombol <strong>"+ Catat Barang Masuk"</strong> di atas.
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
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                    <a href="{{ route('gudang.terima.show', $item->terima_id) }}" class="btn btn-secondary btn-sm" title="Lihat Dokumen Lengkap">
                                        Detail
                                    </a>
                                    <a href="{{ route('gudang.terima.export-pdf', $item->terima_id) }}" target="_blank" class="btn btn-sm" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600;" title="Export PDF Bukti Terima (GRN)">
                                        PDF
                                    </a>
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
