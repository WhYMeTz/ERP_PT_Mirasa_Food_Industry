<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pemusnahan Barang Retur - {{ $retur->retur_no }}</title>
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

        /* Kop Tabel HACCP Mirasa - Proporsional Pas A4 */
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

        /* Informasi Sumber Dokumen (Format Formal Sinkron 1 Tabel - Tanpa Kotak) */
        .ba-dossier-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 0 13px 0;
            border: none;
        }

        .ba-dossier-table td {
            padding: 2.5px 0;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.35;
            border: none;
        }

        .ba-dossier-table .lbl {
            font-weight: bold;
            white-space: nowrap;
            color: #1e293b;
        }

        .ba-dossier-table .sep {
            text-align: center;
            color: #475569;
            width: 10px;
        }

        .ba-dossier-table .val {
            color: #0f172a;
            word-wrap: break-word;
            padding-left: 3px;
        }

        .ba-dossier-table .spacer {
            width: 18px;
        }

        /* Table Rincian Barang Dimusnahkan (Fixed Proportions - 100% Selaras) */
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
            font-size: 9pt;
            page-break-inside: avoid;
        }

        .ba-table th {
            border: 1px solid #000000;
            padding: 6px 5px;
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
            padding: 6px 5px;
            vertical-align: middle;
        }

        .ba-table tfoot td {
            background-color: #fafafa;
            border-top: 1.5px solid #000000;
            font-weight: bold;
            padding: 6px 5px;
        }

        /* Kotak Pernyataan Resmi Keamanan Pangan */
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

    $bapScrapNo = app(\App\Services\Common\CodeGeneratorService::class)->generateBapScrapNo($tglRetur->toDateString(), $retur->retur_no);

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
            <col style="width: 235px;">
            <col style="width: 18px;">
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

    {{-- TABEL RINCIAN BARANG YANG DIMUSNAHKAN (FIXED PROPORTIONS - 100% TIDAK BERTUMPUK) --}}
    <div class="section-title">
        A. DAFTAR BARANG YANG DIMUSNAHKAN FISIK:
    </div>
    <table class="ba-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">NO</th>
                <th style="width: 80px; text-align: center;">KODE BARANG</th>
                <th style="text-align: left;">NAMA PRODUK JADI</th>
                <th style="width: 75px; text-align: center;">QTY MUSNAH</th>
                <th style="width: 55px; text-align: center;">SATUAN</th>
                <th style="width: 120px; text-align: left;">KONDISI / ALASAN</th>
                <th style="width: 125px; text-align: left;">METODE PEMUSNAHAN</th>
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
                        <div style="font-size: 7.5pt; color: #475569; margin-top: 2px;">
                            No. Batch: <span style="font-family: monospace; font-weight: bold; color: #000000;">{{ $dtl->production_batch_no ?: '-' }}</span>
                        </div>
                        @if(!empty($dtl->catatan_txt))
                            <div style="font-size: 7.5pt; color: #475569; font-style: italic; margin-top: 1px;">Catatan: {{ $dtl->catatan_txt }}</div>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; color: #dc2626;">
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
                <td colspan="3" style="text-align: right; padding: 5px 6px;">TOTAL KUANTITAS DIMUSNAHKAN:</td>
                <td style="text-align: right; font-family: monospace; font-size: 9pt; color: #dc2626; padding: 5px 6px;">
                    {{ number_format($totalQtyMusnah, 0) }}
                </td>
                <td colspan="3" style="color: #475569; font-size: 8pt; padding: 5px 6px;">
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
</body>
</html>
