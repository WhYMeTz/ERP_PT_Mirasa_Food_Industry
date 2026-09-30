<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Pengiriman - {{ $order->surat_jalan_no ?: $order->so_no }}</title>
    <style>
        @page {
            margin: 10mm 12mm 12mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Perusahaan */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-logo {
            width: 50px;
            vertical-align: middle;
        }
        .kop-text {
            padding-left: 10px;
            vertical-align: middle;
        }
        .kop-company {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .kop-sub {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }
        .kop-title-box {
            text-align: right;
            vertical-align: middle;
        }
        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            color: #16a34a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Meta Information Table */
        .info-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .info-card {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            background: #f8fafc;
            border-radius: 4px;
        }

        /* Items Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 8pt;
            border: 1px solid #0f172a;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 8.5pt;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Petunjuk & Catatan Pengiriman */
        .instructions-box {
            border: 1px dashed #94a3b8;
            padding: 8px 10px;
            background: #fafafa;
            border-radius: 4px;
            font-size: 7.5pt;
            color: #334155;
            margin-bottom: 15px;
        }

        /* Tanda Tangan */
        .ttd-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .ttd-table td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 4px;
        }
        .ttd-space {
            height: 60px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .ttd-role {
            font-size: 7.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>
    {{-- Kop Surat Perusahaan --}}
    <table class="kop-table">
        <tr>
            @if(!empty($logoBase64))
                <td class="kop-logo">
                    <img src="{{ $logoBase64 }}" alt="Logo PT Mirasa" style="width: 48px; height: 48px;">
                </td>
            @endif
            <td class="kop-text">
                <div class="kop-company">PT MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">
                    Departemen Logistik &amp; Distribusi Produk • Cap Payung<br>
                    Jl. Raya Industri No. 88, Purwokerto, Jawa Tengah • Telp: (0281) 638899
                </div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">SURAT JALAN</div>
                <div class="doc-number">{{ $order->surat_jalan_no ?: ('SJ-' . $order->so_no) }}</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">
                    Tgl Kirim: {{ $order->tgl_kirim_estimasi ? $order->tgl_kirim_estimasi->format('d/m/Y') : date('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Meta Customer & Pengiriman --}}
    <table class="info-table">
        <tr>
            <td style="width: 55%; padding-right: 8px;">
                <div class="info-card">
                    <div style="font-size: 7.5pt; font-weight: bold; color: #64748b; text-transform: uppercase;">Tujuan Pengiriman (Customer):</div>
                    <div style="font-size: 9.5pt; font-weight: bold; color: #0f172a; margin-top: 2px;">
                        {{ $order->customer?->customer_nm }} ({{ $order->customer?->customer_cd }})
                    </div>
                    <div style="font-size: 8pt; color: #334155; margin-top: 2px;">
                        <strong>Alamat Lengkap:</strong><br>
                        {{ $order->customer?->alamat_txt ?: 'Alamat tidak terdata' }}
                    </div>
                    <div style="font-size: 8pt; color: #334155; margin-top: 2px;">
                        <strong>No. Telepon / Kontak:</strong> {{ $order->customer?->kontak_no ?: '-' }}
                    </div>
                </div>
            </td>
            <td style="width: 45%; padding-left: 8px;">
                <div class="info-card">
                    <div style="font-size: 7.5pt; font-weight: bold; color: #64748b; text-transform: uppercase;">Detail Dokumen &amp; Pesanan:</div>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 2px; font-size: 8pt;">
                        <tr>
                            <td style="color: #64748b; width: 45%; padding: 1px 0;">No. Pesanan (SO):</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">{{ $order->so_no }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">No. PO Customer:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">{{ $order->customer_po_no ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Tanggal Pesan:</td>
                            <td style="color: #0f172a; padding: 1px 0;">{{ $order->so_tgl?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">No. Faktur:</td>
                            <td style="color: #0f172a; padding: 1px 0;">{{ $order->faktur_no ?: '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Items Physical Delivery Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th style="width: 120px;">Kode Barang</th>
                <th>Nama Produk / Barang</th>
                <th style="width: 80px; text-align: right;">Jumlah Kirim</th>
                <th style="width: 70px; text-align: center;">Satuan</th>
                <th style="width: 150px;">Keterangan / Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->details as $idx => $dtl)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-size: 7.5pt; font-weight: bold; color: #334155;">
                        {{ $dtl->barang?->barang_cd }}
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #0f172a;">{{ $dtl->barang?->barang_nm }}</div>
                        <div style="font-size: 7pt; color: #64748b;">{{ $dtl->barang?->jenisBarang?->jenis_barang_nm }}</div>
                    </td>
                    <td style="text-align: right; font-weight: bold; font-size: 9.5pt; color: #0f172a;">
                        {{ number_format($dtl->pesan_qty, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; color: #334155;">
                        {{ $dtl->barang?->satuanDasar?->satuan_nm ?? 'Unit' }}
                    </td>
                    <td style="font-size: 7.5pt; color: #64748b;">
                        {{ $dtl->catatan_txt ?: 'Kondisi Baik & Utuh' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Instructions Box --}}
    <div class="instructions-box">
        <strong>PENTING:</strong> Barang-barang di atas telah diperiksa dan diserahkan dalam keadaan baik, utuh, dan sesuai pesanan. Harap memeriksa kondisi fisik dan segel kemasan barang saat penerimaan. Segala bentuk komplain atau klaim kerusakan wajib dicatat pada lembar tanda terima ini saat penyerahan berlangsung.
        @if($order->catatan_txt)
            <br><strong>Catatan Khusus:</strong> {{ $order->catatan_txt }}
        @endif
    </div>

    {{-- Kolom Tanda Tangan 3 Pihak --}}
    <table class="ttd-table">
        <tr>
            <td>
                <div class="ttd-role">Diserahkan Oleh (Gudang/Logistik),</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">Petugas Logistik</div>
                <div class="ttd-role">PT Mirasa Food Industry</div>
            </td>
            <td>
                <div class="ttd-role">Dibawa Oleh (Sopir / Ekspedisi),</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">Pengemudi / Ekspedisi</div>
                <div class="ttd-role">No. Kendaraan: ........................</div>
            </td>
            <td>
                <div class="ttd-role">Diterima Dengan Baik Oleh,</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $order->customer?->customer_nm }}</div>
                <div class="ttd-role">Tanda Tangan, Nama &amp; Cap Toko</div>
            </td>
        </tr>
    </table>
</body>
</html>
