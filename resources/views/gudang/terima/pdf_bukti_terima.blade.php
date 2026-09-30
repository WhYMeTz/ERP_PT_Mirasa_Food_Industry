<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Penerimaan Barang - {{ $terima->terima_no }}</title>
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
            border-bottom: 2px solid #059669;
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
            color: #059669;
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
            margin-bottom: 10px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            font-size: 8pt;
            border: 1px solid #0f172a;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 4.5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 8pt;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        /* Summary / Footer Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .summary-box {
            width: 45%;
            margin-left: auto;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 4px;
            padding: 6px 10px;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .summary-box td {
            padding: 2px 0;
        }

        /* Signatures */
        .sign-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .sign-table td {
            text-align: center;
            vertical-align: top;
            width: 25%;
            font-size: 8pt;
        }
        .sign-space {
            height: 50px;
        }
        .sign-line {
            border-bottom: 1px solid #334155;
            width: 80%;
            margin: 0 auto 3px auto;
        }

        .badge-batch {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 1px 4px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 7.5pt;
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="height: 48px; width: auto;">
                @else
                    <div style="font-size: 16pt; font-weight: bold; color: #059669;">MIRASA</div>
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
                <div style="font-size: 7.5pt; color: #64748b; text-transform: uppercase;">Goods Receipt Note (GRN)</div>
                <div class="doc-number">{{ $terima->terima_no }}</div>
            </td>
        </tr>
    </table>

    {{-- DOKUMEN & MITRA INFORMASI --}}
    <table class="info-table">
        <tr>
            <td style="width: 50%; padding-right: 5px;">
                <div class="info-card">
                    <strong style="color: #0f172a; font-size: 8.5pt; display: block; margin-bottom: 4px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">
                        SUPPLIER PENGIRIM:
                    </strong>
                    <div style="font-size: 9pt; font-weight: bold; color: #0f172a;">{{ $terima->supplier?->supplier_nm ?? '-' }}</div>
                    <div style="color: #64748b; font-size: 7.5pt; font-family: monospace;">Kode: {{ $terima->supplier?->supplier_cd ?? '-' }}</div>
                    <div style="color: #475569; font-size: 8pt; margin-top: 2px;">
                        {{ $terima->supplier?->alamat_txt ?? 'Alamat tidak terdata' }}
                    </div>
                    <div style="color: #475569; font-size: 8pt; margin-top: 1px;">
                        Kontak: <strong>{{ $terima->supplier?->kontak_no ?? '-' }}</strong>
                    </div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 5px;">
                <div class="info-card">
                    <strong style="color: #0f172a; font-size: 8.5pt; display: block; margin-bottom: 4px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">
                        REFERENSI &amp; GUDANG PENYIMPANAN:
                    </strong>
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr>
                            <td style="width: 42%; color: #64748b; padding: 1px 0;">Tanggal Masuk:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0;">
                                {{ $terima->terima_tgl ? \Carbon\Carbon::parse($terima->terima_tgl)->format('d F Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">No. Surat Jalan:</td>
                            <td style="font-weight: bold; color: #0284c7; padding: 1px 0; font-family: monospace;">
                                {{ $terima->suratjalan_no ?: '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">No. Referensi PO:</td>
                            <td style="font-weight: bold; color: #0f172a; padding: 1px 0; font-family: monospace;">
                                {{ $terima->po ? $terima->po->po_no : 'Non-PO / Pembelian Langsung' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;">Gudang Simpan:</td>
                            <td style="font-weight: bold; color: #059669; padding: 1px 0;">
                                {{ $terima->gudang?->gudang_nm ?? '-' }} ({{ $terima->gudang?->gudang_cd ?? '-' }})
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- RINCIAN BARANG MASUK & BATCH --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="min-width: 180px;">Nama Bahan Baku &amp; Kode</th>
                <th style="width: 80px;">Jenis</th>
                <th style="width: 105px;">No. Batch Fisik</th>
                <th style="width: 70px;">Expired</th>
                <th style="width: 65px; text-align: right;">Kuantitas</th>
                <th style="width: 45px; text-align: left;">Satuan</th>
                <th style="width: 80px; text-align: right;">@Harga Netto</th>
                <th style="width: 95px; text-align: right;">Total Nilai</th>
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
                    <td>
                        <strong style="color: #0f172a;">{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                        <div style="font-family: monospace; font-size: 7pt; color: #64748b;">{{ $dtl->barang?->barang_cd ?? '-' }}</div>
                    </td>
                    <td style="color: #475569; font-size: 7.5pt;">
                        {{ $dtl->barang?->jenisBarang?->jenis_barang_nm ?? ($dtl->barang?->jenisBarang?->jenis_barang_cd ?? 'RAW') }}
                    </td>
                    <td>
                        <span class="badge-batch">{{ $dtl->batch_no }}</span>
                    </td>
                    <td style="color: #475569; font-size: 7.5pt;">
                        {{ $dtl->expired_tgl ? \Carbon\Carbon::parse($dtl->expired_tgl)->format('d/m/Y') : '-' }}
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #059669;">
                        {{ number_format($qty, 2, ',', '.') }}
                    </td>
                    <td style="color: #475569; font-size: 7.5pt;">
                        {{ $dtl->barang?->satuanDasar?->satuan_nm ?? ($dtl->barang?->satuanDasar?->satuan_cd ?? '-') }}
                    </td>
                    <td style="text-align: right; font-family: monospace; color: #475569;">
                        Rp {{ number_format($harga, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-family: monospace; color: #0f172a;">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #94a3b8; padding: 15px;">
                        Tidak ada rincian barang.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot style="background: #f8fafc; font-weight: bold;">
            <tr>
                <td colspan="5" style="text-align: right; padding: 6px;">Total Kuantitas Fisik Masuk:</td>
                <td style="text-align: right; color: #059669; padding: 6px;">
                    {{ number_format($totalQty, 2, ',', '.') }}
                </td>
                <td colspan="2" style="text-align: right; padding: 6px;">Total Tagihan / Nilai:</td>
                <td style="text-align: right; font-family: monospace; color: #059669; font-size: 9pt; padding: 6px;">
                    Rp {{ number_format($totalNominal, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- CATATAN & BREAKDOWN --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 6px;">
        <tr>
            <td style="vertical-align: top; width: 55%; padding-right: 10px;">
                <div style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; background: #ffffff;">
                    <strong style="color: #475569; font-size: 8pt; display: block; margin-bottom: 2px;">Catatan Fisik Penerimaan:</strong>
                    <div style="font-size: 7.5pt; color: #334155; line-height: 1.35;">
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
                                Rp {{ number_format((float) ($terima->subtotal_nominal ?: $totalNominal), 0, ',', '.') }}
                            </td>
                        </tr>
                        @if((float) ($terima->potongan_nominal ?? 0) > 0)
                            <tr>
                                <td style="color: #dc2626;">Potongan Langsung:</td>
                                <td style="text-align: right; font-family: monospace; color: #dc2626;">
                                    - Rp {{ number_format((float) $terima->potongan_nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        @if((float) ($terima->ppn_nominal ?? 0) > 0)
                            <tr>
                                <td style="color: #0284c7;">PPN 11%:</td>
                                <td style="text-align: right; font-family: monospace; color: #0284c7;">
                                    + Rp {{ number_format((float) $terima->ppn_nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        <tr style="border-top: 1px solid #cbd5e1;">
                            <td style="font-weight: bold; color: #0f172a; padding-top: 3px;">Total Tagihan Masuk:</td>
                            <td style="text-align: right; font-weight: bold; font-family: monospace; color: #059669; font-size: 9.5pt; padding-top: 3px;">
                                Rp {{ number_format((float) ($terima->total_tagihan ?: $totalNominal), 0, ',', '.') }}
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
                <div style="color: #64748b; font-size: 7pt;">(Sopir / Ekspedisi)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 7.5pt; color: #64748b;">Tgl: ............................</div>
            </td>
            <td>
                <div>Diperiksa Oleh,</div>
                <div style="color: #64748b; font-size: 7pt;">(Quality Control / QC)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 7.5pt; color: #64748b;">Tgl: ............................</div>
            </td>
            <td>
                <div>Diterima Oleh,</div>
                <div style="color: #64748b; font-size: 7pt;">(Petugas Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; font-size: 8pt;">{{ $printedBy ?? 'Staff Gudang' }}</div>
            </td>
            <td>
                <div>Mengetahui,</div>
                <div style="color: #64748b; font-size: 7pt;">(Kepala Bag. Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; font-size: 8pt;">Supervisor Gudang</div>
            </td>
        </tr>
    </table>

    {{-- WATERMARK / FOOTNOTE --}}
    <div style="margin-top: 15px; font-size: 7pt; color: #94a3b8; text-align: justify; border-top: 1px dotted #cbd5e1; padding-top: 4px;">
        Dokumen ini merupakan Bukti Resmi Penerimaan Barang Fisik (GRN) pada sistem ERP PT. Mirasa Food Industry. Dicetak otomatis pada {{ $printedAt }} WIB oleh {{ $printedBy }}.
    </div>

</body>
</html>
