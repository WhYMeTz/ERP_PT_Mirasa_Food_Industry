@php
    if (empty($logoBase64)) {
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
    }

    $subtotal = 0;
    foreach ($po->details as $d) {
        $qty = (float) $d->pesan_qty;
        $price = (float) ($d->harga_nominal ?? 0);
        $lineTotal = (float) ($d->subtotal_nominal ?? ($qty * $price));
        $subtotal += $lineTotal;
    }

    $ppn = (float) ($po->ppn_nominal ?? 0);
    if ($ppn == 0 && ($po->is_harga_termasuk_pajak || str_contains(strtoupper($po->catatan_txt ?? ''), 'PPN'))) {
        $ppn = round($subtotal * 0.11);
    }
    $grandTotal = (float) ($po->total_tagihan ?? ($subtotal + $ppn));

    $tglPo = $po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->format('d M Y') : date('d M Y');
    $tglEta = $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang)->locale('id')->translatedFormat('d F Y') : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PO - {{ $po->po_no }} - PT Mirasa Food Industry</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #000000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* 1. KOP SURAT HACCP */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .kop-table td {
            vertical-align: middle;
            border: none;
        }
        .kop-logo {
            width: 75px;
            text-align: left;
            padding-right: 8px;
        }
        .kop-title {
            text-align: left;
            padding-left: 5px;
        }
        .company-name {
            font-size: 13pt;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.03em;
        }
        .form-name {
            font-size: 11pt;
            font-weight: 700;
            color: #000000;
            margin-top: 3px;
            letter-spacing: 0.02em;
        }
        .kop-meta {
            width: 255px;
            text-align: right;
            vertical-align: top;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .meta-table td {
            padding: 2px 3px;
            vertical-align: top;
        }
        .meta-label {
            width: 95px;
            text-align: left;
            font-weight: 600;
        }
        .meta-sep {
            width: 8px;
            text-align: center;
        }
        .meta-val {
            text-align: left;
            font-weight: 600;
        }

        .kop-divider {
            border: none;
            border-top: 2px solid #000000;
            margin: 4px 0 12px 0;
        }

        /* 2. JUDUL PURCHASE ORDER */
        .doc-title {
            text-align: center;
            font-size: 14pt;
            font-weight: 800;
            letter-spacing: 0.06em;
            margin: 0 0 12px 0;
            color: #000000;
        }

        /* 3. INFO PO & KOTAK SUPPLIER */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            vertical-align: top;
            padding: 0;
        }

        .po-params-table {
            border-collapse: collapse;
            font-size: 9pt;
        }
        .po-params-table td {
            padding: 2px 4px;
        }
        .po-no-red {
            color: #dc2626;
            font-weight: 800;
            font-size: 10pt;
            font-family: 'Courier New', Courier, monospace;
        }

        .supplier-box {
            border: 1px solid #000000;
            padding: 6px 10px;
            background: #ffffff;
            font-size: 8.5pt;
            line-height: 1.35;
            min-height: 65px;
        }

        /* 4. KOTAK SHIP TO & INVOICE TO */
        .address-row-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .address-box {
            border: 1px solid #000000;
            padding: 6px 10px;
            font-size: 8.5pt;
            line-height: 1.35;
            vertical-align: top;
        }

        /* 5. TABEL ITEM PESANAN */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 0;
        }
        .items-table th {
            border: 1px solid #000000;
            background-color: #d1d5db;
            color: #000000;
            font-weight: 800;
            font-size: 8.5pt;
            padding: 5px 4px;
            text-align: center;
        }
        .items-table td {
            border-left: 1px solid #000000;
            border-right: 1px solid #000000;
            border-top: none;
            border-bottom: none;
            padding: 4px 6px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .items-table tr.data-row td {
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table tr.blank-row td {
            border-bottom: none;
            height: 18px;
        }

        /* 6. NOTE & TOTAL SECTION */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            border-top: 1px solid #000000;
            margin-bottom: 10px;
        }
        .summary-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .note-cell {
            width: 62%;
            background: #ffffff;
            line-height: 1.4;
        }
        .total-box-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .total-box-table td {
            border: none;
            padding: 2px 4px;
        }

        /* 7. TERM OF PAYMENT & FRANCO BOX */
        .terms-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        .terms-table td {
            border: 1px solid #000000;
            padding: 3px 8px;
        }
        .terms-label {
            width: 140px;
            font-weight: 700;
            border-right: none !important;
        }
        .terms-sep {
            width: 10px;
            text-align: center;
            border-left: none !important;
            border-right: none !important;
        }
        .terms-val {
            border-left: none !important;
        }

        /* 8. SIGNATURE & FOOTNOTE */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .footer-table td {
            border: none;
            vertical-align: top;
        }
        .signature-block {
            text-align: center;
            width: 220px;
            float: right;
        }
        .nb-tax {
            font-size: 8pt;
            font-style: italic;
            font-weight: 700;
            color: #000000;
            margin-top: 35px;
        }
    </style>
</head>
<body>

    {{-- 1. KOP RESMI HACCP PT MIRASA FOOD INDUSTRY --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if (!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 54px; height: auto; display: block;" alt="Logo Cap Payung">
                @endif
                <div style="font-size: 5.5pt; font-weight: 800; color: #dc2626; margin-top: 2px; letter-spacing: 0.02em; text-align: center;">
                    ENAK &bull; GURIH &bull; LEZAT
                </div>
            </td>
            <td class="kop-title">
                <div class="company-name">PT MIRASA FOOD INDUSTRY</div>
                <div class="form-name">FORM PERMINTAAN BARANG</div>
            </td>
            <td class="kop-meta">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Nomor dokumen</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Terbitan/Tgl</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">19-08-2021</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Revisi</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">0</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Halaman</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val">1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr class="kop-divider">

    {{-- 2. JUDUL DOKUMEN --}}
    <div class="doc-title">PURCHASE ORDER</div>

    {{-- 3. PARAMETER PO & KOTAK SUPPLIER --}}
    <table class="info-table">
        <tr>
            {{-- Kiri: Parameter PO --}}
            <td style="width: 48%;">
                <table class="po-params-table">
                    <tr>
                        <td style="width: 65px; font-weight: 600;">Quot No</td>
                        <td style="width: 10px; text-align: center;">:</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">PO No.</td>
                        <td style="text-align: center; font-weight: 700;">:</td>
                        <td><span class="po-no-red">{{ $po->po_no }}</span></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Tanggal</td>
                        <td style="text-align: center;">:</td>
                        <td><strong>{{ $tglPo }}</strong></td>
                    </tr>
                </table>
            </td>

            {{-- Jarak antar kolom --}}
            <td style="width: 4%;"></td>

            {{-- Kanan: Kotak Supplier --}}
            <td style="width: 48%;">
                <div class="supplier-box">
                    <strong style="font-size: 9.5pt; text-transform: uppercase;">
                        {{ $po->supplier?->supplier_nm ?? '-' }}
                    </strong>
                    <div>{{ $po->supplier?->alamat_txt ?? '-' }}</div>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 3px; font-size: 8.5pt;">
                        <tr>
                            <td style="width: 45px; padding: 0;">Up</td>
                            <td style="width: 8px; padding: 0;">:</td>
                            <td style="padding: 0;">{{ $po->supplier?->kontak_person ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0;">Telpon</td>
                            <td style="padding: 0;">:</td>
                            <td style="padding: 0;">{{ $po->supplier?->kontak_no ?? $po->supplier?->telepon_no ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- 4. KOTAK SHIP TO & INVOICE TO (SIDE BY SIDE) --}}
    <table class="address-row-table">
        <tr>
            <td class="address-box" style="width: 48%;">
                <strong style="text-decoration: underline;">Ship To :</strong><br>
                <strong>PT MIRASA FOOD INDUSTRY</strong><br>
                JL. Munggur No. 2 Kalangan Wetan, Ambartawang<br>
                Mungkid, Magelang, Jawa Tengah<br>
                Telp : 0293 3280554, 3280556
            </td>
            <td style="width: 4%;"></td>
            <td class="address-box" style="width: 48%;">
                <strong style="text-decoration: underline;">Invoice To :</strong><br>
                <strong>PT MIRASA FOOD INDUSTRY</strong><br>
                JL. Munggur No. 2 Kalangan Wetan, Ambartawang<br>
                Mungkid, Magelang, Jawa Tengah<br>
                Telp : 0293 3280554, 3280556<br>
                NPWP : 07.635.700.4-152.000
            </td>
        </tr>
    </table>

    {{-- 5. TABEL RINCIAN ITEM BARANG --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 32px;">No</th>
                <th style="text-align: left; padding-left: 8px;">Description</th>
                <th style="width: 70px; text-align: right; padding-right: 6px;">Qty</th>
                <th style="width: 48px;">Sat</th>
                <th style="width: 105px; text-align: right; padding-right: 6px;">Unit Price</th>
                <th style="width: 120px; text-align: right; padding-right: 8px;">Total Price</th>
            </tr>
        </thead>
        <tbody>
            @php
                $itemCount = count($po->details);
                $totalTargetRows = max(6, $itemCount);
            @endphp

            @foreach ($po->details as $idx => $d)
                @php
                    $qty = (float) $d->pesan_qty;
                    $price = (float) ($d->harga_nominal ?? 0);
                    $lineTotal = (float) ($d->subtotal_nominal ?? ($qty * $price));
                    $satuan = $d->barang?->satuanDasar?->satuan_cd ?? 'PCS';
                @endphp
                <tr class="data-row">
                    <td style="text-align: center; font-weight: 600;">{{ $idx + 1 }}</td>
                    <td style="padding-left: 8px;">
                        <strong style="text-transform: uppercase;">{{ $d->barang?->barang_nm ?? '-' }}</strong>
                        @if ($d->barang?->barang_cd)
                            <span style="font-size: 7.5pt; color: #4b5563;">({{ $d->barang->barang_cd }})</span>
                        @endif
                    </td>
                    <td style="text-align: right; padding-right: 6px; font-weight: 700;">
                        {{ number_format($qty, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; text-transform: uppercase;">
                        {{ $satuan }}
                    </td>
                    <td style="text-align: right; padding-right: 6px;">
                        <span style="float: left; font-size: 7.5pt;">Rp</span>
                        {{ number_format($price, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; padding-right: 8px; font-weight: 700;">
                        <span style="float: left; font-size: 7.5pt;">Rp</span>
                        {{ number_format($lineTotal, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            {{-- Baris Grid Kosong untuk menjaga tinggi tabel stabil seperti format kertas resmi --}}
            @for ($i = $itemCount; $i < $totalTargetRows; $i++)
                <tr class="blank-row">
                    <td style="text-align: center; color: #9ca3af; font-size: 7.5pt;">{{ $i + 1 }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- 6. NOTE & KOTAK TOTAL / PPN / GRAND TOTAL --}}
    <table class="summary-table">
        <tr>
            <td class="note-cell">
                <strong>NOTE :</strong>
                @if ($po->catatan_txt)
                    <span style="font-weight: 700; color: #000000;">{{ $po->catatan_txt }}</span>
                @elseif ($tglEta)
                    <span style="font-weight: 700; color: #000000;">*Mohon dikirim tanggal {{ $tglEta }}</span>
                @else
                    <span>-</span>
                @endif
            </td>
            <td style="width: 38%; padding: 4px 6px;">
                <table class="total-box-table">
                    <tr>
                        <td style="font-weight: 700; width: 85px;">Total</td>
                        <td style="width: 15px;">Rp</td>
                        <td style="text-align: right; font-weight: 700;">
                            {{ number_format($subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">PPN</td>
                        <td>Rp</td>
                        <td style="text-align: right; font-weight: 700;">
                            {{ number_format($ppn, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #000000;">
                        <td style="font-weight: 800; font-size: 9pt;">Grand Total</td>
                        <td style="font-weight: 800; font-size: 9pt;">Rp</td>
                        <td style="text-align: right; font-weight: 800; font-size: 9.5pt;">
                            {{ number_format($grandTotal, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 7. TERM OF PAYMENT, FRANCO, REMARK --}}
    <table class="terms-table">
        <tr>
            <td class="terms-label">Term of Payment</td>
            <td class="terms-sep">:</td>
            <td class="terms-val">
                <strong>
                    @if (!empty($po->term_of_payment))
                        {{ $po->term_of_payment }}
                    @elseif ($po->supplier?->top_hari && $po->supplier->top_hari > 0)
                        Tempo {{ $po->supplier->top_hari }} Hari (Transfer)
                    @else
                        Transfer
                    @endif
                </strong>
            </td>
        </tr>
        <tr>
            <td class="terms-label">Franco</td>
            <td class="terms-sep">:</td>
            <td class="terms-val">Franco Gudang Magelang</td>
        </tr>
        <tr>
            <td class="terms-label">Remark</td>
            <td class="terms-sep">:</td>
            <td class="terms-val">{{ $po->remark_txt ?: '-' }}</td>
        </tr>
    </table>

    {{-- 8. TANDA TANGAN & FOOTNOTE NB FAKTUR PAJAK --}}
    <table class="footer-table">
        <tr>
            {{-- Kiri: Footnote NPWP & Faktur Pajak --}}
            <td style="width: 60%; vertical-align: bottom;">
                <div class="nb-tax">
                    NB : FAKTUR PAJAK AN. PT MIRASA FOOD INDUSTRY (NPWP: 07.635.700.4-152.000)
                </div>
            </td>

            {{-- Kanan: Tanda Tangan Hormat Kami --}}
            <td style="width: 40%; text-align: center; vertical-align: top;">
                <div style="font-size: 9pt; font-weight: 600; margin-bottom: 4px;">
                    Hormat Kami,
                </div>

                @if ($po->isApproved() || in_array($po->status_cd, ['APPROVED', 'PARTIAL', 'COMPLETED']))
                    @php
                        $verifyUrl = route('po.verify.qr', $po->approval_token ?? 'unverified');
                        $qrCodeSvg = '';
                        try {
                            $qrCodeSvg = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(52)->margin(0)->generate($verifyUrl));
                        } catch (\Throwable $e) {}
                    @endphp

                    <div style="margin: 4px auto; text-align: center;">
                        @if ($qrCodeSvg)
                            <img src="data:image/svg+xml;base64,{{ $qrCodeSvg }}" width="50" height="50" style="display: inline-block; border: 1px solid #cbd5e1; padding: 2px; background: #ffffff;">
                        @else
                            <div style="height: 48px;"></div>
                        @endif
                    </div>

                    <div style="font-size: 9.5pt; font-weight: 800; text-decoration: underline; margin-top: 4px;">
                        {{ $po->approved_by ?? $po->approver?->name ?? 'Habieb Soleh Yoenanto' }}
                    </div>
                    <div style="font-size: 7.5pt; color: #4b5563; font-style: italic;">
                        PT Mirasa Food Industry
                    </div>
                @else
                    <div style="height: 52px;"></div>
                    <div style="font-size: 9.5pt; font-weight: 800; text-decoration: underline;">
                        Habieb Soleh Yoenanto
                    </div>
                    <div style="font-size: 7.5pt; color: #4b5563;">
                        PT Mirasa Food Industry
                    </div>
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
