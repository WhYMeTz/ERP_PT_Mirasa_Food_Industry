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
            {{-- Tombol Utama: Eksekusi Pengiriman & Potong Stok FEFO --}}
            <button type="button" 
                    onclick="openConfirmDeliveryModal({{ $order->so_id }}, '{{ $order->so_no }}', '{{ addslashes($order->customer?->customer_nm ?? '') }}')" 
                    class="btn btn-primary" 
                    style="background: #059669; border-color: #059669; color: #ffffff; padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 4px rgba(5,150,105,0.2);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Konfirmasi Kirim &amp; Potong Stok (FEFO)</span>
            </button>
        @elseif($order->isCompleted())
            <span class="delivery-badge-success" style="padding: 0.45rem 0.85rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Stok FG Terpotong Resmi (FEFO)</span>
            </span>

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

        {{-- Cetak Faktur PDF --}}
        <a href="{{ route('penjualan.so.export-faktur', $order->so_id) }}" target="_blank" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Faktur (PDF)</span>
        </a>

        {{-- Cetak Surat Jalan PDF --}}
        <a href="{{ route('penjualan.so.export-surat-jalan', $order->so_id) }}" target="_blank" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            <span>Surat Jalan (PDF)</span>
        </a>

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
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            {{ $totalItem }} varian &bull; {{ number_format($totalQty, 0) }} total kuantitas
        </div>
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
                            <th style="width: 110px; text-align: left;">e. Jenis Barang</th>
                            <th style="min-width: 190px; text-align: left;">f. Nama Barang</th>
                            <th style="width: 100px; text-align: left;">g. Kode Barang</th>
                            <th style="width: 90px; text-align: right;">h. Jumlah Pesanan</th>
                            <th style="width: 70px; text-align: left;">i. Satuan</th>
                            <th style="width: 110px; text-align: right;">j. @Harga satuan</th>
                            <th style="width: 70px; text-align: right;">k. Diskon %</th>
                            <th style="width: 90px; text-align: right;">l. Potongan Harga</th>
                            <th style="width: 95px; text-align: center;">m. PPN 11% / Non</th>
                            <th style="width: 125px; text-align: right;">n. Total Harga</th>
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
                </div>
            </div>
        </div>

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
                <strong style="font-size: 1rem; color: #0f172a; display: block;">{{ $order->customer?->customer_nm ?? '-' }}</strong>
                <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">Kode: {{ $order->customer?->customer_cd ?? '-' }}</span>
                
                <div style="margin-top: 0.75rem; font-size: 0.825rem; color: #475569; line-height: 1.4;">
                    <div style="margin-bottom: 0.35rem;">
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Alamat Pengiriman:</span>
                        {{ $order->customer?->alamat_txt ?? '-' }}
                    </div>
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Telepon / Kontak:</span>
                        <strong>{{ $order->customer?->kontak_no ?? '-' }}</strong>
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

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/so/so-show.js') }}"></script>
@endpush
