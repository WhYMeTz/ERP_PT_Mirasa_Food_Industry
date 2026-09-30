<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Barang Masuk - ERP PT Mirasa</title>
    <style>
        @page {
            margin: 10mm 12mm 12mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Perusahaan */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #059669;
            padding-bottom: 8px;
            margin-bottom: 10px;
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
            font-size: 12pt;
            font-weight: bold;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 8pt;
            color: #64748b;
            margin-top: 2px;
        }

        /* Filter info table */
        .filter-table {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
            font-size: 7.5pt;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 4px 8px;
        }

        /* Data table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 8px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 5px;
            font-size: 7.5pt;
            border: 1px solid #0f172a;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 3.5px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 7.5pt;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        .badge-batch {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 1px 3px;
            border-radius: 2px;
            font-family: monospace;
            font-size: 7pt;
            font-weight: bold;
        }

        /* Signatures */
        .sign-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .sign-table td {
            text-align: center;
            vertical-align: top;
            width: 33%;
            font-size: 8pt;
        }
        .sign-space {
            height: 45px;
        }
        .sign-line {
            border-bottom: 1px solid #334155;
            width: 70%;
            margin: 0 auto 3px auto;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="height: 44px; width: auto;">
                @else
                    <div style="font-size: 16pt; font-weight: bold; color: #059669;">MIRASA</div>
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">
                    Pabrik Pengolahan Makanan &amp; Keripik Singkong &bull; Ambartawang, Magelang, Jawa Tengah<br>
                    Telepon: (0293) 788-234 &bull; Email: logistic@mirasafood.co.id
                </div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">BUKU REKAPITULASI BARANG MASUK</div>
                <div class="doc-number">Dicetak: {{ $printedAt }} WIB</div>
            </td>
        </tr>
    </table>

    {{-- FILTER INFO --}}
    <table class="filter-table">
        <tr>
            <td style="padding: 4px;">
                <strong>Gudang:</strong> {{ $gudangNm ?? 'Semua Gudang' }} &bull;
                <strong>Filter Pencarian:</strong> {{ !empty($search) ? '"' . $search . '"' : 'Semua Data' }} &bull;
                <strong>Total Data:</strong> {{ count($items) }} transaksi barang masuk
            </td>
            <td style="text-align: right; padding: 4px;">
                <strong>Operator:</strong> {{ $printedBy }}
            </td>
        </tr>
    </table>

    {{-- TABEL DATA BARANG MASUK --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 65px;">Tanggal</th>
                <th style="width: 105px;">No. GRN</th>
                <th style="width: 90px;">No. Surat Jalan</th>
                <th style="width: 85px;">Ref PO</th>
                <th style="min-width: 110px;">Supplier Pengirim</th>
                <th style="min-width: 130px;">Nama Bahan Baku &amp; Kode</th>
                <th style="width: 90px;">No. Batch Fisik</th>
                <th style="width: 55px;">Expired</th>
                <th style="width: 55px; text-align: right;">Qty</th>
                <th style="width: 40px;">Satuan</th>
                <th style="width: 80px;">Gudang</th>
                <th style="width: 85px; text-align: right;">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandQty = 0;
                $grandTotal = 0;
            @endphp
            @forelse($items as $idx => $row)
                @php
                    $qty = (float) $row->terima_qty;
                    $subtotal = (float) ($row->subtotal_tagihan ?: ($qty * (float)($row->harga_netto ?: $row->harga_nominal)));
                    $grandQty += $qty;
                    $grandTotal += $subtotal;
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td>{{ $row->header?->terima_tgl ? \Carbon\Carbon::parse($row->header->terima_tgl)->format('d/m/Y') : '-' }}</td>
                    <td style="font-family: monospace; font-weight: bold; color: #0284c7;">{{ $row->header?->terima_no ?? '-' }}</td>
                    <td style="font-family: monospace; font-size: 7pt;">{{ $row->header?->suratjalan_no ?: '-' }}</td>
                    <td style="font-family: monospace; font-size: 7pt;">{{ $row->header?->po?->po_no ?? 'Non-PO' }}</td>
                    <td>{{ $row->header?->supplier?->supplier_nm ?? '-' }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $row->barang?->barang_nm ?? '-' }}</strong>
                        <div style="font-family: monospace; font-size: 6.5pt; color: #64748b;">{{ $row->barang?->barang_cd }}</div>
                    </td>
                    <td><span class="badge-batch">{{ $row->batch_no }}</span></td>
                    <td style="font-size: 7pt; color: #475569;">{{ $row->expired_tgl ? \Carbon\Carbon::parse($row->expired_tgl)->format('d/m/Y') : '-' }}</td>
                    <td style="text-align: right; font-weight: bold; color: #059669;">{{ number_format($qty, 2, ',', '.') }}</td>
                    <td style="font-size: 7pt; color: #475569;">{{ $row->barang?->satuanDasar?->satuan_nm ?? '-' }}</td>
                    <td style="font-size: 7pt;">{{ $row->header?->gudang?->gudang_nm ?? '-' }}</td>
                    <td style="text-align: right; font-family: monospace; font-weight: bold;">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align: center; color: #94a3b8; padding: 15px;">
                        Tidak ada transaksi barang masuk pada kriteria filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot style="background: #f8fafc; font-weight: bold;">
            <tr>
                <td colspan="9" style="text-align: right; padding: 5px;">TOTAL AKUMULASI PENERIMAAN:</td>
                <td style="text-align: right; color: #059669; padding: 5px;">{{ number_format($grandQty, 2, ',', '.') }}</td>
                <td colspan="2"></td>
                <td style="text-align: right; font-family: monospace; color: #059669; font-size: 8.5pt; padding: 5px;">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="sign-table">
        <tr>
            <td>
                <div>Disiapkan Oleh,</div>
                <div style="color: #64748b; font-size: 7pt;">(Administrasi Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold;">{{ $printedBy }}</div>
            </td>
            <td>
                <div>Diperiksa Oleh,</div>
                <div style="color: #64748b; font-size: 7pt;">(Supervisor Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold;">Supervisor Logistik</div>
            </td>
            <td>
                <div>Mengetahui,</div>
                <div style="color: #64748b; font-size: 7pt;">(Kepala Pabrik / Manager)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold;">Factory Manager</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 10px; font-size: 6.5pt; color: #94a3b8; text-align: center; border-top: 1px dotted #cbd5e1; padding-top: 3px;">
        Laporan ini dicetak secara otomatis dari Sistem ERP PT. Mirasa Food Industry dan merupakan dokumen resmi inventaris gudang.
    </div>

</body>
</html>
