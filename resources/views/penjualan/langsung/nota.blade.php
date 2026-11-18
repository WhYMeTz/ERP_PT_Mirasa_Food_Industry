<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan - {{ $order->so_no }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace, monospace;
        }

        body {
            background: #f1f5f9;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .receipt-container {
            width: 320px;
            background: #ffffff;
            padding: 16px;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            color: #000000;
            font-size: 11px;
            line-height: 1.35;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .company-sub {
            font-size: 9px;
            margin-top: 2px;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            font-size: 10px;
        }

        .meta-table td {
            vertical-align: top;
            padding: 1px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 10px;
        }

        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-weight: 700;
        }

        .items-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .calc-section {
            border-top: 1px dashed #000;
            padding-top: 6px;
            margin-bottom: 8px;
            font-size: 10px;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            padding: 1px 0;
        }

        .calc-row.grand-total {
            font-size: 13px;
            font-weight: 800;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 0;
            margin: 4px 0;
        }

        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 9px;
        }

        .no-print-bar {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
            z-index: 999;
        }

        .btn-print {
            background: #059669;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            font-family: sans-serif;
        }

        .btn-close {
            background: #475569;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            font-family: sans-serif;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 80mm;
            }

            .no-print-bar {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Nota</button>
    <button class="btn-close" onclick="window.close()">Tutup</button>
</div>

<div class="receipt-container">
    {{-- HEADER TOKO / PABRIK --}}
    <div class="header">
        <div class="company-name">PT MIRASA FOOD INDUSTRY</div>
        <div class="company-sub">Produsen Keripik &amp; Makanan Ringan</div>
        <div class="company-sub">Kebumen, Jawa Tengah - Indonesia</div>
    </div>

    {{-- META TRANSAKSI --}}
    <table class="meta-table">
        <tr>
            <td style="width: 55px;">No. Nota</td>
            <td style="width: 6px;">:</td>
            <td><strong>{{ $order->so_no }}</strong></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($order->so_tgl)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($order->created_dt)->format('H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $order->created_by ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Pembeli</td>
            <td>:</td>
            <td>{{ $order->supir_nm ?: ($order->customer->customer_nm ?? 'Pelanggan Tunai') }} @if($order->nopol_kendaraan) ({{ $order->nopol_kendaraan }}) @endif</td>
        </tr>
    </table>

    {{-- DAFTAR ITEM --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Item Produk</th>
                <th style="text-align: center; width: 35px;">Qty</th>
                <th style="text-align: right; width: 60px;">Harga</th>
                <th style="text-align: right; width: 70px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->details as $item)
                <tr>
                    <td colspan="4" style="padding-top: 4px; font-weight: 700;">
                        {{ $item->barang->barang_nm ?? "Barang #{$item->barang_id}" }}
                    </td>
                </tr>
                <tr>
                    <td style="color: #444; font-size: 9px; padding-left: 6px;">
                        @if ($item->diskon_persen > 0)
                            Disc: {{ number_format($item->diskon_persen, 1) }}%
                        @endif
                    </td>
                    <td style="text-align: center;">{{ number_format($item->qty, 0) }}</td>
                    <td style="text-align: right;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->subtotal_netto, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PERHITUNGAN --}}
    <div class="calc-section">
        <div class="calc-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($order->subtotal_bruto, 0, ',', '.') }}</span>
        </div>
        @if ($order->total_potongan > 0)
            <div class="calc-row">
                <span>Diskon Tambahan:</span>
                <span>-Rp {{ number_format($order->total_potongan, 0, ',', '.') }}</span>
            </div>
        @endif
        @if ($order->ppn_nominal > 0)
            <div class="calc-row">
                <span>DPP:</span>
                <span>Rp {{ number_format($order->dpp_nominal, 0, ',', '.') }}</span>
            </div>
            <div class="calc-row">
                <span>PPN ({{ $order->ppn_persen }}%):</span>
                <span>Rp {{ number_format($order->ppn_nominal, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="calc-row grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}</span>
        </div>
        <div class="calc-row">
            <span>Metode Bayar:</span>
            <span><strong>{{ strtoupper($order->top_txt) }} (LUNAS)</strong></span>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div>*** TERIMA KASIH ***</div>
        <div style="margin-top: 3px;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</div>
        <div style="margin-top: 4px; font-size: 8px; color: #555;">Dicetak otomatis oleh Sistem ERP Mirasa</div>
    </div>
</div>

<script>
    // Otomatis munculkan print dialog saat jendela nota dibuka
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 300);
    };
</script>

</body>
</html>
