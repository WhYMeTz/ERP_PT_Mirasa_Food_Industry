@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Purchase Order (PO) Pengadaan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Kelola dokumen pemesanan bahan baku singkong, minyak, bumbu, dan kemasan ke mitra supplier.
        </p>
    </div>
    <div>
        <a href="{{ route('gudang.po.create') }}" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat PO Baru
        </a>
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL (INFORMATIF) --}}
@php
    $currentStatus = $status ?? '';
@endphp
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- SEMUA PO --}}
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
            Total transaksi pengadaan aktif
        </div>
    </div>

    {{-- MENUNGGU PENGIRIMAN (APPROVED) --}}
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
            PO disetujui &bull; Supplier siap kirim
        </div>
    </div>

    {{-- MASUK SEBAGIAN (PARTIAL) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706;">
                    Masuk Sebagian (Parsial)
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
            Truk sudah tiba &bull; Masih ada sisa
        </div>
    </div>

    {{-- SELESAI / DITUTUP --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Selesai &amp; Ditutup
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($statusCounts['completed'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Pengadaan selesai &bull; Arsip tuntas
        </div>
    </div>
</div>

{{-- KARTU TABEL UTAMA --}}
<div class="card">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('gudang.po.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 650px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nomor PO atau nama supplier..." class="form-control" style="padding: 0.45rem 0.75rem; max-width: 280px; font-size: 0.85rem;">
            
            <select name="status" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 175px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="APPROVED" {{ ($currentStatus == 'APPROVED') ? 'selected' : '' }}>Disetujui (Approved)</option>
                <option value="PARTIAL" {{ ($currentStatus == 'PARTIAL') ? 'selected' : '' }}>Sebagian Diterima</option>
                <option value="COMPLETED_CLOSED" {{ ($currentStatus == 'COMPLETED_CLOSED') ? 'selected' : '' }}>Selesai / Ditutup</option>
                <option value="COMPLETED" {{ ($currentStatus == 'COMPLETED') ? 'selected' : '' }}>Selesai (Completed)</option>
                <option value="CLOSED" {{ ($currentStatus == 'CLOSED') ? 'selected' : '' }}>Ditutup (Closed)</option>
                <option value="DRAFT" {{ ($currentStatus == 'DRAFT') ? 'selected' : '' }}>Draft</option>
                <option value="CANCELLED" {{ ($currentStatus == 'CANCELLED') ? 'selected' : '' }}>Dibatalkan</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($search) || !empty($currentStatus))
                <a href="{{ route('gudang.po.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif
        </form>

        <span style="color: #64748b; font-size: 0.85rem;">
            Total PO: <strong style="color: #0f172a;">{{ $poList->total() }}</strong> dokumen
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="min-width: 180px;">Nomor PO</th>
                    <th style="min-width: 120px;">Tanggal</th>
                    <th style="min-width: 180px;">Supplier Mitra</th>
                    <th style="min-width: 140px;">Gudang Tujuan</th>
                    <th style="min-width: 200px;">Status &amp; Realisasi</th>
                    <th style="min-width: 130px; text-align: right;">Total Nominal</th>
                    <th style="width: 160px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($poList as $index => $po)
                    @php
                        $pct = $po->persentase_terima;
                        $totalPesan = (float) $po->details->sum('pesan_qty');
                        $totalTerima = (float) $po->details->sum('terima_qty');
                        $totalSisa = (float) $po->total_sisa_qty;
                        $canReceive = in_array($po->status_cd, ['APPROVED', 'PARTIAL']) && $totalSisa > 0;

                        // Siapkan payload JSON aman untuk Quick Receive Modal
                        $poPayload = [
                            'po_id' => $po->po_id,
                            'po_no' => $po->po_no,
                            'supplier_id' => $po->supplier_id,
                            'supplier_nm' => $po->supplier?->supplier_nm ?? '-',
                            'gudang_id' => $po->gudang_id,
                            'gudang_nm' => $po->gudang?->gudang_nm ?? '-',
                            'items' => $po->details->map(function ($dtl) {
                                $acronym = app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym(
                                    $dtl->barang?->barang_nm,
                                    $dtl->barang?->barang_cd
                                );
                                return [
                                    'podtl_id'      => $dtl->podtl_id,
                                    'barang_id'     => $dtl->barang_id,
                                    'barang_nm'     => $dtl->barang?->barang_nm ?? '-',
                                    'batch_prefix'  => ($acronym ?: 'BRG') . '-',
                                    'satuan_nm'     => $dtl->barang?->satuanDasar?->satuan_nm ?? ($dtl->barang?->satuanDasar?->satuan_cd ?? '-'),
                                    'pesan_qty'     => (float) $dtl->pesan_qty,
                                    'terima_qty'    => (float) $dtl->terima_qty,
                                    'sisa_qty'      => (float) $dtl->sisa_qty,
                                    'harga_nominal' => (float) $dtl->harga_nominal,
                                ];
                            })->values(),
                        ];
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                            {{ $poList->firstItem() + $index }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                {{-- Tombol expand preview item tanpa pindah halaman --}}
                                <button type="button" 
                                        onclick="togglePoRow('row-items-{{ $po->po_id }}', this)" 
                                        title="Intip rincian barang"
                                        style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; padding: 0; flex-shrink: 0;">
                                    <svg class="chevron-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: transform 0.15s ease;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <div>
                                    <a href="{{ route('gudang.po.show', $po->po_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                        {{ $po->po_no }}
                                    </a>
                                    <small style="display: block; font-size: 0.725rem; color: #64748b;">
                                        {{ $po->details->count() }} item bahan baku
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155; font-size: 0.85rem;">
                                {{ \Carbon\Carbon::parse($po->po_tgl)->format('d/m/Y') }}
                            </div>
                            @if ($po->tgl_estimasi_datang)
                                <small style="display: block; font-size: 0.725rem; color: #0369a1;">
                                    ETA: {{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d/m/Y') }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.875rem;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                            <small style="display: block; font-size: 0.725rem; color: #64748b;">{{ $po->supplier?->supplier_cd }}</small>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: #334155;">{{ $po->gudang?->gudang_nm ?? '-' }}</span>
                        </td>
                        <td>
                            {{-- BADGE STATUS & PROGRESS BAR REALISASI FISIK --}}
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <div>
                                    @if ($po->status_cd == 'COMPLETED')
                                        <span class="badge badge-success">Selesai (100%)</span>
                                    @elseif ($po->status_cd == 'PARTIAL')
                                        <span class="badge badge-info">Sebagian Diterima ({{ $pct }}%)</span>
                                    @elseif ($po->status_cd == 'CLOSED')
                                        <span class="badge" style="background:#f1f5f9; color:#475569; border: 1px solid #cbd5e1;">Ditutup ({{ $pct }}%)</span>
                                    @elseif ($po->status_cd == 'APPROVED')
                                        <span class="badge" style="background:#e0f2fe; color:#0369a1;">Disetujui</span>
                                    @elseif ($po->status_cd == 'DRAFT')
                                        <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
                                    @else
                                        <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
                                    @endif
                                </div>

                                {{-- Progress Bar Ramping --}}
                                @php
                                    $barBg = '#e2e8f0';
                                    $barFill = '#0284c7';
                                    if ($po->status_cd == 'COMPLETED') {
                                        $barFill = '#10b981';
                                    } elseif ($po->status_cd == 'PARTIAL') {
                                        $barFill = '#f59e0b';
                                    } elseif ($po->status_cd == 'CLOSED') {
                                        $barFill = '#94a3b8';
                                    }
                                @endphp
                                <div style="width: 100%; max-width: 160px; height: 5px; background: {{ $barBg }}; border-radius: 9999px; overflow: hidden; margin-top: 2px;">
                                    <div style="width: {{ min(100, $pct) }}%; height: 100%; background: {{ $barFill }}; border-radius: 9999px;"></div>
                                </div>
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    @if ($po->status_cd == 'COMPLETED')
                                        Tuntas diterima
                                    @elseif ($po->status_cd == 'PARTIAL')
                                        Masuk {{ number_format($totalTerima, 0) }} / {{ number_format($totalPesan, 0) }}
                                    @elseif ($po->status_cd == 'APPROVED')
                                        Menunggu kedatangan truk
                                    @elseif ($po->status_cd == 'CLOSED')
                                        Realisasi {{ number_format($totalTerima, 0) }} / {{ number_format($totalPesan, 0) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        @php
                            $nominalRow = (float) $po->total_tagihan > 0 
                                ? (float) $po->total_tagihan 
                                : ((float) $po->total_nominal > 0 
                                    ? (float) $po->total_nominal 
                                    : (float) $po->details->sum(fn($d) => (float)$d->subtotal_tagihan > 0 ? (float)$d->subtotal_tagihan : ((float)$d->subtotal_nominal > 0 ? (float)$d->subtotal_nominal : (float)$d->pesan_qty * (float)$d->harga_nominal)));
                        @endphp
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                            Rp {{ number_format($nominalRow, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; position: relative;">
                            <div style="position: relative; display: inline-block;">
                                <button type="button" 
                                        class="btn btn-secondary btn-sm po-dropdown-trigger" 
                                        onclick="togglePoIndexDropdown(event, 'poDropdown-{{ $po->po_id }}')"
                                        style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.65rem; font-size: 0.8rem; font-weight: 600; border-radius: 6px; background: #ffffff; border: 1px solid #cbd5e1; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                {{-- DROPDOWN MENU ITEMS --}}
                                <div id="poDropdown-{{ $po->po_id }}" class="po-action-menu-dropdown" style="display: none; position: absolute; right: 0; top: calc(100% + 4px); background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12), 0 8px 10px -6px rgba(0,0,0,0.08); width: 220px; z-index: 1000; text-align: left; padding: 0.35rem 0; font-size: 0.825rem;">
                                    
                                    {{-- 1. LIHAT DETAIL --}}
                                    <a href="{{ route('gudang.po.show', $po->po_id) }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; color: #1e293b; text-decoration: none; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                        <svg width="15" height="15" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Detail PO</span>
                                    </a>

                                    {{-- 2. CETAK PDF RESMI (HACCP) --}}
                                    <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; color: #1e293b; text-decoration: none; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                        <svg width="15" height="15" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Cetak PDF Resmi (HACCP)</span>
                                    </a>

                                    {{-- 3. TERIMA BARANG (JIKA STATUS MEMUNGKINKAN) --}}
                                    @if ($canReceive)
                                        <button type="button" 
                                                onclick="openQuickReceiveIndexModal({{ json_encode($poPayload) }})"
                                                style="width: 100%; border: none; background: transparent; text-align: left; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; color: #059669; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                                                onmouseover="this.style.background='#ecfdf5'" onmouseout="this.style.background='transparent'">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Catat Terima Barang</span>
                                        </button>
                                    @endif

                                    <div style="border-top: 1px solid #f1f5f9; margin: 0.25rem 0;"></div>

                                    {{-- 4. EDIT PO --}}
                                    @php
                                        $canEdit = (Auth::user()?->isSuperAdmin() || Auth::user()?->canEditPo()) && (!in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED']) || Auth::user()?->isSuperAdmin());
                                    @endphp
                                    @if ($canEdit)
                                        <a href="{{ route('gudang.po.edit', $po->po_id) }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; color: #d97706; text-decoration: none; font-weight: 600; transition: background 0.15s;" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='transparent'">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit Purchase Order</span>
                                        </a>
                                    @endif

                                    {{-- 5. HAPUS PO --}}
                                    @php
                                        $canDelete = (Auth::user()?->isSuperAdmin() || Auth::user()?->canDeletePo()) && ($totalTerima == 0 || Auth::user()?->isSuperAdmin());
                                    @endphp
                                    @if ($canDelete)
                                        <form action="{{ route('gudang.po.destroy', $po->po_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS dokumen Purchase Order {{ $po->po_no }}? Tindakan ini tidak dapat dibatalkan.')" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="width: 100%; border: none; background: transparent; text-align: left; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; color: #dc2626; cursor: pointer; transition: background 0.15s; font-size: 0.825rem;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Hapus PO</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- EXPANDABLE SUB-ROW: RINCIAN ITEM BARANG TANPA PINDAH HALAMAN --}}
                    <tr id="row-items-{{ $po->po_id }}" style="display: none; background: #f8fafc;">
                        <td colspan="8" style="padding: 0.75rem 1.25rem; border-bottom: 2px solid #e2e8f0;">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <strong style="font-size: 0.825rem; color: #334155;">
                                        Rincian Item Pesanan ({{ $po->po_no }}):
                                    </strong>
                                    <a href="{{ route('gudang.po.show', $po->po_id) }}" style="font-size: 0.75rem; color: #0284c7; text-decoration: none;">
                                        Buka halaman detail penuh &rarr;
                                    </a>
                                </div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Nama Bahan Baku</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Satuan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Pesan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Sudah Masuk</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Sisa Belum Tiba</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Harga Satuan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($po->details as $item)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 0.4rem 0.5rem;">
                                                    <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                                                    <span style="display: block; font-size: 0.7rem; color: #64748b;">{{ $item->barang?->barang_cd }}</span>
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; color: #475569;">
                                                    {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600;">
                                                    {{ number_format((float) $item->pesan_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: #059669;">
                                                    {{ number_format((float) $item->terima_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: {{ (float) $item->sisa_qty > 0 ? '#b45309' : '#64748b' }};">
                                                    {{ number_format((float) $item->sisa_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; color: #475569;">
                                                    Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: #0f172a;">
                                                    Rp {{ number_format((float) $item->subtotal_nominal, 0, ',', '.') }}
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
                            Belum ada transaksi Purchase Order. Klik tombol <strong>"Buat PO Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($poList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $poList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL CEPAT CATAT BARANG MASUK (IN-PLACE QUICK RECEIVE) --}}
@include('gudang.po.partials.modal-quick-receive-index')

@endsection

@push('scripts')
<script>
    window.quickTerimaCreateRoute = "{{ route('gudang.terima.create') }}";
</script>
<script src="{{ asset('js/gudang/po/po-index.js') }}"></script>
@endpush
