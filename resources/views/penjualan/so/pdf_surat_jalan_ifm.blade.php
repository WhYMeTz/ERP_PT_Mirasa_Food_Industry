<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan IFM - {{ $order->surat_jalan_no ?: $order->so_no }}</title>
    <style>
        @page {
            size: a4 portrait;
            /* Margin presisi 100% sesuai lembar Excel fisik IFM: Kiri 3.5mm, Kanan 7.4mm, Atas 12mm, Bawah 10mm */
            margin: 12mm 7.4mm 10mm 3.5mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8.5pt;
            color: #000000;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }

        .doc-container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Perusahaan (Box penuh dengan garis tepi hitam keliling persis Gambar 2 Excel) */
        .kop-box {
            width: 100%;
            border: 1px solid #000000;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .kop-logo-cell {
            width: 9.6%;
            border-right: 1px solid #000000;
            text-align: center;
            vertical-align: middle;
            padding: 2px;
        }
        .kop-text-cell {
            width: 57%;
            padding: 4px 8px 0 8px;
            vertical-align: top;
        }
        .kop-company-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #000000;
            letter-spacing: 0.2px;
            line-height: 1.25;
            margin-bottom: 2px;
        }
        .kop-address {
            font-size: 9.5pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.25;
            margin-bottom: 2px;
        }
        .kop-contact {
            font-size: 9.5pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.25;
        }

        /* Box Dokumen ISO di Kanan Kop (Lebar 33.4% presisi sejajar Keterangan) */
        .kop-iso-cell {
            width: 33.4%;
            vertical-align: top;
            padding: 0;
            border-left: 1px solid #000000;
        }
        .iso-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: fixed;
        }
        .iso-table td {
            border: 1px solid #000000;
            padding: 2px 4px;
        }
        .iso-table tr:first-child td {
            border-top: none;
        }
        .iso-table tr td:first-child {
            border-left: none;
            width: 40%;
            font-weight: normal;
        }
        .iso-table tr td:last-child {
            border-right: none;
            width: 60%;
        }
        .iso-table tr:last-child td {
            border-bottom: none;
        }

        /* Sub Header (Baris 8-12): Jarak 18px dari Kop (Row 7 Excel Kosong) */
        .sub-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            margin-bottom: 0;
            table-layout: fixed;
        }

        /* Kotak Customer di Kanan (33.4% lebar, lurus sejajar ISO & Keterangan) */
        .customer-outer-box {
            width: 100%;
            border: 1px solid #000000;
            border-collapse: collapse;
            font-size: 8.5pt;
            table-layout: fixed;
        }
        .customer-outer-box td {
            border: none;
            padding: 1.5px 5px;
        }

        /* Baris Transport Nopol (Baris 14 Excel: Jarak 16px dari Customer Box / Row 13 Excel Kosong) */
        .transport-row-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            margin-bottom: 4px;
            font-size: 8.5pt;
            font-weight: bold;
            table-layout: fixed;
        }

        /* Tabel Rincian Barang (Baris 15-21 Excel) */
        table.table-barang-ifm {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin-top: 2px;
            table-layout: fixed;
        }
        table.table-barang-ifm th {
            background-color: #CCFFFF; /* Warna Light Turquoise persis 100% Excel IFM */
            color: #000000;
            font-weight: bold;
            font-size: 8.5pt;
            text-align: center;
            padding: 2.5px 2px;
            border: 1px solid #000000;
        }
        table.table-barang-ifm td {
            padding: 2.5px 3px;
            border: 1px solid #000000;
            vertical-align: middle;
            font-size: 8.5pt;
        }

        /* Garis Bawah Ganda untuk Baris TOTAL (Excel Accounting Double Bottom Border) */
        table.table-barang-ifm tfoot tr td {
            border-top: 1px solid #000000;
            border-bottom: 3px double #000000;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 3px 2px;
        }

        /* Kolom Tanda Tangan (Baris 26 Excel: 3 Pasang Kurung Bertitik) */
        .ttd-table-ifm {
            width: 100%;
            border-collapse: collapse;
            margin-top: 36px;
            table-layout: fixed;
        }
        .ttd-table-ifm td {
            text-align: center;
            font-size: 9pt;
            font-weight: normal;
        }
    </style>
</head>
<body>
    @php
        // Deteksi Plant/Cabang Tujuan: Semarang (MFI) vs Cikokol Tangerang (MFI-CKL)
        $soDate = $order->so_tgl ? $order->so_tgl->format('Y-m-d') : date('Y-m-d');
        
        $searchScope = strtoupper(
            ($order->customer?->customer_nm ?? '') . ' ' .
            ($order->customer?->customer_cd ?? '') . ' ' .
            ($order->customer?->alamat_txt ?? '') . ' ' .
            ($order->catatan_txt ?? '') . ' ' .
            ($order->customer_po_no ?? '') . ' ' .
            ($order->faktur_no ?? '')
        );

        $isCikokol = str_contains($searchScope, 'CKL') 
            || str_contains($searchScope, 'CIKOKOL') 
            || str_contains($searchScope, 'DAMATEX') 
            || str_contains($searchScope, 'TANGERANG')
            || (request()->query('plant') === 'ckl')
            || (request()->query('plant') === 'cikokol');

        $plantCode = $isCikokol ? 'MFI-CKL' : 'MFI';

        // Nomor Faktur MFI: XX/MFI/MM/YY atau XX/MFI-CKL/MM/YY
        if (!empty($order->faktur_no) && (str_contains($order->faktur_no, '/MFI/') || str_contains($order->faktur_no, '/MFI-CKL/'))) {
            $fakturMfiNo = $order->faktur_no;
        } else {
            $fakturMfiNo = app(\App\Services\Common\CodeGeneratorService::class)->generateFakturMfiNo($soDate, $plantCode);
        }

        // Tanggal Dokumen format Excel: j-M-Y (misal: 3-Sep-2026 atau 22-Oct-2026)
        $dateObj = $order->tgl_kirim_aktual ?: ($order->tgl_kirim_estimasi ?: ($order->so_tgl ?: now()));
        $tglDokumen = $dateObj->format('j-M-Y');

        // Customer & Baris Alamat / Kota
        $custUpper = strtoupper($order->customer?->customer_nm ?? '');
        $custDisplay = (str_contains($custUpper, 'INDOFOOD') || str_contains($custUpper, 'FORTUNA') || str_contains($custUpper, 'IFM'))
            ? 'PT. INDOFOOD FORTUNA'
            : ($order->customer?->customer_nm ?: 'PT. INDOFOOD FORTUNA');
        
        // Baris kedua di bawah PT. INDOFOOD FORTUNA:
        // Jika Cikokol Tangerang: "JL. WISMA DAMATEX TANGERANG"
        // Jika Semarang (default): "SEMARANG"
        if ($isCikokol) {
            $kotaDisplay = 'JL. WISMA DAMATEX TANGERANG';
        } else {
            $kotaDisplay = $order->customer?->kota ?: 'SEMARANG';
        }
    @endphp

    <div class="doc-container">
        {{-- KOP SURAT PERUSAHAAN (Box Penuh Tertutup Sesuai Fisik IFM) --}}
        <table class="kop-box">
            <tr>
                <td class="kop-logo-cell">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" alt="Logo PT Mirasa" style="width: 58px; height: 58px; display: block; margin: 0 auto;">
                    @else
                        <div style="width: 58px; height: 58px;"></div>
                    @endif
                </td>
                <td class="kop-text-cell">
                    <div class="kop-company-title">PT. MIRASA FOOD INDUSTRY</div>
                    <div class="kop-address">JL. Munggur No. 2, Ambartawang, Mungkid, Magelang.</div>
                    <div class="kop-contact">Telp. 0293-3280554, 3280556 Fax. 0293-782614</div>
                </td>
                <td class="kop-iso-cell">
                    <table class="iso-table">
                        <tr>
                            <td>No Doc</td>
                            <td>: MFI/QMS/F.8.2.0.2</td>
                        </tr>
                        <tr>
                            <td>No Rev</td>
                            <td>: 0</td>
                        </tr>
                        <tr>
                            <td>Tgl. Terbit</td>
                            <td>: 01-07-2022</td>
                        </tr>
                        <tr>
                            <td>Hal</td>
                            <td>: 1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- SUB HEADER (Baris 8-12): 5 Baris Presisi 1:1 Mengikuti Baris 8-12 Excel --}}
        <table class="sub-header-table">
            {{-- Baris 8 Excel: Tanggal di Kanan, Kiri Kosong --}}
            <tr>
                <td style="width: 9.6%; height: 16px;"></td>
                <td style="width: 31.3%;"></td>
                <td style="width: 25.7%;"></td>
                <td style="width: 33.4%; vertical-align: middle; padding-bottom: 3px;">
                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <tr>
                            <td style="width: 40%; text-align: left; font-size: 9.5pt; font-weight: bold; padding: 0 4px;">
                                Magelang,
                            </td>
                            <td style="width: 60%; text-align: left; font-size: 9.5pt; font-weight: bold; padding: 0 4px;">
                                {{ $tglDokumen }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- Baris 9 Excel: Kepada Yth. di Kanan, Kiri Kosong --}}
            <tr>
                <td style="height: 18px;"></td>
                <td></td>
                <td></td>
                <td style="border-top: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000; font-size: 9.5pt; font-weight: bold; padding: 2px 5px 1px 5px;">
                    Kepada Yth.
                </td>
            </tr>

            {{-- Baris 10 Excel: SURAT JALAN di Kiri, PT INDOFOOD FORTUNA di Kanan --}}
            <tr>
                <td style="height: 20px;"></td>
                <td style="vertical-align: middle; padding-left: 2px;">
                    <div style="padding-left: 28px;">
                        <span style="font-size: 12pt; font-weight: bold; text-decoration: underline; letter-spacing: 0.5px;">
                            SURAT JALAN
                        </span>
                    </div>
                </td>
                <td></td>
                <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; font-size: 10.5pt; font-weight: bold; color: #0000ff; text-transform: uppercase; padding: 1px 5px;">
                    {{ $custDisplay }}
                </td>
            </tr>

            {{-- Baris 11 Excel: NO. FAKTUR & NO. PO di Kiri (1 Tabel Terpadu Presisi Excel), SEMARANG di Kanan --}}
            <tr>
                <td style="height: 18px;"></td>
                <td rowspan="2" style="vertical-align: middle; padding-left: 2px;">
                    <table style="border-collapse: collapse; font-size: 10pt; font-weight: bold; text-align: left; width: auto;">
                        <tr>
                            <td style="white-space: nowrap; padding: 1.5px 0;">NO. FAKTUR</td>
                            <td style="padding: 1.5px 4px; text-align: center;">:</td>
                            <td style="white-space: nowrap; font-size: 10pt; padding: 1.5px 0;">{{ $fakturMfiNo }}</td>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap; padding: 1.5px 0;">NO. PO</td>
                            <td style="padding: 1.5px 4px; text-align: center;">:</td>
                            <td style="white-space: nowrap; font-size: 10pt; padding: 1.5px 0;">{{ $order->customer_po_no ?: ($order->catatan_txt ?: '') }}</td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; font-size: 9.5pt; font-weight: bold; text-transform: uppercase; padding: 1px 5px 3px 5px;">
                    {{ $kotaDisplay }}
                </td>
            </tr>

            {{-- Baris 12 Excel: NO. PO di Kiri tercakup rowspan="2", SOPIR di Kanan --}}
            <tr>
                <td style="height: 20px;"></td>
                <td></td>
                <td style="border: 1px solid #000000; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 9.5pt; font-weight: bold; table-layout: fixed;">
                        <tr>
                            <td style="width: 40%; text-align: center; border-right: 1px solid #000000; padding: 2px 4px;">
                                SOPIR
                            </td>
                            <td style="width: 60%; padding: 2px 4px;">
                                {{ $order->supir_nm ?: '' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- BARIS KENDARAAN (Baris 14 Excel: Tepat di atas tabel data) --}}
        <table class="transport-row-table">
            <tr>
                <td style="width: 66.6%; white-space: nowrap; vertical-align: middle;">
                    Dengan Kendaraan Truck Box No. Polisi &nbsp;&nbsp; <span>{{ $order->nopol_kendaraan ?: '' }}</span>
                </td>
                <td style="width: 33.4%; vertical-align: middle; text-align: left;">
                    kami kirim barang sbb.:
                </td>
            </tr>
        </table>

        {{-- TABEL RINCIAN BARANG (Baris 15-21 Excel: Header Warna #CCFFFF Light Turquoise) --}}
        <table class="table-barang-ifm">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 9.6%;">No.</th>
                    <th rowspan="2" style="width: 31.3%;">NAMA BARANG</th>
                    <th colspan="2" style="width: 25.7%;">BANYAKNYA</th>
                    <th colspan="2" style="width: 33.4%;">KETERANGAN</th>
                </tr>
                <tr>
                    <th style="width: 13.3%;">KARTON</th>
                    <th style="width: 12.4%;">KG</th>
                    <th style="width: 13.5%;">TGL PROD.</th>
                    <th style="width: 19.9%;">VARIETAS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalKarton = 0;
                    $totalKg = 0;
                    $rowCount = 0;
                @endphp
                @foreach($order->details as $idx => $dtl)
                    @php
                        $kartonQty = (float) $dtl->pesan_qty;
                        $totalKarton += $kartonQty;

                        // Konversi Kg: Standar 6 Kg per Karton untuk pasokan B2B Indofood
                        $multiplierKg = ($dtl->barang && (float)$dtl->barang->konversi_qty > 1) 
                            ? (float)$dtl->barang->konversi_qty 
                            : 6.0;
                        $kgQty = $kartonQty * $multiplierKg;
                        $totalKg += $kgQty;

                        // Tanggal Produksi & Varietas
                        $hasDate = preg_match('/\d{1,2}-[A-Za-z]{3}-\d{2,4}/', $dtl->catatan_txt ?? '', $matches);
                        $tglProd = $hasDate ? $matches[0] : ($order->tgl_kirim_aktual ? $order->tgl_kirim_aktual->format('d-M-y') : ($order->so_tgl ? $order->so_tgl->format('d-M-y') : date('d-M-y')));
                        
                        $varietas = (str_contains($dtl->catatan_txt ?? '', 'MGU') || str_contains($dtl->catatan_txt ?? '', 'Varietas')) 
                            ? trim(explode('|', $dtl->catatan_txt)[1] ?? 'MGU/A') 
                            : 'MGU/A';
                        $rowCount++;
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                        <td style="font-weight: bold; padding-left: 5px; text-transform: uppercase;">
                            {{ $dtl->barang?->barang_nm ?: 'KERIPIK SINGKONG TANPA BUMBU' }}
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ number_format($kartonQty, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ number_format($kgQty, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ $tglProd }}
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ $varietas }}
                        </td>
                    </tr>
                @endforeach

                {{-- Tepat 3 Baris Kosong Persis Sesuai Excel Fisik (Baris 18, 19, 20) --}}
                @for($i = $rowCount; $i < 4; $i++)
                    <tr>
                        <td style="height: 14px;">&nbsp;</td>
                        <td style="height: 14px;">&nbsp;</td>
                        <td style="height: 14px;">&nbsp;</td>
                        <td style="height: 14px;">&nbsp;</td>
                        <td style="height: 14px;">&nbsp;</td>
                        <td style="height: 14px;">&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: center; letter-spacing: 0.5px;">
                        TOTAL
                    </td>
                    <td style="text-align: center;">
                        {{ number_format($totalKarton, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        {{ number_format($totalKg, 0, ',', '.') }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- KOLOM TANDA TANGAN (Baris 26 Excel: 3 Pasang Kurung Bertitik, Diposisikan Persis Sesuai Kolom Data) --}}
        <table class="ttd-table-ifm">
            <tr>
                <td style="width: 40.9%;">(.......................)</td>
                <td style="width: 25.7%;">(.......................)</td>
                <td style="width: 33.4%;">(.......................)</td>
            </tr>
        </table>
    </div>
</body>
</html>
