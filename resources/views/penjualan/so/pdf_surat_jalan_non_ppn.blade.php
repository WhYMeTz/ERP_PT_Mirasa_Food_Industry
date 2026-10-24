@php
    $pengeluaran = $pengeluaran ?? null;
    $order = $order ?? ($pengeluaran?->order ?? null);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $order?->surat_jalan_no ?: ($order?->so_no ?? ($pengeluaran?->pengeluaran_no ?? 'Dokumen')) }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8.5pt;
            color: #000000;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        /* 1. KOP SURAT PERUSAHAAN (SESUAI GRID 1:1 BLANGKO RESMI EXCEL) */
        .kop-box {
            width: 100%;
            table-layout: fixed;
            border: 1.5px solid #000000;
            border-collapse: collapse;
        }
        .kop-left-cell {
            width: 64.5%;
            vertical-align: middle;
            padding: 0;
            border-right: 1.5px solid #000000;
        }
        .kop-identity-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-logo-cell {
            width: 58px;
            text-align: center;
            vertical-align: middle;
            padding: 3px;
            border-right: 1.5px solid #000000;
        }
        .kop-identity-cell {
            padding: 3px 8px;
            vertical-align: middle;
        }
        .company-title {
            font-size: 11pt;
            font-weight: bold;
            font-style: italic;
            font-family: 'Arial', sans-serif;
            letter-spacing: 0.2px;
        }
        .company-address {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 1px;
        }
        .company-telp {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 1px;
        }

        /* ISO Document Control Box */
        .doc-control-cell {
            width: 35.5%;
            padding: 0;
            vertical-align: top;
        }
        .doc-control-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            font-family: 'Arial', sans-serif;
        }
        .doc-control-table td {
            padding: 1.5px 5px;
            font-size: 7.5pt;
        }
        .doc-label-col {
            width: 78px;
            border-right: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }
        .doc-val-col {
            border-bottom: 1px solid #000000;
        }
        .doc-control-table tr:last-child td {
            border-bottom: none !important;
        }

        /* 2. SUB HEADER (SURAT JALAN & KOTAK CUSTOMER) */
        .sub-header-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 4px;
        }
        .sub-header-left-cell {
            width: 64.5%;
            vertical-align: middle;
            padding: 0 10px 0 0;
        }
        .sub-header-right-cell {
            width: 35.5%;
            vertical-align: top;
            padding: 0;
        }
        .title-surat-jalan {
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1.5px;
        }
        .date-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        /* Customer Destination Box */
        .customer-dest-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-size: 8.5pt;
        }
        .cust-title-line {
            border-bottom: 1px solid #000000;
            padding: 2.5px 6px;
            font-weight: bold;
            font-size: 10.5pt;
        }
        .cust-name-line {
            border-bottom: 1px solid #000000;
            padding: 2.5px 6px;
            font-size: 10.5pt;
            font-weight: bold;
            color: #0000ff;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .cust-address-line {
            border-bottom: 1px solid #000000;
            padding: 2.5px 6px;
            font-size: 8.5pt;
            font-weight: normal;
            text-transform: uppercase;
            line-height: 1.25;
        }
        .sopir-label {
            width: 32%;
            border-right: 1px solid #000000;
            padding: 2.5px 6px;
            font-weight: bold;
            font-size: 8.5pt;
        }
        .sopir-name {
            width: 68%;
            padding: 2.5px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
        }

        /* 3. TEKS PENGANTAR TRUCK BOX */
        .intro-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 4px;
            font-size: 8.5pt;
        }

        /* 4. TABEL DATA BARANG (GRID BLANGKO EXCEL) */
        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin-top: 2px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000000;
            padding: 3.5px 4px;
            font-size: 8.5pt;
        }
        .data-table th {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #ccffff;
            padding: 3.5px 2px;
        }

        /* 5. TANDA TANGAN 3 PIHAK */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            font-size: 8.5pt;
        }
        .ttd-table td {
            vertical-align: top;
        }
        .ttd-space {
            height: 52px;
        }
    </style>
</head>
<body>
    @php
        // 1. Tanggal Dokumen
        $tglKirim = $order?->tgl_kirim_aktual 
            ?: ($pengeluaran?->pengeluaran_tgl ? \Carbon\Carbon::parse($pengeluaran->pengeluaran_tgl) : null)
            ?: ($order?->tgl_kirim_estimasi ?: ($order?->so_tgl ?: now()));
        $tglFormatted = \Carbon\Carbon::parse($tglKirim)->format('d-M-Y');

        // 2. Customer, Alamat, Sopir, Nopol
        $customer = $order?->customer ?: ($pengeluaran?->customer ?? null);
        $custName = strtoupper($customer?->customer_nm ?: 'CUSTOMER MANUAL');
        
        // Prioritas alamat: alamat_txt -> kota/wilayah
        $rawAddress = $customer?->alamat_txt ?: ($customer?->wilayah ?: ($customer?->kota ?: ''));
        $custAddress = strtoupper($rawAddress);

        $sopir = strtoupper($order?->supir_nm ?: ($pengeluaran?->supir_nm ?? ''));
        $nopol = $order?->nopol_kendaraan ?: ($pengeluaran?->nopol_kendaraan ?? '');

        // 3. Rincian Barang & Pemetaan Satuan (IKAT, BUNGKUS, KARTON, BALL)
        $items = [];
        $totalIkat = 0;
        $totalBungkus = 0;
        $totalKarton = 0;
        $totalBall = 0;

        $sourceDetails = (!empty($pengeluaran) && $pengeluaran->details && $pengeluaran->details->count() > 0)
            ? $pengeluaran->details
            : ($order?->details ?? collect());

        foreach ($sourceDetails as $dtl) {
            $qty = (float) ($dtl->kirim_qty > 0 ? $dtl->kirim_qty : ($dtl->pesan_qty ?? 0));
            $satuanCd = strtoupper($dtl->barang?->satuanBesar?->satuan_cd ?? ($dtl->barang?->satuanDasar?->satuan_cd ?? ''));
            $satuanNm = strtoupper($dtl->barang?->satuanBesar?->satuan_nm ?? ($dtl->barang?->satuanDasar?->satuan_nm ?? ''));
            $barangNm = strtoupper($dtl->barang?->barang_nm ?? '-');

            $ikat = 0;
            $bungkus = 0;
            $karton = 0;
            $ball = 0;

            if (str_contains($satuanCd, 'IKAT') || str_contains($satuanNm, 'IKAT')) {
                $ikat = $qty;
                $totalIkat += $qty;
            } elseif (str_contains($satuanCd, 'BAL') || str_contains($satuanNm, 'BAL') || str_contains($barangNm, 'BAL')) {
                $ball = $qty;
                $totalBall += $qty;
            } elseif (str_contains($satuanCd, 'BKS') || str_contains($satuanCd, 'BUNGKUS') || str_contains($satuanNm, 'BUNGKUS') || str_contains($satuanCd, 'PACK') || str_contains($satuanCd, 'PCS')) {
                $bungkus = $qty;
                $totalBungkus += $qty;
            } else {
                // Default barang jadi Finish Good PT Mirasa dalam Karton
                $karton = $qty;
                $totalKarton += $qty;
            }

            $items[] = [
                'nama'     => $barangNm,
                'ikat'     => $ikat,
                'bungkus'  => $bungkus,
                'karton'   => $karton,
                'ball'     => $ball,
                'catatan'  => $dtl->catatan_txt ?? '',
            ];
        }
    @endphp

    {{-- KOP SURAT PERUSAHAAN (SESUAI BLANGKO RESMI EXCEL) --}}
    <table class="kop-box">
        <tr>
            {{-- Kolom Kiri KOP (60%): Logo & Identitas Perusahaan --}}
            <td class="kop-left-cell">
                <table class="kop-identity-table">
                    <tr>
                        <td class="kop-logo-cell">
                            @if(!empty($logoBase64))
                                <img src="{{ $logoBase64 }}" alt="Logo PT Mirasa" style="width: 48px; height: 48px; display: block; margin: 0 auto;">
                            @else
                                <div style="width: 48px; height: 48px;"></div>
                            @endif
                        </td>
                        <td class="kop-identity-cell">
                            <div class="company-title">PT. MIRASA FOOD INDUSTRY</div>
                            <div class="company-address">Jl. Munggur No. 2, Ambartawang, Mungkid, Magelang.</div>
                            <div class="company-telp">Telp. 0293-3280554, 3280556 Fax. 0293-782614</div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Kolom Kanan KOP (40%): ISO Document Control Box --}}
            <td class="doc-control-cell">
                <table class="doc-control-table">
                    <tr>
                        <td class="doc-label-col">No. Doc</td>
                        <td class="doc-val-col">: MFI/QMS/F.8.2.0.2</td>
                    </tr>
                    <tr>
                        <td class="doc-label-col">No. Rev</td>
                        <td class="doc-val-col">: 0</td>
                    </tr>
                    <tr>
                        <td class="doc-label-col">Tgl. Terbit</td>
                        <td class="doc-val-col">: 01-07-2022</td>
                    </tr>
                    <tr>
                        <td class="doc-label-col">Halaman</td>
                        <td class="doc-val-col">: 1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- SUB HEADER (SURAT JALAN & KOTAK CUSTOMER) --}}
    <table class="sub-header-table">
        <tr>
            {{-- Bagian Kiri (60%): Judul SURAT JALAN --}}
            <td class="sub-header-left-cell">
                <div class="title-surat-jalan">SURAT JALAN</div>
            </td>

            {{-- Bagian Kanan (40%): Tanggal & Kotak Kepada Yth --}}
            <td class="sub-header-right-cell">
                <table class="date-table">
                    <tr>
                        <td style="font-size: 8.5pt; text-align: left; width: 45%;">Magelang,</td>
                        <td style="font-size: 8.5pt; font-weight: bold; text-align: right; width: 55%;">{{ $tglFormatted }}</td>
                    </tr>
                </table>

                <table class="customer-dest-box">
                    <tr>
                        <td colspan="2" class="cust-title-line">Kepada Yth.</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="cust-name-line">{{ $custName }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="cust-address-line">{!! nl2br(e($custAddress)) !!}</td>
                    </tr>
                    <tr>
                        <td class="sopir-label">SOPIR</td>
                        <td class="sopir-name">{{ $sopir ?: '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TEKS PENGANTAR TRUCK BOX --}}
    <table class="intro-table">
        <tr>
            <td style="text-align: left; width: 34%; white-space: nowrap;">
                Dengan Kendaraan Truck Box No. Polisi
            </td>
            <td style="text-align: left; width: 30.5%; font-weight: bold; font-size: 9pt; padding-left: 4px;">
                {{ $nopol ?: '' }}
            </td>
            <td style="text-align: right; width: 35.5%; font-style: italic;">
                kami kirim barang sbb.:
            </td>
        </tr>
    </table>

    {{-- TABEL DATA RINCIAN BARANG --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 8%;">No.</th>
                <th rowspan="2" style="width: 25%;">NAMA BARANG</th>
                <th colspan="4" style="width: 49.5%;">BANYAKNYA</th>
                <th rowspan="2" style="width: 17.5%;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 10%;">IKAT</th>
                <th style="width: 11.5%;">BUNGKUS</th>
                <th style="width: 10%;">KARTON</th>
                <th style="width: 18%;">BALL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $it)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                    <td style="text-align: left; padding-left: 6px; font-weight: bold;">
                        {{ $it['nama'] }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $it['ikat'] > 0 ? (fmod($it['ikat'], 1) != 0 ? number_format($it['ikat'], 2, ',', '.') : number_format($it['ikat'], 0, ',', '.')) : '' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $it['bungkus'] > 0 ? (fmod($it['bungkus'], 1) != 0 ? number_format($it['bungkus'], 2, ',', '.') : number_format($it['bungkus'], 0, ',', '.')) : '' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $it['karton'] > 0 ? (fmod($it['karton'], 1) != 0 ? number_format($it['karton'], 2, ',', '.') : number_format($it['karton'], 0, ',', '.')) : '' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $it['ball'] > 0 ? (fmod($it['ball'], 1) != 0 ? number_format($it['ball'], 2, ',', '.') : number_format($it['ball'], 0, ',', '.')) : '' }}
                    </td>
                    <td style="text-align: center; font-size: 8pt;">
                        {{ $it['catatan'] }}
                    </td>
                </tr>
            @endforeach

            {{-- Baris Kosong Tambahan Sesuai Fisik Blangko Excel Asli --}}
            @for($i = count($items); $i < 3; $i++)
                <tr>
                    <td style="height: 20px;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor

            {{-- Baris Total Akumulasi Sesuai Format Blangko Asli --}}
            <tr style="font-weight: bold;">
                <td style="border: 1px solid #000;">&nbsp;</td>
                <td style="border: 1px solid #000; font-weight: bold; padding-left: 6px; font-size: 9pt;">TOTAL</td>
                <td style="text-align: center; border: 1px solid #000; font-size: 9pt;">
                    {{ $totalIkat > 0 ? (fmod($totalIkat, 1) != 0 ? number_format($totalIkat, 2, ',', '.') : number_format($totalIkat, 0, ',', '.')) : '' }}
                </td>
                <td style="text-align: center; border: 1px solid #000; font-size: 9pt;">
                    {{ $totalBungkus > 0 ? (fmod($totalBungkus, 1) != 0 ? number_format($totalBungkus, 2, ',', '.') : number_format($totalBungkus, 0, ',', '.')) : '' }}
                </td>
                <td style="text-align: center; border: 1px solid #000; font-size: 9pt;">
                    {{ $totalKarton > 0 ? (fmod($totalKarton, 1) != 0 ? number_format($totalKarton, 2, ',', '.') : number_format($totalKarton, 0, ',', '.')) : '' }}
                </td>
                <td style="text-align: center; border: 1px solid #000; font-size: 9pt;">
                    {{ $totalBall > 0 ? (fmod($totalBall, 1) != 0 ? number_format($totalBall, 2, ',', '.') : number_format($totalBall, 0, ',', '.')) : '' }}
                </td>
                <td style="border: 1px solid #000;">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN 3 PIHAK --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 32%; text-align: center; font-weight: bold;">Penerima</td>
            <td style="width: 36%; text-align: center; font-weight: bold;">Sopir/Sales</td>
            <td style="width: 32%; text-align: center; font-weight: bold;">Bagian Gudang</td>
        </tr>
        <tr>
            <td class="ttd-space"></td>
            <td class="ttd-space"></td>
            <td class="ttd-space"></td>
        </tr>
        <tr>
            <td style="text-align: center;">(....................)</td>
            <td style="text-align: center;">(&nbsp;....................&nbsp;)</td>
            <td style="text-align: center;">(....................)</td>
        </tr>
    </table>
</body>
</html>
