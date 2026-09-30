<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Master Barang - PT Mirasa Food Industry</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
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
            border-bottom: 2px solid #0284c7;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-logo {
            width: 50px;
            vertical-align: middle;
        }
        .kop-text {
            padding-left: 12px;
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
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-meta {
            font-size: 7pt;
            color: #64748b;
            margin-top: 3px;
        }

        /* Info Ringkasan Dokumen */
        .summary-bar {
            width: 100%;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
            font-size: 7.5pt;
        }
        .summary-bar td {
            vertical-align: middle;
        }

        /* Tabel Data Barang */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th {
            background-color: #0284c7;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 6px 4px;
            border: 1px solid #0284c7;
            text-align: center;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 4.5px 5px;
            border: 1px solid #cbd5e1;
            font-size: 7.5pt;
            vertical-align: middle;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Helpers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-mono { font-family: 'Courier', monospace; font-weight: bold; }
        .badge-active {
            color: #047857;
            font-weight: bold;
        }
        .badge-inactive {
            color: #dc2626;
            font-weight: bold;
        }

        /* Footer Halaman */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 15px;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-size: 6.5pt;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    {{-- KOP LAPORAN RESMI --}}
    <table class="kop-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="kop-logo">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 48px; height: 48px; object-fit: contain;">
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-company">PT MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">Sistem Manajemen Terpadu ERP Pabrik Keripik Singkong &amp; Makanan Ringan</div>
                <div class="kop-sub">Katalog Resmi Master Data Inventaris &amp; Komoditas Produksi</div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">KATALOG MASTER BARANG</div>
                <div class="doc-meta">Tanggal Cetak: <strong>{{ $printedAt }} WIB</strong></div>
                <div class="doc-meta">Operator: <strong>{{ $printedBy }}</strong></div>
            </td>
        </tr>
    </table>

    {{-- BARIS INFO RINGKASAN --}}
    <table class="summary-bar" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 33%;">
                Total Komoditas: <strong>{{ $barangs->count() }} Item</strong>
            </td>
            <td style="width: 34%; text-align: center;">
                Status: <strong>{{ $barangs->where('active_st', true)->count() }} Aktif</strong> | {{ $barangs->where('active_st', false)->count() }} Nonaktif
            </td>
            <td style="width: 33%; text-align: right;">
                Format: <strong>A4 Landscape Official ERP</strong>
            </td>
        </tr>
    </table>

    {{-- TABEL DATA MASTER BARANG --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Kode Barang</th>
                <th>Nama Komoditas / Barang</th>
                <th style="width: 110px;">Kategori / Jenis</th>
                <th style="width: 60px;">Satuan Dasar</th>
                <th style="width: 100px;">Satuan Beli &amp; Konversi</th>
                <th style="width: 65px;">Min. Stok</th>
                <th style="width: 90px;">Harga Beli Std</th>
                <th style="width: 45px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barangs as $idx => $b)
                @php
                    $konversiTxt = '-';
                    if ($b->satuan_besar_id && $b->satuanBesar) {
                        $rasio = (float) ($b->konversi_qty ?? 1);
                        $konversiTxt = "1 {$b->satuanBesar->satuan_nm} = {$rasio} {$b->satuanDasar?->satuan_nm}";
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-mono" style="color: #0369a1;">{{ $b->barang_cd }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $b->barang_nm }}</strong>
                    </td>
                    <td>{{ $b->jenisBarang->jenis_barang_nm ?? '-' }}</td>
                    <td class="text-center font-mono">{{ $b->satuanDasar?->satuan_nm ?? '-' }}</td>
                    <td class="text-center" style="font-size: 7pt; color: #475569;">{{ $konversiTxt }}</td>
                    <td class="text-right font-mono">
                        {{ number_format((float) ($b->batas_minimum_qty ?? 0), 2, ',', '.') }}
                    </td>
                    <td class="text-right font-mono" style="color: #0f172a;">
                        Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($b->active_st)
                            <span class="badge-active">Aktif</span>
                        @else
                            <span class="badge-inactive">Nonaktif</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #64748b;">
                        Belum ada data barang yang terdaftar di sistem.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT} | Dicetak otomatis melalui Sistem ERP PT Mirasa Food Industry";
            $size = 7;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 22;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.5, 0.5, 0.5));
        }
    </script>

</body>
</html>
