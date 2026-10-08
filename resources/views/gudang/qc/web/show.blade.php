@extends(request('popup') ? 'layouts.blank' : 'layouts.app')

@section('title', 'Dokumen QC: ' . $qc->qc_no . ' - PT Mirasa')

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 4mm 5mm 4mm 5mm;
        }
        *, *::before, *::after {
            box-sizing: border-box !important;
        }
        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            height: auto !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        header, .header-container, .navbar, footer, .no-print, .btn, .alert {
            display: none !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .doc-sheet-print-container {
            overflow: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .excel-doc-sheet {
            box-shadow: none !important;
            border: 2px solid #000000 !important;
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding: 3mm 4mm !important;
            font-size: 0.72rem !important;
            line-height: 1.2 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            page-break-after: avoid !important;
            break-after: avoid !important;
        }
        .excel-doc-sheet table {
            width: 100% !important;
            max-width: 100% !important;
        }
        .excel-doc-sheet td, .excel-doc-sheet th {
            padding: 2px 4px !important;
        }
        .excel-doc-sheet input, .excel-doc-sheet select {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            color: #000000 !important;
        }
        .excel-doc-sheet .doc-header-logo {
            width: 58px !important;
            height: 58px !important;
        }
    }
</style>
@endpush

@php
    $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');
    $firstDetail = $qc->details->first();
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalRefraksi = $qc->details->sum('qty_refraksi');
    $totalReject = $qc->details->sum('qty_reject');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin() && !Auth::user()?->isGudang();

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

@section('content')
<div style="max-width: {{ request('popup') ? '100%' : '1050px' }}; margin: 0 auto; padding-bottom: {{ request('popup') ? '0.5rem' : '3.5rem' }};">
    @if (!request('popup'))
        {{-- TOP ACTION TOOLBAR (NO PRINT) --}}
        <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                    &larr; Riwayat Tiket QC
                </a>
                <span style="font-size: 0.85rem; color: #64748b;">|</span>
                <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">Tiket #{{ $qc->qc_no }}</span>
                <span style="font-size: 0.75rem; font-weight: 800; background: #e0f2fe; color: #0284c7; padding: 0.2rem 0.55rem; border-radius: 12px;">
                    {{ $kat }}
                </span>
                @if ($kat === 'SINGKONG')
                    @php
                        $firstDtl = $qc->details->first();
                        $grade = $firstDtl?->grade_cd ?? 'A';
                    @endphp
                    @if ($qc->tahap_uji === 'PENGUJIAN_2')
                        <span style="font-size: 0.75rem; font-weight: 800; background: #f3e8ff; color: #7e22ce; padding: 0.2rem 0.55rem; border-radius: 12px; border: 1px solid #d8b4fe;">
                            🍟 PENGUJIAN II (PRODUKSI)
                        </span>
                    @else
                        <span style="font-size: 0.75rem; font-weight: 800; background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.55rem; border-radius: 12px; border: 1px solid #bae6fd;">
                            🚛 PENGUJIAN I (KEDATANGAN)
                        </span>
                    @endif
                    @if ($grade === 'B')
                        <span style="font-size: 0.75rem; font-weight: 800; background: #fef3c7; color: #b45309; padding: 0.2rem 0.55rem; border-radius: 12px; border: 1px solid #fde68a;">
                            🟡 Grade B
                        </span>
                    @elseif ($grade === 'REJECT')
                        <span style="font-size: 0.75rem; font-weight: 800; background: #fee2e2; color: #991b1b; padding: 0.2rem 0.55rem; border-radius: 12px; border: 1px solid #fca5a5;">
                            ❌ Afkir
                        </span>
                    @else
                        <span style="font-size: 0.75rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 0.2rem 0.55rem; border-radius: 12px; border: 1px solid #86efac;">
                            🟢 Grade A
                        </span>
                    @endif
                    @if ($qc->batch_no)
                        <span style="font-size: 0.75rem; font-weight: 800; background: #f8fafc; color: #334155; padding: 0.2rem 0.55rem; border-radius: 12px; border: 1px solid #cbd5e1;">
                            📦 Batch: {{ $qc->batch_no }}
                        </span>
                    @endif
                @endif
                @if ($qc->terima)
                    <a href="{{ route('gudang.terima.show', $qc->terima->terima_id) }}" style="font-size: 0.75rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 0.2rem 0.55rem; border-radius: 12px; text-decoration: none; border: 1px solid #86efac;">
                        📦 GRN #{{ $qc->terima->terima_no }}
                    </a>
                @endif
            </div>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 700;">
                        📱 Ringkas Mobile
                    </a>
                @endif

                @if ($qc->status_qc === 'DITOLAK_TOTAL' || $qc->details->sum('qty_reject') > 0)
                    <a href="{{ route('qc.inbound.berita_acara', $qc->qc_id) }}" class="btn btn-sm" style="background: #dc2626; color: #ffffff; border: none; font-weight: 800; border-radius: 8px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);">
                        📄 Cetak Berita Acara Penolakan
                    </a>
                @endif

                @if (Auth::user()?->canEditQc() && !$isLocked)
                    <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'ref' => 'detail']) }}" class="btn btn-sm" style="background: #0284c7; color: #ffffff; border: none; font-weight: 800; border-radius: 8px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3);">
                        ✏️ Edit Seluruh Dokumen
                    </a>
                @endif

                <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" style="background: #1e293b; border: none; border-radius: 8px; font-weight: 700;">
                    🖨️ Cetak Dokumen HACCP (A4)
                </button>
            </div>
        </div>
    @endif

    {{-- BANNER STATUS & PENOLAKAN --}}
    @if ($qc->status_qc === 'DITOLAK_TOTAL')
        <div class="no-print" style="margin-bottom: 1.25rem; background: #fef2f2; border: 1.5px solid #fca5a5; color: #991b1b; border-radius: 8px; padding: 0.85rem 1.15rem; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); flex-wrap: wrap;">
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div style="font-size: 0.85rem; line-height: 1.4;">
                    <strong style="font-size: 0.95rem; color: #b91c1c;">TIKET DITOLAK TOTAL DI GERBANG / GUDANG</strong><br>
                    Bahan baku singkong tidak memenuhi standar mutu pabrik. Muatan ditolak dan tidak diizinkan bongkar ke stok pabrik.
                </div>
            </div>
            <a href="{{ route('qc.inbound.berita_acara', $qc->qc_id) }}" class="btn btn-sm" style="background: #dc2626; color: #ffffff; border: none; font-weight: 800; border-radius: 8px; white-space: nowrap;">
                📄 Buka Berita Acara Penolakan
            </a>
        </div>
    @elseif ($qc->details->sum('qty_reject') > 0)
        <div class="no-print" style="margin-bottom: 1.25rem; background: #fef2f2; border: 1.5px solid #fca5a5; color: #991b1b; border-radius: 8px; padding: 0.85rem 1.15rem; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); flex-wrap: wrap;">
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div style="font-size: 0.85rem; line-height: 1.4;">
                    <strong style="font-size: 0.95rem; color: #b91c1c;">PENOLAKAN SEBAGIAN ({{ number_format($qc->details->sum('qty_reject'), 2, ',', '.') }} KG REJECT)</strong><br>
                    Terdapat bagian muatan (Grade B / afkir) yang ditolak dan tidak masuk stok pabrik. Berita Acara Penolakan resmi telah disiapkan.
                </div>
            </div>
            <a href="{{ route('qc.inbound.berita_acara', $qc->qc_id) }}" class="btn btn-sm" style="background: #dc2626; color: #ffffff; border: none; font-weight: 800; border-radius: 8px; white-space: nowrap;">
                📄 Buka Berita Acara Penolakan
            </a>
        </div>
    @elseif ($kat === 'SINGKONG')
        @if ($qc->status_uji_goreng === 'SELESAI')
            <div class="no-print" style="margin-bottom: 1.25rem; background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #166534; border-radius: 8px; padding: 0.85rem 1.15rem; display: flex; gap: 0.75rem; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div style="font-size: 0.85rem;">
                    <strong>Pengujian I Selesai:</strong> Sampling fisik kedatangan dan uji cepat rasa fryer di depan gerbang telah diverifikasi dengan rasa gurih (tidak pahit). Telah disetujui oleh Petugas QC &amp; Direktur.
                </div>
            </div>
        @endif
    @endif

    {{-- WRAPPER RESPONSIVE AGAR TABEL DOKUMEN DAPAT DI-PAN DI HP TANPA HANCUR --}}
    <div class="doc-sheet-print-container" style="overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 0.5rem;">
        @if ($kat === 'SINGKONG')
            @include('gudang.qc.partials.singkong.doc-singkong')
        @elseif ($kat === 'MINYAK')
            @include('gudang.qc.partials.minyak.doc-minyak')
        @elseif ($kat === 'PLASTIK')
            @include('gudang.qc.partials.plastik.doc-plastik')
        @elseif ($kat === 'KARTON')
            @include('gudang.qc.partials.karton.doc-karton')
        @else
            @include('gudang.qc.partials.bahan-penolong.doc-bahan-penolong')
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if (request('print') == 1)
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 400);
        });
    @endif
</script>
@endpush
