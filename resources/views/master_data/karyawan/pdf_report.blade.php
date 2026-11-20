<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Karyawan</title>
    <style>
        @page { margin: 1.2cm 1.2cm 1.9cm 1.2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5pt; color: #1e293b; line-height: 1.35; }

        /* FOOTER */
        footer { position: fixed; bottom: -42px; left: 0; right: 0; height: 30px; border-top: 1px solid #cbd5e1; padding-top: 5px; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { border: none; padding: 0; font-size: 7pt; color: #64748b; }
        .pagenum:before { content: counter(page); }

        /* KOP SURAT */
        .kop { width: 100%; border-collapse: collapse; }
        .kop td { border: none; padding: 0; vertical-align: middle; }
        .kop-company { font-size: 14pt; font-weight: bold; color: #1f4e78; letter-spacing: .3px; }
        .kop-sub { font-size: 8pt; color: #64748b; margin-top: 2px; }
        .kop-doc { text-align: right; }
        .kop-doc-title { font-size: 13pt; font-weight: bold; color: #0f172a; letter-spacing: .5px; }
        .kop-doc-meta { font-size: 8pt; color: #64748b; margin-top: 2px; }
        .kop-line { border: 0; border-top: 3px solid #1f4e78; border-bottom: 1px solid #1f4e78; height: 2px; margin: 8px 0 10px 0; }

        /* RINGKASAN */
        .info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info td { border: none; padding: 0; font-size: 8pt; color: #475569; }
        .info strong { color: #0f172a; }
        .chip { background: #eaf1f8; color: #1f4e78; border: 1px solid #c5d7e8; padding: 3px 9px; font-weight: bold; font-size: 8pt; }

        /* TABEL */
        .main-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 12px; }
        .main-table th { background-color: #1f4e78; color: #fff; font-weight: bold; padding: 7px 4px; font-size: 8pt; border: 1px solid #1f4e78; text-align: center; text-transform: uppercase; letter-spacing: .2px; }
        .main-table td { padding: 5px 4px; border: 1px solid #d5dde6; vertical-align: middle; word-wrap: break-word; font-size: 8pt; }
        .main-table tr.alt td { background-color: #f4f7fb; }
        .main-table td.nama { font-weight: bold; color: #0f172a; }
        .main-table td.mono { letter-spacing: .2px; }
        .main-table td.npwp { font-size: 7.5pt; letter-spacing: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .summary-text { font-weight: bold; font-size: 8.5pt; color: #1f4e78; }

        /* TANDA TANGAN */
        .signature-container { margin-top: 26px; width: 100%; page-break-inside: avoid; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { border: none; }
        .signature-box { text-align: center; width: 300px; }
        .signature-title { margin-bottom: 65px; }
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
                    Halaman <span class="pagenum"></span>
                </td>
            </tr>
        </table>
    </footer>

    @php
        // DomPDF tidak memuat gambar dari URL lokal dengan andal; pakai file di folder public, disisipkan langsung.
        $logoFile = public_path('images/logo.png');
        $logo = is_file($logoFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) : null;
    @endphp

    <!-- KOP LAPORAN -->
    <table class="kop">
        <tr>
            @if ($logo)
                <td style="width: 70px;"><img src="{{ $logo }}" style="height: 52px;" alt="Mirasa"></td>
            @endif
            <td>
                <div class="kop-company">PT MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">Mirasa Food Magelang</div>
            </td>
            <td class="kop-doc">
                <div class="kop-doc-title">LAPORAN DATA KARYAWAN</div>
                <div class="kop-doc-meta">Periode: {{ Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
            </td>
        </tr>
    </table>
    <hr class="kop-line">

    <table class="info">
        <tr>
            <td><span class="chip">Total: {{ $karyawanList->count() }} karyawan</span></td>
            <td style="text-align: right;">Dicetak pada: <strong>{{ date('d/m/Y H:i') }}</strong></td>
        </tr>
    </table>

    @php
        // NPWP/NIK + PTKP hanya ikut dicetak untuk yang berhak mengedit karyawan (data pribadi)
        $denganPajak = $denganPajak ?? false;
        $w = $denganPajak
            ? [3, 9, 8, 11.5, 8, 6, 6, 8, 7, 8, 4, 7.5, 10.5, 4]
            : [4, 11, 9, 15, 10, 7, 7, 12, 9, 11, 4, 11];
    @endphp

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: {{ $w[0] }}%;">No</th>
                <th style="width: {{ $w[1] }}%;">ID Karyawan</th>
                <th style="width: {{ $w[2] }}%;">ID Absensi</th>
                <th style="width: {{ $w[3] }}%;">Nama Karyawan</th>
                <th style="width: {{ $w[4] }}%;">Kelompok</th>
                <th style="width: {{ $w[5] }}%;">Shift</th>
                <th style="width: {{ $w[6] }}%;">Status</th>
                <th style="width: {{ $w[7] }}%;">Departemen</th>
                <th style="width: {{ $w[8] }}%;">Tanggal Masuk</th>
                <th style="width: {{ $w[9] }}%;">No. HP</th>
                <th style="width: {{ $w[10] }}%;">Bank</th>
                <th style="width: {{ $w[11] }}%;">No. Rekening</th>
                @if ($denganPajak)
                    <th style="width: {{ $w[12] }}%;">NPWP/NIK</th>
                    <th style="width: {{ $w[13] }}%;">PTKP</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($karyawanList as $index => $karyawan)
                <tr class="{{ $index % 2 ? 'alt' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $karyawan->nik }}</td>
                    <td class="text-center">{{ $karyawan->id_absensi }}</td>
                    <td class="nama">{{ $karyawan->karyawan_nm }}</td>
                    <td class="text-center">{{ $karyawan->kelompok }}</td>
                    <td class="text-center">{{ $karyawan->shift }}</td>
                    <td class="text-center">{{ $karyawan->status_kerja }}</td>
                    <td>{{ $karyawan->departemen_cd }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($karyawan->tgl_masuk)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $karyawan->telepon_no }}</td>
                    <td class="text-center">{{ $karyawan->bank_name }}</td>
                    <td class="text-center">{{ $karyawan->no_rekening }}</td>
                    @if ($denganPajak)
                        <td class="text-center npwp">{{ $karyawan->npwp_nik }}</td>
                        <td class="text-center">{{ $karyawan->status_ptkp }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $denganPajak ? 14 : 12 }}" class="text-center">Tidak ada data karyawan yang sesuai dengan kriteria filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

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
