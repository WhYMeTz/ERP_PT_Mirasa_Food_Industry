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
        .th-orange {
            background-color: #f4b084;
            color: #000000;
        }
        .th-green {
            background-color: #92d050;
            color: #000000;
        }
        .th-yellow {
            background-color: #ffc000;
            color: #000000;
        }
        .th-grey {
            background-color: #f2f2f2;
            color: #000000;
        }
        .text-red {
            color: #c00000 !important;
        }
        .text-blue {
            color: #002060 !important;
        }

        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold; }
        .row-total {
            background-color: #ffc000;
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
                    <div class="kpi-val">{{ number_format($tot['total_wip_qty'], 2, ',', '.') }} kg</div>
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
                    <div class="kpi-val">Rp {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- SPREADSHEET REKAP PRODUKSI --}}
    <table class="grid-table">
        <thead>
            {{-- ROW 1 --}}
            <tr>
                <th rowspan="3" class="th-orange">HARI</th>
                <th rowspan="3" class="th-orange">TANGGAL</th>
                <th colspan="28" class="th-green" style="letter-spacing: 0.1em; font-size: 6.5pt;">TOTAL BIAYA PRODUKSI / KG</th>
                <th rowspan="3" class="th-yellow">TOTAL<br>BIAYA</th>
                <th colspan="2" class="th-green">TOTAL WIP</th>
                <th rowspan="3" class="th-grey">HARGA POKOK<br>PRODUKSI</th>
            </tr>
            {{-- ROW 2 --}}
            <tr>
                <th colspan="2" class="th-green">SINGKONG</th>
                <th colspan="4" class="th-green">MINYAK GORENG</th>
                <th colspan="2" class="th-green">CNG</th>
                <th colspan="4" class="th-green">TENAGA KERJA</th>
                <th rowspan="2" class="th-green">BUMBU<br>PERENYAH</th>
                <th colspan="2" class="th-green">KARTON FL</th>
                <th rowspan="2" class="th-green">PLASTIK HD<br>90x100</th>
                <th colspan="2" class="th-green">LAKBAN</th>
                <th rowspan="2" class="th-green">TALI<br>RAFIA</th>
                <th rowspan="2" class="th-green text-red">FOTO<br>COPY</th>
                <th colspan="2" class="th-green">SARUNG TANGAN</th>
                <th rowspan="2" class="th-green text-red">PENGAWASAN<br>MUTU</th>
                <th rowspan="2" class="th-green text-red">LISTRIK &amp;<br>AIR - TELP</th>
                <th rowspan="2" class="th-green text-red">PEMLHR<br>MESIN</th>
                <th rowspan="2" class="th-green text-red">PENYS<br>MESIN</th>
                <th colspan="2" class="th-green text-red">B. PNGOLHN LIMBAH</th>

                {{-- TOTAL WIP --}}
                <th rowspan="2" class="th-green">TOTAL<br>KG</th>
                <th rowspan="2" class="th-green text-blue">RENDE<br>MEN %</th>
            </tr>
            {{-- ROW 3 --}}
            <tr>
                {{-- Singkong --}}
                <th class="th-green">KG</th>
                <th class="th-green">Rp</th>

                {{-- Minyak --}}
                <th class="th-green">SAWIT</th>
                <th class="th-green">KELAPA</th>
                <th class="th-green">Rp</th>
                <th class="th-green text-blue">%</th>

                {{-- CNG --}}
                <th class="th-green">MMBTU</th>
                <th class="th-green">Rp</th>

                {{-- Tenaga Kerja --}}
                <th class="th-green">DIR</th>
                <th class="th-green">INDIR</th>
                <th class="th-green">TRAIN</th>
                <th class="th-green">Rp</th>

                {{-- Karton --}}
                <th class="th-green">BARU</th>
                <th class="th-green">BEKAS</th>

                {{-- Lakban --}}
                <th class="th-green">BSR</th>
                <th class="th-green">KCL</th>

                {{-- Sarung Tangan --}}
                <th class="th-green">PLSTK</th>
                <th class="th-green">KAIN</th>

                {{-- Limbah --}}
                <th class="th-green text-red">PADAT</th>
                <th class="th-green text-red">KIMIA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['days'] as $d)
                @php
                    $isSunday = in_array(strtolower($d['hari_nm']), ['minggu', 'ahad']) || (\Carbon\Carbon::parse($d['date'])->dayOfWeek === 0);
                    $rowBg = $isSunday ? '#fff1f2' : ($d['has_data'] ? '#ffffff' : '#f8fafc');
                @endphp
                <tr style="background-color: {{ $rowBg }};">
                    <td class="text-center font-bold" style="color: {{ $isSunday ? '#dc2626' : '#0f172a' }};">{{ \Carbon\Carbon::parse($d['date'])->format('l') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($d['date'])->format('d/m/y') }}</td>

                    {{-- Singkong --}}
                    <td>{{ $d['has_data'] && $d['singkong_qty'] > 0 ? number_format($d['singkong_qty'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['singkong_nilai'] > 0 ? number_format($d['singkong_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Minyak --}}
                    <td>{{ $d['has_data'] && $d['minyak_sawit_qty'] > 0 ? number_format($d['minyak_sawit_qty'], 1, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['minyak_kelapa_qty'] > 0 ? number_format($d['minyak_kelapa_qty'], 1, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['minyak_nilai'] > 0 ? number_format($d['minyak_nilai'], 0, ',', '.') : '-' }}</td>
                    <td class="text-center text-blue font-bold">{{ $d['has_data'] && $d['minyak_rasio_persen'] > 0 ? number_format($d['minyak_rasio_persen'], 2, ',', '.') . '%' : '-' }}</td>

                    {{-- CNG --}}
                    <td>{{ $d['has_data'] && $d['cng_mmbtu'] > 0 ? number_format($d['cng_mmbtu'], 2, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['cng_nilai'] > 0 ? number_format($d['cng_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Tenaga Kerja --}}
                    <td class="text-center">{{ $d['has_data'] && $d['tk_langsung_org'] > 0 ? $d['tk_langsung_org'] : '-' }}</td>
                    <td class="text-center">{{ $d['has_data'] && $d['tk_tidak_langsung_org'] > 0 ? $d['tk_tidak_langsung_org'] : '-' }}</td>
                    <td class="text-center">{{ $d['has_data'] && $d['tk_training_org'] > 0 ? $d['tk_training_org'] : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['tk_total_nilai'] > 0 ? number_format($d['tk_total_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Bahan Pembantu --}}
                    <td>{{ $d['has_data'] && $d['bumbu_nilai'] > 0 ? number_format($d['bumbu_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['karton_baru_nilai'] > 0 ? number_format($d['karton_baru_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['karton_bekas_nilai'] > 0 ? number_format($d['karton_bekas_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['plastik_hd_nilai'] > 0 ? number_format($d['plastik_hd_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['lakban_besar_nilai'] > 0 ? number_format($d['lakban_besar_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['lakban_kecil_nilai'] > 0 ? number_format($d['lakban_kecil_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['tali_rafia_nilai'] > 0 ? number_format($d['tali_rafia_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['fotocopy_nilai'] > 0 ? number_format($d['fotocopy_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['sarung_tangan_plastik_nilai'] > 0 ? number_format($d['sarung_tangan_plastik_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['sarung_tangan_kain_nilai'] > 0 ? number_format($d['sarung_tangan_kain_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- FOH --}}
                    <td>{{ $d['has_data'] && $d['qc_pengawasan_nilai'] > 0 ? number_format($d['qc_pengawasan_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['listrik_air_telp_nilai'] > 0 ? number_format($d['listrik_air_telp_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['pemeliharaan_mesin_nilai'] > 0 ? number_format($d['pemeliharaan_mesin_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['penyusutan_mesin_nilai'] > 0 ? number_format($d['penyusutan_mesin_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['limbah_padat_nilai'] > 0 ? number_format($d['limbah_padat_nilai'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $d['has_data'] && $d['limbah_kimia_nilai'] > 0 ? number_format($d['limbah_kimia_nilai'], 0, ',', '.') : '-' }}</td>

                    {{-- Total Biaya --}}
                    <td class="font-bold text-blue" style="background:#ffeb3b;">{{ $d['has_data'] && $d['total_biaya_produksi'] > 0 ? number_format($d['total_biaya_produksi'], 0, ',', '.') : '-' }}</td>

                    {{-- Total WIP, Rendemen, HPP --}}
                    <td class="font-bold text-blue">{{ $d['has_data'] && $d['total_wip_qty'] > 0 ? number_format($d['total_wip_qty'], 2, ',', '.') : '-' }}</td>
                    <td class="font-bold text-center text-blue">{{ $d['has_data'] && $d['rendemen_persen'] > 0 ? number_format($d['rendemen_persen'], 2, ',', '.') . '%' : '-' }}</td>
                    <td class="font-bold" style="background:#f1f5f9;">{{ $d['has_data'] && $d['hpp_per_kg'] > 0 ? number_format($d['hpp_per_kg'], 0, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="34" class="text-center" style="padding: 10px;">Tidak ada catatan produksi pada bulan ini.</td>
                </tr>
            @endforelse

            {{-- BARIS GRAND TOTAL --}}
            <tr class="row-total">
                <td colspan="2" class="text-center font-bold">TOTAL BULANAN</td>

                <td>{{ number_format($tot['singkong_qty'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['singkong_nilai'], 0, ',', '.') }}</td>

                <td>{{ number_format($tot['minyak_sawit_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['minyak_kelapa_qty'], 1, ',', '.') }}</td>
                <td>{{ number_format($tot['minyak_nilai'], 0, ',', '.') }}</td>
                <td class="text-center text-blue font-bold">{{ number_format($tot['minyak_rasio_persen'], 2, ',', '.') }}%</td>

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
                <td>{{ number_format($tot['tali_rafia_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['fotocopy_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['sarung_tangan_plastik_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['sarung_tangan_kain_nilai'], 0, ',', '.') }}</td>

                <td>{{ number_format($tot['qc_pengawasan_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['listrik_air_telp_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['pemeliharaan_mesin_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['penyusutan_mesin_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['limbah_padat_nilai'], 0, ',', '.') }}</td>
                <td>{{ number_format($tot['limbah_kimia_nilai'], 0, ',', '.') }}</td>

                <td class="font-bold text-blue" style="background:#ffeb3b;">Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}</td>

                <td class="font-bold text-blue">{{ number_format($tot['total_wip_qty'], 2, ',', '.') }} kg</td>
                <td class="font-bold text-center text-blue">{{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%</td>
                <td class="font-bold" style="background:#f1f5f9;">Rp {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}</td>
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
