<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan {{ $order->so_no }} - PT Mirasa Food Industry</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace, Arial, sans-serif;
        }
        body {
            background: #f1f5f9;
            color: #0f172a;
            padding: 20px;
        }
        .receipt-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            padding: 25px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 9pt;
            color: #334155;
        }
        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 10px;
            text-decoration: underline;
        }
        .meta-table {
            width: 100%;
            margin-top: 12px;
            margin-bottom: 15px;
            font-size: 9.5pt;
        }
        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 15px;
        }
        .items-table th {
            border-top: 1px solid #0f172a;
            border-bottom: 1px solid #0f172a;
            padding: 6px 4px;
            text-align: left;
        }
        .items-table td {
            padding: 6px 4px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .calc-section {
            width: 100%;
            font-size: 9.5pt;
            border-top: 1px solid #0f172a;
            padding-top: 8px;
            margin-bottom: 20px;
        }
        .calc-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .calc-row.grand-total {
            border-top: 1px dashed #0f172a;
            margin-top: 6px;
            padding-top: 6px;
            font-weight: bold;
            font-size: 11pt;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
        }
        .sign-box {
            width: 45%;
        }
        .sign-space {
            height: 60px;
        }
        .no-print-bar {
            text-align: center;
            margin-bottom: 15px;
        }
        .btn-print {
            background: #059669;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10pt;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .receipt-container {
                border: none;
                box-shadow: none;
                padding: 10px;
                max-width: 100%;
            }
            .no-print-bar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">Cetak Dokumen Nota</button>
        <button class="btn-print" style="background: #64748b;" onclick="window.close()">Tutup</button>
    </div>

    <div class="receipt-container">
        <div class="header">
            <h1>PT. MIRASA FOOD INDUSTRY</h1>
            <p>Pabrik Keripik &amp; Olahan Singkong Nusantara</p>
            <p>Jl. Munggur No. 2 Ambartawang, Magelang &bull; Telp: (0293) 788xxx</p>
            <div class="doc-title">BUKTI PENJUALAN BAHAN PENOLONG &amp; BUMBU</div>
        </div>

        <table class="meta-table">
            <tr>
                <td style="width: 25%;">No. Transaksi</td>
                <td style="width: 2%;">:</td>
                <td style="width: 35%; font-weight: bold;">{{ $order->so_no }}</td>
                <td style="width: 18%;">Tanggal</td>
                <td style="width: 2%;">:</td>
                <td>{{ \Carbon\Carbon::parse($order->so_tgl)->translatedFormat('d M Y') }}</td>
            </tr>
            <tr>
                <td>Pembeli / Mitra</td>
                <td>:</td>
                <td style="font-weight: bold;">{{ $order->supir_nm ?: ($order->customer?->customer_nm ?? 'Pelanggan Umum') }}</td>
                <td>No. Faktur</td>
                <td>:</td>
                <td>{{ $order->faktur_no ?: '-' }}</td>
            </tr>
            <tr>
                <td>Gudang Asal</td>
                <td>:</td>
                <td>{{ $order->pengeluaran->first()?->gudang?->gudang_nm ?? 'PT Mirasa Food Industry' }}</td>
                <td>Pembayaran</td>
                <td>:</td>
                <td style="font-weight: bold;">LUNAS ({{ $order->top_txt ?: 'TUNAI' }})</td>
            </tr>
            @if ($order->catatan_txt)
                <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td colspan="4">{{ $order->catatan_txt }}</td>
                </tr>
            @endif
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">No</th>
                    <th>Nama Barang / Bahan</th>
                    <th style="width: 80px; text-align: right;">Qty</th>
                    <th style="width: 60px; text-align: center;">Satuan</th>
                    <th style="width: 100px; text-align: right;">Harga</th>
                    <th style="width: 110px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->details as $idx => $dtl)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                            <div style="font-size: 8pt; color: #475569;">{{ $dtl->barang?->barang_cd ?? '' }}</div>
                        </td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format((float) $dtl->pesan_qty, 2) }}</td>
                        <td style="text-align: center;">{{ $dtl->satuan }}</td>
                        <td style="text-align: right;">Rp {{ number_format((float) $dtl->harga_satuan, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format((float) $dtl->subtotal_tagihan, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="calc-section">
            <div class="calc-row">
                <span>Subtotal Nilai Bruto:</span>
                <span>Rp {{ number_format((float) $order->subtotal_bruto, 0, ',', '.') }}</span>
            </div>
            @if ($order->diskon_total > 0)
                <div class="calc-row">
                    <span>Total Diskon Item:</span>
                    <span>- Rp {{ number_format((float) $order->diskon_total, 0, ',', '.') }}</span>
                </div>
            @endif
            @if ($order->potongan_nominal > 0)
                <div class="calc-row">
                    <span>Potongan Tambahan:</span>
                    <span>- Rp {{ number_format((float) $order->potongan_nominal, 0, ',', '.') }}</span>
                </div>
            @endif
            @if ($order->ppn_nominal > 0)
                <div class="calc-row">
                    <span>PPN (11%):</span>
                    <span>+ Rp {{ number_format((float) $order->ppn_nominal, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="calc-row grand-total">
                <span>TOTAL PEMBAYARAN:</span>
                <span>Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="signatures">
            <div class="sign-box">
                <p>Penerima / Pembeli,</p>
                <div class="sign-space"></div>
                <p>( {{ $order->supir_nm ?: ($order->customer?->customer_nm ?? '........................') }} )</p>
            </div>
            <div class="sign-box">
                <p>Kasir / Gudang Bahan,</p>
                <div class="sign-space"></div>
                <p>( {{ $order->created_by ?: auth()->user()?->nama_lengkap ?? 'Kasir MFI' }} )</p>
            </div>
        </div>

        <div style="margin-top: 25px; text-align: center; font-size: 7.5pt; color: #64748b; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
            Barang yang sudah dibeli dan diserahterimakan dari gudang pabrik tidak dapat ditukar/dikembalikan tanpa persetujuan resmi.
        </div>
    </div>
</body>
</html>
