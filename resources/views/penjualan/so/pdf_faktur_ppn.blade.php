<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan (Ber-PPN) - {{ $order->faktur_no ?: $order->so_no }}</title>
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

        /* Sub Header & Meta Tables */
        .title-faktur {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            margin: 3px 0 6px 0;
            text-decoration: underline;
        }

        .meta-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .meta-header-table td {
            vertical-align: top;
        }

        .faktur-no-box {
            border: 1.5px solid #000000;
            border-collapse: collapse;
            width: 95%;
            font-size: 8pt;
        }
        .faktur-no-box td {
            padding: 3px 6px;
            border: 1px solid #000000;
        }

        .customer-box {
            border: 1.5px solid #000000;
            padding: 4px 6px;
            font-size: 7.5pt;
            min-height: 58px;
        }

        /* Items Data Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 6px;
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

        /* Bottom Section: Payment & Commercial Calculations */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .bottom-table td {
            vertical-align: top;
        }

        .payment-info-box {
            border: 1px solid #000000;
            padding: 4px 6px;
            font-size: 7pt;
            background: #ffffff;
            margin-top: 4px;
            line-height: 1.3;
        }

        .calc-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-size: 8pt;
        }
        .calc-table td {
            padding: 3px 6px;
            border: 1px solid #000000;
        }
        .calc-table .label-col {
            font-weight: bold;
            width: 48%;
            background: #ffffff;
        }
        .calc-table .val-col {
            text-align: right;
            font-weight: bold;
            font-family: monospace;
            font-size: 8.5pt;
        }
        .calc-table .grand-total-row td {
            background: #ffffff;
            font-size: 9pt;
            font-weight: bold;
            border-top: 2px solid #000000;
        }

        /* Tanda Tangan */
        .ttd-box {
            text-align: center;
            margin-top: 6px;
            font-size: 8pt;
        }
        .ttd-space {
            height: 42px;
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

    {{-- Judul Dokumen --}}
    <div class="title-faktur">FAKTUR PENJUALAN</div>

    {{-- Meta Header: No Faktur / PO di Kiri, Customer di Kanan --}}
    <table class="meta-header-table">
        <tr>
            <td style="width: 58%; padding-right: 10px;">
                <table class="faktur-no-box">
                    <tr>
                        <td style="width: 110px; font-weight: bold;">NOMOR FAKTUR</td>
                        <td style="font-family: monospace; font-weight: bold; font-size: 9pt;">: {{ $order->faktur_no ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">NOMOR PO</td>
                        <td style="font-family: monospace; font-weight: bold;">: {{ $order->customer_po_no ?: $order->so_no }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">SYARAT BAYAR</td>
                        <td style="font-weight: bold;">: {{ $order->top_txt ?: ($order->customer?->top_txt ?: '-') }}</td>
                    </tr>
                </table>
                <div style="font-size: 7.5pt; color: #475569; margin-top: 3px;">
                    Ref. Surat Jalan: {{ $order->surat_jalan_no ?: '-' }}
                </div>
            </td>
            <td style="width: 42%;">
                <div style="text-align: right; font-size: 8pt; margin-bottom: 3px;">
                    Magelang, <strong>{{ $order->so_tgl ? $order->so_tgl->translatedFormat('d-M-Y') : date('d-M-Y') }}</strong>
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

    {{-- Tabel Rincian Barang Faktur (Sesuai Layout Ber-PPN Mirasa) --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No.</th>
                <th rowspan="2" style="text-align: left; padding-left: 6px;">NAMA BARANG</th>
                <th colspan="4" style="width: 200px;">BANYAKNYA</th>
                <th rowspan="2" style="width: 75px;">HARGA</th>
                <th rowspan="2" style="width: 95px;">JUMLAH (Rp)</th>
            </tr>
            <tr>
                <th style="width: 45px;">IKAT</th>
                <th style="width: 60px;">BUNGKUS</th>
                <th style="width: 50px;">KARTON</th>
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
                    $lineTotal = (float) ($dtl->subtotal_netto ?: ($qty * $dtl->harga_satuan));
                    $rowCount++;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-weight: bold; padding-left: 6px;">
                        {{ $dtl->barang?->barang_nm }}
                    </td>
                    <td style="text-align: center; color: #64748b;">-</td>
                    <td style="text-align: center; color: #64748b;">-</td>
                    <td style="text-align: right; font-weight: bold; padding-right: 5px;">
                        {{ number_format($qty, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; color: #64748b;">-</td>
                    <td style="text-align: right; font-family: monospace; padding-right: 5px;">
                        {{ number_format($dtl->harga_satuan, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; padding-right: 5px;">
                        {{ number_format($lineTotal, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            {{-- Baris Kosong Tambahan Agar Rapi & Proporsional Sesuai Form Fisik --}}
            @for($i = $rowCount; $i < 6; $i++)
                <tr>
                    <td style="text-align: center; color: #cbd5e1;">&nbsp;</td>
                    <td>&nbsp;</td>
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
                <td style="text-align: right; padding-right: 5px; font-size: 8.5pt;">
                    {{ number_format($totalKarton, 0, ',', '.') }}
                </td>
                <td style="text-align: center;">-</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Bagian Bawah: Sistem Pembayaran & Rekening di Kiri, Rekapitulasi Ber-PPN di Kanan --}}
    <table class="bottom-table">
        <tr>
            {{-- Sisi Kiri Bawah --}}
            <td style="width: 52%; padding-right: 12px;">
                <div style="font-size: 7.5pt; font-weight: bold; margin-bottom: 2px;">
                    Sistem pembayaran :
                    <span style="font-weight: normal; margin-left: 12px;">
                        [ &nbsp; ] Tunai &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [ &nbsp; ] C.A / DG
                    </span>
                </div>

                {{-- Box Rekening BCA Mirasa (Sesuai Gambar Asli) --}}
                <div class="payment-info-box">
                    <strong>NB :</strong><br>
                    Untuk pembayaran via <strong>Transfer / BG</strong><br>
                    mohon ke rekening an. <strong>PT Mirasa Food Industry</strong><br>
                    <strong>AC. 1845175551 BCA</strong>
                </div>

                {{-- Tanda Tangan Penerima / Customer --}}
                <div class="ttd-box" style="margin-top: 10px;">
                    <div>Diterima,</div>
                    <div class="ttd-space"></div>
                    <div>( .................................................... )</div>
                </div>
            </td>

            {{-- Sisi Kanan Bawah: Kalkulasi Ber-PPN Resmi --}}
            <td style="width: 48%;">
                <table class="calc-table">
                    <tr>
                        <td class="label-col">DISCOUNT</td>
                        <td class="val-col">
                            {{ $order->diskon_total > 0 ? ('Rp ' . number_format($order->diskon_total, 0, ',', '.')) : 'Rp  -' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">DPP</td>
                        <td class="val-col">
                            Rp {{ number_format($order->dpp_nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">PPN 11%</td>
                        <td class="val-col">
                            Rp {{ number_format($order->ppn_nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="grand-total-row">
                        <td class="label-col" style="letter-spacing: 0.5px;">Jumlah dibayar</td>
                        <td class="val-col" style="font-size: 9.5pt;">
                            Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>

                {{-- Tanda Tangan Bagian Keuangan --}}
                <div class="ttd-box" style="margin-top: 10px;">
                    <div>Bag. Keuangan</div>
                    <div class="ttd-space"></div>
                    <div>( .................................................... )</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Footer Info --}}
    <div style="margin-top: 12px; font-size: 6.5pt; color: #64748b; border-top: 1px dashed #cbd5e1; padding-top: 2px; display: flex; justify-content: space-between;">
        <span>Dokumen Faktur Ber-PPN resmi &bull; Dicetak pada {{ $printedAt }} oleh {{ $printedBy }}</span>
        <span style="float: right;">Hal 1 / 1</span>
    </div>
</body>
</html>
