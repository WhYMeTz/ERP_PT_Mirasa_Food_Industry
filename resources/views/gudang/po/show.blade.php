@extends('layouts.app')

@section('title', 'Detail Purchase Order ' . $po->po_no . ' - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.po.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar PO
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $po->po_no }}</h1>
            @if ($po->status_cd == 'COMPLETED')
                <span class="badge badge-success">Selesai (Completed)</span>
            @elseif ($po->status_cd == 'PARTIAL')
                <span class="badge badge-info">Sebagian Diterima</span>
            @elseif ($po->status_cd == 'APPROVED')
                <span class="badge" style="background:#e0f2fe; color:#0369a1;">Siap Diterima (Approved)</span>
            @elseif ($po->status_cd == 'DRAFT')
                <span class="badge" style="background:#fef3c7; color:#92400e;">Draft</span>
            @else
                <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
            @endif
        </div>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        @if (in_array($po->status_cd, ['APPROVED', 'PARTIAL']))
            <a href="{{ route('gudang.terima.create', ['po_id' => $po->po_id]) }}" class="btn btn-primary" style="background:#059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terima Barang Fisik
            </a>
        @endif

        @if (in_array($po->status_cd, ['DRAFT', 'APPROVED']) && $po->details->sum('terima_qty') == 0)
            <form action="{{ route('gudang.po.cancel', $po->po_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan Purchase Order ini?')">
                @csrf
                <button type="submit" class="btn btn-danger">Batalkan PO</button>
            </form>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    {{-- KARTU INFO SUPPLIER & GUDANG --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Informasi Pengadaan & Mitra</strong>
        </div>
        <div style="padding: 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Supplier Mitra:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $po->supplier?->alamat_txt ?? '-' }}<br>
                    Telp: {{ $po->supplier?->kontak_no ?? '-' }}
                </p>
            </div>
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Gudang Tujuan Masuk:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $po->gudang?->gudang_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    Kode: {{ $po->gudang?->gudang_cd ?? '-' }}<br>
                    Lokasi: {{ $po->gudang?->alamat_txt ?? '-' }}
                </p>
            </div>
            @if ($po->catatan_txt)
                <div style="grid-column: 1 / -1; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.8rem; display: block;">Catatan Khusus:</span>
                    <p style="color: #334155; font-size: 0.875rem; margin-top: 0.25rem;">{{ $po->catatan_txt }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU RINGKASAN TOTAL --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Ringkasan Biaya</strong>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Tanggal PO:</span>
                <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($po->po_tgl)->format('d F Y') }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Total Item Barang:</span>
                <strong style="color: #0f172a;">{{ $po->details->count() }} Item</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                <span style="color: #0f172a; font-weight: 700; font-size: 1rem;">Grand Total:</span>
                <strong style="color: #0284c7; font-size: 1.25rem;">Rp {{ number_format((float) $po->total_nominal, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>
</div>

{{-- TABEL DETAIL BARANG --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="background: #f8fafc;">
        <strong style="color: #0f172a;">Rincian Barang yang Dipesan & Progres Penerimaan</strong>
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode & Nama Barang</th>
                    <th>Satuan</th>
                    <th style="text-align: right;">Kuantitas Pesan</th>
                    <th style="text-align: right;">Sudah Diterima</th>
                    <th style="text-align: right;">Sisa Belum Diterima</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($po->details as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: #64748b;">{{ $item->barang?->barang_cd }}</span>
                        </td>
                        <td>{{ $item->barang?->satuanDasar?->satuan_nm ?? '-' }}</td>
                        <td style="text-align: right; font-weight: 600;">
                            {{ number_format((float) $item->pesan_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #059669;">
                            {{ number_format((float) $item->terima_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: {{ (float) $item->sisa_qty > 0 ? '#d97706' : '#64748b' }};">
                            {{ number_format((float) $item->sisa_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;">
                            Rp {{ number_format((float) $item->subtotal_nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- RIWAYAT PENERIMAAN FISIK DENGAN PO INI --}}
@if ($po->penerimaan->isNotEmpty())
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Riwayat Penerimaan Fisik di Gudang (Good Receipts)</strong>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>No Penerimaan</th>
                        <th>Tanggal Terima</th>
                        <th>No Surat Jalan</th>
                        <th>Gudang Masuk</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($po->penerimaan as $terima)
                        <tr>
                            <td>
                                <strong style="color: #0284c7;">{{ $terima->terima_no }}</strong>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($terima->terima_tgl)->format('d/m/Y') }}</td>
                            <td>{{ $terima->suratjalan_no ?? '-' }}</td>
                            <td>{{ $terima->gudang?->gudang_nm }}</td>
                            <td><span class="badge badge-success">Diterima & Disimpan</span></td>
                            <td style="text-align: right;">
                                <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn btn-secondary btn-sm">
                                    Lihat GRN
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
