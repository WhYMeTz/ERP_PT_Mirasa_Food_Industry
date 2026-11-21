<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pengeluaran Sampel - {{ $pengeluaran->pengeluaran_no }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8.5pt;
            color: #000000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* KOP SURAT PERUSAHAAN */
        .kop-box {
            width: 100%;
            table-layout: fixed;
            border: 1.5px solid #000000;
            border-collapse: collapse;
        }
        .kop-left-cell {
            width: 65%;
            vertical-align: middle;
            padding: 0;
            border-right: 1.5px solid #000000;
        }
        .kop-identity-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-logo-cell {
            width: 58px;
            text-align: center;
            vertical-align: middle;
            padding: 4px;
            border-right: 1.5px solid #000000;
        }
        .kop-identity-cell {
            padding: 4px 8px;
            vertical-align: middle;
        }
        .company-title {
            font-size: 11pt;
            font-weight: bold;
            font-style: italic;
            font-family: 'Arial', sans-serif;
            letter-spacing: 0.2px;
        }
        .company-address {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 1px;
        }
        .company-telp {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 1px;
        }

        .doc-control-cell {
            width: 35%;
            padding: 0;
            vertical-align: top;
        }
        .doc-control-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .doc-control-table td {
            padding: 2px 6px;
            font-size: 7.5pt;
        }
        .doc-label-col {
            width: 80px;
            border-right: 1px solid #000000;
            border-bottom: 1px solid #000000;
            font-weight: bold;
        }

        /* JUDUL DOKUMEN */
        .doc-header-box {
            text-align: center;
            margin: 10px 0 6px 0;
        }
        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .doc-badge-noncom {
            display: inline-block;
            font-size: 8pt;
            font-weight: bold;
            color: #7c3aed;
            background: #faf5ff;
            border: 1px solid #d8b4fe;
            padding: 2px 8px;
            margin-top: 3px;
            letter-spacing: 0.5px;
        }

        /* DATA TUJUAN & LOGISTIK */
        .dest-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin-top: 6px;
            margin-bottom: 8px;
            font-size: 8.5pt;
        }
        .dest-table td {
            padding: 3.5px 6px;
            border-bottom: 1px solid #000000;
            vertical-align: top;
        }

        /* TABEL BARANG */
        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin-top: 4px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            font-size: 8.5pt;
        }
        .data-table th {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f3e8ff;
        }

        /* TANDA TANGAN */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 8.5pt;
        }
        .ttd-table td {
            vertical-align: top;
            text-align: center;
            width: 33.33%;
        }
        .ttd-space {
            height: 55px;
        }
    </style>
</head>
<body>

    {{-- 1. KOP SURAT RESMI --}}
    <table class="kop-box">
        <tr>
            <td class="kop-left-cell">
                <table class="kop-identity-table">
                    <tr>
                        <td class="kop-logo-cell">
                            @if ($logoBase64)
                                <img src="{{ $logoBase64 }}" alt="Logo" style="width: 48px; height: auto;">
                            @else
                                <div style="font-weight: bold; font-size: 9pt;">MFI</div>
                            @endif
                        </td>
                        <td class="kop-identity-cell">
                            <div class="company-title">PT. MIRASA FOOD INDUSTRY</div>
                            <div class="company-address">JL. RAYA KARANGANYAR KM 1.5 KEBUMEN, JAWA TENGAH</div>
                            <div class="company-telp">Telp : (0287) 381200 / Fax : (0287) 381201</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="doc-control-cell">
                <table class="doc-control-table">
                    <tr>
                        <td class="doc-label-col">No. Dokumen</td>
                        <td style="border-bottom: 1px solid #000000; font-family: monospace; font-weight: bold;">
                            {{ $pengeluaran->pengeluaran_no }}
                        </td>
                    </tr>
                    <tr>
                        <td class="doc-label-col">Tanggal</td>
                        <td style="border-bottom: 1px solid #000000;">
                            {{ \Carbon\Carbon::parse($pengeluaran->pengeluaran_tgl)->translatedFormat('d F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="doc-label-col">Sifat Dokumen</td>
                        <td style="font-weight: bold; color: #7c3aed;">
                            NON-KOMERSIAL (FOC)
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 2. JUDUL DOKUMEN --}}
    <div class="doc-header-box">
        <div class="doc-title">BUKTI PENGELUARAN BARANG SAMPEL &amp; PROMOSI</div>
        <div class="doc-badge-noncom">DOKUMEN PENGELUARAN FISIK INTERNAL (BEBAS BIAYA / NON-KOMERSIAL)</div>
    </div>

    {{-- 3. INFORMASI PENERIMA & PENGELUARAN --}}
    <table class="dest-table">
        <tr>
            <td style="width: 20%; font-weight: bold; background: #faf5ff;">Penerima / Pemohon:</td>
            <td style="width: 40%; font-weight: bold; font-size: 9.5pt;">
                {{ $pengeluaran->penerima_nm }}
            </td>
            <td style="width: 18%; font-weight: bold; background: #faf5ff;">Jenis Keperluan:</td>
            <td style="width: 22%; font-weight: bold; color: #7c3aed;">
                {{ $pengeluaran->keperluan_txt ?: 'Sampel Promosi' }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #faf5ff;">Gudang Asal:</td>
            <td style="font-weight: bold; color: #059669;">
                {{ $pengeluaran->gudang?->gudang_nm ?? 'Gudang Pusat (MFI)' }}
            </td>
            <td style="font-weight: bold; background: #faf5ff;">Disetujui Oleh:</td>
            <td style="font-weight: bold;">
                {{ $pengeluaran->otorisasi_oleh ?: 'Manajemen' }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #faf5ff;">Catatan / Keterangan:</td>
            <td colspan="3">
                {{ $pengeluaran->catatan_txt ?: '-' }}
            </td>
        </tr>
    </table>

    <div style="font-size: 8.5pt; margin-bottom: 4px;">
        Harap diterima barang contoh / promosi dengan rincian fisik sebagai berikut:
    </div>

    {{-- 4. TABEL DAFTAR BARANG JADI & BATCH FEFO --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 100px;">Kode Barang</th>
                <th style="text-align: left;">Nama Produk / Barang Jadi</th>
                <th style="width: 130px;">Nomor Batch Fisik</th>
                <th style="width: 80px;">Kuantitas</th>
                <th style="width: 65px;">Satuan</th>
                <th style="width: 130px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @foreach ($pengeluaran->details as $idx => $dtl)
                @php
                    $qty = (float) $dtl->kirim_qty;
                    $totalQty += $qty;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="text-align: center; font-family: monospace;">
                        {{ $dtl->barang?->barang_cd ?: '-' }}
                    </td>
                    <td>
                        <strong>{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                    </td>
                    <td style="text-align: center; font-family: monospace;">
                        {{ $dtl->batch_no ?: '-' }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format($qty, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        {{ $dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton' }}
                    </td>
                    <td>
                        {{ $dtl->catatan_txt ?: 'Sampel Uji Coba' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #faf5ff;">
                <td colspan="4" style="text-align: right; padding-right: 8px;">
                    TOTAL BARANG SAMPEL KELUAR:
                </td>
                <td style="text-align: right; font-size: 9.5pt;">
                    {{ number_format($totalQty, 0, ',', '.') }}
                </td>
                <td style="text-align: center;">Karton</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 7.5pt; font-style: italic; color: #4b5563; margin-top: 6px;">
        * Catatan: Dokumen ini adalah bukti serah terima resmi pengeluaran fisik barang sampel/promosi dari gudang PT Mirasa Food Industry. Tidak dapat digunakan sebagai dasar penagihan keuangan komersial.
    </div>

    {{-- 5. TANDA TANGAN 3 PIHAK --}}
    <table class="ttd-table">
        <tr>
            <td>
                <div>Penerima / Pemohon,</div>
                <div class="ttd-space"></div>
                <div>( .................................................. )</div>
                <div style="font-size: 7.5pt; color: #6b7280; margin-top: 2px;">Tanda Tangan &amp; Nama Terang</div>
            </td>
            <td>
                <div>Diserahkan Oleh (Gudang),</div>
                <div class="ttd-space"></div>
                <div>( <strong>{{ $pengeluaran->created_by ?? 'Petugas Gudang' }}</strong> )</div>
                <div style="font-size: 7.5pt; color: #6b7280; margin-top: 2px;">Bagian Logistik &amp; Gudang</div>
            </td>
            <td>
                <div>Mengetahui / Menyetujui,</div>
                <div class="ttd-space"></div>
                <div>( <strong>{{ $pengeluaran->otorisasi_oleh ?: 'Kepala Gudang / Manajemen' }}</strong> )</div>
                <div style="font-size: 7.5pt; color: #6b7280; margin-top: 2px;">Sales / Manajemen</div>
            </td>
        </tr>
    </table>

</body>
</html>
