@extends('layouts.app')

@section('title', 'Pemeriksaan Mutu (QC) Inbound & HACCP - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-admin-index.css') }}">
@endpush

@section('content')
{{-- TOP HEADER COMMAND (STANDAR PO) --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Pemeriksaan Mutu (QC) Inbound &amp; HACCP</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Monitoring hasil sampling kedatangan bahan baku, arsip lembar mutu HACCP, serta serah terima penarikan ke Gudang (GRN).
        </p>
    </div>
    
</div>

{{-- 4 KARTU METRIK OPERASIONAL --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- 1. SIAP DITARIK KE GRN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Siap Ditarik ke GRN
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($countSiap ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Lolos sampling &bull; Siap bongkar gudang
        </div>
    </div>

    {{-- 2. MENUNGGU UJI FRYER --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706;">
                    Menunggu Uji Fryer
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($countFryer ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Singkong butuh hasil lab goreng
        </div>
    </div>

    {{-- 3. SUDAH MASUK GUDANG (GRN) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Sudah Masuk Gudang
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($countSelesai ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Telah terbit bukti penerimaan (GRN)
        </div>
    </div>

    {{-- 4. DITOLAK TOTAL (REJECT) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #dc2626;">
                    Ditolak QC / Reject
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($countReject ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Tidak memenuhi parameter mutu
        </div>
    </div>
</div>

{{-- KARTU TABEL UTAMA & FILTER TERPADU (STANDAR PO) --}}
<div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('qc.inbound.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 850px; width: 100%;">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nomor QC, plat truk, supplier, sopir..." class="form-control" style="padding: 0.45rem 0.75rem; max-width: 250px; font-size: 0.85rem;">
            
            <select name="kategori_barang" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 165px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Komoditas --</option>
                <option value="SINGKONG" {{ ($filters['kategori_barang'] ?? '') === 'SINGKONG' ? 'selected' : '' }}>Singkong</option>
                <option value="MINYAK" {{ ($filters['kategori_barang'] ?? '') === 'MINYAK' ? 'selected' : '' }}>Minyak Goreng</option>
                <option value="PLASTIK" {{ ($filters['kategori_barang'] ?? '') === 'PLASTIK' ? 'selected' : '' }}>Plastik</option>
                <option value="KARTON" {{ ($filters['kategori_barang'] ?? '') === 'KARTON' ? 'selected' : '' }}>Karton</option>
                <option value="MSG" {{ ($filters['kategori_barang'] ?? '') === 'MSG' ? 'selected' : '' }}>MSG</option>
                <option value="GARAM" {{ ($filters['kategori_barang'] ?? '') === 'GARAM' ? 'selected' : '' }}>Garam</option>
                <option value="PERENYAH" {{ ($filters['kategori_barang'] ?? '') === 'PERENYAH' ? 'selected' : '' }}>Perenyah</option>
            </select>

            <select name="status_qc" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 165px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Status QC --</option>
                <option value="SIAP_GUDANG" {{ ($filters['status_qc'] ?? '') === 'SIAP_GUDANG' ? 'selected' : '' }}>Siap Gudang</option>
                <option value="DITERIMA_GUDANG" {{ ($filters['status_qc'] ?? '') === 'DITERIMA_GUDANG' ? 'selected' : '' }}>Selesai Masuk (GRN)</option>
                <option value="DITOLAK_TOTAL" {{ ($filters['status_qc'] ?? '') === 'DITOLAK_TOTAL' ? 'selected' : '' }}>Ditolak QC (Reject)</option>
            </select>

            <select name="supplier_id" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 175px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Supplier --</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->supplier_id }}" {{ ($filters['supplier_id'] ?? '') == $s->supplier_id ? 'selected' : '' }}>
                        {{ $s->supplier_nm }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($filters['search']) || !empty($filters['kategori_barang']) || !empty($filters['status_qc']) || !empty($filters['supplier_id']))
                <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif
        </form>

        <span style="color: #64748b; font-size: 0.85rem;">
            Total QC: <strong style="color: #0f172a;">{{ $inspeksiList->total() }}</strong> dokumen
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0; background: #f8fafc;">
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; width: 175px; text-align: left;">No. Tiket QC &amp; Waktu</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; width: 110px; text-align: left;">Komoditas</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; min-width: 170px; text-align: left;">Supplier &amp; PO</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; min-width: 140px; text-align: left;">Armada / Sopir</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; text-align: right;">Gross (kg)</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; text-align: right;">Refraksi</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; text-align: right;">Reject</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #047857; text-align: right;">Netto Lolos (kg)</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; text-align: center;">Status Mutu</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; text-align: center;">Status Gudang</th>
                    <th style="padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; width: 140px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inspeksiList as $qc)
                    @php
                        $totalGross = $qc->details->sum('qty_timbang_gross');
                        $totalRefraksi = $qc->details->sum('qty_refraksi');
                        $totalReject = $qc->details->sum('qty_reject');
                        $totalNetto = $qc->details->sum('qty_netto_lolos');
                        $kat = strtoupper((string) ($qc->kategori_barang ?: 'SINGKONG'));
                        $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin() && !Auth::user()?->isGudang();
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.12s ease;">
                        {{-- 1. NO QC & WAKTU --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle;">
                            <a href="javascript:void(0)" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')" style="font-weight: 700; color: #0284c7; text-decoration: none; font-family: monospace; font-size: 0.875rem;" title="Klik untuk membuka popup Dokumen HACCP">
                                {{ $qc->qc_no }}
                            </a>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }}
                            </div>
                        </td>

                        {{-- 2. KOMODITAS --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle;">
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 700; font-size: 0.75rem;">
                                {{ $kat }}
                            </span>
                        </td>

                        {{-- 3. SUPPLIER & PO --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle;">
                            <div style="font-weight: 700; color: #0f172a;">
                                {{ $qc->supplier?->supplier_nm ?: '-' }}
                            </div>
                            @if ($qc->po)
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                    PO: <a href="{{ route('gudang.po.show', $qc->po->po_id) }}" style="color: #0284c7; text-decoration: none;">{{ $qc->po->po_no }}</a>
                                </div>
                            @endif
                        </td>

                        {{-- 4. ARMADA / SOPIR --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle;">
                            <div style="font-weight: 600; color: #334155;">
                                {{ $qc->plat_nomor_truk ?: '-' }}
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                Sopir: {{ $qc->sopir_nama ?: '-' }}
                            </div>
                            @if ($qc->nomor_do)
                                <div style="font-size: 0.72rem; color: #64748b;">
                                    DO: {{ $qc->nomor_do }}
                                </div>
                            @endif
                        </td>

                        {{-- 5. GROSS --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle; text-align: right; font-weight: 600; color: #334155;">
                            {{ number_format($totalGross, 0, ',', '.') }}
                        </td>

                        {{-- 6. REFRAKSI --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle; text-align: right; color: #d97706; font-weight: 600;">
                            {{ number_format($totalRefraksi, 0, ',', '.') }}
                        </td>

                        {{-- 7. REJECT --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle; text-align: right; color: #dc2626; font-weight: 600;">
                            {{ number_format($totalReject, 0, ',', '.') }}
                        </td>

                        {{-- 8. NETTO LOLOS --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.875rem; vertical-align: middle; text-align: right; font-weight: 700; color: #059669;">
                            {{ number_format($totalNetto, 0, ',', '.') }}
                        </td>

                        {{-- 9. STATUS MUTU & LAB --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle; text-align: center;">
                            @if ($qc->status_qc === 'SIAP_GUDANG')
                                <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700;">Lolos / Siap</span>
                            @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                                <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700;">Diterima</span>
                            @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                                <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700;">Ditolak</span>
                            @else
                                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700;">{{ $qc->status_qc }}</span>
                            @endif

                            @if ($kat === 'SINGKONG')
                                @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
                                    <div>
                                        <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.7rem; font-weight: 700; margin-top: 0.25rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            ⚠️ Belum Uji Fryer
                                        </span>
                                    </div>
                                @elseif ($qc->status_uji_goreng === 'SELESAI')
                                    <div>
                                        <span style="font-size: 0.7rem; color: #166534; font-weight: 700;">✅ Fryer Selesai</span>
                                    </div>
                                @endif
                            @endif
                        </td>

                        {{-- 10. STATUS GUDANG --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: middle; text-align: center;">
                            @if ($qc->terima)
                                <a href="{{ route('gudang.terima.show', $qc->terima->terima_id) }}" class="badge-grn-pill" title="Buka Dokumen Penerimaan Gudang (GRN)">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <span>GRN #{{ $qc->terima->terima_no }}</span>
                                </a>
                            @elseif ($qc->status_qc === 'SIAP_GUDANG')
                                <span style="font-size: 0.75rem; color: #d97706; font-weight: 700;">
                                    Belum Ditarik
                                </span>
                            @else
                                <span style="font-size: 0.75rem; color: #94a3b8;">-</span>
                            @endif
                        </td>

                        {{-- 11. SMART ACTION DROPDOWN (STANDAR PO GUDANG) --}}
                        <td style="text-align: right; position: relative; padding: 0.75rem 0.85rem; vertical-align: middle;">
                            <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'qcDropdown-{{ $qc->qc_id }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            {{-- DROPDOWN MENU ITEMS (STANDAR PO GUDANG) --}}
                            <div id="qcDropdown-{{ $qc->qc_id }}" class="action-dropdown-menu">
                                {{-- 1. LIHAT DOKUMEN HACCP (POPUP MODAL) --}}
                                <button type="button" class="action-dropdown-item" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Dokumen HACCP</span>
                                </button>

                                {{-- 2. CETAK LEMBAR HACCP A4 (DIRECT PRINT LANGSUNG TANPA BUKA HALAMAN) --}}
                                <button type="button" class="action-dropdown-item" onclick="directPrintHaccp('{{ $qc->qc_id }}')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak Lembar HACCP (A4)</span>
                                </button>

                                {{-- 3. TARIK KE GRN GUDANG --}}
                                @if ($qc->status_qc === 'SIAP_GUDANG' && Auth::user()?->canAccessTerima())
                                    <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <span>Tarik ke GRN Gudang</span>
                                    </a>
                                @endif

                                <div class="action-dropdown-divider"></div>

                                {{-- 5. EDIT DOKUMEN QC --}}
                                @if (Auth::user()?->canEditQc())
                                    @if (!$isLocked)
                                        <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit Dokumen QC</span>
                                        </a>
                                    @else
                                        <span class="action-dropdown-item" style="color: #94a3b8; cursor: not-allowed;" title="Terkunci karena sudah terbit GRN Gudang">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Terkunci (GRN Ada)</span>
                                        </span>
                                    @endif
                                @endif

                                {{-- 6. BATALKAN / HAPUS TIKET QC --}}
                                @if (Auth::user()?->canDeleteQc() && !$isLocked)
                                    <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteQcModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batalkan Tiket QC</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="padding: 3.5rem 1.5rem; text-align: center; color: #64748b;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔬</div>
                            <div style="font-weight: 700; font-size: 1rem; color: #0f172a;">Belum Ada Tiket QC Inbound</div>
                            <div style="font-size: 0.85rem; margin-top: 0.25rem;">
                                Belum ada data kedatangan bahan baku yang dicatat atau sesuai kriteria filter di atas.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($inspeksiList->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; background: #ffffff;">
            {{ $inspeksiList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
@include('gudang.qc.partials.modal-delete')

{{-- MODAL POPUP PREVIEW DOKUMEN HACCP --}}
@include('gudang.qc.partials.modal-preview-haccp')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/qc-admin-index.js') }}"></script>
    @if (request('direct_print'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    if (typeof directPrintHaccp === 'function') {
                        directPrintHaccp('{{ request('direct_print') }}');
                    }
                }, 400);
            });
        </script>
    @endif
@endpush
