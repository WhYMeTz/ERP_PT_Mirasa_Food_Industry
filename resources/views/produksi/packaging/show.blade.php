@extends('layouts.app')

@section('title', 'Detail Lembar Pengemasan - ' . $header->pack_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-show.css') }}">
@endpush

@section('content')
<div class="packaging-show-container">
    {{-- BARIS HEADER NAVIGASI & AKSI (IDENTIK DENGAN HPP PRODUKSI) --}}
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;" class="no-print">
        <div>
            <a href="{{ route('produksi.packaging.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Riwayat Pengemasan
            </a>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span>Bukti Hasil Pengemasan: {{ $header->pack_no }}</span>
                @if ($header->status_cd === 'POSTED')
                    <span style="font-size: 0.75rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.2rem 0.65rem; border-radius: 9999px; font-weight: 700;">
                        ✓ POSTED (Stok FG Tersimpan)
                    </span>
                @else
                    <span style="font-size: 0.75rem; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.2rem 0.65rem; border-radius: 9999px; font-weight: 700;">
                        ✕ VOID (Dibatalkan)
                    </span>
                @endif
            </h1>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="window.print()" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 0.85rem; background: #ffffff; border: 1.5px solid #cbd5e1; color: #0f172a; font-weight: 700;">
                🖨️ Cetak Lembar HPP Kemas
            </button>

            @if($header->status_cd === 'POSTED')
                <button type="button" onclick="openVoidShowModal()" class="btn btn-danger" style="font-size: 0.85rem; padding: 0.5rem 0.85rem; font-weight: 700;">
                    Batalkan (Void)
                </button>
            @endif
        </div>
    </div>

    {{-- NOTIFIKASI SUKSES / ERROR --}}
    @if(session('success'))
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- 4 KARTU KPI UTAMA (SERAGAM DENGAN HPP PRODUKSI) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        {{-- KPI 1: HPP PER KARTON --}}
        <div class="card" style="padding: 1.15rem; border-left: 4px solid #059669;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #059669;">
                HPP Riil per Karton
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: #059669; margin: 0.25rem 0;">
                Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #64748b;">
                Modal pokok persediaan FG di gudang
            </div>
        </div>

        {{-- KPI 2: HPP PER BUNGKUS / PCS --}}
        <div class="card" style="padding: 1.15rem; border-left: 4px solid #0284c7;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #0284c7;">
                HPP per Bungkus (Pcs)
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0.25rem 0;">
                Rp {{ number_format($header->hpp_per_pcs, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #64748b;">
                Harga modal satuan retail eceran
            </div>
        </div>

        {{-- KPI 3: TOTAL HASIL KARTON & PCS --}}
        <div class="card" style="padding: 1.15rem; border-left: 4px solid #10b981;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #059669;">
                Hasil Kemas Siap Jual
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0.25rem 0;">
                {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} <span style="font-size: 0.95rem; font-weight: 600; color: #64748b;">Karton</span>
            </div>
            <div style="font-size: 0.75rem; color: #059669; font-weight: 700;">
                📦 {{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs ({{ number_format($header->isi_per_karton_pcs, 0) }} Pcs/Ktn)
            </div>
        </div>

        {{-- KPI 4: TOTAL BIAYA PENGEMASAN --}}
        <div class="card" style="padding: 1.15rem; border-left: 4px solid #d97706;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #d97706;">
                Total Biaya Pengemasan
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0.25rem 0;">
                Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #64748b;">
                Akumulasi Curah WIP + Kemasan + Upah
            </div>
        </div>
    </div>

    @php
        $expDate = $header->expired_tgl
            ? Carbon\Carbon::parse($header->expired_tgl)->format('d/m/Y')
            : Carbon\Carbon::parse($header->pack_tgl)->addMonths(6)->format('d/m/Y');
    @endphp

    {{-- GRID SECTION: INFORMASI SPESIFIKASI BATCH, KARTON & PRATINJAU STIKER (PERSIS HPP PRODUKSI) --}}
    <div class="card" style="margin-bottom: 1.5rem; overflow: hidden;">
        <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                <span>📦 Spesifikasi Batch &amp; Kedaluwarsa Barang Jadi (Standar Persediaan Mirasa)</span>
            </div>
            <div>
                <span style="font-size: 0.75rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.2rem 0.65rem; border-radius: 9999px; font-weight: 700;">
                    🏷️ Barang Jadi Kemasan Retail
                </span>
            </div>
        </div>

        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; align-items: start;">
                {{-- KIRI: DATA SPESIFIKASI OPERASIONAL --}}
                <div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b; width: 45%;">Tanggal Pengemasan</td>
                            <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">
                                {{ Carbon\Carbon::parse($header->pack_tgl)->translatedFormat('l, d F Y') }}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b;">Target Produk Jadi (FG)</td>
                            <td style="padding: 0.6rem 0; font-weight: 800; color: #0284c7;">
                                {{ $header->barangJadi?->barang_nm ?? '-' }}
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">({{ $header->barangJadi?->barang_cd ?? '-' }})</span>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b;">Formula Standar (BOM)</td>
                            <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">
                                {{ $header->bom?->bom_nm ?? 'Tanpa BOM / Manual' }}
                                @if($header->bom)
                                    <span style="font-size: 0.725rem; color: #059669; font-weight: 600;">[{{ $header->bom->bom_no }}]</span>
                                @endif
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b;">Gudang Penyimpanan FG</td>
                            <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">
                                {{ $header->gudang?->gudang_nm ?? 'Gudang Hasil Produksi (FG)' }}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b;">No. Batch Persediaan FG</td>
                            <td style="padding: 0.6rem 0; font-weight: 800; color: #0369a1; font-family: monospace; font-size: 0.95rem;">
                                {{ $header->batch_no ?: '-' }}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b;">Masa Kedaluwarsa (Exp)</td>
                            <td style="padding: 0.6rem 0; font-weight: 800; color: #059669;">
                                {{ $expDate }}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.6rem 0; color: #64748b;">Alur Pengeluaran</td>
                            <td style="padding: 0.6rem 0; font-weight: 600; color: #475569;">
                                FIFO Persediaan &bull; Penjualan Toko / Distributor
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.6rem 0; color: #64748b;">Operator Pencatat</td>
                            <td style="padding: 0.6rem 0; font-weight: 700; color: #0f172a;">
                                {{ $header->created_by ?? 'Operator Packing' }}
                            </td>
                        </tr>
                    </table>

                    @if($header->catatan_txt)
                        <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8rem; color: #334155;">
                            <strong>Catatan Operasional:</strong> {{ $header->catatan_txt }}
                        </div>
                    @endif
                </div>

                {{-- KANAN: PRATINJAU STIKER FISIK STANDAR MIRASA --}}
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-align: center;">
                        CONTOH LABEL IDENTITAS KEMASAN DUS KARTON
                    </div>

                    <div class="sticker-preview-box">
                        {{-- Header: Produk & Halal Emblem --}}
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
    </div>

    {{-- TABEL OUTPUT BARANG JADI (FG OUTPUT) --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
            <span style="font-weight: 800; font-size: 0.9rem; color: #0f172a;">
                ⚖️ Rincian Hasil Barang Jadi (Finished Goods Output)
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table" style="margin: 0; font-size: 0.85rem; width: 100%; border-collapse: collapse;">
                <thead style="background: #f8fafc; color: #334155; font-weight: 700;">
                    <tr>
                        <th style="padding: 0.65rem 1rem; text-align: left;">Kategori Varian</th>
                        <th style="padding: 0.65rem 1rem; text-align: left;">Kode &amp; Nama Barang Jadi (FG)</th>
                        <th style="padding: 0.65rem 1rem; text-align: left;">No. Batch FG</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Kuantitas (Karton)</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Isi / Karton</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Total (Pcs)</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">HPP Satuan</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Total Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.65rem 1rem; font-weight: 700; color: #0f172a;">
                            Finish Good (FG)
                        </td>
                        <td style="padding: 0.65rem 1rem;">
                            <strong style="color: #0284c7;">{{ $header->barangJadi?->barang_cd ?? '-' }}</strong> - 
                            <span>{{ $header->barangJadi?->barang_nm ?? '-' }}</span>
                        </td>
                        <td style="padding: 0.65rem 1rem; font-family: monospace; font-weight: 600; color: #0369a1;">
                            {{ $header->batch_no }}
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: right; font-weight: 800; color: #059669;">
                            {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} Karton
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: right; color: #64748b;">
                            {{ number_format($header->isi_per_karton_pcs, 0) }} Pcs
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                            {{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: right; color: #475569;">
                            Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: right; font-weight: 800; color: #0f172a;">
                            Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                    <tr>
                        <td colspan="3" style="padding: 0.75rem 1rem; text-align: right;">TOTAL FG TERKEMAS:</td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #059669; font-size: 0.95rem;">
                            {{ number_format($header->hasil_karton_qty, 0, ',', '.') }} Karton
                        </td>
                        <td colspan="2" style="padding: 0.75rem 1rem; text-align: right; color: #64748b;">
                            ({{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs)
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #64748b;">HPP/KARTON:</td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #059669; font-size: 0.95rem;">
                            Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- TABEL RINCIAN BIAYA & PEMAKAIAN BAHAN KEMASAN (IDENTIK DENGAN HPP PRODUKSI) --}}
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
            <span style="font-weight: 800; font-size: 0.9rem; color: #0f172a;">
                📊 Rincian Komponen Biaya Pengemasan (Curah WIP, Bahan Kemasan, Tenaga Kerja &amp; Utilitas)
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table" style="margin: 0; font-size: 0.825rem; width: 100%; border-collapse: collapse;">
                <thead style="background: #f8fafc; color: #334155; font-weight: 700;">
                    <tr>
                        <th style="padding: 0.65rem 1rem; text-align: left;">Komponen Biaya</th>
                        <th style="padding: 0.65rem 1rem; text-align: left;">Rincian Bahan &amp; Nomor Lot</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Standar BOM</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Kuantitas Aktual</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Harga Satuan (Rp)</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Nominal Biaya (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. KERIPIK CURAH (WIP) --}}
                    @php
                        $wipItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) === 'WIP');
                        $kemasanItems = $header->details->filter(fn($d) => strtoupper($d->kategori_bahan) !== 'WIP');
                    @endphp

                    @foreach($wipItems as $wip)
                        <tr>
                            <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">
                                Keripik Curah (WIP)
                            </td>
                            <td style="padding: 0.6rem 1rem;">
                                <strong style="color: #0284c7;">{{ $wip->barang?->barang_nm }}</strong>
                                @if($wip->batch_no)
                                    <div style="font-size: 0.7rem; color: #64748b; font-family: monospace;">Lot: {{ $wip->batch_no }}</div>
                                @endif
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; color: #64748b;">
                                {{ number_format($wip->qty_standar, 2, ',', '.') }} {{ $wip->satuan_cd }}
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                                {{ number_format($wip->qty_aktual, 2, ',', '.') }} {{ $wip->satuan_cd }}
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; color: #475569;">
                                Rp {{ number_format($wip->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                                Rp {{ number_format($wip->subtotal_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach

                    <tr style="background: #fff7ed; border-top: 1px solid #fed7aa; border-bottom: 1px solid #fed7aa;">
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #c2410c;">Subtotal Biaya Curah (WIP)</td>
                        <td colspan="4" style="padding: 0.6rem 1rem; color: #c2410c; font-size: 0.75rem;">
                            Bahan dasar keripik matang siap kemas
                        </td>
                        <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 800; color: #c2410c;">
                            Rp {{ number_format($header->total_biaya_wip, 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- 2. MATERIAL KEMASAN --}}
                    @foreach($kemasanItems as $kem)
                        <tr>
                            <td style="padding: 0.6rem 1rem; font-weight: 600; color: #334155;">
                                Bahan Kemasan ({{ $kem->kategori_bahan }})
                            </td>
                            <td style="padding: 0.6rem 1rem;">
                                <strong>{{ $kem->barang?->barang_nm }}</strong>
                                @if($kem->batch_no)
                                    <span style="font-size: 0.7rem; color: #64748b; font-family: monospace;">({{ $kem->batch_no }})</span>
                                @endif
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; color: #64748b;">
                                {{ number_format($kem->qty_standar, 2, ',', '.') }} {{ $kem->satuan_cd }}
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                                {{ number_format($kem->qty_aktual, 2, ',', '.') }} {{ $kem->satuan_cd }}
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; color: #475569;">
                                Rp {{ number_format($kem->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700;">
                                Rp {{ number_format($kem->subtotal_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach

                    <tr style="background: #f0f9ff; border-top: 1px solid #bae6fd; border-bottom: 1px solid #bae6fd;">
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0284c7;">Subtotal Bahan Kemasan &amp; Dus</td>
                        <td colspan="4" style="padding: 0.6rem 1rem; color: #0284c7; font-size: 0.75rem;">
                            Akumulasi plastik roll/sachet, dus karton, lakban
                        </td>
                        <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 800; color: #0284c7;">
                            Rp {{ number_format($header->total_biaya_kemasan, 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- 3. TENAGA KERJA KEMAS --}}
                    <tr>
                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">Upah Tenaga Kerja Kemas</td>
                        <td colspan="4" style="padding: 0.6rem 1rem; color: #64748b;">
                            Upah borongan tim meja packing sachet &amp; susun karton
                        </td>
                        <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                            Rp {{ number_format($header->total_biaya_tenaga_kerja, 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- 4. BIAYA LAIN-LAIN / OVERHEAD --}}
                    @if($header->total_biaya_lain > 0)
                        <tr>
                            <td style="padding: 0.6rem 1rem; font-weight: 700; color: #0f172a;">Biaya Lain-Lain / Overhead</td>
                            <td colspan="4" style="padding: 0.6rem 1rem; color: #64748b;">
                                Alokasi listrik sealer, lakban tambahan &amp; utilitas pengemasan
                            </td>
                            <td style="padding: 0.6rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                                Rp {{ number_format($header->total_biaya_lain, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                    <tr style="font-size: 0.95rem;">
                        <td colspan="5" style="padding: 0.85rem 1rem; text-align: right; color: #0f172a;">
                            TOTAL BIAYA PRODUKSI PENGEMASAN:
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: right; color: #059669;">
                            Rp {{ number_format($header->total_biaya_produksi, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="background: #ecfdf5; color: #065f46; font-size: 0.95rem;">
                        <td colspan="3" style="padding: 0.85rem 1rem;">
                            HASIL KEMAS: <strong>{{ number_format($header->hasil_karton_qty, 0, ',', '.') }} KARTON</strong> ({{ number_format($header->hasil_pcs_qty, 0, ',', '.') }} Pcs)
                        </td>
                        <td colspan="2" style="padding: 0.85rem 1rem; text-align: right;">
                            HPP FINAL PER KARTON:
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 900; font-size: 1.1rem; color: #059669;">
                            Rp {{ number_format($header->hpp_per_karton, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- MODAL PEMBATALAN (VOID) --}}
@if($header->status_cd === 'POSTED')
<div id="modalVoidPackagingShow" class="modal-backdrop">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('produksi.packaging.void', $header->pack_id) }}">
            @csrf
            <div class="modal-danger-header">
                <span class="modal-danger-title">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Batalkan Dokumen Pengemasan {{ $header->pack_no }}
                </span>
                <button type="button" class="modal-close" onclick="closeVoidShowModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8rem; color: #9f1239; margin-bottom: 1rem;">
                    <strong>Perhatian:</strong>
                    Tindakan ini akan mengembalikan {{ number_format($header->hasil_karton_qty, 0) }} karton FG dari gudang, serta mengembalikan seluruh stok keripik curah dan kemasan yang terpakai.
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="inputAlasanVoidShow" style="font-size: 0.8rem; font-weight: 600; color: #475569;">Alasan Pembatalan <span style="color: #dc2626;">*</span></label>
                    <textarea id="inputAlasanVoidShow" name="alasan_batal" class="form-control" rows="3" required placeholder="Masukkan alasan pembatalan dokumen pengemasan ini..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeVoidShowModal()">Batal</button>
                <button type="submit" class="btn btn-danger" style="font-weight: 700;">
                    Ya, Batalkan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/packaging/packaging-show.js') }}"></script>
@endpush
