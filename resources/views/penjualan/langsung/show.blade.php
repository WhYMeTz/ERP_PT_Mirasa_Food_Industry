@extends('layouts.app')

@section('title', 'Detail Penjualan Langsung ' . $order->so_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/direct-sales-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penjualan/direct-sales-show.css') }}">
@endpush

@section('content')
<div class="ds-show-container">
    {{-- HEADER --}}
    <div class="ds-show-header">
        <div>
            <a href="{{ route('penjualan.langsung.index') }}" class="ds-back-btn" style="display: inline-flex; align-items: center; gap: 0.35rem; color: #64748b; font-size: 0.825rem; font-weight: 600; text-decoration: none; margin-bottom: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Penjualan Langsung
            </a>
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $order->so_no }}</h1>
                <span class="ds-badge selesai">Selesai</span>
                <span class="ds-badge selesai">Lunas</span>
                @if (strtoupper($order->top_txt) === 'TRANSFER')
                    <span class="ds-badge transfer">Transfer</span>
                @else
                    <span class="ds-badge tunai">Tunai</span>
                @endif
            </div>
            <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
                Transaksi penjualan langsung pabrik &bull; Tanggal {{ \Carbon\Carbon::parse($order->so_tgl)->format('d F Y') }}
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('penjualan.langsung.cetak-nota', $order->so_id) }}" target="_blank" class="btn btn-primary" style="background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; font-weight: 600; font-size: 0.875rem; border-radius: 6px; color: #fff; text-decoration: none;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Struk / Nota Kasir
            </a>

            @php
                $firstPengeluaran = $order->pengeluaran->first();
                $firstFaktur = $order->faktur->first();
            @endphp

            @if ($firstPengeluaran)
                <a href="{{ route('penjualan.pengeluaran.export-pdf', $firstPengeluaran->pengeluaran_id) }}" target="_blank" class="btn" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.85rem; font-weight: 600; font-size: 0.875rem; border-radius: 6px; text-decoration: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Surat Jalan (PDF)
                </a>
            @endif

            @if ($firstFaktur)
                <a href="{{ route('penjualan.faktur.export-pdf', $firstFaktur->faktur_id) }}" target="_blank" class="btn" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.85rem; font-weight: 600; font-size: 0.875rem; border-radius: 6px; text-decoration: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Faktur Resmi (PDF)
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; color: #065f46; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- 3 KARTU INFORMASI --}}
    <div class="ds-info-grid">
        {{-- KARTU 1: PELANGGAN --}}
        <div class="ds-info-card">
            <div class="ds-info-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Informasi Pelanggan
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">Akun Mitra:</span>
                <span class="ds-info-value">{{ $order->customer->customer_nm ?? 'Pelanggan Umum' }}</span>
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">Nama Pembeli:</span>
                <span class="ds-info-value">{{ $order->supir_nm ?? '-' }}</span>
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">No. Kendaraan:</span>
                <span class="ds-info-value">{{ $order->nopol_kendaraan ?? '-' }}</span>
            </div>
        </div>

        {{-- KARTU 2: PARAMETER DOKUMEN --}}
        <div class="ds-info-card">
            <div class="ds-info-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Waktu &amp; Petugas Kasir
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">Tanggal Transaksi:</span>
                <span class="ds-info-value">{{ \Carbon\Carbon::parse($order->so_tgl)->format('d/m/Y') }}</span>
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">Kasir / Pembuat:</span>
                <span class="ds-info-value">{{ $order->created_by ?? 'Kasir' }}</span>
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">Waktu Rekam:</span>
                <span class="ds-info-value">{{ \Carbon\Carbon::parse($order->created_dt)->format('d/m/Y H:i') }} WIB</span>
            </div>
        </div>

        {{-- KARTU 3: INTEGRASI ERP --}}
        <div class="ds-info-card">
            <div class="ds-info-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Integrasi Otomatis ERP
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">Surat Jalan (DO):</span>
                <span class="ds-info-value" style="color: #059669;">{{ $firstPengeluaran->pengeluaran_no ?? $order->surat_jalan_no ?? '-' }}</span>
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">Faktur Piutang:</span>
                <span class="ds-info-value" style="color: #7c3aed;">{{ $firstFaktur->faktur_no ?? $order->faktur_no ?? '-' }}</span>
            </div>
            <div class="ds-info-row">
                <span class="ds-info-label">Pemotongan Stok:</span>
                <span class="ds-info-value" style="color: #059669;">FIFO Terpotong (Gudang FG)</span>
            </div>
        </div>
    </div>

    {{-- TABEL RINCIAN BARANG --}}
    <div class="ds-table-card" style="margin-bottom: 1.25rem;">
        <div style="padding: 0.85rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; font-size: 0.875rem; color: #0f172a;">
            Rincian Item Produk Terjual
        </div>
        <div class="ds-table-wrapper">
            <table class="ds-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th>Kode &amp; Nama Produk</th>
                        <th style="text-align: center; width: 120px;">Qty Terjual</th>
                        <th style="text-align: right; width: 140px;">Harga Satuan</th>
                        <th style="text-align: center; width: 100px;">Diskon (%)</th>
                        <th style="text-align: right; width: 150px;">Subtotal Netto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->details as $idx => $item)
                        <tr>
                            <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">{{ $item->barang->barang_nm ?? "Barang #{$item->barang_id}" }}</div>
                                <div style="font-size: 0.72rem; color: #64748b;">Kode: {{ $item->barang->barang_cd ?? '-' }}</div>
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                {{ number_format($item->qty, 0) }} {{ $item->barang->satuanDasar?->satuan_nm ?? 'Karton' }}
                            </td>
                            <td style="text-align: right; font-feature-settings: 'tnum';">
                                Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                {{ $item->diskon_persen > 0 ? number_format($item->diskon_persen, 1) . '%' : '-' }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-feature-settings: 'tnum';">
                                Rp {{ number_format($item->subtotal_netto, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- KARTU REKAPITULASI KEUANGAN & PEMBAYARAN --}}
    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 1.25rem; align-items: start;">
        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="ds-card-title">Catatan &amp; Riwayat Pembayaran</h3>
            </div>
            <div class="ds-card-body" style="font-size: 0.85rem; color: #475569;">
                <div style="margin-bottom: 0.75rem;">
                    <strong>Catatan:</strong>
                    <div>{{ $order->catatan_txt ?: 'Tidak ada catatan tambahan.' }}</div>
                </div>
                @if ($firstFaktur && !empty($firstFaktur->catatan_txt))
                    <div style="padding: 0.75rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.8rem;">
                        <strong>Log Pelunasan:</strong> {{ $firstFaktur->catatan_txt }}
                    </div>
                @endif
            </div>
        </div>

        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="ds-card-title">Rincian Nilai Faktur</h3>
            </div>
            <div class="ds-card-body" style="padding: 1rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.825rem; color: #475569;">
                    <span>Subtotal Bruto</span>
                    <span style="font-weight: 600; color: #0f172a;">Rp {{ number_format($order->subtotal_bruto, 0, ',', '.') }}</span>
                </div>
                @if ($order->total_potongan > 0)
                    <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.825rem; color: #dc2626;">
                        <span>Potongan Diskon</span>
                        <span>- Rp {{ number_format($order->total_potongan, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if ($order->ppn_nominal > 0)
                    <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.825rem; color: #475569;">
                        <span>DPP</span>
                        <span>Rp {{ number_format($order->dpp_nominal, 0, ',', '.') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.825rem; color: #7c3aed;">
                        <span>PPN ({{ $order->ppn_persen }}%)</span>
                        <span style="font-weight: 600;">Rp {{ number_format($order->ppn_nominal, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; padding: 0.75rem 0 0.35rem; margin-top: 0.5rem; border-top: 2px dashed #cbd5e1; font-size: 1.1rem; font-weight: 800; color: #0f172a;">
                    <span>Total Tagihan</span>
                    <span style="color: #059669;">Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.825rem; color: #059669; font-weight: 700;">
                    <span>Status Pembayaran</span>
                    <span>LUNAS ({{ strtoupper($order->top_txt) }})</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
