<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Karyawan</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 2.0cm 1.2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }
        
        /* DEKLARASI FOOTER YANG AMAN UNTUK DOMPDF */
        footer {
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px;
            height: 30px; 
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            width: 100%;
        }
        /* Menggunakan tabel mini di footer agar tidak merusak layout utama */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: none !important;
        }
        .footer-table td {
            border: none !important;
            padding: 0 !important;
            font-size: 7.5pt !important;
            color: #777 !important;
            font-style: italic !important;
        }

        /* KONTEN UTAMA LAPORAN */
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 2px;
            color: #111;
        }
        .header-subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .header-period {
            text-align: center;
            font-size: 8pt;
            color: #555;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        /* TABEL UTAMA DATA */
        .main-table {
            width: 100%;
            table-layout: fixed; /* Memaksa lebar kolom konsisten mengikuti width % */
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .main-table th {
            background-color: #1f4e78;
            color: white;
            font-weight: bold;
            padding: 8px 4px;
            font-size: 8.5pt;
            border: 1px solid #1f4e78;
            text-align: center;
        }
        .main-table td {
            padding: 6px 4px;
            border: 1px solid #bfbfbf;
            vertical-align: middle;
            word-wrap: break-word; /* Mencegah teks meluber keluar kolom */
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .summary-text {
            font-weight: bold;
            margin-top: 5px;
            font-size: 9pt;
        }
        
        /* KELOMPOK TANDA TANGAN */
        .signature-container {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border: none !important;
        }
        .signature-table td {
            border: none !important;
        }
        .signature-box {
            text-align: center;
            width: 300px;
        }
        .signature-title {
            font-weight: 500;
            margin-bottom: 65px;
        }
    </style>
</head>
<body>

    <!-- TAG FOOTER RESMI DOMPDF (Diletakkan di paling atas) -->
    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 70%; text-align: left;">
                    *Dokumen rekapitulasi kepegawaian ini diterbitkan secara otomatis oleh sistem data komputer internal PT Mirasa Food Industry dan bersifat rahasia.
                </td>
                <td style="width: 30%; text-align: right;">
                    Dicetak pada: {{ date('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </footer>

    <!-- KONTEN UTAMA LAPORAN -->
    <div class="header-title">MIRASA FOOD MAGELANG</div>
    <div class="header-subtitle">LAPORAN DATA KARYAWAN</div>
    <div class="header-period">Periode: {{ Carbon\Carbon::now()->translatedFormat('F Y') }}</div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 11%;">ID Karyawan</th>
                <th style="width: 9%;">ID Absensi</th>
                <th style="width: 15%;">Nama Karyawan</th>
                <th style="width: 10%;">Kelompok</th>
                <th style="width: 7%;">Shift</th>
                <th style="width: 7%;">Status</th>
                <th style="width: 12%;">Bagian / Divisi</th>
                <th style="width: 9%;">Tanggal Masuk</th>
                <th style="width: 11%;">No. HP</th>
                <th style="width: 4%;">Bank</th>
                <th style="width: 11%;">No. Rekening</th>
            </tr>
        </thead>
        <tbody>
            @forelse($karyawanList as $index => $karyawan)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $karyawan->nik }}</td>
                    <td class="text-center">{{ $karyawan->id_absensi }}</td>
                    <td>{{ $karyawan->karyawan_nm }}</td>
                    <td class="text-center">{{ $karyawan->kelompok }}</td>
                    <td class="text-center">{{ $karyawan->shift }}</td>
                    <td class="text-center">{{ $karyawan->status_kerja }}</td>
                    <td>{{ $karyawan->departemen_cd }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($karyawan->tgl_masuk)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $karyawan->telepon_no }}</td>
                    <td class="text-center">{{ $karyawan->bank_name }}</td>
                    <td class="text-center">{{ $karyawan->no_rekening }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data karyawan yang sesuai dengan kriteria filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-text">Total Karyawan: {{ $karyawanList->count() }} orang</div>

    <!-- AREA TANDA TANGAN MENGGUNAKAN STRUKTUR TABEL TRANSPARAN AGAR LAYOUT AMAN -->
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
                        <div>
                            ( ............................................................ )
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
