@extends('layouts.app')

@section('title', 'Lembar HPP & Biaya Pengemasan - ' . $header->pack_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-show.css') }}?v={{ filemtime(public_path('css/produksi/produksi-show.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-show.css') }}?v={{ filemtime(public_path('css/produksi/packaging/packaging-show.css')) }}">
@endpush

@section('content')
<div class="show-container packaging-show-container">
    @php
        $expCarbon = $header->expired_tgl
            ? ($header->expired_tgl instanceof \Carbon\CarbonInterface ? $header->expired_tgl : Carbon\Carbon::parse($header->expired_tgl))
            : ($header->pack_tgl ? Carbon\Carbon::parse($header->pack_tgl)->addMonths(6) : now()->addMonths(6));
        $expDate = $expCarbon->format('d/m/Y');

        $totBiaya = (float) $header->total_biaya_produksi;
        $totKarton = (float) ($header->hasil_karton_qty ?: 1);

        $biayaWip = (float) $header->total_biaya_wip;
        $biayaKemasan = (float) $header->total_biaya_kemasan;
        $biayaBtkl = (float) $header->total_biaya_tenaga_kerja;
        $biayaLain = (float) $header->total_biaya_lain;

        $pctWip = $totBiaya > 0 ? ($biayaWip / $totBiaya) * 100 : 0;
        $pctKemasan = $totBiaya > 0 ? ($biayaKemasan / $totBiaya) * 100 : 0;
        $pctBtkl = $totBiaya > 0 ? ($biayaBtkl / $totBiaya) * 100 : 0;
        $pctLain = $totBiaya > 0 ? ($biayaLain / $totBiaya) * 100 : 0;

        $wipItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) === 'WIP');
        $bumbuItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) === 'BUMBU');
        $kemasanItems = $header->details->filter(fn($d) => !in_array(strtoupper($d->kategori_bahan), ['WIP', 'BUMBU']));
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
                <strong style="font-size: 1.05rem; color: #0284c7; display: block;">LEMBAR BUKTI HASIL PENGEMASAN &amp; HARGA POKOK (HPP)</strong>
                <span style="font-family: monospace; font-size: 0.9rem; font-weight: 700;">{{ $header->pack_no }}</span>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS HEADER NAVIGASI & AKSI (NO-PRINT)                            --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="show-header no-print">
        <div>
            <a href="{{ route('produksi.packaging.rekap') }}" class="btn-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Rekapitulasi HPP Kemas</span>
            </a>
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $header->pack_no }}</h1>
                @if ($header->status_cd === 'POSTED')
                    @if($header->isHppFinal())
                        <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700;">HPP Final</span>
                        @if($header->jurnal_id && $header->jurnal)
                            <a href="{{ route('keuangan.jurnal.index', ['search' => $header->jurnal->jurnal_no]) }}" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; font-size: 0.775rem; font-weight: 700; padding: 4px 10px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Lihat detail Jurnal Umum Akuntansi">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Jurnal: {{ $header->jurnal->jurnal_no }}</span>
                            </a>
                        @endif
                    @else
                        <span class="badge" style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 700;">Belum Final HPP (Estimasi)</span>
                    @endif
                @else
                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">VOID (Dibatalkan)</span>
                @endif
            </div>
        </div>

        <div class="show-actions">
            @if($header->status_cd === 'POSTED' && !$header->isHppFinal() && Auth::user()->canCreatePackaging())
                @if($header->canCalculateHpp())
                    <button type="button" 
                            onclick="openFinalizeHppModal({{ $header->pack_id }}, '{{ $header->pack_no }}', '{{ addslashes($header->barangJadi?->barang_nm ?? '') }}', '{{ $header->produksi?->produksi_no ?? '-' }}', '{{ number_format($header->produksi?->hpp_per_kg ?? 0, 0, ',', '.') }}')" 
                            class="btn btn-primary" 
                            style="font-size: 0.85rem; padding: 0.5rem 0.95rem; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.4rem; background: #0284c7; border-color: #0284c7; color: #ffffff;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Tarik &amp; Finalisasi HPP FG</span>
                    </button>
                @else
                    <button type="button" 
                            onclick="openWarningProdHppModal('{{ $header->pack_no }}', '{{ $header->produksi?->produksi_no ?? '-' }}')" 
                            class="btn" 
                            style="font-size: 0.85rem; padding: 0.5rem 0.95rem; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.4rem; background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Menunggu HPP Produksi ({{ $header->produksi?->produksi_no ?? 'SPK' }})</span>
                    </button>
                @endif
            @endif

            @if($header->status_cd === 'POSTED' && !$header->isHppFinal() && Auth::user()->canCreatePackaging())
                <a href="{{ route('produksi.packaging.adjust', $header->pack_id) }}" class="btn-adjust-hpp" title="Penyesuaian Biaya & HPP Pengemasan">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Adjustment HPP</span>
                </a>
            @endif
            @if($header->produksi_id)
                <a href="{{ route('produksi.hpp.terpadu', $header->produksi_id) }}" class="btn-print-doc" style="background: #0284c7; color: #fff; text-decoration: none; border-color: #0369a1; font-weight: 600;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Lembar HPP Terpadu</span>
                </a>
            @endif
            <button type="button" onclick="window.print()" class="btn-print-doc">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar HPP Kemas</span>
            </button>
            @if($header->status_cd === 'POSTED' && !$header->isHppFinal() && Auth::user()->canCreatePackaging())
                <button type="button" id="btnVoidPackaging" data-pack-id="{{ $header->pack_id }}" data-pack-no="{{ $header->pack_no }}" data-target-nm="{{ $header->barangJadi?->barang_nm ?? '' }}" onclick="openVoidPackagingModal({{ $header->pack_id }}, '{{ $header->pack_no }}', '{{ addslashes($header->barangJadi?->barang_nm ?? '') }}')" class="btn btn-danger" style="font-size: 0.85rem; padding: 0.5rem 0.95rem; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Batalkan (Void)</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 1: 4 KARTU METRIK EKSEKUTIF UTAMA (EXECUTIVE STAT GRID)      --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL BIAYA PENGEMASAN --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Total Biaya Pengemasan</span>
                    <div class="executive-stat-value">
                        Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                {{ $header->isHppFinal() ? 'Akumulasi Curah WIP, Bahan Kemas & Upah BTKL' : 'Estimasi Biaya Kemasan (Menunggu Finalisasi HPP)' }}
            </div>
        </div>

        {{-- METRIK 2: HASIL KEMAS SIAP JUAL --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Total Output Barang Jadi (FG)</span>
                    <div class="executive-stat-value">
                        {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #f1f5f9; color: #475569;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                <strong style="color: #059669;">{{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs</strong> ({{ number_format($header->isi_per_karton_pcs, 0) }} Pcs/Ktn)
            </div>
        </div>

        {{-- METRIK 3: BEBAN KEMASAN PER KARTON --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #0284c7;">Beban Kemasan per Karton</span>
                    <div class="executive-stat-value" style="color: #0284c7;">
                        Rp {{ number_format($totKarton > 0 ? ($header->total_biaya_kemasan / $totKarton) : 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                Dus, Plastik, Lakban &amp; Label per Box
            </div>
        </div>

        {{-- METRIK 4: HPP RIIL PER KARTON & PER PCS --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #059669;">
                        {{ $header->isHppFinal() ? 'HPP Riil per Karton' : 'Estimasi HPP per Karton' }}
                    </span>
                    <div class="executive-stat-value" style="color: #059669;">
                        Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #ecfdf5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext" style="color: #047857; font-weight: 700;">
                Setara Rp {{ number_format($header->hpp_per_pcs, 0, ',', '.') }} / Pcs Eceran
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 2: 2 PANEL SEJAJAR (PARAMETER OPERASIONAL & BATCH GUDANG FG) --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="meta-cards-grid-2">
        {{-- PANEL 1: PARAMETER OPERASIONAL & BIAYA --}}
        <div class="card-enterprise" style="margin-bottom: 0;">
            <div class="card-enterprise-header">
                <div class="card-enterprise-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Parameter Operasional &amp; Biaya</span>
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
                    @if($header->produksi)
                    <tr>
                        <td class="label-col">Tiket Asal Produksi (SPK)</td>
                        <td class="value-col">
                            <a href="{{ route('produksi.hpp.show', $header->produksi_id) }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; color: #059669; font-weight: 700;" title="Buka Detail Lembar HPP Produksi (WIP)">
                                <span>{{ $header->produksi->produksi_no }}</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.25rem;">({{ Carbon\Carbon::parse($header->produksi->produksi_tgl)->format('d/m/Y') }} &bull; HPP Rp {{ number_format($header->produksi->hpp_per_kg, 0, ',', '.') }}/kg)</span>
                        </td>
                    </tr>
                    @endif
                    @if($header->pemakaian)
                    <tr>
                        <td class="label-col">Bukti Pengeluaran Gudang</td>
                        <td class="value-col">
                            <a href="{{ route('gudang.pemakaian.show', $header->pakai_id) }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; color: #0284c7; font-weight: 700;" title="Buka Bukti Pengeluaran Barang / Bahan Gudang (BPPB)">
                                <span>{{ $header->pemakaian->pakai_no }}</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <span style="font-size: 0.725rem; color: #059669; font-weight: 600; margin-left: 0.25rem;">(Auto BPPB Gudang)</span>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label-col">Alokasi Upah BTKL Kemas</td>
                        <td class="value-col">
                            <span style="font-weight: 700; color: #059669;">Rp {{ number_format($header->total_biaya_tenaga_kerja, 0, ',', '.') }}</span>
                            <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.25rem;">({{ $header->jumlah_personil ?: 5 }} Personil Meja Kemas)</span>
                        </td>
                    </tr>
                    @if($header->total_biaya_lain > 0)
                    <tr>
                        <td class="label-col">Beban Utilitas &amp; BOP</td>
                        <td class="value-col" style="font-weight: 700; color: #6d28d9;">
                            Rp {{ number_format($header->total_biaya_lain, 0, ',', '.') }}
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
                    <span>Identitas Batch &amp; Gudang FG</span>
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
                        <td class="value-col" style="font-family: monospace; font-weight: 700; color: #059669; font-size: 0.95rem;">
                            {{ $header->batch_no ?: '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Gudang Penyimpanan FG</td>
                        <td class="value-col">{{ $header->gudang?->gudang_nm ?? 'Gudang Hasil Produksi (FG)' }}</td>
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
                                @if($header->lakban_nilai > 0)
                                    <span style="color: #0284c7; font-weight: 700; font-size: 0.775rem; margin-left: 0.25rem;">(Rp {{ number_format($header->lakban_nilai, 0, ',', '.') }})</span>
                                @endif
                            @else
                                <span style="color: #64748b;">Standar Operasional Meja Kemas</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Operator Pencatat</td>
                        <td class="value-col" style="font-weight: 600;">{{ $header->created_by ?? 'Operator Packing' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Status Jurnal HPP FG</td>
                        <td class="value-col">
                            @if($header->jurnal_id && $header->jurnal)
                                <a href="{{ route('keuangan.jurnal.index', ['search' => $header->jurnal->jurnal_no]) }}" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 0.775rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Lihat detail Jurnal Umum Akuntansi">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Sudah Terbit ({{ $header->jurnal->jurnal_no }})</span>
                                </a>
                            @elseif($header->isHppFinal())
                                <span style="color: #059669; font-weight: 700; font-size: 0.8125rem;">HPP Final</span>
                            @else
                                <span style="color: #92400e; font-weight: 600; font-size: 0.8125rem;">Belum Terbit (HPP Belum Final)</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 3: TAB CONTAINER TERPADU (OUTPUT FG | BIAYA HPP | AUDIT)    --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="prod-tab-container">
        {{-- TAB NAVIGATION HEADER (NO-PRINT) --}}
        <div class="prod-tab-nav no-print">
            <button type="button" class="prod-tab-btn active" data-tab="output" onclick="switchProdTab('output', event)">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Hasil Output Barang Jadi</span>
                <span class="prod-tab-badge">{{ number_format($header->hasil_karton_qty, 0, ',', '.') }} Karton</span>
            </button>
            <button type="button" class="prod-tab-btn" data-tab="biaya" onclick="switchProdTab('biaya', event)">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Kalkulasi Biaya Pengemasan</span>
                <span class="prod-tab-badge">Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}</span>
            </button>
            <button type="button" class="prod-tab-btn" data-tab="audit" onclick="switchProdTab('audit', event)">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Riwayat Adjustment &amp; Audit</span>
                <span class="prod-tab-badge">v{{ $header->version_no ?? 1 }}</span>
            </button>
        </div>

        {{-- ── TAB 1: HASIL OUTPUT BARANG JADI (FG OUTPUT) ── --}}
        <div id="pane-output" class="prod-tab-pane active">
            <div class="prod-tab-pane-header">
                <div class="prod-tab-pane-title">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
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
                            <td style="padding: 0.75rem 1rem; text-align: right; color: #0284c7; font-size: 0.95rem; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── TAB 2: KALKULASI BIAYA PENGEMASAN (MIRIP HPP WIP) ── --}}
        <div id="pane-biaya" class="prod-tab-pane">
            <div class="prod-tab-pane-header">
                <div class="prod-tab-pane-title">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Kalkulasi Komponen Biaya Manufaktur Pengemasan</span>
                </div>
                <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                    Distribusi Beban Curah WIP, Bahan Kemasan, Upah BTKL &amp; Overhead
                </span>
            </div>

            {{-- 1. DISTRIBUSI KOMPOSISI BIAYA (COST PROPORTION BAR DARI HPP WIP) --}}
            <div class="cost-distribution-card">
                <div class="cost-distribution-header">
                    <span class="cost-dist-title">Distribusi Biaya per Karton FG (HPP: Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }} / ktn)</span>
                </div>
                <div class="cost-dist-bar-track">
                    <div class="cost-dist-segment" style="width: {{ $pctWip }}%; background: #ea580c;" title="Curah WIP: {{ number_format($pctWip, 1) }}%"></div>
                    <div class="cost-dist-segment seg-kemasan" style="width: {{ $pctKemasan }}%;" title="Kemasan: {{ number_format($pctKemasan, 1) }}%"></div>
                    <div class="cost-dist-segment seg-energi" style="width: {{ $pctBtkl }}%;" title="Upah BTKL: {{ number_format($pctBtkl, 1) }}%"></div>
                    <div class="cost-dist-segment seg-foh" style="width: {{ $pctLain }}%;" title="Overhead/Lain: {{ number_format($pctLain, 1) }}%"></div>
                </div>
                <div class="cost-dist-legend-grid">
                    <div class="cost-legend-item">
                        <span class="cost-legend-dot" style="background: #ea580c;"></span>
                        <div>
                            <span class="cost-legend-label">Bahan Curah (WIP)</span>
                            <div class="cost-legend-value">
                                Rp {{ number_format($totKarton > 0 ? $biayaWip / $totKarton : 0, 0, ',', '.') }}/ktn 
                                <span class="cost-legend-pct">({{ number_format($pctWip, 1) }}%)</span>
                            </div>
                        </div>
                    </div>
                    <div class="cost-legend-item">
                        <span class="cost-legend-dot seg-kemasan"></span>
                        <div>
                            <span class="cost-legend-label">Bahan Kemasan &amp; Dus</span>
                            <div class="cost-legend-value">
                                Rp {{ number_format($totKarton > 0 ? $biayaKemasan / $totKarton : 0, 0, ',', '.') }}/ktn 
                                <span class="cost-legend-pct">({{ number_format($pctKemasan, 1) }}%)</span>
                            </div>
                        </div>
                    </div>
                    <div class="cost-legend-item">
                        <span class="cost-legend-dot seg-energi"></span>
                        <div>
                            <span class="cost-legend-label">Upah Tenaga Kerja Kemas</span>
                            <div class="cost-legend-value">
                                Rp {{ number_format($totKarton > 0 ? $biayaBtkl / $totKarton : 0, 0, ',', '.') }}/ktn 
                                <span class="cost-legend-pct">({{ number_format($pctBtkl, 1) }}%)</span>
                            </div>
                        </div>
                    </div>
                    <div class="cost-legend-item">
                        <span class="cost-legend-dot seg-foh"></span>
                        <div>
                            <span class="cost-legend-label">Biaya Utilitas &amp; BOP</span>
                            <div class="cost-legend-value">
                                Rp {{ number_format($totKarton > 0 ? $biayaLain / $totKarton : 0, 0, ',', '.') }}/ktn 
                                <span class="cost-legend-pct">({{ number_format($pctLain, 1) }}%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. TABEL STRUKTUR BIAYA 5 KOLOM SESUAI STANDAR HPP WIP --}}
            <div style="border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-top: 1rem;">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; table-layout: fixed;">
                        <thead>
                            <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; color: #1e293b; font-weight: 700; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.03em;">
                                <th style="padding: 0.75rem 0.5rem; width: 5%; text-align: center; border-right: 1px solid #cbd5e1;">No</th>
                                <th style="padding: 0.75rem 0.85rem; width: 30%; text-align: left; border-right: 1px solid #cbd5e1;">Komponen Biaya Manufaktur</th>
                                <th style="padding: 0.75rem 0.85rem; width: 31%; text-align: left; border-right: 1px solid #cbd5e1;">Rincian Fisik / Parameter Kuantitas</th>
                                <th style="padding: 0.75rem 0.85rem; width: 17%; text-align: right; border-right: 1px solid #cbd5e1;">Tarif / Harga Satuan</th>
                                <th style="padding: 0.75rem 0.85rem; width: 17%; text-align: right;">Subtotal Biaya (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- A. BAHAN CURAH LANGSUNG (DIRECT MATERIALS WIP) --}}
                            <tr style="background: #f8fafc; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                <td colspan="5" style="padding: 0.55rem 0.85rem; font-weight: 800; font-size: 0.8rem; color: #0f172a; letter-spacing: 0.03em;">
                                    A. BAHAN CURAH LANGSUNG (DIRECT MATERIALS WIP)
                                </td>
                            </tr>
                            @php $rowNo = 1; @endphp
                            @foreach($wipItems as $wip)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="text-align: center; color: #64748b; font-weight: 600; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.5rem;">{{ $rowNo++ }}</td>
                                    <td style="font-weight: 600; color: #0f172a; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                        <span>{{ $wip->barang?->barang_nm }}</span>
                                        @if($wip->batch_no)
                                            <span class="chip-batch-badge" style="font-size: 0.68rem; margin-left: 0.35rem; vertical-align: middle;">Lot: {{ $wip->batch_no }}</span>
                                        @endif
                                    </td>
                                    <td style="border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                        <div>{{ number_format($wip->qty_aktual, 2, ',', '.') }} {{ $wip->satuan_cd }} (Input Curah)</div>
                                        @if($header->produksi)
                                            <div style="font-size: 0.725rem; color: #059669; margin-top: 2px;">
                                                SPK: {{ $header->produksi->produksi_no }} &bull; {{ str_contains(strtoupper($header->produksi->gudang?->gudang_nm ?? ''), 'GUDANG') ? $header->produksi->gudang->gudang_nm : 'Gudang Produksi (' . ($header->produksi->gudang?->gudang_cd ?? 'BMB') . ')' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: right; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem; color: #475569; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($wip->harga_satuan, 0, ',', '.') }} / {{ $wip->satuan_cd }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a; padding: 0.65rem 0.85rem; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($wip->subtotal_nilai, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            @foreach($bumbuItems as $bmb)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="text-align: center; color: #64748b; font-weight: 600; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.5rem;">{{ $rowNo++ }}</td>
                                    <td style="font-weight: 600; color: #0f172a; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                        <span>Bumbu Tabur: {{ $bmb->barang?->barang_nm }}</span>
                                        @if($bmb->batch_no)
                                            <span class="chip-batch-badge" style="font-size: 0.68rem; margin-left: 0.35rem; vertical-align: middle;">Lot: {{ $bmb->batch_no }}</span>
                                        @endif
                                    </td>
                                    <td style="border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                        Seasoning perenyah / tabur {{ number_format($bmb->qty_aktual, 2, ',', '.') }} {{ $bmb->satuan_cd }}
                                    </td>
                                    <td style="text-align: right; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem; color: #475569; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($bmb->harga_satuan, 0, ',', '.') }} / {{ $bmb->satuan_cd }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a; padding: 0.65rem 0.85rem; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($bmb->subtotal_nilai, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr style="background: #f8fafc; font-weight: 700; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                <td colspan="4" style="text-align: right; padding: 0.65rem 0.85rem; color: #ea580c; font-size: 0.8rem; letter-spacing: 0.02em; border-right: 1px solid #e2e8f0;">
                                    SUBTOTAL BAHAN CURAH (WIP):
                                </td>
                                <td style="text-align: right; padding: 0.65rem 0.85rem; color: #ea580c; font-weight: 800; font-variant-numeric: tabular-nums;">
                                    Rp {{ number_format($header->total_biaya_wip, 0, ',', '.') }}
                                </td>
                            </tr>

                            {{-- B. BIAYA KEMASAN & MATERIAL PACKING --}}
                            <tr style="background: #f8fafc; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                <td colspan="5" style="padding: 0.55rem 0.85rem; font-weight: 800; font-size: 0.8rem; color: #0f172a; letter-spacing: 0.03em;">
                                    B. BIAYA KEMASAN &amp; MATERIAL PACKING (PACKAGING MATERIALS)
                                </td>
                            </tr>
                            @foreach($kemasanItems as $kem)
                                @php
                                    $isLakban = str_contains(strtoupper($kem->barang?->barang_nm ?? ''), 'LAKBAN') || strtoupper($kem->kategori_bahan) === 'LAKBAN';
                                    $bCdK = strtoupper($kem->barang?->barang_cd ?? '');
                                    $bNmK = strtoupper($kem->barang?->barang_nm ?? '');
                                @endphp
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="text-align: center; color: #64748b; font-weight: 600; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.5rem;">{{ $rowNo++ }}</td>
                                    <td style="font-weight: 600; color: #0f172a; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                        <span>{{ $kem->barang?->barang_nm }}</span>
                                        @if($bCdK === 'KIF00G-BP1' || $bCdK === 'KIF00G-PUT' || (str_contains($bNmK, 'IFM') && str_contains($bNmK, 'KARTON')))
                                            <span style="font-size: 0.685rem; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 1.5px 6px; border-radius: 4px; margin-left: 4px;">
                                                Sirkulasi 5x
                                            </span>
                                        @elseif($bCdK === 'KMS-KTN-REJ' || str_contains($bNmK, 'REJECT') || str_contains($bNmK, 'MANUAL'))
                                            <span style="font-size: 0.685rem; font-weight: 700; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 1.5px 6px; border-radius: 4px; margin-left: 4px;">
                                                Manual (Rp 0)
                                            </span>
                                        @endif
                                        @if($kem->batch_no)
                                            <span class="chip-batch-badge" style="font-size: 0.68rem; margin-left: 0.35rem; vertical-align: middle;">Lot: {{ $kem->batch_no }}</span>
                                        @endif
                                    </td>
                                    <td style="border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                        {{ number_format($kem->qty_aktual, 2, ',', '.') }} {{ $kem->satuan_cd }}
                                        @if($isLakban && ($header->lakban_atas_gr > 0 || $header->lakban_bawah_gr > 0 || $header->lakban_jumlah_gr > 0))
                                            <div style="font-size: 0.725rem; color: #059669; font-weight: 700; margin-top: 2px;">
                                                Segel: Atas {{ number_format($header->lakban_atas_gr, 0) }}g &bull; Bawah {{ number_format($header->lakban_bawah_gr, 0) }}g &bull; Total {{ number_format($header->lakban_jumlah_gr, 0) }}g
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: right; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem; color: #475569; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($kem->harga_satuan, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a; padding: 0.65rem 0.85rem; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($kem->subtotal_nilai, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr style="background: #f8fafc; font-weight: 700; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                <td colspan="4" style="text-align: right; padding: 0.65rem 0.85rem; color: #0284c7; font-size: 0.8rem; letter-spacing: 0.02em; border-right: 1px solid #e2e8f0;">
                                    SUBTOTAL BIAYA KEMASAN &amp; DUS:
                                </td>
                                <td style="text-align: right; padding: 0.65rem 0.85rem; color: #0284c7; font-weight: 800; font-variant-numeric: tabular-nums;">
                                    Rp {{ number_format($header->total_biaya_kemasan, 0, ',', '.') }}
                                </td>
                            </tr>

                            {{-- C. BIAYA TENAGA KERJA LANGSUNG (BTKL) --}}
                            <tr style="background: #f8fafc; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                <td colspan="5" style="padding: 0.55rem 0.85rem; font-weight: 800; font-size: 0.8rem; color: #0f172a; letter-spacing: 0.03em;">
                                    C. UPAH TENAGA KERJA LANGSUNG (BTKL) PENGEMASAN
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="text-align: center; color: #64748b; font-weight: 600; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.5rem;">{{ $rowNo++ }}</td>
                                <td style="font-weight: 600; color: #0f172a; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                    Upah Tenaga Kerja Kemas (Regu Shift)
                                </td>
                                <td style="border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                    Alokasi tim meja packing sachet &amp; susun karton dus ({{ $header->jumlah_personil ?: 5 }} Org)
                                </td>
                                <td style="text-align: right; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem; color: #475569; font-variant-numeric: tabular-nums;">
                                    Borongan Shift
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; padding: 0.65rem 0.85rem; font-variant-numeric: tabular-nums;">
                                    Rp {{ number_format($header->total_biaya_tenaga_kerja, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr style="background: #f8fafc; font-weight: 700; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                <td colspan="4" style="text-align: right; padding: 0.65rem 0.85rem; color: #059669; font-size: 0.8rem; letter-spacing: 0.02em; border-right: 1px solid #e2e8f0;">
                                    SUBTOTAL UPAH BTKL KEMAS:
                                </td>
                                <td style="text-align: right; padding: 0.65rem 0.85rem; color: #059669; font-weight: 800; font-variant-numeric: tabular-nums;">
                                    Rp {{ number_format($header->total_biaya_tenaga_kerja, 0, ',', '.') }}
                                </td>
                            </tr>

                            {{-- D. BIAYA OVERHEAD PABRIK & LAIN-LAIN --}}
                            @if($header->total_biaya_lain > 0)
                                <tr style="background: #f8fafc; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                    <td colspan="5" style="padding: 0.55rem 0.85rem; font-weight: 800; font-size: 0.8rem; color: #0f172a; letter-spacing: 0.03em;">
                                        D. BIAYA OVERHEAD PABRIK &amp; LAIN-LAIN (OVERHEAD COSTS)
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="text-align: center; color: #64748b; font-weight: 600; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.5rem;">{{ $rowNo++ }}</td>
                                    <td style="font-weight: 600; color: #0f172a; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                        Biaya Utilitas &amp; BOP Kemas
                                    </td>
                                    <td style="border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                                        Alokasi listrik continuous sealer &amp; pemeliharaan mesin
                                    </td>
                                    <td style="text-align: right; border-right: 1px solid #e2e8f0; padding: 0.65rem 0.85rem; color: #475569;">
                                        -
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a; padding: 0.65rem 0.85rem; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($header->total_biaya_lain, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr style="background: #f8fafc; font-weight: 700; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                    <td colspan="4" style="text-align: right; padding: 0.65rem 0.85rem; color: #6d28d9; font-size: 0.8rem; letter-spacing: 0.02em; border-right: 1px solid #e2e8f0;">
                                        SUBTOTAL BIAYA OVERHEAD / LAIN-LAIN:
                                    </td>
                                    <td style="text-align: right; padding: 0.65rem 0.85rem; color: #6d28d9; font-weight: 800; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($header->total_biaya_lain, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot style="background: #f1f5f9; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                            <tr>
                                <td colspan="4" style="text-align: right; padding: 0.85rem 1rem; color: #0f172a; font-weight: 800; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.04em; border-right: 1px solid #cbd5e1;">
                                    TOTAL BIAYA PRODUKSI PENGEMASAN:
                                </td>
                                <td style="text-align: right; padding: 0.85rem 1rem; color: #0f172a; font-weight: 800; font-size: 1.05rem; font-variant-numeric: tabular-nums;">
                                    Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr style="background: #ecfdf5; color: #065f46; font-size: 0.95rem; font-weight: 800;">
                                <td colspan="3" style="padding: 0.85rem 1rem; border-right: 1px solid #cbd5e1;">
                                    HASIL KEMAS: <strong>{{ number_format($header->hasil_karton_qty, 0, ',', '.') }} KARTON</strong> ({{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs)
                                </td>
                                <td style="padding: 0.85rem 1rem; text-align: right; border-right: 1px solid #cbd5e1;">
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
        </div>

        {{-- ── TAB 3: RIWAYAT ADJUSTMENT & AUDIT TRAIL JURNAL AKUNTANSI ── --}}
        <div id="pane-audit" class="prod-tab-pane">
            <div class="prod-tab-pane-header">
                <div class="prod-tab-pane-title">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Adjustment &amp; Audit Trail Jurnal Akuntansi</span>
                    <span class="badge-version">
                        v{{ $header->version_no ?? 1 }}
                    </span>
                    <span style="font-size: 0.72rem; font-weight: 700; color: #065f46; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.15rem 0.5rem; border-radius: 4px; font-family: ui-monospace, monospace;">
                        Batch FG: {{ $header->batch_no ?: $header->pack_no }}
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    @if($header->status_cd === 'POSTED' && Auth::user()->canCreatePackaging())
                        <a href="{{ route('produksi.packaging.adjust', $header->pack_id) }}" style="font-size: 0.775rem; color: #0284c7; text-decoration: none; font-weight: 700;">
                            + Worksheet Adjustment &rarr;
                        </a>
                    @endif
                </div>
            </div>

            {{-- 1. RINCIAN JURNAL UMUM AKUNTANSI (FINANSIAL) --}}
            <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 1.5rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.15rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <strong style="font-size: 0.875rem; color: #0f172a;">Jurnal Umum Akuntansi (Posting Persediaan FG)</strong>
                    </div>
                    @if($header->jurnal_id && $header->jurnal)
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700;">POSTED</span>
                            <a href="{{ route('keuangan.jurnal.index', ['search' => $header->jurnal->jurnal_no]) }}" style="font-size: 0.75rem; color: #0284c7; font-weight: 700; text-decoration: none;">
                                #{{ $header->jurnal->jurnal_no }} &rarr;
                            </a>
                        </div>
                    @else
                        <span class="badge" style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 700;">BELUM POSTING (DRAFT)</span>
                    @endif
                </div>

                @if($header->jurnal_id && $header->jurnal && $header->jurnal->details->count() > 0)
                    <div style="overflow-x: auto;">
                        <table class="table-enterprise" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;">No</th>
                                    <th style="width: 130px;">Kode Akun</th>
                                    <th>Nama Rekening (COA)</th>
                                    <th style="width: 160px; text-align: right;">Debet (Rp)</th>
                                    <th style="width: 160px; text-align: right;">Kredit (Rp)</th>
                                    <th style="width: 260px;">Keterangan Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($header->jurnal->details as $jIdx => $jDtl)
                                    <tr>
                                        <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $jIdx + 1 }}</td>
                                        <td style="font-family: monospace; font-weight: 700; color: #0369a1;">
                                            {{ $jDtl->coa?->coa_cd ?? '-' }}
                                        </td>
                                        <td style="font-weight: 600; color: #0f172a;">
                                            {{ $jDtl->coa?->coa_nm ?? '-' }}
                                        </td>
                                        <td style="text-align: right; font-variant-numeric: tabular-nums; font-weight: {{ (float)$jDtl->debet > 0 ? '700' : 'normal' }}; color: {{ (float)$jDtl->debet > 0 ? '#059669' : '#64748b' }};">
                                            {{ (float)$jDtl->debet > 0 ? 'Rp ' . number_format($jDtl->debet, 0, ',', '.') : '-' }}
                                        </td>
                                        <td style="text-align: right; font-variant-numeric: tabular-nums; font-weight: {{ (float)$jDtl->kredit > 0 ? '700' : 'normal' }}; color: {{ (float)$jDtl->kredit > 0 ? '#0284c7' : '#64748b' }};">
                                            {{ (float)$jDtl->kredit > 0 ? 'Rp ' . number_format($jDtl->kredit, 0, ',', '.') : '-' }}
                                        </td>
                                        <td style="color: #64748b; font-size: 0.8rem;">
                                            {{ $jDtl->keterangan_txt ?: $header->jurnal->deskripsi_txt }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background: #f8fafc; font-weight: 700; border-top: 1.5px solid #cbd5e1;">
                                <tr>
                                    <td colspan="3" style="text-align: right; padding: 0.65rem 0.85rem; color: #475569; font-size: 0.8rem;">
                                        TOTAL KESEIMBANGAN JURNAL:
                                    </td>
                                    <td style="text-align: right; padding: 0.65rem 0.85rem; color: #059669; font-weight: 800; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($header->jurnal->details->sum('debet'), 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; padding: 0.65rem 0.85rem; color: #0284c7; font-weight: 800; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($header->jurnal->details->sum('kredit'), 0, ',', '.') }}
                                    </td>
                                    <td style="color: #059669; font-size: 0.75rem; font-weight: 700;">
                                        BALANCE (Seimbang)
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div style="padding: 1.25rem; color: #92400e; font-size: 0.825rem; display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong>Jurnal akuntansi belum diterbitkan.</strong>
                            <div style="color: #64748b; font-size: 0.775rem; margin-top: 2px;">
                                Jurnal debit persediaan barang jadi (FG) dan kredit persediaan WIP serta kemasan akan otomatis dibukukan saat tombol <strong>"Tarik &amp; Finalisasi HPP FG"</strong> dieksekusi.
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. TABEL RIWAYAT ADJUSTMENT HPP (PERSIS HPP WIP) --}}
            <div style="overflow-x: auto;">
                <table class="table-enterprise">
                    <thead>
                        <tr>
                            <th style="width: 140px;">No. Berita Acara</th>
                            <th style="width: 110px;">Tanggal</th>
                            <th style="width: 130px;">Komponen</th>
                            <th style="width: 160px; text-align: right;">Perubahan Biaya Total</th>
                            <th style="width: 160px; text-align: right;">Dampak HPP/Karton</th>
                            <th>Alasan &amp; Rincian Perubahan</th>
                            <th style="width: 120px;">Operator</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($header->visibleAdjustmentLogs() as $log)
                            @php
                                $isBiayaNaik = (float) $log->selisih_biaya > 0;
                                $isBiayaTurun = (float) $log->selisih_biaya < 0;
                                $isHppNaik = (float) $log->selisih_hpp_karton > 0;
                                $isHppTurun = (float) $log->selisih_hpp_karton < 0;
                            @endphp
                            <tr>
                                <td style="font-family: monospace; font-weight: 700; color: #0284c7;">
                                    {{ $log->adj_no }}
                                </td>
                                <td>
                                    {{ Carbon\Carbon::parse($log->adj_tgl)->format('d/m/Y') }}
                                    <div style="font-size: 0.7rem; color: #94a3b8;">{{ $log->created_at?->format('H:i') }} WIB</div>
                                </td>
                                <td>
                                    <span style="background: #f1f5f9; color: #334155; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">
                                        {{ $log->kategori_komponen ?: 'Semua Komponen' }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums;">
                                    <div style="font-size: 0.725rem; color: #64748b;">
                                        Rp {{ number_format($log->total_biaya_sebelum, 0, ',', '.') }} &rarr;
                                    </div>
                                    <div style="font-weight: 700; color: #0f172a;">
                                        Rp {{ number_format($log->total_biaya_sesudah, 0, ',', '.') }}
                                    </div>
                                    <div style="font-size: 0.7rem; font-weight: 700; color: {{ $isBiayaNaik ? '#dc2626' : ($isBiayaTurun ? '#16a34a' : '#64748b') }};">
                                        {{ $isBiayaNaik ? '+' : '' }}Rp {{ number_format($log->selisih_biaya, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums;">
                                    <div style="font-size: 0.725rem; color: #64748b;">
                                        Rp {{ number_format($log->hpp_karton_sebelum, 0, ',', '.') }} &rarr;
                                    </div>
                                    <div style="font-weight: 700; color: #0284c7;">
                                        Rp {{ number_format($log->hpp_karton_sesudah, 0, ',', '.') }}
                                    </div>
                                    <div style="font-size: 0.7rem; font-weight: 700; color: {{ $isHppNaik ? '#dc2626' : ($isHppTurun ? '#16a34a' : '#64748b') }};">
                                        {{ $isHppNaik ? '+' : '' }}Rp {{ number_format($log->selisih_hpp_karton, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #334155;">{{ $log->alasan_txt }}</div>
                                    @if(!empty($log->perubahan_field_json))
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.2rem;">
                                            Rincian: 
                                            @foreach($log->perubahan_field_json as $fKey => $fVal)
                                                <span style="background: #f8fafc; padding: 1px 4px; border: 1px solid #e2e8f0; border-radius: 3px; margin-right: 3px;">
                                                    {{ $fVal['label'] ?? $fKey }}: Rp {{ number_format($fVal['old'] ?? 0, 0, ',', '.') }} &rarr; Rp {{ number_format($fVal['new'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td style="color: #475569; font-weight: 600;">
                                    <div>{{ $log->created_by ?? 'System' }}</div>
                                    @if($log->role_cd)
                                        <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 500;">{{ str_replace('_', ' ', $log->role_cd) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8;">
                                    <div style="font-weight: 700; color: #64748b; font-size: 0.9rem;">Belum Ada Riwayat Penyesuaian</div>
                                    <div style="font-size: 0.775rem; margin-top: 0.25rem;">Tidak ada catatan penyesuaian yang sesuai dengan hak akses peran akun Anda.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 4: LEMBAR PENGESAHAN & TANDA TANGAN FORMAL                   --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="signatures-grid">
        <div class="sign-box">
            <span class="sign-role">Diserahkan Oleh,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">{{ $header->karyawan?->karyawan_nm ?: ($header->operator_nama ?: ($header->created_by ?: 'Operator Kemas')) }}</span>
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
            <span class="sign-role">Mengetahui &amp; Validasi HPP,</span>
            <div style="height: 48px;"></div>
            <div>
                <span class="sign-name">Finance &amp; Cost Accounting</span>
                <div class="sign-sub">Bagian Keuangan PT Mirasa</div>
            </div>
        </div>
    </div>


</div>

{{-- MODAL PEMBATALAN (VOID) --}}
@if($header->status_cd === 'POSTED')
    @include('produksi.packaging.partials.modal-void')
@endif

{{-- MODAL FINALISASI HPP & PERINGATAN HPP PRODUKSI --}}
@include('produksi.packaging.partials.modal-finalize-hpp')
@include('produksi.packaging.partials.modal-warning-prod-hpp')
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/packaging/packaging-show.js') }}?v={{ filemtime(public_path('js/produksi/packaging/packaging-show.js')) }}"></script>
@endpush
