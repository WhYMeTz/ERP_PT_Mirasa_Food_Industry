@extends('layouts.app')

@section('title', 'Detail PO Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/so/so-show.css') }}">
@endpush

@section('content')

{{-- TOP COMMAND HEADER --}}
<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('penjualan.so.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar PO Penjualan
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $order->so_no }}</h1>
            @if ($order->status_cd == 'COMPLETED')
                <span class="badge badge-success">Selesai (100%)</span>
            @elseif ($order->status_cd == 'PARTIAL')
                <span class="badge badge-info">Sebagian Kirim</span>
            @elseif ($order->status_cd == 'PROCESSING')
                <span class="badge" style="background:#e0f2fe; color:#0369a1;">Diproses</span>
            @elseif ($order->status_cd == 'APPROVED')
                <span class="badge" style="background:#dbeafe; color:#1d4ed8;">Disetujui</span>
            @elseif ($order->status_cd == 'DRAFT')
                <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
            @else
                <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
            @endif
        </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        @if($order->isDeliverable())
            {{-- Tombol Utama: Buat Surat Jalan di Modul Pengeluaran Barang --}}
            <a href="{{ route('penjualan.pengeluaran.create', ['so_id' => $order->so_id]) }}" 
               class="btn btn-primary" 
               style="background: #059669; border-color: #059669; color: #ffffff; padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 4px rgba(5,150,105,0.2);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>+ Buat Surat Jalan (Kirim Barang)</span>
            </a>
        @elseif($order->isCompleted())
            <span class="delivery-badge-success" style="padding: 0.45rem 0.85rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Stok FG Terpotong Resmi (FEFO)</span>
            </span>

            {{-- Tombol Catat Retur Penjualan --}}
            <button type="button" 
                    onclick="openReturModal()" 
                    class="btn btn-secondary" 
                    style="padding: 0.45rem 0.85rem; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" 
                    title="Catat barang rusak/cacat yang dikembalikan oleh pelanggan">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4 4m-4-4l4-4m12 11a4 4 0 004-4v-1a4 4 0 00-4-4h-4"/></svg>
                <span>+ Catat Retur Barang</span>
            </button>

            <button type="button" 
                    onclick="openVoidDeliveryModal({{ $order->so_id }}, '{{ $order->so_no }}')" 
                    class="btn btn-secondary" 
                    style="border-color: #fca5a5; color: #dc2626; background: #fff5f5; padding: 0.45rem 0.85rem; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;" 
                    title="Batalkan pengiriman dan kembalikan stok fisik ke Gudang FG">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                <span>Batalkan Pengiriman</span>
            </button>
        @endif

        @if($order->sisa_tagihan > 0 && !$order->isCancelled())
            {{-- Tombol Cepat: Catat Pembayaran / Kas Masuk (BKM) --}}
            <button type="button" 
                    onclick="openRecordPaymentModal()" 
                    class="btn btn-primary" 
                    style="background: #0284c7; border-color: #0284c7; color: #ffffff; padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 4px rgba(2,132,199,0.2);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>+ Catat Pembayaran (BKM)</span>
            </button>
        @elseif($order->total_tagihan > 0)
            <span style="background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; border-radius: 6px; padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Tagihan Lunas (100%)</span>
            </span>
        @endif

        @php
            $custNmUpper = strtoupper($order->customer?->customer_nm ?? '');
            $custCdUpper = strtoupper($order->customer?->customer_cd ?? '');
            $isOrderIfm = str_contains($custNmUpper, 'INDOFOOD') || str_contains($custNmUpper, 'IFM') || str_contains($custNmUpper, 'FORTUNA') || str_contains($custCdUpper, 'IFM') || str_contains($custCdUpper, 'INDOFOOD');
            $isOrderPpn = $order->ppn_nominal > 0 || $order->ppn_tipe === 'PPN_11';
            $sjTitle = $isOrderIfm ? 'IFM' : ($isOrderPpn ? 'PPN' : 'Non-PPN');
        @endphp

        {{-- Cetak Faktur PDF Utama --}}
        <a href="{{ route('penjualan.so.export-faktur', $order->so_id) }}" target="_blank" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Cetak Faktur Penjualan Utama (Format {{ $isOrderPpn ? 'Ber-PPN' : 'Non-PPN' }})">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Faktur ({{ $isOrderPpn ? 'PPN' : 'Non-PPN' }})</span>
        </a>

        {{-- Cetak Surat Jalan PDF Utama --}}
        <a href="{{ route('penjualan.so.export-surat-jalan', $order->so_id) }}" target="_blank" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Cetak Surat Jalan Utama (Format {{ $sjTitle }})">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            <span>Surat Jalan ({{ $sjTitle }})</span>
        </a>

        {{-- Dropdown Pilihan Format PDF Lengkap (IFM, Non-PPN & Ber-PPN) --}}
        <div style="position: relative; display: inline-block;">
            <button type="button" 
                    class="btn btn-secondary" 
                    onclick="togglePrintMenu(event)" 
                    style="padding: 0.45rem 0.65rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.25rem;" 
                    title="Pilihan Format Cetak (IFM / Non-PPN / Ber-PPN)">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div id="printMenuDropdown" style="display: none; position: absolute; right: 0; top: calc(100% + 5px); background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); min-width: 275px; z-index: 50; overflow: hidden; text-align: left;">
                {{-- Format Khusus IFM Indofood --}}
                <div style="padding: 0.45rem 0.75rem; background: #ecfdf5; border-bottom: 1px solid #a7f3d0; font-size: 0.7rem; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 0.05em;">
                    Format Khusus IFM (PT. Indofood Fortuna)
                </div>
                <a href="{{ route('penjualan.so.export-surat-jalan', [$order->so_id, 'tipe' => 'ifm']) }}" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.85rem; font-size: 0.8125rem; color: #1e293b; text-decoration: none; border-bottom: 1px solid #e2e8f0;" onmouseover="this.style.background='#f0fdf4'; this.style.color='#047857';" onmouseout="this.style.background='transparent'; this.style.color='#1e293b';">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <div>
                        <div style="font-weight: 600;">Surat Jalan (Khusus IFM)</div>
                        <div style="font-size: 0.7rem; color: #64748b;">Format KARTON &bull; KG &bull; Tgl Prod &bull; Varietas</div>
                    </div>
                </a>

                {{-- Format Non-PPN --}}
                <div style="padding: 0.45rem 0.75rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
                    Format Non-PPN (MFI/OMS/7.0)
                </div>
                <a href="{{ route('penjualan.so.export-faktur', [$order->so_id, 'tipe' => 'non_ppn']) }}" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.85rem; font-size: 0.8125rem; color: #1e293b; text-decoration: none; border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f0fdf4'; this.style.color='#059669';" onmouseout="this.style.background='transparent'; this.style.color='#1e293b';">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <div>
                        <div style="font-weight: 600;">Faktur Penjualan (Non-PPN)</div>
                        <div style="font-size: 0.7rem; color: #64748b;">Tanpa PPN &bull; Rek. an. Habibah S.P</div>
                    </div>
                </a>
                <a href="{{ route('penjualan.so.export-surat-jalan', [$order->so_id, 'tipe' => 'non_ppn']) }}" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.85rem; font-size: 0.8125rem; color: #1e293b; text-decoration: none; border-bottom: 1px solid #e2e8f0;" onmouseover="this.style.background='#f0fdf4'; this.style.color='#059669';" onmouseout="this.style.background='transparent'; this.style.color='#1e293b';">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <div>
                        <div style="font-weight: 600;">Surat Jalan (Non-PPN)</div>
                        <div style="font-size: 0.7rem; color: #64748b;">Format PACK &bull; 3 Tanda Tangan</div>
                    </div>
                </a>

                {{-- Format Ber-PPN --}}
                <div style="padding: 0.45rem 0.75rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
                    Format Ber-PPN (MFI/BMR/1.0)
                </div>
                <a href="{{ route('penjualan.so.export-faktur', [$order->so_id, 'tipe' => 'ppn']) }}" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.85rem; font-size: 0.8125rem; color: #1e293b; text-decoration: none; border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#eff6ff'; this.style.color='#0284c7';" onmouseout="this.style.background='transparent'; this.style.color='#1e293b';">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <div>
                        <div style="font-weight: 600;">Faktur Penjualan (Ber-PPN)</div>
                        <div style="font-size: 0.7rem; color: #64748b;">PPN 11% &bull; DPP &bull; Rek. PT Mirasa</div>
                    </div>
                </a>
                <a href="{{ route('penjualan.so.export-surat-jalan', [$order->so_id, 'tipe' => 'ppn']) }}" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.85rem; font-size: 0.8125rem; color: #1e293b; text-decoration: none;" onmouseover="this.style.background='#eff6ff'; this.style.color='#0284c7';" onmouseout="this.style.background='transparent'; this.style.color='#1e293b';">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <div>
                        <div style="font-weight: 600;">Surat Jalan (Ber-PPN)</div>
                        <div style="font-size: 0.7rem; color: #64748b;">Format Bungkus/Dus &bull; Kendali Mutu</div>
                    </div>
                </a>
            </div>
        </div>

        @if(!in_array($order->status_cd, ['COMPLETED', 'CANCELLED']))
            <a href="{{ route('penjualan.so.edit', $order->so_id) }}" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit PO</span>
            </a>

            <button type="button" class="btn btn-danger" onclick="openCancelSoModal({{ $order->so_id }}, '{{ $order->so_no }}')" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Batalkan</span>
            </button>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL PO PENJUALAN --}}
@php
    $totalItem = $order->details->count();
    $totalQty = (float) $order->details->sum('pesan_qty');
    $statusCd = $order->status_cd;
    $pct = match($statusCd) {
        'COMPLETED' => 100,
        'PARTIAL'   => 50,
        'PROCESSING'=> 25,
        'APPROVED'  => 10,
        default     => 0
    };
@endphp
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- KARTU 1: TOTAL NILAI TAGIHAN FAKTUR --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Total Tagihan Faktur
        </span>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem; font-family: monospace;">
            Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}
        </div>
        @if($order->total_retur_nominal > 0)
            <div style="font-size: 0.725rem; color: #ea580c; font-weight: 600; margin-top: 0.2rem;">
                Retur (Potong): - Rp {{ number_format((float) $order->total_retur_nominal, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #0284c7; font-weight: 700;">
                Net: Rp {{ number_format((float) $order->net_tagihan, 0, ',', '.') }}
            </div>
        @else
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
                {{ $totalItem }} varian &bull; {{ number_format($totalQty, 0) }} total kuantitas
            </div>
        @endif
    </div>

    {{-- KARTU 2: SUDAH TERBAYAR KAS & BANK --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Terbayar (Kas/Bank)
            </span>
            <span style="font-size: 0.8rem; font-weight: 700; color: #059669;">
                {{ $order->persen_bayar }}%
            </span>
        </div>
        <div style="font-size: 1.35rem; font-weight: 700; color: #059669; margin-top: 0.25rem; font-family: monospace;">
            Rp {{ number_format((float) $order->total_bayar, 0, ',', '.') }}
        </div>
        {{-- Progress Bar Pembayaran --}}
        <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-top: 0.45rem;">
            <div style="width: {{ $order->persen_bayar }}%; height: 100%; background: #10b981;"></div>
        </div>
    </div>

    {{-- KARTU 3: SISA PIUTANG (AR) --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Sisa Piutang (AR)
            </span>
            @if ($order->status_bayar === 'LUNAS')
                <span class="badge badge-success" style="font-size: 0.7rem;">Lunas</span>
            @elseif ($order->status_bayar === 'SEBAGIAN')
                <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.7rem;">Sebagian</span>
            @else
                <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.7rem;">Belum Bayar</span>
            @endif
        </div>
        <div style="font-size: 1.35rem; font-weight: 700; color: {{ $order->sisa_tagihan > 0 ? '#dc2626' : '#15803d' }}; margin-top: 0.25rem; font-family: monospace;">
            Rp {{ number_format((float) $order->sisa_tagihan, 0, ',', '.') }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            {{ $order->payments->count() }} transaksi pembayaran masuk
        </div>
    </div>

    {{-- KARTU 4: STATUS REALISASI PENGIRIMAN --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Realisasi Kirim
            </span>
            <span style="font-size: 0.8rem; font-weight: 700; color: {{ $pct >= 100 ? '#059669' : ($pct > 0 ? '#d97706' : '#64748b') }};">
                {{ $pct }}%
            </span>
        </div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            {{ $statusCd }}
        </div>
        <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-top: 0.45rem;">
            <div style="width: {{ $pct }}%; height: 100%; background: {{ $pct >= 100 ? '#10b981' : ($pct > 0 ? '#f59e0b' : '#0284c7') }};"></div>
        </div>
    </div>
</div>

{{-- MASTER-DETAIL WORKSPACE: 2 KOLOM (KIRI: RINCIAN & KEUANGAN, KANAN: SIDEBAR INFO MITRA & REFERENSI) --}}
<div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 1.5rem; align-items: start; margin-bottom: 2rem;">
    {{-- KOLOM KIRI (KONTEN UTAMA) --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        {{-- KARTU 1: RINCIAN ITEM PRODUK YANG DIPESAN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">Rincian Barang Hasil Produksi yang Dipesan</strong>
                    <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;">({{ $totalItem }} Item)</span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <th style="width: 35px; text-align: center;">No</th>
                            <th style="width: 110px; text-align: left;">Kategori</th>
                            <th style="min-width: 190px; text-align: left;">Nama Produk / Barang Jadi</th>
                            <th style="width: 100px; text-align: left;">Kode Barang</th>
                            <th style="width: 90px; text-align: right;">Jumlah</th>
                            <th style="width: 70px; text-align: left;">Satuan</th>
                            <th style="width: 110px; text-align: right;">Harga Satuan</th>
                            <th style="width: 70px; text-align: right;">Diskon (%)</th>
                            <th style="width: 90px; text-align: right;">Potongan</th>
                            <th style="width: 95px; text-align: center;">PPN</th>
                            <th style="width: 125px; text-align: right;">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->details as $index => $item)
                            @php
                                $diskonPct = (float) ($item->diskon_persen ?? 0);
                                $potNom = (float) ($item->potongan_nominal ?? 0);
                                $isPpn = ($item->ppn_tipe ?? '') === 'PPN_11';
                                $subtotalRow = (float) ($item->subtotal_tagihan ?: $item->subtotal_nominal);
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="text-align: center; color: #64748b; font-size: 0.85rem;">{{ $index + 1 }}</td>
                                <td style="color: #475569; font-size: 0.85rem;">
                                    <span class="badge" style="background:#f1f5f9; color:#334155; font-size:0.7rem;">
                                        {{ $item->barang?->jenisBarang?->jenis_barang_nm ?? ($item->barang?->jenis_barang_cd ?? 'FG') }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: #0f172a; font-size: 0.875rem;">{{ $item->barang?->barang_nm }}</strong>
                                    @php
                                        $stokFg = (float) ($stockMap[$item->barang_id] ?? 0);
                                    @endphp
                                    <div style="font-size: 0.725rem; margin-top: 2px;">
                                        @if($order->isCompleted())
                                            <span style="color: #15803d; font-weight: 600;">✓ Terkirim: {{ number_format((float)$item->kirim_qty, 0) }} karton (Stok FEFO Terpotong)</span>
                                        @else
                                            <span style="color: {{ $stokFg >= $item->pesan_qty ? '#166534' : '#b45309' }}; font-weight: 600;">
                                                Stok Gudang FG: {{ number_format($stokFg, 0) }} {{ $stokFg >= $item->pesan_qty ? '(Cukup Siap Kirim)' : '(Kurang ' . number_format($item->pesan_qty - $stokFg, 0) . ' Karton)' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td style="font-family: monospace; font-size: 0.8rem; color: #0284c7; font-weight: 600;">
                                    {{ $item->barang?->barang_cd }}
                                </td>
                                <td style="text-align: right; font-weight: 600; font-size: 0.875rem;">
                                    {{ number_format((float) $item->pesan_qty, 0) }}
                                </td>
                                <td style="color: #475569; font-size: 0.85rem;">
                                    {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                </td>
                                <td style="text-align: right; font-size: 0.85rem; color: #475569; font-family: monospace;">
                                    Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $diskonPct > 0 ? '#d97706' : '#94a3b8' }};">
                                    {{ $diskonPct > 0 ? number_format($diskonPct, 1, ',', '.') . '%' : '-' }}
                                </td>
                                <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $potNom > 0 ? '#dc2626' : '#94a3b8' }};">
                                    {{ $potNom > 0 ? 'Rp ' . number_format($potNom, 0, ',', '.') : '-' }}
                                </td>
                                <td style="text-align: center;">
                                    @if ($isPpn)
                                        <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:0.7rem; font-weight:700;">PPN 11%</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:0.7rem;">Non PPN</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;">
                                    Rp {{ number_format($subtotalRow, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0; font-weight: 700;">
                        <tr>
                            <td colspan="4" style="text-align: right; padding: 0.75rem 1rem; color: #475569; font-size: 0.85rem;">
                                Total Kuantitas:
                            </td>
                            <td style="text-align: right; padding: 0.75rem 0.5rem; color: #0f172a; font-size: 0.875rem;">
                                {{ number_format($totalQty, 0) }}
                            </td>
                            <td colspan="5"></td>
                            <td style="text-align: right; padding: 0.75rem 1rem; color: #0f172a; font-size: 1rem; font-family: monospace;">
                                Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- SUMMARY BREAKDOWN KEUANGAN PO PENJUALAN --}}
            <div style="border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 1.25rem; display: flex; justify-content: flex-end;">
                <div style="width: 100%; max-width: 440px; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; color: #64748b;">
                        <span>Subtotal Nilai Bruto:</span>
                        <strong style="color: #0f172a; font-family: monospace;">Rp {{ number_format((float) $order->subtotal_bruto, 0, ',', '.') }}</strong>
                    </div>

                    @if ($order->diskon_total > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; color: #d97706;">
                            <span>Akumulasi Diskon Item:</span>
                            <strong style="font-family: monospace;">- Rp {{ number_format((float) $order->diskon_total, 0, ',', '.') }}</strong>
                        </div>
                    @endif

                    @if ($order->potongan_nominal > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; color: #dc2626;">
                            <span>Potongan Faktur Langsung:</span>
                            <strong style="font-family: monospace;">- Rp {{ number_format((float) $order->potongan_nominal, 0, ',', '.') }}</strong>
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between; align-items: center; color: #475569; padding-top: 0.35rem; border-top: 1px dashed #cbd5e1;">
                        <span>Dasar Pengenaan Pajak (DPP):</span>
                        <strong style="color: #0f172a; font-family: monospace;">Rp {{ number_format((float) $order->dpp_nominal, 0, ',', '.') }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; color: #0284c7;">
                        <span>PPN (11%):</span>
                        <strong style="font-family: monospace;">+ Rp {{ number_format((float) $order->ppn_nominal, 0, ',', '.') }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.65rem; border-top: 2px solid #0f172a; font-size: 1.05rem;">
                        <span style="font-weight: 700; color: #0f172a;">Total Tagihan SO Resmi:</span>
                        <strong style="color: #0284c7; font-size: 1.2rem; font-family: monospace;">Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}</strong>
                    </div>

                    @if ($order->total_retur_nominal > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; color: #dc2626; padding-top: 0.35rem;">
                            <span>Retur Penjualan (Potong Tagihan):</span>
                            <strong style="font-family: monospace;">- Rp {{ number_format((float) $order->total_retur_nominal, 0, ',', '.') }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.35rem; border-top: 1px dashed #cbd5e1; font-size: 0.95rem;">
                            <span style="font-weight: 700; color: #0f172a;">Sisa Tagihan Bersih (Net):</span>
                            <strong style="color: #059669; font-size: 1.15rem; font-family: monospace;">Rp {{ number_format((float) $order->net_tagihan, 0, ',', '.') }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KARTU 1.5: RIWAYAT RETUR PENJUALAN (JIKA ADA RETUR) --}}
        @if($order->returns && $order->returns->count() > 0)
            <div class="card" id="retur-penjualan" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4 4m-4-4l4-4m12 11a4 4 0 004-4v-1a4 4 0 00-4-4h-4"/></svg>
                        </div>
                        <div>
                            <strong style="color: #0f172a; font-size: 0.95rem;">Riwayat Retur Penjualan (Customer Returns)</strong>
                            <span style="font-size: 0.75rem; color: #64748b; display: block;">Barang rusak/cacat telah dikembalikan ke Gudang &amp; dicatat pada kartu stok</span>
                        </div>
                    </div>

                    @if($order->isCompleted() || ($order->status_cd === 'PARTIAL' && $order->total_kirim_qty > 0))
                        <button type="button" onclick="openReturModal()" class="btn btn-secondary btn-sm" style="font-size: 0.775rem; font-weight: 600; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Tambah Retur</span>
                        </button>
                    @endif
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 0.6rem 0.85rem; text-align: center; width: 40px;">No</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: left;">No. Retur</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: left;">Tanggal</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: left;">Gudang Masuk</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: left;">Tindakan</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: left;">Rincian Barang</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: right; width: 80px;">Total Qty</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: right; width: 120px;">Nilai Retur</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: center; width: 90px;">Status</th>
                                <th style="padding: 0.6rem 0.85rem; text-align: center; width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->returns as $rIdx => $retur)
                                <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $retur->status_cd === 'VOID' ? '#fff1f2' : '#ffffff' }};">
                                    <td style="text-align: center; color: #64748b; padding: 0.6rem 0.85rem;">{{ $rIdx + 1 }}</td>
                                    <td style="padding: 0.6rem 0.85rem;">
                                        <strong style="font-family: monospace; color: #0284c7;">{{ $retur->retur_no }}</strong>
                                        @if($retur->alasan_txt)
                                            <span style="display: block; font-size: 0.725rem; color: #64748b; margin-top: 2px;">{{ Str::limit($retur->alasan_txt, 45) }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 0.6rem 0.85rem; color: #334155;">
                                        {{ $retur->retur_tgl ? $retur->retur_tgl->format('d/m/Y') : '-' }}
                                    </td>
                                    <td style="padding: 0.6rem 0.85rem; color: #334155;">
                                        <strong>{{ $retur->gudang?->gudang_nm ?? '-' }}</strong>
                                        <span style="font-size: 0.725rem; color: #64748b; display: block;">Batch: RET-{{ $order->so_no }}</span>
                                    </td>
                                    <td style="padding: 0.6rem 0.85rem;">
                                        @if($retur->tindakan_cd === 'POTONG_TAGIHAN')
                                            <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.7rem; font-weight: 700;">
                                                Potong Tagihan
                                            </span>
                                        @else
                                            <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.7rem; font-weight: 700;">
                                                Tukar Guling
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding: 0.6rem 0.85rem;">
                                        @foreach($retur->details as $rdtl)
                                            <div style="font-size: 0.775rem; margin-bottom: 2px;">
                                                &bull; <strong>{{ $rdtl->barang?->barang_nm }}</strong>: {{ number_format((float)$rdtl->qty_retur, 0) }} unit 
                                                <span style="color: #64748b;">({{ $rdtl->kondisi_label }})</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td style="padding: 0.6rem 0.85rem; text-align: right; font-family: monospace; font-weight: 700;">
                                        {{ number_format((float)$retur->total_qty, 0) }}
                                    </td>
                                    <td style="padding: 0.6rem 0.85rem; text-align: right; font-family: monospace; font-weight: 700; color: #0f172a;">
                                        Rp {{ number_format((float)$retur->total_nilai, 0, ',', '.') }}
                                    </td>
                                    <td style="padding: 0.6rem 0.85rem; text-align: center;">
                                        {!! $retur->status_badge !!}
                                    </td>
                                    <td style="padding: 0.6rem 0.85rem; text-align: center; white-space: nowrap;">
                                        <a href="{{ route('penjualan.retur.export-pdf', $retur->retur_id) }}" 
                                           target="_blank" 
                                           class="btn btn-secondary btn-sm" 
                                           style="font-size: 0.725rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-right: 0.25rem;"
                                           title="Cetak Berita Acara Retur PDF">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>PDF</span>
                                        </a>
                                        @if($retur->status_cd === 'CONFIRMED')
                                            <button type="button" 
                                                    onclick="openVoidReturModal({{ $retur->retur_id }}, '{{ $retur->retur_no }}')"
                                                    class="btn btn-secondary btn-sm" 
                                                    style="border-color: #fca5a5; color: #dc2626; background: #fff5f5; font-size: 0.725rem; padding: 0.25rem 0.5rem;"
                                                    title="Batalkan retur ini">
                                                Void
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; border-top: 2px solid #e2e8f0; font-weight: 700;">
                                <td colspan="6" style="padding: 0.65rem 0.85rem; text-align: right; color: #334155;">
                                    Total Retur Terkonfirmasi:
                                </td>
                                <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; color: #0f172a;">
                                    {{ number_format((float)$order->total_retur_qty, 0) }}
                                </td>
                                <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; color: #0f172a; font-size: 0.9rem;">
                                    Rp {{ number_format((float)$order->total_retur_nominal, 0, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- KARTU 2: RIWAYAT PEMBAYARAN & PELUNASAN KAS MASUK (BKM) --}}
        <div class="card" id="pembayaran" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 28px; height: 28px; border-radius: 6px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">Riwayat Pembayaran &amp; Pelunasan Kas Masuk (BKM)</strong>
                        <span style="font-size: 0.75rem; color: #64748b; display: block;">Mendukung pencatatan bertahap / cicilan termin / uang muka (DP)</span>
                    </div>
                </div>

                @if($order->sisa_tagihan > 0 && !$order->isCancelled())
                    <button type="button" onclick="openRecordPaymentModal()" class="btn btn-primary btn-sm" style="background: #059669; border-color: #059669; font-size: 0.775rem; font-weight: 700; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Catat Pembayaran</span>
                    </button>
                @endif
            </div>

            {{-- Progress Status Pembayaran --}}
            <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; font-size: 0.825rem;">
                    <span style="color: #475569;">
                        Progress Pelunasan: <strong>{{ $order->persen_bayar }}%</strong>
                        ({{ $order->status_bayar === 'LUNAS' ? 'LUNAS' : ($order->status_bayar === 'SEBAGIAN' ? 'SEBAGIAN / CICILAN' : 'BELUM DIBAYAR') }})
                    </span>
                    <span style="font-family: monospace; font-weight: 700; color: {{ $order->sisa_tagihan > 0 ? '#dc2626' : '#15803d' }};">
                        Sisa Piutang: Rp {{ number_format((float) $order->sisa_tagihan, 0, ',', '.') }}
                    </span>
                </div>
                <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                    <div style="width: {{ $order->persen_bayar }}%; height: 100%; background: {{ $order->persen_bayar >= 100 ? '#10b981' : ($order->persen_bayar > 0 ? '#f59e0b' : '#cbd5e1') }}; transition: width 0.3s;"></div>
                </div>
            </div>

            {{-- Tabel Voucher BKM --}}
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
                            <th style="padding: 0.6rem 0.85rem; text-align: center; width: 40px;">No</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">No. Bukti Kas Masuk (BKM)</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">Tanggal Terima</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">Rekening Kas / Bank</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">Metode / Ref</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">Catatan / Uraian</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: right; width: 130px;">Nominal Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->payments as $pIdx => $payment)
                            @php
                                $bkmHdr = $payment->header;
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="text-align: center; color: #64748b; padding: 0.6rem 0.85rem;">{{ $pIdx + 1 }}</td>
                                <td style="padding: 0.6rem 0.85rem;">
                                    <a href="{{ route('keuangan.kas_masuk.show', $bkmHdr?->bkm_id ?? 0) }}" target="_blank" style="font-family: monospace; font-weight: 700; color: #0284c7; text-decoration: none;">
                                        {{ $bkmHdr?->bkm_no ?? '-' }}
                                    </a>
                                </td>
                                <td style="padding: 0.6rem 0.85rem; color: #334155;">
                                    {{ $bkmHdr?->tgl_terima ? \Carbon\Carbon::parse($bkmHdr->tgl_terima)->format('d/m/Y') : '-' }}
                                </td>
                                <td style="padding: 0.6rem 0.85rem; color: #334155;">
                                    <strong>{{ $bkmHdr?->kasBank?->nama_akun ?? '-' }}</strong>
                                    <span style="font-size: 0.725rem; color: #64748b; display: block;">{{ $bkmHdr?->kasBank?->bank_nama ?? '' }} {{ $bkmHdr?->kasBank?->nomor_rekening ? '(' . $bkmHdr->kasBank->nomor_rekening . ')' : '' }}</span>
                                </td>
                                <td style="padding: 0.6rem 0.85rem; color: #475569;">
                                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.7rem;">
                                        {{ $bkmHdr?->metode_terima ?? 'TRANSFER' }}
                                    </span>
                                    @if($bkmHdr?->no_referensi_bank)
                                        <div style="font-size: 0.725rem; font-family: monospace; color: #64748b; margin-top: 2px;">
                                            Ref: {{ $bkmHdr->no_referensi_bank }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 0.6rem 0.85rem; color: #475569; font-size: 0.775rem;">
                                    {{ $payment->uraian }}
                                </td>
                                <td style="padding: 0.6rem 0.85rem; text-align: right; font-family: monospace; font-weight: 700; color: #059669;">
                                    Rp {{ number_format((float) $payment->nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem;">
                                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Belum Ada Pembayaran yang Tercatat</span>
                                        <span style="font-size: 0.75rem;">Pelanggan belum menyetor uang muka (DP) atau cicilan pelunasan untuk tagihan pesanan ini.</span>
                                        @if(!$order->isCancelled())
                                            <button type="button" onclick="openRecordPaymentModal()" class="btn btn-primary btn-sm" style="margin-top: 0.5rem; font-size: 0.775rem; background: #0284c7; border-color: #0284c7;">
                                                + Input Pembayaran Pertama Sekarang
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($order->payments->count() > 0)
                        <tfoot>
                            <tr style="background: #f8fafc; border-top: 2px solid #e2e8f0; font-weight: 700;">
                                <td colspan="6" style="padding: 0.65rem 0.85rem; text-align: right; color: #334155;">
                                    Total Pembayaran Terverifikasi (Kas/Bank):
                                </td>
                                <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; color: #059669; font-size: 0.9rem;">
                                    Rp {{ number_format((float) $order->total_bayar, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- KARTU 3: CATATAN DOKUMEN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.95rem;">Catatan Pesanan / Syarat Khusus</strong>
            </div>
            <div style="padding: 1.25rem; color: #475569; font-size: 0.85rem; line-height: 1.5; white-space: pre-line;">
                {{ $order->catatan_txt ?: 'Tidak ada catatan khusus yang dilampirkan pada pesanan ini.' }}
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN (SIDEBAR RINGKAS STICKY) --}}
    <div style="display: flex; flex-direction: column; gap: 1.25rem; position: sticky; top: 1rem;">
        
        {{-- PANEL 1: MITRA CUSTOMER --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Mitra Customer (Pelanggan)</strong>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                    <div>
                        <strong style="font-size: 1rem; color: #0f172a; display: block;">{{ $order->customer?->customer_nm ?? '-' }}</strong>
                        <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">
                            Kode: <strong>{{ $order->customer?->customer_cd ?? '-' }}</strong>
                            @if($order->customer?->kode_pelanggan)
                                &bull; Ref: <strong>{{ $order->customer->kode_pelanggan }}</strong>
                            @endif
                        </span>
                    </div>
                    @php
                        $klas = $order->customer?->klasifikasi;
                        $klasBadgeBg = match($klas) {
                            'IFM' => '#e0f2fe',
                            'IDM_ALFA' => '#f3e8ff',
                            default => '#f0fdf4'
                        };
                        $klasBadgeClr = match($klas) {
                            'IFM' => '#0284c7',
                            'IDM_ALFA' => '#7e22ce',
                            default => '#15803d'
                        };
                        $klasLabel = match($klas) {
                            'IFM' => 'Indofood (IFM)',
                            'IDM_ALFA' => 'Modern Trade (IDM/ALFA)',
                            'MANUAL' => 'Manual (Sales/Toko)',
                            default => 'Customer'
                        };
                    @endphp
                    <span style="display: inline-block; background: {{ $klasBadgeBg }}; color: {{ $klasBadgeClr }}; font-weight: 700; font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; white-space: nowrap;">
                        {{ $klasLabel }}
                    </span>
                </div>
                
                <div style="margin-top: 0.75rem; font-size: 0.825rem; color: #475569; line-height: 1.4;">
                    @if($order->customer?->wilayah)
                        <div style="margin-bottom: 0.35rem;">
                            <span style="color: #64748b; display: block; font-size: 0.75rem;">Wilayah / Area:</span>
                            <strong style="color: #0f172a;">{{ $order->customer->wilayah }}</strong>
                        </div>
                    @endif
                    <div style="margin-bottom: 0.35rem;">
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Alamat Pengiriman:</span>
                        {{ $order->customer?->alamat_txt ?? '-' }}
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.35rem; border-top: 1px dashed #e2e8f0; padding-top: 0.5rem;">
                        <div>
                            <span style="color: #64748b; display: block; font-size: 0.75rem;">Syarat Bayar (TOP):</span>
                            <strong style="color: #0284c7;">{{ $order->top_txt ?: ($order->customer?->top_txt ?: '-') }}</strong>
                        </div>
                        <div>
                            <span style="color: #64748b; display: block; font-size: 0.75rem;">Telepon / Kontak:</span>
                            <strong>{{ $order->customer?->kontak_no ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL 2: STATUS LOGISTIK & DOKUMEN RESMI --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Status Logistik &amp; Dokumen Resmi</strong>
            </div>
            <div style="padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.825rem;">
                <div>
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Status Mutasi Stok Gudang:</span>
                    @if($order->isCompleted())
                        <span style="display: inline-block; background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 0.725rem; margin-top: 2px;">
                            ✓ Stok Terpotong FEFO (Gudang FG)
                        </span>
                    @else
                        <span style="display: inline-block; background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 0.725rem; margin-top: 2px;">
                            Menunggu Pengiriman Gudang
                        </span>
                    @endif
                </div>

                <div>
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Tanggal Kirim:</span>
                    <strong style="color: #0f172a;">
                        {{ $order->tgl_kirim_aktual ? $order->tgl_kirim_aktual->format('d/m/Y') : ($order->tgl_kirim_estimasi ? 'Estimasi: ' . $order->tgl_kirim_estimasi->format('d/m/Y') : '-') }}
                    </strong>
                </div>

                @if($order->supir_nm || $order->nopol_kendaraan)
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Armada / Pengemudi:</span>
                        <strong style="color: #334155;">{{ $order->supir_nm ?? '-' }} ({{ $order->nopol_kendaraan ?? '-' }})</strong>
                    </div>
                @endif

                <div>
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Nomor Surat Jalan:</span>
                    <strong style="font-family: monospace; font-size: 0.85rem; color: #16a34a;">{{ $order->surat_jalan_no ?: '-' }}</strong>
                </div>

                <div>
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Nomor Faktur Penjualan:</span>
                    <strong style="font-family: monospace; font-size: 0.85rem; color: #0284c7;">{{ $order->faktur_no ?: '-' }}</strong>
                </div>

                <div>
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Nomor PO Customer:</span>
                    <strong style="font-family: monospace; font-size: 0.85rem; color: #334155;">{{ $order->customer_po_no ?: '-' }}</strong>
                </div>

                <div style="border-top: 1px dashed #e2e8f0; padding-top: 0.5rem; margin-top: 0.25rem;">
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Dibuat Oleh:</span>
                    <strong style="color: #334155;">{{ $order->created_by ?: 'Administrator' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DIALOGS LENGKAP --}}
@include('penjualan.so.partials.modal-confirm-delivery')
@include('penjualan.so.partials.modal-void-delivery')
@include('penjualan.so.partials.modal-cancel-so')
@include('penjualan.so.partials.modal-record-payment')
@include('penjualan.so.partials.modal-retur-penjualan')

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/so/so-show.js') }}"></script>
@endpush
