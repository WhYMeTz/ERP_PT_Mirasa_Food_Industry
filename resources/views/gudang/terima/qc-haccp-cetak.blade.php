<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Lembar HACCP QC: {{ $qc->qc_no }} - PT Mirasa</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background: #f1f5f9; color: #000; padding: 1.5rem 1rem; }
        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 1rem;
            background: #ffffff;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .btn-print {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .btn-close {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .a4-sheet {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 1.5rem 2rem;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        table { width: 100%; border-collapse: collapse; }
        .tbl-border th, .tbl-border td { border: 1.5px solid #000; padding: 5px 8px; }
        @media print {
            body { background: #ffffff; padding: 0; }
            .no-print-bar { display: none !important; }
            .a4-sheet { border: none; box-shadow: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    @php
        $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');
        $firstDetail = $qc->details->first();
        $docTitle = 'Checklist Standar Kebeterimaan Bahan Masuk';
        $docNo = 'MFI/HACCP-04/FRM-03/048/VIII/2021';
        $revisi = '1';
        $tglTerbit = '11-09-2023';

        if ($kat === 'MINYAK') {
            $docNo = 'MFI/HACCP-04/FRM-03/029/VIII/2021';
            $docTitle = 'Cheklist Pemeriksaan Kedatangan Minyak Goreng';
        } elseif ($kat === 'PLASTIK') {
            $docNo = 'MFI/HACCP-04/FRM-03/030/VIII/2021';
            $docTitle = 'Cheklist Pemeriksaan Kedatangan Plastik';
        } elseif ($kat === 'KARTON') {
            $docNo = 'MFI/HACCP-04/FRM-03/031/VIII/2021';
            $docTitle = 'Cheklist Pemeriksaan Kedatangan Karton';
        } elseif ($kat === 'MSG') {
            $docNo = 'MFI/HACCP-04/FRM-03/032/VIII/2021';
            $docTitle = 'Cheklist Pemeriksaan Kedatangan MSG';
        } elseif ($kat === 'GARAM') {
            $docNo = 'MFI/HACCP-04/FRM-03/033/VIII/2021';
            $docTitle = 'Cheklist Pemeriksaan Kedatangan Garam';
        } elseif ($kat === 'PERENYAH') {
            $docNo = 'MFI/HACCP-04/FRM-03/063/IX/2023';
            $docTitle = 'Cheklist Pemeriksaan Kedatangan Perenyah';
            $revisi = '0';
            $tglTerbit = '14-09-2023';
        }
    @endphp

    {{-- TOOLBAR ATAS (HANYA DILIHAT DI LAYAR) --}}
    <div class="no-print-bar">
        <div>
            <strong style="color: #0f172a;">Dokumen Arsip HACCP &bull; {{ $qc->qc_no }}</strong>
            <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.5rem;">({{ $kat }})</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" onclick="window.print()" class="btn-print">
                🖨️ Cetak Lembar HACCP (A4)
            </button>
            <a href="javascript:window.close()" class="btn-close">
                Tutup
            </a>
        </div>
    </div>

    {{-- KERTAS CETAK A4 PABRIK PT MIRASA --}}
    <div class="a4-sheet">
        {{-- KOP SURAT RESMI PT MIRASA --}}
        <div style="border: 2px solid #000; margin-bottom: 0.75rem;">
            <table>
                <tr>
                    <td style="width: 85px; text-align: center; vertical-align: middle; padding: 6px; border-right: 2px solid #000;">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 65px; height: 65px; object-fit: contain;">
                    </td>
                    <td style="vertical-align: middle; text-align: center; padding: 6px;">
                        <div style="font-size: 1.15rem; font-weight: 900; color: #000; letter-spacing: 0.05em;">
                            PT. MIRASA FOOD INDUSTRY
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: #000; margin-top: 3px;">
                            {{ $docTitle }}
                        </div>
                    </td>
                    <td style="width: 250px; vertical-align: middle; padding: 0; border-left: 2px solid #000;">
                        <table style="font-size: 0.72rem;">
                            <tr style="border-bottom: 1px solid #000;">
                                <td style="padding: 3px 6px; font-weight: 700; width: 85px; border-right: 1px solid #000;">No. Dokumen</td>
                                <td style="padding: 3px 6px; font-weight: 800;">{{ $docNo }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000;">
                                <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000;">Revisi</td>
                                <td style="padding: 3px 6px;">{{ $revisi }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000;">Tgl. Terbit</td>
                                <td style="padding: 3px 6px;">{{ $tglTerbit }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- METADATA LOGISTIK KEDATANGAN --}}
        <table class="tbl-border" style="font-size: 0.78rem; margin-bottom: 0.75rem;">
            <tr>
                <td style="width: 22%; font-weight: 700;">Nama Produsen / Suplier</td>
                <td style="width: 28%;">: {{ $qc->supplier?->supplier_nm ?? '-' }}</td>
                <td style="width: 22%; font-weight: 700;">Tanggal Datang / Jam</td>
                <td style="width: 28%;">: {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Negara Produsen</td>
                <td>: {{ $qc->negara_produsen ?? 'Indonesia' }}</td>
                <td style="font-weight: 700;">Tanggal Periksa</td>
                <td>: {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: 700;">No. Surat Jalan</td>
                <td>: {{ $qc->surat_jalan_supplier ?? '-' }}</td>
                <td style="font-weight: 700;">Nomor DO</td>
                <td>: {{ $qc->nomor_do ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Armada Truk / Sopir</td>
                <td>: {{ $qc->plat_nomor_truk ?: '-' }} / {{ $qc->sopir_nama ?: '-' }}</td>
                <td style="font-weight: 700;">Gudang Tujuan</td>
                <td>: {{ $qc->gudang?->gudang_nm ?? '-' }}</td>
            </tr>
        </table>

        {{-- AUDIT KEBERSIHAN & JAMINAN HALAL --}}
        <div style="font-weight: 800; font-size: 0.8rem; margin-bottom: 0.35rem; text-transform: uppercase;">
            1. Standar Kebersihan Transportasi &amp; Jaminan Halal
        </div>
        <table class="tbl-border" style="font-size: 0.78rem; margin-bottom: 0.75rem;">
            <tr>
                <td style="width: 55%;">Kondisi Transportasi (Bersih, Bebas Cemaran / Najis)</td>
                <td style="font-weight: 700; color: {{ $qc->bebas_cemaran_st ? '#047857' : '#b91c1c' }};">
                    {{ $qc->bebas_cemaran_st ? '☑ Memenuhi Syarat (Bebas Najis/Cemaran)' : '☒ Tidak Memenuhi Syarat' }}
                </td>
            </tr>
            <tr>
                <td>Apakah barang diangkut bersama dengan barang haram?</td>
                <td style="font-weight: 700;">
                    {{ $qc->tidak_bercampur_haram_st ? '☑ TIDAK (Aman / Terpisah)' : '☒ YA (Tercampur)' }}
                </td>
            </tr>
            <tr>
                <td>Apakah bahan terdaftar &amp; disetujui LPPOM MUI / BPJPH?</td>
                <td style="font-weight: 700;">
                    {{ $qc->ada_sertifikat_halal_st ? '☑ YA (Terdaftar & Terverifikasi)' : '☒ Tidak' }}
                </td>
            </tr>
            <tr>
                <td>Apakah sertifikat halal barang masih berlaku?</td>
                <td style="font-weight: 700;">
                    {{ $qc->sertifikat_halal_berlaku_st ? '☑ YA (Masa Berlaku Valid)' : '☒ Tidak' }}
                </td>
            </tr>
        </table>

        {{-- RINCIAN TIMBANGAN & MUTU --}}
        <div style="font-weight: 800; font-size: 0.8rem; margin-bottom: 0.35rem; text-transform: uppercase;">
            2. Rincian Sampling Timbangan &amp; Parameter Mutu
        </div>
        <table class="tbl-border" style="font-size: 0.78rem; margin-bottom: 1.25rem;">
            <thead>
                <tr style="background: #f8fafc; font-weight: 800;">
                    <th>Barang / Spesifikasi</th>
                    <th style="text-align: right;">Gross (kg)</th>
                    <th style="text-align: right;">Refraksi (kg)</th>
                    <th style="text-align: right;">Reject (kg)</th>
                    <th style="text-align: right;">Netto Lolos (kg)</th>
                    <th>Catatan Mutu Fisik</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($qc->details as $d)
                    <tr>
                        <td>{{ $d->barang?->barang_nm ?? $qc->nama_jenis }}</td>
                        <td style="text-align: right;">{{ number_format($d->qty_timbang_gross, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($d->qty_refraksi, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($d->qty_reject, 2, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: 800;">{{ number_format($d->qty_netto_lolos, 2, ',', '.') }}</td>
                        <td>{{ $d->catatan_cacat ?: 'Sesuai Standar Mutu PT Mirasa' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TANDA TANGAN SERAH TERIMA OPERASIONAL GUDANG --}}
        <table style="font-size: 0.78rem; text-align: center; margin-top: 1.5rem;">
            <tr>
                <td style="width: 33%; padding-bottom: 3.5rem;">
                    <div>Petugas Sampling QC:</div>
                </td>
                <td style="width: 33%; padding-bottom: 3.5rem;">
                    <div>Pengemudi / Ekspedisi:</div>
                </td>
                <td style="width: 33%; padding-bottom: 3.5rem;">
                    <div>Kepala / Admin Gudang:</div>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">( {{ $qc->petugas_qc_nama }} )</td>
                <td style="font-weight: 700;">( {{ $qc->sopir_nama ?: 'Sopir Ekspedisi' }} )</td>
                <td style="font-weight: 700;">( .................................... )</td>
            </tr>
        </table>
    </div>
</body>
</html>
