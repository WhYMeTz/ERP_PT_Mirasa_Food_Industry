@extends('layouts.app')

@section('title', 'Catat Penjualan Baru - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/faktur/faktur-form.css') }}">
@endpush

@section('content')
<div class="fk-form-container">

    {{-- HEADER FORM --}}
    <div class="fk-page-header">
        <a href="{{ route('penjualan.faktur.index') }}" class="fk-back-link">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Rekapitulasi Penjualan</span>
        </a>

        <div class="fk-title-row">
            <h1 class="fk-page-title">
                Catat Penjualan Baru
            </h1>
        </div>
        <p class="fk-page-subtitle">
            Pilih dokumen Surat Jalan pengiriman untuk menarik rincian kuantitas fisik yang telah diterima customer, menghitung DPP &amp; PPN, serta mencatat piutang komersial.
        </p>
    </div>

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Mohon periksa kembali formulir pengisian:</span>
            </div>
            <ul style="margin: 0.25rem 0 0 1.25rem; font-size: 0.8125rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="fakturCreateForm" method="POST" action="{{ route('penjualan.faktur.store') }}">
        @csrf

        {{-- 2-COLUMN GRID HEADER CARDS --}}
        <div class="fk-grid-2-col">

            {{-- CARD 1: PENGIRIMAN & FAKTUR --}}
            <div class="fk-card">
                <div class="fk-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>1. Dokumen Surat Jalan &amp; Faktur</span>
                </div>

                {{-- Pilih Surat Jalan --}}
                <div class="fk-field-group">
                    <label class="fk-label" for="pengeluaran_id">
                        Pilih Surat Jalan Pengiriman <span class="required">*</span>
                    </label>
                    <select id="pengeluaran_id" name="pengeluaran_id" class="fk-select" required>
                        <option value="">-- Pilih Surat Jalan yang Belum Difakturkan --</option>
                        @foreach ($uninvoicedSuratJalan as $sj)
                            @php
                                $totalKarton = (float) $sj->details->sum('kirim_qty');
                                $isSel = ($selectedPengeluaranId == $sj->pengeluaran_id) || (old('pengeluaran_id') == $sj->pengeluaran_id);
                            @endphp
                            <option value="{{ $sj->pengeluaran_id }}" {{ $isSel ? 'selected' : '' }}>
                                {{ $sj->pengeluaran_no }} &mdash; {{ $sj->customer?->customer_nm }} ({{ number_format($totalKarton, 0, ',', '.') }} Karton) &bull; {{ \Carbon\Carbon::parse($sj->pengeluaran_tgl)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fk-form-grid-2">
                    {{-- Tanggal Faktur --}}
                    <div class="fk-field-group">
                        <label class="fk-label" for="faktur_tgl">
                            Tanggal Faktur <span class="required">*</span>
                        </label>
                        <input type="date" id="faktur_tgl" name="faktur_tgl" class="fk-input" 
                               value="{{ old('faktur_tgl', now()->toDateString()) }}" required>
                    </div>

                    {{-- Tanggal Jatuh Tempo --}}
                    <div class="fk-field-group">
                        <label class="fk-label" for="tgl_jatuh_tempo">
                            Jatuh Tempo (Due Date)
                        </label>
                        <input type="date" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo" class="fk-input" 
                               value="{{ old('tgl_jatuh_tempo') }}">
                    </div>
                </div>

                {{-- Nomor Faktur Resmi --}}
                <div class="fk-field-group">
                    <label class="fk-label" for="faktur_no">
                        Nomor Faktur Penjualan Resmi
                    </label>
                    <input type="text" id="faktur_no" name="faktur_no" class="fk-input" 
                           placeholder="Otomatis terisi standar format customer (cth: 01/MFI/10/26)" 
                           value="{{ old('faktur_no') }}" style="font-family: monospace; font-weight: 700; color: #059669;">
                    <span style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                        Format otomatis terisi sesuai klasifikasi customer: IFM (01/MFI/MM/YY), Manual (01/PM.A/MM/YY), atau Modern Trade.
                    </span>
                </div>
            </div>

            {{-- CARD 2: CUSTOMER & KOMERSIAL --}}
            <div class="fk-card">
                <div class="fk-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    <span>2. Informasi Customer &amp; Pajak</span>
                </div>

                {{-- Customer Info Snapshot --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 0.85rem; margin-bottom: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span id="badgeKlasifikasi" class="badge-klasifikasi manual">-</span>
                            <div id="infoCustomerNm" style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-top: 0.25rem;">
                                -
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                Kode: <span id="infoCustomerCd" style="font-family: monospace;">-</span> &bull; 
                                Alamat: <span id="infoCustomerAlamat">-</span>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #e2e8f0; font-size: 0.725rem; color: #475569; display: flex; gap: 1rem;">
                        <div>Ref SJ: <strong id="infoSjNo" style="font-family: monospace;">-</strong></div>
                        <div>Ref SO: <strong id="infoSoNo" style="font-family: monospace;">-</strong></div>
                    </div>
                </div>

                <div class="fk-form-grid-2">
                    {{-- Ref PO Customer --}}
                    <div class="fk-field-group">
                        <label class="fk-label" for="customer_po_no">
                            No. PO Eksternal Customer
                        </label>
                        <input type="text" id="customer_po_no" name="customer_po_no" class="fk-input" 
                               value="{{ old('customer_po_no') }}" placeholder="Contoh: PO-IFM-2026-098">
                    </div>

                    {{-- Syarat Bayar / TOP --}}
                    <div class="fk-field-group">
                        <label class="fk-label" for="top_txt">
                            Syarat Bayar (TOP)
                        </label>
                        <input type="text" id="top_txt" name="top_txt" class="fk-input" 
                               value="{{ old('top_txt', '14 Hari') }}" placeholder="Contoh: 14 Hari / 30 Hari">
                    </div>
                </div>

                {{-- Tipe PPN --}}
                <div class="fk-field-group">
                    <label class="fk-label" for="ppn_tipe">
                        Pengenaan Pajak Pertambahan Nilai (PPN) <span class="required">*</span>
                    </label>
                    <select id="ppn_tipe" name="ppn_tipe" class="fk-select" required>
                        <option value="NON_PPN" {{ old('ppn_tipe') === 'NON_PPN' ? 'selected' : '' }}>NON PPN (Bebas PPN / Tarif 0%)</option>
                        <option value="PPN_11" {{ old('ppn_tipe') === 'PPN_11' ? 'selected' : '' }}>PPN 11% (Faktur Pajak Standar)</option>
                    </select>
                </div>
            </div>

        </div>

        {{-- TABEL RINCIAN BARANG FAKTUR --}}
        <div class="fk-table-container">
            <div class="fk-card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>3. Rincian Barang yang Ditagihkan (Commercial Invoice Items)</span>
            </div>

            <div style="overflow-x: auto;">
                <table class="fk-items-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No.</th>
                            <th>Nama Produk Keripik</th>
                            <th style="text-align: right; width: 120px;">Qty Kirim</th>
                            <th style="text-align: right; width: 140px;">Harga Satuan (Rp)</th>
                            <th style="text-align: right; width: 120px;">Diskon (Rp)</th>
                            <th style="text-align: right; width: 130px;">Subtotal Netto</th>
                            <th style="text-align: right; width: 110px;">PPN</th>
                            <th style="text-align: right; width: 140px;">Total Item</th>
                        </tr>
                    </thead>
                    <tbody id="fakturItemsBody">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                Silakan pilih dokumen Surat Jalan terlebih dahulu di atas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- BAGIAN BAWAH: CATATAN & KOTAK PERHITUNGAN --}}
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 1.5rem; gap: 1.5rem; flex-wrap: wrap;">
                
                {{-- KIRI: CATATAN & POTONGAN LAIN --}}
                <div style="flex: 1; min-width: 280px;">
                    <div class="fk-field-group">
                        <label class="fk-label" for="catatan_txt">Catatan / Keterangan Faktur</label>
                        <textarea id="catatan_txt" name="catatan_txt" class="fk-textarea" 
                                  placeholder="Tambahkan catatan khusus rekening bank, informasi nomor faktur pajak, dll...">{{ old('catatan_txt') }}</textarea>
                    </div>

                    <div class="fk-field-group" style="width: 240px; margin-top: 0.75rem;">
                        <label class="fk-label" for="total_potongan">Potongan / Retur Tambahan (Rp)</label>
                        <input type="number" step="any" min="0" id="total_potongan" name="total_potongan" 
                               class="fk-input" value="{{ old('total_potongan', 0) }}" style="text-align: right; font-family: monospace;">
                    </div>
                </div>

                {{-- KANAN: KOTAK PERHITUNGAN TOTAL --}}
                <div class="fk-calc-box">
                    <div class="fk-calc-row">
                        <span style="color: #64748b;">Subtotal Bruto:</span>
                        <span id="summarySubtotalBruto" style="font-family: monospace; font-weight: 700; color: #1e293b;">Rp 0</span>
                    </div>
                    <div class="fk-calc-row">
                        <span style="color: #64748b;">Total Diskon:</span>
                        <span id="summaryTotalDiskon" style="font-family: monospace; font-weight: 700; color: #dc2626;">Rp 0</span>
                    </div>
                    <div class="fk-calc-row" style="border-top: 1px solid #f1f5f9; padding-top: 0.4rem; margin-top: 0.35rem;">
                        <span style="font-weight: 700; color: #334155;">DPP (Dasar Pengenaan Pajak):</span>
                        <span id="summaryDpp" style="font-family: monospace; font-weight: 700; color: #0f172a;">Rp 0</span>
                    </div>
                    <div class="fk-calc-row">
                        <span style="color: #64748b;">PPN:</span>
                        <span id="summaryPpn" style="font-family: monospace; font-weight: 700; color: #2563eb;">Rp 0</span>
                    </div>
                    <div class="fk-calc-row total-row">
                        <span>Total Tagihan:</span>
                        <span id="summaryTotalTagihan" style="font-family: monospace;">Rp 0</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- TOMBOL AKSI SIMPAN --}}
        <div class="fk-bottom-actions">
            <a href="{{ route('penjualan.faktur.index') }}" class="btn btn-secondary" style="font-size: 0.85rem;">
                Batal
            </a>
            <button type="submit" id="btnSubmitFaktur" class="btn btn-primary" 
                    style="display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.85rem; font-weight: 700; background: #059669; border-color: #047857; padding: 0.55rem 1.4rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Terbitkan Faktur &amp; Catat Piutang</span>
            </button>
        </div>

    </form>

</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            prefilledData: @json($prefilledData)
        };
    </script>
    <script src="{{ asset('js/penjualan/faktur/faktur-create.js') }}"></script>
@endpush
