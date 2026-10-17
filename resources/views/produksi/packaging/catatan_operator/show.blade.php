@extends('layouts.app')

@section('title', 'Rincian Lembar Catatan Pengemasan Operator - ' . $laporan->laporan_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/catatan-operator.css') }}">
@endpush

@section('content')
<div class="catatan-operator-container co-show-wrapper">
    
    {{-- Breadcrumb Navigation --}}
    <div class="co-breadcrumb">
        <a href="{{ url('/') }}">Dashboard</a>
        <span>&rsaquo;</span>
        <a href="{{ route('produksi.packaging.index') }}">Pengemasan (FG)</a>
        <span>&rsaquo;</span>
        <a href="{{ route('produksi.packaging.catatan-operator') }}">Riwayat Catatan Operator</a>
        <span>&rsaquo;</span>
        <span style="color: #0f172a;">{{ $laporan->laporan_no }}</span>
    </div>

    {{-- Main Header & Toolbar --}}
    <div class="co-show-header">
        <div class="co-show-title-group">
            <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Bukti Catatan Pengemasan Lapangan (Mobile First)
            </span>
            <h1>
                <span>Lembar Catatan:</span>
                <span class="co-code-badge">{{ $laporan->laporan_no }}</span>
                @php $badge = $laporan->status_badge; @endphp
                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 999px; font-size: 0.775rem; font-weight: 800; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1.5px solid {{ $badge['border'] }};">
                    <span>{{ $badge['icon'] }}</span>
                    <span>{{ $badge['label'] }}</span>
                </span>
            </h1>
            <div style="font-size: 0.8125rem; color: #64748b; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span>📅 <strong>{{ $laporan->tgl_kemas->translatedFormat('d F Y') }}</strong></span>
                <span>&bull;</span>
                <span class="co-chip co-chip-amber">Shift {{ $laporan->shift }}</span>
                <span>&bull;</span>
                <span>Leader: <strong style="color: #0f172a;">{{ $laporan->operator_nama }}</strong></span>
            </div>
        </div>

        <div class="co-show-actions">
            <a href="{{ route('produksi.packaging.catatan-operator') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.825rem; padding: 0.5rem 0.95rem; display: inline-flex; align-items: center; gap: 6px; border: 1.5px solid #cbd5e1;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>

            <button type="button" onclick="window.print()" class="btn btn-secondary" style="font-weight: 700; font-size: 0.825rem; padding: 0.5rem 0.95rem; display: inline-flex; align-items: center; gap: 6px; border: 1.5px solid #cbd5e1;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar</span>
            </button>

            @if ($laporan->status_cd === 'SUBMITTED' && Auth::user()->canCreateProduksi())
                <a href="{{ route('produksi.packaging.create', ['laporan_id' => $laporan->laporan_id]) }}" class="btn btn-primary" style="background: #059669; border: none; font-weight: 800; font-size: 0.85rem; padding: 0.55rem 1.25rem; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>📥 Proses Jadi HPP FG</span>
                </a>
            @elseif ($laporan->pack_id)
                <a href="{{ route('produksi.packaging.show', $laporan->pack_id) }}" class="btn" style="background: #0284c7; color: #ffffff; font-weight: 800; font-size: 0.85rem; padding: 0.55rem 1.25rem; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Buka Dokumen HPP ({{ $laporan->packagingHdr->pack_no ?? '' }})</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 4 Executive Metric Cards --}}
    <div class="co-stat-grid">
        {{-- Card 1: Output Kemas --}}
        <div class="co-stat-card" style="border-left: 4px solid #059669;">
            <div>
                <span class="co-stat-label">Hasil Kemas Barang Jadi</span>
                <div class="co-stat-val" style="color: #059669;">
                    {{ number_format($laporan->hasil_karton_qty, 0, ',', '.') }}
                    <span style="font-size: 0.9rem; font-weight: 700; color: #047857;">{{ $laporan->satuan_hasil }}</span>
                </div>
                <div class="co-stat-sub">
                    <strong>{{ $laporan->barangJadi->barang_nm ?? '-' }}</strong>
                    @if ($laporan->hasil_pack_qty > 0)
                        &bull; +{{ number_format($laporan->hasil_pack_qty, 0, ',', '.') }} Pack
                    @endif
                </div>
            </div>
            <div class="co-stat-icon" style="background: #ecfdf5; color: #059669; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
                📦
            </div>
        </div>

        {{-- Card 2: WIP Curah --}}
        <div class="co-stat-card" style="border-left: 4px solid #ea580c;">
            <div>
                <span class="co-stat-label">Curah Keripik (WIP)</span>
                <div class="co-stat-val" style="color: #ea580c;">
                    {{ number_format($laporan->wip_qty_kg, 2, ',', '.') }}
                    <span style="font-size: 0.9rem; font-weight: 700; color: #c2410c;">{{ $laporan->wip_satuan ?: 'Kg' }}</span>
                </div>
                <div class="co-stat-sub">
                    {{ $laporan->wipBarang?->barang_nm ?? 'Keripik Curah' }}
                    @if ($laporan->wip_batch_no)
                        &bull; Lot: <code>{{ $laporan->wip_batch_no }}</code>
                    @endif
                </div>
            </div>
            <div class="co-stat-icon" style="background: #fff7ed; color: #ea580c; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
                🍘
            </div>
        </div>

        {{-- Card 3: Timbangan Lakban --}}
        <div class="co-stat-card" style="border-left: 4px solid #dc2626;">
            <div>
                <span class="co-stat-label">Timbangan Lakban Dus</span>
                <div class="co-stat-val" style="color: #dc2626;">
                    {{ number_format($laporan->lakban_qty, 1, ',', '.') }}
                    <span style="font-size: 0.9rem; font-weight: 700; color: #b91c1c;">Gram</span>
                </div>
                <div class="co-stat-sub">
                    @if ($laporan->lakban_atas_gr > 0 || $laporan->lakban_bawah_gr > 0)
                        Atas: <strong>{{ number_format($laporan->lakban_atas_gr, 1, ',', '.') }}g</strong> &bull; Bawah: <strong>{{ number_format($laporan->lakban_bawah_gr, 1, ',', '.') }}g</strong>
                    @else
                        Timbangan Presisi Lapangan
                    @endif
                </div>
            </div>
            <div class="co-stat-icon" style="background: #fef2f2; color: #dc2626; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
                ⚖️
            </div>
        </div>

        {{-- Card 4: Shift & Tim Kerja --}}
        <div class="co-stat-card" style="border-left: 4px solid #0284c7;">
            <div>
                <span class="co-stat-label">Tim Kemas &amp; Shift</span>
                <div class="co-stat-val" style="color: #0284c7;">
                    Shift {{ $laporan->shift }}
                </div>
                <div class="co-stat-sub">
                    {{ $laporan->operator_nama }} &bull; <strong>{{ $laporan->jumlah_personil }} Kru</strong> &bull; <strong>{{ $laporan->jumlah_mesin }} Mesin</strong>
                </div>
            </div>
            <div class="co-stat-icon" style="background: #f0f9ff; color: #0284c7; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
                👥
            </div>
        </div>
    </div>

    {{-- Main 2-Column Responsive Layout --}}
    <div class="co-show-grid">
        
        {{-- KOLOM KIRI: RINCIAN MATERIAL & KONSUMSI LAPANGAN --}}
        <div>
            <div class="co-card">
                <div class="co-card-header">
                    <h3 class="co-card-title">
                        <svg width="18" height="18" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Daftar Konsumsi Bahan Baku &amp; Kemasan Riil</span>
                    </h3>
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                        Inputan Aktual Operator HP
                    </span>
                </div>

                <div class="co-card-body" style="padding: 0;">
                    <table class="co-table-detail">
                        <thead>
                            <tr>
                                <th style="width: 110px;">Kategori</th>
                                <th>Nama Barang &amp; Spesifikasi</th>
                                <th style="text-align: right; width: 140px;">Kuantitas Riil</th>
                                <th>Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 1. WIP Curah --}}
                            @if ($laporan->wip_qty_kg > 0 || $laporan->wipBarang)
                                <tr>
                                    <td>
                                        <span class="co-badge-kategori badge-wip">WIP CURAH</span>
                                    </td>
                                    <td>
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">
                                            {{ $laporan->wipBarang?->barang_nm ?? ($laporan->produksi ? 'Keripik Curah (WIP)' : 'WIP Curah') }}
                                        </strong>
                                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                            Kode: <code>{{ $laporan->wipBarang?->barang_cd ?? 'WIP' }}</code>
                                            @if ($laporan->wip_batch_no)
                                                &bull; Lot: <strong>{{ $laporan->wip_batch_no }}</strong>
                                            @endif
                                            @if ($laporan->produksi)
                                                &bull; Tiket: <strong>{{ $laporan->produksi->produksi_no }}</strong>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <strong style="color: #c2410c; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem;">
                                            {{ number_format($laporan->wip_qty_kg, 2, ',', '.') }}
                                        </strong>
                                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">{{ $laporan->wip_satuan ?: 'Kg' }}</span>
                                    </td>
                                    <td style="font-size: 0.75rem; color: #64748b;">
                                        Bahan Masak Curah Dapur
                                    </td>
                                </tr>
                            @endif

                            {{-- 2. Bumbu Tabur --}}
                            @if ($laporan->bumbuBarang && $laporan->bumbu_qty_kg > 0)
                                <tr>
                                    <td>
                                        <span class="co-badge-kategori badge-bumbu">BUMBU</span>
                                    </td>
                                    <td>
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">
                                            {{ $laporan->bumbuBarang->barang_nm }}
                                        </strong>
                                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                            Kode: <code>{{ $laporan->bumbuBarang->barang_cd }}</code>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <strong style="color: #b45309; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem;">
                                            {{ number_format($laporan->bumbu_qty_kg, 2, ',', '.') }}
                                        </strong>
                                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">{{ $laporan->bumbuBarang->satuanDasar?->satuan_nm ?? 'Kg' }}</span>
                                    </td>
                                    <td style="font-size: 0.75rem; color: #64748b;">
                                        Perasa / Seasoning Tabur
                                    </td>
                                </tr>
                            @endif

                            {{-- 3. Plastik Kemasan --}}
                            @if ($laporan->plastikBarang && $laporan->plastik_qty > 0)
                                <tr>
                                    <td>
                                        <span class="co-badge-kategori badge-kemasan">KEMASAN</span>
                                    </td>
                                    <td>
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">
                                            {{ $laporan->plastikBarang->barang_nm }}
                                        </strong>
                                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                            Kode: <code>{{ $laporan->plastikBarang->barang_cd }}</code>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <strong style="color: #0369a1; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem;">
                                            {{ number_format($laporan->plastik_qty, 0, ',', '.') }}
                                        </strong>
                                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">{{ $laporan->plastikBarang->satuanDasar?->satuan_nm ?? 'Pcs' }}</span>
                                    </td>
                                    <td style="font-size: 0.75rem;">
                                        @if ($laporan->plastik_rusak_pcs > 0)
                                            <span style="color: #dc2626; font-weight: 700;">⚠️ Afkir: {{ number_format($laporan->plastik_rusak_pcs, 0, ',', '.') }} pcs</span>
                                        @else
                                            <span style="color: #15803d;">✓ Normal tanpa reject</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            {{-- 4. Karton Dus --}}
                            @if ($laporan->kartonBarang && $laporan->karton_qty > 0)
                                <tr>
                                    <td>
                                        <span class="co-badge-kategori badge-kemasan">KARTON</span>
                                    </td>
                                    <td>
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">
                                            {{ $laporan->kartonBarang->barang_nm }}
                                        </strong>
                                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                            Kode: <code>{{ $laporan->kartonBarang->barang_cd }}</code>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <strong style="color: #0369a1; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem;">
                                            {{ number_format($laporan->karton_qty, 0, ',', '.') }}
                                        </strong>
                                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">{{ $laporan->kartonBarang->satuanDasar?->satuan_nm ?? 'Lbr' }}</span>
                                    </td>
                                    <td style="font-size: 0.75rem;">
                                        @if ($laporan->karton_rusak_lembar > 0)
                                            <span style="color: #dc2626; font-weight: 700;">⚠️ Afkir: {{ number_format($laporan->karton_rusak_lembar, 0, ',', '.') }} lbr</span>
                                        @else
                                            <span style="color: #15803d;">✓ Dus kondisi baik</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            {{-- 5. Kertas / Plastik Expired --}}
                            @if ($laporan->expired_qty > 0 || $laporan->expiredBarang)
                                <tr>
                                    <td>
                                        <span class="co-badge-kategori badge-kemasan">EXPIRED</span>
                                    </td>
                                    <td>
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">
                                            {{ $laporan->expiredBarang?->barang_nm ?? 'Label / Kertas Expired' }}
                                        </strong>
                                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                            Kode: <code>{{ $laporan->expiredBarang?->barang_cd ?? 'EXP' }}</code>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <strong style="color: #0f172a; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem;">
                                            {{ number_format($laporan->expired_qty, 0, ',', '.') }}
                                        </strong>
                                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">{{ $laporan->expiredBarang?->satuanDasar?->satuan_nm ?? 'Lbr' }}</span>
                                    </td>
                                    <td style="font-size: 0.75rem; color: #64748b;">
                                        Label Batch Kadaluarsa
                                    </td>
                                </tr>
                            @endif

                            {{-- 6. Lakban Timbangan --}}
                            @if ($laporan->lakban_qty > 0 || $laporan->lakban_atas_gr > 0 || $laporan->lakban_bawah_gr > 0)
                                <tr>
                                    <td>
                                        <span class="co-badge-kategori badge-lakban">LAKBAN</span>
                                    </td>
                                    <td>
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">
                                            {{ $laporan->lakbanBarang?->barang_nm ?? 'Lakban Dus (Selotip)' }}
                                        </strong>
                                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                            Atas: <strong>{{ number_format($laporan->lakban_atas_gr, 1, ',', '.') }}g</strong> &bull; Bawah: <strong>{{ number_format($laporan->lakban_bawah_gr, 1, ',', '.') }}g</strong>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <strong style="color: #dc2626; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem;">
                                            {{ number_format($laporan->lakban_qty, 1, ',', '.') }}
                                        </strong>
                                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Gram</span>
                                    </td>
                                    <td style="font-size: 0.75rem; color: #64748b;">
                                        Timbangan Riil Lakban Dus
                                    </td>
                                </tr>
                            @endif

                            {{-- 7. Multi Kemasan Tambahan (Dinamis Lebih Dari 1 Item) --}}
                            @php
                                $kemasanTambahan = $laporan->formatted_kemasan_tambahan;
                            @endphp

                            @if (!empty($kemasanTambahan) && count($kemasanTambahan) > 0)
                                @foreach ($kemasanTambahan as $idx => $extra)
                                    <tr>
                                        <td>
                                            <span class="co-badge-kategori badge-extra">TAMBAHAN #{{ $idx + 1 }}</span>
                                        </td>
                                        <td>
                                            <strong style="color: #0f172a; display: block; font-size: 0.85rem;">
                                                {{ $extra['barang_nm'] ?? 'Kemasan Tambahan' }}
                                            </strong>
                                            @if (!empty($extra['barang_cd']))
                                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                                    Kode: <code>{{ $extra['barang_cd'] }}</code>
                                                </div>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            <strong style="color: #7e22ce; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem;">
                                                {{ number_format($extra['qty'] ?? 0, 1, ',', '.') }}
                                            </strong>
                                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">{{ $extra['satuan_nm'] ?? 'Pcs' }}</span>
                                        </td>
                                        <td style="font-size: 0.75rem; color: #64748b;">
                                            {{ !empty($extra['catatan']) ? $extra['catatan'] : 'Kemasan Tambahan Lapangan' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: INFORMASI OPERASIONAL, AFKIR, CATATAN & INTEGRASI --}}
        <div>
            
            {{-- Alert Afkir Jika Ada Kerusakan --}}
            @if ($laporan->plastik_rusak_pcs > 0 || $laporan->karton_rusak_lembar > 0)
                <div class="co-afkir-box">
                    <span class="co-afkir-icon">⚠️</span>
                    <div class="co-afkir-content">
                        <h4>Perhatian: Tercatat Afkir / Kerusakan Bahan</h4>
                        <p>
                            @if ($laporan->plastik_rusak_pcs > 0)
                                &bull; Plastik Rusak: <strong>{{ number_format($laporan->plastik_rusak_pcs, 0, ',', '.') }} Pcs</strong><br>
                            @endif
                            @if ($laporan->karton_rusak_lembar > 0)
                                &bull; Karton Dus Rusak: <strong>{{ number_format($laporan->karton_rusak_lembar, 0, ',', '.') }} Lembar</strong>
                            @endif
                        </p>
                    </div>
                </div>
            @endif

            {{-- Catatan Operator --}}
            @if ($laporan->catatan)
                <div class="co-quote-box">
                    <span class="co-quote-title">Catatan Kendala dari Operator HP:</span>
                    <p class="co-quote-text">
                        "{{ $laporan->catatan }}"
                    </p>
                </div>
            @endif

            {{-- Card Integrasi HPP FG --}}
            <div class="co-card">
                <div class="co-card-header">
                    <h3 class="co-card-title">
                        <svg width="18" height="18" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span>Status Integrasi HPP FG (Office)</span>
                    </h3>
                </div>

                <div class="co-card-body">
                    @if ($laporan->status_cd === 'PROCESSED' && $laporan->packagingHdr)
                        <div class="co-integration-box processed">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="font-size: 1.15rem;">✅</span>
                                <strong style="font-size: 0.875rem;">Laporan Ini Sudah Diproses ke HPP FG</strong>
                            </div>
                            <div style="font-size: 0.8rem; line-height: 1.45;">
                                Dokumen HPP FG: <strong style="font-family: ui-monospace, monospace;">{{ $laporan->packagingHdr->pack_no }}</strong><br>
                                Tanggal Posting: <strong>{{ $laporan->packagingHdr->pack_tgl?->format('d/m/Y') }}</strong><br>
                                Hasil: <strong>{{ number_format($laporan->packagingHdr->hasil_karton_qty, 0, ',', '.') }} Karton</strong> (HPP: Rp {{ number_format($laporan->packagingHdr->hpp_per_karton, 0, ',', '.') }}/Karton)
                            </div>
                            <div style="margin-top: 0.75rem;">
                                <a href="{{ route('produksi.packaging.show', $laporan->pack_id) }}" class="btn btn-sm" style="background: #15803d; color: #ffffff; font-weight: 700; font-size: 0.775rem; text-decoration: none; padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>Lihat Rincian HPP FG &rarr;</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="co-integration-box unprocessed">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="font-size: 1.15rem;">📩</span>
                                <strong style="font-size: 0.875rem;">Menunggu Penarikan oleh Office</strong>
                            </div>
                            <p style="font-size: 0.8rem; margin: 0 0 0.75rem 0; line-height: 1.45;">
                                Laporan ini siap dikonversikan menjadi kalkulasi HPP Finished Goods (FG) resmi pabrik dengan 1 klik.
                            </p>
                            @if (Auth::user()->canCreateProduksi())
                                <a href="{{ route('produksi.packaging.create', ['laporan_id' => $laporan->laporan_id]) }}" class="btn btn-sm" style="background: #0284c7; color: #ffffff; font-weight: 800; font-size: 0.8rem; text-decoration: none; padding: 0.45rem 0.95rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);">
                                    <span>📥 Tarik &amp; Bentuk HPP FG Sekarang</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card Info Kru & Audit Trail --}}
            <div class="co-card">
                <div class="co-card-header">
                    <h3 class="co-card-title">
                        <svg width="18" height="18" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Informasi Kru &amp; Audit Trail</span>
                    </h3>
                </div>

                <div class="co-card-body" style="font-size: 0.8125rem;">
                    <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                        <span style="color: #64748b;">Leader Lapangan:</span>
                        <strong style="color: #0f172a;">{{ $laporan->operator_nama }}</strong>
                    </div>

                    @if ($laporan->karyawan)
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                            <span style="color: #64748b;">Karyawan Terkait:</span>
                            <strong style="color: #0f172a;">{{ $laporan->karyawan->karyawan_nm }} ({{ $laporan->karyawan->nik ?? '-' }})</strong>
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <span style="color: #64748b;">Jumlah Personil Kru:</span>
                        <strong style="color: #0f172a;">{{ $laporan->jumlah_personil }} Orang</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <span style="color: #64748b;">Jumlah Mesin Digunakan:</span>
                        <strong style="color: #0f172a;">{{ $laporan->jumlah_mesin }} Unit Mesin</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <span style="color: #64748b;">Waktu Pengiriman:</span>
                        <strong style="color: #0f172a;">{{ $laporan->created_at->translatedFormat('d F Y, H:i') }} WIB</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding-top: 0.5rem;">
                        <span style="color: #64748b;">Akun Pengirim:</span>
                        <strong style="color: #0f172a;">{{ $laporan->created_by ?: 'Operator HP' }}</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
