<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Serah Terima Retur Penjualan - {{ $retur->retur_no }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }

        body {
            font-family: 'Times-Roman', Times, serif;
            font-size: 9.5pt;
            color: #000000;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }

        /* Kop Tabel HACCP Mirasa - Standar Resmi Form MFI */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin-bottom: 14px;
        }

        .kop-table td {
            border: 1.5px solid #000000;
            vertical-align: middle;
        }

        .kop-logo-cell {
            width: 105px;
            text-align: center;
            padding: 6px;
        }

        .kop-logo-img {
            max-height: 44px;
            width: auto;
            display: block;
            margin: 0 auto;
        }

        .kop-slogan {
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-top: 2px;
            color: #dc2626;
            font-family: Helvetica, Arial, sans-serif;
            text-align: center;
        }

        .kop-center-cell {
            text-align: center;
            padding: 6px 10px;
        }

        .kop-company-name {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: center;
            font-family: Helvetica, Arial, sans-serif;
            margin: 0;
            color: #000000;
        }

        .kop-form-name {
            font-size: 9.5pt;
            font-weight: bold;
            color: #000000;
            letter-spacing: 0.2px;
            text-transform: uppercase;
            font-family: Helvetica, Arial, sans-serif;
            margin-top: 4px;
            line-height: 1.25;
            white-space: nowrap;
        }

        .kop-meta-cell {
            width: 160px;
            font-size: 7pt;
            font-family: Helvetica, Arial, sans-serif;
            line-height: 1.4;
            padding: 5px 6px;
            vertical-align: middle;
        }

        /* Nomor Dokumen & Referensi */
        .doc-header-box {
            text-align: center;
            margin-bottom: 13px;
        }

        .doc-number {
            font-size: 11.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            font-family: Helvetica, Arial, sans-serif;
        }

        .doc-ref {
            font-size: 8.5pt;
            color: #334155;
            font-style: italic;
            margin-top: 3px;
        }

        /* Paragraf Pembuka Legal */
        .opening-p {
            margin: 0 0 13px 0;
            text-align: justify;
            text-indent: 20px;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        /* Frame Informasi Sumber Dokumen (2 Panel Terkunci 50% / 50% - Struktur Baku & Stabil) */
        .ba-dossier-container {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 0 12px 0;
        }

        .dossier-col-left {
            width: 50%;
            vertical-align: top;
            padding-right: 5px;
            border: none;
        }

        .dossier-col-right {
            width: 50%;
            vertical-align: top;
            padding-left: 5px;
            border: none;
        }

        .dossier-panel {
            border: 1px solid #000000;
            background-color: #fafafa;
            padding: 5px 8px;
        }

        .dossier-panel-title {
            font-size: 8pt;
            font-weight: bold;
            font-family: Helvetica, Arial, sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 1px solid #000000;
            padding-bottom: 2px;
            margin-bottom: 4px;
            color: #000000;
        }

        .dossier-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8.5pt;
        }

        .dossier-table td {
            padding: 2px 0;
            vertical-align: top;
            border: none;
        }

        .dossier-table .lbl {
            font-weight: bold;
            width: 95px;
            white-space: nowrap;
        }

        .dossier-table .sep {
            width: 10px;
            text-align: center;
        }

        .dossier-table .val {
            width: auto;
        }

        /* Section Title & Tabel Rincian Barang */
        .section-title {
            font-weight: bold;
            font-size: 8.5pt;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            font-family: Helvetica, Arial, sans-serif;
        }

        .ba-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin: 0 0 13px 0;
            font-size: 8.5pt;
            page-break-inside: avoid;
        }

        .ba-table th {
            border: 1px solid #000000;
            padding: 6px 4px;
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            font-family: Helvetica, Arial, sans-serif;
        }

        .ba-table td {
            border: 1px solid #000000;
            padding: 5px 4px;
            vertical-align: middle;
        }

        .ba-table tfoot td {
            background-color: #fafafa;
            border-top: 1.5px solid #000000;
            font-weight: bold;
            padding: 6px 5px;
        }

        /* Kotak Pernyataan Resmi Administrasi & Status Keuangan */
        .ba-legal-box {
            border: 1px solid #000000;
            background-color: #fafafa;
            padding: 9px 12px;
            font-size: 9.5pt;
            line-height: 1.45;
            margin-bottom: 14px;
            text-align: justify;
            page-break-inside: avoid;
        }

        .ba-legal-box strong {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            letter-spacing: 0.02em;
            display: block;
            margin-bottom: 3px;
        }

        /* Tabel Tanda Tangan 4 Kolom Formal (Pas A4 - Baris Rata Sempurna) */
        .ba-signature-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
            margin-top: 4px;
        }

        .ba-signature-table td {
            width: 25%;
            text-align: center;
            padding: 0 4px;
            border: none;
        }

        .ba-sig-head-cell {
            vertical-align: top;
            height: 32px;
        }

        .ba-sig-space-cell {
            height: 46px;
        }

        .ba-sig-foot-cell {
            vertical-align: top;
        }

        .ba-sig-title {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.25;
        }

        .ba-sig-subrole {
            font-size: 6.5pt;
            font-style: italic;
            color: #475569;
            margin-top: 3px;
            line-height: 1.25;
        }

        .ba-sig-underline {
            border-bottom: 1px solid #000000;
            width: 82%;
            margin: 0 auto 3px auto;
        }

        .ba-sig-name {
            font-family: 'Times-Roman', Times, serif;
            font-weight: bold;
            font-size: 9pt;
            line-height: 1.25;
        }

        .ba-sig-caption {
            font-size: 6.5pt;
            color: #334155;
            line-height: 1.25;
            margin-top: 3px;
        }
    </style>
</head>
<body>
@php
    \Carbon\Carbon::setLocale('id');
    $tglRetur = $retur->retur_tgl ? \Carbon\Carbon::parse($retur->retur_tgl) : now();
    $hari = $tglRetur->translatedFormat('l');
    $tglLengkap = $tglRetur->translatedFormat('d F Y');

    $customerNm = $retur->customer?->customer_nm ?: ($retur->order?->customer?->customer_nm ?: 'Pelanggan Umum');
    $customerCd = $retur->customer?->customer_cd ?: ($retur->order?->customer?->customer_cd ?: '-');
    $soNo = $retur->order?->so_no ?: '-';
    $sjNo = $retur->order?->surat_jalan_no ?: ($retur->order?->pengeluaran?->first()?->pengeluaran_no ?: '-');
    $fakturNo = $retur->order?->faktur_no ?: ($retur->order?->faktur?->first()?->faktur_no ?: '-');
@endphp

    {{-- KOP PERUSAHAAN HACCP MIRASA (STANDAR RESMI FORM MFI) --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo-cell">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo PT Mirasa" class="kop-logo-img">
                @else
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo PT Mirasa" class="kop-logo-img">
                @endif
                <div class="kop-slogan">ENAK - GURIH - LEZAT</div>
            </td>
            <td class="kop-center-cell">
                <div class="kop-company-name">PT. MIRASA FOOD INDUSTRY</div>
                <div class="kop-form-name">
                    BERITA ACARA PENERIMAAN RETUR PENJUALAN
                </div>
            </td>
            <td class="kop-meta-cell">
                <div><strong>No. Dokumen:</strong> MFI/HACCP/BA-RET/{{ $tglRetur->format('Y') }}</div>
                <div><strong>Revisi:</strong> 01</div>
                <div><strong>Tgl Berlaku:</strong> 01-01-2024</div>
                <div><strong>Halaman:</strong> 1 dari 1</div>
            </td>
        </tr>
    </table>

    {{-- NOMOR DOKUMEN RESMI --}}
    <div class="doc-header-box">
        <div class="doc-number">
            NOMOR: {{ $retur->retur_no }}
        </div>
    </div>

    {{-- PARAGRAF PEMBUKA LEGAL --}}
    <p class="opening-p">
        Pada hari ini <strong>{{ $hari }}</strong>, tanggal <strong>{{ $tglLengkap }}</strong>, bertempat di area Gudang PT. Mirasa Food Industry, telah dilaksanakan proses serah terima, pemeriksaan fisik, dan verifikasi barang retur penjualan (customer returns) dari mitra pelanggan dengan rincian data sebagai berikut:
    </p>

    {{-- INFORMASI SUMBER DOKUMEN (2 PANEL TERKUNCI 50% / 50% - STRUKTUR STABIL TIDAK BERUBAH) --}}
    <table class="ba-dossier-container">
        <tr>
            <td class="dossier-col-left">
                <div class="dossier-panel">
                    <div class="dossier-panel-title">DATA MITRA PELANGGAN (CUSTOMER)</div>
                    <table class="dossier-table">
                        <colgroup>
                            <col style="width: 95px;">
                            <col style="width: 10px;">
                            <col style="width: auto;">
                        </colgroup>
                        <tr>
                            <td class="lbl">Pelanggan</td>
                            <td class="sep">:</td>
                            <td class="val"><strong>{{ $customerNm }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl">Kode Customer</td>
                            <td class="sep">:</td>
                            <td class="val" style="font-family: monospace;">{{ $customerCd }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">No. Faktur</td>
                            <td class="sep">:</td>
                            <td class="val" style="font-family: monospace;">{{ $fakturNo }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Tindakan Retur</td>
                            <td class="sep">:</td>
                            <td class="val">
                                <strong>{{ $retur->tindakan_cd === 'POTONG_TAGIHAN' ? 'Potong Piutang (Credit Note)' : 'Tukar Guling Fisik' }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="dossier-col-right">
                <div class="dossier-panel">
                    <div class="dossier-panel-title">DETAIL REFERENSI DOKUMEN &amp; GUDANG</div>
                    <table class="dossier-table">
                        <colgroup>
                            <col style="width: 95px;">
                            <col style="width: 10px;">
                            <col style="width: auto;">
                        </colgroup>
                        <tr>
                            <td class="lbl">Tgl. Retur</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $tglLengkap }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Ref. PO Penjualan</td>
                            <td class="sep">:</td>
                            <td class="val" style="font-family: monospace;">{{ $soNo }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">No. Surat Jalan</td>
                            <td class="sep">:</td>
                            <td class="val" style="font-family: monospace;">{{ $sjNo }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Gudang Masuk</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $retur->gudang?->gudang_nm ?? 'PT Mirasa Food Industry' }}</td>
                        </tr>
                        @if($retur->alasan_txt)
                        <tr>
                            <td class="lbl">Alasan Retur</td>
                            <td class="sep">:</td>
                            <td class="val">{{ $retur->alasan_txt }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABEL RINCIAN BARANG RETUR DITERIMA & STATUS FISIK --}}
    <div class="section-title">
        A. DAFTAR BARANG RETUR DITERIMA &amp; HASIL PEMERIKSAAN FISIK:
    </div>
    <table class="ba-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">NO</th>
                <th style="width: 75px; text-align: center;">KODE</th>
                <th style="text-align: left;">NAMA PRODUK JADI</th>
                <th style="width: 110px; text-align: left;">KONDISI / ALASAN</th>
                <th style="width: 105px; text-align: left;">DISPOSISI FISIK</th>
                <th style="width: 60px; text-align: center;">QTY</th>
                <th style="width: 75px; text-align: right;">HARGA</th>
                <th style="width: 85px; text-align: right;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retur->details as $index => $item)
                @php
                    $prodBatch = $item->production_batch_no ?: ($item->batch_no && !str_starts_with($item->batch_no, 'RET-') ? $item->batch_no : '-');
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $item->barang?->barang_cd ?? '-' }}</td>
                    <td>
                        <strong>{{ $item->barang?->barang_nm ?? '-' }}</strong>
                        @if($prodBatch && $prodBatch !== '-')
                            <div style="font-size: 7.5pt; color: #475569; margin-top: 1px;">
                                No. Batch: <span style="font-family: monospace; font-weight: bold; color: #000000;">{{ $prodBatch }}</span>
                            </div>
                        @endif
                        @if(!empty($item->catatan_txt))
                            <div style="font-size: 7.5pt; color: #475569; font-style: italic; margin-top: 1px;">Catatan: {{ $item->catatan_txt }}</div>
                        @endif
                    </td>
                    <td style="font-size: 8pt;">
                        <strong style="color: #991b1b;">{{ $item->kondisi_label }}</strong>
                    </td>
                    <td style="font-size: 8pt;">
                        @if($item->perlakuan_cd === 'DIMUSNAHKAN')
                            <span style="color: #dc2626; font-weight: bold;">Dimusnahkan Resmi</span>
                        @elseif($item->perlakuan_cd === 'CAMPUR')
                            <span style="color: #166534; font-weight: bold;">KW2: {{ number_format((float)$item->qty_masuk_persediaan, 0) }}</span><br>
                            <span style="color: #dc2626; font-weight: bold;">Musnah: {{ number_format((float)$item->qty_dimusnahkan, 0) }}</span>
                        @else
                            <span style="color: #166534; font-weight: bold;">Masuk Sortir KW 2</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold; font-family: monospace;">
                        {{ number_format((float) $item->qty_retur, 0) }} {{ $item->barang?->satuan_kecil ?? 'Karton' }}
                    </td>
                    <td style="text-align: right; font-family: monospace;">
                        Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace;">
                        Rp {{ number_format((float) $item->subtotal_nilai, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right; padding: 5px 6px;">TOTAL BARANG RETUR:</td>
                <td style="text-align: center; font-family: monospace; font-size: 9pt; padding: 5px 6px;">
                    {{ number_format((float) $retur->total_qty, 0) }}
                </td>
                <td style="text-align: right; font-size: 8pt; padding: 5px 6px;">TOTAL NILAI:</td>
                <td style="text-align: right; font-family: monospace; font-size: 9pt; font-weight: bold; padding: 5px 6px;">
                    Rp {{ number_format((float) $retur->total_nilai, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- KOTAK KETENTUAN RESMI ADMINISTRASI & STATUS KEUANGAN --}}
    <div class="ba-legal-box">
        <strong>KETENTUAN ADMINISTRASI SERAH TERIMA &amp; STATUS KEUANGAN:</strong>
        @if($retur->tindakan_cd === 'POTONG_TAGIHAN')
            Dokumen Berita Acara ini bertindak sebagai bukti sah serah terima fisik barang retur dan dasar penyesuaian komersial (Credit Note / Nota Kredit). Total nilai retur sebesar <strong>Rp {{ number_format((float) $retur->total_nilai, 0, ',', '.') }}</strong> secara otomatis memotong sisa tagihan/piutang pada pesanan <strong>{{ $soNo }}</strong> pada sistem ERP PT. Mirasa Food Industry. Stok fisik barang cacat yang diterima telah dicatat dan ditempatkan pada area karantina gudang untuk pemisahan lebih lanjut.
        @else
            Dokumen Berita Acara ini bertindak sebagai bukti sah serah terima fisik barang retur untuk keperluan penggantian barang sejenis (Tukar Guling). Sisa piutang/tagihan pelanggan tetap berjalan normal tanpa pemotongan komersial. Fisik barang retur telah masuk ke inventaris karantina gudang PT. Mirasa Food Industry.
        @endif
    </div>

    {{-- BAGIAN B: TANDA TANGAN 4 KOLOM FORMAL (RATA HORIZONTAL SEMPURNA) --}}
    <div class="section-title">
        B. PIHAK YANG MENERIMA, MEMERIKSA &amp; MENYETUJUI RETUR PENJUALAN:
    </div>
    <table class="ba-signature-table">
        <tr>
            <td class="ba-sig-head-cell">
                <div class="ba-sig-title">YANG MENYERAHKAN</div>
                <div class="ba-sig-subrole">Driver Ekspedisi / Pelanggan</div>
            </td>
            <td class="ba-sig-head-cell">
                <div class="ba-sig-title">PETUGAS SORTIR &amp; GUDANG</div>
                <div class="ba-sig-subrole">Penerima Fisik &amp; Administrasi Gudang</div>
            </td>
            <td class="ba-sig-head-cell">
                <div class="ba-sig-title">QUALITY CONTROL (QC)</div>
                <div class="ba-sig-subrole">Verifikator Cacat Mutu Produk</div>
            </td>
            <td class="ba-sig-head-cell">
                <div class="ba-sig-title">BAGIAN FINANCE / PENJUALAN</div>
                <div class="ba-sig-subrole">Verifikator Tagihan / Nota Kredit</div>
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
                <div class="ba-sig-name">( ........................................ )</div>
                <div class="ba-sig-caption">Perwakilan Mitra / Ekspedisi</div>
            </td>
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
        </tr>
    </table>

</body>
</html>
