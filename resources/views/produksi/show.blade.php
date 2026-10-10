@extends('layouts.app')

@section('title', 'Detail Lembar Produksi - ' . $produksi->produksi_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-show.css') }}">
@endpush

@section('content')
<div class="show-container">
    {{-- BARIS HEADER NAVIGASI & AKSI --}}
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;" class="no-print">
        <div>
            <a href="{{ route('produksi.rekap', ['tahun' => Carbon\Carbon::parse($produksi->produksi_tgl)->year, 'bulan' => Carbon\Carbon::parse($produksi->produksi_tgl)->month]) }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Buku Rekap Bulanan
            </a>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span>Bukti Hasil Produksi: {{ $produksi->produksi_no }}</span>
                @if ($produksi->status_cd === 'POSTED')
                    <span style="font-size: 0.75rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.2rem 0.6rem; border-radius: 9999px; font-weight: 700;">
                        ✓ POSTED (Stok WIP Tersimpan)
                    </span>
                @else
                    <span style="font-size: 0.75rem; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 0.2rem 0.6rem; border-radius: 9999px; font-weight: 700;">
                        📝 DRAFT
                    </span>
                @endif
            </h1>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button type="button" onclick="window.print()" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 0.85rem;">
                🖨️ Cetak Lembar HPP
            </button>
            <a href="{{ route('produksi.cetak-stiker', $produksi->produksi_id) }}" target="_blank" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
                🏷️ Cetak Stiker Box
            </a>
        </div>
    </div>

    {{-- 3 KARTU KPI UTAMA --}}
    @php
        $rendemenColor = $produksi->rendemen_persen >= 33.0 ? '#059669' : ($produksi->rendemen_persen >= 30.0 ? '#d97706' : '#dc2626');
        $rendemenBg = $produksi->rendemen_persen >= 33.0 ? '#ecfdf5' : ($produksi->rendemen_persen >= 30.0 ? '#fffbeb' : '#fef2f2');
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        {{-- KPI 1: RENDEMEN --}}
        <div class="card" style="padding: 1.15rem; border-left: 4px solid {{ $rendemenColor }};">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: {{ $rendemenColor }};">
                Rendemen Singkong
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: {{ $rendemenColor }}; margin: 0.25rem 0;">
                {{ number_format($produksi->rendemen_persen, 2, ',', '.') }}%
            </div>
            <div style="font-size: 0.75rem; color: #64748b;">
                Standar Pabrik: &ge; 33.00%
            </div>
        </div>

        {{-- KPI 2: HPP PER KG --}}
        <div class="card" style="padding: 1.15rem; border-left: 4px solid #0284c7;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #0284c7;">
                HPP Riil per Kg
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0.25rem 0;">
                Rp {{ number_format($produksi->hpp_per_kg, 2, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #64748b;">
                Biaya pokok per kg produk jadi WIP
            </div>
        </div>

        {{-- KPI 3: TOTAL WIP & KARTON --}}
        <div class="card" style="padding: 1.15rem; border-left: 4px solid #10b981;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #059669;">
                Total WIP &amp; Karton
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0.25rem 0;">
                {{ number_format($produksi->total_wip_qty, 2, ',', '.') }} <span style="font-size: 0.95rem; font-weight: 600; color: #64748b;">kg</span>
            </div>
            <div style="font-size: 0.75rem; color: #059669; font-weight: 700;">
                @if ($produksi->qty_karton)
                    📦 {{ number_format($produksi->qty_karton, 0, ',', '.') }} Karton / Box Jadi
                @else
                    Output WIP terdaftar
                @endif
            </div>
        </div>
    </div>

    @php
        $isIfm = str_contains(strtoupper($produksi->lini_produksi), 'IFM') || str_contains(strtoupper($produksi->lini_produksi), 'IFL');
        $expDate = !empty($produksi->exp_date)
            ? Carbon\Carbon::parse($produksi->exp_date)->format('d/m/Y')
            : ($isIfm
                ? Carbon\Carbon::parse($produksi->produksi_tgl)->addMonths(6)->format('d/m/Y')
                : Carbon\Carbon::parse($produksi->produksi_tgl)->addYear()->subDay()->format('d/m/Y'));
    @endphp

    {{-- GRID SECTION: INFORMASI SPESIFIKASI SHIFT, KARTON & STIKER FISIK --}}
    <div class="card" style="margin-bottom: 1.5rem; overflow: hidden;">
        <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <span>📦 {{ $isIfm ? 'Spesifikasi Shift Kerja & Label Karton Fisik (WIP IFM)' : 'Spesifikasi Batch & Kedaluwarsa Barang Jadi (Standar Persediaan Mirasa)' }}</span>
            </div>
            <div>
                @if ($isIfm)
                    @if ($produksi->shift_cd === 'A')
                        <span class="badge-shift-a">Shift A</span>
                    @elseif ($produksi->shift_cd === 'B')
                        <span class="badge-shift-b">Shift B</span>
                    @endif
                @else
                    <span style="font-size: 0.75rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.2rem 0.65rem; border-radius: 9999px; font-weight: 700;">
                        🏷️ Barang Jadi Reguler
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; align-items: start;">
                {{-- KIRI: DATA SPESIFIKASI OPERASIONAL --}}
                <div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b; width: 45%;">Tanggal Produksi</td>
                            <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">
                                {{ Carbon\Carbon::parse($produksi->produksi_tgl)->translatedFormat('l, d F Y') }}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b;">Lini Produksi</td>
                            <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">{{ $produksi->lini_produksi }}</td>
                        </tr>
                        @if ($isIfm)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Shift Kerja</td>
                                <td style="padding: 0.6rem 0; font-weight: 800; color: #0f172a;">
                                    @if ($produksi->shift_cd === 'A')
                                        <span style="color: #0369a1;">Shift A (Pagi - Siang)</span>
                                    @elseif ($produksi->shift_cd === 'B')
                                        <span style="color: #7e22ce;">Shift B (Siang - Malam)</span>
                                    @else
                                        <span>Reguler</span>
                                    @endif
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Jam Packing / Cetak</td>
                                <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">{{ $produksi->jam_produksi ?: '-' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Varietas Singkong</td>
                                <td style="padding: 0.6rem 0; font-weight: 700; color: #0284c7;">{{ $produksi->varietas_singkong ?: '-' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Jumlah Karton Jadi</td>
                                <td style="padding: 0.6rem 0; font-weight: 800; color: #0f172a;">
                                    {{ $produksi->qty_karton ? number_format($produksi->qty_karton, 0, ',', '.') . ' Box' : '-' }}
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Rentang No. Seri Karton</td>
                                <td style="padding: 0.6rem 0; font-weight: 800; color: #0f172a; font-family: monospace;">
                                    @if ($produksi->no_karton_awal && $produksi->no_karton_akhir)
                                        No. {{ str_pad($produksi->no_karton_awal, 4, '0', STR_PAD_LEFT) }} s/d {{ str_pad($produksi->no_karton_akhir, 4, '0', STR_PAD_LEFT) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Batch WIP Resmi</td>
                                <td style="padding: 0.6rem 0; font-weight: 800; color: #0369a1; font-family: monospace; font-size: 0.95rem;">
                                    {{ $produksi->batch_wip_no ?: '-' }}
                                </td>
                            </tr>
                        @else
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">No. Batch Persediaan</td>
                                <td style="padding: 0.6rem 0; font-weight: 800; color: #0369a1; font-family: monospace; font-size: 1rem;">
                                    {{ $produksi->batch_wip_no ?: '-' }}
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Masa Kedaluwarsa (Exp)</td>
                                <td style="padding: 0.6rem 0; font-weight: 800; color: #059669;">
                                    {{ $expDate }} (1 Tahun - 1 Hari)
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Jam Produksi</td>
                                <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">{{ $produksi->jam_produksi ?: '-' }} WIB</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.6rem 0; color: #64748b;">Alur Pengeluaran</td>
                                <td style="padding: 0.6rem 0; font-weight: 600; color: #475569;">FIFO Persediaan &bull; Penjualan Toko / SO</td>
                            </tr>
                        @endif
                        <tr>
                            <td style="padding: 0.6rem 0; color: #64748b;">Gudang Penyimpanan</td>
                            <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">
                                {{ $produksi->gudang?->gudang_nm ?? 'Gudang Utama' }}
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- KANAN: PRATINJAU STIKER FISIK STANDAR MIRASA --}}
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-align: center;">
                        {{ $isIfm ? 'CONTOH LABEL STIKER FISIK BOX (KARTON IKATAN)' : 'CONTOH LABEL IDENTITAS KEMASAN RETAIL' }}
                    </div>

                    <div class="sticker-preview-box">
                        {{-- Header: WIP-FCC & Halal Emblem --}}
                        <div class="sticker-header">
                            <div class="sticker-title">
                                {{ $isIfm ? 'WIP-FCC' : strtoupper(str_replace('PRODUKSI ', '', $produksi->lini_produksi ?: 'FINISHED GOODS')) }}
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

                        {{-- Body: 2 Kolom Alami Tanpa Gap Canggung --}}
                        @php
                            $activeShift = $produksi->shift_cd ?: 'A';
                            $kartonNoPadded = str_pad((string) ($produksi->no_karton_awal ?: 1), 4, '0', STR_PAD_LEFT);
                            $batchNoDisplay = $isIfm ? ($activeShift . ' / ' . $kartonNoPadded) : $produksi->batch_wip_no;
                        @endphp
                        <div class="sticker-cols-container">
                            {{-- Kolom Kiri --}}
                            <div class="st-left-col">
                                <div class="st-row">
                                    <span class="st-lbl">No Batch :</span>
                                    <span class="st-val st-batch-num">{{ $batchNoDisplay }}</span>
                                </div>
                                <div class="st-row">
                                    <span class="st-lbl">Gross :</span>
                                    <span class="st-val">{{ $isIfm ? '7.08 kg' : 'STANDAR' }}</span>
                                </div>
                                <div class="st-row">
                                    <span class="st-lbl">Netto :</span>
                                    <span class="st-val">{{ $isIfm ? '6 kg' : 'BAL/RETAIL' }}</span>
                                </div>
                                <div class="st-row">
                                    <span class="st-lbl">Jam :</span>
                                    <span class="st-val">{{ $produksi->jam_produksi ? str_replace(':', '.', $produksi->jam_produksi) : '14.03' }}</span>
                                </div>
                            </div>

                            {{-- Kolom Kanan --}}
                            <div class="st-right-col">
                                <div class="st-row">
                                    <span class="st-lbl">Tgl. Produksi :</span>
                                    <span class="st-val">{{ strtoupper(Carbon\Carbon::parse($produksi->produksi_tgl)->format('d M Y')) }}</span>
                                </div>
                                <div class="st-row">
                                    <span class="st-lbl">Tgl. Kadaluarsa :</span>
                                    <span class="st-val">{{ strtoupper(Carbon\Carbon::parse($expDate)->format('d M Y')) }}</span>
                                </div>
                                <div class="st-row">
                                    <span class="st-lbl">Varietas RM :</span>
                                    <span class="st-val">{{ $isIfm ? ($produksi->varietas_singkong ?: 'STP / MGU') : 'STANDAR' }}</span>
                                </div>
                                <div class="st-row" style="justify-content: flex-end;">
                                    <div class="plant-code-box">{{ $isIfm ? 'M029 / - / ISA' : 'MIRASA / FG' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 0.75rem;" class="no-print">
                        <a href="{{ route('produksi.cetak-stiker', $produksi->produksi_id) }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">
                            🏷️ Cetak Label Ukuran Standar (Ctrl + P)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL OUTPUT BARANG JADI (WIP) --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
            <span style="font-weight: 800; font-size: 0.9rem; color: #0f172a;">
                ⚖️ Rincian Hasil Timbangan Barang Jadi (WIP Output)
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table" style="margin: 0; font-size: 0.85rem;">
                <thead style="background: #f8fafc; color: #334155; font-weight: 700;">
                    <tr>
                        <th style="padding: 0.65rem 1rem;">Kategori Varian</th>
                        <th style="padding: 0.65rem 1rem;">Kode &amp; Nama Barang WIP</th>
                        <th style="padding: 0.65rem 1rem;">Batch Varian</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Kuantitas (Kg)</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">HPP Satuan</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Total Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produksi->outputs as $out)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.65rem 1rem; font-weight: 700; color: #0f172a;">
                                {{ $out->kategori_output }}
                            </td>
                            <td style="padding: 0.65rem 1rem;">
                                <strong style="color: #0284c7;">{{ $out->barang?->barang_cd ?? '-' }}</strong> - 
                                <span>{{ $out->barang?->barang_nm ?? '-' }}</span>
                            </td>
                            <td style="padding: 0.65rem 1rem; font-family: monospace; font-weight: 600;">
                                {{ $out->batch_no }}
                            </td>
                            <td style="padding: 0.65rem 1rem; text-align: right; font-weight: 800; color: #059669;">
                                {{ number_format($out->qty_kg, 2, ',', '.') }} kg
                            </td>
                            <td style="padding: 0.65rem 1rem; text-align: right; color: #475569;">
                                Rp {{ number_format($out->hpp_satuan, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.65rem 1rem; text-align: right; font-weight: 800; color: #0f172a;">
                                Rp {{ number_format($out->total_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                Belum ada rincian output tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                    <tr>
                        <td colspan="3" style="padding: 0.75rem 1rem; text-align: right;">TOTAL WIP TERTIMBANG:</td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #059669; font-size: 0.95rem;">
                            {{ number_format($produksi->total_wip_qty, 2, ',', '.') }} kg
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #64748b;">HPP/KG:</td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #0369a1; font-size: 0.95rem;">
                            Rp {{ number_format($produksi->hpp_per_kg, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- TABEL RINCIAN BIAYA & PEMAKAIAN BAHAN --}}
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
            <span style="font-weight: 800; font-size: 0.9rem; color: #0f172a;">
                📊 Rincian Komponen Biaya Produksi (Bahan Baku, Energi, Tenaga Kerja &amp; FOH)
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table" style="margin: 0; font-size: 0.825rem;">
                <thead style="background: #f8fafc; color: #334155; font-weight: 700;">
                    <tr>
                        <th style="padding: 0.65rem 1rem;">Komponen Biaya</th>
                        <th style="padding: 0.65rem 1rem;">Rincian Fisik / Kuantitas</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Nominal Biaya (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. BAHAN BAKU --}}
                    <tr>
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">Singkong Mentah</td>
                        <td style="padding: 0.6rem 1rem;">{{ number_format($produksi->singkong_qty, 0, ',', '.') }} kg</td>
                        <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700;">Rp {{ number_format($produksi->singkong_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">Minyak Goreng Sawit</td>
                        <td style="padding: 0.6rem 1rem;">{{ number_format($produksi->minyak_sawit_qty, 1, ',', '.') }} kg</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">-</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">Minyak Goreng Kelapa (Barco)</td>
                        <td style="padding: 0.6rem 1rem;">{{ number_format($produksi->minyak_kelapa_qty, 1, ',', '.') }} kg</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">-</td>
                    </tr>
                    <tr style="background: #f8fafc;">
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0369a1;">Subtotal Minyak Goreng</td>
                        <td style="padding: 0.6rem 1rem; color: #0369a1; font-weight: 600;">Rasio terhadap Singkong: {{ number_format($produksi->minyak_rasio_persen, 2, ',', '.') }}%</td>
                        <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700; color: #0369a1;">Rp {{ number_format($produksi->minyak_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">Bumbu &amp; Perenyah</td>
                        <td style="padding: 0.6rem 1rem;">Garam, bumbu racik &amp; bahan perenyah</td>
                        <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700;">Rp {{ number_format($produksi->bumbu_nilai, 0, ',', '.') }}</td>
                    </tr>

                    {{-- 2. KEMASAN --}}
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Karton Baru (FL)</td>
                        <td style="padding: 0.6rem 1rem;">Kemasan karton baru</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->karton_baru_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Karton Bekas</td>
                        <td style="padding: 0.6rem 1rem;">Kemasan karton daur ulang/bekas</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->karton_bekas_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Plastik HD 90x100</td>
                        <td style="padding: 0.6rem 1rem;">Plastik inner pelindung karton</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->plastik_hd_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Lakban Besar</td>
                        <td style="padding: 0.6rem 1rem;">Isolasi perekat karton besar</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->lakban_besar_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Lakban Kecil</td>
                        <td style="padding: 0.6rem 1rem;">Isolasi perekat kemasan kecil</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->lakban_kecil_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Tali Rafia</td>
                        <td style="padding: 0.6rem 1rem;">Pengikat packing karton</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->tali_rafia_nilai, 0, ',', '.') }}</td>
                    </tr>

                    {{-- 3. ENERGI & TENAGA KERJA --}}
                    <tr>
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">Gas Alam (CNG)</td>
                        <td style="padding: 0.6rem 1rem;">{{ number_format($produksi->cng_mmbtu, 3, ',', '.') }} MMBTU (Tarif: Rp {{ number_format($produksi->cng_tarif, 0, ',', '.') }})</td>
                        <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700;">Rp {{ number_format($produksi->cng_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">Upah Tenaga Kerja</td>
                        <td style="padding: 0.6rem 1rem;">Tenaga Kerja Hadir: {{ $produksi->tk_jumlah_org ?? ($produksi->tk_langsung_org + $produksi->tk_tidak_langsung_org + $produksi->tk_training_org) }} Orang</td>
                        <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700;">Rp {{ number_format($produksi->tk_total_nilai, 0, ',', '.') }}</td>
                    </tr>

                    {{-- 4. OVERHEAD PABRIK (FOH) --}}
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Foto Copy / ATK</td>
                        <td style="padding: 0.6rem 1rem;">Dokumentasi &amp; label stiker karton</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->fotocopy_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Sarung Tangan Plastik</td>
                        <td style="padding: 0.6rem 1rem;">Perlengkapan sanitasi food-grade</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->sarung_tangan_plastik_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Sarung Tangan Kain</td>
                        <td style="padding: 0.6rem 1rem;">Perlengkapan proteksi operator</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->sarung_tangan_kain_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Pengawasan Mutu (QC)</td>
                        <td style="padding: 0.6rem 1rem;">Inspeksi standar mutu &amp; lab</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->qc_pengawasan_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Listrik &amp; Air - Telp</td>
                        <td style="padding: 0.6rem 1rem;">Utilitas operasional pabrik</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->listrik_air_telp_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Pemeliharaan Mesin</td>
                        <td style="padding: 0.6rem 1rem;">Perawatan preventif &amp; perbaikan mesin fryer/boiler</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->pemeliharaan_mesin_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Penyusutan Mesin &amp; Gedung</td>
                        <td style="padding: 0.6rem 1rem;">Amortisasi aset mesin manufaktur</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->penyusutan_mesin_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Pengolahan Limbah Padat</td>
                        <td style="padding: 0.6rem 1rem;">Penanganan limbah padat singkong / kulit</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->limbah_padat_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 1rem;">Bahan Kimia Limbah (IPAL)</td>
                        <td style="padding: 0.6rem 1rem;">Treatment kimia air limbah IPAL</td>
                        <td style="padding: 0.6rem 1rem; text-align: right;">Rp {{ number_format($produksi->limbah_kimia_nilai, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot style="background: #fef9c3; font-weight: 800; border-top: 2px solid #ca8a04;">
                    <tr>
                        <td colspan="2" style="padding: 0.85rem 1rem; font-size: 0.95rem; color: #854d0e;">TOTAL BIAYA PRODUKSI HARIAN:</td>
                        <td style="padding: 0.85rem 1rem; text-align: right; font-size: 1.1rem; color: #854d0e;">
                            Rp {{ number_format($produksi->total_biaya_produksi, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-show.js') }}"></script>
@endpush
