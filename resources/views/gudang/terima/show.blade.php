@extends('layouts.app')

@section('title', 'Detail Penerimaan Barang ' . $terima->terima_no . ' - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.terima.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Penerimaan
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $terima->terima_no }}</h1>
            <span class="badge badge-success">Stok Masuk Gudang (GRN)</span>
        </div>
    </div>
    <div>
        <a href="{{ route('gudang.stok.index', ['gudang_id' => $terima->gudang_id]) }}" class="btn btn-secondary">
            Lihat Stok di Gudang Ini &rarr;
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    {{-- KARTU INFO PENGIRIM & GUDANG --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Informasi Dokumen & Pengirim</strong>
        </div>
        <div style="padding: 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Supplier Pengirim:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $terima->supplier?->supplier_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $terima->supplier?->alamat_txt ?? '-' }}<br>
                    Kontak: {{ $terima->supplier?->kontak_no ?? '-' }}
                </p>
            </div>
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Gudang Penyimpanan Fisik:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $terima->gudang?->gudang_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    Kode Gudang: {{ $terima->gudang?->gudang_cd ?? '-' }}<br>
                    Lokasi: {{ $terima->gudang?->alamat_txt ?? '-' }}
                </p>
            </div>
            @if ($terima->catatan_txt)
                <div style="grid-column: 1 / -1; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.8rem; display: block;">Catatan Fisik Penerimaan:</span>
                    <p style="color: #334155; font-size: 0.875rem; margin-top: 0.25rem;">{{ $terima->catatan_txt }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU INFO REFERENSI --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Referensi Dokumen</strong>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Tanggal Terima:</span>
                <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($terima->terima_tgl)->format('d F Y') }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Surat Jalan:</span>
                <strong style="color: #0f172a;">{{ $terima->suratjalan_no ?? '-' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-size: 0.875rem;">Referensi PO:</span>
                @if ($terima->po)
                    <a href="{{ route('gudang.po.show', $terima->po_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;">
                        {{ $terima->po->po_no }} &rarr;
                    </a>
                @else
                    <span style="color: #94a3b8; font-style: italic;">Non-PO</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- TABEL BARANG DITERIMA DENGAN NOMOR BATCH --}}
<div class="card">
    <div class="card-header" style="background: #f8fafc;">
        <strong style="color: #0f172a;">Rincian Barang, Alokasi Nomor Batch & Tanggal Kadaluarsa</strong>
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Barang / Komoditas</th>
                    <th>Nomor Batch / Lot</th>
                    <th>Tgl Kadaluarsa</th>
                    <th style="text-align: right;">Kuantitas Diterima</th>
                    <th>Satuan</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($terima->details as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: #64748b;">Kode: {{ $item->barang?->barang_cd }}</span>
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.875rem; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; color: #0284c7;">
                                {{ $item->batch_no }}
                            </span>
                        </td>
                        <td>
                            @if ($item->expired_tgl)
                                <span style="color: #0f172a; font-weight: 600;">{{ \Carbon\Carbon::parse($item->expired_tgl)->format('d/m/Y') }}</span>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 700; font-size: 1rem; color: #059669;">
                            + {{ number_format((float) $item->terima_qty, 2, ',', '.') }}
                        </td>
                        <td>{{ $item->barang?->satuanDasar?->satuan_nm ?? '-' }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $terima->gudang_id]) }}" class="btn btn-secondary btn-sm" title="Lihat Kartu Stok Barang Ini">
                                Kartu Stok &rarr;
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
