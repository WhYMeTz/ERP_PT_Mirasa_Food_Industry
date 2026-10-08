@extends('layouts.app')

@section('title', 'Dokumen Adjustment: ' . $header->adj_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-form.css') }}">
    <style>
        @media print {
            .adj-header-actions, .navbar, .pill-nav-container, .btn-action-trigger, .no-print {
                display: none !important;
            }
            .adj-form-card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="adj-container" style="max-width: 1400px;">

    {{-- Breadcrumb / Back Link --}}
    <div style="margin-bottom: 1rem;" class="no-print">
        <a href="{{ route('gudang.adjustment.index') }}" style="display: inline-flex; align-items: center; gap: 0.35rem; color: #64748b; font-size: 0.85rem; text-decoration: none; font-weight: 500;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Adjustment
        </a>
    </div>

    {{-- Header Dokumen --}}
    <div class="adj-header-wrap">
        <div class="adj-title-area">
            <h1>
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Dokumen Adjustment: {{ $header->adj_no }}
            </h1>
            <p class="adj-subtitle">
                Tanggal: <strong>{{ $header->adj_tgl ? $header->adj_tgl->format('d/m/Y') : '-' }}</strong> | 
                Gudang: <strong>{{ $header->gudang->gudang_nm ?? '-' }}</strong> | 
                Status: 
                @if($header->status_cd === 'POSTED')
                    <span class="badge-adj-status posted">DIPOSTING (AKTIF)</span>
                @else
                    <span class="badge-adj-status void">DIBATALKAN (VOID)</span>
                @endif
            </p>
        </div>
        <div class="adj-header-actions no-print">
            <button type="button" onclick="window.print()" class="btn-adj-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Berita Acara
            </button>
            @if($header->status_cd !== 'VOID')
                <button type="button" onclick="openVoidModal({{ $header->adj_id }}, '{{ $header->adj_no }}')" class="btn-adj-secondary" style="color: #dc2626; border-color: #fca5a5;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Batalkan Dokumen
                </button>
            @endif
        </div>
    </div>

    {{-- Detail Informasi --}}
    <div class="adj-form-card">
        <h3 class="adj-section-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Ringkasan Berita Acara
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">NOMOR DOKUMEN:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 700; color: #0284c7; font-family: monospace;">{{ $header->adj_no }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">TANGGAL PELAKSANAAN:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #1e293b;">{{ $header->adj_tgl ? $header->adj_tgl->format('d F Y') : '-' }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">GUDANG LOKASI:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #1e293b;">{{ $header->gudang->gudang_nm ?? '-' }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">KATEGORI:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #1e293b;">{{ str_replace('_', ' ', $header->kategori_adj) }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">PETUGAS PENCATAT:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #1e293b;">{{ $header->created_by ?? '-' }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">CATATAN UMUM:</span>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #475569;">{{ $header->catatan_txt ?: '-' }}</p>
            </div>
        </div>

        {{-- Tabel Detail Barang Sesuai Blueprint --}}
        <div class="adj-items-table-wrap">
            <table class="adj-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="vertical-align: middle;">No</th>
                        <th rowspan="2" style="vertical-align: middle;">b. Kode Barang</th>
                        <th rowspan="2" style="vertical-align: middle;">c. Nama Barang</th>
                        <th colspan="3" class="bg-group-awal">DATA SISTEM (SEBELUM OPNAME)</th>
                        <th colspan="2" class="bg-group-fisik">FISIK GUDANG (HASIL RIIL)</th>
                        <th colspan="3" class="bg-group-selisih">KOREKSI SELISIH (ADJUSTMENT)</th>
                        <th rowspan="2" style="vertical-align: middle;">Alasan / Keterangan</th>
                    </tr>
                    <tr>
                        <th class="th-num bg-group-awal">d. Stok Awal</th>
                        <th class="th-num bg-group-awal">e. @Harga</th>
                        <th class="th-num bg-group-awal">f. Total Awal</th>

                        <th class="th-num bg-group-fisik">g. Stok Fisik</th>
                        <th class="th-num bg-group-fisik">i. Total Fisik</th>

                        <th class="th-num bg-group-selisih">j. Selisih Qty</th>
                        <th class="th-num bg-group-selisih">k. @Harga</th>
                        <th class="th-num bg-group-selisih">l. Total Selisih (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($header->details as $idx => $dtl)
                        @php
                            $barang = $dtl->barang;
                            $satuan = $barang->satuanDasar->satuan_cd ?? 'KG';
                            $selisihQty = (float) $dtl->selisih_qty;
                            $selisihNilai = (float) $dtl->total_selisih_nilai;
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td style="font-family: monospace; font-weight: 600;">{{ $barang->barang_cd ?? '-' }}</td>
                            <td>
                                <strong>{{ $barang->barang_nm ?? '-' }}</strong>
                                @if($dtl->batch_no)
                                    <span style="display: block; font-size: 0.7rem; color: #64748b; font-family: monospace;">Batch: {{ $dtl->batch_no }}</span>
                                @endif
                            </td>
                            <td class="td-num">{{ number_format((float) $dtl->stok_sistem_qty, 2, ',', '.') }} {{ $satuan }}</td>
                            <td class="td-num">Rp {{ number_format((float) $dtl->harga_satuan, 0, ',', '.') }}</td>
                            <td class="td-num" style="font-weight: 600;">Rp {{ number_format((float) $dtl->total_sistem_nilai, 0, ',', '.') }}</td>
                            
                            <td class="td-num" style="background: #f0fdfa; font-weight: 700; color: #0f766e;">{{ number_format((float) $dtl->stok_fisik_qty, 2, ',', '.') }} {{ $satuan }}</td>
                            <td class="td-num" style="background: #f0fdfa; font-weight: 600;">Rp {{ number_format((float) $dtl->total_fisik_nilai, 0, ',', '.') }}</td>
                            
                            <td class="td-num" style="background: #fefce8;">
                                @if($selisihQty < 0)
                                    <span class="badge-adj-defisit">{{ number_format($selisihQty, 2, ',', '.') }}</span>
                                @elseif($selisihQty > 0)
                                    <span class="badge-adj-surplus">+{{ number_format($selisihQty, 2, ',', '.') }}</span>
                                @else
                                    <span class="badge-adj-match">0,00</span>
                                @endif
                            </td>
                            <td class="td-num" style="background: #fefce8; color: #64748b;">Rp {{ number_format((float) $dtl->harga_satuan, 0, ',', '.') }}</td>
                            <td class="td-num" style="background: #fefce8; font-weight: 700; color: {{ $selisihNilai < 0 ? '#b91c1c' : ($selisihNilai > 0 ? '#15803d' : '#475569') }};">
                                {{ $selisihNilai < 0 ? '-' : ($selisihNilai > 0 ? '+' : '') }}Rp {{ number_format(abs($selisihNilai), 0, ',', '.') }}
                            </td>
                            <td style="font-size: 0.8rem; color: #475569;">{{ $dtl->alasan_txt ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Ringkasan Bawah --}}
        <div class="adj-summary-bar" style="margin-top: 1.5rem;">
            <div class="adj-summary-item">
                <span class="adj-summary-label">Total Item Barang:</span>
                <span class="adj-summary-val">{{ $header->total_item }}</span>
            </div>
            <div class="adj-summary-item">
                <span class="adj-summary-label">Total Selisih Kuantitas:</span>
                <span class="adj-summary-val" style="color: {{ (float) $header->total_selisih_qty < 0 ? '#b91c1c' : ((float) $header->total_selisih_qty > 0 ? '#15803d' : '#0f172a') }};">
                    {{ (float) $header->total_selisih_qty > 0 ? '+' : '' }}{{ number_format((float) $header->total_selisih_qty, 2, ',', '.') }}
                </span>
            </div>
            <div class="adj-summary-item">
                <span class="adj-summary-label">Net Selisih Persediaan (Rp):</span>
                <span class="adj-summary-val" style="color: {{ (float) $header->total_selisih_nilai < 0 ? '#b91c1c' : ((float) $header->total_selisih_nilai > 0 ? '#15803d' : '#0f172a') }};">
                    {{ (float) $header->total_selisih_nilai < 0 ? '-' : ((float) $header->total_selisih_nilai > 0 ? '+' : '') }}Rp {{ number_format(abs((float) $header->total_selisih_nilai), 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Kolom Tanda Tangan Berita Acara Cetak --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 3rem; text-align: center;">
            <div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Dihitung &amp; Dibuat Oleh,</p>
                <div style="height: 60px;"></div>
                <p style="margin: 0; font-weight: 700; color: #0f172a; text-decoration: underline;">( {{ $header->created_by ?? 'Petugas Gudang' }} )</p>
                <p style="margin: 0.15rem 0 0 0; font-size: 0.725rem; color: #64748b;">Petugas Tim Opname</p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Diverifikasi Oleh,</p>
                <div style="height: 60px;"></div>
                <p style="margin: 0; font-weight: 700; color: #0f172a; text-decoration: underline;">( ......................................... )</p>
                <p style="margin: 0.15rem 0 0 0; font-size: 0.725rem; color: #64748b;">Kepala Gudang</p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Disetujui Oleh,</p>
                <div style="height: 60px;"></div>
                <p style="margin: 0; font-weight: 700; color: #0f172a; text-decoration: underline;">( ......................................... )</p>
                <p style="margin: 0.15rem 0 0 0; font-size: 0.725rem; color: #64748b;">Pimpinan / Manajer Operasional</p>
            </div>
        </div>
    </div>

</div>

{{-- Include Modal Konfirmasi Void (Partials) --}}
@include('gudang.adjustment.partials.modal-void')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/adjustment/adjustment-index.js') }}"></script>
@endpush
