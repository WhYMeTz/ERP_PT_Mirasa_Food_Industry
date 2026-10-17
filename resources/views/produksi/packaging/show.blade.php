@extends('layouts.app')

@section('title', 'Bukti Hasil Pengemasan - ' . $header->pack_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-show.css') }}?v={{ filemtime(public_path('css/produksi/packaging/packaging-show.css')) }}">
@endpush

@section('content')
<div class="show-container packaging-show-container">
    @php
        $expDate = $header->expired_tgl
            ? Carbon\Carbon::parse($header->expired_tgl)->format('d/m/Y')
            : Carbon\Carbon::parse($header->pack_tgl)->addMonths(6)->format('d/m/Y');

        $wipItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) === 'WIP');
        $kemasanItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) !== 'WIP');
    @endphp

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- KOP SURAT RESMI CETAK (PRINT-ONLY)                                 --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="print-only" style="margin-bottom: 1.5rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
                <p style="font-size: 0.75rem; color: #475569; margin: 0.2rem 0 0 0;">
                    Pabrik Pengolahan F&amp;B Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
                </p>
            </div>
            <div style="text-align: right;">
                <strong style="font-size: 1.05rem; color: #059669; display: block;">LEMBAR BUKTI HASIL PENGEMASAN &amp; HARGA POKOK (HPP)</strong>
                <span style="font-family: monospace; font-size: 0.9rem; font-weight: 700;">{{ $header->pack_no }}</span>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS HEADER NAVIGASI & AKSI (NO-PRINT)                            --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="show-header no-print">
        <div>
            <a href="{{ route('produksi.packaging.index') }}" class="btn-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Riwayat Pengemasan</span>
            </a>
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $header->pack_no }}</h1>
                @if ($header->status_cd === 'POSTED')
                    <span class="badge-posted">
                        ✓ Posted (Stok FG Tersimpan)
                    </span>
                @else
                    <span class="badge-void">
                        ✕ VOID (Dibatalkan)
                    </span>
                @endif
            </div>
        </div>

        <div class="show-actions">
            @if(Auth::user()->canCreatePackaging())
                <a href="{{ route('produksi.packaging.create') }}" class="btn-adjust-hpp" style="color: #059669; border-color: #10b981;" title="Buat Lembar Pengemasan Baru">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Kemas Baru</span>
                </a>
            @endif
            <button type="button" onclick="window.print()" class="btn-print-doc">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar HPP Kemas</span>
            </button>
            @if($header->status_cd === 'POSTED' && Auth::user()->canCreatePackaging())
                <button type="button" id="btnVoidPackaging" data-pack-id="{{ $header->pack_id }}" data-pack-no="{{ $header->pack_no }}" data-target-nm="{{ $header->barangJadi?->barang_nm ?? '' }}" onclick="openVoidPackagingModal({{ $header->pack_id }}, '{{ $header->pack_no }}', '{{ addslashes($header->barangJadi?->barang_nm ?? '') }}')" class="btn btn-danger" style="font-size: 0.85rem; padding: 0.5rem 0.95rem; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Batalkan (Void)</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ALERT NOTIFIKASI SUKSES / ERROR --}}
    @if(session('success'))
        <div class="no-print" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="no-print" style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 1: 4 KARTU METRIK EKSEKUTIF UTAMA (EXECUTIVE STAT GRID)      --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="executive-stat-grid">
        {{-- METRIK 1: HPP RIIL PER KARTON --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #059669;">HPP Riil per Karton</span>
                    <div class="executive-stat-value" style="color: #059669;">
                        Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #ecfdf5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Modal pokok persediaan FG di gudang
            </div>
        </div>

        {{-- METRIK 2: HPP PER BUNGKUS / PCS --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #0284c7;">HPP per Bungkus (Pcs)</span>
                    <div class="executive-stat-value" style="color: #0284c7;">
                        Rp {{ number_format($header->hpp_per_pcs, 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Harga modal satuan retail eceran
            </div>
        </div>

        {{-- METRIK 3: TOTAL HASIL KARTON & PCS --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Hasil Kemas Siap Jual</span>
                    <div class="executive-stat-value">
                        {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #f1f5f9; color: #475569;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="font-weight: 700; color: #059669;">{{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs</span> ({{ number_format($header->isi_per_karton_pcs, 0) }} Pcs/Ktn)
                @if($header->gramatur_gr > 0)
                    &bull; {{ number_format($header->gramatur_gr, 1, ',', '.') }} gr/pcs
                @endif
            </div>
        </div>

        {{-- METRIK 4: TOTAL BIAYA PENGEMASAN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Total Biaya Pengemasan</span>
                    <div class="executive-stat-value">
                        Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #fef3c7; color: #d97706;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Akumulasi Curah WIP + Kemasan + Upah
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 2: 2 PANEL SEJAJAR (PARAMETER OPERASIONAL & STIKER KARTON)   --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="meta-cards-grid-2">
        {{-- PANEL 1: PARAMETER OPERASIONAL --}}
        <div class="card-enterprise" style="margin-bottom: 0;">
            <div class="card-enterprise-header">
                <div class="card-enterprise-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Parameter Operasional</span>
                </div>
                <span class="cat-badge cat-kemasan">
                    FG Retail Snack
                </span>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <table class="spec-table">
                    <tr>
                        <td class="label-col">Tanggal Pengemasan</td>
                        <td class="value-col">{{ Carbon\Carbon::parse($header->pack_tgl)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Target Produk Jadi (FG)</td>
                        <td class="value-col" style="font-weight: 700; color: #0284c7;">
                            {{ $header->barangJadi?->barang_nm ?? '-' }}
                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">({{ $header->barangJadi?->barang_cd ?? '-' }})</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Formula Acuan (BOM)</td>
                        <td class="value-col">
                            {{ $header->bom?->bom_nm ?? 'Tanpa BOM / Manual' }}
                            @if($header->bom)
                                <span style="font-size: 0.725rem; color: #059669; font-weight: 700;">[{{ $header->bom->bom_no }}]</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Gudang Penyimpanan FG</td>
                        <td class="value-col">{{ $header->gudang?->gudang_nm ?? 'Gudang Hasil Produksi (FG)' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">No. Batch Persediaan FG</td>
                        <td class="value-col" style="font-family: monospace; font-weight: 700; color: #0369a1;">
                            {{ $header->batch_no ?: '-' }}
                        </td>
                    </tr>
                    @if($header->produksi)
                    <tr>
                        <td class="label-col">Tiket Goreng Berko</td>
                        <td class="value-col">
                            <a href="{{ route('produksi.show', $header->produksi_id) }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; color: #059669; font-weight: 700;">
                                <span>{{ $header->produksi->produksi_no }}</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <span style="font-size: 0.725rem; color: #64748b;">({{ Carbon\Carbon::parse($header->produksi->produksi_tgl)->format('d/m/Y') }} &bull; HPP Rp {{ number_format($header->produksi->hpp_per_kg, 0, ',', '.') }}/kg)</span>
                        </td>
                    </tr>
                    @endif
                    @if($header->pemakaian)
                    <tr>
                        <td class="label-col">Bukti Pengeluaran Gudang</td>
                        <td class="value-col">
                            <a href="{{ route('gudang.pemakaian.show', $header->pakai_id) }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; color: #0284c7; font-weight: 700;">
                                <span>{{ $header->pemakaian->pakai_no }}</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <span style="font-size: 0.725rem; color: #059669; font-weight: 600;">(Auto Issuance Gudang)</span>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label-col">Segel Lakban Dus Karton</td>
                        <td class="value-col">
                            @if($header->lakban_atas_gr > 0 || $header->lakban_bawah_gr > 0 || $header->lakban_jumlah_gr > 0)
                                <span style="font-weight: 700; color: #059669;">
                                    Atas: {{ number_format($header->lakban_atas_gr, 0) }}g &bull; Bawah: {{ number_format($header->lakban_bawah_gr, 0) }}g &bull; Total: {{ number_format($header->lakban_jumlah_gr, 0) }}g
                                </span>
                                @if($header->lakban_nilai > 0)
                                    <span style="color: #0284c7; font-weight: 700; font-size: 0.775rem;">(Rp {{ number_format($header->lakban_nilai, 0, ',', '.') }})</span>
                                @endif
                            @else
                                <span style="color: #64748b;">Standar Operasional Meja Kemas</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Masa Kedaluwarsa (Exp)</td>
                        <td class="value-col" style="color: #059669; font-weight: 700;">
                            {{ $expDate }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Operator Pencatat</td>
                        <td class="value-col" style="font-weight: 700;">{{ $header->created_by ?? 'Operator Packing' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Catatan Operasional</td>
                        <td class="value-col">{{ $header->catatan_txt ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- PANEL 2: PRATINJAU STIKER KARTON DUS --}}
        <div class="card-enterprise" style="margin-bottom: 0;">
            <div class="card-enterprise-header">
                <div class="card-enterprise-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Identitas Label Dus Karton</span>
                </div>
                <span class="cat-badge cat-wip">
                    Standar BPOM &amp; Halal RI
                </span>
            </div>
            <div style="padding: 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="font-size: 0.725rem; font-weight: 700; color: #64748b; margin-bottom: 0.65rem; text-transform: uppercase; letter-spacing: 0.04em;">
                    CONTOH LABEL IDENTITAS KEMASAN DUS KARTON
                </div>

                <div class="sticker-preview-box">
                    {{-- Header: Nama Produk & Halal Emblem --}}
                    <div class="sticker-header">
                        <div class="sticker-title">
                            {{ strtoupper($header->barangJadi?->barang_nm ?? 'PING-PING 500') }}
                        </div>
                        <div class="sticker-halal-box">
                            <svg viewBox="0 0 100 100" width="34" height="34" style="display: block; margin: 0 auto;">
                                <circle cx="50" cy="50" r="46" fill="none" stroke="#000000" stroke-width="4"/>
                                <circle cx="50" cy="50" r="39" fill="none" stroke="#000000" stroke-width="1.5"/>
                                <text x="50" y="30" font-size="8.5" font-weight="900" text-anchor="middle" font-family="Arial, sans-serif">MAJELIS ULAMA</text>
                                <text x="50" y="58" font-size="18" font-weight="900" text-anchor="middle" font-family="'Times New Roman', serif">حلال</text>
                                <text x="50" y="73" font-size="8" font-weight="900" text-anchor="middle" font-family="Arial, sans-serif">INDONESIA</text>
                            </svg>
                            <div class="halal-cert-id">ID3321000001931219</div>
                            <div class="halal-cert-date">6 Februari 2024</div>
                        </div>
                    </div>

                    {{-- Body: 2 Kolom Alami Tanpa Gap --}}
                    <div class="sticker-cols-container">
                        {{-- Kolom Kiri --}}
                        <div class="st-left-col">
                            <div class="st-row">
                                <span class="st-lbl">No Batch :</span>
                                <span class="st-val st-batch-num">{{ $header->batch_no ?: 'LOT-FG-DEFAULT' }}</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Isi Karton:</span>
                                <span class="st-val">{{ number_format($header->isi_per_karton_pcs, 0) }} Bungkus</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Jenis :</span>
                                <span class="st-val">RETAIL SNACK</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Status :</span>
                                <span class="st-val">SIAP JUAL</span>
                            </div>
                        </div>

                        {{-- Kolom Kanan --}}
                        <div class="st-right-col">
                            <div class="st-row">
                                <span class="st-lbl">Tgl. Kemas :</span>
                                <span class="st-val">{{ strtoupper(Carbon\Carbon::parse($header->pack_tgl)->format('d M Y')) }}</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Tgl. Exp :</span>
                                <span class="st-val">{{ strtoupper(Carbon\Carbon::parse($expDate)->format('d M Y')) }}</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Kode FG :</span>
                                <span class="st-val">{{ $header->barangJadi?->barang_cd ?? 'FG-XX001' }}</span>
                            </div>
                            <div class="st-row" style="justify-content: flex-end;">
                                <div class="plant-code-box">MIRASA / FG</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 3: TABEL OUTPUT BARANG JADI (FG OUTPUT)                      --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="card-enterprise">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Rincian Hasil Barang Jadi (Finished Goods Output)</span>
            </div>
            <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                1 Varian Selesai Dikemas
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-enterprise">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 140px;">Kategori Varian</th>
                        <th>Kode &amp; Nama Barang Jadi (FG)</th>
                        <th style="width: 150px; text-align: center;">No. Batch FG</th>
                        <th style="width: 140px; text-align: right;">Hasil (Karton)</th>
                        <th style="width: 110px; text-align: right;">Isi / Ktn</th>
                        <th style="width: 130px; text-align: right;">Total (Pcs)</th>
                        <th style="width: 140px; text-align: right;">HPP Satuan</th>
                        <th style="width: 160px; text-align: right;">Total Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center; color: #94a3b8; font-weight: 700;">1</td>
                        <td>
                            <span class="cat-badge cat-kemasan">Finished Good (FG)</span>
                        </td>
                        <td>
                            <strong style="color: #0284c7; font-family: monospace;">{{ $header->barangJadi?->barang_cd ?? '-' }}</strong> &ndash;
                            <span style="font-weight: 600;">{{ $header->barangJadi?->barang_nm ?? '-' }}</span>
                        </td>
                        <td style="font-family: monospace; font-weight: 700; color: #0369a1; text-align: center;">
                            {{ $header->batch_no }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #059669; font-variant-numeric: tabular-nums;">
                            {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} Karton
                        </td>
                        <td style="text-align: right; color: #64748b; font-variant-numeric: tabular-nums;">
                            {{ number_format($header->isi_per_karton_pcs, 0) }} Pcs
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                            {{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs
                        </td>
                        <td style="text-align: right; color: #475569; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 700; border-top: 2px solid #e2e8f0;">
                    <tr>
                        <td colspan="4" style="padding: 0.75rem 1rem; text-align: right; font-size: 0.8rem; color: #475569;">
                            TOTAL FG TERKEMAS:
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #059669; font-size: 0.95rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} Karton
                        </td>
                        <td colspan="2" style="padding: 0.75rem 1rem; text-align: right; color: #64748b; font-size: 0.8rem;">
                            ({{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs)
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #64748b; font-size: 0.8rem;">
                            HPP/KARTON:
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #059669; font-size: 0.95rem; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 4: RINCIAN BIAYA & PEMAKAIAN BAHAN PENGEMASAN                --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="card-enterprise">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rincian Komponen Biaya Pengemasan (Curah WIP, Kemasan, Upah &amp; Utilitas)</span>
            </div>
            <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                Akuntansi Biaya Manufaktur
            </span>
        </div>

        {{-- 4 PILL RINGKASAN BIAYA --}}
        <div class="cost-summary-bar">
            <div class="cost-summary-pill">
                <span class="cost-pill-label">Bahan Curah (WIP)</span>
                <span class="cost-pill-val" style="color: #c2410c;">Rp {{ number_format($header->total_biaya_wip, 0, ',', '.') }}</span>
            </div>
            <div class="cost-summary-pill">
                <span class="cost-pill-label">Bahan Kemasan &amp; Dus</span>
                <span class="cost-pill-val" style="color: #0284c7;">Rp {{ number_format($header->total_biaya_kemasan, 0, ',', '.') }}</span>
            </div>
            <div class="cost-summary-pill">
                <span class="cost-pill-label">Upah Tenaga Kerja Kemas</span>
                <span class="cost-pill-val" style="color: #059669;">Rp {{ number_format($header->total_biaya_tenaga_kerja, 0, ',', '.') }}</span>
            </div>
            <div class="cost-summary-pill">
                <span class="cost-pill-label">Biaya Lain / Utilitas</span>
                <span class="cost-pill-val" style="color: #64748b;">Rp {{ number_format($header->total_biaya_lain, 0, ',', '.') }}</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-enterprise">
                <thead>
                    <tr>
                        <th style="width: 26%;">Komponen Biaya</th>
                        <th style="width: 32%;">Rincian Bahan &amp; Nomor Lot</th>
                        <th style="width: 14%; text-align: right;">Standar BOM</th>
                        <th style="width: 14%; text-align: right;">Kuantitas Aktual</th>
                        <th style="width: 14%; text-align: right;">Harga Satuan (Rp)</th>
                        <th style="width: 16%; text-align: right;">Nominal Biaya (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. KELOMPOK A: KERIPIK CURAH (WIP) --}}
                    <tr class="cost-group-header">
                        <td colspan="6">
                            A. Keripik Curah / Produk Matang (Direct Materials WIP)
                        </td>
                    </tr>
                    @foreach($wipItems as $wip)
                        <tr>
                            <td style="font-weight: 700; color: #0f172a; padding-left: 1.5rem;">
                                Keripik Curah (WIP)
                            </td>
                            <td>
                                <strong style="color: #0284c7;">{{ $wip->barang?->barang_nm }}</strong>
                                @if($wip->batch_no)
                                    <div style="font-size: 0.7rem; color: #64748b; font-family: monospace;">Lot: {{ $wip->batch_no }}</div>
                                @endif
                            </td>
                            <td style="text-align: right; color: #64748b; font-variant-numeric: tabular-nums;">
                                {{ number_format($wip->qty_standar, 2, ',', '.') }} {{ $wip->satuan_cd }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                                {{ number_format($wip->qty_aktual, 2, ',', '.') }} {{ $wip->satuan_cd }}
                            </td>
                            <td style="text-align: right; color: #475569; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($wip->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($wip->subtotal_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="cost-group-subtotal" style="color: #c2410c;">
                        <td colspan="5" style="padding-left: 1.5rem;">SUBTOTAL BIAYA CURAH (WIP):</td>
                        <td style="text-align: right; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->total_biaya_wip, 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- 2. KELOMPOK B: MATERIAL KEMASAN --}}
                    <tr class="cost-group-header">
                        <td colspan="6">
                            B. Biaya Kemasan &amp; Material Packing (Packaging Materials)
                        </td>
                    </tr>
                    @foreach($kemasanItems as $kem)
                        @php
                            $isLakban = str_contains(strtoupper($kem->barang?->barang_nm ?? ''), 'LAKBAN') || strtoupper($kem->kategori_bahan) === 'LAKBAN';
                        @endphp
                        <tr>
                            <td style="padding-left: 1.5rem; font-weight: 600; color: #334155;">
                                Bahan Kemasan ({{ $kem->kategori_bahan }})
                            </td>
                            <td>
                                <strong style="color: #0f172a;">{{ $kem->barang?->barang_nm }}</strong>
                                @if($kem->batch_no)
                                    <span style="font-size: 0.7rem; color: #64748b; font-family: monospace;">({{ $kem->batch_no }})</span>
                                @endif
                                @if($isLakban && ($header->lakban_atas_gr > 0 || $header->lakban_bawah_gr > 0 || $header->lakban_jumlah_gr > 0))
                                    <div style="font-size: 0.725rem; color: #059669; font-weight: 700; margin-top: 2px;">
                                        🏷️ Timbangan Segel: Atas {{ number_format($header->lakban_atas_gr, 0) }}g &bull; Bawah {{ number_format($header->lakban_bawah_gr, 0) }}g &bull; Total {{ number_format($header->lakban_jumlah_gr, 0) }}g
                                        @if($header->lakban_nilai > 0)
                                            <span style="color: #0284c7;">(Rp {{ number_format($header->lakban_nilai, 0, ',', '.') }})</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right; color: #64748b; font-variant-numeric: tabular-nums;">
                                {{ number_format($kem->qty_standar, 2, ',', '.') }} {{ $kem->satuan_cd }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                                {{ number_format($kem->qty_aktual, 2, ',', '.') }} {{ $kem->satuan_cd }}
                            </td>
                            <td style="text-align: right; color: #475569; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($kem->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($kem->subtotal_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="cost-group-subtotal" style="color: #0284c7;">
                        <td colspan="5" style="padding-left: 1.5rem;">SUBTOTAL BIAYA KEMASAN &amp; DUS:</td>
                        <td style="text-align: right; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->total_biaya_kemasan, 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- 3. KELOMPOK C: UPAH TENAGA KERJA LANGSUNG (BTKL) --}}
                    <tr class="cost-group-header">
                        <td colspan="6">
                            C. Upah Tenaga Kerja Langsung (BTKL) Pengemasan
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem; font-weight: 700; color: #0f172a;">Upah Tenaga Kerja Kemas</td>
                        <td colspan="4" style="color: #64748b;">
                            Upah borongan tim meja packing sachet &amp; susun karton dus
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->total_biaya_tenaga_kerja, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="cost-group-subtotal" style="color: #059669;">
                        <td colspan="5" style="padding-left: 1.5rem;">SUBTOTAL UPAH BTKL KEMAS:</td>
                        <td style="text-align: right; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->total_biaya_tenaga_kerja, 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- 4. KELOMPOK D: BIAYA OVERHEAD & UTILITAS (JIKA ADA) --}}
                    @if($header->total_biaya_lain > 0)
                        <tr class="cost-group-header">
                            <td colspan="6">
                                D. Biaya Overhead Pabrik &amp; Utilitas Pengemasan
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 1.5rem; font-weight: 700; color: #0f172a;">Biaya Lain-Lain / Overhead</td>
                            <td colspan="4" style="color: #64748b;">
                                Alokasi listrik sealer, pemeliharaan mesin packaging &amp; utilitas
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($header->total_biaya_lain, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="cost-group-subtotal" style="color: #6d28d9;">
                            <td colspan="5" style="padding-left: 1.5rem;">SUBTOTAL BIAYA OVERHEAD / LAIN-LAIN:</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($header->total_biaya_lain, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="cost-total-footer">
                    <tr>
                        <td colspan="5" style="font-size: 0.95rem; font-weight: 800;">
                            TOTAL BIAYA PRODUKSI PENGEMASAN:
                        </td>
                        <td style="text-align: right; font-size: 1.15rem; font-weight: 900; color: #0f172a; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="background: #ecfdf5; color: #065f46; font-size: 0.95rem; font-weight: 800;">
                        <td colspan="3" style="padding: 0.85rem 1rem;">
                            HASIL KEMAS: <span style="color: #065f46;">{{ number_format($header->hasil_karton_qty, 0, ',', '.') }} KARTON</span> ({{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs)
                        </td>
                        <td colspan="2" style="padding: 0.85rem 1rem; text-align: right;">
                            HPP FINAL PER KARTON:
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 900; font-size: 1.15rem; color: #059669; font-variant-numeric: tabular-nums;">
                            Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 5: LEMBAR PENGESAHAN & TANDA TANGAN FORMAL                   --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="signatures-grid">
        <div class="sign-box">
            <span class="sign-role">Diserahkan Oleh,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">{{ $header->created_by ?: 'Operator Kemas' }}</span>
                <div class="sign-sub">Operator Pengemasan</div>
            </div>
        </div>

        <div class="sign-box">
            <span class="sign-role">Diperiksa Oleh,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">Supervisor Packing &amp; QC</span>
                <div class="sign-sub">Kendali Mutu Pabrik</div>
            </div>
        </div>

        <div class="sign-box">
            <span class="sign-role">Diterima Oleh,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">Kepala Gudang FG</span>
                <div class="sign-sub">Penyimpanan Barang Jadi</div>
            </div>
        </div>

        <div class="sign-box">
            <span class="sign-role">Mengetahui,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">Manager Operasional</span>
                <div class="sign-sub">Pabrik PT Mirasa</div>
            </div>
        </div>
    </div>

    {{-- FOOTER AUDIT TRAIL --}}
    <div class="audit-trail-footer">
        <div>
            Dibuat oleh: <strong>{{ $header->created_by ?? 'Operator Packing' }}</strong> 
            pada {{ $header->created_at ? $header->created_at->format('d/m/Y H:i') . ' WIB' : '-' }}
        </div>
        <div>
            @if($header->updated_by)
                Terakhir diubah oleh: <strong>{{ $header->updated_by }}</strong> ({{ $header->updated_at ? $header->updated_at->format('d/m/Y H:i') : '-' }}) &bull;
            @endif
            Versi Dokumen: <strong>v{{ $header->version_no ?? 1 }}</strong> &bull;
            Status: <strong>{{ $header->status_cd }}</strong>
        </div>
    </div>
</div>

{{-- MODAL PEMBATALAN (VOID) --}}
@if($header->status_cd === 'POSTED')
    @include('produksi.packaging.partials.modal-void')
@endif
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/packaging/packaging-show.js') }}?v={{ filemtime(public_path('js/produksi/packaging/packaging-show.js')) }}"></script>
@endpush
