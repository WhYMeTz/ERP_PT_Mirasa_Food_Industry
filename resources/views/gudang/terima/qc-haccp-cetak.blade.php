<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Lembar HACCP QC: {{ $qc->qc_no }} - PT Mirasa</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background: #f1f5f9; color: #000000; padding: 1.5rem 1rem; }
        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 1.25rem;
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
            padding: 0.55rem 1.15rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .btn-close {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 0.55rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .print-sheet {
            max-width: 900px;
            margin: 0 auto 2rem;
            background: #ffffff;
            padding: 1.5rem 1.75rem;
            border: 1.5px solid #000000;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            page-break-after: always;
        }
        table { width: 100%; border-collapse: collapse; }
        @media print {
            body { background: #ffffff; padding: 0; }
            .no-print-bar { display: none !important; }
            .print-sheet { border: 2px solid #000000 !important; box-shadow: none !important; padding: 10mm !important; max-width: 100% !important; margin: 0 !important; }
        }
    </style>
</head>
<body>
    @php
        $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');
        $firstDetail = $qc->details->first();
        $totalGross = $qc->details->sum('qty_timbang_gross');
        $totalRefraksi = $qc->details->sum('qty_refraksi');
        $totalReject = $qc->details->sum('qty_reject');
        $totalNetto = $qc->details->sum('qty_netto_lolos');

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
        } else {
            $docNo = 'MFI/HACCP-04/FRM-03/048/VIII/2021';
            $docTitle = 'Cheklist Standar Kebeterimaan Bahan Baku';
        }
    @endphp

    {{-- TOOLBAR ATAS (NO PRINT) --}}
    <div class="no-print-bar">
        <div>
            <strong style="color: #0f172a;">Dokumen Arsip HACCP PT Mirasa &bull; {{ $qc->qc_no }}</strong>
            <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.5rem;">({{ $kat }})</span>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            @if (Auth::user()?->canEditQc())
                <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="btn-edit" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; padding: 0.55rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <span>✏️</span> <span>Edit Dokumen</span>
                </a>
            @endif
            <button type="button" onclick="window.print()" class="btn-print">
                🖨️ Cetak Lembar Dokumen (A4)
            </button>
            <a href="javascript:window.close()" class="btn-close">
                Tutup
            </a>
        </div>
    </div>

    {{-- KONTEN DOKUMEN MODULAR --}}
    @if ($kat === 'SINGKONG')
        @include('gudang.qc.partials.doc-singkong')
    @elseif ($kat === 'MINYAK')
        @include('gudang.qc.partials.doc-minyak')
    @elseif ($kat === 'PLASTIK')
        @include('gudang.qc.partials.doc-plastik')
    @elseif ($kat === 'KARTON')
        @include('gudang.qc.partials.doc-karton')
    @else
        @include('gudang.qc.partials.doc-bahan-penolong')
    @endif
</body>
</html>
