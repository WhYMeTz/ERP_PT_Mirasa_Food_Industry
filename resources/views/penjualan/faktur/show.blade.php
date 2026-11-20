@extends('layouts.app')

@section('title', 'Detail Faktur ' . $faktur->faktur_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/faktur/faktur-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penjualan/faktur/faktur-form.css') }}">
@endpush

@section('content')
<div class="fk-form-container">

    {{-- HEADER & BREADCRUMB --}}
    <div class="fk-page-header">
        <a href="{{ route('penjualan.faktur.index') }}" class="fk-back-link">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Rekapitulasi Penjualan</span>
        </a>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 0.5rem;">
            <div>
                <div class="fk-title-row">
                    <h1 class="fk-page-title" style="font-family: monospace; color: #059669;">
                        {{ $faktur->faktur_no }}
                    </h1>
                    @if ($faktur->status_pembayaran === 'LUNAS')
                        <span class="badge-status lunas">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                            Lunas
                        </span>
                    @elseif ($faktur->status_pembayaran === 'SEBAGIAN')
                        <span class="badge-status sebagian">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                            Sebagian
                        </span>
                    @elseif ($faktur->status_pembayaran === 'BATAL')
                        <span class="badge-status batal">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                            Dibatalkan (Void)
                        </span>
                    @else
                        <span class="badge-status belum-lunas">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                            Belum Lunas
                        </span>
                    @endif
                </div>
                <p class="fk-page-subtitle">
                    Diterbitkan pada {{ \Carbon\Carbon::parse($faktur->faktur_tgl)->translatedFormat('d F Y') }} oleh {{ $faktur->created_by ?? 'System' }}.
                </p>
            </div>

            <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
                {{-- Cetak PDF Faktur --}}
                <a href="{{ route('penjualan.faktur.export-pdf', $faktur->faktur_id) }}" target="_blank" class="btn btn-primary" 
                   style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 700; background: #059669; border-color: #047857;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Faktur (PDF)</span>
                </a>

                @if ($faktur->pengeluaran)
                    <a href="{{ route('penjualan.pengeluaran.show', $faktur->pengeluaran->pengeluaran_id) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Surat Jalan ({{ $faktur->pengeluaran->pengeluaran_no }})</span>
                    </a>
                @endif

                @if ($faktur->order)
                    <a href="{{ route('penjualan.so.show', $faktur->order->so_id) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>PO Penjualan ({{ $faktur->order->so_no }})</span>
                    </a>
                @endif

                @if ($faktur->status_pembayaran !== 'BATAL' && $faktur->total_dibayar <= 0)
                    <button type="button" class="btn btn-danger" onclick="openVoidModal('{{ $faktur->faktur_id }}', '{{ $faktur->faktur_no }}')"
                            style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 700; background: #dc2626; border-color: #b91c1c; color: #ffffff;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Batalkan (Void)</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if ($faktur->status_pembayaran === 'BATAL')
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #991b1b;">
            <div style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Dokumen Faktur Ini Telah Dibatalkan (VOID)</span>
            </div>
            <div style="font-size: 0.8125rem; margin-top: 0.35rem; color: #b91c1c;">
                Catatan: {!! nl2br(e($faktur->catatan_txt)) !!}
            </div>
        </div>
    @endif

    {{-- 3 INFO CARDS: FAKTUR, CUSTOMER, SURAT JALAN --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
        
        {{-- CARD 1: DOKUMEN FAKTUR --}}
        <div class="fk-card">
            <div class="fk-card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Data Penjualan</span>
            </div>
            <table style="width: 100%; font-size: 0.8125rem; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.35rem 0; color: #64748b; width: 140px;">Nomor Faktur</td>
                    <td style="font-weight: 700; font-family: monospace; color: #059669;">: {{ $faktur->faktur_no }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #64748b;">Tanggal Terbit</td>
                    <td style="font-weight: 600;">: {{ \Carbon\Carbon::parse($faktur->faktur_tgl)->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #64748b;">Jatuh Tempo</td>
                    <td style="font-weight: 700; color: #b45309;">
                        : {{ $faktur->tgl_jatuh_tempo ? \Carbon\Carbon::parse($faktur->tgl_jatuh_tempo)->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #64748b;">Ketentuan Pajak</td>
                    <td style="font-weight: 600;">
                        : {{ $faktur->ppn_tipe === 'PPN_11' ? 'Ber-PPN (11%)' : 'Non-PPN (Bebas PPN)' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- CARD 2: MITRA CUSTOMER --}}
        <div class="fk-card">
            <div class="fk-card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                <span>Mitra Customer</span>
            </div>
            @php
                $cust = $faktur->customer;
                $klas = $cust?->klasifikasi ?? 'MANUAL';
            @endphp
            <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a;">
                {{ $cust?->customer_nm ?: 'Pelanggan Umum' }}
            </div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                Kode: <span style="font-family: monospace;">{{ $cust?->customer_cd ?: '-' }}</span> &bull; 
                Klasifikasi: <span class="badge-klasifikasi {{ in_array($klas, ['IDM', 'MIDI_ALFA', 'IDM_ALFA']) ? 'modern' : strtolower($klas) }}">{{ $klas === 'MIDI_ALFA' ? 'Midi/Alfa' : $klas }}</span>
            </div>
            <div style="font-size: 0.785rem; color: #475569; margin-top: 0.4rem; line-height: 1.4;">
                {!! nl2br(e($cust?->alamat_txt ?: ($cust?->wilayah ?: '-'))) !!}
            </div>
            <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #f1f5f9; font-size: 0.75rem; color: #64748b;">
                Syarat Pembayaran: <strong style="color: #1e293b;">{{ $faktur->top_txt ?: '-' }}</strong>
                @if ($faktur->customer_po_no)
                    &bull; PO Cust: <strong style="color: #1e293b; font-family: monospace;">{{ $faktur->customer_po_no }}</strong>
                @endif
            </div>
        </div>

        {{-- CARD 3: DOKUMEN PENGIRIMAN --}}
        <div class="fk-card">
            <div class="fk-card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Surat Jalan &amp; PO</span>
            </div>
            <table style="width: 100%; font-size: 0.8125rem; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.35rem 0; color: #64748b; width: 130px;">Surat Jalan No.</td>
                    <td style="font-weight: 700; font-family: monospace;">
                        @if ($faktur->pengeluaran)
                            <a href="{{ route('penjualan.pengeluaran.show', $faktur->pengeluaran->pengeluaran_id) }}" style="color: #0284c7; text-decoration: none;">
                                {{ $faktur->pengeluaran->pengeluaran_no }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #64748b;">Tanggal Kirim</td>
                    <td style="font-weight: 600;">
                        : {{ $faktur->pengeluaran ? \Carbon\Carbon::parse($faktur->pengeluaran->pengeluaran_tgl)->translatedFormat('d M Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #64748b;">Armada / Supir</td>
                    <td style="font-weight: 600;">
                        : {{ $faktur->pengeluaran?->supir_nm ?: '-' }} ({{ $faktur->pengeluaran?->nopol_kendaraan ?: '-' }})
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #64748b;">PO Penjualan No.</td>
                    <td style="font-weight: 600;">
                        @if ($faktur->order)
                            <a href="{{ route('penjualan.so.show', $faktur->order->so_id) }}" style="color: #334155; text-decoration: none;">
                                {{ $faktur->order->so_no }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
        </div>

    </div>

    {{-- TABEL RINCIAN ITEM BARANG --}}
    <div class="fk-table-container">
        <div class="fk-card-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span>Rincian Produk &amp; Nilai Tagihan Komersial</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="fk-items-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No.</th>
                        <th>Nama Produk Keripik</th>
                        <th style="text-align: right; width: 110px;">Qty Tagih</th>
                        <th style="text-align: right; width: 140px;">Harga Satuan</th>
                        <th style="text-align: right; width: 120px;">Diskon</th>
                        <th style="text-align: right; width: 130px;">Subtotal Netto</th>
                        <th style="text-align: right; width: 110px;">PPN</th>
                        <th style="text-align: right; width: 140px;">Total Item</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totQty = 0;
                    @endphp
                    @foreach ($faktur->details as $idx => $dtl)
                        @php
                            $qty = (float) $dtl->qty;
                            $totQty += $qty;
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: 600;">
                                {{ $idx + 1 }}
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #1e293b;">
                                    {{ $dtl->barang_nm ?: ($dtl->barang?->barang_nm ?: "Barang #{$dtl->barang_id}") }}
                                </div>
                                <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">
                                    Kode: #{{ $dtl->barang_id }}
                                </div>
                            </td>
                            <td style="text-align: right; font-weight: 700; font-family: monospace;">
                                {{ number_format($qty, 0, ',', '.') }}
                                <span style="font-size: 0.725rem; color: #64748b; font-weight: normal;">{{ $dtl->satuan }}</span>
                            </td>
                            <td style="text-align: right; font-family: monospace;">
                                Rp {{ number_format($dtl->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; color: #dc2626;">
                                Rp {{ number_format($dtl->diskon_nominal, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #1e293b;">
                                Rp {{ number_format($dtl->subtotal_netto, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; color: #64748b;">
                                Rp {{ number_format($dtl->ppn_nominal, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 800; color: #059669;">
                                Rp {{ number_format($dtl->total_nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="font-weight: 700; text-align: right;">Total Kuantitas:</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 800; color: #0f172a;">
                            {{ number_format($totQty, 0, ',', '.') }} Karton
                        </td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- BAGIAN BAWAH: CATATAN & KOTAK RINGKASAN FINANSIAL --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 1.5rem; gap: 1.5rem; flex-wrap: wrap;">
            
            {{-- KIRI: CATATAN & AUDIT TRAIL --}}
            <div style="flex: 1; min-width: 280px;">
                @if ($faktur->catatan_txt)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1rem;">
                        <div style="font-weight: 700; font-size: 0.775rem; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem;">
                            Catatan Faktur:
                        </div>
                        <div style="font-size: 0.825rem; color: #334155; line-height: 1.5;">
                            {!! nl2br(e($faktur->catatan_txt)) !!}
                        </div>
                    </div>
                @endif

                <div style="font-size: 0.725rem; color: #94a3b8; line-height: 1.6;">
                    <div>Dibuat oleh: <strong style="color: #64748b;">{{ $faktur->created_by ?? 'System' }}</strong> pada {{ $faktur->created_dt ? \Carbon\Carbon::parse($faktur->created_dt)->translatedFormat('d F Y H:i') : '-' }}</div>
                    @if ($faktur->updated_by)
                        <div>Terakhir diubah oleh: <strong style="color: #64748b;">{{ $faktur->updated_by }}</strong> pada {{ $faktur->updated_dt ? \Carbon\Carbon::parse($faktur->updated_dt)->translatedFormat('d F Y H:i') : '-' }}</div>
                    @endif
                    <div>Versi Dokumen: v{{ $faktur->version_no }}</div>
                </div>
            </div>

            {{-- KANAN: KOTAK PERHITUNGAN TOTAL FINANSIAL --}}
            <div class="fk-calc-box">
                <div class="fk-calc-row">
                    <span style="color: #64748b;">Subtotal Bruto:</span>
                    <span style="font-family: monospace; font-weight: 700; color: #1e293b;">
                        Rp {{ number_format($faktur->subtotal_bruto, 0, ',', '.') }}
                    </span>
                </div>
                <div class="fk-calc-row">
                    <span style="color: #64748b;">Total Diskon:</span>
                    <span style="font-family: monospace; font-weight: 700; color: #dc2626;">
                        - Rp {{ number_format($faktur->total_diskon, 0, ',', '.') }}
                    </span>
                </div>
                @if ($faktur->total_potongan > 0)
                    <div class="fk-calc-row">
                        <span style="color: #64748b;">Potongan Tambahan:</span>
                        <span style="font-family: monospace; font-weight: 700; color: #dc2626;">
                            - Rp {{ number_format($faktur->total_potongan, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
                <div class="fk-calc-row" style="border-top: 1px solid #f1f5f9; padding-top: 0.4rem; margin-top: 0.35rem;">
                    <span style="font-weight: 700; color: #334155;">DPP (Dasar Pengenaan Pajak):</span>
                    <span style="font-family: monospace; font-weight: 700; color: #0f172a;">
                        Rp {{ number_format($faktur->dpp_nominal, 0, ',', '.') }}
                    </span>
                </div>
                <div class="fk-calc-row">
                    <span style="color: #64748b;">PPN ({{ $faktur->ppn_persen }}%):</span>
                    <span style="font-family: monospace; font-weight: 700; color: #2563eb;">
                        Rp {{ number_format($faktur->ppn_nominal, 0, ',', '.') }}
                    </span>
                </div>
                <div class="fk-calc-row total-row">
                    <span>Total Tagihan:</span>
                    <span style="font-family: monospace;">
                        Rp {{ number_format($faktur->total_tagihan, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Status Pelunasan & Sisa Piutang --}}
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <div class="fk-calc-row">
                        <span style="color: #64748b;">Total Telah Dibayar:</span>
                        <span style="font-family: monospace; font-weight: 700; color: #059669;">
                            Rp {{ number_format($faktur->total_dibayar, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="fk-calc-row" style="font-weight: 800; font-size: 0.925rem; color: {{ $faktur->sisa_piutang > 0 ? '#b45309' : '#059669' }};">
                        <span>Sisa Piutang:</span>
                        <span style="font-family: monospace;">
                            Rp {{ number_format($faktur->sisa_piutang, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- MODAL VOID FAKTUR --}}
@include('penjualan.faktur.partials.modal-void')

@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/faktur/faktur-index.js') }}"></script>
@endpush
