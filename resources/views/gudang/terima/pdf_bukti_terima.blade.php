<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Penerimaan Barang - {{ $terima->terima_no }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 10mm 12mm 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Perusahaan - Tidak menggunakan table-layout fixed agar logo dan teks rapat di kiri */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #134e5e;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .kop-logo {
            width: 50px;
            vertical-align: middle;
        }
        .kop-text {
            padding-left: 10px;
            vertical-align: middle;
            text-align: left;
        }
        .kop-company {
            font-size: 12.5pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .kop-sub {
            font-size: 7pt;
            color: #64748b;
            margin-top: 1px;
        }
        .kop-title-box {
            text-align: right;
            vertical-align: middle;
            white-space: nowrap;
        }
        .doc-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #134e5e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
            font-family: monospace;
        }

        /* Meta Information Table */
        .info-outer {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .info-outer td {
            vertical-align: top;
            padding: 0;
        }
        .info-card {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            background: #f8fafc;
            border-radius: 4px;
        }

        /* Items Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th {
            background-color: #134e5e; /* Dark Teal sesuai tema Excel */
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4.5px 4px;
            font-size: 7.2pt;
            border: 1px solid #0d3844;
            text-transform: uppercase;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data-table td {
            padding: 3.5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 7.2pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        .badge-batch {
            font-family: monospace;
            font-size: 6.8pt;
            font-weight: bold;
            color: #0d3844;
            word-break: break-all;
        }

        /* Signatures */
        .sign-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            page-break-inside: avoid;
            table-layout: fixed;
        }
        .sign-table td {
            text-align: center;
            vertical-align: top;
            width: 25%;
            font-size: 7.2pt;
            padding: 0 4px;
        }
        .sign-space {
            height: 42px;
        }
        .sign-line {
            border-bottom: 1px solid #334155;
            width: 82%;
            margin: 0 auto 2px auto;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="height: 40px; width: auto;">
                @else
                    <div style="font-size: 15pt; font-weight: bold; color: #134e5e;">MIRASA</div>
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
                <div class="kop-sub">
                    Pabrik Pengolahan Makanan &amp; Keripik Singkong &bull; Ambartawang, Magelang, Jawa Tengah<br>
                    Telepon: (0293) 788-234 &bull; Email: logistic@mirasafood.co.id
                </div>
            </td>
            <td class="kop-title-box">
                <div class="doc-title">BUKTI PENERIMAAN BARANG</div>
                <div style="font-size: 7pt; color: #64748b; text-transform: uppercase;">Goods Receipt Note (GRN)</div>
                <div class="doc-number">{{ $terima->terima_no }}</div>
            </td>
        </tr>
    </table>

    {{-- DOKUMEN & MITRA INFORMASI (Kotak Kiri & Kanan Sama Tinggi dengan outer table) --}}
    <table class="info-outer">
        <tr>
            {{-- KOLOM KIRI: Supplier --}}
            <td style="width: 49%; padding-right: 4px;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 4px;">
                    <tr>
                        <td colspan="2" style="padding: 5px 8px 3px 8px; border-bottom: 1px solid #e2e8f0;">
                            <strong style="color: #0f172a; font-size: 7.8pt;">SUPPLIER PENGIRIM:</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 38%; color: #64748b; font-size: 7.2pt; padding: 2px 8px;">Nama Supplier:</td>
                        <td style="font-weight: bold; color: #0f172a; font-size: 7.2pt; padding: 2px 8px;">
                            {{ $terima->supplier?->supplier_nm ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7.2pt; padding: 2px 8px;">Kode Supplier:</td>
                        <td style="font-weight: bold; color: #475569; font-size: 7.2pt; padding: 2px 8px;">
                            {{ $terima->supplier?->supplier_cd ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7.2pt; padding: 2px 8px;">Alamat Kantor:</td>
                        <td style="color: #334155; font-size: 7.2pt; padding: 2px 8px;">
                            {{ $terima->supplier?->alamat_txt ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7.2pt; padding: 2px 8px 5px 8px;">Kontak HP/Telp:</td>
                        <td style="font-weight: bold; color: #0f172a; font-size: 7.2pt; padding: 2px 8px 5px 8px;">
                            {{ $terima->supplier?->kontak_no ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>
            {{-- KOLOM KANAN: Dokumen --}}
            <td style="width: 49%; padding-left: 4px;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 4px;">
                    <tr>
                        <td colspan="2" style="padding: 5px 8px 3px 8px; border-bottom: 1px solid #e2e8f0;">
                            <strong style="color: #0f172a; font-size: 7.8pt;">DOKUMEN &amp; GUDANG PENYIMPANAN:</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 42%; color: #64748b; font-size: 7.2pt; padding: 2px 8px;">Tanggal Masuk:</td>
                        <td style="font-weight: bold; color: #0f172a; font-size: 7.2pt; padding: 2px 8px;">
                            {{ $terima->terima_tgl ? \Carbon\Carbon::parse($terima->terima_tgl)->translatedFormat('d F Y') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7.2pt; padding: 2px 8px;">No. Surat Jalan:</td>
                        <td style="font-weight: bold; color: #0284c7; font-size: 7.2pt; padding: 2px 8px;">
                            {{ $terima->suratjalan_no ?: '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7.2pt; padding: 2px 8px;">Ref. Purchase Order:</td>
                        <td style="font-weight: bold; color: #0f172a; font-size: 7.2pt; padding: 2px 8px;">
                            {{ $terima->po ? $terima->po->po_no : 'Non-PO / Langsung' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7.2pt; padding: 2px 8px 5px 8px;">Gudang Simpan:</td>
                        <td style="font-weight: bold; color: #134e5e; font-size: 7.2pt; padding: 2px 8px 5px 8px;">
                            {{ $terima->gudang?->gudang_nm ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- RINCIAN BARANG MASUK DENGAN PERSENTASE LEBAR 100% PASTI PAS --}}
    <table class="data-table">
        <colgroup>
            <col style="width: 3%;">  {{-- No --}}
            <col style="width: 10%;"> {{-- Kode Barang --}}
            <col style="width: 18%;"> {{-- Nama Barang --}}
            <col style="width: 9%;">  {{-- Jenis --}}
            <col style="width: 12%;"> {{-- Kode Batch --}}
            <col style="width: 8%;">  {{-- Expired --}}
            <col style="width: 8%;">  {{-- Qty Masuk --}}
            <col style="width: 4%;">  {{-- Satuan --}}
            <col style="width: 11%;"> {{-- Harga Satuan --}}
            <col style="width: 17%;"> {{-- Total Nilai --}}
        </colgroup>
        <thead>
            <tr>
                <th style="text-align: center;">No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Jenis</th>
                <th>Kode Batch</th>
                <th style="text-align: center;">Expired</th>
                <th style="text-align: right; white-space: nowrap; padding-right: 6px;">Qty</th>
                <th style="text-align: center;">Sat</th>
                <th style="text-align: right; white-space: nowrap; padding-right: 6px;">@Harga</th>
                <th style="text-align: right; white-space: nowrap; padding-right: 6px;">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalQty = 0;
                $totalNominal = 0;
            @endphp
            @forelse($terima->details as $idx => $dtl)
                @php
                    $qty = (float) $dtl->terima_qty;
                    $harga = (float) ($dtl->harga_netto ?: $dtl->harga_nominal);
                    $subtotal = (float) ($dtl->subtotal_tagihan ?: ($qty * $harga));
                    $totalQty += $qty;
                    $totalNominal += $subtotal;
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-size: 6.8pt; color: #475569;">
                        {{ $dtl->barang?->barang_cd ?? '-' }}
                    </td>
                    <td>
                        <strong style="color: #0f172a;">{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                    </td>
                    <td style="color: #475569; font-size: 6.8pt; text-transform: uppercase;">
                        {{ $dtl->barang?->jenisBarang?->jenis_barang_nm ?? ($dtl->barang?->jenisBarang?->jenis_barang_cd ?? '-') }}
                    </td>
                    <td>
                        <span class="badge-batch">{{ $dtl->batch_no }}</span>
                    </td>
                    <td style="text-align: center; font-size: 6.8pt; color: #475569; white-space: nowrap;">
                        {{ $dtl->expired_tgl ? \Carbon\Carbon::parse($dtl->expired_tgl)->format('d/m/Y') : '-' }}
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #134e5e; white-space: nowrap; padding-right: 6px;">
                        {{ number_format($qty, 2, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-size: 6.8pt; color: #475569; white-space: nowrap;">
                        {{ $dtl->barang?->satuanDasar?->satuan_cd ?? ($dtl->barang?->satuanDasar?->satuan_nm ?? '-') }}
                    </td>
                    <td style="text-align: right; font-size: 7.2pt; color: #475569; white-space: nowrap; padding-right: 6px; padding-left: 6px;">
                        {{ number_format($harga, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a; font-size: 7.5pt; white-space: nowrap; padding-right: 6px; padding-left: 6px;">
                        {{ number_format($subtotal, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #94a3b8; padding: 12px;">
                        Tidak ada rincian barang.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot style="background: #f1f5f9; font-weight: bold;">
            <tr>
                <td colspan="6" style="text-align: right; padding: 5px 6px; font-size: 7.2pt; white-space: nowrap;">Total Kuantitas Masuk:</td>
                <td style="text-align: right; color: #134e5e; padding: 5px 6px; font-size: 7.5pt; white-space: nowrap;">
                    {{ number_format($totalQty, 2, ',', '.') }}
                </td>
                <td colspan="2" style="text-align: right; padding: 5px 6px; font-size: 7.2pt; white-space: nowrap;">Total Subtotal:</td>
                <td style="text-align: right; color: #134e5e; font-size: 7.8pt; padding: 5px 6px; white-space: nowrap;">
                    {{ number_format($totalNominal, 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- CATATAN & FINANCIAL BREAKDOWN (Rapi & Rata Sisi Kanan Tanpa Menonjol) --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed;">
        <tr>
            <td style="vertical-align: top; width: 54%; padding-right: 6px;">
                <div style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 8px; background: #ffffff;">
                    <strong style="color: #475569; font-size: 7.5pt; display: block; margin-bottom: 2px;">Catatan Fisik Penerimaan:</strong>
                    <div style="font-size: 7pt; color: #334155; line-height: 1.35;">
                        {{ $terima->catatan_txt ?: 'Seluruh barang telah diperiksa kondisi fisik, kuantitas timbang, dan kemasan dalam keadaan baik saat diterima di gudang.' }}
                    </div>
                </div>
            </td>
            <td style="vertical-align: top; width: 46%;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 4px;">
                    <tr>
                        <td style="padding: 3px 8px; font-size: 7.5pt; color: #64748b;">Subtotal Bruto:</td>
                        <td style="padding: 3px 8px; text-align: right; font-weight: bold; font-size: 7.8pt; white-space: nowrap; color: #0f172a;">
                            Rp {{ number_format((float) ($terima->subtotal_nominal ?: $totalNominal), 2, ',', '.') }}
                        </td>
                    </tr>
                    @if((float) ($terima->potongan_nominal ?? 0) > 0)
                        <tr>
                            <td style="padding: 2px 8px; font-size: 7.5pt; color: #dc2626;">Potongan Langsung:</td>
                            <td style="padding: 2px 8px; text-align: right; color: #dc2626; font-size: 7.5pt; white-space: nowrap; font-weight: bold;">
                                - Rp {{ number_format((float) $terima->potongan_nominal, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    @if((float) ($terima->ppn_nominal ?? 0) > 0)
                        <tr>
                            <td style="padding: 2px 8px; font-size: 7.5pt; color: #0284c7;">PPN:</td>
                            <td style="padding: 2px 8px; text-align: right; color: #0284c7; font-size: 7.5pt; white-space: nowrap; font-weight: bold;">
                                + Rp {{ number_format((float) $terima->ppn_nominal, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    <tr style="border-top: 1px solid #cbd5e1; background: #f1f5f9;">
                        <td style="padding: 4px 8px; font-weight: bold; color: #0f172a; font-size: 7.8pt;">Total Tagihan Masuk:</td>
                        <td style="padding: 4px 8px; text-align: right; font-weight: bold; color: #134e5e; font-size: 8.5pt; white-space: nowrap;">
                            Rp {{ number_format((float) ($terima->total_tagihan ?: $totalNominal), 2, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- KOLOM TANDA TANGAN FORMAL --}}
    <table class="sign-table">
        <tr>
            <td>
                <div>Diserahkan Oleh,</div>
                <div style="color: #64748b; font-size: 6.5pt;">(Sopir / Ekspedisi)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 7pt; color: #64748b;">Tgl: ............................</div>
            </td>
            <td>
                <div>Diperiksa Oleh,</div>
                <div style="color: #64748b; font-size: 6.5pt;">(Quality Control / QC)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 7pt; color: #64748b;">Tgl: ............................</div>
            </td>
            <td>
                <div>Diterima Oleh,</div>
                <div style="color: #64748b; font-size: 6.5pt;">(Petugas Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; font-size: 7.5pt;">{{ $printedBy ?? 'Staff Gudang' }}</div>
            </td>
            <td>
                <div>Mengetahui,</div>
                <div style="color: #64748b; font-size: 6.5pt;">(Kepala Bag. Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; font-size: 7.5pt;">Supervisor Gudang</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 10px; font-size: 6.5pt; color: #94a3b8; text-align: justify; border-top: 1px dotted #cbd5e1; padding-top: 3px;">
        Dokumen ini merupakan Bukti Resmi Penerimaan Barang Fisik (GRN) pada sistem ERP PT. Mirasa Food Industry. Dicetak otomatis pada {{ $printedAt }} WIB oleh {{ $printedBy }}.
    </div>

    {{-- Script Nomor Halaman Otomatis di DomPDF --}}
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT} | Dicetak otomatis melalui Sistem ERP PT Mirasa Food Industry";
            $size = 7;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.5, 0.5, 0.5));
        }
    </script>

</body>
</html>
