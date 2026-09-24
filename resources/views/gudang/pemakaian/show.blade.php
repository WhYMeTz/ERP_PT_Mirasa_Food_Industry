@extends('layouts.app')

@section('title', 'Detail Dokumen Pengeluaran ' . $pakai->pakai_no . ' - ERP PT Mirasa')

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.pemakaian.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem;">
            &larr; Kembali ke Daftar Barang Keluar
        </a>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <span>{{ $pakai->pakai_no }}</span>
            <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.85rem;">{{ $pakai->tujuan_pemakaian }}</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem;">
            Dokumen pengeluaran fisik barang dari {{ $pakai->gudang?->gudang_nm }} pada {{ \Carbon\Carbon::parse($pakai->pakai_tgl)->format('d F Y') }}.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button onclick="window.print()" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Dokumen
        </button>
        <a href="{{ route('gudang.pemakaian.create') }}" class="btn btn-primary" style="background: #dc2626;">
            + Pengeluaran Baru
        </a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Rincian Pengeluaran Bahan</h2>
        <span style="font-size: 0.85rem; color: #64748b;">Gudang: <strong>{{ $pakai->gudang?->gudang_nm }}</strong></span>
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr style="background: #0f394c; color: #ffffff;">
                    <th style="width: 40px; color: #e2e8f0;">No</th>
                    <th style="color: #e2e8f0;">Kode Batch</th>
                    <th style="color: #e2e8f0;">Kode Barang</th>
                    <th style="color: #e2e8f0;">Nama Barang</th>
                    <th style="color: #e2e8f0;">Jenis</th>
                    <th style="color: #e2e8f0;">Keterangan / SPK</th>
                    <th style="text-align: right; color: #e2e8f0;">Qty Keluar</th>
                    <th style="color: #e2e8f0;">Satuan</th>
                    <th style="text-align: right; color: #e2e8f0;">Harga Satuan</th>
                    <th style="text-align: right; color: #e2e8f0;">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $grandQty = 0; 
                    $grandNilai = 0; 
                @endphp
                @foreach ($pakai->details as $index => $dtl)
                    @php
                        $grandQty += (float) $dtl->qty_keluar;
                        $grandNilai += (float) $dtl->total_harga;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.85rem; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700;">
                                {{ $dtl->batch_no }}
                            </span>
                        </td>
                        <td style="font-family: monospace; font-weight: 600; color: #475569;">
                            {{ $dtl->barang?->barang_cd }}
                        </td>
                        <td style="font-weight: 600; color: #0f172a;">
                            {{ $dtl->barang?->barang_nm }}
                        </td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.75rem;">
                                {{ $dtl->barang?->jenisBarang?->jenis_barang_cd ?? '-' }}
                            </span>
                        </td>
                        <td>
                            {{ $dtl->keterangan_txt ?? $pakai->tujuan_pemakaian }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #dc2626;">
                            {{ number_format((float) $dtl->qty_keluar, 2, ',', '.') }}
                        </td>
                        <td style="color: #64748b;">
                            {{ $dtl->barang?->satuanDasar?->satuan_nm }}
                        </td>
                        <td style="text-align: right; color: #334155;">
                            Rp {{ number_format((float) $dtl->harga_satuan, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;">
                            Rp {{ number_format((float) $dtl->total_harga, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #e2e8f0;">
                    <td colspan="6" style="text-align: right; padding: 0.85rem 1rem;">Total Keseluruhan:</td>
                    <td style="text-align: right; color: #dc2626; font-size: 1rem; padding: 0.85rem 1rem;">
                        {{ number_format($grandQty, 2, ',', '.') }}
                    </td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right; color: #0f172a; font-size: 1.05rem; padding: 0.85rem 1rem;">
                        Rp {{ number_format($grandNilai, 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
