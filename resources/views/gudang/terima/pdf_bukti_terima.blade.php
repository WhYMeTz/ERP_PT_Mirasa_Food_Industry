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

        /* Kop Surat Perusahaan */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #134e5e;
            padding-bottom: 6px;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        .kop-logo {
            width: 46px;
            vertical-align: middle;
        }
        .kop-text {
            padding-left: 8px;
            vertical-align: middle;
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
            width: 250px;
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
        .info-table {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .info-table td {
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

        /* Summary / Footer Table */
        .summary-box {
            width: 100%;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 4px;
            padding: 5px 8px;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .summary-box td {
            padding: 1.5px 0;
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

    {{-- DOKUMEN & MITRA INFORMASI --}}
    <table class="info-table">
        <tr>
            <td style="width: 50%; padding-right: 4px;">
                <div class="info-card">
                    <strong style="color: #0f172a; font-size: 7.8pt; display: block; margin-bottom: 3px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">
                        SUPPLIER PENGIRIM:
                    </strong>
                    <div style="font-size: 8.5pt; font-weight: bold; color: #0f172a;">{{ $terima->supplier?->supplier_nm ?? '-' }}</div>
                    <div style="color: #64748b; font-size: 7pt; font-family: monospace;">Kode: {{ $terima->supplier?->supplier_cd ?? '-' }}</div>
                    <div style="color: #475569; font-size: 7.2pt; margin-top: 1px;">
                        {{ $terima->supplier?->alamat_txt ?? 'Alamat tidak terdata' }}
                    </div>
                    <div style="color: #475569; font-size: 7.2pt; margin-top: 1px;">
                        Kontak: <strong>{{ $terima->supplier?->kontak_no ?? '-' }}</strong>
                    </div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 4px;">
                <div class="info-card">
                    <strong style="color: #0f172a; font-size: 7.8pt; display: block; margin-bottom: 3px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">
                        DOKUMEN &amp; GUDANG PENYIMPANAN:
                    </strong>
                    <table style="width: 100%; border-collapse: collapse; font-size: 7.2pt;">
                        <tr>
                            <td style="width: 44%; color: #64748b; padding: 1px 0;">Tanggal Masuk:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">
                                {{ $terima->terima_tgl ? \Carbon\Carbon::parse($terima->terima_tgl)->translatedFormat('d F Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">No. Surat Jalan:</td>
                            <td style="font-weight: bold; color: #0284c7; padding: 1px 0; font-family: monospace;">
                                {{ $terima->suratjalan_no ?: '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Ref. Purchase Order:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0; font-family: monospace;">
                                {{ $terima->po ? $terima->po->po_no : 'Non-PO / Langsung' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Gudang Simpan:</td>
                            <td style="font-weight: bold; color: #134e5e; padding: 1px 0;">
                                {{ $terima->gudang?->gudang_nm ?? '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- RINCIAN BARANG MASUK DENGAN PERSENTASE LEBAR 100% PASTI PAS --}}
    <table class="data-table">
        <colgroup>
            <col style="width: 4%;">  {{-- No --}}
            <col style="width: 11%;"> {{-- Kode Barang --}}
            <col style="width: 21%;"> {{-- Nama Barang --}}
            <col style="width: 10%;"> {{-- Jenis --}}
            <col style="width: 14%;"> {{-- Kode Batch --}}
            <col style="width: 9%;">  {{-- Expired --}}
            <col style="width: 8%;">  {{-- Qty Masuk --}}
            <col style="width: 5%;">  {{-- Satuan --}}
            <col style="width: 9%;">  {{-- Harga Satuan --}}
            <col style="width: 9%;">  {{-- Total Nilai --}}
        </colgroup>
        <thead>
            <tr>
                <th style="text-align: center;">No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Jenis</th>
                <th>Kode Batch</th>
                <th style="text-align: center;">Expired</th>
                <th style="text-align: right;">Qty</th>
                <th style="text-align: center;">Sat</th>
                <th style="text-align: right;">@Harga</th>
                <th style="text-align: right;">Total Nilai</th>
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
                    <td style="text-align: center; font-size: 6.8pt; color: #475569;">
                        {{ $dtl->expired_tgl ? \Carbon\Carbon::parse($dtl->expired_tgl)->format('d/m/Y') : '-' }}
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #134e5e;">
                        {{ number_format($qty, 2, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-size: 6.8pt; color: #475569;">
                        {{ $dtl->barang?->satuanDasar?->satuan_cd ?? ($dtl->barang?->satuanDasar?->satuan_nm ?? '-') }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-size: 6.8pt; color: #475569;">
                        {{ number_format($harga, 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; color: #0f172a; font-size: 7pt;">
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
                <td colspan="6" style="text-align: right; padding: 4px 6px; font-size: 7.2pt;">Total Kuantitas Masuk:</td>
                <td style="text-align: right; color: #134e5e; padding: 4px; font-size: 7.5pt;">
                    {{ number_format($totalQty, 2, ',', '.') }}
                </td>
                <td colspan="2" style="text-align: right; padding: 4px 6px; font-size: 7.2pt;">Total Subtotal:</td>
                <td style="text-align: right; font-family: monospace; color: #134e5e; font-size: 7.5pt; padding: 4px;">
                    {{ number_format($totalNominal, 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- CATATAN & FINANCIAL BREAKDOWN --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 4px; table-layout: fixed;">
        <tr>
            <td style="vertical-align: top; width: 55%; padding-right: 6px;">
                <div style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px 8px; background: #ffffff;">
                    <strong style="color: #475569; font-size: 7.5pt; display: block; margin-bottom: 2px;">Catatan Fisik Penerimaan:</strong>
                    <div style="font-size: 7pt; color: #334155; line-height: 1.35;">
                        {{ $terima->catatan_txt ?: 'Seluruh barang telah diperiksa kondisi fisik, kuantitas timbang, dan kemasan dalam keadaan baik saat diterima di gudang.' }}
                    </div>
                </div>
            </td>
            <td style="vertical-align: top; width: 45%;">
                <div class="summary-box">
                    <table>
                        <tr>
                            <td style="color: #64748b;">Subtotal Bruto:</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;">
                                Rp {{ number_format((float) ($terima->subtotal_nominal ?: $totalNominal), 2, ',', '.') }}
                            </td>
                        </tr>
                        @if((float) ($terima->potongan_nominal ?? 0) > 0)
                            <tr>
                                <td style="color: #dc2626;">Potongan Langsung:</td>
                                <td style="text-align: right; font-family: monospace; color: #dc2626;">
                                    - Rp {{ number_format((float) $terima->potongan_nominal, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        @if((float) ($terima->ppn_nominal ?? 0) > 0)
                            <tr>
                                <td style="color: #0284c7;">PPN:</td>
                                <td style="text-align: right; font-family: monospace; color: #0284c7;">
                                    + Rp {{ number_format((float) $terima->ppn_nominal, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        <tr style="border-top: 1px solid #cbd5e1;">
                            <td style="font-weight: bold; color: #0f172a; padding-top: 2px;">Total Tagihan Masuk:</td>
                            <td style="text-align: right; font-weight: bold; font-family: monospace; color: #134e5e; font-size: 8.5pt; padding-top: 2px;">
                                Rp {{ number_format((float) ($terima->total_tagihan ?: $totalNominal), 2, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
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
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 7;
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $width = $fontMetrics->get_text_width($text, $font, $size);
            // Kertas A4 Portrait: lebar 595.28 pt, tinggi 841.89 pt
            $pdf->page_text(595.28 - $width - 28, 841.89 - 18, $text, $font, $size, array(0.4, 0.4, 0.4));
        }
    </script>

</body>
</html>
