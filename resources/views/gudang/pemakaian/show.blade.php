@extends('layouts.app')

@section('title', 'Detail Dokumen Pemakaian Bahan ' . $pakai->pakai_no . ' - ERP PT Mirasa')

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

{{-- PRINT-ONLY HEADER --}}
<div class="print-only" style="margin-bottom: 1.5rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.75rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
            <p style="font-size: 0.75rem; color: #475569; margin: 0.2rem 0 0 0;">
                Pabrik Pengolahan F&B Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
            </p>
        </div>
        <div style="text-align: right;">
            <strong style="font-size: 1.1rem; color: #b91c1c; display: block;">BUKTI PEMAKAIAN BAHAN (OUTBOUND)</strong>
            <span style="font-family: monospace; font-size: 0.9rem; font-weight: 700;">{{ $pakai->pakai_no }}</span>
        </div>
    </div>
</div>

<div class="no-print" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.pemakaian.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem;">
            &larr; Kembali ke Daftar Pemakaian Bahan
        </a>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <span>{{ $pakai->pakai_no }}</span>
            <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.85rem;">{{ $pakai->tujuan_pemakaian }}</span>
            @if($pakai->isDraft())
                <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.8rem; border: 1px solid #fde68a; font-weight: 700; padding: 0.25rem 0.65rem;">
                    ⏳ DRAFT (Menunggu Verifikasi)
                </span>
            @else
                <span class="badge" style="background: #d1fae5; color: #065f46; font-size: 0.8rem; border: 1px solid #a7f3d0; font-weight: 700; padding: 0.25rem 0.65rem;">
                    ✓ POSTED (Terverifikasi)
                </span>
            @endif
        </h1>
        <p style="color: #64748b; font-size: 0.875rem;">
            Dokumen pengeluaran bahan dari {{ $pakai->gudang?->gudang_nm }} pada {{ \Carbon\Carbon::parse($pakai->pakai_tgl)->format('d F Y') }}.
        </p>
        @if($pakai->produksi)
            <div style="margin-top: 0.5rem; display: inline-flex; align-items: center; gap: 0.5rem; background: #f0fdf4; border: 1px solid #a7f3d0; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem;">
                <span style="color: #065f46; font-weight: 600;">Diproses dalam Lembar Produksi Harian:</span>
                <a href="{{ route('produksi.show', $pakai->produksi->produksi_id) }}" style="color: #059669; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <span>{{ $pakai->produksi->produksi_no }} ({{ Carbon\Carbon::parse($pakai->produksi->produksi_tgl)->format('d/m/Y') }})</span> &rarr;
                </a>
            </div>
        @endif
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        @if($pakai->isDraft())
            @if(Auth::user()?->canEditPemakaian())
                <a href="{{ route('gudang.pemakaian.edit', $pakai->pakai_id) }}" class="btn btn-secondary" style="font-weight: 700; color: #0284c7; border: 1.5px solid #bae6fd; background: #f0f9ff; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Dokumen
                </a>
            @endif
            @if(Auth::user()?->canVerifyPemakaian())
                <button type="button" onclick="openModalPostingPemakaian()" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1.15rem; box-shadow: 0 2px 4px rgba(5,150,105,0.25);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verifikasi &amp; Terbitkan Jurnal
                </button>
            @endif
            @if(Auth::user()?->canDeletePemakaian())
                <button type="button" onclick="openDeletePemakaianModal({{ $pakai->pakai_id }}, '{{ $pakai->pakai_no }}', '{{ addslashes($pakai->tujuan_pemakaian ?? '') }}')" class="btn btn-secondary" style="font-weight: 700; color: #dc2626; border: 1.5px solid #fecaca; background: #fef2f2; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            @endif
        @else
            @if($pakai->jurnal_id)
                <button type="button" onclick="openModalDetailJurnal({{ $pakai->jurnal_id }})" class="btn btn-secondary" style="font-weight: 700; color: #065f46; border: 1.5px solid #a7f3d0; background: #f0fdf4; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Lihat Jurnal Umum
                </button>
            @endif
        @endif
        <a href="{{ route('gudang.pemakaian.export-pdf', $pakai->pakai_id) }}" target="_blank" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Cetak PDF
        </a>
        <button onclick="window.print()" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print
        </button>
        @if(Auth::user()?->canCreatePemakaian())
            <a href="{{ route('gudang.pemakaian.create') }}" class="btn btn-primary" style="background: #dc2626;">
                + Pengeluaran Baru
            </a>
        @endif
    </div>
</div>

{{-- BANNER STATUS VERIFIKASI OPERASIONAL --}}
<div class="no-print">
    @if($pakai->isDraft())
        <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0; border: 1px solid #fde68a;">
                    ⏳
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #92400e;">Status Dokumen: DRAFT (Menunggu Verifikasi &amp; Posting)</h4>
                    <p style="margin: 0.2rem 0 0; font-size: 0.825rem; color: #b45309; line-height: 1.45;">
                        Dokumen ini dicatat oleh petugas gudang sebagai bukti pengeluaran fisik. <strong>Stok belum dipotong</strong> dan <strong>Jurnal Umum belum terbit</strong>. Office / Admin Produksi akan memvalidasi sebelum stok terpotong secara sistem.
                    </p>
                </div>
            </div>
            @if(Auth::user()?->canVerifyPemakaian())
                <button type="button" onclick="openModalPostingPemakaian()" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1.15rem; flex-shrink: 0;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verifikasi &amp; Terbitkan Jurnal
                </button>
            @endif
        </div>
    @else
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #d1fae5; color: #065f46; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                    ✓
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.925rem; font-weight: 700; color: #065f46;">Dokumen Telah Terverifikasi &amp; Terposting</h4>
                    <p style="margin: 0.15rem 0 0; font-size: 0.8rem; color: #047857;">
                        Diverifikasi pada <strong>{{ $pakai->posted_at ? $pakai->posted_at->format('d/m/Y H:i') : '-' }}</strong> oleh <strong>{{ $pakai->posted_by ?? 'SYSTEM' }}</strong>. Stok fisik gudang telah dipotong dan Jurnal Umum telah diterbitkan.
                    </p>
                </div>
            </div>
            @if($pakai->jurnal_id)
                <button type="button" onclick="openModalDetailJurnal({{ $pakai->jurnal_id }})" class="btn btn-secondary btn-sm" style="font-weight: 700; background: #ffffff; color: #065f46; border: 1.5px solid #a7f3d0; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Lihat Jurnal Umum
                </button>
            @endif
        </div>
    @endif
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Rincian Pengeluaran Bahan</h2>
        <span style="font-size: 0.85rem; color: #64748b;">Gudang: <strong>{{ $pakai->gudang?->gudang_nm }}</strong></span>
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="min-width: 140px;">Kode Batch</th>
                    <th style="min-width: 110px;">Kode Barang</th>
                    <th style="min-width: 180px;">Nama Barang</th>
                    <th style="min-width: 80px;">Jenis</th>
                    <th style="min-width: 150px;">Keterangan / SPK</th>
                    <th style="min-width: 120px; text-align: right;">Qty Keluar</th>
                    <th style="min-width: 80px;">Satuan</th>
                    <th style="min-width: 120px; text-align: right;">Sisa Barang</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $grandQty = 0; 
                @endphp
                @foreach ($pakai->details as $index => $dtl)
                    @php
                        $grandQty += (float) $dtl->qty_keluar;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                <span style="font-family: monospace; font-size: 0.85rem; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700;">
                                    {{ $dtl->batch_no }}
                                </span>
                                @if(!empty($dtl->grade_cd))
                                    @php
                                        $gColor = ($dtl->grade_cd === 'A') ? '#059669' : (($dtl->grade_cd === 'B') ? '#d97706' : '#dc2626');
                                        $gBg = ($dtl->grade_cd === 'A') ? '#ecfdf5' : (($dtl->grade_cd === 'B') ? '#fffbeb' : '#fef2f2');
                                        $gBorder = ($dtl->grade_cd === 'A') ? '#a7f3d0' : (($dtl->grade_cd === 'B') ? '#fde68a' : '#fecaca');
                                    @endphp
                                    <span style="font-size: 0.725rem; font-weight: 700; color: {{ $gColor }}; background: {{ $gBg }}; border: 1px solid {{ $gBorder }}; padding: 0.15rem 0.4rem; border-radius: 4px;">
                                        Grade {{ $dtl->grade_cd }}
                                    </span>
                                @endif
                            </div>
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
                        <td style="text-align: right; font-weight: 700; color: #dc2626; font-size: 0.95rem;">
                            {{ number_format((float) $dtl->qty_keluar, 2, ',', '.') }}
                        </td>
                        <td style="color: #64748b;">
                            {{ $dtl->barang?->satuanDasar?->satuan_nm }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: {{ ($dtl->sisa_gudang_qty ?? 0) > 0 ? '#059669' : '#dc2626' }};">
                            {{ number_format((float) ($dtl->sisa_gudang_qty ?? 0), 2, ',', '.') }}
                            @if (!empty($dtl->batch_no) && isset($dtl->sisa_batch_qty))
                                <div style="font-size: 0.725rem; color: #64748b; font-weight: normal;">
                                    Batch: {{ number_format((float) $dtl->sisa_batch_qty, 2, ',', '.') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #e2e8f0;">
                    <td colspan="6" style="text-align: right; padding: 0.85rem 1rem;">Total Akumulasi Fisik:</td>
                    <td style="text-align: right; color: #047857; font-size: 1.1rem; padding: 0.85rem 1rem; font-family: monospace;">
                        {{ number_format($grandQty, 2, ',', '.') }}
                    </td>
                    <td colspan="2" style="color: #64748b; font-size: 0.8rem; vertical-align: middle;">Total Kuantitas Fisik Keluar</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- TANDA TANGAN BUKTI PENGELUARAN (BPPB) --}}
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 2rem; text-align: center; font-size: 0.875rem;">
    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Diserahkan Oleh:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">Petugas Gudang</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanggal</span>
    </div>

    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Diterima Oleh:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">Mandor / Staf Produksi</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanggal</span>
    </div>

    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Mengetahui:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">Kepala Pabrik / Gudang</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanda Tangan</span>
    </div>
</div>

{{-- MODALS PARTIALS (DRAFT VERIFICATION, DELETE CONFIRM & JOURNAL PREVIEW) --}}
@include('gudang.pemakaian.partials.modal-posting')
@include('gudang.pemakaian.partials.modal-delete-confirm')
@include('keuangan.jurnal.partials.modal-detail-jurnal')
@endsection
