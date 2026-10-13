<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Rekap HPP Pengemasan (Packaging) - {{ strtoupper($monthName) }} {{ $year }}</title>
    <style>
        @page {
            size: legal landscape;
            margin: 6mm 8mm 8mm 8mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 6.5pt;
            color: #0f172a;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }

        .kop-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .kop-text {
            vertical-align: middle;
            text-align: left;
        }
        .kop-company {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .kop-sub {
            font-size: 6.5pt;
            color: #64748b;
        }
        .kop-title-box {
            text-align: right;
            vertical-align: middle;
            white-space: nowrap;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
        }
        .doc-meta {
            font-size: 6pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* Summary KPI Bar */
        .kpi-table {
            width: 100%;
            margin-bottom: 6px;
            border-collapse: collapse;
        }
        .kpi-box {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            background: #f8fafc;
            border-radius: 4px;
            text-align: center;
        }
        .kpi-label {
            font-size: 5.5pt;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kpi-val {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 1px;
        }

        /* Data Grid Table */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6pt;
        }
        .grid-table th, .grid-table td {
            border: 0.5px solid #94a3b8;
            padding: 3px 4px;
            text-align: right;
            white-space: nowrap;
        }
        .grid-table th {
            text-align: center;
            font-weight: bold;
            background-color: #0284c7;
            color: #ffffff;
        }

        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold; }
        .row-total {
            background-color: #fef3c7;
            font-weight: bold;
        }

        /* Footer signatures */
        .sign-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            font-size: 6.5pt;
        }
        .sign-box {
            width: 25%;
            text-align: center;
            vertical-align: top;
            padding: 4px;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td class="kop-text">
                <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">Sistem Informasi Manufaktur &amp; Akuntansi Biaya Pengemasan Barang Jadi</div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">BUKU REKAPITULASI HPP PENGEMASAN (PACKAGING)</div>
                <div class="doc-meta">Periode: {{ strtoupper($monthName) }} {{ $year }} &bull; Dicetak: {{ date('d/m/Y H:i') }} &bull; Oleh: {{ Auth::user()->username ?? 'Admin' }}</div>
            </td>
        </tr>
    </table>

    @php
        $tot = $report['totals'];
    @endphp

    {{-- 4 KARTU KPI --}}
    <table class="kpi-table">
        <tr>
            <td style="width: 25%; padding-right: 4px;">
                <div class="kpi-box" style="border-left: 3px solid #0284c7;">
                    <div class="kpi-label">Total Biaya Kemas</div>
                    <div class="kpi-val">Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 4px; padding-left: 4px;">
                <div class="kpi-box" style="border-left: 3px solid #059669;">
                    <div class="kpi-label">Hasil Kemas (FG)</div>
                    <div class="kpi-val">{{ number_format($tot['total_karton'], 0, ',', '.') }} Ktn ({{ number_format($tot['total_pcs'], 0, ',', '.') }} Pcs)</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 4px; padding-left: 4px;">
                <div class="kpi-box" style="border-left: 3px solid #d97706;">
                    <div class="kpi-label">Biaya Kemas / Karton</div>
                    <div class="kpi-val">Rp {{ number_format($tot['biaya_kemas_per_karton'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 4px;">
                <div class="kpi-box" style="border-left: 3px solid #10b981;">
                    <div class="kpi-label">HPP Riil per Karton</div>
                    <div class="kpi-val">Rp {{ number_format($tot['rata_hpp_karton'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABEL DATA HARIAN REKAPITULASI HPP PACKAGING --}}
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 5%;">Hari</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 10%;">No PCK</th>
                <th style="width: 14%;">Target Produk Jadi (FG)</th>
                <th style="width: 9%;">Batch No</th>
                <th style="width: 8%;">Biaya WIP (Rp)</th>
                <th style="width: 8%;">Biaya Kemas (Rp)</th>
                <th style="width: 7%;">Upah (Rp)</th>
                <th style="width: 6%;">Lain (Rp)</th>
                <th style="width: 9%;">Total Biaya (Rp)</th>
                <th style="width: 6%;">Hasil (Ktn)</th>
                <th style="width: 6%;">Hasil (Pcs)</th>
                <th style="width: 8%;">HPP / Ktn (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['days'] as $d)
                @php $has = $d['has_data']; @endphp
                <tr style="{{ !$has ? 'color: #94a3b8; background: #fcfcfc;' : '' }}">
                    <td class="text-center font-bold">{{ $d['hari_nm'] }}</td>
                    <td class="text-center">{{ date('d/m/Y', strtotime($d['date'])) }}</td>
                    <td class="text-left font-bold">{{ $has ? $d['primary_pack_no'] : '-' }}</td>
                    <td class="text-left">{{ $has ? $d['primary_product_nm'] : '-' }}</td>
                    <td class="text-center">{{ $has ? $d['primary_batch_no'] : '-' }}</td>
                    <td>{{ $has ? number_format($d['total_biaya_wip'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $has ? number_format($d['total_biaya_kemasan'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $has ? number_format($d['total_biaya_tenaga_kerja'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $has ? number_format($d['total_biaya_lain'], 0, ',', '.') : '-' }}</td>
                    <td class="font-bold">{{ $has ? number_format($d['total_biaya_produksi'], 0, ',', '.') : '-' }}</td>
                    <td class="font-bold text-center">{{ $has ? number_format($d['hasil_karton'], 0, ',', '.') : '-' }}</td>
                    <td class="text-center">{{ $has ? number_format($d['hasil_pcs'], 0, ',', '.') : '-' }}</td>
                    <td class="font-bold">{{ $has ? number_format($d['hpp_per_karton'], 0, ',', '.') : '-' }}</td>
                </tr>
            @endforeach

            {{-- ROW TOTAL --}}
            <tr class="row-total">
                <td colspan="5" class="text-center">TOTAL KONSOLIDASI BULAN {{ strtoupper($monthName) }} {{ $year }}</td>
                <td>{{ number_format($tot['total_biaya_wip'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['total_biaya_kemasan'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['total_biaya_tenaga_kerja'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['total_biaya_lain'], 0, ',', '.') }}</td>
                <td class="font-bold">{{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}</td>
                <td class="font-bold text-center">{{ number_format($tot['total_karton'], 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($tot['total_pcs'], 0, ',', '.') }}</td>
                <td class="font-bold">Rp {{ number_format($tot['rata_hpp_karton'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN FORMAL --}}
    <table class="sign-table">
        <tr>
            <td class="sign-box">
                <div>Dibuat Oleh,</div>
                <div style="font-weight: bold; margin-top: 1px;">Admin Packaging / Gudang</div>
                <div style="height: 38px;"></div>
                <div style="text-decoration: underline; font-weight: bold;">( {{ Auth::user()->name ?? 'Operator Packaging' }} )</div>
                <div style="color: #64748b; font-size: 5.5pt;">Petugas Administrasi</div>
            </td>
            <td class="sign-box">
                <div>Diperiksa Oleh,</div>
                <div style="font-weight: bold; margin-top: 1px;">Supervisor Produksi / QC</div>
                <div style="height: 38px;"></div>
                <div style="text-decoration: underline; font-weight: bold;">( ........................................ )</div>
                <div style="color: #64748b; font-size: 5.5pt;">Kepala Bagian Kemas</div>
            </td>
            <td class="sign-box">
                <div>Divalidasi Oleh,</div>
                <div style="font-weight: bold; margin-top: 1px;">Cost Accounting / Finansial</div>
                <div style="height: 38px;"></div>
                <div style="text-decoration: underline; font-weight: bold;">( ........................................ )</div>
                <div style="color: #64748b; font-size: 5.5pt;">Akunting Biaya &amp; HPP</div>
            </td>
            <td class="sign-box">
                <div>Disetujui Oleh,</div>
                <div style="font-weight: bold; margin-top: 1px;">Factory Manager / Direktur</div>
                <div style="height: 38px;"></div>
                <div style="text-decoration: underline; font-weight: bold;">( ........................................ )</div>
                <div style="color: #64748b; font-size: 5.5pt;">Pimpinan Operasional</div>
            </td>
        </tr>
    </table>

</body>
</html>
