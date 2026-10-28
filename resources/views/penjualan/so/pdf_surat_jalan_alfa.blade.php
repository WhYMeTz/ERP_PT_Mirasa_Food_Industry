@php
    $pengeluaran = $pengeluaran ?? null;
    $order = $order ?? ($pengeluaran?->order ?? null);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan PT. Sumber Alfaria Trijaya - {{ $order?->surat_jalan_no ?: ($order?->so_no ?? ($pengeluaran?->pengeluaran_no ?? 'Dokumen')) }}</title>
    <style>
        @page {
            size: a4 portrait;
            /* Margin kanan-kiri disamakan persis dengan lembar fisik Surat Jalan IFM: Kiri 3.5mm, Kanan 7.4mm, Atas 12mm, Bawah 10mm */
            margin: 12mm 7.4mm 10mm 3.5mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8.5pt;
            color: #000000;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        /* 1. KOP SURAT PERUSAHAAN (SESUAI GRID 1:1 EXCEL) */
        .kop-box {
            width: 100%;
            border: 1.5px solid #000000;
            border-collapse: collapse;
        }
        .kop-box td {
            vertical-align: middle;
        }
        .kop-logo-cell {
            width: 60px;
            border-right: 1.5px solid #000000;
            text-align: center;
            vertical-align: middle;
            padding: 3px;
        }
        .kop-identity-cell {
            border-right: 1.5px solid #000000;
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
            width: 42%;
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
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 4px;
        }
        .title-surat-jalan {
            font-size: 17pt;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            letter-spacing: 1px;
            margin-top: 24px;
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
            padding: 3px 6px;
            font-weight: bold;
            font-size: 11.5pt;
        }
        .cust-name-line {
            border-bottom: 1px solid #000000;
            padding: 3px 6px;
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: #0000ff;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .cust-address-line {
            border-bottom: 1px solid #000000;
            padding: 3px 6px;
            text-align: center;
            font-size: 8.5pt;
            font-weight: normal !important;
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
            text-align: center;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
        }

        /* 3. TEKS PENGANTAR KENDARAAN (GRID PRESISI EXCEL) */
        .intro-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 4px;
            font-size: 8.5pt;
        }

        /* 4. TABEL BARANG (HEADER PUTIH & CYAN PERSIS EXCEL) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-size: 9pt;
        }
        .data-table th {
            border: 1px solid #000000;
            color: #000000;
            font-weight: bold;
            text-align: center;
            padding: 3px 2px;
            font-size: 8.5pt;
        }
        .th-white {
            background-color: #ffffff;
        }
        .th-cyan {
            background-color: #d8f5fa;
        }
        .data-table td {
            border: 1px solid #000000;
            padding: 5px 4px;
            vertical-align: middle;
            font-size: 9.5pt;
        }

        /* 5. TANDA TANGAN (POSISI PRESISI EXCEL: PENERIMA & BAGIAN GUDANG) */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 28px;
            font-size: 9pt;
        }
        .ttd-space {
            height: 55px;
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
        $custName = strtoupper($customer?->customer_nm ?: 'PT. SUMBER ALFARIA TRIJAYA');
        
        // Alamat customer resmi persis blangko Excel PT Mirasa untuk Alfamart Klaten
        $custCdUpper = strtoupper($customer?->customer_cd ?? '');
        $custNmUpper = strtoupper($customer?->customer_nm ?? '');
        $rawAddrUpper = strtoupper($customer?->alamat_txt ?? '');

        if (str_contains($custCdUpper, 'ALFA') || str_contains($custNmUpper, 'ALFARIA') || str_contains($rawAddrUpper, 'KM 23') || str_contains($rawAddrUpper, 'DELANGGU')) {
            $custAddress = "JL. SOLO-YOGYA NO.22,\nSEKARAN,BANARAN,DELANGGU KAB.KLATEN";
        } else {
            $rawAddress = $customer?->alamat_txt ?: ($customer?->wilayah ?: "JL. SOLO-YOGYA NO.22,\nSEKARAN,BANARAN,DELANGGU KAB.KLATEN");
            $custAddress = strtoupper($rawAddress);
        }

        $sopir = strtoupper($order?->supir_nm ?: ($pengeluaran?->supir_nm ?? 'YUHDI'));
        $nopol = $order?->nopol_kendaraan ?: ($pengeluaran?->nopol_kendaraan ?? 'AA 9475 B');

        // 3. Rincian Barang
        $items = [];
        $totalKarton = 0;
        $totalBungkus = 0;
        $mainMultiplier = 10; // Standar Alfamart 1 karton = 10 bungkus

        if (!empty($pengeluaran) && $pengeluaran->details && $pengeluaran->details->count() > 0) {
            foreach ($pengeluaran->details as $dtl) {
                $qty = (float) $dtl->kirim_qty;
                $konversi = (float) ($dtl->barang?->konversi_qty ?? 0);
                $multiplier = ($konversi > 1) ? $konversi : 10;
                $mainMultiplier = $multiplier;
                $bungkus = $qty * $multiplier;
                $totalKarton += $qty;
                $totalBungkus += $bungkus;

                $plu = !empty($dtl->barang?->kode_pelanggan) ? $dtl->barang->kode_pelanggan : ($dtl->barang?->barang_cd ?? '4297432');
                $batchNo = $dtl->batch_no ?: (date('d m Y', strtotime($tglKirim)) . ' A');

                // Ambil Exp Date asli dari kartu stok batch fisik di gudang
                $batchStok = \App\Models\Gudang\DatStokBatch::where('barang_id', $dtl->barang_id)
                    ->where('batch_no', $dtl->batch_no)
                    ->first();
                $expDate = ($batchStok && $batchStok->expired_tgl)
                    ? date('d/m/Y', strtotime($batchStok->expired_tgl))
                    : date('d/m/Y', strtotime($tglKirim . ' +14 months'));

                $items[] = [
                    'plu'      => $plu,
                    'nama'     => $dtl->barang?->barang_nm ?? 'PAYUNG KERIPIK SINGKONG ASIN PCK 200g',
                    'karton'   => $qty,
                    'bungkus'  => $bungkus,
                    'batch_no' => $batchNo,
                    'exp_date' => $expDate,
                ];
            }
        } elseif (!empty($order) && $order->details && $order->details->count() > 0) {
            foreach ($order->details as $dtl) {
                $qty = (float) ($dtl->kirim_qty > 0 ? $dtl->kirim_qty : $dtl->pesan_qty);
                $konversi = (float) ($dtl->barang?->konversi_qty ?? 0);
                $multiplier = ($konversi > 1) ? $konversi : 10;
                $mainMultiplier = $multiplier;
                $bungkus = $qty * $multiplier;
                $totalKarton += $qty;
                $totalBungkus += $bungkus;

                $plu = !empty($dtl->barang?->kode_pelanggan) ? $dtl->barang->kode_pelanggan : ($dtl->barang?->barang_cd ?? '4297432');
                $batchNo = date('d m Y', strtotime($tglKirim)) . ' A';
                $expDate = date('d/m/Y', strtotime($tglKirim . ' +14 months'));

                $items[] = [
                    'plu'      => $plu,
                    'nama'     => $dtl->barang?->barang_nm ?? 'PAYUNG KERIPIK SINGKONG ASIN PCK 200g',
                    'karton'   => $qty,
                    'bungkus'  => $bungkus,
                    'batch_no' => $batchNo,
                    'exp_date' => $expDate,
                ];
            }
        }

        // Fallback jika kosong
        if (empty($items)) {
            $totalKarton = 36;
            $totalBungkus = 360;
            $items[] = [
                'plu'      => '4297432',
                'nama'     => 'PAYUNG KERIPIK SINGKONG ASIN PCK 200g',
                'karton'   => 36,
                'bungkus'  => 360,
                'batch_no' => '13 05 2026 A',
                'exp_date' => '05/12/2027',
            ];
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
                <div class="company-address">JL. Munggur No. 2, Ambartawang, Mungkid, Magelang.</div>
                <div class="company-telp">Telp. 0293-3280554, 3280556 Fax. 0293-782614</div>
            </td>
            <td class="doc-control-cell">
                <table class="doc-control-table">
                    <tr>
                        <td class="doc-label-col">No. Dok.</td>
                        <td class="doc-val-col">: MFI/QMS/F 8.2.0.2</td>
                    </tr>
                    <tr>
                        <td class="doc-label-col">Revisi</td>
                        <td class="doc-val-col">: 0</td>
                    </tr>
                    <tr>
                        <td class="doc-label-col">Tgl. Terbit</td>
                        <td class="doc-val-col">: 1/7/2022</td>
                    </tr>
                    <tr>
                        <td class="doc-label-col">Halaman</td>
                        <td class="doc-val-col">: 1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- SUB HEADER (SURAT JALAN & KOTAK CUSTOMER PT SUMBER ALFARIA TRIJAYA) --}}
    <table class="sub-header-table">
        <tr>
            <td style="width: 58%; vertical-align: middle;">
                <div class="title-surat-jalan">SURAT JALAN</div>
            </td>
            <td style="width: 42%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 3px;">
                    <tr>
                        <td style="font-size: 10.5pt; text-align: left; width: 35%;">Magelang,</td>
                        <td style="font-size: 11pt; font-weight: bold; text-align: center; width: 45%;">{{ $tglFormatted }}</td>
                        <td style="width: 20%;"></td>
                    </tr>
                </table>

                <table class="customer-dest-box">
                    <tr>
                        <td colspan="2" class="cust-title-line" style="font-size: 11.5pt; font-weight: bold;">Kepada Yth.</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="cust-name-line">{{ $custName }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="cust-address-line" style="font-weight: normal !important;">{!! nl2br(e($custAddress)) !!}</td>
                    </tr>
                    <tr>
                        <td class="sopir-label">SOPIR</td>
                        <td class="sopir-name">{{ $sopir }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TEKS PENGANTAR MOBIL BOX (POSISI PRESISI EXCEL) --}}
    <table class="intro-table">
        <tr>
            <td style="text-align: left; width: 30%; white-space: nowrap;">
                Dengan Kendaraan Mobil Box No. Polisi
            </td>
            <td style="text-align: left; width: 36%; font-weight: bold; font-size: 9pt; padding-left: 8px;">
                {{ $nopol ?: 'AA 9475 B' }}
            </td>
            <td style="text-align: left; width: 34%; font-style: italic; padding-left: 12px;">
                kami kirim barang sbb.:
            </td>
        </tr>
    </table>

    {{-- TABEL DATA RINCIAN BARANG --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" class="th-white" style="width: 12%;">PLU</th>
                <th rowspan="2" class="th-white" style="width: 29%;">NAMA BARANG</th>
                <th colspan="2" class="th-white" style="width: 26%;">BANYAKNYA</th>
                <th colspan="2" class="th-cyan" style="width: 33%;">KETERANGAN</th>
            </tr>
            <tr>
                <th class="th-white" style="width: 12%;">KARTON</th>
                <th class="th-white" style="width: 14%;">BUNGKUS ({{ (int)$mainMultiplier }})</th>
                <th class="th-cyan" style="width: 16.5%;">NO BATCH</th>
                <th class="th-cyan" style="width: 16.5%;">EXP DATE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $it)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $it['plu'] }}</td>
                    <td style="text-align: center; font-weight: bold; line-height: 1.25;">
                        {{ $it['nama'] }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ number_format($it['karton'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ number_format($it['bungkus'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $it['batch_no'] }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $it['exp_date'] }}
                    </td>
                </tr>
            @endforeach

            {{-- Tepat 2 Baris Kosong Tambahan Sesuai Fisik Asli Excel --}}
            @for($i = count($items); $i < 3; $i++)
                <tr>
                    <td style="height: 22px;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor

            {{-- Baris Total Akumulasi (Karton & Bungkus) --}}
            <tr style="font-weight: bold;">
                <td style="border: 1px solid #000;">&nbsp;</td>
                <td style="border: 1px solid #000;">&nbsp;</td>
                <td style="text-align: center; border: 1px solid #000; font-size: 10pt;">
                    {{ number_format($totalKarton, 0, ',', '.') }}
                </td>
                <td style="text-align: center; border: 1px solid #000; font-size: 10pt;">
                    {{ number_format($totalBungkus, 0, ',', '.') }}
                </td>
                <td style="border: 1px solid #000;">&nbsp;</td>
                <td style="border: 1px solid #000;">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN 2 PIHAK RESMI (POSISI PERSIS EXCEL) --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 8%;"></td>
            <td style="width: 32%; text-align: center; font-weight: bold;">Penerima</td>
            <td style="width: 25%;"></td>
            <td style="width: 35%; text-align: center; font-weight: bold;">Bagian Gudang</td>
        </tr>
        <tr>
            <td></td>
            <td class="ttd-space"></td>
            <td></td>
            <td class="ttd-space"></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: center;">(.......................)</td>
            <td></td>
            <td style="text-align: center;">(.......................)</td>
        </tr>
    </table>
</body>
</html>
