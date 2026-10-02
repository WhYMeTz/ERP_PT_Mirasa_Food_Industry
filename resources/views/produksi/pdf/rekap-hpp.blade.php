<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Rekap HPP Harian & Rendemen - {{ strtoupper($monthName) }} {{ $year }}</title>
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
            font-size: 6pt;
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
            font-size: 5.5pt;
        }
        .grid-table th, .grid-table td {
            border: 0.5px solid #94a3b8;
            padding: 2.5px 2px;
            text-align: right;
            white-space: nowrap;
        }
        .grid-table th {
            text-align: center;
            font-weight: bold;
        }
        .th-main {
            background-color: #a3e635;
            color: #1a2e05;
        }
        .th-sub {
            background-color: #bef264;
            color: #1a2e05;
        }
        .th-yellow {
            background-color: #facc15;
            color: #713f12;
        }
        .th-wip {
            background-color: #86efac;
            color: #064e3b;
        }
        .th-green {
            background-color: #22c55e;
            color: #ffffff;
        }
        .th-blue {
            background-color: #0284c7;
            color: #ffffff;
        }
        .th-sticky {
            background-color: #cbd5e1;
            color: #0f172a;
        }

        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold; }
        .row-total {
            background-color: #e2e8f0;
            font-weight: bold;
        }

        /* Footer signatures */
        .sign-table {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
            font-size: 6pt;
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
                <div class="kop-sub">Sistem Informasi Manufaktur &amp; Akuntansi Biaya Produksi</div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">BUKU REKAPITULASI HPP &amp; RENDEMEN PRODUKSI</div>
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
                <div class="kpi-box" style="border-left: 3px solid #eab308;">
                    <div class="kpi-label">Total Biaya Produksi</div>
                    <div class="kpi-val">Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 4px; padding-left: 4px;">
                <div class="kpi-box" style="border-left: 3px solid #0284c7;">
                    <div class="kpi-label">Total WIP Jadi</div>
                    <div class="kpi-val">{{ number_format($tot['total_wip_qty'], 2, ',', '.') }} kg ({{ number_format($tot['total_karton'], 0, ',', '.') }} Box)</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 4px; padding-left: 4px;">
                <div class="kpi-box" style="border-left: 3px solid #10b981;">
                    <div class="kpi-label">Rendemen Rata-Rata</div>
                    <div class="kpi-val">{{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 4px;">
                <div class="kpi-box" style="border-left: 3px solid #059669;">
                    <div class="kpi-label">HPP Rata-Rata / Kg</div>
                    <div class="kpi-val">Rp {{ number_format($tot['hpp_per_kg'], 2, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- SPREADSHEET REKAP PRODUKSI --}}
    <table class="grid-table">
        <thead>
            <tr>
                <th rowspan="2" class="th-sticky">HARI</th>
                <th rowspan="2" class="th-sticky">TGL</th>
                <th rowspan="2" class="th-sticky">SHIFT/BATCH</th>
                <th colspan="2" class="th-main">SINGKONG</th>
                <th colspan="4" class="th-main">MINYAK GORENG</th>
                <th colspan="2" class="th-main">CNG</th>
                <th colspan="4" class="th-main">TENAGA KERJA</th>
                <th rowspan="2" class="th-sub">BUMBU</th>
                <th colspan="2" class="th-main">KARTON</th>
                <th rowspan="2" class="th-sub">PLASTIK</th>
                <th colspan="2" class="th-main">LAKBAN</th>
                <th rowspan="2" class="th-sub">FOTO<br>COPY</th>
                <th colspan="2" class="th-main">SARUNG TANGAN</th>
                <th rowspan="2" class="th-sub">QC</th>
                <th rowspan="2" class="th-sub">LISTRIK<br>AIR</th>
                <th rowspan="2" class="th-sub">PMLHRN<br>MESIN</th>
                <th rowspan="2" class="th-sub">PENYS<br>MESIN</th>
                <th colspan="2" class="th-main">LIMBAH</th>
                <th rowspan="2" class="th-yellow">TOTAL BIAYA<br>(Rp)</th>
                <th colspan="4" class="th-wip">MANUAL</th>
                <th colspan="2" class="th-wip">BERKO</th>
                <th rowspan="2" class="th-green">TOTAL WIP<br>(KG)</th>
                <th rowspan="2" class="th-green">RENDEMEN<br>%</th>
                <th rowspan="2" class="th-blue">HPP/KG<br>(Rp)</th>
            </tr>
            <tr>
                {{-- Singkong --}}
                <th class="th-sub">KG</th>
                <th class="th-sub">Rp</th>
                {{-- Minyak --}}
                <th class="th-sub">SAWIT</th>
                <th class="th-sub">KELAPA</th>
                <th class="th-sub">Rp</th>
                <th class="th-sub">%</th>
                {{-- CNG --}}
                <th class="th-sub">MMBTU</th>
                <th class="th-sub">Rp</th>
                {{-- Tenaga Kerja --}}
                <th class="th-sub">DIR</th>
                <th class="th-sub">INDIR</th>
                <th class="th-sub">TRAIN</th>
                <th class="th-sub">Rp</th>
                {{-- Karton --}}
                <th class="th-sub">BARU</th>
                <th class="th-sub">BEKAS</th>
                {{-- Lakban --}}
                <th class="th-sub">BSR</th>
                <th class="th-sub">KCL</th>
                {{-- Sarung Tangan --}}
                <th class="th-sub">PLSTK</th>
                <th class="th-sub">KAIN</th>
                {{-- Limbah --}}
                <th class="th-sub">PADAT</th>
                <th class="th-sub">KIMIA</th>
                {{-- Output Manual --}}
                <th class="th-wip">BARCO</th>
                <th class="th-wip">SAWIT</th>
                <th class="th-wip">NOSALT</th>
                <th class="th-wip">BALO</th>
                {{-- Output Berko --}}
                <th class="th-wip">BERKO</th>
                <th class="th-wip">BK ME</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['days'] as $d)
                <tr>
                    <td class="text-center font-bold">{{ substr($d['hari_nm'], 0, 3) }}</td>
                    <td class="text-center">{{ sprintf('%02d', $d['day']) }}</td>
                    <td class="text-center">{{ $d['has_data'] ? ($d['shift_cd'] . ' ' . $d['batch_wip_no']) : '-' }}</td>

                    {{-- Singkong --}}
                    <td>{{ $d['singkong_qty'] > 0 ? number_format($d['singkong_qty'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['singkong_nilai'] > 0 ? number_format($d['singkong_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Minyak --}}
                    <td>{{ $d['minyak_sawit_qty'] > 0 ? number_format($d['minyak_sawit_qty'], 1, ',', '.') : '-' }}</td>
                    <td>{{ $d['minyak_kelapa_qty'] > 0 ? number_format($d['minyak_kelapa_qty'], 1, ',', '.') : '-' }}</td>
                    <td>{{ $d['minyak_nilai'] > 0 ? number_format($d['minyak_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['minyak_rasio_persen'] > 0 ? number_format($d['minyak_rasio_persen'], 1, ',', '.') : '-' }}</td>

                    {{-- CNG --}}
                    <td>{{ $d['cng_mmbtu'] > 0 ? number_format($d['cng_mmbtu'], 2, ',', '.') : '-' }}</td>
                    <td>{{ $d['cng_nilai'] > 0 ? number_format($d['cng_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Tenaga Kerja --}}
                    <td class="text-center">{{ $d['tk_langsung_org'] > 0 ? $d['tk_langsung_org'] : '-' }}</td>
                    <td class="text-center">{{ $d['tk_tidak_langsung_org'] > 0 ? $d['tk_tidak_langsung_org'] : '-' }}</td>
                    <td class="text-center">{{ $d['tk_training_org'] > 0 ? $d['tk_training_org'] : '-' }}</td>
                    <td>{{ $d['tk_total_nilai'] > 0 ? number_format($d['tk_total_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Bumbu --}}
                    <td>{{ $d['bumbu_nilai'] > 0 ? number_format($d['bumbu_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Karton --}}
                    <td>{{ $d['karton_baru_nilai'] > 0 ? number_format($d['karton_baru_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['karton_bekas_nilai'] > 0 ? number_format($d['karton_bekas_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Plastik --}}
                    <td>{{ $d['plastik_hd_nilai'] > 0 ? number_format($d['plastik_hd_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Lakban --}}
                    <td>{{ $d['lakban_besar_nilai'] > 0 ? number_format($d['lakban_besar_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['lakban_kecil_nilai'] > 0 ? number_format($d['lakban_kecil_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Foto copy --}}
                    <td>{{ $d['fotocopy_nilai'] > 0 ? number_format($d['fotocopy_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Sarung Tangan --}}
                    <td>{{ $d['sarung_tangan_plastik_nilai'] > 0 ? number_format($d['sarung_tangan_plastik_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['sarung_tangan_kain_nilai'] > 0 ? number_format($d['sarung_tangan_kain_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- QC --}}
                    <td>{{ $d['qc_pengawasan_nilai'] > 0 ? number_format($d['qc_pengawasan_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Listrik / Air --}}
                    <td>{{ $d['listrik_air_telp_nilai'] > 0 ? number_format($d['listrik_air_telp_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Pemeliharaan Mesin --}}
                    <td>{{ $d['pemeliharaan_mesin_nilai'] > 0 ? number_format($d['pemeliharaan_mesin_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Penyusutan Mesin --}}
                    <td>{{ $d['penyusutan_mesin_nilai'] > 0 ? number_format($d['penyusutan_mesin_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Limbah --}}
                    <td>{{ $d['limbah_padat_nilai'] > 0 ? number_format($d['limbah_padat_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['limbah_kimia_nilai'] > 0 ? number_format($d['limbah_kimia_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Total Biaya --}}
                    <td class="font-bold" style="background:#fef9c3;">{{ $d['total_biaya_produksi'] > 0 ? number_format($d['total_biaya_produksi'], 0, ',', '.') : '-' }}</td>

                    {{-- WIP Manual --}}
                    <td>{{ $d['asin_barco_qty'] > 0 ? number_format($d['asin_barco_qty'], 1, ',', '.') : '-' }}</td>
                    <td>{{ $d['asin_sawit_qty'] > 0 ? number_format($d['asin_sawit_qty'], 1, ',', '.') : '-' }}</td>
                    <td>{{ $d['no_salt_qty'] > 0 ? number_format($d['no_salt_qty'], 1, ',', '.') : '-' }}</td>
                    <td>{{ $d['balo_gelombang_qty'] > 0 ? number_format($d['balo_gelombang_qty'], 1, ',', '.') : '-' }}</td>

                    {{-- WIP Berko --}}
                    <td>{{ $d['berko_qty'] > 0 ? number_format($d['berko_qty'], 1, ',', '.') : '-' }}</td>
                    <td>{{ $d['berko_me_qty'] > 0 ? number_format($d['berko_me_qty'], 1, ',', '.') : '-' }}</td>

                    {{-- Total WIP, Rendemen, HPP --}}
                    <td class="font-bold">{{ $d['total_wip_qty'] > 0 ? number_format($d['total_wip_qty'], 1, ',', '.') : '-' }}</td>
                    <td class="font-bold text-center">{{ $d['rendemen_persen'] > 0 ? number_format($d['rendemen_persen'], 2, ',', '.') . '%' : '-' }}</td>
                    <td class="font-bold" style="background:#e0f2fe;">{{ $d['hpp_per_kg'] > 0 ? number_format($d['hpp_per_kg'], 0, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="36" class="text-center" style="padding: 10px;">Tidak ada catatan produksi pada bulan ini.</td>
                </tr>
            @endforelse

            {{-- BARIS GRAND TOTAL --}}
            <tr class="row-total">
                <td colspan="3" class="text-center font-bold">TOTAL BULANAN</td>

                <td>{{ number_format($tot['singkong_qty'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['singkong_nilai'], 0, ',', '.') }}</td>

                <td>{{ number_format($tot['minyak_sawit_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['minyak_kelapa_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['minyak_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['minyak_rasio_persen'], 1, ',', '.') }}%</td>

                <td>{{ number_format($tot['cng_mmbtu'], 2, ',', '.') }}</td>
                <td>{{ number_format($tot['cng_nilai'], 0, ',', '.') }}</td>

                <td class="text-center">{{ $tot['tk_langsung_org'] }}</td>
                <td class="text-center">{{ $tot['tk_tidak_langsung_org'] }}</td>
                <td class="text-center">{{ $tot['tk_training_org'] }}</td>
                <td>{{ number_format($tot['tk_total_nilai'], 0, ',', '.') }}</td>

                <td>{{ number_format($tot['bumbu_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['karton_baru_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['karton_bekas_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['plastik_hd_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['lakban_besar_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['lakban_kecil_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['fotocopy_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['sarung_tangan_plastik_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['sarung_tangan_kain_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['qc_pengawasan_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['listrik_air_telp_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['pemeliharaan_mesin_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['penyusutan_mesin_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['limbah_padat_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['limbah_kimia_nilai'], 0, ',', '.') }}</td>

                <td class="font-bold" style="background:#fde047;">Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}</td>

                <td>{{ number_format($tot['asin_barco_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['asin_sawit_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['no_salt_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['balo_gelombang_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['berko_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['berko_me_qty'], 1, ',', '.') }}</td>

                <td class="font-bold">{{ number_format($tot['total_wip_qty'], 1, ',', '.') }} kg</td>
                <td class="font-bold text-center">{{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%</td>
                <td class="font-bold" style="background:#bae6fd;">Rp {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN AUDIT --}}
    <table class="sign-table">
        <tr>
            <td class="sign-box">
                Dibuat Oleh,<br><br><br><br>
                <strong>( ______________________ )</strong><br>
                Admin Produksi
            </td>
            <td class="sign-box">
                Diperiksa Oleh,<br><br><br><br>
                <strong>( ______________________ )</strong><br>
                Supervisor Produksi
            </td>
            <td class="sign-box">
                Diverifikasi Oleh,<br><br><br><br>
                <strong>( ______________________ )</strong><br>
                Cost Accounting / Finance
            </td>
            <td class="sign-box">
                Disetujui Oleh,<br><br><br><br>
                <strong>( ______________________ )</strong><br>
                Factory Manager
            </td>
        </tr>
    </table>

</body>
</html>
