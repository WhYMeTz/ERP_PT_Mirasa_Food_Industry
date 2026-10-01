<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Barang Keluar - PT Mirasa Food Industry</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 8mm 10mm 12mm 10mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        .kop-table {
            width: 100%;
            border-bottom: 2px solid #7f1d1d;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .kop-logo { width: 50px; vertical-align: middle; }
        .kop-text { padding-left: 10px; vertical-align: middle; text-align: left; }
        .kop-company { font-size: 13pt; font-weight: bold; color: #0f172a; letter-spacing: 0.5px; }
        .kop-sub { font-size: 7pt; color: #64748b; margin-top: 1px; }
        .kop-title-box { text-align: right; vertical-align: middle; white-space: nowrap; }
        .doc-title { font-size: 12pt; font-weight: bold; color: #7f1d1d; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-number { font-size: 7.5pt; color: #64748b; margin-top: 2px; }

        .filter-table {
            width: 100%;
            margin-bottom: 6px;
            border-collapse: collapse;
            font-size: 7.5pt;
            background: #fff7f7;
            border: 1px solid #fecaca;
            table-layout: fixed;
        }
        .filter-table td { padding: 4px 8px; vertical-align: middle; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        table.data-table thead { display: table-header-group; }
        table.data-table tr { page-break-inside: avoid; }
        table.data-table th {
            background-color: #7f1d1d;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 4px;
            font-size: 7.5pt;
            border: 1px solid #6b0000;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data-table td {
            padding: 3.5px 4px;
            border: 1px solid #fecaca;
            vertical-align: middle;
            font-size: 7pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data-table tr:nth-child(even) td { background-color: #fff7f7; }

        .badge-batch {
            font-family: monospace;
            font-size: 6.8pt;
            font-weight: bold;
            color: #7f1d1d;
            display: inline-block;
            word-break: break-all;
        }

        .sign-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            page-break-inside: avoid;
            table-layout: fixed;
        }
        .sign-table td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            font-size: 7.5pt;
            padding: 0 10px;
        }
        .sign-space { height: 40px; }
        .sign-line { border-bottom: 1px solid #334155; width: 75%; margin: 0 auto 3px auto; }

        .footer-note {
            margin-top: 8px;
            font-size: 6.5pt;
            color: #94a3b8;
            text-align: center;
            border-top: 1px dotted #fecaca;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="height: 42px; width: auto;">
                @else
                    <div style="font-size: 15pt; font-weight: bold; color: #7f1d1d;">MIRASA</div>
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
                <div class="doc-title">LAPORAN BARANG KELUAR</div>
                <div class="doc-number">Dicetak: {{ $printedAt }} WIB &bull; Oleh: {{ $printedBy }}</div>
            </td>
        </tr>
    </table>

    {{-- FILTER INFO --}}
    <table class="filter-table">
        <tr>
            <td style="width: 70%;">
                <strong>Gudang:</strong> {{ $gudangNm ?? 'Semua Gudang' }} &bull;
                @if(!empty($tujuan))
                    <strong>Tujuan:</strong> {{ $tujuan }} &bull;
                @endif
                <strong>Pencarian:</strong> {{ !empty($search) ? '"' . $search . '"' : 'Semua Data' }} &bull;
                <strong>Jumlah Transaksi:</strong> {{ count($items) }} baris
            </td>
            <td style="width: 30%; text-align: right; color: #475569;">
                <strong>Standar Dokumen:</strong> Register Outbound (FIFO)
            </td>
        </tr>
    </table>

    {{-- TABEL DATA BARANG KELUAR --}}
    <table class="data-table">
        <colgroup>
            <col style="width: 3%;">   {{-- No --}}
            <col style="width: 9%;">   {{-- No. Dokumen --}}
            <col style="width: 8%;">   {{-- Tanggal --}}
            <col style="width: 11%;">  {{-- Kode Batch --}}
            <col style="width: 9%;">   {{-- Kode Barang --}}
            <col style="width: 16%;">  {{-- Nama Barang --}}
            <col style="width: 9%;">   {{-- Jenis --}}
            <col style="width: 13%;">  {{-- Keterangan --}}
            <col style="width: 7%;">   {{-- Qty Keluar --}}
            <col style="width: 7%;">   {{-- Satuan --}}
            <col style="width: 9%;">   {{-- Harga Satuan --}}
            <col style="width: 9%;">   {{-- Total Harga --}}
        </colgroup>
        <thead>
            <tr>
                <th style="text-align: center;">No</th>
                <th>No. Dokumen</th>
                <th style="text-align: center;">Tanggal</th>
                <th>Kode Batch</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th style="text-align: right; white-space: nowrap;">Qty Keluar</th>
                <th style="text-align: center;">Satuan</th>
                <th style="text-align: right; white-space: nowrap;">Harga Satuan</th>
                <th style="text-align: right; white-space: nowrap;">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandQty = 0;
                $grandTotal = 0;
            @endphp
            @forelse($items as $idx => $row)
                @php
                    $qty      = (float) ($row->qty_keluar ?? 0);
                    $harga    = (float) ($row->harga_satuan ?? 0);
                    $total    = (float) ($row->total_harga ?: ($qty * $harga));
                    $grandQty   += $qty;
                    $grandTotal += $total;

                    $tglStr  = $row->header?->pakai_tgl
                        ? \Carbon\Carbon::parse($row->header->pakai_tgl)->translatedFormat('j-M-Y')
                        : '-';
                    $keterangan = strtoupper($row->header?->tujuan_pemakaian ?? 'PEMAKAIAN BAHAN');
                    $satuan  = $row->barang?->satuanDasar?->satuan_cd ?? ($row->barang?->satuanDasar?->satuan_nm ?? '-');
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-size: 6.8pt; color: #7f1d1d; font-weight: bold;">
                        {{ $row->header?->pakai_no ?? '-' }}
                    </td>
                    <td style="text-align: center; white-space: nowrap;">{{ $tglStr }}</td>
                    <td>
                        <span class="badge-batch">{{ $row->batch_no ?? '-' }}</span>
                    </td>
                    <td style="font-family: monospace; font-size: 6.8pt; color: #334155;">{{ $row->barang?->barang_cd ?? '-' }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $row->barang?->barang_nm ?? '-' }}</strong>
                    </td>
                    <td style="color: #334155; text-transform: uppercase; font-size: 6.8pt;">
                        {{ $row->barang?->jenisBarang?->jenis_barang_nm ?? '-' }}
                    </td>
                    <td style="color: #475569; font-size: 6.8pt;">{{ $keterangan }}</td>
                    <td style="text-align: right; font-weight: bold; color: #7f1d1d; white-space: nowrap;">
                        {{ number_format($qty, 2, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-size: 6.5pt; color: #64748b;">{{ $satuan }}</td>
                    <td style="text-align: right; font-family: monospace; font-size: 6.8pt; white-space: nowrap;">
                        {{ number_format($harga, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: bold; color: #7f1d1d; font-size: 7pt; white-space: nowrap;">
                        {{ number_format($total, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; color: #94a3b8; padding: 15px;">
                        Tidak ada transaksi barang keluar pada kriteria filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot style="background: #fee2e2; font-weight: bold;">
            <tr>
                <td colspan="8" style="text-align: right; padding: 4px 6px; font-size: 7.5pt; color: #0f172a; white-space: nowrap;">
                    TOTAL:
                </td>
                <td style="text-align: right; color: #7f1d1d; padding: 4px; font-size: 7.5pt; white-space: nowrap;">
                    {{ number_format($grandQty, 2, ',', '.') }}
                </td>
                <td></td>
                <td></td>
                <td style="text-align: right; font-family: monospace; color: #7f1d1d; font-size: 7.5pt; padding: 4px; white-space: nowrap;">
                    Rp {{ number_format($grandTotal, 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- TANDA TANGAN LEGALITAS --}}
    <table class="sign-table">
        <tr>
            <td>
                <div>Disiapkan Oleh,</div>
                <div style="color: #64748b; font-size: 6.8pt;">(Petugas Administrasi Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold;">{{ $printedBy }}</div>
            </td>
            <td>
                <div>Diperiksa Oleh,</div>
                <div style="color: #64748b; font-size: 6.8pt;">(Supervisor Gudang / Logistik)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold;">Supervisor Logistik</div>
            </td>
            <td>
                <div>Mengetahui,</div>
                <div style="color: #64748b; font-size: 6.8pt;">(Manager Operasional / Pabrik)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold;">Factory Manager</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Laporan ini dicetak secara otomatis dari Sistem ERP PT. Mirasa Food Industry dan merupakan dokumen resmi register barang keluar gudang.
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT} | Dicetak otomatis melalui Sistem ERP PT Mirasa Food Industry";
            $size = 7;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.5, 0.5, 0.5));
        }
    </script>

</body>
</html>
