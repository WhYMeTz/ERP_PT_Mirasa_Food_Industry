@extends('layouts.app')

@section('title', 'Detail PO Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@section('content')
{{-- PRINT STYLES --}}
<style>
    @media print {
        header, nav, .btn, .no-print, .alert {
            display: none !important;
        }
        body, main, .main-content {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            break-inside: avoid;
        }
        .print-only {
            display: block !important;
        }
    }
    .print-only {
        display: none;
    }
</style>

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
        {{-- Cetak Faktur PDF --}}
        <a href="{{ route('penjualan.so.export-faktur', $order->so_id) }}" target="_blank" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Cetak Faktur (PDF)
        </a>

        {{-- Cetak Surat Jalan PDF --}}
        <a href="{{ route('penjualan.so.export-surat-jalan', $order->so_id) }}" target="_blank" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            Cetak Surat Jalan (PDF)
        </a>

        @if(!in_array($order->status_cd, ['COMPLETED', 'CANCELLED']))
            <a href="{{ route('penjualan.so.edit', $order->so_id) }}" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit PO
            </a>

            <button type="button" class="btn btn-danger" onclick="document.getElementById('cancelModal').style.display='flex'" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batalkan
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
    {{-- TOTAL NILAI PESANAN --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Total Nilai Pesanan
        </span>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            {{ $totalItem }} item produk &bull; {{ number_format($totalQty, 0) }} total kuantitas
        </div>
    </div>

    {{-- REALISASI & STATUS DOKUMEN --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Status Realisasi
            </span>
            <span style="font-size: 0.8rem; font-weight: 700; color: {{ $pct >= 100 ? '#059669' : ($pct > 0 ? '#d97706' : '#64748b') }};">
                {{ $pct }}%
            </span>
        </div>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            {{ $statusCd }}
        </div>
        {{-- Progress Bar --}}
        <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-top: 0.45rem;">
            <div style="width: {{ $pct }}%; height: 100%; background: {{ $pct >= 100 ? '#10b981' : ($pct > 0 ? '#f59e0b' : '#0284c7') }};"></div>
        </div>
    </div>

    {{-- TOTAL ITEM PRODUK --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Total Varian Produk
        </span>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            {{ $totalItem }} <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">varian</span>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            Produk hasil produksi (FG / WIP)
        </div>
    </div>

    {{-- JADWAL & ESTIMASI KIRIM --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Jadwal Pesanan
        </span>
        <div style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            {{ \Carbon\Carbon::parse($order->so_tgl)->format('d/m/Y') }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            Estimasi Kirim: 
            @if ($order->tgl_kirim_estimasi)
                <strong style="color: #0284c7;">{{ \Carbon\Carbon::parse($order->tgl_kirim_estimasi)->format('d/m/Y') }}</strong>
            @else
                <span>-</span>
            @endif
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
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Nama Komoditas / Produk</th>
                            <th>Satuan</th>
                            <th style="text-align: right;">Jumlah Pesan</th>
                            <th style="text-align: right;">Harga Satuan</th>
                            <th style="text-align: right;">Diskon</th>
                            <th style="text-align: right;">Potongan</th>
                            <th style="text-align: center;">PPN</th>
                            <th style="text-align: right;">Subtotal</th>
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
                                <td>
                                    <strong style="color: #0f172a; font-size: 0.875rem;">{{ $item->barang?->barang_nm }}</strong>
                                    <span style="display: block; font-size: 0.725rem; color: #64748b; font-family: monospace;">
                                        {{ $item->barang?->barang_cd }} &bull; {{ $item->barang?->jenisBarang?->jenis_barang_nm ?? '-' }}
                                    </span>
                                </td>
                                <td style="color: #475569; font-size: 0.85rem;">
                                    {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                </td>
                                <td style="text-align: right; font-weight: 600; font-size: 0.875rem;">
                                    {{ number_format((float) $item->pesan_qty, 0) }}
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
                                        <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:0.7rem;">Non-PPN</span>
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
                            <td colspan="3" style="text-align: right; padding: 0.75rem 1rem; color: #475569; font-size: 0.85rem;">
                                Total Kuantitas &amp; Estimasi:
                            </td>
                            <td style="text-align: right; padding: 0.75rem 0.5rem; color: #0f172a; font-size: 0.875rem;">
                                {{ number_format($totalQty, 0) }}
                            </td>
                            <td colspan="4"></td>
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

        {{-- KARTU 2: CATATAN DOKUMEN --}}
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

        {{-- PANEL 2: DOKUMEN & PENAGIHAN RESMI --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Dokumen &amp; Penagihan Resmi</strong>
            </div>
            <div style="padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.825rem;">
                <div>
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Nomor Faktur Penjualan:</span>
                    <strong style="font-family: monospace; font-size: 0.85rem; color: #0284c7;">{{ $order->faktur_no ?: '-' }}</strong>
                </div>

                <div>
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Nomor Surat Jalan:</span>
                    <strong style="font-family: monospace; font-size: 0.85rem; color: #16a34a;">{{ $order->surat_jalan_no ?: '-' }}</strong>
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

{{-- MODAL PEMBATALAN PESANAN --}}
<div id="cancelModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);">
        <div style="padding: 1.25rem; background: #fef2f2; border-bottom: 1px solid #fee2e2; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 style="font-size: 1rem; font-weight: 800; color: #991b1b; margin: 0;">Konfirmasi Pembatalan PO Penjualan</h3>
                <p style="font-size: 0.775rem; color: #7f1d1d; margin: 0;">Pesanan {{ $order->so_no }} akan ditandai dibatalkan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('penjualan.so.cancel', $order->so_id) }}" style="padding: 1.25rem;">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                    Alasan Pembatalan <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="reason" required rows="3" class="form-control" placeholder="Contoh: Permintaan pembatalan dari customer karena kendala logistik toko..." style="font-size: 0.85rem; width: 100%; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('cancelModal').style.display='none'" style="font-weight: 600; padding: 0.5rem 1rem;">
                    Tutup
                </button>
                <button type="submit" class="btn btn-danger" style="font-weight: 700; padding: 0.5rem 1.25rem;">
                    Ya, Batalkan Pesanan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
