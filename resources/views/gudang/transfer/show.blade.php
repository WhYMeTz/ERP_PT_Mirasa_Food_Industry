@extends('layouts.app')

@section('title', 'Dokumen Transfer Antar-Gudang - ' . $transfer->transfer_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/transfer/transfer-show.css') }}">
@endpush

@section('content')
<div class="transfer-show-container">
    {{-- TOP ACTION BAR (NO-PRINT) --}}
    <div class="transfer-doc-header no-print">
        <div>
            <a href="{{ route('gudang.transfer.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.8125rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Daftar Transfer</span>
            </a>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; font-family: monospace;">
                    {{ $transfer->transfer_no }}
                </h1>
                @if($transfer->isPosted())
                    <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        POSTED (Stok Termutasi)
                    </span>
                @else
                    <span style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        VOID (Dibatalkan)
                    </span>
                @endif
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button type="button" onclick="window.print()" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Surat Jalan</span>
            </button>

            @if($transfer->isPosted())
                <button type="button" onclick="openVoidTransferModal({{ $transfer->transfer_id }}, '{{ $transfer->transfer_no }}')" class="btn btn-danger" style="font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 0.35rem; background: #dc2626; border-color: #b91c1c; color: #ffffff;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Batalkan (VOID)</span>
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="no-print" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- KERTAS DOKUMEN SURAT JALAN & TRANSFER --}}
    <div class="transfer-doc-card">
        {{-- KOP PERUSAHAAN --}}
        <div class="doc-kop-surat">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
                <p style="font-size: 0.775rem; color: #475569; margin: 0.2rem 0 0 0;">
                    Pabrik Pengolahan F&amp;B Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
                </p>
            </div>
            <div style="text-align: right;">
                <strong style="font-size: 1.1rem; color: #0d9488; display: block; text-transform: uppercase;">
                    SURAT JALAN TRANSFER ANTAR-GUDANG
                </strong>
                <span style="font-family: monospace; font-size: 0.95rem; font-weight: 800; color: #0f172a;">
                    {{ $transfer->transfer_no }}
                </span>
            </div>
        </div>

        {{-- METADATA DOKUMEN --}}
        <div class="doc-meta-grid">
            <table class="doc-meta-table">
                <tr>
                    <td class="doc-meta-label">Tanggal Kirim:</td>
                    <td class="doc-meta-val">{{ Carbon\Carbon::parse($transfer->transfer_tgl)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                </tr>
                <tr>
                    <td class="doc-meta-label">Gudang Asal:</td>
                    <td class="doc-meta-val" style="color: #dc2626;">
                        {{ $transfer->gudangAsal?->gudang_nm }} ({{ $transfer->gudangAsal?->gudang_cd }})
                    </td>
                </tr>
                <tr>
                    <td class="doc-meta-label">Gudang Tujuan:</td>
                    <td class="doc-meta-val" style="color: #0284c7;">
                        {{ $transfer->gudangTujuan?->gudang_nm }} ({{ $transfer->gudangTujuan?->gudang_cd }})
                    </td>
                </tr>
                <tr>
                    <td class="doc-meta-label">Petugas Input:</td>
                    <td class="doc-meta-val">{{ $transfer->created_by }}</td>
                </tr>
            </table>

            <table class="doc-meta-table">
                <tr>
                    <td class="doc-meta-label">No. Surat Jalan:</td>
                    <td class="doc-meta-val" style="font-family: monospace;">{{ $transfer->no_surat_jalan ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="doc-meta-label">Nama Pengemudi:</td>
                    <td class="doc-meta-val">{{ $transfer->sopir_nama ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="doc-meta-label">Nopol Kendaraan:</td>
                    <td class="doc-meta-val" style="font-family: monospace;">{{ $transfer->nopol_kendaraan ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="doc-meta-label">Catatan:</td>
                    <td class="doc-meta-val">{{ $transfer->catatan_txt ?: '-' }}</td>
                </tr>
            </table>
        </div>

        {{-- TABEL RINCIAN BARANG --}}
        <table class="doc-items-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th style="width: 15%;">Kode Barang</th>
                    <th style="width: 35%;">Nama Barang</th>
                    <th style="width: 18%;">Nomor Batch</th>
                    <th style="width: 12%; text-align: center;">Grade</th>
                    <th style="width: 15%; text-align: right;">Kuantitas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfer->details as $idx => $dtl)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="font-family: monospace; font-weight: 700;">{{ $dtl->barang?->barang_cd ?? '-' }}</td>
                        <td style="font-weight: 600;">
                            {{ $dtl->barang?->barang_nm ?? '-' }}
                            @if($dtl->catatan_txt)
                                <div style="font-size: 0.725rem; color: #64748b; font-weight: normal;">
                                    Catatan: {{ $dtl->catatan_txt }}
                                </div>
                            @endif
                        </td>
                        <td style="font-family: monospace; color: #0d9488; font-weight: 700;">{{ $dtl->batch_no }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $dtl->grade_cd }}</td>
                        <td style="text-align: right; font-weight: 800; font-family: monospace;">
                            {{ number_format($dtl->qty_transfer, 2, ',', '.') }}
                            <span style="font-size: 0.75rem; font-weight: normal; color: #64748b;">
                                {{ $dtl->barang?->satuanDasar?->satuan_nm ?? 'Unit' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 800;">
                    <td colspan="5" style="text-align: right; padding: 0.75rem 1rem;">TOTAL BARANG DIKIRIM:</td>
                    <td style="text-align: right; padding: 0.75rem 1rem; font-family: monospace; font-size: 1rem; color: #0d9488;">
                        {{ number_format($transfer->total_qty, 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- KOLOM TANDA TANGAN (SURAT JALAN RESMI) --}}
        <div class="doc-sign-grid">
            <div>
                <span style="font-size: 0.75rem; color: #64748b;">Gudang Pengirim (Asal),</span>
                <div class="doc-sign-box">
                    ( {{ $transfer->created_by }} )
                </div>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: #64748b;">Sopir / Pengemudi Armada,</span>
                <div class="doc-sign-box">
                    ( {{ $transfer->sopir_nama ?: '..............................' }} )
                </div>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: #64748b;">Gudang Penerima (Tujuan),</span>
                <div class="doc-sign-box">
                    ( .............................. )
                </div>
            </div>
        </div>
    </div>
</div>

@include('gudang.transfer.partials.modal-void')
@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/transfer/transfer-index.js') }}"></script>
@endpush
