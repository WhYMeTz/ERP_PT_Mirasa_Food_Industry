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
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000000;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }

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
            padding: 2px 6px;
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
        .kop-doc-lbl {
            border-top: 1px solid #000;
            border-left: 1px solid #000;
            padding: 1px 0 1px 6px;
            font-size: 7.5pt;
            vertical-align: middle;
        }
        .kop-doc-val {
            border-top: 1px solid #000;
            border-right: 1px solid #000;
            padding: 1px 4px 1px 0;
            font-size: 7.5pt;
            vertical-align: middle;
        }
        .kop-doc-lbl-bot {
            border-top: 1px solid #000;
            border-left: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 1px 0 1px 6px;
            font-size: 7.5pt;
            vertical-align: middle;
        }
        .kop-doc-val-bot {
            border-top: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 1px 4px 1px 0;
            font-size: 7.5pt;
            vertical-align: middle;
        }

        /* 2. TITLE & DATE */
        .title-text {
            font-size: 15pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            text-decoration: underline;
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

        /* 3. NO FAKTUR & PO */
        .faktur-po-label {
            font-weight: bold;
            font-size: 8.5pt;
            white-space: nowrap;
            vertical-align: middle;
            padding-left: 4px;
        }
        .faktur-box-top {
            border-top: 1.5px solid #000;
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            border-bottom: 1px solid #000;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 8.5pt;
            padding: 2.5px 4px;
        }
        .faktur-box-bot {
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 8.5pt;
            padding: 2.5px 4px;
        }

        /* 4. CUSTOMER BOX */
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
        .cust-box-addr {
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            font-weight: bold;
            font-size: 7.5pt;
            line-height: 1.25;
            padding: 1px 6px 2px 6px;
            text-transform: uppercase;
        }
        .sopir-lbl {
            border: 1.5px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 2px 6px;
            text-transform: uppercase;
            text-align: center;
        }
        .sopir-val {
            border: 1.5px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 2px 6px;
            text-align: center;
            text-transform: uppercase;
        }

        /* 5. TABLE HEADER */
        .th-cyan {
            background-color: #d9f2f7;
            border: 1px solid #000;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
            padding: 3px 2px;
            white-space: nowrap;
        }
        .td-cell {
            border: 1px solid #000;
            font-size: 8pt;
            padding: 2px 3px;
        }

        /* 6. CALCULATION */
        .calc-title {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 8pt;
            padding: 2px 4px;
            white-space: nowrap;
        }
        .calc-amount {
            border: 1px solid #000;
            font-weight: bold;
            font-size: 8pt;
            padding: 2px 4px;
        }

        /* 7. NB BOX */
        .nb-label-td {
            border: 1.5px solid #000;
            font-weight: bold;
            font-size: 8.5pt;
            text-align: center;
            vertical-align: middle;
            padding: 3px;
        }
        .nb-text-td {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            border-right: 1.5px solid #000;
            font-size: 7.5pt;
            line-height: 1.3;
            padding: 3px 6px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
@php
    \Carbon\Carbon::setLocale('id');

    $faktur = $faktur ?? null;
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
    $customerNm = $cust?->customer_nm ?: 'PT MIDI UTAMA INDONESIA TBK';
    $customerAlamat = $cust?->alamat_txt ?: ($cust?->wilayah ?: '-');
    $sopir = ($order->supir_nm ?? null) ?: (($order->pengeluaran ?? null)?->first()?->supir_nm ?? (($faktur->pengeluaran ?? null)?->supir_nm ?? ''));

    // Rekapitulasi Komersial & Pajak
    $diskon = (float) ($faktur?->diskon_nominal ?? ($order->diskon_total ?? 0));
    $dpp = (float) ($faktur?->dpp_nominal ?? ($order->dpp_nominal ?? 0));
    $ppn = (float) ($faktur?->ppn_nominal ?? ($order->ppn_nominal ?? 0));
    $grandTotal = (float) ($faktur?->total_tagihan ?? ($order->total_tagihan ?? ($dpp + $ppn - $diskon)));

    // Diskon persen jika ada
    $diskonPersen = '';
    if ($dpp + $diskon > 0 && $diskon > 0) {
        $calcPct = round(($diskon / ($dpp + $diskon)) * 100);
        if ($calcPct > 0) {
            $diskonPersen = ' ' . $calcPct;
        }
    }

    $ppnTipe = $faktur?->ppn_tipe ?? ($order->ppn_tipe ?? 'PPN_11');
    $ppnLabel = ($ppnTipe === 'PPN_12' || $ppnTipe === '12%') ? 'PPN 12 %' : 'PPN 11 %';

    $totalKarton = 0;
    $totalBungkus = 0;
    $mainMultiplier = 10;
@endphp

<table class="master-grid">
    <!-- MASTER 7-COLUMN GRID MATCHING GAMBAR 1 EXCEL 1:1 -->
    <colgroup>
        <col style="width: 7.8%;">   <!-- Col 1: Logo / No. / NB label -->
        <col style="width: 22.2%;">  <!-- Col 2: No Faktur Label / Nama Barang pt 1 -->
        <col style="width: 21.4%;">  <!-- Col 3: No Faktur Box / Nama Barang pt 2 -->
        <col style="width: 7.2%;">   <!-- Col 4a: Karton -->
        <col style="width: 7.2%;">   <!-- Col 4b: Bungkus -->
        <col style="width: 12.2%;">  <!-- Col 5: No Dok / Magelang / SOPIR / Harga / Calc Titles -->
        <col style="width: 22.0%;">  <!-- Col 6: Doc Values / Date / FAJAR / Jumlah Rp / Calc Amounts -->
    </colgroup>

    {{-- KUNCI ZERO HEIGHT UNTUK MENGUNCI PROPORSI KOLOM DOMPDF PERSIS GAMBAR 1 --}}
    <tr style="height: 0; line-height: 0;">
        <th style="width: 7.8%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 22.2%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 21.4%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 7.2%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 7.2%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 12.2%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
        <th style="width: 22.0%; height: 0; padding: 0; border: none; font-size: 0; line-height: 0;"></th>
    </tr>

    <!-- KOP SURAT BARIS 1 (No. Dok.) -->
    <tr>
        <td rowspan="4" class="kop-logo-td">
            @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" style="max-height: 38px; width: auto;" alt="Logo PT Mirasa">
            @else
                <img src="{{ public_path('images/logo.png') }}" style="max-height: 38px; width: auto;" alt="Logo PT Mirasa">
            @endif
        </td>
        <td colspan="4" rowspan="4" class="kop-info-td">
            <div class="kop-company">PT. MIRASA FOOD INDUSTRY</div>
            <div class="kop-sub">JL. Munggur No. 2, Ambartawang, Mungkid, Magelang.</div>
            <div class="kop-sub">Telp. 0293-3280554, 3280556 Fax. 0293-782614</div>
        </td>
        <td class="kop-doc-lbl">No. Dok.</td>
        <td class="kop-doc-val">: MFI/QMS/F 8.2.0.3</td>
    </tr>
    <!-- KOP SURAT BARIS 2 (Revisi) -->
    <tr>
        <td class="kop-doc-lbl">Revisi</td>
        <td class="kop-doc-val">: 0</td>
    </tr>
    <!-- KOP SURAT BARIS 3 (Tgl. Terbit) -->
    <tr>
        <td class="kop-doc-lbl">Tgl. Terbit</td>
        <td class="kop-doc-val">: 1/7/2022</td>
    </tr>
    <!-- KOP SURAT BARIS 4 (Halaman) -->
    <tr>
        <td class="kop-doc-lbl-bot">Halaman</td>
        <td class="kop-doc-val-bot">: 1</td>
    </tr>

    <!-- ROW PEMBATAS TRANSPARAN -->
    <tr style="height: 5px;">
        <td colspan="7" style="border: none; height: 5px;"></td>
    </tr>

    <!-- JUDUL & TANGGAL -->
    <tr>
        <td style="border: none;">&nbsp;</td>
        <td colspan="2" style="border: none; text-align: center; vertical-align: middle;">
            <div class="title-text">FAKTUR PENJUALAN</div>
        </td>
        <td colspan="2" style="border: none;">&nbsp;</td>
        <td style="border: none;" class="date-magelang">Magelang,</td>
        <td style="border: none;" class="date-val">{{ $tanggalFormat }}</td>
    </tr>

    <!-- ROW PEMBATAS TRANSPARAN -->
    <tr style="height: 4px;">
        <td colspan="7" style="border: none; height: 4px;"></td>
    </tr>

    <!-- ROW 1 METADATA: NOMOR FAKTUR & KEPADA YTH -->
    <tr>
        <td style="border: none;">&nbsp;</td>
        <td class="faktur-po-label">NOMOR FAKTUR</td>
        <td class="faktur-box-top">{{ $fakturNo }}</td>
        <td colspan="2" style="border: none;">&nbsp;</td>
        <td colspan="2" class="cust-box-top">Kepada Yth.</td>
    </tr>

    <!-- ROW 2 METADATA: NOMOR PO & NAMA CUSTOMER -->
    <tr>
        <td style="border: none;">&nbsp;</td>
        <td class="faktur-po-label">NOMOR PO</td>
        <td class="faktur-box-bot">{{ $poNo }}</td>
        <td colspan="2" style="border: none;">&nbsp;</td>
        <td colspan="2" class="cust-box-name">{{ $customerNm }}</td>
    </tr>

    <!-- ROW 3 METADATA: ALAMAT CUSTOMER (LINE 1 & 2) -->
    <tr>
        <td colspan="5" style="border: none;">&nbsp;</td>
        <td colspan="2" class="cust-box-addr">{{ $customerAlamat }}</td>
    </tr>

    <!-- ROW 4 METADATA: SOPIR & NAMA SOPIR -->
    <tr>
        <td colspan="5" style="border: none;">&nbsp;</td>
        <td class="sopir-lbl">SOPIR</td>
        <td class="sopir-val">{{ $sopir ?: '-' }}</td>
    </tr>

    <!-- ROW PEMBATAS TRANSPARAN -->
    <tr style="height: 5px;">
        <td colspan="7" style="border: none; height: 5px;"></td>
    </tr>

    <!-- TABLE HEADER -->
    <thead>
        <tr>
            <th rowspan="2" class="th-cyan">No.</th>
            <th rowspan="2" colspan="2" class="th-cyan">NAMA BARANG</th>
            <th colspan="2" class="th-cyan">BANYAKNYA</th>
            <th rowspan="2" class="th-cyan">HARGA</th>
            <th rowspan="2" class="th-cyan">JUMLAH<br>( Rp )</th>
        </tr>
        <tr>
            <th class="th-cyan">KARTON</th>
            <th class="th-cyan">BUNGKUS ({{ $mainMultiplier }})</th>
        </tr>
    </thead>
    <tbody>
    @php $rowCount = 0; @endphp
    @foreach($items as $idx => $dtl)
        @php
            $rowCount++;
            $qty = (float) ($dtl->kirim_qty ?? ($dtl->pesan_qty ?? ($dtl->qty ?? 0)));
            $harga = (float) ($dtl->harga_satuan ?? ($dtl->harga ?? 0));
            $lineTotal = (float) ($dtl->subtotal_netto ?? ($dtl->subtotal_tagihan ?? ($qty * $harga)));

            $satuan = strtoupper(trim($dtl->satuan ?? ($dtl->satuan_cd ?? 'CTN')));
            $konversi = (float) ($dtl->barang?->konversi_qty ?? 0);
            $multiplier = ($konversi > 1) ? $konversi : 10;
            $mainMultiplier = $multiplier;

            if (in_array($satuan, ['BUNGKUS', 'PCS', 'PCK', 'BKS'])) {
                $bungkus = $qty;
                $karton = $multiplier > 0 ? ($qty / $multiplier) : $qty;
            } else {
                $karton = $qty;
                $bungkus = $qty * $multiplier;
            }

            $totalKarton += $karton;
            $totalBungkus += $bungkus;
        @endphp
        <tr>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
            <td colspan="2" class="td-cell" style="font-weight: bold; padding-left: 5px;">{{ $dtl->barang?->barang_nm ?? ($dtl->barang_nm ?? '-') }}</td>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ $karton > 0 ? number_format($karton, 0, ',', '.') : '' }}</td>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ $bungkus > 0 ? number_format($bungkus, 0, ',', '.') : '' }}</td>
            <td class="td-cell" style="text-align: center; font-weight: bold;">{{ number_format($harga, 0, ',', '.') }}</td>
            <td class="td-cell" style="padding: 2px 4px;">
                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                    <tr>
                        <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                        <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ number_format($lineTotal, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach

    {{-- Baris Kosong Tambahan Minimal 1 Sesuai Fisik Asli --}}
    @for($i = $rowCount; $i < 2; $i++)
        <tr>
            <td class="td-cell">&nbsp;</td>
            <td colspan="2" class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
            <td class="td-cell">&nbsp;</td>
        </tr>
    @endfor

    <!-- BARIS TOTAL BUNGKUS SESUAI FISIK GAMBAR -->
    <tr>
        <td class="td-cell">&nbsp;</td>
        <td colspan="2" class="td-cell">&nbsp;</td>
        <td class="td-cell">&nbsp;</td>
        <td class="td-cell" style="text-align: center; font-weight: bold;">{{ number_format($totalBungkus, 0, ',', '.') }}</td>
        <td class="td-cell">&nbsp;</td>
        <td class="td-cell">&nbsp;</td>
    </tr>

    <!-- FOOTER SECTION: KIRI NB & PENERIMA, KANAN KALKULASI & BAG. KEUANGAN -->
    <!-- ROW 1 KALKULASI: NB (rowspan 2) di kiri, DISCOUNT di kanan -->
    <tr>
        <td class="nb-label-td" rowspan="2">NB:</td>
        <td colspan="4" class="nb-text-td" rowspan="2">
            Untuk pembayaran via <u><em>Transfer / BG</em></u> mohon ke rekening an.<br>
            <strong>PT Mirasa Food Industry</strong> <u>AC. 1049173332 BCA</u>
        </td>
        <td class="calc-title">DISCOUNT{{ $diskonPersen }}</td>
        <td class="calc-amount">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                    <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ $diskon > 0 ? number_format($diskon, 0, ',', '.') : '-' }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- ROW 2 KALKULASI: DPP di kanan -->
    <tr>
        <td class="calc-title">DPP</td>
        <td class="calc-amount">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                    <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ number_format($dpp, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- ROW 3 KALKULASI: Penerima di kiri, PPN di kanan -->
    <tr>
        <td colspan="5" style="border: none; text-align: center; vertical-align: top; padding-top: 4px;">
            <div style="font-size: 8.5pt;">Penerima</div>
        </td>
        <td class="calc-title">{{ $ppnLabel }}</td>
        <td class="calc-amount">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                    <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ number_format($ppn, 2, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- ROW 4 KALKULASI: Ruang kosong di kiri, JUMLAH BAYAR di kanan -->
    <tr>
        <td colspan="5" style="border: none;">&nbsp;</td>
        <td class="calc-title" style="letter-spacing: 0.2px;">JUMLAH BAYAR</td>
        <td class="calc-amount">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 20px; border: none; font-weight: bold; text-align: left; padding: 0;">Rp</td>
                    <td style="border: none; text-align: right; font-weight: bold; padding: 0; padding-right: 3px;">{{ number_format($grandTotal, 2, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- ROW 5 TANDA TANGAN: Baris titik tanda tangan Penerima di kiri, Bag. Keuangan di kanan -->
    <tr>
        <td colspan="5" style="border: none; text-align: center; vertical-align: bottom; padding-bottom: 2px;">
            <div style="font-size: 8.5pt; padding-top: 25px;">( .................................... )</div>
        </td>
        <td colspan="2" style="border: none; text-align: center; vertical-align: top; padding-top: 3px;">
            <div style="font-size: 8.5pt;">Bag. Keuangan</div>
            <div style="padding-top: 25px; font-size: 8.5pt; padding-bottom: 2px;">( .................... )</div>
        </td>
    </tr>
    </tbody>
</table>

</body>
</html>
