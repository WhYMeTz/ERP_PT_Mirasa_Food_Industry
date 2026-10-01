@extends('layouts.app')

@section('title', 'Detail Purchase Order ' . $po->po_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-show.css') }}">
@endpush

@section('content')
{{-- TOP COMMAND HEADER --}}
<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.po.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar PO
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $po->po_no }}</h1>
            @if ($po->status_cd == 'COMPLETED')
                <span class="badge badge-success">Selesai (100%)</span>
            @elseif ($po->status_cd == 'PARTIAL')
                <span class="badge badge-info">Sebagian Diterima ({{ $po->persentase_terima }}%)</span>
            @elseif ($po->status_cd == 'CLOSED')
                <span class="badge" style="background:#f1f5f9; color:#475569; border: 1px solid #cbd5e1;">Ditutup ({{ $po->persentase_terima }}%)</span>
            @elseif ($po->status_cd == 'APPROVED')
                <span class="badge" style="background:#e0f2fe; color:#0369a1;">Disetujui</span>
            @elseif ($po->status_cd == 'DRAFT')
                <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
            @else
                <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
            @endif
        </div>
    </div>

    {{-- SINGLE UNIFIED ACTION DROPDOWN --}}
    <div style="position: relative; display: inline-block;">
        <button type="button" 
                class="btn btn-primary show-dropdown-trigger" 
                onclick="toggleShowActionMenu(event)"
                style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
            <span>Menu Aksi PO</span>
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </button>

        {{-- DROPDOWN MENU ITEMS --}}
        <div id="showActionMenuDropdown" class="show-action-menu-dropdown" style="display: none; position: absolute; right: 0; top: calc(100% + 6px); background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); width: 250px; z-index: 1000; overflow: hidden; padding: 0.4rem 0; font-size: 0.85rem; text-align: left;">
            
            {{-- 1. TERIMA BARANG (JIKA STATUS MEMUNGKINKAN) --}}
            @if (in_array($po->status_cd, ['APPROVED', 'PARTIAL']) && $po->total_sisa_qty > 0)
                <button type="button" onclick="openQuickReceiveModal(); toggleShowActionMenu(event);" style="width: 100%; border: none; background: transparent; text-align: left; display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; color: #059669; font-weight: 700; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#ecfdf5'" onmouseout="this.style.background='transparent'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Catat Terima Barang</span>
                </button>
            @endif

            {{-- 2. CETAK PDF RESMI (HACCP) --}}
            <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; color: #1e293b; text-decoration: none; font-weight: 600; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <svg width="16" height="16" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Cetak PDF Resmi (HACCP)</span>
            </a>

            {{-- 3. PRINT BROWSER --}}
            <button type="button" onclick="window.print(); toggleShowActionMenu(event);" style="width: 100%; border: none; background: transparent; text-align: left; display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; color: #475569; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Dokumen (Browser)</span>
            </button>

            <div style="border-top: 1px solid #f1f5f9; margin: 0.25rem 0;"></div>

            {{-- 4. EDIT PO --}}
            @php
                $canEdit = (Auth::user()?->isSuperAdmin() || Auth::user()?->canEditPo()) && (!in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED']) || Auth::user()?->isSuperAdmin());
            @endphp
            @if ($canEdit)
                <a href="{{ route('gudang.po.edit', $po->po_id) }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; color: #d97706; text-decoration: none; font-weight: 600; transition: background 0.15s;" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='transparent'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Purchase Order</span>
                </a>
            @endif

            {{-- 5. TUTUP PO (SELESAI PARSIAL) --}}
            @if ($po->status_cd == 'PARTIAL')
                <button type="button" onclick="openForceCloseModal(); toggleShowActionMenu(event);" style="width: 100%; border: none; background: transparent; text-align: left; display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; color: #475569; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Tutup PO (Selesai Parsial)</span>
                </button>
            @endif

            {{-- 6. BATALKAN PO --}}
            @if (in_array($po->status_cd, ['DRAFT', 'APPROVED']) && $po->details->sum('terima_qty') == 0)
                <form action="{{ route('gudang.po.cancel', $po->po_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan Purchase Order ini?')" style="margin: 0;">
                    @csrf
                    <button type="submit" style="width: 100%; border: none; background: transparent; text-align: left; display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; color: #ea580c; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#fff7ed'" onmouseout="this.style.background='transparent'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span>Batalkan PO</span>
                    </button>
                </form>
            @endif

            {{-- 7. HAPUS PO --}}
            @php
                $canDelete = (Auth::user()?->isSuperAdmin() || Auth::user()?->canDeletePo()) && ($po->details->sum('terima_qty') == 0 || Auth::user()?->isSuperAdmin());
            @endphp
            @if ($canDelete)
                <div style="border-top: 1px solid #f1f5f9; margin: 0.25rem 0;"></div>
                <form action="{{ route('gudang.po.destroy', $po->po_id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS dokumen Purchase Order {{ $po->po_no }}? Tindakan ini tidak dapat dibatalkan.')" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="width: 100%; border: none; background: transparent; text-align: left; display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; color: #dc2626; font-weight: 600; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus PO</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- BUNGKUS KONTEN DASHBOARD WEB DENGAN NO-PRINT AGAR TIDAK MUNCUL SAAT CETAK BROWSER --}}
<div class="no-print">

{{-- ALERT JIKA PO SUDAH DITUTUP --}}
@if ($po->status_cd == 'CLOSED')
    <div class="alert" style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #475569; color: #334155; margin-bottom: 1.25rem; padding: 0.875rem 1.25rem; border-radius: 6px;">
        <div style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">Dokumen Purchase Order Telah Ditutup (Closed)</div>
        <div style="font-size: 0.85rem; color: #475569; margin-top: 0.25rem;">
            Ditutup pada: <strong>{{ $po->closed_at ? \Carbon\Carbon::parse($po->closed_at)->format('d/m/Y H:i') : '-' }}</strong> &bull; Oleh: <strong>{{ $po->closed_by ?? '-' }}</strong>
        </div>
        @if ($po->closed_reason)
            <div style="margin-top: 0.35rem; font-size: 0.85rem; color: #64748b; background: #ffffff; padding: 0.4rem 0.65rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                <strong>Alasan Penutupan:</strong> {{ $po->closed_reason }}
            </div>
        @endif
    </div>
@endif

{{-- 4 KARTU METRIK OPERASIONAL PO --}}
@php
    $pct = $po->persentase_terima;
    $totalPesan = (float) $po->details->sum('pesan_qty');
    $totalTerima = (float) $po->details->sum('terima_qty');
    $totalSisa = (float) $po->total_sisa_qty;

    $totalSubtotalItems = (float) $po->details->sum(function($d) {
        $subTagihan = (float) ($d->subtotal_tagihan ?? 0);
        $subNominal = (float) ($d->subtotal_nominal ?? 0);
        $subNetto = (float) ($d->subtotal_netto ?? 0);
        $calc = (float) $d->pesan_qty * (float) $d->harga_nominal;
        if ($subTagihan > 0) return $subTagihan;
        if ($subNominal > 0) return $subNominal;
        if ($subNetto > 0) return $subNetto;
        return $calc;
    });

    $totalTagihanHdr = (float) ($po->total_tagihan ?? 0);
    $totalNominalHdr = (float) ($po->total_nominal ?? 0);
    $displayTotalPO = $totalTagihanHdr > 0 
        ? $totalTagihanHdr 
        : ($totalNominalHdr > 0 ? $totalNominalHdr : $totalSubtotalItems);
@endphp
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- TOTAL NOMINAL PO --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Total Nilai Pesanan
        </span>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            Rp {{ number_format($displayTotalPO, 0, ',', '.') }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            {{ $po->details->count() }} item bahan baku &bull; {{ number_format($totalPesan, 0) }} total kuantitas
        </div>
    </div>

    {{-- REALISASI NOMINAL DITERIMA --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Realisasi Penerimaan Barang
            </span>
            <span style="font-size: 0.8rem; font-weight: 700; color: {{ $pct >= 100 ? '#059669' : ($pct > 0 ? '#d97706' : '#64748b') }};">
                {{ $pct }}%
            </span>
        </div>
        <div style="font-size: 1.35rem; font-weight: 700; color: {{ $pct >= 100 ? '#059669' : '#0f172a' }}; margin-top: 0.25rem;">
            Rp {{ number_format((float) $po->realisasi_nominal, 0, ',', '.') }}
        </div>
        {{-- Progress Bar --}}
        <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-top: 0.45rem;">
            <div style="width: {{ min(100, $pct) }}%; height: 100%; background: {{ $pct >= 100 ? '#10b981' : ($pct > 0 ? '#f59e0b' : '#cbd5e1') }};"></div>
        </div>
    </div>

    {{-- TOTAL SISA FISIK BELUM DATANG --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Sisa Fisik Belum Tiba
        </span>
        <div style="font-size: 1.35rem; font-weight: 700; color: {{ $totalSisa > 0 ? '#b45309' : '#059669' }}; margin-top: 0.25rem;">
            {{ number_format($totalSisa, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            @if ($totalSisa > 0)
                Menunggu kiriman supplier berikutnya
            @else
                Seluruh barang telah diterima lengkap
            @endif
        </div>
    </div>

    {{-- JADWAL PO & ESTIMASI TIBA --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Jadwal &amp; Estimasi Tiba
        </span>
        <div style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            {{ \Carbon\Carbon::parse($po->po_tgl)->format('d/m/Y') }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            Estimasi Tiba: 
            @if ($po->tgl_estimasi_datang)
                <strong style="color: #0284c7;">{{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d/m/Y') }}</strong>
            @else
                <span>-</span>
            @endif
        </div>
    </div>
</div>

{{-- MASTER-DETAIL WORKSPACE: 2 KOLOM (KIRI: RINCIAN & HISTORI, KANAN: SIDEBAR INFO MITRA) --}}
<div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 1.5rem; align-items: start; margin-bottom: 2rem;">
    {{-- KOLOM KIRI (KONTEN UTAMA) --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        {{-- KARTU 1: RINCIAN ITEM BARANG YANG DIPESAN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">Rincian Barang yang Dipesan</strong>
                    <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;">({{ $po->details->count() }} Item Bahan)</span>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Nama Komoditas / Bahan Baku</th>
                            <th>Satuan</th>
                            <th style="text-align: right;">Pesan</th>
                            <th style="text-align: right;">Diterima</th>
                            <th style="text-align: right;">Sisa</th>
                            <th style="text-align: right;">Harga Satuan</th>
                            <th style="text-align: right;">Diskon</th>
                            <th style="text-align: right;">Potongan</th>
                            <th style="text-align: center;">PPN</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po->details as $index => $item)
                            @php
                                $diskonPct = (float) ($item->diskon_persen ?? 0);
                                $potNom = (float) ($item->potongan_nominal ?? 0);
                                $isPpn = ($item->ppn_tipe ?? '') === 'PPN_11';
                                
                                $subTagihan = (float) ($item->subtotal_tagihan ?? 0);
                                $subNominal = (float) ($item->subtotal_nominal ?? 0);
                                $subNetto = (float) ($item->subtotal_netto ?? 0);
                                $calcManual = (float)$item->pesan_qty * (float)$item->harga_nominal;

                                if ($subTagihan > 0) {
                                    $subtotalRow = $subTagihan;
                                } elseif ($subNominal > 0) {
                                    $subtotalRow = $subNominal;
                                } elseif ($subNetto > 0) {
                                    $subtotalRow = $subNetto;
                                } else {
                                    $subtotalRow = $calcManual;
                                }
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="text-align: center; color: #64748b; font-size: 0.85rem;">{{ $index + 1 }}</td>
                                <td>
                                    <strong style="color: #0f172a; font-size: 0.875rem;">{{ $item->barang?->barang_nm }}</strong>
                                    <span style="display: block; font-size: 0.725rem; color: #64748b; font-family: monospace;">{{ $item->barang?->barang_cd }}</span>
                                </td>
                                <td style="color: #475569; font-size: 0.85rem;">
                                    {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                </td>
                                <td style="text-align: right; font-weight: 600; font-size: 0.875rem;">
                                    {{ number_format((float) $item->pesan_qty, 2) }}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #059669; font-size: 0.875rem;">
                                    {{ number_format((float) $item->terima_qty, 2) }}
                                </td>
                                <td style="text-align: right; font-weight: 600; font-size: 0.875rem; color: {{ (float) $item->sisa_qty > 0 ? '#b45309' : '#64748b' }};">
                                    {{ number_format((float) $item->sisa_qty, 2) }}
                                </td>
                                <td style="text-align: right; font-size: 0.85rem; color: #475569; font-family: monospace;">
                                    Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $diskonPct > 0 ? '#d97706' : '#94a3b8' }};">
                                    {{ $diskonPct > 0 ? number_format($diskonPct, 1, ',', '.') . '%' : '-' }}
                                </td>
                                <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $potNom > 0 ? '#dc2626' : '#94a3b8' }};">
                                    {{ $potNom > 0 ? 'Rp ' . number_format($potNom, 0, ',', '.') : '-' }}
                                </td>
                                <td style="text-align: center;">
                                    @if ($isPpn)
                                        <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:0.7rem; font-weight:700;">PPN 11%</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:0.7rem;">Non-PPN</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;">
                                    Rp {{ number_format($subtotalRow, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0; font-weight: 700;">
                        <tr>
                            <td colspan="3" style="text-align: right; padding: 0.75rem 1rem; color: #475569; font-size: 0.85rem;">
                                Total Kuantitas &amp; Subtotal Keseluruhan:
                            </td>
                            <td style="text-align: right; padding: 0.75rem 0.5rem; color: #0f172a; font-size: 0.875rem;">
                                {{ number_format($totalPesan, 2) }}
                            </td>
                            <td style="text-align: right; padding: 0.75rem 0.5rem; color: #059669; font-size: 0.875rem;">
                                {{ number_format($totalTerima, 2) }}
                            </td>
                            <td style="text-align: right; padding: 0.75rem 0.5rem; color: {{ $totalSisa > 0 ? '#b45309' : '#64748b' }}; font-size: 0.875rem;">
                                {{ number_format($totalSisa, 2) }}
                            </td>
                            <td colspan="4"></td>
                            <td style="text-align: right; padding: 0.75rem 1rem; color: #0f172a; font-size: 1rem; font-family: monospace;">
                                Rp {{ number_format($displayTotalPO, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- SUMMARY BREAKDOWN KEUANGAN PO: DISKON, POTONGAN, DPP & PPN --}}
            @php
                $calcSubtotalBruto = (float) ($po->subtotal_bruto ?? 0) > 0 
                    ? (float) $po->subtotal_bruto 
                    : (float) $po->details->sum(fn($d) => (float)$d->pesan_qty * (float)$d->harga_nominal);

                if ($calcSubtotalBruto <= 0 && $displayTotalPO > 0) {
                    $calcSubtotalBruto = $displayTotalPO;
                }

                $calcDiskonItem = (float) ($po->diskon_total ?? 0) > 0 
                    ? (float) $po->diskon_total 
                    : (float) $po->details->sum(fn($d) => (float)$d->pesan_qty * (float)($d->diskon_nominal ?? 0));

                $calcPotongan = (float) ($po->potongan_nominal ?? 0) > 0 
                    ? (float) $po->potongan_nominal 
                    : (float) $po->details->sum(fn($d) => (float)($d->potongan_nominal ?? 0));

                $calcDpp = (float) ($po->dpp_nominal ?? 0) > 0 
                    ? (float) $po->dpp_nominal 
                    : max(0, $calcSubtotalBruto - $calcDiskonItem - $calcPotongan);

                $calcPpn = (float) ($po->ppn_nominal ?? 0) > 0 
                    ? (float) $po->ppn_nominal 
                    : (float) $po->details->sum(fn($d) => (float)($d->ppn_nominal ?? 0));

                $grandTotalTagihan = $totalTagihanHdr > 0 
                    ? $totalTagihanHdr 
                    : ($totalNominalHdr > 0 ? $totalNominalHdr : ($calcDpp + $calcPpn));

                if ($grandTotalTagihan <= 0 && $displayTotalPO > 0) {
                    $grandTotalTagihan = $displayTotalPO;
                }
            @endphp

            <div style="border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 1.25rem; display: flex; justify-content: flex-end;">
                <div style="width: 100%; max-width: 440px; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; color: #64748b;">
                        <span>Subtotal Nilai Bruto:</span>
                        <strong style="color: #0f172a; font-family: monospace;">Rp {{ number_format($calcSubtotalBruto, 0, ',', '.') }}</strong>
                    </div>

                    @if ($calcDiskonItem > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; color: #d97706;">
                            <span>Akumulasi Diskon Item:</span>
                            <strong style="font-family: monospace;">- Rp {{ number_format($calcDiskonItem, 0, ',', '.') }}</strong>
                        </div>
                    @endif

                    @if ($calcPotongan > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; color: #dc2626;">
                            <span>Potongan Harga Langsung:</span>
                            <strong style="font-family: monospace;">- Rp {{ number_format($calcPotongan, 0, ',', '.') }}</strong>
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between; align-items: center; color: #475569; padding-top: 0.35rem; border-top: 1px dashed #cbd5e1;">
                        <span>Dasar Pengenaan Pajak (DPP):</span>
                        <strong style="color: #0f172a; font-family: monospace;">Rp {{ number_format($calcDpp, 0, ',', '.') }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; color: #0284c7;">
                        <span>PPN (11%):</span>
                        <strong style="font-family: monospace;">+ Rp {{ number_format($calcPpn, 0, ',', '.') }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.65rem; border-top: 2px solid #0f172a; font-size: 1.05rem;">
                        <span style="font-weight: 700; color: #0f172a;">Total Nilai PO Resmi:</span>
                        <strong style="color: #0284c7; font-size: 1.2rem; font-family: monospace;">Rp {{ number_format($grandTotalTagihan, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: RIWAYAT KEDATANGAN BARANG (SURAT JALAN / GOOD RECEIPTS) --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">Riwayat Kedatangan Barang Fisik</strong>
                    <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;">({{ $po->penerimaan->count() }} Surat Jalan / Termin)</span>
                </div>
            </div>

            @if ($po->penerimaan->isNotEmpty())
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                <th>No Penerimaan</th>
                                <th>Tanggal Tiba</th>
                                <th>Ketepatan Jadwal</th>
                                <th>Gudang Masuk</th>
                                <th>Status</th>
                                <th style="text-align: right; width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($po->penerimaan as $terima)
                                @php
                                    $tglTerima = \Carbon\Carbon::parse($terima->terima_tgl);
                                    $eta = $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang) : null;
                                @endphp
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td>
                                        <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.85rem;">
                                            {{ $terima->terima_no }}
                                        </a>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #334155;">{{ $tglTerima->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($eta)
                                            @if ($tglTerima->lt($eta))
                                                <span class="badge" style="background:#f0fdf4; color:#166534; font-size:0.75rem;">
                                                    Lebih Cepat
                                                </span>
                                            @elseif ($tglTerima->equalTo($eta))
                                                <span class="badge" style="background:#f0f9ff; color:#0369a1; font-size:0.75rem;">
                                                    Tepat Waktu
                                                </span>
                                            @else
                                                <span class="badge" style="background:#fef2f2; color:#991b1b; font-size:0.75rem;">
                                                    Terlambat
                                                </span>
                                            @endif
                                        @else
                                            <span style="color:#94a3b8; font-size:0.8rem;">-</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.85rem; color: #475569;">{{ $terima->gudang?->gudang_nm }}</td>
                                    <td><span class="badge badge-success">Diterima Fisik</span></td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn btn-secondary btn-sm no-print" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                                            Lihat Surat Jalan
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                    <div style="width: 42px; height: 42px; margin: 0 auto 0.75rem; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    </div>
                    <strong style="color: #64748b; font-size: 0.9rem; display: block;">Belum ada riwayat kedatangan barang</strong>
                    <p style="font-size: 0.8rem; margin-top: 0.25rem; margin-bottom: 0;">
                        Barang belum pernah dicatat masuk. Saat armada supplier tiba di pabrik, gunakan tombol hijau <strong>"Terima Barang"</strong> di bagian atas.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- KOLOM KANAN (SIDEBAR RINGKAS STICKY) --}}
    <div style="display: flex; flex-direction: column; gap: 1.25rem; position: sticky; top: 1rem;">
        
        {{-- PANEL 1: MITRA SUPPLIER --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Mitra Supplier</strong>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <strong style="font-size: 1rem; color: #0f172a; display: block;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">Kode: {{ $po->supplier?->supplier_cd }}</span>
                
                <div style="margin-top: 0.75rem; font-size: 0.825rem; color: #475569; line-height: 1.4;">
                    <div style="margin-bottom: 0.35rem;">
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Alamat:</span>
                        {{ $po->supplier?->alamat_txt ?? '-' }}
                    </div>
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Telepon / Kontak:</span>
                        <strong>{{ $po->supplier?->kontak_no ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL 2: GUDANG TUJUAN PENERIMAAN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Gudang Tujuan Masuk</strong>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <strong style="font-size: 1rem; color: #0f172a; display: block;">{{ $po->gudang?->gudang_nm ?? '-' }}</strong>
                <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">Kode: {{ $po->gudang?->gudang_cd }}</span>
                
                <div style="margin-top: 0.75rem; font-size: 0.825rem; color: #475569;">
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Lokasi Pabrik:</span>
                    {{ $po->gudang?->alamat_txt ?? '-' }}
                </div>
            </div>
        </div>

        {{-- PANEL 3: CATATAN & INSTRUKSI PENGADAAN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Instruksi &amp; Catatan</strong>
            </div>
            <div style="padding: 1rem 1.25rem; font-size: 0.85rem; color: #334155; line-height: 1.4;">
                @if ($po->catatan_txt)
                    <p style="margin: 0; white-space: pre-line;">{{ $po->catatan_txt }}</p>
                @else
                    <span style="color: #94a3b8; font-style: italic;">Tidak ada catatan khusus untuk dokumen ini.</span>
                @endif
            </div>
        </div>

    </div>
</div>
</div> {{-- AKHIR DARI .no-print --}}

{{-- DOKUMEN CETAK RESMI FORMAT ASLI HACCP (HANYA MUNCUL SAAT PRINT BROWSER) --}}
<div class="print-only">
    {{-- KOP TABEL STANDAR HACCP --}}
    <table style="width: 100%; border: 2px solid #000000; border-collapse: collapse; margin-bottom: 25px;">
        <tr>
            <td style="width: 110px; text-align: center; padding: 6px 4px; border: 1px solid #000000; vertical-align: middle;">
                <img src="{{ asset('images/logo.png') }}" style="width: 58px; height: auto;" alt="Logo Cap Payung">
                <div style="font-size: 6.5pt; font-weight: 800; color: #dc2626; margin-top: 2px; letter-spacing: 0.04em;">
                    ENAK - GURIH - LEZAT
                </div>
            </td>
            <td style="text-align: center; padding: 8px 10px; border: 1px solid #000000; vertical-align: middle;">
                <div style="font-size: 11pt; font-weight: 800; color: #000000; letter-spacing: 0.03em;">PT. MIRASA FOOD INDUSTRY</div>
                <div style="font-size: 10pt; font-weight: 600; margin-top: 6px;">Form</div>
                <div style="font-size: 12pt; font-weight: 800; margin-top: 1px;">Permintaan Barang</div>
            </td>
            <td style="width: 250px; padding: 0; border: 1px solid #000000; vertical-align: middle;">
                <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                    <tr>
                        <td style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">Nomor dokumen</td>
                        <td style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">:</td>
                        <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                    </tr>
                    <tr>
                        <td style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">Terbitan/Tgl</td>
                        <td style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">:</td>
                        <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">19-08-2021</td>
                    </tr>
                    <tr>
                        <td style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">Revisi</td>
                        <td style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">:</td>
                        <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">00</td>
                    </tr>
                    <tr>
                        <td style="width: 100px; border-right: 1px solid #000000; font-weight: 600; padding: 4px 6px;">Halaman</td>
                        <td style="width: 8px; text-align: center; padding: 4px 0;">:</td>
                        <td style="font-weight: 700; padding: 4px 6px;">1 dari 1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- NO PO & KEPADA YTH --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 0;">
                <table style="border-collapse: collapse; font-size: 10pt;">
                    <tr>
                        <td style="width: 32px; font-weight: 700; vertical-align: top;">No.</td>
                        <td style="width: 8px; vertical-align: top;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; vertical-align: top;">PO</td>
                        <td style="font-weight: 700; vertical-align: top;">:</td>
                        <td style="font-weight: 800; font-family: monospace; font-size: 10.5pt; padding-left: 4px;">{{ $po->po_no }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding-left: 20px; vertical-align: top;">
                <div style="font-size: 10pt; font-weight: 700; margin-bottom: 2px;">
                    Kepada Yth:
                </div>
                <div style="font-size: 10.5pt; font-weight: 800;">
                    {{ $po->supplier?->supplier_nm ?? '-' }}
                </div>
                @if ($po->supplier?->alamat_txt)
                    <div style="font-size: 9pt; color: #334155; margin-top: 2px;">
                        {{ $po->supplier->alamat_txt }}
                    </div>
                @endif
                @if ($po->supplier?->telepon_no && $po->supplier?->telepon_no !== '-')
                    <div style="font-size: 9pt; color: #334155;">
                        Telp: {{ $po->supplier->telepon_no }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- JUDUL DOKUMEN --}}
    <div style="text-align: center; margin-bottom: 22px;">
        <div style="font-size: 12.5pt; font-weight: 800; text-decoration: underline; letter-spacing: 0.05em;">SURAT PERMINTAAN BARANG</div>
        <div style="font-size: 11pt; font-weight: 800; letter-spacing: 0.05em; margin-top: 3px;">PURCHASE ORDER</div>
    </div>

    {{-- TABEL BARANG --}}
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000; margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="width: 45px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">NO</th>
                <th style="border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: left; padding-left: 10px; background: #ffffff;">NAMA BARANG</th>
                <th style="width: 140px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">JUMLAH</th>
                <th style="width: 180px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($po->details as $idx => $d)
                <tr>
                    <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; text-align: center; font-weight: 700;">{{ $idx + 1 }}.</td>
                    <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; padding-left: 10px;">
                        <strong style="color: #000000;">{{ $d->barang?->barang_nm ?? '-' }}</strong>
                        @if ($d->barang?->barang_cd)
                            <div style="font-size: 8pt; color: #64748b; font-family: monospace;">{{ $d->barang->barang_cd }}</div>
                        @endif
                    </td>
                    <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; text-align: center; font-weight: 800;">
                        {{ number_format((float) $d->pesan_qty, 0, ',', '.') }} {{ $d->barang?->satuanDasar?->satuan_cd ?? 'KG' }}
                    </td>
                    <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9pt; padding-left: 8px;">
                        {{ $d->catatan_txt ?: '-' }}
                    </td>
                </tr>
            @endforeach

            @php $emptyRows = max(0, 3 - count($po->details)); @endphp
            @for ($i = 0; $i < $emptyRows; $i++)
                <tr>
                    <td style="border: 1px solid #000000; padding: 7px 8px; text-align: center; color: transparent;">&nbsp;</td>
                    <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                    <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                    <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- TANDA TANGAN & CATATAN PENGIRIMAN --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding: 0;">
                <div style="font-size: 9.5pt; line-height: 1.45; margin-top: 15px;">
                    <strong>NB.</strong><br>
                    Barang di kirim ke alamat:<br>
                    <strong style="font-size: 10pt;">PT. MIRASA FOOD INDUSTRY</strong><br>
                    <strong>Magelang</strong><br>
                    <span style="font-size: 8.5pt; color: #475569;">Jl. Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah 56512</span>
                </div>
            </td>
            <td style="width: 45%; text-align: center; vertical-align: top; padding: 0;">
                <div style="font-size: 10pt;">
                    Magelang, {{ $po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}<br>
                    <strong>Mengetahui</strong>
                </div>
                <div style="height: 65px;"></div>
                <div style="font-size: 10pt;">
                    .......................................
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- MODALS PARTIALS --}}
@include('gudang.po.partials.modal-quick-receive-show')
@include('gudang.po.partials.modal-force-close')

@endsection

@push('scripts')
<script src="{{ asset('js/gudang/po/po-show.js') }}"></script>
@endpush
