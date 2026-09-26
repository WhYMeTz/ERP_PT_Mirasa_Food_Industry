@extends('layouts.app')

@section('title', 'Daftar Purchase Order - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Purchase Order (PO) Pengadaan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Kelola dokumen pemesanan bahan baku singkong, minyak, bumbu, dan kemasan ke mitra supplier.
        </p>
    </div>
    <div>
        <a href="{{ route('gudang.po.create') }}" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat PO Baru
        </a>
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL (QUICK FILTER 1-KLIK) --}}
@php
    $currentStatus = $status ?? '';
@endphp
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- SEMUA PO --}}
    <a href="{{ route('gudang.po.index') }}" 
       style="display: block; text-decoration: none; background: #ffffff; border-radius: 8px; border: 1.5px solid {{ empty($currentStatus) ? '#0284c7' : '#e2e8f0' }}; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: all 0.15s ease;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ empty($currentStatus) ? '#0284c7' : '#64748b' }};">
                    Semua Dokumen PO
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($statusCounts['all'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ empty($currentStatus) ? '#e0f2fe' : '#f1f5f9' }}; display: flex; align-items: center; justify-content: center; color: {{ empty($currentStatus) ? '#0284c7' : '#64748b' }};">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total transaksi pengadaan aktif
        </div>
    </a>

    {{-- MENUNGGU PENGIRIMAN (APPROVED) --}}
    <a href="{{ route('gudang.po.index', ['status' => 'APPROVED']) }}" 
       style="display: block; text-decoration: none; background: #ffffff; border-radius: 8px; border: 1.5px solid {{ $currentStatus === 'APPROVED' ? '#0284c7' : '#e2e8f0' }}; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: all 0.15s ease;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $currentStatus === 'APPROVED' ? '#0284c7' : '#64748b' }};">
                    Menunggu Pengiriman
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($statusCounts['approved'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ $currentStatus === 'APPROVED' ? '#e0f2fe' : '#f1f5f9' }}; display: flex; align-items: center; justify-content: center; color: {{ $currentStatus === 'APPROVED' ? '#0284c7' : '#64748b' }};">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            PO disetujui &bull; Supplier siap kirim
        </div>
    </a>

    {{-- MASUK SEBAGIAN (PARTIAL) --}}
    <a href="{{ route('gudang.po.index', ['status' => 'PARTIAL']) }}" 
       style="display: block; text-decoration: none; background: #ffffff; border-radius: 8px; border: 1.5px solid {{ $currentStatus === 'PARTIAL' ? '#d97706' : '#e2e8f0' }}; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: all 0.15s ease;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $currentStatus === 'PARTIAL' ? '#d97706' : '#64748b' }};">
                    Masuk Sebagian (Parsial)
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($statusCounts['partial'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ $currentStatus === 'PARTIAL' ? '#fef3c7' : '#f1f5f9' }}; display: flex; align-items: center; justify-content: center; color: {{ $currentStatus === 'PARTIAL' ? '#d97706' : '#64748b' }};">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM4 17h1m4 0h6m4 0h1m-1-4V6a1 1 0 00-1-1H4a1 1 0 00-1 1v7m14 0h3l2 3v1a1 1 0 01-1 1h-1m-17 0H3a1 1 0 01-1-1v-1l2-3h12"/>
                </svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Truk sudah tiba &bull; Masih ada sisa
        </div>
    </a>

    {{-- SELESAI / DITUTUP --}}
    <a href="{{ route('gudang.po.index', ['status' => 'COMPLETED_CLOSED']) }}" 
       style="display: block; text-decoration: none; background: #ffffff; border-radius: 8px; border: 1.5px solid {{ in_array($currentStatus, ['COMPLETED_CLOSED', 'COMPLETED', 'CLOSED']) ? '#059669' : '#e2e8f0' }}; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: all 0.15s ease;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ in_array($currentStatus, ['COMPLETED_CLOSED', 'COMPLETED', 'CLOSED']) ? '#059669' : '#64748b' }};">
                    Selesai &amp; Ditutup
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($statusCounts['completed'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ in_array($currentStatus, ['COMPLETED_CLOSED', 'COMPLETED', 'CLOSED']) ? '#d1fae5' : '#f1f5f9' }}; display: flex; align-items: center; justify-content: center; color: {{ in_array($currentStatus, ['COMPLETED_CLOSED', 'COMPLETED', 'CLOSED']) ? '#059669' : '#64748b' }};">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Pengadaan selesai &bull; Arsip tuntas
        </div>
    </a>
</div>

{{-- KARTU TABEL UTAMA --}}
<div class="card">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('gudang.po.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 650px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nomor PO atau nama supplier..." class="form-control" style="padding: 0.45rem 0.75rem; max-width: 280px; font-size: 0.85rem;">
            
            <select name="status" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 175px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="APPROVED" {{ ($currentStatus == 'APPROVED') ? 'selected' : '' }}>Disetujui (Approved)</option>
                <option value="PARTIAL" {{ ($currentStatus == 'PARTIAL') ? 'selected' : '' }}>Sebagian Diterima</option>
                <option value="COMPLETED_CLOSED" {{ ($currentStatus == 'COMPLETED_CLOSED') ? 'selected' : '' }}>Selesai / Ditutup</option>
                <option value="COMPLETED" {{ ($currentStatus == 'COMPLETED') ? 'selected' : '' }}>Selesai (Completed)</option>
                <option value="CLOSED" {{ ($currentStatus == 'CLOSED') ? 'selected' : '' }}>Ditutup (Closed)</option>
                <option value="DRAFT" {{ ($currentStatus == 'DRAFT') ? 'selected' : '' }}>Draft</option>
                <option value="CANCELLED" {{ ($currentStatus == 'CANCELLED') ? 'selected' : '' }}>Dibatalkan</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($search) || !empty($currentStatus))
                <a href="{{ route('gudang.po.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif
        </form>

        <span style="color: #64748b; font-size: 0.85rem;">
            Total PO: <strong style="color: #0f172a;">{{ $poList->total() }}</strong> dokumen
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="min-width: 180px;">Nomor PO</th>
                    <th style="min-width: 120px;">Tanggal</th>
                    <th style="min-width: 180px;">Supplier Mitra</th>
                    <th style="min-width: 140px;">Gudang Tujuan</th>
                    <th style="min-width: 200px;">Status &amp; Realisasi</th>
                    <th style="min-width: 130px; text-align: right;">Total Nominal</th>
                    <th style="width: 160px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($poList as $index => $po)
                    @php
                        $pct = $po->persentase_terima;
                        $totalPesan = (float) $po->details->sum('pesan_qty');
                        $totalTerima = (float) $po->details->sum('terima_qty');
                        $totalSisa = (float) $po->total_sisa_qty;
                        $canReceive = in_array($po->status_cd, ['APPROVED', 'PARTIAL']) && $totalSisa > 0;

                        // Siapkan payload JSON aman untuk Quick Receive Modal
                        $poPayload = [
                            'po_id' => $po->po_id,
                            'po_no' => $po->po_no,
                            'supplier_id' => $po->supplier_id,
                            'supplier_nm' => $po->supplier?->supplier_nm ?? '-',
                            'gudang_id' => $po->gudang_id,
                            'gudang_nm' => $po->gudang?->gudang_nm ?? '-',
                            'items' => $po->details->map(function ($dtl) {
                                return [
                                    'podtl_id'      => $dtl->podtl_id,
                                    'barang_id'     => $dtl->barang_id,
                                    'barang_nm'     => $dtl->barang?->barang_nm ?? '-',
                                    'satuan_nm'     => $dtl->barang?->satuanDasar?->satuan_nm ?? ($dtl->barang?->satuanDasar?->satuan_cd ?? '-'),
                                    'pesan_qty'     => (float) $dtl->pesan_qty,
                                    'terima_qty'    => (float) $dtl->terima_qty,
                                    'sisa_qty'      => (float) $dtl->sisa_qty,
                                    'harga_nominal' => (float) $dtl->harga_nominal,
                                ];
                            })->values(),
                        ];
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                            {{ $poList->firstItem() + $index }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                {{-- Tombol expand preview item tanpa pindah halaman --}}
                                <button type="button" 
                                        onclick="togglePoRow('row-items-{{ $po->po_id }}', this)" 
                                        title="Intip rincian barang"
                                        style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; padding: 0; flex-shrink: 0;">
                                    <svg class="chevron-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: transform 0.15s ease;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <div>
                                    <a href="{{ route('gudang.po.show', $po->po_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                        {{ $po->po_no }}
                                    </a>
                                    <small style="display: block; font-size: 0.725rem; color: #64748b;">
                                        {{ $po->details->count() }} item bahan baku
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155; font-size: 0.85rem;">
                                {{ \Carbon\Carbon::parse($po->po_tgl)->format('d/m/Y') }}
                            </div>
                            @if ($po->tgl_estimasi_datang)
                                <small style="display: block; font-size: 0.725rem; color: #0369a1;">
                                    ETA: {{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d/m/Y') }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.875rem;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                            <small style="display: block; font-size: 0.725rem; color: #64748b;">{{ $po->supplier?->supplier_cd }}</small>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: #334155;">{{ $po->gudang?->gudang_nm ?? '-' }}</span>
                        </td>
                        <td>
                            {{-- BADGE STATUS & PROGRESS BAR REALISASI FISIK --}}
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <div>
                                    @if ($po->status_cd == 'COMPLETED')
                                        <span class="badge badge-success">Selesai (100%)</span>
                                    @elseif ($po->status_cd == 'PARTIAL')
                                        <span class="badge badge-info">Sebagian Diterima ({{ $pct }}%)</span>
                                    @elseif ($po->status_cd == 'CLOSED')
                                        <span class="badge" style="background:#f1f5f9; color:#475569; border: 1px solid #cbd5e1;">Ditutup ({{ $pct }}%)</span>
                                    @elseif ($po->status_cd == 'APPROVED')
                                        <span class="badge" style="background:#e0f2fe; color:#0369a1;">Disetujui</span>
                                    @elseif ($po->status_cd == 'DRAFT')
                                        <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
                                    @else
                                        <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
                                    @endif
                                </div>

                                {{-- Progress Bar Ramping --}}
                                @php
                                    $barBg = '#e2e8f0';
                                    $barFill = '#0284c7';
                                    if ($po->status_cd == 'COMPLETED') {
                                        $barFill = '#10b981';
                                    } elseif ($po->status_cd == 'PARTIAL') {
                                        $barFill = '#f59e0b';
                                    } elseif ($po->status_cd == 'CLOSED') {
                                        $barFill = '#94a3b8';
                                    }
                                @endphp
                                <div style="width: 100%; max-width: 160px; height: 5px; background: {{ $barBg }}; border-radius: 9999px; overflow: hidden; margin-top: 2px;">
                                    <div style="width: {{ min(100, $pct) }}%; height: 100%; background: {{ $barFill }}; border-radius: 9999px;"></div>
                                </div>
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    @if ($po->status_cd == 'COMPLETED')
                                        Tuntas diterima
                                    @elseif ($po->status_cd == 'PARTIAL')
                                        Masuk {{ number_format($totalTerima, 0) }} / {{ number_format($totalPesan, 0) }}
                                    @elseif ($po->status_cd == 'APPROVED')
                                        Menunggu kedatangan truk
                                    @elseif ($po->status_cd == 'CLOSED')
                                        Realisasi {{ number_format($totalTerima, 0) }} / {{ number_format($totalPesan, 0) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                            Rp {{ number_format((float) $po->total_nominal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                <a href="{{ route('gudang.po.show', $po->po_id) }}" class="btn btn-secondary btn-sm" title="Lihat Detail Lengkap PO">
                                    Detail
                                </a>
                                @if ($canReceive)
                                    <button type="button" 
                                            class="btn btn-primary btn-sm" 
                                            style="background: #059669; padding: 0.3rem 0.65rem;" 
                                            title="Catat barang masuk langsung di tempat tanpa pindah halaman"
                                            onclick="openQuickReceiveIndexModal({{ json_encode($poPayload) }})">
                                        Terima
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- EXPANDABLE SUB-ROW: RINCIAN ITEM BARANG TANPA PINDAH HALAMAN --}}
                    <tr id="row-items-{{ $po->po_id }}" style="display: none; background: #f8fafc;">
                        <td colspan="8" style="padding: 0.75rem 1.25rem; border-bottom: 2px solid #e2e8f0;">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <strong style="font-size: 0.825rem; color: #334155;">
                                        Rincian Item Pesanan ({{ $po->po_no }}):
                                    </strong>
                                    <a href="{{ route('gudang.po.show', $po->po_id) }}" style="font-size: 0.75rem; color: #0284c7; text-decoration: none;">
                                        Buka halaman detail penuh &rarr;
                                    </a>
                                </div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Nama Bahan Baku</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Satuan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Pesan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Sudah Masuk</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Sisa Belum Tiba</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Harga Satuan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($po->details as $item)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 0.4rem 0.5rem;">
                                                    <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                                                    <span style="display: block; font-size: 0.7rem; color: #64748b;">{{ $item->barang?->barang_cd }}</span>
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; color: #475569;">
                                                    {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600;">
                                                    {{ number_format((float) $item->pesan_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: #059669;">
                                                    {{ number_format((float) $item->terima_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: {{ (float) $item->sisa_qty > 0 ? '#b45309' : '#64748b' }};">
                                                    {{ number_format((float) $item->sisa_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; color: #475569;">
                                                    Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: #0f172a;">
                                                    Rp {{ number_format((float) $item->subtotal_nominal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada transaksi Purchase Order. Klik tombol <strong>"Buat PO Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($poList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $poList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL CEPAT CATAT BARANG MASUK (IN-PLACE QUICK RECEIVE) --}}
<div id="modalQuickReceiveIndex" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 10px; width: 100%; max-width: 660px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="color: #0f172a; font-size: 1.05rem;" id="modalPoTitle">Catat Barang Masuk</strong>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;" id="modalPoSubtitle">
                    -
                </div>
            </div>
            <button type="button" onclick="closeQuickReceiveIndexModal()" style="background: transparent; border: none; font-size: 1.35rem; color: #64748b; cursor: pointer; padding: 0 0.25rem;">&times;</button>
        </div>

        <form action="{{ route('gudang.terima.store') }}" method="POST" style="padding: 1.25rem; overflow-y: auto; flex: 1;" id="formQuickReceiveIndex">
            @csrf
            <input type="hidden" name="po_id" id="quick_po_id">
            <input type="hidden" name="supplier_id" id="quick_supplier_id">
            <input type="hidden" name="gudang_id" id="quick_gudang_id">
            <input type="hidden" name="redirect_to" value="po_index">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="quick_terima_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal Masuk <span style="color:#ef4444;">*</span></label>
                    <input type="date" id="quick_terima_tgl" name="terima_tgl" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="quick_suratjalan_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">No. Surat Jalan Supplier</label>
                    <input type="text" id="quick_suratjalan_no" name="suratjalan_no" placeholder="Contoh: SJ-2026/09/88" class="form-control">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0;">
                        Kuantitas Tiba Hari Ini:
                    </label>
                    <div style="display: flex; gap: 0.35rem;">
                        <button type="button" onclick="fillAllQuickSisa()" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                            Terima Semua Sisa
                        </button>
                        <button type="button" onclick="clearAllQuickInputs()" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
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
                        <tbody id="quickReceiveItemsBody">
                            {{-- Diisi secara dinamis via JavaScript --}}
                        </tbody>
                    </table>
                </div>
                <small style="color: #64748b; font-size: 0.75rem; margin-top: 0.35rem; display: block;">
                    * Masukkan kuantitas yang benar-benar tiba saat ini. Jika sebagian, sisa kuota akan tetap disimpan untuk kedatangan berikutnya.
                </small>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="quick_catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Penerimaan (Opsional)</label>
                <input type="text" id="quick_catatan_txt" name="catatan_txt" placeholder="Contoh: Pengiriman termin 1, supir Pak Joko truk AB 8122 AA" class="form-control">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <a href="#" id="linkFullForm" style="color: #0284c7; text-decoration: none; font-size: 0.825rem;">
                    Formulir Lengkap (Batch Manual &amp; QC) &rarr;
                </a>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" onclick="closeQuickReceiveIndexModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background:#059669;">
                        Simpan Penerimaan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle expandable item row tanpa pindah halaman
    function togglePoRow(rowId, btn) {
        const row = document.getElementById(rowId);
        if (!row) return;

        const isHidden = row.style.display === 'none' || row.style.display === '';
        row.style.display = isHidden ? 'table-row' : 'none';

        const icon = btn.querySelector('.chevron-icon');
        if (icon) {
            icon.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
        }
    }

    // Buka Modal Catat Terima Cepat di halaman Indeks
    function openQuickReceiveIndexModal(po) {
        document.getElementById('modalPoTitle').textContent = 'Catat Penerimaan: ' + po.po_no;
        document.getElementById('modalPoSubtitle').textContent = (po.supplier_nm || '-') + ' \u2022 ' + (po.gudang_nm || '-');
        
        document.getElementById('quick_po_id').value = po.po_id;
        document.getElementById('quick_supplier_id').value = po.supplier_id;
        document.getElementById('quick_gudang_id').value = po.gudang_id;
        document.getElementById('linkFullForm').href = "{{ route('gudang.terima.create') }}?po_id=" + po.po_id;

        const tbody = document.getElementById('quickReceiveItemsBody');
        tbody.innerHTML = '';

        let rowCount = 0;
        po.items.forEach((item, idx) => {
            if (item.sisa_qty > 0) {
                const tr = document.createElement('tr');
                tr.style.borderTop = '1px solid #f1f5f9';
                tr.innerHTML = `
                    <td style="padding: 0.6rem 0.75rem;">
                        <input type="hidden" name="items[${rowCount}][podtl_id]" value="${item.podtl_id}">
                        <input type="hidden" name="items[${rowCount}][barang_id]" value="${item.barang_id}">
                        <input type="hidden" name="items[${rowCount}][harga_nominal]" value="${item.harga_nominal}">
                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">${item.barang_nm}</strong>
                        <span style="font-size: 0.75rem; color: #64748b;">Satuan: ${item.satuan_nm}</span>
                    </td>
                    <td style="padding: 0.6rem 0.75rem; text-align: right; font-weight: 600; color: #b45309;">
                        ${Number(item.sisa_qty).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}
                    </td>
                    <td style="padding: 0.6rem 0.75rem; text-align: right;">
                        <input type="number" 
                               step="0.0001" 
                               min="0" 
                               max="${item.sisa_qty}" 
                               name="items[${rowCount}][terima_qty]" 
                               value="${item.sisa_qty}" 
                               data-sisa="${item.sisa_qty}"
                               class="form-control quick-index-input" 
                               style="text-align: right; font-weight: 700; width: 130px; display: inline-block; padding: 0.35rem 0.5rem; font-size: 0.85rem;" 
                               required>
                    </td>
                `;
                tbody.appendChild(tr);
                rowCount++;
            }
        });

        if (rowCount === 0) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:1.5rem; color:#94a3b8;">Seluruh item pesanan PO ini sudah diterima lengkap.</td></tr>';
        }

        const modal = document.getElementById('modalQuickReceiveIndex');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeQuickReceiveIndexModal() {
        const modal = document.getElementById('modalQuickReceiveIndex');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function fillAllQuickSisa() {
        document.querySelectorAll('.quick-index-input').forEach(input => {
            input.value = input.getAttribute('data-sisa') || 0;
        });
    }

    function clearAllQuickInputs() {
        document.querySelectorAll('.quick-index-input').forEach(input => {
            input.value = 0;
        });
    }

    // Tutup modal jika tombol ESC ditekan
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickReceiveIndexModal();
        }
    });
</script>
@endsection
