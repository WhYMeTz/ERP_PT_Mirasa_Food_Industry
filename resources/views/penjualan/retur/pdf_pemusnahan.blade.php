<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pemusnahan Barang Retur - {{ $retur->retur_no }}</title>
    <style>
        @page {
            margin: 10mm 12mm 12mm 12mm;
        }
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
            border-bottom: 2px solid #dc2626;
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
            font-size: 12pt;
            font-weight: bold;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
            font-family: monospace;
        }

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
        }

        .legal-clause {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 8pt;
            color: #991b1b;
            margin-bottom: 15px;
            line-height: 1.45;
        }

        .ttd-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            text-align: center;
        }
        .ttd-table td {
            width: 25%;
            vertical-align: top;
            padding: 0 5px;
        }
        .ttd-space {
            height: 55px;
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
                <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">Divisi Pengendalian Mutu &amp; Karantina Produk Pangan Olahan</div>
                <div class="kop-sub">Kawasan Industri Candi Blok XI No. 8, Krapyak, Semarang &bull; Telp: (024) 7601234</div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">BERITA ACARA PEMUSNAHAN BARANG</div>
                <div class="doc-number">BAP-SCRAP/{{ $retur->retur_no }}</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 3px;">
                    Tanggal Eksekusi: <strong>{{ $retur->retur_tgl ? $retur->retur_tgl->format('d/m/Y') : date('d/m/Y') }}</strong>
                </div>
            </td>
        </tr>
    </table>

    {{-- Meta Data Transaksi Retur --}}
    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <div class="info-card">
                    <strong style="color: #0f172a; font-size: 8.5pt; display: block; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin-bottom: 4px;">
                        SUMBER ASAL PENGEMBALIAN BARANG
                    </strong>
                    <table style="width: 100%; font-size: 8pt; border-collapse: collapse;">
                        <tr>
                            <td style="width: 32%; color: #64748b; padding: 1px 0;">Pelanggan / Toko:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">{{ $retur->customer?->customer_nm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Ref. Order (SO):</td>
                            <td style="font-weight: bold; color: #0284c7; padding: 1px 0; font-family: monospace;">{{ $retur->order?->so_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Ref. Faktur:</td>
                            <td style="color: #334155; padding: 1px 0; font-family: monospace;">{{ $retur->order?->faktur_no ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 50%;">
                <div class="info-card">
                    <strong style="color: #0f172a; font-size: 8.5pt; display: block; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin-bottom: 4px;">
                        PARAMETER DOKUMEN RETUR TERKAIT
                    </strong>
                    <table style="width: 100%; font-size: 8pt; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; color: #64748b; padding: 1px 0;">No. Retur Asal:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0; font-family: monospace;">{{ $retur->retur_no }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Lokasi Pabrik:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">Pabrik Ambartawang / Semarang</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Disposisi Fisik:</td>
                            <td style="font-weight: bold; color: #dc2626; padding: 1px 0;">DIMUSNAHKAN DI TEMPAT (SCRAP)</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Klausul Pemusnahan Resmi --}}
    <div class="legal-clause">
        <strong>PERNYATAAN RESMI PEMUSNAHAN BARANG:</strong><br>
        Menerangkan bahwa produk keripik olahan di bawah ini yang berasal dari retur penjualan pelanggan, setelah dilakukan inspeksi mutu fisik oleh tim Quality Control (QC), dinyatakan <strong>TIDAK LAYAK KONSUMSI / RUSAK BERAT</strong> dan dilarang diedarkan kembali. Seluruh fisik produk ini telah <strong>DIMUSNAHKAN / DISCRAP</strong> di area fasilitas pabrik dengan mematuhi standar sanitasi, keamanan pangan, dan audit HACCP PT. Mirasa Food Industry.
    </div>

    {{-- Tabel Rincian Barang yang Dimusnahkan --}}
    @php
        $itemsMusnah = $retur->details->filter(function($d) {
            return (float) ($d->qty_dimusnahkan ?? 0) > 0 || $d->perlakuan_cd === 'DIMUSNAHKAN';
        });
        $grandTotalMusnahQty = 0;
        $grandTotalMusnahNilai = 0;
    @endphp

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 80px;">Kode Produk</th>
                <th>Nama Varian Produk</th>
                <th style="width: 120px;">Kondisi / Alasan Rusak</th>
                <th style="width: 75px; text-align: right;">Qty Musnah</th>
                <th style="width: 85px; text-align: right;">Harga Satuan</th>
                <th style="width: 95px; text-align: right;">Beban Kerugian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemsMusnah as $index => $item)
                @php
                    $qMusnah = (float) ($item->qty_dimusnahkan > 0 ? $item->qty_dimusnahkan : $item->qty_retur);
                    $subtotalMusnah = round($qMusnah * (float) $item->harga_satuan, 2);
                    $grandTotalMusnahQty += $qMusnah;
                    $grandTotalMusnahNilai += $subtotalMusnah;
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $loop->iteration }}</td>
                    <td style="font-family: monospace; font-size: 8pt; color: #475569;">{{ $item->barang?->barang_cd ?? '-' }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $item->barang?->barang_nm ?? '-' }}</strong>
                        @if($item->catatan_txt)
                            <div style="font-size: 7pt; color: #64748b; margin-top: 1px;">Catatan: {{ $item->catatan_txt }}</div>
                        @endif
                    </td>
                    <td style="font-size: 7.5pt; color: #dc2626; font-weight: 600;">
                        {{ $item->kondisi_label }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; color: #dc2626;">
                        {{ number_format($qMusnah, 0) }} {{ $item->barang?->satuan_kecil ?? 'Karton' }}
                    </td>
                    <td style="text-align: right; font-family: monospace;">
                        Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; color: #dc2626;">
                        Rp {{ number_format($subtotalMusnah, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 15px;">
                        Tidak ada item yang dimusnahkan pada dokumen retur ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #fef2f2; font-weight: bold;">
                <td colspan="4" style="text-align: right; padding: 6px 8px; color: #991b1b;">
                    TOTAL PEMUSNAHAN BARANG:
                </td>
                <td style="text-align: right; padding: 6px 8px; font-family: monospace; font-size: 9pt; color: #dc2626;">
                    {{ number_format($grandTotalMusnahQty, 0) }} Karton
                </td>
                <td></td>
                <td style="text-align: right; padding: 6px 8px; font-family: monospace; font-size: 9.5pt; color: #dc2626;">
                    Rp {{ number_format($grandTotalMusnahNilai, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Tanda Tangan 4 Pihak Berita Acara --}}
    <table class="ttd-table">
        <tr>
            <td>
                <div class="ttd-role">Petugas QC / Inspeksi Mutu</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">Siti Astiyanti</div>
                <div class="ttd-role">Staf QC Pabrik</div>
            </td>
            <td>
                <div class="ttd-role">Kepala Gudang Barang Jadi</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ auth()->user()?->karyawan?->karyawan_nm ?? 'Raden Rahmat' }}</div>
                <div class="ttd-role">Penanggung Jawab Gudang</div>
            </td>
            <td>
                <div class="ttd-role">Bagian Keuangan / Akuntansi</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">Mei Nur Rahmawati</div>
                <div class="ttd-role">Pencatat Beban Kerugian</div>
            </td>
            <td>
                <div class="ttd-role">Pimpinan Pabrik / Direktur</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">Habieb Soleh Y.</div>
                <div class="ttd-role">Menyetujui Pemusnahan</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px; font-size: 7pt; color: #94a3b8; text-align: right;">
        Dokumen dicetak otomatis oleh sistem ERP PT. Mirasa Food Industry pada {{ now()->translatedFormat('d F Y H:i') }}.
    </div>
</body>
</html>

