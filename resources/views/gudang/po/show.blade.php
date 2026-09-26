@extends('layouts.app')

@section('title', 'Detail Purchase Order ' . $po->po_no . ' - ERP PT Mirasa')

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
        <a href="{{ route('gudang.po.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar PO
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $po->po_no }}</h1>
            @if ($po->status_cd == 'COMPLETED')
                <span class="badge badge-success">Selesai (100%)</span>
            @elseif ($po->status_cd == 'PARTIAL')
                <span class="badge badge-info">Sebagian Diterima ({{ $po->persentase_terima }}%)</span>
            @elseif ($po->status_cd == 'CLOSED')
                <span class="badge" style="background:#f1f5f9; color:#475569; border: 1px solid #cbd5e1;">Ditutup ({{ $po->persentase_terima }}%)</span>
            @elseif ($po->status_cd == 'APPROVED')
                <span class="badge" style="background:#e0f2fe; color:#0369a1;">Disetujui</span>
            @elseif ($po->status_cd == 'DRAFT')
                <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
            @else
                <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
            @endif
        </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        {{-- TOMBOL CETAK PO --}}
        <button type="button" onclick="window.print()" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Dokumen PO
        </button>

        @if (in_array($po->status_cd, ['APPROVED', 'PARTIAL']) && $po->total_sisa_qty > 0)
            <button type="button" onclick="openQuickReceiveModal()" class="btn btn-primary" style="background:#059669; padding: 0.45rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terima Barang
            </button>
        @endif

        @if ($po->status_cd == 'PARTIAL')
            <button type="button" onclick="openForceCloseModal()" class="btn btn-secondary" style="border: 1px solid #cbd5e1; padding: 0.45rem 0.85rem; font-size: 0.85rem;">
                Tutup PO (Selesai Parsial)
            </button>
        @endif

        @if (in_array($po->status_cd, ['DRAFT', 'APPROVED']) && $po->details->sum('terima_qty') == 0)
            <form action="{{ route('gudang.po.cancel', $po->po_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan Purchase Order ini?')" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-danger" style="padding: 0.45rem 0.85rem; font-size: 0.85rem;">
                    Batalkan PO
                </button>
            </form>
        @endif
    </div>
</div>

{{-- ALERT JIKA PO SUDAH DITUTUP --}}
@if ($po->status_cd == 'CLOSED')
    <div class="alert" style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #475569; color: #334155; margin-bottom: 1.25rem; padding: 0.875rem 1.25rem; border-radius: 6px;">
        <div style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">Dokumen Purchase Order Telah Ditutup (Closed)</div>
        <div style="font-size: 0.85rem; color: #475569; margin-top: 0.25rem;">
            Ditutup pada: <strong>{{ $po->closed_at ? \Carbon\Carbon::parse($po->closed_at)->format('d/m/Y H:i') : '-' }}</strong> &bull; Oleh: <strong>{{ $po->closed_by ?? '-' }}</strong>
        </div>
        @if ($po->closed_reason)
            <div style="margin-top: 0.35rem; font-size: 0.85rem; color: #64748b; background: #ffffff; padding: 0.4rem 0.65rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                <strong>Alasan Penutupan:</strong> {{ $po->closed_reason }}
            </div>
        @endif
    </div>
@endif

{{-- 4 KARTU METRIK OPERASIONAL PO --}}
@php
    $pct = $po->persentase_terima;
    $totalPesan = (float) $po->details->sum('pesan_qty');
    $totalTerima = (float) $po->details->sum('terima_qty');
    $totalSisa = (float) $po->total_sisa_qty;
@endphp
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- TOTAL NOMINAL PO --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Total Nilai Pesanan
        </span>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            Rp {{ number_format((float) $po->total_nominal, 0, ',', '.') }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            {{ $po->details->count() }} item bahan baku &bull; {{ number_format($totalPesan, 0) }} total kuantitas
        </div>
    </div>

    {{-- REALISASI NOMINAL DITERIMA --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Realisasi Barang Masuk
            </span>
            <span style="font-size: 0.8rem; font-weight: 700; color: {{ $pct >= 100 ? '#059669' : ($pct > 0 ? '#d97706' : '#64748b') }};">
                {{ $pct }}%
            </span>
        </div>
        <div style="font-size: 1.35rem; font-weight: 700; color: {{ $pct >= 100 ? '#059669' : '#0f172a' }}; margin-top: 0.25rem;">
            Rp {{ number_format((float) $po->realisasi_nominal, 0, ',', '.') }}
        </div>
        {{-- Progress Bar --}}
        <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-top: 0.45rem;">
            <div style="width: {{ min(100, $pct) }}%; height: 100%; background: {{ $pct >= 100 ? '#10b981' : ($pct > 0 ? '#f59e0b' : '#cbd5e1') }};"></div>
        </div>
    </div>

    {{-- TOTAL SISA FISIK BELUM DATANG --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Sisa Fisik Belum Tiba
        </span>
        <div style="font-size: 1.35rem; font-weight: 700; color: {{ $totalSisa > 0 ? '#b45309' : '#059669' }}; margin-top: 0.25rem;">
            {{ number_format($totalSisa, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            @if ($totalSisa > 0)
                Menunggu kiriman supplier berikutnya
            @else
                Seluruh barang telah diterima lengkap
            @endif
        </div>
    </div>

    {{-- JADWAL PO & ESTIMASI TIBA --}}
    <div class="card" style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
            Jadwal &amp; Estimasi Tiba
        </span>
        <div style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
            {{ \Carbon\Carbon::parse($po->po_tgl)->format('d/m/Y') }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
            Estimasi Tiba: 
            @if ($po->tgl_estimasi_datang)
                <strong style="color: #0284c7;">{{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d/m/Y') }}</strong>
            @else
                <span>-</span>
            @endif
        </div>
    </div>
</div>

{{-- MASTER-DETAIL WORKSPACE: 2 KOLOM (KIRI: RINCIAN & HISTORI, KANAN: SIDEBAR INFO MITRA) --}}
<div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 1.5rem; align-items: start; margin-bottom: 2rem;">
    {{-- KOLOM KIRI (KONTEN UTAMA) --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        {{-- KARTU 1: RINCIAN ITEM BARANG YANG DIPESAN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">Rincian Barang yang Dipesan</strong>
                    <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;">({{ $po->details->count() }} Item Bahan)</span>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Nama Bahan Baku</th>
                            <th>Satuan</th>
                            <th style="text-align: right;">Pesan</th>
                            <th style="text-align: right;">Diterima</th>
                            <th style="text-align: right;">Sisa</th>
                            <th style="text-align: right;">Harga Satuan</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po->details as $index => $item)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="text-align: center; color: #64748b; font-size: 0.85rem;">{{ $index + 1 }}</td>
                                <td>
                                    <strong style="color: #0f172a; font-size: 0.875rem;">{{ $item->barang?->barang_nm }}</strong>
                                    <span style="display: block; font-size: 0.725rem; color: #64748b;">{{ $item->barang?->barang_cd }}</span>
                                </td>
                                <td style="color: #475569; font-size: 0.85rem;">
                                    {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                </td>
                                <td style="text-align: right; font-weight: 600; font-size: 0.875rem;">
                                    {{ number_format((float) $item->pesan_qty, 2) }}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #059669; font-size: 0.875rem;">
                                    {{ number_format((float) $item->terima_qty, 2) }}
                                </td>
                                <td style="text-align: right; font-weight: 600; font-size: 0.875rem; color: {{ (float) $item->sisa_qty > 0 ? '#b45309' : '#64748b' }};">
                                    {{ number_format((float) $item->sisa_qty, 2) }}
                                </td>
                                <td style="text-align: right; font-size: 0.85rem; color: #475569;">
                                    Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                    Rp {{ number_format((float) $item->subtotal_nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0; font-weight: 700;">
                        <tr>
                            <td colspan="3" style="text-align: right; padding: 0.75rem 1rem; color: #475569; font-size: 0.85rem;">
                                Total Kuantitas &amp; Nominal:
                            </td>
                            <td style="text-align: right; padding: 0.75rem 0.5rem; color: #0f172a; font-size: 0.875rem;">
                                {{ number_format($totalPesan, 2) }}
                            </td>
                            <td style="text-align: right; padding: 0.75rem 0.5rem; color: #059669; font-size: 0.875rem;">
                                {{ number_format($totalTerima, 2) }}
                            </td>
                            <td style="text-align: right; padding: 0.75rem 0.5rem; color: {{ $totalSisa > 0 ? '#b45309' : '#64748b' }}; font-size: 0.875rem;">
                                {{ number_format($totalSisa, 2) }}
                            </td>
                            <td></td>
                            <td style="text-align: right; padding: 0.75rem 1rem; color: #0f172a; font-size: 1rem;">
                                Rp {{ number_format((float) $po->total_nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- KARTU 2: RIWAYAT KEDATANGAN BARANG (SURAT JALAN / GOOD RECEIPTS) --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">Riwayat Kedatangan Barang Fisik</strong>
                    <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;">({{ $po->penerimaan->count() }} Surat Jalan / Termin)</span>
                </div>
            </div>

            @if ($po->penerimaan->isNotEmpty())
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                <th>No Penerimaan</th>
                                <th>Tanggal Tiba</th>
                                <th>No. Surat Jalan</th>
                                <th>Ketepatan Jadwal</th>
                                <th>Gudang Masuk</th>
                                <th>Status</th>
                                <th style="text-align: right; width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($po->penerimaan as $terima)
                                @php
                                    $tglTerima = \Carbon\Carbon::parse($terima->terima_tgl);
                                    $eta = $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang) : null;
                                @endphp
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td>
                                        <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.85rem;">
                                            {{ $terima->terima_no }}
                                        </a>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #334155;">{{ $tglTerima->format('d/m/Y') }}</td>
                                    <td style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">{{ $terima->suratjalan_no ?? '-' }}</td>
                                    <td>
                                        @if ($eta)
                                            @if ($tglTerima->lt($eta))
                                                <span class="badge" style="background:#f0fdf4; color:#166534; font-size:0.75rem;">
                                                    Lebih Cepat
                                                </span>
                                            @elseif ($tglTerima->equalTo($eta))
                                                <span class="badge" style="background:#f0f9ff; color:#0369a1; font-size:0.75rem;">
                                                    Tepat Waktu
                                                </span>
                                            @else
                                                <span class="badge" style="background:#fef2f2; color:#991b1b; font-size:0.75rem;">
                                                    Terlambat
                                                </span>
                                            @endif
                                        @else
                                            <span style="color:#94a3b8; font-size:0.8rem;">-</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.85rem; color: #475569;">{{ $terima->gudang?->gudang_nm }}</td>
                                    <td><span class="badge badge-success">Diterima Fisik</span></td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn btn-secondary btn-sm no-print" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                                            Lihat Surat Jalan
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                    <div style="width: 42px; height: 42px; margin: 0 auto 0.75rem; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    </div>
                    <strong style="color: #64748b; font-size: 0.9rem; display: block;">Belum ada riwayat kedatangan barang</strong>
                    <p style="font-size: 0.8rem; margin-top: 0.25rem; margin-bottom: 0;">
                        Barang belum pernah dicatat masuk. Saat armada supplier tiba di pabrik, gunakan tombol hijau <strong>"Terima Barang"</strong> di bagian atas.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- KOLOM KANAN (SIDEBAR RINGKAS STICKY) --}}
    <div style="display: flex; flex-direction: column; gap: 1.25rem; position: sticky; top: 1rem;">
        
        {{-- PANEL 1: MITRA SUPPLIER --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Mitra Supplier</strong>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <strong style="font-size: 1rem; color: #0f172a; display: block;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">Kode: {{ $po->supplier?->supplier_cd }}</span>
                
                <div style="margin-top: 0.75rem; font-size: 0.825rem; color: #475569; line-height: 1.4;">
                    <div style="margin-bottom: 0.35rem;">
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Alamat:</span>
                        {{ $po->supplier?->alamat_txt ?? '-' }}
                    </div>
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Telepon / Kontak:</span>
                        <strong>{{ $po->supplier?->kontak_no ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL 2: GUDANG TUJUAN PENERIMAAN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Gudang Tujuan Masuk</strong>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <strong style="font-size: 1rem; color: #0f172a; display: block;">{{ $po->gudang?->gudang_nm ?? '-' }}</strong>
                <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">Kode: {{ $po->gudang?->gudang_cd }}</span>
                
                <div style="margin-top: 0.75rem; font-size: 0.825rem; color: #475569;">
                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Lokasi Pabrik:</span>
                    {{ $po->gudang?->alamat_txt ?? '-' }}
                </div>
            </div>
        </div>

        {{-- PANEL 3: CATATAN & INSTRUKSI PENGADAAN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.9rem;">Instruksi &amp; Catatan</strong>
            </div>
            <div style="padding: 1rem 1.25rem; font-size: 0.85rem; color: #334155; line-height: 1.4;">
                @if ($po->catatan_txt)
                    <p style="margin: 0; white-space: pre-line;">{{ $po->catatan_txt }}</p>
                @else
                    <span style="color: #94a3b8; font-style: italic;">Tidak ada catatan khusus untuk dokumen ini.</span>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- MODAL CEPAT CATAT BARANG MASUK --}}
@if (in_array($po->status_cd, ['APPROVED', 'PARTIAL']) && $po->total_sisa_qty > 0)
<div id="modalQuickReceive" class="no-print" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 10px; width: 100%; max-width: 660px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="color: #0f172a; font-size: 1.05rem;">Catat Barang Masuk</strong>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                    {{ $po->po_no }} &bull; {{ $po->supplier?->supplier_nm }}
                </div>
            </div>
            <button type="button" onclick="closeQuickReceiveModal()" style="background: transparent; border: none; font-size: 1.35rem; color: #64748b; cursor: pointer; padding: 0 0.25rem;">&times;</button>
        </div>

        <form action="{{ route('gudang.terima.store') }}" method="POST" style="padding: 1.25rem; overflow-y: auto; flex: 1;">
            @csrf
            <input type="hidden" name="po_id" value="{{ $po->po_id }}">
            <input type="hidden" name="supplier_id" value="{{ $po->supplier_id }}">
            <input type="hidden" name="gudang_id" value="{{ $po->gudang_id }}">
            <input type="hidden" name="redirect_to" value="po">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="terima_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal Masuk <span style="color:#ef4444;">*</span></label>
                    <input type="date" id="terima_tgl" name="terima_tgl" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="suratjalan_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">No. Surat Jalan Supplier</label>
                    <input type="text" id="suratjalan_no" name="suratjalan_no" placeholder="Contoh: SJ-2026/09/88" class="form-control">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0;">
                        Kuantitas Tiba Hari Ini:
                    </label>
                    <div style="display: flex; gap: 0.35rem;">
                        <button type="button" onclick="fillAllSisa()" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                            Terima Semua Sisa
                        </button>
                        <button type="button" onclick="clearAllInputs()" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                            Kosongkan (0)
                        </button>
                    </div>
                </div>

                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead style="background: #f8fafc; font-size: 0.8rem; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 0.6rem 0.75rem; text-align: left;">Nama Barang</th>
                                <th style="padding: 0.6rem 0.75rem; text-align: right; width: 90px;">Sisa PO</th>
                                <th style="padding: 0.6rem 0.75rem; text-align: right; width: 145px;">Masuk Hari Ini</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowIdx = 0; @endphp
                            @foreach ($po->details as $pdtl)
                                @if ((float) $pdtl->sisa_qty > 0)
                                    <tr style="border-top: 1px solid #f1f5f9;">
                                        <td style="padding: 0.6rem 0.75rem;">
                                            <input type="hidden" name="items[{{ $rowIdx }}][podtl_id]" value="{{ $pdtl->podtl_id }}">
                                            <input type="hidden" name="items[{{ $rowIdx }}][barang_id]" value="{{ $pdtl->barang_id }}">
                                            <input type="hidden" name="items[{{ $rowIdx }}][harga_nominal]" value="{{ (float) $pdtl->harga_nominal }}">
                                            <strong style="color: #0f172a; display: block; font-size: 0.85rem;">{{ $pdtl->barang?->barang_nm }}</strong>
                                            <span style="font-size: 0.75rem; color: #64748b;">
                                                Satuan: {{ $pdtl->barang?->satuanDasar?->satuan_nm ?? ($pdtl->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                            </span>
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem; text-align: right; font-weight: 600; color: #b45309;">
                                            {{ number_format((float) $pdtl->sisa_qty, 2) }}
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem; text-align: right;">
                                            <input type="number" 
                                                   step="0.0001" 
                                                   min="0" 
                                                   max="{{ $pdtl->sisa_qty }}" 
                                                   name="items[{{ $rowIdx }}][terima_qty]" 
                                                   value="{{ (float) $pdtl->sisa_qty }}" 
                                                   data-sisa="{{ (float) $pdtl->sisa_qty }}"
                                                   class="form-control quick-terima-input" 
                                                   style="text-align: right; font-weight: 700; width: 130px; display: inline-block; padding: 0.35rem 0.5rem; font-size: 0.85rem;" 
                                                   required>
                                        </td>
                                    </tr>
                                    @php $rowIdx++; @endphp
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <small style="color: #64748b; font-size: 0.75rem; margin-top: 0.35rem; display: block;">
                    * Masukkan kuantitas yang benar-benar tiba saat ini. Jika sebagian, sisa kuota akan tetap disimpan untuk kedatangan berikutnya.
                </small>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Penerimaan (Opsional)</label>
                <input type="text" id="catatan_txt" name="catatan_txt" placeholder="Contoh: Pengiriman termin 1, kondisi fisik baik" class="form-control">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <a href="{{ route('gudang.terima.create', ['po_id' => $po->po_id]) }}" style="color: #0284c7; text-decoration: none; font-size: 0.825rem;">
                    Formulir Lengkap (Batch Manual &amp; QC) &rarr;
                </a>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" onclick="closeQuickReceiveModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background:#059669;">
                        Simpan Penerimaan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL KONFIRMASI TUTUP PO --}}
<div id="modalForceClose" class="no-print" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 480px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <strong style="color: #0f172a; font-size: 1rem;">Tutup PO (Selesai Parsial)</strong>
            <button type="button" onclick="closeForceCloseModal()" style="background: transparent; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('gudang.po.force_close', $po->po_id) }}" method="POST" style="padding: 1.25rem;">
            @csrf
            <p style="color: #475569; font-size: 0.875rem; margin-top: 0; margin-bottom: 0.75rem; line-height: 1.4;">
                Gunakan fungsi ini jika sisa barang tidak akan dikirim lagi oleh supplier. Status PO akan diubah menjadi <strong>Ditutup</strong> dan sisa kuota ({{ number_format($po->total_sisa_qty, 2) }}) dibatalkan.
            </p>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="closed_reason" class="form-label" style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                    Alasan Penutupan <span style="color:#ef4444;">*</span>
                </label>
                <textarea id="closed_reason" name="closed_reason" rows="3" class="form-control" placeholder="Tuliskan alasan penutupan PO..." required minlength="5"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeForceCloseModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">
                    Simpan &amp; Tutup PO
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openQuickReceiveModal() {
        const modal = document.getElementById('modalQuickReceive');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    function closeQuickReceiveModal() {
        const modal = document.getElementById('modalQuickReceive');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function fillAllSisa() {
        document.querySelectorAll('.quick-terima-input').forEach(input => {
            input.value = input.dataset.sisa || 0;
        });
    }

    function clearAllInputs() {
        document.querySelectorAll('.quick-terima-input').forEach(input => {
            input.value = 0;
        });
    }

    function openForceCloseModal() {
        const modal = document.getElementById('modalForceClose');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    function closeForceCloseModal() {
        const modal = document.getElementById('modalForceClose');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickReceiveModal();
            closeForceCloseModal();
        }
    });
</script>
@endsection
