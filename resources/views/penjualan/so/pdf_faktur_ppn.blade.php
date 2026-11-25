<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>FAKTUR PENJUALAN - {{ $order->faktur_no ?: ($order->so_no ?? 'DOKUMEN') }}</title>
    <style>
        @page {
            size: 9.5in 5.5in;
            margin: 2.5mm 8mm 2.5mm 8mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* MASTER GRID TABLE (100% SINKRONISASI DENGAN GRID EXCEL FORM ASLI) */
        table.master-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* 1. KOP SURAT */
        .kop-logo-td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 2px;
        }
        .kop-info-td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
        }
        .kop-company {
            font-size: 11pt;
            font-weight: bold;
            font-style: italic;
            letter-spacing: 0.5px;
        }
        .kop-sub {
            font-size: 7.5pt;
            margin-top: 1px;
            font-weight: 500;
        }
        .kop-meta-td {
            border: 1px solid #000;
            padding: 2px 0;
            vertical-align: middle;
            font-size: 7.5pt;
        }

        /* 2. TITLE & DATE */
        .title-text {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1.5px;
            position: relative;
            top: 3px;
        }
        .date-magelang {
            font-size: 8.5pt;
            vertical-align: bottom;
            padding-left: 2px;
        }
        .date-val {
            font-size: 8.5pt;
            font-weight: bold;
            text-align: right;
            vertical-align: bottom;
            padding-right: 2px;
        }

        /* 3. METADATA: NO FAKTUR & PO */
        .faktur-po-label {
            font-weight: bold;
            font-size: 8.5pt;
            white-space: nowrap;
            vertical-align: middle;
        }
        .faktur-po-box {
            border: 1.5px solid #000;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 8.5pt;
            padding: 2.5px 4px;
        }

        /* KEPADA YTH */
        .cust-box-top {
            border-top: 1.5px solid #000;
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 2px 6px 1px 6px;
        }
        .cust-box-name {
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            font-weight: bold;
            color: #0000cc;
            font-size: 9pt;
            padding: 1px 6px;
            text-transform: uppercase;
        }
        .cust-box-city {
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 1px 6px 2px 6px;
            text-transform: uppercase;
        }
        .sopir-box-lbl {
            border: 1.5px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 2px 6px;
            text-transform: uppercase;
        }
        .sopir-box-val {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            border-right: 1.5px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 2px 6px;
        }

        /* 4. TABLE HEADER */
        .th-cyan {
            background-color: #d9f2f7;
            border: 1px solid #000;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
            padding: 3px 2px;
            white-space: nowrap;
        }

        /* TABLE CELLS */
        .td-cell {
            border: 1px solid #000;
            font-size: 8pt;
            padding: 2.5px 3px;
        }

        /* 5. SISTEM PEMBAYARAN */
        .pay-title {
            border: 1px solid #000;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
            padding: 2px 4px;
            white-space: nowrap;
        }
        .pay-opt-name {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 8pt;
            padding: 2px 4px;
            white-space: nowrap;
        }
        .pay-opt-empty {
            border: 1px solid #000;
            font-size: 8pt;
            padding: 2px 4px;
        }

        /* CALCULATION TABLE (DISCOUNT, DPP, PPN, JUMLAH) */
        .calc-title {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 8pt;
            padding: 2.5px 4px;
            white-space: nowrap;
        }
        .calc-amount {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 8pt;
            padding: 2.5px 4px;
        }

        /* 6. NB BOX */
        .nb-label-td {
            border: 1.5px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            text-align: center;
            vertical-align: middle;
            padding: 4px;
        }
        .nb-text-td {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            border-right: 1.5px solid #000;
            font-size: 7.5pt;
            line-height: 1.35;
            padding: 4px 6px;
            vertical-align: middle;
        }

        /* 7. BAGIAN KEUANGAN */
        .keuangan-title {
            text-align: center;
            font-size: 8.5pt;
            padding-top: 2px;
            padding-bottom: 6px;
        }
        .keuangan-signature {
            text-align: center;
            font-size: 8.5pt;
            padding-top: 36px;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>
@php
    \Carbon\Carbon::setLocale('id');

    $faktur = $faktur ?? null;

    // Sumber data item detail (Apakah dari DatFakturHdr langsung atau dari DatSoHdr)
    $items = isset($faktur) && $faktur->details && $faktur->details->isNotEmpty() 
        ? $faktur->details 
        : ($order->details ?? collect());

    $fakturNo = $faktur?->faktur_no ?? ($order->faktur_no ?: '-');
    $poNo = $order->customer_po_no ?: ($order->so_no ?: '-');
    
    $fakturTgl = $faktur?->faktur_tgl 
        ? \Carbon\Carbon::parse($faktur->faktur_tgl) 
        : ($order->so_tgl ? \Carbon\Carbon::parse($order->so_tgl) : now());
    $tanggalFormat = $fakturTgl->format('d-M-Y');

    $cust = $faktur?->customer ?? ($order->customer ?? null);
    $customerNm = $cust?->customer_nm ?: 'Pelanggan Umum';
    $kotaWilayah = $cust?->wilayah ?: ($cust?->kota ?: ($cust?->alamat_txt ? explode("\n", $cust->alamat_txt)[0] : '-'));
    $sopir = ($order->supir_nm ?? null) ?: (($order->pengeluaran ?? null)?->first()?->supir_nm ?? (($faktur->pengeluaran ?? null)?->supir_nm ?? ''));

    // Rekapitulasi Komersial & Pajak
    $diskon = (float) ($faktur?->diskon_nominal ?? ($order->diskon_total ?? 0));
    $dpp = (float) ($faktur?->dpp_nominal ?? ($order->dpp_nominal ?? 0));
    $ppn = (float) ($faktur?->ppn_nominal ?? ($order->ppn_nominal ?? 0));
    $grandTotal = (float) ($faktur?->total_tagihan ?? ($order->total_tagihan ?? ($dpp + $ppn - $diskon)));

    $ppnTipe = $faktur?->ppn_tipe ?? ($order->ppn_tipe ?? 'PPN_11');
    $ppnLabel = ($ppnTipe === 'PPN_12' || $ppnTipe === '12%') ? 'PPN 12%' : 'PPN 11%';

    // Akumulasi Banyaknya Unit
    $totalIkat = 0;
    $totalBungkus = 0;
    $totalKarton = 0;
    $totalBall = 0;
@endphp

<table class="master-grid">
    <colgroup>
        <col style="width: 7.0%;">   <!-- Col 1: No / Tunai / CekBG / NB / Logo -->
        <col style="width: 25.5%;">  <!-- Col 2: NAMA BARANG / NOMOR FAKTUR label / Pay Box / Bank Txt -->
        <col style="width: 10.0%;">  <!-- Col 3: IKAT / Box part 1 -->
        <col style="width: 8.0%;">   <!-- Col 4: BUNGKUS / Box part 2 -->
        <col style="width: 10.0%;">  <!-- Col 5: KARTON / Box part 3 -->
        <col style="width: 9.5%;">   <!-- Col 6: BALL / Spacer -->
        <col style="width: 14.0%;">  <!-- Col 7: Magelang / SOPIR / HARGA / DISCOUNT Labels -->
        <col style="width: 16.0%;">  <!-- Col 8: Date / Yono / JUMLAH Amounts / Calc Amounts -->
    </colgroup>

    {{-- BARIS KUNCI ZERO-HEIGHT UNTUK MENGUNCI PROPORSI KOLOM DOMPDF 100% PERSIS DENGAN GRID EXCEL FORM ASLI --}}
    <tr style="height: 0; line-height: 0;">
        <th style="width: 7.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 25.5%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 10.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 8.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 10.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 9.5%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 14.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 16.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
    </tr>

    <!-- 1. KOP SURAT (GARIS TEPI UTUH SESUAI GAMBAR 1) -->
    <tr>
        <td class="kop-logo-td">
            @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" style="max-height: 42px; width: auto;" alt="Logo PT Mirasa">
            @else
                <img src="{{ public_path('images/logo.png') }}" style="max-height: 42px; width: auto;" alt="Logo PT Mirasa">
            @endif
        </td>
        <td colspan="5" class="kop-info-td">
            <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
            <div class="kop-sub">JL. Munggur No. 2, Ambartawang, Mungkid, Magelang.</div>
            <div class="kop-sub">Telp. 0293-3280554, 3280556 Fax. 0293-782614</div>
        </td>
        <td colspan="2" class="kop-meta-td">
            <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
                <tr>
                    <td style="width: 46.7%; padding: 1px 0 1px 6px;">No. Doc</td>
                    <td style="padding: 1px 4px 1px 0;">: MFI/QMS/F.8.2.0.3</td>
                </tr>
                <tr>
                    <td style="padding: 1px 0 1px 6px;">No. Rev</td>
                    <td style="padding: 1px 4px 1px 0;">: 0</td>
                </tr>
                <tr>
                    <td style="padding: 1px 0 1px 6px;">Tgl. Terbit</td>
                    <td style="padding: 1px 4px 1px 0;">: 01-07-2022</td>
                </tr>
                <tr>
                    <td style="padding: 1px 0 1px 6px;">Halaman</td>
                    <td style="padding: 1px 4px 1px 0;">: 1</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- PEMBATAS KOP (SEIMBANG JARAK ATAS & BAWAH) -->
    <tr style="height: 4px; line-height: 4px;"><td colspan="8" style="border: none; font-size: 3pt; line-height: 4px; padding: 0;">&nbsp;</td></tr>

    <!-- 2. JUDUL DOKUMEN & TANGGAL -->
    <tr>
        <td colspan="6" style="border: none; text-align: center; vertical-align: middle;">
            <div class="title-text"><strong>FAKTUR PENJUALAN</strong></div>
        </td>
        <td class="date-magelang">Magelang,</td>
        <td class="date-val">{{ $tanggalFormat }}</td>
    </tr>

    <!-- PEMBATAS TITLE (MEMBERIKAN JARAK KE NOMOR FAKTUR & PO) -->
    <tr style="height: 6px;"><td colspan="8" style="border: none;">&nbsp;</td></tr>

    <!-- 3. METADATA: NOMOR FAKTUR & KEPADA YTH (BARIS 1) -->
    <tr>
        <td colspan="6" rowspan="4" style="border: none; vertical-align: top; padding: 0;">
            <div style="position: relative; top: 7px;">
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <colgroup>
                        <col style="width: 10.0%;">  <!-- Col 1: 7.0 / 70.0 = 10% -->
                        <col style="width: 36.43%;"> <!-- Col 2: 25.5 / 70.0 = 36.43% -->
                        <col style="width: 40.0%;">  <!-- Col 3,4,5: 28.0 / 70.0 = 40% -->
                        <col style="width: 13.57%;"> <!-- Col 6: 9.5 / 70.0 = 13.57% -->
                    </colgroup>
                    <tr style="height: 0; line-height: 0;">
                        <th style="width: 10.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
                        <th style="width: 36.43%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
                        <th style="width: 40.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
                        <th style="width: 13.57%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
                    </tr>
                    <tr>
                        <td style="border: none;">&nbsp;</td>
                        <td class="faktur-po-label"><strong>NOMOR FAKTUR</strong></td>
                        <td class="faktur-po-box"><strong>{{ $fakturNo }}</strong></td>
                        <td style="border: none;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="border: none;">&nbsp;</td>
                        <td class="faktur-po-label"><strong>NOMOR PO</strong></td>
                        <td class="faktur-po-box"><strong>{{ $poNo }}</strong></td>
                        <td style="border: none;">&nbsp;</td>
                    </tr>
                </table>
            </div>
        </td>
        <td colspan="2" class="cust-box-top"><strong>Kepada Yth.</strong></td>
    </tr>

    <!-- METADATA: CUSTOMER (BARIS 2) -->
    <tr>
        <td colspan="2" class="cust-box-name"><strong>{{ $customerNm }}</strong></td>
    </tr>

    <!-- METADATA: KOTA (BARIS 3) -->
    <tr>
        <td colspan="2" class="cust-box-city"><strong>{{ $kotaWilayah }}</strong></td>
    </tr>

    <!-- METADATA: SOPIR (BARIS 4 - GARIS VERTIKAL TEPAT SEJAJAR DENGAN JUMLAH & RP DI BAWAHNYA, PROPORSI 47%:53% SEIMBANG) -->
    <tr>
        <td class="sopir-box-lbl"><strong>SOPIR</strong></td>
        <td class="sopir-box-val"><strong>{{ $sopir ?: '&nbsp;' }}</strong></td>
    </tr>

    <!-- PEMBATAS SEBELUM TABEL -->
    <tr style="height: 8px;"><td colspan="8" style="border: none;">&nbsp;</td></tr>

    <!-- 4. TABEL BARANG (HEADER 2 TINGKAT BIRU CYAN) -->
    <tr>
        <th rowspan="2" class="th-cyan">No.</th>
        <th rowspan="2" class="th-cyan">NAMA BARANG</th>
        <th colspan="4" class="th-cyan">BANYAKNYA</th>
        <th rowspan="2" class="th-cyan">HARGA</th>
        <th rowspan="2" class="th-cyan">JUMLAH<br>( Rp )</th>
    </tr>
    <tr>
        <th class="th-cyan">IKAT</th>
        <th class="th-cyan">BUNGKUS</th>
        <th class="th-cyan">KARTON</th>
        <th class="th-cyan">BALL</th>
    </tr>
    @php $rowCount = 0; @endphp
    @foreach($items as $idx => $dtl)
        @php
            $rowCount++;
            $qty = (float) ($dtl->qty ?? ($dtl->pesan_qty ?? 0));
            $harga = (float) ($dtl->harga_satuan ?? 0);
            $lineTotal = (float) ($dtl->subtotal ?? ($dtl->subtotal_netto ?? ($qty * $harga)));
            $satuan = strtolower($dtl->barang?->satuan?->satuan_nm ?? ($dtl->satuan_cd ?? 'karton'));

            $valIkat = null;
            $valBungkus = null;
            $valKarton = null;
            $valBall = null;

            if (str_contains($satuan, 'ikat')) {
                $valIkat = $qty;
                $totalIkat += $qty;
            } elseif (str_contains($satuan, 'bungkus') || str_contains($satuan, 'bks') || str_contains($satuan, 'pack') || str_contains($satuan, 'pcs')) {
                $valBungkus = $qty;
                $totalBungkus += $qty;
            } elseif (str_contains($satuan, 'ball') || str_contains($satuan, 'bal')) {
                $valBall = $qty;
                $totalBall += $qty;
            } else {
                // Default Mirasa: Karton / Ctn
                $valKarton = $qty;
                $totalKarton += $qty;
            }
        @endphp
        <tr>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
            <td class="td-cell" style="font-weight: bold;">{{ $dtl->barang?->barang_nm ?? ($dtl->barang_nm ?? '-') }}</td>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ $valIkat !== null ? number_format($valIkat, 0, ',', '.') : '' }}</td>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ $valBungkus !== null ? number_format($valBungkus, 0, ',', '.') : '' }}</td>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ $valKarton !== null ? number_format($valKarton, 0, ',', '.') : '' }}</td>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ $valBall !== null ? number_format($valBall, 0, ',', '.') : '' }}</td>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ number_format($harga, 0, ',', '.') }}</td>
            <td class="td-cell" style="padding: 2.5px 4px;">
                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                    <tr>
                        <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                        <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ number_format($lineTotal, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach

    {{-- Baris Kosong Tambahan Agar Rapi & Sesuai Desain Lembar Faktur Fisik --}}
    @for($i = $rowCount; $i < 3; $i++)
        <tr>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
        </tr>
    @endfor

    <!-- TOTAL ROW -->
    <tr style="font-weight: bold;">
        <td class="td-cell">&nbsp;</td>
        <td class="td-cell" style="padding-left: 6px;">TOTAL</td>
        <td class="td-cell" style="text-align: center;">{{ $totalIkat > 0 ? number_format($totalIkat, 0, ',', '.') : '' }}</td>
        <td class="td-cell" style="text-align: center;">{{ $totalBungkus > 0 ? number_format($totalBungkus, 0, ',', '.') : '' }}</td>
        <td class="td-cell" style="text-align: center;">{{ $totalKarton > 0 ? number_format($totalKarton, 0, ',', '.') : '' }}</td>
        <td class="td-cell" style="text-align: center;">{{ $totalBall > 0 ? number_format($totalBall, 0, ',', '.') : '' }}</td>
        <td class="td-cell">&nbsp;</td>
        <td class="td-cell">&nbsp;</td>
    </tr>

    <!-- 5. POSISI PERSIS 100% GAMBAR 1: -->
    <!-- ROW 1: KIRI BARIS KOSONG BERBINGKAI UTUH (HINGGA KOLOM IKAT, TANPA GARIS VERTIKAL DALAM), KANAN DISCOUNT -->
    <tr>
        <td colspan="3" class="td-cell">&nbsp;</td>
        <td colspan="2" style="border: none;">&nbsp;</td>
        <td colspan="2" class="calc-title">DISCOUNT</td>
        <td class="calc-amount">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                    <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ $diskon > 0 ? number_format($diskon, 0, ',', '.') : '-' }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- ROW 2: KIRI SISTEM PEMBAYARAN (SEJAJAR VERTIKAL DENGAN KOLOM IKAT), KANAN DPP -->
    <tr>
        <td colspan="3" class="pay-title">Sistem pembayaran</td>
        <td colspan="2" style="border: none;">&nbsp;</td>
        <td colspan="2" class="calc-title">DPP</td>
        <td class="calc-amount">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                    <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ number_format($dpp, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- ROW 3: KIRI TUNAI & KOTAK KOSONG (SEJAJAR VERTIKAL DENGAN KOLOM IKAT), KANAN PPN -->
    <tr>
        <td class="pay-opt-name">Tunai</td>
        <td colspan="2" class="pay-opt-empty">&nbsp;</td>
        <td colspan="2" style="border: none;">&nbsp;</td>
        <td colspan="2" class="calc-title">{{ $ppnLabel }}</td>
        <td class="calc-amount">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                    <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ number_format($ppn, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- ROW 4: KIRI CEK/BG & KOTAK KOSONG (SEJAJAR VERTIKAL DENGAN KOLOM IKAT), KANAN JUMLAH DIBAYAR -->
    <tr>
        <td class="pay-opt-name">Cek/BG</td>
        <td colspan="2" class="pay-opt-empty">&nbsp;</td>
        <td colspan="2" style="border: none;">&nbsp;</td>
        <td colspan="2" class="calc-title">Jumlah dibayar</td>
        <td class="calc-amount">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                    <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- ROW 5: BAGIAN KEUANGAN DI KANAN -->
    <tr>
        <td colspan="6" style="border: none;">&nbsp;</td>
        <td colspan="2" style="border: none; text-align: center; vertical-align: top; padding-top: 3px;">
            <div class="keuangan-title">Bag. Keuangan</div>
        </td>
    </tr>

    <!-- ROW 6: NB BOX DI KIRI, TANDA TANGAN DI KANAN -->
    <tr>
        <td class="nb-label-td">NB:</td>
        <td colspan="4" class="nb-text-td">
            Untuk pembayaran via <u><em>Transfer / BG</em></u> mohon ke rekening an.<br>
            <strong>PT Mirasa Food Industry</strong> <u>AC. 1049173332 BCA</u>
        </td>
        <td style="border: none;">&nbsp;</td>
        <td colspan="2" style="border: none; text-align: center; vertical-align: bottom; padding-bottom: 2px;">
            <div class="keuangan-signature">( .................................... )</div>
        </td>
    </tr>
</table>

</body>
</html>
