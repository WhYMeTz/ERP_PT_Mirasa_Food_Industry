<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Penjualan - {{ $dateInfo['label'] }}</title>
    <style>
        @page {
            margin: 28px 32px;
            size: a4 portrait;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 8pt;
            color: #475569;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0284c7;
            text-align: right;
            text-transform: uppercase;
        }
        .doc-meta {
            font-size: 8pt;
            color: #64748b;
            text-align: right;
        }
        .metrics-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .metric-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            width: 25%;
        }
        .metric-title {
            font-size: 7.5pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
        }
        .metric-value {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        .metric-sub {
            font-size: 7pt;
            color: #64748b;
            margin-top: 2px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            margin: 14px 0 6px 0;
            border-left: 3px solid #0284c7;
            padding-left: 6px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .data-table td {
            font-size: 8pt;
            padding: 5px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .sign-table {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sign-box {
            width: 33.33%;
            text-align: center;
            font-size: 8.5pt;
        }
        .sign-space {
            height: 48px;
        }
    </style>
</head>
<body>

    {{-- HEADER KOP RESMI --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div class="company-name">PT MIRASA FOOD INDUSTRY</div>
                <div class="company-sub">Industri Pengolahan Makanan Ringan &amp; Keripik Tradisional Modern</div>
                <div class="company-sub">Dukuh Krajan RT 02/RW 01, Gatak, Delanggu, Klaten, Jawa Tengah</div>
            </td>
            <td style="width: 40%; vertical-align: middle;">
                <div class="doc-title">Rekapitulasi Penjualan</div>
                <div class="doc-meta">Periode: {{ $dateInfo['label'] }}</div>
                <div class="doc-meta">Saluran: {{ $channel === 'ALL' ? 'Semua (PO + Non-PO)' : ($channel === 'PO' ? 'PO Langganan' : 'Penjualan Langsung') }}</div>
                <div class="doc-meta">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    {{-- 4 KOTAK RINGKASAN METRIK --}}
    <table class="metrics-table">
        <tr>
            <td class="metric-box">
                <div class="metric-title">Total Omzet Penjualan</div>
                <div class="metric-value">Rp {{ number_format($metrics['total_omzet'], 0, ',', '.') }}</div>
                <div class="metric-sub">{{ number_format($metrics['total_transaksi']) }} transaksi selesai</div>
            </td>
            <td class="metric-box">
                <div class="metric-title">PO Langganan (B2B)</div>
                <div class="metric-value">Rp {{ number_format($metrics['po_omzet'], 0, ',', '.') }}</div>
                <div class="metric-sub">{{ $metrics['po_pct'] }}% kontribusi omzet</div>
            </td>
            <td class="metric-box">
                <div class="metric-title">Penjualan Langsung / Tunai</div>
                <div class="metric-value">Rp {{ number_format($metrics['direct_omzet'], 0, ',', '.') }}</div>
                <div class="metric-sub">{{ $metrics['direct_pct'] }}% kontribusi omzet</div>
            </td>
            <td class="metric-box">
                <div class="metric-title">Volume &amp; Sisa Piutang</div>
                <div class="metric-value">{{ number_format($metrics['total_qty'], 0) }} Unit</div>
                <div class="metric-sub">Piutang: Rp {{ number_format($metrics['total_piutang'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    {{-- BAGIAN 1: REKAPITULASI PENJUALAN PER PRODUK --}}
    <div class="section-title">1. Rekapitulasi Produk Barang Jadi Terjual</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 80px;">Kode</th>
                <th>Nama Produk Varian</th>
                <th style="width: 70px;" class="text-right">Qty</th>
                <th style="width: 45px;">Satuan</th>
                <th style="width: 90px;" class="text-right">Rata-rata Harga</th>
                <th style="width: 105px;" class="text-right">Total Omzet</th>
                <th style="width: 60px;" class="text-right">Pangsa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($salesByProduct as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $p->barang_cd }}</td>
                    <td>{{ $p->barang_nm }}</td>
                    <td class="text-right font-bold">{{ number_format((float) $p->total_qty, 0) }}</td>
                    <td>{{ $p->satuan_nm }}</td>
                    <td class="text-right">Rp {{ number_format((float) $p->avg_harga, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $p->total_omzet, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $p->kontribusi_pct }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #94a3b8;">
                        Tidak ada transaksi penjualan produk pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($salesByProduct->count() > 0)
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="3" class="text-right">TOTAL VOLUME &amp; OMZET:</td>
                    <td class="text-right">{{ number_format($salesByProduct->sum('total_qty'), 0) }}</td>
                    <td>Unit</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($salesByProduct->sum('total_omzet'), 0, ',', '.') }}</td>
                    <td class="text-right">100.0%</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- BAGIAN 2: REKAPITULASI PEMBELIAN KONSUMEN / LANGGANAN --}}
    <div class="section-title">2. Rekapitulasi Kontribusi Pelanggan (Customer)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 90px;">Kode Customer</th>
                <th>Nama Pelanggan</th>
                <th style="width: 70px;">Klasifikasi</th>
                <th style="width: 65px;" class="text-center">Transaksi</th>
                <th style="width: 110px;" class="text-right">Total Pembelian</th>
                <th style="width: 100px;" class="text-right">Sisa Piutang</th>
                <th style="width: 60px;" class="text-right">Pangsa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($salesByCustomer as $idx => $c)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $c->customer_cd }}</td>
                    <td>{{ $c->customer_nm }}</td>
                    <td>{{ $c->klasifikasi }}</td>
                    <td class="text-center">{{ $c->total_transaksi }}</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $c->total_omzet, 0, ',', '.') }}</td>
                    <td class="text-right">{{ (float) $c->total_sisa_piutang > 0 ? 'Rp ' . number_format((float) $c->total_sisa_piutang, 0, ',', '.') : 'Lunas' }}</td>
                    <td class="text-right">{{ $c->kontribusi_pct }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #94a3b8;">
                        Tidak ada transaksi pelanggan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- LEMBAR TANDA TANGAN --}}
    <table class="sign-table">
        <tr>
            <td class="sign-box">
                <div>Dibuat Oleh,</div>
                <div style="color: #64748b; font-size: 7.5pt;">Admin Penjualan &amp; Kasir</div>
                <div class="sign-space"></div>
                <div class="font-bold">( _______________________ )</div>
            </td>
            <td class="sign-box">
                <div>Diperiksa &amp; Diverifikasi,</div>
                <div style="color: #64748b; font-size: 7.5pt;">Manajer Penjualan &amp; Distribusi</div>
                <div class="sign-space"></div>
                <div class="font-bold">( _______________________ )</div>
            </td>
            <td class="sign-box">
                <div>Disetujui Oleh,</div>
                <div style="color: #64748b; font-size: 7.5pt;">Direktur Operasional</div>
                <div class="sign-space"></div>
                <div class="font-bold">( _______________________ )</div>
            </td>
        </tr>
    </table>

</body>
</html>
