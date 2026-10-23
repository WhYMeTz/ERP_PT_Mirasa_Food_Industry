<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan (Non-PPN) - {{ $order->surat_jalan_no ?: ($order->so_no ?? ($pengeluaran->pengeluaran_no ?? 'Dokumen')) }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #000000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* 1. KOP SURAT PERUSAHAAN (SESUAI FORM EXCEL RESMI) */
        .kop-box {
            width: 100%;
            border: 1px solid #000000;
            border-collapse: collapse;
        }
        .kop-box td {
            border: 1px solid #000000;
            vertical-align: middle;
        }
        .kop-logo-cell {
            width: 58px;
            text-align: center;
            padding: 4px;
        }
        .kop-identity-cell {
            padding: 4px 8px;
        }
        .company-title {
            font-size: 11pt;
            font-weight: bold;
            font-style: italic;
            letter-spacing: 0.3px;
        }
        .company-address {
            font-size: 8pt;
            margin-top: 1px;
        }
        .company-telp {
            font-size: 8pt;
            margin-top: 1px;
        }

        /* ISO Document Control Box */
        .doc-control-cell {
            width: 210px;
            padding: 0;
        }
        .doc-control-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .doc-control-table td {
            border: none;
            padding: 1.5px 5px;
            font-size: 7.5pt;
        }

        /* 2. SUB HEADER (SURAT JALAN & RUTE/SALESMAN/SOPIR) */
        .sub-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .sub-header-table td {
            vertical-align: top;
        }
        .title-surat-jalan {
            font-size: 15pt;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            letter-spacing: 1px;
            margin-top: 18px;
        }

        /* Box Rute & Personil */
        .personil-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            font-size: 8pt;
            font-weight: bold;
        }
        .personil-box td {
            border: 1px solid #000000;
            padding: 2.5px 6px;
        }

        /* 3. TEKS PENGANTAR KENDARAAN */
        .intro-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 4px;
            font-size: 8pt;
        }

        /* 4. TABEL BARANG (HEADER CYAN & BANYAK MERGED) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-size: 8pt;
        }
        .data-table th {
            background-color: #d8f5fa;
            border: 1px solid #000000;
            color: #000000;
            font-weight: bold;
            text-align: center;
            padding: 4px 2px;
            font-size: 8pt;
        }
        .data-table td {
            border: 1px solid #000000;
            padding: 3.5px 4px;
            vertical-align: middle;
            font-size: 8pt;
        }

        /* 5. TANDA TANGAN (3 PIHAK: Sales/Sopir | Salesman | Bag. Gudang) */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 8.5pt;
        }
        .ttd-table td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 2px;
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
        $tglFormatted = \Carbon\Carbon::parse($tglKirim)->translatedFormat('j-M-Y');

        // 2. Customer, Rute, Salesman, Sopir, dan Nopol
        $customer = $order?->customer ?: ($pengeluaran?->customer ?? null);
        $rute = strtoupper($customer?->wilayah ?: ($customer?->kota ?: ($customer?->alamat_txt ?: 'YOGYAKARTA')));
        $salesman = strtoupper($order?->salesman_nm ?? ($customer?->salesman_nm ?? ($order?->created_by ?: 'YANI')));
        $sopir = strtoupper($order?->supir_nm ?: ($pengeluaran?->supir_nm ?? 'SYAEFUDIN'));
        $nopol = $order?->nopol_kendaraan ?: ($pengeluaran?->nopol_kendaraan ?? '');

        // 3. Agregasi Rincian Barang
        $items = [];
        if (!empty($pengeluaran) && $pengeluaran->details && $pengeluaran->details->count() > 0) {
            foreach ($pengeluaran->details as $dtl) {
                $qty = (float) $dtl->kirim_qty;
                $konversi = (float) ($dtl->barang?->konversi_qty ?? 0);
                $multiplier = ($konversi > 1) ? $konversi : 20; // Default 20 bks per karton (standar Mirasa)
                $items[] = [
                    'nama'     => $dtl->barang?->barang_nm ?? '-',
                    'qty'      => $qty,
                    'bks'      => ($qty > 0) ? ($qty * $multiplier) : 0,
                    'catatan'  => $dtl->catatan_txt ?? '',
                ];
            }
        } elseif (!empty($order) && $order->details && $order->details->count() > 0) {
            foreach ($order->details as $dtl) {
                $qty = (float) ($dtl->kirim_qty > 0 ? $dtl->kirim_qty : $dtl->pesan_qty);
                $konversi = (float) ($dtl->barang?->konversi_qty ?? 0);
                $multiplier = ($konversi > 1) ? $konversi : 20; // Default 20 bks per karton (standar Mirasa)
                $items[] = [
                    'nama'     => $dtl->barang?->barang_nm ?? '-',
                    'qty'      => $qty,
                    'bks'      => ($qty > 0) ? ($qty * $multiplier) : 0,
                    'catatan'  => $dtl->catatan_txt ?? '',
                ];
            }
        }
    @endphp

    {{-- KOP SURAT PERUSAHAAN (SESUAI DOKUMEN ISO RESMI EXCEL) --}}
    <table class="kop-box">
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
                <div class="company-address">Jl.Munggur No.2 Kalangan Ambartawang Mungkid Magelang</div>
                <div class="company-telp">Telp. 0293-3280554, 3280556 Fax. 0293-782614</div>
            </td>
            <td class="doc-control-cell">
                <table class="doc-control-table">
                    <tr>
                        <td style="width: 75px;">No. Doc</td>
                        <td>: MFI/QMS/F.8.2.0.2</td>
                    </tr>
                    <tr>
                        <td>No. Rev</td>
                        <td>: 0</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 01-07-2022</td>
                    </tr>
                    <tr>
                        <td>Halaman</td>
                        <td>: 1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- SUB HEADER (SURAT JALAN & KOTAK RUTE / SALESMAN / SOPIR) --}}
    <table class="sub-header-table">
        <tr>
            <td style="width: 58%; vertical-align: middle;">
                <div class="title-surat-jalan">SURAT JALAN</div>
            </td>
            <td style="width: 42%;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 3px;">
                    <tr>
                        <td style="font-size: 8.5pt; text-align: left; width: 45%;">Magelang,</td>
                        <td style="font-size: 8.5pt; font-weight: bold; text-align: right; width: 55%;">{{ $tglFormatted }}</td>
                    </tr>
                </table>

                <table class="personil-box">
                    <tr>
                        <td style="width: 35%;">RUTE</td>
                        <td style="width: 65%;">{{ $rute }}</td>
                    </tr>
                    <tr>
                        <td>SALESMAN</td>
                        <td>{{ $salesman }}</td>
                    </tr>
                    <tr>
                        <td>SOPIR</td>
                        <td>{{ $sopir }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TEKS PENGANTAR MOBIL BOX --}}
    <table class="intro-table">
        <tr>
            <td style="text-align: left;">
                Dengan Kendaraan Mobil Box Nomor Polisi 
                @if(!empty($nopol))
                    <strong style="font-size: 8.5pt; padding: 0 4px; border-bottom: 1px solid #000;">{{ $nopol }}</strong>
                @else
                    <span style="display: inline-block; width: 120px; border-bottom: 1px dotted #000;">&nbsp;</span>
                @endif
            </td>
            <td style="text-align: right; font-style: italic;">
                kami kirim barang sbb.:
            </td>
        </tr>
    </table>

    {{-- TABEL DATA RINCIAN BARANG --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">No.</th>
                <th rowspan="2" style="text-align: left; padding-left: 8px;">NAMA BARANG</th>
                <th colspan="2" style="width: 140px;">BANYAK</th>
                <th rowspan="2" style="width: 180px;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 70px;">JUMLAH</th>
                <th style="width: 70px;">BKS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $it)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="padding-left: 8px; font-weight: bold;">
                        {{ $it['nama'] }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $it['qty'] > 0 ? number_format($it['qty'], 0, ',', '.') : '' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $it['bks'] > 0 ? number_format($it['bks'], 0, ',', '.') : '' }}
                    </td>
                    <td style="padding-left: 6px; font-size: 7.5pt; color: #1e293b;">
                        {{ $it['catatan'] }}
                    </td>
                </tr>
            @endforeach

            {{-- Baris Kosong Tambahan Agar Selalu Minimal 10 Baris Sesuai Form Fisik Asli --}}
            @for($i = count($items); $i < 10; $i++)
                <tr>
                    <td style="text-align: center; height: 19px;">{{ $i + 1 }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- TANDA TANGAN 3 PIHAK RESMI (Sales/Sopir | Salesman | Bag. Gudang) --}}
    <table class="ttd-table">
        <tr>
            <td style="font-weight: bold;">Sales/Sopir</td>
            <td style="font-weight: bold;">Salesman</td>
            <td style="font-weight: bold;">Bag. Gudang</td>
        </tr>
        <tr>
            <td class="ttd-space"></td>
            <td class="ttd-space"></td>
            <td class="ttd-space"></td>
        </tr>
        <tr>
            <td>( ........................................ )</td>
            <td>( ........................................ )</td>
            <td>( ........................................ )</td>
        </tr>
    </table>
</body>
</html>
