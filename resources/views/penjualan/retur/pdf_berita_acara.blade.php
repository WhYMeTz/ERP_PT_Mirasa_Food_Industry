<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Retur Penjualan - {{ $retur->retur_no }}</title>
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
            border-bottom: 2px solid #ea580c;
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
            font-size: 13pt;
            font-weight: bold;
            color: #ea580c;
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

        /* Petunjuk & Catatan */
        .instructions-box {
            border: 1px dashed #ea580c;
            padding: 8px 10px;
            background: #fff7ed;
            border-radius: 4px;
            font-size: 8pt;
            color: #9a3412;
            margin-bottom: 15px;
        }

        /* Tanda Tangan */
        .ttd-table {
            width: 100%;
            margin-top: 20px;
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
                <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">Produsen Keripik Tempe, Keripik Singkong & Aneka Olahan Makanan Tradisional Modern</div>
                <div class="kop-sub">Kawasan Industri Candi Blok XI No. 8, Krapyak, Semarang &bull; Telp: (024) 7601234</div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">BERITA ACARA RETUR PENJUALAN</div>
                <div class="doc-number">{{ $retur->retur_no }}</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 3px;">
                    Status: <strong style="color: {{ $retur->status_cd === 'CONFIRMED' ? '#16a34a' : '#dc2626' }};">{{ $retur->status_cd }}</strong>
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
                        DATA PELANGGAN (CUSTOMER)
                    </strong>
                    <table style="width: 100%; font-size: 8pt; border-collapse: collapse;">
                        <tr>
                            <td style="width: 28%; color: #64748b; padding: 1px 0;">Pelanggan:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">{{ $retur->customer?->customer_nm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Kode:</td>
                            <td style="color: #334155; padding: 1px 0; font-family: monospace;">{{ $retur->customer?->customer_cd ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Alamat:</td>
                            <td style="color: #334155; padding: 1px 0;">{{ $retur->customer?->alamat_txt ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Kontak:</td>
                            <td style="color: #334155; padding: 1px 0;">{{ $retur->customer?->kontak_no ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 50%;">
                <div class="info-card">
                    <strong style="color: #0f172a; font-size: 8.5pt; display: block; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin-bottom: 4px;">
                        DETAIL REFERENSI DOKUMEN
                    </strong>
                    <table style="width: 100%; font-size: 8pt; border-collapse: collapse;">
                        <tr>
                            <td style="width: 35%; color: #64748b; padding: 1px 0;">Tgl. Retur:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">{{ $retur->retur_tgl ? $retur->retur_tgl->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Ref. PO Penjualan:</td>
                            <td style="font-weight: bold; color: #0284c7; padding: 1px 0; font-family: monospace;">{{ $retur->order?->so_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Ref. Faktur:</td>
                            <td style="color: #334155; padding: 1px 0; font-family: monospace;">{{ $retur->order?->faktur_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Gudang Masuk:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">
                                {{ $retur->gudang?->gudang_nm ?? '-' }} 
                                <span style="font-size: 7pt; color: #c2410c; font-family: monospace;">(Batch: RET-{{ $retur->order?->so_no }})</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Tindakan Retur:</td>
                            <td style="font-weight: bold; color: #c2410c; padding: 1px 0;">
                                {{ $retur->tindakan_label }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Keterangan Alasan Retur --}}
    @if($retur->alasan_txt)
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 10px; font-size: 8pt; margin-bottom: 10px;">
            <strong style="color: #475569;">Alasan Pengembalian Barang:</strong>
            <span style="color: #0f172a; margin-left: 4px;">{{ $retur->alasan_txt }}</span>
        </div>
    @endif

    {{-- Tabel Rincian Barang yang Diretur --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 75px;">Kode</th>
                <th>Nama Produk Keripik</th>
                <th style="width: 110px;">Kondisi Produk</th>
                <th style="width: 65px; text-align: right;">Qty Retur</th>
                <th style="width: 85px; text-align: right;">Harga Satuan</th>
                <th style="width: 95px; text-align: right;">Subtotal Retur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retur->details as $index => $item)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-size: 8pt; color: #475569;">{{ $item->barang?->barang_cd ?? '-' }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $item->barang?->barang_nm ?? '-' }}</strong>
                        @if($item->catatan_txt)
                            <div style="font-size: 7pt; color: #64748b; margin-top: 1px;">Catatan: {{ $item->catatan_txt }}</div>
                        @endif
                    </td>
                    <td style="font-size: 7.5pt; color: #9a3412; font-weight: 600;">
                        {{ $item->kondisi_label }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace;">
                        {{ number_format((float) $item->qty_retur, 0) }} {{ $item->barang?->satuan_kecil ?? 'Karton' }}
                    </td>
                    <td style="text-align: right; font-family: monospace;">
                        Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; color: #c2410c;">
                        Rp {{ number_format((float) $item->subtotal_nilai, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #fff7ed; font-weight: bold;">
                <td colspan="4" style="text-align: right; padding: 6px 8px; color: #9a3412;">
                    TOTAL RETUR:
                </td>
                <td style="text-align: right; padding: 6px 8px; font-family: monospace; font-size: 9pt; color: #0f172a;">
                    {{ number_format((float) $retur->total_qty, 0) }}
                </td>
                <td></td>
                <td style="text-align: right; padding: 6px 8px; font-family: monospace; font-size: 9.5pt; color: #c2410c;">
                    Rp {{ number_format((float) $retur->total_nilai, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Instruksi & Kebijakan Sistem --}}
    <div class="instructions-box">
        <strong>Pemberitahuan Sistem ERP Mirasa:</strong>
        @if($retur->tindakan_cd === 'POTONG_TAGIHAN')
            Dokumen ini bertindak sebagai <em>Credit Note</em> resmi. Total nilai retur sebesar <strong>Rp {{ number_format((float) $retur->total_nilai, 0, ',', '.') }}</strong> telah otomatis memotong sisa piutang / tagihan pada pesanan <strong>{{ $retur->order?->so_no }}</strong>. Stok fisik barang cacat telah dicatat kembali ke kartu persediaan dengan nomor batch khusus untuk keperluan audit/sortir.
        @else
            Dokumen ini mencatat pengembalian fisik untuk penggantian barang sejenis (Tukar Guling). Sisa piutang tagihan pelanggan tidak mengalami perubahan.
        @endif
    </div>

    {{-- Kolom Tanda Tangan 3 Pihak --}}
    <table class="ttd-table">
        <tr>
            <td>
                <div class="ttd-role">Yang Menyerahkan,</div>
                <div style="font-size: 7.5pt; color: #64748b;">(Pelanggan / Driver Ekspedisi)</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">( ........................................ )</div>
                <div class="ttd-role">Tgl: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
            </td>
            <td>
                <div class="ttd-role">Diterima &amp; Diperiksa Oleh,</div>
                <div style="font-size: 7.5pt; color: #64748b;">(Bagian Gudang &amp; QC Mutu)</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">( ........................................ )</div>
                <div class="ttd-role">Admin Gudang / Supervisor</div>
            </td>
            <td>
                <div class="ttd-role">Diketahui &amp; Divalidasi Oleh,</div>
                <div style="font-size: 7.5pt; color: #64748b;">(Bagian Penjualan &amp; Finance)</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">( {{ $printedBy }} )</div>
                <div class="ttd-role">Sales &amp; Finance Officer</div>
            </td>
        </tr>
    </table>

    {{-- Footer Info --}}
    <div style="position: fixed; bottom: 0; left: 0; right: 0; font-size: 7pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 3px; display: flex; justify-content: space-between;">
        <span>Dicetak secara otomatis oleh Sistem ERP PT Mirasa Food Industry pada {{ $printedAt }}</span>
        <span style="float: right;">Hal 1 / 1</span>
    </div>
</body>
</html>
