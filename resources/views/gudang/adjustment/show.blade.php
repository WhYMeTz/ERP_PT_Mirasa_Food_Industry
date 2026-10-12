@extends('layouts.app')

@section('title', 'Dokumen Adjustment: ' . $header->adj_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-form.css') }}">
    <style>
        @media print {
            .adj-header-actions, .navbar, .pill-nav-container, .btn-action-trigger, .no-print, .adj-breadcrumb {
                display: none !important;
            }
            .adj-form-card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                padding: 1rem !important;
            }
            body {
                background: #ffffff !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="adj-container">

    {{-- Breadcrumb Navigation --}}
    <div class="adj-breadcrumb no-print">
        <a href="{{ route('gudang.adjustment.index') }}" class="adj-breadcrumb-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Adjustment
        </a>
    </div>

    {{-- Header Dokumen Standar Mirasa --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Dokumen Adjustment: {{ $header->adj_no }}</h1>
            <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
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
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;" class="no-print">
            <button type="button" onclick="window.print()" class="btn-adj-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Berita Acara</span>
            </button>
            @if($header->status_cd !== 'VOID')
                <button type="button" onclick="openVoidModal({{ $header->adj_id }}, '{{ $header->adj_no }}')" class="btn-adj-secondary" style="color: #dc2626; border-color: #fca5a5;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <span>Batalkan Dokumen</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Detail Informasi Berita Acara --}}
    <div class="adj-form-card">
        <h3 class="adj-section-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Ringkasan Berita Acara
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1.125rem; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Nomor Dokumen:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 700; color: #0284c7; font-family: 'Consolas', monospace; font-size: 0.95rem;">{{ $header->adj_no }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Tanggal Pelaksanaan:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #1e293b;">{{ $header->adj_tgl ? $header->adj_tgl->format('d F Y') : '-' }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Gudang Penyimpanan:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #1e293b;">{{ $header->gudang->gudang_nm ?? '-' }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Jenis Dokumen:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #1e293b;">{{ str_replace('_', ' ', $header->kategori_adj ?: 'OPNAME_RUTIN') }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Petugas Pencatat:</span>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #1e293b;">{{ $header->created_by ?? '-' }}</p>
            </div>
            <div>
                <span style="font-size: 0.725rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Catatan Dokumen:</span>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #475569;">{{ $header->catatan_txt ?: '-' }}</p>
            </div>
        </div>

        {{-- Tabel Detail Barang (Excel Grid Table) --}}
        <div class="excel-table-scroll" style="overflow-x: auto; border: 1px solid #cbd5e1; border-radius: 6px;">
            <table class="excel-grid-table">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">No</th>
                        <th class="col-adj-barang" style="width: 320px; min-width: 280px; text-align: left;">Nama &amp; Kode Komoditas</th>
                        <th style="width: 55px; text-align: center;">Satuan</th>
                        <th style="width: 130px; text-align: right;">Persediaan Awal</th>
                        <th style="width: 130px; text-align: right; background: #064e3b; color: #ffffff;">Persediaan Gudang</th>
                        <th style="width: 110px; text-align: right;">Selisih Qty</th>
                        <th style="width: 110px; text-align: right;">@Harga Satuan</th>
                        <th style="width: 130px; text-align: right; background: #0f172a; color: #ffffff;">Total Selisih (Rp)</th>
                        <th class="col-adj-catatan" style="min-width: 180px; text-align: left;">Keterangan / Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($header->details as $idx => $dtl)
                        @php
                            $barang = $dtl->barang;
                            $satuan = $barang->satuanDasar->satuan_cd ?? 'KG';
                            $stokSistem = (float) $dtl->stok_sistem_qty;
                            $stokFisik = (float) $dtl->stok_fisik_qty;
                            $hargaSatuan = (float) $dtl->harga_satuan;
                            $totalSistemNilai = (float) ($dtl->total_sistem_nilai ?: ($stokSistem * $hargaSatuan));
                            $totalFisikNilai = (float) ($dtl->total_fisik_nilai ?: ($stokFisik * $hargaSatuan));
                            $selisihQty = (float) $dtl->selisih_qty;
                            $selisihNilai = (float) $dtl->total_selisih_nilai;
                        @endphp
                        <tr>
                            <td class="row-num" style="text-align: center; color: #475569; font-weight: 700; background: #f1f5f9;">{{ $idx + 1 }}</td>
                            <td class="col-adj-barang">
                                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                                    <strong style="color: #0f172a; font-size: 0.85rem;">{{ $barang->barang_nm ?? '-' }}</strong>
                                    <span style="font-size: 0.725rem; color: #64748b; font-family: monospace;">
                                        {{ $barang->barang_cd ?? '-' }}
                                        @if($dtl->batch_no)
                                            • Batch: <strong>{{ $dtl->batch_no }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-unit-pill">{{ $satuan }}</span>
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #334155;">
                                <div>{{ number_format($stokSistem, 2, ',', '.') }}</div>
                                <small style="color: #64748b; font-size: 0.725rem; font-weight: normal; display: block;" title="Total Persediaan Awal (Rp)">
                                    Rp {{ number_format($totalSistemNilai, 0, ',', '.') }}
                                </small>
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #0f172a; background: #f0fdf4;">
                                <div>{{ number_format($stokFisik, 2, ',', '.') }}</div>
                                <small style="color: #047857; font-size: 0.725rem; font-weight: 600; display: block;" title="Total Persediaan Gudang (Rp)">
                                    Rp {{ number_format($totalFisikNilai, 0, ',', '.') }}
                                </small>
                            </td>
                            <td style="text-align: right;">
                                @if($selisihQty < 0)
                                    <span class="badge-selisih-qty defisit">{{ number_format($selisihQty, 2, ',', '.') }}</span>
                                @elseif($selisihQty > 0)
                                    <span class="badge-selisih-qty surplus">+{{ number_format($selisihQty, 2, ',', '.') }}</span>
                                @else
                                    <span class="badge-selisih-qty match">0,00</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 600; color: #475569;">
                                Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 800; color: {{ $selisihNilai < 0 ? '#dc2626' : ($selisihNilai > 0 ? '#16a34a' : '#64748b') }};">
                                {{ $selisihNilai < 0 ? '-' : ($selisihNilai > 0 ? '+' : '') }}Rp {{ number_format(abs($selisihNilai), 0, ',', '.') }}
                            </td>
                            <td style="font-size: 0.8rem; color: #475569;">
                                {{ $dtl->alasan_txt ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Ringkasan Eksekutif --}}
        <div class="adj-executive-summary" style="margin-top: 1.5rem;">
            <div class="summary-stat-box">
                <span class="summary-stat-label">Total Barang Disesuaikan</span>
                <span class="summary-stat-val">{{ $header->total_item }} Komoditas</span>
                <span class="summary-stat-sub">Item yang masuk dalam berita acara</span>
            </div>
            <div class="summary-stat-box">
                <span class="summary-stat-label">Total Selisih Kuantitas</span>
                <span class="summary-stat-val" style="color: {{ (float) $header->total_selisih_qty < 0 ? '#dc2626' : ((float) $header->total_selisih_qty > 0 ? '#16a34a' : '#0f172a') }};">
                    {{ (float) $header->total_selisih_qty > 0 ? '+' : '' }}{{ number_format((float) $header->total_selisih_qty, 2, ',', '.') }}
                </span>
                <span class="summary-stat-sub">Akumulasi kuantitas selisih fisik</span>
            </div>
            <div class="summary-stat-box">
                <span class="summary-stat-label">Net Dampak Persediaan</span>
                <span class="summary-stat-val" style="color: {{ (float) $header->total_selisih_nilai < 0 ? '#dc2626' : ((float) $header->total_selisih_nilai > 0 ? '#16a34a' : '#0f172a') }};">
                    {{ (float) $header->total_selisih_nilai < 0 ? '-' : ((float) $header->total_selisih_nilai > 0 ? '+' : '') }}Rp {{ number_format(abs((float) $header->total_selisih_nilai), 0, ',', '.') }}
                </span>
                <span class="summary-stat-sub">Dampak nilai buku persediaan</span>
            </div>
        </div>

        {{-- Kolom Tanda Tangan Berita Acara Cetak --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 3.5rem; text-align: center;">
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
