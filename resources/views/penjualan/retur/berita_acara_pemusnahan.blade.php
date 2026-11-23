@extends('layouts.app')

@section('title', 'Berita Acara Pemusnahan Barang Retur - ' . $retur->retur_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/retur/retur-berita-acara.css') }}">
@endpush

@php
    \Carbon\Carbon::setLocale('id');
    $tglRetur = $retur->retur_tgl ? \Carbon\Carbon::parse($retur->retur_tgl) : now();
    $hari = $tglRetur->translatedFormat('l');
    $tglLengkap = $tglRetur->translatedFormat('d F Y');
    
    // Filter detail yang dimusnahkan
    $destroyedItems = $retur->details->filter(function($d) {
        return $d->perlakuan_cd === 'DIMUSNAHKAN' || (float)$d->qty_dimusnahkan > 0;
    });

    if ($destroyedItems->isEmpty()) {
        $destroyedItems = $retur->details;
    }

    $totalQtyMusnah = (float) ($retur->total_qty_dimusnahkan ?: $destroyedItems->sum('qty_dimusnahkan'));
    if ($totalQtyMusnah == 0) {
        $totalQtyMusnah = (float) $destroyedItems->sum('qty_retur');
    }

    $customerNm = $retur->customer?->customer_nm ?: ($retur->order?->customer?->customer_nm ?: 'Pelanggan Umum');
@endphp

@section('content')
<div class="ba-container">
    {{-- TOOLBAR ATAS (NO-PRINT) --}}
    <div class="ba-toolbar no-print">
        <div class="ba-toolbar-left">
            <a href="{{ $retur->so_id ? route('penjualan.so.show', $retur->so_id) : route('penjualan.retur.index') }}" class="ba-btn ba-btn-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
            <span class="ba-toolbar-title">
                Berita Acara Pemusnahan: {{ $retur->retur_no }}
            </span>
        </div>
        <div class="ba-toolbar-right">
            <a href="{{ route('penjualan.retur.export-pemusnahan-pdf', $retur->retur_id) }}" target="_blank" class="ba-btn ba-btn-outline" title="Unduh format dokumen PDF resmi">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Download PDF</span>
            </a>
            <button type="button" onclick="window.print()" class="ba-btn ba-btn-danger">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar A4</span>
            </button>
        </div>
    </div>

    {{-- LEMBAR RESMI BERITA ACARA PEMUSNAHAN (A4 PRINT READY) --}}
    <div class="ba-sheet">
        {{-- KOP PERUSAHAAN HACCP MIRASA --}}
        <table class="kop-table">
            <tr>
                <td rowspan="2" class="kop-logo-cell">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo PT Mirasa" class="kop-logo-img">
                    <div class="kop-slogan">ENAK - GURIH - LEZAT</div>
                </td>
                <td colspan="2">
                    <div class="kop-company-name">PT. MIRASA FOOD INDUSTRY</div>
                    <div style="font-size: 7.5pt; text-align: center; color: #475569; font-family: Arial, sans-serif;">
                        Pabrik Olahan Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang &bull; Divisi Pengendalian Mutu & Karantina Pangan
                    </div>
                </td>
            </tr>
            <tr>
                <td style="text-align: center; vertical-align: middle;">
                    <div class="kop-form-name">
                        BERITA ACARA PEMUSNAHAN BARANG RETUR<br>
                        <span style="font-size: 9pt; font-weight: normal; color: #dc2626;">(OFFICIAL SCRAP DISPOSAL REPORT)</span>
                    </div>
                </td>
                <td style="width: 220px; font-size: 7.5pt; font-family: Arial, sans-serif; line-height: 1.4;">
                    <div><strong>No. Dokumen:</strong> MFI/HACCP/BAP-RET/{{ date('Y') }}</div>
                    <div><strong>Revisi:</strong> 01</div>
                    <div><strong>Tgl Berlaku:</strong> 01-01-2024</div>
                    <div><strong>Halaman:</strong> 1 dari 1</div>
                </td>
            </tr>
        </table>

        {{-- NOMOR DOKUMEN & TANGGAL --}}
        <div style="text-align: center; margin-bottom: 1.25rem;">
            <div style="font-size: 11pt; font-weight: bold; letter-spacing: 0.5px;">
                NOMOR: BAP-SCRAP/{{ $retur->retur_no }}
            </div>
            <div style="font-size: 8.5pt; color: #475569; font-style: italic; margin-top: 2px;">
                Ref. Retur: <strong>{{ $retur->retur_no }}</strong> &bull; Ref. PO Penjualan: <strong>{{ $retur->order?->so_no ?? '-' }}</strong>
            </div>
        </div>

        {{-- PARAGRAF PEMBUKA LEGAL --}}
        <p style="margin-bottom: 0.75rem; text-align: justify;">
            Pada hari ini <strong>{{ $hari }}</strong>, tanggal <strong>{{ $tglLengkap }}</strong>, bertempat di area karantina pemusnahan limbah & scrap PT. Mirasa Food Industry, telah dilaksanakan proses inspeksi, pemisahan, dan pemusnahan fisik terhadap produk jadi hasil retur penjualan (customer returns) dengan rincian dokumen sebagai berikut:
        </p>

        {{-- INFORMASI SUMBER DOKUMEN --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: 9pt;">
            <tr>
                <td style="width: 18%; padding: 3px 0; font-weight: bold;">Mitra Pelanggan</td>
                <td style="width: 2%; padding: 3px 0;">:</td>
                <td style="width: 30%; padding: 3px 0;">{{ $customerNm }}</td>
                <td style="width: 18%; padding: 3px 0; font-weight: bold;">Gudang Pelaksana</td>
                <td style="width: 2%; padding: 3px 0;">:</td>
                <td style="width: 30%; padding: 3px 0;">{{ $retur->gudang?->gudang_nm ?? 'Gudang Karantina / FG' }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0; font-weight: bold;">No. Faktur Penjualan</td>
                <td style="padding: 3px 0;">:</td>
                <td style="padding: 3px 0; font-family: monospace;">{{ $retur->order?->faktur_no ?: '-' }}</td>
                <td style="padding: 3px 0; font-weight: bold;">No. Surat Jalan</td>
                <td style="padding: 3px 0;">:</td>
                <td style="padding: 3px 0; font-family: monospace;">{{ $retur->order?->surat_jalan_no ?: '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0; font-weight: bold;">Tindakan Tagihan</td>
                <td style="padding: 3px 0;">:</td>
                <td style="padding: 3px 0;">{{ $retur->tindakan_cd === 'POTONG_TAGIHAN' ? 'Pemotongan Piutang / Nota Kredit' : 'Penggantian Barang Baru' }}</td>
                <td style="padding: 3px 0; font-weight: bold;">Alasan Pengembalian</td>
                <td style="padding: 3px 0;">:</td>
                <td style="padding: 3px 0;">{{ $retur->alasan_txt ?: 'Cacat mutu / kemasan rusak dari pelanggan' }}</td>
            </tr>
        </table>

        {{-- TABEL RINCIAN BARANG YANG DIMUSNAHKAN --}}
        <div style="font-weight: bold; font-size: 9pt; margin-bottom: 4px; text-transform: uppercase;">
            A. Daftar Barang yang Dimusnahkan Fisik (Scrap Inventory):
        </div>
        <table class="ba-table">
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">No</th>
                    <th style="width: 90px; text-align: left;">Kode Barang</th>
                    <th style="text-align: left;">Nama Produk Jadi</th>
                    <th style="width: 80px; text-align: right;">Qty Musnah</th>
                    <th style="width: 65px; text-align: center;">Satuan</th>
                    <th style="width: 140px; text-align: left;">Kondisi / Alasan Scrap</th>
                    <th style="width: 130px; text-align: left;">Metode Pemusnahan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($destroyedItems as $idx => $dtl)
                    @php
                        $qty = (float) ($dtl->qty_dimusnahkan > 0 ? $dtl->qty_dimusnahkan : $dtl->qty_retur);
                        $kondisiMap = [
                            'RUSAK_KEMASAN'   => 'Kemasan Rusak / Sobek',
                            'REMUK_MELEMPEM'  => 'Remuk / Melempem',
                            'KADALUARSA'      => 'Kadaluarsa / Expired',
                            'SALAH_KIRIM'     => 'Salah Kirim / Afkir',
                            'REJECT_CUSTOMER' => 'Reject Pelanggan',
                        ];
                        $kondisiTxt = $kondisiMap[$dtl->kondisi_cd] ?? ($dtl->kondisi_cd ?: 'Cacat Mutu Fisik');
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="font-family: monospace;">{{ $dtl->barang?->barang_cd ?? '-' }}</td>
                        <td>
                            <strong>{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                            @if(!empty($dtl->catatan_txt))
                                <div style="font-size: 7.5pt; color: #475569; font-style: italic;">Catatan: {{ $dtl->catatan_txt }}</div>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: bold; font-family: monospace;">
                            {{ number_format($qty, 0) }}
                        </td>
                        <td style="text-align: center;">
                            {{ $dtl->satuan ?: ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton') }}
                        </td>
                        <td style="font-size: 8pt;">
                            <span style="color: #dc2626; font-weight: bold;">{{ $kondisiTxt }}</span>
                        </td>
                        <td style="font-size: 8pt; color: #334155;">
                            Pembongkaran &amp; Pembuangan Akhir Limbah
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">Total Kuantitas yang Dimusnahkan:</td>
                    <td style="text-align: right; font-family: monospace; font-size: 9.5pt; color: #dc2626;">
                        {{ number_format($totalQtyMusnah, 0) }}
                    </td>
                    <td colspan="3" style="text-align: left; font-size: 8pt; color: #475569;">
                        Karton / Satuan (Stok Persediaan Telah Dikeluarkan / Scrap)
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- KLAUSUL LEGALITAS PEMUSNAHAN --}}
        <div class="legal-box">
            <strong>PERNYATAAN RESMI JAMINAN KEAMANAN PANGAN:</strong><br>
            Seluruh produk yang tercantum pada tabel di atas dinyatakan tidak layak konsumsi dan/atau tidak memenuhi standar mutu peredaran pasar PT. Mirasa Food Industry. Produk telah dimusnahkan secara fisik, tidak diperjualbelikan kembali ke pihak mana pun, dan tidak dimasukkan ke dalam persediaan barang jadi (finished goods). Berita Acara ini sah sebagai bukti pengurangan aset persediaan/scrap pabrik.
        </div>

        {{-- KOLOM TANDA TANGAN 4 PIHAK --}}
        <div style="font-weight: bold; font-size: 9pt; margin-top: 1rem; margin-bottom: 0.5rem;">
            B. Pihak yang Melaksanakan &amp; Menyetujui Pemusnahan:
        </div>
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-title">Petugas Gudang Sortir</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( ........................................ )</div>
                    <div class="sig-role">Pelaksana Pemisahan Fisik</div>
                </td>
                <td>
                    <div class="sig-title">Quality Control (QC)</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( ........................................ )</div>
                    <div class="sig-role">Verifikasi Cacat Mutu Produk</div>
                </td>
                <td>
                    <div class="sig-title">Bagian Finance / Akuntansi</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( ........................................ )</div>
                    <div class="sig-role">Verifikasi Nilai Tagihan / Nota</div>
                </td>
                <td>
                    <div class="sig-title">Kepala Pabrik / Pimpinan</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( ........................................ )</div>
                    <div class="sig-role">Persetujuan Otorisasi Scrap</div>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
