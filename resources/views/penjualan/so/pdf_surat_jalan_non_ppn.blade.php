<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan (Non-PPN) - {{ $order->surat_jalan_no ?: $order->so_no }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #000000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Perusahaan */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 6px;
            border-collapse: collapse;
        }
        .kop-logo {
            width: 52px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-text {
            padding-left: 8px;
            vertical-align: middle;
        }
        .kop-company {
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
            letter-spacing: 0.5px;
            font-style: italic;
        }
        .kop-sub {
            font-size: 7.5pt;
            color: #000000;
            margin-top: 1px;
        }
        .doc-control-box {
            width: 170px;
            border: 1px solid #000000;
            border-collapse: collapse;
            font-size: 7pt;
        }
        .doc-control-box td {
            padding: 1px 4px;
            border: 1px solid #000000;
        }

        /* Header Sub & Customer Info */
        .header-info-table {
            width: 100%;
            margin-bottom: 4px;
            border-collapse: collapse;
        }
        .header-info-table td {
            vertical-align: top;
        }

        .customer-box {
            border: 1.5px solid #000000;
            padding: 4px 6px;
            font-size: 7.5pt;
            min-height: 54px;
        }

        .title-surat-jalan {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            margin: 2px 0 2px 0;
            text-decoration: underline;
        }

        .faktur-no-box {
            border: 1.5px solid #000000;
            border-collapse: collapse;
            width: 95%;
            font-size: 7.5pt;
            margin-top: 4px;
        }
        .faktur-no-box td {
            padding: 2px 5px;
            border: 1px solid #000000;
        }

        .meta-line {
            font-size: 7.5pt;
            margin-bottom: 4px;
            margin-top: 4px;
            border-bottom: 1px dotted #000000;
            padding-bottom: 2px;
        }

        /* Items Table (Sesuai Layout Fisik Non-PPN Mirasa) */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
            margin-bottom: 8px;
        }
        table.data-table th {
            background-color: #ffffff;
            color: #000000;
            font-weight: bold;
            text-align: center;
            padding: 4px 3px;
            font-size: 7.5pt;
            border: 1.5px solid #000000;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 3px 4px;
            border: 1px solid #000000;
            vertical-align: middle;
            font-size: 7.5pt;
        }

        /* Kolom Tanda Tangan */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .ttd-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 7.5pt;
        }
        .ttd-space {
            height: 48px;
        }
        .ttd-line {
            font-size: 7.5pt;
        }
    </style>
</head>
<body>
    {{-- Kop Surat Perusahaan (ISO Non-PPN Mirasa) --}}
    <table class="kop-table">
        <tr>
            @if(!empty($logoBase64))
                <td class="kop-logo">
                    <img src="{{ $logoBase64 }}" alt="Logo PT Mirasa" style="width: 44px; height: 44px;">
                </td>
            @endif
            <td class="kop-text">
                <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">Jl. Menur No. 2, Ambartawang, Mungkid, Magelang.</div>
                <div class="kop-sub">Telp. 0293-3288554, 3288556 Fax. 0293-782614</div>
            </td>
            <td style="width: 175px; text-align: right; vertical-align: top;">
                {{-- Box Kendali Mutu ISO NON-PPN Surat Jalan: MFI/OMS/7.0.2.0.2 --}}
                <table class="doc-control-box" align="right">
                    <tr>
                        <td style="width: 60px;">No. Dok</td>
                        <td>: MFI/OMS/7.0.2.0.2</td>
                    </tr>
                    <tr>
                        <td>No. Rev</td>
                        <td>: 0</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 01-07-2022</td>
                    </tr>
                    <tr>
                        <td>Halaman</td>
                        <td>: 1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Tanggal, Judul & Kotak Customer --}}
    <table class="header-info-table">
        <tr>
            <td style="width: 58%; vertical-align: middle;">
                <div class="title-surat-jalan">SURAT JALAN</div>
                
                {{-- Box Nomor Faktur & PO --}}
                <table class="faktur-no-box">
                    <tr>
                        <td style="width: 105px; font-weight: bold;">NO FAKTUR</td>
                        <td style="font-family: monospace; font-weight: bold;">: {{ $order->faktur_no ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">NOMOR PO</td>
                        <td style="font-family: monospace; font-weight: bold;">: {{ $order->customer_po_no ?: $order->so_no }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 42%; vertical-align: top;">
                <div style="text-align: right; font-size: 8pt; margin-bottom: 2px;">
                    Magelang, <strong>{{ $order->tgl_kirim_aktual ? $order->tgl_kirim_aktual->translatedFormat('d-M-Y') : ($order->tgl_kirim_estimasi ? $order->tgl_kirim_estimasi->translatedFormat('d-M-Y') : date('d-M-Y')) }}</strong>
                </div>
                <div class="customer-box">
                    <div style="font-size: 7pt; color: #334155;">Kepada Yth.</div>
                    <div style="font-weight: bold; font-size: 8.5pt; color: #000000; text-transform: uppercase;">
                        {{ $order->customer?->customer_nm }}
                    </div>
                    <div style="font-size: 7.5pt; color: #000000;">
                        {{ $order->customer?->kota ?: ($order->customer?->alamat_txt ?: '-') }}
                    </div>
                    <div style="margin-top: 3px; font-weight: bold; font-size: 7.5pt; border-top: 1px dashed #000000; padding-top: 2px;">
                        SOPIR : {{ $order->supir_nm ?: '-' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Keterangan Nopol Kendaraan --}}
    <div class="meta-line">
        <span style="font-size: 7.5pt;">Dengan Kendaraan Truck/Box No. Polisi : <strong>{{ $order->nopol_kendaraan ?: '........................' }}</strong></span>
        <span style="float: right; font-style: italic; font-size: 7.5pt;">kami kirim barang sbb.:</span>
    </div>

    {{-- Tabel Rincian Fisik Barang (Non-PPN Mirasa: IKAT | PACK | KARTON | BALL) --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No.</th>
                <th rowspan="2" style="text-align: left; padding-left: 6px;">NAMA BARANG</th>
                <th colspan="4" style="width: 210px;">BANYAKNYA</th>
                <th rowspan="2" style="width: 140px;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 45px;">IKAT</th>
                <th style="width: 55px;">PACK</th>
                <th style="width: 55px;">KARTON</th>
                <th style="width: 45px;">BALL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalKarton = 0;
                $rowCount = 0;
            @endphp
            @foreach($order->details as $idx => $dtl)
                @php
                    $qty = (float) $dtl->pesan_qty;
                    $totalKarton += $qty;
                    $rowCount++;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-weight: bold; padding-left: 6px;">
                        {{ $dtl->barang?->barang_nm }}
                    </td>
                    <td style="text-align: center; color: #64748b;">-</td>
                    <td style="text-align: center; color: #64748b;">-</td>
                    <td style="text-align: right; font-weight: bold; padding-right: 6px;">
                        {{ number_format($qty, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; color: #64748b;">-</td>
                    <td style="font-size: 7pt; color: #334155;">
                        {{ $dtl->catatan_txt ?: '' }}
                    </td>
                </tr>
            @endforeach

            {{-- Baris Kosong Tambahan Agar Menyerupai Continuous Form Fisik --}}
            @for($i = $rowCount; $i < 6; $i++)
                <tr>
                    <td style="text-align: center; color: #cbd5e1;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="2" style="text-align: center; padding: 4px; letter-spacing: 1px;">
                    TOTAL
                </td>
                <td style="text-align: center;">-</td>
                <td style="text-align: center;">-</td>
                <td style="text-align: right; padding-right: 6px; font-size: 8.5pt;">
                    {{ number_format($totalKarton, 0, ',', '.') }}
                </td>
                <td style="text-align: center;">-</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Kolom Tanda Tangan 3 Pihak (Penerima | Sopir/Sales | Bagian Gudang) --}}
    <table class="ttd-table">
        <tr>
            <td>
                <div>Penerima</div>
                <div class="ttd-space"></div>
                <div class="ttd-line">( ........................................ )</div>
            </td>
            <td>
                <div>Sopir / Sales</div>
                <div class="ttd-space"></div>
                <div class="ttd-line">( {{ $order->supir_nm ?: '........................................' }} )</div>
            </td>
            <td>
                <div>Bagian Gudang</div>
                <div class="ttd-space"></div>
                <div class="ttd-line">( ........................................ )</div>
            </td>
        </tr>
    </table>

    {{-- Footer Info Cetak --}}
    <div style="margin-top: 14px; font-size: 6.5pt; color: #64748b; border-top: 1px dashed #cbd5e1; padding-top: 2px; display: flex; justify-content: space-between;">
        <span>Dokumen Pengiriman Non-PPN resmi &bull; Dicetak pada {{ $printedAt }} oleh {{ $printedBy }}</span>
        <span style="float: right;">Hal 1 / 1</span>
    </div>
</body>
</html>
