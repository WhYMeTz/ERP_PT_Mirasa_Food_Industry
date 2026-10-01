<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Penerimaan Barang - {{ $terima->terima_no }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 8mm 10mm 14mm 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #134e5e;
            padding-bottom: 5px;
            margin-bottom: 7px;
        }
        .kop-logo { width: 50px; vertical-align: middle; }
        .kop-text { padding-left: 10px; vertical-align: middle; text-align: left; }
        .kop-company { font-size: 12pt; font-weight: bold; color: #0f172a; letter-spacing: 0.5px; }
        .kop-sub { font-size: 6.8pt; color: #64748b; margin-top: 1px; }
        .kop-title-box { text-align: right; vertical-align: middle; white-space: nowrap; }
        .doc-title { font-size: 11pt; font-weight: bold; color: #134e5e; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-number { font-size: 9pt; font-weight: bold; color: #0f172a; margin-top: 2px; }

        /* Info Cards */
        .info-outer { width: 100%; border-collapse: collapse; margin-bottom: 7px; }
        .info-outer td { vertical-align: top; padding: 0; }

        /* Tabel Data Barang */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        table.data-table thead { display: table-header-group; }
        table.data-table tr { page-break-inside: avoid; }
        table.data-table th {
            background-color: #134e5e;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 3px;
            font-size: 6.8pt;
            border: 1px solid #0d3844;
            text-transform: uppercase;
            word-wrap: break-word;
        }
        table.data-table td {
            padding: 3px 3px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 6.8pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data-table tr:nth-child(even) td { background-color: #fafafa; }

        /* Signatures */
        .sign-table {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
            page-break-inside: avoid;
            table-layout: fixed;
        }
        .sign-table td {
            text-align: center;
            vertical-align: top;
            width: 25%;
            font-size: 7pt;
            padding: 0 4px;
        }
        .sign-space { height: 38px; }
        .sign-line { border-bottom: 1px solid #334155; width: 82%; margin: 0 auto 2px auto; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="height: 38px; width: auto;">
                @else
                    <div style="font-size: 14pt; font-weight: bold; color: #134e5e;">MIRASA</div>
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
                <div style="font-size: 6.8pt; color: #64748b; text-transform: uppercase;">Goods Receipt Note (GRN)</div>
                <div class="doc-number">{{ $terima->terima_no }}</div>
            </td>
        </tr>
    </table>

    {{-- INFO CARD KIRI & KANAN --}}
    <table class="info-outer">
        <tr>
            {{-- KOLOM KIRI: Supplier --}}
            <td style="width: 49%; padding-right: 4px;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; background: #f8fafc;">
                    <tr>
                        <td colspan="2" style="padding: 4px 8px 3px 8px; border-bottom: 1px solid #e2e8f0;">
                            <strong style="color: #0f172a; font-size: 7.5pt;">SUPPLIER PENGIRIM:</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 36%; color: #64748b; font-size: 7pt; padding: 2px 8px;">Nama Supplier:</td>
                        <td style="font-weight: bold; color: #0f172a; font-size: 7pt; padding: 2px 8px;">{{ $terima->supplier?->supplier_nm ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7pt; padding: 2px 8px;">Kode Supplier:</td>
                        <td style="font-weight: bold; color: #475569; font-size: 7pt; padding: 2px 8px;">{{ $terima->supplier?->supplier_cd ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7pt; padding: 2px 8px;">Alamat Kantor:</td>
                        <td style="color: #334155; font-size: 7pt; padding: 2px 8px;">{{ $terima->supplier?->alamat_txt ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7pt; padding: 2px 8px 4px 8px;">Kontak HP/Telp:</td>
                        <td style="font-weight: bold; color: #0f172a; font-size: 7pt; padding: 2px 8px 4px 8px;">{{ $terima->supplier?->kontak_no ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            {{-- KOLOM KANAN: Dokumen --}}
            <td style="width: 49%; padding-left: 4px;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; background: #f8fafc;">
                    <tr>
                        <td colspan="2" style="padding: 4px 8px 3px 8px; border-bottom: 1px solid #e2e8f0;">
                            <strong style="color: #0f172a; font-size: 7.5pt;">DOKUMEN &amp; GUDANG PENYIMPANAN:</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 40%; color: #64748b; font-size: 7pt; padding: 2px 8px;">Tanggal Masuk:</td>
                        <td style="font-weight: bold; color: #0f172a; font-size: 7pt; padding: 2px 8px;">{{ $terima->terima_tgl ? \Carbon\Carbon::parse($terima->terima_tgl)->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7pt; padding: 2px 8px;">No. Surat Jalan:</td>
                        <td style="font-weight: bold; color: #0284c7; font-size: 7pt; padding: 2px 8px;">{{ $terima->suratjalan_no ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7pt; padding: 2px 8px;">Ref. Purchase Order:</td>
                        <td style="font-weight: bold; color: #0f172a; font-size: 7pt; padding: 2px 8px;">{{ $terima->po ? $terima->po->po_no : 'Non-PO / Langsung' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 7pt; padding: 2px 8px 4px 8px;">Gudang Simpan:</td>
                        <td style="font-weight: bold; color: #134e5e; font-size: 7pt; padding: 2px 8px 4px 8px;">{{ $terima->gudang?->gudang_nm ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- RINCIAN BARANG MASUK (LENGKAP SESUAI SHOW PAGE) --}}
    @php
        $totalQty      = 0;
        $totalBruto    = 0;
        $totalDiskon   = 0;
        $totalPotongan = 0;
        $totalHpp      = 0;
        $totalTagihan  = 0;
    @endphp

    <table class="data-table">
        <colgroup>
            <col style="width: 2.5%;">   {{-- No --}}
            <col style="width: 8%;">     {{-- Kode --}}
            <col style="width: 14%;">    {{-- Nama Barang --}}
            <col style="width: 7%;">     {{-- Batch --}}
            <col style="width: 6.5%;">   {{-- Expired --}}
            <col style="width: 4%;">     {{-- Grade --}}
            <col style="width: 6%;">     {{-- Qty --}}
            <col style="width: 3.5%;">   {{-- Sat --}}
            <col style="width: 8.5%;">   {{-- Harga Awal --}}
            <col style="width: 5%;">     {{-- Diskon % --}}
            <col style="width: 8%;">     {{-- Potongan --}}
            <col style="width: 9%;">     {{-- HPP Masuk --}}
            <col style="width: 5%;">     {{-- Pajak --}}
            <col style="width: 12.5%;">  {{-- Subtotal Tagihan --}}
        </colgroup>
        <thead>
            <tr>
                <th style="text-align: center;">No</th>
                <th>Kode</th>
                <th>Nama Barang / Komoditas</th>
                <th>Batch / Lot</th>
                <th style="text-align: center;">Expired</th>
                <th style="text-align: center;">Grade</th>
                <th style="text-align: right; padding-right: 5px;">Qty Netto</th>
                <th style="text-align: center;">Sat</th>
                <th style="text-align: right; padding-right: 5px;">Harga Satuan</th>
                <th style="text-align: center;">Diskon %</th>
                <th style="text-align: right; padding-right: 5px;">Potongan</th>
                <th style="text-align: right; padding-right: 5px;">HPP Masuk</th>
                <th style="text-align: center;">Pajak</th>
                <th style="text-align: right; padding-right: 5px;">Subtotal Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($terima->details as $idx => $dtl)
                @php
                    $hargaAwal    = (float) ($dtl->harga_nominal ?? 0);
                    $diskonPct    = (float) ($dtl->diskon_persen ?? 0);
                    $potonganItem = (float) ($dtl->potongan_nominal ?? 0);
                    $hargaNet     = (float) ($dtl->harga_netto ?: ($hargaAwal * (1 - $diskonPct / 100)));
                    $qty          = (float) $dtl->terima_qty;
                    $hpp          = (float) ($dtl->subtotal_netto ?: max(0, ($qty * $hargaNet) - $potonganItem));
                    $isItemPpn    = ($dtl->ppn_tipe === 'PPN_11');
                    $tagihan      = (float) ($dtl->subtotal_tagihan ?: ($hpp + ($isItemPpn ? round($hpp * 0.11) : 0)));
                    $diskonNom    = $qty * $hargaAwal * ($diskonPct / 100);

                    $totalQty      += $qty;
                    $totalBruto    += $qty * $hargaAwal;
                    $totalDiskon   += $diskonNom;
                    $totalPotongan += $potonganItem;
                    $totalHpp      += $hpp;
                    $totalTagihan  += $tagihan;
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="font-size: 6.5pt; color: #475569;">{{ $dtl->barang?->barang_cd ?? '-' }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                    </td>
                    <td style="font-size: 6.5pt; font-weight: bold; color: #0d3844; word-break: break-all;">{{ $dtl->batch_no }}</td>
                    <td style="text-align: center; font-size: 6.5pt; color: #475569; white-space: nowrap;">
                        {{ $dtl->expired_tgl ? \Carbon\Carbon::parse($dtl->expired_tgl)->format('d/m/Y') : '-' }}
                    </td>
                    <td style="text-align: center; font-size: 6.5pt; color: #334155;">{{ $dtl->grade_cd ?? 'A' }}</td>
                    <td style="text-align: right; font-weight: bold; color: #047857; white-space: nowrap; padding-right: 5px;">
                        {{ number_format($qty, 2, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-size: 6.5pt; color: #475569; white-space: nowrap;">
                        {{ $dtl->barang?->satuanDasar?->satuan_cd ?? ($dtl->barang?->satuanDasar?->satuan_nm ?? '-') }}
                    </td>
                    <td style="text-align: right; font-size: 6.8pt; color: #475569; white-space: nowrap; padding-right: 5px;">
                        {{ number_format($hargaAwal, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-size: 6.5pt; color: {{ $diskonPct > 0 ? '#d97706' : '#94a3b8' }}; white-space: nowrap;">
                        {{ $diskonPct > 0 ? number_format($diskonPct, 1, ',', '.') . '%' : '-' }}
                    </td>
                    <td style="text-align: right; font-size: 6.8pt; color: {{ $potonganItem > 0 ? '#dc2626' : '#94a3b8' }}; white-space: nowrap; padding-right: 5px;">
                        {{ $potonganItem > 0 ? '- ' . number_format($potonganItem, 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align: right; font-size: 6.8pt; font-weight: 600; color: #047857; white-space: nowrap; padding-right: 5px;">
                        {{ number_format($hpp, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-size: 6pt; white-space: nowrap;">
                        @if($isItemPpn)
                            <span style="background: #e0f2fe; color: #0284c7; padding: 1px 3px; font-weight: bold;">PPN 11%</span>
                        @else
                            <span style="color: #94a3b8;">Non-Pajak</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a; white-space: nowrap; padding-right: 5px;">
                        {{ number_format($tagihan, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="text-align: center; color: #94a3b8; padding: 10px;">
                        Tidak ada rincian barang.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot style="background: #f1f5f9; font-weight: bold;">
            <tr>
                <td colspan="6" style="text-align: right; padding: 4px 5px; font-size: 6.8pt; white-space: nowrap;">Total Keseluruhan:</td>
                <td style="text-align: right; color: #047857; padding: 4px 5px; white-space: nowrap; padding-right: 5px;">{{ number_format($totalQty, 2, ',', '.') }}</td>
                <td colspan="2" style="text-align: right; padding: 4px 5px; font-size: 6.8pt; white-space: nowrap;">Sub Bruto:</td>
                <td colspan="2" style="text-align: right; color: #475569; padding: 4px 5px; white-space: nowrap; padding-right: 5px; font-size: 6.8pt;">
                    {{ number_format($totalBruto - $totalDiskon - $totalPotongan, 0, ',', '.') }}
                </td>
                <td style="text-align: right; color: #047857; padding: 4px 5px; white-space: nowrap; padding-right: 5px;">{{ number_format($totalHpp, 0, ',', '.') }}</td>
                <td></td>
                <td style="text-align: right; color: #134e5e; font-size: 7.5pt; padding: 4px 5px; white-space: nowrap; padding-right: 5px;">{{ number_format($totalTagihan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- CATATAN & FINANCIAL BREAKDOWN LENGKAP --}}
    @php
        $calcSubtotalBruto = (float) ($terima->subtotal_nominal ?? 0);
        if ($calcSubtotalBruto <= 0) {
            $calcSubtotalBruto = $totalBruto;
        }
        $calcDiskonItem    = $totalDiskon;
        $calcPotonganItem  = $totalPotongan;
        $totalSemuaPotongan = (float) ($terima->potongan_nominal ?? 0);
        $calcPotonganFaktur = max(0, $totalSemuaPotongan - $calcPotonganItem);
        $totalDppPpn  = (float) $terima->details->where('ppn_tipe', 'PPN_11')->sum('subtotal_netto');
        $totalDppNonPpn = (float) $terima->details->where('ppn_tipe', '!=', 'PPN_11')->sum('subtotal_netto');
        if ($totalDppPpn == 0 && $totalDppNonPpn == 0) {
            if ($terima->ppn_tipe === 'PPN_11') {
                $totalDppPpn = (float) ($terima->dpp_nominal ?: ($calcSubtotalBruto - $calcDiskonItem - $totalSemuaPotongan));
            } else {
                $totalDppNonPpn = (float) ($calcSubtotalBruto - $calcDiskonItem - $totalSemuaPotongan);
            }
        }
        $calcPpn     = (float) ($terima->ppn_nominal ?? $terima->details->sum('ppn_nominal'));
        $calcTagihan = (float) ($terima->total_tagihan ?? ($totalDppPpn + $totalDppNonPpn + $calcPpn - $calcPotonganFaktur));
        $calcHpp     = $totalHpp > 0 ? $totalHpp : (float) ($terima->total_nominal ?? 0);
    @endphp

    <table style="width: 100%; border-collapse: collapse; margin-top: 4px; table-layout: fixed;">
        <tr>
            {{-- CATATAN --}}
            <td style="vertical-align: top; width: 55%; padding-right: 6px;">
                <div style="border: 1px solid #cbd5e1; border-radius: 3px; padding: 5px 8px; background: #ffffff;">
                    <strong style="color: #475569; font-size: 7pt; display: block; margin-bottom: 2px;">Catatan Fisik Penerimaan:</strong>
                    <div style="font-size: 6.8pt; color: #334155; line-height: 1.35;">
                        {{ $terima->catatan_txt ?: 'Seluruh barang telah diperiksa kondisi fisik, kuantitas timbang, dan kemasan dalam keadaan baik saat diterima di gudang.' }}
                    </div>
                </div>
            </td>
            {{-- FINANCIAL BREAKDOWN LENGKAP --}}
            <td style="vertical-align: top; width: 45%;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; background: #f8fafc;">
                    <tr>
                        <td style="padding: 2.5px 8px; font-size: 7pt; color: #64748b;">Subtotal Nilai Kotor:</td>
                        <td style="padding: 2.5px 8px; text-align: right; font-weight: bold; font-size: 7pt; white-space: nowrap; color: #0f172a;">
                            Rp {{ number_format($calcSubtotalBruto, 0, ',', '.') }}
                        </td>
                    </tr>
                    @if($calcDiskonItem > 0)
                    <tr>
                        <td style="padding: 2px 8px; font-size: 7pt; color: #d97706;">Akumulasi Diskon Item:</td>
                        <td style="padding: 2px 8px; text-align: right; color: #d97706; font-size: 7pt; white-space: nowrap; font-weight: bold;">
                            - Rp {{ number_format($calcDiskonItem, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    @if($calcPotonganItem > 0)
                    <tr>
                        <td style="padding: 2px 8px; font-size: 7pt; color: #dc2626;">Akumulasi Potongan Item:</td>
                        <td style="padding: 2px 8px; text-align: right; color: #dc2626; font-size: 7pt; white-space: nowrap; font-weight: bold;">
                            - Rp {{ number_format($calcPotonganItem, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    @if($calcPotonganFaktur > 0)
                    <tr>
                        <td style="padding: 2px 8px; font-size: 7pt; color: #dc2626;">Potongan Tambahan Faktur:</td>
                        <td style="padding: 2px 8px; text-align: right; color: #dc2626; font-size: 7pt; white-space: nowrap; font-weight: bold;">
                            - Rp {{ number_format($calcPotonganFaktur, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    @if($totalDppPpn > 0)
                    <tr style="border-top: 1px dashed #cbd5e1;">
                        <td style="padding: 2px 8px; font-size: 7pt; font-weight: 600; color: #334155;">DPP Kena PPN (11%):</td>
                        <td style="padding: 2px 8px; text-align: right; font-weight: bold; font-size: 7pt; white-space: nowrap; color: #0f172a;">
                            Rp {{ number_format($totalDppPpn, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 8px; font-size: 7pt; color: #0284c7;">Nominal PPN (11%):</td>
                        <td style="padding: 2px 8px; text-align: right; color: #0284c7; font-size: 7pt; white-space: nowrap; font-weight: bold;">
                            + Rp {{ number_format($calcPpn, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    @if($totalDppNonPpn > 0)
                    <tr>
                        <td style="padding: 2px 8px; font-size: 7pt; color: #047857;">Subtotal Non-Pajak (0%):</td>
                        <td style="padding: 2px 8px; text-align: right; color: #047857; font-size: 7pt; white-space: nowrap; font-weight: bold;">
                            Rp {{ number_format($totalDppNonPpn, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 2px solid #0f172a; background: #f1f5f9;">
                        <td style="padding: 4px 8px; font-weight: bold; color: #0f172a; font-size: 7.5pt;">Total Tagihan Akhir Supplier:</td>
                        <td style="padding: 4px 8px; text-align: right; font-weight: bold; color: #134e5e; font-size: 8pt; white-space: nowrap;">
                            Rp {{ number_format($calcTagihan, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="background: #dcfce7;">
                        <td style="padding: 3px 8px; font-size: 6.8pt; font-weight: 700; color: #166534; text-transform: uppercase;">Total Nilai Masuk Stok (HPP):</td>
                        <td style="padding: 3px 8px; text-align: right; font-weight: bold; color: #15803d; font-size: 7.5pt; white-space: nowrap;">
                            Rp {{ number_format($calcHpp, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- KOLOM TANDA TANGAN --}}
    <table class="sign-table">
        <tr>
            <td>
                <div>Diserahkan Oleh,</div>
                <div style="color: #64748b; font-size: 6.5pt;">(Sopir / Ekspedisi)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 6.8pt; color: #64748b;">Tgl: ..........................</div>
            </td>
            <td>
                <div>Diperiksa Oleh,</div>
                <div style="color: #64748b; font-size: 6.5pt;">(Quality Control / QC)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 6.8pt; color: #64748b;">Tgl: ..........................</div>
            </td>
            <td>
                <div>Diterima Oleh,</div>
                <div style="color: #64748b; font-size: 6.5pt;">(Petugas Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; font-size: 7pt;">{{ $printedBy ?? 'Staff Gudang' }}</div>
            </td>
            <td>
                <div>Mengetahui,</div>
                <div style="color: #64748b; font-size: 6.5pt;">(Kepala Bag. Gudang)</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; font-size: 7pt;">Supervisor Gudang</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 8px; font-size: 6.3pt; color: #94a3b8; text-align: justify; border-top: 1px dotted #cbd5e1; padding-top: 3px;">
        Dokumen ini merupakan Bukti Resmi Penerimaan Barang Fisik (GRN) pada sistem ERP PT. Mirasa Food Industry. Dicetak otomatis pada {{ $printedAt }} WIB oleh {{ $printedBy }}.
    </div>

    {{-- Script Nomor Halaman Otomatis di DomPDF --}}
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT} | Dicetak otomatis melalui Sistem ERP PT Mirasa Food Industry";
            $size = 6.5;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 18;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.5, 0.5, 0.5));
        }
    </script>

</body>
</html>
