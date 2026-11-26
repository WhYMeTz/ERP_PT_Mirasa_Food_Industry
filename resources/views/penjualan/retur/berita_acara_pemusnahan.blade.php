@extends('layouts.app')

@section('title', 'Berita Acara Pemusnahan Barang Retur - ' . $retur->retur_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/retur/retur-berita-acara.css') }}?v={{ filemtime(public_path('css/penjualan/retur/retur-berita-acara.css')) }}">
@endpush

@php
    \Carbon\Carbon::setLocale('id');
    $tglRetur = $retur->retur_tgl ? \Carbon\Carbon::parse($retur->retur_tgl) : now();
    $hari = $tglRetur->translatedFormat('l');
    $tglLengkap = $tglRetur->translatedFormat('d F Y');
    
    // Nomor Berita Acara Pemusnahan Resmi Sesuai Standar MFI
    $bapScrapNo = app(\App\Services\Common\CodeGeneratorService::class)->generateBapScrapNo($tglRetur->toDateString(), $retur->retur_no);

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
    $soNo = $retur->order?->so_no ?: '-';
    $sjNo = $retur->order?->surat_jalan_no ?: ($retur->order?->pengeluaran?->first()?->pengeluaran_no ?: '-');
    $fakturNo = $retur->order?->faktur_no ?: ($retur->order?->faktur?->first()?->faktur_no ?: '-');
@endphp

@section('content')
<div class="ba-container">
    {{-- TOOLBAR / COMMAND BAR ATAS (NO-PRINT) --}}
    <div class="ba-toolbar no-print">
        <div class="ba-toolbar-left">
            <a href="{{ route('penjualan.retur.index') }}" class="ba-btn ba-btn-secondary" title="Kembali ke Halaman Retur Penjualan">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
            <div class="ba-toolbar-sep"></div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="ba-toolbar-title">
                        Berita Acara Pemusnahan
                    </span>
                    <span class="badge" style="background: #fef2f2; color: #991b1b; font-size: 0.675rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca;">
                        HACCP
                    </span>
                </div>
                <div style="font-size: 0.75rem; color: #64748b; font-family: monospace; margin-top: 3px; line-height: 1.45;">
                    <div>Nomor: <span style="color: #334155; font-weight: 600;">{{ $bapScrapNo }}</span></div>
                    <div>Retur: <span style="color: #334155; font-weight: 600;">{{ $retur->retur_no }}</span></div>
                </div>
            </div>
        </div>
        <div class="ba-toolbar-right">
            <a href="{{ route('penjualan.retur.export-pemusnahan-pdf', $retur->retur_id) }}" target="_blank" class="ba-btn ba-btn-outline" title="Unduh format dokumen PDF resmi">
                <svg width="14" height="14" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Download PDF</span>
            </a>
            <button type="button" onclick="window.print()" class="ba-btn ba-btn-danger">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar A4</span>
            </button>
        </div>
    </div>

    {{-- LEMBAR RESMI BERITA ACARA PEMUSNAHAN (STANDAR KERTAS A4 PROPORSIONAL) --}}
    <div class="ba-sheet">
        {{-- KOP PERUSAHAAN HACCP MIRASA (STANDAR RESMI FORM MFI) --}}
        <table class="kop-table">
            <tr>
                <td class="kop-logo-cell">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo PT Mirasa" class="kop-logo-img">
                    <div class="kop-slogan">ENAK - GURIH - LEZAT</div>
                </td>
                <td class="kop-center-cell">
                    <div class="kop-company-name">PT. MIRASA FOOD INDUSTRY</div>
                    <div class="kop-form-name">
                        BERITA ACARA PEMUSNAHAN PRODUK JADI RETUR
                    </div>
                </td>
                <td class="kop-meta-cell">
                    <div><strong>No. Dokumen:</strong> MFI/HACCP/BAP-RET/{{ $tglRetur->format('Y') }}</div>
                    <div><strong>Revisi:</strong> 01</div>
                    <div><strong>Tgl Berlaku:</strong> 01-01-2024</div>
                    <div><strong>Halaman:</strong> 1 dari 1</div>
                </td>
            </tr>
        </table>

        {{-- NOMOR DOKUMEN RESMI & REFERENSI --}}
        <div class="doc-header-box">
            <div class="doc-number">
                NOMOR: {{ $bapScrapNo }}
            </div>
            <div class="doc-ref">
                Ref. Dokumen Retur: <strong style="font-family: monospace; color: #000;">{{ $retur->retur_no }}</strong> &bull; Ref. PO Penjualan: <strong style="font-family: monospace; color: #000;">{{ $soNo }}</strong>
            </div>
        </div>

        {{-- PARAGRAF PEMBUKA LEGAL --}}
        <p class="opening-p">
            Pada hari ini <strong>{{ $hari }}</strong>, tanggal <strong>{{ $tglLengkap }}</strong>, bertempat di area PT. Mirasa Food Industry, telah dilaksanakan proses inspeksi fisik, pemisahan, dan pemusnahan resmi terhadap produk jadi hasil retur penjualan (customer returns) dengan rincian data sebagai berikut:
        </p>

        {{-- INFORMASI SUMBER DOKUMEN (FORMAT FORMAL SINKRON - TANPA KOTAK) --}}
        <table class="ba-dossier-table">
            <colgroup>
                <col style="width: 114px;">
                <col style="width: 10px;">
                <col style="width: 255px;">
                <col style="width: 20px;">
                <col style="width: 104px;">
                <col style="width: 10px;">
                <col style="width: auto;">
            </colgroup>
            <tr>
                <td class="lbl">Mitra Pelanggan</td>
                <td class="sep">:</td>
                <td class="val">
                    <strong>{{ $customerNm }}</strong>
                    <span style="font-size: 7.5pt; color: #475569; font-family: monospace;">({{ $customerCd ?? ($retur->customer?->customer_cd ?? '-') }})</span>
                </td>
                <td class="spacer"></td>
                <td class="lbl">Gudang Pelaksana</td>
                <td class="sep">:</td>
                <td class="val">{{ $retur->gudang?->gudang_nm ?? 'PT Mirasa Food Industry' }}</td>
            </tr>
            <tr>
                <td class="lbl">No. Faktur Penjualan</td>
                <td class="sep">:</td>
                <td class="val" style="font-family: monospace;">{{ $fakturNo }}</td>
                <td class="spacer"></td>
                <td class="lbl">No. Surat Jalan</td>
                <td class="sep">:</td>
                <td class="val" style="font-family: monospace;">{{ $sjNo }}</td>
            </tr>
            <tr>
                <td class="lbl">Tindakan Komersial</td>
                <td class="sep">:</td>
                <td class="val">
                    {{ $retur->tindakan_cd === 'POTONG_TAGIHAN' ? 'Pemotongan Piutang / Nota Kredit' : 'Penggantian Barang Baru' }}
                </td>
                <td class="spacer"></td>
                <td class="lbl">Alasan Retur</td>
                <td class="sep">:</td>
                <td class="val">{{ $retur->alasan_txt ?: 'Cacat mutu / kemasan rusak dari pelanggan' }}</td>
            </tr>
        </table>

        {{-- TABEL RINCIAN BARANG YANG DIMUSNAHKAN (PAS A4 - TIDAK TERLALU LEBAR) --}}
        <div class="section-title">
            A. DAFTAR BARANG YANG DIMUSNAHKAN FISIK:
        </div>
        <table class="ba-table">
            <thead>
                <tr>
                    <th style="width: 35px; text-align: center;">NO</th>
                    <th style="width: 95px; text-align: center;">KODE BARANG</th>
                    <th style="text-align: left;">NAMA PRODUK JADI</th>
                    <th style="width: 90px; text-align: center;">QTY MUSNAH</th>
                    <th style="width: 65px; text-align: center;">SATUAN</th>
                    <th style="width: 135px; text-align: left;">KONDISI / ALASAN</th>
                    <th style="width: 140px; text-align: left;">METODE PEMUSNAHAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($destroyedItems as $idx => $dtl)
                    @php
                        $qty = (float) ($dtl->qty_dimusnahkan > 0 ? $dtl->qty_dimusnahkan : $dtl->qty_retur);
                        $kondisiMap = [
                            'RUSAK_KEMASAN'   => 'Kemasan Rusak / Cacat',
                            'REMUK_MELEMPEM'  => 'Remuk / Melempem',
                            'KADALUARSA'      => 'Kadaluarsa / Expired',
                            'SALAH_KIRIM'     => 'Salah Kirim / Lebih',
                            'REJECT_CUSTOMER' => 'Reject Mitra / Customer',
                        ];
                        $kondisiTxt = $kondisiMap[$dtl->kondisi_cd] ?? ($dtl->kondisi_cd ?: 'Cacat Mutu Fisik');
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="text-align: center; font-family: monospace;">{{ $dtl->barang?->barang_cd ?? '-' }}</td>
                        <td>
                            <strong>{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                            <div style="font-size: 8pt; color: #475569; margin-top: 2px;">
                                No. Batch: <span style="font-family: monospace; font-weight: 600; color: #0f172a;">{{ $dtl->production_batch_no ?: '-' }}</span>
                            </div>
                            @if(!empty($dtl->catatan_txt))
                                <div style="font-size: 7.5pt; color: #475569; font-style: italic; margin-top: 1px;">Catatan: {{ $dtl->catatan_txt }}</div>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 800; font-family: monospace; color: #dc2626;">
                            {{ number_format($qty, 0) }}
                        </td>
                        <td style="text-align: center;">
                            {{ $dtl->satuan ?: ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton') }}
                        </td>
                        <td style="font-size: 8pt;">
                            <strong style="color: #991b1b;">{{ $kondisiTxt }}</strong>
                        </td>
                        <td style="font-size: 7.5pt; color: #334155;">
                            Pembongkaran &amp; Pembuangan Akhir Limbah
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">TOTAL KUANTITAS DIMUSNAHKAN:</td>
                    <td style="text-align: right; font-family: monospace; font-size: 9pt; color: #dc2626;">
                        {{ number_format($totalQtyMusnah, 0) }}
                    </td>
                    <td colspan="3" style="color: #475569; font-size: 8pt;">
                        Karton / Satuan (Stok Persediaan Telah Dikeluarkan)
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- PERNYATAAN RESMI KEAMANAN PANGAN & MUTU --}}
        <div class="ba-legal-box">
            <strong>PERNYATAAN RESMI JAMINAN KEAMANAN PANGAN &amp; MUTU:</strong>
            Seluruh produk yang tercantum pada tabel di atas dinyatakan tidak layak konsumsi dan/atau tidak memenuhi standar mutu peredaran pasar PT. Mirasa Food Industry. Produk telah dimusnahkan secara fisik di area PT. Mirasa Food Industry, tidak diperjualbelikan kembali ke pihak mana pun, dan tidak dimasukkan ke dalam persediaan barang jadi (finished goods). Berita Acara ini sah sebagai bukti otentik pengurangan aset persediaan barang pabrik.
        </div>

        {{-- TABEL TANDA TANGAN 4 PIHAK RESMI (PAS A4) --}}
        <div class="section-title">
            B. PIHAK YANG MELAKSANAKAN, MEMERIKSA &amp; MENYETUJUI PEMUSNAHAN:
        </div>
        <table class="ba-signature-table">
            <tr>
                <td class="ba-sig-head-cell">
                    <div class="ba-sig-title">PETUGAS SORTIR &amp; GUDANG</div>
                    <div class="ba-sig-subrole">Pelaksana Pemisahan Fisik</div>
                </td>
                <td class="ba-sig-head-cell">
                    <div class="ba-sig-title">QUALITY CONTROL (QC)</div>
                    <div class="ba-sig-subrole">Verifikator Cacat Mutu Produk</div>
                </td>
                <td class="ba-sig-head-cell">
                    <div class="ba-sig-title">BAGIAN FINANCE / AKUNTANSI</div>
                    <div class="ba-sig-subrole">Verifikator Tagihan / Nota Kredit</div>
                </td>
                <td class="ba-sig-head-cell">
                    <div class="ba-sig-title">KEPALA PABRIK / DIREKTUR</div>
                    <div class="ba-sig-subrole">Persetujuan Otorisasi Pemusnahan</div>
                </td>
            </tr>
            <tr>
                <td class="ba-sig-space-cell"></td>
                <td class="ba-sig-space-cell"></td>
                <td class="ba-sig-space-cell"></td>
                <td class="ba-sig-space-cell"></td>
            </tr>
            <tr>
                <td class="ba-sig-foot-cell">
                    <div class="ba-sig-underline"></div>
                    <div class="ba-sig-name">Raden Rahmat</div>
                    <div class="ba-sig-caption">Penanggung Jawab Gudang FG</div>
                </td>
                <td class="ba-sig-foot-cell">
                    <div class="ba-sig-underline"></div>
                    <div class="ba-sig-name">Siti Astiyanti</div>
                    <div class="ba-sig-caption">Staf Pengendalian Mutu (QC)</div>
                </td>
                <td class="ba-sig-foot-cell">
                    <div class="ba-sig-underline"></div>
                    <div class="ba-sig-name">Mei Nur Rahmawati</div>
                    <div class="ba-sig-caption">Staf Keuangan &amp; Piutang</div>
                </td>
                <td class="ba-sig-foot-cell">
                    <div class="ba-sig-underline"></div>
                    <div class="ba-sig-name">Habieb Soleh Y.</div>
                    <div class="ba-sig-caption">Pimpinan Pabrik / Direktur</div>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
