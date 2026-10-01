<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PO - {{ $po->po_no }} - PT Mirasa Food Industry</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 15mm 18mm 15mm 18mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #000000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* HEADER BOX / KOP STANDAR HACCP */
        .kop-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .kop-table td {
            border: 1px solid #000000;
            vertical-align: middle;
        }
        .kop-logo {
            width: 110px;
            text-align: center;
            padding: 6px 4px;
        }
        .kop-title {
            text-align: center;
            padding: 8px 10px;
        }
        .company-name {
            font-size: 11pt;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.03em;
        }
        .form-label-title {
            font-size: 10pt;
            font-weight: 600;
            margin-top: 6px;
        }
        .form-doc-title {
            font-size: 12pt;
            font-weight: 800;
            margin-top: 1px;
        }
        .kop-meta {
            width: 250px;
            padding: 0;
        }
        .meta-subtable {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .meta-subtable td {
            border: none;
            border-bottom: 1px solid #000000;
            padding: 4px 6px;
        }
        .meta-subtable tr:last-child td {
            border-bottom: none;
        }
        .meta-label {
            width: 100px;
            border-right: 1px solid #000000 !important;
            font-weight: 600;
        }
        .meta-sep {
            width: 8px;
            text-align: center;
            border-right: none;
        }
        .meta-val {
            font-weight: 700;
            padding-left: 4px;
        }

        /* DOKUMEN HEADER: NO PO & KEPADA YTH */
        .doc-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .doc-header-table td {
            vertical-align: top;
            padding: 0;
        }

        /* JUDUL SURAT RESMI */
        .doc-title-block {
            text-align: center;
            margin-bottom: 22px;
        }
        .doc-title-main {
            font-size: 12.5pt;
            font-weight: 800;
            text-decoration: underline;
            letter-spacing: 0.05em;
        }
        .doc-title-sub {
            font-size: 11pt;
            font-weight: 800;
            letter-spacing: 0.05em;
            margin-top: 3px;
        }

        /* TABEL BARANG */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000000;
            margin-bottom: 30px;
        }
        .items-table th {
            border: 1px solid #000000;
            border-bottom: 2px solid #000000;
            padding: 6px 6px;
            font-weight: 800;
            font-size: 9.5pt;
            text-align: center;
            background: #ffffff;
            letter-spacing: 0.03em;
        }
        .items-table td {
            border: 1px solid #000000;
            padding: 7px 8px;
            font-size: 9.5pt;
            vertical-align: middle;
        }

        /* TANDA TANGAN & CATATAN NB */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .footer-table td {
            vertical-align: top;
            padding: 0;
        }
    </style>
</head>
<body>

    {{-- KOP TABEL STANDAR HACCP --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if (!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 58px; height: auto;" alt="Logo Cap Payung">
                @endif
                <div style="font-size: 6.5pt; font-weight: 800; color: #dc2626; margin-top: 2px; letter-spacing: 0.04em;">
                    ENAK - GURIH - LEZAT
                </div>
            </td>
            <td class="kop-title">
                <div class="company-name">PT. MIRASA FOOD INDUSTRY</div>
                <div class="form-label-title">Form</div>
                <div class="form-doc-title">Permintaan Barang</div>
            </td>
            <td class="kop-meta">
                <table class="meta-subtable">
                    <tr>
                        <td class="meta-label">Nomor dokumen</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Terbitan/Tgl</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">19-08-2021</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Revisi</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">00</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Halaman</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">1 dari 1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- NO PO & KEPADA YTH --}}
    <table class="doc-header-table">
        <tr>
            <td style="width: 50%;">
                <table style="border-collapse: collapse; font-size: 10pt;">
                    <tr>
                        <td style="width: 32px; font-weight: 700; vertical-align: top;">No.</td>
                        <td style="width: 8px; vertical-align: top;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; vertical-align: top;">PO</td>
                        <td style="font-weight: 700; vertical-align: top;">:</td>
                        <td style="font-weight: 800; font-family: monospace; font-size: 10.5pt; padding-left: 4px;">{{ $po->po_no }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding-left: 20px;">
                <div style="font-size: 10pt; font-weight: 700; margin-bottom: 2px;">
                    Kepada Yth:
                </div>
                <div style="font-size: 10.5pt; font-weight: 800;">
                    {{ $po->supplier?->supplier_nm ?? '-' }}
                </div>
                @if ($po->supplier?->alamat_txt)
                    <div style="font-size: 9pt; color: #334155; margin-top: 2px;">
                        {{ $po->supplier->alamat_txt }}
                    </div>
                @endif
                @if ($po->supplier?->telepon_no && $po->supplier?->telepon_no !== '-')
                    <div style="font-size: 9pt; color: #334155;">
                        Telp: {{ $po->supplier->telepon_no }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- JUDUL DOKUMEN --}}
    <div class="doc-title-block">
        <div class="doc-title-main">SURAT PERMINTAAN BARANG</div>
        <div class="doc-title-sub">PURCHASE ORDER</div>
    </div>

    {{-- TABEL BARANG --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45px;">NO</th>
                <th style="text-align: left; padding-left: 10px;">NAMA BARANG</th>
                <th style="width: 140px;">JUMLAH</th>
                <th style="width: 180px;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($po->details as $idx => $d)
                <tr>
                    <td style="text-align: center; font-weight: 700;">{{ $idx + 1 }}.</td>
                    <td style="padding-left: 10px;">
                        <strong style="color: #000000;">{{ $d->barang?->barang_nm ?? '-' }}</strong>
                        @if ($d->barang?->barang_cd)
                            <div style="font-size: 8pt; color: #64748b; font-family: monospace;">{{ $d->barang->barang_cd }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: 800;">
                        {{ number_format((float) $d->pesan_qty, 0, ',', '.') }} {{ $d->barang?->satuanDasar?->satuan_cd ?? 'KG' }}
                    </td>
                    <td style="font-size: 9pt; padding-left: 8px;">
                        {{ $d->catatan_txt ?: '-' }}
                    </td>
                </tr>
            @endforeach

            {{-- Baris Kosong Tambahan agar Tinggi Tabel Seimbang seperti template Word asli --}}
            @php $emptyRows = max(0, 3 - count($po->details)); @endphp
            @for ($i = 0; $i < $emptyRows; $i++)
                <tr>
                    <td style="text-align: center; color: transparent;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- TANDA TANGAN & CATATAN PENGIRIMAN --}}
    <table class="footer-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div style="font-size: 9.5pt; line-height: 1.45; margin-top: 15px;">
                    <strong>NB.</strong><br>
                    Barang di kirim ke alamat:<br>
                    <strong style="font-size: 10pt;">PT. MIRASA FOOD INDUSTRY</strong><br>
                    <strong>Magelang</strong><br>
                    <span style="font-size: 8.5pt; color: #475569;">Jl. Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah 56512</span>
                </div>
            </td>
            <td style="width: 45%; text-align: center; vertical-align: top;">
                <div style="font-size: 10pt;">
                    Magelang, {{ $po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}<br>
                    <strong>Mengetahui</strong>
                </div>
                <div style="height: 65px;"></div>
                <div style="font-size: 10pt;">
                    .......................................
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
