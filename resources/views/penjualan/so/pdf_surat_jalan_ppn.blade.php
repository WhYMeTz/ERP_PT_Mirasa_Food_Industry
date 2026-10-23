<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan (Ber-PPN) - {{ $order->surat_jalan_no ?: $order->so_no }}</title>
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
            margin-bottom: 8px;
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
            margin-bottom: 6px;
            border-collapse: collapse;
        }
        .header-info-table td {
            vertical-align: top;
        }

        .customer-box {
            border: 1.5px solid #000000;
            padding: 4px 6px;
            font-size: 7.5pt;
            min-height: 58px;
        }

        .title-surat-jalan {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            margin: 4px 0 2px 0;
            text-decoration: underline;
        }

        .meta-line {
            font-size: 8pt;
            margin-bottom: 4px;
        }

        /* Items Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
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
            padding: 3.5px 4px;
            border: 1px solid #000000;
            vertical-align: middle;
            font-size: 7.5pt;
        }

        /* Tanda Tangan */
        .ttd-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .ttd-table td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 2px;
            font-size: 8pt;
        }
        .ttd-space {
            height: 48px;
        }
        .ttd-line {
            font-weight: bold;
        }
    </style>
</head>
<body>
    {{-- Kop Surat Perusahaan --}}
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
                <table class="doc-control-box" align="right">
                    <tr>
                        <td style="width: 60px;">No. Dok</td>
                        <td>: MFI/BMR/1.0.3.0.3</td>
                    </tr>
                    <tr>
                        <td>Rev.</td>
                        <td>: 0</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 01-07-2023</td>
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
            <td style="width: 58%; vertical-align: bottom;">
                <div class="title-surat-jalan">SURAT JALAN</div>
                <div style="font-size: 7.5pt; font-family: monospace; color: #334155; text-align: center;">
                    No. SJ: <strong>{{ $order->surat_jalan_no ?: ('SJ-' . $order->so_no) }}</strong> | Ref. PO: {{ $order->so_no }}
                </div>
            </td>
            <td style="width: 42%; vertical-align: top;">
                <div style="text-align: right; font-size: 8pt; margin-bottom: 3px;">
                    Magelang, <strong>{{ $order->tgl_kirim_aktual ? $order->tgl_kirim_aktual->translatedFormat('d-M-Y') : ($order->tgl_kirim_estimasi ? $order->tgl_kirim_estimasi->translatedFormat('d-M-Y') : date('d-M-Y')) }}</strong>
                </div>
                <div class="customer-box">
                    <div style="font-size: 7pt; color: #334155;">Kepada Yth.</div>
                    <div style="font-weight: bold; font-size: 8.5pt; color: #000000; text-transform: uppercase;">
                        {{ $order->customer?->customer_nm }}
                    </div>
                    <div style="font-size: 7.5pt; color: #000000;">
                        {{ $order->customer?->alamat_txt ?: ($order->customer?->kota ?: '-') }}
                    </div>
                    <div style="margin-top: 3px; font-weight: bold; font-size: 7.5pt; border-top: 1px dashed #000000; padding-top: 2px;">
                        SOPIR : {{ $order->supir_nm ?: '-' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Keterangan Nopol Kendaraan --}}
    <div class="meta-line" style="display: flex; justify-content: space-between; border-bottom: 1px dotted #000000; padding-bottom: 2px; margin-top: 4px;">
        <span>Dengan Kendaraan Truk/Mobil Box Nomor Polisi: <strong>{{ $order->nopol_kendaraan ?: '........................' }}</strong></span>
        <span style="float: right; font-style: italic;">kami kirim barang sbb.:</span>
    </div>

    {{-- Tabel Rincian Fisik Barang (Struktur Sesuai Format Mirasa Ber-PPN) --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No.</th>
                <th rowspan="2" style="text-align: left; padding-left: 6px;">NAMA BARANG</th>
                <th colspan="4" style="width: 220px;">BANYAKNYA</th>
                <th rowspan="2" style="width: 140px;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 50px;">IKAT</th>
                <th style="width: 65px;">BUNGKUS / DUS</th>
                <th style="width: 55px;">KARTON</th>
                <th style="width: 50px;">BALL</th>
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
                        {{ $dtl->catatan_txt ?: 'Kondisi Baik' }}
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

    {{-- Kolom Tanda Tangan 3 Pihak --}}
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
    <div style="margin-top: 12px; font-size: 6.5pt; color: #64748b; border-top: 1px dashed #cbd5e1; padding-top: 2px;">
        <span>Dokumen Ber-PPN resmi &bull; Dicetak pada {{ $printedAt }} oleh {{ $printedBy }}</span>
    </div>
</body>
</html>
