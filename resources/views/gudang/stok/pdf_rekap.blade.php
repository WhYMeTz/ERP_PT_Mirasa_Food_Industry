<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Stok Komoditas &amp; Valuasi Persediaan - PT Mirasa Food Industry</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 8mm 10mm 12mm 10mm;
        }
        * {
            box-sizing: border-box;
        }
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
            border-bottom: 2px solid #0f766e;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .kop-logo {
            width: 50px;
            vertical-align: middle;
        }
        .kop-text {
            padding-left: 10px;
            vertical-align: middle;
            text-align: left;
        }
        .kop-company {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .kop-sub {
            font-size: 7pt;
            color: #64748b;
            margin-top: 1px;
        }
        .kop-title-box {
            text-align: right;
            vertical-align: middle;
            white-space: nowrap;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .filter-table {
            width: 100%;
            margin-bottom: 6px;
            border-collapse: collapse;
            font-size: 7.5pt;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            table-layout: fixed;
        }
        .filter-table td {
            padding: 4px 8px;
            vertical-align: middle;
        }

        .kpi-mini-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        .kpi-mini-card {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            padding: 5px 8px;
            text-align: center;
        }
        .kpi-mini-label {
            font-size: 6.5pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }
        .kpi-mini-value {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 4px;
            font-size: 7pt;
            border: 1px solid #0d5f58;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.data-table td {
            padding: 3.5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 7pt;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .badge-status {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: 700;
            text-align: center;
        }
        .badge-aman {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-rendah {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-habis {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
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
        .sign-space {
            height: 38px;
        }
        .sign-line {
            border-bottom: 1px solid #334155;
            width: 75%;
            margin: 0 auto 3px auto;
        }

        .footer-note {
            margin-top: 8px;
            font-size: 6.5pt;
            color: #94a3b8;
            text-align: center;
            border-top: 1px dotted #cbd5e1;
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
                    <div style="font-size: 15pt; font-weight: bold; color: #0f766e;">MIRASA</div>
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
                <div class="doc-title">REKAPITULASI STOK &amp; VALUASI PERSEDIAAN</div>
                <div class="doc-number">Dicetak: {{ $printedAt }} WIB &bull; Oleh: {{ $printedBy }}</div>
            </td>
        </tr>
    </table>

    {{-- FILTER INFO --}}
    <table class="filter-table">
        <tr>
            <td style="width: 70%;">
                <strong>Gudang:</strong> {{ $gudangNm ?? 'Semua Gudang' }} &bull;
                <strong>Status Filter:</strong> {{ !empty($filters['status']) ? strtoupper($filters['status']) : 'SEMUA STATUS' }} &bull;
                <strong>Pencarian:</strong> {{ !empty($filters['search']) ? '"' . $filters['search'] . '"' : 'Semua Komoditas' }} &bull;
                <strong>Total Item:</strong> {{ count($items) }} komoditas
            </td>
            <td style="width: 30%; text-align: right; color: #475569;">
                <strong>Standar Dokumen:</strong> Rekap Stok Saldo &amp; Valuasi (ERP)
            </td>
        </tr>
    </table>

    {{-- KPI MINI SUMMARY --}}
    <table class="kpi-mini-table">
        <tr>
            <td class="kpi-mini-card" style="border-left: 3px solid #0f766e;">
                <div class="kpi-mini-label">Total Komoditas</div>
                <div class="kpi-mini-value">{{ number_format($totals['total_item'] ?? count($items), 0, ',', '.') }} SKU</div>
            </td>
            <td class="kpi-mini-card" style="border-left: 3px solid #0284c7;">
                <div class="kpi-mini-label">Total Saldo Fisik</div>
                <div class="kpi-mini-value">{{ number_format($totals['total_stok_akhir'] ?? 0, 2, ',', '.') }}</div>
            </td>
            <td class="kpi-mini-card" style="border-left: 3px solid #16a34a;">
                <div class="kpi-mini-label">Total Valuasi Aset</div>
                <div class="kpi-mini-value" style="color: #16a34a;">Rp {{ number_format($totals['total_nilai'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-mini-card" style="border-left: 3px solid #dc2626;">
                <div class="kpi-mini-label">Alert Rendah / Habis</div>
                <div class="kpi-mini-value" style="color: #dc2626;">
                    {{ ($totals['total_rendah'] ?? 0) + ($totals['total_habis'] ?? 0) }} SKU
                </div>
            </td>
        </tr>
    </table>

    {{-- TABEL DATA REKAP STOK --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 75px;">Gudang</th>
                <th style="width: 75px;">Jenis</th>
                <th style="width: 70px;">Kode</th>
                <th style="width: 140px;">Nama Komoditas</th>
                <th style="width: 45px; text-align: center;">Satuan</th>
                <th style="width: 55px; text-align: right;">Batas Min</th>
                <th style="width: 65px; text-align: right;">Total IN</th>
                <th style="width: 65px; text-align: right;">Total OUT</th>
                <th style="width: 65px; text-align: right;">Stok Akhir</th>
                <th style="width: 90px; text-align: right;">Nilai Persediaan</th>
                <th style="width: 50px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                @php
                    $statusClass = 'badge-aman';
                    if ($item->status === 'HABIS') {
                        $statusClass = 'badge-habis';
                    } elseif ($item->status === 'RENDAH') {
                        $statusClass = 'badge-rendah';
                    }
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td>{{ $item->gudang_nm ?? 'Default Gudang' }}</td>
                    <td><span style="color: #0369a1; font-weight: 600;">{{ $item->jenis_barang_nm ?? '-' }}</span></td>
                    <td style="font-family: monospace; font-weight: bold; color: #0f172a;">{{ $item->barang_cd }}</td>
                    <td style="font-weight: 600; color: #0f172a;">{{ $item->barang_nm }}</td>
                    <td style="text-align: center;">{{ $item->satuan_nama ?? 'SAT' }}</td>
                    <td style="text-align: right; color: #64748b;">{{ number_format((float) ($item->batas_minimum_qty ?? 0), 2, ',', '.') }}</td>
                    <td style="text-align: right; color: #16a34a; font-weight: 600;">+{{ number_format((float) ($item->total_masuk ?? 0), 2, ',', '.') }}</td>
                    <td style="text-align: right; color: #dc2626; font-weight: 600;">-{{ number_format((float) ($item->total_keluar ?? 0), 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ number_format((float) ($item->stok_akhir ?? 0), 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 600; color: #0f766e;">Rp {{ number_format((float) ($item->nilai_persediaan ?? 0), 0, ',', '.') }}</td>
                    <td style="text-align: center;">
                        <span class="badge-status {{ $statusClass }}">{{ $item->status }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 18px; color: #94a3b8;">
                        Tidak ada data stok komoditas yang sesuai kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($items) > 0)
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #0f766e;">
                    <td colspan="7" style="text-align: right; padding: 5px; text-transform: uppercase; font-size: 7.5pt; color: #0f172a;">
                        TOTAL KESELURUHAN:
                    </td>
                    <td style="text-align: right; color: #16a34a; font-size: 7.5pt;">
                        +{{ number_format($totals['total_in'] ?? 0, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #dc2626; font-size: 7.5pt;">
                        -{{ number_format($totals['total_out'] ?? 0, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #0f172a; font-size: 7.5pt;">
                        {{ number_format($totals['total_stok_akhir'] ?? 0, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #0f766e; font-size: 7.5pt;">
                        Rp {{ number_format($totals['total_nilai'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- TANDA TANGAN DOKUMEN --}}
    <table class="sign-table">
        <tr>
            <td>
                <div>Dibuat Oleh:</div>
                <div style="font-weight: 600; color: #64748b; margin-top: 1px;">Staff Logistik &amp; Gudang</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; color: #0f172a;">{{ $printedBy }}</div>
                <div style="font-size: 6.5pt; color: #64748b;">Operator Sistem</div>
            </td>
            <td>
                <div>Diperiksa Oleh:</div>
                <div style="font-weight: 600; color: #64748b; margin-top: 1px;">Kepala Bagian Gudang</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; color: #0f172a;">( ............................................ )</div>
                <div style="font-size: 6.5pt; color: #64748b;">Supervisor Gudang</div>
            </td>
            <td>
                <div>Disetujui Oleh:</div>
                <div style="font-weight: 600; color: #64748b; margin-top: 1px;">Manajer Keuangan &amp; Akuntansi</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; color: #0f172a;">( ............................................ )</div>
                <div style="font-size: 6.5pt; color: #64748b;">Valuasi Persediaan Valid</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dihasilkan secara otomatis oleh Sistem ERP PT Mirasa Food Industry &bull; Rekapitulasi Stok Komoditas &amp; Valuasi &bull; Lembar Asli Gudang
    </div>

</body>
</html>
