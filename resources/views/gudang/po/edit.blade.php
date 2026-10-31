@extends('layouts.app')

@section('title', 'Edit Purchase Order ' . $po->po_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-workstation.css') }}?v={{ filemtime(public_path('css/gudang/po/po-workstation.css')) }}">
@endpush

@php
    $hasAnyReceived = $po->details->contains(fn($dtl) => (float) $dtl->terima_qty > 0);
    $firstDetail = $po->details->first();
    $currentDiskonPersen = old('diskon_persen', (float) ($firstDetail?->diskon_persen ?? 0));
    $currentPotonganNominal = old('potongan_nominal', (float) ($po->potongan_nominal ?? 0));
    $currentPajakId = old('pajak_id', $firstDetail?->pajak_id);
@endphp

@section('content')
{{-- 1. CHEVRON BREADCRUMB NAVIGATION (GAYA AKUNTANSI ENTERPRISE) --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.5rem;">
    <nav class="erp-chevron-bar" aria-label="Breadcrumb">
        <a href="{{ route('gudang.po.index') }}" class="erp-chevron-item">
            <span>Pembelian</span>
        </a>
        <span class="erp-chevron-sep">
            <svg width="8" height="20" viewBox="0 0 8 20" fill="none"><path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round"/></svg>
        </span>
        <a href="{{ route('gudang.po.index') }}" class="erp-chevron-item">
            <span>Purchase Order</span>
        </a>
        <span class="erp-chevron-sep">
            <svg width="8" height="20" viewBox="0 0 8 20" fill="none"><path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round"/></svg>
        </span>
        <span class="erp-chevron-item active">
            <span>Edit PO: {{ $po->po_no }}</span>
        </span>
        <span class="erp-chevron-end">
            <svg width="10" height="32" viewBox="0 0 10 32" fill="none"><path d="M1 1L8 16L1 31" stroke="#dce4ec" stroke-width="1.5"/></svg>
        </span>
    </nav>

    {{-- Live KPI Header Widget di Siku Kanan Atas --}}
    <div class="po-live-kpi-widget">
        <div>
            <div class="po-live-kpi-title">ESTIMASI GRAND TOTAL:</div>
            <div class="po-live-kpi-val" id="topLiveGrandTotal">Rp 0</div>
        </div>
        <div style="height: 28px; width: 1.5px; background: #e2e8f0;"></div>
        <div style="font-size: 0.775rem; line-height: 1.35; color: #475569;">
            <div>Baris: <strong id="topLiveItemCount" style="color: #0f172a; font-family: monospace;">{{ $po->details->count() }} Item</strong></div>
            <div style="font-size: 0.7rem; color: #0284c7; font-weight: 700;">Mode Edit</div>
        </div>
    </div>
</div>

{{-- TITLE ROW RAMPING --}}
<div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.55rem; flex-wrap: wrap;">
    <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
        Edit Purchase Order: {{ $po->po_no }}
    </h1>
    @if ($po->status_cd == 'REVISION')
        <span class="badge" style="background:#eff6ff; color:#1d4ed8; border: 1.5px solid #bfdbfe; font-weight: 700; font-size: 0.725rem; padding: 0.25rem 0.6rem; border-radius: 4px;">
            🔄 Status: Perlu Revisi Atasan
        </span>
    @elseif ($po->status_cd == 'REJECTED')
        <span class="badge" style="background:#fee2e2; color:#b91c1c; border: 1.5px solid #fecaca; font-weight: 700; font-size: 0.725rem; padding: 0.25rem 0.6rem; border-radius: 4px;">
            ✕ Status: Ditolak Atasan
        </span>
    @else
        <span class="badge" style="background:#e0f2fe; color:#0369a1; border: 1.5px solid #bae6fd; font-weight: 700; font-size: 0.7rem; padding: 0.2rem 0.55rem; border-radius: 4px;">
            Status: {{ $po->status_cd }}
        </span>
    @endif
</div>

{{-- BANNER ARAHAN REVISI ATASAN --}}
@if ($po->status_cd == 'REVISION')
    <div style="margin-bottom: 1rem; background: #eff6ff; border: 1.5px solid #bfdbfe; border-left: 5px solid #2563eb; border-radius: 8px; padding: 0.95rem 1.15rem; box-shadow: 0 1px 3px rgba(37,99,235,0.06);">
        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #1e40af;">
                        Catatan &amp; Arahan Revisi dari Atasan ({{ $po->rejected_by ?? ($po->approver?->name ?? 'Atasan Peninjau') }})
                    </h4>
                    <span style="font-size: 0.75rem; color: #3b82f6; font-weight: 600;">
                        {{ $po->rejected_at ? \Carbon\Carbon::parse($po->rejected_at)->translatedFormat('d F Y H:i') : '' }}
                    </span>
                </div>
                <div style="margin-top: 0.45rem; background: #ffffff; border: 1.5px solid #bfdbfe; border-radius: 6px; padding: 0.65rem 0.85rem; font-size: 0.875rem; color: #1e3a8a; font-weight: 700; line-height: 1.45;">
                    &ldquo;{{ $po->reject_reason ?? 'Mohon periksa dan sesuaikan kembali kuantitas serta harga penawaran.' }}&rdquo;
                </div>
                <div style="margin-top: 0.4rem; font-size: 0.785rem; color: #2563eb;">
                    &bull; Silakan sesuaikan jumlah kuantitas, harga, atau rincian item di bawah ini. Setelah Anda klik tombol <strong>Simpan &amp; Ajukan Ulang</strong>, dokumen ini akan otomatis dikirimkan kembali ke antrean persetujuan atasan.
                </div>
            </div>
        </div>
    </div>
@elseif ($po->status_cd == 'REJECTED')
    <div style="margin-bottom: 1rem; background: #fef2f2; border: 1.5px solid #fecaca; border-left: 5px solid #ef4444; border-radius: 8px; padding: 0.95rem 1.15rem;">
        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #991b1b;">
                    Dokumen Ditolak oleh Atasan ({{ $po->rejected_by ?? 'Atasan' }})
                </h4>
                <div style="margin-top: 0.45rem; background: #ffffff; border: 1px solid #fecaca; border-radius: 6px; padding: 0.65rem 0.85rem; font-size: 0.875rem; color: #991b1b; font-weight: 700;">
                    &ldquo;{{ $po->reject_reason ?? 'Tidak ada catatan tambahan.' }}&rdquo;
                </div>
            </div>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 0.75rem; border-radius: 6px; padding: 0.6rem 1rem; font-size: 0.8125rem;">
        <strong>Perhatian! Terdapat kesalahan pada input Anda:</strong>
        <ul style="margin: 0.25rem 0 0 1.15rem; padding: 0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($hasAnyReceived)
    <div style="margin-bottom: 0.75rem; background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e; border-radius: 6px; padding: 0.5rem 0.85rem; display: flex; gap: 0.5rem; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div style="font-size: 0.775rem; line-height: 1.35;">
            <strong>PO telah memiliki penerimaan gudang.</strong> Item yang sudah diterima terkunci nama komoditasnya dan kuantitasnya tidak boleh lebih kecil dari jumlah yang sudah diterima fisik.
        </div>
    </div>
@endif

<form action="{{ route('gudang.po.update', $po->po_id) }}" method="POST" id="formEditPo">
    @csrf
    @method('PUT')

    {{-- 2. TOOLBAR CARD RAMPING: SEMUA FIELD LANGSUNG TAMPIL 2 BARIS (NO ACCORDION, ZERO EMPTY SPACE) --}}
    <div class="po-toolbar-card">
        {{-- Header Bar Toolbar: Label Info & Tax Setting di Kanan Atas --}}
        <div class="po-mode-toolbar-wrap">
            <div style="font-size: 0.775rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Informasi Header Dokumen Pengadaan</span>
            </div>

            {{-- Skema Pajak PPN (Dipindah ke Atas agar Grid 4 Kolom di Bawah Simetris & Tidak Sempit) --}}
            <div style="display: flex; align-items: center; gap: 0.45rem; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.25rem 0.65rem;">
                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; margin-bottom: 0; user-select: none;">
                    <input type="hidden" name="is_harga_termasuk_pajak" value="0">
                    <input type="checkbox" id="is_harga_termasuk_pajak" name="is_harga_termasuk_pajak" value="1" {{ old('is_harga_termasuk_pajak', $po->is_harga_termasuk_pajak) ? 'checked' : '' }} onchange="onHeaderTaxInclusiveToggle(this)" style="width: 15px; height: 15px; accent-color: #059669; cursor: pointer;">
                    <span style="color: #0f172a; font-size: 0.775rem; font-weight: 700;">Harga Termasuk PPN</span>
                    <span class="badge" id="badgeTaxMode" style="{{ old('is_harga_termasuk_pajak', $po->is_harga_termasuk_pajak) ? 'background:#dcfce7; color:#15803d;' : 'background:#e2e8f0; color:#475569;' }} font-size: 0.65rem; font-weight: 700; margin-left: 0.2rem;">
                        {{ old('is_harga_termasuk_pajak', $po->is_harga_termasuk_pajak) ? 'Gross' : 'Net' }}
                    </span>
                </label>
            </div>
        </div>

        {{-- GRID UNIFIED 4 KOLOM X 2 BARIS (SIMETRIS, PRESISI, TIDAK MEPET & TIDAK BERONGGA) --}}
        <div class="po-toolbar-grid">
            {{-- [R1-C1] Tanggal PO --}}
            <div>
                <label for="po_tgl" class="po-field-label">
                    Tanggal PO <span style="color:#ef4444;">*</span>
                </label>
                <input type="date" id="po_tgl" name="po_tgl" class="po-field-input" value="{{ old('po_tgl', $po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->format('Y-m-d') : date('Y-m-d')) }}" required>
            </div>

            {{-- [R1-C2] Nomor PO Resmi --}}
            <div>
                <label for="po_no" class="po-field-label">
                    Nomor PO Resmi <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" id="po_no" name="po_no" class="po-field-input" value="{{ old('po_no', $po->po_no) }}" style="font-family: monospace; font-weight: 700;" required>
                <small id="po_no_help" style="display: none;">Nomor urut resmi pengadaan.</small>
            </div>

            {{-- [R1-C3] Supplier Mitra --}}
            <div>
                <label for="supplier_id" class="po-field-label">
                    Supplier Mitra <span style="color:#ef4444;">*</span>
                </label>
                @if($hasAnyReceived)
                    <input type="hidden" name="supplier_id" value="{{ $po->supplier_id }}">
                    <select class="po-field-select" disabled style="background: #f1f5f9; color: #475569; cursor: not-allowed;">
                        <option>{{ $po->supplier?->supplier_nm ?? 'Supplier' }} ({{ $po->supplier?->supplier_cd }}) - Terkunci</option>
                    </select>
                @else
                    <select id="supplier_id" name="supplier_id" class="po-field-select" required>
                        <option value="">-- Pilih Supplier Mitra --</option>
                        @foreach ($supplierList as $sup)
                            <option value="{{ $sup->supplier_id }}" {{ old('supplier_id', $po->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- [R1-C4] Gudang Tujuan Masuk --}}
            <div>
                <label for="gudang_id" class="po-field-label">
                    Gudang Tujuan Masuk <span style="color:#ef4444;">*</span>
                </label>
                @if($hasAnyReceived)
                    <input type="hidden" name="gudang_id" value="{{ $po->gudang_id }}">
                    <select class="po-field-select" disabled style="background: #f1f5f9; cursor: not-allowed;">
                        <option>{{ $po->gudang?->gudang_nm ?? 'Gudang' }} (Terkunci)</option>
                    </select>
                @else
                    <select id="gudang_id" name="gudang_id" class="po-field-select" required>
                        <option value="">-- Pilih Gudang Masuk --</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $po->gudang_id) == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- [R2-C1] Penyetuju PO --}}
            <div>
                <label for="approver_id" class="po-field-label">
                    👔 Penyetuju (Approver)
                </label>
                <select id="approver_id" name="approver_id" class="po-field-select">
                    <option value="">-- Belum Ditentukan --</option>
                    @foreach ($approverList as $appr)
                        <option value="{{ $appr->id }}" {{ old('approver_id', $po->approver_id) == $appr->id ? 'selected' : '' }}>
                            {{ $appr->name }} [{{ $appr->role_cd }}]
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- [R2-C2] Term of Payment --}}
            <div>
                <label for="term_of_payment" class="po-field-label">
                    Term of Payment (TOP)
                </label>
                <input type="text" id="term_of_payment" name="term_of_payment" class="po-field-input" value="{{ old('term_of_payment', $po->term_of_payment ?? ($po->supplier?->top_hari ? 'Tempo ' . $po->supplier->top_hari . ' Hari (Transfer)' : 'Transfer')) }}" placeholder="Contoh: Transfer / Tempo 30 Hari">
            </div>

            {{-- [R2-C3] Catatan Khusus (Remark) --}}
            <div>
                <label for="remark_txt" class="po-field-label">
                    Catatan Khusus (Remark)
                </label>
                <input type="text" id="remark_txt" name="remark_txt" class="po-field-input" value="{{ old('remark_txt', $po->remark_txt) }}" placeholder="Contoh: Kadar air standar">
                <input type="hidden" id="franco_txt" name="franco_txt" value="{{ old('franco_txt', $po->franco_txt ?? 'Pabrik PT Mirasa Food Industry') }}">
            </div>

            {{-- [R2-C4] Estimasi Kedatangan --}}
            <div>
                <label for="tgl_estimasi_datang" class="po-field-label">
                    Estimasi Kedatangan
                </label>
                <input type="date" id="tgl_estimasi_datang" name="tgl_estimasi_datang" value="{{ old('tgl_estimasi_datang', $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('Y-m-d') : '') }}" class="po-field-input">
            </div>
        </div>
    </div>

    {{-- 3. KARTU TABEL WORKSTATION FULL-WIDTH (100% LEBAR TANPA TERJEPIT) --}}
    <div class="po-workstation-table-card">
        <div style="background: #ffffff; padding: 0.55rem 0.85rem; border-bottom: 1.5px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <strong style="color: #0f172a; font-size: 0.875rem;">Rincian Barang &amp; Bahan Baku Pesanan</strong>
                <span style="font-size: 0.725rem; color: #64748b;" id="badgeItemCount">({{ $po->details->count() }} item)</span>
            </div>
            <div style="font-size: 0.725rem; color: #64748b;">
                💡 Tekan <kbd style="background:#e2e8f0; padding:1px 4px; border-radius:3px; font-weight:700;">Enter</kbd> untuk baris baru
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="excel-grid-table" id="tablePoItems" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">No</th>
                        <th style="min-width: 280px; text-align: left;">Bahan Baku / Komoditas <span style="color:#ef4444;">*</span></th>
                        <th style="width: 70px; text-align: center;">Satuan</th>
                        <th style="width: 120px; text-align: right;">Kuantitas <span style="color:#ef4444;">*</span></th>
                        <th style="width: 140px; text-align: right;">Harga Satuan (Rp) <span style="color:#ef4444;">*</span></th>
                        <th style="width: 150px; text-align: right;">Total (Rp)</th>
                        <th style="min-width: 140px; text-align: left;">Catatan Item</th>
                        <th style="width: 45px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyPoItems">
                    @foreach ($po->details as $idx => $dtl)
                        @php
                            $isItemReceived = (float) $dtl->terima_qty > 0;
                        @endphp
                        <tr class="po-item-row" data-row-index="{{ $idx }}">
                            <td style="text-align: center; font-weight: 600; color: #64748b;" class="row-num">
                                {{ $idx + 1 }}
                            </td>
                            <td>
                                <input type="hidden" name="items[{{ $idx }}][podtl_id]" value="{{ $dtl->podtl_id }}">
                                @if($isItemReceived)
                                    <input type="hidden" name="items[{{ $idx }}][barang_id]" value="{{ $dtl->barang_id }}">
                                    <div style="font-weight: 700; color: #1e293b; font-size: 0.8rem;">
                                        {{ $dtl->barang?->barang_nm }} <span style="color:#64748b; font-size:0.725rem;">({{ $dtl->barang?->barang_cd }})</span>
                                    </div>
                                    <div style="margin-top: 2px;">
                                        <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 0.675rem; font-weight: 700; padding: 1px 5px;">
                                            Diterima: {{ (float) $dtl->terima_qty }} {{ $dtl->barang?->satuanDasar?->satuan_cd ?? 'KG' }}
                                        </span>
                                    </div>
                                @else
                                    <select name="items[{{ $idx }}][barang_id]" class="form-control item-barang-select" required onchange="handleBarangChange(this)">
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach ($barangList as $b)
                                            <option value="{{ $b->barang_id }}" 
                                                    data-satuan="{{ $b->satuanDasar?->satuan_cd ?? 'KG' }}"
                                                    data-harga="{{ (float) $b->harga_beli_standar }}"
                                                    {{ $dtl->barang_id == $b->barang_id ? 'selected' : '' }}>
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #475569; font-size: 0.775rem;" class="satuan-badge">
                                {{ $dtl->barang?->satuanDasar?->satuan_cd ?? 'KG' }}
                            </td>
                            <td>
                                <input type="number" step="any" min="{{ $isItemReceived ? (float) $dtl->terima_qty : 0.01 }}" name="items[{{ $idx }}][pesan_qty]" class="form-control input-qty" value="{{ (float) $dtl->pesan_qty }}" required style="text-align: right; font-weight: 700;" oninput="calculateRow(this)">
                                @if($isItemReceived)
                                    <div style="font-size: 0.65rem; color: #b45309; font-weight: 600; text-align: right; margin-top: 1px;">Min: {{ (float) $dtl->terima_qty }}</div>
                                @endif
                            </td>
                            <td>
                                <input type="number" step="any" min="0" name="items[{{ $idx }}][harga_nominal]" class="form-control input-harga" value="{{ (float) $dtl->harga_nominal }}" required style="text-align: right; font-weight: 600;" oninput="calculateRow(this)">
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace; font-size: 0.85rem;" class="row-subtotal">
                                Rp {{ number_format((float) ($dtl->pesan_qty * $dtl->harga_nominal), 0, ',', '.') }}
                            </td>
                            <td>
                                <input type="text" name="items[{{ $idx }}][catatan_txt]" class="form-control" value="{{ $dtl->catatan_txt }}" placeholder="Catatan spesifikasi...">
                            </td>
                            <td style="text-align: center;">
                                @if($isItemReceived)
                                    <button type="button" class="btn btn-sm btn-secondary" disabled style="padding: 0.2rem 0.4rem; font-size: 0.75rem; border-radius: 4px; opacity: 0.6; cursor: not-allowed;" title="Item sudah diterima di gudang, tidak dapat dihapus.">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </button>
                                @else
                                    <button type="button" onclick="removeRow(this)" class="btn btn-sm btn-danger" style="padding: 0.15rem 0.4rem; font-size: 0.75rem; border-radius: 4px;" title="Hapus Baris">
                                        &times;
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                        <td colspan="3" style="padding: 0.55rem 0.75rem; text-align: right; color: #475569; font-size: 0.8rem; text-transform: uppercase;">
                            Akumulasi:
                        </td>
                        <td id="totalQtyDisplay" style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #047857; padding-right: 0.65rem;">
                            {{ number_format((float)$po->total_qty, 2, '.', '') }}
                        </td>
                        <td></td>
                        <td id="totalSubtotalDisplay" style="text-align: right; padding: 0.55rem 0.65rem; font-size: 0.95rem; color: #0f172a; font-family: monospace;">
                            Rp {{ number_format((float)$po->subtotal_bruto, 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Tombol Tambah Baris Lebar Ramping --}}
        <div style="padding: 0.45rem 0.85rem; background: #ffffff;">
            <button type="button" class="btn-add-row-wide" onclick="addNewItemRow()">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>+ Tambah Baris Barang Baru</span>
            </button>
        </div>
    </div>

    {{-- 4. BOTTOM SECTION: CATATAN (KIRI) & FINANCIAL STACK (KANAN) --}}
    <div class="po-bottom-grid">
        {{-- Sisi Kiri: Catatan Dokumen --}}
        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
            <div class="po-notes-card">
                <label for="catatan_txt" class="po-field-label" style="display: flex; align-items: center; justify-content: space-between;">
                    <span>Catatan Pengiriman (NOTE)</span>
                    <small style="font-weight: normal; color: #64748b;">Tampil di kotak NOTE dokumen</small>
                </label>
                <textarea id="catatan_txt" name="catatan_txt" rows="3" class="form-control" placeholder="Contoh: *Mohon dikirim tanggal 29 Oktober 2026 jam 08.00 pagi..." style="border: 1.5px solid #cbd5e1; border-radius: 5px; font-size: 0.8125rem; padding: 0.5rem;">{{ old('catatan_txt', $po->catatan_txt) }}</textarea>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.85rem; font-size: 0.725rem; color: #64748b; line-height: 1.4;">
                <strong style="color: #334155;">💡 Tips Pengubahan PO:</strong> Tekan <kbd style="background:#e2e8f0; padding:1px 3px; border-radius:3px; font-weight:700;">Enter</kbd> pada kuantitas/harga untuk langsung membuat baris baru. Item yang sudah diterima terkunci nama komoditasnya.
            </div>
        </div>

        {{-- Sisi Kanan: Financial Stack Ringkasan Kalkulasi & Tombol Aksi --}}
        <div class="po-financial-card">
            <div style="font-size: 0.775rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.65rem;">
                Ringkasan Finansial Dokumen
            </div>

            {{-- 1. Total Kuantitas --}}
            <div class="po-financial-row">
                <span style="color: #64748b; font-weight: 600;">Total Kuantitas:</span>
                <strong style="color: #0284c7; font-family: monospace; font-size: 0.95rem;">
                    <span id="summaryTotalQty">{{ number_format((float)$po->total_qty, 2, '.', '') }}</span> Unit
                </strong>
            </div>

            {{-- 2. Subtotal Bruto --}}
            <div class="po-financial-row">
                <span style="color: #64748b; font-weight: 600;">Subtotal Bruto:</span>
                <span id="summarySubtotalBruto" style="font-weight: 700; font-family: monospace; font-size: 0.95rem; color: #1e293b;">
                    Rp {{ number_format((float)$po->subtotal_bruto, 0, ',', '.') }}
                </span>
            </div>

            {{-- 3. Diskon Global (-) % dan Nominal --}}
            <div class="po-financial-row" style="align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span style="color: #64748b; font-weight: 600;">Diskon Global (-):</span>
                    <div style="display: inline-flex; align-items: center; gap: 2px;">
                        <input type="number" step="0.1" min="0" max="100" id="global_diskon_persen" name="diskon_persen" value="{{ $currentDiskonPersen }}" oninput="calculateAllTotals()" class="form-control" style="width: 58px; height: 28px; padding: 2px 6px; font-size: 0.8rem; text-align: right; font-weight: 700;" placeholder="0">
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">%</span>
                    </div>
                </div>
                <span id="summaryDiskon" style="font-weight: 700; font-family: monospace; color: #d97706;">
                    Rp {{ number_format((float)$po->diskon_total, 0, ',', '.') }}
                </span>
            </div>

            {{-- 4. Potongan Harga (-) Nominal --}}
            <div class="po-financial-row" style="align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span style="color: #64748b; font-weight: 600;">Potongan (-):</span>
                    <input type="number" step="any" min="0" id="global_potongan_nominal" name="potongan_nominal" value="{{ $currentPotonganNominal }}" oninput="calculateAllTotals()" class="form-control" style="width: 105px; height: 28px; padding: 2px 8px; font-size: 0.8rem; text-align: right; font-weight: 700;" placeholder="Rp 0">
                </div>
                <span id="summaryPotongan" style="font-weight: 700; font-family: monospace; color: #dc2626;">
                    Rp {{ number_format((float)$po->potongan_nominal, 0, ',', '.') }}
                </span>
            </div>

            {{-- 5. DPP (Netto) --}}
            <div class="po-financial-row" style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.45rem; margin-top: 0.2rem;">
                <span style="color: #475569; font-weight: 700;">DPP (Netto):</span>
                <span id="summaryDpp" style="font-weight: 700; font-family: monospace; color: #1e293b; font-size: 0.95rem;">
                    Rp {{ number_format((float)$po->dpp_nominal, 0, ',', '.') }}
                </span>
            </div>

            {{-- 6. Pajak (PPN) Selector & Nilai --}}
            <div class="po-financial-row" style="align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span style="color: #64748b; font-weight: 600;">Pajak (PPN):</span>
                    <select id="global_pajak_id" name="pajak_id" class="form-control" style="width: 130px; height: 28px; padding: 2px 6px; font-size: 0.775rem; font-weight: 700;" onchange="calculateAllTotals()">
                        <option value="" data-tarif="0" data-sifat="TAMBAH">Non-PPN (0%)</option>
                        @foreach ($pajakList as $pjk)
                            <option value="{{ $pjk->pajak_id }}" data-tarif="{{ (float)$pjk->tarif_persen }}" data-sifat="{{ $pjk->sifat_pajak }}" {{ ($currentPajakId == $pjk->pajak_id || (empty($currentPajakId) && $po->ppn_tipe == 'PPN_11' && (float)$pjk->tarif_persen == 11)) ? 'selected' : '' }}>
                                {{ $pjk->pajak_cd }} ({{ (float)$pjk->tarif_persen }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
                <span id="summaryPpn" style="font-weight: 700; font-family: monospace; color: #0284c7;">
                    Rp {{ number_format((float)$po->ppn_nominal, 0, ',', '.') }}
                </span>
            </div>

            {{-- 7. Grand Total Emphasized Box --}}
            <div class="po-grand-total-box">
                <div>
                    <span style="font-size: 0.725rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #065f46; display: block;">
                        Total Kesepakatan Tagihan:
                    </span>
                    <span style="font-size: 0.7rem; color: #059669;">Termasuk Pajak &amp; Diskon</span>
                </div>
                <div class="po-grand-total-val" id="summaryGrandTotal">
                    Rp {{ number_format((float)$po->total_tagihan, 0, ',', '.') }}
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="po-action-bar">
                <a href="{{ route('gudang.po.show', $po->po_id) }}" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8125rem; font-weight: 700; border-radius: 5px; background: #f8fafc; border: 1.5px solid #cbd5e1; color: #475569; text-decoration: none;">
                    Batal
                </a>
                <button type="submit" id="btnSubmitEditPo" class="btn btn-primary" style="padding: 0.55rem 1.35rem; font-size: 0.875rem; font-weight: 800; background: {{ $po->status_cd == 'REVISION' ? '#2563eb' : '#059669' }}; border: none; border-radius: 5px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15); display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $po->status_cd == 'REVISION' ? 'Simpan & Ajukan Ulang ke Atasan' : 'Simpan Perubahan PO' }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

{{-- TEMPLATE BARIS BARANG BARU (HIDDEN) --}}
<template id="templateRow">
    <tr class="po-item-row" data-row-index="__INDEX__">
        <td style="text-align: center; font-weight: 600; color: #64748b;" class="row-num">
            __NUM__
        </td>
        <td>
            <input type="hidden" name="items[__INDEX__][podtl_id]" value="">
            <select name="items[__INDEX__][barang_id]" class="form-control item-barang-select" required onchange="handleBarangChange(this)">
                <option value="">-- Pilih Barang --</option>
                @foreach ($barangList as $b)
                    <option value="{{ $b->barang_id }}" 
                            data-satuan="{{ $b->satuanDasar?->satuan_cd ?? 'KG' }}"
                            data-harga="{{ (float) $b->harga_beli_standar }}">
                        {{ $b->barang_nm }} ({{ $b->barang_cd }})
                    </option>
                @endforeach
            </select>
        </td>
        <td style="text-align: center; font-weight: 600; color: #475569; font-size: 0.775rem;" class="satuan-badge">
            -
        </td>
        <td>
            <input type="number" step="any" min="0.01" name="items[__INDEX__][pesan_qty]" class="form-control input-qty" value="1" required style="text-align: right; font-weight: 700;" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[__INDEX__][harga_nominal]" class="form-control input-harga" value="0" required style="text-align: right; font-weight: 600;" oninput="calculateRow(this)">
        </td>
        <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace; font-size: 0.85rem;" class="row-subtotal">
            Rp 0
        </td>
        <td>
            <input type="text" name="items[__INDEX__][catatan_txt]" class="form-control" placeholder="Catatan spesifikasi...">
        </td>
        <td style="text-align: center;">
            <button type="button" onclick="removeRow(this)" class="btn btn-sm btn-danger" style="padding: 0.15rem 0.4rem; font-size: 0.75rem; border-radius: 4px;" title="Hapus Baris">
                &times;
            </button>
        </td>
    </tr>
</template>

{{-- MODAL PERINGATAN KONFIRMASI BULAN SEBELUMNYA --}}
@include('gudang.po.partials.modal-confirm-previous-month')

@push('scripts')
<script>
    window.poId = {{ $po->po_id }};
    window.originalPoTgl = '{{ $po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->format('Y-m-d') : date('Y-m-d') }}';
    window.originalPoNo = @json($po->po_no);
    window.currentServerYearMonth = '{{ date('Y-m') }}';
    window.editRowIndex = {{ $po->details->count() }};
</script>
<script src="{{ asset('js/gudang/po/po-edit.js') }}?v={{ filemtime(public_path('js/gudang/po/po-edit.js')) }}"></script>
@endpush
@endsection