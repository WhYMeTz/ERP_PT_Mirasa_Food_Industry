<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BPPB - {{ $pakai->pakai_no }} - PT Mirasa Food Industry</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .kop-table {
            width: 100%;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-logo { width: 55px; vertical-align: middle; }
        .kop-text { padding-left: 10px; vertical-align: middle; text-align: left; }
        .kop-company { font-size: 13pt; font-weight: bold; color: #0f172a; letter-spacing: 0.5px; }
        .kop-sub { font-size: 7.5pt; color: #475569; margin-top: 2px; }
        .kop-title-box { text-align: right; vertical-align: middle; }
        .doc-title { font-size: 13pt; font-weight: bold; color: #991b1b; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-number { font-size: 9.5pt; font-family: monospace; font-weight: bold; color: #0f172a; margin-top: 3px; }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 8.5pt;
        }
        .meta-table td {
            padding: 5px 8px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 18%;
        }
        .meta-val {
            color: #0f172a;
            width: 32%;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 6px;
            font-size: 8pt;
            border: 1px solid #334155;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            font-size: 8pt;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .badge-draft {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
        }
        .badge-posted {
            display: inline-block;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
        }

        .ttd-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .ttd-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .ttd-box {
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            padding: 8px 6px;
            background: #ffffff;
        }
        .ttd-role {
            font-size: 7.5pt;
            color: #64748b;
        }
        .ttd-name-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .ttd-space {
            height: 48px;
        }
        .ttd-line {
            border-bottom: 1px solid #64748b;
            width: 80%;
            margin: 0 auto;
        }
        .ttd-date {
            font-size: 7pt;
            color: #64748b;
            margin-top: 3px;
        }

        .footer-note {
            margin-top: 15px;
            font-size: 7pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            @if(!empty($logoBase64))
                <td class="kop-logo">
                    <img src="{{ $logoBase64 }}" alt="Logo" style="width: 50px; height: auto;">
                </td>
            @endif
            <td class="kop-text">
                <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">Pabrik Pengolahan Aneka Keripik Singkong &amp; Snack Makanan Ringan</div>
                <div class="kop-sub">Jl. Munggur No. 2 Ambartawang, Magelang, Jawa Tengah</div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">BUKTI PENGELUARAN BAHAN</div>
                <div class="doc-number">{{ $pakai->pakai_no }}</div>
                <div style="margin-top: 3px;">
                    @if($pakai->isDraft())
                        <span class="badge-draft">STATUS: DRAFT</span>
                    @else
                        <span class="badge-posted">STATUS: POSTED</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- INFORMASI DOKUMEN HEADER --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor BPPB</td>
            <td class="meta-val"><strong style="font-family: monospace;">{{ $pakai->pakai_no }}</strong></td>
            <td class="meta-label">Tanggal Keluar</td>
            <td class="meta-val">{{ \Carbon\Carbon::parse($pakai->pakai_tgl)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Gudang Asal</td>
            <td class="meta-val">{{ $pakai->gudang?->gudang_nm ?? '-' }} ({{ $pakai->gudang?->gudang_cd }})</td>
            <td class="meta-label">Tujuan Lini</td>
            <td class="meta-val"><strong>{{ $pakai->tujuan_pemakaian }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Catatan Fisik</td>
            <td class="meta-val" colspan="3">{{ $pakai->catatan_txt ?: '-' }}</td>
        </tr>
    </table>

    {{-- TABEL RINCIAN BAHAN KELUAR --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 110px;">Kode Batch (FIFO)</th>
                <th style="width: 80px;">Kode Bahan</th>
                <th>Nama Bahan Produksi</th>
                <th style="width: 65px; text-align: center;">Grade</th>
                <th style="width: 75px; text-align: right;">Qty Keluar</th>
                <th style="width: 50px; text-align: center;">Satuan</th>
                <th style="width: 130px;">Keterangan / SPK</th>
            </tr>
        </thead>
        <tbody>
            @php $totalFisik = 0; @endphp
            @foreach($pakai->details as $idx => $dtl)
                @php $totalFisik += (float) $dtl->qty_keluar; @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold; color: #991b1b;">
                        {{ $dtl->batch_no }}
                    </td>
                    <td style="font-family: monospace; color: #475569;">
                        {{ $dtl->barang?->barang_cd ?? '-' }}
                    </td>
                    <td>
                        <strong>{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $dtl->grade_cd ? 'Grade ' . $dtl->grade_cd : '-' }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; font-size: 8.5pt;">
                        {{ number_format((float) $dtl->qty_keluar, 2, ',', '.') }}
                    </td>
                    <td style="text-align: center; color: #475569;">
                        {{ $dtl->barang?->satuanDasar?->satuan_nm ?? '-' }}
                    </td>
                    <td style="color: #475569; font-size: 7.5pt;">
                        {{ $dtl->keterangan_txt ?: $pakai->tujuan_pemakaian }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="5" style="text-align: right; padding: 6px 8px; text-transform: uppercase;">
                    Total Akumulasi Kuantitas Fisik:
                </td>
                <td style="text-align: right; font-family: monospace; font-size: 9pt; color: #0f172a; padding: 6px;">
                    {{ number_format($totalFisik, 2, ',', '.') }}
                </td>
                <td colspan="2" style="color: #64748b; font-size: 7.5pt;">
                    Total Fisik Keluar
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- KOLOM TANDA TANGAN 3 PIHAK --}}
    <table class="ttd-table">
        <tr>
            <td>
                <div class="ttd-box">
                    <div class="ttd-role">Diserahkan Oleh:</div>
                    <div class="ttd-name-title">Petugas Gudang</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-line"></div>
                    <div class="ttd-date">( ........................................ )</div>
                </div>
            </td>
            <td>
                <div class="ttd-box">
                    <div class="ttd-role">Diterima Oleh:</div>
                    <div class="ttd-name-title">Mandor / Staf Produksi</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-line"></div>
                    <div class="ttd-date">( ........................................ )</div>
                </div>
            </td>
            <td>
                <div class="ttd-box">
                    <div class="ttd-role">Mengetahui:</div>
                    <div class="ttd-name-title">Kepala Pabrik / Gudang</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-line"></div>
                    <div class="ttd-date">( ........................................ )</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- FOOTER INFO --}}
    <div class="footer-note">
        <span>Dicetak pada: {{ $printedAt }} | Oleh: {{ $printedBy }}</span>
        <span>Dokumen Resmi PT Mirasa Food Industry - BPPB</span>
    </div>

</body>
</html>
