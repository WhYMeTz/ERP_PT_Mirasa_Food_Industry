@extends('layouts.app')

@section('title', 'Edit Dokumen QC ' . $qc->qc_no . ' - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-edit-desktop.css') }}">
@endpush

@php
    $kat = strtoupper((string)($qc->kategori_barang ?: 'SINGKONG'));
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;
    $barangId = $firstDetail?->barang_id ?? ($barangs->firstWhere('barang_nm', 'like', '%Singkong%')?->barang_id ?? $barangs->first()?->barang_id);
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $totalReject = $qc->details->sum('qty_reject');

    $revisi = '1';
    $tglTerbit = '11-09-2023';

    if ($kat === 'SINGKONG') {
        $docNo = 'MFI/HACCP-04/FRM-03/048/VIII/2021';
        $docTitle = 'Standar Kebeterimaan Bahan Baku Singkong';
    } elseif ($kat === 'MINYAK') {
        $docNo = 'MFI/HACCP-04/FRM-03/029/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Minyak Goreng';
    } elseif ($kat === 'PLASTIK') {
        $docNo = 'MFI/HACCP-04/FRM-03/030/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Plastik';
    } elseif ($kat === 'KARTON') {
        $docNo = 'MFI/HACCP-04/FRM-03/031/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Karton';
    } elseif ($kat === 'MSG') {
        $docNo = 'MFI/HACCP-04/FRM-03/032/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan MSG';
    } elseif ($kat === 'GARAM') {
        $docNo = 'MFI/HACCP-04/FRM-03/033/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Garam';
    } elseif ($kat === 'PERENYAH') {
        $docNo = 'MFI/HACCP-04/FRM-03/063/IX/2023';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Perenyah';
        $revisi = '0';
        $tglTerbit = '14-09-2023';
    } else {
        $docNo = 'MFI/HACCP-04/FRM-03/048/VIII/2021';
        $docTitle = 'Cheklist Standar Kebeterimaan Bahan Baku';
    }
@endphp

@section('content')
{{-- TOP HEADER COMMAND (STANDAR PO GUDANG) --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('qc.inbound.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat QC
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
                Edit Dokumen QC: {{ $qc->qc_no }}
            </h1>
            <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight: 700;">Status: {{ $qc->status_qc }}</span>
            <span class="badge" style="background:#f1f5f9; color:#475569; font-weight: 700;">Komoditas: {{ $kat }}</span>
        </div>
        <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
            Koreksi dan sesuaikan data dokumen pemeriksaan mutu, parameter sampling laboratorium, tonase timbangan fisik, serta lembar HACCP.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button type="button" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span>Lihat Dokumen HACCP</span>
        </button>
    </div>
</div>

{{-- ALERT INFORMASI PENERIMAAN GUDANG (GRN) --}}
@if ($qc->terima)
    <div style="margin-bottom: 1.25rem; background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e; border-radius: 8px; padding: 0.85rem 1.15rem; display: flex; gap: 0.75rem; align-items: flex-start; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div style="font-size: 0.85rem; line-height: 1.45;">
            <strong style="font-size: 0.9rem;">Informasi: Dokumen QC ini telah memiliki Penerimaan Barang di gudang (GRN #{{ $qc->terima->terima_no }}).</strong><br>
            Setiap revisi tonase timbangan fisik, kadar kotoran, atau kuantitas reject pada formulir ini akan <strong>otomatis menyelaraskan data Penerimaan Gudang &amp; Saldo Batch Stok terkait</strong> tanpa perlu proses void manual.
        </div>
    </div>
@endif

{{-- ALERT ERROR VALIDASI --}}
@if (isset($errors) && $errors->any())
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        <strong>Perhatian! Terdapat kesalahan pada input Anda:</strong>
        <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- FORMULIR UTAMA WEB DESKTOP --}}
<form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcEditDesktopForm">
    @csrf
    @method('PUT')

    <input type="hidden" name="kategori_barang" value="{{ $qc->kategori_barang }}">
    <input type="hidden" name="items[{{ $qcdtlId }}][qcdtl_id]" value="{{ $qcdtlId }}">
    <input type="hidden" name="items[{{ $qcdtlId }}][podtl_id]" value="{{ $firstDetail?->podtl_id }}">

    <div class="order-station-grid">
        {{-- KOLOM UTAMA (KIRI) --}}
        <div>
            {{-- KARTU 1: INFORMASI PENGIRIMAN & REFERENSI --}}
            <div class="card">
                <div class="card-header">
                    <strong>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        1. Data Pengiriman, Supplier &amp; Referensi PO
                    </strong>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    <div class="qc-grid-4">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Nomor Dokumen QC</label>
                            <input type="text" class="form-control" value="{{ $qc->qc_no }}" readonly style="background: #f1f5f9; font-weight: 700; color: #475569; font-family: monospace;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Waktu Periksa Kedatangan <span style="color:#ef4444;">*</span></label>
                            <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Mitra Supplier <span style="color:#ef4444;">*</span></label>
                            <select name="supplier_id" class="form-control" required style="font-weight: 600;">
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->supplier_id }}" {{ old('supplier_id', $qc->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                        {{ $sup->supplier_nm }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Gudang Penerima <span style="color:#ef4444;">*</span></label>
                            <select name="gudang_id" class="form-control" required style="font-weight: 600;">
                                @foreach ($gudangs as $g)
                                    <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $qc->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                        {{ $g->gudang_nm }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="qc-grid-4">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Nomor Surat Jalan / DO</label>
                            <input type="text" name="nomor_do" value="{{ old('nomor_do', $qc->nomor_do) }}" class="form-control" placeholder="Contoh: SJ-2026/09/012">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Plat Nomor Truk</label>
                            <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" class="form-control" placeholder="Contoh: AD 8129 OE" style="text-transform: uppercase;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Nama Pengemudi / Sopir</label>
                            <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" class="form-control" placeholder="Nama sopir armada">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Referensi Dokumen PO</label>
                            @if ($qc->po)
                                <input type="text" class="form-control" value="{{ $qc->po->po_no }}" readonly style="background: #f1f5f9; color: #475569;">
                            @else
                                <input type="text" class="form-control" value="Non-PO / Pembelian Langsung" readonly style="background: #f1f5f9; color: #64748b; font-style: italic;">
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: PARAMETER MUTU & SAMPLING LAB (HACCP) --}}
            <div class="card">
                <div class="card-header">
                    <strong>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        2. Parameter Mutu Sampling Laboratorium (Formulir HACCP)
                    </strong>
                    <span style="font-size: 0.75rem; color: #64748b;">Standar Spesifikasi Bahan: {{ $kat }}</span>
                </div>
                <div style="padding: 1.25rem;">
                    {{-- SELEKSI ITEM BARANG MASTER --}}
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Master Bahan Baku Terkait <span style="color:#ef4444;">*</span></label>
                        <select name="items[{{ $qcdtlId }}][barang_id]" class="form-control" required style="font-weight: 600;">
                            @foreach ($barangs as $b)
                                <option value="{{ $b->barang_id }}" {{ old("items.{$qcdtlId}.barang_id", $barangId) == $b->barang_id ? 'selected' : '' }}>
                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - {{ $b->satuanDasar?->satuan_cd ?? 'KG' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- INCLUDE SPESIFIK PARAMETER SESUAI KOMODITAS --}}
                    @if ($kat === 'SINGKONG')
                        @include('gudang.qc.partials.doc-singkong')
                    @elseif ($kat === 'MINYAK')
                        @include('gudang.qc.partials.doc-minyak')
                    @elseif ($kat === 'PLASTIK')
                        @include('gudang.qc.partials.doc-plastik')
                    @elseif ($kat === 'KARTON')
                        @include('gudang.qc.partials.doc-karton')
                    @else
                        @include('gudang.qc.partials.doc-seasoning')
                    @endif
                </div>
            </div>

            {{-- KARTU 3: TONASE TIMBANGAN FISIK & POTONGAN --}}
            <div class="card">
                <div class="card-header">
                    <strong>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        3. Tonase Timbangan Fisik &amp; Potongan
                    </strong>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    <div class="qc-grid-3">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Tonase Gross Timbangan (kg) <span style="color:#ef4444;">*</span></label>
                            <input type="number" step="0.01" min="0" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="inputGross" value="{{ old("items.{$qcdtlId}.qty_timbang_gross", $firstDetail?->qty_timbang_gross ?? $totalGross) }}" class="form-control" required style="font-weight: 700; font-size: 1rem;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Refraksi Kotoran / Sortir (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="items[{{ $qcdtlId }}][refraksi_persen]" id="inputRefraksiPersen" value="{{ old("items.{$qcdtlId}.refraksi_persen", $firstDetail?->refraksi_persen ?? 0) }}" class="form-control" style="font-weight: 700; font-size: 1rem; color: #d97706;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Kuantitas Reject Ditolak (kg)</label>
                            <input type="number" step="0.01" min="0" name="items[{{ $qcdtlId }}][qty_reject]" id="inputReject" value="{{ old("items.{$qcdtlId}.qty_reject", $firstDetail?->qty_reject ?? $totalReject) }}" class="form-control" style="font-weight: 700; font-size: 1rem; color: #dc2626;">
                        </div>
                    </div>

                    <div class="qc-grid-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Alasan Reject / Penolakan Bahan</label>
                            <input type="text" name="items[{{ $qcdtlId }}][catatan_reject]" value="{{ old("items.{$qcdtlId}.catatan_reject", $firstDetail?->catatan_reject) }}" class="form-control" placeholder="Contoh: Singkong busuk basah, minyak bau tengik, dll...">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Catatan Mutu Baris Item</label>
                            <input type="text" name="items[{{ $qcdtlId }}][catatan_mutu]" value="{{ old("items.{$qcdtlId}.catatan_mutu", $firstDetail?->catatan_mutu) }}" class="form-control" placeholder="Kondisi fisik saat sampling...">
                        </div>
                    </div>

                    <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputNettoLolos" value="{{ $totalNetto }}">
                </div>
            </div>

            {{-- KARTU 4: PEMERIKSAAN HIGIENITAS & HALAL --}}
            <div class="card">
                <div class="card-header">
                    <strong>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        4. Pemeriksaan Higienitas Pengiriman &amp; Status Halal
                    </strong>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    <div class="qc-check-grid">
                        <label class="qc-check-item">
                            <input type="checkbox" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st) ? 'checked' : '' }}>
                            <span style="font-weight: 600;">Truk Bersih (Bebas Najis &amp; Bau)</span>
                        </label>
                        <label class="qc-check-item">
                            <input type="checkbox" name="angkut_barang_haram_st" value="1" {{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st) ? 'checked' : '' }}>
                            <span style="font-weight: 600; color: #dc2626;">Armada Bawa Barang Haram</span>
                        </label>
                        <label class="qc-check-item">
                            <input type="checkbox" name="terdaftar_lppom_st" value="1" {{ old('terdaftar_lppom_st', $qc->terdaftar_lppom_st ?? 1) ? 'checked' : '' }}>
                            <span style="font-weight: 600;">Terdaftar LPPOM MUI / BPJPH</span>
                        </label>
                        <label class="qc-check-item">
                            <input type="checkbox" name="ada_sertifikat_halal_st" value="1" {{ old('ada_sertifikat_halal_st', $qc->ada_sertifikat_halal_st ?? 1) ? 'checked' : '' }}>
                            <span style="font-weight: 600;">Sertifikat Halal Valid</span>
                        </label>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Komentar / Catatan Khusus Kondisi Pengiriman</label>
                        <input type="text" name="komentar_transportasi" value="{{ old('komentar_transportasi', $qc->komentar_transportasi) }}" class="form-control" placeholder="Kondisi terpal penutup, kebersihan bak truk, atau catatan higienitas...">
                    </div>
                </div>
            </div>

            {{-- KARTU 5: KESIMPULAN KEPUTUSAN & PETUGAS --}}
            <div class="card">
                <div class="card-header">
                    <strong>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        5. Kesimpulan Keputusan QC &amp; Verifikator
                    </strong>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    <div class="qc-grid-3">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Keputusan Kelayakan QC <span style="color:#ef4444;">*</span></label>
                            <select name="status_qc" id="selectStatusQc" class="form-control" style="font-weight: 700;" required>
                                <option value="SIAP_GUDANG" {{ old('status_qc', $qc->status_qc) === 'SIAP_GUDANG' ? 'selected' : '' }}>
                                    SIAP GUDANG (Lolos Uji &amp; Siap GRN)
                                </option>
                                <option value="DITERIMA_GUDANG" {{ old('status_qc', $qc->status_qc) === 'DITERIMA_GUDANG' ? 'selected' : '' }}>
                                    DITERIMA GUDANG (Telah Terbit GRN)
                                </option>
                                <option value="DITOLAK_TOTAL" {{ old('status_qc', $qc->status_qc) === 'DITOLAK_TOTAL' ? 'selected' : '' }}>
                                    DITOLAK TOTAL (Reject / Tidak Layak)
                                </option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Petugas Sampling QC</label>
                            <input type="text" name="petugas_qc_nama" value="{{ old('petugas_qc_nama', $qc->petugas_qc_nama) }}" class="form-control" style="font-weight: 600;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Supervisor QC / Ka. Lab</label>
                            <input type="text" name="qc_supervisor_nama" value="{{ old('qc_supervisor_nama', $qc->qc_supervisor_nama) }}" class="form-control" style="font-weight: 600;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Catatan Umum / Instruksi Khusus Gudang</label>
                        <textarea name="catatan_umum" rows="2" class="form-control" placeholder="Tuliskan catatan tambahan mengenai kondisi barang, penanganan khusus saat bongkar muat, dll...">{{ old('catatan_umum', $qc->catatan_umum) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: RINGKASAN TONASE & TOMBOL SUBMIT (PERSIS PO EDIT) --}}
        <div class="sticky-action-sidebar" style="position: sticky; top: 1.5rem;">
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
                <div class="card-header" style="background: #0f172a; color: #ffffff; padding: 0.85rem 1.25rem;">
                    <strong style="font-size: 0.95rem; display: flex; align-items: center; gap: 0.45rem; color: #ffffff;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Ringkasan Tonase &amp; Status QC
                    </strong>
                </div>

                <div style="padding: 1.25rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                        <span style="color: #64748b;">Timbangan Gross:</span>
                        <strong style="color: #0f172a;" id="sidebarGross">{{ number_format($totalGross, 0, ',', '.') }} kg</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                        <span style="color: #64748b;">Potongan Refraksi:</span>
                        <strong style="color: #d97706;" id="sidebarRefraksi">{{ number_format($firstDetail?->qty_refraksi ?? 0, 0, ',', '.') }} kg</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                        <span style="color: #64748b;">Barang Reject:</span>
                        <strong style="color: #dc2626;" id="sidebarReject">{{ number_format($totalReject, 0, ',', '.') }} kg</strong>
                    </div>

                    {{-- HIGHLIGHT NETTO BOX (PERSIS TOTAL KESEPAKATAN PO) --}}
                    <div style="background: #f0fdf4; border: 1.5px solid #059669; border-radius: 8px; padding: 0.85rem; margin-top: 0.5rem; text-align: center;">
                        <span style="font-size: 0.75rem; color: #047857; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; display: block;">
                            Total Netto Lolos Gudang
                        </span>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #059669; margin-top: 0.15rem; font-family: monospace;" id="sidebarNetto">
                            {{ number_format($totalNetto, 0, ',', '.') }} kg
                        </div>
                    </div>

                    @if ($kat === 'SINGKONG')
                        <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; padding-top: 0.65rem; border-top: 1px dashed #e2e8f0;">
                            <span style="color: #64748b;">Rendemen Est:</span>
                            <strong style="color: #0284c7;">{{ number_format($firstDetail?->rendemen_persen ?? 0, 1) }}%</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 0.45rem;">
                            <span style="color: #64748b;">Status Uji Fryer:</span>
                            <span class="badge" style="background:#fef3c7; color:#b45309; font-weight: 700; font-size: 0.75rem;">
                                {{ $qc->status_uji_goreng ?: 'DRAFT' }}
                            </span>
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.65rem; padding-top: 0.65rem; border-top: 1px dashed #e2e8f0;">
                        <span style="color: #64748b;">Status QC:</span>
                        <span class="badge" id="sidebarStatusBadge" style="background:#e0f2fe; color:#0369a1; font-weight: 700;">
                            {{ $qc->status_qc }}
                        </span>
                    </div>

                    {{-- TOMBOL AKSI SUBMIT (PERSIS PO EDIT) --}}
                    <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.65rem;">
                        <button type="submit" name="and_print" value="0" class="btn btn-primary" style="width: 100%; padding: 0.65rem 1rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan Perubahan QC
                        </button>

                        <button type="submit" name="and_print" value="1" class="btn btn-primary" style="width: 100%; background: #0f172a; padding: 0.65rem 1rem; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Simpan &amp; Cetak Lembar HACCP A4
                        </button>

                        <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary" style="width: 100%; text-align: center; padding: 0.55rem; font-size: 0.85rem;">
                            Batal &amp; Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- MODAL POPUP PREVIEW DOKUMEN HACCP --}}
@include('gudang.qc.partials.modal-preview-haccp')
@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/qc-edit-desktop.js') }}"></script>
@endpush
