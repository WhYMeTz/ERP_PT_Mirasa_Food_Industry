<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Karyawan</title>
    <style>
        @page { margin: 1.2cm 1.2cm 2.0cm 1.2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5pt; color: #333; line-height: 1.35; }
        footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 30px; border-top: 1px solid #e2e8f0; padding-top: 5px; width: 100%; }
        .footer-table { width: 100%; border-collapse: collapse; border: none !important; }
        .footer-table td { border: none !important; padding: 0 !important; font-size: 7.5pt !important; color: #777 !important; font-style: italic !important; }
        .header-title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 2px; color: #111; }
        .header-subtitle { text-align: center; font-weight: bold; font-size: 11pt; text-transform: uppercase; margin-bottom: 5px; }
        .header-period { text-align: center; font-size: 8pt; color: #555; margin-bottom: 4px; text-transform: uppercase; }
        .header-filter { text-align: center; font-size: 7.5pt; color: #777; margin-bottom: 14px; }
        .main-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 12px; }
        .main-table th { background-color: #1f4e78; color: white; font-weight: bold; padding: 6px 3px; font-size: 8pt; border: 1px solid #1f4e78; text-align: center; }
        .main-table td { padding: 4px 3px; border: 1px solid #bfbfbf; vertical-align: middle; word-wrap: break-word; }
        .main-table tr.total td { background-color: #ddebf7; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary-text { font-weight: bold; margin-top: 5px; font-size: 9pt; }
        .signature-container { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .signature-table { width: 100%; border: none !important; }
        .signature-table td { border: none !important; }
        .signature-box { text-align: center; width: 300px; }
        .signature-title { margin-bottom: 65px; }
    </style>
</head>
@php
    $f2 = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $f4 = fn ($v) => rtrim(rtrim(number_format((float) $v, 4, ',', '.'), '0'), ',');
    $tglId = fn ($s) => \Carbon\Carbon::parse($s)->format('d/m/Y');
@endphp
<body>
    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 70%; text-align: left;">
                    *Dokumen rekapitulasi absensi ini diterbitkan secara otomatis oleh sistem data komputer internal PT Mirasa Food Industry dan bersifat rahasia.
                </td>
                <td style="width: 30%; text-align: right;">Dicetak pada: {{ date('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </footer>

    <div class="header-title">MIRASA FOOD MAGELANG</div>
    <div class="header-subtitle">LAPORAN ABSENSI KARYAWAN — REKAP PER KARYAWAN</div>
    <div class="header-period">Periode: {{ $tglId($dari) }} s/d {{ $tglId($sampai) }}</div>
    <div class="header-filter">{{ $filter ?: 'Semua data (tanpa filter tambahan)' }}</div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 10%;">ID Karyawan</th>
                    <th style="width: 8%;">ID Absen</th>
                    <th style="width: 20%;">Nama</th>
                    <th style="width: 14%;">Bagian / Divisi</th>
                    <th style="width: 8%;">Kelompok</th>
                    <th style="width: 6%;">Hari Data</th>
                    <th style="width: 8%;">Kehadiran (hari)</th>
                    <th style="width: 7%;">Total Jam</th>
                    <th style="width: 7%;">Potongan (jam)</th>
                    <th style="width: 7%;">Lembur (jam)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekap as $i => $r)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center">{{ $r['nik'] }}</td>
                        <td class="text-center">{{ $r['id_absen'] }}</td>
                        <td>{{ $r['nama'] }}</td>
                        <td>{{ $r['departemen'] }}</td>
                        <td class="text-center">{{ $r['kelompok'] }}</td>
                        <td class="text-center">{{ $r['hari_data'] }}</td>
                        <td class="text-right">{{ $f4($r['kehadiran']) }}</td>
                        <td class="text-right">{{ $f2($r['total_jam']) }}</td>
                        <td class="text-right">{{ $f2($r['potongan']) }}</td>
                        <td class="text-right">{{ $f2($r['lembur']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center">Tidak ada data absensi pada filter ini.</td></tr>
                @endforelse
                @if (count($rekap))
                    <tr class="total">
                        <td colspan="6" class="text-right">TOTAL</td>
                        <td class="text-center">{{ $total['hari_data'] }}</td>
                        <td class="text-right">{{ $f4($total['kehadiran']) }}</td>
                        <td class="text-right">{{ $f2($total['total_jam']) }}</td>
                        <td class="text-right">{{ $f2($total['potongan']) }}</td>
                        <td class="text-right">{{ $f2($total['lembur']) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="summary-text">Total Karyawan: {{ count($rekap) }} orang &nbsp;|&nbsp; Total Data: {{ $total['hari_data'] }} baris</div>

    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td style="width: 65%;"></td>
                <td style="width: 35%;">
                    <div class="signature-box">
                        <div class="signature-title">
                            Mengetahui,<br>
                            <strong>Kabag. HRD & Personalia PT Mirasa Food Industry</strong>
                        </div>
                        <div>( ............................................................ )</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
