@extends('layouts.app')

@section('title', 'Detail Pengeluaran Barang ' . $pengeluaran->pengeluaran_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/pengeluaran/pengeluaran-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penjualan/pengeluaran/pengeluaran-index.css') }}">
@endpush

@section('content')
<div class="sj-form-container">

    {{-- PAGE HEADER & TOMBOL AKSI UTAMA --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ route('penjualan.pengeluaran.index') }}" class="sj-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Pengeluaran Barang
            </a>
            <div class="sj-title-row">
                <h1 class="sj-page-title" style="font-family: monospace;">{{ $pengeluaran->pengeluaran_no }}</h1>
                @if ($pengeluaran->status_cd === 'SAMPAI_TUJUAN')
                    <span class="badge-status sampai">
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                        Sampai Tujuan
                    </span>
                @elseif ($pengeluaran->status_cd === 'BATAL')
                    <span class="badge-status batal">
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                        Dibatalkan (Void)
                    </span>
                @else
                    <span class="badge-status terkirim">
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                        Terkirim (Dalam Perjalanan)
                    </span>
                @endif
            </div>
            <p class="sj-page-subtitle">
                Diterbitkan pada {{ \Carbon\Carbon::parse($pengeluaran->pengeluaran_tgl)->translatedFormat('d F Y') }} oleh {{ $pengeluaran->created_by ?? 'System' }}.
            </p>
        </div>

        <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
            {{-- Tombol Cetak PDF --}}
            <a href="{{ route('penjualan.pengeluaran.export-pdf', $pengeluaran->pengeluaran_id) }}" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 700;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar Surat Jalan (PDF)</span>
            </a>

            @if ($pengeluaran->order)
                <a href="{{ route('penjualan.so.show', $pengeluaran->order->so_id) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Lihat Dokumen PO ({{ $pengeluaran->order->so_no }})</span>
                </a>
            @endif

            {{-- Tahap 3: Terbitkan / Lihat Penjualan --}}
            @if ($pengeluaran->faktur)
                <a href="{{ route('penjualan.faktur.show', $pengeluaran->faktur->faktur_id) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 700; background: #059669; border-color: #047857;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    <span>Lihat Penjualan ({{ $pengeluaran->faktur->faktur_no }})</span>
                </a>
            @elseif ($pengeluaran->status_cd !== 'BATAL')
                <a href="{{ route('penjualan.faktur.create', ['pengeluaran_id' => $pengeluaran->pengeluaran_id]) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 700; background: #059669; border-color: #047857;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Catat Penjualan</span>
                </a>
            @endif

            @if ($pengeluaran->status_cd !== 'BATAL')
                <button type="button" class="btn btn-danger" onclick="openVoidModal('{{ $pengeluaran->pengeluaran_id }}', '{{ $pengeluaran->pengeluaran_no }}')"
                        style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 700; background: #dc2626; border-color: #b91c1c; color: #ffffff;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Batalkan (Void SJ)</span>
                </button>
            @endif
        </div>
    </div>

    @if ($pengeluaran->status_cd === 'BATAL')
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #991b1b;">
            <div style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Dokumen Surat Jalan Ini Telah Dibatalkan (Void)
            </div>
            <div style="font-size: 0.825rem; margin-top: 0.35rem; color: #7f1d1d;">
                Seluruh kuantitas fisik barang telah dikembalikan ke Gudang FG. Catatan: {{ $pengeluaran->catatan_txt ?? '-' }}
            </div>
        </div>
    @endif

    {{-- 2 KOLOM KARTU INFORMASI --}}
    <div class="sj-grid-2-col">
        
        {{-- KARTU 1: DATA TUJUAN & CUSTOMER --}}
        <div class="sj-card">
            <div class="sj-card-header">
                <div class="sj-card-header-left">
                    <span class="sj-card-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <div>
                        <h3 class="sj-card-title">Tujuan Pengiriman &amp; Mitra Pembeli</h3>
                        <div class="sj-card-subtitle">Identitas pelanggan dan referensi order</div>
                    </div>
                </div>
            </div>

            <div class="sj-card-body">
                @php
                    $customer = $pengeluaran->customer;
                    $order = $pengeluaran->order;
                    $klasifikasi = $customer?->klasifikasi ?? 'MANUAL';
                @endphp
                <table style="width: 100%; font-size: 0.825rem; border-collapse: collapse;">
                    <tr>
                        <td style="width: 140px; padding: 0.4rem 0; color: #64748b; font-weight: 600;">Nama Mitra Customer:</td>
                        <td style="padding: 0.4rem 0; font-weight: 700; color: #0f172a;">
                            {{ $customer?->customer_nm ?? '-' }}
                            <span class="badge-klasifikasi {{ $klasifikasi === 'IFM' ? 'ifm' : ($klasifikasi === 'IDM_ALFA' ? 'modern' : 'manual') }}" style="margin-left: 0.4rem;">
                                {{ $klasifikasi }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">Alamat Pengiriman:</td>
                        <td style="padding: 0.4rem 0; color: #334155;">
                            {{ $customer?->alamat_txt ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">Wilayah / Kota:</td>
                        <td style="padding: 0.4rem 0; font-weight: 600; color: #0f172a;">
                            {{ $customer?->wilayah ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">Referensi PO Penjualan:</td>
                        <td style="padding: 0.4rem 0; font-weight: 700; color: #0284c7;">
                            {{ $order?->so_no ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">No. PO Eksternal:</td>
                        <td style="padding: 0.4rem 0; font-family: monospace; font-weight: 600;">
                            {{ $order?->customer_po_no ?: '(Tidak ada)' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">Gudang Asal:</td>
                        <td style="padding: 0.4rem 0; font-weight: 600; color: #059669;">
                            {{ $pengeluaran->gudang?->gudang_nm ?? 'Gudang Barang Jadi (GDG-FG)' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- KARTU 2: DATA LOGISTIK & EKSPEDISI --}}
        <div class="sj-card">
            <div class="sj-card-header">
                <div class="sj-card-header-left">
                    <span class="sj-card-icon emerald">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </span>
                    <div>
                        <h3 class="sj-card-title">Data Logistik &amp; Ekspedisi Armada</h3>
                        <div class="sj-card-subtitle">Supir, armada truk, dan tanggal keberangkatan</div>
                    </div>
                </div>
            </div>

            <div class="sj-card-body">
                <table style="width: 100%; font-size: 0.825rem; border-collapse: collapse;">
                    <tr>
                        <td style="width: 140px; padding: 0.4rem 0; color: #64748b; font-weight: 600;">Tanggal Berangkat:</td>
                        <td style="padding: 0.4rem 0; font-weight: 700; color: #0f172a;">
                            {{ \Carbon\Carbon::parse($pengeluaran->pengeluaran_tgl)->translatedFormat('l, d F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">Nama Supir / Driver:</td>
                        <td style="padding: 0.4rem 0; font-weight: 700; color: #0f172a;">
                            {{ $pengeluaran->supir_nm ?: 'Belum Dicatat' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">No. Polisi Armada:</td>
                        <td style="padding: 0.4rem 0; font-family: monospace; font-weight: 700; color: #0284c7;">
                            {{ $pengeluaran->nopol_kendaraan ?: '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">Ekspedisi / Armada:</td>
                        <td style="padding: 0.4rem 0; color: #334155;">
                            {{ $pengeluaran->ekspedisi_nm ?: 'Armada Internal PT Mirasa' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">Catatan / Instruksi:</td>
                        <td style="padding: 0.4rem 0; color: #334155;">
                            {{ $pengeluaran->catatan_txt ?: '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.4rem 0; color: #64748b; font-weight: 600;">Total Karton Keluar:</td>
                        <td style="padding: 0.4rem 0; font-size: 1rem; font-weight: 800; color: #059669;">
                            {{ number_format($pengeluaran->total_qty, 0, ',', '.') }} Karton
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    {{-- KARTU 3: TABEL DETAIL BARANG KELUAR & ALOKASI BATCH FEFO --}}
    <div class="sj-card">
        <div class="sj-card-header">
            <div class="sj-card-header-left">
                <span class="sj-card-icon amber">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <div>
                    <h3 class="sj-card-title">Rincian Fisik Barang &amp; Traceability Batch (FEFO)</h3>
                    <div class="sj-card-subtitle">Nomor lot produksi hasil pabrik yang termuat di armada ini</div>
                </div>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="sj-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No.</th>
                        <th>Kode &amp; Nama Produk</th>
                        <th style="width: 200px;">Nomor Batch Produksi (FEFO)</th>
                        <th style="text-align: right; width: 140px;">Kuantitas Keluar</th>
                        <th style="width: 100px;">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengeluaran->details as $index => $dtl)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                                {{ $index + 1 }}
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">{{ $dtl->barang?->barang_nm ?? '-' }}</div>
                                <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">Kode: {{ $dtl->barang?->barang_cd ?? '-' }}</div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-weight: 700; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; color: #334155; font-size: 0.8125rem;">
                                    {{ $dtl->batch_no ?: 'NON-BATCH' }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: #0f172a; font-feature-settings: 'tnum';">
                                {{ number_format($dtl->kirim_qty, 0, ',', '.') }}
                            </td>
                            <td style="color: #64748b; font-weight: 600;">
                                {{ $dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">
                                Tidak ada rincian barang tercatat pada dokumen ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Summary --}}
        <div class="sj-table-footer-bar">
            <div class="sj-footer-left">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Dokumen ini resmi mengurangi saldo fisik di kartu stok Gudang Barang Jadi (GDG-FG).</span>
            </div>

            <div class="sj-footer-right">
                <div class="sj-total-display">
                    <div class="sj-total-label">Total Muatan:</div>
                    <div class="sj-total-val">{{ number_format($pengeluaran->total_qty, 0, ',', '.') }} Karton</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL VOID / PEMBATALAN SURAT JALAN --}}
@include('penjualan.pengeluaran.partials.modal-void')

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/pengeluaran/pengeluaran-index.js') }}"></script>
@endpush
