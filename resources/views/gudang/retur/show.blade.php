@extends('layouts.app')

@section('title', 'Surat Jalan Retur ' . $retur->retur_no . ' - ERP PT Mirasa')

@section('content')
<style>
    @media print {
        header, nav, .btn, .no-print, .alert, footer {
            display: none !important;
        }
        body, main {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #000000 !important;
            break-inside: avoid;
        }
        .print-only {
            display: block !important;
        }
        table th, table td {
            border: 1px solid #cbd5e1 !important;
            color: #000000 !important;
        }
    }
    .print-only {
        display: none;
    }
</style>

{{-- KOP SURAT CETAK (PRINT-ONLY) --}}
<div class="print-only" style="margin-bottom: 1.5rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.75rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
            <p style="font-size: 0.75rem; color: #475569; margin: 0.2rem 0 0 0;">
                Pabrik Pengolahan F&B Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
            </p>
        </div>
        <div style="text-align: right;">
            <strong style="font-size: 1.1rem; color: #dc2626; display: block;">SURAT JALAN PENGEMBALIAN BARANG (RETUR)</strong>
            <span style="font-family: monospace; font-size: 0.95rem; font-weight: 700;">{{ $retur->retur_no }}</span>
        </div>
    </div>
</div>

{{-- HEADER WEB VIEW --}}
<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.retur.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Retur
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $retur->retur_no }}</h1>
            <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700;">
                Pengeluaran Retur Gudang
            </span>
            @if ($retur->tindakan_cd === 'REPLACE')
                <span class="badge" style="background: #d1fae5; color: #047857; font-weight: 700;">
                    🔄 Ganti Barang Baru
                </span>
            @else
                <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700;">
                    💰 Potong Tagihan
                </span>
            @endif
        </div>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button onclick="window.print()" class="btn btn-secondary" style="font-weight: 600;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Surat Jalan Retur
        </button>
        @if (Auth::user()?->canCreateRetur())
            <a href="{{ route('gudang.retur.create') }}" class="btn btn-primary" style="background: #dc2626;">
                + Buat Retur Baru
            </a>
        @endif
    </div>
</div>

{{-- INFORMASI SUPPLIER & KESEPAKATAN RETUR --}}
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div style="font-weight: 700; color: #0f172a; padding: 0.75rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            Detail Pihak Supplier &amp; Gudang Asal
        </div>
        <div style="padding: 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Mitra Supplier Tujuan:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $retur->supplier?->supplier_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.825rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Kode: {{ $retur->supplier?->supplier_cd ?? '-' }}<br>
                    Alamat: {{ $retur->supplier?->alamat_txt ?? '-' }}<br>
                    Kontak: {{ $retur->supplier?->kontak_no ?? '-' }}
                </p>
            </div>
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Gudang Asal Pengeluaran:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $retur->gudang?->gudang_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.825rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Kode Gudang: {{ $retur->gudang?->gudang_cd ?? '-' }}<br>
                    Tanggal Keluar: <strong>{{ $retur->retur_tgl ? $retur->retur_tgl->format('d F Y') : '-' }}</strong>
                </p>
            </div>

            @if ($retur->alasan_txt)
                <div style="grid-column: 1 / -1; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.8rem; display: block;">Catatan / Alasan Retur:</span>
                    <p style="color: #991b1b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0; font-weight: 500;">
                        ⚠️ {{ $retur->alasan_txt }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU STATUS REFERENSI & KESEPAKATAN --}}
    <div class="card">
        <div style="font-weight: 700; color: #0f172a; padding: 0.75rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            Status &amp; Tindakan Penanganan
        </div>
        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <div>
                <span style="color: #64748b; font-size: 0.775rem; display: block;">Referensi PO:</span>
                @if ($retur->po)
                    <a href="{{ route('gudang.po.show', $retur->po_id) }}" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.95rem;">
                        {{ $retur->po->po_no }}
                    </a>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        Status PO saat ini: <span class="badge" style="background: #e0f2fe; color: #0369a1;">{{ $retur->po->status_cd }}</span>
                    </div>
                @else
                    <span style="color: #475569; font-weight: 600; font-size: 0.85rem;">Non-PO (Pembelian Langsung / Bebas)</span>
                @endif
            </div>

            @if ($retur->suratjalan_supplier_no)
                <div>
                    <span style="color: #64748b; font-size: 0.775rem; display: block;">No SJ Asal Supplier:</span>
                    <strong style="color: #0f172a; font-size: 0.875rem;">{{ $retur->suratjalan_supplier_no }}</strong>
                </div>
            @endif

            <div style="padding-top: 0.5rem; border-top: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-size: 0.775rem; display: block;">Kesepakatan Retur:</span>
                @if ($retur->tindakan_cd === 'REPLACE')
                    <div style="color: #047857; font-weight: 700; font-size: 0.875rem; margin-top: 0.2rem;">
                        🔄 Supplier Mengirim Ulang (Replace)
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem; margin-bottom: 0;">
                        Kuota PO telah dibuka kembali. Barang pengganti dapat diterima lewat form Barang Masuk.
                    </p>
                @else
                    <div style="color: #b45309; font-weight: 700; font-size: 0.875rem; margin-top: 0.2rem;">
                        💰 Potong Tagihan Pembayaran
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem; margin-bottom: 0;">
                        Nilai retur ini memotong kewajiban pembayaran faktur supplier.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- TABEL RINCIAN ITEM RETUR --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 0; overflow: hidden;">
    <div style="padding: 0.75rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a;">
        Rincian Barang yang Dikeluarkan &amp; Dikembalikan ke Supplier
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama Barang &amp; Kode</th>
                    <th style="width: 170px;">Nomor Batch</th>
                    <th style="width: 140px; text-align: right;">Qty Retur</th>
                    <th style="width: 100px;">Satuan</th>
                    <th style="width: 140px; text-align: right;">Harga Satuan</th>
                    <th style="width: 150px; text-align: right;">Subtotal Nilai</th>
                    <th style="min-width: 200px;">Alasan Kerusakan / Reject</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($retur->details as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.9rem;">{{ $item->barang?->barang_nm ?? '-' }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;">{{ $item->barang?->barang_cd ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #0f172a; font-family: monospace; font-weight: 700; font-size: 0.8rem;">
                                {{ $item->batch_no }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 700; font-size: 0.95rem; color: #dc2626;">
                            {{ number_format((float) $item->retur_qty, 2, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge" style="background: #e2e8f0; color: #334155;">
                                {{ $item->barang?->satuanDasar?->satuan_nm ?? '-' }}
                            </span>
                        </td>
                        <td style="text-align: right; color: #475569; font-size: 0.85rem;">
                            Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                            Rp {{ number_format((float) $item->subtotal_nominal, 0, ',', '.') }}
                        </td>
                        <td>
                            @if ($item->alasan_reject)
                                <div style="color: #b91c1c; font-weight: 600; font-size: 0.825rem;">
                                    ⚠️ {{ $item->alasan_reject }}
                                </div>
                            @endif
                            @if ($item->catatan_txt)
                                <div style="color: #64748b; font-size: 0.75rem; margin-top: 2px;">
                                    {{ $item->catatan_txt }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 700;">
                    <td colspan="6" style="text-align: right; padding: 0.85rem 1.25rem; font-size: 0.9rem; color: #334155;">
                        TOTAL NILAI RETUR:
                    </td>
                    <td style="text-align: right; padding: 0.85rem 1.25rem; font-size: 1.15rem; color: #dc2626;">
                        Rp {{ number_format((float) $retur->total_nominal, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- AREA TANDA TANGAN (CETAK & ARSIP RESMI) --}}
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-top: 1.5rem; break-inside: avoid;">
    <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem; text-align: center;">
        Dokumen ini sah sebagai tanda pengembalian fisik barang cacat/rusak dari Gudang PT. Mirasa Food Industry kepada Supplier.
    </div>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; gap: 1.5rem;">
        <div>
            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 3.5rem;">
                Yang Mengeluarkan (Gudang/QC)
            </div>
            <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 0.25rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                ( {{ $retur->created_by ?? 'Petugas Gudang' }} )
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 3.5rem;">
                Yang Membawa / Ekspedisi Supplier
            </div>
            <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 0.25rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                ( Sopir / Driver Supplier )
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 3.5rem;">
                Mengetahui (Purchasing / Manager)
            </div>
            <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 0.25rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                ( Kepala Bagian )
            </div>
        </div>
    </div>
</div>
@endsection
