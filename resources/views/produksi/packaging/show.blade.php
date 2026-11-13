@extends('layouts.app')

@section('title', 'Bukti Serah Terima Pengemasan FG - ' . $header->pack_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-show.css') }}?v={{ filemtime(public_path('css/produksi/packaging/packaging-show.css')) }}">
@endpush

@section('content')
<div class="show-container packaging-show-container">
    @php
        $expCarbon = $header->expired_tgl
            ? ($header->expired_tgl instanceof \Carbon\CarbonInterface ? $header->expired_tgl : Carbon\Carbon::parse($header->expired_tgl))
            : ($header->pack_tgl ? Carbon\Carbon::parse($header->pack_tgl)->addMonths(6) : now()->addMonths(6));
        $expDate = $expCarbon->format('d/m/Y');

        $wipItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) === 'WIP');
        $kemasanItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) !== 'WIP');

        $lko = $header->laporanKemasOperator;
        $dusAfkir = (float) ($lko?->karton_rusak_lembar ?? 0);
        $plastikAfkir = (float) ($lko?->plastik_rusak_pcs ?? 0);
        $lakbanRusak = (float) ($lko?->lakban_rusak_m ?? 0);
    @endphp

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- KOP SURAT RESMI CETAK GUDANG & OPERASIONAL (PRINT-ONLY)            --}}
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
                <strong style="font-size: 1.05rem; color: #059669; display: block;">SURAT BUKTI HASIL PENGEMASAN &amp; SERAH TERIMA GUDANG FG</strong>
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
                    <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Selesai Dikemas</span>
                    </span>
                @else
                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-weight: 700;">VOID (Dibatalkan)</span>
                @endif
                <span style="font-size: 0.75rem; font-weight: 700; color: #0369a1; background: #e0f2fe; border: 1px solid #bae6fd; padding: 0.2rem 0.6rem; border-radius: 4px;">
                    {{ $header->gudang?->gudang_nm ?? 'Gudang Barang Jadi' }}
                </span>
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
                <span>Cetak Bukti Serah Terima</span>
            </button>

            @if($header->status_cd === 'POSTED' && !$header->isHppFinal() && Auth::user()->canCreatePackaging())
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

    {{-- TAB SWITCHER SPLIT DOKUMEN PENGEMASAN (JIKA PADA TANGGAL INI TERDAPAT LEBIH DARI 1 DOKUMEN KEMAS) --}}
    @if(isset($sameDatePackaging) && $sameDatePackaging->count() > 1)
        <div class="line-split-tabs-wrapper no-print">
            <div class="line-split-header">
                <div class="line-split-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Dokumen Pengemasan Hari Ini ({{ Carbon\Carbon::parse($header->pack_tgl)->format('d/m/Y') }}):</span>
                </div>
                <div class="line-split-badge-info">
                    <span>Tersedia {{ $sameDatePackaging->count() }} Dokumen Produk &bull; Klik Tab untuk Beralih</span>
                </div>
            </div>

            <div class="line-split-tabs-list">
                @foreach($sameDatePackaging as $pItem)
                    @php
                        $isActive = $pItem->pack_id === $header->pack_id;
                    @endphp
                    <a href="{{ route('produksi.packaging.show', $pItem->pack_id) }}" 
                       class="line-split-tab-card {{ $isActive ? 'active tab-lini-ifm' : '' }}">
                        <div class="tab-card-top">
                            <div class="tab-card-title-group">
                                <span class="tab-card-dot" style="background: {{ $isActive ? '#059669' : '#94a3b8' }};"></span>
                                <span class="tab-card-name">{{ $pItem->barangJadi?->barang_nm ?? '-' }}</span>
                            </div>
                            @if($isActive)
                                <span class="tab-card-active-pill" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">Sedang Ditampilkan</span>
                            @else
                                <span class="tab-card-switch-hint">Beralih ke Dokumen Ini &rarr;</span>
                            @endif
                        </div>
                        <div class="tab-card-meta">
                            <span class="meta-doc-no">#{{ $pItem->pack_no }}</span>
                            <span class="meta-sep">&bull;</span>
                            <span class="meta-qty">{{ number_format($pItem->hasil_karton_qty, 0, ',', '.') }} Ktn ({{ number_format($pItem->hasil_pcs_qty, 0, ',', '.') }} Pcs)</span>
                            <span class="meta-sep">&bull;</span>
                            <span class="meta-hpp">{{ $pItem->gudang?->gudang_nm ?? 'Gudang FG' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 1: 4 KARTU METRIK OPERASIONAL FISIK GUDANG                   --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="executive-stat-grid">
        {{-- METRIK 1: HASIL KEMAS SIAP JUAL (KARTON) --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Hasil Kemas Siap Jual</span>
                    <div class="executive-stat-value" style="color: #059669;">
                        {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #ecfdf5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Target persediaan di gudang barang jadi
            </div>
        </div>

        {{-- METRIK 2: HASIL KEMAS ECERAN (PCS) --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Hasil Kemas Eceran</span>
                    <div class="executive-stat-value" style="color: #0284c7;">
                        {{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Pcs</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Kemasan retail ({{ number_format($header->isi_per_karton_pcs, 0) }} Pcs/Karton)
            </div>
        </div>

        {{-- METRIK 3: BAHAN CURAH WIP TERPAKAI --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Curah WIP Terpakai</span>
                    <div class="executive-stat-value" style="color: #d97706;">
                        {{ number_format($header->total_wip_kg, 1, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #fffbeb; color: #d97706;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Berat bersih keripik matang dikemas
            </div>
        </div>

        {{-- METRIK 4: GUDANG TUJUAN PENYIMPANAN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Gudang Penyimpanan</span>
                    <div class="executive-stat-value" style="font-size: 1.05rem; color: #334155; line-height: 1.35;">
                        {{ $header->gudang?->gudang_nm ?? 'Gudang FG' }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #f1f5f9; color: #475569;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Status mutasi stok: Bertambah di gudang
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 2: 2 PANEL SEJAJAR (PARAMETER OPERASIONAL & BATCH GUDANG FG) --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="meta-cards-grid-2">
        {{-- PANEL 1: PARAMETER OPERASIONAL PENGEMASAN --}}
        <div class="card-enterprise" style="margin-bottom: 0;">
            <div class="card-enterprise-header">
                <div class="card-enterprise-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Parameter Operasional Pengemasan</span>
                </div>
                <span class="cat-badge cat-kemasan">
                    SOP Pengemasan FG
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
                    @if($header->produksi)
                    <tr>
                        <td class="label-col">Tiket Asal Masak (SPK)</td>
                        <td class="value-col">
                            <a href="{{ route('produksi.show', $header->produksi_id) }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; color: #059669; font-weight: 700;" title="Buka Detail SPK Produksi">
                                <span>{{ $header->produksi->produksi_no }}</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.25rem;">({{ Carbon\Carbon::parse($header->produksi->produksi_tgl)->format('d/m/Y') }})</span>
                        </td>
                    </tr>
                    @endif
                    @if($header->pemakaian)
                    <tr>
                        <td class="label-col">Bukti Pengeluaran Bahan</td>
                        <td class="value-col">
                            <a href="{{ route('gudang.pemakaian.show', $header->pakai_id) }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; color: #0284c7; font-weight: 700;" title="Buka Bukti Pengeluaran Bahan Kemas (BPPB)">
                                <span>{{ $header->pemakaian->pakai_no }}</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <span style="font-size: 0.725rem; color: #059669; font-weight: 600; margin-left: 0.25rem;">(Auto BPPB Gudang)</span>
                        </td>
                    </tr>
                    @endif
                    @if($lko && ($dusAfkir > 0 || $plastikAfkir > 0 || $lakbanRusak > 0))
                    <tr>
                        <td class="label-col">Afkir / Scrap Kemasan</td>
                        <td class="value-col">
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                @if($dusAfkir > 0)
                                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                        Dus Afkir: {{ number_format($dusAfkir, 0, ',', '.') }} lbr
                                    </span>
                                @endif
                                @if($plastikAfkir > 0)
                                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                        Plastik Reject: {{ number_format($plastikAfkir, 0, ',', '.') }} pcs
                                    </span>
                                @endif
                                @if($lakbanRusak > 0)
                                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                        Lakban Kusut: {{ number_format($lakbanRusak, 1, ',', '.') }} m
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label-col">Catatan Operasional</td>
                        <td class="value-col">{{ $header->catatan_txt ?: 'Pengemasan standar finish goods retail snack.' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- PANEL 2: IDENTITAS BATCH & GUDANG FG --}}
        <div class="card-enterprise" style="margin-bottom: 0;">
            <div class="card-enterprise-header">
                <div class="card-enterprise-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Identitas Batch &amp; Gudang Simpan</span>
                </div>
                <span class="badge-batch-pill">
                    {{ $header->batch_no ?: $header->pack_no }}
                </span>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <table class="spec-table">
                    <tr>
                        <td class="label-col">No. Dokumen Kemas</td>
                        <td class="value-col" style="font-family: monospace; font-weight: 700; color: #0284c7;">
                            {{ $header->pack_no }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">No. Batch Persediaan FG</td>
                        <td class="value-col" style="font-family: monospace; font-weight: 700; color: #059669;">
                            {{ $header->batch_no ?: '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Gudang Simpan FG</td>
                        <td class="value-col" style="font-weight: 600;">{{ $header->gudang?->gudang_nm ?? 'Gudang Hasil Produksi (FG)' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Masa Kedaluwarsa (Exp)</td>
                        <td class="value-col" style="color: #059669; font-weight: 700;">
                            {{ $expDate }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Standar Kemasan Karton</td>
                        <td class="value-col">
                            <strong>{{ number_format($header->isi_per_karton_pcs, 0) }} Bungkus / Karton</strong>
                            @if($header->gramatur_gr > 0)
                                <span style="font-size: 0.775rem; color: #64748b; margin-left: 0.25rem;">({{ number_format($header->gramatur_gr, 1, ',', '.') }} gr/pcs)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Segel Lakban Dus Karton</td>
                        <td class="value-col">
                            @if($header->lakban_atas_gr > 0 || $header->lakban_bawah_gr > 0 || $header->lakban_jumlah_gr > 0)
                                <span style="font-weight: 700; color: #059669;">
                                    Atas: {{ number_format($header->lakban_atas_gr, 0) }}g &bull; Bawah: {{ number_format($header->lakban_bawah_gr, 0) }}g &bull; Total: {{ number_format($header->lakban_jumlah_gr, 0) }}g
                                </span>
                            @else
                                <span style="color: #64748b;">Standar Operasional Meja Kemas</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Petugas Pencatat</td>
                        <td class="value-col" style="font-weight: 600;">{{ $header->created_by ?? 'Operator Packing' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL 1: HASIL OUTPUT BARANG JADI SIAP JUAL (FG OUTPUT)            --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="card-enterprise" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Rincian Hasil Barang Jadi (Finished Goods Output)</span>
            </div>
            <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                Serah Terima Fisik ke Gudang Barang Jadi
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-enterprise">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 140px;">Kategori</th>
                        <th>Kode &amp; Nama Produk Jadi (FG)</th>
                        <th style="width: 150px; text-align: center;">No. Batch FG</th>
                        <th style="width: 130px; text-align: center;">Expired Date</th>
                        <th style="width: 180px;">Gudang Penyimpanan</th>
                        <th style="width: 140px; text-align: right;">Hasil (Karton)</th>
                        <th style="width: 110px; text-align: right;">Isi / Ktn</th>
                        <th style="width: 140px; text-align: right;">Total Hasil (Pcs)</th>
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
                        <td style="text-align: center; color: #059669; font-weight: 600;">
                            {{ $expDate }}
                        </td>
                        <td style="color: #334155; font-weight: 600;">
                            {{ $header->gudang?->gudang_nm ?? 'Gudang Hasil Produksi' }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #059669; font-variant-numeric: tabular-nums; font-size: 0.95rem;">
                            {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} Karton
                        </td>
                        <td style="text-align: right; color: #64748b; font-variant-numeric: tabular-nums;">
                            {{ number_format($header->isi_per_karton_pcs, 0) }} Pcs
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums; font-size: 0.95rem;">
                            {{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs
                        </td>
                    </tr>
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 700; border-top: 2px solid #e2e8f0;">
                    <tr>
                        <td colspan="6" style="padding: 0.75rem 1rem; text-align: right; font-size: 0.825rem; color: #475569;">
                            TOTAL BARANG JADI (FG) SIAP JUAL:
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #059669; font-size: 1rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} Karton
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #64748b; font-size: 0.8rem;">
                            -
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #0284c7; font-size: 1rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL 2: RINCIAN BAHAN CURAH (WIP) TERPAKAI                        --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="card-enterprise" style="margin-bottom: 1.5rem;">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d97706;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Rincian Bahan Curah (WIP) Terpakai</span>
            </div>
            <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                Keripik Matang Hasil Penggorengan Dapur
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-enterprise">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 140px;">Kode Barang</th>
                        <th>Nama Bahan Curah (WIP)</th>
                        <th style="width: 180px;">Asal Tiket SPK / Lot Masak</th>
                        <th style="width: 140px; text-align: right;">Standar BOM</th>
                        <th style="width: 150px; text-align: right;">Aktual Terpakai</th>
                        <th style="width: 90px; text-align: center;">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wipItems as $idx => $wip)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $idx + 1 }}</td>
                            <td style="font-family: monospace; font-weight: 600; color: #475569;">
                                {{ $wip->barang?->barang_cd ?? '-' }}
                            </td>
                            <td>
                                <strong style="color: #0284c7;">{{ $wip->barang?->barang_nm }}</strong>
                            </td>
                            <td>
                                @if($header->produksi)
                                    <span style="font-weight: 600; color: #059669;">SPK #{{ $header->produksi->produksi_no }}</span>
                                @elseif($wip->batch_no)
                                    <span style="font-family: monospace; color: #475569;">Lot: {{ $wip->batch_no }}</span>
                                @else
                                    <span style="color: #94a3b8;">Konsumsi Bebas / Gudang</span>
                                @endif
                            </td>
                            <td style="text-align: right; color: #64748b; font-variant-numeric: tabular-nums;">
                                {{ number_format($wip->qty_standar, 2, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #d97706; font-variant-numeric: tabular-nums; font-size: 0.95rem;">
                                {{ number_format($wip->qty_aktual, 2, ',', '.') }}
                            </td>
                            <td style="text-align: center; color: #475569; font-weight: 600;">
                                {{ $wip->satuan_cd }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 1.5rem;">Tidak ada data bahan curah tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 700; border-top: 2px solid #e2e8f0;">
                    <tr>
                        <td colspan="5" style="padding: 0.75rem 1rem; text-align: right; font-size: 0.825rem; color: #475569;">
                            TOTAL BAHAN CURAH TERKONSUMSI:
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #d97706; font-size: 1rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($header->total_wip_kg, 2, ',', '.') }}
                        </td>
                        <td style="text-align: center; color: #475569;">
                            kg
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL 3: RINCIAN PEMAKAIAN BAHAN KEMASAN (PACKAGING MATERIALS)     --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="card-enterprise" style="margin-bottom: 1.5rem;">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span>Rincian Pemakaian Bahan Kemasan &amp; Pembantu</span>
            </div>
            <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                Bahan Dus Karton, Plastik Kemas, Lakban, Bumbu &amp; Label
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-enterprise">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 140px;">Kategori Bahan</th>
                        <th style="width: 130px;">Kode Bahan</th>
                        <th>Nama Barang Kemasan</th>
                        <th style="width: 140px; text-align: right;">Standar BOM</th>
                        <th style="width: 150px; text-align: right;">Aktual Terpakai</th>
                        <th style="width: 90px; text-align: center;">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kemasanItems as $kIdx => $kem)
                        @php
                            $isLakban = str_contains(strtoupper($kem->barang?->barang_nm ?? ''), 'LAKBAN') || strtoupper($kem->kategori_bahan) === 'LAKBAN';
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $kIdx + 1 }}</td>
                            <td>
                                <span class="cat-badge cat-kemasan" style="font-size: 0.7rem;">
                                    {{ $kem->kategori_bahan }}
                                </span>
                            </td>
                            <td style="font-family: monospace; font-weight: 600; color: #475569;">
                                {{ $kem->barang?->barang_cd ?? '-' }}
                            </td>
                            <td>
                                <strong style="color: #0f172a;">{{ $kem->barang?->barang_nm }}</strong>
                                @if($isLakban && ($header->lakban_atas_gr > 0 || $header->lakban_bawah_gr > 0 || $header->lakban_jumlah_gr > 0))
                                    <div style="font-size: 0.725rem; color: #059669; font-weight: 700; margin-top: 2px;">
                                        🏷️ Timbangan Segel: Atas {{ number_format($header->lakban_atas_gr, 0) }}g &bull; Bawah {{ number_format($header->lakban_bawah_gr, 0) }}g &bull; Total {{ number_format($header->lakban_jumlah_gr, 0) }}g
                                    </div>
                                @endif
                                @if($kem->batch_no)
                                    <div style="font-size: 0.7rem; color: #64748b; font-family: monospace;">Lot: {{ $kem->batch_no }}</div>
                                @endif
                            </td>
                            <td style="text-align: right; color: #64748b; font-variant-numeric: tabular-nums;">
                                {{ number_format($kem->qty_standar, 2, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0284c7; font-variant-numeric: tabular-nums; font-size: 0.95rem;">
                                {{ number_format($kem->qty_aktual, 2, ',', '.') }}
                            </td>
                            <td style="text-align: center; color: #475569; font-weight: 600;">
                                {{ $kem->satuan_cd }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 1.5rem;">Tidak ada pemakaian bahan kemas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 4: LEMBAR PENGESAHAN & TANDA TANGAN SERAH TERIMA FISIK       --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="signatures-grid">
        <div class="sign-box">
            <span class="sign-role">Diserahkan Oleh,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">{{ $header->created_by ?: 'Operator Kemas' }}</span>
                <div class="sign-sub">Leader / Operator Kemas</div>
            </div>
        </div>

        <div class="sign-box">
            <span class="sign-role">Diperiksa Oleh,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">Supervisor Packing &amp; QC</span>
                <div class="sign-sub">Kendali Mutu Lapangan</div>
            </div>
        </div>

        <div class="sign-box">
            <span class="sign-role">Diterima Oleh,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">Kepala Gudang FG</span>
                <div class="sign-sub">Penerimaan Barang Jadi</div>
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
