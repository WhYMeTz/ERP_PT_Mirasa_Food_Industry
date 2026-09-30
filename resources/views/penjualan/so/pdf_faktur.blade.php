<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan - {{ $order->faktur_no ?: $order->so_no }}</title>
    <style>
        @page {
            margin: 10mm 12mm 12mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Perusahaan */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-logo {
            width: 50px;
            vertical-align: middle;
        }
        .kop-text {
            padding-left: 10px;
            vertical-align: middle;
        }
        .kop-company {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .kop-sub {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }
        .kop-title-box {
            text-align: right;
            vertical-align: middle;
        }
        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Meta Information Table */
        .info-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .info-card {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            background: #f8fafc;
            border-radius: 4px;
        }

        /* Items Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 10px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            font-size: 8pt;
            border: 1px solid #0f172a;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 4.5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 8pt;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Summary Box */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .summary-table td {
            padding: 2.5px 6px;
            font-size: 8pt;
        }
        .grand-total {
            font-size: 10pt;
            font-weight: bold;
            color: #0284c7;
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
        }

        /* Tanda Tangan */
        .ttd-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .ttd-table td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 4px;
        }
        .ttd-space {
            height: 55px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .ttd-role {
            font-size: 7.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>
    {{-- Kop Surat Perusahaan --}}
    <table class="kop-table">
        <tr>
            @if(!empty($logoBase64))
                <td class="kop-logo">
                    <img src="{{ $logoBase64 }}" alt="Logo PT Mirasa" style="width: 48px; height: 48px;">
                </td>
            @endif
            <td class="kop-text">
                <div class="kop-company">PT MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">
                    Produsen &amp; Pengolahan Makanan Ringan Berkualitas • Cap Payung<br>
                    Jl. Raya Industri No. 88, Purwokerto, Jawa Tengah • Telp: (0281) 638899
                </div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">FAKTUR PENJUALAN</div>
                <div class="doc-number">{{ $order->faktur_no ?: ('INV-' . $order->so_no) }}</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">
                    Tanggal: {{ $order->so_tgl?->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Meta Customer & Order Info --}}
    <table class="info-table">
        <tr>
            <td style="width: 55%; padding-right: 8px;">
                <div class="info-card">
                    <div style="font-size: 7.5pt; font-weight: bold; color: #64748b; text-transform: uppercase;">Ditujukan Kepada (Customer):</div>
                    <div style="font-size: 9.5pt; font-weight: bold; color: #0f172a; margin-top: 2px;">
                        {{ $order->customer?->customer_nm }} ({{ $order->customer?->customer_cd }})
                    </div>
                    <div style="font-size: 8pt; color: #334155; margin-top: 2px;">
                        <strong>Alamat:</strong> {{ $order->customer?->alamat_txt ?: '-' }}
                    </div>
                    <div style="font-size: 8pt; color: #334155;">
                        <strong>Kontak:</strong> {{ $order->customer?->kontak_no ?: '-' }}
                    </div>
                </div>
            </td>
            <td style="width: 45%; padding-left: 8px;">
                <div class="info-card">
                    <div style="font-size: 7.5pt; font-weight: bold; color: #64748b; text-transform: uppercase;">Referensi Pesanan:</div>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 2px; font-size: 8pt;">
                        <tr>
                            <td style="color: #64748b; width: 45%; padding: 1px 0;">No. Pesanan (SO):</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">{{ $order->so_no }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">No. PO Customer:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">{{ $order->customer_po_no ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Tgl Pengiriman:</td>
                            <td style="color: #0f172a; padding: 1px 0;">{{ $order->tgl_kirim_estimasi ? $order->tgl_kirim_estimasi->format('d/m/Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Items Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th>Nama Barang &amp; Kode</th>
                <th style="width: 50px; text-align: right;">Jumlah</th>
                <th style="width: 50px; text-align: center;">Satuan</th>
                <th style="width: 80px; text-align: right;">@Harga Satuan</th>
                <th style="width: 55px; text-align: right;">Diskon</th>
                <th style="width: 60px; text-align: right;">Potongan</th>
                <th style="width: 45px; text-align: center;">PPN</th>
                <th style="width: 90px; text-align: right;">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->details as $idx => $dtl)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td>
                        <div style="font-weight: bold; color: #0f172a;">{{ $dtl->barang?->barang_nm }}</div>
                        <div style="font-size: 7pt; color: #64748b; font-family: monospace;">{{ $dtl->barang?->barang_cd }}</div>
                    </td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($dtl->pesan_qty, 0, ',', '.') }}</td>
                    <td style="text-align: center; color: #475569;">{{ $dtl->barang?->satuanDasar?->satuan_nm ?? 'Unit' }}</td>
                    <td style="text-align: right;">{{ number_format($dtl->harga_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #dc2626;">{{ $dtl->diskon_persen > 0 ? number_format($dtl->diskon_persen, 1) . '%' : '-' }}</td>
                    <td style="text-align: right; color: #dc2626;">{{ $dtl->potongan_nominal > 0 ? number_format($dtl->potongan_nominal, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: center;">{{ $dtl->ppn_tipe === 'PPN_11' ? '11%' : '0%' }}</td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ number_format($dtl->subtotal_tagihan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Bottom Section: Catatan & Commercial Summary --}}
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 12px;">
                <div style="font-size: 7.5pt; font-weight: bold; color: #475569; text-transform: uppercase;">Catatan &amp; Pembayaran:</div>
                <div style="border: 1px solid #cbd5e1; background: #fafafa; padding: 6px 8px; border-radius: 4px; font-size: 7.5pt; color: #334155; margin-top: 3px; min-height: 50px;">
                    {{ $order->catatan_txt ?: 'Pembayaran ditransfer ke rekening resmi PT Mirasa Food Industry (BCA / Mandiri). Bukti transfer harap dikirimkan ke bagian Finance.' }}
                </div>
                <div style="font-size: 7pt; color: #64748b; margin-top: 6px;">
                    Dicetak pada: {{ $printedAt }} | Oleh: {{ $printedBy }}
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="summary-table">
                    <tr>
                        <td style="color: #475569;">Subtotal Bruto</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($order->subtotal_bruto, 0, ',', '.') }}</td>
                    </tr>
                    @if($order->diskon_total > 0)
                        <tr>
                            <td style="color: #dc2626;">Total Diskon Item</td>
                            <td style="text-align: right; color: #dc2626; font-weight: bold;">- Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($order->potongan_nominal > 0)
                        <tr>
                            <td style="color: #dc2626;">Potongan Faktur</td>
                            <td style="text-align: right; color: #dc2626; font-weight: bold;">- Rp {{ number_format($order->potongan_nominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="color: #475569; border-top: 1px dashed #cbd5e1;">Dasar Pengenaan Pajak (DPP)</td>
                        <td style="text-align: right; font-weight: bold; border-top: 1px dashed #cbd5e1;">Rp {{ number_format($order->dpp_nominal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #475569;">PPN 11%</td>
                        <td style="text-align: right; font-weight: bold; color: #16a34a;">+ Rp {{ number_format($order->ppn_nominal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td style="padding: 5px 6px;">TOTAL TAGIHAN</td>
                        <td style="text-align: right; padding: 5px 6px;">Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Kolom Tanda Tangan --}}
    <table class="ttd-table">
        <tr>
            <td>
                <div class="ttd-role">Disiapkan Oleh (Sales / Admin),</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $order->created_by ?: 'Bagian Penjualan' }}</div>
                <div class="ttd-role">PT Mirasa Food Industry</div>
            </td>
            <td>
                <div class="ttd-role">Disetujui Oleh (Finance / Manajer),</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">Finance &amp; Accounting</div>
                <div class="ttd-role">PT Mirasa Food Industry</div>
            </td>
            <td>
                <div class="ttd-role">Diterima &amp; Disetujui Oleh,</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $order->customer?->customer_nm }}</div>
                <div class="ttd-role">Pihak Customer / Pembeli</div>
            </td>
        </tr>
    </table>
</body>
</html>
